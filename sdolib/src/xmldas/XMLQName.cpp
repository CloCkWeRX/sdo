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

#include "XMLQName.h"
namespace commonj
{
	namespace sdo
	{
		namespace xmldas
		{
			XMLQName::XMLQName()
			{
			}

			XMLQName::XMLQName(const SDOXMLString& sdouri)
			{
				int index = sdouri.lastIndexOf('#');
				if (index < 0)
				{
					localName = sdouri;
				}
				else
				{
					uri = sdouri.substring(0, index);
					localName = sdouri.substring(index+1);
				}
			}
					
			XMLQName::XMLQName(
				const SDOXMLString& qname, 
				const SAX2Namespaces& globalNamespaces,
				const SAX2Namespaces& localNamespaces)
			{
				SDOXMLString prefix;

				int index = qname.firstIndexOf(':');
				if (index < 0)
				{
					localName = qname;
				}
				else
				{
					prefix = qname.substring(0, index);
					localName = qname.substring(index+1);
				}
					
				const SDOXMLString* namespaceURI = localNamespaces.find(prefix);
				if (namespaceURI == 0)
				{
					namespaceURI = globalNamespaces.find(prefix);
				}
				if (namespaceURI != 0)
				{
					uri = *namespaceURI;
				}
				
			}
			
			XMLQName::~XMLQName()
			{
			}
			
			SDOXMLString XMLQName::getSDOName() const
			{
				return uri + "#" + localName;
			}
		} // End - namespace xmldas
	} // End - namespace sdo
} // End - namespace commonj
