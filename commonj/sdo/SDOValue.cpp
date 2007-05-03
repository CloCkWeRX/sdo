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

/* $Rev: 509676 $ $Date$ */

#include "commonj/sdo/SDOValue.h"

namespace commonj
{
  namespace sdo
  {

     SDOValue::SDOValue(const char* inValue, unsigned int len) : 
        typeOfValue(DataTypeInfo::SDOByteArray), transient_buffer(0)
     {
        char* temp_buffer = new char[len + 1];

        // We would like to use strncpy at this point, but it is
        // deprecated and its preferred alternative, strncpy_s, does
        // not copy nulls if they appear in the input stream so we'll
        // do it by hand.
        for (unsigned int i = 0; i < len; i++)
        {
           temp_buffer[i] = (char) inValue[i];
        }
        temp_buffer[len] = 0;

        value.TextString = new SDOString(temp_buffer, len);

        delete[] temp_buffer;
     }

     SDOValue::SDOValue(const wchar_t* inValue, unsigned int len) : 
        typeOfValue(DataTypeInfo::SDOWideString), transient_buffer(0)
     {
        value.WideString.data = new wchar_t[len + 1];

        for (unsigned int i = 0; i < len; i++)
        {
           value.WideString.data[i] = (wchar_t) inValue[i];
        }
        value.WideString.data[len] = (wchar_t) 0;

        value.WideString.length = len; // NOTE: length is the number of _real_
                                       // characters in the WideString 

     }

     // Copy constructor
     SDOValue::SDOValue(const SDOValue& inValue) : 
        typeOfValue(inValue.typeOfValue), transient_buffer(0)
     {
        switch (inValue.typeOfValue)
        {
           case DataTypeInfo::SDObool:
              value.Boolean = inValue.value.Boolean;
              break;
           case DataTypeInfo::SDOchar:
              value.Character = inValue.value.Character;
              break;
           case DataTypeInfo::SDOwchar_t:
              value.WideChar = inValue.value.WideChar;
              break;
           case DataTypeInfo::SDOshort:
              value.Short = inValue.value.Short;
              break;
           case DataTypeInfo::SDOlong:
              value.Integer = inValue.value.Integer;
              break;
           case DataTypeInfo::SDOfloat:
              value.Float = inValue.value.Float;
              break;
           case DataTypeInfo::SDOdouble:
              value.Double = inValue.value.Double;
              break;
           case DataTypeInfo::SDOSDODate:
              value.Date = new SDODate((inValue.value.Date)->getTime());
              break;
           case DataTypeInfo::SDOCString:
           case DataTypeInfo::SDOByteArray:
              value.TextString = new SDOString(*(inValue.value.TextString));
			  break;
           case DataTypeInfo::SDOWideString:
              value.WideString.data = new wchar_t[inValue.value.WideString.length + 1];
              // The loop copies the null terminator that was added to the end
              // of the source data when _it_ was constructed.
              for (unsigned int i = 0; i <= inValue.value.WideString.length; i++)
              {
                 value.WideString.data[i] = inValue.value.WideString.data[i];
              }
              value.WideString.length = inValue.value.WideString.length;
              break;
        }
     }
     // End of copy constructor

     // Copy assignment
     SDOValue& SDOValue::operator=(const SDOValue& inValue)
     {
        if (this != &inValue)   // sval = sval is a no-op.
        {
           // Clear out any allocated data in the target SDOValue.
           switch (typeOfValue)
           {
              case DataTypeInfo::SDOSDODate:
                 delete value.Date;
                 value.Date = 0;
                 break;
             case DataTypeInfo::SDOCString:
             case DataTypeInfo::SDOByteArray:
                 delete value.TextString;
                 value.TextString = 0;
                 break;
              case DataTypeInfo::SDOWideString:
                 delete[] value.WideString.data;
                 value.WideString.data = 0;
                 value.WideString.length = 0;
                 break;
              default:
                 // Nothing to delete.
                 break;
           }           

           if (transient_buffer != 0)
           {
              delete transient_buffer;
              transient_buffer = 0;
           }

           // Copy the source data into the target
           switch (inValue.typeOfValue)
           {
              case DataTypeInfo::SDObool:
                 value.Boolean = inValue.value.Boolean;
                 break;
              case DataTypeInfo::SDOchar:
                 value.Character = inValue.value.Character;
                 break;
              case DataTypeInfo::SDOwchar_t:
                 value.WideChar = inValue.value.WideChar;
                 break;
              case DataTypeInfo::SDOshort:
                 value.Short = inValue.value.Short;
                 break;
              case DataTypeInfo::SDOlong:
                 value.Integer = inValue.value.Integer;
                 break;
              case DataTypeInfo::SDOint64_t:
                 value.Int64 = inValue.value.Int64;
                 break;
              case DataTypeInfo::SDOfloat:
                 value.Float = inValue.value.Float;
                 break;
              case DataTypeInfo::SDOdouble:
                 value.Double = inValue.value.Double;
                 break;
              case DataTypeInfo::SDOSDODate:
                 value.Date = new SDODate((inValue.value.Date)->getTime());
                 break;
              case DataTypeInfo::SDOCString:
              case DataTypeInfo::SDOByteArray:
                 value.TextString = new SDOString(*(inValue.value.TextString));
                 break;
              case DataTypeInfo::SDOWideString:
                 value.WideString.data = new wchar_t[inValue.value.WideString.length + 1];
                 // The loop copies the null terminator that was added to the end
                 // of the source data when _it_ was constructed.
                 for (unsigned int i = 0; i <= inValue.value.WideString.length; i++)
                 {
                    value.WideString.data[i] = inValue.value.WideString.data[i];
                 }
                 value.WideString.length = inValue.value.WideString.length;
                 break;
           }
           // Finally, set the new type.
           typeOfValue = inValue.typeOfValue;
        }
        return *this;
     }
     // End of copy assignment

     // Destructor
     SDOValue::~SDOValue()
     {

        // Clear out any allocated data in the target SDOValue.
        switch (typeOfValue)
        {
           case DataTypeInfo::SDOSDODate:
              delete value.Date;
              value.Date = 0;
              break;
          case DataTypeInfo::SDOCString:
          case DataTypeInfo::SDOByteArray:
              delete value.TextString;
              value.TextString = 0;
              break;
           case DataTypeInfo::SDOWideString:
              delete[] value.WideString.data;
              value.WideString.data = 0;
              value.WideString.length = 0;
              break;
           default:
              // Nothing to delete.
              break;
        }

        if (transient_buffer != 0)
        {
           delete transient_buffer;
           transient_buffer = 0;
        }

        typeOfValue = DataTypeInfo::SDOunset;
     }
     // End of Destructor


     const SDOValue SDOValue::nullSDOValue = SDOValue(DataTypeInfo::SDOnull);
     const SDOValue SDOValue::unsetSDOValue = SDOValue(DataTypeInfo::SDOunset);
  } // End - namespace sdo
} // End - namespace commonj
