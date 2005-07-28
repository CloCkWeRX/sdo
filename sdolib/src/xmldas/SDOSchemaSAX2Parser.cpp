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

#include "SDOSchemaSAX2Parser.h"
#include "XSDPropertyInfo.h"
#include "XSDTypeInfo.h"

namespace commonj
{
	namespace sdo
	{
		namespace xmldas
		{
			
			SDOSchemaSAX2Parser::SDOSchemaSAX2Parser(SchemaInfo& schemaInf)
				: schemaInfo(schemaInf)
			{
			}
			
			SDOSchemaSAX2Parser::~SDOSchemaSAX2Parser()
			{
			}
			
			
			// ============================================================================
			// startElementNS
			// ============================================================================
			void SDOSchemaSAX2Parser::startElementNs(
				const SDOXMLString& localname,
				const SDOXMLString& prefix,
				const SDOXMLString& URI,
				const SAX2Namespaces& namespaces,
				const SAX2Attributes& attributes)
			{
				if (URI.equalsIgnoreCase("http://www.w3.org/2001/XMLSchema"))
				{
					///////////////////////////////////////////////////////////////////////
					// Handle schema
					// Set the URI from the targetNamespace of the xsd:schema element
					// Remember namespace mappings
					// Create the root Type
					///////////////////////////////////////////////////////////////////////
					if (localname.equalsIgnoreCase("schema"))
					{
						// Handle namespace definitions
						schemaInfo.getSchemaNamespaces() = namespaces;
						
						// Handle attributes
						for (int i=0; i < attributes.size(); i++)
						{
							if (attributes[i].getName().equalsIgnoreCase("targetNamespace"))
							{
								schemaInfo.setTargetNamespaceURI(attributes[i].getValue());
							}						
						}
						
						currentType.uri = schemaInfo.getTargetNamespaceURI();
						currentType.name = "RootType";
						
					} // end schema handling

					// Handle <import> of other schema
					else if (localname.equalsIgnoreCase("import"))
					{
						startImport(localname, prefix, URI, namespaces, attributes);
					}
					
					///////////////////////////////////////////////////////////////////////
					// Handle elements and attributes
					// These become Properties of the current Type
					// ?? Any special handling of global elements???
					///////////////////////////////////////////////////////////////////////
					else if (localname.equalsIgnoreCase("element"))
					{
						startElement(localname, prefix, URI, namespaces, attributes);
					}
					else if (localname.equalsIgnoreCase("attribute"))
					{
						startAttribute(localname, prefix, URI, namespaces, attributes);
					}
					
					
					///////////////////////////////////////////////////////////////////////
					// Handle complexType
					// These become new types
					///////////////////////////////////////////////////////////////////////
					else if (localname.equalsIgnoreCase("complexType"))
					{
						startComplexType(localname, prefix, URI, namespaces, attributes);
					} // end complexType handling
					
					else if (localname.equalsIgnoreCase("choice") 
						|| localname.equalsIgnoreCase("sequence")
						|| localname.equalsIgnoreCase("all"))
					{
						startGroup(localname, prefix, URI, namespaces, attributes);
					} // end Group handling
					
					///////////////////////////////////////////////////////////////////////
					// Handle simpleType
					// These become new types
					///////////////////////////////////////////////////////////////////////
					else if (localname.equalsIgnoreCase("simpleType"))
					{
						startSimpleType(localname, prefix, URI, namespaces, attributes);
					} // end complexType handling
					
					else if (localname.equalsIgnoreCase("restriction"))
					{
						startRestriction(localname, prefix, URI, namespaces, attributes);
					}
					
					else if (localname.equalsIgnoreCase("extension"))
					{
						startExtension(localname, prefix, URI, namespaces, attributes);
					}
				}
				
			}			
			
			
			// ============================================================================
			// endElementNs
			// ============================================================================			
			void SDOSchemaSAX2Parser::endElementNs(
				const SDOXMLString& localname,
				const SDOXMLString& prefix,
				const SDOXMLString& URI)
			{
				if (URI.equalsIgnoreCase("http://www.w3.org/2001/XMLSchema"))
				{
					///////////////////////////////////////////////////////////////////////
					// Handle complexType
					// Pop the Type off our stack
					///////////////////////////////////////////////////////////////////////
					if (localname.equalsIgnoreCase("complexType"))
					{
						defineType();
					} // end complexType handling
					else if (localname.equalsIgnoreCase("simpleType"))
					{
						defineType();
					}
					
					else if (localname.equalsIgnoreCase("schema"))
					{
						defineType();
					} // end complexType handling
					
					else if (localname.equalsIgnoreCase("element")
						|| localname.equalsIgnoreCase("attribute"))
					{
						// PropertyDefinition should now be complete
						defineProperty();
					}
					else if (localname.equalsIgnoreCase("choice") 
						|| localname.equalsIgnoreCase("sequence")
						|| localname.equalsIgnoreCase("all"))
					{
						currentType.isMany = false;
					}
					
				}
			}
			
			
			// ============================================================================
			// startImport
			// ============================================================================
			void SDOSchemaSAX2Parser::startImport(
				const SDOXMLString& localname,
				const SDOXMLString& prefix,
				const SDOXMLString& URI,
				const SAX2Namespaces& namespaces,
				const SAX2Attributes& attributes)
			{		
				SDOXMLString schemaNamespace = attributes.getValue("namespace");
				SDOXMLString schemaLocation = attributes.getValue("schemaLocation");
				if (!schemaLocation.isNull())
				{
					SchemaInfo schemaInf;
					SDOSchemaSAX2Parser schemaParser(schemaInf);
					schemaParser.parse(schemaLocation);
					TypeDefinitions& typedefs = schemaParser.getTypeDefinitions();
					XMLDAS_TypeDefs types = typedefs.types;
					XMLDAS_TypeDefs::iterator iter;
					for (iter=types.begin(); iter != types.end(); iter++)
					{
						typeDefinitions.types.insert(*iter);
					}
				}				
			}
			
