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

#ifndef _DATAFACTORY_H_
#define _DATAFACTORY_H_

#include "export.h"

#include "TypeList.h"

#include "RefCountingObject.h"
#include "RefCountingPointer.h"

namespace commonj{
namespace sdo{

class DataObject;
class Type;

class DataFactory : public RefCountingObject
{
	public:

	 SDO_API virtual ~DataFactory();

	 SDO_API virtual DataObjectPtr create(const char* uri, const char* typeName)  = 0;

	 SDO_API virtual DataObjectPtr create(const Type& type)  = 0;

 	 SDO_API virtual const Type& getType(const char* uri, const char* inTypeName) const = 0;

	 SDO_API virtual TypeList getTypes() const = 0;

};
};
};
#endif //_DATAFACTORY_H_
