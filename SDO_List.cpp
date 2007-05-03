/*
+----------------------------------------------------------------------+
| (c) Copyright IBM Corporation 2005, 2006.                            |
| All Rights Reserved.                                                 |
+----------------------------------------------------------------------+
|                                                                      |
| Licensed under the Apache License, Version 2.0 (the "License"); you  |
| may not use this file except in compliance with the License. You may |
| obtain a copy of the License at                                      |
| http://www.apache.org/licenses/LICENSE-2.0                           |
|                                                                      |
| Unless required by applicable law or agreed to in writing, software  |
| distributed under the License is distributed on an "AS IS" BASIS,    |
| WITHOUT WARRANTIES OR CONDITIONS OF ANY KIND, either express or      |
| implied. See the License for the specific language governing         |
| permissions and limitations under the License.                       |
+----------------------------------------------------------------------+
| Author: Caroline Maynard                                             |
+----------------------------------------------------------------------+

*/
static char rcs_id[] = "$Id$";

#ifdef PHP_WIN32
#include <iostream>
#include <math.h>
#include "zend_config.w32.h"
#endif

#include "php.h"
#include "zend_exceptions.h"
#include "zend_interfaces.h"

#include "php_sdo_int.h"

#define CLASS_NAME "SDO_List"

/*
 * The three subclasses of SDO_List are each handled here
 */
enum sdo_list_type {
	TYPE_DataObjectList,
	TYPE_ChangedDataObjectList,
	TYPE_SettingList
};

/* {{{ sdo_list_object
 * The instance data for this class - extends the standard zend_object
 */
typedef struct {
	zend_object		 zo;			/* the standard zend_object */
	sdo_list_type	 list_type;		/* discriminator for the list type */
	DataObjectPtr    dop;           /* the containing DataObject */
	union {
		void						*listp;
		DataObjectList				*dolp;
		const ChangedDataObjectList	*cdolp;
		const SettingList			*slp;
	};
} sdo_list_object;
/* }}} */

/* {{{ sdo_list_iterator
 * The iterator data for this class - extends the standard zend_object_iterator
 */
typedef struct {
	zend_object_iterator zoi;		/* The standard zend_object_iterator */
	ulong				 index;		/* current index */
	zval				*value;
} sdo_list_iterator;
/* }}} */

static zend_object_handlers sdo_list_object_handlers;
static zend_object_iterator_funcs sdo_list_iterator_funcs;
/* }}} */

/* {{{ sdo_list_get_instance
 */
static sdo_list_object *sdo_list_get_instance(zval *me TSRMLS_DC)
{
	return (sdo_list_object *)zend_object_store_get_object(me TSRMLS_CC);
}
/* }}} */

/* {{{ debug macro functions
 */
SDO_DEBUG_ADDREF(list)
SDO_DEBUG_DELREF(list)
SDO_DEBUG_DESTROY(list)
/* }}} */

/* {{{ sdo_list_object_free_storage
 */
static void sdo_list_object_free_storage(void *object TSRMLS_DC)
{
	sdo_list_object *my_object;

	SDO_DEBUG_FREE(object);

	my_object = (sdo_list_object *)object;
	zend_hash_destroy(my_object->zo.properties);
	FREE_HASHTABLE(my_object->zo.properties);

	if (my_object->zo.guards) {
	    zend_hash_destroy(my_object->zo.guards);
	    FREE_HASHTABLE(my_object->zo.guards);
	}

	my_object->listp = NULL;
	if (my_object->dop)
		my_object->dop = NULL;
	efree(object);
}
/* }}} */

/* {{{ sdo_list_object_create
 */
static zend_object_value sdo_list_object_create(zend_class_entry *ce TSRMLS_DC)
{
	zend_object_value retval;
	zval *tmp; /* this must be passed to hash_copy, but doesn't seem to be used */
	sdo_list_object *my_object;

	my_object = (sdo_list_object *)emalloc(sizeof(sdo_list_object));
	memset(my_object, 0, sizeof(sdo_list_object));
	my_object->zo.ce = ce;
	my_object->zo.guards = NULL;
	ALLOC_HASHTABLE(my_object->zo.properties);
	zend_hash_init(my_object->zo.properties, 0, NULL, ZVAL_PTR_DTOR, 0);
	zend_hash_copy(my_object->zo.properties, &ce->default_properties, (copy_ctor_func_t)zval_add_ref,
		(void *)&tmp, sizeof(zval *));
	retval.handle = zend_objects_store_put(my_object, SDO_FUNC_DESTROY(list), sdo_list_object_free_storage, NULL TSRMLS_CC);
	retval.handlers = &sdo_list_object_handlers;
	SDO_DEBUG_ALLOCATE(retval.handle, my_object);

	return retval;
}
/* }}} */

/* {{{ sdo_list_new
 * called from the new function for each of the List types
 */
static sdo_list_object *sdo_list_new(zval *me, zend_class_entry *ce TSRMLS_DC)
{
	sdo_list_object *my_object;
	char *class_name, *space;

	Z_TYPE_P(me) = IS_OBJECT;
	if (object_init_ex(me, ce) == FAILURE) {
		class_name = get_active_class_name(&space TSRMLS_CC);
		php_error(E_ERROR, "%s%s%s(): internal error (%i) - failed to instantiate %s object",
			class_name, space, get_active_function_name(TSRMLS_C), __LINE__, CLASS_NAME);
		return (sdo_list_object *)NULL;
	}

	my_object = (sdo_list_object *)zend_object_store_get_object(me TSRMLS_CC);
	return my_object;
}
/* }}} */

/* {{{ sdo_dataobjectlist_new
 */
void sdo_dataobjectlist_new(zval *me, const Type& type, DataObjectPtr dop, DataObjectList *dolp TSRMLS_DC)
{
	sdo_list_object *my_object;

	my_object = sdo_list_new(me, sdo_dataobjectlist_class_entry TSRMLS_CC);

	my_object->list_type = TYPE_DataObjectList;
	/* 
	 * We copy the DataObjectPtr to the php SDO_List object because the DataObjectList 
	 * itself is not reference-counted. So if the containing DataObject is cleared, 
	 * the list data becomes invalid. Saving the reference-counted pointer here
	 * protects it while the list is in use.
	 */
	my_object->dop = dop;
	my_object->dolp = dolp;
}
/* }}} */

/* {{{ sdo_changeddataobjectlist_new
 */
