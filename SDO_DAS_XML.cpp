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
| Author: Anantoju V Srinivas (Srini), Matthew Peters                  |
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

#include "zend_interfaces.h" // needed for several uses of zend_call_method() 

/******************* Forward declarations for this module only **********************/
zend_object_value	sdo_das_xml_object_create		(zend_class_entry *ce TSRMLS_DC);
void 				sdo_das_xml_object_free_storage	(void *object TSRMLS_DC);

#define PARSER_EXCEPTION_MSG_LEN 1000

zend_class_entry*     sdo_das_xml_class_entry;
zend_object_handlers  sdo_das_xml_object_handlers;

/* argument definitions of SDO_DAS_XML class, start */
ZEND_BEGIN_ARG_INFO(sdo_das_xml___construct_args, 0)
    ZEND_ARG_INFO(0, schema)
ZEND_END_ARG_INFO();

ZEND_BEGIN_ARG_INFO_EX(sdo_das_xml_create_args, 0, ZEND_RETURN_VALUE, 1)
    ZEND_ARG_INFO(0, schema)
ZEND_END_ARG_INFO();

ZEND_BEGIN_ARG_INFO(sdo_das_xml_addTypes_args, 0)
    ZEND_ARG_INFO(0, schema)
ZEND_END_ARG_INFO();

ZEND_BEGIN_ARG_INFO(sdo_das_xml_loadFile_args, 0)
    ZEND_ARG_INFO(0, fileName)
ZEND_END_ARG_INFO();

ZEND_BEGIN_ARG_INFO(sdo_das_xml_loadString_args, 0)
    ZEND_ARG_INFO(0, xmlString)
ZEND_END_ARG_INFO();

ZEND_BEGIN_ARG_INFO(sdo_das_xml_createdo_args, 0)
    ZEND_ARG_INFO(0, nameSpaceURI)
    ZEND_ARG_INFO(0, typeName)
ZEND_END_ARG_INFO();

ZEND_BEGIN_ARG_INFO(sdo_das_xml_saveDocString_args, 0)
    ZEND_ARG_INFO(0, docObj)
ZEND_END_ARG_INFO();

ZEND_BEGIN_ARG_INFO(sdo_das_xml_saveDocFile_args, 0)
    ZEND_ARG_INFO(0, docObj)
    ZEND_ARG_INFO(0, xmlFile)
ZEND_END_ARG_INFO();

ZEND_BEGIN_ARG_INFO(sdo_das_xml_saveDOString_args, 0)
    ZEND_ARG_INFO(0, dataobj)
    ZEND_ARG_INFO(0, rootURI)
    ZEND_ARG_INFO(0, rootName)
ZEND_END_ARG_INFO();

ZEND_BEGIN_ARG_INFO(sdo_das_xml_saveDOFile_args, 0)
    ZEND_ARG_INFO(0, dataobj)
    ZEND_ARG_INFO(0, rootURI)
    ZEND_ARG_INFO(0, rootName)
    ZEND_ARG_INFO(0, xmlFile)
ZEND_END_ARG_INFO();

ZEND_BEGIN_ARG_INFO(sdo_das_xml_getProperty_args, 0)
    ZEND_ARG_INFO(0, nameSpaceURI)
    ZEND_ARG_INFO(0, typeName)
ZEND_END_ARG_INFO();

ZEND_BEGIN_ARG_INFO(sdo_das_xml_defineFromFile_args, 0)
    ZEND_ARG_INFO(0, schema)
ZEND_END_ARG_INFO();

/* argument definitions of SDO_DAS_XML class, end */

/* {{{ SDO_DAS_XML Class methods
 */
function_entry sdo_das_xml_methods[] = {
    ZEND_ME(SDO_DAS_XML, __construct, sdo_das_xml___construct_args,
            ZEND_ACC_PRIVATE)
    ZEND_ME(SDO_DAS_XML, create, sdo_das_xml_create_args,
            ZEND_ACC_PUBLIC|ZEND_ACC_STATIC)
    ZEND_ME(SDO_DAS_XML, addTypes, sdo_das_xml_addTypes_args,
            ZEND_ACC_PUBLIC)
    ZEND_ME(SDO_DAS_XML, loadFromFile, sdo_das_xml_loadFile_args,
            ZEND_ACC_PUBLIC)
    ZEND_ME(SDO_DAS_XML, loadFromString, sdo_das_xml_loadString_args,
            ZEND_ACC_PUBLIC)
    ZEND_ME(SDO_DAS_XML, saveDocumentToString, sdo_das_xml_saveDocString_args,
            ZEND_ACC_PUBLIC)
    ZEND_ME(SDO_DAS_XML, saveDocumentToFile, sdo_das_xml_saveDocFile_args,
            ZEND_ACC_PUBLIC)
    ZEND_ME(SDO_DAS_XML, saveDataObjectToString, sdo_das_xml_saveDOString_args,
            ZEND_ACC_PUBLIC)
    ZEND_ME(SDO_DAS_XML, saveDataObjectToFile, sdo_das_xml_saveDOFile_args,
            ZEND_ACC_PUBLIC)
    ZEND_ME(SDO_DAS_XML, createDataObject, sdo_das_xml_createdo_args,
            ZEND_ACC_PUBLIC)
//    ZEND_ME(SDO_DAS_XML, getProperty, sdo_das_xml_getProperty_args,
//            ZEND_ACC_PUBLIC)
    {NULL, NULL, NULL}
};
/* }}} */

/* {{{ initialize_sdo_das_xml_class
 */
