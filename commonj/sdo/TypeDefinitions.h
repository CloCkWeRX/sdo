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

#ifndef _TYPEDefinitions_H_
#define _TYPEDefinitions_H_


#include "commonj/sdo/TypeDefinition.h"

namespace commonj
{
    namespace sdo
    {
        
    class TypeDefinitionsImpl;
    
/**
 * TypeDefinitionsImpl holds a list information gathered from parsing the
 * XSD and used for creating Types
 */
        class SDO_API TypeDefinitions
        {
            
        public:
            TypeDefinitions();
            TypeDefinitions(const TypeDefinitions& tds);
            TypeDefinitions& operator=(const TypeDefinitions& tds);

            TypeDefinitions(const TypeDefinitionsImpl& tds);

            virtual ~TypeDefinitions();

            void addTypeDefinition(TypeDefinition& t);

            TypeDefinitionsImpl& getTypeDefinitions();

            unsigned int size();
 
        protected:
            TypeDefinitionsImpl* typedefinitions;
            void copy(const TypeDefinitions& tds);

        friend class XSDHelperImpl;
            
        };
    } // End - namespace sdo
} // End - namespace commonj


#endif //_TYPEDefinitions_H_
