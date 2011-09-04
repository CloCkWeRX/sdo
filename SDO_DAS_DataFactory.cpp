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
static char rcs_id[] = "$Id: SDO_DAS_DataFactory.cpp 234945 2007-05-04 15:05:53Z mfp $";

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

	SDO_DEBUG_FREE(object);

	zend_hash_destroy(my_object->zo.properties);
	FREE_HASHTABLE(my_object->zo.properties);
	if (my_object->zo.guards) {
	    zend_hash_destroy(my_object->zo.guards);
	    FREE_HASHTABLE(my_object->zo.guards);
	}
	my_object->dfp = NULL;
	efree(object);
}
/* }}} */

/* {{{ debug macro functions
 */
SDO_DEBUG_ADDREF(das_df)
SDO_DEBUG_DELREF(das_df)
SDO_DEBUG_DESTROY(das_df)
/* }}} */

/* {{{ sdo_das_df_object_create
 */
static zend_object_value sdo_das_df_object_create(zend_class_entry *ce TSRMLS_DC)
{
	zend_object_value retval;
	zval *tmp; /* this must be passed to hash_copy, but doesn't seem to be used */
	sdo_das_df_object *my_object;

	my_object = (sdo_das_df_object *)emalloc(sizeof(sdo_das_df_object));
	memset(my_object, 0, sizeof(sdo_das_df_object));
	my_object->zo.ce = ce;
	my_object->zo.guards = NULL;
	ALLOC_HASHTABLE(my_object->zo.properties);
	zend_hash_init(my_object->zo.properties, 0, NULL, ZVAL_PTR_DTOR, 0);
	zend_hash_copy(my_object->zo.properties, &ce->default_properties, (copy_ctor_func_t)zval_add_ref,
		(void *)&tmp, sizeof(zval *));
	retval.handle = zend_objects_store_put(my_object, SDO_FUNC_DESTROY(das_df), sdo_das_df_object_free_storage, NULL TSRMLS_CC);
	retval.handlers = &sdo_das_df_object_handlers;
	SDO_DEBUG_ALLOCATE(retval.handle, my_object);

	return retval;
}
/* }}} */

/* {{{ sdo_das_df_get
 * utility function for DAS implementations
 */
DataFactoryPtr sdo_das_df_get(zval *me TSRMLS_DC)
{
	sdo_das_df_object *my_object;

	my_object = (sdo_das_df_object *)zend_object_store_get_object(me TSRMLS_CC);
	return my_object->dfp;
}
/* }}} */

/* {{{ sdo_das_df_new
 * take as arguments 1. a pointer field for a zval and 2. a pointer to a C++ data factory object
 * create a PHP SDO_DAS_DataFactory object, point the first argument at it, and push into it
 * the second argument
 */
void sdo_das_df_new(zval *me, DataFactoryPtr dfp TSRMLS_DC)
{
	sdo_das_df_object *my_object;
	char *class_name, *space;

	Z_TYPE_P(me) = IS_OBJECT;
	if (object_init_ex(me, sdo_das_datafactoryimpl_class_entry) == FAILURE) {
		class_name = get_active_class_name(&space TSRMLS_CC);
		php_error(E_ERROR, "%s%s%s(): internal error (%i) - failed to instantiate %s object",
			class_name, space, get_active_function_name(TSRMLS_C), __LINE__, CLASS_NAME);
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
	sdo_das_df_object_handlers.add_ref = SDO_FUNC_ADDREF(das_df);
	sdo_das_df_object_handlers.del_ref = SDO_FUNC_DELREF(das_df);
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
		sdo_das_df_new (return_value, dfp TSRMLS_CC);
	} catch (SDORuntimeException e) {
		sdo_throw_runtimeexception(&e TSRMLS_CC);
	}

}
/* }}} */

/* {{{ SDO_DAS_DataFactoryImpl::create
 */