void initialize_sdo_das_xml_class(TSRMLS_D) 
{
    zend_class_entry ce;

    INIT_CLASS_ENTRY(ce, "SDO_DAS_XML", sdo_das_xml_methods);
    ce.create_object = sdo_das_xml_object_create;
    sdo_das_xml_class_entry = zend_register_internal_class(&ce TSRMLS_CC);
    memcpy(&sdo_das_xml_object_handlers, zend_get_std_object_handlers(),
           sizeof(zend_object_handlers));
    sdo_das_xml_object_handlers.clone_obj = NULL;
}
/* }}} */


/* {{{ sdo_das_xml_object_create
 */
zend_object_value sdo_das_xml_object_create(zend_class_entry *ce TSRMLS_DC) 
{
    zend_object_value retval;
    zval *tmp;
    xmldas_object *obj = (xmldas_object *) emalloc(sizeof(xmldas_object));
    memset(obj, 0, sizeof(xmldas_object));

    obj->z_obj.ce = ce;
    ALLOC_HASHTABLE(obj->z_obj.properties);
    zend_hash_init(obj->z_obj.properties, 0, NULL, ZVAL_PTR_DTOR, 0);
    zend_hash_copy(obj->z_obj.properties, &ce->default_properties,
                   (copy_ctor_func_t) zval_add_ref, (void *) &tmp,
                   sizeof(zval *));
    retval.handle = zend_objects_store_put(obj, NULL,
                                           sdo_das_xml_object_free_storage,
                                           NULL TSRMLS_CC);
    retval.handlers = &sdo_das_xml_object_handlers;
    return retval;
}
/* }}} */

/* {{{ sdo_das_xml_object_free_storage
 */
void sdo_das_xml_object_free_storage(void *object TSRMLS_DC)
{
    /*
     * Frees up the SDO_DAS_XML object also decrements reference to
     * PHP version of SDO_DAS_DataFactory
     */
    sdo_das_df_object *das_obj;
    xmldas_object *obj = (xmldas_object *) object;
    zend_hash_destroy(obj->z_obj.properties);
    FREE_HASHTABLE(obj->z_obj.properties);

    if (obj->php_das_df) {
        das_obj = (sdo_das_df_object *)
                  zend_object_store_get_object(obj->php_das_df TSRMLS_CC);

        if(das_obj) {
            das_obj->dfp = NULL;
        }
        zend_objects_store_del_ref(obj->php_das_df TSRMLS_CC);
    }
    efree(obj);
}
/* }}} */

/* {{{ create_dataobject_tree
 */
void create_dataobject_tree(DataObjectPtr data_object TSRMLS_DC) 
{
    zval    *z_obj;
    zend_object *php_das_obj = NULL;
    MAKE_STD_ZVAL(z_obj);
    if (!data_object) {
        return;
        // tempting to raise an error as this should never happen - not going to do it though
        // php_error(E_ERROR, "SDO_DAS_XML::create_dataobject_tree was passed a null argument");
    }
    if (data_object->getUserData() != (void*) 0xFFFFFFFF) {
        // if the user data is set then we have encountered this object before
        // and already created a PHP object corresponding to it.
        // This will happen when we follow a reference to an object that 
        // was already visited following a different reference: for example following the
        // employee_of_the_month non-containment reference to an employee who has already been 
        // visited by following the department->employee containmment reference.
        // Since we already have a corresponding object, return without creating another.
        return;
    }
    sdo_do_new(z_obj, data_object TSRMLS_CC);
    // not sure whether any need to increase the reference count?
    // zend_objects_store_add_ref(z_obj TSRMLS_CC);
    const Type& data_object_type = data_object->getType();
    PropertyList pl = data_object->getInstanceProperties();
    for (int i = 0; i < pl.size(); i++) {
        const Type& property_type = pl[i].getType();
        if (data_object->isSet(pl[i])) {
            if (pl[i].isMany()) {
                DataObjectList& dol = data_object->getList(pl[i]);
                for (int j = 0; j <dol.size(); j++) {
                    create_dataobject_tree(dol[j] TSRMLS_CC);
                }
            } else {
                if (!property_type.isDataType()) {
                     create_dataobject_tree(data_object->getDataObject(pl[i])
                                             TSRMLS_CC);
                }
            }
        }
    }
}
/* }}} */

/* {{{ PHP_METHOD(SDO_DAS_XML, __construct)
 */
PHP_METHOD(SDO_DAS_XML, __construct)
{
    php_error(E_ERROR, "SDO_DAS_XML::__construct - you cannot instantiate SDO_DAS_XML by calling the constructor. Use SDO_DAS_XML::create(xsd) static function instead.");
}
/* }}} */

/* {{{ proto SDO_DAS_XML SDO_DAS_XML::addTypes(string xsd_file)
 */
