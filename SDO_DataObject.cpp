/* 
+----------------------------------------------------------------------+
| (c) Copyright IBM Corporation 2005.                                  | 
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
#include <string>
#include "zend_config.w32.h"
#endif


#include "php.h"
#include "zend_exceptions.h"

#include "php_sdo_int.h"

#define CLASS_NAME "SDO_DataObject"

/* {{{ sdo_do_iterator
 * The iterator data for this class - extends the standard zend_object_iterator
 */
typedef struct {
	zend_object_iterator zoi;		/* The standard zend_object_iterator */
	ulong				 index;		/* current index */
	zend_bool			 valid;
/*	PropertyList		 pl; sdolib crashes when I copy the list here */
	zval				*value;
} sdo_do_iterator;
/* }}} */

static zend_object_handlers sdo_do_object_handlers;
static zend_object_iterator_funcs sdo_do_iterator_funcs;

/* {{{ sdo_do_get_instance
 */
static sdo_do_object *sdo_do_get_instance(zval *me TSRMLS_DC) 
{
	return (sdo_do_object *)zend_object_store_get_object(me TSRMLS_CC);
}
/* }}} */

/* {{{ sdo_do_object_free_storage
 */
static void sdo_do_object_free_storage(void *object TSRMLS_DC)
{
	sdo_do_object *my_object;

	my_object = (sdo_do_object *)object;
	zend_hash_destroy(my_object->zo.properties);
	FREE_HASHTABLE(my_object->zo.properties);

	/* just release the reference, and the reference counting will kick in */
	/*TODO assigning NULL triggers an error in the C++ library at shutdown.
	 *my_object->dop = NULL;
	 */
	efree(object);
}
/* }}} */

/* {{{ sdo_do_object_create
 */
static zend_object_value sdo_do_object_create(zend_class_entry *ce TSRMLS_DC)
{
	zend_object_value retval;
	zval *tmp; /* this must be passed to hash_copy, but doesn't seem to be used */
	sdo_do_object *my_object;

	my_object = (sdo_do_object *)emalloc(sizeof(sdo_do_object));
	memset(my_object, 0, sizeof(sdo_do_object));
	my_object->zo.ce = ce;
	ALLOC_HASHTABLE(my_object->zo.properties);
	zend_hash_init(my_object->zo.properties, 0, NULL, ZVAL_PTR_DTOR, 0);
	zend_hash_copy(my_object->zo.properties, &ce->default_properties, (copy_ctor_func_t)zval_add_ref,
		(void *)&tmp, sizeof(zval *));
	retval.handle = zend_objects_store_put(my_object, NULL, sdo_do_object_free_storage, NULL TSRMLS_CC);
	retval.handlers = &sdo_do_object_handlers;

	return retval;
}
/* }}} */

/* {{{ sdo_do_clone_obj
 */
static zend_object_value sdo_do_clone_obj(zval *object TSRMLS_DC)
{
	sdo_do_object *my_old_object;
	DataObjectPtr new_dop;
	zval *z_new;

    MAKE_STD_ZVAL(z_new);

	my_old_object = sdo_do_get_instance(object TSRMLS_CC);
	
	try {
		new_dop = CopyHelper::copy(my_old_object->dop);	    
		sdo_do_new(z_new, new_dop TSRMLS_CC);
	} catch (SDORuntimeException e) {
		sdo_throw_runtimeexception(&e TSRMLS_CC);
	}
	
	return z_new->value.obj;
}
/* }}} */

/* {{{ sdo_do_has_dimension
*/
static int sdo_do_has_dimension(zval *object, zval *offset, int check_empty TSRMLS_DC)
{
	const char		 *xpath;
	const Property	 *propertyp;
	sdo_do_object	 *my_object = (sdo_do_object *)NULL;
	DataObjectPtr	  dop;
	int				  return_value = 0; 

	my_object = sdo_do_get_instance(object TSRMLS_CC);
	if (my_object == (sdo_do_object *)NULL) {
		php_error(E_ERROR, "%s:%i: object is not in object store", CLASS_NAME, __LINE__);
		return 0;
	}
	
	dop = my_object->dop;
	
	try {
		if (sdo_parse_offset_param(dop, offset, &propertyp, &xpath, 1, 1 TSRMLS_CC)
			== FAILURE) {
			return 0;
		}

		/* Note: although we now have a reference to the Property, 
		 * if the offset was an xpath, then it may not be a property of the 
		 * DataObject instance. So we don't use the Property as a parameter 
		 * to the DataObject methods, since this can only work if the xpath 
		 * is a simple property name, otherwise an exception will be thrown.
		 */
		return_value = dop->isSet(xpath);
		
		if (return_value && check_empty) {
			/* check_empty says we should additionally test if the value is equivalent to 0 */
			
			if (dop->isNull(xpath)) {
				return_value = 0;
			} else {
				switch (propertyp->getTypeEnum()) {		
				case Type::OtherTypes:
					php_error(E_ERROR, "%s:%i: unexpected DataObject type 'OtherTypes'", CLASS_NAME, __LINE__);
					return_value = 0;
					break;
				case Type::BigDecimalType:
				case Type::BigIntegerType:
				case Type::BooleanType:
				case Type::ByteType:
					return_value = dop->getBoolean(xpath);
					break;
				case Type::BytesType:
					return_value = (dop->getLength(xpath) != 0);
					break;
				case Type::CharacterType:
					return_value = dop->getBoolean(xpath);
					break;
				case Type::DateType:
					return_value = (dop->getDate(xpath).getTime() != 0);
					break;
				case Type::DoubleType:
				case Type::FloatType:
				case Type::IntegerType:
				case Type::LongType:
				case Type::ShortType:
					return_value = dop->getBoolean(xpath);
					break;
				case Type::StringType:
				case Type::UriType:
					/* TODO is this the buffer length or the string length ??? */
					return_value = (dop->getLength(xpath) > 0);
					break;		
				case Type::DataObjectType:
					/* since the property is set, the data object cannot be 'empty' */
					break;
				case Type::ChangeSummaryType:
					php_error(E_ERROR, "%s:%i: unexpected DataObject type 'ChangeSummaryType'", CLASS_NAME, __LINE__);
					return_value = 0;
					break;
				case Type::TextType:
					php_error(E_ERROR, "%s:%i: unexpected DataObject type 'TextType'", CLASS_NAME, __LINE__);
					return_value = 0;
					break;
				default:
					php_error(E_ERROR, "%s:%i: unexpected type %s for property '%s'", CLASS_NAME, __LINE__, 
						propertyp->getType().getName(), xpath);
					return_value = 0;
				}
			}
		}
	} catch(SDORuntimeException e) {
		return_value = 0;
	}
	
	return return_value;
}
/* }}} */

