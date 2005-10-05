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

#ifndef _SDOXSDWRITER_H_
#define _SDOXSDWRITER_H_
#include <libxml/xmlwriter.h>
#include "SDOXMLString.h"
#include "DataObject.h"
#include "DataFactory.h"


namespace commonj
{
	namespace sdo
	{

		class SDOXSDWriter
		{

		public:

			SDOXSDWriter(DataFactoryPtr dataFactory = NULL);

			virtual ~SDOXSDWriter();

			int write(const TypeList& types, const SDOXMLString& targetNamespaceURI);

		protected:
			void setWriter(xmlTextWriterPtr textWriter);
			void freeWriter();

		private:
			xmlTextWriterPtr writer;

			int writeDO(DataObjectPtr dataObject, const SDOXMLString& elementName);

			DataFactoryPtr	dataFactory;

			SDOXMLString resolveName(const SDOXMLString& uri, const SDOXMLString& name, const SDOXMLString& targetNamespaceURI);

		};
	} // End - namespace sdo
} // End - namespace commonj


#endif //_SDOXSDWRITER_H_
