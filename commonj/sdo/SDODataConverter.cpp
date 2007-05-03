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

/* $Rev: 509991 $ $Date$ */

#include <string.h>
#include "commonj/sdo/SDODataConverter.h"
#include "commonj/sdo/SDORuntimeException.h"


// Data type conversion code is currently spread across this class and
// TypeImpl. This is necessary while the widespread use of C macros is
// eradicated, however, the long term aim should be to have all the conversion
// code here and anything else that needs to perform conversions (eg TypeImpl)
// should invoke these methods.


namespace commonj
{
   namespace sdo
   {

      const int SDODataConverter::MAX_TRANSIENT_SIZE = 48;

      const bool SDODataConverter::convertToBoolean(const DataTypeInfo::SDODataTypeUnion& sourceValue,
                                                    const DataTypeInfo::TrueDataType& dataType)
      {
         switch (dataType)
         {
            case DataTypeInfo::TDTbool:
               return sourceValue.Boolean;

            case DataTypeInfo::TDTchar:
               return (sourceValue.Character != 0) ? true : false;

            case DataTypeInfo::TDTwchar_t:
               return (sourceValue.WideChar != 0) ? true : false;

            case DataTypeInfo::TDTshort:
               return (sourceValue.Short != 0) ? true : false;

            case DataTypeInfo::TDTlong:
               return (sourceValue.Integer != 0) ? true : false;

            case DataTypeInfo::TDTint64_t:
               return (sourceValue.Int64 != 0) ? true : false;

            case DataTypeInfo::TDTfloat:
               return (sourceValue.Float != 0) ? true : false;

            case DataTypeInfo::TDTdouble:
               return (sourceValue.Double != 0) ? true : false;

            case DataTypeInfo::TDTSDODate:
               return ((sourceValue.Date)->getTime() != 0) ? true : false;

            case DataTypeInfo::TDTCString:
            case DataTypeInfo::TDTByteArray:
               return (*(sourceValue.TextString) == "true") ? true : false;

            case DataTypeInfo::TDTWideString:
               if (sourceValue.WideString.length < 4) 
               {
                  return false;
               }
               
               if ((sourceValue.WideString.data[0] == (wchar_t) 't') &&
                   (sourceValue.WideString.data[1] == (wchar_t) 'r') &&
                   (sourceValue.WideString.data[2] == (wchar_t) 'u') &&
                   (sourceValue.WideString.data[3] == (wchar_t) 'e'))
               {
                  return true;
               }

               return false;

            default:
            {
               std::string msg("Invalid conversion to boolean from SDOValue of type: ");
               msg += DataTypeInfo::convertTypeEnumToString(dataType);
               SDO_THROW_EXCEPTION("SDODataConverter::convertToBoolean",
                                   SDOInvalidConversionException,
                                   msg.c_str());
               break;
            }
         }
      }


      const char SDODataConverter::convertToByte(const DataTypeInfo::SDODataTypeUnion& sourceValue,
                                                 const DataTypeInfo::TrueDataType& dataType)
      {
         switch (dataType)
         {
            case DataTypeInfo::TDTbool:
               return (sourceValue.Boolean) ? 1 : 0;

            case DataTypeInfo::TDTchar:
               return sourceValue.Character;

            case DataTypeInfo::TDTwchar_t:
               return (char) sourceValue.WideChar;

            case DataTypeInfo::TDTshort:
               return (char) sourceValue.Short;

            case DataTypeInfo::TDTlong:
               return (char) sourceValue.Integer;

            case DataTypeInfo::TDTint64_t:
               return (char) sourceValue.Int64;

            case DataTypeInfo::TDTfloat:
               return (char) sourceValue.Float;

            case DataTypeInfo::TDTdouble:
               return (char) sourceValue.Double;

            case DataTypeInfo::TDTSDODate:
               return (char) (sourceValue.Date)->getTime();

            case DataTypeInfo::TDTCString:
            case DataTypeInfo::TDTByteArray:
               return (char) atoi((sourceValue.TextString)->c_str());

            case DataTypeInfo::TDTWideString:
            {
               // char tmpstr[SDODataConverter::MAX_TRANSIENT_SIZE];
               char* tmpstr = new char[sourceValue.WideString.length + 1];
               for (unsigned int j = 0; j < sourceValue.WideString.length; j++)
               {
                  tmpstr[j] = (char) sourceValue.WideString.data[j];
               }
               tmpstr[sourceValue.WideString.length] = 0;
               char result = (char) atoi(tmpstr);
               delete[] tmpstr;
               return result;
            }
            default:
            {
               std::string msg("Invalid conversion to byte from SDOValue of type: ");
               msg += DataTypeInfo::convertTypeEnumToString(dataType);
               SDO_THROW_EXCEPTION("SDODataConverter::convertToByte",
                                   SDOInvalidConversionException,
                                   msg.c_str());
               break;
            }
         }
         return 0;
      }

