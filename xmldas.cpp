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

#include "php_sdo_das_xml.h"

/* {{{ sdo_das_xml_module_entry
 */
zend_module_entry sdo_das_xml_module_entry = {
#if ZEND_MODULE_API_NO >= 20010901
    STANDARD_MODULE_HEADER,
#endif
    "sdo_das_xml",
    NULL,
    PHP_MINIT(sdo_das_xml),
    NULL, /* Module Shutdown */
    NULL, /* Request start up*/
    NULL, /* Request shutdown*/
    PHP_MINFO(sdo_das_xml),
#if ZEND_MODULE_API_NO >= 20010901
    SDO_DAS_XML_VERSION,
#endif
    STANDARD_MODULE_PROPERTIES
};
/* }}} */

#ifdef COMPILE_DL_SDO_DAS_XML
BEGIN_EXTERN_C()
ZEND_GET_MODULE(sdo_das_xml)
END_EXTERN_C()
#endif

zend_class_entry *sdo_xmlparserexcep_ce = NULL;

/* {{{ PHP_MINIT_FUNCTION
 *
 * sdo_das_xml module initialization
 */

PHP_MINIT_FUNCTION(sdo_das_xml)
{

    zend_class_entry ce;
    /* Initializes sdo_das_xml class*/
    initialize_sdo_das_xml_class(ce TSRMLS_CC);
    /*Initializes sdo_xmldocument class*/
    initialize_sdo_das_xml_document_class(ce TSRMLS_CC);

    /* SDO_XMLParserException extends SDO_Exception */
    INIT_CLASS_ENTRY(ce, "SDO_DAS_XML_ParserException", sdo_exception_methods);
    sdo_xmlparserexcep_ce = zend_register_internal_class_ex(
                            &ce, NULL,
                            "sdo_exception" TSRMLS_CC);
    return SUCCESS;
}
/* }}} */


/* {{{ PHP_MINFO_FUNCTION
 */
PHP_MINFO_FUNCTION(sdo_das_xml)
{
    php_info_print_table_start();
    php_info_print_table_header(2, "sdo_das_xml support", "enabled");
    php_info_print_table_header(2, "sdo_das_xml version", SDO_DAS_XML_VERSION);
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
