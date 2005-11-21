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

#ifndef _SDOUTILS_H_
#define _SDOUTILS_H_

#include "commonj/sdo/DataObject.h"

namespace commonj
{
	namespace sdo
	{
		
		class SDOUtils
		{
			
		public:
			
			static SDO_API void printDataObject(DataObjectPtr d);				
			
		private:
			static void printTabs();
			static int increment;
		};
	} // End - namespace sdo
} // End - namespace commonj


#endif //_SDOUTILS_H_
