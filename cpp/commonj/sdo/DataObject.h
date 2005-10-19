/* 
+----------------------------------------------------------------------+
| (c) Copyright IBM Corporation 2005.                                  | 
| All Rights Reserved.                                                 |
+----------------------------------------------------------------------+ 
|                                                                      | 
| Licensed under the Apache License, Version 2.0 (the "License"); you  | 
| may not use this file except in compliance with the License. You may | 
| obtain a copy of the License at                                      | 
|  http://www.apache.org/licenses/LICENSE-2.0                          |
|                                                                      | 
| Unless required by applicable law or agreed to in writing, software  | 
| distributed under the License is distributed on an "AS IS" BASIS,    | 
| WITHOUT WARRANTIES OR CONDITIONS OF ANY KIND, either express or      | 
| implied. See the License for the specific language governing         | 
| permissions and limitations under the License.                       | 
+----------------------------------------------------------------------+ 
| Author: Ed Slattery                                                  | 
+----------------------------------------------------------------------+ 

*/
/* $Id$ */

#ifndef _DATAOBJECT_H_
#define _DATAOBJECT_H_
#include "commonj/sdo/export.h"




#include "commonj/sdo/Property.h"
#include "commonj/sdo/Type.h"
#include "commonj/sdo/DataFactory.h"
#include "commonj/sdo/Sequence.h"
#include "commonj/sdo/DataObjectList.h"
#include "commonj/sdo/PropertyList.h"


namespace commonj{
namespace sdo{
	
class DataGraph; 
class DataObjectList;
class DataFactory;
class ChangeSummary;


 //////////////////////////////////////////////////////////////////////////////
 // A data object is a representation of some structured data. 
 // It is the fundamental component in the SDO (Service Data Objects) package.
 // Data objects support reflection, path-based accesss, convenience creation 
 // and deletion methods,and the ability to be part of a data graph.
 // Each data object holds its data as a series of properties. 
 // Properties can be accessed by name, property index, or using the property 
 // meta object itself. 
 // A data object can also contain references to other data objects, through 
 // reference-type properties.
 // A data object has a series of convenience accessors for its properties. 
 // These methods either use a path (String), a property index, 
 // or the property's meta object itself, to identify the property.
 // Some examples of the path-based accessors are as follows:
 // DataObject company = ...;
 // company.get("name");                   
 // company.set("name", "acme");
 // company.get("department.0/name")       
 //                                        .n  indexes from 0.
 // company.get("department[1]/name")      [] indexes from 1.
 // company.get("department[number=123]")  returns the department where number=123
 // company.get("..")                      returns the containing data object
 // company.get("/")                       returns the root containing data object
 // There are general accessors for properties, i.e., get and set, 
 // as well as specific accessors for the primitive types and commonly used 
 // data types like String.
 //////////////////////////////////////////////////////////////////////////////

class DataObject : public RefCountingObject
{
 	public:
		virtual ~DataObject();

    /////////////////////////////////////////////////////////////////////////
	//	Introspection
    /////////////////////////////////////////////////////////////////////////

	// This locates a property index for this object from the property

	virtual unsigned int SDO_API getPropertyIndex(const Property& p) = 0;
	
	// Locates the property of this object from its index.

	virtual SDO_API const Property& getPropertyFromIndex(unsigned int index) = 0;
	
    // Returns a read-only List of the Properties currently used in this DataObject.
    // This list will contain all of the properties in getType().getProperties()
    // and any properties where isSet(property) is true.
    // For example, properties resulting from the use of
    // open or mixed XML content are present if allowed by the Type.
    // The list does not contain duplicates. 
    // The order of the properties in the list begins with getType().getProperties()
    // and the order of the remaining properties is determined by the implementation.
    // The same list will be returned unless the DataObject is updated so that 
    // the contents of the list change
    // Returns the list of Properties currently used in this DataObject.
   
	virtual SDO_API PropertyList getInstanceProperties() = 0;

