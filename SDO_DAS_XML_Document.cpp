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
| Author: Anantoju V Srinivas (Srini)                                  |
+----------------------------------------------------------------------+

*/
#ifdef PHP_WIN32
/* disable warning about identifier lengths in browser information */
#pragma warning(disable: 4786)
#include <iostream>
#include <math.h>
#include "zend_config.w32.h"
#endif

#include "php_sdo_das_xml.h"


extern
zval* sdo_das_dfdefault_get_data_object(zend_object *me, void *doh TSRMLS_DC);

zend_class_entry* sdo_das_xml_doc_cls_entry;
zend_object_handlers sdo_das_xml_doc_object_handlers;

/* argument definations of SDO_DAS_XML_Document class, start */
ZEND_BEGIN_ARG_INFO(sdo_xmldoc_setEncoding_args, 0)
    ZEND_ARG_INFO(0, encoding)
ZEND_END_ARG_INFO();

ZEND_BEGIN_ARG_INFO(sdo_xmldoc_setXMLDeclaration_args, 0)
    ZEND_ARG_INFO(0, xmlDeclaration)
ZEND_END_ARG_INFO();

ZEND_BEGIN_ARG_INFO(sdo_xmldoc_setXMLVersion_args, 0)
    ZEND_ARG_INFO(0, xmlVersion)
ZEND_END_ARG_INFO();

ZEND_BEGIN_ARG_INFO(sdo_xmldoc_setSchemaLocation_args, 0)
    ZEND_ARG_INFO(0, schemaLocation)
ZEND_END_ARG_INFO();

ZEND_BEGIN_ARG_INFO(sdo_xmldoc_setNoNamespaceSchemaLocation_args, 0)
    ZEND_ARG_INFO(0, noNamespaceSchemaLocation)
ZEND_END_ARG_INFO();
/* argument definations of SDO_DAS_XML_Document class, end */

/* {{{ sdo_xmldocument_methods
 *
 * Every method of SDO_DAS_XML_Document class needs to be defined here
 */
function_entry sdo_das_xml_document_methods[] = {
    ZEND_ME(SDO_DAS_XML_Document, getRootDataObject, 0, ZEND_ACC_PUBLIC)
    ZEND_ME(SDO_DAS_XML_Document, getRootElementURI, 0, ZEND_ACC_PUBLIC)
    ZEND_ME(SDO_DAS_XML_Document, getRootElementName, 0, ZEND_ACC_PUBLIC)
    ZEND_ME(SDO_DAS_XML_Document, getEncoding, 0, ZEND_ACC_PUBLIC)
    ZEND_ME(SDO_DAS_XML_Document, setEncoding,
            sdo_xmldoc_setEncoding_args, ZEND_ACC_PUBLIC)
    ZEND_ME(SDO_DAS_XML_Document, getXMLDeclaration, 0, ZEND_ACC_PUBLIC)
    ZEND_ME(SDO_DAS_XML_Document, setXMLDeclaration,
            sdo_xmldoc_setXMLDeclaration_args, ZEND_ACC_PUBLIC)
    ZEND_ME(SDO_DAS_XML_Document, getXMLVersion, 0, ZEND_ACC_PUBLIC)
    ZEND_ME(SDO_DAS_XML_Document, setXMLVersion,
            sdo_xmldoc_setXMLVersion_args, ZEND_ACC_PUBLIC)
    ZEND_ME(SDO_DAS_XML_Document, getSchemaLocation, 0, ZEND_ACC_PUBLIC)
    ZEND_ME(SDO_DAS_XML_Document, setSchemaLocation,
            sdo_xmldoc_setSchemaLocation_args, ZEND_ACC_PUBLIC)
    ZEND_ME(SDO_DAS_XML_Document, getNoNamespaceSchemaLocation, 0,
            ZEND_ACC_PUBLIC)
    ZEND_ME(SDO_DAS_XML_Document, setNoNamespaceSchemaLocation,
            sdo_xmldoc_setNoNamespaceSchemaLocation_args, ZEND_ACC_PUBLIC)
    {NULL, NULL, NULL}
};
/* }}} */


/* {{{ initialize_sdo_das_xml_document_class
 *
 * Initializes SDO_DAS_XML_Document class
 */
