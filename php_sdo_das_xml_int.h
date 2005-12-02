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
void initialize_sdo_das_xml_class(TSRMLS_D);
void initialize_sdo_das_xml_document_class(TSRMLS_D);
void initialize_sdo_das_xml_parserexception_class(TSRMLS_D);
void initialize_sdo_das_xml_fileexception_class(TSRMLS_D);

/* 	 The following three are defined in xmldas_utils.cpp - they logically belong in a xmldas_utils.h file	*/
void sdo_das_xml_throw_runtimeexception(SDORuntimeException *e TSRMLS_DC);
void sdo_das_xml_throw_fileexception(char* filename TSRMLS_DC);
void sdo_das_xml_throw_parserexception(char* filename TSRMLS_DC);

/******************* Our data types ***********************************/
/* SDO_DAS_XML */
typedef struct {
    zend_object z_obj;
    XMLHelperPtr xmlHelper;
    XSDHelperPtr xsdHelper;
    SDOXMLString targetNamespaceURI;
    zval *php_das_df;
} xmldas_object;

/* SDO_DAS_XML_Document */
typedef struct {
    zend_object z_obj;
    XMLDocumentPtr xdoch;
} xmldocument_object;

/* SDO_DAS_DataFactory */
typedef struct {
    zend_object zo;
    DataFactoryPtr dfp;
} sdo_das_df_object;

#endif /*_PHP_SDO_DEFS_H*/

/*
 * Local variables:
 * tab-width: 4
 * c-basic-offset: 4
 * End:
 * vim600: noet sw=4 ts=4 fdm=marker
 * vim<600: noet sw=4 ts=4
 */
