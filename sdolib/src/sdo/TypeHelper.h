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

#ifndef _TYPEHELPER_H_
#define _TYPEHELPER_H_

namespace commonj{
namespace sdo{

	///////////////////////////////////////////////////////////////////////////
    // Provide access to additional metadata.
    // Look up a Type given the uri and typeName or interfaceClass.
    // The SDO 1.1 defined types are available through the
    //   getType("commonj.sdo", typeName) interface.
    // A Type is only considered to have an interface class if
    //   isDataObjectType() is true and getInstanceClass() is not null.
	///////////////////////////////////////////////////////////////////////////
 
class TypeHelper
{
	public:
	///////////////////////////////////////////////////////////////////////////
    // Return the Type specified by typeName with the given uri,
    //   or null if not found.
    // @param uri The uri of the Type - type.getURI();
    // @param typeName The name of the Type - type.getName();
    // @return the Type specified by typeName with the given uri,
    //   or null if not found.
	///////////////////////////////////////////////////////////////////////////
	const Type& getType(const char* uri, const char* typeName);
  
	///////////////////////////////////////////////////////////////////////////
    // Return the Type for this interfaceClass or null if not found.
    // Type.isDataObjectType() must be true.
    // @param interfaceClass is the interface for the DataObject's Type -  
    //   type.getInstanceClass();
    // @return the Type for this interfaceClass or null if not found.
	///////////////////////////////////////////////////////////////////////////
    const Type& getType(void* interfaceClass); /* TODO */
  
	///////////////////////////////////////////////////////////////////////////
    // Return a list of alias names for the property.
    // @param property The property to alias names for.
    // @return a list of alias names for the property.
	///////////////////////////////////////////////////////////////////////////
	//std::list<string> /*String*/ getAliasNames(const Property& property);

	///////////////////////////////////////////////////////////////////////////
    // Return a list of alias names for the type.
    // @param type The type to return alias names for.
    // @return a list of alias names for the type.
	///////////////////////////////////////////////////////////////////////////
	//std::list<string> /*String*/ getAliasNames(const Type& type);

	TypeHelper* getSingleton();
  
	private:
	///////////////////////////////////////////////////////////////////////////
    // The default TypeHelper.
	///////////////////////////////////////////////////////////////////////////
	static TypeHelper* singleton;
};

};
};


#endif //_TYPEHELPER_H_
