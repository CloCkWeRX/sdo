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
| Author: Anantoju V Srinivas(Srini), Matthew Peters, Caroline Maynard |
+----------------------------------------------------------------------+

*/
static char rcs_id[] = "$Id$";

#ifdef PHP_WIN32
/* disable warning about identifier lengths in browser information */
#pragma warning(disable: 4786)
#include <iostream>
#include <math.h>
#include "zend_config.w32.h"
#endif

#include "php_sdo_das_xml_int.h"

#define CLASS_NAME "SDO_DAS_XML_Document"

PHP_SDO_DAS_XML_API zend_class_entry *sdo_das_xml_document_class_entry;
static zend_object_handlers sdo_das_xml_doc_object_handlers;

/* argument definitions of SDO_DAS_XML_Document class, start */
ZEND_BEGIN_ARG_INFO(sdo_xmldoc_setXMLDeclaration_args, 0)
    ZEND_ARG_INFO(0, xml_declaration)
ZEND_END_ARG_INFO();

ZEND_BEGIN_ARG_INFO(sdo_xmldoc_setXMLVersion_args, 0)
    ZEND_ARG_INFO(0, xml_version)
ZEND_END_ARG_INFO();

ZEND_BEGIN_ARG_INFO(sdo_xmldoc_setEncoding_args, 0)
    ZEND_ARG_INFO(0, encoding)
ZEND_END_ARG_INFO();
/* argument definitions of SDO_DAS_XML_Document class, end */

/* {{{ sdo_xmldocument_methods
 *
 * Every method of SDO_DAS_XML_Document class needs to be defined here
 */
function_entry sdo_das_xml_document_methods[] = {
    ZEND_ME(SDO_DAS_XML_Document, getRootDataObject, 0, ZEND_ACC_PUBLIC)
    ZEND_ME(SDO_DAS_XML_Document, getRootElementURI, 0, ZEND_ACC_PUBLIC)
    ZEND_ME(SDO_DAS_XML_Document, getRootElementName, 0, ZEND_ACC_PUBLIC)
    ZEND_ME(SDO_DAS_XML_Document, setXMLDeclaration,
            sdo_xmldoc_setXMLDeclaration_args, ZEND_ACC_PUBLIC)
    ZEND_ME(SDO_DAS_XML_Document, setXMLVersion,
            sdo_xmldoc_setXMLVersion_args, ZEND_ACC_PUBLIC)
    ZEND_ME(SDO_DAS_XML_Document, setEncoding,
            sdo_xmldoc_setEncoding_args, ZEND_ACC_PUBLIC)
    {NULL, NULL, NULL}
};
/* }}} */

/* {{{ sdo_das_xml_document_free_storage
 */
void sdo_das_xml_document_object_free_storage(void *object TSRMLS_DC) 
{

    xmldocument_object *xmldocument = (xmldocument_object *) object;
    zend_hash_destroy(xmldocument->zo.properties);
    FREE_HASHTABLE(xmldocument->zo.properties);

	if (xmldocument->zo.guards) {
	    zend_hash_destroy(xmldocument->zo.guards);
	    FREE_HASHTABLE(xmldocument->zo.guards);
	}

    if (xmldocument->xmlDocumentPtr) {
         delete xmldocument->xmlDocumentPtr;
    }
	xmldocument->xmlDocumentPtr = NULL;
    efree(xmldocument);
}
/* }}} */

/* {{{ sdo_das_xml_document_object_create
 */
