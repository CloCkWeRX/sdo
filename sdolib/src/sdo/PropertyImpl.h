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

#ifndef _PROPERTYIMPL_H_
#define _PROPERTYIMPL_H_

#include <string>
using namespace std;

#include "DASProperty.h"


namespace commonj{
namespace sdo{

class Type;
class TypeImpl;
class DataObject;

///////////////////////////////////////////////////////////////////////////
// A representation of a property in the type of a data object.
///////////////////////////////////////////////////////////////////////////
class PropertyImpl :public DASProperty
{
	public:


	///////////////////////////////////////////////////////////////////////////
    // Construction for the DAS 
	///////////////////////////////////////////////////////////////////////////

	SDO_API PropertyImpl(    
		         const Type& cont,
				 const char* name,  
			     const TypeImpl& type, 
				 bool many = false, 
  	             bool ro = false,
				 bool contain = false);



	virtual SDO_API ~PropertyImpl();

	///////////////////////////////////////////////////////////////////////////
	// Settings by the DAS
	///////////////////////////////////////////////////////////////////////////

	virtual SDO_API void setMany(bool many );
	virtual SDO_API void setReadOnly(bool rdonly);
	virtual SDO_API void setContainment(bool contains);

	
	virtual SDO_API void setDefault(bool b );
	virtual SDO_API void setDefault(char c);
	virtual SDO_API void setDefault(wchar_t c);
	virtual SDO_API void setDefault(char* c);
	virtual SDO_API void setDefault(short s);
	virtual SDO_API void setDefault(long l);
	virtual SDO_API void setDefault(int64_t i);
	virtual SDO_API void setDefault(float f);
	virtual SDO_API void setDefault(long double d);
	virtual SDO_API void setDefault(const wchar_t* c, unsigned int len);
	virtual SDO_API void setDefault(const char* c, unsigned int len);




	
	///////////////////////////////////////////////////////////////////////////
    // Returns the name of the property.
   	///////////////////////////////////////////////////////////////////////////
	virtual const SDO_API char* getName() const;
  
	///////////////////////////////////////////////////////////////////////////
    // Alias support.
    // @return nth alias
	///////////////////////////////////////////////////////////////////////////
	virtual const char* getAlias(unsigned int index = 0) const ;
	virtual unsigned int getAliasCount() const ;
	virtual void setAlias(const char* alias) ;

	///////////////////////////////////////////////////////////////////////////
    // Returns the type of the property.
 	///////////////////////////////////////////////////////////////////////////
	virtual SDO_API const Type& getType() const;

	virtual SDO_API const Type::Types getTypeEnum() const;

	///////////////////////////////////////////////////////////////////////////
    // Returns the type as an impl
	///////////////////////////////////////////////////////////////////////////
	virtual SDO_API const TypeImpl& getTypeImpl() const;
  
	///////////////////////////////////////////////////////////////////////////
    // Returns whether the property is many-valued.
	///////////////////////////////////////////////////////////////////////////
	virtual bool SDO_API isMany() const;
  
	///////////////////////////////////////////////////////////////////////////
    // Returns whether the property is containment. .
	///////////////////////////////////////////////////////////////////////////
	virtual bool SDO_API isContainment() const;

	///////////////////////////////////////////////////////////////////////////
    // Returns whether the property is a reference. .
	///////////////////////////////////////////////////////////////////////////
	virtual bool SDO_API isReference() const;
 
	///////////////////////////////////////////////////////////////////////////
    // Returns the containing type of this property.
	///////////////////////////////////////////////////////////////////////////
	virtual SDO_API const Type& getContainingType() const;
  


	///////////////////////////////////////////////////////////////////////////
    //  setters for type primitive types and 'an object'
	///////////////////////////////////////////////////////////////////////////

	SDO_API void setDefaultCString(  const char* s);
	SDO_API void setDefaultString( const wchar_t* c , unsigned int len  );
	SDO_API void setDefaultBytes( const char* c , unsigned int len  );
	SDO_API void setDefaultBoolean( const bool b   );
	SDO_API void setDefaultByte(    const char c   );
	SDO_API void setDefaultCharacter(   const wchar_t c);
	SDO_API void setDefaultShort(   const short s  );
	SDO_API void setDefaultInteger( const long i    );
	SDO_API void setDefaultLong(const int64_t l);
	SDO_API void setDefaultFloat(   const float f  );
	SDO_API void setDefaultDouble(  const long double d );
	SDO_API void setDefaultDate(    const time_t d );

	SDO_API const char*	    getCStringDefault() ;
	SDO_API unsigned int 	getStringDefault(wchar_t* val, unsigned int max) ;
	SDO_API unsigned int    getBytesDefault(char* val, unsigned int max) ;
	SDO_API bool        getBooleanDefault() const;
	SDO_API char        getByteDefault() const;
	SDO_API wchar_t     getCharacterDefault() const;
	SDO_API short       getShortDefault() const;
	SDO_API long        getIntegerDefault() const;
	SDO_API int64_t     getLongDefault() const;
	SDO_API float       getFloatDefault() const;
	SDO_API long double getDoubleDefault() const;
	SDO_API time_t      getDateDefault() const;

	///////////////////////////////////////////////////////////////////////////
    // Returns true if values for this Property cannot be modified using the SDO APIs.
    // When true, DataObject.set(Property property, Object value) throws an exception.
    // Values may change due to other factors, such as services operating on DataObjects.
	///////////////////////////////////////////////////////////////////////////
	virtual bool SDO_API isReadOnly() const;

	SDO_API PropertyImpl(const PropertyImpl& p);

  private:
	
	 // clear the default value
	void deleteValue();

	bool bisReadOnly;
	bool bisContainer;
	bool bisReference;
	bool bisMany;
	  
	const TypeImpl& type;
	const Type& containertype;
	char* name;
    char* stringdef;

    void* defvalue;
	// in the event of a bytes and string, this holds the length
	unsigned int defvaluelength;

	// alias support
	std::vector<char*> aliases;

	typedef std::list<PropertyImpl*> REFEREE_LIST;
	REFEREE_LIST references;

  
};
};
};

#endif //_PROPERTYIMPL_H_
