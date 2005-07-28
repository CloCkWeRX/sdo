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

#ifndef _SDO_DASValue_H_
#define _SDO_DASValue_H_

#pragma warning(disable: 4786)

#include "export.h"
#include "string"

namespace commonj
{
	namespace sdo
	{
		
		
		class DASValue
		{
		public:
			SDO_API DASValue();
			SDO_API DASValue(const char* value);
			SDO_API virtual ~DASValue();
			SDO_API const char* getValue() const;

		private:
			std::string value;
		
		};
		
	}
}
#endif //_SDO_DASValue_H_
