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

#ifdef HAVE_CONFIG_H
#include "config.h"
#endif

#include "php.h"
#include "php_ini.h"
#include "ext/standard/info.h"

#include "zend_exceptions.h"
#include "zend_interfaces.h"
#include "ext/standard/info.h"

#include "php_sdo_das_xml_int.h"

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

/* {{{ PHP_MINIT_FUNCTION
 *
 * sdo_das_xml module initialization
 */

PHP_MINIT_FUNCTION(sdo_das_xml)
{
    initialize_sdo_das_xml_class(TSRMLS_C);
    initialize_sdo_das_xml_document_class(TSRMLS_C);
    initialize_sdo_das_xml_parserexception_class(TSRMLS_C);
    initialize_sdo_das_xml_fileexception_class(TSRMLS_C);

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
