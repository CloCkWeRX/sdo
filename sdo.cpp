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

#ifdef HAVE_CONFIG_H
#include "config.h"
#endif

#include "php.h"
#include "php_ini.h"
#include "ext/standard/info.h"

#include "zend_exceptions.h"
#include "zend_interfaces.h"

#include "php_sdo_int.h"

/* {{{ zend_class_entry declarations
 */
PHP_SDO_API zend_class_entry *sdo_propertyaccess_class_entry;
PHP_SDO_API zend_class_entry *sdo_dataobject_class_entry;
PHP_SDO_API zend_class_entry *sdo_sequence_class_entry;
PHP_SDO_API zend_class_entry *sdo_list_class_entry;
PHP_SDO_API zend_class_entry *sdo_datafactory_class_entry;
PHP_SDO_API zend_class_entry *sdo_das_dataobject_class_entry;
PHP_SDO_API zend_class_entry *sdo_das_datafactory_class_entry;
PHP_SDO_API zend_class_entry *sdo_das_changesummary_class_entry;
PHP_SDO_API zend_class_entry *sdo_das_setting_class_entry;
PHP_SDO_API zend_class_entry *sdo_das_datafactoryimpl_class_entry;
PHP_SDO_API zend_class_entry *sdo_dataobjectimpl_class_entry;
PHP_SDO_API zend_class_entry *sdo_dataobjectlist_class_entry;
PHP_SDO_API zend_class_entry *sdo_changeddataobjectlist_class_entry;
PHP_SDO_API zend_class_entry *sdo_das_settinglist_class_entry;
PHP_SDO_API zend_class_entry *sdo_sequenceimpl_class_entry;
PHP_SDO_API zend_class_entry *sdo_exception_class_entry;
PHP_SDO_API zend_class_entry *sdo_propertynotsetexception_class_entry;
PHP_SDO_API zend_class_entry *sdo_propertynotfoundexception_class_entry;
PHP_SDO_API zend_class_entry *sdo_typenotfoundexception_class_entry;
PHP_SDO_API zend_class_entry *sdo_invalidconversionexception_class_entry;
PHP_SDO_API zend_class_entry *sdo_indexoutofboundsexception_class_entry;
PHP_SDO_API zend_class_entry *sdo_unsupportedoperationexception_class_entry;
/* }}} */

/* {{{ SDO_PropertyAccess methods */
static ZEND_BEGIN_ARG_INFO(arginfo___get, 0)
    ZEND_ARG_INFO(0, name)
ZEND_END_ARG_INFO();

static ZEND_BEGIN_ARG_INFO(arginfo___set, 0)
    ZEND_ARG_INFO(0, name)
    ZEND_ARG_INFO(0, value)
ZEND_END_ARG_INFO();
function_entry sdo_propertyaccess_methods[] = {
	ZEND_ABSTRACT_ME(SDO_PropertyAccess, __get, arginfo___get)
	ZEND_ABSTRACT_ME(SDO_ProperytAccess, __set, arginfo___set)
};
/* }}} */

/* {{{ SDO_DataObject methods */
static ZEND_BEGIN_ARG_INFO(arginfo_sdo_dataobject_createdataobject, 0)
    ZEND_ARG_INFO(0, identifier)
ZEND_END_ARG_INFO();

function_entry sdo_dataobject_methods[] = {
	ZEND_ABSTRACT_ME(SDO_DataObject, getType, 0)
	ZEND_ABSTRACT_ME(SDO_DataObject, getSequence, 0)
	ZEND_ABSTRACT_ME(SDO_DataObject, createDataObject, arginfo_sdo_dataobject_createdataobject)
	ZEND_ABSTRACT_ME(SDO_DataObject, clear, 0)
	ZEND_ABSTRACT_ME(SDO_DataObject, getContainer, 0)
	ZEND_ABSTRACT_ME(SDO_DataObject, getContainmentPropertyName, 0)
	{NULL, NULL, NULL}
};
/* }}} */

/* {{{ SDO_Sequence methods */
static ZEND_BEGIN_ARG_INFO(arginfo_sdo_sequence_getpropertyindex, 0)
    ZEND_ARG_INFO(0, index)
