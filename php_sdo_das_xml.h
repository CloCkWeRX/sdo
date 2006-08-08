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
/* $Id$ */
#ifndef PHP_SDO_DAS_XML_H
#define PHP_SDO_DAS_XML_H

#include "php.h"

#ifdef ZTS
#include "TSRM.h"
#endif

#define SDO_DAS_XML_VERSION "1.0.3"

#ifdef PHP_WIN32
#    if defined (SDO_DAS_XML_EXPORTS)
#        define PHP_SDO_DAS_XML_API __declspec(dllexport)
#    elif defined(COMPILE_DL_SDO_DAS_XML)
#        define PHP_SDO_DAS_XML_API __declspec(dllimport)
#    else
#        define PHP_SDO_DAS_XML_API
#    endif
#else
#define PHP_SDO_DAS_XML_API
#endif

PHP_MINIT_FUNCTION(sdo_das_xml);
PHP_MINFO_FUNCTION(sdo_das_xml);

/* SDO_DAS_XML Class methods declarations */
PHP_METHOD(SDO_DAS_XML, __construct);
PHP_METHOD(SDO_DAS_XML, create);
PHP_METHOD(SDO_DAS_XML, addTypes);
PHP_METHOD(SDO_DAS_XML, loadFile);
PHP_METHOD(SDO_DAS_XML, loadString);
PHP_METHOD(SDO_DAS_XML, saveFile);
PHP_METHOD(SDO_DAS_XML, saveString);
PHP_METHOD(SDO_DAS_XML, createDocument);
PHP_METHOD(SDO_DAS_XML, createDataObject);
PHP_METHOD(SDO_DAS_XML, __toString);

/* SDO_DAS_XML_Document Class methods declarations */
PHP_METHOD(SDO_DAS_XML_Document, getRootDataObject);
PHP_METHOD(SDO_DAS_XML_Document, getRootElementURI);
PHP_METHOD(SDO_DAS_XML_Document, getRootElementName);
PHP_METHOD(SDO_DAS_XML_Document, setXMLDeclaration);
PHP_METHOD(SDO_DAS_XML_Document, setXMLVersion);
PHP_METHOD(SDO_DAS_XML_Document, setEncoding);

#endif	/* PHP_SDO_DAS_XML_H */

/*
 * Local variables:
 * tab-width: 4
 * c-basic-offset: 4
 * End:
 * vim600: noet sw=4 ts=4 fdm=marker
 * vim<600: noet sw=4 ts=4
 */
