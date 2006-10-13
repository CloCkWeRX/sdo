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
#include "zend_config.w32.h"
#endif


#include "php.h"
#include "zend_exceptions.h"

#include "php_sdo_int.h"

#define CLASS_NAME "SDO_Model_Property"

/* {{{ sdo_model_property_object
 * The instance data for this class - extends the standard zend_object
 */
typedef struct {
	zend_object		 zo;			/* The standard zend_object */
	const Property  *propertyp;		/* The sdo4cpp Property */
} sdo_model_property_object;
/* }}} */

static zend_object_handlers sdo_model_property_object_handlers;

/* {{{ sdo_model_property_get_instance
 */
static sdo_model_property_object *sdo_model_property_get_instance(zval *me TSRMLS_DC) 
{
	return (sdo_model_property_object *)zend_object_store_get_object(me TSRMLS_CC);
}
/* }}} */

/* {{{ sdo_model_property_get_property
 */
const Property *sdo_model_property_get_property(zval *me TSRMLS_DC)
{
	sdo_model_property_object *my_object = (sdo_model_property_object *)zend_object_store_get_object(me TSRMLS_CC);
	return my_object->propertyp;
}
/* }}} */

/* {{{ sdo_model_property_object_free_storage
 */
static void sdo_model_property_object_free_storage(void *object TSRMLS_DC)
{
	sdo_model_property_object *my_object;

	my_object = (sdo_model_property_object *)object;
	zend_hash_destroy(my_object->zo.properties);
	FREE_HASHTABLE(my_object->zo.properties);

	if (my_object->zo.guards) {
	    zend_hash_destroy(my_object->zo.guards);
	    FREE_HASHTABLE(my_object->zo.guards);
	}

	my_object->propertyp = NULL;
	efree(my_object);
}
/* }}} */

/* {{{ sdo_model_property_object_create
 */
static zend_object_value sdo_model_property_object_create(zend_class_entry *ce TSRMLS_DC)
{
	zend_object_value retval;
	zval *tmp; /* this must be passed to hash_copy, but doesn't seem to be used */
	sdo_model_property_object *my_object;

	my_object = (sdo_model_property_object *)emalloc(sizeof(sdo_model_property_object));
	memset(my_object, 0, sizeof(sdo_model_property_object));
	my_object->zo.ce = ce;
	my_object->zo.guards = NULL;
	ALLOC_HASHTABLE(my_object->zo.properties);
	zend_hash_init(my_object->zo.properties, 0, NULL, ZVAL_PTR_DTOR, 0);
	zend_hash_copy(my_object->zo.properties, &ce->default_properties, (copy_ctor_func_t)zval_add_ref,
		(void *)&tmp, sizeof(zval *));
	retval.handle = zend_objects_store_put(my_object, NULL, sdo_model_property_object_free_storage, NULL TSRMLS_CC);
	retval.handlers = &sdo_model_property_object_handlers;

	return retval;
}
/* }}} */

/* {{{ sdo_model_property_new
 */
void sdo_model_property_new(zval *me, const Property *propertyp TSRMLS_DC)
{	
	sdo_model_property_object *my_object;
	char *class_name, *space;

	Z_TYPE_P(me) = IS_OBJECT;	
	if (object_init_ex(me, sdo_model_propertyimpl_class_entry) == FAILURE) {
		class_name = get_active_class_name(&space TSRMLS_CC);
		php_error(E_ERROR, "%s%s%s(): internal error (%i) - failed to instantiate %s object", 
			class_name, space, get_active_function_name(TSRMLS_C), __LINE__, CLASS_NAME);
		ZVAL_NULL(me);
		return;
	}

	my_object = (sdo_model_property_object *)zend_object_store_get_object(me TSRMLS_CC);
	my_object->propertyp = propertyp;
	zend_update_property_string(sdo_model_propertyimpl_class_entry, me, 
		"name", strlen("name"), (char *)propertyp->getName() TSRMLS_CC);
}
/* }}} */