ZEND_END_ARG_INFO();

static ZEND_BEGIN_ARG_INFO(arginfo_sdo_sequence_getpropertyname, 0)
    ZEND_ARG_INFO(0, index)
ZEND_END_ARG_INFO();

static ZEND_BEGIN_ARG_INFO(arginfo_sdo_sequence_move, 0)
    ZEND_ARG_INFO(0, toIndex)
    ZEND_ARG_INFO(0, fromIndex)
ZEND_END_ARG_INFO();

static ZEND_BEGIN_ARG_INFO_EX(arginfo_sdo_sequence_insert, 0, ZEND_RETURN_VALUE, 1)
    ZEND_ARG_INFO(0, value)
    ZEND_ARG_INFO(0, sequenceIndex)
	ZEND_ARG_INFO(0, propertyIdentifier)
ZEND_END_ARG_INFO();

function_entry sdo_sequence_methods[] = {
	ZEND_ABSTRACT_ME(SDO_Sequence, getPropertyIndex, arginfo_sdo_sequence_getpropertyindex)
	ZEND_ABSTRACT_ME(SDO_Sequence, getPropertyName, arginfo_sdo_sequence_getpropertyname)
	ZEND_ABSTRACT_ME(SDO_Sequence, move, arginfo_sdo_sequence_move)
	ZEND_ABSTRACT_ME(SDO_Sequence, insert, arginfo_sdo_sequence_insert)
	ZEND_ABSTRACT_ME(SDO_Sequence, count, 0)
	{NULL, NULL, NULL}
};
/* }}} */

/* {{{ SDO_List methods */
static ZEND_BEGIN_ARG_INFO_EX(arginfo_sdo_list_insert, 0, ZEND_RETURN_VALUE, 1)
    ZEND_ARG_INFO(0, value)
    ZEND_ARG_INFO(0, index)
ZEND_END_ARG_INFO();

function_entry sdo_list_methods[] = {
	ZEND_ME(SDO_List, __construct, 0, ZEND_ACC_PRIVATE)
	ZEND_ME(SDO_List, insert, arginfo_sdo_list_insert, ZEND_ACC_PUBLIC)
	ZEND_ME(SDO_List, count, 0, ZEND_ACC_PUBLIC)
	{NULL, NULL, NULL}
};
/* }}} */

/* {{{ SDO_DataFactory methods */
static ZEND_BEGIN_ARG_INFO(arginfo_sdo_datafactory_create, 0)
    ZEND_ARG_INFO(0, namespaceURI)
    ZEND_ARG_INFO(0, typeName)
ZEND_END_ARG_INFO();

function_entry sdo_datafactory_methods[] = {
	ZEND_ABSTRACT_ME(SDO_DataFactory, create, arginfo_sdo_datafactory_create)
	{NULL, NULL, NULL}
};
/* }}} */

/* {{{ SDO_DAS_DataObject methods */
function_entry sdo_das_dataobject_methods[] = {
	ZEND_ABSTRACT_ME(SDO_DAS_DataObject, getChangeSummary, 0)
	{NULL, NULL, NULL}
};
/* }}} */

/* {{{ SDO_DAS_DataFactory methods */
static ZEND_BEGIN_ARG_INFO_EX(arginfo_sdo_das_datafactory_addType, 0, ZEND_RETURN_VALUE, 2)
    ZEND_ARG_INFO(0, namespaceURI)
    ZEND_ARG_INFO(0, typeName)
    ZEND_ARG_INFO(0, sequenced)
    ZEND_ARG_INFO(0, open)
ZEND_END_ARG_INFO();

static ZEND_BEGIN_ARG_INFO_EX(arginfo_sdo_das_datafactory_addPropertyToType, 0, ZEND_RETURN_VALUE, 5)
    ZEND_ARG_INFO(0, parentNamespaceURI)
    ZEND_ARG_INFO(0, parentTypeName)
    ZEND_ARG_INFO(0, propertyName)
    ZEND_ARG_INFO(0, namespaceURI)
    ZEND_ARG_INFO(0, typeName)
    ZEND_ARG_INFO(0, many)
    ZEND_ARG_INFO(0, readOnly)
    ZEND_ARG_INFO(0, containment)