void sdo_changeddataobjectlist_new(zval *me, const ChangedDataObjectList *cdolp TSRMLS_DC)
{
	sdo_list_object *my_object;

	my_object = sdo_list_new(me, sdo_changeddataobjectlist_class_entry TSRMLS_CC);

	my_object->list_type = TYPE_ChangedDataObjectList;
	my_object->cdolp = cdolp;
}
/* }}} */

/* {{{ sdo_settinglist_new
 */
void sdo_das_settinglist_new(zval *me, SettingList& sl TSRMLS_DC)
{
	sdo_list_object *my_object;

	my_object = sdo_list_new(me, sdo_das_settinglist_class_entry TSRMLS_CC);

	my_object->list_type = TYPE_SettingList;
	my_object->slp = &sl;
}
/* }}} */

/* {{{ sdo_dataobjectlist_read_value
 */
static zval *sdo_dataobjectlist_read_value(sdo_list_object *my_object, long index TSRMLS_DC) {
	uint			 bytes_len;
	char			*bytes_value;
	char			 char_value;
	wchar_t			 wchar_value;
	zval			*return_value;
	DataObjectPtr	 doh_value;
	char			*class_name, *space;

	DataObjectList& dol = *my_object->dolp;

	ALLOC_INIT_ZVAL(return_value);
	return_value->refcount = 0;

	try {
		if (index >= dol.size()) {
			RETVAL_NULL();
		} else {
			const Type& type = dol.getType();
			switch(type.getTypeEnum()) {
			case Type::OtherTypes:
				class_name = get_active_class_name(&space TSRMLS_CC);
				php_error(E_ERROR, "%s%s%s(): internal error (%i) - unexpected DataObject type 'OtherTypes'",
					class_name, space, get_active_function_name(TSRMLS_C), __LINE__);
				break;
			case Type::BigDecimalType:
			case Type::BigIntegerType:
				RETVAL_STRING((char *)dol.getCString(index), 1);
				break;
			case Type::BooleanType:
				RETVAL_BOOL(dol.getBoolean(index));
				break;
			case Type::ByteType:
				RETVAL_LONG(dol.getByte(index));
				break;
			case Type::BytesType:
				bytes_len = dol.getLength(index);
				bytes_value = (char *)emalloc(1 + bytes_len);
				bytes_len = dol.getBytes(index, bytes_value, bytes_len);
				bytes_value[bytes_len] = '\0';
				RETVAL_STRINGL(bytes_value, bytes_len, 0);
				break;
			case Type::CharacterType:
				wchar_value = dol.getCharacter(index);
				if (wchar_value > INT_MAX) {
					class_name = get_active_class_name(&space TSRMLS_CC);
					php_error(E_WARNING, "%s%s%s(): wide character data lost reading many-valued property",
						class_name, space, get_active_function_name(TSRMLS_C));
				}
				char_value = dol.getByte(index);
				RETVAL_STRINGL(&char_value, 1, 1);
				break;
			case Type::DateType:
				RETVAL_LONG(dol.getDate(index).getTime());
				break;
			case Type::DoubleType:
				RETVAL_DOUBLE(dol.getDouble(index));
				break;
			case Type::FloatType:
				RETVAL_DOUBLE(dol.getFloat(index));
				break;
			case Type::IntegerType:
				RETVAL_LONG(dol.getInteger(index));
				break;
			case Type::LongType:
				/* An SDO long (64 bits) may overflow a PHP int, so we return it as a string */
				RETVAL_STRING((char *)dol.getCString(index), 1);
				break;
			case Type::ShortType:
				RETVAL_LONG(dol.getShort(index));
				break;
			case Type::StringType:
			case Type::UriType:
				RETVAL_STRING((char *)dol.getCString(index), 1);
				break;
			case Type::DataObjectType:
			case Type::OpenDataObjectType:
				doh_value = dol[index];
				/*find PHP object from C++ object */
				if (!doh_value) {
					RETVAL_NULL();
				} else {
					sdo_do_new(return_value, doh_value TSRMLS_CC);
				}
				break;
			case Type::ChangeSummaryType:
				class_name = get_active_class_name(&space TSRMLS_CC);
				php_error(E_ERROR, "%s%s%s(): internal error (%i) - unexpected DataObject type 'ChangeSummaryType'",
					class_name, space, get_active_function_name(TSRMLS_C), __LINE__);
				break;
			case Type::TextType:
				class_name = get_active_class_name(&space TSRMLS_CC);
				php_error(E_ERROR, "%s%s%s(): internal error (%i) - unexpected DataObject type 'TextType'",
					class_name, space, get_active_function_name(TSRMLS_C), __LINE__);
				break;
			default:
				class_name = get_active_class_name(&space TSRMLS_CC);
				php_error(E_ERROR, "%s%s%s(): internal error (%i) - unexpected DataObject type %i",
					class_name, space, get_active_function_name(TSRMLS_C), __LINE__, type.getTypeEnum());
			}

		}
	} catch (SDORuntimeException e) {
		sdo_throw_runtimeexception (&e TSRMLS_CC);
		efree(return_value);
		return_value = EG(uninitialized_zval_ptr);
	}
	return return_value;
}
/* }}} */

/* {{{ sdo_changeddataobjectlist_read_value
 */
static zval *sdo_changeddataobjectlist_read_value(sdo_list_object *my_object, long index TSRMLS_DC) {

	const ChangedDataObjectList& dol = *my_object->cdolp;
	zval *return_value;

	MAKE_STD_ZVAL(return_value);
	return_value->refcount = 0;

	/* Elements of a ChangedDataObjectList are all DataObject type */
	try {
		if (index >= dol.size()) {
			RETVAL_NULL();
		} else {
			DataObjectPtr doh_value = dol[index];
			/*find PHP object from C++ object */
			if (!doh_value) {
				RETVAL_NULL();
			} else {
				sdo_do_new(return_value, doh_value TSRMLS_CC);
			}
		}
	} catch (SDORuntimeException e) {
		sdo_throw_runtimeexception (&e TSRMLS_CC);
		efree(return_value);
		return_value = EG(uninitialized_zval_ptr);
	}
	return return_value;
}
/* }}} */

/* {{{ sdo_das_settinglist_read_value
 */
