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

/* $Rev: 505382 $ $Date$ */

#ifndef _SDODATACONVERTER_H_
#define _SDODATACONVERTER_H_

#include "commonj/sdo/DataTypeInfo.h"
#include "commonj/sdo/SDODate.h"

namespace commonj
{
   namespace sdo
   {
      // The SDODataConverter class provides methods that convert between the
      // many primitive data types that SDO must support. In general, the
      // inputs to a method are a DataTypeInfo::SDODataTypeUnion that gives
      // the source value that is to be converted and a
      // DataTypeInfo::TrueDataType that says which member of the union is
      // actually set. The target of the conversion is determined by the
      // method name.

      class SDODataConverter
      {

         public:
            static const bool convertToBoolean(const DataTypeInfo::SDODataTypeUnion& sourceValue,
                                               const DataTypeInfo::TrueDataType& dataType);
            static const char convertToByte(const DataTypeInfo::SDODataTypeUnion& sourceValue,
                                            const DataTypeInfo::TrueDataType& dataType);
            static const wchar_t convertToCharacter(const DataTypeInfo::SDODataTypeUnion& sourceValue,
                                                    const DataTypeInfo::TrueDataType& dataType);
            static const short convertToShort(const DataTypeInfo::SDODataTypeUnion& sourceValue,
                                              const DataTypeInfo::TrueDataType& dataType);
            static const long convertToInteger(const DataTypeInfo::SDODataTypeUnion& sourceValue,
                                               const DataTypeInfo::TrueDataType& dataType);
            static const int64_t convertToLong(const DataTypeInfo::SDODataTypeUnion& sourceValue,
                                               const DataTypeInfo::TrueDataType& dataType);
            static const float convertToFloat(const DataTypeInfo::SDODataTypeUnion& sourceValue,
                                              const DataTypeInfo::TrueDataType& dataType);
            static const double convertToDouble(const DataTypeInfo::SDODataTypeUnion& sourceValue,
                                                const DataTypeInfo::TrueDataType& dataType);
            static const SDODate convertToDate(const DataTypeInfo::SDODataTypeUnion& sourceValue,
                                               const DataTypeInfo::TrueDataType& dataType);

            static SDOString* convertToSDOString(const DataTypeInfo::SDODataTypeUnion& sourceValue,
                                                 const DataTypeInfo::TrueDataType& dataType);

            static unsigned int convertToBytes(const DataTypeInfo::SDODataTypeUnion& sourceValue,
                                               const DataTypeInfo::TrueDataType& dataType,
                                               char* outptr,
                                               unsigned int max_length);
            static unsigned int convertToString(const DataTypeInfo::SDODataTypeUnion& sourceValue,
                                                const DataTypeInfo::TrueDataType& dataType,
                                                wchar_t* outptr,
                                                unsigned int max_length);
			static unsigned int precision;
         private:
            // We sometimes need to convert primitive data types into an
            // equivalent string representation and for that we need a
            // temporary buffer. Rather than fret too much about how big each
            // one can be we choose a size that should be adequate for any of them

            static const int MAX_TRANSIENT_SIZE;
      };
   }
}

#endif // _SDODATACONVERTER_H
