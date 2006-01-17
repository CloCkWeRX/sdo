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

#define CLASS_NAME "SDO_DAS_DataFactory"

/* {{{ sdo_das_df_object
 * The instance data for this class - extends the standard zend_object
 */
typedef struct {
	zend_object			 zo;
	DataFactoryPtr		dfp;
} sdo_das_df_object;
/* }}} */

static zend_object_handlers sdo_das_df_object_handlers;

/* {{{ sdo_das_df_get_instance
 */
static sdo_das_df_object *sdo_das_df_get_instance(zval *me TSRMLS_DC) 
{
	return (sdo_das_df_object *)zend_object_store_get_object(me TSRMLS_CC);
}
/* }}} */

/* {{{ sdo_das_df_object_free_storage
 */
static void sdo_das_df_object_free_storage(void *object TSRMLS_DC)
{
	sdo_das_df_object *my_object;

	my_object = (sdo_das_df_object *)object;

	zend_hash_destroy(my_object->zo.properties);
	FREE_HASHTABLE(my_object->zo.properties);

	efree(object);
}
/* }}} */

/* {{{ sdo_das_df_object_create
 */
static zend_object_value sdo_das_df_object_create(zend_class_entry *ce TSRMLS_DC)
{
	zend_object_value retval;
	zval *tmp; /* this must be passed to hash_copy, but doesn't seem to be used */
	sdo_das_df_object *intern;

	intern = (sdo_das_df_object *)emalloc(sizeof(sdo_das_df_object));
	memset(intern, 0, sizeof(sdo_das_df_object));
	intern->zo.ce = ce;
	ALLOC_HASHTABLE(intern->zo.properties);
	zend_hash_init(intern->zo.properties, 0, NULL, ZVAL_PTR_DTOR, 0);
	zend_hash_copy(intern->zo.properties, &ce->default_properties, (copy_ctor_func_t)zval_add_ref,
		(void *)&tmp, sizeof(zval *));
	retval.handle = zend_objects_store_put(intern, NULL, sdo_das_df_object_free_storage, NULL TSRMLS_CC);
	retval.handlers = &sdo_das_df_object_handlers;

	return retval;
}
/* }}} */

/* {{{ sdo_das_df_new
 */
void sdo_das_df_new(zval *me, DataFactoryPtr dfp TSRMLS_DC)
{	
	sdo_das_df_object *my_object;

	Z_TYPE_P(me) = IS_OBJECT;	
	if (object_init_ex(me, sdo_das_datafactoryimpl_class_entry) == FAILURE) {
		php_error(E_ERROR, "%s:%i: object_init failed", CLASS_NAME, __LINE__);
		return;
	}

	my_object = (sdo_das_df_object *)zend_object_store_get_object(me TSRMLS_CC);
	my_object->dfp = dfp;
}
/* }}} */

/* {{{ sdo_das_df_minit
 */
void sdo_das_df_minit(zend_class_entry *tmp_ce TSRMLS_DC) 
{
	tmp_ce->create_object = sdo_das_df_object_create;
	sdo_das_datafactoryimpl_class_entry = zend_register_internal_class(tmp_ce TSRMLS_CC);
	zend_class_implements(sdo_das_datafactoryimpl_class_entry TSRMLS_CC, 1, sdo_das_datafactory_class_entry);
	memcpy(&sdo_das_df_object_handlers, zend_get_std_object_handlers(), sizeof(zend_object_handlers));
	sdo_das_df_object_handlers.clone_obj = NULL;
}
/* }}} */

/* {{{ SDO_DAS_DataFactory::getDataFactory
 * This is a static factory method
 */
PHP_METHOD(SDO_DAS_DataFactory, getDataFactory)
{
	DataFactoryPtr dfp;
	
	if (ZEND_NUM_ARGS() != 0) {
		WRONG_PARAM_COUNT;
	}
	
	try {
		dfp = DataFactory::getDataFactory();
	} catch (SDORuntimeException e) {
		sdo_throw_runtimeexception(&e TSRMLS_CC);
		return;
	}
	
	sdo_das_df_new (return_value, dfp TSRMLS_CC);
}
/* }}} */

/* {{{ SDO_DAS_DataFactoryImpl::create
 */
