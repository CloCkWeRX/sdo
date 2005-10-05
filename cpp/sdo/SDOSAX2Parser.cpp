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

#include "SDOSAX2Parser.h"

#include "SDORuntimeException.h"
#include "ChangeSummary.h"
#include "XSDPropertyInfo.h"
#include "XMLQName.h"
#include "DASProperty.h"
#include "DASType.h"
#include "XSDTypeInfo.h"

namespace commonj
{
	namespace sdo
	{
		
		SDOSAX2Parser::SDOSAX2Parser(
			DataFactoryPtr df,
			const SDOXMLString& targetNamespace,
			DataObjectPtr& rootDO)
			
			: dataFactory(df),
			targetNamespaceURI(targetNamespace),
			rootDataObject(rootDO),
			currentDataObject(0),
			isDataGraph(false),
			ignoreEvents(false)
			// WORK IN PROGRESS dealingWithChangeSummary(false)
		{
			reset();
			if (targetNamespace.isNull())
			{
				targetNamespaceURI = "";
			}
			rootDataObject = 0;
		}
		
		SDOSAX2Parser::~SDOSAX2Parser()
		{
		}
		
		void SDOSAX2Parser::reset()
		{
			rootDataObject = 0;
			currentDataObject = 0;
			isDataGraph = false;
			ignoreEvents = false;
			changeSummary = false;
			IDMap.empty();
			IDRefs.empty();
		}
		
		
		void SDOSAX2Parser::startDocument()
		{
			setNamespaces = true;
			reset();
		}
		
		void SDOSAX2Parser::endDocument()
		{
			// Iterate over IDREFs list and set references
			ID_REFS::iterator refsIter;
			for (refsIter = IDRefs.begin(); refsIter != IDRefs.end(); refsIter++)
			{
				try
				{
					const Property& prop = refsIter->dataObject->getType().getProperty(refsIter->property);
					const Type& propType = prop.getType();

					// Allowing referenes to DataObjects only
					if (!propType.isDataType())
					{
						DataObjectPtr reffedDO;
						ID_MAP::iterator idIter = IDMap.find(refsIter->value);
						if (idIter != IDMap.end())
						{
							reffedDO = idIter->second;
						}
						else
						{
							// assume it is an XPath?

							// Remove #/ from front of XPATH as getDataObject doeesnt
							// support this yet
							SDOXMLString xpath(refsIter->value);
							if (xpath.firstIndexOf('#') == 0)
								xpath = xpath.substring(1);
							if (xpath.firstIndexOf('/') == 0)
								xpath = xpath.substring(1);

							reffedDO = rootDataObject->getDataObject(xpath);
						}

						if (!reffedDO)
						{
							continue;
						}

						if (prop.isMany())
						{
							DataObjectList& dol = refsIter->dataObject->getList(prop);
							dol.append(reffedDO);
						}
						else
						{
							refsIter->dataObject->setDataObject(prop, reffedDO); 
						}
					}
				}
				catch (const SDORuntimeException&)
				{
				}
			}
			
		}
		
		
		void SDOSAX2Parser::startElementNs(
			const SDOXMLString& localname,
			const SDOXMLString& prefix,
			const SDOXMLString& URI,
			const SAX2Namespaces& namespaces,
			const SAX2Attributes& attributes)
			