/* {{{ sdo_do_read_list
 */
static zval *sdo_do_read_list(sdo_do_object *sdo, const char *xpath, const Property *propertyp TSRMLS_DC)
{
	zval			*return_value;

	MAKE_STD_ZVAL(return_value);
	try {
		DataObjectList& list_value = sdo->dop->getList(xpath);
		if (&list_value == NULL) {
			RETVAL_NULL();
		} else {
			/* make a new SDO_DataObjectList */
			sdo_dataobjectlist_new(return_value, propertyp->getType(), &list_value TSRMLS_CC);
		}
	} catch (SDORuntimeException e) {
		sdo_throw_runtimeexception(&e TSRMLS_CC);
		RETVAL_NULL();
	}
	return return_value;
}
/* }}} */

/* {{{ sdo_do_read_value
 */
static zval *sdo_do_read_value(sdo_do_object *sdo, const char *xpath, const Property *propertyp TSRMLS_DC)
{
	DataObjectPtr	 dop = sdo->dop;
	uint			 bytes_len;
	char			*bytes_value;
	char			 char_value; 
	wchar_t			 wchar_value;
	DataObjectPtr	 doh_value;
	zval			*doh_value_zval;
	zval			*return_value;
	

	MAKE_STD_ZVAL(return_value);
	try {
		if (propertyp->isMany()) {
		   /* If the property is many-valued and the list is uninitialized, 
		    * all bets are off. The C++ library does not catch this 
			* consistently, so ...
			*/
			if (! dop->isSet(xpath)) {
				zend_throw_exception_ex(sdo_indexoutofboundsexception_class_entry, 
					0 TSRMLS_CC, 
					"Cannot read list at index \"%s\" because the list is empty", 
					(char *)xpath);
				RETVAL_NULL();
				return return_value;
			}
		} else {
		   /* 
		    * If the property is single-valued, we should just leave it to the 
		    * C++ library to decide whether the property is set, but currently 
			* it fails to detect this error, so we shall catch it here instead.
			* To be honest, there is no justification in the spec for 
			* restricting this to DataObject types, but it just doesn't seem 
			* right in the PHP world to throw an exception when a primitive 
			* happens to be unset.
			*/
			if (propertyp->isReference() && !dop->isValid(xpath)) {
				zend_throw_exception_ex(sdo_propertynotsetexception_class_entry,  
					0 TSRMLS_CC, 
					"Cannot read property \"%s\" because it is not set", 
					(char *)xpath);
				RETVAL_NULL();
				return return_value;
			}
		}

		if (dop->isNull(xpath)) {
			RETVAL_NULL();
		} else {		
			switch(propertyp->getTypeEnum()) {
			case Type::OtherTypes:
				php_error(E_ERROR, "%s:%i: unexpected DataObject type 'OtherTypes'", CLASS_NAME, __LINE__);
				break;
			case Type::BigDecimalType:
			case Type::BigIntegerType:
				RETVAL_STRING((char *)(dop->getCString(xpath)), 1);
				break;
			case Type::BooleanType:
				RETVAL_BOOL(dop->getBoolean(xpath));
				break;
			case Type::ByteType:
				RETVAL_LONG(dop->getByte(xpath));
				break;
			case Type::BytesType:
				bytes_len = dop->getLength(xpath);
				bytes_value = (char *)emalloc(1 + bytes_len);
				bytes_len = dop->getBytes(xpath, bytes_value, bytes_len);
				bytes_value[bytes_len] = '\0';
				RETVAL_STRINGL(bytes_value, bytes_len, 0);
				break;
			case Type::CharacterType:
				wchar_value = dop->getCharacter(xpath);
				if (wchar_value > INT_MAX) {
					php_error(E_WARNING, "%s:%i: wide character data lost", CLASS_NAME, __LINE__);
				}
				char_value = dop->getByte(xpath);
				RETVAL_STRINGL(&char_value, 1, 1);
				break;
			case Type::DateType:
				RETVAL_LONG(dop->getDate(xpath).getTime());
				break;
			case Type::DoubleType:
				RETVAL_DOUBLE(dop->getDouble(xpath));
				break;
			case Type::FloatType:
				RETVAL_DOUBLE(dop->getFloat(xpath));
				break;
			case Type::IntegerType:
				RETVAL_LONG(dop->getInteger(xpath));
				break;
			case Type::LongType:
				/* An SDO long (64 bits) may overflow a PHP int, so we return it as a string */
				RETVAL_STRING((char *)dop->getCString(xpath), 1);
				break;
			case Type::ShortType:
				RETVAL_LONG(dop->getShort(xpath));
				break;
			case Type::StringType:
			case Type::UriType:
				RETVAL_STRING((char *)dop->getCString(xpath), 1);
				break;		
			case Type::DataObjectType:
				doh_value = dop->getDataObject(xpath);
				if (!doh_value) {
					php_error(E_WARNING, "%s:%i: read a NULL DataObject for property '%s'", CLASS_NAME, __LINE__, 
						propertyp->getName());
					RETVAL_NULL();
				} else {
					doh_value_zval = (zval *)doh_value->getUserData();
					if (doh_value_zval == (zval *)SDO_USER_DATA_EMPTY) {
						php_error(E_ERROR, "%s:%i: object is not in object store", CLASS_NAME, __LINE__);
						RETVAL_NULL();
					}
					RETVAL_ZVAL(doh_value_zval, 1, 0);
				}
				break;
			case Type::ChangeSummaryType:
				php_error(E_ERROR, "%s:%i: unexpected DataObject type 'ChangeSummaryType'", CLASS_NAME, __LINE__);
				break;
			case Type::TextType:
				php_error(E_ERROR, "%s:%i: unexpected DataObject type 'TextType'", CLASS_NAME, __LINE__);
				break;
			default:
				php_error(E_ERROR, "%s:%i: unexpected DataObject type '%s' for property '%s'", CLASS_NAME, __LINE__, 
					propertyp->getType().getName(), xpath);
			}
		}
		return return_value;
	} catch (SDORuntimeException e) {
		sdo_throw_runtimeexception(&e TSRMLS_CC);
		RETVAL_NULL();
	}
	return return_value;
}
/* }}} */

