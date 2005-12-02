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
/* $Id$ */
#ifndef PHP_SDO_H
#define PHP_SDO_H

#include "php.h"

extern zend_module_entry sdo_module_entry;
#define phpext_sdo_ptr &sdo_module_entry

#ifdef PHP_WIN32
#    if defined (SDO_EXPORTS)
#        define PHP_SDO_API __declspec(dllexport)
#    elif defined(COMPILE_DL_SDO)
#        define PHP_SDO_API __declspec(dllimport)
#    else
#        define PHP_SDO_API
#    endif
#else
#define PHP_SDO_API
#endif

#ifdef ZTS
#include "TSRM.h"
#endif

PHP_MINIT_FUNCTION(sdo);
PHP_MINFO_FUNCTION(sdo);

PHP_METHOD(SDO_PropertyAccess, __get);
PHP_METHOD(SDO_PropertyAccess, __set);

/* {{{ proto array SDO_DataObject::getType()
Return array containing the type information for the SDO_DataObject.
The first element contains the namespace URI string and the second
contains the type name string.
For example, if the SDO_DataObject were of type 'CompanyType' from
the namespace 'CompanyNS', then getType() would return the equivalent to
array('CompanyNS', 'CompanyType');

Returns an array containing the namespace URI and type name for the data object.
 */
PHP_METHOD(SDO_DataObject, getType);
/* }}} */

/* {{{ proto SDO_Sequence SDO_DataObject::getSequence()
Return the SDO_Sequence for this SDO_DataObject.  Accessing the SDO_DataObject
through the SDO_Sequence interface acts on the same SDO_DataObject instance
data, but preserves ordering across properties.

Returns the sequence for this SDO_DataObject.  Returns Null if the
SDO_DataObject is not of a type which can have a sequence.
*/
PHP_METHOD(SDO_DataObject, getSequence);
/* }}} */

/* {{{ proto SDO_DataObject SDO_DataObject::createDataObject(mixed identifier)
Create a child SDO_DataObject of the default type for the property identified.

Identifies the property for the data object type to be created.  Can be either a property name (string),
or property index (int)

Returns the newly created SDO_DataObject
*/
PHP_METHOD(SDO_DataObject, createDataObject);
/* }}} */

/* {{{ proto void SDO_DataObject::clear()
Clear an SDO_DataObject's properties.  Sets their values back to any defaults.  Read-only properties are unaffected.
Subsequent calls to isset() for the data object will return false.
*/
PHP_METHOD(SDO_DataObject, clear);
/* }}} */

/* {{{ proto SDO_DataObject SDO_DataObject::getContainer()
Get the containing data object for this data object.

Returns the SDO_DataObject which contains this SDO_DataObject, or return NULL
if this is a root SDO_DataObject.
*/
PHP_METHOD(SDO_DataObject, getContainer);
/* }}} */

/* {{{ proto string SDO_DataObject::getContainmentPropertyName()
Get the property name used to refer to this data object by its containing data object.

Returns the name of the container's property which references this SDO_DataObject
*/
PHP_METHOD(SDO_DataObject, getContainmentPropertyName);
/* }}} */


/* {{{ proto integer SDO_Sequence::getPropertyIndex(integer sequenceIndex)
Return the property index for the specified sequence index.

The sequence index

The corresponding property index.  A value of -1 means the element does not belong to a property and must therefore be unstructured text.

Should throw SDO_IndexOutOfBoundsException, but doesn't at the moment.
*/
PHP_METHOD(SDO_Sequence, getPropertyIndex);
/* }}} */

/* {{{ proto string SDO_Sequence::getPropertyName(integer sequenceIndex)
Return the property name for the specified sequence index.

The sequence index

The corresponding property name.  A value of NULL means the element does not belong to a property and must therefore be unstructured text.

Should throw SDO_IndexOutOfBoundsException, but doesn't at the moment.
*/
PHP_METHOD(SDO_Sequence, getPropertyName);
/* }}} */

/* {{{ proto void SDO_Sequence::move(integer toIndex, integer fromIndex)
Modify the position of the item in the sequence, without altering the value of the property in the SDO_DataObject.

No return value.

The destination sequence index
The source sequence index

Should throw SDO_IndexOutOfBoundsException, but doesn't at the moment.
*/
PHP_METHOD(SDO_Sequence, move);
/* }}} */

