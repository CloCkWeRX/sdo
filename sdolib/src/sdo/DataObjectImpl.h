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

#ifndef _DATAOBJECTIMPL_H_
#define _DATAOBJECTIMPL_H_
#include "DASDataObject.h"



#include <ctime>
#include <list>
#include <map>

#include "Property.h"
#include "TypeImpl.h"
#include "DASDataFactory.h"
#include "SequenceImpl.h"
#include "DataObjectListImpl.h"
#include "PropertyList.h"

#include "RefCountingPointer.h"
#include "ChangeSummaryImpl.h"

namespace commonj{
namespace sdo{
	
class DataGraph;
class DataObjectImpl; 
class DataObjectListImpl;
class DataFactory;


#define DataObjectImplPtr RefCountingPointer<DataObjectImpl>
#define ChangeSummaryImplPtr RefCountingPointer<ChangeSummaryImpl>

typedef DataObjectImplPtr rdo;
typedef std::map<unsigned int, rdo > PropertyValueMap;


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

class DataObjectImpl : public DASDataObject
{
 	public:

	DataObjectImpl();
	DataObjectImpl(const TypeImpl& t);
	DataObjectImpl(DASDataFactory* dataFactory, const Type& t);

	// This one only needs the values, and the type/prop info. The rest
	// is not copied and would be unsafe to do so. This is used to
	// store the cloned info in a change summary.

	DataObjectImpl(DataObjectImplPtr indo);

	virtual ~DataObjectImpl();

    /////////////////////////////////////////////////////////////////////////
	//	Introspection
    /////////////////////////////////////////////////////////////////////////

	// This locates a property index for this object from the property

	virtual unsigned int getPropertyIndex(const Property& p);
	
	virtual PropertyImpl& getPropertyImplFromIndex(unsigned int index);

	virtual const Property& getPropertyFromIndex(unsigned int index);
	
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
   
	virtual PropertyList getInstanceProperties();

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

    virtual DataObjectPtr getContainer();

	// Return the Property of the data object containing this data object
	// or 0 if there is no container.

    virtual const Property& getContainmentProperty();
	
	// Returns the data object's type.
	// The type defines the properties available for reflective access.
    
	virtual const Type& getType();
	
	virtual const Type::Types getTypeEnum();

	// returns the list of properties of this object - shorthand for
	// getType().getProperties

	virtual PropertyList getProperties();


    ///////////////////////////////////////////////////////////////////////////
	// get/set
	///////////////////////////////////////////////////////////////////////////
	
    
	// Returns the value of a property of either this object or an object 
    // reachable from it, as identified by the
    // specified path.

	virtual DataObjectPtr getDataObject(const char* path); 
	virtual DataObjectPtr getDataObject(unsigned int propertyIndex); 
	virtual DataObjectPtr getDataObject(const Property& property); 

	// sets a property of either this object or an object reachable from it,
	// as identified by the specified path,
	// to the specified value.
	
	virtual void setDataObject(const char* path, DataObjectPtr value); 
	virtual void setDataObject(unsigned int propertyIndex, DataObjectPtr value); 
	virtual void setDataObject(const Property& property, DataObjectPtr value); 

	// Returns the value of a boolean property identified 
	//by the specified path.

	virtual bool getBoolean(const char* path);
	virtual bool getBoolean(unsigned int propindex);
	virtual bool getBoolean(const Property& p);
    
	virtual void setBoolean(const char* path, bool b);
	virtual void setBoolean(unsigned int propindex, bool b);
	virtual void setBoolean(const Property& p, bool b);

	virtual char getByte(const char* path);
	virtual char getByte(unsigned int propindex);
	virtual char getByte(const Property& p);
    
	virtual void setByte(const char* path, char c);
	virtual void setByte(unsigned int propindex, char c);
	virtual void setByte(const Property& p, char c);

	virtual wchar_t getCharacter(const char* path);
	virtual wchar_t getCharacter(unsigned int propindex);
	virtual wchar_t getCharacter(const Property& p);
    
