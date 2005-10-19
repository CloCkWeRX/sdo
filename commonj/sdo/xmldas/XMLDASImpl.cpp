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

// XMLDASImpl.cpp: implementation of the XMLDASImpl class.
//
//////////////////////////////////////////////////////////////////////
#pragma warning(disable: 4786)
#include "commonj/sdo/SDOXMLFileWriter.h"   // Include first to avoid libxml compile problems!
#include "commonj/sdo/SDOXMLStreamWriter.h" // Include first to avoid libxml compile problems!
#include "commonj/sdo/SDOXMLBufferWriter.h" // Include first to avoid libxml compile problems!
#include "commonj/sdo/xmldas/XMLDASImpl.h"
#include "commonj/sdo/XMLDocumentImpl.h"
#include <iostream>
#include <fstream>
#include <sstream>
#include "commonj/sdo/SDOSchemaSAX2Parser.h"
#include "commonj/sdo/SDOSAX2Parser.h"
#include "commonj/sdo/XSDPropertyInfo.h"
#include "commonj/sdo/XSDTypeInfo.h"
#include "commonj/sdo/HelperProvider.h"
#include "commonj/sdo/DataGraphImpl.h"
#include "commonj/sdo/DataObjectImpl.h"

using std::set;


namespace commonj
{
	namespace sdo
	{
		
		namespace xmldas
		{
			//////////////////////////////////////////////////////////////////////
			// Construction/Destruction
			//////////////////////////////////////////////////////////////////////
			
			XMLDASImpl::XMLDASImpl(const char* schema)
			{
				dataFactory = DataFactory::getDataFactory();
				xsdHelper = HelperProvider::getXSDHelper((DataFactory*)dataFactory);
				xmlHelper = HelperProvider::getXMLHelper((DataFactory*)dataFactory);
				if (schema)
				{
					loadSchemaFile(schema);
				}
			}
			
			XMLDASImpl::~XMLDASImpl()
			{
			}
			
			void XMLDASImpl::loadSchemaFile(const char* schema)
			{
				targetNamespaceURI = xsdHelper->defineFile(schema);
				//schemaInfo.setTargetNamespaceURI(xsdHelper->defineFile(schema));
			}
			
			void XMLDASImpl::loadSchema(istream& schema)
			{
				targetNamespaceURI = xsdHelper->define(schema);
				//schemaInfo.setTargetNamespaceURI(xsdHelper->define(schema));
			}
			
			void XMLDASImpl::loadSchema(const char* schema)
			{
				istringstream str(schema);
                loadSchema(str);
			}
						
			char* XMLDASImpl::generateSchema(
				DataObjectPtr rootDataObject,
				const char* targetNamespaceURI)
			{
				return xsdHelper->generate(
					getTypes(rootDataObject), 
					getTargetNamespace(rootDataObject, targetNamespaceURI));
			}
			
			void XMLDASImpl::generateSchema(
				DataObjectPtr rootDataObject,
				std::ostream& outXsd,
				const char* targetNamespaceURI)
			{
				xsdHelper->generate(
					getTypes(rootDataObject),
					outXsd,
					getTargetNamespace(rootDataObject, targetNamespaceURI));
			}
			
			void XMLDASImpl::generateSchemaFile(
				DataObjectPtr rootDataObject,
				const char* fileName,
				const char* targetNamespaceURI)
			{
				xsdHelper->generateFile(
					getTypes(rootDataObject),
					fileName,
					getTargetNamespace(rootDataObject, targetNamespaceURI));
			}
			
			void XMLDASImpl::getTypes(set<const Type*>& types, const Type* rootType)
			{
				// Add the root type
				types.insert(rootType);
				if (rootType->getBaseType())
				{
					getTypes(types, rootType->getBaseType());
				}
				
				//////////////////////////////////////////////////////////////////////////
				// Iterate over all the properties
				//////////////////////////////////////////////////////////////////////////
				PropertyList pl = rootType->getProperties();
				for (int i = 0; i < pl.size(); i++)
				{
					getTypes(types, &pl[i].getType());
				}
			}
			
			TypeList XMLDASImpl::getTypes(DataObjectPtr rootDataObject)
			{
				set<const Type*> typeSet;

				getTypes(typeSet, &rootDataObject->getType());
				
				TypeList types;

				set<const Type*>::const_iterator iter;
				for (iter=typeSet.begin(); iter!=typeSet.end(); iter++)
				{
					types.insert(*iter);
				}

				return types;
			}
			
