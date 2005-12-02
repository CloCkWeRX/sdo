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

#include <sstream>

#include "php.h"
#include "zend_exceptions.h"

#include "php_sdo_int.h"

#define CLASS_NAME "SDO_Model_ReflectionDataObject"

/* {{{ sdo_model_rdo_object
 * The instance data for this class - extends the standard zend_object
 */
typedef struct {
	zend_object		 zo;			/* The standard zend_object */
	DataObjectPtr	 dop;			/* The sdo4cpp data object */
} sdo_model_rdo_object;
/* }}} */

static zend_object_handlers sdo_model_rdo_object_handlers;

/* {{{ sdo_model_rdo_get_instance
 */
static sdo_model_rdo_object *sdo_model_rdo_get_instance(zval *me TSRMLS_DC) 
{
	return (sdo_model_rdo_object *)zend_object_store_get_object(me TSRMLS_CC);
}
/* }}} */

/* {{{ sdo_model_rdo_object_free_storage
 */
static void sdo_model_rdo_object_free_storage(void *object TSRMLS_DC)
{
	sdo_model_rdo_object *my_object;

	my_object = (sdo_model_rdo_object *)object;
	zend_hash_destroy(my_object->zo.properties);
	FREE_HASHTABLE(my_object->zo.properties);
	my_object->dop = NULL;
	efree(object);
}
/* }}} */

/* {{{ sdo_model_rdo_object_create
 */
static zend_object_value sdo_model_rdo_object_create(zend_class_entry *ce TSRMLS_DC)
{
	zend_object_value retval;
	zval *tmp; /* this must be passed to hash_copy, but doesn't seem to be used */
	sdo_model_rdo_object *my_object;

	my_object = (sdo_model_rdo_object *)emalloc(sizeof(sdo_model_rdo_object));
	memset(my_object, 0, sizeof(sdo_model_rdo_object));
	my_object->zo.ce = ce;
	ALLOC_HASHTABLE(my_object->zo.properties);
	zend_hash_init(my_object->zo.properties, 0, NULL, ZVAL_PTR_DTOR, 0);
	zend_hash_copy(my_object->zo.properties, &ce->default_properties, (copy_ctor_func_t)zval_add_ref,
		(void *)&tmp, sizeof(zval *));
	retval.handle = zend_objects_store_put(my_object, NULL, sdo_model_rdo_object_free_storage, NULL TSRMLS_CC);
	retval.handlers = &sdo_model_rdo_object_handlers;

	return retval;
}
/* }}} */