		{
			// Save the namespace information from the first element
			if (setNamespaces)
			{
				documentNamespaces = namespaces;
				setNamespaces = false;
			}
			
			if (ignoreEvents)
			{
				// Check for the tag we are waiting for
				if (   (ignoreTag.localname.equals(localname))
					&& (ignoreTag.uri.equals(URI))
					&& (ignoreTag.prefix.equals(prefix)) )
				{
					ignoreTag.tagCount++;
				}
				return;
			}
			
			// WORK IN PROGRESS
			//if (dealingWithChangeSummary)
			//{
			//	// we are in a change summary - so need special handling.
			//	// elements are changed data objects, or created/deleted 
			//	// data objects.
			//	if (localname.equalsIgnoreCase("create") ||
			//		localname.equalsIgnoreCase("delete"))
			//	{
			//		// will be expecting a chars message to give the 
			//		// contents
			//		
			//		createDeletes.insert(
			//			createDeletes.end(),createDelete(localname));
			//		// cout << localname << ":";
			//		return;
			//	}
			//	else 
			//	{
			//		 this is an element representing a change item
			//		 WORK IN PROGRESS
			//		if (changeLevel == 0)
			//		{
			//			changeLevel++;
			//			elementIndex=0;
			//			currentElement = ""; // empty string
			//			// start of a new change record
			//			SDOXMLString ref = attributes.getValue("ref");
			//			if (ref.isNull())
			//			{
			//				// we have nothing to refer to - drop this change
			//				// ignore content for now
			//				ignoreEvents = true;
			//				ignoreTag.localname = localname;
			//				ignoreTag.uri = URI;
			//				ignoreTag.prefix = prefix;
			//				ignoreTag.tagCount = 0;
			//				return;
			//			}
			//			currentChange = change(ref);
			//			int i;
			//			for (i=0; i < attributes.size(); i++)
			//			{
			//				// push all the attributes into the channge record.
			//				SDOXMLString name = attributes[i].getName();
			//				if (!name.equalsIgnoreCase("ref"))
			//				{
			//					SDOXMLString value = attributes[i].getValue();
			//					currentChange.getAttributes().insert(
			//						currentChange.getAttributes().end(),
			//						changeAttribute(name,value));
			//				}
			//			}
			//			changes.insert(changes.end(),currentChange);
			//			return;
			//		}
			//		else
			//		{
			//			// its an element within a current change
			//			if (currentElement.equalsIgnoreCase(localname))
			//			{
			//				elementIndex++;
			//			}
			//			else
			//			{
			//				// its a new element
			//				currentElement = localname;
			//				currentChange.moveLocation(localname);
			//				elementIndex = 0;
			//			}
			//			//if ( TODO ) // current element is data type 
			//			//{
			//			//	// we expect characters to be provided for the value
			//			//	currentChange.getElements().insert(
			//			//		currentChange.getElements().end(),
			//			//		changeElement(localname,elementindex));
			//			//}
			//			//else
			//			{
			//				// its an object - does it have a ref?
			//				SDOXMLString ref = attributes.getValue("ref");
			//				if (!ref.isNull())
			//				{
			//					elementindex++;
			//					return;
			//				}
			//				else
			//				{
			//					// this is a deleted object
			//				deletion = deletionElement(localname,
			//				currentChange.getLocation(),elementindex);
			//				currentChange.getDeletions().push(deletion);
			//		
			//				}
			//			}
			//		return;
			//		}
			//	}
			//}


			if (URI.equalsIgnoreCase(Type::SDOTypeNamespaceURI))
			{
				///////////////////////////////////////////////////////////////////////
				// Handle datagraph
				///////////////////////////////////////////////////////////////////////
				if (localname.equalsIgnoreCase("datagraph"))
				{
					// Remember this is a datagraph. The root DO will be created
					// later when we can have a better guess at the namespaceURI
					isDataGraph = true;
				} // end handling sdo:datagraph
				
				////////////////////////////////////
				// Handle ChangeSummary on datagraph
				////////////////////////////////////
				if (localname.equals("changeSummary"))
				{
					changeSummary = true;
					changeSummaryDO = currentDataObject;
					changeSummaryLogging = true;
					
					
					SDOXMLString logging = attributes.getValue("logging");
					if (!logging.isNull())
					{
						if (logging.equals("false"))
						{
							changeSummaryLogging = false;
						}
					}
					
					// WORK IN PROGRESS dealingWithChangeSummary = true;

					// ignore content for now
					ignoreEvents = true;
					ignoreTag.localname = localname;
					ignoreTag.uri = URI;
					ignoreTag.prefix = prefix;
					ignoreTag.tagCount = 0;
					return;
					
				}
				
			}
			else
			{
				///////////////////////////////////////////////////////////////////////
				// Each element is a DataObject or a Property on the current DO
				///////////////////////////////////////////////////////////////////////
				DataObjectPtr newDO = 0;
				
				SDOXMLString typeURI, typeName;
				
				///////////////////////////////////////////////////////////////////////
				// Determine the type. It is either specified by the xsi:type attribute
				// or the localname is the name of a property on "RootType"
				///////////////////////////////////////////////////////////////////////
				int i;
				for (i=0; i < attributes.size(); i++)
				{
					if ((attributes[i].getUri().equalsIgnoreCase("http://www.w3.org/2001/XMLSchema-instance"))
						&& (attributes[i].getName().equalsIgnoreCase("type")))
					{
						SDOXMLString fullTypeName = attributes[i].getValue();
						SDOXMLString pref;

						int index = fullTypeName.firstIndexOf(':');
						if (index < 0)
						{
							typeName = fullTypeName;
						}
						else
						{
							// Is the namespace prefix defined?
							typeName = fullTypeName.substring(index+1);
							pref = fullTypeName.substring(0, index);
						}

						// Convert the prefix to a namespace URI
						const SDOXMLString* namespaceURI = namespaces.find(pref);
						if (namespaceURI == 0)
						{
							namespaceURI = documentNamespaces.find(pref);
						}
						if (namespaceURI != 0)
						{
							typeURI = *namespaceURI;
						}
						
						break;
					}
					
				} // End - attribute loop
				
				if (typeURI.isNull())
				{
					typeURI = "";
				}
				
				
				try
				{
					if (currentDataObject == 0)
					{
						// This element should become the root data object
						
						// Target namespace will be:
						//   the targetNamespaceURI if specified 
						//   or the URI of xsi:type if specified
						//   or the URI of this element
						SDOXMLString tns = URI;
						if (!typeURI.equals(""))
						{
							tns = typeURI;
						}

						if (!targetNamespaceURI.isNull() && !targetNamespaceURI.equals(""))
						{
							tns = targetNamespaceURI;
						}
						
						// Check for localname as a property of the RootType
						// if we do not already know the type
						if (typeName.isNull())
						{
							const Type& rootType = dataFactory->getType(tns, "RootType");
							const SDOXMLString propname = getSDOName(rootType, localname);
							const Type& newType = rootType.getProperty(propname).getType();
							typeURI = newType.getURI();
							typeName = newType.getName();
						}
						
						// Create the root DataObject
						if (isDataGraph)
						{
							DataObjectPtr rootdo = dataFactory->create(tns, "RootType");
							setCurrentDataObject(rootdo);
							changeSummaryDO = currentDataObject;
						}
						
						// NOTE: always creating DO doesn't cater for DataType as top element
						newDO = dataFactory->create(typeURI, typeName);
						
					} // End - currentDataObject == 0
					
					else
					{ // currentDataObject != 0
						
						// Get the Property from the dataObject
						const Property& prop = currentDataObject->getType().getProperty(localname);
						const Type& propType = prop.getType();
						XSDPropertyInfo* pi = (XSDPropertyInfo*)((DASProperty*)&prop)->getDASValue("XMLDAS::PropertyInfo");
						if ((pi && pi->getPropertyDefinition().isIDREF)
							|| prop.isReference())
						{
							// The name of this element is the name of a property on the current DO
							currentPropertySetting = PropertySetting(currentDataObject, localname, true);						
						}
						
						// If it is a DataType then we need set the value
						else if (propType.isDataType())
						{
							// The name of this element is the name of a property on the current DO
							currentPropertySetting = PropertySetting(currentDataObject, localname);
						}
						else
						{
							// If typeName is not set then create object of Type of Property
							// otherwise use the typeURI and typeName specified by e.g. xsi:type
							if (typeName.isNull())
							{
								newDO = dataFactory->create(propType.getURI(), propType.getName());
							}
							else
							{
								newDO = dataFactory->create(typeURI, typeName);
							}
						}
					}  // End // currentDataObject != 0
					
					if (newDO)
					{
						if (currentDataObject)
						{
							const SDOXMLString& propertyName = getSDOName(*currentDataObjectType, localname);
							const Property& property = currentDataObject->getType().getProperty(propertyName);
							const Type& propertyType = property.getType();
							if (currentDataObject->getType().isSequencedType())
							{
								SequencePtr seq = currentDataObject->getSequence();
								seq->addDataObject(property, newDO);
							}
							else
							{
								if (!property.isMany())
								{
									currentDataObject->setDataObject(propertyName, newDO);
								}
								else
								{
									DataObjectList& dol = currentDataObject->getList(propertyName);
									dol.append(newDO);
								}
							}
						}
						
						setCurrentDataObject(newDO);
					}
					
					
				} // end try
				catch (const SDOTypeNotFoundException& )
				{
					
					cout << "Unknown element (ignored): "  <<  localname << endl;
					// We need to ignore all events until the end tag for this element
					ignoreEvents = true;
					ignoreTag.localname = localname;
					ignoreTag.uri = URI;
					ignoreTag.prefix = prefix;
					ignoreTag.tagCount = 0;
					return;
				}
				catch (const SDOPropertyNotFoundException& )
				{
					cout << "Unknown element (ignored): "  <<  localname << endl;
					// We need to ignore all events until the end tag for this element
					ignoreEvents = true;
					ignoreTag.localname = localname;
					ignoreTag.uri = URI;
					ignoreTag.prefix = prefix;
					ignoreTag.tagCount = 0;
					return;
				}
				
				//////////////////////////////////////////////
				// The attributes are properties on the new DO
				// Handle attributes
				//////////////////////////////////////////////
				for (i=0; i < attributes.size(); i++)
				{
					// Should ignore attributes like xsi:type
					if (!(attributes[i].getUri().equalsIgnoreCase("http://www.w3.org/2001/XMLSchema-instance")))
					{							
						try
						{
							const SDOXMLString& propertyName = getSDOName(*currentDataObjectType, attributes[i].getName());
							const Property& prop = currentDataObject->getType().getProperty(propertyName);
							SDOXMLString propValue;
							
							XSDPropertyInfo* pi = (XSDPropertyInfo*)((DASProperty*)&prop)->getDASValue("XMLDAS::PropertyInfo");
							if (pi && pi->getPropertyDefinition().isQName)
							{
								XMLQName qname(attributes[i].getValue(),
									documentNamespaces, namespaces);
								propValue = qname.getSDOName();
							}
							else
							{
								propValue = attributes[i].getValue();
							}
							
							if ((pi && pi->getPropertyDefinition().isIDREF)
								|| prop.isReference())
							{
								// remember this value to resolve later
								IDRef ref(currentDataObject, attributes[i].getName(), propValue);
								IDRefs.insert(IDRefs.end(), ref);
							}
							else
							{	
								if (pi && pi->getPropertyDefinition().isID)
								{
									// add this ID to the map
									IDMap[propValue] = currentDataObject;
								}
								// Always set the property as a String. SDO will do the conversion
								currentDataObject->setCString(attributes[i].getName(), propValue);
							}
						} 
						catch (const SDOPropertyNotFoundException&)
						{
							// cout << "error processing attribute (ignored): " << attributes[i].getName() << endl;		
						}
					}
				} // End iterate over attributes								
			}
			
		}
		
		
		void SDOSAX2Parser::endElementNs(
			const SDOXMLString& localname,
			const SDOXMLString& prefix,
			const SDOXMLString& URI)
		{
			////////////////////////////////////
			// Handle ChangeSummary on datagraph
			////////////////////////////////////

			// WORK IN PROGRESS
			//if (dealingWithChangeSummary)
			//{
			//	// we are in a change summary - so need special handling.
			//	// elements are changed data objects, or created/deleted 
			//	// data objects.
			//	if (!localname.equalsIgnoreCase("create") &&
			//		!localname.equalsIgnoreCase("delete"))
			//	{
			//		// we have completed an element - could be the change
			//		// or an element within it.
			//		if (changelevel != 0)
			//		{
			//			currentChange.popLocation();
			//		}
			//	}
			//}

			//if (localname.equals("changeSummary"))
			//{
				// end of change summary
			//	dealingWithChangeSummary = false;
			//	return;
			//}

			if (ignoreEvents)
			{
				// Check for the tag we are waiting for
				if (   (ignoreTag.localname.equals(localname))
					&& (ignoreTag.uri.equals(URI))
					&& (ignoreTag.prefix.equals(prefix)) )
				{
					if (ignoreTag.tagCount == 0)
					{
						ignoreEvents = false;
					}
					ignoreTag.tagCount--;
				}
				return;
			}
			
			// If currentPropertySetting is set (name is not null)
			// then we need to set the property now
			if (!currentPropertySetting.name.isNull())
			{
				if (!currentPropertySetting.value.isNull())
				{
					try
					{
						if (currentPropertySetting.isIDREF)
						{
							// remember this value to resolve later
							IDRef ref(currentPropertySetting.dataObject,
								currentPropertySetting.name,
								currentPropertySetting.value );
							IDRefs.insert(IDRefs.end(), ref);
						}
						else
						{
							if (currentPropertySetting.dataObject->getType().isSequencedType())
							{
								SequencePtr seq = currentPropertySetting.dataObject->getSequence();
								seq->addCString(currentPropertySetting.name, currentPropertySetting.value);
							}
							// Always set the property as a String. SDO will do the conversion

							// It might be a single setting for a many-valued property.
							// may throw SDOPropertyNotFoundException
							const Property& p = currentPropertySetting.dataObject->getType().getProperty(
								currentPropertySetting.name);
							if (p.isMany())
							{
								DataObjectList& dl = currentPropertySetting.dataObject->
								getList((const char*)currentPropertySetting.name);
								dl.append((const char*)currentPropertySetting.value);
							}
							else
							{
								currentPropertySetting.dataObject->
								setCString((const char*)currentPropertySetting.name, currentPropertySetting.value );
							}
						}
					} 
					catch (const SDOPropertyNotFoundException&)
					{
						//cout << "error processing attribute (ignored): " << currentPropertySetting.name << endl;		
					}
				}
				currentPropertySetting = PropertySetting();
				
			}
			else
			{
				if (changeSummary 
					&& changeSummaryLogging 
					&& changeSummaryDO == currentDataObject)
				{
					// Set logging on for this DO before it is popped from stack
					ChangeSummary* cs = currentDataObject->getChangeSummary();
					if (cs)
					{
						cs->beginLogging();
					}
					changeSummary = false;
				}
				
				if (dataObjectStack.size() == 0 || rootDataObject == dataObjectStack.top())
				{
					currentDataObject = 0;
					currentDataObjectType = 0;
				}
				else
				{
					dataObjectStack.pop();
					currentDataObject = dataObjectStack.top();
					currentDataObjectType = &(currentDataObject->getType());
				}
			}
		}
		
		
		void SDOSAX2Parser::characters(const SDOXMLString& chars)
		{
			// WORK IN PROGRESS
			//if (dealingWithChangeSummary)
			//{
				//if (currentChange != 0)
				//{
				//	// we are within a change - this is text for a many valued
				//	// datatype property.
				//	currentChange.getElements()[
				//		currentChange.getElements().size -1].setValue(chars);
				//	cout << "changed element value:" << chars << endl;
				//}
				//if (createDeletes.size() > 0) 
				//{
				//	createDeletes[createDeletes.size()-1].setValue(chars);
				//	cout << "create/delete element value:"  << chars << endl;
				//}
				//return;
			//}

			if (ignoreEvents)
				return;
			
			// If currentPropertySetting is set (name is not null)
			// then we need to accumulate the value
			if (!currentPropertySetting.name.isNull())
			{
				currentPropertySetting.value = currentPropertySetting.value + chars;	
			}
			else
			{
				// If the current DataObject is a sequenced Type
				// then add this as text to the sequence
				if (currentDataObject && currentDataObjectType->isSequencedType())
				{
					SequencePtr seq = currentDataObject->getSequence();
					if (seq)
					{
						seq->addText(chars);
					}
				}
			}				
		}
		
		
		void SDOSAX2Parser::setCurrentDataObject(DataObjectPtr currentDO)
		{	
			currentDataObject = currentDO;
			dataObjectStack.push(currentDataObject);
			currentDataObjectType = &(currentDataObject->getType());
			if (rootDataObject == 0)
			{
				rootDataObject = currentDataObject;
			}
		}