zend_object_value sdo_das_xml_document_object_create(zend_class_entry *ce TSRMLS_DC) 
{
    zend_object_value    retval;
    zval				*tmp;
    xmldocument_object	*xmldocument;
	
	xmldocument = (xmldocument_object *)emalloc(sizeof(xmldocument_object));
    memset(xmldocument, 0, sizeof(xmldocument_object));
	
    xmldocument->zo.ce = ce;
	xmldocument->zo.guards = NULL;
    ALLOC_HASHTABLE(xmldocument->zo.properties);
    zend_hash_init(xmldocument->zo.properties, 0, NULL, ZVAL_PTR_DTOR, 0);
    zend_hash_copy(xmldocument->zo.properties, &ce->default_properties,
		(copy_ctor_func_t) zval_add_ref, (void *) &tmp,
		sizeof(zval *));
    retval.handle = zend_objects_store_put(xmldocument, NULL,
		sdo_das_xml_document_object_free_storage,
		NULL TSRMLS_CC);
    retval.handlers = &sdo_das_xml_doc_object_handlers;
    return retval;
}
/* }}} */

/* {{{ sdo_das_xml_document_minit
 *
 * Initializes SDO_DAS_XML_Document class
 */
void sdo_das_xml_document_minit(TSRMLS_D) 
{
    zend_class_entry ce;
    
    INIT_CLASS_ENTRY(ce, "SDO_DAS_XML_Document", sdo_das_xml_document_methods);
    ce.create_object = sdo_das_xml_document_object_create;
    sdo_das_xml_document_class_entry = zend_register_internal_class(&ce TSRMLS_CC);

    memcpy(&sdo_das_xml_doc_object_handlers, zend_get_std_object_handlers(),
           sizeof(zend_object_handlers));
    sdo_das_xml_doc_object_handlers.clone_obj = NULL;
}
/* }}} */

/* {{{ proto SDO_DataObject SDO_DAS_XML_Document::getRootDataObject()
 Returns the root SDO_DataObject.
 */
PHP_METHOD(SDO_DAS_XML_Document, getRootDataObject) 
{
    xmldocument_object	*xmldocument;
    DataObjectPtr		 root_do;
	char				*class_name, *space;

    xmldocument = (xmldocument_object *)zend_object_store_get_object(getThis() TSRMLS_CC);

    try {
        root_do = xmldocument->xmlDocumentPtr->getRootDataObject();
		if (!root_do) {
			class_name = get_active_class_name (&space TSRMLS_CC);
            php_error(E_ERROR, "%s%s%s(): internal error(%i) - root DataObject is NULL", 
				class_name, space, get_active_function_name(TSRMLS_C), __LINE__);
            RETURN_NULL();
        }
		sdo_do_new(return_value, root_do TSRMLS_CC);
    } catch (SDORuntimeException e) {
        sdo_das_xml_throw_runtimeexception(&e TSRMLS_CC);
    }
}
/* }}} SDO_DAS_XML_Document::getRootDataObject */


/* {{{ proto string SDO_DAS_XML_Document::getRootElementURI()
  Returns root element URI string.
 */
PHP_METHOD(SDO_DAS_XML_Document, getRootElementURI) 
{
    xmldocument_object *xmldocument;

    if (ZEND_NUM_ARGS() != 0) {
        WRONG_PARAM_COUNT;
    }
    xmldocument = (xmldocument_object *)zend_object_store_get_object(getThis() TSRMLS_CC);

    try {
        RETVAL_STRING((char *)xmldocument->xmlDocumentPtr->getRootElementURI(), 1);
    } catch (SDORuntimeException e) {
        sdo_das_xml_throw_runtimeexception(&e TSRMLS_CC);
        RETVAL_NULL();
    }

}
/* }}} SDO_DAS_XML_Document::getRootElementURI */


/* {{{ proto string SDO_DAS_XML_Document::getRootElementName()
  Returns root element name.
 */
PHP_METHOD(SDO_DAS_XML_Document, getRootElementName) 
{
    xmldocument_object *xmldocument;

    if (ZEND_NUM_ARGS() != 0) {
        WRONG_PARAM_COUNT;
    }
    xmldocument = (xmldocument_object *)zend_object_store_get_object(getThis() TSRMLS_CC);

    try {
        RETVAL_STRING((char *)xmldocument->xmlDocumentPtr->getRootElementName(), 1);
    } catch (SDORuntimeException e) {
        sdo_das_xml_throw_runtimeexception(&e TSRMLS_CC);
    }

}
/* }}} SDO_DAS_XML_Document::getRootElementName */