	virtual void setCharacter(const char* path, wchar_t c);
	virtual void setCharacter(unsigned int propindex, wchar_t c);
	virtual void setCharacter(const Property& p, wchar_t c);

	virtual unsigned int getLength(const char* path) ;
	virtual unsigned int getLength(unsigned int propindex) ;
	virtual unsigned int getLength(const Property& p) ;
	virtual unsigned int getLength() ;

	virtual unsigned int getBytes(const char* path, char* buf, unsigned int max) ;
	virtual unsigned int getBytes(unsigned int propindex, char* buf, unsigned int max) ;
	virtual unsigned int getBytes(const Property& p, char* buf, unsigned int max) ;
    
	virtual void setBytes(const char* path, const char* c, unsigned int len) ;
	virtual void setBytes(unsigned int propindex, const char* c,unsigned int len) ;
	virtual void setBytes(const Property& p, const char* c,unsigned int len) ;

	virtual unsigned int getString(const char* path, wchar_t* buf, unsigned int max) ;
	virtual unsigned int getString(unsigned int propindex,wchar_t* buf, unsigned int max) ;
	virtual unsigned int getString(const Property& p,wchar_t* buf, unsigned int max) ;
    
	virtual void setString(const char* path, const wchar_t* c,unsigned int len) ;
	virtual void setString(unsigned int propindex, const wchar_t* c,unsigned int len) ;
	virtual void setString(const Property& p, const wchar_t* c,unsigned int len) ;

	virtual time_t getDate(const char* path);
	virtual time_t getDate(unsigned int propindex);
	virtual time_t getDate(const Property& p);
    
	virtual void setDate(const char* path, time_t d);
	virtual void setDate(unsigned int propindex, time_t d);
	virtual void setDate(const Property& p, time_t d);

	virtual long double getDouble(const char* path);
	virtual long double getDouble(unsigned int propindex);
	virtual long double getDouble(const Property& p);
    
	virtual void setDouble(const char* path, long double d);
	virtual void setDouble(unsigned int propindex, long double d);
	virtual void setDouble(const Property& p, long double d);

	virtual float getFloat(const char* path);
	virtual float getFloat(unsigned int propindex);
	virtual float getFloat(const Property& p);
    
	virtual void setFloat(const char* path, float f);
	virtual void setFloat(unsigned int propindex, float f);
	virtual void setFloat(const Property& p, float f);

	virtual long getInteger(const char* path);
	virtual long getInteger(unsigned int propindex);
	virtual long getInteger(const Property& p);
    
	virtual void setInteger(const char* path, long i);
	virtual void setInteger(unsigned int propindex, long i);
	virtual void setInteger(const Property& p, long i);

	virtual /*long long*/ int64_t getLong(const char* path);
	virtual /*long long*/ int64_t getLong(unsigned int propindex);
	virtual /*long long*/ int64_t getLong(const Property& p);
    
	virtual void setLong(const char* path, /*long long*/ int64_t l);
	virtual void setLong(unsigned int propindex, /*long long*/ int64_t l);
	virtual void setLong(const Property& p, /*long long*/ int64_t l);

	virtual short getShort(const char* path);
	virtual short getShort(unsigned int propindex);
	virtual short getShort(const Property& p);
    
	virtual void setShort(const char* path, short s);
	virtual void setShort(unsigned int propindex, short s);
	virtual void setShort(const Property& p, short s);

 	virtual const char* getCString(const char* path);
	virtual const char* getCString(unsigned int propertyIndex);
	virtual const char* getCString(const Property& prop);
	
	virtual void setCString(const char* path, const char* value);
	virtual void setCString(unsigned int propertyIndex, const char* value);
	virtual void setCString (const Property& prop, const char* value);
    
	virtual void setNull(const char* path);
	virtual void setNull(unsigned int propertyIndex);
	virtual void setNull(const Property& prop);
	
