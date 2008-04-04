/*
+----------------------------------------------------------------------+
| (c) Copyright IBM Corporation 2005, 2008.                            |
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
#include "php_sdo_das_xml_int.h"

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

PHP_SDO_API zend_class_entry *sdo_model_type_class_entry;
PHP_SDO_API zend_class_entry *sdo_model_typeimpl_class_entry;
PHP_SDO_API zend_class_entry *sdo_model_property_class_entry;
PHP_SDO_API zend_class_entry *sdo_model_propertyimpl_class_entry;
PHP_SDO_API zend_class_entry *sdo_model_reflectiondataobject_class_entry;

PHP_SDO_API zend_class_entry *sdo_exception_class_entry;
PHP_SDO_API zend_class_entry *sdo_propertynotsetexception_class_entry;
PHP_SDO_API zend_class_entry *sdo_propertynotfoundexception_class_entry;
PHP_SDO_API zend_class_entry *sdo_typenotfoundexception_class_entry;
PHP_SDO_API zend_class_entry *sdo_invalidconversionexception_class_entry;
PHP_SDO_API zend_class_entry *sdo_indexoutofboundsexception_class_entry;
PHP_SDO_API zend_class_entry *sdo_unsupportedoperationexception_class_entry;
PHP_SDO_API zend_class_entry *sdo_cppexception_class_entry;
/* }}} */

/* {{{ single SDO_DataObject parameter */
static ZEND_BEGIN_ARG_INFO(arginfo_sdo_dataobject, 0)
    ZEND_ARG_OBJ_INFO(0, data_object, SDO_DataObject, 0)
ZEND_END_ARG_INFO();
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
	ZEND_ABSTRACT_ME(SDO_PropertyAccess, __set, arginfo___set)
	{NULL, NULL, NULL}
};
/* }}} */

/* {{{ SDO_DataObject methods */
static ZEND_BEGIN_ARG_INFO(arginfo_sdo_dataobject_createdataobject, 0)
    ZEND_ARG_INFO(0, identifier)
ZEND_END_ARG_INFO();

function_entry sdo_dataobject_methods[] = {
	ZEND_ABSTRACT_ME(SDO_DataObject, getTypeName, 0)
	ZEND_ABSTRACT_ME(SDO_DataObject, getTypeNamespaceURI, 0)
	ZEND_ABSTRACT_ME(SDO_DataObject, getSequence, 0)
	ZEND_ABSTRACT_ME(SDO_DataObject, createDataObject, arginfo_sdo_dataobject_createdataobject)
	ZEND_ABSTRACT_ME(SDO_DataObject, clear, 0)
	ZEND_ABSTRACT_ME(SDO_DataObject, getContainer, 0)
	{NULL, NULL, NULL}
};
/* }}} */

/* {{{ SDO_Sequence methods */
static ZEND_BEGIN_ARG_INFO(arginfo_sdo_sequence_getproperty, 0)
    ZEND_ARG_INFO(0, sequence_index)
ZEND_END_ARG_INFO();

static ZEND_BEGIN_ARG_INFO(arginfo_sdo_sequence_move, 0)
    ZEND_ARG_INFO(0, to_index)
    ZEND_ARG_INFO(0, from_index)
ZEND_END_ARG_INFO();

static ZEND_BEGIN_ARG_INFO_EX(arginfo_sdo_sequence_insert, 0, ZEND_RETURN_VALUE, 1)
    ZEND_ARG_INFO(0, value)
    ZEND_ARG_INFO(0, sequence_index)
	ZEND_ARG_INFO(0, property_identifier)
ZEND_END_ARG_INFO();

