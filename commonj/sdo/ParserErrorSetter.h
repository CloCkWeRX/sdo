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

/* $Rev: 492097 $ $Date$ */

#ifndef _PARSER_ERROR_SETTER_H_
#define _PARSER_ERROR_SETTER_H_

#include "commonj/sdo/export.h"

#include "commonj/sdo/disable_warn.h"

#include <vector>
#include <map>
#include "libxml/xmlstring.h"

namespace commonj{
namespace sdo{

template<class _Kty>
struct HashCompare
{
    bool operator()(const _Kty& _Keyval1, const _Kty& _Keyval2) const
    {
        return strcmp((char*)_Keyval1, (char*)_Keyval2) < 0;
    }
};

class SDOSchemaSAX2Parser;
typedef std::map<xmlChar*, SDOSchemaSAX2Parser*, HashCompare<xmlChar*> > LocationParserMap;
struct ParsedLocations: public LocationParserMap
{
    virtual ~ParsedLocations();
};

/**
 * The ParserErrorSetter builds a list of all the errors which 
 * occurred during a parse, so they can be displayed for the
 * user of an XSDHelper or XMLHelper
 */

class ParserErrorSetter
{
public:
    virtual ~ParserErrorSetter();
    virtual void setError(const char* message) = 0;
    virtual void clearErrors() = 0;

    SDOSchemaSAX2Parser* parseIfNot(const void* location, bool loadImportNamespace = false, const void* base=0);
protected:
    ParsedLocations parsedLocations;
};
};
};

#endif