			// ============================================================================
			// startElement
			// ============================================================================
			void SDOSchemaSAX2Parser::startElement(
				const SDOXMLString& localname,
				const SDOXMLString& prefix,
				const SDOXMLString& URI,
				const SAX2Namespaces& namespaces,
				const SAX2Attributes& attributes)
			{
				PropertyDefinition thisProperty;
				
				thisProperty.isElement =  true;
				
				setName(attributes,
					thisProperty.name,
					thisProperty.localname);
				
				setType(thisProperty, attributes, namespaces);
				
				SDOXMLString maxOccurs = attributes.getValue("maxOccurs");
				if (!maxOccurs.isNull())
				{
					if (!maxOccurs.equalsIgnoreCase("1"))
					{
						thisProperty.isMany = true;
					}
				}
				
				// count the number of elements in the group
				if (currentType.isMany)
				{
					currentType.groupElementCount++;
				}
				
				setCurrentProperty(thisProperty);	
				
			}
			
			// ============================================================================
			// startAttribute
			// ============================================================================
			void SDOSchemaSAX2Parser::startAttribute(
				const SDOXMLString& localname,
				const SDOXMLString& prefix,
				const SDOXMLString& URI,
				const SAX2Namespaces& namespaces,
				const SAX2Attributes& attributes)
			{
				PropertyDefinition thisProperty;
				
				thisProperty.isElement =  false;
				
				setName(attributes,
					thisProperty.name,
					thisProperty.localname);
				
				setType(thisProperty, attributes, namespaces);
				
				setCurrentProperty(thisProperty);					
			}
			
			// ============================================================================
			// startComplexType
			// ============================================================================
			void SDOSchemaSAX2Parser::startComplexType(
				const SDOXMLString& localname,
				const SDOXMLString& prefix,
				const SDOXMLString& URI,
				const SAX2Namespaces& namespaces,
				const SAX2Attributes& attributes)
			{
				TypeDefinition thisType; // set defaults
				thisType.uri=schemaInfo.getTargetNamespaceURI();
				
				setTypeName(thisType, attributes);
				for (int i=0; i < attributes.size(); i++)
				{
					// If sdo:sequence="true" or mixed="true" it is sequenced
					if ( (attributes[i].getUri().equalsIgnoreCase("commonj.sdo/xml")
						&& attributes[i].getName().equalsIgnoreCase("sequence"))
						|| attributes[i].getName().equalsIgnoreCase("mixed"))
					{	
						if (attributes[i].getValue().equals("true"))
						{
							thisType.isSequenced = true;
						}
					}
				}
				
				setCurrentType(thisType);				
			}
			