			SDOXMLString XMLDASImpl::getTargetNamespace(DataObjectPtr rootDataObject, const SDOXMLString& tns)
			{
				if (tns.isNull() || tns.equals(""))
				{
					if (rootDataObject)
					{
						return rootDataObject->getType().getURI();
					}
				}
				
				return tns;
			}
			
			
			XMLDocumentPtr XMLDASImpl::createDocument(
				DataObjectPtr dataObject,
				const char* rootElementURI,
				const char* rootElementName)
			{
				return xmlHelper->createDocument(dataObject, rootElementURI, rootElementName);
			}
			
			DataGraphPtr XMLDASImpl::loadGraphFromFile(const char* xmlFile)
			{
				XMLDocumentPtr xd =  xmlHelper->loadFile(xmlFile, targetNamespaceURI/*schemaInfo.getTargetNamespaceURI()*/);
				return docToGraph(xd);
			}

			XMLDocumentPtr XMLDASImpl::loadFile(const char* xmlFile)
			{
				return xmlHelper->loadFile(xmlFile, targetNamespaceURI/*schemaInfo.getTargetNamespaceURI()*/);
			}
			
			DataGraphPtr XMLDASImpl::loadGraph(istream& inXml)
			{
				XMLDocumentPtr xd = xmlHelper->load(inXml, targetNamespaceURI/*schemaInfo.getTargetNamespaceURI()*/);
				return docToGraph(xd);
			}
			XMLDocumentPtr XMLDASImpl::load(istream& inXml)
			{
				return xmlHelper->load(inXml, targetNamespaceURI/*schemaInfo.getTargetNamespaceURI()*/);
			}
			
			DataGraphPtr XMLDASImpl::loadGraph(const char* inXml)
			{
				istringstream str(inXml);
				return loadGraph(str);
			}

			XMLDocumentPtr XMLDASImpl::load(const char* inXml)
			{
				istringstream str(inXml);
				return load(str);
			}
			
			void XMLDASImpl::save(DataGraphPtr doc, const char* xmlFile)
			{
				DataObjectPtr xd = graphToDataObject(doc);
				// write the datagraph tag
				// write the model
				// write the dataobjects
				xmlHelper->save(xd, 0,0, xmlFile);
				// end the tag
			}

			void XMLDASImpl::save(XMLDocumentPtr doc, const char* xmlFile)
			{
				xmlHelper->save(doc, xmlFile);
			}
			
			void XMLDASImpl::save(
				DataObjectPtr dataObject,
				const char* rootElementURI,
				const char* rootElementName,
				const char* xmlFile)
			{
				xmlHelper->save(dataObject,rootElementURI, rootElementName, xmlFile);
			}
			
			
			// Serializes the datagraph to a stream
			void XMLDASImpl::save(DataGraphPtr doc, ostream& outXml)
			{
				DataObjectPtr xd = graphToDataObject(doc);
				// write the datagraph tag
				// write the model
				// write the dataobjects
				xmlHelper->save(xd, 0,0,outXml);	
				// end the tag
			}

			void XMLDASImpl::save(XMLDocumentPtr doc, ostream& outXml)
			{
				xmlHelper->save(doc, outXml);	
			}
			
			void XMLDASImpl::save(
				DataObjectPtr dataObject,
				const char* rootElementURI,
				const char* rootElementName,
				std::ostream& outXml)
			{
				xmlHelper->save(dataObject, rootElementURI, rootElementName, outXml);
			}
			
			// Serializes the datagraph to a string
			char* XMLDASImpl::save(DataGraphPtr doc)
			{
				DataObjectPtr xd = graphToDataObject(doc);
				// write the datagraph tag
				// write the model
				// write the dataobjects
				return xmlHelper->save(xd,0,0);
				// end the tag
			}

			char* XMLDASImpl::save(XMLDocumentPtr doc)
			{
				return xmlHelper->save(doc);
			}
			
			char* XMLDASImpl::save(
				DataObjectPtr dataObject,
				const char* rootElementURI,
				const char* rootElementName)
			{
				return xmlHelper->save(dataObject, rootElementURI, rootElementName);
			}

			
			
			DataFactoryPtr XMLDASImpl::getDataFactory()
			{
				return dataFactory;
			}

			DataGraphPtr XMLDASImpl::docToGraph(XMLDocumentPtr doc)
			{
				DataObjectPtr dob = doc->getRootDataObject();
				DataObject* dobptr = (DataObject*)dob;
				DataFactoryPtr fac = ((DataObjectImpl*)dobptr)->getDataFactory();
				DataGraphPtr dg = new DataGraphImpl(fac);
				dg->setRootObject(dob);
				return dg;
			}

			DataObjectPtr XMLDASImpl::graphToDataObject(DataGraphPtr doc)
			{
				DataObjectPtr r =  doc->getRootObject();
				if (!r) return 0;
				return r->getDataObject((unsigned int)0); // get the only prop.
			}
			
			} // End - namespace xmldas
	} // End - namespace sdo
} // End - namespace commonj
