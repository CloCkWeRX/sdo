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
| Author: Pete Robbins                                                 | 
+----------------------------------------------------------------------+ 

*/
/* $Id$ */

#ifndef _XSDPropertyInfo_H_
#define _XSDPropertyInfo_H_

#include "commonj/sdo/DASValue.h"
#include "commonj/sdo/PropertyDefinition.h"
#include "commonj/sdo/SDOXMLString.h"

namespace commonj
{
	namespace sdo
	{
			
		class XSDPropertyInfo : public DASValue
		{
		public:
			
			XSDPropertyInfo();
			XSDPropertyInfo(const PropertyDefinition& prop);
			
			virtual ~XSDPropertyInfo();
			
			const PropertyDefinition& getPropertyDefinition() {return property;}
			
		private:
			PropertyDefinition property;				
		};
	}
}
#endif //_XSDPropertyInfo_H_