			// ============================================================================
			// startSimpleType
			// ============================================================================
			void SDOSchemaSAX2Parser::startSimpleType(
				const SDOXMLString& localname,
				const SDOXMLString& prefix,
				const SDOXMLString& URI,
				const SAX2Namespaces& namespaces,
				const SAX2Attributes& attributes)
			{
				TypeDefinition thisType; // set defaults
				thisType.uri=schemaInfo.getTargetNamespaceURI();
				thisType.dataType = true;
				
				setTypeName(thisType, attributes);
				
				setCurrentType(thisType);				
			}
			
			// ============================================================================
			// startRestriction
			// ============================================================================
			void SDOSchemaSAX2Parser::startRestriction(
				const SDOXMLString& localname,
				const SDOXMLString& prefix,
				const SDOXMLString& URI,
				const SAX2Namespaces& namespaces,
				const SAX2Attributes& attributes)
			{
				SDOXMLString base = attributes.getValue("base");
				if (!base.isNull())
				{
					// Resolve typename to uri:name
					XMLQName qname = resolveTypeName(
						base,
						namespaces,
						currentType.parentTypeUri,
						currentType.parentTypeName);


					if(qname.getLocalName().equals("QName"))
					{
						currentType.isQName = true;
					}					
				}
			}
			
			// ============================================================================
			// startExtension
			// ============================================================================
			void SDOSchemaSAX2Parser::startExtension(
				const SDOXMLString& localname,
				const SDOXMLString& prefix,
				const SDOXMLString& URI,
				const SAX2Namespaces& namespaces,
				const SAX2Attributes& attributes)
			{
				SDOXMLString base = attributes.getValue("base");
				if (!base.isNull())
				{
					SDOXMLString typeUri;
					SDOXMLString typeName;
					// Resolve typename to uri:name
					XMLQName qname = resolveTypeName(
						base,
						namespaces,
						typeUri,
						typeName);
					
					// If extending a simple type (an SDO DataType) we create a 
					// Property named "value" of this type rather than set the
					// simple type as a base
					
					// ?? Does this only apply within a <simpleContent> tag??
					if (typeUri.equalsIgnoreCase(Type::SDOTypeNamespaceURI))
					{
						PropertyDefinition thisProperty;
						thisProperty.name = "value";
						thisProperty.typeUri = typeUri; 
						thisProperty.typeName = typeName; 
						thisProperty.fullTypeName = base; 
						thisProperty.isContainment = false;
												
						if(qname.getLocalName().equals("QName"))
						{
							thisProperty.isQName = true;
						}					

						setCurrentProperty(thisProperty);
						defineProperty();
					} 
					else
					{
						currentType.parentTypeUri = typeUri;
						currentType.parentTypeName = typeName;
					}
				}
			}
			
			// ============================================================================
			// startGroup
			// ============================================================================
			void SDOSchemaSAX2Parser::startGroup(
				const SDOXMLString& localname,
				const SDOXMLString& prefix,
				const SDOXMLString& URI,
				const SAX2Namespaces& namespaces,
				const SAX2Attributes& attributes)
			{
				SDOXMLString maxOccurs = attributes.getValue("maxOccurs");
				if (!maxOccurs.isNull())
				{
					if (!maxOccurs.equalsIgnoreCase("1"))
					{
						currentType.isMany = true;
					}
				}
			}
			
			// ============================================================================
			// setCurrentType
			// ============================================================================
			void SDOSchemaSAX2Parser::setCurrentType(const TypeDefinition& type)
			{				
				typeStack.push(currentType);
				currentType = type;
			}
			
			// ============================================================================
			// defineType
			// ============================================================================
			void SDOSchemaSAX2Parser::defineType()
			{				
				SDOXMLString typeQname = TypeDefinitions::getTypeQName(currentType.uri, currentType.localname);
				typeDefinitions.types[typeQname] = currentType;
				
				if (currentProperty.typeName.isNull())
				{
					// Set the type name to the name of this type
					currentProperty.typeUri = currentType.uri;
					currentProperty.typeName = currentType.localname;
				}
				
				// Set this Type as sequenced of more than one element in a group definition
				if (currentType.groupElementCount > 1)
				{
					currentType.isSequenced = true;
				}
				
				if (typeStack.size() != 0)
				{
					currentType = typeStack.top();				
					typeStack.pop();
				}
				else
				{
					currentType = TypeDefinition();
				}
			}
			
			// ============================================================================
			// setCurrentProperty
			// ============================================================================
			void SDOSchemaSAX2Parser::setCurrentProperty(const PropertyDefinition& prop)
			{				
				propertyStack.push(currentProperty);
				currentProperty = prop;
			}
			
