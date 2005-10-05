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

#ifndef _XMLHELPERIMPL_H_
#define _XMLHELPERIMPL_H_

#include "XMLHelper.h"
#include "export.h"
#include "SDOXMLString.h"
#include "SAX2Namespaces.h"
#include "SchemaInfo.h"
#include "TypeDefinitions.h"

namespace commonj
{
	namespace sdo
	{
		
		
		class XMLHelperImpl : public XMLHelper
		{
		public:			
			// Constructor
			XMLHelperImpl(DataFactoryPtr dataFactory);
			
			// Destructor
			virtual ~XMLHelperImpl();
			

			// load
			// De-serializes the XML and builds a datagraph
			virtual XMLDocumentPtr loadFile(
				const char* xmlFile,
				const char* targetNamespaceURI = 0);
			virtual XMLDocumentPtr load(
				istream& inXml,
				const char* targetNamespaceURI = 0);
			virtual XMLDocumentPtr load(
				const char* inXml,
				const char* targetNamespaceURI = 0);
			
			virtual XMLDocumentPtr createDocument(
				DataObjectPtr dataObject,
				const char* rootElementURI,
				const char* rootElementName);

			// save
			// Serializes the datagraph to the XML file
			void save(XMLDocumentPtr doc, const char* xmlFile);
			void save(
				DataObjectPtr dataObject,
				const char* rootElementURI,
				const char* rootElementName,
				const char* xmlFile);

			// Serializes the datagraph to a stream
			void save(XMLDocumentPtr doc, std::ostream& outXml);
			void save(
				DataObjectPtr dataObject,
				const char* rootElementURI,
				const char* rootElementName,
				std::ostream& outXml);

			// Serializes the datagraph to a string
			char* save(XMLDocumentPtr doc);
			char* save(
				DataObjectPtr dataObject,
				const char* rootElementURI,
				const char* rootElementName);
							
		private:
			int	 parse(const char* source);
			
			// Instance variables
			DataFactoryPtr	dataFactory;
			SDOXMLString targetNamespaceURI;

			XMLDocumentPtr createDocument(DataObjectPtr dataObject);

			DataFactoryPtr getDataFactory();
		};
		
	} // End - namespace sdo
} // End - namespace commonj

#endif // _XMLHELPERIMPL_H_
