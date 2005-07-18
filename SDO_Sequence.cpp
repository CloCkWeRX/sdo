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
#ifdef PHP_WIN32
#include <iostream>
#include <math.h>
#include "zend_config.w32.h"
#endif

#include "php.h"
#include "zend_exceptions.h"

#include "php_sdo_int.h"

#define CLASS_NAME "SDO_Sequence"

/* {{{ sdo_seq_object
 * The instance data for this class - extends the standard zend_object
 */
typedef struct {
	zend_object		 zo;			/* The standard zend_object */
	SequencePtr    	 seqp;			/* The C++ Sequence */
	DataObjectPtr	 dop;			/* The C++ DataObject */
} sdo_seq_object;
/* }}} */

/* {{{ sdo_seq_iterator
 * The iterator data for this class - extends the standard zend_object_iterator
 */
typedef struct {
	zend_object_iterator zoi;		/* The standard zend_object_iterator */
	ulong				 index;		/* current index */
	zval				*value;
} sdo_seq_iterator;
/* }}} */

static zend_object_handlers sdo_sequence_object_handlers;
static zend_object_iterator_funcs sdo_sequence_iterator_funcs;

/* {{{ sdo_sequence_get_instance
 */
static sdo_seq_object *sdo_sequence_get_instance(zval *me TSRMLS_DC) 
{
	return (sdo_seq_object *)zend_object_store_get_object(me TSRMLS_CC);
}
/* }}} */

/* {{{ sdo_sequence_valid
 */
static int sdo_sequence_valid(sdo_seq_object *my_object, long sequenceIndex, int check_empty TSRMLS_DC)
{
	int	return_value = 0;
		
	try {
		Sequence& seq = *my_object->seqp;
		return_value = (sequenceIndex >= 0 && sequenceIndex < seq.size());
		
		if (return_value && check_empty) {
			switch(seq.getTypeEnum(sequenceIndex)) {
			case Type::OtherTypes:
				php_error(E_ERROR, "%s:%i: unexpected DataObject type 'OtherTypes'", CLASS_NAME, __LINE__);
				return_value = 0;
				break;
			case Type::BigDecimalType:
			case Type::BigIntegerType:
			case Type::BooleanType:
			case Type::ByteType:
				return_value = seq.getBooleanValue(sequenceIndex);
				break;
			case Type::BytesType:
				/* magic usage returns the actual length */
				return_value = (seq.getBytesValue(sequenceIndex, 0, 0) != 0);
				break;
			case Type::CharacterType:
				return_value = seq.getBooleanValue(sequenceIndex);
				break;
			case Type::DateType:
				return_value = (seq.getDateValue(sequenceIndex) != 0);
				break;
			case Type::DoubleType:
			case Type::FloatType:
			case Type::IntegerType:
			case Type::LongType:
			case Type::ShortType:
				return_value = seq.getBooleanValue(sequenceIndex);
			case Type::StringType:
			case Type::TextType:
			case Type::UriType:
				/* TODO is this the buffer length or the string length ??? */
				return_value = (seq.getLength(sequenceIndex) > 0);
				break;	
			case Type::DataObjectType:
				return_value = (!seq.getDataObjectValue(sequenceIndex));
				break;
			case Type::ChangeSummaryType:
				php_error(E_ERROR, "%s:%i: unexpected DataObject type 'ChangeSummaryType'", CLASS_NAME, __LINE__);
				return_value = 0;
				break;
			default:
				php_error(E_ERROR, "%s:%; unexpected DataObject type %i for sequence index %i",  CLASS_NAME, __LINE__,
					seq.getTypeEnum(sequenceIndex), sequenceIndex);
				return_value = 0;
			}
		}
	} catch (SDORuntimeException e) {
		return_value = 0;
	}
	
	return return_value;
}
/* }}} */

/* {{{ sdo_sequence_read_value
 */
