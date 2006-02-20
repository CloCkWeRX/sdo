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
/* $Id$ */

#ifndef _PHP_SDO_DEFS_H
#define _PHP_SDO_DEFS_H

/***************** Our own external interface ************************/
#include "php_sdo_das_xml.h"

/***************** Dependencies on the PHP sdo extension *************/
#include "php_sdo_int.h"

/******************* Dependencies on the C++ SDO library *************/
#include "commonj/sdo/SDO.h"
#include "commonj/sdo/DataFactory.h"
#include "commonj/sdo/HelperProvider.h"
#include "commonj/sdo/SDOXMLString.h"
#include "commonj/sdo/XMLDocument.h"
#include <sstream>

using namespace commonj;
using namespace sdo;

/***************** Dependencies between files within XML_DAS ********/
/*	 The following four are called from das_xml.cpp's MINIT, and will be found in SDO_DAS_XML., SDO_DAS_XML_Document., and xmldas_utils.cpp	*/
void sdo_das_xml_minit(TSRMLS_D);
void sdo_das_xml_document_minit(TSRMLS_D);
void sdo_das_xml_parserexception_minit(TSRMLS_D);
void sdo_das_xml_fileexception_minit(TSRMLS_D);

/* 	 The following three are defined in xmldas_utils.cpp - they logically belong in a xmldas_utils.h file	*/
zval *sdo_das_xml_throw_runtimeexception(SDORuntimeException *e TSRMLS_DC);
zval *sdo_das_xml_throw_fileexception(char* filename TSRMLS_DC);
zval *sdo_das_xml_throw_parserexception(char* filename TSRMLS_DC);

extern PHP_SDO_DAS_XML_API zend_class_entry *sdo_das_xml_class_entry;
extern PHP_SDO_DAS_XML_API zend_class_entry *sdo_das_xml_document_class_entry;

/******************* Our data types ***********************************/
/* SDO_DAS_XML */
typedef struct {
    zend_object    zo;
    XMLHelperPtr   xmlHelperPtr;
    XSDHelperPtr   xsdHelperPtr;
	zval           z_df;
} xmldas_object;

/* SDO_DAS_XML_Document */
typedef struct {
    zend_object    zo;
    XMLDocumentPtr xmlDocumentPtr;
} xmldocument_object;

#endif /*_PHP_SDO_DEFS_H*/

/*
 * Local variables:
 * tab-width: 4
 * c-basic-offset: 4
 * End:
 * vim600: noet sw=4 ts=4 fdm=marker
 * vim<600: noet sw=4 ts=4
 */
