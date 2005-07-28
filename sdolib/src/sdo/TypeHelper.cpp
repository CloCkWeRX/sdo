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

#include "Property.h"
#include "Type.h"
#include "TypeList.h"
#include "Sequence.h"

#include "DataObject.h"

#include "TypeHelper.h"
#include "SDORuntimeException.h"

namespace commonj{
namespace sdo{
	
	TypeHelper* TypeHelper::singleton;


	const Type& TypeHelper::getType(const char* uri, const char* typeName)
	{
		SDO_THROW_EXCEPTION("getType", SDORuntimeException,
		"TypeHelper::getType Not implemented");
	}
  
	const Type& TypeHelper::getType(void* interfaceClass) /* TODO */
	{
		SDO_THROW_EXCEPTION("getType", SDORuntimeException,
		"TypeHelper::getType Not implemented");
	}
  
	//std::list<string> /*String*/ TypeHelper::getAliasNames(
	//	        const Property& property)
	//{
	//	SDO_THROW_EXCEPTION("getAliasNames", SDORuntimeException,
	//	"TypeHelper::getAliasNames Not implemented");
	//}

	//std::list<string> /*String*/ TypeHelper::getAliasNames(
	//	const Type& type)
	//{
	//	SDO_THROW_EXCEPTION("getAliasNames", SDORuntimeException,
	//	"TypeHelper::getAliasNames Not implemented");
	//}

	TypeHelper* TypeHelper::getSingleton()
	{
  		if (TypeHelper::singleton == 0)
  		{
  			TypeHelper::singleton = new TypeHelper();
  		}
  	return TypeHelper::singleton;
	}
};
};


