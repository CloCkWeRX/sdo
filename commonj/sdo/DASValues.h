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

#ifndef _SDO_DASValues_H_
#define _SDO_DASValues_H_

#include "commonj/sdo/export.h"
#include "commonj/sdo/DASValue.h"
#include "map"
#include "string"

namespace commonj
{
	namespace sdo
	{
		
		
		class DASValues
		{
		public:
			SDO_API virtual ~DASValues();
			SDO_API virtual void setDASValue(const char* name, DASValue* value);
			SDO_API virtual DASValue* getDASValue(const char* name);
		private:

			typedef std::map<std::string, DASValue*> DASValue_MAP;
			DASValue_MAP properties;
			
		};
		
	}
}
#endif //_SDO_DASValues_H_