      const wchar_t SDODataConverter::convertToCharacter(const DataTypeInfo::SDODataTypeUnion& sourceValue,
                                                         const DataTypeInfo::TrueDataType& dataType)
      {
         switch (dataType)
         {
            case DataTypeInfo::TDTbool:
               return (sourceValue.Boolean == false) ? (wchar_t) 0 : (wchar_t) 1;

            case DataTypeInfo::TDTchar:
               return (wchar_t) sourceValue.Character;

            case DataTypeInfo::TDTwchar_t:
               return sourceValue.WideChar;

            case DataTypeInfo::TDTshort:
               return (wchar_t) sourceValue.Short;

            case DataTypeInfo::TDTlong:
               return (wchar_t) sourceValue.Integer;

            case DataTypeInfo::TDTint64_t:
               return (wchar_t) sourceValue.Int64;

            case DataTypeInfo::TDTfloat:
               return (wchar_t) sourceValue.Float;

            case DataTypeInfo::TDTdouble:
               return (wchar_t) sourceValue.Double;

            case DataTypeInfo::TDTSDODate:
               return (wchar_t) (sourceValue.Date)->getTime();

            case DataTypeInfo::TDTCString:
            case DataTypeInfo::TDTByteArray:
               if ((sourceValue.TextString)->length() == 0)
               {
                  return (wchar_t) 0;
               }
               return (wchar_t) (*sourceValue.TextString)[0];

            case DataTypeInfo::TDTWideString:
               if (sourceValue.WideString.length == 0)
               {
                  return (wchar_t) 0;
               }
               return sourceValue.WideString.data[0];

            default:
            {
               std::string msg("Invalid conversion to character from SDOValue of type: ");
               msg += DataTypeInfo::convertTypeEnumToString(dataType);
               SDO_THROW_EXCEPTION("SDODataConverter::convertToCharacter",
                                   SDOInvalidConversionException,
                                   msg.c_str());
               break;
            }
         }
         return 0;
      }

      const short SDODataConverter::convertToShort(const DataTypeInfo::SDODataTypeUnion& sourceValue,
                                                   const DataTypeInfo::TrueDataType& dataType)
      {
         switch (dataType)
         {
            case DataTypeInfo::TDTbool:
               return (sourceValue.Boolean == false) ? 0 : 1;

            case DataTypeInfo::TDTchar:
               return (short) sourceValue.Character;

            case DataTypeInfo::TDTwchar_t:
               return (short) sourceValue.WideChar;

            case DataTypeInfo::TDTshort:
               return sourceValue.Short;

            case DataTypeInfo::TDTlong:
               return (short) sourceValue.Integer;

            case DataTypeInfo::TDTint64_t:
               return (short) sourceValue.Int64;

            case DataTypeInfo::TDTfloat:
               return (short) sourceValue.Float;

            case DataTypeInfo::TDTdouble:
               return (short) sourceValue.Double;

            case DataTypeInfo::TDTSDODate:
               return (short) (sourceValue.Date)->getTime();

            case DataTypeInfo::TDTCString:
            case DataTypeInfo::TDTByteArray:
               return (short) atoi((sourceValue.TextString)->c_str());

            case DataTypeInfo::TDTWideString:
            {
               char* tmpstr = new char[sourceValue.WideString.length + 1];
               for (unsigned int j = 0; j < sourceValue.WideString.length; j++)
               {
                  tmpstr[j] = (char) sourceValue.WideString.data[j];
               }
               tmpstr[sourceValue.WideString.length] = 0;
               short result = (short) atoi(tmpstr);
               delete[] tmpstr;
               return result;
            }
            
            default:
            {
               std::string msg("Invalid conversion to short from SDOValue of type: ");
               msg += DataTypeInfo::convertTypeEnumToString(dataType);
               SDO_THROW_EXCEPTION("SDODataConverter::convertToShort",
                                   SDOInvalidConversionException,
                                   msg.c_str());
               break;
            }
         }
         return 0;
      }