PHP_METHOD(SDO_DAS_XML, addTypes) 
{
    xmldas_object*	xmldas = NULL;
    char* 			file_name;
    int 			len = 0;
    zval *			retval = NULL;
    zend_class_entry *ce = NULL;
    zval *this_obj = getThis();

	// TODO lots of code duplicated with create()
	
    if (ZEND_NUM_ARGS() != 1) {
        WRONG_PARAM_COUNT;
    }
    if (zend_parse_parameters(ZEND_NUM_ARGS() TSRMLS_CC, "s", &file_name,&len) == FAILURE) {
        RETURN_FALSE;
    }
    if (!len) {
    	RETURN_FALSE;
    }
    
    xmldas = (xmldas_object *) zend_object_store_get_object(this_obj TSRMLS_CC);
    if(!xmldas) {
        php_error(E_ERROR, "SDO_DAS_XML::addTypes - Unable to get XMLDAS object from object store");
    }
    try {
        xmldas->targetNamespaceURI = xmldas->xsdHelper->defineFile(file_name);

        int error_count = xmldas->xsdHelper->getErrorCount();
        if (error_count > 0) { 
        	// TODO so sorry that this code is repeated in slightly differing forms four times now. They vary with
        	// line 1 of the message and whether they call the xsdhelper or the xml helper for the messages
        	int 	max_errors = 50;
        	int 	total_msg_len = 0;
        	int 	error_ix = 0;
        	char* 	buffer; 						// to contain text of parser exception message: will be emalloc'd and efree'd in this block
        	char 	parser_exception_line_1[] 		= "SDO_DAS_XML::addTypes - Unable to parse the supplied xsd file\n";
        	char 	parser_exception_line_2[1000];  // nice and long in case there is a very long file name

        	if (error_count > max_errors) {
				sprintf(parser_exception_line_2,"%d parse error(s) occured when parsing the file \"%s\"  (only the first %d shown):\n",error_count, file_name, max_errors);
        	} else {
				sprintf(parser_exception_line_2,"%d parse error(s) occured when parsing the file \"%s\":\n",error_count, file_name);            	
        	}
        	total_msg_len = strlen(parser_exception_line_1) + strlen(parser_exception_line_2);
        	for (error_ix = 0; error_ix < min(error_count,max_errors); error_ix++) {
        		total_msg_len += strlen(xmldas->xsdHelper->getErrorMessage(error_ix));
        	}
        	// TODO improve on this temporary fix (+1) for 0.7.0
        	buffer = (char*) emalloc(total_msg_len+1);
        	strcpy(buffer, parser_exception_line_1);
        	strcat(buffer, parser_exception_line_2);
        	for (error_ix = 0; error_ix < min(error_count,max_errors); error_ix++) {
        		strcat(buffer, xmldas->xsdHelper->getErrorMessage(error_ix));
        	}
			sdo_das_xml_throw_parserexception(buffer TSRMLS_CC);
			efree(buffer);
	        RETURN_NULL();
        }            
    } catch (SDOFileNotFoundException e) {
        sdo_das_xml_throw_fileexception(file_name TSRMLS_CC);
        RETURN_NULL();
    } catch (SDOXMLParserException *e) {
    	const char* msg = e->getMessageText();
        sdo_das_xml_throw_parserexception((char*)msg TSRMLS_CC);
        RETURN_NULL();            
    } catch (SDORuntimeException e) {
        sdo_das_xml_throw_runtimeexception(&e TSRMLS_CC);
        RETURN_NULL();
    }

}
/* }}} */


/* {{{ proto SDO_DAS_XML SDO_DAS_XML::create(string xsd_file)
 */
PHP_METHOD(SDO_DAS_XML, create) 
{
    /*****************************************************************************
     *  This is the only static method of SDO_DAS_XML class. It is used to instantiate
     *  the SDO_DAS_XML object.
     *  Returns :
     *  SDO_DAS_XML object with model information built-in 
     *****************************************************************************/
    xmldas_object*	xmldas = NULL;
    char* 			file_name;
    int 			len = 0;
    zval *			retval = NULL;
    zend_class_entry *ce = NULL;
    sdo_das_df_object *das_df_obj;
    DataFactoryPtr 	dataFactory;

    if (ZEND_NUM_ARGS() > 1) {
        WRONG_PARAM_COUNT;
    }
    
    Z_TYPE_P(return_value) = IS_OBJECT;
    if (object_init_ex(return_value, sdo_das_xml_class_entry) == FAILURE) {
        php_error(E_ERROR, "SDO_DAS_XML::create - Unable to create SDO_DAS_XML");
        return;
    }
    
    xmldas = (xmldas_object *) zend_object_store_get_object(return_value TSRMLS_CC);

    dataFactory = DataFactory::getDataFactory();
    xmldas->xsdHelper = HelperProvider::getXSDHelper((DataFactory*)dataFactory);
    xmldas->xmlHelper = HelperProvider::getXMLHelper((DataFactory*)dataFactory);

	if (ZEND_NUM_ARGS() == 1) {
	    if (zend_parse_parameters(ZEND_NUM_ARGS() TSRMLS_CC, "s", &file_name,&len) == FAILURE) {
	        RETURN_FALSE;
	    }
	    if(!len) {
	        RETURN_FALSE;
	    }	
	
	    try {
	        xmldas->targetNamespaceURI = xmldas->xsdHelper->defineFile(file_name);
	
	        int error_count = xmldas->xsdHelper->getErrorCount();
	        if (error_count > 0) {
	        	int 	max_errors = 50;
	        	int 	total_msg_len = 0;
	        	int 	error_ix = 0;
	        	char* 	buffer; 						// to contain text of parser exception message: will be emalloc'd and efree'd in this block
	        	char 	parser_exception_line_1[] 		= "SDO_DAS_XML::create - Unable to parse the supplied xsd file\n";
	        	char 	parser_exception_line_2[1000];  // nice and long in case there is a very long file name
	
	        	if (error_count > max_errors) {
					sprintf(parser_exception_line_2,"%d parse error(s) occured when parsing the file \"%s\"  (only the first %d shown):\n",error_count, file_name, max_errors);
	        	} else {
					sprintf(parser_exception_line_2,"%d parse error(s) occured when parsing the file \"%s\":\n",error_count, file_name);            	
	        	}
	        	total_msg_len = strlen(parser_exception_line_1) + strlen(parser_exception_line_2);
	        	for (error_ix = 0; error_ix < min(error_count,max_errors); error_ix++) {
	        		total_msg_len += strlen(xmldas->xsdHelper->getErrorMessage(error_ix));
	        	}
	        	// TODO improve on this temporary fix (+1) for 0.7.0
	        	buffer = (char*) emalloc(total_msg_len+1);
	        	strcpy(buffer, parser_exception_line_1);
	        	strcat(buffer, parser_exception_line_2);
	        	for (error_ix = 0; error_ix < min(error_count,max_errors); error_ix++) {
	        		strcat(buffer, xmldas->xsdHelper->getErrorMessage(error_ix));
	        	}
				sdo_das_xml_throw_parserexception(buffer TSRMLS_CC);
				efree(buffer);
		        RETURN_NULL();
	        }            
	    } catch (SDOFileNotFoundException e) {
	        sdo_das_xml_throw_fileexception(file_name TSRMLS_CC);
	        RETURN_NULL();
	    } catch (SDOXMLParserException *e) {
	    	const char* msg = e->getMessageText();
	        sdo_das_xml_throw_parserexception((char*)msg TSRMLS_CC);
	        RETURN_NULL();            
	    } catch (SDORuntimeException e) {
	        sdo_das_xml_throw_runtimeexception(&e TSRMLS_CC);
	        RETURN_NULL();
	    }
	}

    /********************************************************************
     * Instantiate an SDO_DAS_DataFactoryImpl object now. Fill in with the 
     * dataFactory that we created above.
     ********************************************************************/
    ce = zend_fetch_class("SDO_DAS_DataFactoryImpl",
                          strlen("SDO_DAS_DataFactoryImpl"),
                          ZEND_FETCH_CLASS_AUTO TSRMLS_CC);
    if (!ce) {
        php_error(E_ERROR, "SDO_DAS_XML::create - Unable to fetch SDO_DAS_DataFactoryImpl class");
        RETURN_NULL();
    }
    MAKE_STD_ZVAL(retval);
    if (object_init_ex(retval, ce) == FAILURE) {
        php_error(E_ERROR, "SDO_DAS_XML::create - Unable to create SDO_DAS_DataFactoryImpl");
        return;
    }
    das_df_obj = (sdo_das_df_object *) zend_object_store_get_object(retval TSRMLS_CC);
    if(!das_df_obj) {
        php_error(E_ERROR, "SDO_DAS_XML::create - Unable to get SDO_DAS_DataFactory object from object store");
    }
    das_df_obj->dfp = dataFactory;  
    xmldas->php_das_df = retval;
    zend_objects_store_add_ref(xmldas->php_das_df TSRMLS_CC);
    
}
/* }}} end SDO_DAS_XML::create */

