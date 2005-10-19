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

#ifndef _DATAGRAPHIMPL_H_
#define _DATAGRAPHIMPL_H_
#include "commonj/sdo/export.h"
#include "commonj/sdo/DataGraph.h"

namespace commonj{
namespace sdo{
	


class DataGraphImpl : public DataGraph
{
 	public:
		virtual ~DataGraphImpl();

		SDO_API DataGraphImpl(DataFactoryPtr fac);

 	    virtual SDO_API void setRootObject(DataObjectPtr dob);

   /////////////////////////////////////////////////////////////////////////
	//	
    /////////////////////////////////////////////////////////////////////////

	virtual DataObjectPtr getRootObject();

	virtual const char* getRootElementName();

    /////////////////////////////////////////////////////////////////////////
	//	
    /////////////////////////////////////////////////////////////////////////

	virtual DataObjectPtr createRootObject(const char* uri,
						const char* name);

    /////////////////////////////////////////////////////////////////////////
	//	
    /////////////////////////////////////////////////////////////////////////


	virtual DataObjectPtr createRootObject(const Type& t);

    /////////////////////////////////////////////////////////////////////////
	//	
    /////////////////////////////////////////////////////////////////////////

	virtual ChangeSummaryPtr getChangeSummary();

    /////////////////////////////////////////////////////////////////////////
	//	
    /////////////////////////////////////////////////////////////////////////

	virtual const Type& getType(const char* uri,
							const char* name);

	private:

		DataFactoryPtr factory;
		DataObjectPtr root;

};
};
};
 
#endif //_DATAGRAPHIMPL_H_