void
initialize_sdo_das_xml_document_class(TSRMLS_D) {
    zend_class_entry ce;
    INIT_CLASS_ENTRY(ce, "SDO_DAS_XML_Document", sdo_das_xml_document_methods);
    ce.create_object = sdo_das_xml_document_object_create;
    sdo_das_xml_doc_cls_entry = zend_register_internal_class(&ce TSRMLS_CC);
    memcpy(&sdo_das_xml_doc_object_handlers, zend_get_std_object_handlers(),
           sizeof(zend_object_handlers));
    sdo_das_xml_doc_object_handlers.clone_obj = NULL;
}
/* }}} */


/* {{{ sdo_das_xml_document_object_create
 */
zend_object_value
sdo_das_xml_document_object_create(zend_class_entry *ce TSRMLS_DC) {
    zend_object_value retval;
    zval *tmp;
    xmldocument_object *obj = (xmldocument_object *)
                              emalloc(sizeof(xmldocument_object));
    memset(obj, 0, sizeof(xmldocument_object));

    obj->z_obj.ce = ce;
    obj->z_obj.in_get = 0;
    obj->z_obj.in_set = 0;
    ALLOC_HASHTABLE(obj->z_obj.properties);
    zend_hash_init(obj->z_obj.properties, 0, NULL, ZVAL_PTR_DTOR, 0);
    zend_hash_copy(obj->z_obj.properties, &ce->default_properties,
                   (copy_ctor_func_t) zval_add_ref, (void *) &tmp,
                   sizeof(zval *));
    retval.handle = zend_objects_store_put(obj, NULL,
                                           sdo_das_xml_document_object_free_storage,
                                           NULL TSRMLS_CC);
    retval.handlers = &sdo_das_xml_doc_object_handlers;
    return retval;
}
/* }}} */


/* {{{ sdo_das_xml_document_free_storage
 */
void
sdo_das_xml_document_object_free_storage(void *object TSRMLS_DC) {

    xmldocument_object *obj = (xmldocument_object *) object;
    zend_hash_destroy(obj->z_obj.properties);
    FREE_HASHTABLE(obj->z_obj.properties);

    if (obj->xdoch) {
         delete obj->xdoch;
    }
    efree(obj);
}
/* }}} */


/* {{{ proto SDO_DataObject SDO_DAS_XML_Document::getRoorDataObject()
 Returns the root SDO_DataObject.
 */
PHP_METHOD(SDO_DAS_XML_Document, getRootDataObject) {
    zval *this_obj = getThis();
    xmldocument_object *obj = NULL;
    zval *retval = NULL;
    zend_object *php_das_obj = NULL;
    DataObject *root_do = NULL;
    obj = (xmldocument_object *)
          zend_object_store_get_object(this_obj TSRMLS_CC);
    if (!obj) {
        php_error(E_ERROR,
                  "SDO_DAS_XML_Document::getRootDataObject - Unable to get SDO_DAS_XML_Document Object from object store");
        RETURN_NULL();
    }
    try {
        root_do = obj->xdoch->getRootDataObject();
        retval = (zval *) root_do->getUserData();
        if (!retval) {
            php_error(E_ERROR,
                      "SDO_DAS_XML_Document::getRootDataObject - Unable to find php version of root DataObject");
            RETURN_NULL();
        }
    } catch (SDORuntimeException e) {
        sdo_das_xml_throw_runtimeexception(&e TSRMLS_CC);
    }
    RETVAL_ZVAL(retval, 1, 0);
}
/* }}} SDO_DAS_XML_Document::getRootDataObject */


/* {{{ proto string SDO_DAS_XML_Document::getRootElementURI()
  Returns root element URI string.
 */
PHP_METHOD(SDO_DAS_XML_Document, getRootElementURI) {
    const char *retval = NULL;
    int len = 0;
    zval *this_obj = getThis();
    xmldocument_object *obj = NULL;
    if (ZEND_NUM_ARGS() != 0) {
        WRONG_PARAM_COUNT;
    }
    obj = (xmldocument_object *)zend_object_store_get_object(this_obj TSRMLS_CC);
    if (!obj) {
        php_error(E_ERROR,
                  "SDO_DAS_XML_Document::getRootElementURI - Unable to get SDO_DAS_XML_Document Object from object store");
        RETURN_NULL();
    }
    try {
        retval = obj->xdoch->getRootElementURI();
    } catch (SDORuntimeException e) {
        sdo_das_xml_throw_runtimeexception(&e TSRMLS_CC);
        RETURN_NULL();
    }
    if (retval) {
        RETVAL_STRING((char *)retval, 1);
    } else {
        RETVAL_NULL();
    }
}
/* }}} SDO_DAS_XML_Document::getRootElementURI */


