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
static int sdo_sequence_valid(sdo_seq_object *my_object, long sequence_index, int check_empty TSRMLS_DC)
{
	int	return_value = 0;
		
	try {
		Sequence& seq = *my_object->seqp;
		return_value = (sequence_index >= 0 && sequence_index < seq.size());
		
		if (return_value && check_empty) {
			switch(seq.getTypeEnum(sequence_index)) {
			case Type::OtherTypes:
				php_error(E_ERROR, "%s:%i: unexpected DataObject type 'OtherTypes'", CLASS_NAME, __LINE__);
				return_value = 0;
				break;
			case Type::BigDecimalType:
			case Type::BigIntegerType:
			case Type::BooleanType:
			case Type::ByteType:
				return_value = seq.getBooleanValue(sequence_index);
				break;
			case Type::BytesType:
				return_value = (seq.getLength(sequence_index) != 0);
				break;
			case Type::CharacterType:
				return_value = seq.getBooleanValue(sequence_index);
				break;
			case Type::DateType:
				return_value = (seq.getDateValue(sequence_index).getTime() != 0);
				break;
			case Type::DoubleType:
			case Type::FloatType:
			case Type::IntegerType:
			case Type::LongType:
			case Type::ShortType:
				return_value = seq.getBooleanValue(sequence_index);
			case Type::StringType:
			case Type::TextType:
			case Type::UriType:
				/* TODO is this the buffer length or the string length ??? */
				return_value = (seq.getLength(sequence_index) > 0);
				break;	
			case Type::DataObjectType:
				return_value = (!seq.getDataObjectValue(sequence_index));
				break;
			case Type::ChangeSummaryType:
				php_error(E_ERROR, "%s:%i: unexpected DataObject type 'ChangeSummaryType'", CLASS_NAME, __LINE__);
				return_value = 0;
				break;
			default:
				php_error(E_ERROR, "%s:%; unexpected DataObject type %i for sequence index %i",  CLASS_NAME, __LINE__,
					seq.getTypeEnum(sequence_index), sequence_index);
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
static zval *sdo_sequence_read_value(sdo_seq_object *my_object, long sequence_index TSRMLS_DC)
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
		switch(seq.getTypeEnum(sequence_index)) {
		case Type::OtherTypes:
			php_error(E_ERROR, "%s:%i: unexpected DataObject type 'OtherTypes'", CLASS_NAME, __LINE__);
			break;
		case Type::BigDecimalType:
		case Type::BigIntegerType:
			RETVAL_STRING((char *)seq.getCStringValue(sequence_index), 1);
			break;
		case Type::BooleanType:
			RETVAL_BOOL(seq.getBooleanValue(sequence_index));
			break;
		case Type::ByteType:
			RETVAL_LONG(seq.getByteValue(sequence_index));
			break;
		case Type::BytesType:
			bytes_len = seq.getLength(sequence_index);
			bytes_value = (char *)emalloc(1 + bytes_len);
			bytes_len = seq.getBytesValue(sequence_index, bytes_value, bytes_len);
			bytes_value[bytes_len] = '\0';
			RETVAL_STRINGL(bytes_value, bytes_len, 0);
			break;
		case Type::CharacterType:
			wchar_value = seq.getCharacterValue(sequence_index);
			if (wchar_value > INT_MAX) {
			    php_error(E_WARNING, "%s:%i: wide character data lost", CLASS_NAME, __LINE__);
			}
			char_value = seq.getByteValue(sequence_index);
			RETVAL_STRINGL(&char_value, 1, 1);
			break;
		case Type::DateType:
			RETVAL_LONG(seq.getDateValue(sequence_index).getTime());
			break;
		case Type::DoubleType:
			RETVAL_DOUBLE(seq.getDoubleValue(sequence_index));
			break;
		case Type::FloatType:
			RETVAL_DOUBLE(seq.getFloatValue(sequence_index));
			break;
		case Type::IntegerType:
			RETVAL_LONG(seq.getIntegerValue(sequence_index));
			break;
		case Type::LongType:
			/* An SDO long (64 bits) may overflow a PHP int, so we return it as a string */
			RETVAL_STRING((char *)seq.getCStringValue(sequence_index), 1);
			break;
		case Type::ShortType:
			RETVAL_LONG(seq.getShortValue(sequence_index));
			break;
		case Type::StringType:
		case Type::UriType:
		case Type::TextType:
			RETVAL_STRING((char *)seq.getCStringValue(sequence_index), 1);
			break;		
		case Type::DataObjectType:
			doh_value = seq.getDataObjectValue(sequence_index);
			if (!doh_value) {
				php_error(E_WARNING, "%s:%i: read a NULL DataObject for sequence index %i", CLASS_NAME, __LINE__, 
					sequence_index);
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
				seq.getTypeEnum(sequence_index), sequence_index);
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
static void sdo_sequence_write_value(sdo_seq_object *my_object, char *xpath, long sequence_index, Type::Types type_enum, zval *z_value, sdo_write_type write_type TSRMLS_DC)
{	
	zval temp_zval;
	
	try {	
		Sequence& seq = *my_object->seqp;
		
		/* bounds check for OVERWRITE and INSERT */
		if (write_type != TYPE_APPEND && (sequence_index < 0 || sequence_index >= seq.size())) {
			zend_throw_exception_ex(sdo_indexoutofboundsexception_class_entry, 0 TSRMLS_CC, 
				"index %i out of range [0..%i]", sequence_index, seq.size() - 1);
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
				seq.addCString(xpath, Z_STRVAL(temp_zval));
			else if (write_type == TYPE_INSERT)
				seq.addCString(sequence_index, xpath, Z_STRVAL(temp_zval));
			else
				seq.setCStringValue(sequence_index, Z_STRVAL(temp_zval));
			break;
		case Type::BooleanType:
			convert_to_boolean(&temp_zval);
			if (write_type == TYPE_APPEND)
				seq.addBoolean(xpath, ZEND_TRUTH(Z_BVAL(temp_zval)));
			else if (write_type == TYPE_INSERT)
				seq.addBoolean(sequence_index, xpath, ZEND_TRUTH(Z_BVAL(temp_zval)));
			else 
				seq.setBooleanValue(sequence_index, ZEND_TRUTH(Z_BVAL(temp_zval)));
			break;
		case Type::ByteType:
			convert_to_long(&temp_zval);
			if (write_type == TYPE_APPEND)
				seq.addByte(xpath, (char)Z_LVAL(temp_zval));
			else if (write_type == TYPE_INSERT)
				seq.addByte(sequence_index, xpath, (char)Z_LVAL(temp_zval));
			else
				seq.setByteValue(sequence_index, Z_LVAL(temp_zval));
			break;
		case Type::BytesType:
			convert_to_string(&temp_zval);
			if (write_type == TYPE_APPEND)
				seq.addBytes(xpath, (char *)Z_STRVAL(temp_zval), Z_STRLEN(temp_zval));
			else if (write_type == TYPE_INSERT)
				seq.addBytes(sequence_index, xpath, (char *)Z_STRVAL(temp_zval), Z_STRLEN(temp_zval));
			else
				seq.setBytesValue(sequence_index, Z_STRVAL(temp_zval), Z_STRLEN(temp_zval));
			break;
		case Type::CharacterType: 
			convert_to_string(&temp_zval);
			if (write_type == TYPE_APPEND)
				seq.addCharacter(xpath, (char)Z_STRVAL(temp_zval)[0]);
			else if (write_type == TYPE_INSERT)
				seq.addCharacter(sequence_index, xpath, (char)Z_STRVAL(temp_zval)[0]);
			else
				seq.setCharacterValue(sequence_index, (char)(Z_STRVAL(temp_zval)[0]));
			break;
		case Type::DateType:
			convert_to_long(&temp_zval);
			if (write_type == TYPE_APPEND)
				seq.addDate(xpath, (SDODate)Z_LVAL(temp_zval));
			else if (write_type == TYPE_INSERT)
				seq.addDate(sequence_index, xpath, (SDODate)Z_LVAL(temp_zval));
			else 
				seq.setDateValue(sequence_index, (SDODate)Z_LVAL(temp_zval));
			break;
		case Type::DoubleType:
			convert_to_double(&temp_zval);
			if (write_type == TYPE_APPEND)
				seq.addDouble(xpath, (long double)Z_DVAL(temp_zval));
			else  if (write_type == TYPE_INSERT)
				seq.addDouble(sequence_index, xpath, (long double)Z_DVAL(temp_zval));
			else 
				seq.setDoubleValue(sequence_index, (long double)Z_DVAL(temp_zval));
			break;
		case Type::FloatType:
			convert_to_double(&temp_zval);
			if (write_type == TYPE_APPEND)
				seq.addFloat(xpath, (float)Z_DVAL(temp_zval));
			else if (write_type == TYPE_INSERT)
				seq.addFloat(sequence_index, xpath, (float)Z_DVAL(temp_zval));
			else 
				seq.setFloatValue(sequence_index, (float)Z_DVAL(temp_zval));
			break;
		case Type::IntegerType:
			convert_to_long(&temp_zval);
			if (write_type == TYPE_APPEND)
				seq.addInteger(xpath, (int)Z_LVAL(temp_zval));
			else if (write_type == TYPE_INSERT)
				seq.addInteger(sequence_index, xpath, (int)Z_LVAL(temp_zval));
			else 
				seq.setIntegerValue(sequence_index, (int)Z_LVAL(temp_zval));
			break;
		case Type::LongType:
			if (Z_TYPE(temp_zval) == IS_LONG) {
				if (write_type == TYPE_APPEND)
					seq.addLong(xpath, Z_LVAL(temp_zval));
				else if (write_type == TYPE_INSERT)
					seq.addLong(sequence_index, xpath, Z_LVAL(temp_zval));
				else 
					seq.setLongValue(sequence_index, Z_LVAL(temp_zval));
			} else {					
				convert_to_string(&temp_zval);
				if (write_type == TYPE_APPEND)
					seq.addCString(xpath, Z_STRVAL(temp_zval));
				else if (write_type == TYPE_INSERT)
					seq.addCString(sequence_index, xpath, Z_STRVAL(temp_zval));
				else 
					seq.setCStringValue(sequence_index, Z_STRVAL(temp_zval));
			}
			break;
		case Type::ShortType:
			convert_to_long(&temp_zval);
			if (write_type == TYPE_APPEND)
				seq.addShort(xpath, (short)Z_LVAL(temp_zval));
			else if (write_type == TYPE_INSERT)
				seq.addShort(sequence_index, xpath, (short)Z_LVAL(temp_zval));
			else 
				seq.setShortValue(sequence_index, (short)Z_LVAL(temp_zval));
			break;
		case Type::StringType:
		case Type::UriType:
			convert_to_string(&temp_zval);
			if (write_type == TYPE_APPEND)
				seq.addCString(xpath, Z_STRVAL(temp_zval));
			else if (write_type == TYPE_INSERT)
				seq.addCString(sequence_index, xpath, Z_STRVAL(temp_zval));
			else 
				seq.setCStringValue(sequence_index, Z_STRVAL(temp_zval));
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
					seq.addDataObject(xpath, value_object->dop);
				} else  if (write_type == TYPE_INSERT) {
					seq.addDataObject(sequence_index, xpath, value_object->dop);
				} else  {
					seq.setDataObjectValue(sequence_index, value_object->dop);
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
				seq.addText(sequence_index, Z_STRVAL(temp_zval));
			else {
				seq.setText(sequence_index, Z_STRVAL(temp_zval));
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
	char			 *property_name = NULL;
	long			  sequence_index;
	int				  return_value = 0;
	sdo_seq_object *my_object;
		
	convert_to_long_ex(&offset);
	sequence_index = Z_LVAL_P(offset);
	
	my_object = sdo_sequence_get_instance(object TSRMLS_CC);
	if (my_object == (sdo_seq_object *) NULL) {
		php_error(E_ERROR, "%s:%i: object is not in object store", CLASS_NAME, __LINE__);
		return 0;
	}
	
	try {
		return_value = sdo_sequence_valid(my_object, sequence_index, check_empty TSRMLS_CC);
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
	long			 sequence_index;
	zval			*return_value;
	
	convert_to_long_ex(&offset);
	sequence_index = Z_LVAL_P(offset);
	
	my_object = (sdo_seq_object *)zend_object_store_get_object(object TSRMLS_CC);
	
	try {
		return_value = sdo_sequence_read_value(my_object, sequence_index TSRMLS_CC);
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
	long			 sequence_index = -1;
	sdo_seq_object	*my_object;
	sdo_write_type	 write_type;
	Type::Types		 type_enum;
	const char		*xpath = NULL;
		
	if (Z_TYPE_P(z_value) == IS_NULL) {
		/* TODO: fix this when the C++ lib supports a NULL property  */
		zend_throw_exception(sdo_invalidconversionexception_class_entry, 
			"can't assign NULL to SDO_Sequence value", 0 TSRMLS_CC);
		return;
	}

	my_object = sdo_sequence_get_instance(object TSRMLS_CC);
	if (my_object == (sdo_seq_object *) NULL) {
		php_error(E_ERROR, "%s:%i: object is not in object store", CLASS_NAME, __LINE__);
		return;
	}

	try {		
		if (offset == 0 || Z_TYPE_P(offset) == IS_NULL) {
			write_type = TYPE_APPEND;
			type_enum = Type::TextType;
		} else {
			write_type = TYPE_OVERWRITE;		
			convert_to_long_ex(&offset);
			sequence_index = Z_LVAL_P(offset);
			type_enum = my_object->seqp->getTypeEnum(sequence_index);
			if (! my_object->seqp->isText(sequence_index)) {
				xpath = my_object->seqp->getProperty(sequence_index).getName();
			}
		}

		sdo_sequence_write_value(my_object, (char *)xpath, sequence_index, type_enum, z_value, write_type TSRMLS_CC);
	} catch (SDORuntimeException e) {
		sdo_throw_runtimeexception(&e TSRMLS_CC);
	}
	
}
/* }}} */

/* {{{ sdo_sequence_unset_dimension
 */
static void sdo_sequence_unset_dimension(zval *object, zval *offset TSRMLS_DC) 
{
	long				 sequence_index;
	sdo_seq_object	*my_object;
		
	convert_to_long_ex(&offset);
	sequence_index = Z_LVAL_P(offset);
	
	my_object = sdo_sequence_get_instance(object TSRMLS_CC);	
	if (my_object == (sdo_seq_object *) NULL) {
		php_error(E_ERROR, "%s:%i: object is not in object store", CLASS_NAME, __LINE__);
		return;
	}	
	
	try {	
		if (sequence_index < 0 || sequence_index >= my_object->seqp->size()) {
			zend_throw_exception_ex(sdo_indexoutofboundsexception_class_entry, 0 TSRMLS_CC, 
				"index %i out of range [0..%i]", sequence_index, my_object->seqp->size() - 1);
		} else {		
			my_object->seqp->remove(sequence_index);
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

/* {{{ sdo_sequence_cast_object
*/ 
static int sdo_sequence_cast_object(zval *readobj, zval *writeobj, int type, int should_free TSRMLS_DC) 
{
	sdo_seq_object	*my_object;
	ostringstream	 print_buf;
	zval			 free_obj;
	int				 rc = SUCCESS;
	
	if (should_free) {
		free_obj = *writeobj;
	}
	
	my_object = sdo_sequence_get_instance(readobj TSRMLS_CC);
	if (my_object == (sdo_seq_object *)NULL) {
		ZVAL_NULL(writeobj);
		php_error(E_ERROR, "%s:%i: object is not in object store", CLASS_NAME, __LINE__);
		rc = FAILURE;
	} else {		
		Sequence& seq = *my_object->seqp;
		try {			
			print_buf << "object(" << CLASS_NAME << ")#" <<
				readobj->value.obj.handle << " (" << seq.size() << ") {";
			
			for (unsigned int i = 0; i < seq.size(); i++) {
				if (i > 0) print_buf << "; ";
				if (seq.isText(i)) {
					print_buf << '\"' << seq.getCStringValue(i) << '\"';
				} else {
					const Property& property = seq.getProperty(i);
					print_buf << property.getName();
					if (property.isMany()) {
						print_buf << '[' << seq.getListIndex(i) << ']';
					}
					if (property.getType().isDataType()) {
						print_buf << "=>\"" << seq.getCStringValue(i) << '\"';
					} 
				}
			}
			
			print_buf << '}';
			
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
	/*TODO There's a signature change for cast_object in PHP6. */
#if (PHP_MAJOR_VERSION < 6) 
	sdo_sequence_object_handlers.cast_object = sdo_sequence_cast_object;
#endif
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

/* {{{ SDO_SequenceImpl::getProperty
 */
PHP_METHOD(SDO_SequenceImpl, getProperty) 
{
	sdo_seq_object *my_object;
	long			sequence_index;
	const Property *property_p;

	if (zend_parse_parameters(ZEND_NUM_ARGS() TSRMLS_CC, "l", &sequence_index) == FAILURE)
		return;

	my_object = sdo_sequence_get_instance(getThis() TSRMLS_CC);
	try {
		if (my_object->seqp->isText(sequence_index)) {
			RETVAL_NULL();
		} else {
			property_p = &my_object->seqp->getProperty(sequence_index);	
			sdo_model_property_new(return_value, property_p TSRMLS_CC);	
		}
	} catch (SDORuntimeException e) {
		sdo_throw_runtimeexception(&e TSRMLS_CC);
	}
}

/* {{{ SDO_SequenceImpl::getPropertyIndex
 */
PHP_METHOD(SDO_SequenceImpl, getPropertyIndex) 
{
	sdo_seq_object *my_object;
	long sequence_index;

	php_error(E_WARNING, 
		"SDO_Sequence::getPropertyIndex() is deprecated, and will be removed in the next release.");

	if (zend_parse_parameters(ZEND_NUM_ARGS() TSRMLS_CC, "l", &sequence_index) == FAILURE)
		return;

	my_object = sdo_sequence_get_instance(getThis() TSRMLS_CC);
	try {
		if (my_object->seqp->isText(sequence_index)) {
			RETVAL_LONG(-1);
		} else {
			const Property& property = my_object->seqp->getProperty(sequence_index);		
			RETVAL_LONG(property.getContainingType().getPropertyIndex(property.getName()));
		}
	} catch (SDORuntimeException e) {
		sdo_throw_runtimeexception(&e TSRMLS_CC);
	}
}

PHP_METHOD(SDO_SequenceImpl, getPropertyName) 
{
	sdo_seq_object  *my_object;
	long				 sequence_index;
		
	php_error(E_WARNING, 
		"SDO_Sequence::getPropertyName() is deprecated, and will be removed in the next release. Use SDO_Sequence::getProperty()->getName()");

	if (zend_parse_parameters(ZEND_NUM_ARGS() TSRMLS_CC, "l", &sequence_index) == FAILURE)
		return;
	
	my_object = sdo_sequence_get_instance(getThis() TSRMLS_CC);
	try {
		if (my_object->seqp->isText(sequence_index)) {
			RETVAL_NULL();
		} else {
			const Property& property = my_object->seqp->getProperty(sequence_index);		
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
	zval	*z_sequence_index = NULL;
	long	 sequence_index;
	const	 Property *property_p;
	sdo_write_type	 write_type;
	Type::Types type_enum;
	const char *xpath;
	
	if (zend_parse_parameters(ZEND_NUM_ARGS() TSRMLS_CC, "z|zz", 
		&z_value, &z_sequence_index, &z_property) == FAILURE) 
		return;

	if (z_sequence_index && Z_TYPE_P(z_sequence_index) != IS_NULL) {
		write_type = TYPE_INSERT;
		convert_to_long_ex(&z_sequence_index);
		sequence_index = Z_LVAL_P(z_sequence_index);
	} else {
		write_type = TYPE_APPEND;
		sequence_index = -1;
	}
	
	my_object = sdo_sequence_get_instance(getThis() TSRMLS_CC);

	try {
		/* If there's no property_id, this is a Text type */
		if (z_property == NULL || Z_TYPE_P(z_property) == IS_NULL) {
			type_enum = Type::TextType;
			xpath = NULL;
		} else {
			if (sdo_parse_offset_param (my_object->dop, z_property, &property_p, &xpath, 0, 0 TSRMLS_CC) == FAILURE)
				return;
			
			if (property_p == NULL) {
				/* open type, so we'll derive the sdo type from the php type */
				type_enum = sdo_map_zval_type(z_value);
			} else {
				/* known type, so we'll coerce the php type to the sdo type */
				type_enum = property_p->getTypeEnum();
			}
		}
		
		sdo_sequence_write_value(my_object, (char *)xpath, sequence_index, type_enum, z_value, write_type TSRMLS_CC);
		
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