			// ============================================================================
			// defineProperty
			// ============================================================================
			void SDOSchemaSAX2Parser::defineProperty()
			{	
				if (currentProperty.typeName.isNull())
				{
					// Set the type of this property to default (sdo:String)
					currentProperty.typeUri = Type::SDOTypeNamespaceURI;
					currentProperty.typeName = "String";
				}
				
				// Set isMany if property isMany OR if containing type isMany
				// NOTE: The above comment is as per the SDO2.0 spec however this does not
				// work when serializing a sequence containing a single-valued property and
				// then deserializing.
				// currentProperty.isMany = currentProperty.isMany || currentType.isMany;
				
				currentType.properties.insert(currentType.properties.end(), currentProperty);
				if (propertyStack.size() != 0)
				{
					currentProperty = propertyStack.top();				
					propertyStack.pop();
				}
				else
					currentProperty = PropertyDefinition();
			}
			
			// ============================================================================
			// setDefault
			// ============================================================================
			void SDOSchemaSAX2Parser::setDefault(
				PropertyDefinition& thisProperty,
				const SAX2Attributes& attributes)
			{
				thisProperty.defaultValue = attributes.getValue("fixed");
				if (!thisProperty.defaultValue.isNull())
				{
					thisProperty.isReadOnly = true;
				}
				else
				{
					thisProperty.defaultValue = attributes.getValue("default");
				}
			}

			// ============================================================================
			// setName
			// ============================================================================
			void SDOSchemaSAX2Parser::setName(
				const SAX2Attributes& attributes,
				SDOXMLString& sdoname,
				SDOXMLString& localname)
			{
				for (int i=0; i < attributes.size(); i++)
				{
					// Handle sdo: annotations
					if (attributes[i].getUri().equalsIgnoreCase("commonj.sdo/xml"))
					{
						// sdo:name overrides the property name
						if (attributes[i].getName().equalsIgnoreCase("name"))
						{
							sdoname = attributes[i].getValue();
						}
					}
					else
					{
						
						if (attributes[i].getName().equalsIgnoreCase("name"))
						{
							localname  = attributes[i].getValue();
							// If name is already set it must have been an 
							// override using sdo:name
							if (sdoname.isNull())
							{
								sdoname  = localname;
							}
						}
					}
				}				
			}
			
			// ============================================================================
			// setType
			// ============================================================================
			void SDOSchemaSAX2Parser::setType(
				PropertyDefinition& property,
				const SAX2Attributes& attributes,
				const SAX2Namespaces& namespaces)
			{
				property.fullLocalTypeName = attributes.getValue("type");
				if (!property.fullLocalTypeName.isNull())
				{
					XMLQName qname(property.fullLocalTypeName,schemaInfo.getSchemaNamespaces(), namespaces);
					if (qname.getLocalName().equals("IDREF")
						|| qname.getLocalName().equals("IDREFS")
						|| qname.getLocalName().equals("anyURI"))
					{
						property.fullTypeName = attributes.getValue("commonj.sdo/xml","propertyType");
						property.isIDREF = true;
						property.isContainment = false;
						if (qname.getLocalName().equals("IDREFS"))
						{
							property.isMany = true;
						}
					}
					else if (qname.getLocalName().equals("ID"))
					{
						property.isID = true;
						currentType.IDPropertyName = property.name;
					}

					else
					{
						property.fullTypeName = attributes.getValue("commonj.sdo/xml","dataType");
					}
				}
				
				else 
				{
					property.fullLocalTypeName = attributes.getValue("ref");
					if (!property.fullLocalTypeName.isNull())
					{
						property.isReference = true;
					}
				}
				
				if (property.fullTypeName.isNull())
				{
					property.fullTypeName = property.fullLocalTypeName;
				}
				
				if (!property.fullTypeName.isNull())
				{
					// Resolve typename to uri:name
					XMLQName qname = resolveTypeName(
						property.fullTypeName,
						namespaces,
						property.typeUri,
						property.typeName);

					if(qname.getLocalName().equals("QName"))
					{
						property.isQName = true;
					}					
				}
				
			}
			
			// ============================================================================
			// setTypeName
			// ============================================================================
			void SDOSchemaSAX2Parser::setTypeName(
				TypeDefinition& type,
				const SAX2Attributes& attributes)
			{
				setName(attributes, type.name, type.localname);
				// If localname is not set it is anonymous so use the enclosing element name
				if (type.localname.isNull())
				{
					type.localname = currentProperty.name;
				}
				
				// Set SDO name if not specified
				if (type.name .isNull())
				{
					type.name  = type.localname ;
				}				
			}
			