ZEND_END_ARG_INFO();

function_entry sdo_das_datafactory_methods[] = {
	ZEND_ME(SDO_DAS_DataFactory, getDataFactory, NULL, ZEND_ACC_PUBLIC|ZEND_ACC_STATIC)
	ZEND_ABSTRACT_ME(SDO_DAS_DataFactory, addType, arginfo_sdo_das_datafactory_addType)
	ZEND_ABSTRACT_ME(SDO_DAS_DataFactory, addPropertyToType, arginfo_sdo_das_datafactory_addPropertyToType)
	{NULL, NULL, NULL}
};
/* }}} */

/* {{{SDO_DAS_ChangeSummary methods */
static ZEND_BEGIN_ARG_INFO(arginfo_sdo_das_changesummary_dataobject, 0)
    ZEND_ARG_OBJ_INFO(0, dataObject, SDO_DataObject, 0)
ZEND_END_ARG_INFO();

function_entry sdo_das_changesummary_methods[] = {
	ZEND_ME(SDO_DAS_ChangeSummary, beginLogging, 0, ZEND_ACC_PUBLIC)
	ZEND_ME(SDO_DAS_ChangeSummary, endLogging, 0, ZEND_ACC_PUBLIC)
	ZEND_ME(SDO_DAS_ChangeSummary, isLogging, 0, ZEND_ACC_PUBLIC)
	ZEND_ME(SDO_DAS_ChangeSummary, getChangedDataObjects, 0, ZEND_ACC_PUBLIC)
	ZEND_ME(SDO_DAS_ChangeSummary, getChangeType, arginfo_sdo_das_changesummary_dataobject, ZEND_ACC_PUBLIC)
	ZEND_ME(SDO_DAS_ChangeSummary, getOldValues, arginfo_sdo_das_changesummary_dataobject, ZEND_ACC_PUBLIC)
	ZEND_ME(SDO_DAS_ChangeSummary, getOldContainer, arginfo_sdo_das_changesummary_dataobject, ZEND_ACC_PUBLIC)
	{NULL, NULL, NULL}
};
/* }}} */

/* {{{ SDO_DASSetting methods */
function_entry sdo_das_setting_methods[] = {
	ZEND_ME(SDO_DAS_Setting, getPropertyIndex, 0, ZEND_ACC_PUBLIC)
	ZEND_ME(SDO_DAS_Setting, getPropertyName, 0, ZEND_ACC_PUBLIC)
	ZEND_ME(SDO_DAS_Setting, getValue, 0, ZEND_ACC_PUBLIC)
	ZEND_ME(SDO_DAS_Setting, getListIndex, 0, ZEND_ACC_PUBLIC)
	ZEND_ME(SDO_DAS_Setting, isSet, 0, ZEND_ACC_PUBLIC)
	{NULL, NULL, NULL}
};
/* }}} */

/* {{{ SDO_DAS_DataFactoryImpl methods */
function_entry sdo_das_df_methods[] = {
	ZEND_ME(SDO_DAS_DataFactoryImpl, create, arginfo_sdo_datafactory_create, ZEND_ACC_PUBLIC)
	ZEND_ME(SDO_DAS_DataFactoryImpl, addType, arginfo_sdo_das_datafactory_addType, ZEND_ACC_PUBLIC)
	ZEND_ME(SDO_DAS_DataFactoryImpl, addPropertyToType, arginfo_sdo_das_datafactory_addPropertyToType, ZEND_ACC_PUBLIC)
	{NULL, NULL, NULL}
};
/* }}} */

