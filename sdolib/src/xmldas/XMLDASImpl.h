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

#ifndef _XMLDASIMPL_H_
#define _XMLDASIMPL_H_

#include "XMLDAS.h"
#include <SDOSPI.h>
#include "SDOXMLString.h"
#include "SAX2Namespaces.h"
#include "SchemaInfo.h"
#include "TypeDefinitions.h"
#include <set>

namespace commonj
{
	namespace sdo
	{
		
		namespace xmldas
		{
			
			class XMLDASImpl : public XMLDAS
			{
			public:
				
				// Constructor
				// Creates the XML Data Mediator and it's metadata from the named schema
				XMLDASImpl(const char* schema = 0);
				
				// Destructor
				virtual ~XMLDASImpl();
				
				///////////////////////////////////////////////////////////////////////
				// loadSchema/loadSchemaFile
				//
				// Populates the data factory with Types and Properties from the schema
				// Loads from file, stream or char* buffer
				///////////////////////////////////////////////////////////////////////
			    virtual void loadSchemaFile(const char* schemaFile);
				virtual void loadSchema(std::istream& schema);
				virtual void loadSchema(const char* schema);

				virtual char* generateSchema(
					DataObjectPtr rootDataObject,
					const char* targetNamespaceURI = "");

				virtual void generateSchema(
					DataObjectPtr rootDataObject,
					std::ostream& outXsd,
					const char* targetNamespaceURI = "");

				virtual void generateSchemaFile(
					DataObjectPtr rootDataObject,
					const char* fileName,
					const char* targetNamespaceURI = "");

				// load
				// De-serializes the XML and builds a datagraph
				virtual XMLDocumentPtr loadFile(const char* xmlFile);
				virtual XMLDocumentPtr load(istream& inXml);
				virtual XMLDocumentPtr load(const char* inXml);
				
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
				
				
				// getDataFactory
				// Returns a pointer to the DataFactory 
				virtual DataFactoryPtr getDataFactory();

				
			private:
				// Instance variables
				DASDataFactoryPtr	dataFactory;	// metadata
				XMLHelperPtr	xmlHelper;
				XSDHelperPtr	xsdHelper;
				SchemaInfo		schemaInfo;

				SDOXMLString getTargetNamespace(DataObjectPtr rootDataObject, const SDOXMLString& tns);
				void getTypes(std::set<const Type*>& types, const Type* rootType);
				TypeList getTypes(DataObjectPtr rootDataObject);
			};
			
		} // End - namespace xmldas
	} // End - namespace sdo
} // End - namespace commonj

#endif // _XMLDASIMPL_H_