static zval *sdo_sequence_read_value(sdo_seq_object *my_object, long sequenceIndex TSRMLS_DC)
{
	uint		 bytes_len;
	char		*bytes_value;
	char		 char_value; 
	wchar_t		 wchar_value;
	DataObjectPtr doh_value;
	zval		*doh_value_zval;
	zval		*return_value;

	MAKE_STD_ZVAL(return_value);

	try {
		Sequence& seq = *my_object->seqp;
		switch(seq.getTypeEnum(sequenceIndex)) {
		case Type::OtherTypes:
			php_error(E_ERROR, "%s:%i: unexpected DataObject type 'OtherTypes'", CLASS_NAME, __LINE__);
			break;
		case Type::BigDecimalType:
		case Type::BigIntegerType:
			RETVAL_STRING((char *)seq.getCStringValue(sequenceIndex), 1);
			break;
		case Type::BooleanType:
			RETVAL_BOOL(seq.getBooleanValue(sequenceIndex));
			break;
		case Type::ByteType:
			RETVAL_LONG(seq.getByteValue(sequenceIndex));
			break;
		case Type::BytesType:
			/* magic usage returns the actual length */
			bytes_len = seq.getBytesValue(sequenceIndex, 0, 0);
			bytes_value = (char *)emalloc(bytes_len);
			bytes_len = seq.getBytesValue(sequenceIndex, bytes_value, bytes_len);
			RETVAL_STRINGL(bytes_value, bytes_len, 0);
			break;
		case Type::CharacterType:
			wchar_value = seq.getCharacterValue(sequenceIndex);
			if (wchar_value > INT_MAX) {
			    php_error(E_WARNING, "%s:%i: wide character data lost", CLASS_NAME, __LINE__);
			}
			char_value = seq.getByteValue(sequenceIndex);
			RETVAL_STRINGL(&char_value, 1, 1);
			break;
		case Type::DateType:
			RETVAL_LONG(seq.getDateValue(sequenceIndex));
			break;
		case Type::DoubleType:
			RETVAL_DOUBLE(seq.getDoubleValue(sequenceIndex));
			break;
		case Type::FloatType:
			RETVAL_DOUBLE(seq.getFloatValue(sequenceIndex));
			break;
		case Type::IntegerType:
			RETVAL_LONG(seq.getIntegerValue(sequenceIndex));
			break;
		case Type::LongType:
			/* An SDO long (64 bits) may overflow a PHP int, so we return it as a string */
			RETVAL_STRING((char *)seq.getCStringValue(sequenceIndex), 1);
			break;
		case Type::ShortType:
			RETVAL_LONG(seq.getShortValue(sequenceIndex));
			break;
		case Type::StringType:
		case Type::UriType:
		case Type::TextType:
			RETVAL_STRING((char *)seq.getCStringValue(sequenceIndex), 1);
			break;		
		case Type::DataObjectType:
			doh_value = seq.getDataObjectValue(sequenceIndex);
			if (!doh_value) {
				php_error(E_WARNING, "%s:%i: read a NULL DataObject for sequence index %i", CLASS_NAME, __LINE__, 
					sequenceIndex);
				RETVAL_NULL();
			} else {
				doh_value_zval = (zval *)doh_value->getUserData();
				if (doh_value_zval == NULL) {
					php_error(E_ERROR, "%s:%i: object is not in object store", CLASS_NAME, __LINE__);
					RETVAL_NULL();
				} else {
					RETVAL_ZVAL(doh_value_zval, 1, 0);
				}
			}
			break;
		case Type::ChangeSummaryType:
			php_error(E_ERROR, "%s:%i: unexpected DataObject type 'ChangeSummaryType'", CLASS_NAME, __LINE__);
			break;
		default:
			php_error(E_ERROR, "%s:%; unexpected DataObject type %i for sequence index %i",  CLASS_NAME, __LINE__,
				seq.getTypeEnum(sequenceIndex), sequenceIndex);
		}
	} catch (SDORuntimeException e) {
		sdo_throw_runtimeexception(&e TSRMLS_CC);
		RETVAL_NULL();
	}
	
	return return_value;
}
/* }}} */

/* {{{ sdo_sequence_write_value
 */
