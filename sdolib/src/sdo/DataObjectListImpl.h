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


#ifndef _DATAOBJECTLISTIMPL_H_
#define _DATAOBJECTLISTIMPL_H_

#include "DataObjectList.h"



#include <vector>


namespace commonj{
namespace sdo{

class DataObjectImpl;
class DASDataFactory;

typedef std::vector< RefCountingPointer<DataObjectImpl> > DATAOBJECT_VECTOR;

class DataObjectListImpl : public DataObjectList
{

public:
    DataObjectListImpl(DATAOBJECT_VECTOR p);
    DataObjectListImpl(const DataObjectListImpl &pin);
	DataObjectListImpl();
	DataObjectListImpl(DASDataFactory* df, DataObjectImpl* cont, unsigned int inpindex, 
		               const char* tURI, const char* tName);

	virtual ~DataObjectListImpl();
	virtual DataObjectPtr operator[] (int pos);
	virtual const DataObjectPtr operator[] (int pos) const;

	// set/get primitive values 
    virtual bool getBoolean(unsigned int index) const;
    virtual char getByte(unsigned int index) const;
    virtual wchar_t getCharacter(unsigned int index) const;
    virtual unsigned int getString(unsigned int index, wchar_t* buf,
		unsigned int max) const;
    virtual unsigned int getBytes(unsigned int index, char* buf, 
		unsigned int max) const;
    virtual short getShort(unsigned int index) const;
    virtual long getInteger(unsigned int index) const;
    virtual int64_t getLong(unsigned int index) const;
    virtual float getFloat(unsigned int index) const;
    virtual long double getDouble(unsigned int index) const;
    virtual time_t  getDate(unsigned int index) const;
	virtual const char*  getCString(unsigned int index) const;
	virtual DataObjectPtr getDataObject(unsigned int index) const;

    virtual void setBoolean(unsigned int index, bool d);
    virtual void setByte(unsigned int index, char d);
    virtual void setCharacter(unsigned int index, wchar_t d);
    virtual void setString(unsigned int index, const wchar_t* d, unsigned int len);
    virtual void setBytes(unsigned int index, const char* d, unsigned int len);
    virtual void setShort(unsigned int index, short d);
    virtual void setInteger(unsigned int index, long d);
    virtual void setLong(unsigned int index, int64_t d);
    virtual void setFloat(unsigned int index, float d);
    virtual void setDouble(unsigned int index, long double d);
    virtual void setDate(unsigned int index, time_t d);
	virtual void setCString(unsigned int index, char* d);
	virtual void setDataObject(unsigned int index, DataObjectPtr dob);

	virtual unsigned int getLength(unsigned int index);

	virtual int size () const;

	virtual void insert (unsigned int index, DataObjectPtr d);
	virtual void append (DataObjectPtr d);

	virtual  void insert (unsigned int index, bool d) ;
	virtual  void append (bool d) ;

	virtual  void insert (unsigned int index, char d) ;
	virtual  void append (char d) ;

	virtual  void insert (unsigned int index, wchar_t d) ;
	virtual  void append (wchar_t d) ;

	virtual  void insert (unsigned int index, const wchar_t* d, unsigned int len) ;
	virtual  void append (const wchar_t* d, unsigned int len) ;

	virtual  void insert (unsigned int index, const char* d, unsigned int len) ;
	virtual  void append (const char* d, unsigned int len) ;

	virtual  void insert (unsigned int index, const char* d) ;
	virtual  void append (const char* d) ;

	virtual  void insert (unsigned int index, short d) ;
	virtual  void append (short d) ;

	virtual  void insert (unsigned int index, long d) ;
	virtual  void append (long d) ;

	virtual  void insert (unsigned int index, int64_t d) ;
	virtual  void append (int64_t d) ;
	
	virtual  void insert (unsigned int index, float d) ;
	virtual  void append (float d) ;

	virtual  void insert (unsigned int index, long double d) ;
	virtual  void append (long double d) ;
	
	
	virtual DataObjectPtr  remove (unsigned int index);


private: 
	DATAOBJECT_VECTOR plist;
	DATAOBJECT_VECTOR getVec() const;

	// For creation of items via the insert/append api.
	char* typeURI;
	char* typeName;

	// No reference count held on the factory
	DASDataFactory* theFactory;

	// For logging a change in the change summary when appending
	DataObjectImpl* container;
	unsigned int pindex;
	bool isReference;

	void validateIndex(int index) const;
};
};
};
#endif
