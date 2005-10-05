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

#ifndef _XMLDAS_H_
#define _XMLDAS_H_
#include "XMLDASExport.h"
#include "XMLDocument.h"
#include "DataFactory.h"

namespace commonj
{
	namespace sdo
	{
		
		namespace xmldas
		{
			///////////////////////////////////////////////////////////////////////////
			// XMLDAS
			//
			// An XML Data Mediator Service
			///////////////////////////////////////////////////////////////////////////
			
			class XMLDAS
			{
			public:
				///////////////////////////////////////////////////////////////////////
				// create
				//
				// Creates an instance of the XML Data Mediator.
				//
				// A DataFactory is created and populated with the Types from the
				// specified schema. A pointer to the XMLDAS is returned. It is
				// the callers responsibility to delete the returned XMLDAS.
				///////////////////////////////////////////////////////////////////////
				XMLDAS_API static XMLDAS* create(const char* schema = 0);
				
				///////////////////////////////////////////////////////////////////////
				// loadSchema/loadSchemaFile
				//
				// Populates the data factory with Types and Properties from the schema
				// Loads from file, stream or char* buffer
				///////////////////////////////////////////////////////////////////////
				XMLDAS_API virtual void loadSchemaFile(const char* schemaFile) = 0;
				XMLDAS_API virtual void loadSchema(std::istream& schema) = 0;
				XMLDAS_API virtual void loadSchema(const char* schema) = 0;
				
				
				XMLDAS_API virtual char* generateSchema(
					DataObjectPtr rootDataObject,
					const char* targetNamespaceURI = "") = 0;

				XMLDAS_API virtual void generateSchema(
					DataObjectPtr rootDataObject,
					std::ostream& outXsd,
					const char* targetNamespaceURI = "") = 0;
				
				XMLDAS_API virtual void generateSchemaFile(
					DataObjectPtr rootDataObject,
					const char* fileName,
					const char* targetNamespaceURI = "") = 0;


				///////////////////////////////////////////////////////////////////////
				// load
				//
				// De-serializes the specified XML file building a graph of DataObjects.
				// Returns a pointer to the root data object
				///////////////////////////////////////////////////////////////////////
				XMLDAS_API virtual DataGraphPtr loadGraphFromFile(const char* xmlFile) = 0;
				XMLDAS_API virtual DataGraphPtr loadGraph(std::istream& inXml) = 0;
				XMLDAS_API virtual DataGraphPtr loadGraph(const char* inXml) = 0;
				XMLDAS_API virtual XMLDocumentPtr loadFile(const char* xmlFile) = 0;
				XMLDAS_API virtual XMLDocumentPtr load(std::istream& inXml) = 0;
				XMLDAS_API virtual XMLDocumentPtr load(const char* inXml) = 0;
				
				///////////////////////////////////////////////////////////////////////
				// save - Serializes the datagraph to the XML file
				///////////////////////////////////////////////////////////////////////
				XMLDAS_API virtual void	save(DataGraphPtr doc, const char* xmlFile) = 0;				
				XMLDAS_API virtual void	save(XMLDocumentPtr doc, const char* xmlFile) = 0;				
				XMLDAS_API virtual void save(
					DataObjectPtr dataObject,
					const char* rootElementURI,
					const char* rootElementName,
					const char* xmlFile) = 0;
				
				
				///////////////////////////////////////////////////////////////////////
				// save - Serializes the datagraph to a stream
				///////////////////////////////////////////////////////////////////////
				XMLDAS_API virtual void save(DataGraphPtr doc, std::ostream& outXml) = 0;
				XMLDAS_API virtual void save(XMLDocumentPtr doc, std::ostream& outXml) = 0;
				XMLDAS_API virtual void save(
					DataObjectPtr dataObject,
					const char* rootElementURI,
					const char* rootElementName,
					std::ostream& outXml) = 0;
				
				///////////////////////////////////////////////////////////////////////
				// save - Serializes the datagraph to a string
				///////////////////////////////////////////////////////////////////////
				XMLDAS_API virtual char* save(DataGraphPtr doc) = 0;
				XMLDAS_API virtual char* save(XMLDocumentPtr doc) = 0;
				XMLDAS_API virtual char* save(
					DataObjectPtr dataObject,
					const char* rootElementURI,
					const char* rootElementName) = 0;
				
				///////////////////////////////////////////////////////////////////////
				// createDocument 
				///////////////////////////////////////////////////////////////////////
				XMLDAS_API virtual XMLDocumentPtr createDocument(
					DataObjectPtr dataObject,
					const char* rootElementURI,
					const char* rootElementName) = 0;
				
				
				///////////////////////////////////////////////////////////////////////
				// getDataFactory
				//
				// Returns the DataFactory used by this XML Data Access Service
				///////////////////////////////////////////////////////////////////////
				XMLDAS_API virtual DataFactoryPtr getDataFactory() = 0;
				
				///////////////////////////////////////////////////////////////////////
				// Destructor
				///////////////////////////////////////////////////////////////////////
				XMLDAS_API virtual ~XMLDAS();
				
			};
		} // End - namespace xmldas
	} // End - namespace sdo
} // End - namespace commonj

#endif //_XMLDAS_H_
