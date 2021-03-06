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

/* $Rev: 241789 $ $Date: 2007-08-25 00:50:26 +0930 (Sat, 25 Aug 2007) $ */

#include "commonj/sdo/DASValues.h"

using namespace std;
namespace commonj
{
    namespace sdo
    {
        
/** 
 * DASValues is a class to hold list of information required by data 
 * access services but not required by the data factory.
 */

        DASValues::~DASValues()
        {
            DASValue_MAP::iterator propertyIter;
            for (propertyIter = properties.begin() ; propertyIter != properties.end() ; ++propertyIter)
            {
                delete propertyIter->second;
            }
        }
        
        void DASValues::setDASValue(const char* name, DASValue* property)
        {
            DASValue* prop = getDASValue(name);
            if (prop != 0 && prop != property)
            {
                delete prop;
            }
            properties[name] = property;
        }
        
        DASValue* DASValues::getDASValue(const char* name)
        {
            DASValue_MAP::iterator propertyIter;
            propertyIter = properties.find(name);
            if(propertyIter != properties.end())
            {
                return propertyIter->second;
            }
            return NULL;
            
        }
        
    }
}
// end - namespace sdo