PHP_METHOD(SDO_DAS_DataFactoryImpl, create) 
{
	sdo_das_df_object *my_object;
	char	*namespaceURI;
	int		*namespaceURI_len;
	char	*typeName;
	int		*typeName_len;
	zval	*z_new_do;
	
	DataObjectPtr dop;
		
	if (zend_parse_parameters(ZEND_NUM_ARGS() TSRMLS_CC, "ss", 
		&namespaceURI, &namespaceURI_len, &typeName, &typeName_len) == FAILURE) 
		return;
	
	my_object = sdo_das_df_get_instance(getThis() TSRMLS_CC);
	try {
		dop = my_object->dfp->create(namespaceURI, typeName);
	} catch (SDORuntimeException e) {
		sdo_throw_runtimeexception(&e TSRMLS_CC);
		return;
	}

	/* We have a good data object, so set up its PHP incarnation */
	MAKE_STD_ZVAL(z_new_do);
	sdo_do_new(z_new_do, dop TSRMLS_CC);
	RETVAL_ZVAL(z_new_do, 1, 0);
}
/* }}} */

/* {{{ */
static zend_bool sdo_get_boolean_value (zval *z_value) {
	
	zend_bool bool_value;

	if (Z_TYPE_P(z_value) == IS_BOOL) {
		bool_value = Z_BVAL_P(z_value);
	} else {
		zval temp_zval = *z_value;
		zval_copy_ctor(&temp_zval);
		convert_to_boolean(&temp_zval);
		bool_value = Z_BVAL(temp_zval);
		zval_dtor(&temp_zval);
	}
	return bool_value;
}
/* }}} */

/* {{{ SDO_DAS_DataFactoryImpl::addType
 * This is a static factory method
 */
PHP_METHOD(SDO_DAS_DataFactoryImpl, addType) 
{
	sdo_das_df_object *my_object;
	char       *namespaceURI;
	int         namespaceURI_len;
	char       *typeName;
	int         typeName_len;

	/* optional parameters with default values */
	zend_bool	sequenced = false;
	zend_bool	open = false;
	
	/* optional array of optional argumants */
	zval	  *args = NULL;
	HashTable *args_hash;
	HashPosition args_pointer;
	zval	 **value;
	char	  *key_string;
	uint	   key_string_len;
	ulong	   key_index;
	
	if (ZEND_NUM_ARGS() < 2)
		WRONG_PARAM_COUNT;

	if (zend_parse_parameters_ex(ZEND_PARSE_PARAMS_QUIET, ZEND_NUM_ARGS() TSRMLS_CC, "ssb|b",
		&namespaceURI, &namespaceURI_len, &typeName, &typeName_len,
		&sequenced, &open) == SUCCESS) {
		/* the old (deprecated) signature */
		php_error(E_WARNING, "use of deprecated signature for %s", get_active_function_name(TSRMLS_C));
	} else {
		if (zend_parse_parameters(ZEND_NUM_ARGS() TSRMLS_CC, "ss|a",
			&namespaceURI, &namespaceURI_len, &typeName, &typeName_len,
			&args) == FAILURE) {
			return;		
		} else if (args != NULL) {
			args_hash = Z_ARRVAL_P(args);
			
			for (zend_hash_internal_pointer_reset_ex(args_hash, &args_pointer);
			    zend_hash_get_current_data_ex(args_hash, (void **)&value, &args_pointer) == SUCCESS;
			    zend_hash_move_forward_ex(args_hash, &args_pointer)) {
				if (zend_hash_get_current_key_ex(
					args_hash, &key_string, &key_string_len, &key_index, 0, &args_pointer) == HASH_KEY_IS_STRING) {
					if (strcmp(key_string, "sequenced") == SUCCESS) {
						sequenced = sdo_get_boolean_value (*value);
					} else 
						if (strcmp(key_string, "open") == SUCCESS) {
							open = sdo_get_boolean_value (*value);		
					} else {	
						php_error(E_WARNING, "unrecognized option %s in parameter array for %s", 
							key_string, get_active_function_name(TSRMLS_C));						
					}
				} else {
					php_error(E_WARNING, "%i : option name must be a string in parameter array for %s", 
						key_index, get_active_function_name(TSRMLS_C));
				}
			} 
		} 
	}
	
	my_object = sdo_das_df_get_instance(getThis() TSRMLS_CC);

	try {
		my_object->dfp->addType(namespaceURI, typeName, ZEND_TRUTH(sequenced), ZEND_TRUTH(open));
	} catch (SDORuntimeException e) {
		sdo_throw_runtimeexception(&e TSRMLS_CC);
	}

	return;
}
/* }}} */

/* {{{ SDO_DAS_DataFactoryImpl::addPropertyToType
 * This is a static factory method
 */