	virtual bool isNull(const char* path);
	virtual bool isNull(unsigned int propertyIndex);
	virtual bool isNull(const Property& prop);
	
	// Returns whether a property of either this object or an object reachable 
	// from it, as identified by the specified path,
	// is considered to be set.
	
	virtual bool isSet(const char* path);
	virtual bool isSet(unsigned int propertyIndex);
	virtual bool isSet(const Property& property);

	// unSets a property of either this object or an object reachable 
	// from it, as identified by the specified path.

	virtual void unset(const char* path);
	virtual void unset(unsigned int propertyIndex);
	virtual void unset(const Property& property);


	///////////////////////////////////////////////////////////////////////////
	// Sequences
	///////////////////////////////////////////////////////////////////////////

	// Returns the value of a Sequence property identified by 
	// the specified path.

	virtual SequenceImpl* getSequenceImpl();
	virtual SequencePtr getSequence();
	virtual SequencePtr getSequence(const char* path);
	virtual SequencePtr getSequence(unsigned int propertyIndex);
	virtual SequencePtr getSequence(const Property& property);


    ///////////////////////////////////////////////////////////////////////////	
	// Creation of dataobjects 
    ///////////////////////////////////////////////////////////////////////////

	// Returns a new data object contained by this object using the 
	// specified property,which must be a containment property.
	// The type of the created object is the declared type
	// of the specified property.
	
	virtual DataObjectPtr createDataObject(const char* propertyName);
    virtual DataObjectPtr createDataObject(unsigned int propertyIndex);
    virtual DataObjectPtr createDataObject(const Property& property);



	// remove this object from its container and unSet all its properties.

    virtual void detach();

	// clear the objects propertes - but leave it in the tree
	virtual void clear();


    ///////////////////////////////////////////////////////////////////////////
	// Lists
	///////////////////////////////////////////////////////////////////////////

	virtual DataObjectList& getList(const char* path);
	virtual DataObjectList& getList(unsigned int propIndex);
	virtual DataObjectList& getList(const Property& p);
	virtual DataObjectList& getList();

	void setList( DataObjectList* theList);

	///////////////////////////////////////////////////////////////////////////
	// Change Summary
	///////////////////////////////////////////////////////////////////////////
	
	virtual SDO_API ChangeSummaryPtr getChangeSummary(const char* path);
    virtual SDO_API ChangeSummaryPtr getChangeSummary(unsigned int propIndex);
    virtual SDO_API ChangeSummaryPtr getChangeSummary(const Property& prop);
  	virtual SDO_API ChangeSummaryPtr getChangeSummary();


	virtual bool getBoolean();
	virtual void setBoolean(bool b);
	virtual char getByte();
	virtual void setByte(char c);
	virtual wchar_t getCharacter();
	virtual void setCharacter(wchar_t c);
	virtual unsigned int getString(wchar_t* buf, unsigned int max);
	virtual unsigned int getBytes(char* buf, unsigned int max);
	virtual void setString(const wchar_t* buf, unsigned int len);
	virtual void setBytes(const char* c, unsigned int len);
	virtual short getShort();
	virtual void setShort(short s);
	virtual long getInteger();
	virtual void setInteger(long s);
	virtual /* long long*/ int64_t getLong();
	virtual void setLong(/* long long */ int64_t i);
	virtual float getFloat();
	virtual void setFloat(float b);
	virtual long double getDouble();
	virtual void setDouble(long double d);
	virtual time_t getDate();
	virtual void setDate(time_t d);
	virtual const char*  getCString();
	virtual void setCString(const char* s);
	virtual DataObjectImpl* getDataObject();
	virtual void setDataObject(DataObject* d);

	// null support
	virtual bool isNull();
	virtual void setNull();
	virtual void unsetNull();

	// change logging is used by the dataobjectlistimpl
	virtual void logChange(const Property& prop);
	virtual void logChange(unsigned int propIndex);
	virtual void logDeletion();
	virtual void logCreation(DataObjectImpl* dol,
		DataObjectImpl* cont, const Property& prop);

