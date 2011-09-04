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
static char rcs_id[] = "$Id: SDO_DAS_ChangeSummary.cpp 234945 2007-05-04 15:05:53Z mfp $";

#ifdef PHP_WIN32
#include <iostream>
#include <math.h>
#include "zend_config.w32.h"
#endif

#include "php.h"

#include "php_sdo_int.h"

#define CLASS_NAME "SDO_DAS_ChangeSummary"

/* {{{ sdo_das_changesummary_object
 * The instance data for this class - extends the standard zend_object
 */
typedef struct {
	zend_object		 zo;			/* The standard zend_object */
	ChangeSummaryPtr change_summary;
} sdo_das_changesummary_object;
/* }}} */

/* {{{ sdo_das_changesummary_get_instance
 */
static sdo_das_changesummary_object *sdo_das_changesummary_get_instance(zval *me TSRMLS_DC) 
{
	return (sdo_das_changesummary_object *)zend_object_store_get_object(me TSRMLS_CC);
}
/* }}} */

/* {{{ sdo_das_changesummary_object_free_storage
 */
static void sdo_das_changesummary_object_free_storage(void *object TSRMLS_DC)
{
	sdo_das_changesummary_object *my_object;

	SDO_DEBUG_FREE(object);
	
	my_object = (sdo_das_changesummary_object *)object;
	zend_hash_destroy(my_object->zo.properties);
	FREE_HASHTABLE(my_object->zo.properties);
	if (my_object->zo.guards) {
	    zend_hash_destroy(my_object->zo.guards);
	    FREE_HASHTABLE(my_object->zo.guards);
	}
	/* no need to free the CS, which is ref-counted in the DO */
	my_object->change_summary = NULL;
	efree(object);
}
/* }}} */

/* {{{ debug macro functions
 */
SDO_DEBUG_ADDREF(das_changesummary)
SDO_DEBUG_DELREF(das_changesummary)
SDO_DEBUG_DESTROY(das_changesummary)
/* }}} */

/* {{{ sdo_das_changesummary_object_create
 */
static zend_object_value sdo_das_changesummary_object_create(zend_class_entry *ce TSRMLS_DC)
{
	zend_object_value retval;
	zval *tmp; /* this must be passed to hash_copy, but doesn't seem to be used */
	sdo_das_changesummary_object *my_object;
		
	my_object = (sdo_das_changesummary_object *)emalloc(sizeof(sdo_das_changesummary_object));
	memset(my_object, 0, sizeof(sdo_das_changesummary_object));
	my_object->zo.ce = ce;
	my_object->zo.guards = NULL;
	ALLOC_HASHTABLE(my_object->zo.properties);
	zend_hash_init(my_object->zo.properties, 0, NULL, ZVAL_PTR_DTOR, 0);
	zend_hash_copy(my_object->zo.properties, &ce->default_properties, (copy_ctor_func_t)zval_add_ref,
		(void *)&tmp, sizeof(zval *));
	retval.handle = zend_objects_store_put(my_object, SDO_FUNC_DESTROY(das_changesummary), sdo_das_changesummary_object_free_storage, NULL TSRMLS_CC);
	retval.handlers = zend_get_std_object_handlers();
	retval.handlers->add_ref = SDO_FUNC_ADDREF(das_changesummary);
	retval.handlers->del_ref = SDO_FUNC_DELREF(das_changesummary);
	SDO_DEBUG_ALLOCATE(retval.handle, my_object);
	return retval;
}
/* }}} */

/* {{{ sdo_das_changesummary_new
 */
void sdo_das_changesummary_new(zval *me, ChangeSummaryPtr change_summary TSRMLS_DC)
{	
	sdo_das_changesummary_object *my_object;
	char *class_name, *space;
	
	Z_TYPE_P(me) = IS_OBJECT;
	if (object_init_ex(me, sdo_das_changesummary_class_entry) == FAILURE) {
		class_name = get_active_class_name(&space TSRMLS_CC);
		php_error(E_ERROR, "%s%s%s(): internal error (%i) - failed to instantiate object", 
			class_name, space, get_active_function_name(TSRMLS_C), __LINE__);
		return;
	}
	
	my_object = (sdo_das_changesummary_object *)zend_object_store_get_object(me TSRMLS_CC);
	my_object->change_summary = change_summary;
}
/* }}} */