      const long SDODataConverter::convertToInteger(const DataTypeInfo::SDODataTypeUnion& sourceValue,
                                                    const DataTypeInfo::TrueDataType& dataType)
      {
         switch (dataType)
         {
            case DataTypeInfo::TDTbool:
               return (sourceValue.Boolean == false) ? 0 : 1;

            case DataTypeInfo::TDTchar:
               return (long) sourceValue.Character;

            case DataTypeInfo::TDTwchar_t:
               return (long) sourceValue.WideChar;

            case DataTypeInfo::TDTshort:
               return (long) sourceValue.Short;

            case DataTypeInfo::TDTlong:
               return sourceValue.Integer;

            case DataTypeInfo::TDTint64_t:
               return (long) sourceValue.Int64;

            case DataTypeInfo::TDTfloat:
               return (long) sourceValue.Float;

            case DataTypeInfo::TDTdouble:
               return (long) sourceValue.Double;

            case DataTypeInfo::TDTSDODate:
               return (long) (sourceValue.Date)->getTime();

            case DataTypeInfo::TDTCString:
            case DataTypeInfo::TDTByteArray:
               return (long) atoi((sourceValue.TextString)->c_str());

            case DataTypeInfo::TDTWideString:
            {
               char* tmpstr = new char[sourceValue.WideString.length + 1];
               for (unsigned int j = 0; j < sourceValue.WideString.length; j++)
               {
                  tmpstr[j] = (char) sourceValue.WideString.data[j];
               }
               tmpstr[sourceValue.WideString.length] = 0;
               long result = (long) atoi(tmpstr);
               delete[] tmpstr;
               return result;
            }
            
            default:
            {
               std::string msg("Invalid conversion to long from SDOValue of type: ");
               msg += DataTypeInfo::convertTypeEnumToString(dataType);
               SDO_THROW_EXCEPTION("SDODataConverter::convertToInteger",
                                   SDOInvalidConversionException,
                                   msg.c_str());
               break;
            }
         }
         return 0;
      }


      const int64_t SDODataConverter::convertToLong(const DataTypeInfo::SDODataTypeUnion& sourceValue,
                                                    const DataTypeInfo::TrueDataType& dataType)
      {
         switch (dataType)
         {
            case DataTypeInfo::TDTbool:
               return (sourceValue.Boolean == false) ? 0 : 1;

            case DataTypeInfo::TDTchar:
               return (int64_t) sourceValue.Character;

            case DataTypeInfo::TDTwchar_t:
               return (int64_t) sourceValue.WideChar;

            case DataTypeInfo::TDTshort:
               return (int64_t) sourceValue.Short;

            case DataTypeInfo::TDTlong:
               return (int64_t) sourceValue.Integer;

            case DataTypeInfo::TDTint64_t:
               return sourceValue.Int64;

            case DataTypeInfo::TDTfloat:
               return (int64_t) sourceValue.Float;

            case DataTypeInfo::TDTdouble:
               return (int64_t) sourceValue.Double;

            case DataTypeInfo::TDTSDODate:
               return (int64_t) (sourceValue.Date)->getTime();

            case DataTypeInfo::TDTCString:
            case DataTypeInfo::TDTByteArray:
#if defined(WIN32)  || defined (_WINDOWS)
               return _atoi64((sourceValue.TextString)->c_str());
#else
               return  strtoll((sourceValue.TextString)->c_str(), NULL, 0);
#endif

            case DataTypeInfo::TDTWideString:
            {
               char* tmpstr = new char[sourceValue.WideString.length + 1];
               for (unsigned int j = 0; j < sourceValue.WideString.length; j++)
               {
                  tmpstr[j] = (char) sourceValue.WideString.data[j];
               }
               tmpstr[sourceValue.WideString.length] = 0;
#if defined(WIN32)  || defined (_WINDOWS)
               int64_t result = _atoi64(tmpstr);
#else
               int64_t result = strtoll(tmpstr, NULL, 0);
#endif
               delete[] tmpstr;
               return result;
            }
            
            default:
            {
               std::string msg("Invalid conversion to int64_t from SDOValue of type: ");
               msg += DataTypeInfo::convertTypeEnumToString(dataType);
               SDO_THROW_EXCEPTION("SDODataConverter::convertToLong",
                                   SDOInvalidConversionException,
                                   msg.c_str());
               break;
            }
         }
         return 0;
      }


