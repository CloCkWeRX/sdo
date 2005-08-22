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
static char rcs_id[] = "$Id$";

#ifndef SDO_EXPORTS
    #define SDO_EXPORTS
#endif

#include "export.h"
#include <iostream>
#include "Property.h"
#include "Type.h"
#include "TypeList.h"
#include "Logger.h"

using namespace std;

namespace commonj{
namespace sdo {

SDO_API TypeList::TypeList(std::vector<const Type*> p) : plist (p)
{
	Logger::log("TypeList constructed from vector\n");
}

SDO_API TypeList::TypeList(const TypeList &pin)
{
	Logger::log("TypeList copy constructor\n");
	plist = std::vector<const Type*>(pin.getVec());
}

SDO_API TypeList::TypeList()
{
	Logger::log("TypeList default constructor\n");
}

SDO_API TypeList::~TypeList()
{
	Logger::log("TypeList destructor\n");
}


SDO_API const Type& TypeList::operator[] (int pos) const
{
	return *plist[pos];
}

SDO_API int TypeList::size () const
{
	return plist.size();
}

std::vector<const Type*> TypeList::getVec() const
{
	return plist;
}

SDO_API void TypeList::insert (const Type* t) 
{
	plist.insert(plist.end(),t);
}

};
};