/* {{{ proto void SDO_DAS_XML_Document::setXMLDeclaration(bool xmlDeclatation)
  Sets the xml declaration.
 */
PHP_METHOD(SDO_DAS_XML_Document, setXMLDeclaration) 
{
    zend_bool			 xml_declaration;
    xmldocument_object  *xmldocument;
	char				*class_name, *space;

    if (ZEND_NUM_ARGS() != 1) {
        WRONG_PARAM_COUNT;
    }
    if (zend_parse_parameters(ZEND_NUM_ARGS() TSRMLS_CC, "b", &xml_declaration) == FAILURE) {
        RETURN_FALSE;
    }
    xmldocument = (xmldocument_object *) zend_object_store_get_object(getThis() TSRMLS_CC);
    if (!xmldocument) {
		class_name = get_active_class_name (&space TSRMLS_CC);
		php_error(E_ERROR, "%s%s%s(): internal error (%i) - SDO_DAS_XML_Document not found in store", 
			class_name, space, get_active_function_name(TSRMLS_C), __LINE__);
        RETURN_FALSE;
    }
    try {
        xmldocument->xmlDocumentPtr->setXMLDeclaration(ZEND_TRUTH(xml_declaration));
    } catch (SDORuntimeException e) {
        sdo_das_xml_throw_runtimeexception(&e TSRMLS_CC);
    }
    return;
}
/* }}} SDO_DAS_XML_Document::setXMLDeclaration */

/* {{{ proto void SDO_DAS_XML_Document::setXMLVersion(string xmlVersion)
 Sets the given string as xml version.
 */
PHP_METHOD(SDO_DAS_XML_Document, setXMLVersion) 
{
    char				*xml_version;
    int 				 xml_version_len;
    xmldocument_object	*xmldocument;

    if (ZEND_NUM_ARGS() != 1) {
        WRONG_PARAM_COUNT;
    }
    if (zend_parse_parameters(ZEND_NUM_ARGS() TSRMLS_CC, "s", &xml_version, &xml_version_len) == FAILURE) {
        RETURN_FALSE;
    }
    xmldocument = (xmldocument_object *) zend_object_store_get_object(getThis() TSRMLS_CC);

    try {
        xmldocument->xmlDocumentPtr->setXMLVersion(xml_version);
    } catch (SDORuntimeException e) {
        sdo_das_xml_throw_runtimeexception(&e TSRMLS_CC);
    }
    return;
}
/* }}} SDO_DAS_XML_Document::setXMLVersion */

/* {{{ proto void SDO_DAS_XML_Document::setEncoding(string encoding)
 Sets the given string as encoding.
 */
PHP_METHOD(SDO_DAS_XML_Document, setEncoding) 
{
    char				*encoding;
    int 				 encoding_len;
    xmldocument_object	*xmldocument;

    if (ZEND_NUM_ARGS() != 1) {
        WRONG_PARAM_COUNT;
    }
    if (zend_parse_parameters(ZEND_NUM_ARGS() TSRMLS_CC, "s",
		&encoding, &encoding_len) == FAILURE) {
        RETURN_FALSE;
    }
    xmldocument = (xmldocument_object *)zend_object_store_get_object(getThis() TSRMLS_CC);
	
    try {
        xmldocument->xmlDocumentPtr->setEncoding(encoding);
    } catch (SDORuntimeException e) {
        sdo_das_xml_throw_runtimeexception(&e TSRMLS_CC);
    }
    return;
}
/* }}} SDO_DAS_XML_Document::setEncoding */

/*
 * Local variables:
 * tab-width: 4
 * c-basic-offset: 4
 * End:
 * vim600: noet sw=4 ts=4 fdm=marker
 * vim<600: noet sw=4 ts=4
 */
