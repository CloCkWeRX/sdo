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

#ifndef _SDO_TYPE_H_
#define _SDO_TYPE_H_

#include "commonj/sdo/export.h"

#include "commonj/sdo/PropertyList.h"

namespace commonj{
namespace sdo{


	///////////////////////////////////////////////////////////////////////////
    // A representation of the type of a property of a data object.
	///////////////////////////////////////////////////////////////////////////


class Property;

class Type
{

public:


   SDO_API enum Types
   {
	// Zero is a value for 'unknown type; - all data objects'
	OtherTypes = 0,
	BigDecimalType, 
	BigIntegerType, 
	BooleanType,   
	ByteType,
	BytesType,
	CharacterType,
	DateType,      
	DoubleType,    
	FloatType,    
	IntegerType, 
	LongType,      
	ShortType,     
	StringType,    
	UriType,
	DataObjectType,
	ChangeSummaryType,
	TextType,
	num_types
   };

	virtual SDO_API ~Type();

	///////////////////////////////////////////////////////////////////////////
    // Returns the name of the type.
    // @return the name.
	///////////////////////////////////////////////////////////////////////////
    virtual SDO_API const char* getName() const = 0;
  
	///////////////////////////////////////////////////////////////////////////
    // Alias support.
    // @return nth alias
	///////////////////////////////////////////////////////////////////////////
	virtual SDO_API const char* getAlias(unsigned int index = 0) const = 0;
	virtual SDO_API unsigned int getAliasCount() const = 0;
	
	virtual SDO_API const Type* getBaseType() const = 0;

	///////////////////////////////////////////////////////////////////////////
    // Returns the namespace URI of the type.
    // @return the namespace URI.
	///////////////////////////////////////////////////////////////////////////
	virtual SDO_API const char* getURI() const = 0;



	///////////////////////////////////////////////////////////////////////////
    // Returns the list of the properties of this type.
	///////////////////////////////////////////////////////////////////////////
	virtual SDO_API PropertyList  getProperties() const = 0;

 
	///////////////////////////////////////////////////////////////////////////
    // Returns the property with the specified name.
	///////////////////////////////////////////////////////////////////////////
    virtual SDO_API const Property& getProperty(const char* propertyName)  const = 0;
    virtual SDO_API const Property& getProperty(unsigned int index)  const = 0;

	///////////////////////////////////////////////////////////////////////////
    // Returns the property with the specified name.
	///////////////////////////////////////////////////////////////////////////
	virtual SDO_API unsigned int getPropertyIndex(const char* propertyName) const  = 0;
 
	///////////////////////////////////////////////////////////////////////////
    // Indicates if this Type specifies DataObjects.
	///////////////////////////////////////////////////////////////////////////
	virtual SDO_API bool isDataObjectType() const = 0;
  

	///////////////////////////////////////////////////////////////////////////
    // Indicates if this Type specifies Sequenced DataObjects.
	///////////////////////////////////////////////////////////////////////////
	virtual SDO_API bool isSequencedType() const = 0;


	///////////////////////////////////////////////////////////////////////////
    // Indicates if this type may have extra properties.
	///////////////////////////////////////////////////////////////////////////
	virtual SDO_API bool isOpenType() const = 0;

	///////////////////////////////////////////////////////////////////////////
    // Indicates if this type may not be instantiated.
	///////////////////////////////////////////////////////////////////////////
	virtual SDO_API bool isAbstractType() const = 0;

	///////////////////////////////////////////////////////////////////////////
    // Indicates if this type is a primitive.
	///////////////////////////////////////////////////////////////////////////
	virtual	SDO_API bool isDataType() const = 0;


	///////////////////////////////////////////////////////////////////////////
    // Indicates if this type is a primitive.
	///////////////////////////////////////////////////////////////////////////
	virtual	SDO_API bool isChangeSummaryType() const = 0;

	///////////////////////////////////////////////////////////////////////////
    // gets the enum for this type.
	///////////////////////////////////////////////////////////////////////////
	virtual	SDO_API Type::Types getTypeEnum() const = 0;

	virtual SDO_API bool equals(const Type& tother) const = 0;

	static SDO_API const char* SDOTypeNamespaceURI;
};

};
};
#endif //_SDO_TYPE_H_