function_entry sdo_sequence_methods[] = {
	ZEND_ABSTRACT_ME(SDO_Sequence, getProperty, arginfo_sdo_sequence_getproperty)
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
    ZEND_ARG_INFO(0, type_namespace_uri)
    ZEND_ARG_INFO(0, type_name)
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
    ZEND_ARG_INFO(0, type_namespace_uri)
    ZEND_ARG_INFO(0, type_name)
    ZEND_ARG_INFO(0, options)
ZEND_END_ARG_INFO();

static ZEND_BEGIN_ARG_INFO_EX(arginfo_sdo_das_datafactory_addPropertyToType, 0, ZEND_RETURN_VALUE, 5)
    ZEND_ARG_INFO(0, parent_type_namespace_uri)
    ZEND_ARG_INFO(0, parent_type_name)
    ZEND_ARG_INFO(0, property_name)
    ZEND_ARG_INFO(0, type_namespace_uri)
    ZEND_ARG_INFO(0, type_name)
    ZEND_ARG_INFO(0, options)
ZEND_END_ARG_INFO();

function_entry sdo_das_datafactory_methods[] = {
	ZEND_ME(SDO_DAS_DataFactory, getDataFactory, NULL, ZEND_ACC_PUBLIC|ZEND_ACC_STATIC)
	ZEND_ABSTRACT_ME(SDO_DAS_DataFactory, addType, arginfo_sdo_das_datafactory_addType)
	ZEND_ABSTRACT_ME(SDO_DAS_DataFactory, addPropertyToType, arginfo_sdo_das_datafactory_addPropertyToType)
	{NULL, NULL, NULL}
};
/* }}} */

/* {{{SDO_DAS_ChangeSummary methods */
function_entry sdo_das_changesummary_methods[] = {
	ZEND_ME(SDO_DAS_ChangeSummary, beginLogging, 0, ZEND_ACC_PUBLIC)
	ZEND_ME(SDO_DAS_ChangeSummary, endLogging, 0, ZEND_ACC_PUBLIC)
	ZEND_ME(SDO_DAS_ChangeSummary, isLogging, 0, ZEND_ACC_PUBLIC)
	ZEND_ME(SDO_DAS_ChangeSummary, getChangedDataObjects, 0, ZEND_ACC_PUBLIC)
	ZEND_ME(SDO_DAS_ChangeSummary, getChangeType, arginfo_sdo_dataobject, ZEND_ACC_PUBLIC)
	ZEND_ME(SDO_DAS_ChangeSummary, getOldValues, arginfo_sdo_dataobject, ZEND_ACC_PUBLIC)
	ZEND_ME(SDO_DAS_ChangeSummary, getOldContainer, arginfo_sdo_dataobject, ZEND_ACC_PUBLIC)
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
	ZEND_ME(SDO_DataObjectImpl, getTypeName, 0, ZEND_ACC_PUBLIC)
	ZEND_ME(SDO_DataObjectImpl, getTypeNamespaceURI, 0, ZEND_ACC_PUBLIC)
	ZEND_ME(SDO_DataObjectImpl, getSequence, 0, ZEND_ACC_PUBLIC)
	ZEND_ME(SDO_DataObjectImpl, createDataObject, arginfo_sdo_dataobject_createdataobject, ZEND_ACC_PUBLIC)
	ZEND_ME(SDO_DataObjectImpl, clear, 0, ZEND_ACC_PUBLIC)
	ZEND_ME(SDO_DataObjectImpl, getContainer, 0, ZEND_ACC_PUBLIC)
	/* inherited from SDO_DAS_DataObject ... */
	ZEND_ME(SDO_DataObjectImpl, getChangeSummary, 0, ZEND_ACC_PUBLIC)
	{NULL, NULL, NULL}
};
/* }}} */

/* {{{ SDO_SequenceImpl methods */
function_entry sdo_sequenceimpl_methods[] = {
	ZEND_ME(SDO_SequenceImpl, getProperty, arginfo_sdo_sequence_getproperty, ZEND_ACC_PUBLIC)
	ZEND_ME(SDO_SequenceImpl, move, arginfo_sdo_sequence_move, ZEND_ACC_PUBLIC)
	ZEND_ME(SDO_SequenceImpl, insert, arginfo_sdo_sequence_insert, ZEND_ACC_PUBLIC)
	ZEND_ME(SDO_SequenceImpl, count, 0, ZEND_ACC_PUBLIC)
	{NULL, NULL, NULL}
};
/* }}} */

/* {{{ SDO_Model_Type methods */
static ZEND_BEGIN_ARG_INFO(arginfo_sdo_model_type_identifier, 0)
    ZEND_ARG_INFO(0, identifier)
ZEND_END_ARG_INFO();

function_entry sdo_model_type_methods[] = {
	ZEND_ABSTRACT_ME(SDO_Model_Type, getName, 0)
	ZEND_ABSTRACT_ME(SDO_Model_Type, getNamespaceURI, 0)
	ZEND_ABSTRACT_ME(SDO_Model_Type, isInstance, arginfo_sdo_dataobject)
	ZEND_ABSTRACT_ME(SDO_Model_Type, getProperties, 0)
	ZEND_ABSTRACT_ME(SDO_Model_Type, getProperty, arginfo_sdo_model_type_identifier)
	ZEND_ABSTRACT_ME(SDO_Model_Type, isDataType, 0)
	ZEND_ABSTRACT_ME(SDO_Model_Type, isSequencedType, 0)
	ZEND_ABSTRACT_ME(SDO_Model_Type, isOpenType, 0)
	ZEND_ABSTRACT_ME(SDO_Model_Type, isAbstractType, 0)
	ZEND_ABSTRACT_ME(SDO_Model_Type, getBaseType, 0)
	{NULL, NULL, NULL}
};

function_entry sdo_model_typeimpl_methods[] = {
 	ZEND_ME(SDO_Model_TypeImpl, __construct, 0, ZEND_ACC_PRIVATE) /* can't be newed */
	ZEND_ME(SDO_Model_TypeImpl, getName, 0, ZEND_ACC_PUBLIC)
	ZEND_ME(SDO_Model_TypeImpl, getNamespaceURI, 0, ZEND_ACC_PUBLIC)
	ZEND_ME(SDO_Model_TypeImpl, isInstance, arginfo_sdo_dataobject, ZEND_ACC_PUBLIC)
	ZEND_ME(SDO_Model_TypeImpl, getProperties, 0, ZEND_ACC_PUBLIC)
	ZEND_ME(SDO_Model_TypeImpl, getProperty, arginfo_sdo_model_type_identifier, ZEND_ACC_PUBLIC)
	ZEND_ME(SDO_Model_TypeImpl, isDataType, 0, ZEND_ACC_PUBLIC)
	ZEND_ME(SDO_Model_TypeImpl, isSequencedType, 0, ZEND_ACC_PUBLIC)
	ZEND_ME(SDO_Model_TypeImpl, isOpenType, 0, ZEND_ACC_PUBLIC)
	ZEND_ME(SDO_Model_TypeImpl, isAbstractType, 0, ZEND_ACC_PUBLIC)
	ZEND_ME(SDO_Model_TypeImpl, getBaseType, 0, ZEND_ACC_PUBLIC)
	{NULL, NULL, NULL}
};
/* }}} */

/* {{{ SDO_Model_Property methods */
function_entry sdo_model_property_methods[] = {
    ZEND_ABSTRACT_ME(SDO_Model_Property, getName, 0)
    ZEND_ABSTRACT_ME(SDO_Model_Property, getType, 0)
    ZEND_ABSTRACT_ME(SDO_Model_Property, isMany, 0)
    ZEND_ABSTRACT_ME(SDO_Model_Property, isReadOnly, 0)
    ZEND_ABSTRACT_ME(SDO_Model_Property, isContainment, 0)
    ZEND_ABSTRACT_ME(SDO_Model_Property, getOpposite, 0)
    ZEND_ABSTRACT_ME(SDO_Model_Property, getContainingType, 0)
    ZEND_ABSTRACT_ME(SDO_Model_Property, getDefault, 0)
	{NULL, NULL, NULL}
};

function_entry sdo_model_propertyimpl_methods[] = {
    ZEND_ME(SDO_Model_PropertyImpl, __construct, 0, ZEND_ACC_PRIVATE) /* can't be newed */
    ZEND_ME(SDO_Model_PropertyImpl, getName, 0, ZEND_ACC_PUBLIC)
    ZEND_ME(SDO_Model_PropertyImpl, getType, 0, ZEND_ACC_PUBLIC)
    ZEND_ME(SDO_Model_PropertyImpl, isMany, 0, ZEND_ACC_PUBLIC)
    ZEND_ME(SDO_Model_PropertyImpl, isReadOnly, 0, ZEND_ACC_PUBLIC)
    ZEND_ME(SDO_Model_PropertyImpl, isContainment, 0, ZEND_ACC_PUBLIC)
    ZEND_ME(SDO_Model_PropertyImpl, getOpposite, 0, ZEND_ACC_PUBLIC)
    ZEND_ME(SDO_Model_PropertyImpl, getContainingType, 0, ZEND_ACC_PUBLIC)
    ZEND_ME(SDO_Model_PropertyImpl, getDefault, 0, ZEND_ACC_PUBLIC)
	{NULL, NULL, NULL}
};
/* }}} */

/* {{{ SDO_Model_ReflectionDataObject methods */
static ZEND_BEGIN_ARG_INFO_EX(arginfo_sdo_model_reflectiondataobject_export, 0, ZEND_RETURN_VALUE, 1)
    ZEND_ARG_OBJ_INFO(0, reflector, Reflector, 0)
    ZEND_ARG_INFO(0, return_output)
ZEND_END_ARG_INFO();

function_entry sdo_model_reflectiondataobject_methods[] = {
    ZEND_ME(SDO_Model_ReflectionDataObject, __construct, arginfo_sdo_dataobject, ZEND_ACC_PUBLIC)
    ZEND_ME(SDO_Model_ReflectionDataObject, __toString, 0, ZEND_ACC_PUBLIC)
/*  ZEND_ME(SDO_Model_ReflectionDataObject, export, arginfo_sdo_model_reflectiondataobject_export, ZEND_ACC_PUBLIC | ZEND_ACC_STATIC) */
    /* The above definition fails on some (but not all) platforms.
	 * For me, on Linux AMD_64 non-debug build, the export method (and 
	 * hence the entire class) gets defined as abstract.
     * The variants below works round this.
     */
#if PHP_MAJOR_VERSION > 5 || (PHP_MAJOR_VERSION == 5 && PHP_MINOR_VERSION > 1)
    ZEND_FENTRY(export, ZEND_MN(SDO_Model_ReflectionDataObject_export), arginfo_sdo_model_reflectiondataobject_export, ZEND_ACC_PUBLIC | ZEND_ACC_STATIC)
#else
    ZEND_FENTRY(export, ZEND_FN(SDO_Model_ReflectionDataObject_export), arginfo_sdo_model_reflectiondataobject_export, ZEND_ACC_PUBLIC | ZEND_ACC_STATIC)
#endif
    ZEND_ME(SDO_Model_ReflectionDataObject, getType, 0, ZEND_ACC_PUBLIC)
    ZEND_ME(SDO_Model_ReflectionDataObject, getInstanceProperties, 0, ZEND_ACC_PUBLIC)
    ZEND_ME(SDO_Model_ReflectionDataObject, getContainmentProperty, 0, ZEND_ACC_PUBLIC)
	{NULL, NULL, NULL}
};
/* }}} */

/* {{{ SDO_Exception methods */
function_entry sdo_exception_methods[] = {
	ZEND_ME(SDO_Exception, getCause, 0, ZEND_ACC_PUBLIC)
	{NULL, NULL, NULL}
};
/* }}} */

/* {{{ SDO_CPPException methods */
function_entry sdo_cppexception_methods[] = {
	ZEND_ME(SDO_CPPException, getClass, 0, ZEND_ACC_PUBLIC)
	ZEND_ME(SDO_CPPException, getFile, 0, ZEND_ACC_PUBLIC)
	ZEND_ME(SDO_CPPException, getLine, 0, ZEND_ACC_PUBLIC)
	ZEND_ME(SDO_CPPException, getFunction, 0, ZEND_ACC_PUBLIC)
	ZEND_ME(SDO_CPPException, getMessage, 0, ZEND_ACC_PUBLIC)
	ZEND_ME(SDO_CPPException, getSeverity, 0, ZEND_ACC_PUBLIC)
	ZEND_ME(SDO_CPPException, __toString, 0, ZEND_ACC_PUBLIC)
	{NULL, NULL, NULL}
};
/* }}} */

/* {{{ sdo_exception_get_methods
 */
PHP_SDO_API function_entry *sdo_exception_get_methods()
{
    return sdo_exception_methods;
}
/* }}} */

/* {{{ sdo_deps
*/
#if ZEND_MODULE_API_NO >= 20050922
static zend_module_dep sdo_deps[] = {
	ZEND_MOD_REQUIRED("libxml")
	ZEND_MOD_REQUIRED("spl")
	ZEND_MOD_REQUIRED("Reflection")
    ZEND_MOD_CONFLICTS("sdo_das_xml")
	{NULL, NULL, NULL}
};
#endif
/* }}} */

/* {{{ sdo_module_entry
*/
zend_module_entry sdo_module_entry = {
#if ZEND_MODULE_API_NO >= 20050922
    STANDARD_MODULE_HEADER_EX, 
	NULL,
    sdo_deps,
#else
    STANDARD_MODULE_HEADER,
#endif
	"sdo",
	NULL, /* function list */
	PHP_MINIT(sdo),
	NULL, /* mshutdown */
	NULL, /* rinit */
	NULL, /* rshutdown */
	PHP_MINFO(sdo),
	PHP_SDO_VERSION,
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
	if (SdoRuntime::getMajor() != 0 ||
		SdoRuntime::getMinor() != 9 ||
		SdoRuntime::getFix() < 4) {
		php_error (E_WARNING, "SDO: incompatible versions of SDO extension and Tuscany SDO C++ library. Expected 00:09:0004, found %s",
			SdoRuntime::getVersion());
	}
	
	/*
	 * The sdo_das_xml was formerly shipped as a separate extension.
	 * Make sure the old extension is not loaded.
	 * TODO remove this code when ZEND_MOD_CONFLICTS is better supported.
	 */
	if (zend_get_module_version("sdo_das_xml")) {
		php_error(E_ERROR, "Cannot load module sdo because obsolete conflicting module sdo_das_xml is already loaded. Remove sdo_das_xml from you php.ini.");
		return FAILURE;
	}

	REGISTER_STRING_CONSTANT("SDO_TYPE_NAMESPACE_URI", (char *)Type::SDOTypeNamespaceURI.c_str(), CONST_CS | CONST_PERSISTENT);
	REGISTER_STRING_CONSTANT("SDO_VERSION", PHP_SDO_VERSION, CONST_CS | CONST_PERSISTENT);

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

	/* class SDO_DAS_ChangeSummary */
	INIT_CLASS_ENTRY(ce, "SDO_DAS_ChangeSummary", sdo_das_changesummary_methods);
	sdo_das_changesummary_minit(&ce TSRMLS_CC);

	/* class SDO_DAS_Setting */
	INIT_CLASS_ENTRY(ce, "SDO_DAS_Setting", sdo_das_setting_methods);
	sdo_das_setting_minit(&ce TSRMLS_CC);

	/* class SDO_DAS_SettingList implements SDO_List */
	INIT_CLASS_ENTRY(ce, "SDO_DAS_SettingList", sdo_list_methods);
	sdo_das_settinglist_class_entry = zend_register_internal_class_ex(&ce, sdo_list_class_entry, 0 TSRMLS_CC);

	/* interface SDO_Model_Type */
	INIT_CLASS_ENTRY(ce, "SDO_Model_Type", sdo_model_type_methods);
	sdo_model_type_class_entry = zend_register_internal_interface(&ce TSRMLS_CC);

	/* interface SDO_Property_Type */
	INIT_CLASS_ENTRY(ce, "SDO_Model_Property", sdo_model_property_methods);
	sdo_model_property_class_entry = zend_register_internal_interface(&ce TSRMLS_CC);

	/* class SDO_DAS_DataFactoryImpl implements SDO_DAS_DataFactory */
    INIT_CLASS_ENTRY(ce, "SDO_DAS_DataFactoryImpl", sdo_das_df_methods);
	sdo_das_df_minit(&ce TSRMLS_CC);

	/* class SDO_DataObjectImpl implements SDO_DAS_DataObject */
    INIT_CLASS_ENTRY(ce, "SDO_DataObjectImpl", sdo_dataobjectimpl_methods);
	sdo_do_minit(&ce TSRMLS_CC);

	/* class SDO_SequenceImpl implements SDO_Sequence */
    INIT_CLASS_ENTRY(ce, "SDO_SequenceImpl", sdo_sequenceimpl_methods);
	sdo_sequence_minit(&ce TSRMLS_CC);

	/* class SDO_Model_TypeImpl implements SDO_Model_Type */
    INIT_CLASS_ENTRY(ce, "SDO_Model_TypeImpl", sdo_model_typeimpl_methods);
	sdo_model_type_minit(&ce TSRMLS_CC);

	/* class SDO_Model_PropertyImpl implements SDO_Model_Property */
    INIT_CLASS_ENTRY(ce, "SDO_Model_PropertyImpl", sdo_model_propertyimpl_methods);
	sdo_model_property_minit(&ce TSRMLS_CC);

	/* class SDO_Model_ReflectionDataObject implements Reflector */
    INIT_CLASS_ENTRY(ce, "SDO_Model_ReflectionDataObject", sdo_model_reflectiondataobject_methods);
	sdo_model_rdo_minit(&ce TSRMLS_CC);

	/* class SDO_Exception extends Exception */
    INIT_CLASS_ENTRY(ce, "SDO_Exception", sdo_exception_methods);
	sdo_exception_minit(&ce TSRMLS_CC);

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

	/* class SDO_CPPException */
    INIT_CLASS_ENTRY(ce, "SDO_CPPException", sdo_cppexception_methods);
	sdo_cppexception_minit(&ce TSRMLS_CC);

	/* the SDO_DAS_XML classes */
    sdo_das_xml_minit(TSRMLS_C);
    sdo_das_xml_document_minit(TSRMLS_C);
    sdo_das_xml_parserexception_minit(TSRMLS_C);
    sdo_das_xml_fileexception_minit(TSRMLS_C);

   return SUCCESS;

}
/* }}} */

/* {{{ PHP_MINFO_FUNCTION
*/
PHP_MINFO_FUNCTION(sdo)
{
	php_info_print_table_start();
	php_info_print_table_header(2, "sdo support", "enabled");
	php_info_print_table_row(2, "sdo extension version", PHP_SDO_VERSION);
	php_info_print_table_row(2, "Tuscany sdo cpp version", SdoRuntime::getVersion());
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