		const SDOXMLString& SDOSAX2Parser::getSDOName(const Type& type, const SDOXMLString& localName)
		{
			XSDTypeInfo* typeInfo = (XSDTypeInfo*)((DASType*)&type)->getDASValue("XMLDAS::TypeInfo");
			if (typeInfo)
			{
				const TypeDefinition& typeDefinition = typeInfo->getTypeDefinition();
				XmlDasPropertyDefs::const_iterator propsIter;
				for (propsIter = typeDefinition.properties.begin(); propsIter != typeDefinition.properties.end(); propsIter++)
				{
					const PropertyDefinition& prop = *propsIter;
					if (prop.localname.equals(localName))
					{
						return prop.name;
					}
				}					

			}
			return localName;
		}


		std::istream& operator>>(std::istream& input, SDOSAX2Parser& parser)
		{
			parser.stream(input);
			
			return input;
		}
		
		std::istringstream& operator>>(std::istringstream& input, SDOSAX2Parser& parser)
		{
			parser.stream(input);
			
			return input;
		}

		// WORK IN PROGRESS
		// Dealing with change summaries...

		//createDelete::createDelete()
		//{
		//}

		//createDelete::createDelete(SDOXMLString intype) :
		//type(intype)
		//{
		//}

		//createDelete::~createDelete()
		//{
		//}

