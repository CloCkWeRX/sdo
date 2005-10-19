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

#ifndef _PROPERTYDEFINITION_H_
#define _PROPERTYDEFINITION_H_

#include "commonj/sdo/SDOXMLString.h"

namespace commonj
{
	namespace sdo
	{
		
		
		class PropertyDefinition
		{
			
		public:
			PropertyDefinition();
			virtual ~PropertyDefinition();
			
			//SDOXMLString uri;
			SDOXMLString name;
			SDOXMLString localname;
			
			SDOXMLString typeUri;
			SDOXMLString typeName;
			SDOXMLString fullTypeName;

			SDOXMLString fullLocalTypeName;

			SDOXMLString defaultValue;

			bool isMany;
			bool isContainment;
			bool isReadOnly;

			bool isID;
			bool isIDREF;
			bool isReference;
			bool isElement;
						
			bool isQName;
		};
	} // End - namespace sdo
} // End - namespace commonj


#endif //_PROPERTYDEFINITION_H_
