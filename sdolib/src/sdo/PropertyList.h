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

#ifndef _PROPERTYLIST_H_
#define _PROPERTYLIST_H_

#include "export.h"

#include <vector>
#include <list>





namespace commonj{
namespace sdo{

	class PropertyImpl;
	class Property;
	typedef std::vector<PropertyImpl*> PROPERTY_VECTOR;
#ifndef PROPERTY_LIST
	typedef std::list<PropertyImpl*> PROPERTY_LIST;
#endif

class PropertyList
{
private: 
	PROPERTY_VECTOR plist;
	PROPERTY_VECTOR getVec() const;
public:
    SDO_API PropertyList(PROPERTY_VECTOR p);
    SDO_API PropertyList(PROPERTY_LIST p);
    SDO_API PropertyList(const PropertyList &pin);
	SDO_API PropertyList();
	virtual SDO_API ~PropertyList();
	SDO_API Property& operator[] (int pos);
	SDO_API const Property& operator[] (int pos) const;
	SDO_API int size ();
	SDO_API void insert (const Property& p);
};
};
};

#endif