/* {{{ proto SDO_XMLDocument SDO_DAS_XML::loadFromFile(string xml_file)
 */
PHP_METHOD(SDO_DAS_XML, loadFromFile) 
{
    /*
     * Returns XMLDocument Object containing root SDO object built from the
     * given path to xml instance document.
     */
    xmldocument_object *doc_obj = NULL;
    xmldas_object *xmldas_obj = NULL;
    char* file_name;
    int len = 0;
    zval *this_obj = getThis();
    zend_class_entry *ce = NULL;

    if (ZEND_NUM_ARGS() != 1) {
        WRONG_PARAM_COUNT;
    }
    if (zend_parse_parameters(ZEND_NUM_ARGS() TSRMLS_CC, "s", &file_name, &len) == FAILURE) {
        RETURN_FALSE;
    }
    if (len) {
        Z_TYPE_P(return_value) = IS_OBJECT;
        ce = zend_fetch_class("SDO_DAS_XML_Document",
                              strlen("SDO_DAS_XML_Document"),
                              ZEND_FETCH_CLASS_AUTO TSRMLS_CC);
        if (object_init_ex(return_value, ce) == FAILURE) {
            php_error(E_ERROR,"SDO_DAS_XML::loadFile - Unable to create SDO_DAS_XML");
            RETURN_NULL();
        }
        doc_obj = (xmldocument_object *) zend_object_store_get_object(return_value TSRMLS_CC);
        if (!doc_obj) {
            php_error(E_ERROR, "SDO_DAS_XML::loadFile - Unable to get XMLDocument object from object store");
            RETURN_NULL();
        }
        xmldas_obj = (xmldas_object *) zend_object_store_get_object(this_obj TSRMLS_CC);
        if(!xmldas_obj) {
            php_error(E_ERROR, "SDO_DAS_XML::loadFromFile - Unable to get XMLDAS object from object store");
        }
        try {
            doc_obj->xdoch = xmldas_obj->xmlHelper->loadFile(file_name,xmldas_obj->targetNamespaceURI);
            if (doc_obj->xdoch && doc_obj->xdoch->getRootDataObject() && (xmldas_obj->xmlHelper->getErrorCount() == 0)) {
                create_dataobject_tree(doc_obj->xdoch->getRootDataObject() TSRMLS_CC);
            } else {
            	char parser_exception_msg[PARSER_EXCEPTION_MSG_LEN] = "SDO_DAS_XML::loadFromFile - Unable to obtain a root data object from the supplied XML\n";
            	int error_count = xmldas_obj->xmlHelper->getErrorCount();
			    if (error_count > 0) {
			    	char parse_error_hdr[100];
					sprintf(parse_error_hdr,"%d parse error(s) occured when parsing the XML file \"%s\":\n",error_count, file_name);
					strcat(parser_exception_msg,parse_error_hdr);
					for (int error_index=0;error_index<error_count;error_index++) {
						const char * parse_error_msg = xmldas_obj->xmlHelper->getErrorMessage(error_index);
						if (strlen(parser_exception_msg) + strlen(parse_error_msg) + 10 < PARSER_EXCEPTION_MSG_LEN) {
							char msg_number[10];
							sprintf(msg_number,"%d. ",error_index);
							strcat (parser_exception_msg, msg_number);
							strcat (parser_exception_msg,parse_error_msg);
						} else {
							strncat(parser_exception_msg,"**TRUNCATED**",PARSER_EXCEPTION_MSG_LEN-strlen(parser_exception_msg)-1);
							break;
						}
					}
				}
				sdo_das_xml_throw_parserexception(parser_exception_msg TSRMLS_CC);
    	        RETURN_NULL();
            }
        } catch (SDOXMLParserException *e) {
        	const char* msg = e->getMessageText();
            sdo_das_xml_throw_parserexception((char*)msg TSRMLS_CC);
            RETURN_NULL();
        } catch (SDOFileNotFoundException e) {
            sdo_das_xml_throw_fileexception(file_name TSRMLS_CC);
            RETURN_NULL();
        } catch(SDORuntimeException e) {
            sdo_das_xml_throw_runtimeexception(&e TSRMLS_CC);
            RETURN_NULL();
        }
    } else {
        RETURN_FALSE;
    }
}
/* }}} SDO_DAS_XML::loadFile */

