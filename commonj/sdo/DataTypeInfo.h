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

#ifndef _DATATYPEINFO_H_
#define _DATATYPEINFO_H_

#include "commonj/sdo/SDODate.h"
#include "commonj/sdo/SDOString.h"

namespace commonj
{
   namespace sdo
   {

      // The DataTypeInfo class provides types that encapsulate the various
      // primitive data types that SDO uses to represent the user level data
      // types defined in the specification. The central artifact is a union
      // of all the C++ data types that are used to represent SDO data
      // types. This is augmented by enumerations that provide a convenient
      // way to label a particular type, plus a method to retrieve a text
      // representation of each enumeration value.

      // There are three different array-like primitive data types.
      // 1. CString: An array of char, terminated by a 0 ie a C style string.
      // 2. ByteArray: An array of bytes. Similar to 1. but without the null
      // terminator.
      // 3. String: An array of wchar_t, terminated by a 0.  In the SDOValue
      // class, the first two are stored in an SDOString (ie std::string),
      // while the third is stored explicitly as a pointer to a buffer.

      class DataTypeInfo
      {
         public:
            // The values of TrueDataType are used as subscripts for an array
            // so they must a) start at zero and b) be contiguous. Elsewhere,
            // TDTWideString is used as the definition of the largest
            // TrueDatatype value, so if any constants are added beyond it the
            // code that depends on it will have to be changed.
            enum TrueDataType
            {
               TDTbool = 0,
               TDTchar = 1,
               TDTwchar_t = 2,
               TDTshort = 3,
               TDTlong = 4,
               TDTint64_t = 5,
               TDTfloat = 6,
               TDTdouble = 7,
               TDTSDODate = 8,
               TDTCString = 9,
               TDTByteArray = 10,
               TDTWideString = 11
            };

            enum PseudoDataType
            {
               PDTunset = -2,
               PDTnull = -1
            };

            // This enum identifies what C++ datatype is present in the union.
            // It does not necessarily say what the SDO type is. Since it is
            // possible for a value to be either unset or null there are
            // enumerations for those cases too. This enum is effectively a
            // union of the two previous enums where TrueDataType includes
            // just those data types that can actually have values, while
            // PseudoDataType includes just null and unset. This allows for
            // methods that have to be told which type to create and for which
            // unset and null are inappropriate.
            enum RawDataType
            {
               SDOunset = PDTunset,
               SDOnull = PDTnull,
               SDObool = TDTbool,
               SDOchar = TDTchar,
               SDOwchar_t = TDTwchar_t,
               SDOshort = TDTshort,
               SDOlong = TDTlong,
               SDOint64_t = TDTint64_t,
               SDOfloat = TDTfloat,
               SDOdouble = TDTdouble,
               SDOSDODate = TDTSDODate,
               SDOCString = TDTCString,
               SDOByteArray = TDTByteArray,
               SDOWideString = TDTWideString,
            };

            // Entities with copy constructors/destructors are not allowed in a union,
            // since in general, the compiler doesn't know what type is
            // actually in there so it can't know which constructor/destructor
            // to call, hence the use of pointers for dates and strings
            union SDODataTypeUnion
            {
                  bool Boolean;
                  char Character;
                  wchar_t WideChar;
                  short Short;
                  long Integer;
                  int64_t Int64;
                  float Float;
                  long double Double;
                  SDODate* Date;
                  SDOString* TextString;
                  struct
                  {
                        wchar_t* data;
                        unsigned int length;
                  } WideString;
            };

            static const SDOString& convertTypeEnumToString(TrueDataType dataType);

         private:
            // Array of text strings that correspond to TrueDataType enumeration values
            static SDO_API const SDOString rawTypeNames[];

      };
   } // End - namespace sdo
} // End - namespace commonj

#endif // _DATATYPEINFO_H_
