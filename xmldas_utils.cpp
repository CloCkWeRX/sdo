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
static char rcs_id[] = "$Id$";

#ifdef PHP_WIN32
/* disable warning about identifier lengths in browser information */
#pragma warning(disable: 4786)
#include <iostream>
#include <math.h>
#include "zend_config.w32.h"
#endif

#include "php_sdo_das_xml.h"

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
 * This is an added functionality for PHP SDO's sdo_throw_runtimeexception
 * to capture C++ SDOXMLParserException
 */
void
sdo_das_xml_throw_runtimeexception(SDORuntimeException *e TSRMLS_DC)
{
        zend_class_entry *exception_class;
        const char *exception_type = e->getEClassName();
        if (strcmp(exception_type, "SDOXMLParserException") == 0) {
                exception_class = sdo_xmlparserexcep_ce;
                sdo_das_xml_throw_exception(exception_class, e, NULL TSRMLS_CC);
        } else {
            sdo_throw_runtimeexception(e TSRMLS_CC);
        }
}
/* }}} */
