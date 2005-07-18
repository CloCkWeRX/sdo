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

#include "SDOXMLWriter.h"
#include "SDOXMLString.h"
#include "iostream"
using namespace::std;
#include "DASProperty.h"
#include "XSDPropertyInfo.h"
#include "XSDTypeInfo.h"
#include "ChangeSummary.h"
#include "Sequence.h"
#include "SDORuntimeException.h"
#include "XMLQName.h"
#include "DataObjectImpl.h"

namespace commonj
{
	namespace sdo
	{
		
		namespace xmldas
		{
			
			
			
			SDOXMLWriter::SDOXMLWriter(
				DASDataFactoryPtr dataFact)
				: dataFactory(dataFact)
			{
				
			}
			
			SDOXMLWriter::~SDOXMLWriter()
			{
				freeWriter();
			}
			
			void SDOXMLWriter::setWriter(xmlTextWriterPtr textWriter)
			{
				writer = textWriter;
			}
			
			void SDOXMLWriter::freeWriter()
			{
				if (writer != NULL)
				{
					xmlFreeTextWriter(writer);
					writer = NULL;
				}
			}
			
			int SDOXMLWriter::write(XMLDocumentPtr doc)
			{
				if (!doc)
				{
					return 0;
				}
				
				if (writer == NULL)
				{
					// Throw exception
					return -1;
				}
				
				int rc = 0;
				
				namespaceUriStack.empty();
				namespaceUriStack.push(SDOXMLString());
				namespaces.empty();
				
				
				//xmlTextWriterSetIndent(writer, 1);
				//xmlTextWriterSetIndentString(writer, SDOXMLString("  "));
				
				if (doc->getXMLDeclaration())
				{
					rc = xmlTextWriterStartDocument(writer, doc->getXMLVersion(), doc->getEncoding(), NULL);
					if (rc < 0) {
						SDO_THROW_EXCEPTION("write", SDOXMLParserException, "xmlTextWriterStartDocument failed");
					}
				}
				
				DataObjectPtr root = doc->getRootDataObject();
				if (root)
				{
					bool writeXSIType = false;
					// For the root DataObject we need to determine the element name
					SDOXMLString elementURI = doc->getRootElementURI();
					if (elementURI.isNull() || elementURI.equals(""))
					{
						elementURI = root->getType().getURI();
					}
					SDOXMLString elementName = doc->getRootElementName();
					if (elementName.isNull() || elementName.equals(""))
					{
						elementName = root->getType().getName();
						elementName = elementName.toLower(0,1);
						writeXSIType = true;
					}
					
					writeDO(root, elementURI, elementName, true);
				}
				rc = xmlTextWriterEndDocument(writer);
				if (rc < 0) {
						SDO_THROW_EXCEPTION("write", SDOXMLParserException, "xmlTextWriterEndDocument failed");
					return rc;
				}
				
				xmlTextWriterFlush(writer);
				freeWriter();
				
				return rc;
			}
			
			//////////////////////////////////////////////////////////////////////////
			// Write a DatObject tree
			//////////////////////////////////////////////////////////////////////////
			