/* {{{ proto SDO_DAS_XML_Document SDO_DAS_XML::loadFromString(string xml_string)
 */
PHP_METHOD(SDO_DAS_XML, loadFromString) 
{
    /* Returns SDO_DAS_XML_Document Object containing root SDO object built from the given xml string.
     */
    xmldocument_object *doc_obj = NULL;
    xmldas_object *xmldas_obj = NULL;
    char* xml_string;
    int len = 0;
    zval *this_obj = getThis();
    zend_class_entry *ce = NULL;

    if (ZEND_NUM_ARGS() != 1) {
        WRONG_PARAM_COUNT;
    }
    if (zend_parse_parameters(ZEND_NUM_ARGS() TSRMLS_CC, "s", &xml_string, &len) == FAILURE) {
        RETURN_FALSE;
    }
    if (len) {
        Z_TYPE_P(return_value) = IS_OBJECT;
        ce = zend_fetch_class("SDO_DAS_XML_Document", strlen("SDO_DAS_XML_Document"),
                              ZEND_FETCH_CLASS_AUTO TSRMLS_CC);
        if (object_init_ex(return_value, ce) == FAILURE) {
            php_error(E_ERROR, "SDO_DAS_XML::load - Unable to create SDO_DAS_XML");
            return;
        }
        doc_obj = (xmldocument_object *) zend_object_store_get_object(return_value TSRMLS_CC);
        if (!doc_obj) {
            php_error(E_ERROR, "SDO_DAS_XML::load - Unable to get SDO_DAS_XML_Document object from object store");
        }
        xmldas_obj = (xmldas_object *) zend_object_store_get_object(this_obj TSRMLS_CC);
        if (!xmldas_obj) {
            php_error(E_ERROR, "SDO_DAS_XML::load - Unable to get SDO_DAS_XML object from object store");
        }
        try {
            istringstream str((const char *)xml_string);
            doc_obj->xdoch = xmldas_obj->xmlHelper->load(str);
            if (doc_obj->xdoch && doc_obj->xdoch->getRootDataObject() && (xmldas_obj->xmlHelper->getErrorCount() == 0)) {
                create_dataobject_tree(doc_obj->xdoch->getRootDataObject() TSRMLS_CC);
            } else {
            	char parser_exception_msg[PARSER_EXCEPTION_MSG_LEN] = "SDO_DAS_XML::loadFromFile - Unable to obtain a root data object from the supplied XML\n";
            	int error_count = xmldas_obj->xmlHelper->getErrorCount();
			    if (error_count > 0) {
			    	char parse_error_hdr[100];
					sprintf(parse_error_hdr,"%d parse error(s) occured when parsing the XML string:\n",error_count);
					strcat(parser_exception_msg,parse_error_hdr);
					for (int error_index=0;error_index<error_count;error_index++) {
						const char * parse_error_msg = xmldas_obj->xmlHelper->getErrorMessage(error_index);
						if (strlen(parser_exception_msg) + strlen(parse_error_msg) + 10 < PARSER_EXCEPTION_MSG_LEN) {
							char msg_number[10];
							sprintf(msg_number,"%d. ",error_index);
							strcat (parser_exception_msg, msg_number);
							strcat (parser_exception_msg,parse_error_msg);
						} else {
							strncat(parser_exception_msg,"**TRUNCATED**",PARSER_EXCEPTION_MSG_LEN-strlen(parser_exception_msg)-1);
							break;
						}
					}
				}
				sdo_das_xml_throw_parserexception(parser_exception_msg TSRMLS_CC);
    	        RETURN_NULL();
            }
        } catch (SDOXMLParserException *e) {
        	const char* msg = e->getMessageText();
            sdo_das_xml_throw_parserexception((char*)msg TSRMLS_CC);
            RETURN_NULL();
        } catch(SDORuntimeException e) {
            sdo_das_xml_throw_runtimeexception(&e TSRMLS_CC);
            RETURN_NULL();
        }
    } else {
        RETURN_FALSE;
    }
}
/* }}} SDO_DAS_XML::load */


/* {{{ proto SDO_DataObject SDO_DAS_XML::createDataObject(string namespace_uri, string type_name)
 */
PHP_METHOD(SDO_DAS_XML, createDataObject) 
{
    /*
     * Returns SDO_DataObject for a given namespace_uri and the type name.
     */
    xmldas_object *obj = NULL;
    zval *this_obj = getThis();
    zval *namespace_uri = NULL;
    zval *type_name = NULL;
    zval *retval;

    if (ZEND_NUM_ARGS() != 2) {
        WRONG_PARAM_COUNT;
    }

    if (zend_parse_parameters(ZEND_NUM_ARGS() TSRMLS_CC, "zz",
                              &namespace_uri, &type_name) == FAILURE) {
        RETURN_FALSE;
    }

    obj = (xmldas_object *) zend_object_store_get_object(this_obj TSRMLS_CC);
    if (!obj) {
        php_error(E_ERROR, "SDO_DAS_XML::createDataObject - Unable to get SDO_DAS_XML object from object store");
    }
    zend_call_method(&obj->php_das_df, Z_OBJCE_P(obj->php_das_df), NULL,
                     "create", strlen("create"), &retval, 2, namespace_uri,
                     type_name TSRMLS_CC);
    /*
     *TODO, Check are we leaking memory here?
     */
    if (retval) {
        RETVAL_ZVAL(retval, 1, 0);
    } else {
        RETVAL_NULL();
    }
}
/* }}} SDO_DAS_XML::createDataObject */