/* {{{ proto void SDO_Sequnece::insert(mixed value [, integer sequenceIndex, mixed propertyIdentifier])
Insert a new element at a specified position in the sequence.  All subsequent sequence items are moved up.

The new value to be inserted.  This can be either a primitive or an SDO_DataObject

The position at which to insert the new element.  Default is NULL, which results in the new value being appended to the sequence.

Either a property index or property name, used to identify a property in the sequence's corresponding SDO_DataObject.  A value of NULL signifies unstructured text.

Throws SDO_IndexOutOfBoundsException  if the sequence index is less than zero or greater than the size of the sequence
Throws SDO_InvalidConversionException if the type of the new value cannot be juggled to match the type for the specified property
*/
PHP_METHOD(SDO_Sequence, insert);
/* }}} */

PHP_METHOD(SDO_List, __construct);

/* {{{ proto void SDO_List::insert(mixed value [, integer index])
Insert a new element at a specified position in the list. All subsequent list items are moved up.

Value is the new value to be inserted.  This can be either a primitive or an SDO_DataObject.

Index is the position at which to insert the new element.  If this argument is not specified then the new value will be appended.

Throws SDO_IndexOutOfBoundsException if the list index is less than zero or greater than the size of the list.

Throws SDO_InvalidConversionException if the type of the new value does not match the type for the list (i.e. the type of the many-valued
property that the list represents)
*/
PHP_METHOD(SDO_List, insert);
/* }}} */

PHP_METHOD(SDO_List, count);

/* {{{ proto void SDO_DataFactory::create(string namespaceURI, string typeName)
Create an SDO_DataObject of the type specified by typeName with the given namespace URI.

For example, the following creates a new SDO_DataObject of type
'CompanyType' where that type belongs to the namespace 'CompanyNS'
    $df->create('CompanyNS', 'CompanyType');

The namespace of the type

The name of the type

Returns the newly created SDO_DataObject

Throws SDO_TypeNotFoundException if the namespaceURI and typeName do not correspond to a type known to this factory
   */
PHP_METHOD(SDO_DataFactory, create);
/* }}} */

/* {{{ proto SDO_DAS_DataFactory SDO_DAS_DataFactory::getDataFactory()
Get an instance of an SDO_DAS_DataFactory.

Get an instance of an SDO_DAS_DataFactory.  This instance is initially only
configured with the basic SDO types.  A Data Access Service is responsible for populating
the data factory model and then allowing PHP applications to create SDOs based on the model,
through the SDO_DataFactory interface.

Return: SDO_DataFactory (an SDO_DataFactory)
   */
PHP_METHOD(SDO_DAS_DataFactory, getDataFactory);
/* }}} */

/* {{{ proto void SDO_DAS_DataFactory::addType(string namespaceURI, string typeName)
Add a new type to the SDO_DAS_DataFactory, defined by its namespace and type
name.

The namespace of the type

The name of the type

For example, the following adds a new SDO_DataObject type of 'CompanyType'
where that type belongs to the namespace 'CompanyNS'
$df->addType('CompanyNS', 'CompanyType');
 */
PHP_METHOD(SDO_DAS_DataFactory, addType);
/* }}} */

/* {{{ proto void SDO_DAS_DataFactory::addPropertyToType(string parentNamespaceURI, string parentTypeName, string propertyName, string propertyNamespaceURI, string propertyTypeName [, array options])
Add a property to a type.  The type must already be known to the
SDO_DAS_DataFactory (i.e. have been added using addType()).  The property
becomes a property of the type.  This is how the graph model for the
structure of an SDO_DataObject is built.

The namespaceURI for the parent type

The typeName for the parent type

The name of by which the property will be known

The namespaceURI for the type of the property

The typeName for the type of the property

Optional associative array of optional arguments for the new property.
Valid keys are:
"many" for a many-valued property (default false)
"readonly" for a read-only property(default false)
"containment" to say if the property is contained by the parent (default is true. 
This value only relates to properties which are data object types)
"default" to specifiy a default value for the property. "Default" is a mixed type argument, and omitting a default means the 
property does not have one.
"opposite" to specify the property at the other end of a bi-directional relationship. Opposite  is an array of three values 
required to identify the opposite property. The first two are namespaceURI and type name and identify the type which contains
the opposite property, the thirs value is the property name of the opposite property.

Example: to add a 'name' property to a Person type:
$df->addPropertyToType('PersonNS', 'PersonType',
                       'name', 'commonj.sdo', 'String');
 */
PHP_METHOD(SDO_DAS_DataFactory, addPropertyToType);
/* }}} */