static zval *sdo_das_settinglist_read_value(sdo_list_object *my_object, long index TSRMLS_DC) {
	zval *return_value;
	const SettingList& sl = *my_object->slp;

	MAKE_STD_ZVAL(return_value);
	return_value->refcount = 0;

	/* Elements of a SettingList are all DAS_Setting type */
	try {
		if (index >= sl.size()) {
			RETVAL_NULL();
		} else {
			sdo_das_setting_new(return_value, &sl[index] TSRMLS_CC);
		}
	} catch (SDORuntimeException e) {
		sdo_throw_runtimeexception (&e TSRMLS_CC);
		efree(return_value);
		return_value = EG(uninitialized_zval_ptr);
	}
	return return_value;
}
/* }}} */

/* {{{ sdo_dataobjectlist_write_value
 */
static void sdo_dataobjectlist_write_value(sdo_list_object *my_object, long index, zval *z_value, sdo_write_type write_type TSRMLS_DC)
{
	zval temp_zval;
	char *class_name, *space;

	ZVAL_NULL(&temp_zval);
	DataObjectList& dol = *my_object->dolp;

	try {
		if (write_type != TYPE_APPEND &&
			(index < 0 || index >= dol.size())) {
			zend_throw_exception_ex(sdo_indexoutofboundsexception_class_entry, 0 TSRMLS_CC,
				"index %i out of range [0..%i]", index, dol.size() - 1);
			return;
		}

		/*
		* Since we may have to coerce the type, we make a local copy of the zval, so that the
		 * original is unaffected.
		 *
		 * TODO This could be optimized to only copy if we do actually change the type.
		 */
		temp_zval = *z_value;
		zval_copy_ctor(&temp_zval);

        /* we must have at least the type enum here in case this is an append and
		 * we don't know what type to append
		 */
		const Type& type = dol.getType();
		switch(type.getTypeEnum()) {
		case Type::OtherTypes:
			class_name = get_active_class_name(&space TSRMLS_CC);
			php_error(E_ERROR, "%s%s%s(): internal error (%i) - unexpected DataObject type 'OtherTypes'",
				class_name, space, get_active_function_name(TSRMLS_C), __LINE__);
			break;
		case Type::BigDecimalType:
		case Type::BigIntegerType:
			convert_to_string(&temp_zval);
			if (write_type == TYPE_APPEND)
				dol.append(Z_STRVAL(temp_zval));
			else if (write_type == TYPE_INSERT)
				dol.insert(index, Z_STRVAL(temp_zval));
			else
				dol.setCString(index, Z_STRVAL(temp_zval));
			break;
		case Type::BooleanType:
			convert_to_boolean(&temp_zval);
			if (write_type == TYPE_APPEND)
				dol.append((bool)ZEND_TRUTH(Z_BVAL(temp_zval)));
			else if (write_type == TYPE_INSERT)
				dol.insert(index, (bool)ZEND_TRUTH(Z_BVAL(temp_zval)));
			else
				dol.setBoolean(index, ZEND_TRUTH(Z_BVAL(temp_zval)));
			break;
		case Type::ByteType:
			convert_to_long(&temp_zval);
			if (write_type == TYPE_APPEND)
				dol.append((char)Z_LVAL(temp_zval));
			else if (write_type == TYPE_INSERT)
				dol.insert(index, (char)Z_LVAL(temp_zval));
			else
				dol.setByte(index, Z_LVAL(temp_zval));
			break;
		case Type::BytesType:
			convert_to_string(&temp_zval);
			if (write_type == TYPE_APPEND)
				dol.append((char *)Z_STRVAL(temp_zval), Z_STRLEN(temp_zval));
			else if (write_type == TYPE_INSERT)
				dol.insert(index, (char *)Z_STRVAL(temp_zval), Z_STRLEN(temp_zval));
			else
				dol.setBytes(index, Z_STRVAL(temp_zval), Z_STRLEN(temp_zval));
			break;
		case Type::CharacterType:
			convert_to_string(&temp_zval);
			if (write_type == TYPE_APPEND)
				dol.append((char)Z_STRVAL(temp_zval)[0]);
			else if (write_type == TYPE_INSERT)
				dol.insert(index, (char)Z_STRVAL(temp_zval)[0]);
			else
				dol.setCharacter(index, (char)(Z_STRVAL(temp_zval)[0]));
			break;
		case Type::DateType:
			convert_to_long(&temp_zval);
			if (write_type == TYPE_APPEND)
				dol.append((SDODate)Z_LVAL(temp_zval));
			else if (write_type == TYPE_INSERT)
				dol.insert(index, (SDODate)Z_LVAL(temp_zval));
			else
				dol.setDate(index, (SDODate)Z_LVAL(temp_zval));
			break;
		case Type::DoubleType:
			convert_to_double(&temp_zval);
			if (write_type == TYPE_APPEND)
				dol.append((long double)Z_DVAL(temp_zval));
			else if (write_type == TYPE_INSERT)
				dol.insert(index, (long double)Z_DVAL(temp_zval));
			else
				dol.setDouble(index, (long double)Z_DVAL(temp_zval));
			break;
		case Type::FloatType:
			convert_to_double(&temp_zval);
			if (write_type == TYPE_APPEND)
				dol.append((float)Z_DVAL(temp_zval));
			else if (write_type == TYPE_INSERT)
				dol.insert(index, (float)Z_DVAL(temp_zval));
			else
				dol.setFloat(index, (float)Z_DVAL(temp_zval));
			break;
		case Type::IntegerType:
			convert_to_long(&temp_zval);
			if (write_type == TYPE_APPEND)
				dol.append((long)Z_LVAL(temp_zval));
			else if (write_type == TYPE_INSERT)
				dol.insert(index, (long)Z_LVAL(temp_zval));
			else
				dol.setInteger(index, (long)Z_LVAL(temp_zval));
			break;
		case Type::LongType:
			if (Z_TYPE(temp_zval) == IS_LONG) {
				if (write_type == TYPE_APPEND)
					dol.append(Z_LVAL(temp_zval));
				else if (write_type == TYPE_INSERT)
					dol.insert(index, Z_LVAL(temp_zval));
				else
					dol.setLong(index, Z_LVAL(temp_zval));
			} else {
				convert_to_string(&temp_zval);
				if (write_type == TYPE_APPEND)
					dol.append((const char *)Z_STRVAL(temp_zval));
				else if (write_type == TYPE_INSERT)
					dol.insert(index, (const char *)Z_STRVAL(temp_zval));
				else
					dol.setCString(index, Z_STRVAL(temp_zval));
			}
			break;
		case Type::ShortType:
			convert_to_long(&temp_zval);
			if (write_type == TYPE_APPEND)
				dol.append((short)Z_LVAL(temp_zval));
			else if (write_type == TYPE_INSERT)
				dol.insert(index, (short)Z_LVAL(temp_zval));
			else
				dol.setShort(index, (short)Z_LVAL(temp_zval));
			break;
		case Type::StringType:
		case Type::UriType:
			convert_to_string(&temp_zval);
			if (write_type == TYPE_APPEND)
				dol.append(Z_STRVAL(temp_zval));
			else if (write_type == TYPE_INSERT)
				dol.insert(index, Z_STRVAL(temp_zval));
			else
				dol.setCString(index, Z_STRVAL(temp_zval));
			break;
		case Type::DataObjectType:
		case Type::OpenDataObjectType:
			convert_to_object(&temp_zval);
			if (!instanceof_function(Z_OBJCE(temp_zval), sdo_dataobjectimpl_class_entry TSRMLS_CC)) {
				zend_throw_exception_ex(sdo_unsupportedoperationexception_class_entry, 0 TSRMLS_CC,
				"Class %s is not an instance of %s",
					Z_OBJCE(temp_zval)->name, sdo_dataobjectimpl_class_entry->name);
			} else {
				DataObjectPtr dop = sdo_do_get(&temp_zval TSRMLS_CC);
				if (write_type == TYPE_APPEND) {
					dol.append(dop);
				} else if (write_type == TYPE_INSERT) {
					dol.insert(index, dop);
				} else {
					dol.setDataObject(index, dop);
				}
			}
			break;
		case Type::ChangeSummaryType:
			class_name = get_active_class_name(&space TSRMLS_CC);
			php_error(E_ERROR, "%s%s%s(): internal error (%i) - unexpected DataObject type 'ChangeSummaryType'",
				class_name, space, get_active_function_name(TSRMLS_C), __LINE__);
			break;
		case Type::TextType:
			class_name = get_active_class_name(&space TSRMLS_CC);
			php_error(E_ERROR, "%s%s%s(): internal error (%i) - unexpected DataObject type 'TextType'",
				class_name, space, get_active_function_name(TSRMLS_C), __LINE__);
			break;
		default:
			class_name = get_active_class_name(&space TSRMLS_CC);
			php_error(E_ERROR, "%s%s%s(): internal error (%i) - unexpected DataObject type '%s'",
				class_name, space, get_active_function_name(TSRMLS_C), __LINE__, type.getName());
			}
	} catch (SDORuntimeException e) {
		sdo_throw_runtimeexception(&e TSRMLS_CC);
	}
	zval_dtor(&temp_zval);
}
/* }}} */

