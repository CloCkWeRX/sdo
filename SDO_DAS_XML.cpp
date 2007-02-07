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

#include "zend_interfaces.h" // needed for several uses of zend_call_method() 

using std::endl;
using std::istringstream;

#ifndef min
#define min(a, b) ((a) <= (b) ? (a) : (b))
#endif

#define PARSER_EXCEPTION_MSG_LEN 1000
#define MAX_ERRORS 50
#define CLASS_NAME "SDO_DAS_XML"

PHP_SDO_DAS_XML_API zend_class_entry *sdo_das_xml_class_entry;
static zend_object_handlers  sdo_das_xml_object_handlers;

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
    ZEND_ARG_INFO(0, file_name)
ZEND_END_ARG_INFO();

ZEND_BEGIN_ARG_INFO(sdo_das_xml_loadString_args, 0)
    ZEND_ARG_INFO(0, xml_string)
ZEND_END_ARG_INFO();

ZEND_BEGIN_ARG_INFO_EX(sdo_das_xml_saveFile_args, 0, ZEND_RETURN_VALUE, 2)
    ZEND_ARG_INFO(0, document)
    ZEND_ARG_INFO(0, xml_file)
    ZEND_ARG_INFO(0, indent)
ZEND_END_ARG_INFO();

ZEND_BEGIN_ARG_INFO_EX(sdo_das_xml_saveString_args, 0, ZEND_RETURN_VALUE, 1)
    ZEND_ARG_INFO(0, document)
    ZEND_ARG_INFO(0, indent)
ZEND_END_ARG_INFO();

ZEND_BEGIN_ARG_INFO_EX(sdo_das_xml_createDocument_args, 0, ZEND_RETURN_VALUE, 0)
    ZEND_ARG_INFO(0, namespace_uri)
    ZEND_ARG_INFO(0, element_name)
ZEND_END_ARG_INFO();

ZEND_BEGIN_ARG_INFO(sdo_das_xml_createDataObject_args, 0)
    ZEND_ARG_INFO(0, namespace_uri)
    ZEND_ARG_INFO(0, type_name)
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
    ZEND_ME(SDO_DAS_XML, loadFile, sdo_das_xml_loadFile_args,
            ZEND_ACC_PUBLIC)
    ZEND_ME(SDO_DAS_XML, loadString, sdo_das_xml_loadString_args,
            ZEND_ACC_PUBLIC)
    ZEND_ME(SDO_DAS_XML, saveFile, sdo_das_xml_saveFile_args,
            ZEND_ACC_PUBLIC)
    ZEND_ME(SDO_DAS_XML, saveString, sdo_das_xml_saveString_args,
            ZEND_ACC_PUBLIC)
    ZEND_ME(SDO_DAS_XML, createDocument, sdo_das_xml_createDocument_args,
            ZEND_ACC_PUBLIC)
    ZEND_ME(SDO_DAS_XML, createDataObject, sdo_das_xml_createDataObject_args,
            ZEND_ACC_PUBLIC)
   	ZEND_ME(SDO_DAS_XML, __toString, 0, 
   			ZEND_ACC_PUBLIC)
            
    {NULL, NULL, NULL}
};
/* }}} */

/* {{{ sdo_temporary_exception_test
 * This is a temporary fix for PECL-8374 to ensure that exceptions
 * caught in this code are really the exceptions that have been
 * throw by code that can reside in another shared library. In some
 * build environments only the base SDORuntimeException ever arrives
 */
void sdo_temporary_exception_test(SDORuntimeException &e, char *file_name TSRMLS_DC)
{
	if ( strcmp( e.getEClassName(), "SDOFileNotFoundException" ) == 0 ) { 
		sdo_das_xml_throw_fileexception(file_name TSRMLS_CC);
	} else if ( strcmp( e.getEClassName(), "SDOXMLParserException" ) == 0 ) { 
		sdo_das_xml_throw_parserexception((char *)e.getMessageText() TSRMLS_CC);
	} else {
		sdo_das_xml_throw_runtimeexception(&e TSRMLS_CC);
	}
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
    xmldas_object *xmldas = (xmldas_object *)object;
    zend_hash_destroy(xmldas->zo.properties);
    FREE_HASHTABLE(xmldas->zo.properties);

	if (xmldas->zo.guards) {
	    zend_hash_destroy(xmldas->zo.guards);
	    FREE_HASHTABLE(xmldas->zo.guards);
	}

	xmldas->xmlHelperPtr = NULL;
	xmldas->xsdHelperPtr = NULL;
	zval_dtor(&xmldas->z_df);
    efree(xmldas);
}
/* }}} */

