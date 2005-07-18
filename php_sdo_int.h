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
| Author: Caroline Maynard                                             | 
+----------------------------------------------------------------------+ 

*/
#ifndef PHP_SDO_INT_H
#define PHP_SDO_INT_H

/*
 * Declarations intended for use internal to the SDO extension
 */
#include "php_sdo.h"

#include "SDOSPI.h"
#include "XMLDAS.h"

using namespace commonj::sdo;

#define SDO_VERSION "20050714"

#define SDO_NAMESPACE_URI "namespaceURI"
#define SDO_TYPE_NAME     "typeName"

#define SDO_USER_DATA_EMPTY 0xffffffff

/**
 * The internal structure for an SDO_DataObjectImpl
 * This extends the standard zend_object
 */
typedef struct {
	zend_object		 zo;			/* The standard zend_object */
	DataObjectPtr    dop;			/* The C++ DataObject */
} sdo_do_object;

enum sdo_changesummary_type {
	CS_NONE = 0,
	CS_MODIFICATION = 1,
	CS_ADDITION = 2,
	CS_DELETION = 3
};

enum sdo_write_type {
	TYPE_APPEND = 1001,
	TYPE_INSERT,
	TYPE_OVERWRITE
};

extern zend_class_entry *sdo_das_datafactory_class_entry;
extern zend_class_entry *sdo_das_datafactoryimpl_class_entry;
extern zend_class_entry *sdo_dataobject_class_entry;
extern zend_class_entry *sdo_dataobjectimpl_class_entry;
extern zend_class_entry *sdo_list_class_entry;
extern zend_class_entry *sdo_dataobjectlist_class_entry;
extern zend_class_entry *sdo_changeddataobjectlist_class_entry;
extern zend_class_entry *sdo_das_dataobject_class_entry;
extern zend_class_entry *sdo_das_setting_class_entry;
extern zend_class_entry *sdo_das_settinglist_class_entry;
extern zend_class_entry *sdo_das_changesummary_class_entry;
extern zend_class_entry *sdo_sequence_class_entry;
extern zend_class_entry *sdo_sequenceimpl_class_entry;
extern zend_class_entry *sdo_exception_class_entry;
extern zend_class_entry *sdo_typenotfoundexception_class_entry;
extern zend_class_entry *sdo_propertynotsetexception_class_entry;
extern zend_class_entry *sdo_propertynotfoundexception_class_entry;
extern zend_class_entry *sdo_invalidconversionexception_class_entry;
extern zend_class_entry *sdo_indexoutofboundsexception_class_entry;
extern zend_class_entry *sdo_unsupportedoperationexception_class_entry;

extern void sdo_das_df_minit(zend_class_entry *tmp TSRMLS_DC);
extern void sdo_das_df_new(zval *me, DASDataFactoryPtr dfp TSRMLS_DC);

extern void sdo_do_minit(zend_class_entry *tmp TSRMLS_DC);
extern void sdo_do_new(zval *me, DataObjectPtr dop TSRMLS_DC);

extern void sdo_list_minit(zend_class_entry *tmp TSRMLS_DC);
extern void sdo_dataobjectlist_new(zval *me, const Type& typeh, DataObjectList *listh TSRMLS_DC);
extern void sdo_changeddataobjectlist_new(zval *me, const ChangedDataObjectList *listh TSRMLS_DC);
extern void sdo_das_settinglist_new(zval *me, SettingList& listh TSRMLS_DC);
extern int  sdo_list_count_elements(zval *object, long *count TSRMLS_DC); 

extern void sdo_das_changesummary_minit(zend_class_entry *tmp TSRMLS_DC);
extern void sdo_das_changesummary_new(zval *me, ChangeSummary *change_summary TSRMLS_DC);

extern void sdo_das_setting_minit(zend_class_entry *tmp TSRMLS_DC);
extern void sdo_das_setting_new(zval *me, Setting *setting TSRMLS_DC);

extern void sdo_sequence_minit(zend_class_entry *tmp TSRMLS_DC);
extern void sdo_sequence_new(zval *me, SequencePtr seqp, DataObjectPtr dop TSRMLS_DC);

extern void sdo_throw_propertytnotfoundexception(SDOPropertyNotFoundException *e TSRMLS_DC);
extern void sdo_throw_typenotfoundexception(SDOTypeNotFoundException *e TSRMLS_DC);
extern void sdo_throw_unsupportedoperationexception(SDOUnsupportedOperationException *e TSRMLS_DC);
extern void sdo_throw_invalidconversionexception(SDOInvalidConversionException *e TSRMLS_DC);
extern void sdo_throw_runtimeexception(SDORuntimeException *e TSRMLS_DC);

extern void sdo_make_long_class_constant(zend_class_entry *ce, char *name, long value);

extern xmldas::XMLDAS *sdo_get_XMLDAS();

extern function_entry sdo_exception_methods[];

#endif	/* PHP_SDO_INT_H */

/*
 * Local variables:
 * tab-width: 4
 * c-basic-offset: 4
 * End:
 * vim600: noet sw=4 ts=4 fdm=marker
 * vim<600: noet sw=4 ts=4
 */