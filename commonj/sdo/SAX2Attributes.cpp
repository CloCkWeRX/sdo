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

/* $Rev: 511929 $ $Date$ */

#include "commonj/sdo/SAX2Attributes.h"

namespace commonj
{
    namespace sdo
    {
        
/**
 * SAX2Attributes holds a list of attributes supplied by the SAX2 parser.
 */
        SAX2Attributes::SAX2Attributes(
            int nb_attributes,
            int nb_defaulted,
            const xmlChar **attrs) // localname/prefix/URI/value/end
        {
            for (int i=0; i < nb_attributes*5; i+=5)
            {
               // attributes.insert(attributes.end(), SAX2Attribute(&attrs[i]));
               attributes.push_back(SAX2Attribute(&attrs[i]));
            }
        }
        
        SAX2Attributes::SAX2Attributes()
        {    
        }

       SAX2Attributes::~SAX2Attributes()
        {    
        }
        
        const SAX2Attribute& SAX2Attributes::operator[] (int pos) const
        {
            return attributes[pos];
        }
        
        int SAX2Attributes::size () const
        {
            return attributes.size();
        }
        

        const SAX2Attribute* SAX2Attributes::getAttribute(const SDOXMLString& attributeName) const
        {
            for (unsigned int i=0; i < attributes.size(); i++)
            {
                if (attributes[i].getName().equalsIgnoreCase(attributeName))
                {
                    return &attributes[i];
                }
            }
            return NULL;
        }

        void SAX2Attributes::addAttribute(const SAX2Attribute& attr)
        {
            for (unsigned int i=0; i < attributes.size(); i++)
            {
                if (attributes[i].getUri().equals(attr.getUri()))
                {                    
                    if (attributes[i].getName().equals(attr.getName()))
                    {
                        // oeverwrite this attribute
                        attributes[i] = attr;
                        return;
                    }
                }
            }
            attributes.push_back(attr);
        }


        const SDOXMLString SAX2Attributes::nullValue;
        
        const SDOXMLString& SAX2Attributes::getValue(
            const SDOXMLString& attributeUri,
            const SDOXMLString& attributeName) const
        {
            for (unsigned int i=0; i < attributes.size(); i++)
            {
                if (attributes[i].getUri().equalsIgnoreCase(attributeUri))
                {                    
                    if (attributes[i].getName().equalsIgnoreCase(attributeName))
                    {
                        return attributes[i].getValue();
                    }
                }
            }
            
            return nullValue;
        }
        
        const SDOXMLString& SAX2Attributes::getValue(
            const SDOXMLString& attributeName) const
        {
            for (unsigned int i=0; i < attributes.size(); i++)
            {
                if (attributes[i].getName().equalsIgnoreCase(attributeName))
                {
                    return attributes[i].getValue();
                }
            }
            
            return nullValue;
        }
        
        
    } // End - namespace sdo
} // End - namespace commonj