/* {{{ sdo_dataobjectlist_valid
 */
static int sdo_dataobjectlist_valid(sdo_list_object *my_object, long index, int check_empty TSRMLS_DC)
{
	int	return_value = 0;
	char *class_name, *space;

	try {
		return_value = (index >= 0 && index < my_object->dolp->size());

		if (return_value && check_empty) {
			/* cf SDO_DataObject.read_dimension() */
			DataObjectList& dol = *my_object->dolp;
			const Type& type = dol.getType();
			switch (type.getTypeEnum()) {
			case Type::OtherTypes:
				class_name = get_active_class_name(&space TSRMLS_CC);
				php_error(E_ERROR, "%s%s%s(): internal error (%i) - unexpected DataObject type 'OtherTypes'",
					class_name, space, get_active_function_name(TSRMLS_C), __LINE__);
				return_value = 0;
				break;
			case Type::BigDecimalType:
			case Type::BigIntegerType:
			case Type::BooleanType:
			case Type::ByteType:
				return_value = dol.getBoolean(index);
				break;
			case Type::BytesType:
				return_value = (dol.getLength(index) != 0);
				break;
			case Type::CharacterType:
				return_value = dol.getBoolean(index);
				break;
			case Type::DateType:
				return_value = (dol.getDate(index).getTime() != 0);
				break;
			case Type::DoubleType:
			case Type::FloatType:
			case Type::IntegerType:
			case Type::LongType:
			case Type::ShortType:
				return_value = dol.getBoolean(index);
				break;
			case Type::StringType:
			case Type::UriType:
				return_value = (dol.getLength(index) > 0);
				break;
			case Type::DataObjectType:
		    case Type::OpenDataObjectType:
				return_value = (!(dol[index]));
				break;
			case Type::ChangeSummaryType:
				class_name = get_active_class_name(&space TSRMLS_CC);
				php_error(E_ERROR, "%s%s%s(): internal error (%i) - unexpected DataObject type 'ChangeSummaryType'",
					class_name, space, get_active_function_name(TSRMLS_C), __LINE__);
				return_value = 0;
				break;
			case Type::TextType:
				class_name = get_active_class_name(&space TSRMLS_CC);
				php_error(E_ERROR, "%s%s%s(): internal error (%i) - unexpected DataObject type 'TextType'",
					class_name, space, get_active_function_name(TSRMLS_C), __LINE__);
				return_value = 0;
				break;
			default:
				class_name = get_active_class_name(&space TSRMLS_CC);
				php_error(E_ERROR, "%s%s%s(): internal error (%i) - unexpected DataObject type %i",
					class_name, space, get_active_function_name(TSRMLS_C), __LINE__, type.getTypeEnum());
				return_value = 0;
			}
		}
	} catch (SDORuntimeException e) {
		/* no exception, just true or false */
		return_value = 0;
	}

	return return_value;
}
/* }}} */

/* {{{ sdo_changeddataobjectlist_valid
 */
static int sdo_changeddataobjectlist_valid(sdo_list_object *my_object, long index, int check_empty TSRMLS_DC)
{
	int return_value = 0;

	try {
		return_value = (index >= 0 && index < my_object->cdolp->size());

		if (return_value && check_empty) {
			/* cf SDO_DataObject.read_dimension() */
			return_value = (&my_object->cdolp[index] != NULL);
		}
	} catch (SDORuntimeException e) {
		return_value = 0;
	}

	return return_value;
}
/* }}} */

/* {{{ sdo_das_settinglist_valid
 */
static int sdo_das_settinglist_valid(sdo_list_object *my_object, long index, int check_empty TSRMLS_DC)
{
	int return_value = 0;

	try {
		return_value = (index >= 0 && index < my_object->slp->size());

		if (return_value && check_empty) {
			/* cf SDO_DataObject.read_dimension() */
			return_value = (&my_object->slp[index] != NULL);
		}
	} catch (SDORuntimeException e) {
		return_value = 0;
	}

	return return_value;
}
/* }}} */

/* {{{ sdo_list_has_dimension
 */