    // Returns the sequence for this DataObject.
    // When Type.isSequencedType() == true,
    // the Sequence of a DataObject corresponds to the
    // XML elements representing the values of its properties.
    // Updates through DataObject and the Lists or Sequences returned
    // from DataObject operate on the same data.
    // When Type.isSequencedType() == false, null is returned.  
    // Return the Sequence or null.

	// Returns the containing data object
	// or 0 if there is no container.

    virtual SDO_API DataObjectPtr getContainer() = 0;

	// Return the Property of the data object containing this data object
	// or 0 if there is no container.

    virtual SDO_API const Property& getContainmentProperty() = 0;
	
	// Returns the data object's type.
	// The type defines the properties available for reflective access.
    
	virtual SDO_API const Type& getType() = 0;

	// Returns an enumerator for the type for easy switching. The enumerator 
	// is visible in the Type.h header file.

	virtual SDO_API const Type::Types getTypeEnum() = 0;

	// returns the list of properties of this object - shorthand for
	// getType().getProperties

	virtual SDO_API PropertyList getProperties() = 0;


    ///////////////////////////////////////////////////////////////////////////
	// get/set
	///////////////////////////////////////////////////////////////////////////
	
    
	// Returns the value of a property of either this object or an object 
    // reachable from it, as identified by the
    // specified path.

	virtual SDO_API DataObjectPtr getDataObject(const char* path) = 0; 
	virtual SDO_API DataObjectPtr getDataObject(unsigned int propertyIndex) = 0; 
	virtual SDO_API DataObjectPtr getDataObject(const Property& property) = 0; 

	// sets a property of either this object or an object reachable from it,
	// as identified by the specified path,
	// to the specified value.
	
	virtual SDO_API void setDataObject(const char* path, DataObjectPtr value) = 0; 
	virtual SDO_API void setDataObject(unsigned int propertyIndex, DataObjectPtr value) = 0; 
	virtual SDO_API void setDataObject(const Property& property, DataObjectPtr value) = 0; 

	// Returns the value of a boolean property identified 
	//by the specified path.

	virtual SDO_API bool getBoolean(const char* path) = 0;
	virtual SDO_API bool getBoolean(unsigned int propindex) = 0;
	virtual SDO_API bool getBoolean(const Property& p) = 0;
    
	virtual SDO_API void setBoolean(const char* path, bool b) = 0;
	virtual SDO_API void setBoolean(unsigned int propindex, bool b) = 0;
	virtual SDO_API void setBoolean(const Property& p, bool b) = 0;

	virtual SDO_API char getByte(const char* path) = 0;
	virtual SDO_API char getByte(unsigned int propindex) = 0;
	virtual SDO_API char getByte(const Property& p) = 0;
    
	virtual SDO_API void setByte(const char* path, char c) = 0;
	virtual SDO_API void setByte(unsigned int propindex, char c) = 0;
	virtual SDO_API void setByte(const Property& p, char c) = 0;

	virtual SDO_API wchar_t getCharacter(const char* path) = 0;
	virtual SDO_API wchar_t getCharacter(unsigned int propindex) = 0;
	virtual SDO_API wchar_t getCharacter(const Property& p) = 0;
    
	virtual SDO_API void setCharacter(const char* path, wchar_t c) = 0;
	virtual SDO_API void setCharacter(unsigned int propindex, wchar_t c) = 0;
	virtual SDO_API void setCharacter(const Property& p, wchar_t c) = 0;

	// These are specidfic to Bytes and Characters data objects, and return the 
	// length of the buffer required to hold the contents of the object.
	// len = do->getLength("name");
	// buf = new char[len];
	// reallen = do->getBytes("name",buf,len);

	virtual SDO_API unsigned int getLength(const char* path) = 0;
	virtual SDO_API unsigned int getLength(unsigned int propindex) = 0;
	virtual SDO_API unsigned int getLength(const Property& p) = 0;
	virtual SDO_API unsigned int getLength() = 0;

	virtual SDO_API unsigned int getBytes(const char* path, char* buf, unsigned int max) = 0;
	virtual SDO_API unsigned int getBytes(unsigned int propindex, char* buf, unsigned int max) = 0;
	virtual SDO_API unsigned int getBytes(const Property& p, char* buf, unsigned int max) = 0;
    
