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

#include "commonj/sdo/SDOXMLWriter.h"
#include "commonj/sdo/SDOXMLString.h"
#include "iostream"
using namespace::std;
#include "commonj/sdo/DASProperty.h"
#include "commonj/sdo/XSDPropertyInfo.h"
#include "commonj/sdo/XSDTypeInfo.h"
#include "commonj/sdo/ChangeSummary.h"
#include "commonj/sdo/Sequence.h"
#include "commonj/sdo/SDORuntimeException.h"
#include "commonj/sdo/XMLQName.h"
#include "commonj/sdo/DataObjectImpl.h"

namespace commonj
{
	namespace sdo
	{
		
		
		
		
		SDOXMLWriter::SDOXMLWriter(
			DataFactoryPtr dataFact)
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
		// Write Change Summary attributes
		//////////////////////////////////////////////////////////////////////////

		void SDOXMLWriter::handleChangeSummaryAttributes(
			ChangeSummaryPtr cs, 
			DataObjectPtr dol)
		{
			int rc;

			SettingList& sl = cs->getOldValues(dol);
			if (sl.size() == 0) 
			{
				// no attributes
				return;
			}
			for (int j=0;j< sl.size(); j++)
			{
				try {

					if (sl[j].getProperty().isMany()) 
					{
						// manys are elements
						continue;
					}
		
					if (sl[j].getProperty().getType().isDataType())
					{
						// data types are OK
						rc = xmlTextWriterWriteAttribute(writer, 
							SDOXMLString(sl[j].getProperty().getName()),
							SDOXMLString(sl[j].getCStringValue()));
					}
					else 
					{
						DataObjectPtr dob = sl[j].getDataObjectValue();
						if (dob) 
						{
							if (cs->isDeleted(dob))
							{
							rc = xmlTextWriterWriteAttribute(writer, 
								SDOXMLString(sl[j].getProperty().getName()),
								SDOXMLString(cs->getOldXpath(dob)));
							}
							else 
							{
							rc = xmlTextWriterWriteAttribute(writer, 
								SDOXMLString(sl[j].getProperty().getName()),
								SDOXMLString(dob->objectToXPath()));
							}
						}
						else
						{
							rc = xmlTextWriterWriteAttribute(writer, 
								SDOXMLString(sl[j].getProperty().getName()),
								SDOXMLString(""));
						}
					}
				}
				catch (SDORuntimeException e)
				{
					// ignore this attribute
				}
			}
		}

		//////////////////////////////////////////////////////////////////////////
		// Write  Change Summary elements
		//////////////////////////////////////////////////////////////////////////
		
		void SDOXMLWriter::handleChangeSummaryElements(
			ChangeSummaryPtr cs, 
			DataObjectPtr dob)
		{
			int rc;

			SettingList& sl = cs->getOldValues(dob);
		
			if (sl.size() == 0) 
			{
			// there are no setting for this element.
			return;
			}
	        
			for (int j=0;j< sl.size(); j++)
			{
				try 
				{

					// single values will have been covered by the attributes.
					if (!sl[j].getProperty().isMany()) continue;
		
					if (sl[j].getProperty().getType().isDataType())
					{

						rc = xmlTextWriterWriteElement(
							writer,
							SDOXMLString(sl[j].getProperty().getName()),
							SDOXMLString(sl[j].getCStringValue()));
							
					} // if datatype
					else
					{
						DataObjectPtr dob2 = sl[j].getDataObjectValue();
						if (!dob2) 
						{
							continue;
						}
						if (cs->isDeleted(dob2))
						{
							handleChangeSummaryDeletedObject(sl[j].getProperty().getName(), cs,dob2);
						}
						else
						{
							rc = xmlTextWriterStartElement(
								writer,
								SDOXMLString(sl[j].getProperty().getName()));
							rc = xmlTextWriterWriteAttribute(
								writer,
								SDOXMLString("sdo:ref"),
								SDOXMLString(dob2->objectToXPath()));
							rc = xmlTextWriterEndElement(
								writer);
						}
					} 
				}
				catch (SDORuntimeException e)
				{
					// ignore this element
				}
			} // for
		} 


		//////////////////////////////////////////////////////////////////////////
		// Write a deleted object and all its props
		//////////////////////////////////////////////////////////////////////////

