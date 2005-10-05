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

#ifndef _SAX2PARSER_H_
#define _SAX2PARSER_H_

#include "SDOXMLString.h"
#include "SAX2Namespaces.h"
#include "SAX2Attributes.h"
#include "sstream"
namespace commonj
{
	namespace sdo
	{
		
		class SAX2Parser
		{
			
		public:
			
			SAX2Parser();
			
			virtual ~SAX2Parser();
			
			virtual int parse (const char* filename);
			
			virtual void startDocument();
			virtual void endDocument();

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
			
			virtual void characters(const SDOXMLString& chars);
			
			virtual void fatalError(const char* msg, va_list args);
			
			virtual void error(const char* msg, va_list args);

			virtual void stream(std::istream& input);
			
			friend std::istream& operator>>(std::istream& input, SAX2Parser& parser);
			
			bool parserError;

			char messageBuffer[1024];

			virtual const char* getCurrentFile() const;
		private:

			char* currentFile;


		};
	} // End - namespace sdo
} // End - namespace commonj


#endif //_SAX2PARSER_H_