/* {{{ proto string SDO_DAS_XML::saveDocumentToString(SDO_XMLDocument xdoc)
 */
PHP_METHOD(SDO_DAS_XML, saveDocumentToString) 
{
    zval *xml_doc_obj = NULL;
    zval *this_obj = getThis();
    sdo_do_object *php_do = NULL;
    xmldocument_object *xml_doc_ptr;
    xmldas_object *das_obj = NULL;
    int uri_len = 0, name_len = 0, file_len = 0;
    char *retval = NULL;
    char *ret_string = NULL;

    if (ZEND_NUM_ARGS() != 1) {
        WRONG_PARAM_COUNT;
    }
    das_obj = (xmldas_object *) zend_object_store_get_object(this_obj TSRMLS_CC);
    if (zend_parse_parameters(ZEND_NUM_ARGS() TSRMLS_CC, "z",&xml_doc_obj) == FAILURE) {
        RETURN_FALSE;
    }
    xml_doc_ptr = (xmldocument_object *) zend_object_store_get_object(xml_doc_obj TSRMLS_CC);
    if (!xml_doc_ptr) {
        php_error(E_ERROR, "SDO_DAS_XML::saveDocumentToString - Unable to get SDO_DAS_XML_Document object from object store");
    }
    try {
        retval = das_obj->xmlHelper->save(xml_doc_ptr->xdoch);
    } catch (SDORuntimeException e) {
        sdo_das_xml_throw_runtimeexception(&e TSRMLS_CC);
    }
    ret_string = (char *)emalloc(strlen(retval) + 1);
    memset(ret_string, 0, strlen(retval) + 1);
    strcpy(ret_string, retval);
    /*
     * retval to be freed using "delete" as it was
     * allocated using "new" in save method
     */
    delete retval;
    RETVAL_STRING((char *)ret_string, 0);
}
/* }}} */


/* {{{ proto void SDO_DAS_XML::saveDocumentToFile(SDO_XMLDocument xdoc, string xml_file)
 */
PHP_METHOD(SDO_DAS_XML, saveDocumentToFile) 
{
    char *xml_file = NULL;
    zval *xml_doc_obj = NULL;
    zval *this_obj = getThis();
    sdo_do_object *php_do = NULL;
    xmldocument_object *xml_doc_ptr;
    xmldas_object *das_obj = NULL;
    int uri_len = 0, name_len = 0, file_len = 0;
    char *retval = NULL;
    char *ret_string = NULL;

    if (ZEND_NUM_ARGS() != 2) {
        WRONG_PARAM_COUNT;
    }
    das_obj = (xmldas_object *) zend_object_store_get_object(this_obj TSRMLS_CC);
    if (zend_parse_parameters(ZEND_NUM_ARGS() TSRMLS_CC, "zs",
                              &xml_doc_obj, &xml_file,
                              &file_len) == FAILURE) {
        RETURN_FALSE;
    }
    xml_doc_ptr = (xmldocument_object *) zend_object_store_get_object(xml_doc_obj TSRMLS_CC);
    if (!xml_doc_ptr) {
        php_error(E_ERROR, "SDO_DAS_XML::save - Unable to get SDO_DAS_XML_Document object from object sotre");
    }
    try {
        das_obj->xmlHelper->save(xml_doc_ptr->xdoch, xml_file);
    } catch (SDORuntimeException e) {
        sdo_das_xml_throw_runtimeexception(&e TSRMLS_CC);
    }
}
/* }}} */


/* {{{ proto string SDO_DAS_XML::saveDataObjectToString(SDO_DataObject do, string root_uri, string root_name)
 */
PHP_METHOD(SDO_DAS_XML, saveDataObjectToString) 
{
    char *root_uri = NULL;
    char *root_name = NULL;
    zval *data_obj = NULL;
    zval *xml_doc_obj = NULL;
    zval *this_obj = getThis();
    sdo_do_object *php_do = NULL;
    xmldas_object *das_obj = NULL;
    int uri_len = 0, name_len = 0, file_len = 0;
    char *retval = NULL;
    char *ret_string = NULL;

    if (ZEND_NUM_ARGS() != 3) {
        WRONG_PARAM_COUNT;
    }
    if (zend_parse_parameters(ZEND_NUM_ARGS() TSRMLS_CC, "zss",
                              &data_obj, &root_uri, &uri_len,
                              &root_name, &name_len) == FAILURE) {
        RETURN_FALSE;
    }
    das_obj = (xmldas_object *) zend_object_store_get_object(this_obj TSRMLS_CC);
    php_do = (sdo_do_object *) zend_object_store_get_object(data_obj TSRMLS_CC);
    if (!php_do) {
        php_error(E_ERROR, "XML_DAS::save - We are unable to get SDO_DataObject from object store");
    }
    try {
        retval = das_obj->xmlHelper->save(php_do->dop, root_uri, root_name);
    } catch (SDORuntimeException e) {
        sdo_das_xml_throw_runtimeexception(&e TSRMLS_CC);
    }
    ret_string = (char *)emalloc(strlen(retval) + 1);
    memset(ret_string, 0, strlen(retval) + 1);
    strcpy(ret_string, retval);
    /*
     * retval to be freed using "delete" as it was
     * allocated using "new" in save method
     */
    delete retval;
    RETVAL_STRING((char *)ret_string, 0);
}
/* }}} */