			int SDOXMLWriter::writeDO(
				DataObjectPtr dataObject,
				const SDOXMLString& elementURI,
				const SDOXMLString& elementName,
				bool writeXSIType)
			{
				if (dataObject == 0)
					return 0;

				SDOXMLString uri;
				if (!elementURI.equals(namespaceUriStack.top()))
				{
					uri = elementURI;
					namespaceUriStack.push(elementURI);
				}
				
				
				//xmlTextWriterWriteString(writer,SDOXMLString("\n"));
				int rc = xmlTextWriterStartElementNS(writer, NULL, elementName, uri);
				if (rc < 0) {
					SDO_THROW_EXCEPTION("writeDO", SDOXMLParserException, "xmlTextWriterStartElementNS failed");
				}				
				
				const Type& dataObjectType = dataObject->getType();

				if (writeXSIType)
				{
					rc = xmlTextWriterWriteAttributeNS(writer, 
						SDOXMLString("xsi"), SDOXMLString("type"), 
						SDOXMLString("http://www.w3.org/2001/XMLSchema-instance"), 
						SDOXMLString(dataObject->getType().getName()));
				}
				
				//////////////////////////////////////////////////////////////////////////
				// Iterate over all the properties to find attributes
				//////////////////////////////////////////////////////////////////////////
				int i;
				PropertyList pl = dataObject->getProperties();
				for (i = 0; i < pl.size(); i++)
				{
					if (dataObject->isSet(pl[i]))
					{					
						SDOXMLString propertyName(pl[i].getName());
						XSDPropertyInfo* pi = getPropertyInfo(dataObjectType, pl[i]);
						PropertyDefinition propdef;
						if (pi)
						{
							propdef = pi->getPropertyDefinition();
							propertyName = propdef.localname;
						}
						
						// Elements are written as <element>
						if (propdef.isElement)
							continue;
						
						// Many-valued properties are written as <element>
						if (pl[i].isMany())
							continue;
											
					//	if (pl[i].isContainment())
					//		continue;

						//////////////////////////////////////////////////////////////////////
						// Non contained properties become attributes
						//////////////////////////////////////////////////////////////////////
						const Type& propertyType = pl[i].getType();
						
						if (propertyType.isDataType())
						{
							SDOXMLString propertyValue = (dataObject->getCString(pl[i]));
							if (pi && pi->getPropertyDefinition().isQName)
							{
								XMLQName qname(propertyValue);
								SDOXMLString pref;
								const SDOXMLString* prefix = namespaces.findPrefix(qname.getURI());
								if (prefix == 0)
								{
									char buffer[100];
									pref = "tns";
                                                                        sprintf(buffer, "%d", i);
									pref += buffer;
									namespaces.add(pref, qname.getURI());
									prefix = namespaces.findPrefix(qname.getURI());
								}

								rc = xmlTextWriterWriteAttributeNS(writer, 
								SDOXMLString("xmlns"), pref, NULL, qname.getURI());

								propertyValue = pref + ":" + qname.getLocalName();
								
							}
							rc = xmlTextWriterWriteAttribute(writer, 
								propertyName, propertyValue);
						}
						else
						{
							// Handle non-containment reference to DataObject
							if (pl[i].isReference())
							{
								writeReference(dataObject, pl[i], false);
							}
						}
					}
				}
				
				// --------------------
				// Handle ChangeSummary
				// --------------------
				if (dataObject->getType().isChangeSummaryType())
				{
					ChangeSummaryPtr changeSummary = dataObject->getChangeSummary();
					if (changeSummary)
					{
						// for now just write a <changeSummary> tag
						ChangedDataObjectList& changedDOs =  changeSummary->getChangedDataObjects();
						if (changeSummary->isLogging()
							|| changedDOs.size() > 0)
						{
							rc = xmlTextWriterStartElementNS(writer,
								SDOXMLString("sdo"), SDOXMLString("changeSummary"), SDOXMLString(Type::SDOTypeNamespaceURI));
							SDOXMLString logging("false");
							if (changeSummary->isLogging())
							{
								logging = "true";
							}
							
							rc = xmlTextWriterWriteAttribute(writer, 
								SDOXMLString("logging"),
								logging);
							
							rc = xmlTextWriterEndElement(writer);
							
						}
					}
				}
				
				if (dataObjectType.isSequencedType())
				{
					SequencePtr sequence  = dataObject->getSequence();
					if (sequence)
					{
						for (i=0; i<sequence->size(); i++)
						{
							
							if (sequence->isText(i))
							{
								rc = xmlTextWriterWriteString(
									writer,
									SDOXMLString(sequence->getCStringValue(i)));
								continue;
							} // end TextType

							const Property& seqProp = sequence->getProperty(i);
							SDOXMLString seqPropName = seqProp.getName();
							const Type& seqPropType = seqProp.getType();

							if (seqPropType.isDataObjectType())
							{								
								DataObjectPtr doValue;
								if (seqProp.isMany())
								{
									int index = sequence->getListIndex(i);
									doValue = dataObject->getList(seqProp)[index];
								}
								else
								{
									doValue = dataObject->getDataObject(seqProp);
								}

								if (doValue)
								{
									// Handle non-containment reference to DataObject
									if (seqProp.isReference())
									{
										writeReference(dataObject, seqProp, true, doValue);
									}
									else
									{
										writeDO(doValue, doValue->getType().getURI(), seqPropName);
									}
								}
							} // end DataObject


							else
							{
								// Sequence member is a primitive
								xmlTextWriterWriteElement(
									writer,
									seqPropName,
									SDOXMLString(sequence->getCStringValue(i)));
								
							} // end DataType
						} // end - iterate over sequence
						
					}
				
				} // end sequence handling
				
				else
				{
					
					//////////////////////////////////////////////////////////////////////////
					// Iterate over all the properties to find elements
					//////////////////////////////////////////////////////////////////////////
					for (i = 0; i < pl.size(); i++)
					{
						if (dataObject->isSet(pl[i]))
						{
							
							SDOXMLString propertyName(pl[i].getName());
							XSDPropertyInfo* pi = getPropertyInfo(dataObjectType, pl[i]);
							if (pi)
							{
								if (!pi->getPropertyDefinition().isElement)
									continue;
								propertyName = pi->getPropertyDefinition().localname;
							}
							
							const Type& propertyType = pl[i].getType();
							
							//////////////////////////////////////////////////////////////////////
							// For a many-valued property get the list of values
							//////////////////////////////////////////////////////////////////////
							if (pl[i].isMany())
							{
								DataObjectList& dol = dataObject->getList(pl[i]);
								for (int j = 0; j <dol.size(); j++)
								{
									// Handle non-containment reference to DataObject
									if (pl[i].isReference())
									{
										writeReference(dataObject, pl[i], true, dol[j]);
									}
									else
									{	
										SDOXMLString typeURI = dol[j]->getType().getURI();
										writeDO(dol[j], dol[j]->getType().getURI(), propertyName);
									}
								}
							} // end IsMany
							
							//////////////////////////////////////////////////////////////////////
							// For a dataobject write the do
							//////////////////////////////////////////////////////////////////////
							else if (!propertyType.isDataType())
							{
								// Handle non-containment reference to DataObject
								if (pl[i].isReference())
								{
									writeReference(dataObject, pl[i], true);
								}
								else
								{
									DataObjectPtr propDO = dataObject->getDataObject(pl[i]);				
									writeDO(propDO, propDO->getType().getURI(), propertyName);
								}
							}
							
							//////////////////////////////////////////////////////////////////////
							// For a primitive
							//////////////////////////////////////////////////////////////////////
							else
							{
								// Only write a primitive as an element if defined by the XSD
								if (pi)
								{
									xmlTextWriterWriteElement(
										writer,
										propertyName,
										SDOXMLString(dataObject->getCString(pl[i])));
								}
								
							}
						}
					}
				}
				rc = xmlTextWriterEndElement(writer);
				return rc;
			}
			
