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

#include "SDOXSDWriter.h"
#include "iostream"
using namespace::std;
#include "DASProperty.h"
#include "XSDPropertyInfo.h"
#include "SDORuntimeException.h"

namespace commonj
{
	namespace sdo
	{
		
		namespace xmldas
		{
			
			
			
			SDOXSDWriter::SDOXSDWriter(
				DASDataFactoryPtr dataFact)
				: dataFactory(dataFact)
			{
				
			}
			
			SDOXSDWriter::~SDOXSDWriter()
			{
				freeWriter();
			}
			
			void SDOXSDWriter::setWriter(xmlTextWriterPtr textWriter)
			{
				writer = textWriter;
			}
			
			void SDOXSDWriter::freeWriter()
			{
				if (writer != NULL)
				{
					xmlFreeTextWriter(writer);
					writer = NULL;
				}
			}
			
			int SDOXSDWriter::write(const TypeList& types, const SDOXMLString& targetNamespaceURI)
			{
				if (writer == NULL)
				{
					// Throw exception
					return -1;
				}
				
				int rc = 0;
				
				
				// --------------------------------------------------------------
				// Write the <schema> definition including namespace information
				// --------------------------------------------------------------
				SDOXMLString tns;
				SDOXMLString xsd("xsd");
				SDOXMLString sdoURI(Type::SDOTypeNamespaceURI);
				
				SDOXMLString schemaURI = "http://www.w3.org/2001/XMLSchema";
				rc = xmlTextWriterStartElementNS(writer, xsd, SDOXMLString("schema"), schemaURI);
				if (rc < 0) {
					SDO_THROW_EXCEPTION("write", SDOXMLParserException, "xmlTextWriterStartElementNS failed");
				}				
				
				
				rc = xmlTextWriterWriteAttribute(writer, 
					SDOXMLString("xmlns:sdo"),
					sdoURI);

				rc = xmlTextWriterWriteAttribute(writer, 
					SDOXMLString("xmlns:sdoxml"),
					sdoURI + "/xml");
				
				
				// --------------------------------------------------------------
				// Add targetNamespace if necessary
				// --------------------------------------------------------------
				if (!targetNamespaceURI.isNull() && !targetNamespaceURI.equals(""))
				{
					tns = "tns:";
					rc = xmlTextWriterWriteAttribute(writer,
						SDOXMLString("xmlns:tns"),
						targetNamespaceURI);
					
					rc = xmlTextWriterWriteAttribute(writer, 
						SDOXMLString("targetNamespace"),
						targetNamespaceURI);
				}
				
				// --------------------------------------------------------------
				// Now iterate through the Types creating elements as appropriate
				// --------------------------------------------------------------
				for (int i = 0; i<types.size(); i++)
				{
					const Type& type = types[i];
					const Type* baseType = type.getBaseType();
					
					// Do not define SDO DataTypes
					SDOXMLString uri = type.getURI();
					if (uri.equals(Type::SDOTypeNamespaceURI))
						continue;
					
					// Do not define the RootType
					SDOXMLString name = type.getName();
					if (uri.equals(targetNamespaceURI) && name.equals("RootType"))
						continue;
					
					
					// --------------------------------------------------------------
					// DataTypes become simpleType definitions
					// --------------------------------------------------------------
					if (type.isDataType())
					{
						rc = xmlTextWriterStartElementNS(writer, xsd, SDOXMLString("simpleType"), NULL);
						if (rc < 0) {
							SDO_THROW_EXCEPTION("write", SDOXMLParserException, "xmlTextWriterStartElementNS failed");
						}	
						rc = xmlTextWriterWriteAttribute(writer, 
							SDOXMLString("name"),
							name);
						if (baseType != 0)
						{
							rc = xmlTextWriterStartElementNS(writer, xsd, SDOXMLString("restriction"), NULL);
							
							SDOXMLString baseName = resolveName(baseType->getURI(), baseType->getName(), targetNamespaceURI);
							rc = xmlTextWriterWriteAttribute(writer, 
								SDOXMLString("base"),
								baseName);
							
							
							rc = xmlTextWriterEndElement(writer);
						}
						rc = xmlTextWriterEndElement(writer);
						
					}
					else
						// ---------------------------------------------------------------------
						// DataObject Types become ComplexType definitions with a global elament
						// ---------------------------------------------------------------------
					{	
						rc = xmlTextWriterStartElementNS(writer,xsd, SDOXMLString("element"), NULL);
						if (rc < 0) {
							SDO_THROW_EXCEPTION("write", SDOXMLParserException, "xmlTextWriterStartElementNS failed");
						}	
						
						SDOXMLString typeName = tns + name;
						
						rc = xmlTextWriterWriteAttribute(writer, 
							SDOXMLString("name"),
							name.toLower(0,1));
						
						rc = xmlTextWriterWriteAttribute(writer, 
							SDOXMLString("type"),
							typeName);
						
						rc = xmlTextWriterEndElement(writer);
						
						rc = xmlTextWriterStartElementNS(writer, xsd, SDOXMLString("complexType"), NULL);
						
						rc = xmlTextWriterWriteAttribute(writer, 
							SDOXMLString("name"),
							name);
						
						// -----------------------------------------------------
						// If there is a base type then we need <complexContent>
						// -----------------------------------------------------
						if (baseType != 0)
						{
							rc = xmlTextWriterStartElementNS(writer, xsd, SDOXMLString("complexContent"), NULL);
							rc = xmlTextWriterStartElementNS(writer, xsd, SDOXMLString("extension"), NULL);
							
							SDOXMLString baseName = resolveName(baseType->getURI(), baseType->getName(), targetNamespaceURI);
							rc = xmlTextWriterWriteAttribute(writer, 
								SDOXMLString("base"),
								baseName);
							
							// End extension
							rc = xmlTextWriterEndElement(writer);
						}
						
						// ---------------------------
						// Iterate over the properties
						// ---------------------------
						PropertyList pl = type.getProperties();
						if (pl.size() != 0)
						{
							bool inSequence = false;
							
							// -------------------------------------------------------							
							// Create <element> definitions 
							// -------------------------------------------------------
							
							int j;
							for (j = 0; j < pl.size(); j++)
							{
								const Property& prop = pl[j];
								const Type& propType = prop.getType();
								SDOXMLString propTypeName = resolveName(propType.getURI(), propType.getName(), targetNamespaceURI);
								
								bool isElement = false;
								bool isQName = false; 
								XSDPropertyInfo* pi = (XSDPropertyInfo*)
									((DASProperty*)&prop)->getDASValue("XMLDAS::PropertyInfo");
								if (pi)
								{
									PropertyDefinition propdef = pi->getPropertyDefinition();
									if (propdef.isElement)
										isElement=true;
									if (propdef.isQName)
										isQName=true;
								}
								
								if (prop.isMany()
									|| prop.isContainment()
									|| isElement)
								{
									// -------------------------------------------------
									// Write <sequence> or <choice> before first element
									// -------------------------------------------------
									if (!inSequence)
									{
										if (type.isSequencedType())
										{
											rc = xmlTextWriterWriteAttribute(writer, 
												SDOXMLString("mixed"),
												SDOXMLString("true"));
											
											rc = xmlTextWriterStartElementNS(writer, xsd, SDOXMLString("choice"), NULL);
											rc = xmlTextWriterWriteAttribute(writer, 
												SDOXMLString("maxOccurs"),
												SDOXMLString("unbounded"));
										}
										else 
										{
											rc = xmlTextWriterStartElementNS(writer, xsd, SDOXMLString("sequence"), NULL);
										}
										inSequence = true;
									}
									// -----------------------------
									// Write an <element> definition
									// -----------------------------
									rc = xmlTextWriterStartElementNS(writer, xsd, SDOXMLString("element"), NULL);
									
									rc = xmlTextWriterWriteAttribute(writer, 
										SDOXMLString("name"),
										SDOXMLString(prop.getName()));
									
									if (!propType.isDataType() && !prop.isContainment())
									{
										rc = xmlTextWriterWriteAttribute(writer, 
											SDOXMLString("sdoxml:propertyType"),
											propTypeName);
										propTypeName = "xsd:anyURI";
									}

									if (isQName)
									{
										propTypeName = "xsd:QName";
									}
									
									rc = xmlTextWriterWriteAttribute(writer, 
										SDOXMLString("type"),
										propTypeName);
									
									rc = xmlTextWriterWriteAttribute(writer, 
										SDOXMLString("minOccurs"),
										SDOXMLString("0"));
									
									if (prop.isMany())
									{
										rc = xmlTextWriterWriteAttribute(writer, 
											SDOXMLString("maxOccurs"),
											SDOXMLString("unbounded"));
									}
									
									// End element
									rc = xmlTextWriterEndElement(writer);
								}								
							}
														
							
							// -----------------------------------------------------
							// End <sequence> / <choice> if necessary
							// -----------------------------------------------------
							if (inSequence)
							{
								// End sequence
								rc = xmlTextWriterEndElement(writer);
							}

							// ---------------------------------
							// Add ChangeSummary if there is one
							// ---------------------------------
							if (type.isChangeSummaryType())
							{
								rc = xmlTextWriterStartElementNS(writer, xsd, SDOXMLString("element"), NULL);
									
								rc = xmlTextWriterWriteAttribute(writer, 
									SDOXMLString("name"),
									SDOXMLString("changeSummary"));
									
								
								rc = xmlTextWriterWriteAttribute(writer, 
									SDOXMLString("type"),
									SDOXMLString("sdo:ChangeSummaryType"));
								
								rc = xmlTextWriterEndElement(writer);
							}
							
							// -------------------------------------------------------							
							// Create <element> definitions 
							// -------------------------------------------------------
							for (j = 0; j < pl.size(); j++)
							{
								const Property& prop = pl[j];
								const Type& propType = prop.getType();
								SDOXMLString propTypeName = resolveName(propType.getURI(), propType.getName(), targetNamespaceURI);
								
								bool isElement = false;
								XSDPropertyInfo* pi = (XSDPropertyInfo*)
									((DASProperty*)&prop)->getDASValue("XMLDAS::PropertyInfo");
								if (pi)
								{
									if (pi->getPropertyDefinition().isElement)
										isElement=true;
								}
								
								if (!isElement
									&& !prop.isMany()
									&& !prop.isContainment())
								{
									// -------------------------------
									// Write an <attribute> definition
									// -------------------------------
									rc = xmlTextWriterStartElementNS(writer, xsd, SDOXMLString("attribute"), NULL);
									
									rc = xmlTextWriterWriteAttribute(writer, 
										SDOXMLString("name"),
										SDOXMLString(prop.getName()));
									
									if (!propType.isDataType())
									{
										rc = xmlTextWriterWriteAttribute(writer, 
											SDOXMLString("sdoxml:propertyType"),
											propTypeName);
										propTypeName = "xsd:anyURI";
									}
									
									rc = xmlTextWriterWriteAttribute(writer, 
										SDOXMLString("type"),
										propTypeName);
									
									// End attribute
									rc = xmlTextWriterEndElement(writer);
									
								}
								
							}
							
						}
						
						
						// -----------------------------------------------------
						// End <complexContent> if necessary
						// -----------------------------------------------------
						if (baseType != 0)
						{
							rc = xmlTextWriterEndElement(writer);
						}
						
						
						rc = xmlTextWriterEndElement(writer);
					}
				}
				
				// End the <schema> definition
				rc = xmlTextWriterEndElement(writer);
				return rc;
			}
			
			
			// ========================================================================================
			// resolveName - resolve a Type uri and name to xsd equivalent
			// ========================================================================================
			SDOXMLString  SDOXSDWriter::resolveName(const SDOXMLString& uri, const SDOXMLString& name, const SDOXMLString& targetNamespaceURI)
			{
				SDOXMLString resolvedName = name;
				if (uri.equals(Type::SDOTypeNamespaceURI))
				{
					resolvedName = "xsd:";
					if (name.equalsIgnoreCase("Boolean"))
					{
						resolvedName += "boolean";
					}
					else if (name.equalsIgnoreCase("Byte"))
					{
						resolvedName += "byte";
					}
					else if (name.equalsIgnoreCase("Bytes"))
					{
						resolvedName += "hexBinary";
					}
					else if (name.equalsIgnoreCase("Character"))
					{
						resolvedName += "string";
					}
					else if (name.equalsIgnoreCase("DataObject"))
					{
						resolvedName += "anyType";
					}
					else if (name.equalsIgnoreCase("Date"))
					{
						resolvedName += "dateTime";
					}
					else if (name.equalsIgnoreCase("DateTime"))
					{
						resolvedName += "dateTime";
					}
					else if (name.equalsIgnoreCase("Day"))
					{
						resolvedName += "gDay";
					}
					else if (name.equalsIgnoreCase("Decimal"))
					{
						resolvedName += "decimal";
					}
					else if (name.equalsIgnoreCase("Double"))
					{
						resolvedName += "double";
					}
					else if (name.equalsIgnoreCase("Duration"))
					{
						resolvedName += "duration";
					}
					else if (name.equalsIgnoreCase("Float"))
					{
						resolvedName += "float";
					}
					else if (name.equalsIgnoreCase("Int"))
					{
						resolvedName += "int";
					}
					else if (name.equalsIgnoreCase("Integer"))
					{
						resolvedName += "integer";
					}
					else if (name.equalsIgnoreCase("Long"))
					{
						resolvedName += "long";
					}
					else if (name.equalsIgnoreCase("Month"))
					{
						resolvedName += "gMonth";
					}
					else if (name.equalsIgnoreCase("MonthDay"))
					{
						resolvedName += "gMonthDay";
					}
					else if (name.equalsIgnoreCase("Object"))
					{
						resolvedName += "anySimpleType";
					}
					else if (name.equalsIgnoreCase("Short"))
					{
						resolvedName += "short";
					}
					else if (name.equals("String"))
					{
						resolvedName += "String";
					}
					else if (name.equalsIgnoreCase("Strings"))
					{
						resolvedName += "string";
					}
					else if (name.equalsIgnoreCase("Time"))
					{
						resolvedName += "time";
					}
					else if (name.equalsIgnoreCase("Year"))
					{
						resolvedName += "gYear";
					}
					else if (name.equalsIgnoreCase("YearMonth"))
					{
						resolvedName += "gYearMonth";
					}
					else if (name.equalsIgnoreCase("YearMonthDay"))
					{
						resolvedName += "date";
					}
					else if (name.equalsIgnoreCase("URI"))
					{
						resolvedName += "anyURI";
					}
					else
					{
						resolvedName += "String";
					}
				}
				else if (!targetNamespaceURI.isNull() && uri.equals(targetNamespaceURI))
				{
					resolvedName = "tns:";
					resolvedName += name;
				}
				
				return resolvedName;
			}
			
			
			
		} // End - namespace xmldas
	} // End - namespace sdo
} // End - namespace commonj

