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

/* $Rev: 482588 $ $Date$ */

#include "commonj/sdo/PropertySetting.h"
#include "SDOString.h"
#include "SDOUtils.h"

namespace commonj
{
    namespace sdo
    {


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
			
			SDOString returnString = SDOUtils::replace(valueString, SDOUtils::CDataStartMarker, SDOUtils::XMLCDataStartMarker);
			returnString = SDOUtils::replace(returnString, SDOUtils::CDataEndMarker, SDOUtils::XMLCDataEndMarker);

			return returnString;
		}
        
    } // End - namespace sdo
} // End - namespace commonj