			XSDPropertyInfo* SDOXMLWriter::getPropertyInfo(const Type& type, const Property& property)
			{
				if (dataFactory)
				{
					return (XSDPropertyInfo*)dataFactory->getDASValue(type, property.getName(), "XMLDAS::PropertyInfo");
				}
				else
				{
					return (XSDPropertyInfo*)((DASProperty*)&property)->getDASValue("XMLDAS::PropertyInfo");
				}
				
			}

			void SDOXMLWriter::writeReference(
				DataObjectPtr dataObject, 
				const Property& property,
				bool isElement,
				DataObjectPtr refferedToObject)
			{
				DataObjectPtr reffedObject = refferedToObject;
				if (reffedObject == 0)
				{
					reffedObject = dataObject->getDataObject(property);
				}

				// Get ID from referred to DataObject or use XPath
				SDOXMLString refValue;
				XSDTypeInfo* ti = (XSDTypeInfo*)((DASType*)&reffedObject->getType())->
					getDASValue("XMLDAS::TypeInfo");
				if (ti)
				{
					TypeDefinition typeDef = ti->getTypeDefinition();
					if (!typeDef.IDPropertyName.isNull())
					{
						refValue = reffedObject->getCString(typeDef.IDPropertyName);
					}
				}
				
				if (refValue.isNull())
				{
					// need to get XPATH
					refValue = ((DataObjectImpl*)(DataObject*)reffedObject)->objectToXPath();
				}
				
				if (!refValue.isNull())
				{
					if (isElement)
					{
						// Set the IDREF value
						xmlTextWriterWriteElement(writer, 
							SDOXMLString(property.getName()), refValue);
					}
					else
					{
						// Set the IDREF value
						xmlTextWriterWriteAttribute(writer, 
							SDOXMLString(property.getName()), refValue);
					}
				}
			}	
			
		} // End - namespace xmldas
	} // End - namespace sdo
} // End - namespace commonj