/* {{{ proto SDO_DAS_ChangeSummary SDO_DAS_DataObject::getChangeSummary()
Get the SDO_DAS_ChangeSummary for this SDO_DataObject.

Returns the change summary for this SDO_DataObject, or NULL if it does not have one.
 */
PHP_METHOD(SDO_DAS_DataObject, getChangeSummary);
/* }}} */



/* {{{ proto void SDO_DAS_ChangeSummary::beginLogging()
Begin logging changes made to the SDO_DataObject.
*/
PHP_METHOD(SDO_DAS_ChangeSummary, beginLogging);
/* }}} */

/* {{{ proto void SDO_DAS_ChangeSummary::endLogging()
End logging changes made to the SDO_DataObject.
*/
PHP_METHOD(SDO_DAS_ChangeSummary, endLogging);
/* }}} */

/* {{{ proto boolean SDO_DAS_ChangeSummary::isLogging()
Test to see whether logging is switched on.

Returns true if change logging is on, otherwise returns false.
*/
PHP_METHOD(SDO_DAS_ChangeSummary, isLogging);
/* }}} */

/* {{{ proto SDO_List SDO_DAS_ChangeSummary::getChangedDataObjects()
Get an SDO_List of the SDO_DataObjects which have been changed.

Returns an SDO_List of SDO_DataObjects
*/
PHP_METHOD(SDO_DAS_ChangeSummary, getChangedDataObjects);
/* }}} */

/* {{{ proto integer SDO_DAS_ChangeSummary::getChangeType(SDO_DataObject dataObject)
Get the type of change which has been made to the supplied SDO_DataObject.

The SDO_DataObject which has been changed

An enumeration indicating the type of change.  Valid values are;
SDO_DAS_ChangeSummary::NONE, SDO_DAS_ChangeSummary::MODIFICATION,
SDO_DAS_ChangeSummary::ADDITION, SDO_DAS_ChangeSummary::DELETION.
*/
PHP_METHOD(SDO_DAS_ChangeSummary, getChangeType);
/* }}} */

/* {{{ proto SDO_List SDO_DAS_ChangeSummary::getOldValues(SDO_DataObject dataObject)
Get a list of the old values for a given changed SDO_DataObject.

The data object which has been changed.

Returns a list of SDO_DAS_Settings describing the old values for the changed properties in the SDO_DataObject
*/
PHP_METHOD(SDO_DAS_ChangeSummary, getOldValues);
/* }}} */

/* {{{ proto SDO_DataObject SDO_DAS_ChangeSummary::getOldContainer(SDO_DataObject dataObject)
Get the old container (SDO_DataObject) for a deleted SDO_DataObject.

The SDO_DataObject which has been deleted.

The old containing data object of the deleted SDO_DataObject.
*/
PHP_METHOD(SDO_DAS_ChangeSummary, getOldContainer);
/* }}} */


/* {{{ proto int SDO_DAS_Setting::getPropertyIndex()
Get the property index for the changed property.

Returns the property index for the changed property.
*/
PHP_METHOD(SDO_DAS_Setting, getPropertyIndex);
/* }}} */

/* {{{ proto string SDO_DAS_Setting::getPropertyName()
Get the property name for the changed property.

Returns the property name for the changed property.
*/
PHP_METHOD(SDO_DAS_Setting, getPropertyName);
/* }}} */

/* {{{ proto mixed SDO_DAS_Setting::getValue()
Get the old value for the changed property.

Returns the old value of the changed property.
*/
PHP_METHOD(SDO_DAS_Setting, getValue);
/* }}} */

/* {{{ proto integer SDO_DAS_Setting::getListIndex()
Get the list index for the setting if this came from a many-valued property.

Returns the list index if the change was made to an individual element in a many-valued property.
*/
PHP_METHOD(SDO_DAS_Setting, getListIndex);
/* }}} */

/* {{{ proto boolean SDO_DAS_Setting::isSet()
Query whether a property was set prior to being modified.

Returns true if the property was set prior to being modified, else returns false.
*/
PHP_METHOD(SDO_DAS_Setting, isSet);
/* }}} */

/* {{{ proto string SDO_Model_Type::getName()
Return the name of the type.
*/
PHP_METHOD(SDO_Model_Type, getName);
/* }}} */

/* {{{ proto string SDO_Model_Type::getNamespaceURI()
Return the namespace URI of the type.
*/
PHP_METHOD(SDO_Model_Type, getNamespaceURI);
/* }}} */

