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

#include "export.h"

#include "Type.h"

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

};


};
};

#endif //_PROPERTY_H_