/* {{{ proto string SDO_DAS_XML_Document::getRootElementName()
  Returns root element name.
 */
PHP_METHOD(SDO_DAS_XML_Document, getRootElementName) {
    const char *retval = NULL;
    int len = 0;
    zval *this_obj = getThis();
    xmldocument_object *obj = NULL;
    if (ZEND_NUM_ARGS() != 0) {
        WRONG_PARAM_COUNT;
    }
    obj = (xmldocument_object *)zend_object_store_get_object(this_obj TSRMLS_CC);
    if (!obj) {
        php_error(E_ERROR,
                  "SDO_DAS_XML_Document::getRootElementName - Unable to get SDO_DAS_XML_Document Object from object store");
        RETURN_NULL();
    }
    try {
        retval = obj->xdoch->getRootElementName();
    } catch (SDORuntimeException e) {
        sdo_das_xml_throw_runtimeexception(&e TSRMLS_CC);
    }
    if (retval) {
        RETVAL_STRING((char *)retval, 1);
    } else {
        RETVAL_NULL();
    }
}
/* }}} SDO_DAS_XML_Document::getRootElementName */


/* {{{ proto string SDO_DAS_XML_Document::getEncoding()
 Returns encoding string.
 */
PHP_METHOD(SDO_DAS_XML_Document, getEncoding) {
    const char *retval = NULL;
    int len = 0;
    zval *this_obj = getThis();
    xmldocument_object *obj = NULL;
    if (ZEND_NUM_ARGS() != 0) {
        WRONG_PARAM_COUNT;
    }
    obj = (xmldocument_object *)zend_object_store_get_object(this_obj TSRMLS_CC);
    if (!obj) {
        php_error(E_ERROR,
                  "SDO_DAS_XML_Document::getEncoding - Unable to get SDO_DAS_XML_Document Object from object store");
        RETURN_NULL();
    }
    try {
        retval = obj->xdoch->getEncoding();
    } catch (SDORuntimeException e) {
        sdo_das_xml_throw_runtimeexception(&e TSRMLS_CC);
    }
    if (retval) {
        RETVAL_STRING((char *)retval, 1);
    } else {
        RETVAL_NULL();
    }
}
/* }}} SDO_DAS_XML_Document::getEncoding */


/* {{{ proto boolean SDO_DAS_XML_Document::getXMLDeclaration()
 Returns whethe xml declaration is set or not.
 */
PHP_METHOD(SDO_DAS_XML_Document, getXMLDeclaration) {
    bool retval;
    int len = 0;
    zval *this_obj = getThis();
    xmldocument_object *obj = NULL;
    if (ZEND_NUM_ARGS() != 0) {
        WRONG_PARAM_COUNT;
    }
    obj = (xmldocument_object *)zend_object_store_get_object(this_obj TSRMLS_CC);
    if (!obj) {
        php_error(E_ERROR,
                  "SDO_DAS_XML_Document::getXMLDeclaration - Unable to get SDO_DAS_XML_Document Object from object store");
        RETURN_NULL();
    }
    try {
        retval = obj->xdoch->getXMLDeclaration();
    } catch (SDORuntimeException e) {
        sdo_das_xml_throw_runtimeexception(&e TSRMLS_CC);
    }
    RETVAL_BOOL(retval);
}
/* }}} SDO_DAS_XML_Document::getXMLDeclaration */


/* {{{ proto string SDO_DAS_XML_Document::getXMLVersion()
 Returns xml declaration string.
 */
PHP_METHOD(SDO_DAS_XML_Document, getXMLVersion) {
    const char *retval = NULL;
    int len = 0;
    zval *this_obj = getThis();
    xmldocument_object *obj = NULL;
    if (ZEND_NUM_ARGS() != 0) {
        WRONG_PARAM_COUNT;
    }
    obj = (xmldocument_object *)zend_object_store_get_object(this_obj TSRMLS_CC);
    if (!obj) {
        php_error(E_ERROR,
                  "SDO_DAS_XML_Document::getXMLVersion - Unable to get SDO_DAS_XML_Document Object from object store");
        RETURN_NULL();
    }
    try {
        retval = obj->xdoch->getXMLVersion();
    } catch (SDORuntimeException e) {
        sdo_das_xml_throw_runtimeexception(&e TSRMLS_CC);
    }
    if (retval) {
        RETVAL_STRING((char *)retval, 1);
    } else {
        RETVAL_NULL();
    }
}
/* }}} SDO_DAS_XML_Document::getXMLVersion */