/* {{{ sdo_model_property_get_default
*/
void sdo_model_property_get_default(const Property *propertyp, zval *return_value TSRMLS_DC) {
	uint		 bytes_len;
	char		*bytes_value;
	char		 char_value; 
	wchar_t		 wchar_value;
	char		*class_name, *space;

	try {
		switch(propertyp->getTypeEnum()) {
		case Type::OtherTypes:
			class_name = get_active_class_name(&space TSRMLS_CC);
			php_error(E_ERROR, "%s%s%s(): internal error (%i) - unexpected DataObject type 'OtherTypes'", 
				class_name, space, get_active_function_name(TSRMLS_C), __LINE__);
			break;
		case Type::BigDecimalType:
		case Type::BigIntegerType:
			bytes_len = propertyp->getDefaultLength();
			bytes_value = (char *)emalloc(1 + bytes_len);
			bytes_len = propertyp->getBytesDefault(bytes_value, bytes_len);
			bytes_value[bytes_len] = '\0';
			RETVAL_STRINGL(bytes_value, bytes_len, 0);
			break;
		case Type::BooleanType:
			RETVAL_BOOL(propertyp->getBooleanDefault());
			break;
		case Type::ByteType:
			RETVAL_LONG(propertyp->getByteDefault());
			break;
		case Type::BytesType:
			bytes_len = propertyp->getDefaultLength();
			bytes_value = (char *)emalloc(1 + bytes_len);
			bytes_len = propertyp->getBytesDefault(bytes_value, bytes_len);
			bytes_value[bytes_len] = '\0';
			RETVAL_STRINGL(bytes_value, bytes_len, 0);
			break;
		case Type::CharacterType:
			wchar_value = propertyp->getCharacterDefault();
			if (wchar_value > INT_MAX) {
				class_name = get_active_class_name(&space TSRMLS_CC);
				php_error(E_WARNING, "%s%s%s(): wide character data lost for '%s'", 
					class_name, space, get_active_function_name(TSRMLS_C), propertyp->getName());
			}
			char_value = propertyp->getByteDefault();
			RETVAL_STRINGL(&char_value, 1, 1);
			break;
		case Type::DateType:
			RETVAL_LONG(propertyp->getDateDefault().getTime());
			break;
		case Type::DoubleType:
			RETVAL_DOUBLE(propertyp->getDoubleDefault());
			break;
		case Type::FloatType:
			RETVAL_DOUBLE(propertyp->getFloatDefault());
			break;
		case Type::IntegerType:
			RETVAL_LONG(propertyp->getIntegerDefault());
			break;
		case Type::LongType:
			/* An SDO long (64 bits) may overflow a PHP int, so we return it as a string */
			bytes_len = propertyp->getDefaultLength();
			bytes_value = (char *)emalloc(1 + bytes_len);
			bytes_len = propertyp->getBytesDefault(bytes_value, bytes_len);
			bytes_value[bytes_len] = '\0';
			RETVAL_STRINGL(bytes_value, bytes_len, 0);
			/* TODO restore this code instead of the above once getCStringDefault is implemented */
			/* RETVAL_STRING((char *)property.getCStringDefault(), 1); */
			break;
		case Type::ShortType:
			RETVAL_LONG(propertyp->getShortDefault());
			break;
		case Type::StringType:
		case Type::UriType:
			bytes_len = propertyp->getDefaultLength();
			bytes_value = (char *)emalloc(1 + bytes_len);
			bytes_len = propertyp->getBytesDefault(bytes_value, bytes_len);
			bytes_value[bytes_len] = '\0';
			RETVAL_STRINGL(bytes_value, bytes_len, 0);
			/* TODO restore this code instead of the above once getCStringDefault is implemented */
			/* RETVAL_STRING((char *)property.getCStringDefault(), 1); */
			break;		
		case Type::DataObjectType:
			/* A data object type cannot have a default */
			RETVAL_NULL();
			break;
		case Type::ChangeSummaryType:
			class_name = get_active_class_name(&space TSRMLS_CC);
			php_error(E_ERROR, "%s%s%s(): internal error (%i) - unexpected type 'ChangeSummaryType' for property %s", 
				class_name, space, get_active_function_name(TSRMLS_C), __LINE__, propertyp->getName());
			break;
		case Type::TextType:
			class_name = get_active_class_name(&space TSRMLS_CC);
			php_error(E_ERROR, "%s%s%s(): internal error (%i) - unexpected type 'TextType' for property %s", 
				class_name, space, get_active_function_name(TSRMLS_C), __LINE__, propertyp->getName());
			break;
		default:
			class_name = get_active_class_name(&space TSRMLS_CC);
			php_error(E_ERROR, "%s%s%s(): internal error (%i) - unexpected DataObject type '%s' for property '%s'", 
				class_name, space, get_active_function_name(TSRMLS_C), __LINE__,
				propertyp->getType().getName(), propertyp->getName());
		}
	} catch (SDORuntimeException e) {
		sdo_throw_runtimeexception(&e TSRMLS_CC);
	}
}
/* }}} */