/* {{{ sdo_das_changesummary_minit
 */
void sdo_das_changesummary_minit(zend_class_entry *tmp_ce TSRMLS_DC)
{	
	tmp_ce->create_object = sdo_das_changesummary_object_create;
	sdo_das_changesummary_class_entry = zend_register_internal_class(tmp_ce TSRMLS_CC);
	
	 sdo_make_long_class_constant(sdo_das_changesummary_class_entry, "NONE", CS_NONE);	
	 sdo_make_long_class_constant(sdo_das_changesummary_class_entry, "MODIFICATION", CS_MODIFICATION);
	 sdo_make_long_class_constant(sdo_das_changesummary_class_entry, "ADDITION", CS_ADDITION);
	 sdo_make_long_class_constant(sdo_das_changesummary_class_entry, "DELETION", CS_DELETION);
}
/* }}} */

/* {{{ SDO_DAS_ChangeSummary::beginLogging
 */
PHP_METHOD(SDO_DAS_ChangeSummary, beginLogging)
{
	sdo_das_changesummary_object *my_object;
	
	if (ZEND_NUM_ARGS() != 0) {
		WRONG_PARAM_COUNT;
	}
	
	my_object = sdo_das_changesummary_get_instance(getThis() TSRMLS_CC);
	
	try {
		my_object->change_summary->beginLogging();
	} catch (SDORuntimeException e) {
		sdo_throw_runtimeexception(&e TSRMLS_CC);
	}
	
	return;
}
/* }}} */

/* {{{ SDO_DAS_ChangeSummary::endLogging
 */
PHP_METHOD(SDO_DAS_ChangeSummary, endLogging)
{
	sdo_das_changesummary_object *my_object;
	
	if (ZEND_NUM_ARGS() != 0) {
		WRONG_PARAM_COUNT;
	}
	
	my_object = sdo_das_changesummary_get_instance(getThis() TSRMLS_CC);
	
	try {
		my_object->change_summary->endLogging();
	} catch (SDORuntimeException e) {
		sdo_throw_runtimeexception(&e TSRMLS_CC);
	}
	
	return;
}
/* }}} */

/* {{{ SDO_DAS_ChangeSummary::isLogging
 */
PHP_METHOD(SDO_DAS_ChangeSummary, isLogging)
{
	sdo_das_changesummary_object *my_object;
	
	if (ZEND_NUM_ARGS() != 0) {
		WRONG_PARAM_COUNT;
	}
	
	my_object = sdo_das_changesummary_get_instance(getThis() TSRMLS_CC);
	
	try {
		RETVAL_BOOL(my_object->change_summary->isLogging());
	} catch (SDORuntimeException e) {
		sdo_throw_runtimeexception(&e TSRMLS_CC);
	}
	
	return;
}
/* }}} */

/* {{{ SDO_DAS_ChangeSummary::getOldValues
 */
PHP_METHOD(SDO_DAS_ChangeSummary, getOldValues)
{
	sdo_das_changesummary_object	*my_object;
	zval							*z_dataobject;
	DataObjectPtr					 dop;
	ChangeSummaryPtr				 change_summary;
	char							*class_name, *space;
	
	if (zend_parse_parameters(ZEND_NUM_ARGS() TSRMLS_CC, 
		"O", &z_dataobject, sdo_dataobjectimpl_class_entry) == FAILURE) 
		return;	
	
	my_object = sdo_das_changesummary_get_instance(getThis() TSRMLS_CC);
	change_summary = my_object->change_summary;
	
	/* get the supplied data object */
	dop = sdo_do_get (z_dataobject TSRMLS_CC);
	if (!dop) {
		class_name = get_active_class_name(&space TSRMLS_CC);
		php_error(E_ERROR, "%s%s%s(): internal error (%i) - SDO_DataObject not found in store", 
			class_name, space, get_active_function_name(TSRMLS_C), __LINE__);
		RETVAL_NULL();
	}
	
	try {
		SettingList& sl = my_object->change_summary->getOldValues(dop);
		if (&sl == NULL) {
			RETVAL_NULL();
		} else {
			sdo_das_settinglist_new(return_value, sl TSRMLS_CC);
			/* no need to copy this one */
		}
	} catch (SDORuntimeException e) {
		sdo_throw_runtimeexception(&e TSRMLS_CC);
	}
	
	return;
}
/* }}} */