			std::istream& operator>>(std::istream& input, SDOSchemaSAX2Parser& parser)
			{
				parser.stream(input);
				
				return input;
			}
			
			std::istringstream& operator>>(std::istringstream& input, SDOSchemaSAX2Parser& parser)
			{
				parser.stream(input);
				
				return input;
			}

			// ============================================================================
			// resolveTypeName
			// ============================================================================
			XMLQName SDOSchemaSAX2Parser::resolveTypeName(
				const SDOXMLString& fullTypeName,
				const SAX2Namespaces& namespaces,
				SDOXMLString& uri,
				SDOXMLString& name) 
			{
				XMLQName qname(fullTypeName, 
					schemaInfo.getSchemaNamespaces(),
					namespaces);

				uri = qname.getURI();
				name = qname.getLocalName();

				///////////////////////////////////////////////////////////////////////
				// Map the xsd types to SDO Types
				///////////////////////////////////////////////////////////////////////
				if (qname.getURI().equalsIgnoreCase("http://www.w3.org/2001/XMLSchema"))
				{
					uri = Type::SDOTypeNamespaceURI;
					if (qname.getLocalName().equalsIgnoreCase("string"))
					{
						name = "String";
					}
					else if (qname.getLocalName().equalsIgnoreCase("anyType"))
					{
						name = "DataObject";
					}
					else if (qname.getLocalName().equalsIgnoreCase("int"))
					{
						name = "Integer";
					}
					else if (qname.getLocalName().equalsIgnoreCase("integer"))
					{
						name = "Integer";
					}
					else if (qname.getLocalName().equalsIgnoreCase("negativeInteger"))
					{
						name = "Integer";
					}
					else if (qname.getLocalName().equalsIgnoreCase("nonNegativeInteger"))
					{
						name = "Integer";
					}
					else if (qname.getLocalName().equalsIgnoreCase("positiveInteger"))
					{
						name = "Integer";
					}
					else if (qname.getLocalName().equalsIgnoreCase("nonPositiveInteger"))
					{
						name = "Integer";
					}
					else if (qname.getLocalName().equalsIgnoreCase("unsignedLong"))
					{
						name = "Integer";
					}
					else if (qname.getLocalName().equalsIgnoreCase("unsignedShort"))
					{
						name = "Integer";
					}
					else if (qname.getLocalName().equalsIgnoreCase("unsignedInt"))
					{
						name = "Long";
					}
					else if (qname.getLocalName().equalsIgnoreCase("long"))
					{
						name = "Long";
					}
					else if (qname.getLocalName().equalsIgnoreCase("double"))
					{
						name = "Double";
					}
					else if (qname.getLocalName().equalsIgnoreCase("short"))
					{
						name = "Short";
					}
					else if (qname.getLocalName().equalsIgnoreCase("unsignedByte"))
					{
						name = "Short";
					}
					else if (qname.getLocalName().equalsIgnoreCase("float"))
					{
						name = "Float";
					}
					else if (qname.getLocalName().equalsIgnoreCase("boolean"))
					{
						name = "Boolean";
					}
					else if (qname.getLocalName().equalsIgnoreCase("byte"))
					{
						name = "Byte";
					}
					else if (qname.getLocalName().equalsIgnoreCase("base64Binary"))
					{
						name = "Bytes";
					}
					else if (qname.getLocalName().equalsIgnoreCase("hexBinary"))
					{
						name = "Bytes";
					}
					else if (qname.getLocalName().equalsIgnoreCase("anyURI"))
					{
						name = "URI";
					}
					else if (qname.getLocalName().equalsIgnoreCase("QName"))
					{
						name = "URI";
					}
					else
					{
						// Default unknown xs: types to string??
						name = "String";
					}
				}
				
				// Temporary hack: ChangeSummaryType is ChangeSummary in core
				else if (qname.getURI().equalsIgnoreCase(Type::SDOTypeNamespaceURI))
				{
					if (qname.getLocalName().equalsIgnoreCase("ChangeSummaryType"))
					{
						name = "ChangeSummary";
					}
					
				}

				return qname;
			}
			
		} // End - namespace xmldas
	} // End - namespace sdo
	
} // End - namespace commonj

