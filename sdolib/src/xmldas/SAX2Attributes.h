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

#ifndef _SAX2ATTRIBUTES_H_
#define _SAX2ATTRIBUTES_H_

#include "SAX2Attribute.h"

#include "vector"

namespace commonj
{
	namespace sdo
	{
		namespace xmldas
		{
			
			
			class SAX2Attributes
			{
				
			public:
				
				SAX2Attributes(
					int nb_attributes,
					int nb_defaulted,
					const xmlChar **attributes);
				
				virtual ~SAX2Attributes();
				
				const SAX2Attribute& operator[] (int pos) const;
				int size() const;
				
				const SDOXMLString& getValue(
					const SDOXMLString& attributeUri,
					const SDOXMLString& attributeName) const; 

				const SDOXMLString& getValue(
					const SDOXMLString& attributeName) const; 
				
			private:
				typedef std::vector<SAX2Attribute> ATTRIBUTE_LIST;
				ATTRIBUTE_LIST	attributes;

				static const SDOXMLString nullValue;
				
				
				
			};
		} // End - namespace xmldas
	} // End - namespace sdo
} // End - namespace commonj

#endif //_SAX2ATTRIBUTES_H_
