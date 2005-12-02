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

#ifndef _PROPERTY_H_
#define _PROPERTY_H_

#include "commonj/sdo/export.h"

#include "commonj/sdo/Type.h"
#include "commonj/sdo/SDODate.h"

namespace commonj{
namespace sdo{

class Type;
class TypeImpl;
class DataObject;

///////////////////////////////////////////////////////////////////////////
// A representation of a property in the type of a data object.
///////////////////////////////////////////////////////////////////////////
class Property
{
	public:
	
	///////////////////////////////////////////////////////////////////////////
    // Returns the name of the property.
   	///////////////////////////////////////////////////////////////////////////
	virtual const SDO_API char* getName() const = 0;
  
	///////////////////////////////////////////////////////////////////////////
    // Alias support.
    // @return nth alias
	///////////////////////////////////////////////////////////////////////////
	virtual SDO_API const char* getAlias(unsigned int index = 0) const = 0;
	virtual SDO_API unsigned int getAliasCount() const = 0;

	///////////////////////////////////////////////////////////////////////////
    // Returns the type of the property.
 	///////////////////////////////////////////////////////////////////////////
	virtual SDO_API const Type& getType() const = 0;

	virtual SDO_API const Type::Types getTypeEnum() const = 0;

 	///////////////////////////////////////////////////////////////////////////
    // Returns whether the property is many-valued.
	///////////////////////////////////////////////////////////////////////////
	virtual bool SDO_API isMany() const = 0;
  
	///////////////////////////////////////////////////////////////////////////
    // Returns whether the property is containment. .
	///////////////////////////////////////////////////////////////////////////
	virtual bool SDO_API isContainment() const = 0;

	///////////////////////////////////////////////////////////////////////////
    // Returns whether the property is containment. .
	///////////////////////////////////////////////////////////////////////////
	virtual bool SDO_API isReference() const = 0;
  
	///////////////////////////////////////////////////////////////////////////
    // Returns the containing type of this property.
	///////////////////////////////////////////////////////////////////////////
	virtual SDO_API const Type& getContainingType() const = 0;
  


	///////////////////////////////////////////////////////////////////////////
    // Returns true if values for this Property cannot be modified using the SDO APIs.
    // When true, DataObject.set(Property property, Object value) throws an exception.
    // Values may change due to other factors, such as services operating on DataObjects.
	///////////////////////////////////////////////////////////////////////////
	virtual bool SDO_API isReadOnly() const = 0;

	///////////////////////////////////////////////////////////////////////////
    // returns the opposite property, or zero if there is none
	///////////////////////////////////////////////////////////////////////////
	virtual SDO_API const Property* getOpposite() const = 0;

	virtual SDO_API bool isDefaulted() const = 0 ;

	///////////////////////////////////////////////////////////////////////////
    // set the default value
	///////////////////////////////////////////////////////////////////////////
	virtual SDO_API void setDefault(bool b ) = 0;
	virtual SDO_API void setDefault(char c) = 0;
	virtual SDO_API void setDefault(wchar_t c) = 0;
	virtual SDO_API void setDefault(char* c) = 0;
	virtual SDO_API void setDefault(short s) = 0;
	virtual SDO_API void setDefault(long l) = 0;
	virtual SDO_API void setDefault(int64_t i) = 0;
	virtual SDO_API void setDefault(float f) = 0;
	virtual SDO_API void setDefault(long double d) = 0;
	virtual SDO_API void setDefault(const SDODate d) = 0;
	virtual SDO_API void setDefault(const wchar_t* c, unsigned int len) = 0;
	virtual SDO_API void setDefault(const char* c, unsigned int len) = 0;

	///////////////////////////////////////////////////////////////////////////
    // get the default value
	///////////////////////////////////////////////////////////////////////////
	virtual SDO_API unsigned int 	getStringDefault(wchar_t* val, unsigned int max) const = 0;
	virtual SDO_API unsigned int    getBytesDefault(char* val, unsigned int max) const = 0;
	virtual SDO_API bool        getBooleanDefault() const = 0;
	virtual SDO_API char        getByteDefault() const = 0;
	virtual SDO_API wchar_t     getCharacterDefault() const = 0;
	virtual SDO_API short       getShortDefault() const = 0;
	virtual SDO_API long        getIntegerDefault() const = 0;
	virtual SDO_API int64_t     getLongDefault() const = 0;
	virtual SDO_API float       getFloatDefault() const = 0;
	virtual SDO_API long double getDoubleDefault() const = 0;
	virtual SDO_API const SDODate  getDateDefault() const = 0;
	virtual SDO_API unsigned int getDefaultLength() const = 0;


};


};
};

#endif //_PROPERTY_H_
