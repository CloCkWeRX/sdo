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
static char rcs_id[] = "$Id$";

#ifdef PHP_WIN32
#include <iostream>
#include <math.h>
#include "zend_config.w32.h"
#endif

#include "php.h"
#include "zend_exceptions.h"

#include "php_sdo_int.h"

#include "commonj/sdo/SDORuntimeException.h"

#define CLASS_NAME "SDO_CPPException"

static zend_object_handlers sdo_cppexception_object_handlers;

/* {{{ sdo_throw_runtimeexception
 * maps a C++ SDO exception to a PHP SDO exception and throws it
 */
zval *sdo_throw_runtimeexception(SDORuntimeException *e TSRMLS_DC)
{
	zval *z_cause;
	zval *z_sdo_exception;
	zend_class_entry *exception_class;
	const char *message = e->getMessageText();
	const char *exception_type = e->getEClassName();
	
	MAKE_STD_ZVAL(z_cause);
	sdo_cppexception_new (z_cause, e TSRMLS_CC);
		
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
	else if (strcmp(exception_type, "SDOPropertyNotSetException") == 0) 
		exception_class = sdo_propertynotsetexception_class_entry;
	else
		exception_class = sdo_exception_class_entry;
	
	z_sdo_exception = sdo_throw_exception (exception_class, message, 0, 0 TSRMLS_CC);	
	z_sdo_exception = sdo_throw_exception (exception_class, message, 0, z_cause TSRMLS_CC);	
	zval_ptr_dtor(&z_cause);
	return z_sdo_exception;
}
/* }}} */

/* {{{ sdo_cppexception_object_create
 */
static zend_object_value sdo_cppexception_create_object(zend_class_entry *ce TSRMLS_DC)
{
	zend_object *object;
	zval *tmp; /* this must be passed to hash_copy, but doesn't seem to be used */
	zval z_object;

	z_object.value.obj = zend_objects_new(&object, ce TSRMLS_CC);
	z_object.value.obj.handlers = &sdo_cppexception_object_handlers;

	ALLOC_HASHTABLE(object->properties);
	zend_hash_init(object->properties, 0, NULL, ZVAL_PTR_DTOR, 0);
	zend_hash_copy(object->properties, &ce->default_properties, (copy_ctor_func_t)zval_add_ref,
		(void *)&tmp, sizeof(zval *));

	return z_object.value.obj;
}
/* }}} */

/* {{{ sdo_cppexception_get_property
 */
static void sdo_cppexception_get_property(zval *me, char *name, long name_len, zval *return_value TSRMLS_DC) 
{
	zval *value;

	value = zend_read_property(sdo_cppexception_class_entry, me,
		name, name_len, 0 TSRMLS_CC);

	*return_value = *value;
	zval_copy_ctor(return_value);
	INIT_PZVAL(return_value);
}
/* }}} */

/* {{{ sdo_cppexception_cast_object
*/ 
static int sdo_cppexception_cast_object(zval *readobj, zval *writeobj, int type, int should_free TSRMLS_DC) 
{
	ostringstream print_buf;
	zval free_obj;
	int rc = SUCCESS;
	
	if (should_free) {
		free_obj = *writeobj;
	}
	
	sdo_cppexception_get_property(readobj, "string", sizeof("string") - 1, writeobj TSRMLS_CC);
	
	switch(type) {
	case IS_STRING:
		convert_to_string(writeobj);
		break;
	case IS_BOOL:
		convert_to_boolean(writeobj);
		break;
	case IS_LONG:
		convert_to_long(writeobj);
		break;
	case IS_DOUBLE:
		convert_to_double(writeobj);
		break;
	default:
		rc = FAILURE;
	}
	
	if (should_free) {
		zval_dtor(&free_obj);
	}
	
	return rc;
}
/* }}} */

/* {{{ sdo_cppexception_minit
 * This clas is not a PHP Exception, merely a wrapper for the data
 * from a C++ SDORuntimeException. It has similar properties to a
 * PHP Exception, but there's no debug trace.
 */
void sdo_cppexception_minit(zend_class_entry *ce TSRMLS_DC)
{ 	
	sdo_cppexception_class_entry = 
		zend_register_internal_class(ce TSRMLS_CC);	  
	sdo_cppexception_class_entry->create_object = sdo_cppexception_create_object;

	memcpy(&sdo_cppexception_object_handlers, zend_get_std_object_handlers(), sizeof(zend_object_handlers));
		/*TODO There's a signature change for cast_object in PHP6. */
#if (PHP_MAJOR_VERSION < 6) 
	sdo_cppexception_object_handlers.cast_object = sdo_cppexception_cast_object;
#endif
	
    zend_declare_property_string(sdo_cppexception_class_entry, 
		"class", sizeof("class") - 1, "", ZEND_ACC_PUBLIC TSRMLS_CC);
	zend_declare_property_string(sdo_cppexception_class_entry,
		"file", sizeof("file") - 1, "", ZEND_ACC_PUBLIC TSRMLS_CC);
	zend_declare_property_long(sdo_cppexception_class_entry,
		"line", sizeof("line") - 1, 0, ZEND_ACC_PUBLIC TSRMLS_CC);
	zend_declare_property_string(sdo_cppexception_class_entry,
		"function", sizeof("function") - 1, "", ZEND_ACC_PUBLIC TSRMLS_CC);
	zend_declare_property_string(sdo_cppexception_class_entry,
		"message", sizeof("message") - 1, "", ZEND_ACC_PUBLIC TSRMLS_CC);
	zend_declare_property_long(sdo_cppexception_class_entry,
		"severity", sizeof("severity") - 1, E_ERROR, ZEND_ACC_PUBLIC TSRMLS_CC);

	zend_declare_property_string(sdo_cppexception_class_entry,
		"string", sizeof("string") - 1, "", ZEND_ACC_PRIVATE TSRMLS_CC);
}
/* }}} */