/* {{{ SDO_DataObjectImpl methods */
function_entry sdo_dataobjectimpl_methods[] = {
	ZEND_ME(SDO_DataObjectImpl, __construct, 0, ZEND_ACC_PRIVATE) /* can't be newed */
	ZEND_ME(SDO_DataObjectImpl, __get, arginfo___get, ZEND_ACC_PUBLIC)
	ZEND_ME(SDO_DataObjectImpl, __set, arginfo___set, ZEND_ACC_PUBLIC)
	ZEND_ME(SDO_DataObjectImpl, count, 0, ZEND_ACC_PUBLIC)
	/* inherited from SDO_DataObject ... */
	ZEND_ME(SDO_DataObjectImpl, getType, 0, ZEND_ACC_PUBLIC)
	ZEND_ME(SDO_DataObjectImpl, getSequence, 0, ZEND_ACC_PUBLIC)
	ZEND_ME(SDO_DataObjectImpl, createDataObject, arginfo_sdo_dataobject_createdataobject, ZEND_ACC_PUBLIC)
	ZEND_ME(SDO_DataObjectImpl, clear, 0, ZEND_ACC_PUBLIC)
	ZEND_ME(SDO_DataObjectImpl, getContainer, 0, ZEND_ACC_PUBLIC)
	ZEND_ME(SDO_DataObjectImpl, getContainmentPropertyName, 0, ZEND_ACC_PUBLIC)
	/* inherited from SDO_DAS_DataObject ... */
	ZEND_ME(SDO_DataObjectImpl, getChangeSummary, 0, ZEND_ACC_PUBLIC)
	{NULL, NULL, NULL}
};
/* }}} */

/* {{{ SDO_SequenceImpl methods */
function_entry sdo_sequenceimpl_methods[] = {
	ZEND_ME(SDO_SequenceImpl, getPropertyIndex, arginfo_sdo_sequence_getpropertyindex, ZEND_ACC_PUBLIC)
	ZEND_ME(SDO_SequenceImpl, getPropertyName, arginfo_sdo_sequence_getpropertyname, ZEND_ACC_PUBLIC)
	ZEND_ME(SDO_SequenceImpl, move, arginfo_sdo_sequence_move, ZEND_ACC_PUBLIC)
	ZEND_ME(SDO_SequenceImpl, insert, arginfo_sdo_sequence_insert, ZEND_ACC_PUBLIC)
	ZEND_ME(SDO_SequenceImpl, count, 0, ZEND_ACC_PUBLIC)
	{NULL, NULL, NULL}
};
/* }}} */

/* {{{ SDO_Exception methods
 * These are used for all SDO exceptions, none of them have any methods other than inherited ones
 */
function_entry sdo_exception_methods[] = {
	{NULL, NULL, NULL}
};
/* }}} */

/* {{{ sdo_get_exception_methods
 */
PHP_SDO_API function_entry *sdo_get_exception_methods()
{
    return sdo_exception_methods;
}
/* }}} */

/* {{{ sdo_module_entry
*/
zend_module_entry sdo_module_entry = {
#if ZEND_MODULE_API_NO >= 20010901
	STANDARD_MODULE_HEADER,
#endif
		"sdo",
		NULL, /* function list */
		PHP_MINIT(sdo),
		NULL, /* mshutdown */
		NULL, /* rinit */
		NULL, /* rshutdown */
		PHP_MINFO(sdo),
#if ZEND_MODULE_API_NO >= 20010901
		SDO_VERSION,
#endif
		STANDARD_MODULE_PROPERTIES
};
/* }}} */

#ifdef COMPILE_DL_SDO
BEGIN_EXTERN_C()
ZEND_GET_MODULE(sdo)
END_EXTERN_C()
#endif