PHP_METHOD(SDO_DAS_DataFactoryImpl, create)
{
	sdo_das_df_object *my_object;
	char	*type_uri;
	int		 type_uri_len;
	char	*type_name;
	int		 type_name_len;

	DataObjectPtr dop;

	if (zend_parse_parameters(ZEND_NUM_ARGS() TSRMLS_CC, "ss",
		&type_uri, &type_uri_len, &type_name, &type_name_len) == FAILURE)
		return;

	my_object = sdo_das_df_get_instance(getThis() TSRMLS_CC);
	try {
		dop = my_object->dfp->create(type_uri, type_name);
		/* We have a good data object, so set up its PHP incarnation */
		sdo_do_new(return_value, dop TSRMLS_CC);
	} catch (SDORuntimeException e) {
		sdo_throw_runtimeexception(&e TSRMLS_CC);
	}
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
	char       *type_uri;
	int         type_uri_len;
	char       *type_name;
	int         type_name_len;

	/* optional parameters with default values */
	zend_bool	is_sequenced = false;
	zend_bool	is_open = false;
	zend_bool   is_abstract = false;
	zend_bool   is_derived = false;

	/* optional array of optional argumants */
	zval	  *args = NULL;
	HashTable *args_hash;
	HashPosition args_pointer;
	zval	 **value;
	char	  *key_string;
	uint	   key_string_len;
	ulong	   key_index;

	/* and a further array for the basetype values */
	HashTable *basetype_hash;
	zval **z_basetype_uri;
	zval **z_basetype_name;
	char *basetype_uri;
	char *basetype_name;

	char *class_name, *space;

	if (ZEND_NUM_ARGS() < 2)
		WRONG_PARAM_COUNT;

	if (zend_parse_parameters(ZEND_NUM_ARGS() TSRMLS_CC, "ss|a",
		&type_uri, &type_uri_len, &type_name, &type_name_len,
		&args) == FAILURE) {
		return;
	}

	if (args != NULL) {
		args_hash = Z_ARRVAL_P(args);

		for (zend_hash_internal_pointer_reset_ex(args_hash, &args_pointer);
		zend_hash_get_current_data_ex(args_hash, (void **)&value, &args_pointer) == SUCCESS;
		zend_hash_move_forward_ex(args_hash, &args_pointer)) {
			if (zend_hash_get_current_key_ex(
				args_hash, &key_string, &key_string_len, &key_index, 0, &args_pointer) == HASH_KEY_IS_STRING) {

				if (strcmp(key_string, "sequenced") == SUCCESS) {
					is_sequenced = sdo_get_boolean_value (*value);
					continue;
				}

				if (strcmp(key_string, "open") == SUCCESS) {
					is_open = sdo_get_boolean_value (*value);
					continue;
				}

				if (strcmp(key_string, "abstract") == SUCCESS) {
					is_abstract = sdo_get_boolean_value (*value);
					continue;
				}

				if (strcmp(key_string, "basetype") == SUCCESS) {
				/* If the basetype option is present, its value
				* is an array of type uri and type name.
					*/
					is_derived = true;
					if (Z_TYPE_PP(value) != IS_ARRAY) {
						class_name = get_active_class_name(&space TSRMLS_CC);
						sdo_throw_exception_ex (sdo_unsupportedoperationexception_class_entry, 0, 0 TSRMLS_CC,
							"%s%s%s(): %s argument should be an array",
							class_name, space, get_active_function_name(TSRMLS_C), key_string);
						return;
					}

					basetype_hash = Z_ARRVAL_PP(value);
					if (zend_hash_num_elements(basetype_hash) != 2) {
						class_name = get_active_class_name(&space TSRMLS_CC);
						sdo_throw_exception_ex (sdo_unsupportedoperationexception_class_entry, 0, 0 TSRMLS_CC,
							"%s%s%s(): %s array should have exactly two elements",
							class_name, space, get_active_function_name(TSRMLS_C), key_string);
						return;
					}

					if (zend_hash_index_find(basetype_hash, 0, (void **)&z_basetype_uri) == SUCCESS &&
						Z_TYPE_PP(z_basetype_uri) == IS_STRING) {
						basetype_uri = Z_STRVAL_PP(z_basetype_uri);
					} else {
						class_name = get_active_class_name(&space TSRMLS_CC);
						sdo_throw_exception_ex (sdo_unsupportedoperationexception_class_entry, 0, 0 TSRMLS_CC,
							"%s%s%s(): invalid value for %s type namespace URI",
							class_name, space, get_active_function_name(TSRMLS_C), key_string);
						return;
					}

					if (zend_hash_index_find(basetype_hash, 1, (void **)&z_basetype_name) == SUCCESS&&
						Z_TYPE_PP(z_basetype_name) == IS_STRING) {
						basetype_name = Z_STRVAL_PP(z_basetype_name);
					} else {
						class_name = get_active_class_name(&space TSRMLS_CC);
						sdo_throw_exception_ex (sdo_unsupportedoperationexception_class_entry, 0, 0 TSRMLS_CC,
							"%s%s%s(): invalid value for %s type name",
							class_name, space, get_active_function_name(TSRMLS_C), key_string);
						return;
					}
					continue;
				} 	/* end basetype */

				class_name = get_active_class_name(&space TSRMLS_CC);
				sdo_throw_exception_ex (sdo_unsupportedoperationexception_class_entry, 0, 0 TSRMLS_CC,
					"%s%s%s(): unrecognized option %s in parameter array",
					class_name, space, get_active_function_name(TSRMLS_C), key_string);

			} else {
				class_name = get_active_class_name(&space TSRMLS_CC);
				sdo_throw_exception_ex (sdo_unsupportedoperationexception_class_entry, 0, 0 TSRMLS_CC,
					"%s%s%s(): option name must be a string in parameter array #%i",
					class_name, space, get_active_function_name(TSRMLS_C), key_index);
			}
		}
	}

	my_object = sdo_das_df_get_instance(getThis() TSRMLS_CC);

	try {
		my_object->dfp->addType(type_uri, type_name,
			ZEND_TRUTH(is_sequenced), ZEND_TRUTH(is_open), ZEND_TRUTH(is_abstract));

		if (is_derived) {
			my_object->dfp->setBaseType(type_uri, type_name, basetype_uri, basetype_name);
		}
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
	char      *parent_type_uri;
	int        parent_type_uri_len;
	char      *parent_type_name;
	int        parent_type_name_len;

	char      *property_name;
	int        property_name_len;

	char      *type_uri;
	int        type_uri_len;
	char      *type_name;
	int        type_name_len;

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
	zend_bool  read_only = false;
	zend_bool  containment = true;

	zval	  *default_value = NULL;

	sdo_das_df_object *my_object;
	char	*class_name, *space;

	if (ZEND_NUM_ARGS() < 5)
		WRONG_PARAM_COUNT;

	if (zend_parse_parameters(ZEND_NUM_ARGS() TSRMLS_CC, "sssss|a",
		&parent_type_uri, &parent_type_uri_len, &parent_type_name, &parent_type_name_len,
		&property_name, &property_name_len,
		&type_uri, &type_uri_len, &type_name, &type_name_len,
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
					continue;
				}
				if (strcmp(key_string, "readonly") == SUCCESS) {
					read_only = sdo_get_boolean_value (*value);
					class_name = get_active_class_name(&space TSRMLS_CC);
					php_error(E_WARNING, "%s%s%s(): option %s not yet implemented",
						class_name, space, get_active_function_name(TSRMLS_C), key_string);
					continue;
				}
				if (strcmp(key_string, "containment") == SUCCESS) {
					containment = sdo_get_boolean_value (*value);
					continue;
				}
				if (strcmp(key_string, "opposite") == SUCCESS) {
					class_name = get_active_class_name(&space TSRMLS_CC);
					php_error(E_WARNING, "%s%s%s(): option %s not yet implemented",
						class_name, space, get_active_function_name(TSRMLS_C), key_string);
					continue;
				}
				if (strcmp(key_string, "default") == SUCCESS) {
					default_value = *value;
					continue;
				}

				sdo_throw_exception_ex (sdo_unsupportedoperationexception_class_entry, 0, 0 TSRMLS_CC,
					"%s::%s: unrecognized option %s in parameter array",
					CLASS_NAME, get_active_function_name(TSRMLS_C), key_string);

			} else {
				sdo_throw_exception_ex (sdo_unsupportedoperationexception_class_entry, 0, 0 TSRMLS_CC,
					"%s::%s: option name must be a string in parameter array #%i",
					CLASS_NAME, get_active_function_name(TSRMLS_C), key_index);
			}

		}
	}

	my_object = sdo_das_df_get_instance(getThis() TSRMLS_CC);
	try {
		my_object->dfp->addPropertyToType(parent_type_uri, parent_type_name, property_name, type_uri, type_name,
			ZEND_TRUTH(many), ZEND_TRUTH(read_only), ZEND_TRUTH(containment));

		if (default_value) {
			if (many) {
				zend_throw_exception_ex(sdo_unsupportedoperationexception_class_entry, 0 TSRMLS_CC,
					"multivalued property %s cannot have a default value", property_name);
			} else if (Z_TYPE_P(default_value) == IS_NULL) {
				zend_throw_exception_ex(sdo_unsupportedoperationexception_class_entry, 0 TSRMLS_CC,
					"property %s cannot have a NULL default value", property_name);
			} else {
				/* copy the value so that the original is not modified */
				zval temp_zval = *default_value;
				zval_copy_ctor(&temp_zval);

				const Type& type = my_object->dfp->getType(type_uri, type_name);
				const Type& parentType = my_object->dfp->getType(parent_type_uri, parent_type_name);
				switch (type.getTypeEnum()) {
				case Type::OtherTypes:
					class_name = get_active_class_name(&space TSRMLS_CC);
					php_error(E_ERROR, "%s%s%s(): internal error (%i) - unexpected DataObject type 'OtherTypes'",
						class_name, space, get_active_function_name(TSRMLS_C), __LINE__);
					break;
				case Type::BigDecimalType:
				case Type::BigIntegerType:
					convert_to_string(&temp_zval);
					my_object->dfp->setDefault(parentType, property_name, Z_STRVAL(temp_zval));
					break;
				case Type::BooleanType:
					convert_to_boolean(&temp_zval);
					my_object->dfp->setDefault(parentType, property_name, (bool)ZEND_TRUTH(Z_BVAL(temp_zval)));
					break;
				case Type::ByteType:
					convert_to_long(&temp_zval);
					my_object->dfp->setDefault(parentType, property_name, (char)Z_LVAL(temp_zval));
					break;
				case Type::BytesType:
					convert_to_string(&temp_zval);
					my_object->dfp->setDefault(parentType, property_name, Z_STRVAL(temp_zval), Z_STRLEN(temp_zval));
					break;
				case Type::CharacterType:
					convert_to_string(&temp_zval);
					my_object->dfp->setDefault(parentType, property_name, (char)(Z_STRVAL(temp_zval)[0]));
					break;
				case Type::DateType:
					convert_to_long(&temp_zval);
					my_object->dfp->setDefault(parentType, property_name, (SDODate)Z_LVAL(temp_zval));
					break;
				case Type::DoubleType:
					convert_to_double(&temp_zval);
					/*TODO this may need more work. On Windows, simply omitting the cast, or casting to double,
					* leads to an ambiguous overloaded function message. But some platforms may baulk at long double.
					*/
					my_object->dfp->setDefault(parentType, property_name,  (long double)Z_DVAL(temp_zval));
					break;
				case Type::FloatType:
					convert_to_double(&temp_zval);
					my_object->dfp->setDefault(parentType, property_name, (float)Z_DVAL(temp_zval));
					break;
				case Type::IntegerType:
					convert_to_long(&temp_zval);
					my_object->dfp->setDefault(parentType, property_name, (long)Z_LVAL(temp_zval));
					break;
				case Type::LongType:
					if (Z_TYPE(temp_zval) == IS_LONG) {
						my_object->dfp->setDefault(parentType, property_name, (int64_t)Z_LVAL(temp_zval));
					} else {
						convert_to_string(&temp_zval);
						my_object->dfp->setDefault(parentType, property_name, Z_STRVAL(temp_zval));
					}
					break;
				case Type::ShortType:
					convert_to_long(&temp_zval);
					my_object->dfp->setDefault(parentType, property_name, (short)Z_LVAL(temp_zval));
					break;
				case Type::StringType:
				case Type::UriType:
				case Type::TextType:
					convert_to_string(&temp_zval);
					my_object->dfp->setDefault(parentType, property_name, Z_STRVAL(temp_zval));
					break;
				case Type::DataObjectType:
				case Type::OpenDataObjectType:			
					zend_throw_exception_ex(sdo_unsupportedoperationexception_class_entry, 0 TSRMLS_CC,
						"DataObject property %s cannot have a default value",
						property_name);
					break;
				case Type::ChangeSummaryType:
					zend_throw_exception_ex(sdo_unsupportedoperationexception_class_entry, 0 TSRMLS_CC,
						"ChangeSummary property %s cannot have a default value",
						property_name);
					break;
				default:
					class_name = get_active_class_name(&space TSRMLS_CC);
					php_error(E_ERROR, "%s%s%s(): internal error (%i) - unexpected DataObject type '%s' for property '%s'",
						class_name, space, get_active_function_name(TSRMLS_C), __LINE__,
						type.getName(), property_name);
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
