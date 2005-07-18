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

#ifndef _XMLDocumentImpl_H_
#define _XMLDocumentImpl_H_

#include "XMLDocument.h"
#include "DataObject.h"
#include "SDOXMLString.h"

namespace commonj
{
	namespace sdo
	{
		
		namespace xmldas
		{
			
			class XMLDocumentImpl : public XMLDocument
			{
				
			public:
				XMLDocumentImpl();
				
				XMLDocumentImpl(
					DataObjectPtr dataObject);

				XMLDocumentImpl(
					DataObjectPtr dataObject,
					const char* rootElementURI,
					const char* rootElementName);

				virtual ~XMLDocumentImpl();
				
				virtual DataObjectPtr getRootDataObject() const {return dataObject;}
				virtual const char* getRootElementURI() const {return rootElementURI;}
				virtual const char* getRootElementName() const {return rootElementName;}
				virtual const char* getEncoding() const {return encoding;}
				virtual void setEncoding(const char* enc);

				virtual bool getXMLDeclaration() const {return xmlDeclaration;}
				virtual void setXMLDeclaration(bool xmlDecl);

				virtual const char* getXMLVersion() const {return xmlVersion;}
				virtual void setXMLVersion(const char* xmlVer);

				virtual const char* getSchemaLocation() const {return schemaLocation;}
				virtual void setSchemaLocation(const char* schemaLoc);

				virtual const char* getNoNamespaceSchemaLocation() const { return noNamespaceSchemaLocation;}
				virtual void setNoNamespaceSchemaLocation(const char* noNamespaceSchemaLoc);
				
				
				friend std::istream& operator>>(std::istream& input, XMLDocumentImpl& doc);
			private:
				DataObjectPtr	dataObject;
				SDOXMLString	rootElementURI;
				SDOXMLString	rootElementName;
				SDOXMLString	encoding;
				bool			xmlDeclaration;
				SDOXMLString	xmlVersion;
				SDOXMLString	schemaLocation;
				SDOXMLString	noNamespaceSchemaLocation;

				
			};
		} // End - namespace xmldas
	} // End - namespace sdo
} // End - namespace commonj


#endif //_XMLDocumentImpl_H_