/* {{{ sdo_das_xml_object_create
 */
zend_object_value sdo_das_xml_object_create(zend_class_entry *ce TSRMLS_DC) 
{
    zend_object_value	 retval;
    zval				*tmp;
    xmldas_object		*xmldas;
	
	xmldas = (xmldas_object *)emalloc(sizeof(xmldas_object));
    memset(xmldas, 0, sizeof(xmldas_object));

    xmldas->zo.ce = ce;
	xmldas->zo.guards = NULL;
    ALLOC_HASHTABLE(xmldas->zo.properties);
    zend_hash_init(xmldas->zo.properties, 0, NULL, ZVAL_PTR_DTOR, 0);
    zend_hash_copy(xmldas->zo.properties, &ce->default_properties,
		(copy_ctor_func_t) zval_add_ref, (void *) &tmp, sizeof(zval *));
    retval.handle = zend_objects_store_put(xmldas, NULL, 
		sdo_das_xml_object_free_storage, NULL TSRMLS_CC);
    retval.handlers = &sdo_das_xml_object_handlers;
    return retval;
}
/* }}} */

/* {{{ sdo_das_xml_cast_object
*/ 
#if PHP_MAJOR_VERSION > 5 || (PHP_MAJOR_VERSION == 5 && PHP_MINOR_VERSION > 1)
static int sdo_das_xml_cast_object(zval *readobj, zval *writeobj, int type TSRMLS_DC)
{
	int should_free = 0;
#else
static int sdo_das_xml_cast_object(zval *readobj, zval *writeobj, int type, int should_free TSRMLS_DC)
{
#endif
    xmldas_object 	*xmldas;
	ostringstream	 print_buf;
	const char		*indent = "\n";
	DataFactoryPtr   dataFactoryPtr;
	int              rc = SUCCESS;
	
    xmldas = (xmldas_object *) zend_object_store_get_object(readobj TSRMLS_CC);
	
		try {				
			dataFactoryPtr = sdo_das_df_get(&xmldas->z_df TSRMLS_CC);
			TypeList tl = dataFactoryPtr->getTypes();
			print_buf << indent << "object(" << "SDO_DAS_XML" << ")#" <<
				readobj->value.obj.handle << " {";
			print_buf << indent << tl.size() << " types have been defined. The types and their properties are::";
			
			for (int ix = 0; ix < tl.size(); ix++) {
				if (tl[ix].getURI() == NULL || strlen(tl[ix].getURI()) == 0) {
					print_buf << indent << ix+1 << ". " << "{no namespace}" << "#" << tl[ix].getName(); 
				} else {
					print_buf << indent << ix+1 << ". " << tl[ix].getURI() << "#" << tl[ix].getName(); 
				}
				PropertyList pl = tl[ix].getProperties();
				for (int px = 0; px < pl.size() ; px++) {
					print_buf << indent << "    - " << pl[px].getName() << " (" ;
					if (pl[px].getType().getURI() == NULL || strlen(pl[px].getType().getURI()) == 0) {
						print_buf << "{no namespace}" << "#" << pl[px].getType().getName(); 
					} else {
						print_buf <<  pl[px].getType().getURI() << "#" << pl[px].getType().getName(); 
					}
					print_buf << ")";
			}
		}
		
		print_buf << indent << "}";
		
		std::string print_string = print_buf.str();
		ZVAL_STRINGL(writeobj, (char *)print_string.c_str(), print_string.length(), 1);			
		
	} catch (SDORuntimeException e) {
		ZVAL_NULL(writeobj);
		sdo_throw_runtimeexception(&e TSRMLS_CC);
		rc = FAILURE;
	}

	return rc;
			
}
/* }}} */

/* {{{ sdo_das_xml_minit
 */
void sdo_das_xml_minit(TSRMLS_D) 
{
    zend_class_entry ce;
	
    INIT_CLASS_ENTRY(ce, "SDO_DAS_XML", sdo_das_xml_methods);
    ce.create_object = sdo_das_xml_object_create;
    sdo_das_xml_class_entry = zend_register_internal_class(&ce TSRMLS_CC);
	
    memcpy(&sdo_das_xml_object_handlers, zend_get_std_object_handlers(),
		sizeof(zend_object_handlers));
	sdo_das_xml_object_handlers.cast_object = sdo_das_xml_cast_object;
    sdo_das_xml_object_handlers.clone_obj = NULL;
}
/* }}} */

/* {{{ PHP_METHOD(SDO_DAS_XML, __construct)
 */
PHP_METHOD(SDO_DAS_XML, __construct)
{
	char *class_name, *space;
	class_name = get_active_class_name(&space TSRMLS_CC);

	php_error(E_ERROR, "%s%s%s(): internal error - private constructor was called", 
		class_name, space, get_active_function_name(TSRMLS_C));
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
    xmldas_object	*xmldas;
    char			*file_name;
    int 			 file_name_len;
    DataFactoryPtr   dataFactoryPtr;
	char			*class_name, *space;
	bool			exception_thrown = false;

    if (ZEND_NUM_ARGS() > 1) {
        WRONG_PARAM_COUNT;
    }
    
    Z_TYPE_P(return_value) = IS_OBJECT;
    if (object_init_ex(return_value, sdo_das_xml_class_entry) == FAILURE) {
		class_name = get_active_class_name(&space TSRMLS_CC);
		php_error(E_ERROR, "%s%s%s(): internal error (%i) - failed to instantiate %s object", 
			class_name, space, get_active_function_name(TSRMLS_C), __LINE__, CLASS_NAME);
        return;
    }    
    xmldas = (xmldas_object *) zend_object_store_get_object(return_value TSRMLS_CC);

	/* Create a C++ DataFactory and an SDO_DAS_DataFactory wrapper for it */
    dataFactoryPtr = DataFactory::getDataFactory();
	INIT_ZVAL(xmldas->z_df);
	sdo_das_df_new(&xmldas->z_df, dataFactoryPtr TSRMLS_CC);

    xmldas->xsdHelperPtr = HelperProvider::getXSDHelper((DataFactory *)dataFactoryPtr);
    xmldas->xmlHelperPtr = HelperProvider::getXMLHelper((DataFactory *)dataFactoryPtr);

	if (ZEND_NUM_ARGS() == 1) {
	    if (zend_parse_parameters(ZEND_NUM_ARGS() TSRMLS_CC, "s", &file_name, &file_name_len) == FAILURE) {
	        RETURN_FALSE;
	    }
	    if(!file_name_len) {
	        RETURN_FALSE;
	    }	
	
	    try {
	        xmldas->xsdHelperPtr->defineFile(file_name);
	
	        int error_count = xmldas->xsdHelperPtr->getErrorCount();
	        if (error_count > 0) {
				ostringstream	 print_buf;

				print_buf << "SDO_DAS_XML::create - Unable to parse the supplied xsd file\n";
				print_buf << error_count << " parse error(s) occurred when parsing the file '" << file_name << "'"; 
				if (error_count > MAX_ERRORS) {
					print_buf << " (only the first " << MAX_ERRORS << " shown)";
				} 
				print_buf << ":\n";
				for (int error_ix = 0; error_ix < min(error_count, MAX_ERRORS); error_ix++) {
					print_buf << error_ix + 1 << ". " << xmldas->xsdHelperPtr->getErrorMessage(error_ix) << endl;
				}
				std::string print_string = print_buf.str();
				sdo_das_xml_throw_parserexception((char *)print_string.c_str() TSRMLS_CC);
				exception_thrown = true;
	        }            
	    } catch (SDOFileNotFoundException e) {
	        sdo_das_xml_throw_fileexception(file_name TSRMLS_CC);
			exception_thrown = true;
	    } catch (SDOXMLParserException e) {
	        sdo_das_xml_throw_parserexception((char *)e.getMessageText() TSRMLS_CC);
			exception_thrown = true;
	    } catch (SDORuntimeException e) {
 			// The exceptions caught here have been thrown across the 
			// boundary between two shared libraries (sdo_das_xml.so and sdo.so)
			// In some build environments this has been shown to not work. 
			// The symptom is that the exception hierachy is ignored and the
			// exception arrives here as a runtime exception.
			// We have added this extra test of the stored classname
			// just to be sure
            sdo_temporary_exception_test ( e, file_name TSRMLS_CC);
			exception_thrown = true;
	    }
	}
	if (exception_thrown) {
		/** 
		 * we set a flag and then do the return from here, because returning out of the catch block 
		 * (i.e. doing RETURN_NULL() inside the catch block) would cause a crash in PHP or MSCVRT soon
		 * after. Matthew saw this but not Caroline, and only with optimised build, so perhaps something
		 * specific to particular level of compiler.  
		 */
		RETURN_NULL();
	}
    
}
/* }}} end SDO_DAS_XML::create */

/* {{{ proto SDO_DAS_XML SDO_DAS_XML::addTypes(string xsd_file)
 */
PHP_METHOD(SDO_DAS_XML, addTypes) 
{
    xmldas_object *xmldas;
    char          *file_name;
    int 		   file_name_len;
	bool			exception_thrown = false;

	// TODO lots of code duplicated with create()
	
    if (ZEND_NUM_ARGS() != 1) {
        WRONG_PARAM_COUNT;
    }
    if (zend_parse_parameters(ZEND_NUM_ARGS() TSRMLS_CC, "s", &file_name, &file_name_len) == FAILURE) {
        RETURN_FALSE;
    }
    if (!file_name_len) {
    	RETURN_FALSE;
    }
    
    xmldas = (xmldas_object *) zend_object_store_get_object(getThis() TSRMLS_CC);

    try {
        xmldas->xsdHelperPtr->defineFile(file_name);

        int error_count = xmldas->xsdHelperPtr->getErrorCount();
        if (error_count > 0) { 
			ostringstream	 print_buf;
			
			print_buf << "SDO_DAS_XML::addTypes - Unable to parse the supplied xsd file\n";
			print_buf << error_count << " parse error(s) occurred when parsing the file '" << file_name << "'"; 
			if (error_count > MAX_ERRORS) {
				print_buf << " (only the first " << MAX_ERRORS << " shown)";
			} 
			print_buf << ":\n";
			for (int error_ix = 0; error_ix < min(error_count, MAX_ERRORS); error_ix++) {
				print_buf << error_ix + 1 << ". " << xmldas->xsdHelperPtr->getErrorMessage(error_ix) << endl;
			}
			std::string print_string = print_buf.str();
			sdo_das_xml_throw_parserexception((char *)print_string.c_str() TSRMLS_CC);
	        RETURN_NULL();
        }            
    } catch (SDOFileNotFoundException e) {
        sdo_das_xml_throw_fileexception(file_name TSRMLS_CC);
		exception_thrown = true;
    } catch (SDOXMLParserException e) {
        sdo_das_xml_throw_parserexception((char *)e.getMessageText() TSRMLS_CC);
		exception_thrown = true;
    } catch (SDORuntimeException e) {
 		// The exceptions caught here have been thrown across the 
		// boundary between two shared libraries (sdo_das_xml.so and sdo.so)
		// In some build environments this has been shown to not work. 
		// The symptom is that the exception hierachy is ignored and the
		// exception arrives here as a runtime exception.
		// We have added this extra test of the stored classname
		// just to be sure
        sdo_temporary_exception_test ( e, file_name TSRMLS_CC);
		exception_thrown = true;
    }
	if (exception_thrown) {
		/** 
		 * we set a flag and then do the return from here, because returning out of the catch block 
		 * (i.e. doing RETURN_NULL() inside the catch block) would cause a crash in PHP or MSCVRT soon
		 * after. Matthew saw this but not Caroline, and only with optimised build, so perhaps something
		 * specific to particular level of compiler.  
		 */
		RETURN_NULL();
	}

}
/* }}} */

/* {{{ proto SDO_XMLDocument SDO_DAS_XML::loadFile(string xml_file)
 */
PHP_METHOD(SDO_DAS_XML, loadFile) 
{
    /*
     * Returns XMLDocument Object containing root SDO object built from the
     * given path to xml instance document.
     */
    xmldocument_object	*xmldocument;
    xmldas_object		*xmldas;
    char				*file_name;
    int					 file_name_len;
	char				*class_name, *space;
	bool				exception_thrown = false;

    if (ZEND_NUM_ARGS() != 1) {
        WRONG_PARAM_COUNT;
    }
    if (zend_parse_parameters(ZEND_NUM_ARGS() TSRMLS_CC, "s", &file_name, &file_name_len) == FAILURE) {
        RETURN_FALSE;
    }
        
	xmldas = (xmldas_object *) zend_object_store_get_object(getThis() TSRMLS_CC);

    if (file_name_len) {
        Z_TYPE_P(return_value) = IS_OBJECT;
        if (object_init_ex(return_value, sdo_das_xml_document_class_entry) == FAILURE) {
		class_name = get_active_class_name(&space TSRMLS_CC);
		php_error(E_ERROR, "%s%s%s(): internal error (%i) - failed to instantiate %s object", 
			class_name, space, get_active_function_name(TSRMLS_C), __LINE__, CLASS_NAME);
            RETURN_NULL();
        }
        xmldocument = (xmldocument_object *) zend_object_store_get_object(return_value TSRMLS_CC);
        if (!xmldocument) {
			class_name = get_active_class_name (&space TSRMLS_CC);
			php_error(E_ERROR, "%s%s%s(): internal error (%i) - SDO_DAS_XML_Document not found in store", 
				class_name, space, get_active_function_name(TSRMLS_C), __LINE__);
            RETURN_NULL();
        }

        try {
            xmldocument->xmlDocumentPtr = xmldas->xmlHelperPtr->loadFile(file_name);
            int error_count = xmldas->xmlHelperPtr->getErrorCount();
            if ((error_count > 0) || 
				(!xmldocument->xmlDocumentPtr) || 
				(!xmldocument->xmlDocumentPtr->getRootDataObject())) {
				ostringstream	 print_buf;

				print_buf << "SDO_DAS_XML::loadFile - Unable to parse the supplied xml file\n";
				print_buf << error_count << " parse error(s) occurred when parsing the file '" << file_name << "'"; 
				if (error_count > MAX_ERRORS) {
					print_buf << " (only the first " << MAX_ERRORS << " shown)";
				} 
				print_buf << ":\n";
				for (int error_ix = 0; error_ix < min(error_count, MAX_ERRORS); error_ix++) {
					print_buf << error_ix + 1 << ". " << xmldas->xmlHelperPtr->getErrorMessage(error_ix) << endl;
				}
				std::string print_string = print_buf.str();
				sdo_das_xml_throw_parserexception((char *)print_string.c_str() TSRMLS_CC);
		        RETURN_NULL();
            }
        } catch (SDOXMLParserException e) {
            sdo_das_xml_throw_parserexception((char *)e.getMessageText() TSRMLS_CC);
            exception_thrown = true;
        } catch (SDOFileNotFoundException e) {
            sdo_das_xml_throw_fileexception(file_name TSRMLS_CC);
            exception_thrown = true;
        } catch(SDORuntimeException e) {
            // The exceptions caught here have been thrown across the 
			// boundary between two shared libraries (sdo_das_xml.so and sdo.so)
			// In some build environments this has been shown to not work. 
			// The symptom is that the exception hierachy is ignored and the
			// exception arrives here as a runtime exception.
			// We have added this extra test of the stored classname
			// just to be sure
            sdo_temporary_exception_test ( e, file_name TSRMLS_CC);
            exception_thrown = true;
        }
    } else {
        RETURN_FALSE;
    }
    if (exception_thrown) {
	/** 
	 * we set a flag and then do the return from here, because returning out of the catch block 
	 * (i.e. doing RETURN_NULL() inside the catch block) would cause a crash in PHP or MSCVRT soon
	 * after. Matthew saw this but not Caroline, and only with optimised build, so perhaps something
	 * specific to particular level of compiler.  
	 */
	RETURN_NULL();
	}
    
}
/* }}} SDO_DAS_XML::loadFile */

/* {{{ proto SDO_DAS_XML_Document SDO_DAS_XML::loadString(string xml_string)
 */
PHP_METHOD(SDO_DAS_XML, loadString) 
{
    /* Returns SDO_DAS_XML_Document Object containing root SDO object built from the given xml string.
     */
    xmldocument_object	*xmldocument;
    xmldas_object		*xmldas;
    char				*xml_string;
    int					 xml_string_len;
	char				*class_name, *space;
	bool				exception_thrown = false;

    if (ZEND_NUM_ARGS() != 1) {
        WRONG_PARAM_COUNT;
    }
	
    if (zend_parse_parameters(ZEND_NUM_ARGS() TSRMLS_CC, "s", &xml_string, &xml_string_len) == FAILURE) {
        RETURN_FALSE;
    }
	
	xmldas = (xmldas_object *) zend_object_store_get_object(getThis() TSRMLS_CC);
	
	if (xml_string_len) {
        Z_TYPE_P(return_value) = IS_OBJECT;
        if (object_init_ex(return_value, sdo_das_xml_document_class_entry) == FAILURE) {
		class_name = get_active_class_name(&space TSRMLS_CC);
		php_error(E_ERROR, "%s%s%s(): internal error (%i) - failed to instantiate %s object", 
			class_name, space, get_active_function_name(TSRMLS_C), __LINE__, CLASS_NAME);
            return;
        }
        xmldocument = (xmldocument_object *) zend_object_store_get_object(return_value TSRMLS_CC);
        if (!xmldocument) {
			class_name = get_active_class_name (&space TSRMLS_CC);
			php_error(E_ERROR, "%s%s%s(): internal error (%i) - SDO_DAS_XML_Document not found in store", 
				class_name, space, get_active_function_name(TSRMLS_C), __LINE__);
		}
		
        try {
            istringstream str((const char *)xml_string);
            xmldocument->xmlDocumentPtr = xmldas->xmlHelperPtr->load(str, NULL);
            int error_count = xmldas->xmlHelperPtr->getErrorCount();
            if ((error_count > 0) ||
				(!xmldocument->xmlDocumentPtr) || 
				(!xmldocument->xmlDocumentPtr->getRootDataObject())) {
				ostringstream	 print_buf;
				
				print_buf << "SDO_DAS_XML::loadString - Unable to parse the supplied xml string\n";
				print_buf << error_count << " parse error(s) occurred when parsing the string";
				if (error_count > MAX_ERRORS) {
					print_buf << "(only the first " << MAX_ERRORS << " shown)"; 
				}
				print_buf << ":\n" ;
				
				for (int error_ix = 0; error_ix < min(error_count, MAX_ERRORS); error_ix++) {
					print_buf << error_ix + 1 << ". " << xmldas->xmlHelperPtr->getErrorMessage(error_ix) << endl;
				}
				std::string print_string = print_buf.str();
				sdo_das_xml_throw_parserexception((char *)print_string.c_str() TSRMLS_CC);
				RETURN_NULL();
            }
        } catch (SDOXMLParserException e) {
            sdo_das_xml_throw_parserexception((char*)e.getMessageText() TSRMLS_CC);
            exception_thrown = true;
        } catch(SDORuntimeException e) {
            // The exceptions caught here have been thrown across the 
			// boundary between two shared libraries (sdo_das_xml.so and sdo.so)
			// In some build environments this has been shown to not work. 
			// The symptom is that the exception hierachy is ignored and the
			// exception arrives here as a runtime exception.
			// We have added this extra test of the stored classname
			// just to be sure
            sdo_temporary_exception_test ( e, "InLoadStringSoNoFile" TSRMLS_CC);
            exception_thrown = true;
        }
    } else {
        RETURN_FALSE;
    }
    if (exception_thrown) {
	/** 
	 * we set a flag and then do the return from here, because returning out of the catch block 
	 * (i.e. doing RETURN_NULL() inside the catch block) would cause a crash in PHP or MSCVRT soon
	 * after. Matthew saw this but not Caroline, and only with optimised build, so perhaps something
	 * specific to particular level of compiler.  
	 */
	RETURN_NULL();
	}
}
/* }}} SDO_DAS_XML::load */

/* {{{ proto void SDO_DAS_XML::saveFile(SDO_DAS_XML_Document xdoc, string xml_file)
 */
PHP_METHOD(SDO_DAS_XML, saveFile) 
{
    zval				*z_document;
    xmldocument_object	*xmldocument;
    xmldas_object		*xmldas;
    char				*xml_file;
    int					 xml_file_len;
    long				 indent = -1;
	char				*class_name, *space;

    if (ZEND_NUM_ARGS() != 2 && ZEND_NUM_ARGS() != 3) {
        WRONG_PARAM_COUNT;
    }
    xmldas = (xmldas_object *) zend_object_store_get_object(getThis() TSRMLS_CC);

	if (zend_parse_parameters(ZEND_NUM_ARGS() TSRMLS_CC, "Os|l",
		&z_document, sdo_das_xml_document_class_entry, &xml_file,
		&xml_file_len, &indent) == FAILURE) {
		RETURN_FALSE;
	}
    	
    xmldocument = (xmldocument_object *) zend_object_store_get_object(z_document TSRMLS_CC);
    if (!xmldocument) {
		class_name = get_active_class_name (&space TSRMLS_CC);
		php_error(E_ERROR, "%s%s%s(): internal error (%i) - SDO_DAS_XML_Document not found in store", 
			class_name, space, get_active_function_name(TSRMLS_C), __LINE__);
    }
    try {
        xmldas->xmlHelperPtr->save(xmldocument->xmlDocumentPtr, xml_file, indent);
    } catch (SDORuntimeException e) {
        sdo_das_xml_throw_runtimeexception(&e TSRMLS_CC);
    }
}
/* }}} */

/* {{{ proto string SDO_DAS_XML::saveString(SDO_DAS_XML_Document xdoc)
 */
PHP_METHOD(SDO_DAS_XML, saveString) 
{
    zval				*z_document;
    xmldocument_object	*xmldocument;
    xmldas_object		*xmldas;
    long				 indent = -1;
	char				*class_name, *space;

    if (ZEND_NUM_ARGS() != 1 && ZEND_NUM_ARGS() != 2) {
        WRONG_PARAM_COUNT;
    }
	
    xmldas = (xmldas_object *) zend_object_store_get_object(getThis() TSRMLS_CC);
	
	if (zend_parse_parameters(ZEND_NUM_ARGS() TSRMLS_CC, "O|l",
		&z_document, sdo_das_xml_document_class_entry, &indent) == FAILURE) {
		RETURN_FALSE;
	}
	
    xmldocument = (xmldocument_object *) zend_object_store_get_object(z_document TSRMLS_CC);
    if (!xmldocument) {
		class_name = get_active_class_name (&space TSRMLS_CC);
		php_error(E_ERROR, "%s%s%s(): internal error (%i) - SDO_DAS_XML_Document not found in store", 
			class_name, space, get_active_function_name(TSRMLS_C), __LINE__);
    }
    try {
        char *retval = xmldas->xmlHelperPtr->save(xmldocument->xmlDocumentPtr, indent);
		RETVAL_STRING(retval, 1);
		/*
		 * retval to be freed using "delete" as it was
		 * allocated using "new" in save method
		 */
		delete retval;
    } catch (SDORuntimeException e) {
        sdo_das_xml_throw_runtimeexception(&e TSRMLS_CC);
    }
}
/* }}} */

/* {{{ proto SDO_DAS_XML SDO_DAS_XML::createDocument(empty | typename | uri,typename)
 */
PHP_METHOD(SDO_DAS_XML, createDocument) 
{
    xmldocument_object	*xmldocument;
    xmldas_object		*xmldas;
    char				*namespace_uri;
    int					 namespace_uri_len;
    char				*element_name;
    int					 element_name_len;
	zval                *z_dataobject;
	DataObjectPtr        dop;
	char				*class_name, *space;

    if (ZEND_NUM_ARGS() > 3) {
        WRONG_PARAM_COUNT;
    }

    Z_TYPE_P(return_value) = IS_OBJECT;
    if (object_init_ex(return_value, sdo_das_xml_document_class_entry) == FAILURE) {
		class_name = get_active_class_name(&space TSRMLS_CC);
		php_error(E_ERROR, "%s%s%s(): internal error (%i) - failed to instantiate %s object", 
			class_name, space, get_active_function_name(TSRMLS_C), __LINE__, CLASS_NAME);
        RETURN_NULL();
	}
    xmldocument = (xmldocument_object *) zend_object_store_get_object(return_value TSRMLS_CC);
    if (!xmldocument) {
		class_name = get_active_class_name (&space TSRMLS_CC);
		php_error(E_ERROR, "%s%s%s(): internal error (%i) - SDO_DAS_XML_Document not found in store", 
			class_name, space, get_active_function_name(TSRMLS_C), __LINE__);
        RETURN_NULL();
    }

    xmldas = (xmldas_object *) zend_object_store_get_object(getThis() TSRMLS_CC);

    try {
		switch(ZEND_NUM_ARGS()) {
		case 3:
			if (zend_parse_parameters(ZEND_NUM_ARGS() TSRMLS_CC, "ssO", 
				&namespace_uri, &namespace_uri_len, 
				&element_name, &element_name_len,
				&z_dataobject, sdo_dataobjectimpl_class_entry) == FAILURE) {
				RETURN_FALSE;
			}	
			/* get the supplied data object */
	        dop = sdo_do_get(z_dataobject TSRMLS_CC);
	        if (!dop) {
		        class_name = get_active_class_name(&space TSRMLS_CC);
		        php_error(E_ERROR, "%s%s%s(): internal error (%i) - SDO_DataObject not found in store", 
			        class_name, space, get_active_function_name(TSRMLS_C), __LINE__);
                RETURN_FALSE;
			}
			/* Yes, these parameters really are in a different order to the 2-arg overloaded method */
			xmldocument->xmlDocumentPtr = xmldas->xmlHelperPtr->createDocument(dop, namespace_uri, element_name);
            break;

        case 2:
			if (zend_parse_parameters(ZEND_NUM_ARGS() TSRMLS_CC, "ss", 
				&namespace_uri, &namespace_uri_len, &element_name, &element_name_len) == FAILURE) {
				RETURN_FALSE;
			}
			xmldocument->xmlDocumentPtr = xmldas->xmlHelperPtr->createDocument(element_name, namespace_uri);
			break;

		case 1:
			if (zend_parse_parameters(ZEND_NUM_ARGS() TSRMLS_CC, "s", &element_name, &element_name_len) == FAILURE) {
				RETURN_FALSE;
			}
			xmldocument->xmlDocumentPtr = xmldas->xmlHelperPtr->createDocument(element_name);
			break;

		default:
			xmldocument->xmlDocumentPtr = xmldas->xmlHelperPtr->createDocument();

		}

        if ((!xmldocument->xmlDocumentPtr) || 
			(!xmldocument->xmlDocumentPtr->getRootDataObject())) {
			RETURN_NULL();
        }
    } catch(SDORuntimeException e) {
        sdo_das_xml_throw_runtimeexception(&e TSRMLS_CC);
        RETURN_NULL();
    }
    
}
/* }}} end SDO_DAS_XML::createDocument */

/* {{{ proto SDO_DataObject SDO_DAS_XML::createDataObject(string namespace_uri, string type_name)
 */
PHP_METHOD(SDO_DAS_XML, createDataObject) 
{
    /*
     * Returns SDO_DataObject for a given namespace_uri and the type name.
     */
    xmldas_object	*xmldas;
    zval			*z_namespace_uri;
    zval			*z_type_name;
    zval			*z_do, *z_df;

    if (ZEND_NUM_ARGS() != 2) {
        WRONG_PARAM_COUNT;
    }

    if (zend_parse_parameters(ZEND_NUM_ARGS() TSRMLS_CC, "zz",
		&z_namespace_uri, &z_type_name) == FAILURE) {
        RETURN_FALSE;
    }

    xmldas = (xmldas_object *) zend_object_store_get_object(getThis() TSRMLS_CC);

	/* Invoke SDO_DAS_DataFactory::create */
	z_df = &xmldas->z_df;
	zend_call_method(&z_df, Z_OBJCE(xmldas->z_df), NULL,
		"create", strlen("create"), &z_do, 2, z_namespace_uri,
		z_type_name TSRMLS_CC);
	RETVAL_ZVAL(z_do, 0, 1);

}
/* }}} SDO_DAS_XML::createDataObject */

/* {{{ SDO_DAS_XML::__toString
 */
PHP_METHOD(SDO_DAS_XML, __toString)
{
#if PHP_MAJOR_VERSION > 5 || (PHP_MAJOR_VERSION == 5 && PHP_MINOR_VERSION > 1)
	sdo_das_xml_cast_object(getThis(), return_value, IS_STRING TSRMLS_CC);
#else	
	sdo_das_xml_cast_object(getThis(), return_value, IS_STRING, 0 TSRMLS_CC);
#endif
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