/* {{{ SDO_DAS_ChangeSummary::getChangedDataObjects
 */
PHP_METHOD(SDO_DAS_ChangeSummary, getChangedDataObjects)
{
	sdo_das_changesummary_object *my_object;
	
	if (ZEND_NUM_ARGS() != 0) {
		WRONG_PARAM_COUNT;
	}
	
	my_object = sdo_das_changesummary_get_instance(getThis() TSRMLS_CC);
	
	try {
		const ChangedDataObjectList& cdol = my_object->change_summary->getChangedDataObjects();
		if (&cdol == (ChangedDataObjectList *)NULL) {
			RETVAL_NULL();
		} else {
			sdo_changeddataobjectlist_new(return_value, &cdol TSRMLS_CC);
			/* no need to copy this one */
		}
	} catch (SDORuntimeException e) {
		sdo_throw_runtimeexception(&e TSRMLS_CC);
	}
	
	return;
}
/* }}} */

/* {{{ SDO_DAS_ChangeSummary::getChangeType
 */
PHP_METHOD(SDO_DAS_ChangeSummary, getChangeType)
{
	sdo_das_changesummary_object *my_object;
	zval						 *z_dataobject;
	DataObjectPtr				  dop;
	ChangeSummaryPtr			  change_summary;
	long						  change_type = CS_NONE;
	char						 *class_name, *space;
	
	if (zend_parse_parameters(ZEND_NUM_ARGS() TSRMLS_CC, 
		"O", &z_dataobject, sdo_dataobjectimpl_class_entry) == FAILURE) 
		return;	
	
	my_object = sdo_das_changesummary_get_instance(getThis() TSRMLS_CC);
	change_summary = my_object->change_summary;
	
	/* get the supplied data object */
	dop = sdo_do_get (z_dataobject TSRMLS_CC);
	if (!dop) {
		class_name = get_active_class_name(&space TSRMLS_CC);
		php_error(E_ERROR, "%s%s%s(): internal error (%i) - SDO_DataObject not found in store", 
			class_name, space, get_active_function_name(TSRMLS_C), __LINE__);
		RETVAL_NULL();
	}
	
	try {
	/* this order is important: for example isDeleted and isModified may both return true,
   	 * and that must be treated as a delete 
 	 */
		if (change_summary->isCreated(dop)) {
			change_type = CS_ADDITION;
		} else if (change_summary->isDeleted(dop)) {
			change_type = CS_DELETION;
		} else if (change_summary->isModified(dop)) {
			change_type = CS_MODIFICATION;
		}
		RETVAL_LONG(change_type);
	} catch (SDORuntimeException e) {
		sdo_throw_runtimeexception(&e TSRMLS_CC);
	}
	
	return;
}
/* }}} */

/* {{{ SDO_DAS_ChangeSummary::getOldContainer
 */
PHP_METHOD(SDO_DAS_ChangeSummary, getOldContainer)
{
	sdo_das_changesummary_object *my_object;
	zval				*z_dataobject;
	DataObjectPtr		 dop, container_dop;
	ChangeSummaryPtr	 change_summary;
	long				 change_type = 0;
	char				*class_name, *space;
	
	if (zend_parse_parameters(ZEND_NUM_ARGS() TSRMLS_CC, 
		"O", &z_dataobject, sdo_dataobjectimpl_class_entry) == FAILURE) 
		return;	
	
	my_object = sdo_das_changesummary_get_instance(getThis() TSRMLS_CC);
	change_summary = my_object->change_summary;
	
	/* get the supplied data object */
	dop = sdo_do_get(z_dataobject TSRMLS_CC);
	if (!dop) {
		class_name = get_active_class_name(&space TSRMLS_CC);
		php_error(E_ERROR, "%s%s%s(): internal error (%i) - SDO_DataObject not found in store", 
			class_name, space, get_active_function_name(TSRMLS_C), __LINE__);
		RETVAL_NULL();
	}
	
	try {
		container_dop = change_summary->getOldContainer(dop);
		
		if (!container_dop) {
			RETVAL_NULL();
		} else {
			sdo_do_new(return_value, container_dop TSRMLS_CC);
		}
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