/* {{{ sdo_do_read_dimension
 */

static zval *sdo_do_read_dimension(zval *object, zval *offset, int type TSRMLS_DC)
{
	const char		 *xpath;
	const Property   *propertyp;
	sdo_do_object    *my_object;
	DataObjectPtr	  dop;
	zval			 *return_value;


	my_object = sdo_do_get_instance(object TSRMLS_CC);
	if (my_object == (sdo_do_object *)NULL) {
		php_error(E_ERROR, "%s:%i: object is not in object store", CLASS_NAME, __LINE__);
		MAKE_STD_ZVAL(return_value);
		RETVAL_NULL();
		return return_value;
	}
	
	dop = my_object->dop;
	
	try {	
		if (sdo_parse_offset_param(
			dop, offset, &propertyp, &xpath, 1, 0 TSRMLS_CC) == FAILURE) {
			MAKE_STD_ZVAL(return_value);
			RETVAL_NULL();
			return return_value;
		}

		/* Note: although we now have a reference to the Property, 
		 * if the offset was an xpath, then it may not be a property of the 
		 * DataObject instance. So we don't use the Property as a parameter 
		 * to the DataObject methods, since this can only work if the xpath 
		 * is a simple property name, otherwise an exception will be thrown.
		 */

	   /*
		* We need to discover whether the xpath should result in returning the list itself, or 
		* a list element, hence the XpathHelper test
		*/
		if (propertyp->isMany() && ! XpathHelper::isIndexed(xpath)) {
			return_value = sdo_do_read_list (my_object, xpath, propertyp TSRMLS_CC);
		} else {
			return_value = sdo_do_read_value(my_object, xpath, propertyp TSRMLS_CC);
		} 
	} catch(SDORuntimeException e) {
		sdo_throw_runtimeexception(&e TSRMLS_CC);
		MAKE_STD_ZVAL(return_value);
		RETVAL_NULL();
	}
	return return_value;
}
/* }}} */

/* {{{ sdo_do_unset_dimension
 */
static void sdo_do_unset_dimension(zval *object, zval *offset TSRMLS_DC)
{	
	const char		*xpath;
	sdo_do_object	*my_object;	
	
	my_object = sdo_do_get_instance(object TSRMLS_CC);
	if (my_object == (sdo_do_object *) NULL) {
		php_error(E_ERROR, "%s:%i: object is not in object store", CLASS_NAME, __LINE__);
		return;
	}
	
	try {	
		if (sdo_parse_offset_param(
			my_object->dop, offset, NULL, &xpath, 1, 0 TSRMLS_CC) == FAILURE) {
			return;
		}
		
		my_object->dop->unset(xpath);

	} catch(SDORuntimeException e) {
		sdo_throw_runtimeexception(&e TSRMLS_CC);
	}
}
/* }}} */

/* {{{ sdo_do_write_dimension
 */
static void sdo_do_write_dimension(zval *object, zval *offset, zval *z_propertyValue TSRMLS_DC)
{	
	const char			*xpath;
	const Property		*property_p;
	sdo_do_object		*my_object, *value_object;
	DataObjectPtr		 dop;
	zval				 temp_zval;
	bool				 is_open;
	Type::Types          type_enum;

	my_object = sdo_do_get_instance(object TSRMLS_CC);
	if (my_object == (sdo_do_object *)NULL) {
		php_error(E_ERROR, "%s:%i: object is not in object store", CLASS_NAME, __LINE__);
		return;
	}

	dop = my_object->dop;

	try {	
		if (sdo_parse_offset_param(
			dop, offset, &property_p, &xpath, ! dop->getType().isOpenType(), 0 TSRMLS_CC) == FAILURE) {
			return;
		}

		if (property_p == NULL) {
			/* open type, so we'll derive the sdo type from the php type */
			type_enum = sdo_map_zval_type(z_propertyValue);
		} else {
			/* known type, so we'll coerce the php type to the sdo type */
			type_enum = property_p->getTypeEnum();
		} 
		
	   /* Note: although we now have a reference to the Property, 
		* if the offset was an xpath, then it may not be a property of the 
		* DataObject instance. So we don't use the Property as a parameter 
		* to the DataObject methods, since this can only work if the xpath 
		* is a simple property name, otherwise an exception will be thrown.
		*/
		
		if (Z_TYPE_P(z_propertyValue) == IS_NULL) {
			dop->setNull(xpath);
		} else {
		   /*
		    * Since we may have to coerce the type, we make a local copy of the zval, so that the
		    * original is unaffected. 
		    * 
		    * TODO This could be optimized to only copy if we do actually change the type.
			*/ 
			temp_zval = *z_propertyValue;
			zval_copy_ctor(&temp_zval);
			
			switch(type_enum) {
			case Type::OtherTypes:
				php_error(E_ERROR, "%s:%i: unexpected DataObject type 'OtherTypes'", CLASS_NAME, __LINE__);
				break;
			case Type::BigDecimalType:
			case Type::BigIntegerType:
				convert_to_string(&temp_zval);
				dop->setCString(xpath, Z_STRVAL(temp_zval));
				break;
			case Type::BooleanType:
				convert_to_boolean(&temp_zval);
				dop->setBoolean(xpath, ZEND_TRUTH(Z_BVAL(temp_zval)));
				break;
			case Type::ByteType:
				convert_to_long(&temp_zval);
				dop->setByte(xpath, Z_LVAL(temp_zval));
				break;
			case Type::BytesType:
				convert_to_string(&temp_zval);
				dop->setBytes(xpath, Z_STRVAL(temp_zval), Z_STRLEN(temp_zval));
				break;
			case Type::CharacterType: 
				convert_to_string(&temp_zval);
				dop->setCharacter(xpath, (char)(Z_STRVAL(temp_zval)[0]));
				break;
			case Type::DateType:
				convert_to_long(&temp_zval);
				dop->setDate(xpath, (SDODate)Z_LVAL(temp_zval));
				break;
			case Type::DoubleType:
				convert_to_double(&temp_zval);
				dop->setDouble(xpath, Z_DVAL(temp_zval));
				break;
			case Type::FloatType:
				convert_to_double(&temp_zval);
				dop->setFloat(xpath, (float)Z_DVAL(temp_zval));
				break;
			case Type::IntegerType:
				convert_to_long(&temp_zval);
				dop->setInteger(xpath, (int)Z_LVAL(temp_zval));
				break;
			case Type::LongType:
				if (Z_TYPE(temp_zval) == IS_LONG) {
					dop->setLong(xpath, Z_LVAL(temp_zval));
				} else {					
					convert_to_string(&temp_zval);
					dop->setCString(xpath, Z_STRVAL(temp_zval));
				}
				break;
			case Type::ShortType:
				convert_to_long(&temp_zval);
				dop->setShort(xpath, (short)Z_LVAL(temp_zval));
				break;
			case Type::StringType:
			case Type::UriType:
				convert_to_string(&temp_zval);
				dop->setCString(xpath, Z_STRVAL(temp_zval));
				break;
			case Type::DataObjectType:
				convert_to_object(&temp_zval);
				if (!instanceof_function(Z_OBJCE(temp_zval), sdo_dataobjectimpl_class_entry TSRMLS_CC)) {
					php_error(E_ERROR, "%s:%i: cannot cast %s to %s", CLASS_NAME, __LINE__, 
						Z_OBJCE(temp_zval)->name, sdo_dataobjectimpl_class_entry->name);
				} else {
					value_object = (sdo_do_object *)zend_object_store_get_object(&temp_zval TSRMLS_CC);
					dop->setDataObject(xpath, value_object->dop);
				}
				break;
			case Type::ChangeSummaryType:
				php_error(E_ERROR, "%s:%i: unexpected DataObject type 'ChangeSummaryType'", CLASS_NAME, __LINE__);
				break;
			case Type::TextType:
				php_error(E_ERROR, "%s:%i: unexpected DataObject type 'TextType'", CLASS_NAME, __LINE__);
				break;
			default:
				php_error(E_ERROR, "%s:%i: unexpected DataObject type '%s' for property '%s'", CLASS_NAME, __LINE__, 
					(property_p ? property_p->getType().getName() : ""), xpath);
			}
			zval_dtor(&temp_zval);
		}
	} catch (SDORuntimeException e) {
		sdo_throw_runtimeexception(&e TSRMLS_CC);
	}
}
/* }}} */

