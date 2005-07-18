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

#ifndef _SDOSCHEMASAX2PARSER_H_
#define _SDOSCHEMASAX2PARSER_H_

#include "SAX2Parser.h"
#include "SchemaInfo.h"
#include "stack"
#include "TypeDefinitions.h"
#include "XMLQName.h"

namespace commonj
{
	namespace sdo
	{
		
		namespace xmldas
		{
			
			class SDOSchemaSAX2Parser : public SAX2Parser
			{
				
			public:
				
				SDOSchemaSAX2Parser(SchemaInfo& schemaInfo);
				
				virtual ~SDOSchemaSAX2Parser();
				
				virtual void startElementNs(
					const SDOXMLString& localname,
					const SDOXMLString& prefix,
					const SDOXMLString& URI,
					const SAX2Namespaces& namespaces,
					const SAX2Attributes& attributes);			
				
				virtual void endElementNs(
					const SDOXMLString& localname,
					const SDOXMLString& prefix,
					const SDOXMLString& URI);
				
				const SDOXMLString& getTargetNamespaceURI() const {return schemaInfo.getTargetNamespaceURI();}
				
				TypeDefinitions& getTypeDefinitions() {return typeDefinitions;}
				
				friend std::istream& operator>>(std::istream& input, SDOSchemaSAX2Parser& parser);
				friend std::istringstream& operator>>(std::istringstream& input, SDOSchemaSAX2Parser& parser);
			private:
				virtual void startImport(
					const SDOXMLString& localname,
					const SDOXMLString& prefix,
					const SDOXMLString& URI,
					const SAX2Namespaces& namespaces,
					const SAX2Attributes& attributes);			
				
				virtual void startElement(
					const SDOXMLString& localname,
					const SDOXMLString& prefix,
					const SDOXMLString& URI,
					const SAX2Namespaces& namespaces,
					const SAX2Attributes& attributes);			
				
				virtual void startAttribute(
					const SDOXMLString& localname,
					const SDOXMLString& prefix,
					const SDOXMLString& URI,
					const SAX2Namespaces& namespaces,
					const SAX2Attributes& attributes);			
				
				virtual void startComplexType(
					const SDOXMLString& localname,
					const SDOXMLString& prefix,
					const SDOXMLString& URI,
					const SAX2Namespaces& namespaces,
					const SAX2Attributes& attributes);			
				
				virtual void startSimpleType(
					const SDOXMLString& localname,
					const SDOXMLString& prefix,
					const SDOXMLString& URI,
					const SAX2Namespaces& namespaces,
					const SAX2Attributes& attributes);			
				
				virtual void startRestriction(
					const SDOXMLString& localname,
					const SDOXMLString& prefix,
					const SDOXMLString& URI,
					const SAX2Namespaces& namespaces,
					const SAX2Attributes& attributes);			
				
				virtual void startExtension(
					const SDOXMLString& localname,
					const SDOXMLString& prefix,
					const SDOXMLString& URI,
					const SAX2Namespaces& namespaces,
					const SAX2Attributes& attributes);			
				
				virtual void startGroup(
					const SDOXMLString& localname,
					const SDOXMLString& prefix,
					const SDOXMLString& URI,
					const SAX2Namespaces& namespaces,
					const SAX2Attributes& attributes);			
				
				XMLQName resolveTypeName(
					const SDOXMLString& fullTypeName,
					const SAX2Namespaces& namespaces,
					SDOXMLString& uri,
					SDOXMLString& name);
				
				void setName(
					const SAX2Attributes& attributes,
					SDOXMLString& sdoname,
					SDOXMLString& localname);
				
				void setType(
					PropertyDefinition& property,
					const SAX2Attributes& attributes,
					const SAX2Namespaces& namespaces);
				
				void setTypeName(
					TypeDefinition& type,
					const SAX2Attributes& attributes);
				
				void setDefault(
					PropertyDefinition& thisProperty,
					const SAX2Attributes& attributes);
				
				
				SchemaInfo& schemaInfo;
				
				
				PropertyDefinition currentProperty;
				std::stack<PropertyDefinition>	propertyStack;
				void			 setCurrentProperty(const PropertyDefinition& property);
				void			 defineProperty();
				
				TypeDefinition   currentType;
				std::stack<TypeDefinition>	typeStack;
				void			 setCurrentType(const TypeDefinition& type);
				void			 defineType();
				
				TypeDefinitions typeDefinitions;			
				
			};
		} // End - namespace xmldas
	} // End - namespace sdo
} // End - namespace commonj
#endif //_SDOSCHEMASAX2PARSER_H_
