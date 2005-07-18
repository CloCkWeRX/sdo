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

#ifndef _PHP_SDO_DEFS_H
#define _PHP_SDO_DEFS_H

#include "php_sdo_int.h"
#include "SDO.h"
#include "DASDataFactory.h"
#include "XMLDASExport.h"
#include "XMLDAS.h"
#include "XMLDocument.h"

using namespace commonj;
using namespace sdo;
using namespace xmldas;

#define SDO_DAS_XML_VERSION "20050714"

/*
 * Internal structure for an SDO_DAS_XML
 */

typedef struct {
    zend_object z_obj;
    XMLDAS *xdh;
    zval *php_das_df;
} xmldas_object;

/*
 * Internal structure for an SDO_DAS_XML_Document
 */

typedef struct {
    zend_object z_obj;
    XMLDocumentPtr xdoch;
} xmldocument_object;

extern zend_class_entry* sdo_das_xml_doc_cls_entry;
extern zend_class_entry* sdo_xmlparserexcep_ce;
extern zend_class_entry* sdo_das_xml_class_entry;

void initialize_sdo_das_xml_class(zend_class_entry ce TSRMLS_DC);
void sdo_das_xml_object_free_storage(void *object TSRMLS_DC);
void initialize_sdo_das_xml_document_class(zend_class_entry ce TSRMLS_DC);
void sdo_das_xml_document_object_free_storage(void *object TSRMLS_DC);

extern void sdo_das_xml_throw_runtimeexception(SDORuntimeException *e TSRMLS_DC);

static void
sdo_das_xml_throw_exception(zend_class_entry *ce, SDORuntimeException *e, char *extra TSRMLS_DC);

zend_object_value 
sdo_das_xml_object_create(zend_class_entry *ce TSRMLS_DC);

zend_object_value 
sdo_das_xml_document_object_create(zend_class_entry *ce TSRMLS_DC);

/*
 * Redefine the sdo_das_df_object here, as converting from DataFactoryPtr 
 * to DASDataFactory is not allowed.
 */

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