static int sdo_list_has_dimension(zval *object, zval *offset, int check_empty TSRMLS_DC)
{
	char			 *propertyName = NULL;
	long			  index;
	int				  return_value = 0;
	sdo_list_object  *my_object;
	char			 *class_name, *space;

	if (Z_TYPE_P(offset) != IS_LONG) {
		class_name = get_active_class_name(&space TSRMLS_CC);
		php_error(E_ERROR, "%s%s%s(): internal error (%i) - invalid dimension type %i",
			class_name, space, get_active_function_name(TSRMLS_C), __LINE__, Z_TYPE_P(offset));
		return 0;
	}

	index = Z_LVAL_P(offset);

	my_object = sdo_list_get_instance(object TSRMLS_CC);

	switch (my_object->list_type) {
	case TYPE_DataObjectList:
		return_value = sdo_dataobjectlist_valid(my_object, index, check_empty TSRMLS_CC);
		break;
	case TYPE_ChangedDataObjectList:
		return_value = sdo_changeddataobjectlist_valid(my_object, index, check_empty TSRMLS_CC);
		break;
	case TYPE_SettingList:
		return_value = sdo_das_settinglist_valid(my_object, index, check_empty TSRMLS_CC);
		break;
	default:
		class_name = get_active_class_name(&space TSRMLS_CC);
		php_error(E_ERROR, "%s%s%s(): internal error (%i) - invalid dimension type %i",
			class_name, space, get_active_function_name(TSRMLS_C), __LINE__, my_object->list_type);
	}

	return return_value;
}
/* }}} */

/* {{{ sdo_list_read_dimension
 */
static zval *sdo_list_read_dimension(zval *object, zval *offset, int type TSRMLS_DC)
{
	long			  index;
	sdo_list_object	 *my_object;
	zval			 *return_value;
	char			 *class_name, *space;

	if (Z_TYPE_P(offset) != IS_LONG) {
		class_name = get_active_class_name(&space TSRMLS_CC);
		php_error(E_ERROR, "%s%s%s(): internal error (%i) - invalid dimension type %i",
			class_name, space, get_active_function_name(TSRMLS_C), __LINE__, Z_TYPE_P(offset));
		return EG(uninitialized_zval_ptr);
	}

	index = Z_LVAL_P(offset);
	my_object = sdo_list_get_instance(object TSRMLS_CC);

	switch (my_object->list_type) {
	case TYPE_DataObjectList:
		return_value = sdo_dataobjectlist_read_value(my_object, index TSRMLS_CC);
		break;
	case TYPE_ChangedDataObjectList:
		return_value = sdo_changeddataobjectlist_read_value(my_object, index TSRMLS_CC);
		break;
	case TYPE_SettingList:
		return_value = sdo_das_settinglist_read_value(my_object, index TSRMLS_CC);
		break;
	default:
		class_name = get_active_class_name(&space TSRMLS_CC);
		php_error(E_ERROR, "%s%s%s(): internal error (%i) - invalid dimension type %i",
			class_name, space, get_active_function_name(TSRMLS_C), __LINE__, my_object->list_type);
		return_value = EG(uninitialized_zval_ptr);
	}

	return return_value;
}
/* }}} */

/* {{{ sdo_list_unset_dimension
 */
static void sdo_list_unset_dimension(zval *object, zval *offset TSRMLS_DC)
{
	long			  index;
	sdo_list_object	 *my_object;
	DataObjectList   *dol;
	char			 *class_name, *space;

	if (Z_TYPE_P(offset) != IS_LONG) {
		class_name = get_active_class_name(&space TSRMLS_CC);
		php_error(E_ERROR, "%s%s%s(): internal error (%i) - invalid dimension type %i",
			class_name, space, get_active_function_name(TSRMLS_C), __LINE__, Z_TYPE_P(offset));
		return;
	}
	index = Z_LVAL_P(offset);

	my_object = sdo_list_get_instance(object TSRMLS_CC);

	switch(my_object->list_type) {
	case TYPE_DataObjectList:
		dol = my_object->dolp;
		try {
			if (index < 0 || index >= dol->size()) {
				zend_throw_exception_ex(sdo_indexoutofboundsexception_class_entry, 0 TSRMLS_CC,
					"index %i out of range [0..%i]", index, dol->size() - 1);
			} else {
				dol->remove(index);
			}
		} catch (SDORuntimeException e) {
			sdo_throw_runtimeexception(&e TSRMLS_CC);
		}
		break;
	case TYPE_ChangedDataObjectList:
		zend_throw_exception_ex(sdo_unsupportedoperationexception_class_entry, 0 TSRMLS_CC,
			"A ChangedDataObjectList is read-only");
		break;
	case TYPE_SettingList:
		zend_throw_exception_ex(sdo_unsupportedoperationexception_class_entry, 0 TSRMLS_CC,
			"A SettingList is read-only");
		break;
	default:
		class_name = get_active_class_name(&space TSRMLS_CC);
		php_error(E_ERROR, "%s%s%s(): internal error (%i) - invalid dimension type %i",
			class_name, space, get_active_function_name(TSRMLS_C), __LINE__, my_object->list_type);
	}
}
/* }}} */

/* {{{ sdo_list_write_dimension
 */
static void sdo_list_write_dimension(zval *object, zval *offset, zval *z_value TSRMLS_DC)
{
	long			  index = -1;
	sdo_list_object	 *my_object;
	sdo_write_type	  write_type;
	char			 *class_name, *space;

	if (Z_TYPE_P(z_value) == IS_NULL) {
		/* TODO: fix this when the C++ lib supports a NULL property  */
		zend_throw_exception(sdo_invalidconversionexception_class_entry,
			"can't assign NULL to SDO_List value", 0 TSRMLS_CC);
		return;
	}

	if (offset == 0 || Z_TYPE_P(offset) == IS_NULL) {
		write_type = TYPE_APPEND;
	} else {
		if (Z_TYPE_P(offset) != IS_LONG) {
			class_name = get_active_class_name(&space TSRMLS_CC);
			php_error(E_ERROR, "%s%s%s(): internal error (%i) - invalid dimension type %i",
				class_name, space, get_active_function_name(TSRMLS_C), __LINE__, Z_TYPE_P(offset));
			return;
		}
		index = Z_LVAL_P(offset);
		write_type = TYPE_OVERWRITE;
	}

	my_object = sdo_list_get_instance(object TSRMLS_CC);

	switch (my_object->list_type) {
	case TYPE_DataObjectList:
		sdo_dataobjectlist_write_value(my_object, index, z_value, write_type TSRMLS_CC);
		break;
	case TYPE_ChangedDataObjectList:
		zend_throw_exception_ex(sdo_unsupportedoperationexception_class_entry, 0 TSRMLS_CC,
			"A ChangedDataObjectList is read-only");
		break;
	case TYPE_SettingList:
		zend_throw_exception_ex(sdo_unsupportedoperationexception_class_entry, 0 TSRMLS_CC,
			"A SettingList is read-only");
		break;
	default:
		class_name = get_active_class_name(&space TSRMLS_CC);
		php_error(E_ERROR, "%s%s%s(): internal error (%i) - invalid dimension type %i",
			class_name, space, get_active_function_name(TSRMLS_C), __LINE__, my_object->list_type);
	}

}
/* }}} */