		void SDOXMLWriter::handleChangeSummaryDeletedObject(
			const char* name, 
			ChangeSummaryPtr cs, 
			DataObjectPtr dob)
		{
		
			int rc; // TODO error handling
		
			SettingList& sl = cs->getOldValues(dob);
		
			rc = xmlTextWriterStartElement(
				writer,
				SDOXMLString(name));

			if (sl.size() == 0) 
			{
				rc = xmlTextWriterWriteAttribute(writer, 
					SDOXMLString("sdo:ref"),
					SDOXMLString(cs->getOldXpath(dob)));
				rc = xmlTextWriterEndElement(writer);
				return;
			}

		
			try 
			{
			    // print single valued datatypes as attributes
		
				for (int j=0;j< sl.size(); j++)
				{
					//if (!sl[j].isSet()) 
					//{
					//	// unset properties dont need recording - ah but they do!
                    //
					//	continue;
					//}
					if ( sl[j].getProperty().isMany()) 
					{
						// manys are elements
						continue;
					}
					if (!sl[j].getProperty().getType().isDataType())
					{
						// data objects are element in a deleted data object.
						continue;
					}

					rc = xmlTextWriterWriteAttribute(writer, 
						SDOXMLString(sl[j].getProperty().getName()),
						SDOXMLString(sl[j].getCStringValue()));

				} // for attributes
		
	
				// now we are onto the many-values, 
				// and dataobject single values.
				// 
				// handle deletions within deletions in reverse order, so they match the
				// deletion records above.

				for (int k=sl.size()-1;k>=0; k--)
				{

             		if ( !sl[k].getProperty().getType().isDataType() &&
						  sl[k].getProperty().isMany()) 
					{
						// its a dataobject type
						DataObjectPtr dob2 = sl[k].getDataObjectValue();
						if (!dob2) continue;
						if (!cs->isDeleted(dob2)) continue;
						handleChangeSummaryDeletedObject(sl[k].
							       getProperty().getName(),cs,dob2);
					}
				} // for attributes

				for (int kk=0;kk< sl.size(); kk++)
				{

             		if ( !sl[kk].getProperty().getType().isDataType())
					{
						if (sl[kk].getProperty().isMany()) continue; 
						// its a single valued dataobject type

						DataObjectPtr dob2 = sl[kk].getDataObjectValue();
						if (!dob2) continue;
						if (!cs->isDeleted(dob2)) continue;
						handleChangeSummaryDeletedObject(sl[kk].
							       getProperty().getName(),cs,dob2);

					}
					else 
					{
						if ( !sl[kk].getProperty().isMany()) continue; 
						
						// could only be many valued data type
		
						rc = xmlTextWriterWriteElement(writer, 
							SDOXMLString(sl[kk].getProperty().getName()),
							SDOXMLString(sl[kk].getCStringValue()));
					}
				} // for attributes
			}
			catch (SDORuntimeException e)
			{
                 // ignore - and write the end-element
			}

			rc = xmlTextWriterEndElement(writer);
		} 


		//////////////////////////////////////////////////////////////////////////
		// Write the list of elements of a change summary
		//////////////////////////////////////////////////////////////////////////

		void SDOXMLWriter::handleSummaryChange(
			const SDOXMLString& elementName, 
			ChangeSummaryPtr cs, 
			DataObjectPtr dob)
		{
			int rc; 
			DataObject* temp = dob;
            const char* name;
			try 
			{
				name = temp->getContainmentProperty().getName();
			}
			catch (SDORuntimeException e)
			{
				// This could be a root, and have no name.
				name = 0;
			}			

			if (name == 0) 
			{
			rc = xmlTextWriterStartElement(
				writer,
				elementName);
			}
			else
			{
			rc = xmlTextWriterStartElement(
				writer,
				SDOXMLString(name));
			}

			if (rc != 0)
			{
				// failed to write an element
				return;
			}

			try 
			{
				name =  temp->objectToXPath();
			}
			catch (SDORuntimeException e)
			{
				name = 0;
			}

			rc = xmlTextWriterWriteAttribute(writer, 
				SDOXMLString("sdo:ref"),
				SDOXMLString(name));

			handleChangeSummaryAttributes(cs, temp);

			handleChangeSummaryElements(cs, temp);

			rc = xmlTextWriterEndElement(writer);

		}

		//////////////////////////////////////////////////////////////////////////
		// Write a Change Summary
		//////////////////////////////////////////////////////////////////////////

