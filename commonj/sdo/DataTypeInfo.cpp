/*
*
*  Copyright 2007 The Apache Software Foundation or its licensors, as applicable.
*
*  Licensed under the Apache License, Version 2.0 (the "License");
*  you may not use this file except in compliance with the License.
*  You may obtain a copy of the License at
*
*     http://www.apache.org/licenses/LICENSE-2.0
*
*  Unless required by applicable law or agreed to in writing, software
*  distributed under the License is distributed on an "AS IS" BASIS,
*  WITHOUT WARRANTIES OR CONDITIONS OF ANY KIND, either express or implied.
*  See the License for the specific language governing permissions and
*  limitations under the License.
*/

/* $Rev: 502599 $ $Date$ */

#include "commonj/sdo/SDOString.h"
#include "commonj/sdo/DataTypeInfo.h"

namespace commonj
{
  namespace sdo
  {

     const SDOString& DataTypeInfo::convertTypeEnumToString(TrueDataType dataType)
     {
        if ((dataType < 0) || (dataType > TDTWideString))
        {
           return rawTypeNames[TDTWideString + 1];
        }
        else
        {
           return rawTypeNames[dataType];
        }
     }

     const SDOString DataTypeInfo::rawTypeNames[] = {"Boolean", // 0
                                                     "Byte",
                                                     "Character",
                                                     "Short",
                                                     "Integer",
                                                     "Long", // 5
                                                     "Float",
                                                     "Double",
                                                     "Date",
                                                     "String",
                                                     "Bytes", // 10
                                                     "String",
                                                     "No Such Type"};
  }
}