      const float SDODataConverter::convertToFloat(const DataTypeInfo::SDODataTypeUnion& sourceValue,
                                                   const DataTypeInfo::TrueDataType& dataType)
      {
         switch (dataType)
         {
            case DataTypeInfo::TDTbool:
               return (sourceValue.Boolean == false) ? (float) 0 : (float) 1;

            case DataTypeInfo::TDTchar:
               return (float) sourceValue.Character;

            case DataTypeInfo::TDTwchar_t:
               return (float) sourceValue.WideChar;

            case DataTypeInfo::TDTshort:
               return (float) sourceValue.Short;

            case DataTypeInfo::TDTlong:
               return (float) sourceValue.Integer;

            case DataTypeInfo::TDTint64_t:
               return (float) sourceValue.Int64;

            case DataTypeInfo::TDTfloat:
               return sourceValue.Float;

            case DataTypeInfo::TDTdouble:
               return (float) sourceValue.Double;

            case DataTypeInfo::TDTSDODate:
               return (float) (sourceValue.Date)->getTime();

            case DataTypeInfo::TDTCString:
            case DataTypeInfo::TDTByteArray:
               return (float) atof(sourceValue.TextString->c_str());

            case DataTypeInfo::TDTWideString:
            {
               char* tmpstr = new char[sourceValue.WideString.length + 1];
               for (unsigned int j = 0; j < sourceValue.WideString.length; j++)
               {
                  tmpstr[j] = (char) sourceValue.WideString.data[j];
               }
               tmpstr[sourceValue.WideString.length] = 0;
               float result = (float) atof(tmpstr);
               delete[] tmpstr;
               return result;
            }
            
            default:
            {
               std::string msg("Invalid conversion to float from SDOValue of type: ");
               msg += DataTypeInfo::convertTypeEnumToString(dataType);
               SDO_THROW_EXCEPTION("SDODataConverter::convertToFloat",
                                   SDOInvalidConversionException,
                                   msg.c_str());
               break;
            }
         }
         return 0;
      }


      const double SDODataConverter::convertToDouble(const DataTypeInfo::SDODataTypeUnion& sourceValue,
                                                     const DataTypeInfo::TrueDataType& dataType)
      {
         switch (dataType)
         {
            case DataTypeInfo::TDTbool:
               return (sourceValue.Boolean == false) ? (double) 0 : (double) 1;

            case DataTypeInfo::TDTchar:
               return (double) sourceValue.Character;

            case DataTypeInfo::TDTwchar_t:
               return (double) sourceValue.WideChar;

            case DataTypeInfo::TDTshort:
               return (double) sourceValue.Short;

            case DataTypeInfo::TDTlong:
               return (double) sourceValue.Integer;

            case DataTypeInfo::TDTint64_t:
               return (double) sourceValue.Int64;

            case DataTypeInfo::TDTfloat:
               return (double) sourceValue.Float;

            case DataTypeInfo::TDTdouble:
               return sourceValue.Double;

            case DataTypeInfo::TDTSDODate:
               return (double) (sourceValue.Date)->getTime();

            case DataTypeInfo::TDTCString:
            case DataTypeInfo::TDTByteArray:
               return atof(sourceValue.TextString->c_str());

            case DataTypeInfo::TDTWideString:
            {
               char* tmpstr = new char[sourceValue.WideString.length + 1];
               for (unsigned int j = 0; j < sourceValue.WideString.length; j++)
               {
                  tmpstr[j] = (char) sourceValue.WideString.data[j];
               }
               tmpstr[sourceValue.WideString.length] = 0;
               double result = atof(tmpstr);
               delete[] tmpstr;
               return result;
            }
            
            default:
            {
               std::string msg("Invalid conversion to double from SDOValue of type: ");
               msg += DataTypeInfo::convertTypeEnumToString(dataType);
               SDO_THROW_EXCEPTION("SDODataConverter::convertToDouble",
                                   SDOInvalidConversionException,
                                   msg.c_str());
               break;
            }
         }
         return 0;
      }