/* {{{ proto string SDO_DAS_XML_Document::getSchemaLocation()
 Returns schema location.
 */
PHP_METHOD(SDO_DAS_XML_Document, getSchemaLocation) {
    const char *retval = NULL;
    int len = 0;
    zval *this_obj = getThis();
    xmldocument_object *obj = NULL;
    if (ZEND_NUM_ARGS() != 0) {
        WRONG_PARAM_COUNT;
    }
    obj = (xmldocument_object *)zend_object_store_get_object(this_obj TSRMLS_CC);
    if (!obj) {
        php_error(E_ERROR,
                  "SDO_DAS_XML_Document::getSchemaLocation - Unable to get SDO_DAS_XML_Document Object from object store");
        RETURN_NULL();
    }
    try {
        retval = obj->xdoch->getSchemaLocation();
    } catch (SDORuntimeException e) {
        sdo_das_xml_throw_runtimeexception(&e TSRMLS_CC);
    }
    if (retval) {
        RETVAL_STRING((char *)retval, 1);
    } else {
        RETVAL_NULL();
    }
}
/* }}} SDO_DAS_XML_Document::getSchemaLocation */


/* {{{ proto string SDO_DAS_XML_Document::getNoNamespaceSchemaLocation()
 Returns no namespace schema location.
 */
PHP_METHOD(SDO_DAS_XML_Document, getNoNamespaceSchemaLocation) {
    const char *retval = NULL;
    int len = 0;
    zval *this_obj = getThis();
    xmldocument_object *obj = NULL;
    if (ZEND_NUM_ARGS() != 0) {
        WRONG_PARAM_COUNT;
    }
    obj = (xmldocument_object *)zend_object_store_get_object(this_obj TSRMLS_CC);
    if (!obj) {
        php_error(E_ERROR,
                  "SDO_DAS_XML_Document::getNoNamespaceSchemaLocation - Unable to get SDO_DAS_XML_Document Object from object store");
        RETURN_NULL();
    }
    try {
        retval = obj->xdoch->getNoNamespaceSchemaLocation();
    } catch (SDORuntimeException e) {
        sdo_das_xml_throw_runtimeexception(&e TSRMLS_CC);
    }
    if (retval) {
        RETVAL_STRING((char *)retval, 1);
    } else {
        RETVAL_NULL();
    }
}
/* }}} SDO_DAS_XML_Document::getNoNamespaceSchemaLocation */


/* {{{ proto void SDO_DAS_XML_Document::setXMLDeclaration(bool xmlDeclatation)
  Sets the xml declaration.
 */
PHP_METHOD(SDO_DAS_XML_Document, setXMLDeclaration) {
    bool xmlDeclaration;
    zval *this_obj = getThis();
    xmldocument_object *obj = NULL;
    if (ZEND_NUM_ARGS() != 1) {
        WRONG_PARAM_COUNT;
    }
    if (zend_parse_parameters(ZEND_NUM_ARGS() TSRMLS_CC, "b", &xmlDeclaration) == FAILURE) {
        RETURN_FALSE;
    }
    obj = (xmldocument_object *) zend_object_store_get_object(this_obj TSRMLS_CC);
    if (!obj) {
        php_error(E_ERROR,
                  "SDO_DAS_XML_Document::setXMLDeclaration - Unable to get SDO_DAS_XML_Document Object from object store");
        RETURN_FALSE;
    }
    try {
        obj->xdoch->setXMLDeclaration(xmlDeclaration);
    } catch (SDORuntimeException e) {
        sdo_das_xml_throw_runtimeexception(&e TSRMLS_CC);
    }
    return;
}
/* }}} SDO_DAS_XML_Document::setXMLDeclaration */


/* {{{ proto void SDO_DAS_XML_Document::setXMLVersion(string xmlVersion)
 Sets the given string as xml version.
 */
