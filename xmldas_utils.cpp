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
static char rcs_id[] = "$Id: xmldas_utils.cpp 207591 2006-02-20 18:50:26Z mfp $";

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

/* {{{ sdo_das_parserexception_minit
 */
void sdo_das_xml_parserexception_minit(TSRMLS_D) 
{
	zend_class_entry ce;
    INIT_CLASS_ENTRY(ce, "SDO_DAS_XML_ParserException", sdo_exception_get_methods());
    sdo_das_xml_parserexception_ce = zend_register_internal_class_ex(&ce, NULL, "sdo_exception" TSRMLS_CC);
}
/* }}} */

/* {{{ sdo_das_parserexception_minit
 */
void sdo_das_xml_fileexception_minit(TSRMLS_D) 
{
  	zend_class_entry ce;
    INIT_CLASS_ENTRY(ce, "SDO_DAS_XML_FileException", sdo_exception_get_methods());
    sdo_das_xml_fileexception_ce = zend_register_internal_class_ex(&ce, NULL, "sdo_exception" TSRMLS_CC);
}
/* }}} */

/* {{{ sdo_das_xml_throw_runtimeexception
 */
zval *sdo_das_xml_throw_runtimeexception(SDORuntimeException *e TSRMLS_DC)
{
	return sdo_throw_runtimeexception(e TSRMLS_CC);
}
/* }}} */

/* {{{ sdo_das_xml_throw_parserexception
 */
zval *sdo_das_xml_throw_parserexception(char *msg TSRMLS_DC)
{
	return zend_throw_exception(sdo_das_xml_parserexception_ce, msg, 0 TSRMLS_CC);
}
/* }}} */

/* {{{ sdo_das_xml_throw_fileexception
 */
zval *sdo_das_xml_throw_fileexception(char *filename TSRMLS_DC)
{
	return zend_throw_exception_ex(sdo_das_xml_fileexception_ce, 
		0 TSRMLS_CC, "File \"%s\" could not be found", filename);
}
/* }}} */
