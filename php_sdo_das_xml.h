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
/* $Id$ */

#ifndef PHP_SDO_DAS_XML_H
#define PHP_SDO_DAS_XML_H

extern "C" {

#ifdef HAVE_CONFIG_H
#include "config.h"
#endif

#ifdef PHP_WIN32
#include <iostream>
#include <math.h>
#include "zend_config.w32.h"
#endif

#include "php.h"
#include "php_ini.h"
#include "ext/standard/info.h"

#include "zend_exceptions.h"
#include "zend_hash.h"
#include "zend_interfaces.h"

#ifdef ZTS
#include "TSRM.h"
#endif
}

#include "php_sdo_das_xml_int.h"

extern zend_module_entry sdo_das_xml_module_entry;

PHP_MINIT_FUNCTION(sdo_das_xml);
PHP_MINFO_FUNCTION(sdo_das_xml);

/* SDO_DAS_XML Class methods declarations */
PHP_METHOD(SDO_DAS_XML, __construct);
PHP_METHOD(SDO_DAS_XML, create);
PHP_METHOD(SDO_DAS_XML, loadFromFile);
PHP_METHOD(SDO_DAS_XML, loadFromString);
PHP_METHOD(SDO_DAS_XML, createDataObject);
PHP_METHOD(SDO_DAS_XML, saveDocumentToString);
PHP_METHOD(SDO_DAS_XML, saveDocumentToFile);
PHP_METHOD(SDO_DAS_XML, saveDataObjectToString);
PHP_METHOD(SDO_DAS_XML, saveDataObjectToFile);


/* SDO_DAS_XML_Document Class methods declarations */
PHP_METHOD(SDO_DAS_XML_Document, getRootDataObject);
PHP_METHOD(SDO_DAS_XML_Document, getRootElementURI);
PHP_METHOD(SDO_DAS_XML_Document, getRootElementName);
PHP_METHOD(SDO_DAS_XML_Document, getEncoding);
PHP_METHOD(SDO_DAS_XML_Document, setEncoding);
PHP_METHOD(SDO_DAS_XML_Document, getXMLDeclaration);
PHP_METHOD(SDO_DAS_XML_Document, setXMLDeclaration);
PHP_METHOD(SDO_DAS_XML_Document, getXMLVersion);
PHP_METHOD(SDO_DAS_XML_Document, setXMLVersion);
PHP_METHOD(SDO_DAS_XML_Document, getSchemaLocation);
PHP_METHOD(SDO_DAS_XML_Document, setSchemaLocation);
PHP_METHOD(SDO_DAS_XML_Document, getNoNamespaceSchemaLocation);
PHP_METHOD(SDO_DAS_XML_Document, setNoNamespaceSchemaLocation);

#endif	/* PHP_SDO_DAS_XML_H */

/*
 * Local variables:
 * tab-width: 4
 * c-basic-offset: 4
 * End:
 * vim600: noet sw=4 ts=4 fdm=marker
 * vim<600: noet sw=4 ts=4
 */
