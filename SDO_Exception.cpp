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
#include "zend.h"
#include "zend_exceptions.h"
#include "zend_builtin_functions.h"

#include "php_sdo_int.h"

#define CLASS_NAME "SDO_Exception"

static zend_class_entry *exception_class_entry;
static zend_object_handlers sdo_exception_object_handlers;

/* {{{ sdo_exception_object_create
 */
static zend_object_value sdo_exception_create_object(zend_class_entry *ce TSRMLS_DC)
{
	zend_object *object;
	zval *tmp; /* this must be passed to hash_copy, but doesn't seem to be used */
	zval z_object;
	zval *z_trace;

	z_object.value.obj = zend_objects_new(&object, ce TSRMLS_CC);
	z_object.value.obj.handlers = &sdo_exception_object_handlers;

	ALLOC_HASHTABLE(object->properties);
	zend_hash_init(object->properties, 0, NULL, ZVAL_PTR_DTOR, 0);
	zend_hash_copy(object->properties, &ce->default_properties, (copy_ctor_func_t)zval_add_ref,
		(void *)&tmp, sizeof(zval *));

	zend_update_property_string(exception_class_entry, &z_object,
		"file", sizeof("file") - 1, zend_get_executed_filename(TSRMLS_C) TSRMLS_CC);
	zend_update_property_long(exception_class_entry, &z_object,
		"line", sizeof("line") - 1, zend_get_executed_lineno(TSRMLS_C) TSRMLS_CC);

	ALLOC_ZVAL(z_trace);
	z_trace->is_ref = 0;
	z_trace->refcount = 0;
	zend_fetch_debug_backtrace(z_trace, 0, 0 TSRMLS_CC);

	zend_update_property(exception_class_entry, &z_object,
		"trace", sizeof("trace") - 1, z_trace TSRMLS_CC);
	return z_object.value.obj;
}
/* }}} */

/* {{{ sdo_exception_minit
 */
void sdo_exception_minit(zend_class_entry *ce TSRMLS_DC)
{
#if (PHP_MAJOR_VERSION < 6)
	exception_class_entry = zend_exception_get_default();
#else
	exception_class_entry = zend_exception_get_default(TSRMLS_C);
#endif

	sdo_exception_class_entry = zend_register_internal_class_ex(
		ce, exception_class_entry, NULL TSRMLS_CC);

	sdo_exception_class_entry->create_object = sdo_exception_create_object;

	/*
	* The SDO_Exception adds a cause property to the base Exception class.
	* This may be set to any value, normally the original SDO_CPPException.
	*/
    zend_declare_property_null(sdo_exception_class_entry,
		"cause", sizeof("cause") - 1, ZEND_ACC_PUBLIC TSRMLS_CC);

	memcpy(&sdo_exception_object_handlers, zend_get_std_object_handlers(), sizeof(zend_object_handlers));
	sdo_exception_object_handlers.clone_obj = NULL;

}
/* }}} */

/* {{{ sdo_exception_new
 * Create a new SDO_Exception of the class specified
 */
void sdo_exception_new(zval *z_ex, zend_class_entry *ce, const char *message, long code, zval *z_cause TSRMLS_DC)
{
	char *class_name, *space;

	Z_TYPE_P(z_ex) = IS_OBJECT;
	if (object_init_ex(z_ex, ce) == FAILURE) {
		class_name = get_active_class_name(&space TSRMLS_CC);
		php_error(E_ERROR, "%s%s%s(): internal error (%i) - failed to instantiate %s object",
			class_name, space, get_active_function_name(TSRMLS_C), __LINE__, CLASS_NAME);
		return;
	}

	if (message) {
		zend_update_property_string(exception_class_entry, z_ex,
			"message", sizeof("message") - 1, (char *)message TSRMLS_CC);
	}

	zend_update_property_long(exception_class_entry, z_ex,
		"code", sizeof("code") - 1, code TSRMLS_CC);

	if (z_cause) {
		zend_update_property(sdo_exception_class_entry, z_ex,
			"cause", sizeof("cause") - 1, z_cause TSRMLS_CC);
	}
}
/* }}} */

/* {{{ sdo_throw_exception
 * Throw an SDO_Exception
 */
zval *sdo_throw_exception (zend_class_entry *ce, const char *message, long code, zval *z_cause TSRMLS_DC)
{
	zval *z_ex;

	MAKE_STD_ZVAL(z_ex);
	sdo_exception_new(z_ex, ce, message, code, z_cause TSRMLS_CC);

	zend_throw_exception_object (z_ex TSRMLS_CC);

	return z_ex;
}
/* }}} */

/* {{{ sdo_throw_exception_ex
 * Throw an SDO_Exception
 */
zval *sdo_throw_exception_ex(zend_class_entry *ce, long code, zval *z_cause TSRMLS_DC, char *format, ...)
{
	va_list arg;
	char *message;
	zval *z_ex;

	va_start(arg, format);
	vspprintf(&message, 0, format, arg);
	va_end(arg);

	z_ex = sdo_throw_exception(ce, message, code, z_cause TSRMLS_CC);

	efree(message);
	return z_ex;
}
/* }}} */

/* {{{ sdo_exception_set_cause
 */
void sdo_exception_set_cause(zval *me, zval *z_cause TSRMLS_DC)
{

	zend_update_property(sdo_exception_class_entry, me,
		"cause", sizeof("cause") - 1, z_cause TSRMLS_CC);
}
/* }}} */

/* {{{ sdo_exception_get_cause
 */
zval *sdo_exception_get_cause(zval *me TSRMLS_DC)
{
    return
		zend_read_property(sdo_exception_class_entry, me,
		"cause", sizeof("cause") - 1, 0 TSRMLS_CC);
}
/* }}} */

/* {{{ SDO_Exception::getCause
 */
PHP_METHOD(SDO_Exception, getCause)
{
	if (ZEND_NUM_ARGS() != 0) {
		WRONG_PARAM_COUNT;
	}

	zval *z_cause = sdo_exception_get_cause(getThis() TSRMLS_CC);
	RETURN_ZVAL(z_cause, 1, 0);
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