static void sdo_sequence_write_value(sdo_seq_object *my_object, long sequenceIndex, const Property *property, zval *z_value, sdo_write_type write_type TSRMLS_DC)
{	
	Type::Types type_enum;
	zval temp_zval;
	
	try {	
		Sequence& seq = *my_object->seqp;
		/* for an overwrite we can find the type of the sequence element, otherwise we must
		 * get it from the property.
		 */
		if (write_type == TYPE_OVERWRITE) {			
			type_enum = seq.getTypeEnum(sequenceIndex);
		} else if (property == NULL) {
			type_enum = Type::TextType;
		} else {
			type_enum = property->getTypeEnum();
		}
		
		/* bounds check for OVERWRITE and INSERT */
		if (write_type != TYPE_APPEND && (sequenceIndex < 0 || sequenceIndex >= seq.size())) {
			zend_throw_exception_ex(sdo_indexoutofboundsexception_class_entry, 0 TSRMLS_CC, 
				"index %i out of range [0..%i]", sequenceIndex, seq.size() - 1);
			return;
		}

		/*
		 * Since we may have to coerce the type, we make a local copy of the zval, so that the
		 * original is unaffected. 
		 * 
		 * TODO This could be optimized to only copy if we do actually change the type.
		 */ 
		temp_zval = *z_value;
		zval_copy_ctor(&temp_zval);		

		switch (type_enum) {
		case Type::OtherTypes:
			php_error(E_ERROR, "%s:%i: unexpected DataObject type 'OtherTypes'", CLASS_NAME, __LINE__);
			break;
		case Type::BigDecimalType:
		case Type::BigIntegerType:
			convert_to_string(&temp_zval);
			if (write_type == TYPE_APPEND)
				seq.addCString(*property, Z_STRVAL(temp_zval));
			else if (write_type == TYPE_INSERT)
				seq.addCString(sequenceIndex, Z_STRVAL(temp_zval));
			else
				seq.setCStringValue(sequenceIndex, Z_STRVAL(temp_zval));
			break;
		case Type::BooleanType:
			convert_to_boolean(&temp_zval);
			if (write_type == TYPE_APPEND)
				seq.addBoolean(*property, ZEND_TRUTH(Z_BVAL(temp_zval)));
			else if (write_type == TYPE_INSERT)
				seq.addBoolean(sequenceIndex, *property, ZEND_TRUTH(Z_BVAL(temp_zval)));
			else 
				seq.setBooleanValue(sequenceIndex, ZEND_TRUTH(Z_BVAL(temp_zval)));
			break;
		case Type::ByteType:
			convert_to_long(&temp_zval);
			if (write_type == TYPE_APPEND)
				seq.addByte(*property, (char)Z_LVAL(temp_zval));
			else if (write_type == TYPE_INSERT)
				seq.addByte(sequenceIndex, (char)Z_LVAL(temp_zval));
			else
				seq.setByteValue(sequenceIndex, Z_LVAL(temp_zval));
			break;
		case Type::BytesType:
			convert_to_string(&temp_zval);
			if (write_type == TYPE_APPEND)
				seq.addBytes(*property, (char *)Z_STRVAL(temp_zval), Z_STRLEN(temp_zval));
			else if (write_type == TYPE_INSERT)
				seq.addByte(sequenceIndex, (char *)Z_STRVAL(temp_zval), Z_STRLEN(temp_zval));
			else
				seq.setBytesValue(sequenceIndex, Z_STRVAL(temp_zval), Z_STRLEN(temp_zval));
			break;
		case Type::CharacterType: 
			convert_to_string(&temp_zval);
			if (write_type == TYPE_APPEND)
				seq.addCharacter(*property, (char)Z_STRVAL(temp_zval)[0]);
			else if (write_type == TYPE_INSERT)
				seq.addCharacter(sequenceIndex, (char)Z_STRVAL(temp_zval)[0]);
			else
				seq.setCharacterValue(sequenceIndex, (char)(Z_STRVAL(temp_zval)[0]));
			break;
		case Type::DateType:
			convert_to_long(&temp_zval);
			if (write_type == TYPE_APPEND)
				seq.addDate(*property, (time_t)Z_LVAL(temp_zval));
			else if (write_type == TYPE_INSERT)
				seq.addDate(sequenceIndex, *property, (time_t)Z_LVAL(temp_zval));
			else 
				seq.setDateValue(sequenceIndex, (int)Z_LVAL(temp_zval));
			break;
		case Type::DoubleType:
			convert_to_double(&temp_zval);
			if (write_type == TYPE_APPEND)
				seq.addDouble(*property, (long double)Z_DVAL(temp_zval));
			else  if (write_type == TYPE_INSERT)
				seq.addDouble(sequenceIndex, *property, (long double)Z_DVAL(temp_zval));
			else 
				seq.setDoubleValue(sequenceIndex, (long double)Z_DVAL(temp_zval));
			break;
		case Type::FloatType:
			convert_to_double(&temp_zval);
			if (write_type == TYPE_APPEND)
				seq.addFloat(*property, (float)Z_DVAL(temp_zval));
			else if (write_type == TYPE_INSERT)
				seq.addFloat(sequenceIndex, *property, (float)Z_DVAL(temp_zval));
			else 
				seq.setFloatValue(sequenceIndex, (float)Z_DVAL(temp_zval));
			break;
		case Type::IntegerType:
			convert_to_long(&temp_zval);
			if (write_type == TYPE_APPEND)
				seq.addInteger(*property, (int)Z_LVAL(temp_zval));
			else if (write_type == TYPE_INSERT)
				seq.addInteger(sequenceIndex, *property, (int)Z_LVAL(temp_zval));
			else 
				seq.setIntegerValue(sequenceIndex, (int)Z_LVAL(temp_zval));
			break;
		case Type::LongType:
			if (Z_TYPE(temp_zval) == IS_LONG) {
				if (write_type == TYPE_APPEND)
					seq.addLong(*property, Z_LVAL(temp_zval));
				else if (write_type == TYPE_INSERT)
					seq.addLong(sequenceIndex, *property, Z_LVAL(temp_zval));
				else 
					seq.setLongValue(sequenceIndex, Z_LVAL(temp_zval));
			} else {					
				convert_to_string(&temp_zval);
				if (write_type == TYPE_APPEND)
					seq.addCString(*property, Z_STRVAL(temp_zval));
				else if (write_type == TYPE_INSERT)
					seq.addCString(sequenceIndex, *property, Z_STRVAL(temp_zval));
				else 
					seq.setCStringValue(sequenceIndex, Z_STRVAL(temp_zval));
			}
			break;
		case Type::ShortType:
			convert_to_long(&temp_zval);
			if (write_type == TYPE_APPEND)
				seq.addShort(*property, (short)Z_LVAL(temp_zval));
			else if (write_type == TYPE_INSERT)
				seq.addShort(sequenceIndex, *property, (short)Z_LVAL(temp_zval));
			else 
				seq.setShortValue(sequenceIndex, (short)Z_LVAL(temp_zval));
			break;
		case Type::StringType:
		case Type::UriType:
			convert_to_string(&temp_zval);
			if (write_type == TYPE_APPEND)
				seq.addBytes(*property, Z_STRVAL(temp_zval), 1 + Z_STRLEN(temp_zval));
			else if (write_type == TYPE_INSERT)
				seq.addBytes(sequenceIndex, *property, Z_STRVAL(temp_zval), 1 + Z_STRLEN(temp_zval));
			else 
				seq.setBytesValue(sequenceIndex, Z_STRVAL(temp_zval), 1 + Z_STRLEN(temp_zval));
			break;
		case Type::DataObjectType:
			convert_to_object(&temp_zval);
			if (!instanceof_function(Z_OBJCE(temp_zval), sdo_dataobjectimpl_class_entry TSRMLS_CC)) {
				zend_throw_exception_ex(sdo_unsupportedoperationexception_class_entry, 0 TSRMLS_CC,
					"Class %s is not an instance of %s", 
					Z_OBJCE(temp_zval)->name, sdo_dataobjectimpl_class_entry->name);
			} else {
				sdo_do_object *value_object = (sdo_do_object *)zend_object_store_get_object(&temp_zval TSRMLS_CC);

				if (write_type == TYPE_APPEND) {
					seq.addDataObject(*property, value_object->dop);
				} else  if (write_type == TYPE_INSERT) {
					seq.addDataObject(sequenceIndex, *property, value_object->dop);
				} else  {
					seq.setDataObjectValue(sequenceIndex, value_object->dop);
				};
			}
			break;
		case Type::ChangeSummaryType:
			php_error(E_ERROR, "%s:%i: unexpected DataObject type 'ChangeSummaryType'", CLASS_NAME, __LINE__);
			break;
		case Type::TextType:
			convert_to_string(&temp_zval);
			if (write_type == TYPE_APPEND)
				seq.addText(Z_STRVAL(temp_zval));
			else if (write_type == TYPE_INSERT)
				seq.addText(sequenceIndex, Z_STRVAL(temp_zval));
			else {
				seq.setText(sequenceIndex, Z_STRVAL(temp_zval));
			}
			break;
		default:
			php_error(E_ERROR, "%s:%i: unexpected DataObject type %i", CLASS_NAME, __LINE__, type_enum);
		}
		zval_dtor(&temp_zval);
	} catch (SDORuntimeException e) {
		sdo_throw_runtimeexception(&e TSRMLS_CC);
	}
}
/* }}} */

