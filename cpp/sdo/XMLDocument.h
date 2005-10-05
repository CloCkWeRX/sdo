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
#ifndef _XMLDOCUMENT_H_
#define _XMLDOCUMENT_H_

#include "export.h"

#include "DataObject.h"

namespace commonj
{
	namespace sdo
	{
		
		class XMLDocument : public RefCountingObject
		{
			
		public:
			
			SDO_API virtual ~XMLDocument();
			
			SDO_API virtual DataObjectPtr getRootDataObject() const = 0;
			SDO_API virtual const char* getRootElementURI() const = 0;
			SDO_API virtual const char* getRootElementName() const = 0;
			SDO_API virtual const char* getEncoding() const = 0;
			SDO_API virtual void setEncoding(const char* encoding) = 0;
			SDO_API virtual bool getXMLDeclaration() const = 0;
			SDO_API virtual void setXMLDeclaration(bool xmlDeclaration) = 0;
			SDO_API virtual const char* getXMLVersion() const = 0;
			SDO_API virtual void setXMLVersion(const char* xmlVersion) = 0;
			SDO_API virtual const char* getSchemaLocation() const = 0;
			SDO_API virtual void setSchemaLocation(const char* schemaLocation) = 0;
			SDO_API virtual const char* getNoNamespaceSchemaLocation() const = 0;
			SDO_API virtual void setNoNamespaceSchemaLocation(const char* noNamespaceSchemaLocation) = 0;		
			
			SDO_API friend std::istream& operator>>(std::istream& input, XMLDocument& doc);
			
		};
	} // End - namespace sdo
} // End - namespace commonj


#endif //_XMLDOCUMENT_H_
