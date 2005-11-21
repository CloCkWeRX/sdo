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

#ifndef _XSDHELPERIMPL_H_
#define _XSDHELPERIMPL_H_

#include "commonj/sdo/XSDHelper.h"
#include "commonj/sdo/export.h"
#include "commonj/sdo/SDOXMLString.h"
#include "commonj/sdo/SAX2Namespaces.h"
#include "commonj/sdo/SchemaInfo.h"
#include "commonj/sdo/TypeDefinitions.h"
#include "commonj/sdo/ParserErrorSetter.h"

namespace commonj
{
	namespace sdo
	{
		
		
		class XSDHelperImpl : public XSDHelper, ParserErrorSetter
		{
		public:
			
			// Constructor
			XSDHelperImpl(DataFactoryPtr dataFactory);
			
			// Destructor
			virtual ~XSDHelperImpl();
			
			///////////////////////////////////////////////////////////////////////
			// loadSchema/loadSchemaFile
			//
			// Populates the data factory with Types and Properties from the schema
			// Loads from file, stream or char* buffer
			///////////////////////////////////////////////////////////////////////
			virtual const char* defineFile(const char* schemaFile);
			virtual const char* define(std::istream& schema);
			virtual const char* define(const char* schema);
			
			virtual int  getErrorCount() const;
			virtual const char* getErrorMessage(int errnum) const;
			virtual void setError(const char* error);
			
			virtual char* generate(
				const TypeList& types,
				const char* targetNamespaceURI = ""
				);
			void generate(
				const TypeList& types,
				std::ostream& outXsd,
				const char* targetNamespaceURI = ""
				);
			virtual void generateFile(
				const TypeList& types,
				const char* fileName,
				const char* targetNamespaceURI = "");
			
			virtual DataFactoryPtr getDataFactory();
			
			// Return the URI for the root Type
			virtual const char* getRootTypeURI()
			{
				return schemaInfo.getTargetNamespaceURI();
			}
			
		private:
			virtual void clearErrors();

			void newSubstitute(const char* entryName,
			                   PropertyDefinition& prop);

			void addSubstitutes(PropertyDefinition& prop,
								TypeDefinition& ty);

			void defineTypes(TypeDefinitions& types);
			int	 parse(const char* source);
			
			// Instance variables
			DataFactoryPtr	dataFactory;	// metadata
			SchemaInfo		schemaInfo;

			std::vector<char*> parseErrors;
			
		};
		
	} // End - namespace sdo
} // End - namespace commonj

#endif // _XSDHELPERIMPL_H_
