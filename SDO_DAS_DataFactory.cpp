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
	DASDataFactoryPtr	 dfp;
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
void sdo_das_df_new(zval *me, DASDataFactoryPtr dfp TSRMLS_DC)
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
 * This is s static factory method
 */
PHP_METHOD(SDO_DAS_DataFactory, getDataFactory)
{
	DASDataFactoryPtr dfp;
	
	if (ZEND_NUM_ARGS() != 0) {
		WRONG_PARAM_COUNT;
	}
	
	try {
		dfp = DASDataFactory::getDataFactory();
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

/* {{{ SDO_DAS_DataFactoryImpl::addType
 * This is s static factory method
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
	
	if (zend_parse_parameters(ZEND_NUM_ARGS() TSRMLS_CC, "ss|bb", 
		    &namespaceURI, &namespaceURI_len, &typeName, &typeName_len, &sequenced, &open) == FAILURE) 
		return;
	
	my_object = sdo_das_df_get_instance(getThis() TSRMLS_CC);

	try {
		my_object->dfp->addType(namespaceURI, typeName, ZEND_TRUTH(sequenced), ZEND_TRUTH(open));
	} catch (SDORuntimeException e) {
		sdo_throw_runtimeexception(&e TSRMLS_CC);
	}

	/* return the type as a PHP array */
	array_init(return_value);
	add_assoc_string(return_value, SDO_NAMESPACE_URI, namespaceURI, 1);	
	add_assoc_string(return_value, SDO_TYPE_NAME, typeName, 1);

	return;
}
/* }}} */

/* {{{ SDO_DAS_DataFactoryImpl::addPropertyToType
 * This is s static factory method
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

	/* optional parameters with default values */
	zend_bool  many = false;
	zend_bool  readOnly = false;
	zend_bool  containment = true;

	sdo_das_df_object *my_object;

	if (zend_parse_parameters(ZEND_NUM_ARGS() TSRMLS_CC, "sssss|bbb", 
		&parent_namespaceURI, &parent_namespaceURI_len, &parent_typeName, &parent_typeName_len,
		&propertyName, &propertyName_len, 
		&namespaceURI, &namespaceURI_len, &typeName, &typeName_len,
		&many, &readOnly, &containment) == FAILURE) 
		return;
		
	my_object = sdo_das_df_get_instance(getThis() TSRMLS_CC);
	try {
		my_object->dfp->addPropertyToType(parent_namespaceURI, parent_typeName, propertyName, namespaceURI, typeName,
		ZEND_TRUTH(many), ZEND_TRUTH(readOnly), ZEND_TRUTH(containment));
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
