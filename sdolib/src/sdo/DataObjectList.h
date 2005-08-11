/* 
+----------------------------------------------------------------------+
| (c) Copyright IBM Corporation 2005.                                  | 
| All Rights Reserved.                                                 |
+----------------------------------------------------------------------+ 
|                                                                      | 
| Licensed under the Apache License, Version 2.0 (the "License"); you  | 
| may not use this file except in compliance with the License. You may | 
| obtain a copy of the License at                                      | 
|  http://www.apache.org/licenses/LICENSE-2.0                          |
|                                                                      | 
| Unless required by applicable law or agreed to in writing, software  | 
| distributed under the License is distributed on an "AS IS" BASIS,    | 
| WITHOUT WARRANTIES OR CONDITIONS OF ANY KIND, either express or      | 
| implied. See the License for the specific language governing         | 
| permissions and limitations under the License.                       | 
+----------------------------------------------------------------------+ 
| Author: Ed Slattery                                                  | 
+----------------------------------------------------------------------+ 

*/
/* $Id$ */

#ifndef _DATAOBJECTLIST_H_
#define _DATAOBJECTLIST_H_

#include "export.h"
#include "RefCountingPointer.h"
#include "DataObject.h"
#include "SDODate.h"
#include <wchar.h>


namespace commonj{
namespace sdo{

//class DataObject;

class DataObjectList
{
public:
	virtual  ~DataObjectList();

	virtual SDO_API DataObjectPtr operator[] (int pos) = 0;
	virtual SDO_API const DataObjectPtr operator[] (int pos) const = 0;
	virtual SDO_API int size () const = 0;

    virtual SDO_API bool getBoolean(unsigned int index) const = 0;
    virtual SDO_API char getByte(unsigned int index) const = 0;
    virtual SDO_API wchar_t getCharacter(unsigned int index) const = 0;
    virtual SDO_API unsigned int getString(unsigned int index, wchar_t* value,
		unsigned int max) const = 0;
    virtual SDO_API unsigned int getBytes(unsigned int index, char* value,
		unsigned int max) const = 0;
    virtual SDO_API short getShort(unsigned int index) const = 0;
    virtual SDO_API long getInteger(unsigned int index) const = 0;
    virtual SDO_API int64_t getLong(unsigned int index) const = 0;
    virtual SDO_API float getFloat(unsigned int index) const = 0;
    virtual SDO_API long double getDouble(unsigned int index) const = 0;
    virtual SDO_API const SDODate  getDate(unsigned int index) const = 0;
	virtual SDO_API const char*  getCString(unsigned int index) const = 0;
	virtual SDO_API DataObjectPtr  getDataObject(unsigned int index) const = 0;

    virtual SDO_API void setBoolean(unsigned int index, bool d)  = 0;
    virtual SDO_API void setByte(unsigned int index, char d)  = 0;
    virtual SDO_API void setCharacter(unsigned int index, wchar_t d)  = 0;
    virtual SDO_API void setString(unsigned int index, const wchar_t* d, unsigned int len)  = 0;
    virtual SDO_API void setBytes(unsigned int index, const char* d, unsigned int len)  = 0;
    virtual SDO_API void setShort(unsigned int index, short d)  = 0;
    virtual SDO_API void setInteger(unsigned int index, long d)  = 0;
    virtual SDO_API void setLong(unsigned int index, int64_t d)  = 0;
    virtual SDO_API void setFloat(unsigned int index, float d)  = 0;
    virtual SDO_API void setDouble(unsigned int index, long double d)  = 0;
    virtual SDO_API void setDate(unsigned int index, const SDODate d)  = 0;
	virtual SDO_API void setCString(unsigned int index, char* d)  = 0;
	virtual SDO_API void setDataObject(unsigned int index, DataObjectPtr dob)  = 0;

	virtual SDO_API unsigned int getLength(unsigned int index) = 0;

	virtual SDO_API void insert (unsigned int index, DataObjectPtr d) = 0;
	virtual SDO_API void append (DataObjectPtr d) = 0;
	
    /* The overrides for non-dataobject types */
	virtual SDO_API void insert (unsigned int index, bool d) = 0;
	virtual SDO_API void append (bool d) = 0;

	virtual SDO_API void insert (unsigned int index, char d) = 0;
	virtual SDO_API void append (char d) = 0;

	virtual SDO_API void insert (unsigned int index, wchar_t d) = 0;
	virtual SDO_API void append (wchar_t d) = 0;

	virtual SDO_API void insert (unsigned int index, const wchar_t* d, unsigned int len) = 0;
	virtual SDO_API void append (const wchar_t* d, unsigned int len) = 0;

	virtual SDO_API void insert (unsigned int index, const char* d, unsigned int len) = 0;
	virtual SDO_API void append (const char* d, unsigned int len) = 0;

	virtual SDO_API void insert (unsigned int index, const char* d) = 0;
	virtual SDO_API void append (const char* d) = 0;

	virtual SDO_API void insert (unsigned int index, short d) = 0;
	virtual SDO_API void append (short d) = 0;

	virtual SDO_API void insert (unsigned int index, const SDODate d) = 0;
	virtual SDO_API void append (const SDODate d) = 0;

	virtual SDO_API void insert (unsigned int index, long d) = 0;
	virtual SDO_API void append (long d) = 0;

	virtual SDO_API void insert (unsigned int index, int64_t d) = 0;
	virtual SDO_API void append (int64_t d) = 0;
	
	virtual SDO_API void insert (unsigned int index, float d) = 0;
	virtual SDO_API void append (float d) = 0;


	virtual SDO_API void insert (unsigned int index, long double d) = 0;
	virtual SDO_API void append (long double d) = 0;

	virtual SDO_API DataObjectPtr  remove (unsigned int index) = 0;
};
};
};

#endif
