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

// XSDHelperImpl.cpp: implementation of the XSDHelperImpl class.
//
//////////////////////////////////////////////////////////////////////
#pragma warning(disable: 4786)
#include "SDOXMLFileWriter.h"   // Include first to avoid libxml compile problems!
#include "SDOXMLStreamWriter.h" // Include first to avoid libxml compile problems!
#include "SDOXMLBufferWriter.h" // Include first to avoid libxml compile problems!
#include "SDOXSDFileWriter.h"
#include "SDOXSDStreamWriter.h"
#include "SDOXSDBufferWriter.h"
#include "XSDHelperImpl.h"
#include "XMLDocumentImpl.h"
#include <iostream>
#include <fstream>
#include <sstream>
#include "SDOSchemaSAX2Parser.h"
#include "SDOSAX2Parser.h"
#include "XSDPropertyInfo.h"
#include "XSDTypeInfo.h"

namespace commonj
{
	namespace sdo
	{
		
		namespace xmldas
		{
			//////////////////////////////////////////////////////////////////////
			// Construction/Destruction
			//////////////////////////////////////////////////////////////////////			
			XSDHelperImpl::XSDHelperImpl(DataFactoryPtr df)
			{
				dataFactory = (DASDataFactory*)(DataFactory*)df;
			}
			
			XSDHelperImpl::~XSDHelperImpl()
			{
			}
			
			//////////////////////////////////////////////////////////////////////
			// load schema and define the Types
			//////////////////////////////////////////////////////////////////////			
			const char* XSDHelperImpl::defineFile(const char* schema)
			{
				SDOSchemaSAX2Parser schemaParser(schemaInfo);
				schemaParser.parse(schema);
				defineTypes(schemaParser.getTypeDefinitions());
				return schemaInfo.getTargetNamespaceURI();
			}
			
			const char*  XSDHelperImpl::define(istream& schema)
			{
				SDOSchemaSAX2Parser schemaParser(schemaInfo);
				schema >> schemaParser;
				defineTypes(schemaParser.getTypeDefinitions());
				return schemaInfo.getTargetNamespaceURI();
			}
			
			const char*  XSDHelperImpl::define(const char* schema)
			{
				istringstream str(schema);
                return define(str);
			}
			
			
			//////////////////////////////////////////////////////////////////////
			// defineTypes - add Types and Properties to the DataFactory
			//////////////////////////////////////////////////////////////////////			
			void XSDHelperImpl::defineTypes(TypeDefinitions& typedefs) 
			{
				if (!dataFactory) 
				{
					dataFactory = DASDataFactory::getDataFactory();
				}
				
				XMLDAS_TypeDefs types = typedefs.types;
				XMLDAS_TypeDefs::iterator iter;
				for (iter=types.begin(); iter != types.end(); iter++)
				{
					TypeDefinition ty = iter->second;
					try
					{
						
						dataFactory->addType(ty.uri, ty.name, ty.isSequenced, ty.isOpen);
						dataFactory->setDASValue(
							ty.uri, ty.name,
							"XMLDAS::TypeInfo",
							new XSDTypeInfo(ty));
					}		
					catch (SDORuntimeException& e)
					{
						SDO_RETHROW_EXCEPTION("defineTypes", e);
					}
				}
				for (iter=types.begin(); iter != types.end(); iter++)
				{
					TypeDefinition ty = iter->second;
					if (!ty.parentTypeName.isNull())
					{
						try 
						{
							dataFactory->setBaseType(
								ty.uri,
								ty.name,
								ty.parentTypeUri,
								ty.parentTypeName);
						}		
						catch (SDORuntimeException& e)
						{
							SDO_RETHROW_EXCEPTION("defineTypes", e);
						}						
					}
					
					XmlDasPropertyDefs::iterator propsIter;
					for (propsIter = ty.properties.begin(); propsIter != ty.properties.end(); propsIter++)
					{
						PropertyDefinition prop = *propsIter;
						
						// For a refence we need to determine the type from the
						// global element declaration
						if(prop.isReference)
						{
							//prop.name = prop.typeName;
							
							XMLDAS_TypeDefs::iterator refTypeIter = 
								types.find(TypeDefinitions::getTypeQName(prop.typeUri, "RootType"));
							if(refTypeIter != types.end())
							{
								TypeDefinition rootTy = iter->second;
								
								// find the property on the root type
								XmlDasPropertyDefs::iterator refPropsIter;
								for (refPropsIter = rootTy.properties.begin(); refPropsIter != rootTy.properties.end(); refPropsIter++)
								{
									if (refPropsIter->name.equals(prop.name))
									{
										prop.typeName = refPropsIter->typeUri;
										prop.typeName = refPropsIter->typeName;
									}
								}
							}
							else
							{
								// Check if this type is already defined to the data factory
								try
								{
									const Type& rootType = dataFactory->getType(prop.typeUri, "RootType");
									const Type& refPropType = rootType.getProperty((const char*)prop.typeName).getType();
									prop.name = prop.typeName;
									prop.typeUri = refPropType.getURI();
									prop.typeName = refPropType.getName();
								}
								catch (const SDORuntimeException&)
								{
								}
							}
						}
						
						if (prop.name.isNull())
						{
							continue;
						}

						XMLDAS_TypeDefs::iterator propTypeIter = 
							types.find(TypeDefinitions::getTypeQName(prop.typeUri, prop.typeName));
						if(propTypeIter != types.end())
						{
							prop.typeName = propTypeIter->second.name;
						}

						try 
						{
							dataFactory->addPropertyToType(ty.uri, ty.name,
								prop.name,
								prop.typeUri,
								prop.typeName,
								prop.isMany,
								prop.isReadOnly,
								prop.isContainment);
							
							// Do not add DASValue to ChangeSummary
							if (!(prop.typeUri.equals(Type::SDOTypeNamespaceURI)
								&& prop.typeName.equals("ChangeSummary")))
							{
								dataFactory->setDASValue(
									ty.uri, ty.name,
									prop.name,
									"XMLDAS::PropertyInfo",
									new XSDPropertyInfo(prop));
							}
						}
						catch (SDORuntimeException& e)
						{
							SDO_RETHROW_EXCEPTION("defineTypes", e);
						}
					}
					
				}
				
			} // End - defineTypes
			
			//////////////////////////////////////////////////////////////////////
			// getDataFactory - return the DataFactory
			//////////////////////////////////////////////////////////////////////			
			DataFactoryPtr XSDHelperImpl::getDataFactory()
			{
				return dataFactory;
			}
			
			
			//////////////////////////////////////////////////////////////////////
			// generate - create an XSD from Types and Properties
			//////////////////////////////////////////////////////////////////////			
			void XSDHelperImpl::generateFile(
				const TypeList& types,
				const char* fileName, 
				const char* targetNamespaceURI)
			{
				SDOXSDFileWriter writer(fileName);
				writer.write(types, targetNamespaceURI);
			}
			
			void XSDHelperImpl::generate(
				const TypeList& types,
				std::ostream& outXml,
				const char* targetNamespaceURI)
			{
				SDOXSDStreamWriter writer(outXml);
				writer.write(types, targetNamespaceURI);				
			}
			
			char* XSDHelperImpl::generate(
				const TypeList& types,
				const char* targetNamespaceURI)
			{
				SDOXSDBufferWriter writer;
				writer.write(types, targetNamespaceURI);
				SDOXMLString ret = writer.getBuffer();
				char* retString = new char[strlen(ret) +1];
				strcpy(retString, ret);
				return retString;
			}
			
		} // End - namespace xmldas
	} // End - namespace sdo
} // End - namespace commonj
