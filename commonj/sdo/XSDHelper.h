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
| Author: Ed Slattery                                                  | 
+----------------------------------------------------------------------+ 

*/
/* $Id$ */
#ifndef _XSDHELPER_H_
#define _XSDHELPER_H_

#include "commonj/sdo/export.h"
#include "commonj/sdo/RefCountingObject.h"
#include "commonj/sdo/DataFactory.h"

namespace commonj
{
	namespace sdo
	{
		
		///////////////////////////////////////////////////////////////////////////
		// XSDHelper
		///////////////////////////////////////////////////////////////////////////
		
		class XSDHelper : public RefCountingObject
		{
		public:
			
			///////////////////////////////////////////////////////////////////////
			// define/defineFile
			//
			// Populates the data factory with Types and Properties from the schema
			// Loads from file, stream or char* buffer.
			// The return value is the URI of the root Type
			///////////////////////////////////////////////////////////////////////
			SDO_API virtual const char* defineFile(const char* schemaFile) = 0;
			SDO_API virtual const char* define(std::istream& schema) = 0;
			SDO_API virtual const char* define(const char* schema) = 0;
			

			SDO_API virtual char* generate(
				const TypeList& types,
				const char* targetNamespaceURI = "") = 0;
			SDO_API virtual void generate(
				const TypeList& types,
				std::ostream& outXsd,
				const char* targetNamespaceURI = "") = 0;
			SDO_API virtual void generateFile(
				const TypeList& types,
				const char* fileName,
				const char* targetNamespaceURI = "") = 0;

			///////////////////////////////////////////////////////////////////////
			// Destructor
			///////////////////////////////////////////////////////////////////////
			SDO_API virtual ~XSDHelper();

			// Return the DataFactory
			SDO_API virtual DataFactoryPtr getDataFactory() = 0;

			// Return the URI for the root Type
			SDO_API virtual const char* getRootTypeURI() = 0;
			
			///////////////////////////////////////////////////////////////////////
			// Parser error passing to upper layers
			///////////////////////////////////////////////////////////////////////
			virtual int  getErrorCount() const = 0;
			virtual const char* getErrorMessage(int errnum) const = 0;
		};
	} // End - namespace sdo
} // End - namespace commonj

#endif //_XSDHELPER_H_