/* {{{ sdo_model_rdo_cast_object
*/ 
static int sdo_model_rdo_cast_object(zval *readobj, zval *writeobj, int type, int should_free TSRMLS_DC) 
{
	sdo_model_rdo_object *my_object;
	ostringstream print_buf;
	zval free_obj;
	int rc = SUCCESS;
	const char *indent = "\n";
	
	if (should_free) {
		free_obj = *writeobj;
	}
	
	my_object = sdo_model_rdo_get_instance(readobj TSRMLS_CC);
	if (my_object == (sdo_model_rdo_object *)NULL) {
		ZVAL_NULL(writeobj);
		php_error(E_ERROR, "%s:%i: object is not in object store", CLASS_NAME, __LINE__);
		rc = FAILURE;
	} else {		
		try {			
			const Type& type = my_object->dop->getType();
			PropertyList pl = type.getProperties();

			print_buf << indent << "object(" << CLASS_NAME << ")#" <<
				readobj->value.obj.handle << " {";
			print_buf << indent << "  - ";
			if (my_object->dop->getContainer()) {
				print_buf << 
					"Instance of {" << my_object->dop->getContainmentProperty().getName() << "}";
			} else {
				print_buf << "ROOT OBJECT";
			}
			print_buf << indent << "  - Type {";
			sdo_model_type_string (print_buf, &type, indent TSRMLS_CC);
			print_buf << indent << "}";

//			print_buf << "\n  - Type {" << type.getURI() << ":" << type.getName () << "}";
//			print_buf << "\n  - Properties [" << pl.size() << "] {";
//			for (int i = 0; i < pl.size(); i++) {
//				print_buf << "\n        " << pl[i].getType().getURI() << ":" <<
//					pl[i].getType().getName() << " $" << pl[i].getName();
//				if (pl[i].isMany()) print_buf <<"[]";
//			}
//			print_buf << "\n    }";
//			print_buf << "\n}\n";
			string print_string = print_buf.str()/*.substr(0, SDO_TOSTRING_MAX)*/;
			ZVAL_STRINGL(writeobj, (char *)print_string.c_str(), print_string.length(), 1);			
				
		} catch (SDORuntimeException e) {
			ZVAL_NULL(writeobj);
			sdo_throw_runtimeexception(&e TSRMLS_CC);
			rc = FAILURE;
		}
	}	
	
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

/* {{{ sdo_model_rdo_minit
 */
void sdo_model_rdo_minit(zend_class_entry *tmp_ce TSRMLS_DC)
{
	zend_class_entry **reflector_ce_ptr;
	tmp_ce->create_object = sdo_model_rdo_object_create;
	
	if (zend_hash_find(CG(class_table), "reflector", sizeof("reflector"), (void **)&reflector_ce_ptr) == FAILURE) {
		php_error(E_ERROR, "%s:%i: can't find class entry for Reflector", CLASS_NAME, __LINE__);
		return;
	}
	
	sdo_model_reflectiondataobject_class_entry = zend_register_internal_class(tmp_ce TSRMLS_CC);
	//  TODO improve upon this temporary fix for 0.7.0
	//	zend_class_implements(sdo_model_reflectiondataobject_class_entry TSRMLS_CC, 1, *reflector_ce_ptr);

	memcpy(&sdo_model_rdo_object_handlers, zend_get_std_object_handlers(), sizeof(zend_object_handlers));
	sdo_model_rdo_object_handlers.clone_obj = NULL;
	sdo_model_rdo_object_handlers.cast_object = sdo_model_rdo_cast_object;
}
/* }}} */

/* {{{ SDO_Model_ReflectionDataObject::__construct
 */
PHP_METHOD(SDO_Model_ReflectionDataObject, __construct)
{
	int argc;
	zval *z_do;
	sdo_do_object *target;
	sdo_model_rdo_object *my_object;
	
	if ((argc = ZEND_NUM_ARGS()) != 1) {
		WRONG_PARAM_COUNT;
	}
	
	if (zend_parse_parameters(argc TSRMLS_CC, "O", &z_do, sdo_dataobject_class_entry) == FAILURE) {
		return;
	}

	my_object = sdo_model_rdo_get_instance(getThis() TSRMLS_CC);

	target = (sdo_do_object *)zend_object_store_get_object(z_do TSRMLS_CC);
	if (target == (sdo_do_object *)NULL) {
		php_error(E_ERROR, "%s:%i: object is not in object store", CLASS_NAME, __LINE__);
		RETURN_NULL();
	} 

	my_object->dop = target->dop;
}
/* }}} */

/* {{{ SDO_Model_ReflectionDataObject::__toString
 */
PHP_METHOD(SDO_Model_ReflectionDataObject, __toString)
{
	sdo_model_rdo_cast_object(getThis(), return_value, IS_STRING, 0 TSRMLS_CC);
}
/* }}} */

/* {{{ SDO_Model_ReflectionDataObject::export
 */
PHP_METHOD(SDO_Model_ReflectionDataObject, export) 
{	
	zend_class_entry *reflection_ce;
	zend_function	 *reflection_export_zf;

	/* Just call up to Reflection::export */
	reflection_ce = zend_fetch_class ("Reflection", strlen("Reflection"), 
		ZEND_FETCH_CLASS_AUTO TSRMLS_CC);
	if (!reflection_ce) {
		php_error(E_ERROR, "%s:%i: Could not find Reflection class", CLASS_NAME, __LINE__);
		return;
	}

	reflection_export_zf = zend_std_get_static_method(reflection_ce, "export", strlen("export") TSRMLS_CC);
	if (!reflection_export_zf) {
		php_error(E_ERROR, "%s:%i: Could not call Reflection::export method", CLASS_NAME, __LINE__);
		return;
	}

	reflection_export_zf->internal_function.handler(INTERNAL_FUNCTION_PARAM_PASSTHRU);
}
/* }}} */

/* {{{ SDO_Model_ReflectionDataObject::getType
 */
PHP_METHOD(SDO_Model_ReflectionDataObject, getType) 
{
	sdo_model_rdo_object	*my_object;
		
	if (ZEND_NUM_ARGS() != 0) {
		WRONG_PARAM_COUNT;
	}

	my_object = sdo_model_rdo_get_instance(getThis() TSRMLS_CC);
	try {
		sdo_model_type_new (return_value, &my_object->dop->getType() TSRMLS_CC);
	} catch (SDORuntimeException e) {
		sdo_throw_runtimeexception(&e TSRMLS_CC);
	}
}
/* }}} */

/* {{{ SDO_Model_ReflectionDataObject::getInstanceProperties
 */
PHP_METHOD(SDO_Model_ReflectionDataObject, getInstanceProperties) 
{
	sdo_model_rdo_object	*my_object;
	PropertyList			 pl;
	zval					*z_property;
		
	if (ZEND_NUM_ARGS() != 0) {
		WRONG_PARAM_COUNT;
	}

	my_object = sdo_model_rdo_get_instance(getThis() TSRMLS_CC);
	try {
		pl = my_object->dop->getInstanceProperties();
		array_init(return_value);
		for (int i = 0; i < pl.size(); i++) {
			MAKE_STD_ZVAL(z_property);
			sdo_model_property_new(z_property, &pl[i] TSRMLS_CC);
		    add_next_index_zval(return_value, z_property);
		}
	} catch (SDORuntimeException e) {
		sdo_throw_runtimeexception(&e TSRMLS_CC);
	}
}

/* {{{ SDO_Model_ReflectionDataObject::getContainmentProperty
 */
PHP_METHOD(SDO_Model_ReflectionDataObject, getContainmentProperty) 
{
	sdo_model_rdo_object	*my_object;
	
	if (ZEND_NUM_ARGS() != 0) {
		WRONG_PARAM_COUNT;
	}
	
	my_object = sdo_model_rdo_get_instance(getThis() TSRMLS_CC);
	try {
		/* getContainmentProperty() will throw an exception if the DO has no 
		 * container, so check first.
		 */
		if (my_object->dop->getContainer()) {
			sdo_model_property_new (return_value, &my_object->dop->getContainmentProperty() TSRMLS_CC);
		} 
	} catch (SDORuntimeException e) {
		sdo_throw_runtimeexception(&e TSRMLS_CC);
	}
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