	virtual SDO_API void setBytes(const char* path, const char* c, unsigned int length) = 0;
	virtual SDO_API void setBytes(unsigned int propindex, const char* c, unsigned int length) = 0;
	virtual SDO_API void setBytes(const Property& p, const char* c, unsigned int length) = 0;

	virtual SDO_API unsigned int getString(const char* path , wchar_t* c, unsigned int max) = 0;
	virtual SDO_API unsigned int getString(unsigned int propindex, wchar_t* c, unsigned int max) = 0;
	virtual SDO_API unsigned int getString(const Property& p, wchar_t* c, unsigned int max) = 0;
    
	virtual SDO_API void setString(const char* path, const wchar_t* c, unsigned int length) = 0;
	virtual SDO_API void setString(unsigned int propindex, const wchar_t* c, unsigned int length) = 0;
	virtual SDO_API void setString(const Property& p, const wchar_t* c, unsigned int length) = 0;

	virtual SDO_API const SDODate getDate(const char* path) = 0;
	virtual SDO_API const SDODate getDate(unsigned int propindex) = 0;
	virtual SDO_API const SDODate getDate(const Property& p) = 0;
    
	virtual SDO_API void setDate(const char* path, const SDODate d) = 0;
	virtual SDO_API void setDate(unsigned int propindex, const SDODate d) = 0;
	virtual SDO_API void setDate(const Property& p, const SDODate d) = 0;

	virtual SDO_API long double getDouble(const char* path) = 0;
	virtual SDO_API long double getDouble(unsigned int propindex) = 0;
	virtual SDO_API long double getDouble(const Property& p) = 0;
    
	virtual SDO_API void setDouble(const char* path, long double d) = 0;
	virtual SDO_API void setDouble(unsigned int propindex, long double d) = 0;
	virtual SDO_API void setDouble(const Property& p, long double d) = 0;

	virtual SDO_API float getFloat(const char* path) = 0;
	virtual SDO_API float getFloat(unsigned int propindex) = 0;
	virtual SDO_API float getFloat(const Property& p) = 0;
    
	virtual SDO_API void setFloat(const char* path, float f) = 0;
	virtual SDO_API void setFloat(unsigned int propindex, float f) = 0;
	virtual SDO_API void setFloat(const Property& p, float f) = 0;

	virtual SDO_API long getInteger(const char* path) = 0;
	virtual SDO_API long getInteger(unsigned int propindex) = 0;
	virtual SDO_API long getInteger(const Property& p) = 0;
    
	virtual SDO_API void setInteger(const char* path, long i) = 0;
	virtual SDO_API void setInteger(unsigned int propindex, long i) = 0;
	virtual SDO_API void setInteger(const Property& p, long i) = 0;

	virtual SDO_API int64_t getLong(const char* path) = 0;
	virtual SDO_API int64_t getLong(unsigned int propindex) = 0;
	virtual SDO_API int64_t getLong(const Property& p) = 0;
    
	virtual SDO_API void setLong(const char* path, int64_t l) = 0;
	virtual SDO_API void setLong(unsigned int propindex, int64_t l) = 0;
	virtual SDO_API void setLong(const Property& p, int64_t l) = 0;

	virtual SDO_API short getShort(const char* path) = 0;
	virtual SDO_API short getShort(unsigned int propindex) = 0;
	virtual SDO_API short getShort(const Property& p) = 0;
    
	virtual SDO_API void setShort(const char* path, short s) = 0;
	virtual SDO_API void setShort(unsigned int propindex, short s) = 0;
	virtual SDO_API void setShort(const Property& p, short s) = 0;

 	virtual SDO_API const char* getCString(const char* path) = 0;
	virtual SDO_API const char* getCString(unsigned int propertyIndex) = 0;
	virtual SDO_API const char* getCString(const Property& prop) = 0;
	
	virtual SDO_API void setCString(const char* path, const char* value) = 0;
	virtual SDO_API void setCString(unsigned int propertyIndex, const char* value) = 0;
	virtual SDO_API void setCString (const Property& prop, const char* value) = 0;
    
