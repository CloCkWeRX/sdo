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

/* $Rev: 524004 $ $Date$ */

#ifndef _SDOUTILS_H_
#define _SDOUTILS_H_

#include "commonj/sdo/DataObject.h"
#include <map>
#include <string>

namespace commonj
{
    namespace sdo
    {
        

    /**
     * SDOUtils will provide utility functions.
     * The class currently holds a method to print data objects.
     */

    class SDOUtils
        {
            
        public:
            
            static SDO_API void printDataObject(std::ostream& out, DataObjectPtr d);                
            static SDO_API const char* SDOToXSD(const char* sdoname);
            static SDO_API const char*  XSDToSDO(const char* xsdname);
            static SDO_API void printTypes(std::ostream& out, DataFactoryPtr df);
            static SDOString replace(SDOString hostString, const char *fromString, const char *toString);
			static SDOString escapeHtmlEntities(SDOString inputString);
			static SDOString escapeHtmlEntitiesExcludingCData(SDOString inputString);
            
           /*
            * Markers used to represent the start and end of CDATA sections in the 
            * settings value. The noew XML CDATA markers are not used here because the 
            * XML string processing URL encodes parts of the markers
            */
           static SDO_API const char *CDataStartMarker;
           static SDO_API const char *XMLCDataStartMarker;
           static SDO_API const char *CDataEndMarker;
           static SDO_API const char *XMLCDataEndMarker;            
                        
        private:

            static bool populate();
            static bool populated;

            static void printDataObject(std::ostream& out, DataObjectPtr d, unsigned int incr);                
            static void printTabs(std::ostream& out, unsigned int incr);

            static std::map<std::string,std::string> XsdToSdo;
            static std::map<std::string,std::string> SdoToXsd;
			static std::map<char, std::string> HtmlEntities;

        };
    } // End - namespace sdo
} // End - namespace commonj


#endif //_SDOUTILS_H_
