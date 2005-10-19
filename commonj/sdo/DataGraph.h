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

#ifndef _DATAGRAPH_H_
#define _DATAGRAPH_H_
#include "commonj/sdo/RefCountingObject.h"
#include "commonj/sdo/Type.h"
#include "commonj/sdo/export.h"
#include "commonj/sdo/DataObject.h"

namespace commonj{
namespace sdo{
	


class DataGraph : public RefCountingObject
{
 	public:
		virtual ~DataGraph();

    /////////////////////////////////////////////////////////////////////////
	//	
    /////////////////////////////////////////////////////////////////////////

	virtual SDO_API DataObjectPtr getRootObject() = 0;
	
	virtual SDO_API const char*  getRootElementName() = 0;

	virtual SDO_API void setRootObject(DataObjectPtr dob) = 0;

    /////////////////////////////////////////////////////////////////////////
	//	
    /////////////////////////////////////////////////////////////////////////

	virtual SDO_API DataObjectPtr createRootObject(const char* uri,
						const char* name) = 0;

    /////////////////////////////////////////////////////////////////////////
	//	
    /////////////////////////////////////////////////////////////////////////

	virtual SDO_API DataObjectPtr createRootObject(const Type& t) = 0;

    /////////////////////////////////////////////////////////////////////////
	//	
    /////////////////////////////////////////////////////////////////////////

	virtual SDO_API ChangeSummaryPtr getChangeSummary() = 0;

    /////////////////////////////////////////////////////////////////////////
	//	
    /////////////////////////////////////////////////////////////////////////

	virtual SDO_API const Type& getType(const char* uri,
							const char* name) = 0;


};
};
};
 
#endif //_DATAGRAPH_H_