/* {{{ sdo_model_property_string
*/
void sdo_model_property_string (ostringstream& print_buf, const Property *propertyp, const char *old_indent TSRMLS_DC) 
{	
	zval z_default;
	const Type& type = propertyp->getType();

	char *indent = (char *)emalloc(strlen(old_indent) + 4 + 1);
	sprintf (indent, "%s    ", old_indent);
	
	print_buf << indent;

	if (propertyp->isReadOnly()) {
		print_buf << "<readOnly> ";
	}

	if (type.isDataObjectType() && !propertyp->isContainment()) {
		print_buf << "<reference";
		//if (!type.equals(propertyp->getContainingType())) {
		//	print_buf << " " << propertyp->getContainingType().getURI() << 
		//		":" << propertyp->getContainingType().getName();
		//}
		print_buf << "> ";
	}

	const Property *opposite_p = propertyp->getOpposite();
	if (opposite_p) {
		print_buf << "<opposite of " << opposite_p->getName() << "> ";
	}

	print_buf << type.getURI() << ":" << type.getName() << " $" << propertyp->getName();

	if (propertyp->isMany()) {
		print_buf << "[]";
	}

	/*TODO it would be preferable if sdo4cpp had a hasDefault() method.
	 * While it does not, we get a default value and only display it if it 
	 * seems to be a non-default default
	 */
	sdo_model_property_get_default(propertyp, &z_default TSRMLS_CC);
	switch (Z_TYPE(z_default)) {
	case IS_LONG:
		if (Z_LVAL(z_default) != 0) {
			print_buf << " = " << Z_LVAL(z_default);
		}
		break;
	case IS_DOUBLE:
		if (Z_DVAL(z_default) != 0.0) {
			print_buf << " = " << Z_DVAL(z_default);
		}
		break;
	case IS_BOOL:
		if (ZEND_TRUTH(Z_LVAL(z_default))) {
			print_buf << " = true";
		}
		break;
	case IS_STRING:
		if (! (Z_STRLEN(z_default) == 0 ||
			  (Z_STRLEN(z_default) == 1 && Z_STRVAL(z_default)[0] == '\0'))) {
			print_buf << " = '" << Z_STRVAL(z_default) << "'";
		}
		break;
	default:
		break;
	}
	zval_dtor(&z_default);

	if (type.isDataObjectType() && propertyp->isContainment()) {
		print_buf << " {";
		PropertyList pl = type.getProperties();
		for (int i = 0; i < pl.size(); i++) {
			sdo_model_property_string(print_buf, &pl[i], indent TSRMLS_CC);
		}
		print_buf << indent << "}";
	} else {
		print_buf << ";";
	}

	efree(indent);
}
/* }}} */