      const SDODate SDODataConverter::convertToDate(const DataTypeInfo::SDODataTypeUnion& sourceValue,
                                                    const DataTypeInfo::TrueDataType& dataType)
      {
         switch (dataType)
         {
            case DataTypeInfo::TDTbool:
            case DataTypeInfo::TDTCString:
            case DataTypeInfo::TDTByteArray:
            case DataTypeInfo::TDTWideString:
            {
               std::string msg("Cannot get Date from object of type:");
               msg += DataTypeInfo::convertTypeEnumToString(dataType);
               SDO_THROW_EXCEPTION("SDODataConverter::convertToDate" ,
                                   SDOInvalidConversionException,
                                   msg.c_str());
               break;
            }
            case DataTypeInfo::TDTchar:
               return SDODate((time_t) sourceValue.Character);

            case DataTypeInfo::TDTwchar_t:
               return SDODate((time_t) sourceValue.WideChar);

            case DataTypeInfo::TDTshort:
               return SDODate((time_t) sourceValue.Short);

            case DataTypeInfo::TDTlong:
               return SDODate((time_t) sourceValue.Integer);

            case DataTypeInfo::TDTint64_t:
               return SDODate((time_t) sourceValue.Int64);

            case DataTypeInfo::TDTfloat:
               return SDODate((time_t) sourceValue.Float);

            case DataTypeInfo::TDTdouble:
               return SDODate((time_t) sourceValue.Double);

            case DataTypeInfo::TDTSDODate:
               return *(sourceValue.Date);

            default:
            {
               std::string msg("Invalid conversion to SDODate from SDOValue of type: ");
               msg += DataTypeInfo::convertTypeEnumToString(dataType);
               SDO_THROW_EXCEPTION("SDODataConverter::convertToDate",
                                   SDOInvalidConversionException,
                                   msg.c_str());
               break;
            }
         }
         return 0;
      }


      SDOString* SDODataConverter::convertToSDOString(const DataTypeInfo::SDODataTypeUnion& sourceValue,
                                                      const DataTypeInfo::TrueDataType& dataType)
      {
         char buffer[SDODataConverter::MAX_TRANSIENT_SIZE];
         buffer[0] = 0;

         switch (dataType)
         {
            case DataTypeInfo::TDTbool:
               if (sourceValue.Boolean == false)
               {
                  return new SDOString("false");
               }
               return new SDOString("true");
            case DataTypeInfo::TDTchar:
            {
               sprintf(buffer , "%ld", (long) sourceValue.Character);
               break;
            }
            case DataTypeInfo::TDTwchar_t:
            {
               sprintf(buffer , "%ld", (long) sourceValue.WideChar);
               break;
            }
            case DataTypeInfo::TDTshort:
            {
               sprintf(buffer , "%ld", (long) sourceValue.Short);
               break;
            }
            case DataTypeInfo::TDTlong:
            {
               sprintf(buffer , "%ld", sourceValue.Integer);
               break;
            }
            case DataTypeInfo::TDTint64_t:
               sprintf(buffer , "%lld", sourceValue.Int64);
               break;

            case DataTypeInfo::TDTfloat:
            {
               sprintf(buffer , "%.3e", sourceValue.Float);
               break;
            }
            case DataTypeInfo::TDTdouble:
            {
               sprintf(buffer , "%.3Le", sourceValue.Double);
               break;
            }
            case DataTypeInfo::TDTSDODate:
            {
               sprintf(buffer , "%ld", (sourceValue.Date)->getTime());
               break;
            }
            case DataTypeInfo::TDTByteArray:
            case DataTypeInfo::TDTCString:
               return new SDOString(*sourceValue.TextString);
            case DataTypeInfo::TDTWideString:
            {
               char* tmpbuf = new char[sourceValue.WideString.length + 1];
               for (unsigned int i = 0; i < sourceValue.WideString.length; i++)
               {
                  tmpbuf[i] = (char) (sourceValue.WideString.data)[i];
               }

               tmpbuf[sourceValue.WideString.length] = 0;
               SDOString* result = new SDOString(tmpbuf);
               delete[] tmpbuf;
               return result;
            }
               
            default:
            {
               std::string msg("Invalid conversion to String from SDOValue of type: ");
               msg += DataTypeInfo::convertTypeEnumToString(dataType);
               SDO_THROW_EXCEPTION("SDODataConverter::convertToSDOString",
                                   SDOInvalidConversionException,
                                   msg.c_str());
               break;
            }
         }
         return new SDOString(buffer);
      }