/* {{{ sdo_cppexception_new
 * Instantiate an SDO_CPPException from an SDORuntimeException
 */
void sdo_cppexception_new(zval *me, SDORuntimeException *cpp_exception TSRMLS_DC)
{	
	const char *eclass = cpp_exception->getEClassName();
	const char *function = cpp_exception->getFunctionName();
	const char *message = cpp_exception->getMessageText();
	const char *file = cpp_exception->getFileName();
	long line = cpp_exception->getLineNumber();
	char *class_name, *space;
	
	long severity;
	
	ostringstream print_buf;
	string print_string;
	
	switch(cpp_exception->getSeverity()) {
	case SDORuntimeException::Normal:
		severity = E_NOTICE;
		break;
	case SDORuntimeException::Warning:
		severity = E_WARNING;
		break;
	case SDORuntimeException::Error:
	case SDORuntimeException::Severe:
	default:
		severity = E_ERROR;
		break;
	}
	
	if (object_init_ex(me, sdo_cppexception_class_entry) == FAILURE) {
		class_name = get_active_class_name(&space TSRMLS_CC);
		php_error(E_ERROR, "%s%s%s(): internal error (%i) - failed to instantiate %s object", 
			class_name, space, get_active_function_name(TSRMLS_C), __LINE__, CLASS_NAME);
		return;
	}
	
	if (eclass) {
		zend_update_property_string(sdo_cppexception_class_entry, me,
			"class", sizeof("class")-1, (char *)eclass TSRMLS_CC);
	}
	
	if (file) {
		zend_update_property_string(sdo_cppexception_class_entry, me,
			"file", sizeof("file")-1, (char *)file TSRMLS_CC);
	}
	
	zend_update_property_long(sdo_cppexception_class_entry, me, 
		"line", sizeof("line")-1, line TSRMLS_CC);
	
	if (function) {
		zend_update_property_string(sdo_cppexception_class_entry, me,
			"function", sizeof("function")-1, (char *)function TSRMLS_CC);
	}
	
	if (message) {
		zend_update_property_string(sdo_cppexception_class_entry, me,
			"message", sizeof("message")-1, (char *)message TSRMLS_CC);
	}
	
	zend_update_property_long(sdo_cppexception_class_entry, me, 
		"severity", sizeof("severity")-1, severity TSRMLS_CC);
	
	print_buf << *cpp_exception;
	print_string = print_buf.str();
	zend_update_property_stringl (sdo_cppexception_class_entry, me,
		"string", sizeof("string") - 1, 
		(char *)print_string.c_str(), print_string.length() TSRMLS_CC);
}
/* }}} */

/* {{{ SDO_CPPException::getClass
 */
PHP_METHOD(SDO_CPPException, getClass)
{	
	if (ZEND_NUM_ARGS() != 0) {
		WRONG_PARAM_COUNT;
	}
	
	sdo_cppexception_get_property(
		getThis(), "class", sizeof("class") - 1, return_value TSRMLS_CC);
}
/* }}} */

/* {{{ SDO_CPPException::getFile
 */
PHP_METHOD(SDO_CPPException, getFile)
{	
	if (ZEND_NUM_ARGS() != 0) {
		WRONG_PARAM_COUNT;
	}
	
	sdo_cppexception_get_property(
		getThis(), "file", sizeof("file") - 1, return_value TSRMLS_CC);
}
/* }}} */

/* {{{ SDO_CPPException::getLine
 */
PHP_METHOD(SDO_CPPException, getLine)
{	
	if (ZEND_NUM_ARGS() != 0) {
		WRONG_PARAM_COUNT;
	}
	
	sdo_cppexception_get_property(
		getThis(), "line", sizeof("line") - 1, return_value TSRMLS_CC);
}
/* }}} */

/* {{{ SDO_CPPException::getFunction
 */
PHP_METHOD(SDO_CPPException, getFunction)
{	
	if (ZEND_NUM_ARGS() != 0) {
		WRONG_PARAM_COUNT;
	}
	
	sdo_cppexception_get_property(
		getThis(), "function", sizeof("function") - 1, return_value TSRMLS_CC);
}
/* }}} */

/* {{{ SDO_CPPException::getMessage
 */
PHP_METHOD(SDO_CPPException, getMessage)
{	
	if (ZEND_NUM_ARGS() != 0) {
		WRONG_PARAM_COUNT;
	}
	
	sdo_cppexception_get_property(
		getThis(), "message", sizeof("message") - 1, return_value TSRMLS_CC);
}
/* }}} */

/* {{{ SDO_CPPException::getSeverity
 */
PHP_METHOD(SDO_CPPException, getSeverity)
{	
	if (ZEND_NUM_ARGS() != 0) {
		WRONG_PARAM_COUNT;
	}
	
	sdo_cppexception_get_property(
		getThis(), "severity", sizeof("severity") - 1, return_value TSRMLS_CC);
}
/* }}} */

/* {{{ SDO_CPPException::__toString
 */
PHP_METHOD(SDO_CPPException, __toString)
{
	sdo_cppexception_cast_object(getThis(), return_value, IS_STRING, 0 TSRMLS_CC);
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
