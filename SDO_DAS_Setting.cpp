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

#include "php_sdo_int.h"

#define CLASS_NAME "SDO_DAS_Setting"

/* {{{ sdo_das_setting_object
 * The instance data for this class - extends the standard zend_object
 */
typedef struct {
	zend_object		 zo;			/* The standard zend_object */
	Setting         *setting;
} sdo_das_setting_object;
/* }}} */

static zend_object_handlers sdo_das_setting_object_handlers;

/* {{{ sdo_das_setting_get_instance
 */
static sdo_das_setting_object *sdo_das_setting_get_instance(zval *me TSRMLS_DC)
{
	return (sdo_das_setting_object *)zend_object_store_get_object(me TSRMLS_CC);
}
/* }}} */

/* {{{ sdo_das_setting_object_free_storage
 */
static void sdo_das_setting_object_free_storage(void *object TSRMLS_DC)
{
	sdo_das_setting_object *my_object;

	my_object = (sdo_das_setting_object *)object;
	zend_hash_destroy(my_object->zo.properties);
	FREE_HASHTABLE(my_object->zo.properties);
	if (my_object->zo.guards) {
	    zend_hash_destroy(my_object->zo.guards);
	    FREE_HASHTABLE(my_object->zo.guards);
	}
	my_object->setting = NULL;
	efree(object);
}
/* }}} */

/* {{{ sdo_das_setting_object_create
 */
static zend_object_value sdo_das_setting_object_create(zend_class_entry *ce TSRMLS_DC)
{
	zend_object_value retval;
	zval *tmp; /* this must be passed to hash_copy, but doesn't seem to be used */
	sdo_das_setting_object *my_object;

	my_object = (sdo_das_setting_object *)emalloc(sizeof(sdo_das_setting_object));
	memset(my_object, 0, sizeof(sdo_das_setting_object));
	my_object->zo.ce = ce;
	my_object->zo.guards = NULL;
	ALLOC_HASHTABLE(my_object->zo.properties);
	zend_hash_init(my_object->zo.properties, 0, NULL, ZVAL_PTR_DTOR, 0);
	zend_hash_copy(my_object->zo.properties, &ce->default_properties, (copy_ctor_func_t)zval_add_ref,
		(void *)&tmp, sizeof(zval *));
	retval.handle = zend_objects_store_put(my_object, NULL, sdo_das_setting_object_free_storage, NULL TSRMLS_CC);
	retval.handlers = &sdo_das_setting_object_handlers;

	return retval;
}
/* }}} */

/* {{{ sdo_das_setting_new
 */
void sdo_das_setting_new(zval *me, Setting *setting TSRMLS_DC)
{
	sdo_das_setting_object *my_object;
	char *class_name, *space;

	Z_TYPE_P(me) = IS_OBJECT;
	if (object_init_ex(me, sdo_das_setting_class_entry) == FAILURE) {
		class_name = get_active_class_name(&space TSRMLS_CC);
		php_error(E_ERROR, "%s%s%s(): internal error (%i) - failed to instantiate %s object",
			class_name, space, get_active_function_name(TSRMLS_C), __LINE__, CLASS_NAME);
		return;
	}

	my_object = (sdo_das_setting_object *)zend_object_store_get_object(me TSRMLS_CC);
	my_object->setting = setting;
}
/* }}} */

