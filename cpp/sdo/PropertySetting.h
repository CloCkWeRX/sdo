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

#ifndef _PROPERTYSETTING_H_
#define _PROPERTYSETTING_H_

#include "SDOXMLString.h"
#include "DataObject.h"

namespace commonj
{
	namespace sdo
	{
		
		
		class PropertySetting
		{
			
		public:
			PropertySetting();
			PropertySetting(DataObjectPtr dataObj, 
				const SDOXMLString& propertyName,
				bool isIDREF=false);
			virtual ~PropertySetting();
			
			SDOXMLString name;	
			SDOXMLString value;
			DataObjectPtr dataObject;
			bool isIDREF;
						
		};
	} // End - namespace sdo
} // End - namespace commonj


#endif //_PROPERTYSETTING_H_
