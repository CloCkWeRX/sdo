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

/*
 * Utility functions for internal use by the SDO extension
 */
#ifdef PHP_WIN32
#include <iostream>
#include <math.h>
#include "zend_config.w32.h"
#endif

#include "php.h"

#include "php_sdo_int.h"

/* {{{ sdo_make_long_class_constant
 * creates a class constant
 */
void sdo_make_long_class_constant(zend_class_entry *ce, char *name, long value)
{
	/* Cannot emalloc the storage for this constant, it must endure beyond the current request */
	zval *z_constant = (zval *)malloc(sizeof(zval));
	INIT_PZVAL(z_constant);
	ZVAL_LONG(z_constant, value);
	zend_hash_update(&ce->constants_table, name, 1 + strlen(name), &z_constant, sizeof(zval *), NULL);
}
/* }}} */

/* {{{ sdo_parse_offset_param
 * internal function to get an sdo property offset from a zval parameter. 
 * The value may have been passed as a SDO_Model_Property, an xpath or a property index.
 * Calling functions should catch SDORuntimeException.
 */
int sdo_parse_offset_param (DataObjectPtr dop, zval *z_offset, 
	const Property **return_property, const char **return_xpath,
	int property_required, 
	int quiet TSRMLS_DC) {
	
	long			 prop_index;
	const Property  *property_p;
	const char		*xpath;
	char			*class_name;
	char		    *space;
	
	switch(Z_TYPE_P(z_offset)) {
	case IS_NULL:
		if (!quiet) {
			class_name = get_active_class_name(&space TSRMLS_CC);
			sdo_throw_exception_ex (sdo_unsupportedoperationexception_class_entry, 0, 0 TSRMLS_CC,
				"%s%s%s(): parameter is NULL", 
				class_name, space, get_active_function_name(TSRMLS_C));
		}
		return FAILURE;
	case IS_STRING:	
		xpath = Z_STRVAL_P(z_offset);
		
		/* If the type is open, then it's OK for the xpath offset to
		 * specify an unknown property. But even an open type may have
		 * defined properties, so we still need to try for one.
		 */
		if (property_required || dop->hasProperty(xpath)) {
			/* exception will be thrown if xpath is invalid */
			property_p = &dop->getProperty(xpath);
		} else {
			property_p = NULL;
		}
		break;
	case IS_LONG:
	case IS_BOOL:
	case IS_RESOURCE:
	case IS_DOUBLE:
		if (Z_TYPE_P(z_offset) == IS_DOUBLE) {
			if (!quiet) {
				class_name = get_active_class_name(&space TSRMLS_CC);
				php_error(E_WARNING, "%s%s%s(): double parameter %f rounded to %i", 
					class_name, space, get_active_function_name(TSRMLS_C), 
					Z_DVAL_P(z_offset), (long)Z_DVAL_P(z_offset));
			}
			prop_index =(long)Z_DVAL_P(z_offset);
		} else {
			prop_index = Z_LVAL_P(z_offset);
		}
		/* Note an open type may not be specified using a property index,
		 * so no need to repeat the check that was done for IS_STRING above.
         */
		property_p = &dop->getProperty(prop_index);
		xpath = property_p->getName();
		break;
	case IS_OBJECT:
		if (!instanceof_function(Z_OBJCE_P(z_offset), sdo_model_property_class_entry TSRMLS_CC)) {
			if (!quiet) {
				class_name = get_active_class_name(&space TSRMLS_CC);		
				sdo_throw_exception_ex (sdo_unsupportedoperationexception_class_entry, 0, 0 TSRMLS_CC,
					"%s%s%s(): expects object parameter to be SDO_Model_Property, %s given",
					class_name, space, get_active_function_name(TSRMLS_C), 
					Z_OBJCE_P(z_offset)->name);
			}
			return FAILURE;
		}
		property_p = sdo_model_property_get_property(z_offset TSRMLS_CC);
		xpath = property_p->getName();
		break;
	default:
		if (!quiet) {
			class_name = get_active_class_name(&space TSRMLS_CC);
			php_error(E_ERROR, "%s%s%s(): internal error - invalid dimension type %i", 
				class_name, space, get_active_function_name(TSRMLS_C), 
				Z_TYPE_P(z_offset));
		}
		return FAILURE;
	}

	if (return_xpath) {
		*return_xpath = xpath;
	}

	if (return_property) {
		*return_property = property_p;
	}

	return SUCCESS;
}
/* }}} */

/* {{{ sdo_map_zval_type
 * internal function to get an sdo property type from a zval parameter. 
 * This is needed when the zval is to be assigned into an open type, that is,
 * when the target SDO property type is not predetermined by the model.
 */
Type::Types sdo_map_zval_type (zval *z_value) {
	Type::Types type_enum;
	switch(Z_TYPE_P(z_value)) {
	case IS_DOUBLE:
		type_enum = Type::DoubleType;
		break;
	case IS_BOOL:
		type_enum = Type::BooleanType;
		break;
	case IS_OBJECT:
		type_enum = Type::DataObjectType;
		break;
	case IS_STRING:
		type_enum = Type::StringType;
		break;
	default:
		type_enum = Type::IntegerType;
		break;
	}
	return type_enum;
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
