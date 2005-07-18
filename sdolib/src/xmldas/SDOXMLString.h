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

// SDOSaxHandler.h: interface for the SDOSaxHandler class.
//
//////////////////////////////////////////////////////////////////////

#pragma warning(disable: 4786)
#ifndef _SDOXMLString_H_
#define _SDOXMLString_H_

#include <libxml/xmlstring.h>
#include <iostream>

namespace commonj
{
	namespace sdo
	{
		namespace xmldas
		{
			
			class SDOXMLString
			{
			public:
				SDOXMLString();
				SDOXMLString(const xmlChar* xmlString);
				SDOXMLString(const char* localString);
				SDOXMLString(const SDOXMLString& str);	
				SDOXMLString(const xmlChar* str, int start, int len);	
				virtual ~SDOXMLString();
				
				SDOXMLString& operator=(const SDOXMLString& str);
				SDOXMLString operator+(const SDOXMLString& str) const;
				SDOXMLString& operator+=(const SDOXMLString& str);
				
		
				bool operator== (const SDOXMLString& str) const;
				bool equals(const xmlChar* xmlString) const;
				bool equals(const char* localString) const;
				bool equals(const SDOXMLString& str) const;
				bool equalsIgnoreCase(const xmlChar* xmlString) const;
				bool equalsIgnoreCase(const char* localString) const;
				bool equalsIgnoreCase(const SDOXMLString& str) const;

				bool operator< (const SDOXMLString& str) const;
				
				operator const char*() const {return (const char*) xmlForm;}
				operator const xmlChar*() const {return xmlForm;}
				
				friend std::ostream& operator<<(std::ostream& output, const SDOXMLString& str);
				
				bool isNull() const;
//				operator bool ();

				SDOXMLString toLower(
					unsigned int start = 0, 
					unsigned int length = 0);


				int firstIndexOf(const char ch) const;
				int lastIndexOf(const char ch) const;
				SDOXMLString substring(int start, int length) const;
				SDOXMLString substring(int start) const;
			private :
				xmlChar* xmlForm;
				void release();				
			};
		} // End - namespace xmldas
	} // End - namespace sdo
} // End - namespace commonj



#endif // _SDOXMLString_H_