/* {{{ PHP_MINIT_FUNCTION
*/
PHP_MINIT_FUNCTION(sdo)
{
	zend_class_entry ce;

	/*
	 * Check the level of the C++ library
	 */
	if (SdoRuntime::getMajor() != 0) {
		php_error (E_ERROR, "SDO: incompatible versions of SDO extension and SDO library");
	}

	/* TODO I'd like this to be extern rather than have to redefine it
	 * REGISTER_STRING_CONSTANT("SDO_TYPE_NAMESPACE_URI", (char *)Type::SDOTypeNamespaceURI, CONST_CS | CONST_PERSISTENT);
	 */
	REGISTER_STRING_CONSTANT("SDO_TYPE_NAMESPACE_URI", "commonj.sdo", CONST_CS | CONST_PERSISTENT);
	REGISTER_STRING_CONSTANT("SDO_VERSION", SDO_VERSION, CONST_CS | CONST_PERSISTENT);

	/*
	 * TODO The following should be class constants, but were defined as module constants to work around a problem.
	 * These are now deprecated and will be removed in a future release.
	 */
	REGISTER_LONG_CONSTANT("SDO_DAS_CHANGE_SUMMARY_NONE", CS_NONE, CONST_CS | CONST_PERSISTENT);
	REGISTER_LONG_CONSTANT("SDO_DAS_CHANGE_SUMMARY_MODIFICATION", CS_MODIFICATION, CONST_CS | CONST_PERSISTENT);
	REGISTER_LONG_CONSTANT("SDO_DAS_CHANGE_SUMMARY_ADDITION", CS_ADDITION, CONST_CS | CONST_PERSISTENT);
	REGISTER_LONG_CONSTANT("SDO_DAS_CHANGE_SUMMARY_DELETION", CS_DELETION, CONST_CS | CONST_PERSISTENT);

	/* interface SDO_PropertyAccess */
	INIT_CLASS_ENTRY(ce, "SDO_PropertyAccess", sdo_propertyaccess_methods);
	sdo_propertyaccess_class_entry = zend_register_internal_interface(&ce TSRMLS_CC);

	/* interface SDO_DataObject extends Traversable, SDO_PropertyAccess */
	INIT_CLASS_ENTRY(ce, "SDO_DataObject", sdo_dataobject_methods);
	sdo_dataobject_class_entry = zend_register_internal_interface(&ce TSRMLS_CC);
	zend_class_implements(sdo_dataobject_class_entry TSRMLS_CC, 2,
		zend_ce_traversable, sdo_propertyaccess_class_entry);

	/* interface SDO_Sequence extends Traversable */
	INIT_CLASS_ENTRY(ce, "SDO_Sequence", sdo_sequence_methods);
	sdo_sequence_class_entry = zend_register_internal_interface(&ce TSRMLS_CC);
	zend_class_implements(sdo_sequence_class_entry TSRMLS_CC, 1, zend_ce_traversable);

	/* interface SDO_List extends Traversable */
	INIT_CLASS_ENTRY(ce, "SDO_List", sdo_list_methods);
	sdo_list_minit(&ce TSRMLS_CC);

	/* class SDO_DataObjectList implements SDO_List */
	INIT_CLASS_ENTRY(ce, "SDO_DataObjectList", sdo_list_methods);
	sdo_dataobjectlist_class_entry = zend_register_internal_class_ex(&ce, sdo_list_class_entry, 0 TSRMLS_CC);

	/* class SDO_ChangedDataObjectList implements SDO_List */
	INIT_CLASS_ENTRY(ce, "SDO_ChangedDataObjectList", sdo_list_methods);
	sdo_changeddataobjectlist_class_entry = zend_register_internal_class_ex(&ce, sdo_list_class_entry, 0 TSRMLS_CC);

	/* interface SDO_DataFactory */
	INIT_CLASS_ENTRY(ce, "SDO_DataFactory", sdo_datafactory_methods);
	sdo_datafactory_class_entry = zend_register_internal_interface(&ce TSRMLS_CC);

	/* interface SDO_DAS_DataObject */
	INIT_CLASS_ENTRY(ce, "SDO_DAS_DataObject", sdo_das_dataobject_methods);
	sdo_das_dataobject_class_entry = zend_register_internal_interface(&ce TSRMLS_CC);
	zend_class_implements(sdo_das_dataobject_class_entry TSRMLS_CC, 1, sdo_dataobject_class_entry);

	/* SDO_DAS_DataFactory implements SDO_DataFactory */
	INIT_CLASS_ENTRY(ce, "SDO_DAS_DataFactory", sdo_das_datafactory_methods);
	sdo_das_datafactory_class_entry = zend_register_internal_class(&ce TSRMLS_CC);
	zend_class_implements(sdo_das_datafactory_class_entry TSRMLS_CC, 1, sdo_datafactory_class_entry);

	/* interface SDO_DAS_ChangeSummary */
	INIT_CLASS_ENTRY(ce, "SDO_DAS_ChangeSummary", sdo_das_changesummary_methods);
	sdo_das_changesummary_minit(&ce TSRMLS_CC);

	/* interface SDO_DAS_Setting */
	INIT_CLASS_ENTRY(ce, "SDO_DAS_Setting", sdo_das_setting_methods);
	sdo_das_setting_minit(&ce TSRMLS_CC);

	/* class SDO_DAS_SettingList implements SDO_List */
	INIT_CLASS_ENTRY(ce, "SDO_DAS_SettingList", sdo_list_methods);
	sdo_das_settinglist_class_entry = zend_register_internal_class_ex(&ce, sdo_list_class_entry, 0 TSRMLS_CC);

	/* class SDO_Exception extends Exception */
    INIT_CLASS_ENTRY(ce, "SDO_Exception", sdo_exception_methods);
    sdo_exception_class_entry = zend_register_internal_class_ex(
        &ce, 
#if (PHP_MAJOR_VERSION < 6) 
		zend_exception_get_default(), 
#else  
		zend_exception_get_default(TSRMLS_C),
#endif
		NULL TSRMLS_CC);

	/* class SDO_PropertyNotFoundException extends SDO_Exception */
    INIT_CLASS_ENTRY(ce, "SDO_PropertyNotFoundException", sdo_exception_methods);
    sdo_propertynotfoundexception_class_entry = zend_register_internal_class_ex(
        &ce, sdo_exception_class_entry, NULL TSRMLS_CC);

	/* class SDO_PropertyNotSetException extends SDO_Exception */
    INIT_CLASS_ENTRY(ce, "SDO_PropertyNotSetException", sdo_exception_methods);
    sdo_propertynotsetexception_class_entry = zend_register_internal_class_ex(
        &ce, sdo_exception_class_entry, NULL TSRMLS_CC);

	/* class SDO_TypeNotFoundException extends SDO_Exception */
    INIT_CLASS_ENTRY(ce, "SDO_TypeNotFoundException", sdo_exception_methods);
    sdo_typenotfoundexception_class_entry = zend_register_internal_class_ex(
        &ce, sdo_exception_class_entry, NULL TSRMLS_CC);

	/* class SDO_InvalidConversionException extends SDO_Exception */
    INIT_CLASS_ENTRY(ce, "SDO_InvalidConversionException", sdo_exception_methods);
    sdo_invalidconversionexception_class_entry = zend_register_internal_class_ex(
        &ce, sdo_exception_class_entry, NULL TSRMLS_CC);

	/* class SDO_IndexOutOfBoundsException extends SDO_Exception */
    INIT_CLASS_ENTRY(ce, "SDO_IndexOutOfBoundsException", sdo_exception_methods);
    sdo_indexoutofboundsexception_class_entry = zend_register_internal_class_ex(
        &ce, sdo_exception_class_entry, NULL TSRMLS_CC);

	/* class SDO_UnsupportedOperationException extends SDO_Exception */
    INIT_CLASS_ENTRY(ce, "SDO_UnsupportedOperationException", sdo_exception_methods);
    sdo_unsupportedoperationexception_class_entry = zend_register_internal_class_ex(
        &ce, sdo_exception_class_entry, NULL TSRMLS_CC);

	/* class SDO_DAS_DataFactoryImpl implements SDO_DAS_DataFactory */
    INIT_CLASS_ENTRY(ce, "SDO_DAS_DataFactoryImpl", sdo_das_df_methods);
	sdo_das_df_minit(&ce TSRMLS_CC);

	/* class SDO_DataObjectImpl implements SDO_DAS_DataObject */
    INIT_CLASS_ENTRY(ce, "SDO_DataObjectImpl", sdo_dataobjectimpl_methods);
	sdo_do_minit(&ce TSRMLS_CC);

	/* class SDO_SequenceImpl implements SDO_Sequence */
    INIT_CLASS_ENTRY(ce, "SDO_SequenceImpl", sdo_sequenceimpl_methods);
	sdo_sequence_minit(&ce TSRMLS_CC);

   return SUCCESS;

}
/* }}} */

/* {{{ PHP_MINFO_FUNCTION
*/
PHP_MINFO_FUNCTION(sdo)
{
	php_info_print_table_start();
	php_info_print_table_row(2, "sdo support", "enabled");
	php_info_print_table_row(2, "sdo version", SDO_VERSION);
	php_info_print_table_row(2, "libsdo version", SdoRuntime::getVersion());
	php_info_print_table_end();
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