PHP_METHOD(SDO_DAS_DataFactoryImpl, addPropertyToType) 
{
	char      *parent_namespaceURI;
	int        parent_namespaceURI_len;
	char      *parent_typeName;
	int        parent_typeName_len;

	char      *propertyName;
	int        propertyName_len;

	char      *namespaceURI;
	int        namespaceURI_len;
	char      *typeName;
	int        typeName_len;

	/* optional array of optional argumants */
	zval	  *args = NULL;
	HashTable *args_hash;
	HashPosition args_pointer;
	zval	 **value;
	char	  *key_string;
	uint	   key_string_len;
	ulong	   key_index;

	/* optional parameters with default values */
	zend_bool  many = false;
	zend_bool  readOnly = false;
	zend_bool  containment = true;

	zval	  *default_value = NULL;

	sdo_das_df_object *my_object;

	if (ZEND_NUM_ARGS() < 5)
		WRONG_PARAM_COUNT;

	if (zend_parse_parameters_ex(ZEND_PARSE_PARAMS_QUIET, ZEND_NUM_ARGS() TSRMLS_CC, "sssssb|bb",
		&parent_namespaceURI, &parent_namespaceURI_len, &parent_typeName, &parent_typeName_len,
		&propertyName, &propertyName_len, 
		&namespaceURI, &namespaceURI_len, &typeName, &typeName_len,
		&many, &readOnly, &containment) == SUCCESS) {
		/* the old (deprecated) signature */
		php_error(E_WARNING, "use of deprecated signature for %s", get_active_function_name(TSRMLS_C));
	} else {
		if (zend_parse_parameters(ZEND_NUM_ARGS() TSRMLS_CC, "sssss|a",
			&parent_namespaceURI, &parent_namespaceURI_len, &parent_typeName, &parent_typeName_len,
			&propertyName, &propertyName_len, 
			&namespaceURI, &namespaceURI_len, &typeName, &typeName_len,
			&args) == FAILURE) {
			return;		
		} else if (args != NULL) {
			args_hash = Z_ARRVAL_P(args);
			
			for (zend_hash_internal_pointer_reset_ex(args_hash, &args_pointer);
			    zend_hash_get_current_data_ex(args_hash, (void **)&value, &args_pointer) == SUCCESS;
			    zend_hash_move_forward_ex(args_hash, &args_pointer)) {
				if (zend_hash_get_current_key_ex(
					args_hash, &key_string, &key_string_len, &key_index, 0, &args_pointer) == HASH_KEY_IS_STRING) {
					if (strcmp(key_string, "many") == SUCCESS) {
						many = sdo_get_boolean_value (*value);
					} else 
						if (strcmp(key_string, "readonly") == SUCCESS) {
							readOnly = sdo_get_boolean_value (*value);
					} else 
						if (strcmp(key_string, "containment") == SUCCESS) {
							containment = sdo_get_boolean_value (*value);
					} else 
						if (strcmp(key_string, "opposite") == SUCCESS) {
							php_error(E_WARNING, "option %s not yet implemented for %s", 
								key_string, get_active_function_name(TSRMLS_C));	
					} else 
						if (strcmp(key_string, "default") == SUCCESS) {
							default_value = *value;				
					} else {	
						php_error(E_WARNING, "unrecognized option %s in parameter array for %s", 
							key_string, get_active_function_name(TSRMLS_C));						
					}
				} else {
					php_error(E_WARNING, "%i : option name must be a string in parameter array for %s", 
						key_index, get_active_function_name(TSRMLS_C));
				}
			} 
		} 
	}
		
	my_object = sdo_das_df_get_instance(getThis() TSRMLS_CC);
	try {
		my_object->dfp->addPropertyToType(parent_namespaceURI, parent_typeName, propertyName, namespaceURI, typeName,
			ZEND_TRUTH(many), ZEND_TRUTH(readOnly), ZEND_TRUTH(containment));
		
		if (default_value) {			 
			if (many) {
				zend_throw_exception_ex(sdo_unsupportedoperationexception_class_entry, 0 TSRMLS_CC, 
					"%s:%i: multivalued property %s cannot have a default value", CLASS_NAME, __LINE__, propertyName);
			} else if (Z_TYPE_P(default_value) == IS_NULL) {
				zend_throw_exception_ex(sdo_unsupportedoperationexception_class_entry, 0 TSRMLS_CC, 
					"%s:%i: property %s cannot have a NULL default value", CLASS_NAME, __LINE__, propertyName); 				
			} else {				
				/* copy the value so that the original is not modified */
				zval temp_zval = *default_value;			
				zval_copy_ctor(&temp_zval);
				
				const Type& type = my_object->dfp->getType(namespaceURI, typeName);
				const Type& parentType = my_object->dfp->getType(parent_namespaceURI, parent_typeName);
				switch (type.getTypeEnum()) {
				case Type::OtherTypes:
					php_error(E_ERROR, "%s:%i: unexpected DataObject type 'OtherTypes'", CLASS_NAME, __LINE__);
					break;
				case Type::BigDecimalType:
				case Type::BigIntegerType:
					convert_to_string(&temp_zval);
					my_object->dfp->setDefault(parentType, propertyName, Z_STRVAL(temp_zval));
					break;
				case Type::BooleanType:
					convert_to_boolean(&temp_zval);
					my_object->dfp->setDefault(parentType, propertyName, (bool)ZEND_TRUTH(Z_BVAL(temp_zval)));
					break;
				case Type::ByteType:
					convert_to_long(&temp_zval);
					my_object->dfp->setDefault(parentType, propertyName, (char)Z_LVAL(temp_zval));
					break;
				case Type::BytesType:
					convert_to_string(&temp_zval);
					my_object->dfp->setDefault(parentType, propertyName, Z_STRVAL(temp_zval), Z_STRLEN(temp_zval));
					break;
				case Type::CharacterType: 
					convert_to_string(&temp_zval);
					my_object->dfp->setDefault(parentType, propertyName, (char)(Z_STRVAL(temp_zval)[0]));
					break;
				case Type::DateType:
					convert_to_long(&temp_zval);
					my_object->dfp->setDefault(parentType, propertyName, (SDODate)Z_LVAL(temp_zval));
					break;
				case Type::DoubleType:
					convert_to_double(&temp_zval);
					/*TODO this may need more work. On Windows, simply omitting the cast, or casting to double, 
					* leads to an ambiguous overloaded function message. But some platforms may baulk at long double. 
					*/
					my_object->dfp->setDefault(parentType, propertyName,  (long double)Z_DVAL(temp_zval));
					break;
				case Type::FloatType:
					convert_to_double(&temp_zval);
					my_object->dfp->setDefault(parentType, propertyName, (float)Z_DVAL(temp_zval));
					break;
				case Type::IntegerType:
					convert_to_long(&temp_zval);
					my_object->dfp->setDefault(parentType, propertyName, (long)Z_LVAL(temp_zval));
					break;
				case Type::LongType:
					if (Z_TYPE(temp_zval) == IS_LONG) {
						my_object->dfp->setDefault(parentType, propertyName, (int64_t)Z_LVAL(temp_zval));
					} else {					
						convert_to_string(&temp_zval);
						my_object->dfp->setDefault(parentType, propertyName, Z_STRVAL(temp_zval));
					}
					break;
				case Type::ShortType:
					convert_to_long(&temp_zval);
					my_object->dfp->setDefault(parentType, propertyName, (short)Z_LVAL(temp_zval));
					break;
				case Type::StringType:
				case Type::UriType:
				case Type::TextType:
					convert_to_string(&temp_zval);
					my_object->dfp->setDefault(parentType, propertyName, Z_STRVAL(temp_zval));
					break;
				case Type::DataObjectType:
					zend_throw_exception_ex(sdo_unsupportedoperationexception_class_entry, 0 TSRMLS_CC, 
						"%s:%i: DataObject property %s cannot have a default value", CLASS_NAME, __LINE__, propertyName); 
					break;
				case Type::ChangeSummaryType:
					zend_throw_exception_ex(sdo_unsupportedoperationexception_class_entry, 0 TSRMLS_CC, 
						"%s:%i: ChangeSummary property %s cannot have a default value", CLASS_NAME, __LINE__, propertyName); 
					break;
				default:
					php_error(E_ERROR, "%s:%i: unexpected DataObject type '%s' for property '%s'", CLASS_NAME, __LINE__, 
						type.getName(), propertyName);
				}
				
				zval_dtor(&temp_zval);
			}
		}
		
	} catch (SDORuntimeException e) {
		sdo_throw_runtimeexception(&e TSRMLS_CC);
	}
	
	return;
}/* }}} */

/*
 * Local variables:
 * tab-width: 4
 * c-basic-offset: 4
 * End:
 * vim600: noet sw=4 ts=4 fdm=marker
 * vim<600: noet sw=4 ts=4
 */