/* {{{ sdo_sequence_has_dimension
 */
static int sdo_sequence_has_dimension(zval *object, zval *offset, int check_empty TSRMLS_DC)
{
	char			 *propertyName = NULL;
	long			  sequenceIndex;
	int				  return_value = 0;
	sdo_seq_object *my_object;
		
	convert_to_long_ex(&offset);
	sequenceIndex = Z_LVAL_P(offset);
	
	my_object = sdo_sequence_get_instance(object TSRMLS_CC);
	if (my_object == (sdo_seq_object *) NULL) {
		php_error(E_ERROR, "%s:%i: object is not in object store", CLASS_NAME, __LINE__);
		return 0;
	}
	
	try {
		return_value = sdo_sequence_valid(my_object, sequenceIndex, check_empty TSRMLS_CC);
	} catch (SDORuntimeException e) {
		sdo_throw_runtimeexception(&e TSRMLS_CC);
	}
	
	return return_value;
}
/* }}} */

/* {{{ sdo_sequence_read_dimension
 */
static zval *sdo_sequence_read_dimension(zval *object, zval *offset, int type TSRMLS_DC)
{
	sdo_seq_object	*my_object;
	long				 sequenceIndex;
	zval				*return_value;
	
	convert_to_long_ex(&offset);
	sequenceIndex = Z_LVAL_P(offset);
	
	my_object = (sdo_seq_object *)zend_object_store_get_object(object TSRMLS_CC);
	
	try {
		return_value = sdo_sequence_read_value(my_object, sequenceIndex TSRMLS_CC);
	} catch (SDORuntimeException e) {
		sdo_throw_runtimeexception(&e TSRMLS_CC);
	}
	
	return return_value;
}
/* }}} */