/* {{{ sdo_list_get_properties
 * called as a result of print_r() or vardump(), but doesn't get called for reflection
 * Returns an indexed array of the values of all the list elements
 */
static HashTable *sdo_list_get_properties(zval *object TSRMLS_DC)
{
	sdo_list_object	*my_object;
	int				 entries;
	zval			*tmp;
	char			*space, *class_name;

	my_object = sdo_list_get_instance(object TSRMLS_CC);
	zend_hash_clean(my_object->zo.properties);

	try {
		switch (my_object->list_type) {
		case TYPE_ChangedDataObjectList:
			entries = my_object->cdolp->size();
			break;
		case TYPE_DataObjectList:
			entries = my_object->dolp->size();
			break;
		case TYPE_SettingList:
			entries = my_object->slp->size();
			break;
		default:
			class_name = get_active_class_name(&space TSRMLS_CC);
			php_error(E_ERROR, "%s%s%s(): internal error (%i) - invalid dimension type %i",
				class_name, space, get_active_function_name(TSRMLS_C), __LINE__, my_object->list_type);
			return 0;
		}

		for (long index = 0; index < entries; index++) {

			switch (my_object->list_type) {
			case TYPE_DataObjectList:
				tmp = sdo_dataobjectlist_read_value(my_object, index TSRMLS_CC);
				break;
			case TYPE_ChangedDataObjectList:
				tmp = sdo_changeddataobjectlist_read_value(my_object, index TSRMLS_CC);
				break;
			case TYPE_SettingList:
				tmp = sdo_das_settinglist_read_value(my_object, index TSRMLS_CC);
				break;
			}

			zval_add_ref(&tmp);
			zend_hash_next_index_insert(my_object->zo.properties,
				&tmp, sizeof(zval *), NULL);
		}
	} catch (SDORuntimeException e) {
		sdo_throw_runtimeexception(&e TSRMLS_CC);
	}

	return my_object->zo.properties;
}
/* }}} */