/* {{{ proto boolean SDO_Model_Type::isInstance(SDO_DataObject dataObject)
Returns true if the object is an instance of the type.

The SDO_DataObject to test.
*/
PHP_METHOD(SDO_Model_Type, isInstance);
/* }}} */

/* {{{ proto array SDO_Model_Type::getProperties()
Get the array of properties defined for the type.

An array of SDO_Model_Property objects.
*/
PHP_METHOD(SDO_Model_Type, getProperty);
/* }}} */

/* {{{ proto SDO_Model_Property SDO_Model_Type::getProperty(mixed identifier)
Get a property of this type, identified by its property index or property name.

The property index or property name.
*/
PHP_METHOD(SDO_Model_Type, getProperty);
/* }}} */

/* {{{ proto boolean SDO_Model_Type::isDataType()
Returns true if this type corresponds to a primitive data type.
*/
PHP_METHOD(SDO_Model_Type, isDataType);
/* }}} */

/* {{{ proto boolean SDO_Model_Type::isSequencedType()
Returns true if this type corresponds to a sequenced SDO_DataObject.
*/
PHP_METHOD(SDO_Model_Type, isSequencedType);
/* }}} */

/* {{{ proto boolean SDO_Model_Type::isOpenType()
Returns true if this type corresponds to a an SDO_DataObject which allows 
open content, that is, aditional properties to be added to an instance.
*/
PHP_METHOD(SDO_Model_Type, isOpenType);
/* }}} */

/* {{{ proto SDO_Model_Type SDO_Model_Type::getBaseType()
Get the base (parent) type for this type. Returns NULL if this type does
not inherit from any other type.
*/
PHP_METHOD(SDO_Model_Type, getBaseType);
/* }}} */

/* {{{ proto string SDO_Model_Property::getName()
Returns the name of the property.
*/
PHP_METHOD(SDO_Model_Property, getName);
/* }}} */

/* {{{ proto SDO_Model_Type SDO_Model_Property::getType()
Returns the type of the property.
*/
PHP_METHOD(SDO_Model_Property, getType);
/* }}} */

/* {{{ proto boolean SDO_Model_Property::isMany()
Returns true if this is a many-valued property.
*/
PHP_METHOD(SDO_Model_Property, isMany);
/* }}} */

/* {{{ proto boolean SDO_Model_Property::isReadOnly()
Returns true if this is a read-only property.
*/
PHP_METHOD(SDO_Model_Property, isReadOnly);
/* }}} */

/* {{{ proto boolean SDO_Model_Property::isContainment()
Returns true if this property is contained by its parent.
*/
PHP_METHOD(SDO_Model_Property, isContainment);
/* }}} */

/* {{{ proto SDO_Model_Property SDO_Model_Property::getOpposite()
Returns the opposite property if this property is part of a bi-directional
relationship, otherwise returns NULL.
*/
PHP_METHOD(SDO_Model_Property, getOpposite);
/* }}} */

/* {{{ proto boolean SDO_Model_Property::getContainingType()
Returns the type which contains this property.
*/
PHP_METHOD(SDO_Model_Property, getContainingType);
/* }}} */

/* {{{ proto mixed SDO_Model_Property::getDefault()
Returns the default value for this property.
*/
PHP_METHOD(SDO_Model_Property, getDefault);
/* }}} */

/* {{{ proto SDO_Model_ReflectionDataObject SDO_Model_ReflectionDataObject::__construct(SDO_DataObject data_object)
Construct an SDO_Model_ReflectionDataObject to reflect on an SDO_DataObject

The SDO_DataObject to reflect on for model information.
*/
PHP_METHOD(SDO_Model_ReflectionDataObject, __construct);
/* }}} */

/* {{{ proto string SDO_Model_ReflectionDataObject::__toString()
Return a string representation of the object.
*/
PHP_METHOD(SDO_Model_ReflectionDataObject, __toString);
/* }}} */

/* {{{ proto static mixed SDO_Model_ReflectionDataObject::export(SDO_Model_ReflectionDataObject rdo [, bool return])
Exports an SDO_Model_ReflectionDataObject. 
Returns the output if TRUE is specified for return, printing it otherwise.
*/
PHP_METHOD(SDO_Model_ReflectionDataObject,export);
/* }}} */

/* {{{ proto SDO_Model_Type SDO_Model_ReflectionDataObject::getType()
Get the SDO_Model_Type for the SDO_DataObject.
*/
PHP_METHOD(SDO_Model_ReflectionDataObject, getType);
/* }}} */