/* {{{ sdo_sequence_write_dimension
 */
static void sdo_sequence_write_dimension(zval *object, zval *offset, zval *z_value TSRMLS_DC) 
{		
	long				 sequenceIndex = -1;
	sdo_seq_object	*my_object;
	sdo_write_type		 write_type;
		
	if (Z_TYPE_P(z_value) == IS_NULL) {
		/* TODO: fix this when the C++ lib supports a NULL property  */
		zend_throw_exception(sdo_invalidconversionexception_class_entry, 
			"can't assign NULL to SDO_Sequence value", 0 TSRMLS_CC);
		return;
	}
	
	if (offset == 0 || Z_TYPE_P(offset) == IS_NULL) {
		write_type = TYPE_APPEND;
	} else {
		convert_to_long_ex(&offset);
		sequenceIndex = Z_LVAL_P(offset);
		write_type = TYPE_OVERWRITE;
	}
	
	my_object = sdo_sequence_get_instance(object TSRMLS_CC);
	if (my_object == (sdo_seq_object *) NULL) {
		php_error(E_ERROR, "%s:%i: object is not in object store", CLASS_NAME, __LINE__);
		return;
	}
	
	try {
		sdo_sequence_write_value(my_object, sequenceIndex, NULL, z_value, write_type TSRMLS_CC);
	} catch (SDORuntimeException e) {
		sdo_throw_runtimeexception(&e TSRMLS_CC);
	}
	
}
/* }}} */

/* {{{ sdo_sequence_unset_dimension
 */
static void sdo_sequence_unset_dimension(zval *object, zval *offset TSRMLS_DC) 
{
	long				 sequenceIndex;
	sdo_seq_object	*my_object;
		
	convert_to_long_ex(&offset);
	sequenceIndex = Z_LVAL_P(offset);
	
	my_object = sdo_sequence_get_instance(object TSRMLS_CC);	
	if (my_object == (sdo_seq_object *) NULL) {
		php_error(E_ERROR, "%s:%i: object is not in object store", CLASS_NAME, __LINE__);
		return;
	}	
	
	try {	
		if (sequenceIndex < 0 || sequenceIndex >= my_object->seqp->size()) {
			zend_throw_exception_ex(sdo_indexoutofboundsexception_class_entry, 0 TSRMLS_CC, 
				"index %i out of range [0..%i]", sequenceIndex, my_object->seqp->size() - 1);
		} else {		
			my_object->seqp->remove(sequenceIndex);
		}
		
	} catch (SDORuntimeException e) {
		sdo_throw_runtimeexception(&e TSRMLS_CC);
	}
	
}
/* }}} */

/* {{{ sdo_sequence_get_properties
 * called as a result of print_r() or vardump(), but doesn't get called for reflection
 * Returns an indexed array of the values of all the sequence elements
 */
static HashTable *sdo_sequence_get_properties(zval *object TSRMLS_DC)
{
	sdo_seq_object	*my_object;
	HashTable			*properties;
	int					 entries;
	zval				*tmp;

	my_object = sdo_sequence_get_instance(object TSRMLS_CC);
	if (my_object == (sdo_seq_object *) NULL) {
		php_error(E_ERROR, "%s:%i: object is not in object store", CLASS_NAME, __LINE__);
		return 0;
	}
	ALLOC_HASHTABLE(properties);

	try {
		entries = my_object->seqp->size();


		zend_hash_init(properties, entries, NULL, NULL, 0);

		for (long index = 0; index < entries; index++) {
			tmp = sdo_sequence_read_value(my_object, index TSRMLS_CC);
			zend_hash_next_index_insert(properties, &tmp, sizeof (zval *), NULL);
		}
	} catch (SDORuntimeException e) {
		sdo_throw_runtimeexception(&e TSRMLS_CC);
	}
	return properties;
}
/* }}} */

