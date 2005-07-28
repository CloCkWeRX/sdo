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

#ifndef _DASDATAOBJECT_H_
#define _DASDATAOBJECT_H_
#include "DataObject.h"
#include "RefCountingPointer.h"

namespace commonj{
namespace sdo{
	

	class DASDataObject : public DataObject
{
 	public:

	virtual ~DASDataObject();


	///////////////////////////////////////////////////////////////////////////
	// Change Summary
	///////////////////////////////////////////////////////////////////////////
	
	SDO_API virtual ChangeSummaryPtr getChangeSummary() = 0;
	SDO_API virtual ChangeSummaryPtr getChangeSummary(const char* path) = 0;
    SDO_API virtual ChangeSummaryPtr getChangeSummary(unsigned int propIndex) = 0;
    SDO_API virtual ChangeSummaryPtr getChangeSummary(const Property& prop) = 0;

 	//////////////////////////////////////////////////////////////////////////
	// get the XPAth to this object
	//////////////////////////////////////////////////////////////////////////

	virtual SDO_API char* objectToXPath() = 0;


};
};
};
 
#endif //_DASDATAOBJECT_H_
