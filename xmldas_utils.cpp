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

#include "zend_exceptions.h" // needed for zend_throw_exception_ex

zend_class_entry *sdo_das_xml_parserexception_ce = NULL;
zend_class_entry *sdo_das_xml_fileexception_ce = NULL;

/* {{{ initialize_sdo_das_parserexception_class
 */
void initialize_sdo_das_xml_parserexception_class(TSRMLS_D) 
{
    zend_class_entry ce;

    INIT_CLASS_ENTRY(ce, "SDO_DAS_XML_ParserException", sdo_get_exception_methods());
    sdo_das_xml_parserexception_ce = zend_register_internal_class_ex(&ce, NULL, "sdo_exception" TSRMLS_CC);
}
/* }}} */

/* {{{ initialize_sdo_das_parserexception_class
 */
void initialize_sdo_das_xml_fileexception_class(TSRMLS_D) 
{
    zend_class_entry ce;

    INIT_CLASS_ENTRY(ce, "SDO_DAS_XML_FileException", sdo_get_exception_methods());
    sdo_das_xml_fileexception_ce = zend_register_internal_class_ex(&ce, NULL, "sdo_exception" TSRMLS_CC);
}
/* }}} */

/* {{{ sdo_das_xml_throw_exception
 * Throws C++ SDO exception as PHP exception
 */
static void
sdo_das_xml_throw_exception(zend_class_entry *ce, SDORuntimeException *e, char *extra TSRMLS_DC)
{
    if (extra) {
        zend_throw_exception_ex(ce, 0 TSRMLS_CC, "%s: %s\n Filename %s\n At line %ld in function %s\n Message %s\n",
                                extra, e->getEClassName(), e->getFileName(),
                                e->getLineNumber(), e->getFunctionName(),
                                e->getMessageText());
    } else {
        zend_throw_exception_ex(ce, 0 TSRMLS_CC, "%s\n Filename %s\n At line %ld in function %s\n Message %s\n",
                                e->getEClassName(), e->getFileName(),
                                e->getLineNumber(), e->getFunctionName(),
                                e->getMessageText());
    }
}
/* }}} */

/* {{{ sdo_das_xml_throw_runtimeexception
 */
void
sdo_das_xml_throw_runtimeexception(SDORuntimeException *e TSRMLS_DC)
{
        const char *exception_type = e->getEClassName();
        sdo_throw_runtimeexception(e TSRMLS_CC);
}
/* }}} */

/* {{{ sdo_das_xml_throw_parserexception
 */
void
sdo_das_xml_throw_parserexception(char * msg TSRMLS_DC)
{
        zend_class_entry *ce = sdo_das_xml_parserexception_ce;

		zend_throw_exception_ex(ce, 0 TSRMLS_CC, "%s\n", msg);
}
/* }}} */

/* {{{ sdo_das_xml_throw_fileexception
 */
void sdo_das_xml_throw_fileexception(char *filename TSRMLS_DC)
{
        zend_class_entry *ce = sdo_das_xml_fileexception_ce;
        
		zend_throw_exception_ex(ce, 0 TSRMLS_CC, "File \"%s\" could not be found.\n", filename);
}
/* }}} */