/* {{{ sdo_sequence_get_iterator
 */
zend_object_iterator *sdo_sequence_get_iterator(zend_class_entry *ce, zval *object TSRMLS_DC) {

	sdo_seq_iterator *iterator = (sdo_seq_iterator *)emalloc(sizeof(sdo_seq_iterator));
	object->refcount++;
	iterator->zoi.data = (void *)object;
	iterator->zoi.funcs = &sdo_sequence_iterator_funcs;
	iterator->index = 0;

	return (zend_object_iterator *)iterator;
}
/* }}} */

/* {{{ sdo_sequence_iterator_dtor
 */
static void sdo_sequence_iterator_dtor(zend_object_iterator *iter TSRMLS_DC)
{
	/* nothing special to be done */
	efree(iter);
}
/* }}} */

/* {{{ sdo_sequence_iterator_valid
 */
static int sdo_sequence_iterator_valid (zend_object_iterator *iter TSRMLS_DC)
{
	int valid;
	
	sdo_seq_iterator *iterator = (sdo_seq_iterator *)iter;
	zval *z_seq_object = (zval *)iterator->zoi.data;
	sdo_seq_object *my_object = (sdo_seq_object *)sdo_sequence_get_instance(z_seq_object TSRMLS_CC);
	
	try {
		valid = (iterator->index >= 0 && iterator->index < my_object->seqp->size()) ? SUCCESS : FAILURE;
	} catch (SDORuntimeException e) {
		sdo_throw_runtimeexception(&e TSRMLS_CC);
	}
	
	return valid;
}
/* }}} */

/* {{{ sdo_sequence_iterator_current_key
 */
static int sdo_sequence_iterator_current_key (zend_object_iterator *iter, 
		char **str_key, uint *str_key_len, ulong *int_key TSRMLS_DC)
{
	sdo_seq_iterator *iterator = (sdo_seq_iterator *)iter;
	*int_key = iterator->index;

	return HASH_KEY_IS_LONG;
}
/* }}} */

/* {{{ sdo_sequence_iterator_current_data
 */
static void sdo_sequence_iterator_current_data (zend_object_iterator *iter, zval ***data TSRMLS_DC)
{	
	sdo_seq_iterator *iterator = (sdo_seq_iterator *)iter;
	zval *z_seq_object = (zval *)iterator->zoi.data;
	sdo_seq_object *my_object = (sdo_seq_object *)sdo_sequence_get_instance(z_seq_object TSRMLS_CC);	
	
	try {
		iterator->value = sdo_sequence_read_value(my_object, iterator->index TSRMLS_CC);
	} catch (SDORuntimeException e) {
		sdo_throw_runtimeexception(&e TSRMLS_CC);
	}
	
	*data = &iterator->value;
}
/* }}} */

/* {{{ sdo_sequence_iterator_move_forward
 */
static void sdo_sequence_iterator_move_forward (zend_object_iterator *iter TSRMLS_DC)
{
	sdo_seq_iterator *iterator = (sdo_seq_iterator *)iter;
	iterator->index++;
}
/* }}} */

/* {{{ sdo_sequence_iterator_rewind
 */
static void sdo_sequence_iterator_rewind (zend_object_iterator *iter TSRMLS_DC)
{
	sdo_seq_iterator *iterator = (sdo_seq_iterator *)iter;
	iterator->index = 0;
}
/* }}} */

/* {{{ sdo_sequence_count_elements
 */
static int sdo_sequence_count_elements (zval *me, long *count TSRMLS_DC)
{
	sdo_seq_object *my_object;

	my_object = sdo_sequence_get_instance(me TSRMLS_CC);
	try {
		*count = my_object->seqp->size();
	} catch (SDORuntimeException e) {
		sdo_throw_runtimeexception(&e TSRMLS_CC);
	}
	
	return SUCCESS;
}
/* }}} */

/* {{{ sdo_sequence_object_free_storage
 */
static void sdo_sequence_object_free_storage(void *object TSRMLS_DC)
{
	sdo_seq_object *my_object;

	my_object = (sdo_seq_object *)object;
	zend_hash_destroy(my_object->zo.properties);
	FREE_HASHTABLE(my_object->zo.properties);
	my_object->seqp = NULL;
	my_object->dop = NULL;
	efree(object);

}
/* }}} */