/* {{{ sdo_list_cast_object
*/#if PHP_MAJOR_VERSION > 5 || (PHP_MAJOR_VERSION == 5 && PHP_MINOR_VERSION > 1)
static int sdo_list_cast_object(zval *readobj, zval *writeobj, int type TSRMLS_DC)
{
	int should_free = 0;
#else
static int sdo_list_cast_object(zval *readobj, zval *writeobj, int type, int should_free TSRMLS_DC)
{
#endif
	sdo_list_object	*my_object;
	ostringstream	 print_buf;
	zval			 free_obj;
	int				 rc = SUCCESS;
	int				 entries;
	char		    *class_name, *space;

	if (should_free) {
		free_obj = *writeobj;
	}

	my_object = sdo_list_get_instance(readobj TSRMLS_CC);

	if (my_object->list_type > TYPE_SettingList) {
		class_name = get_active_class_name(&space TSRMLS_CC);
		php_error(E_ERROR, "%s%s%s(): internal error (%i) - invalid dimension type %i",
			class_name, space, get_active_function_name(TSRMLS_C), __LINE__, my_object->list_type);
		ZVAL_NULL(writeobj);
		rc = FAILURE;
	} else {
		print_buf << "object(";
		try {
			switch (my_object->list_type) {
			case TYPE_ChangedDataObjectList:
				print_buf << "SDO_ChangedDataObjectList";
				entries = my_object->cdolp->size();
				break;
			case TYPE_DataObjectList:
				print_buf << "SDO_DataObjectList";
				entries = my_object->dolp->size();
				break;
			case TYPE_SettingList:
				print_buf << "SDO_SettingList";
				entries = my_object->slp->size();
				break;
			}

			print_buf << ")#" << readobj->value.obj.handle << " (" << entries << ')';

			switch (my_object->list_type) {
			case TYPE_ChangedDataObjectList:
				/* Any useful information would require navigation to the related Setting */
				break;
			case TYPE_DataObjectList:
				{
					DataObjectList& dol = *my_object->dolp;
					if (dol.getType().isDataType()) {
						print_buf << " {";
						for (int i = 0; i < entries; i++) {
							if (i > 0) print_buf << "; ";
							print_buf << '[' << i << "]=>\"" << dol.getCString(i) << '\"';
						}
						print_buf << '}';
					}
				}
				break;
			case TYPE_SettingList:
				{
					print_buf << '{';
					const SettingList &sl = *my_object->slp;
					for (int i = 0; i < entries; i++) {
						if (i > 0) print_buf << "; ";
						const Property& property = sl[i].getProperty();
						print_buf << property.getName();
						if (property.isMany()) {
							print_buf << '[' << sl[i].getIndex() << ']';
						}
					}
					print_buf << '}';
				}
				break;
			}

			std::string print_string = print_buf.str()/*.substr(0, SDO_TOSTRING_MAX)*/;
			ZVAL_STRINGL(writeobj, (char *)print_string.c_str(), print_string.length(), 1);

		} catch (SDORuntimeException e) {
			ZVAL_NULL(writeobj);
			sdo_throw_runtimeexception(&e TSRMLS_CC);
			rc = FAILURE;
		}
	}

	switch(type) {
	case IS_STRING:
		convert_to_string(writeobj);
		break;
	case IS_BOOL:
		convert_to_boolean(writeobj);
		break;
	case IS_LONG:
		convert_to_long(writeobj);
		break;
	case IS_DOUBLE:
		convert_to_double(writeobj);
		break;
	default:
		rc = FAILURE;
	}

	if (should_free) {
		zval_dtor(&free_obj);
	}
	return rc;
}
/* }}} */

/* {{{ sdo_list_compare_objects
 * gets called as a consequence of an == comparison
 * we do a deep compare of the values of the two lists
 */
static int sdo_list_compare_objects(zval *object1, zval *object2 TSRMLS_DC)
{
	sdo_list_object	*my_object1, *my_object2;
	DataObjectList	*dol1, *dol2;
	int				 entries;
	zval			 result;
	char			*class_name, *space;

	INIT_PZVAL(&result);

	my_object1 = sdo_list_get_instance(object1 TSRMLS_CC);
	my_object2 = sdo_list_get_instance(object2 TSRMLS_CC);

	switch (my_object1->list_type) {
	case TYPE_ChangedDataObjectList:
	case TYPE_SettingList:
		return FAILURE;
		break;
	case TYPE_DataObjectList:
		break;
	default:
		class_name = get_active_class_name(&space TSRMLS_CC);
		php_error(E_ERROR, "%s%s%s(): internal error (%i) - invalid dimension type %i",
			class_name, space, get_active_function_name(TSRMLS_C), __LINE__, my_object1->list_type);
		return FAILURE;
	}

	dol1 = my_object1->dolp;
	dol2 = my_object2->dolp;

	if (dol1 == dol2)
		return SUCCESS;

	try {
		entries = dol1->size();
		if (entries != dol2->size())
			return FAILURE;
		for (int i = 0; i < entries; i++) {
			zval *value1 = sdo_dataobjectlist_read_value(my_object1, i TSRMLS_CC);
			zval *value2 = sdo_dataobjectlist_read_value(my_object2, i TSRMLS_CC);
			int rc = compare_function(&result, value1, value2 TSRMLS_CC);
			zval_ptr_dtor(&value1);
			zval_ptr_dtor(&value2);
			if (rc || Z_LVAL(result))
				return FAILURE;
		}
		return SUCCESS;
	} catch (SDORuntimeException e) {
		/* In this case we won't rethrow the exception - suffice it to say that the objects are not equal */
		return FAILURE;
	}
}
/* }}} */

/* {{{ sdo_list_get_iterator
 */
#if PHP_MAJOR_VERSION > 5 || (PHP_MAJOR_VERSION == 5 && PHP_MINOR_VERSION > 1)
zend_object_iterator *sdo_list_get_iterator(zend_class_entry *ce, zval *object, int by_ref TSRMLS_DC)
{	
	char *class_name, *space;
	class_name = get_active_class_name(&space TSRMLS_CC);

	if (by_ref) {	
		php_error(E_ERROR, "%s%s%s(): an iterator cannot be used with foreach by reference",
		class_name, space, get_active_function_name(TSRMLS_C));
	}

#else
zend_object_iterator *sdo_list_get_iterator(zend_class_entry *ce, zval *object TSRMLS_DC)
{
#endif

	sdo_list_iterator *iterator = (sdo_list_iterator *)emalloc(sizeof(sdo_list_iterator));
	object->refcount++;
	iterator->zoi.data = (void *)object;
	iterator->zoi.funcs = &sdo_list_iterator_funcs;
	iterator->index = 0;

	return (zend_object_iterator *)iterator;
}
/* }}} */

/* {{{ sdo_list_iterator_dtor
 */
static void sdo_list_iterator_dtor(zend_object_iterator *iter TSRMLS_DC)
{
	sdo_list_iterator *iterator = (sdo_list_iterator *)iter;

    if (iterator->zoi.data) { 
		zval_ptr_dtor((zval **)&iterator->zoi.data);
    }

	efree(iterator);
}
/* }}} */

/* {{{ sdo_list_iterator_valid
 */
static int sdo_list_iterator_valid (zend_object_iterator *iter TSRMLS_DC)
{
	int valid;
	long count;
	char *class_name, *space;

	sdo_list_iterator *iterator = (sdo_list_iterator *)iter;
	zval *z_list_object = (zval *)iterator->zoi.data;
	sdo_list_object *my_object = (sdo_list_object *)sdo_list_get_instance(z_list_object TSRMLS_CC);

	try {
		switch (my_object->list_type) {
		case TYPE_ChangedDataObjectList:
			count = my_object->cdolp->size();
			break;
		case TYPE_DataObjectList:
			count = my_object->dolp->size();
			break;
		case TYPE_SettingList:
			count = my_object->slp->size();
			break;
		default:
			class_name = get_active_class_name(&space TSRMLS_CC);
			php_error(E_ERROR, "%s%s%s(): internal error (%i) - invalid dimension type %i",
				class_name, space, get_active_function_name(TSRMLS_C), __LINE__, my_object->list_type);
		}
		valid = (iterator->index >= 0 && iterator->index < count) ? SUCCESS : FAILURE;
	} catch (SDORuntimeException e) {
		sdo_throw_runtimeexception(&e TSRMLS_CC);
	}

	return valid;
}
/* }}} */

/* {{{ sdo_list_iterator_current_key
 */
static int sdo_list_iterator_current_key (zend_object_iterator *iter,
		char **str_key, uint *str_key_len, ulong *int_key TSRMLS_DC)
{
	sdo_list_iterator *iterator = (sdo_list_iterator *)iter;
	*int_key = iterator->index;

	return HASH_KEY_IS_LONG;
}
/* }}} */

/* {{{ sdo_list_iterator_current_data
 */
static void sdo_list_iterator_current_data (zend_object_iterator *iter, zval ***data TSRMLS_DC)
{
	char *class_name, *space;

	sdo_list_iterator *iterator = (sdo_list_iterator *)iter;
	zval *z_list_object = (zval *)iterator->zoi.data;
	sdo_list_object *my_object = (sdo_list_object *)sdo_list_get_instance(z_list_object TSRMLS_CC);

	try {
		switch(my_object->list_type) {
		case TYPE_DataObjectList:
			iterator->value = sdo_dataobjectlist_read_value(my_object, iterator->index TSRMLS_CC);
			break;
		case TYPE_ChangedDataObjectList:
			iterator->value = sdo_changeddataobjectlist_read_value(my_object, iterator->index TSRMLS_CC);
			break;
		case TYPE_SettingList:
			iterator->value = sdo_das_settinglist_read_value(my_object, iterator->index TSRMLS_CC);
			break;
		default:
		class_name = get_active_class_name(&space TSRMLS_CC);
		php_error(E_ERROR, "%s%s%s(): internal error (%i) - invalid dimension type %i",
			class_name, space, get_active_function_name(TSRMLS_C), __LINE__, my_object->list_type);
			return;
		}
	} catch (SDORuntimeException e) {
		sdo_throw_runtimeexception(&e TSRMLS_CC);
	}

	*data = &iterator->value;
}
/* }}} */

/* {{{ sdo_list_iterator_move_forward
 */
static void sdo_list_iterator_move_forward (zend_object_iterator *iter TSRMLS_DC)
{
	sdo_list_iterator *iterator = (sdo_list_iterator *)iter;
	iterator->index++;
}
/* }}} */

/* {{{ sdo_list_iterator_rewind
 */
static void sdo_list_iterator_rewind (zend_object_iterator *iter TSRMLS_DC)
{
	sdo_list_iterator *iterator = (sdo_list_iterator *)iter;
	iterator->index = 0;
}
/* }}} */

/* {{{ sdo_list_count_elements
 */
int sdo_list_count_elements(zval *object, long *count TSRMLS_DC)
{
	sdo_list_object *my_object;
	char *class_name, *space;

	my_object = sdo_list_get_instance(object TSRMLS_CC);

	try {
		switch (my_object->list_type) {
		case TYPE_ChangedDataObjectList:
			*count = my_object->cdolp->size();
			break;
		case TYPE_DataObjectList:
			*count = my_object->dolp->size();
			break;
		case TYPE_SettingList:
			*count = my_object->slp->size();
			break;
		default:
			class_name = get_active_class_name(&space TSRMLS_CC);
			php_error(E_ERROR, "%s%s%s(): internal error (%i) - invalid dimension type %i",
				class_name, space, get_active_function_name(TSRMLS_C), __LINE__, my_object->list_type);
		}
	} catch (SDORuntimeException e) {
		sdo_throw_runtimeexception(&e TSRMLS_CC);
		return FAILURE;
	}

	return SUCCESS;
}
/* }}} */

/* {{{ sdo_list_minit
 */
void sdo_list_minit(zend_class_entry *tmp_ce TSRMLS_DC)
{
	tmp_ce->create_object = sdo_list_object_create;
	sdo_list_class_entry = zend_register_internal_class(tmp_ce TSRMLS_CC);
	sdo_list_class_entry->get_iterator = sdo_list_get_iterator;
	sdo_list_class_entry->ce_flags |= ZEND_ACC_EXPLICIT_ABSTRACT_CLASS;
	zend_class_implements(sdo_list_class_entry TSRMLS_CC, 1, zend_ce_traversable);

	memcpy(&sdo_list_object_handlers, zend_get_std_object_handlers(), sizeof(zend_object_handlers));
	sdo_list_object_handlers.add_ref = SDO_FUNC_ADDREF(list);
	sdo_list_object_handlers.del_ref = SDO_FUNC_DELREF(list);
	sdo_list_object_handlers.clone_obj = NULL;
	sdo_list_object_handlers.read_dimension = sdo_list_read_dimension;
	sdo_list_object_handlers.write_dimension = sdo_list_write_dimension;
	sdo_list_object_handlers.has_dimension = sdo_list_has_dimension;
	sdo_list_object_handlers.unset_dimension = sdo_list_unset_dimension;
	sdo_list_object_handlers.get_properties = sdo_list_get_properties;
	sdo_list_object_handlers.compare_objects = sdo_list_compare_objects;
	sdo_list_object_handlers.cast_object = sdo_list_cast_object;
	sdo_list_object_handlers.count_elements = sdo_list_count_elements;

	sdo_list_iterator_funcs.dtor = sdo_list_iterator_dtor;
	sdo_list_iterator_funcs.valid = sdo_list_iterator_valid;
	sdo_list_iterator_funcs.get_current_data = sdo_list_iterator_current_data;
	sdo_list_iterator_funcs.get_current_key = sdo_list_iterator_current_key;
	sdo_list_iterator_funcs.move_forward = sdo_list_iterator_move_forward;
	sdo_list_iterator_funcs.rewind = sdo_list_iterator_rewind;
	sdo_list_iterator_funcs.invalidate_current = 0;
}
/* }}} */

/* {{{ SDO_List::__construct
 */
PHP_METHOD(SDO_List, __construct)
{
	char *class_name, *space;
	class_name = get_active_class_name(&space TSRMLS_CC);

	php_error(E_ERROR, "%s%s%s(): internal error - private constructor was called",
		class_name, space, get_active_function_name(TSRMLS_C));
}
/* }}} */

/* {{{ SDO_List::count
 */
PHP_METHOD(SDO_List, count)
{
	long count = 0;

	sdo_list_count_elements(getThis(), &count TSRMLS_CC);
	RETVAL_LONG(count);
}
/* }}} */

/* {{{ SDO_List::insert
 */
PHP_METHOD(SDO_List, insert)
{
	zval			 *z_value;
	zval			 *z_index = NULL;
	long			  index = -1;
	sdo_list_object	 *my_object;
	sdo_write_type	  write_type;
	char			 *class_name, *space;

	if (zend_parse_parameters(ZEND_NUM_ARGS() TSRMLS_CC, "z|z", &z_value, &z_index) == FAILURE) {
		return;
	}

	/* If the index parameter was not set, or was set to NULL, treat this as an append */
	if (z_index == 0 || Z_TYPE_P(z_index) == IS_NULL) {
		write_type = TYPE_APPEND;
	} else {
		convert_to_long_ex(&z_index);
		index = Z_LVAL_P(z_index);
		write_type = TYPE_INSERT;
	}

	my_object = sdo_list_get_instance(getThis() TSRMLS_CC);

	switch (my_object->list_type) {
	case TYPE_DataObjectList:
		sdo_dataobjectlist_write_value(my_object, index, z_value, write_type TSRMLS_CC);
		break;
	case TYPE_ChangedDataObjectList:
		zend_throw_exception_ex(sdo_unsupportedoperationexception_class_entry, 0 TSRMLS_CC,
			"A ChangedDataObjectList is read-only");
		break;
	case TYPE_SettingList:
		zend_throw_exception_ex(sdo_unsupportedoperationexception_class_entry, 0 TSRMLS_CC,
			"A SettingList is read-only");
		break;
	default:
		class_name = get_active_class_name(&space TSRMLS_CC);
		php_error(E_ERROR, "%s%s%s(): internal error (%i) - invalid dimension type %i",
			class_name, space, get_active_function_name(TSRMLS_C), __LINE__, my_object->list_type);
	}

}
/* }}} */

/*
 * Local variables:
 * tab-width: 4
 * c-basic-offset: 4
 * End:
 * vim600: noet sw=4 ts=4 fdm=marker
 * vim<600: noet sw=4 ts=4
 */