/* {{{ sdo_do_get_properties
 * called as a result of print_r() or vardump(), but doesn't get called for reflection
 * Returns an array of name=>value pairs for the properties of the data object
 */
static HashTable *sdo_do_get_properties(zval *object TSRMLS_DC)
{ 
	sdo_do_object	*my_object;
	HashTable			*properties;
	int				 entries;
	zval				*tmp;
	
	ALLOC_HASHTABLE(properties);
	
	my_object = sdo_do_get_instance(object TSRMLS_CC);
	if (my_object == (sdo_do_object *)NULL) {
		php_error(E_ERROR, "%s:%i: object is not in object store", CLASS_NAME, __LINE__);
	} else {		 
		try {
			PropertyList pl = my_object->dop->getInstanceProperties();
			entries = pl.size();
			zend_hash_init(properties, entries, NULL, NULL, 0);
			for (long index = 0; index < entries; index++) {
				/* It's safe to use the property directly here, it cannot be an xpath */
				const Property& property = pl[index];
				const char *propertyName = property.getName();
				
				/* Usually we check isMany() before isSet(), because we should not return NULL just because
				* the List is empty. But for get_properties the return values are readonly, so it's
				*	safer and clearer to omit empty lists.
				*/
				if (my_object->dop->isSet(property)) { 
					if (property.isMany()) {
						long count = 0;
						tmp = sdo_do_read_list(my_object, propertyName, &property TSRMLS_CC);
						sdo_list_count_elements (tmp, &count TSRMLS_CC);
						if (count == 0)
							continue;
					} else {
						tmp = sdo_do_read_value(my_object, propertyName, &property TSRMLS_CC);
					}
				} else {
					continue;
				}
				zend_hash_update(properties, (char *)propertyName, 1 + strlen(propertyName),
					&tmp, sizeof(zval *), NULL);
			}
		} catch (SDORuntimeException e) {
			sdo_throw_runtimeexception(&e TSRMLS_CC);
		}
	}
	return properties;
}
/* }}} */

/* {{{ sdo_do_compare_objects
 * gets called as a consequence of an == comparison
 * we do a deep compare of property names, types, and values
 */
static int sdo_do_compare_objects(zval *object1, zval *object2 TSRMLS_DC) 
{
	sdo_do_object	*my_object1, *my_object2;
	DataObjectPtr		 dop1, dop2;
	PropertyList		 pl;
	int				 entries;
	const char			*propertyName;
	zval				 offset;
	zval				 result;
	
	INIT_PZVAL(&offset);
	INIT_PZVAL(&result);
	
	my_object1 = sdo_do_get_instance(object1 TSRMLS_CC);
	my_object2 = sdo_do_get_instance(object2 TSRMLS_CC);
	dop1 = my_object1->dop;
	dop2 = my_object2->dop;
	
	if (dop1 == dop2)
		return SUCCESS;
	
	try {
		pl = dop1->getInstanceProperties();
		entries = pl.size();
		if (entries != dop2->getInstanceProperties().size())
			return FAILURE;
		for (int i = 0; i < entries; i++) {
			const Property& prop1 = pl[i];
			propertyName = prop1.getName();
			const Property& prop2 = dop2->getType().getProperty(propertyName);
			/* if we get here, then the object does have a property of the same name */
			if (&prop1 != &prop2) {
				if ((prop1.isMany() != prop2.isMany()) ||
				/*
				* When a property is many-valued, it may not return consistent values for isSet,
				* Let it carry on to the value equality tests.
				*/
				((!prop1.isMany()) && dop1->isSet(prop1) != dop2->isSet(prop2)) ||
				(prop1.getTypeEnum() != prop2.getTypeEnum()) ||
				/*
				* The meaning of containment for primitives is somewhat unclear in the spec.
				* I'm going to ignore it to avoid a discrepancy in unserializing
				*/
				(prop1.getType().isDataObjectType() && (prop1.isContainment() != prop2.isContainment())) ||
				(prop1.isReadOnly() != prop2.isReadOnly()))
				
				return FAILURE;
			}
			/* OK we can consider the properties equal */
			if (dop1->isSet(prop1)) {
				/* the property is set, so we must also compare its value */
				ZVAL_STRING(&offset, (char *)propertyName, 0);
				zval *value1 = sdo_do_read_dimension(object1, &offset, 0 TSRMLS_CC);
				zval *value2 = sdo_do_read_dimension(object2, &offset, 0 TSRMLS_CC);
				int rc = compare_function(&result, value1, value2 TSRMLS_CC);
				zval_ptr_dtor(&value1);
				zval_ptr_dtor(&value2);
				if (rc || Z_LVAL(result))
					return FAILURE;
			}
		}
		return SUCCESS;
	} catch (SDORuntimeException e) {
		/* In this case we won't rethrow the exception - suffice it to say that the objects are not equal */
		return FAILURE;
	}
}
/* }}} */