/* {{{ sdo_sequence_object_create
 */
static zend_object_value sdo_sequence_object_create(zend_class_entry *ce TSRMLS_DC)
{
	zend_object_value retval;
	zval *tmp; /* this must be passed to hash_copy, but doesn't seem to be used */
	sdo_seq_object *my_object;

	my_object = (sdo_seq_object *)emalloc(sizeof(sdo_seq_object));
	memset(my_object, 0, sizeof(sdo_seq_object));
	my_object->zo.ce = ce;
	ALLOC_HASHTABLE(my_object->zo.properties);
	zend_hash_init(my_object->zo.properties, 0, NULL, ZVAL_PTR_DTOR, 0);
	zend_hash_copy(my_object->zo.properties, &ce->default_properties, (copy_ctor_func_t)zval_add_ref,
		(void *)&tmp, sizeof(zval *));
	retval.handle = zend_objects_store_put(my_object, NULL, sdo_sequence_object_free_storage, NULL TSRMLS_CC);
	retval.handlers = &sdo_sequence_object_handlers;

	return retval;
}
/* }}} */

/* {{{ sdo_sequence_new
 */
void sdo_sequence_new(zval *me, SequencePtr seqp, DataObjectPtr dop TSRMLS_DC)
{	
	sdo_seq_object *my_object;

	Z_TYPE_P(me) = IS_OBJECT;	
	if (object_init_ex(me, sdo_sequenceimpl_class_entry) == FAILURE) {
		php_error(E_ERROR, "%s:%i: object_init failed", CLASS_NAME, __LINE__);
		ZVAL_NULL(me);
		return;
	}

	my_object = (sdo_seq_object *)zend_object_store_get_object(me TSRMLS_CC);
	my_object->seqp = seqp;
	my_object->dop = dop;
}
/* }}} */

/* {{{ sdo_sequence_minit
 */
void sdo_sequence_minit(zend_class_entry *tmp_ce TSRMLS_DC)
{
	tmp_ce->create_object = sdo_sequence_object_create;
	
	sdo_sequenceimpl_class_entry = zend_register_internal_class(tmp_ce TSRMLS_CC);
	sdo_sequenceimpl_class_entry->get_iterator = sdo_sequence_get_iterator;
	zend_class_implements(sdo_sequenceimpl_class_entry TSRMLS_CC, 1, sdo_sequence_class_entry);

	memcpy(&sdo_sequence_object_handlers, zend_get_std_object_handlers(), sizeof(zend_object_handlers));
	sdo_sequence_object_handlers.clone_obj = NULL;
	sdo_sequence_object_handlers.read_dimension = sdo_sequence_read_dimension;
	sdo_sequence_object_handlers.write_dimension = sdo_sequence_write_dimension;
	sdo_sequence_object_handlers.has_dimension = sdo_sequence_has_dimension;
	sdo_sequence_object_handlers.unset_dimension = sdo_sequence_unset_dimension;
	sdo_sequence_object_handlers.get_properties = sdo_sequence_get_properties;
	sdo_sequence_object_handlers.count_elements = sdo_sequence_count_elements;

	sdo_sequence_iterator_funcs.dtor = sdo_sequence_iterator_dtor;
	sdo_sequence_iterator_funcs.valid = sdo_sequence_iterator_valid;
	sdo_sequence_iterator_funcs.get_current_data = sdo_sequence_iterator_current_data;
	sdo_sequence_iterator_funcs.get_current_key = sdo_sequence_iterator_current_key;
	sdo_sequence_iterator_funcs.move_forward = sdo_sequence_iterator_move_forward;
	sdo_sequence_iterator_funcs.rewind = sdo_sequence_iterator_rewind;
	sdo_sequence_iterator_funcs.invalidate_current = 0;
}
/* }}} */

/* {{{ SDO_SequenceImpl::getPropertyIndex
 */
PHP_METHOD(SDO_SequenceImpl, getPropertyIndex) 
{
	sdo_seq_object *my_object;
	long sequenceIndex;

	if (zend_parse_parameters(ZEND_NUM_ARGS() TSRMLS_CC, "l", &sequenceIndex) == FAILURE)
		return;

	my_object = sdo_sequence_get_instance(getThis() TSRMLS_CC);
	try {
		if (my_object->seqp->isText(sequenceIndex)) {
			RETVAL_LONG(-1);
		} else {
			const Property& property = my_object->seqp->getProperty(sequenceIndex);		
			RETVAL_LONG(property.getContainingType().getPropertyIndex(property.getName()));
		}
	} catch (SDORuntimeException e) {
		sdo_throw_runtimeexception(&e TSRMLS_CC);
	}
}