/* {{{ sdo_das_setting_read_value
*/
static zval *sdo_das_setting_read_value (sdo_das_setting_object *my_object TSRMLS_DC)
{
	Setting		*setting = my_object->setting;
	uint		 bytes_len;
	char		*bytes_value;
	char		 char_value;
	wchar_t		 wchar_value;
	DataObjectPtr doh_value;
	zval		*return_value;
	char		*class_name, *space;

	MAKE_STD_ZVAL(return_value);
	try {
		if ((!setting->isSet()) || setting->isNull()) {
			RETVAL_NULL();
		} else {
			const Property& property = setting->getProperty();
			switch(property.getTypeEnum()) {
			case Type::OtherTypes:
				class_name = get_active_class_name(&space TSRMLS_CC);
				php_error(E_ERROR, "%s%s%s(): internal error (%i) - unexpected DataObject type 'OtherTypes'",
					class_name, space, get_active_function_name(TSRMLS_C), __LINE__);
				break;
			case Type::BigDecimalType:
			case Type::BigIntegerType:
				RETVAL_STRING((char *)setting->getCStringValue(), 1);
				break;
			case Type::BooleanType:
				RETVAL_BOOL(setting->getBooleanValue());
				break;
			case Type::ByteType:
				RETVAL_LONG(setting->getByteValue());
				break;
			case Type::BytesType:
				bytes_len = setting->getLength();
				bytes_value = (char *)emalloc(1 + bytes_len);
				bytes_len = setting->getBytesValue(bytes_value, bytes_len);
				bytes_value[bytes_len] = '\0';
				RETVAL_STRINGL(bytes_value, bytes_len, 0);
				break;
			case Type::CharacterType:
				wchar_value = setting->getCharacterValue();
				if (wchar_value > INT_MAX) {
					class_name = get_active_class_name(&space TSRMLS_CC);
					php_error(E_WARNING, "%s%s%s(): wide character data lost for '%s'",
						class_name, space, get_active_function_name(TSRMLS_C), property.getName());
				}
				char_value = setting->getByteValue();
				RETVAL_STRINGL(&char_value, 1, 1);
				break;
			case Type::DateType:
				RETVAL_LONG(setting->getDateValue().getTime());
				break;
			case Type::DoubleType:
				RETVAL_DOUBLE(setting->getDoubleValue());
				break;
			case Type::FloatType:
				RETVAL_DOUBLE(setting->getFloatValue());
				break;
			case Type::IntegerType:
				RETVAL_LONG(setting->getIntegerValue());
				break;
			case Type::LongType:
				/* An SDO long (64 bits) may overflow a PHP int, so we return it as a string */
				RETVAL_STRING((char *)setting->getCStringValue(), 1);
				break;
			case Type::ShortType:
				RETVAL_LONG(setting->getShortValue());
				break;
			case Type::StringType:
			case Type::UriType:
				RETVAL_STRING((char *)setting->getCStringValue(), 1);
				break;
			case Type::DataObjectType:
				doh_value = setting->getDataObjectValue();
				if (!doh_value) {
					/* An old value may legitimately be null */
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
				php_error(E_ERROR, "%s%s%s(): internal error (%i) - unexpected DataObject type '%s' for property '%s'",
					class_name, space, get_active_function_name(TSRMLS_C), __LINE__,
					property.getType().getName(), property.getName());
			}
		}
	} catch (SDORuntimeException e) {
		sdo_throw_runtimeexception(&e TSRMLS_CC);
	}
	return return_value;
}
/* }}} */

/* {{{ sdo_das_setting_get_properties
*/
static HashTable *sdo_das_setting_get_properties(zval *object TSRMLS_DC)
{
	sdo_das_setting_object	*my_object;
	zval					*tmp;
	Setting					*setting;
	const char				*property_name;

	my_object = sdo_das_setting_get_instance(object TSRMLS_CC);
	zend_hash_clean(my_object->zo.properties);

	try {
		setting = my_object->setting;
		property_name = setting->getProperty().getName();

		/* TODO if the setting is for a list element, we should
		* probably include the list index as part of the key
		*/

		tmp = sdo_das_setting_read_value(my_object TSRMLS_CC);

		zval_add_ref(&tmp);
		zend_hash_add(my_object->zo.properties, (char *)property_name, 1 + strlen(property_name),
				&tmp, sizeof(zval *), NULL);
	} catch (SDORuntimeException e) {
		sdo_throw_runtimeexception(&e TSRMLS_CC);
	}

	return my_object->zo.properties;
}
/* }}} */

/* {{{ sdo_das_setting_cast_object
*/
#if PHP_MAJOR_VERSION > 5 || (PHP_MAJOR_VERSION == 5 && PHP_MINOR_VERSION > 1)
static int sdo_das_setting_cast_object(zval *readobj, zval *writeobj, int type TSRMLS_DC)
{
	int should_free = 0;
#else
static int sdo_das_setting_cast_object(zval *readobj, zval *writeobj, int type, int should_free TSRMLS_DC)
{
#endif
	sdo_das_setting_object	*my_object;
	ostringstream	 print_buf;
	zval			 free_obj;
	int				 rc = SUCCESS;
	Setting			*setting;

	if (should_free) {
		free_obj = *writeobj;
	}

	my_object = sdo_das_setting_get_instance(readobj TSRMLS_CC);

	setting = my_object->setting;
	try {
		const Property& property = setting->getProperty();

		print_buf << "object(" << CLASS_NAME <<
			")#" << readobj->value.obj.handle;

		if (property.isMany()) {
			print_buf << '[' << setting->getIndex() << ']';
		}

		if (setting->isSet() && property.getType().isDataType()) {
			print_buf << "=>";
			if (setting->isNull()) {
				print_buf << "NULL";
			} else {
				print_buf << '\"' << setting->getCStringValue() << '\"';
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

/* {{{ sdo_das_setting_minit
 */
void sdo_das_setting_minit(zend_class_entry *tmp_ce TSRMLS_DC)
{
	tmp_ce->create_object = sdo_das_setting_object_create;
	sdo_das_setting_class_entry = zend_register_internal_class(tmp_ce TSRMLS_CC);

	memcpy(&sdo_das_setting_object_handlers, zend_get_std_object_handlers(), sizeof(zend_object_handlers));
	sdo_das_setting_object_handlers.clone_obj = NULL;
	sdo_das_setting_object_handlers.get_properties = sdo_das_setting_get_properties;
	sdo_das_setting_object_handlers.cast_object = sdo_das_setting_cast_object;
}
/* }}} */

/* {{{ SDO_DAS_Setting::getPropertyIndex
 */
PHP_METHOD(SDO_DAS_Setting, getPropertyIndex)
{
	sdo_das_setting_object *my_object;

	if (ZEND_NUM_ARGS() != 0) {
		WRONG_PARAM_COUNT;
	}

	my_object = sdo_das_setting_get_instance(getThis() TSRMLS_CC);

	try {
		const Property& property = my_object->setting->getProperty();
		RETVAL_LONG(property.getContainingType().getPropertyIndex(property.getName()));
	} catch (SDORuntimeException e) {
		sdo_throw_runtimeexception(&e TSRMLS_CC);
	}

	return;
}
/* }}} */

/* {{{ SDO_DAS_Setting::getPropertyName
 */
PHP_METHOD(SDO_DAS_Setting, getPropertyName)
{
	sdo_das_setting_object *my_object;

	if (ZEND_NUM_ARGS() != 0) {
		WRONG_PARAM_COUNT;
	}

	my_object = sdo_das_setting_get_instance(getThis() TSRMLS_CC);

	try {
		RETVAL_STRING((char *)my_object->setting->getProperty().getName(), 1);
	} catch (SDORuntimeException e) {
		sdo_throw_runtimeexception(&e TSRMLS_CC);
	}

	return;
}
/* }}} */

/* {{{ SDO_DAS_Setting::getValue
 */
PHP_METHOD(SDO_DAS_Setting, getValue)
{
	zval *value;
	sdo_das_setting_object *my_object;

	if (ZEND_NUM_ARGS() != 0) {
		WRONG_PARAM_COUNT;
	}

	my_object = sdo_das_setting_get_instance(getThis() TSRMLS_CC);

	value = sdo_das_setting_read_value(my_object TSRMLS_CC);
	RETVAL_ZVAL(value, 1, 0);

	return;
}
/* }}} */

/* {{{ SDO_DAS_Setting::getListIndex
 */
PHP_METHOD(SDO_DAS_Setting, getListIndex)
{
	sdo_das_setting_object *my_object;

	if (ZEND_NUM_ARGS() != 0) {
		WRONG_PARAM_COUNT;
	}

	my_object = sdo_das_setting_get_instance(getThis() TSRMLS_CC);

	try {
		if (my_object->setting->getProperty().isMany()) {
			RETVAL_LONG(my_object->setting->getIndex());
		} else {
			RETVAL_LONG(-1);
		}
	} catch (SDORuntimeException e) {
		sdo_throw_runtimeexception(&e TSRMLS_CC);
	}

	return;
}
/* }}} */

/* {{{ SDO_DAS_Setting::isSet
 */
PHP_METHOD(SDO_DAS_Setting, isSet)
{
	sdo_das_setting_object *my_object;

	if (ZEND_NUM_ARGS() != 0) {
		WRONG_PARAM_COUNT;
	}

	my_object = sdo_das_setting_get_instance(getThis() TSRMLS_CC);

	try {
		RETVAL_BOOL(my_object->setting->isSet());
	} catch (SDORuntimeException e) {
		sdo_throw_runtimeexception(&e TSRMLS_CC);
	}

	return;
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