/* {{{ sdo_do_cast_object
*/ 
static int sdo_do_cast_object(zval *readobj, zval *writeobj, int type, int should_free TSRMLS_DC) 
{
	sdo_do_object *my_object;
	ostringstream print_buf;
	zval free_obj;
	int rc = SUCCESS;
	
	if (should_free) {
		free_obj = *writeobj;
	}
	
	my_object = sdo_do_get_instance(readobj TSRMLS_CC);
	if (my_object == (sdo_do_object *)NULL) {
		ZVAL_NULL(writeobj);
		php_error(E_ERROR, "%s:%i: object is not in object store", CLASS_NAME, __LINE__);
		rc = FAILURE;
	} else {
		
		try {
			const Type& type = my_object->dop->getType();
			PropertyList pl = my_object->dop->getInstanceProperties();
			
			print_buf << "object(" << CLASS_NAME << ")#" <<
				readobj->value.obj.handle << " (" << pl.size() << ") {";
			
			for (unsigned int i = 0; i < pl.size(); i++) {
				const Property& prop = pl[i];
				
				if (i > 0) {
					print_buf << "; ";
				}
				
				print_buf << prop.getName();
				
				/* We'll try to print the value for single-valued primitives only.
				* Multi-valued properties just get a dimension.
				*/
				if (prop.isMany()) {
					print_buf << '[' << my_object->dop->getList(prop).size() << ']';
					
				} else if (my_object->dop->isSet(i) && prop.getType().isDataType()) {
					print_buf << "=>";
					if (my_object->dop->isNull(i)) {
						print_buf << "NULL";
					} else {
						print_buf << '\"' << my_object->dop->getCString(i) << '\"';
					}
					
				}
			}
			
			print_buf << '}';
			string print_string = print_buf.str()/*.substr(0, SDO_TOSTRING_MAX)*/;
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

/* {{{ sdo_do_count_elements
 */ 
static int sdo_do_count_elements(zval *object, long *count TSRMLS_DC) 
{
	sdo_do_object *my_object;
	
	my_object = sdo_do_get_instance(object TSRMLS_CC);
	if (my_object == (sdo_do_object *)NULL) {
		php_error(E_ERROR, "%s:%i: object is not in object store", CLASS_NAME, __LINE__);
		*count = 0;
		return FAILURE;
	}
	
	try {
		*count = my_object->dop->getInstanceProperties().size();
	} catch (SDORuntimeException e) {
		sdo_throw_runtimeexception(&e TSRMLS_CC);
		return FAILURE;
	}
	return SUCCESS;
}
/* }}} */

static void sdo_do_iterator_rewind (zend_object_iterator *iter TSRMLS_DC);

/* {{{ sdo_do_get_iterator
 */
zend_object_iterator *sdo_do_get_iterator(zend_class_entry *ce, zval *object TSRMLS_DC) {

	sdo_do_object *my_object = (sdo_do_object *)sdo_do_get_instance(object TSRMLS_CC);
	sdo_do_iterator *iterator = (sdo_do_iterator *)emalloc(sizeof(sdo_do_iterator));
	object->refcount++;
	iterator->zoi.data = (void *)object;
	iterator->zoi.funcs = &sdo_do_iterator_funcs;
	sdo_do_iterator_rewind((zend_object_iterator *)iterator TSRMLS_CC);

	/* TODO
	 * I'd like to store a reference to the C++ PropertyList in the iterator object at this point.
	 * But I'm seeing nasty memory corruption problems, so instead I'm reading out the list
	 * with getInstanceProperties() each time it's needed. This could lead to the iterator state 
	 * becoming inconsistent if the DataObject's properties are modified during the iteration.
	 */

	return (zend_object_iterator *)iterator;
}
/* }}} */

/* {{{ sdo_sequence_iterator_dtor
 */
static void sdo_do_iterator_dtor(zend_object_iterator *iter TSRMLS_DC)
{
	/* nothing special to be done */
	efree(iter);
}
/* }}} */

/* {{{ sdo_do_iterator_valid
 */
static int sdo_do_iterator_valid (zend_object_iterator *iter TSRMLS_DC)
{	
	sdo_do_iterator *iterator = (sdo_do_iterator *)iter;
	
	return (iterator->valid) ? SUCCESS : FAILURE;
}
/* }}} */

/* {{{ sdo_do_iterator_current_key
 */
static int sdo_do_iterator_current_key (zend_object_iterator *iter, 
										char **str_key, uint *str_key_len, ulong *int_key TSRMLS_DC)
{
	sdo_do_iterator *iterator = (sdo_do_iterator *)iter;
	zval *z_do_object = (zval *)iterator->zoi.data;
	sdo_do_object *my_object = (sdo_do_object *)sdo_do_get_instance(z_do_object TSRMLS_CC);
	
	if (!iterator->valid)
		return HASH_KEY_NON_EXISTANT;

	try {
		const Property& property = my_object->dop->getInstanceProperties()[iterator->index];
		const char *key = property.getName();
		*str_key_len = 1 + strlen(key);	
		*str_key = (char *)emalloc(1 + *str_key_len);
		strcpy(*str_key, key);
		return HASH_KEY_IS_STRING;
	} catch (SDORuntimeException e) {
		iterator->valid = false;
		sdo_throw_runtimeexception(&e TSRMLS_CC);
		return HASH_KEY_NON_EXISTANT; /* sic */
	}
}
/* }}} */

/* {{{ sdo_do_iterator_current_data
 */
static void sdo_do_iterator_current_data (zend_object_iterator *iter, zval ***data TSRMLS_DC)
{	
	sdo_do_iterator *iterator = (sdo_do_iterator *)iter;
	zval *z_do_object = (zval *)iterator->zoi.data;
	sdo_do_object *my_object = (sdo_do_object *)sdo_do_get_instance(z_do_object TSRMLS_CC);	
	
	try {
		/* It's safe to use the property directly here, it cannot be an xpath */
		const Property& property = my_object->dop->getInstanceProperties()[iterator->index];
		if (property.isMany()) {
			iterator->value = sdo_do_read_list(my_object,  property.getName(), &property TSRMLS_CC);
		/* either it is set or it has a default value */
		} else if (my_object->dop->isValid(property)) { 
			iterator->value = sdo_do_read_value(my_object, property.getName(), &property TSRMLS_CC);
		} else {
			MAKE_STD_ZVAL(iterator->value);
			ZVAL_NULL(iterator->value);
		}
		*data = &iterator->value;
	} catch (SDORuntimeException e) {
		iterator->valid = false;
		sdo_throw_runtimeexception(&e TSRMLS_CC);
	}
	
}
/* }}} */

/* {{{ sdo_do_iterator_move_forward
 */
static void sdo_do_iterator_move_forward (zend_object_iterator *iter TSRMLS_DC)
{
	sdo_do_iterator *iterator = (sdo_do_iterator *)iter;
	
	if (iterator->valid) {
		zval *z_do_object = (zval *)iterator->zoi.data;
		sdo_do_object *my_object = (sdo_do_object *)sdo_do_get_instance(z_do_object TSRMLS_CC);
		
		try {
			PropertyList pl = my_object->dop->getInstanceProperties();
			for (iterator->index++; 
			iterator->index < pl.size() && ! my_object->dop->isValid(iterator->index);
			iterator->index++);
			if (iterator->index >= pl.size()) 
				iterator->valid = false;
		} catch (SDORuntimeException e) {
			iterator->valid = false;
			sdo_throw_runtimeexception(&e TSRMLS_CC);
		}
	}
}
/* }}} */

/* {{{ sdo_do_iterator_rewind
 */
static void sdo_do_iterator_rewind (zend_object_iterator *iter TSRMLS_DC)
{
	sdo_do_iterator *iterator = (sdo_do_iterator *)iter;
	zval *z_do_object = (zval *)iterator->zoi.data;
	sdo_do_object *my_object = (sdo_do_object *)sdo_do_get_instance(z_do_object TSRMLS_CC);

	try {
		PropertyList pl = my_object->dop->getInstanceProperties();
		for (iterator->index = 0; 
		    iterator->index < pl.size() && ! my_object->dop->isValid(iterator->index);
		    iterator->index++);
		iterator->valid = (iterator->index < pl.size());
	} catch (SDORuntimeException e) {
		iterator->valid = false;
		sdo_throw_runtimeexception(&e TSRMLS_CC);
	}
}
/* }}} */

/* {{{ sdo_do_serialize
 * Both the model and the graph are serialized to a buffer.
 */
static int sdo_do_serialize (zval *object, unsigned char **buffer_p, zend_uint *buf_len_p, zend_serialize_data *data TSRMLS_DC)
{	
	sdo_do_object		*my_object;
	char				*serialized_model;
	char				*serialized_graph;
	unsigned long		 model_length;
	unsigned long		 graph_length;
	xmldas::XMLDAS		*xmldasp;
	
	my_object = sdo_do_get_instance(object TSRMLS_CC);
	if (my_object == (sdo_do_object *)NULL) {
		php_error(E_ERROR, "%s:%i: object is not in object store", CLASS_NAME, __LINE__);
		return FAILURE;
	}

	try {
		xmldasp = sdo_get_XMLDAS();
		/* create an XML representation of the model */
		serialized_model = xmldasp->generateSchema(my_object->dop);
		model_length = strlen(serialized_model);
		/* serialize the data graph */
		serialized_graph = xmldasp->save(my_object->dop, 0, 0);
		graph_length = strlen(serialized_graph);
		
		/*
		 * The serialized buffer contains the schema followed by the data graph
		 * These are both null-terminated strings.
		 */
		unsigned long buffer_length = model_length + 1 + graph_length + 1;
		unsigned char *buffer = (unsigned char *)emalloc(buffer_length);
		memcpy((void *)&buffer[0], serialized_model, 1 + model_length);
		memcpy((void *)&buffer[1 + model_length], serialized_graph, 1 + graph_length);

		*buffer_p = buffer;
		*buf_len_p = buffer_length;
	} catch (SDORuntimeException e) {
		sdo_throw_runtimeexception(&e TSRMLS_CC);
		return FAILURE;
	}
	return SUCCESS;
}
/* }}} */

/* {{{ sdo_do_populate_graph
 * walk the data graph creating a php wrapper for each data object.
 * Return the zval for the root object.
 */
static zval *sdo_do_populate_graph (DataObjectPtr dop TSRMLS_DC)
{
	zval *z_new_do;
	
   /* We may have previously encountered this DataObject, perhaps as a non-containment reference.
	* If so, don't create a duplicate PHP object.
	*/
	z_new_do = (zval *)dop->getUserData();
	if (z_new_do == (zval *)SDO_USER_DATA_EMPTY) {
		
		MAKE_STD_ZVAL(z_new_do);
		sdo_do_new(z_new_do, dop TSRMLS_CC);
		
		PropertyList pl = dop->getInstanceProperties();
		for (int i = 0; i < pl.size(); i++) {
			if (dop->isSet(pl[i])) {
				if (pl[i].isMany()) {
					DataObjectList& dol = dop->getList(pl[i]);
					for (int j = 0; j < dol.size(); j++) {
						sdo_do_populate_graph(dol[j] TSRMLS_CC);
					}
				} else if (pl[i].getType().isDataObjectType()) {
					sdo_do_populate_graph(dop->getDataObject(pl[i]) TSRMLS_CC);
				}
			}
		}
	}
	return z_new_do;
}
/* }}} */

/* {{{ sdo_do_unserialize
 * Recreate the graph and underlying model from a buffer.
 */
static int sdo_do_unserialize (zval **object, zend_class_entry *ce, const unsigned char *buffer, zend_uint buffer_length, zend_unserialize_data *data TSRMLS_DC)
{
	char				*serialized_model;
	char				*serialized_graph;
	zval				*factory_zval;
	zval				*root_object_zval;
	xmldas::XMLDAS		*xmldasp;

	/*
	 * The serialized data comprises the model and the graph, both as null-terminated strings
	 */
	serialized_model = (char *)&buffer[0];
	serialized_graph = (char *)&buffer[1 + strlen(serialized_model)];

	try {  
		/* the XMLDAS contains the new DataFactory, so do not use a cached XMLDAS */
		xmldasp = xmldas::XMLDAS::create();
		/* Load the model from the serialized data */
		xmldasp->loadSchema(serialized_model);
		/* Create the PHP representation of the factory */
		MAKE_STD_ZVAL(factory_zval);
		sdo_das_df_new(factory_zval, xmldasp->getDataFactory() TSRMLS_CC);

		/* Load the graph */
		DataObjectPtr root_dop = xmldasp->load(serialized_graph)->getRootDataObject();
		
		/* Walk the graph creating its PHP representation */
		root_object_zval = sdo_do_populate_graph(root_dop TSRMLS_CC);
		**object = *root_object_zval;
		zval_copy_ctor(*object);
		
	} catch (SDORuntimeException e) {
		sdo_throw_runtimeexception(&e TSRMLS_CC);
		return FAILURE;
	}

	return SUCCESS;
}
/* }}} */

/* {{{ sdo_do_new 
 * The DataObject has no public constructor, this is in effect its factory method.
 */
void sdo_do_new(zval *me, DataObjectPtr dop TSRMLS_DC)
{	
	sdo_do_object *my_object;
	
	Z_TYPE_P(me) = IS_OBJECT;	
	if (object_init_ex(me, sdo_dataobjectimpl_class_entry) == FAILURE) {
		php_error(E_ERROR, "%s:%i: object_init failed", CLASS_NAME, __LINE__);
		return;
	}
	
	my_object = (sdo_do_object *)zend_object_store_get_object(me TSRMLS_CC);
	my_object->dop = dop;
	/* add a back pointer to the C++ object */
	try {
		dop->setUserData ((void *)me);
	} catch (SDORuntimeException e) {
		sdo_throw_runtimeexception(&e TSRMLS_CC);	
	}
}
/* }}} */

/* {{{ sdo_do_minit
 */
void sdo_do_minit(zend_class_entry *tmp_ce TSRMLS_DC)
{
	tmp_ce->create_object = sdo_do_object_create;
	sdo_dataobjectimpl_class_entry = zend_register_internal_class(tmp_ce TSRMLS_CC);
	sdo_dataobjectimpl_class_entry->get_iterator = sdo_do_get_iterator;
	sdo_dataobjectimpl_class_entry->serialize = sdo_do_serialize;
	sdo_dataobjectimpl_class_entry->unserialize = sdo_do_unserialize;
	zend_class_implements(sdo_dataobjectimpl_class_entry TSRMLS_CC, 1, sdo_das_dataobject_class_entry);

	memcpy(&sdo_do_object_handlers, zend_get_std_object_handlers(), sizeof(zend_object_handlers));
	sdo_do_object_handlers.clone_obj = sdo_do_clone_obj;
	sdo_do_object_handlers.read_dimension = sdo_do_read_dimension;
	sdo_do_object_handlers.read_property = sdo_do_read_dimension;
	sdo_do_object_handlers.write_dimension = sdo_do_write_dimension;
	sdo_do_object_handlers.write_property = sdo_do_write_dimension;
	sdo_do_object_handlers.has_dimension = sdo_do_has_dimension;
	sdo_do_object_handlers.has_property = sdo_do_has_dimension;
	sdo_do_object_handlers.unset_dimension = sdo_do_unset_dimension;
	sdo_do_object_handlers.unset_property = sdo_do_unset_dimension;
	sdo_do_object_handlers.get_properties = sdo_do_get_properties;
	sdo_do_object_handlers.compare_objects = sdo_do_compare_objects;	
	/*TODO There's a signature change for cast_object in PHP6. */
#if (PHP_MAJOR_VERSION < 6) 
	sdo_do_object_handlers.cast_object = sdo_do_cast_object;
#endif
	sdo_do_object_handlers.count_elements = sdo_do_count_elements;

	sdo_do_iterator_funcs.dtor = sdo_do_iterator_dtor;
	sdo_do_iterator_funcs.valid = sdo_do_iterator_valid;
	sdo_do_iterator_funcs.get_current_data = sdo_do_iterator_current_data;
	sdo_do_iterator_funcs.get_current_key = sdo_do_iterator_current_key;
	sdo_do_iterator_funcs.move_forward = sdo_do_iterator_move_forward;
	sdo_do_iterator_funcs.rewind = sdo_do_iterator_rewind;
	sdo_do_iterator_funcs.invalidate_current = 0;
}
/* }}} */

/* {{{ SDO_DataObjectImpl::__construct
 */
PHP_METHOD(SDO_DataObjectImpl, __construct)
{
	php_error(E_ERROR, "%s:%i: private constructor was called", CLASS_NAME, __LINE__);
}
/* }}} */

/* {{{ SDO_DataObjectImpl::getContainer
 */
PHP_METHOD(SDO_DataObjectImpl, getContainer)
{
	sdo_do_object		*my_object;
	DataObjectPtr		 container_dop;
	zval				*container_zval;
	
	my_object = sdo_do_get_instance(getThis() TSRMLS_CC);
	if (my_object == (sdo_do_object *)NULL) {
		php_error(E_ERROR, "%s:%i: object is not in object store", CLASS_NAME, __LINE__);
		RETURN_NULL();
	} 
	
	try {
		container_dop = my_object->dop->getContainer();		
		if (!container_dop) {
			RETVAL_NULL();
		} else {
			container_zval = (zval *)container_dop->getUserData();
			RETVAL_ZVAL(container_zval, 1, 0);
		}
	} catch (SDORuntimeException e){
		sdo_throw_runtimeexception(&e TSRMLS_CC);
	}
}
/* }}} */

/* {{{ SDO_DataObjectImpl::getContainmentPropertyName
 */
PHP_METHOD(SDO_DataObjectImpl, getContainmentPropertyName)
{
	sdo_do_object *my_object;

	php_error(E_WARNING, 
		"SDO_DataObject::getContainmentPropertyName() is deprecated, and will be removed in the next release. Use SDO_Model_ReflectionDataObject::getContainmentProperty()");		
	
	my_object = sdo_do_get_instance(getThis() TSRMLS_CC);
	if (my_object == (sdo_do_object *)NULL) {
		php_error(E_ERROR, "%s:%i: object is not in object store", CLASS_NAME, __LINE__);
		RETURN_NULL();
	}
	
	try {
		RETVAL_STRING((char *)my_object->dop->getContainmentProperty().getName(), 1);
	} catch (SDOPropertyNotFoundException e) {
		/* In this case the C++ API means we must catch an exception on the normal path */
		RETVAL_NULL();
	} catch (SDORuntimeException e) {
		sdo_throw_runtimeexception(&e TSRMLS_CC);
	}
}
/* }}} */

/* {{{ SDO_DataObjectImpl::clear
 */
PHP_METHOD(SDO_DataObjectImpl, clear)
{
	sdo_do_object	*my_object;
	
	my_object = sdo_do_get_instance(getThis() TSRMLS_CC);
	if (my_object == (sdo_do_object *) NULL) {
		php_error(E_ERROR, "%s:%i: object is not in object store", CLASS_NAME, __LINE__);
	} else {
		try {
			my_object->dop->clear();
		} catch (SDORuntimeException e){
			sdo_throw_runtimeexception(&e TSRMLS_CC);
		}
	}
}
/* }}} */

/* {{{ SDO_DataObjectImpl::getType
 */
PHP_METHOD(SDO_DataObjectImpl, getType)
{
	sdo_do_object *my_object;
	
	php_error(E_WARNING, 
		"SDO_DataObject::getType() is deprecated, and will be removed in the next release. Use SDO_Model_ReflectionDataObject::getType()");
		
	my_object = sdo_do_get_instance(getThis() TSRMLS_CC);
	if (my_object == (sdo_do_object *)NULL) {
		php_error(E_ERROR, "%s:%i: object is not in object store", CLASS_NAME, __LINE__);
		RETURN_NULL();
	}
	
	try {
		const Type& type = my_object->dop->getType();
		
		/* return the type as a PHP array */
		array_init(return_value);
		add_next_index_string(return_value, (char *)type.getURI(), 1);	
		add_next_index_string(return_value, (char *)type.getName(), 1);
	} catch (SDORuntimeException e) {
		sdo_throw_runtimeexception(&e TSRMLS_CC);
	}
}
/* }}} */

/* {{{ SDO_DataObjectImpl::createDataObject
 */
PHP_METHOD(SDO_DataObjectImpl, createDataObject)
{	
	zval				*z_property;
	const char			*xpath;
	sdo_do_object		*my_object;
	DataObjectPtr		 dop, new_dop;
	zval				*z_new_do;

	if (zend_parse_parameters(ZEND_NUM_ARGS() TSRMLS_CC, "z", &z_property) == FAILURE) 
		return;

	my_object = sdo_do_get_instance(getThis() TSRMLS_CC);
	if (my_object == (sdo_do_object *)NULL) {
		php_error(E_ERROR, "%s:%i: object is not in object store", CLASS_NAME, __LINE__);
		return;
	}
	
	dop = my_object->dop;	
	
	try {	
		if (sdo_parse_offset_param(dop, z_property, NULL, &xpath, ! dop->getType().isOpenType(), 0 TSRMLS_CC) == FAILURE) {
			return;
		}

		new_dop = dop->createDataObject(xpath);

	} catch (SDORuntimeException e) {
		sdo_throw_runtimeexception(&e TSRMLS_CC);
		return;
	}
	
	/* We have a good data object, so set up its PHP incarnation */
	MAKE_STD_ZVAL(z_new_do);
	sdo_do_new(z_new_do, new_dop TSRMLS_CC);
	RETVAL_ZVAL(z_new_do, 1, 0);
}
/* }}} */

/* {{{ SDO_DataObjectImpl::getSequence
 */
PHP_METHOD(SDO_DataObjectImpl, getSequence)
{
	sdo_do_object	*my_object;
	DataObjectPtr	 dop;

	if (ZEND_NUM_ARGS() != 0) {
		WRONG_PARAM_COUNT;
	}

	my_object = sdo_do_get_instance(getThis() TSRMLS_CC);
	if (my_object == (sdo_do_object *)NULL) {
		php_error(E_ERROR, "%s:%i: object is not in object store", CLASS_NAME, __LINE__);
		RETURN_NULL();
	}

	dop = my_object->dop;	
	
	try {
		SequencePtr seqp = dop->getSequence();
		if (!seqp) {
			RETVAL_NULL();
		} else {			
			/* Create the PHP wrapper */
			sdo_sequence_new (return_value, seqp, dop TSRMLS_CC);
		}
	} catch (SDORuntimeException e) {
		sdo_throw_runtimeexception(&e TSRMLS_CC);
	}
}
/* }}} */

/* {{{ SDO_DataObjectImpl::getChangeSummary
 */
PHP_METHOD(SDO_DataObjectImpl, getChangeSummary)
{	
	sdo_do_object	*my_object;
	DataObjectPtr		 dop;

	if (ZEND_NUM_ARGS() != 0) {
		WRONG_PARAM_COUNT;
	}

	my_object = sdo_do_get_instance(getThis() TSRMLS_CC);
	if (my_object == (sdo_do_object *)NULL) {
		php_error(E_ERROR, "%s:%i: object is not in object store", CLASS_NAME, __LINE__);
		RETURN_NULL();
	}
	
	dop = my_object->dop;	
	
	try {
		ChangeSummaryPtr change_summary = dop->getChangeSummary();
		if (change_summary == NULL)  {
			RETVAL_NULL();
		} else {			
			/* Create the PHP wrapper */
			sdo_das_changesummary_new (return_value, change_summary TSRMLS_CC);
		}
	} catch (SDORuntimeException e) {
		sdo_throw_runtimeexception(&e TSRMLS_CC);
	}
}
/* }}} */

/* {{{ SDO_DataObjectImpl::__get
 */
PHP_METHOD(SDO_DataObjectImpl, __get)
{
	zval *index, *value;
	
	if (zend_parse_parameters(ZEND_NUM_ARGS() TSRMLS_CC, "z", &index) == FAILURE) {
		return;
	}
	value = sdo_do_read_dimension(getThis(), index, BP_VAR_R TSRMLS_CC);
	RETVAL_ZVAL(value, 1, 0);
}
/* }}} */

/* {{{ SDO_DataObjectImpl::__set
 */
PHP_METHOD(SDO_DataObjectImpl, __set)
{
	zval *index, *value;
	
	if (zend_parse_parameters(ZEND_NUM_ARGS() TSRMLS_CC, "zz", &index, &value) == FAILURE) {
		return;
	}
	sdo_do_write_dimension(getThis(), index, value TSRMLS_CC);
}
/* }}} */

/* {{{ SDO_DataObjectImpl::count
 */
PHP_METHOD(SDO_DataObjectImpl, count)
{
	long count = 0;
	
	sdo_do_count_elements(getThis(), &count TSRMLS_CC);
	RETVAL_LONG(count);
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