		void SDOXMLWriter::handleChangeSummary(
			const SDOXMLString& elementName,
			ChangeSummaryPtr cs)
		{
			int i;
			int rc; 

			ChangedDataObjectList& changedDOs =  cs->getChangedDataObjects();
			if (changedDOs.size() > 0)
			{
				rc = xmlTextWriterStartElementNS(writer,
						SDOXMLString("sdo"), SDOXMLString("changeSummary"), SDOXMLString(Type::SDOTypeNamespaceURI));

				// Fall at the first hurdle - dont write anything.
				if (rc != 0) return;

				// write the creates/deletes in the order they
				// happened, as elements.

			    for (i=0;i< changedDOs.size();i++)
				{
					if  (cs->isCreated(changedDOs[i])
						&& changedDOs.getType(i) == ChangedDataObjectList::Create) 
					{
						// TODO - should work out if theres a IDREF here
						// TODO - can we have more than one create like this?
						try
						{
							rc = xmlTextWriterWriteElement(writer, 
							SDOXMLString("create"),
							SDOXMLString(changedDOs[i]->objectToXPath()));
						}
						catch (SDORuntimeException e)
						{
								// The object was not in our tree - we ignore it.
						}
					}
					if  (cs->isDeleted(changedDOs[i])
						&& changedDOs.getType(i) == ChangedDataObjectList::Delete) 
					{
						// TODO - should work out if theres a IDREF here
						try 
						{
							rc = xmlTextWriterWriteElement(writer, 
							SDOXMLString("delete"),
							SDOXMLString(cs->getOldXpath(changedDOs[i])));
						}
						catch (SDORuntimeException e)
						{
							// The object was not in the deleted list - we ignore it.
						}
					}
				}

				if (cs->isLogging())
				{
					rc = xmlTextWriterWriteAttribute(writer, 
						SDOXMLString("logging"),
						SDOXMLString("true"));
				}
						
			
				for (i=0;i< changedDOs.size();i++)
				{
					if (cs->isModified(changedDOs[i]))
					{
						handleSummaryChange(elementName, cs, changedDOs[i]);
					}
				}

				rc = xmlTextWriterEndElement(writer);
						
				}
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

			int rc;

			if (dataObject == 0)
				return 0;

			SDOXMLString uri;
			if (!elementURI.equals(namespaceUriStack.top()))
			{
				uri = elementURI;
				namespaceUriStack.push(elementURI);
			}
			
			const Type& dataObjectType = dataObject->getType();

			//////////////////////////////////////////////////////////////////////////
			// suppose its a primitive type - just write the value
			//////////////////////////////////////////////////////////////////////////
            if (dataObjectType.isDataType())
			{
				if (dataObject->isNull(""))
				{
					rc = xmlTextWriterStartElementNS(writer, 
						NULL, elementName, uri);
					if (rc < 0) 
					{
						SDO_THROW_EXCEPTION("writeDO", 
							SDOXMLParserException, 
							"xmlTextWriterStartElementNS failed");
					}				
					rc = xmlTextWriterWriteAttribute(writer, 
						(const unsigned char*)"xsi:nil", 
						(const unsigned char*)"true");
					rc = xmlTextWriterEndElement(writer);
				}
				else
				{
					xmlTextWriterWriteElement(
					writer,
					elementName,
					SDOXMLString(dataObject->getCString("")));
				}
				return 0;

			}
			
			//xmlTextWriterWriteString(writer,SDOXMLString("\n"));
			rc = xmlTextWriterStartElementNS(writer, NULL, elementName, uri);
			if (rc < 0) {
				SDO_THROW_EXCEPTION("writeDO", SDOXMLParserException, "xmlTextWriterStartElementNS failed");
			}				
			

			if (writeXSIType)
			{
				rc = xmlTextWriterWriteAttributeNS(writer, 
					SDOXMLString("xsi"), SDOXMLString("type"), 
					SDOXMLString("http://www.w3.org/2001/XMLSchema-instance"), 
					SDOXMLString(dataObject->getType().getName()));
			}


			//////////////////////////////////////////////////////////////////////////
			// write out the type if the xsi:type if the containing type is open
	 		//////////////////////////////////////////////////////////////////////////
			DataObject* dob = dataObject;
            DataObjectImpl* cont = 
				     ((DataObjectImpl*)dob)->getContainerImpl();
			if (cont != 0)
			{
				if (cont->getType().isOpenType())
				{
					//if (dataObject->getType().getURI() != 0)
					//{
					//	std::string value = 
					//		dataObject->getType().getURI();
					//	value += ":";
					//	value += dataObject->getType().getName();
					//	rc = xmlTextWriterWriteAttribute(writer, 
					//		(const unsigned char*)"xsi:type", 
					//		(const unsigned char*)value.c_str());
					//}
					//else
					//{
						rc = xmlTextWriterWriteAttribute(writer, 
						(const unsigned char*)"xsi:type", 
						(const unsigned char*)dataObject->getType().getName());
					//}
				}
			}

			// write nil if required
			if (dataObject->isNull(""))
			{
				rc = xmlTextWriterWriteAttribute(writer, 
				(const unsigned char*)"xsi:nil", 
				(const unsigned char*)"true");
			}


			//////////////////////////////////////////////////////////////////////////
			// Iterate over all the properties to find attributes
			//////////////////////////////////////////////////////////////////////////
			int i;
			PropertyList pl = dataObject->getInstanceProperties();
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
					handleChangeSummary(elementName, changeSummary);
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
								if (pl[i].isReference() )
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
								if (pi)
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
							if (dataObject->isNull(propertyName))
								{
									rc = xmlTextWriterStartElementNS(writer, 
									NULL, elementName, uri);
									if (rc < 0) 
									{
										SDO_THROW_EXCEPTION("writeDO", 
										SDOXMLParserException, 
										"xmlTextWriterStartElementNS failed");
									}
									rc = xmlTextWriterWriteAttribute(writer, 
									(const unsigned char*)"xsi:nil", 
									(const unsigned char*)"true");
									rc = xmlTextWriterEndElement(writer);
								}
								else
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
		
	} // End - namespace sdo
} // End - namespace commonj