	// reference support
	virtual void setReference(DataObject* dob, const Property& prop);
	virtual void unsetReference(DataObject* dob, const Property& prop);
	virtual void clearReferences();
	
	// user data support
	virtual void setUserData(const char* path,void* value);
	virtual void setUserData(unsigned int propertyIndex, void* value);
	virtual void setUserData(const Property& property, void* value);
	virtual void setUserData(void* value);
	virtual void* getUserData(const char* path);
	virtual void* getUserData(unsigned int propertyIndex);
	virtual void* getUserData(const Property& property);
	virtual void* getUserData();

	virtual void setContainer(DataObjectImpl* d);
    DataObjectImpl* getContainerImpl();

	// builds a temporary XPath for this object.
	char* objectToXPath();

	// The data factory can be used to create new data objects within
    // the Type system of this data object
    // 
 	 
    DASDataFactory* getDataFactory();

private:

	void validateIndex(unsigned int index);
  	
	virtual bool DataObjectImpl::remove(DataObjectImpl* indol);

	// cache a copy of the change summary in this data object, if there
	// is one in the tree.

	virtual void setApplicableChangeSummary();


	// The real setters are hidden from the public, and used by the
	// other setters.

	virtual const TypeImpl& getTypeImpl();




	// Returns the value of a property of either this object or an object 
    // reachable from it, as identified by the
    // specified path.

	virtual DataObjectImpl* getDataObjectImpl(const char* path); 
	virtual DataObjectImpl* getDataObjectImpl(unsigned int propertyIndex); 
	virtual DataObjectImpl* getDataObjectImpl(const Property& property); 

	// TODO - This is capable of creating a DO of the wrong type -
	// is that sensible?

	virtual DataObjectPtr
		createDataObject(const Property& property, 
	    const char* namespaceURI,
		const char* typeName);

    DataObjectImpl* findDataObject(char* token, long* index);
 	const Property*   findInProperties(DataObject* ob);
	char* findPropertyContainer(const char* path, DataObjectImpl** din);
 	char* stripPath(const char* path);


	// Does not keep a reference counted pointer to the container.
    DataObjectImpl* container;
 
	// remove the value from the data object.
	void deleteValue();


    PropertyValueMap PropertyValues;
	
	const TypeImpl& ObjectType;


	DataObjectListImpl* listValue;
	
	// Holds the value , reallocated as necessary for strings
	void* value;  

	// In the case of a bytes/string - this holds the length;
	unsigned int valuelength;
	                     
    // holds the value as a string - if requested.
	char* asStringBuffer; 

	// holds the Xpath to this object if requested.
	char* asXPathBuffer;

	// The data object holds a counted reference to the data factory.
	DASDataFactoryPtr factory;

    void setDataFactory(DASDataFactory *df);


	static const char* emptyString;
	static const char* templateString;

	// Data may be set to null in any data object
	bool isnull;

	// user supplied 32 bit value.
	void* userdata;

	//
	// The sequence, if this is a sequenced type - not
	// reference counted by the data object
	//
	SequenceImpl* sequence;

	//
	// The change summary if this is a summarised type
	// not reference counted by the data object - only by
	// clients
	//

	ChangeSummaryImpl* getChangeSummaryImpl();
	ChangeSummaryImpl* getSummary();
	ChangeSummaryImpl* localCS;
	DataObjectImpl* changesummaryobject;


	// reference type support

	class Reference
	{
	public:
		DataObject* getDataObject()
		{
			return referer;
		}
		const Property& getProperty()
		{
			return refprop;
		}
		Reference(DataObject* d, const Property& p) : refprop(p), referer(d)
		{
		}
	private:
		DataObject* referer;
		const Property& refprop;
	};

	typedef std::vector<Reference*> REFERENCE_LIST;

	REFERENCE_LIST refs;


  
};
};
};
 
#endif //_DATAOBJECTIMPL_H_