	virtual SDO_API void setNull(const char* path) = 0;
	virtual SDO_API void setNull(unsigned int propertyIndex) = 0;
	virtual SDO_API void setNull(const Property& prop) = 0;
	
	virtual SDO_API bool isNull(const char* path) = 0;
	virtual SDO_API bool isNull(unsigned int propertyIndex) = 0;
	virtual SDO_API bool isNull(const Property& prop) = 0;

	// Returns whether a property of either this object or an object reachable 
	// from it, as identified by the specified path,
	// is considered to be set.
	
	virtual SDO_API bool isSet(const char* path) = 0;
	virtual SDO_API bool isSet(unsigned int propertyIndex) = 0;
	virtual SDO_API bool isSet(const Property& property) = 0;

	// unSets a property of either this object or an object reachable 
	// from it, as identified by the specified path.

	virtual SDO_API void unset(const char* path) = 0;
	virtual SDO_API void unset(unsigned int propertyIndex) = 0;
	virtual SDO_API void unset(const Property& property) = 0;

	virtual SDO_API void setUserData(const char* path,void* value) = 0;
	virtual SDO_API void setUserData(unsigned int propertyIndex, void* value) = 0;
	virtual SDO_API void setUserData(const Property& property, void* value) = 0;
	virtual SDO_API void setUserData(void* value) = 0;
	virtual SDO_API void* getUserData(const char* path) = 0;
	virtual SDO_API void* getUserData(unsigned int propertyIndex) = 0;
	virtual SDO_API void* getUserData(const Property& property) = 0;
	virtual SDO_API void* getUserData() = 0;

	///////////////////////////////////////////////////////////////////////////
	// Sequences
	///////////////////////////////////////////////////////////////////////////

	// Returns the value of a Sequence property identified by 
	// the specified path.

	virtual SDO_API SequencePtr getSequence() = 0;
	virtual SDO_API SequencePtr getSequence(const char* path) = 0;
	virtual SDO_API SequencePtr getSequence(unsigned int propertyIndex) = 0;
	virtual SDO_API SequencePtr getSequence(const Property& property) = 0;


    ///////////////////////////////////////////////////////////////////////////	
	// Creation of dataobjects 
    ///////////////////////////////////////////////////////////////////////////

	// Returns a new data object contained by this object using the 
	// specified property,which must be a containment property.
	// The type of the created object is the declared type
	// of the specified property.
	
	virtual SDO_API DataObjectPtr createDataObject(const char* propertyName) = 0;
    virtual SDO_API DataObjectPtr createDataObject(unsigned int propertyIndex) = 0;
    virtual SDO_API DataObjectPtr createDataObject(const Property& property) = 0;



	// Remove this object from its container and unSet all its properties.

    virtual SDO_API void detach() = 0;

    virtual SDO_API void clear() = 0;


    ///////////////////////////////////////////////////////////////////////////
	// Lists
	///////////////////////////////////////////////////////////////////////////

	virtual SDO_API DataObjectList& getList(const char* path) = 0;
	virtual SDO_API DataObjectList& getList(unsigned int propIndex) = 0;
	virtual SDO_API DataObjectList& getList(const Property& p) = 0;
	virtual DataObjectList& getList() = 0;


	///////////////////////////////////////////////////////////////////////////
	// Change Summary
	///////////////////////////////////////////////////////////////////////////

	virtual SDO_SPI ChangeSummaryPtr getChangeSummary() = 0;
	virtual SDO_SPI ChangeSummaryPtr getChangeSummary(const char* path) = 0;
    virtual SDO_SPI ChangeSummaryPtr getChangeSummary(unsigned int propIndex) = 0;
    virtual SDO_SPI ChangeSummaryPtr getChangeSummary(const Property& prop) = 0;

 	//////////////////////////////////////////////////////////////////////////
	// get the XPAth to this object
	//////////////////////////////////////////////////////////////////////////

	virtual SDO_SPI const char* objectToXPath() = 0;


};
};
};
 
#endif //_DATAOBJECT_H_