/* {{{ sdo_model_property_cast_object
*/ 
#if PHP_MAJOR_VERSION > 5 || (PHP_MAJOR_VERSION == 5 && PHP_MINOR_VERSION > 1)
static int sdo_model_property_cast_object(zval *readobj, zval *writeobj, int type TSRMLS_DC)
{
	int should_free = 0;
#else
static int sdo_model_property_cast_object(zval *readobj, zval *writeobj, int type, int should_free TSRMLS_DC)
{
#endif
	sdo_model_property_object *my_object;
	ostringstream print_buf;
	zval free_obj;
	int rc = SUCCESS;
	
	if (should_free) {
		free_obj = *writeobj;
	}
	
	my_object = sdo_model_property_get_instance(readobj TSRMLS_CC);
	try {			
		sdo_model_property_string (print_buf, my_object->propertyp, "\n" TSRMLS_CC);	
		std::string print_string = print_buf.str()/*.substr(0, SDO_TOSTRING_MAX)*/;	
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

/* {{{ sdo_model_property_minit
 */
void sdo_model_property_minit(zend_class_entry *tmp_ce TSRMLS_DC)
{
	tmp_ce->create_object = sdo_model_property_object_create;
	
	sdo_model_propertyimpl_class_entry = zend_register_internal_class(tmp_ce TSRMLS_CC);
	zend_declare_property_null (sdo_model_propertyimpl_class_entry, "name", strlen("name"), ZEND_ACC_PUBLIC TSRMLS_CC);
	zend_class_implements(sdo_model_propertyimpl_class_entry TSRMLS_CC, 1, sdo_model_property_class_entry);

	memcpy(&sdo_model_property_object_handlers, zend_get_std_object_handlers(), sizeof(zend_object_handlers));
	sdo_model_property_object_handlers.clone_obj = NULL;
	sdo_model_property_object_handlers.cast_object = sdo_model_property_cast_object;
}
/* }}} */

/* {{{ SDO_Model_PropertyImpl::__construct
 */
PHP_METHOD(SDO_Model_PropertyImpl, __construct)
{	
	char *class_name, *space;
	class_name = get_active_class_name(&space TSRMLS_CC);

	php_error(E_ERROR, "%s%s%s(): internal error - private constructor was called", 
		class_name, space, get_active_function_name(TSRMLS_C));
}
/* }}} */

/* {{{ SDO_Model_PropertyImpl::getName
 */
PHP_METHOD(SDO_Model_PropertyImpl, getName) 
{
	sdo_model_property_object	*my_object;
		
	if (ZEND_NUM_ARGS() != 0) {
		WRONG_PARAM_COUNT;
	}

	my_object = sdo_model_property_get_instance(getThis() TSRMLS_CC);
	try {
		RETVAL_STRING((char *)my_object->propertyp->getName(), 1);
	} catch (SDORuntimeException e) {
		sdo_throw_runtimeexception(&e TSRMLS_CC);
	}
}
/* }}} */

/* {{{ SDO_Model_PropertyImpl::getType
 */
PHP_METHOD(SDO_Model_PropertyImpl, getType) 
{
	sdo_model_property_object	*my_object;
		
	if (ZEND_NUM_ARGS() != 0) {
		WRONG_PARAM_COUNT;
	}

	my_object = sdo_model_property_get_instance(getThis() TSRMLS_CC);
	try {
		sdo_model_type_new (return_value, &my_object->propertyp->getType() TSRMLS_CC);
	} catch (SDORuntimeException e) {
		sdo_throw_runtimeexception(&e TSRMLS_CC);
	}
}
/* }}} */

/* {{{ SDO_Model_PropertyImpl::isMany
 */
PHP_METHOD(SDO_Model_PropertyImpl, isMany) 
{
	sdo_model_property_object	*my_object;
		
	if (ZEND_NUM_ARGS() != 0) {
		WRONG_PARAM_COUNT;
	}

	my_object = sdo_model_property_get_instance(getThis() TSRMLS_CC);
	try {
		RETVAL_BOOL(my_object->propertyp->isMany());
	} catch (SDORuntimeException e) {
		sdo_throw_runtimeexception(&e TSRMLS_CC);
	}
}
/* }}} */

/* {{{ SDO_Model_PropertyImpl::isReadOnly
 */
PHP_METHOD(SDO_Model_PropertyImpl, isReadOnly) 
{
	sdo_model_property_object	*my_object;
		
	if (ZEND_NUM_ARGS() != 0) {
		WRONG_PARAM_COUNT;
	}

	my_object = sdo_model_property_get_instance(getThis() TSRMLS_CC);
	try {
		RETVAL_BOOL(my_object->propertyp->isReadOnly());
	} catch (SDORuntimeException e) {
		sdo_throw_runtimeexception(&e TSRMLS_CC);
	}
}
/* }}} */

/* {{{ SDO_Model_PropertyImpl::isContainment
 */
PHP_METHOD(SDO_Model_PropertyImpl, isContainment) 
{
	sdo_model_property_object	*my_object;
		
	if (ZEND_NUM_ARGS() != 0) {
		WRONG_PARAM_COUNT;
	}

	my_object = sdo_model_property_get_instance(getThis() TSRMLS_CC);
	try {
		RETVAL_BOOL(my_object->propertyp->isContainment());
	} catch (SDORuntimeException e) {
		sdo_throw_runtimeexception(&e TSRMLS_CC);
	}
}
/* }}} */

/* {{{ SDO_Model_PropertyImpl::getOpposite
 */
PHP_METHOD(SDO_Model_PropertyImpl, getOpposite) 
{
	sdo_model_property_object	*my_object;
	const Property				*opposite_p;
		
	if (ZEND_NUM_ARGS() != 0) {
		WRONG_PARAM_COUNT;
	}

	my_object = sdo_model_property_get_instance(getThis() TSRMLS_CC);
	try {
		opposite_p = my_object->propertyp->getOpposite();
		if (opposite_p) {
			sdo_model_property_new (return_value, opposite_p TSRMLS_CC);
		}
	} catch (SDORuntimeException e) {
		sdo_throw_runtimeexception(&e TSRMLS_CC);
	}
}
/* }}} */

/* {{{ SDO_Model_PropertyImpl::getContainingType
 */
PHP_METHOD(SDO_Model_PropertyImpl, getContainingType) 
{
	sdo_model_property_object	*my_object;
	
	if (ZEND_NUM_ARGS() != 0) {
		WRONG_PARAM_COUNT;
	}
	
	my_object = sdo_model_property_get_instance(getThis() TSRMLS_CC);
	try {
		sdo_model_type_new (return_value, &my_object->propertyp->getContainingType() TSRMLS_CC);
	} catch (SDORuntimeException e) {
		sdo_throw_runtimeexception(&e TSRMLS_CC);
	}
}
/* }}} */

/* {{{ SDO_Model_PropertyImpl::getDefault
 */
PHP_METHOD(SDO_Model_PropertyImpl, getDefault) 
{
	sdo_model_property_object	*my_object;
	
	if (ZEND_NUM_ARGS() != 0) {
		WRONG_PARAM_COUNT;
	}
	
	my_object = sdo_model_property_get_instance(getThis() TSRMLS_CC);
	sdo_model_property_get_default (my_object->propertyp, return_value TSRMLS_CC);
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
