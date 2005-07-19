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

/*
 * Utility functions for internal use by the SDO extension
 */
#ifdef PHP_WIN32
#include <iostream>
#include <math.h>
#include "zend_config.w32.h"
#endif

#include "php.h"
#include "zend_exceptions.h"

#include "php_sdo_int.h"

static xmldas::XMLDAS *xmldasp = NULL;

/* {{{ sdo_throw_exception
 * rethrows a C++ SDO exception with a PHP SDO exception wrapper
 */
static void sdo_throw_exception(zend_class_entry *ce, SDORuntimeException *e, char *extra TSRMLS_DC)
{	
	if (extra)	
		zend_throw_exception_ex(ce, 0 TSRMLS_CC, "%s: %s\n Filename %s\n At line %ld in function %s\n Message %s\n",
		extra, e->getEClassName(), e->getFileName(), e->getLineNumber(), e->getFunctionName(), e->getMessageText());
	else 
		zend_throw_exception_ex(ce, 0 TSRMLS_CC, "%s\n Filename %s\n At line %ld in function %s\n Message %s\n",
		e->getEClassName(), e->getFileName(), e->getLineNumber(), e->getFunctionName(), e->getMessageText());
}
/* }}} */

/* {{{ sdo_throw_typenotfoundexception
 */
void sdo_throw_typenotfoundexception(SDOTypeNotFoundException *e TSRMLS_DC)
{
	sdo_throw_exception(sdo_typenotfoundexception_class_entry, e, NULL TSRMLS_CC);
}
/* }}} */

/* {{{ sdo_throw_propertynotfoundexception
 */
void sdo_throw_propertynotfoundexception(SDOPropertyNotFoundException *e TSRMLS_DC)
{
	sdo_throw_exception(sdo_propertynotfoundexception_class_entry, e, NULL TSRMLS_CC);
}
/* }}} */

/* {{{ sdo_throw_unsupportedoperationexception
 */
void sdo_throw_unsupportedoperationexception(SDOUnsupportedOperationException *e TSRMLS_DC)
{
	sdo_throw_exception(sdo_unsupportedoperationexception_class_entry, e, NULL TSRMLS_CC);
}
/* }}} */

/* {{{ sdo_throw_invalidconversionexception
 */
void sdo_throw_invalidconversionexception(SDOInvalidConversionException *e TSRMLS_DC)
{
	sdo_throw_exception(sdo_invalidconversionexception_class_entry, e, NULL TSRMLS_CC);
}
/* }}} */

/* {{{ sdo_throw_indexoutofboundsexception
 */
void sdo_throw_indexoutfboundsexception(SDOIndexOutOfRangeException *e TSRMLS_DC)
{
	sdo_throw_exception(sdo_indexoutofboundsexception_class_entry, e, NULL TSRMLS_CC);
}
/* }}} */

/* {{{ sdo_throw_runtimeexception
 * maps a C++ SDO exception to a PHP SDO exception and throws it
 */
void sdo_throw_runtimeexception(SDORuntimeException *e TSRMLS_DC)
{
	zend_class_entry *exception_class;
	const char *exception_type = e->getEClassName();

	if (strcmp(exception_type, "SDOUnsupportedOperationException") == 0) 
		exception_class = sdo_unsupportedoperationexception_class_entry;
	else if (strcmp(exception_type, "SDOPropertyNotFoundException") == 0 || 
		     strcmp(exception_type, "SDOPathNotFoundException") == 0) 
		exception_class = sdo_propertynotfoundexception_class_entry;
	else if (strcmp(exception_type, "SDOTypeNotFoundException") == 0) 
		exception_class = sdo_typenotfoundexception_class_entry;
	else if (strcmp(exception_type, "SDOIndexOutOfRangeException") == 0) 
		exception_class = sdo_indexoutofboundsexception_class_entry;
	else if (strcmp(exception_type, "SDOInvalidConversionException") == 0) 
		exception_class = sdo_invalidconversionexception_class_entry;
	else
		exception_class = sdo_exception_class_entry;

	sdo_throw_exception(exception_class, e, NULL TSRMLS_CC);
}
/* }}} */

/* {{{ sdo_make_long_class_constant
 * creates a class constant
 * not used at present because of memory corruption problems on shutdown
 */
void sdo_make_long_class_constant(zend_class_entry *ce, char *name, long value)
{
	zval *z_constant;
	MAKE_STD_ZVAL(z_constant);
	ZVAL_LONG(z_constant, value);
	zend_hash_add(&ce->constants_table, name, 1 + strlen(name), &z_constant, sizeof(zval *), NULL);
}
/* }}} */

/* {{{ sdo_get_XMLDAS
 * returns a lazily-instantiated static XMLDAS, for use in serializing /unserializing
 */
xmldas::XMLDAS *sdo_get_XMLDAS() {
	if (xmldasp == NULL) {
		xmldasp = xmldas::XMLDAS::create();
	}
	return xmldasp;
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
