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

/* $Rev: 506932 $ $Date$ */

#ifndef _SDOValue_H_
#define _SDOValue_H_

#include "commonj/sdo/export.h"
#include "commonj/sdo/SDODate.h"
#include "commonj/sdo/SDOString.h"
#include "commonj/sdo/SDODataConverter.h"
#include "commonj/sdo/DataTypeInfo.h"

#include <iostream>

namespace commonj
{
   namespace sdo
   {

      // The SDOValue class provides a discriminated data type that wraps the
      // many different primitive data types that SDO must use. This allows
      // common treatment of SDO values by all methods except those that
      // really must do different things for different types. The key point is
      // that instances of the class combine a union with and enum that says
      // which member of the union is actually present. The enum also allows
      // for the possibility that the value is unset or has been explicitly
      // set to null.

      class SDOValue
      {
         private:
            // Entities with copy constructors/destructors are not allowed in a union,
            // since in general, the compiler doesn't know what type is
            // actually in there so it can't know which constructor/destructor
            // to call, hence the use of pointers for certain datatypes.
            DataTypeInfo::SDODataTypeUnion value;

            DataTypeInfo::RawDataType typeOfValue;

            mutable SDOString* transient_buffer;

         public:

            // Constructors
            SDO_API SDOValue(bool inValue) : 
               typeOfValue(DataTypeInfo::SDObool), transient_buffer(0)
            {
               value.Boolean = inValue;
            }
            SDO_API SDOValue(float inValue) : 
               typeOfValue(DataTypeInfo::SDOfloat), transient_buffer(0)
            {
               value.Float = inValue;
            }
            SDO_API SDOValue(long double inValue) : 
               typeOfValue(DataTypeInfo::SDOdouble), transient_buffer(0)
            {
               value.Double = inValue;
            }
            SDO_API SDOValue(short inValue) : 
               typeOfValue(DataTypeInfo::SDOshort), transient_buffer(0)
            {
               value.Short = inValue;
            }
#if 32 != 64
            SDO_API SDOValue(long inValue) : 
               typeOfValue(DataTypeInfo::SDOlong), transient_buffer(0)
            {
               value.Integer = inValue;
            }
#endif
            SDO_API SDOValue(int64_t inValue) : 
               typeOfValue(DataTypeInfo::SDOint64_t), transient_buffer(0)
            {
               value.Int64 = inValue;
            }
            SDO_API SDOValue(char inValue) : 
               typeOfValue(DataTypeInfo::SDOchar), transient_buffer(0)
            {
               value.Character = inValue;
            }
            SDO_API SDOValue(wchar_t inValue) : 
               typeOfValue(DataTypeInfo::SDOwchar_t), transient_buffer(0)
            {
               value.WideChar = inValue;
            }
            SDO_API SDOValue(const SDODate inValue) : 
               typeOfValue(DataTypeInfo::SDOSDODate), transient_buffer(0)
            {
               value.Date = new SDODate(inValue.getTime());
            }
            SDO_API SDOValue(const SDOString& inValue) : 
               typeOfValue(DataTypeInfo::SDOCString), transient_buffer(0)
            {
               value.TextString = new SDOString(inValue);
            }

            SDO_API SDOValue(const char* inValue) : 
               typeOfValue(DataTypeInfo::SDOCString), transient_buffer(0)
            {
               value.TextString = new SDOString(inValue);
            }

            SDO_API SDOValue(const char* inValue, unsigned int len);

            SDO_API SDOValue(const wchar_t* inValue, unsigned int len);

            SDO_API SDOValue() : typeOfValue(DataTypeInfo::SDOunset), transient_buffer(0)
            {
            }
            //End of Constructors

            // Copy constructor
            SDO_API SDOValue(const SDOValue& inValue);
            
            // Copy assignment
            SDO_API SDOValue& operator=(const SDOValue& inValue);
            
            // Destructor
            SDO_API virtual ~SDOValue();

            inline SDO_API bool isSet() const
            {
               return (typeOfValue != DataTypeInfo::SDOunset);
            }
            inline SDO_API bool isNull() const
            {
               return (typeOfValue == DataTypeInfo::SDOnull);
            }

            // Get methods to retrieve the stored value.
            SDO_API bool getBoolean() const
            {
               return SDODataConverter::convertToBoolean(value,
                                                         (DataTypeInfo::TrueDataType) typeOfValue);
            }

            SDO_API float getFloat() const
            {
               return SDODataConverter::convertToFloat(value,
                                                       (DataTypeInfo::TrueDataType) typeOfValue);
            }

            SDO_API long double getDouble() const
            {
               return SDODataConverter::convertToDouble(value,
                                                        (DataTypeInfo::TrueDataType) typeOfValue);
            }

            SDO_API const SDODate getDate() const
            {
               return SDODataConverter::convertToDate(value,
                                                      (DataTypeInfo::TrueDataType) typeOfValue);
            }

            SDO_API short getShort() const
            {
               return SDODataConverter::convertToShort(value,
                                                      (DataTypeInfo::TrueDataType) typeOfValue);
            }

            SDO_API long getInteger() const
            {
               return SDODataConverter::convertToInteger(value,
                                                         (DataTypeInfo::TrueDataType) typeOfValue);
            }

            SDO_API char getByte() const
            {
               return SDODataConverter::convertToByte(value,
                                                      (DataTypeInfo::TrueDataType) typeOfValue);
            }

            SDO_API wchar_t getCharacter() const
            {
               return SDODataConverter::convertToCharacter(value,
                                                           (DataTypeInfo::TrueDataType) typeOfValue);
            }

            SDO_API int64_t getLong() const
            {
               return SDODataConverter::convertToLong(value,
                                                      (DataTypeInfo::TrueDataType) typeOfValue);
            }

            // The following method is regrettably necessary to provide the
            // CString style interface for the V2.01 spec.
            SDO_API const char* getCString() const
            {
               if (transient_buffer != 0)
               {
                  delete transient_buffer;
               }
               transient_buffer =
                  SDODataConverter::convertToSDOString(value,
                                                       (DataTypeInfo::TrueDataType) typeOfValue);
               return transient_buffer->c_str();
            }

            // This method is the preferred way to retrieve a string value
            SDO_API SDOString getString() const
            {
               return *SDODataConverter::convertToSDOString(value,
                                                            (DataTypeInfo::TrueDataType) typeOfValue);
            }

            SDO_API unsigned int getString(wchar_t* outptr, const unsigned int max_length) const
            {
               return SDODataConverter::convertToString(value,
                                                        (DataTypeInfo::TrueDataType) typeOfValue,
                                                        outptr,
                                                        max_length);
            }

            SDO_API unsigned int getBytes(char* outptr, const unsigned int max_length) const
            {
               return SDODataConverter::convertToBytes(value,
                                                       (DataTypeInfo::TrueDataType) typeOfValue,
                                                       outptr,
                                                       max_length);
            }

            // Beware, the array does not contain values for all the
            // enumeration values and it is the callers job to avoid
            // triggering that.
            SDO_API const SDOString& convertTypeEnumToString() const
            {
               return DataTypeInfo::convertTypeEnumToString((DataTypeInfo::TrueDataType) typeOfValue);
            }
            
            static SDO_API const SDOValue nullSDOValue;
            static SDO_API const SDOValue unsetSDOValue;
            // static SDO_API const SDOString rawTypeNames[];

         private:
            SDO_API SDOValue(DataTypeInfo::RawDataType rdt) : typeOfValue(rdt), transient_buffer(0) {}

      };
   } // End - namespace sdo
} // End - namespace commonj

#endif // _SDOValue_H_