/* {{{ proto array SDO_Model_ReflectionDataObject::getInstanceProperties()
Get the instance properties for the SDO_DataObject. The instance 
properties consist of all the properties defined on the data object's 
type, plus any instance properties from open content (if the data object
is an open type).

An array of SDO_Model_Property objects.
*/
PHP_METHOD(SDO_Model_ReflectionDataObject, getInstanceProperties);
/* }}} */

/* {{{ proto SDO_Model_Property SDO_Model_ReflectionDataObject::getContainmentProperty()
Return the container's SDO_Model_Property which references the SDO_DataObject,
or NULL if it is a root SDO_DataObject.
*/
PHP_METHOD(SDO_Model_ReflectionDataObject, getContainmentProperty);
/* }}} */

/* {{{ Implementation methods */
PHP_METHOD(SDO_DAS_DataFactoryImpl, create);
PHP_METHOD(SDO_DAS_DataFactoryImpl, addType);
PHP_METHOD(SDO_DAS_DataFactoryImpl, addPropertyToType);

PHP_METHOD(SDO_DataObjectImpl, __construct);
PHP_METHOD(SDO_DataObjectImpl, getType);
PHP_METHOD(SDO_DataObjectImpl, getSequence);
PHP_METHOD(SDO_DataObjectImpl, createDataObject);
PHP_METHOD(SDO_DataObjectImpl, clear);
PHP_METHOD(SDO_DataObjectImpl, getContainer);
PHP_METHOD(SDO_DataObjectImpl, getContainmentPropertyName);
PHP_METHOD(SDO_DataObjectImpl, getChangeSummary);
PHP_METHOD(SDO_DataObjectImpl, __get);
PHP_METHOD(SDO_DataObjectImpl, __set);
PHP_METHOD(SDO_DataObjectImpl, count);

PHP_METHOD(SDO_SequenceImpl, getPropertyIndex);
PHP_METHOD(SDO_SequenceImpl, getPropertyName);
PHP_METHOD(SDO_SequenceImpl, move);
PHP_METHOD(SDO_SequenceImpl, insert);
PHP_METHOD(SDO_SequenceImpl, count);

PHP_METHOD(SDO_Model_TypeImpl, __construct);
PHP_METHOD(SDO_Model_TypeImpl, getName);
PHP_METHOD(SDO_Model_TypeImpl, getNamespaceURI);
PHP_METHOD(SDO_Model_TypeImpl, isInstance);
PHP_METHOD(SDO_Model_TypeImpl, getProperties);
PHP_METHOD(SDO_Model_TypeImpl, getProperty);
PHP_METHOD(SDO_Model_TypeImpl, isDataType);
PHP_METHOD(SDO_Model_TypeImpl, isSequencedType);
PHP_METHOD(SDO_Model_TypeImpl, isOpenType);
PHP_METHOD(SDO_Model_TypeImpl, getBaseType);

PHP_METHOD(SDO_Model_PropertyImpl, __construct);
PHP_METHOD(SDO_Model_PropertyImpl, getName);
PHP_METHOD(SDO_Model_PropertyImpl, getType);
PHP_METHOD(SDO_Model_PropertyImpl, isMany);
PHP_METHOD(SDO_Model_PropertyImpl, isReadOnly);
PHP_METHOD(SDO_Model_PropertyImpl, isContainment);
PHP_METHOD(SDO_Model_PropertyImpl, getOpposite);
PHP_METHOD(SDO_Model_PropertyImpl, getContainingType);
PHP_METHOD(SDO_Model_PropertyImpl, getDefault);
/* }}} */

/* In every utility function you add that needs to use variables
   in php_sdo_globals, call TSRMLS_FETCH(); after declaring other
   variables used by that function, or better yet, pass in TSRMLS_CC
   after the last function argument and declare your utility function
   with TSRMLS_DC after the last declared argument.  Always refer to
   the globals in your function as SDO_G(variable).  You are
   encouraged to rename these macros something shorter, see
   examples in any other php module directory.
*/

#ifdef ZTS
#    define SDO_G(v) TSRMG(sdo_globals_id, zend_sdo_globals *, v)
#else
#    define SDO_G(v) (sdo_globals.v)
#endif

#endif	/* PHP_SDO_H */

/*
 * Local variables:
 * tab-width: 4
 * c-basic-offset: 4
 * End:
 * vim600: noet sw=4 ts=4 fdm=marker
 * vim<600: noet sw=4 ts=4
 */
