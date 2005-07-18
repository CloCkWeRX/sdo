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

#ifndef _SDOXSDSTREAMWRITER_H_
#define _SDOXSDSTREAMWRITER_H_

#include "SDOXSDBufferWriter.h"
#include <iostream>

namespace commonj
{
	namespace sdo
	{
		namespace xmldas
		{
			
			class SDOXSDStreamWriter : public SDOXSDBufferWriter
			{
				
			public:
				
				SDOXSDStreamWriter(std::ostream& outXML);				
				virtual ~SDOXSDStreamWriter();
				
				int write(const TypeList& types, const SDOXMLString& targetNamespaceURI);
			private:
				std::ostream& outXmlStream;
				
			};
		} // End - namespace xmldas
	} // End - namespace sdo
} // End - namespace commonj


#endif //_SDOXSDSTREAMWRITER_H_