/* {{{ proto void  SDO_DAS_XML::saveDataObjectToFile(SDO_DataObject do, string root_uri, string root_name string xml_file)
 */
PHP_METHOD(SDO_DAS_XML, saveDataObjectToFile) 
{
    char *xml_file = NULL;
    char *root_uri = NULL;
    char *root_name = NULL;
    zval *data_obj = NULL;
    zval *xml_doc_obj = NULL;
    zval *this_obj = getThis();
    sdo_do_object *php_do = NULL;
    xmldas_object *das_obj = NULL;
    int uri_len = 0, name_len = 0, file_len = 0;
    char *retval = NULL;
    char *ret_string = NULL;

    if (ZEND_NUM_ARGS() != 4) {
        WRONG_PARAM_COUNT;
    }
    if (zend_parse_parameters(ZEND_NUM_ARGS() TSRMLS_CC, "zsss",
                              &data_obj, &root_uri, &uri_len, &root_name,
                              &name_len, &xml_file,
                              &file_len) == FAILURE) {
        RETURN_FALSE;
    }
    das_obj = (xmldas_object *) zend_object_store_get_object(this_obj TSRMLS_CC);
    php_do = (sdo_do_object *)  zend_object_store_get_object(data_obj TSRMLS_CC);
    if (!php_do) {
        php_error(E_ERROR, "SDO_DAS_XML::save - We are unable to get SDO_DataObject from object store");
    }
    try {
        das_obj->xmlHelper->save(php_do->dop, root_uri, root_name, xml_file);
    } catch (SDORuntimeException e) {
        sdo_das_xml_throw_runtimeexception(&e TSRMLS_CC);
    }
}
/* }}} */


/* {{{ proto void  SDO_DAS_XML::getProperty()
 */
PHP_METHOD(SDO_DAS_XML, getProperty) 
{
    xmldas_object *das_obj 	= NULL;
    zval*	this_obj 		= getThis();
    char* 	namespace_uri 	= NULL;
    char* 	type_name 		= NULL;
    int 	namespace_uri_len = 0;
    int 	type_name_len	= 0;

	// TODO
	// When the rest of open types is ready, re-enable and complete this method
    if (ZEND_NUM_ARGS() != 2) {
        WRONG_PARAM_COUNT;
    }
    das_obj = (xmldas_object *) zend_object_store_get_object(this_obj TSRMLS_CC);
    if (!das_obj) {
        php_error(E_ERROR, "SDO_DAS_XML::getProperty - Unable to get SDO_DAS_XML object from object store");
    }
    if (zend_parse_parameters(ZEND_NUM_ARGS() TSRMLS_CC, "ss",
				&namespace_uri,&namespace_uri_len, &type_name, &type_name_len) == FAILURE) {
        RETURN_FALSE;
    }
    // at this point we have char* versions of uri and type name
    // TODO: get a property of the requested type from Hydra
    // pass to SDO_Model_Property::sdo_model_property_new
    // return to the user
    return;
}
/* }}} */

/* {{{ proto SDO_DAS_XML SDO_DAS_XML::defineFromFile(string xsd_file)
 */
PHP_METHOD(SDO_DAS_XML, defineFromFile) 
{
    xmldas_object *das_obj 	= NULL;
    zval*		this_obj	= getThis();
    char*		file_name 	= NULL;
    int 		file_name_len = 0;
    
    // TODO
    // This method is currently not enabled. When the rest of open type support is ready enable in the 
    // list of methods 

    if (ZEND_NUM_ARGS() != 1) {
        WRONG_PARAM_COUNT;
    }
    das_obj = (xmldas_object *) zend_object_store_get_object(this_obj TSRMLS_CC);
    if (!das_obj) {
        php_error(E_ERROR, "SDO_DAS_XML::getProperty - Unable to get SDO_DAS_XML object from object store");
    }
    if (zend_parse_parameters(ZEND_NUM_ARGS() TSRMLS_CC, "s", &file_name, &file_name_len) == FAILURE) {
        RETURN_FALSE;
    }
    try {
        das_obj->xsdHelper->defineFile(file_name);
            if (das_obj->xsdHelper->getErrorCount() != 0) {
        	char parser_exception_msg[PARSER_EXCEPTION_MSG_LEN] = "SDO_DAS_XML::defineFromFile - Unable to parse the supplied xsd file\n";
        	int error_count = das_obj->xsdHelper->getErrorCount();
		    if (error_count > 0) {
		    	char parse_error_hdr[100];
				sprintf(parse_error_hdr,"%d parse error(s) occured when parsing the XSD file \"%s\":\n",error_count, file_name);
				strcat(parser_exception_msg,parse_error_hdr);
				for (int error_index=0;error_index<error_count;error_index++) {
					const char * parse_error_msg = das_obj->xsdHelper->getErrorMessage(error_index);
					if (strlen(parser_exception_msg) + strlen(parse_error_msg) + 10 < PARSER_EXCEPTION_MSG_LEN) {
						char msg_number[10];
						sprintf(msg_number,"%d. ",error_index);
						strcat (parser_exception_msg, msg_number);
						strcat (parser_exception_msg,parse_error_msg);
					} else {
						strncat(parser_exception_msg,"**TRUNCATED**",PARSER_EXCEPTION_MSG_LEN-strlen(parser_exception_msg)-1);
						break;
					}
				}
			}
			sdo_das_xml_throw_parserexception(parser_exception_msg TSRMLS_CC);
	        RETURN_NULL();
        }            
    } catch (SDOFileNotFoundException e) {
        sdo_das_xml_throw_fileexception(file_name TSRMLS_CC);
        RETURN_NULL();
    } catch (SDOXMLParserException *e) {
    	const char* msg = e->getMessageText();
        sdo_das_xml_throw_parserexception((char*)msg TSRMLS_CC);
        RETURN_NULL();            
    } catch (SDORuntimeException e) {
        sdo_das_xml_throw_runtimeexception(&e TSRMLS_CC);
        RETURN_NULL();
    }

//    zend_call_method(&das_obj->php_das_df, Z_OBJCE_P(das_obj->php_das_df), NULL,
//                     "create", strlen("create"), &retval, 2, znamespace_uri,
//                     ztype_name TSRMLS_CC);
//    /*
//     *TODO, Check are we leaking memory here?
//     */
//    if (retval) {
//        RETVAL_ZVAL(retval, 1, 0);
//    } else {
//        RETVAL_NULL();
//    }
}
//    php_do = (sdo_do_object *) zend_object_store_get_object(data_obj TSRMLS_CC);
//    if (!php_do) {
//        php_error(E_ERROR, "XML_DAS::save - We are unable to get SDO_DataObject from object store");
//    }
//    try {
//        retval = das_obj->xmlHelper->save(php_do->dop, root_uri, root_name);
//    } catch (SDORuntimeException e) {
//        sdo_das_xml_throw_runtimeexception(&e TSRMLS_CC);
//    }
//    ret_string = (char *)emalloc(strlen(retval) + 1);
//    memset(ret_string, 0, strlen(retval) + 1);
//    strcpy(ret_string, retval);
//    /*
//     * retval to be freed using "delete" as it was
//     * allocated using "new" in save method
//     */
//    delete retval;
//    RETVAL_STRING((char *)ret_string, 0);

