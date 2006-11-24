/*
 * Licensed to the Apache Software Foundation (ASF) under one
 * or more contributor license agreements.  See the NOTICE file
 * distributed with this work for additional information
 * regarding copyright ownership.  The ASF licenses this file
 * to you under the Apache License, Version 2.0 (the
 * "License"); you may not use this file except in compliance
 * with the License.  You may obtain a copy of the License at
 * 
 *   http://www.apache.org/licenses/LICENSE-2.0
 *   
 * Unless required by applicable law or agreed to in writing,
 * software distributed under the License is distributed on an
 * "AS IS" BASIS, WITHOUT WARRANTIES OR CONDITIONS OF ANY
 * KIND, either express or implied.  See the License for the
 * specific language governing permissions and limitations
 * under the License.
 */

/* $Rev: 452786 $ $Date$ */

#include "commonj/sdo/PropertySetting.h"
#include "SDOString.h"

namespace commonj
{
    namespace sdo
    {
	    const char *PropertySetting::CDataStartMarker    = "XXXCDATA@STARTXXX";
		const char *PropertySetting::XMLCDataStartMarker = "<![CDATA[";
	    const char *PropertySetting::CDataEndMarker      = "XXXCDATA@ENDX";
		const char *PropertySetting::XMLCDataEndMarker   = "]]>";

        PropertySetting::PropertySetting()
            : dataObject(NULL), isNULL(false), isIDREF(false), pendingUnknownType(false)

        {
        }
                        
        PropertySetting::PropertySetting(DataObjectPtr dataObj, const SDOXMLString& propertyName,
            bool isNull, bool IDREF)
            : dataObject(dataObj), name(propertyName), isNULL(isNull),isIDREF(IDREF),pendingUnknownType(false)
        {
        }

        PropertySetting::~PropertySetting()
        {
        }

		/*
		 * A local utility function that replaces one string with and another within a
		 * host string and adjusts the lenght of the host string accordingly.
		 */ 
		SDOString replace(SDOString hostString, const char *fromString, const char *toString)
		{
			SDOString returnString("");

			// find and replace all occurances of fromString with toString. The start, end
			// and length variables are used to indicate the start, end and length
			// of the text sections to be copied from the host string to the return
			// string. toString is appended in between these copied sections because the
			// string is broken whenever fronString is found
			std::string::size_type start  = 0;
			std::string::size_type end    = hostString.find(fromString, 0);
			std::string::size_type length = 0;

			while ( end != std::string::npos )
			{
				// copy all the text up to the fromString
				length = end - start;
                returnString.append(hostString.substr(start, length));

				// add in the toString
				returnString.append(toString);

				// find the next fromString
				start = end + strlen(fromString);
				end = hostString.find(fromString, start);
			}

			// copy any text left at the end of the host string
            returnString.append(hostString.substr(start));

			return returnString;
		}

		/*
		 * The value that PropertySetting uses to hold values passing from 
		 * an input XML stream to data object properties is currently an SDOXMLString
		 * SDOXMLString use libxml2 functions to do it's thing and in the process messes
		 * up CDATA markers. To avoid this we use our own version of CDATA makers and 
		 * use this method to replace them with the real ones just before the PropertSetting
		 * gets committed to the SDO proper in SDOSAX2Parser
		 */
        SDOString PropertySetting::getStringWithCDataMarkers()
		{
			SDOString valueString((const char*)value);
			
			SDOString returnString = replace(valueString, CDataStartMarker, XMLCDataStartMarker);
			returnString = replace(returnString, CDataEndMarker, XMLCDataEndMarker);

			return returnString;
		}
        
    } // End - namespace sdo
} // End - namespace commonj
