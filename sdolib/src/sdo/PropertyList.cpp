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

#ifndef SDO_EXPORTS
    #define SDO_EXPORTS
#endif

#include "export.h"
#include <iostream>
#include "Property.h"
#include "PropertyImpl.h"
#include "Type.h"
#include "PropertyList.h"

#include "Logger.h"

namespace commonj{
namespace sdo {

class Property;

SDO_API PropertyList::PropertyList(PROPERTY_LIST p) 
{
	Logger::log("PropertyList constructed from list\n");
	PROPERTY_LIST::iterator i;
	for (i = p.begin(); i != p.end(); ++i)
	{
		plist.insert(plist.end(),(PropertyImpl*)(*i));
	}
}

SDO_API PropertyList::PropertyList(PROPERTY_VECTOR p) 
{
	Logger::log("PropertyList constructed from vector\n");
	plist = PROPERTY_VECTOR(p);
}

SDO_API PropertyList::PropertyList(const PropertyList &pin)
{
	Logger::log("PropertyList copy constructor\n");
	plist = PROPERTY_VECTOR(pin.getVec());
}

SDO_API PropertyList::PropertyList()
{
	Logger::log("PropertyList default constructor");
}

SDO_API PropertyList::~PropertyList()
{
	Logger::log("PropertyList destructor\n");
}

SDO_API Property& PropertyList::operator[] (int pos)
{
	return *(plist[pos]);
}

SDO_API const Property& PropertyList::operator[] (int pos) const
{
	return *(plist[pos]);
}

SDO_API int PropertyList::size () 
{
	return plist.size();
}

SDO_API void PropertyList::insert(const Property& p) 
{
	PropertyImpl* pi = (PropertyImpl*)&p;
	plist.insert(plist.end(),new PropertyImpl(*pi));
}

PROPERTY_VECTOR PropertyList::getVec() const
{
	return plist;
}

};
};