PHP_METHOD(SDO_SequenceImpl, getPropertyName) 
{
	sdo_seq_object  *my_object;
	long				 sequenceIndex;
		
	if (zend_parse_parameters(ZEND_NUM_ARGS() TSRMLS_CC, "l", &sequenceIndex) == FAILURE)
		return;
	
	my_object = sdo_sequence_get_instance(getThis() TSRMLS_CC);
	try {
		if (my_object->seqp->isText(sequenceIndex)) {
			RETVAL_NULL();
		} else {
			const Property& property = my_object->seqp->getProperty(sequenceIndex);		
			RETVAL_STRING((char *)property.getName(), 1);		
		}
	} catch (SDORuntimeException e) {
		sdo_throw_runtimeexception(&e TSRMLS_CC);
	}
}

PHP_METHOD(SDO_SequenceImpl, move) 
{
	sdo_seq_object *my_object;
	long toIndex, fromIndex;

	if (zend_parse_parameters(ZEND_NUM_ARGS() TSRMLS_CC, "ll", &toIndex, &fromIndex) == FAILURE)
		return;

	my_object = sdo_sequence_get_instance(getThis() TSRMLS_CC);
	try {
		my_object->seqp->move(toIndex, fromIndex);
	} catch (SDORuntimeException e) {
		sdo_throw_runtimeexception(&e TSRMLS_CC);
	}
}

PHP_METHOD(SDO_SequenceImpl, insert) 
{
	sdo_seq_object *my_object;
	zval	*z_value;
	zval	*z_property = NULL;
	char	*propertyName = NULL;
	int		 propertyName_len;
	long	 propertyIndex;
	zval	*z_sequenceIndex = NULL;
	long	 sequenceIndex;
	const	 Property *property = NULL;
	sdo_write_type	 write_type;
	zend_bool is_text = false;
	
	if (zend_parse_parameters(ZEND_NUM_ARGS() TSRMLS_CC, "z|zz", 
		&z_value, &z_sequenceIndex, &z_property) == FAILURE) 
		return;
	
	/* If there's no propertyID, this is a Text type */
	if (z_property == NULL || Z_TYPE_P(z_property) == IS_NULL) {
		is_text = true;
	} else {
		switch(Z_TYPE_P(z_property)) {
		case IS_STRING:	
			propertyName = Z_STRVAL_P(z_property);
			propertyName_len = Z_STRLEN_P(z_property);
			break;
		case IS_LONG:
		case IS_BOOL:
		case IS_RESOURCE:
			propertyIndex = Z_LVAL_P(z_property);
			break;
		case IS_DOUBLE:
			propertyIndex =(long)Z_DVAL_P(z_property);
			break;
		default:
			php_error(E_ERROR, "%s:%i: invalid dimension type %i", CLASS_NAME, __LINE__, Z_TYPE_P(z_property));
			RETURN_NULL();
		}
	}

	if (z_sequenceIndex && Z_TYPE_P(z_sequenceIndex) != IS_NULL) {
		write_type = TYPE_INSERT;
		convert_to_long_ex(&z_sequenceIndex);
		sequenceIndex = Z_LVAL_P(z_sequenceIndex);
	} else {
		write_type = TYPE_APPEND;
		sequenceIndex = -1;
	}
	
	my_object = sdo_sequence_get_instance(getThis() TSRMLS_CC);
	try {
		if (!is_text) {
			/* get the Type we will assign into */
			if (propertyName) {
				property = &my_object->dop->getType().getProperty(propertyName);
			} else {
				property = &my_object->dop->getType().getProperty(propertyIndex);
			}
			
		}

		sdo_sequence_write_value(my_object, sequenceIndex, property, z_value, write_type TSRMLS_CC);
		
	} catch (SDORuntimeException e) {
		sdo_throw_runtimeexception(&e TSRMLS_CC);
	}
}

PHP_METHOD(SDO_SequenceImpl, count) 
{
	long count = 0;
	
	sdo_sequence_count_elements(getThis(), &count TSRMLS_CC);
	RETVAL_LONG(count);
}

/*
 * Local variables:
 * tab-width: 4
 * c-basic-offset: 4
 * End:
 * vim600: noet sw=4 ts=4 fdm=marker
 * vim<600: noet sw=4 ts=4
 */