		//SDOXMLString createDelete::getValue()
		//{
		//	return value;
		//}

		//void createDelete::setValue(SDOXMLString s)
		//{
		//	value = s;
		//}

		//SDOXMLString createDelete::getType()
		//{
		//	return type;
		//}

		//changeElement::changeElement()
		//{
		//}

		//changeElement::changeElement(
		//		SDOXMLString inlocation,
		//		SDOXMLString inname,
		//		int index): location(inlocation), name(inname), index(inindex)
		//{
		//}

		//void changeElement::setValue(SDOXMLString v)
		//{
		//	value = v;
		//}

		//changeElement::~changeElement()
		//{
		//}
		
		//changeAttribute::changeAttribute()
		//{
		//}

		//changeAttribute::changeAttribute(
		//		SDOXMLString inname,
		//		SDOXMLString invalue) : name(inname), value(invalue)
		//{
		//}

		//change::change()
		//{
		//}

		//change::change(SDOXMLString inName): name(inName)
		//{
		//	location.push(inName);
		//};
		
		//change::~change()
		//{
		//}

		//SDOXMLString change::getName()
		//{
		//	return name;
		//}

		//std::vector<changeElement> change::getElements()
		//{
		//	return elements;
		//}

		//std::vector<changeAttribute> change::getAttributes()
		//{
		//	return attributes;
		//}

		//void change::moveLocation(SDOXMLString newloc)
		//{
		//	locations.pop();
		//	locations.push(locations.top() + "/" + newloc);
		//}

		//void change::popLocation()
		//{
		//	locations.pop();
		//}

		//void change::setLocation(SDOXMLString newloc)
		//{
		//	locations.push(newloc);
		//}
		
	} // End - namespace sdo
} // End - namespace commonj

