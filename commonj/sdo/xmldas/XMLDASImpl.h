/*
 *
 *  Copyright 2005 International Business Machines Corporation
 *
 *  Licensed under the Apache License, Version 2.0 (the "License");
 *  you may not use this file except in compliance with the License.
 *  You may obtain a copy of the License at
 *
 *     http://www.apache.org/licenses/LICENSE-2.0
 *
 *  Unless required by applicable law or agreed to in writing, software
 *  distributed under the License is distributed on an "AS IS" BASIS,
 *  WITHOUT WARRANTIES OR CONDITIONS OF ANY KIND, either express or implied.
 *  See the License for the specific language governing permissions and
 *  limitations under the License.
 */


#ifndef _XMLDASIMPL_H_
#define _XMLDASIMPL_H_

#include "commonj/sdo/xmldas/XMLDAS.h"
#include "commonj/sdo/SDO.h"
#include "commonj/sdo/SDOXMLString.h"
#include "commonj/sdo/SAX2Namespaces.h"
#include "commonj/sdo/SchemaInfo.h"
#include "commonj/sdo/TypeDefinitions.h"
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
                virtual DataGraphPtr loadGraphFromFile(const char* xmlFile);
                virtual DataGraphPtr loadGraph(istream& inXml);
                virtual DataGraphPtr loadGraph(const char* inXml);
                virtual XMLDocumentPtr loadFile(const char* xmlFile);
                virtual XMLDocumentPtr load(istream& inXml);
                virtual XMLDocumentPtr load(const char* inXml);
                
                virtual XMLDocumentPtr createDocument(
                    DataObjectPtr dataObject,
                    const char* rootElementURI,
                    const char* rootElementName);

                // save
                // Serializes the datagraph to the XML file
                void save(DataGraphPtr doc, const char* xmlFile, int indent  = -1);
                void save(XMLDocumentPtr doc, const char* xmlFile, int indent  = -1);
                void save(
                    DataObjectPtr dataObject,
                    const char* rootElementURI,
                    const char* rootElementName,
                    const char* xmlFile, int indent  = -1);

                // Serializes the datagraph to a stream
                void save(DataGraphPtr doc, std::ostream& outXml, int indent  = -1);
                void save(XMLDocumentPtr doc, std::ostream& outXml, int indent  = -1);
                void save(
                    DataObjectPtr dataObject,
                    const char* rootElementURI,
                    const char* rootElementName,
                    std::ostream& outXml, int indent  = -1);

                // Serializes the datagraph to a string
                char* save(DataGraphPtr doc, int indent  = -1);
                char* save(XMLDocumentPtr doc, int indent  = -1);
                char* save(
                    DataObjectPtr dataObject,
                    const char* rootElementURI,
                    const char* rootElementName, int indent  = -1);
                
                
                // getDataFactory
                // Returns a pointer to the DataFactory 
                virtual DataFactoryPtr getDataFactory();

                
            private:

                DataGraphPtr docToGraph(XMLDocumentPtr doc);
                DataObjectPtr graphToDataObject(DataGraphPtr doc);
                // Instance variables
                DataFactoryPtr    dataFactory;    // metadata
                XMLHelperPtr    xmlHelper;
                XSDHelperPtr    xsdHelper;
                //SchemaInfo        schemaInfo;
                SDOXMLString targetNamespaceURI;

                SDOXMLString getTargetNamespace(DataObjectPtr rootDataObject, const SDOXMLString& tns);
                void getTypes(std::set<const Type*>& types, const Type* rootType);
                TypeList getTypes(DataObjectPtr rootDataObject);
            };
            
        } // End - namespace xmldas
    } // End - namespace sdo
} // End - namespace commonj

#endif // _XMLDASIMPL_H_
