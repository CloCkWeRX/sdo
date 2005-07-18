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

#ifndef _SAX2NAMESPACES_H_
#define _SAX2NAMESPACES_H_
#pragma warning(disable: 4786)
#include "SDOXMLString.h"
#include "map"

namespace commonj
{
	namespace sdo
	{
		namespace xmldas
		{
			
			
			class SAX2Namespaces
			{
				
			public:
				
				SAX2Namespaces();
				
				SAX2Namespaces(int nb_namespaces, const xmlChar** namespaces);
				
				virtual ~SAX2Namespaces();

				void add(const SDOXMLString& prefix, const SDOXMLString& uri);
				
				const SDOXMLString* SAX2Namespaces::find(const SDOXMLString& prefix) const;
				const SDOXMLString* SAX2Namespaces::findPrefix(const SDOXMLString& uri) const;

				void empty();
				
			private:
				typedef std::map<SDOXMLString, SDOXMLString> NAMESPACE_MAP;
				NAMESPACE_MAP	namespaceMap;
				
				
				
			};
		} // End - namespace xmldas
	} // End - namespace sdo
} // End - namespace commonj

#endif //_SAX2NAMESPACES_H_