//    php_do = (sdo_do_object *) zend_object_store_get_object(data_obj TSRMLS_CC);
//    if (!php_do) {
//        php_error(E_ERROR, "XML_DAS::save - We are unable to get SDO_DataObject from object store");
//    }
//    try {
//        retval = das_obj->xmlHelper->save(php_do->dop, root_uri, root_name);
//    } catch (SDORuntimeException e) {
//        sdo_das_xml_throw_runtimeexception(&e TSRMLS_CC);
//    }
//    ret_string = (char *)emalloc(strlen(retval) + 1);
//    memset(ret_string, 0, strlen(retval) + 1);
//    strcpy(ret_string, retval);
//    /*
//     * retval to be freed using "delete" as it was
//     * allocated using "new" in save method
//     */
//    delete retval;
//    RETVAL_STRING((char *)ret_string, 0);
//
//
//
//**************
//    xmldas_object *das_obj = NULL;
//    zval *this_obj = getThis();
//    char* file_name;
//    int len = 0;
//
//    das_obj = (xmldas_object *) zend_object_store_get_object(this_obj TSRMLS_CC);
//    if (!das_obj) {
//        php_error(E_ERROR, "SDO_DAS_XML::defineFromFile - Unable to get SDO_DAS_XML object from object store");
//    }
//
//    if (ZEND_NUM_ARGS() != 1) {
//        WRONG_PARAM_COUNT;
//    }
//    if (zend_parse_parameters(ZEND_NUM_ARGS() TSRMLS_CC, "s", &file_name,&len) == FAILURE) {
//        RETURN_FALSE;
//    }
//    
//
//printf("got file name %s\n",file_name);
//printf("got das obje with %p %p\n",das_obj->xsdHelper, das_obj->xmlHelper);
//das_obj->xsdHelper->defineFile(file_name);
//return;
//    try {
//        das_obj->xsdHelper->defineFile(file_name);
//        printf("after definefile\n");
//        if (das_obj->xsdHelper->getErrorCount() != 0) {
//        	char parser_exception_msg[PARSER_EXCEPTION_MSG_LEN] = "SDO_DAS_XML::defineFromFile - Unable to parse the supplied xsd file\n";
//        	int error_count = das_obj->xsdHelper->getErrorCount();
//		    if (error_count > 0) {
//		    	char parse_error_hdr[100];
//				sprintf(parse_error_hdr,"%d parse error(s) occured when parsing the XML file \"%s\":\n",error_count, file_name);
//				strcat(parser_exception_msg,parse_error_hdr);
//				for (int error_index=0;error_index<error_count;error_index++) {
//					const char * parse_error_msg = das_obj->xsdHelper->getErrorMessage(error_index);
//					if (strlen(parser_exception_msg) + strlen(parse_error_msg) + 10 < PARSER_EXCEPTION_MSG_LEN) {
//						char msg_number[10];
//						sprintf(msg_number,"%d. ",error_index);
//						strcat (parser_exception_msg, msg_number);
//						strcat (parser_exception_msg,parse_error_msg);
//					} else {
//						strncat(parser_exception_msg,"**TRUNCATED**",PARSER_EXCEPTION_MSG_LEN-strlen(parser_exception_msg)-1);
//						break;
//					}
//				}
//			}
//			sdo_das_xml_throw_parserexception(parser_exception_msg TSRMLS_CC);
//	        RETURN_NULL();
//        }            
//    } catch (SDOFileNotFoundException e) {
//        sdo_das_xml_throw_fileexception(file_name TSRMLS_CC);
//        RETURN_NULL();
//    } catch (SDOXMLParserException *e) {
//    	const char* msg = e->getMessageText();
//        sdo_das_xml_throw_parserexception((char*)msg TSRMLS_CC);
//        RETURN_NULL();            
//    } catch (SDORuntimeException e) {
//        sdo_das_xml_throw_runtimeexception(&e TSRMLS_CC);
//        RETURN_NULL();
//    }


/* }}} end SDO_DAS_XML::create */


/*
 * Local variables:
 * tab-width: 4
 * c-basic-offset: 4
 * End:
 * vim600: noet sw=4 ts=4 fdm=marker
 * vim<600: noet sw=4 ts=4
 */
