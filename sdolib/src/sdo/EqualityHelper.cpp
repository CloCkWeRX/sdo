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


#include "Property.h"
#include "Type.h"
#include "TypeList.h"
#include "Sequence.h"

#include "DataObject.h"

#include "EqualityHelper.h"

namespace commonj{
namespace sdo{
	
	EqualityHelper* EqualityHelper::singleton;


	bool EqualityHelper::isEqualShallow(DataObjectPtr dataObject1, DataObjectPtr dataObject2)
	{
    	return false;
    }
    
    bool EqualityHelper::isEqual(DataObjectPtr dataObject1, DataObjectPtr dataObject2)
    {
    	return false;
    }

    EqualityHelper* EqualityHelper::getSingleton()
    {
		if (EqualityHelper::singleton == 0) 
    	{
    		EqualityHelper::singleton = new EqualityHelper();
    	}
    	return EqualityHelper::singleton;
	}

};
};