      unsigned int SDODataConverter::convertToBytes(const DataTypeInfo::SDODataTypeUnion& sourceValue,
                                                    const DataTypeInfo::TrueDataType& dataType,
                                                    char* outptr,
                                                    unsigned int max_length)
      {
         // max_length and outptr are allowed to be zero for some data types,
         // to request that the length required to hold this value be returned
         // rather than the actual data. This is a now obselete alternative to
         // the getLength method. It creates a confusing situation because
         // some datatypes respond to this and some return 0.
         // if ((outptr == 0) || (max_length == 0))

         switch (dataType)
         {
            case DataTypeInfo::TDTbool:
               if (outptr == 0)
               {
                  return 0;
               }

               if (sourceValue.Boolean)
               {
                  if (max_length < 4)
                  {
                     return 0;
                  }
                  else
                  {
                    outptr[0] = 't';
                    outptr[1] = 'r';
                    outptr[2] = 'u';
                    outptr[3] = 'e';
                    return 4;
                  }
               }
               else
               {
                  if (max_length < 5)
                  {
                     return 0;
                  }
                  else
                  {
                    outptr[0] = 'f';
                    outptr[1] = 'a';
                    outptr[2] = 'l';
                    outptr[3] = 's';
                    outptr[4] = 'e';
                    return 5;
                  }
               }

            case DataTypeInfo::TDTchar:
               if (outptr == 0)
               {
                  return 0;
               }
               outptr[0] = sourceValue.Character;
               return 1;

            case DataTypeInfo::TDTwchar_t:
               if (outptr == 0)
               {
                  return 0;
               }
               outptr[0] = (char) sourceValue.WideChar;
               return 1;

            case DataTypeInfo::TDTshort:
            {
               char tmpstr[SDODataConverter::MAX_TRANSIENT_SIZE];
               unsigned int j = 0;

               sprintf(tmpstr, "%ld", sourceValue.Short);
               size_t tmplen = strlen(tmpstr);
               if ((tmplen > max_length) || (outptr == 0))
               {
                  return 0;
               }
               for (j = 0; j < tmplen; j++)
               {
                  outptr[j] = tmpstr[j];
               }
               return j;

            }
            case DataTypeInfo::TDTlong:
            {
               char tmpstr[SDODataConverter::MAX_TRANSIENT_SIZE];
               unsigned int j = 0;
               
               sprintf(tmpstr, "%ld", sourceValue.Integer);
               size_t tmplen = strlen(tmpstr);
               if ((tmplen > max_length) || (outptr == 0))
               {
                  return 0;
               }
               for (j = 0; j < tmplen; j++)
               {
                  outptr[j] = tmpstr[j];
               }
               return j;

            }
            case DataTypeInfo::TDTint64_t:
            {
               char tmpstr[SDODataConverter::MAX_TRANSIENT_SIZE];
               unsigned int j = 0;
               
               sprintf(tmpstr, "%lld", sourceValue.Int64);
               size_t tmplen = strlen(tmpstr);
               if ((tmplen > max_length) || (outptr == 0))
               {
                  return 0;
               }
               for (j = 0; j < tmplen; j++)
               {
                  outptr[j] = tmpstr[j];
               }
               return j;

            }
            case DataTypeInfo::TDTfloat:
            {
               char tmpstr[SDODataConverter::MAX_TRANSIENT_SIZE];
               unsigned int j = 0;
               
               sprintf(tmpstr, "%.3e", sourceValue.Float);
               size_t tmplen = strlen(tmpstr);
               if ((tmplen > max_length) || (outptr == 0))
               {
                  return 0;
               }
               for (j = 0; j < tmplen; j++)
               {
                  outptr[j] = tmpstr[j];
               }
               return j;

            }
            case DataTypeInfo::TDTdouble:
            {
               char tmpstr[SDODataConverter::MAX_TRANSIENT_SIZE];
               unsigned int j = 0;
               
               sprintf(tmpstr, "%.3Le", sourceValue.Double);
               size_t tmplen = strlen(tmpstr);
               if ((tmplen > max_length) || (outptr == 0))
               {
                  return 0;
               }
               for (j = 0; j < tmplen; j++)
               {
                  outptr[j] = tmpstr[j];
               }
               return j;

            }
            case DataTypeInfo::TDTSDODate:
            {
               std::string msg("Conversion to bytes not implemented from type: SDODate");
               SDO_THROW_EXCEPTION("getString",
                                   SDOInvalidConversionException,
                                   msg.c_str());
               break;
            }

            case DataTypeInfo::TDTByteArray:
            case DataTypeInfo::TDTCString:
            {
               if (max_length == 0)
               {
                  return (sourceValue.TextString)->length();
               }
               else
               {
                  unsigned int copy_count = (sourceValue.TextString)->length();
                  if (max_length < copy_count)
                  {
                     copy_count = max_length;
                  }
                  for (unsigned int i = 0; (i < copy_count); i++)
                  {
                     outptr[i] = (*sourceValue.TextString)[i];
                  }
                  return copy_count;
               }
            }

            case DataTypeInfo::TDTWideString:
            {
               if (max_length == 0)
               {
                  return sourceValue.WideString.length;
               }
               else
               {
                  unsigned int copy_count = sourceValue.WideString.length;
                  if (max_length < copy_count)
                  {
                     copy_count = max_length;
                  }
                  for (unsigned int i = 0; i < copy_count; i++)
                  {
                     outptr[i] = (char) (sourceValue.WideString.data)[i];
                  }
                  return copy_count;
               }
            }
            
            default:
            {
               std::string msg("Invalid conversion to bytes from SDOValue of type: ");
               msg += DataTypeInfo::convertTypeEnumToString(dataType);
               SDO_THROW_EXCEPTION("SDODataConverter::convertToBytes",
                                   SDOInvalidConversionException,
                                   msg.c_str());
               break;
            }
         }
      }