PHP_METHOD(SDO_DAS_XML_Document, setXMLVersion) {
    char *xmlVersion = NULL;
    int len;
    zval *this_obj = getThis();
    xmldocument_object *obj = NULL;
    if (ZEND_NUM_ARGS() != 1) {
        WRONG_PARAM_COUNT;
    }
    if (zend_parse_parameters(ZEND_NUM_ARGS() TSRMLS_CC, "s", &xmlVersion, &len) == FAILURE) {
        RETURN_FALSE;
    }
    obj = (xmldocument_object *) zend_object_store_get_object(this_obj TSRMLS_CC);
    if (!obj) {
        php_error(E_ERROR,
                  "SDO_DAS_XML_Document::setXMLVersion - Unable to get SDO_DAS_XML_Document Object from object store");
        RETURN_NULL();
    }
    try {
        obj->xdoch->setXMLVersion(xmlVersion);
    } catch (SDORuntimeException e) {
        sdo_das_xml_throw_runtimeexception(&e TSRMLS_CC);
    }
    return;
}
/* }}} SDO_DAS_XML_Document::setXMLVersion */


/* {{{ proto void SDO_DAS_XML_Document::setSchemaLocation(string schemaLocation)
 Sets the given string as schema location.
 */
PHP_METHOD(SDO_DAS_XML_Document, setSchemaLocation) {
    char *schemaLocation = NULL;
    int len;
    zval *this_obj = getThis();
    xmldocument_object *obj = NULL;
    if (ZEND_NUM_ARGS() != 1) {
        WRONG_PARAM_COUNT;
    }
    if (zend_parse_parameters(ZEND_NUM_ARGS() TSRMLS_CC, "s", &schemaLocation, &len) == FAILURE) {
        RETURN_FALSE;
    }
    obj = (xmldocument_object *) zend_object_store_get_object(this_obj TSRMLS_CC);
    if (!obj) {
        php_error(E_ERROR,
                  "SDO_DAS_XML_Document::setSchemaLocation - Unable to get SDO_DAS_XML_Document Object from object store");
        RETURN_NULL();
    }
    try {
        obj->xdoch->setSchemaLocation(schemaLocation);
    } catch (SDORuntimeException e) {
        sdo_das_xml_throw_runtimeexception(&e TSRMLS_CC);
    }
    return;
}
/* }}} SDO_DAS_XML_Document::setSchemaLocation */


/* {{{ proto void SDO_DAS_XML_Document::setNoNamespaceSchemaLocation(string nnschemaLocation)
 Sets the given string as no namespace schema location.
 */
PHP_METHOD(SDO_DAS_XML_Document, setNoNamespaceSchemaLocation) {
    char *nnSchemaLocation = NULL;
    int len;
    zval *this_obj = getThis();
    xmldocument_object *obj = NULL;
    if (ZEND_NUM_ARGS() != 1) {
        WRONG_PARAM_COUNT;
    }
    if (zend_parse_parameters(ZEND_NUM_ARGS() TSRMLS_CC, "s",
                              &nnSchemaLocation, &len) == FAILURE) {
        RETURN_FALSE;
    }
    obj = (xmldocument_object *)
          zend_object_store_get_object(this_obj TSRMLS_CC);
    if (!obj) {
        php_error(E_ERROR,
                  "SDO_DAS_XML_Document::setNoNamespaceSchemaLocation - Unable to get SDO_DAS_XML_Document Object from object store");
        RETURN_NULL();
    }
    try {
        obj->xdoch->setNoNamespaceSchemaLocation(nnSchemaLocation);
    } catch (SDORuntimeException e) {
        sdo_das_xml_throw_runtimeexception(&e TSRMLS_CC);
    }
}
/* }}} SDO_DAS_XML_Document::setNoNamespaceSchemaLocation */


/* {{{ proto void SDO_DAS_XML_Document::setEncoding(string encoding)
 Sets the given string as encoding.
 */
PHP_METHOD(SDO_DAS_XML_Document, setEncoding) {
    char *encoding = NULL;
    int len;
    zval *this_obj = getThis();
    xmldocument_object *obj = NULL;
    if (ZEND_NUM_ARGS() != 1) {
        WRONG_PARAM_COUNT;
    }
    if (zend_parse_parameters(ZEND_NUM_ARGS() TSRMLS_CC, "s",
                              &encoding, &len) == FAILURE) {
        RETURN_FALSE;
    }
    obj = (xmldocument_object *)
          zend_object_store_get_object(this_obj TSRMLS_CC);
    if (!obj) {
        php_error(E_ERROR,
                  "SDO_DAS_XML_Document::setEncoding - Unable to get SDO_DAS_XML_Document Object from object store");
        RETURN_NULL();
    }

    try {
        obj->xdoch->setNoNamespaceSchemaLocation(encoding);
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