      unsigned int SDODataConverter::convertToString(const DataTypeInfo::SDODataTypeUnion& sourceValue,
                                                     const DataTypeInfo::TrueDataType& dataType,
                                                     wchar_t* outptr,
                                                     unsigned int max_length)
      {
         // max_length and outptr are allowed to be zero for some data types,
         // to request that the length required to hold this value be returned
         // rather than the actual data. This is a now obselete alternative to
         // the getLength method. It creates a confusing situation because
         // some datatypes respond to this and some return 0.
         // if ((outptr == 0) || (max_length == 0))

         switch (dataType)
         {
            case DataTypeInfo::TDTbool:
               if (outptr == 0)
               {
                  return 0;
               }

               if (sourceValue.Boolean)
               {
                  if (max_length < 4)
                  {
                     return 0;
                  }
                  else
                  {
                    outptr[0] = (wchar_t) 't';
                    outptr[1] = (wchar_t) 'r';
                    outptr[2] = (wchar_t) 'u';
                    outptr[3] = (wchar_t) 'e';
                    return 4;
                  }
               }
               else
               {
                  if (max_length < 5)
                  {
                     return 0;
                  }
                  else
                  {
                    outptr[0] = (wchar_t) 'f';
                    outptr[1] = (wchar_t) 'a';
                    outptr[2] = (wchar_t) 'l';
                    outptr[3] = (wchar_t) 's';
                    outptr[4] = (wchar_t) 'e';
                    return 5;
                  }
               }
            case DataTypeInfo::TDTchar:
               if (outptr == 0)
               {
                  return 0;
               }
               outptr[0] = (wchar_t) sourceValue.Character;
               return 1;

            case DataTypeInfo::TDTwchar_t:
               if (outptr == 0)
               {
                  return 0;
               }
               outptr[0] = sourceValue.WideChar;
               return 1;

            case DataTypeInfo::TDTshort:
            {
               char tmpstr[SDODataConverter::MAX_TRANSIENT_SIZE];
               unsigned int j = 0;
               
               sprintf(tmpstr, "%ld", sourceValue.Short);
               size_t tmplen = strlen(tmpstr);
               if ((tmplen > max_length) || (outptr == 0))
               {
                  return 0;
               }
               for (j = 0; j < tmplen; j++)
               {
                  outptr[j] = (wchar_t) tmpstr[j];
               }
               return j;
            }

            case DataTypeInfo::TDTlong:
            {
               char tmpstr[SDODataConverter::MAX_TRANSIENT_SIZE];
               unsigned int j = 0;
               
               sprintf(tmpstr, "%ld", sourceValue.Integer);
               size_t tmplen = strlen(tmpstr);
               if ((tmplen > max_length) || (outptr == 0))
               {
                  return 0;
               }
               for (j = 0; j < tmplen; j++)
               {
                  outptr[j] = (wchar_t) tmpstr[j];
               }
               return j;
            }

            case DataTypeInfo::TDTint64_t:
            {
               char tmpstr[SDODataConverter::MAX_TRANSIENT_SIZE];
               unsigned int j = 0;
               
               sprintf(tmpstr, "%lld", sourceValue.Integer);
               size_t tmplen = strlen(tmpstr);
               if ((tmplen > max_length) || (outptr == 0))
               {
                  return 0;
               }
               for (j = 0; j < tmplen; j++)
               {
                  outptr[j] = (wchar_t) tmpstr[j];
               }
               return j;
            }

            case DataTypeInfo::TDTfloat:
            {
               char tmpstr[SDODataConverter::MAX_TRANSIENT_SIZE];
               unsigned int j = 0;
               
               sprintf(tmpstr, "%.3e", sourceValue.Float);
               size_t tmplen = strlen(tmpstr);
               if ((tmplen > max_length) || (outptr == 0))
               {
                  return 0;
               }
               for (j = 0; j < tmplen; j++)
               {
                  outptr[j] = (wchar_t) tmpstr[j];
               }
               return j;
            }

            case DataTypeInfo::TDTdouble:
            {
               char tmpstr[SDODataConverter::MAX_TRANSIENT_SIZE];
               unsigned int j = 0;
               
               sprintf(tmpstr, "%.3Le", sourceValue.Double);
               size_t tmplen = strlen(tmpstr);
               if ((tmplen > max_length) || (outptr == 0))
               {
                  return 0;
               }
               for (j = 0; j < tmplen; j++)
               {
                  outptr[j] = (wchar_t) tmpstr[j];
               }
               return j;
            }

            case DataTypeInfo::TDTSDODate:
            {
               std::string msg("Conversion to string not implemented from type: SDODate");
               SDO_THROW_EXCEPTION("getString",
                                   SDOInvalidConversionException,
                                   msg.c_str());
               break;
            }

            case DataTypeInfo::TDTByteArray:
            case DataTypeInfo::TDTCString:
            {
               if (max_length == 0)
               {
                  return (sourceValue.TextString)->length();
               }
               else
               {
                  unsigned int copy_count = (sourceValue.TextString)->length();
                  if (max_length < copy_count)
                  {
                     copy_count = max_length;
                  }
                  for (unsigned int i = 0; (i < copy_count); i++)
                  {
                     outptr[i] = (wchar_t) (*sourceValue.TextString)[i];
                  }
                  return copy_count;
               }
            }

            case DataTypeInfo::TDTWideString:
            {
               if (max_length == 0)
               {
                  return sourceValue.WideString.length;
               }
               else
               {
                  unsigned int copy_count = sourceValue.WideString.length;
                  if (max_length < copy_count)
                  {
                     copy_count = max_length;
                  }
                  for (unsigned int i = 0; i < copy_count; i++)
                  {
                     outptr[i] = (sourceValue.WideString.data)[i];
                  }
                  return copy_count;
               }
            }

            default:
            {
               std::string msg("Invalid conversion to String from SDOValue of type: ");
               msg += DataTypeInfo::convertTypeEnumToString(dataType);
               SDO_THROW_EXCEPTION("SDODataConverter::convertToString",
                                   SDOInvalidConversionException,
                                   msg.c_str());
               break;
            }
         }
      }
   }
}
