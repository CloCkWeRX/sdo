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

#ifndef _ChangedDataObjectListImplIMPL_H_
#define _ChangedDataObjectListImplIMPL_H_


#include <vector>

#include "ChangedDataObjectList.h"
namespace commonj{
namespace sdo{

class DataObjectImpl;
class DataObject;

class CDOListElement 
{
public:
	CDOListElement ();
	CDOListElement (DataObject* in, ChangedDataObjectList::ChangeType type);
    virtual ~CDOListElement();
    virtual ChangedDataObjectList::ChangeType getType() const;
    virtual DataObject* getObject() const;
private:
	DataObject* theObject;
	ChangedDataObjectList::ChangeType	theType; 
};

typedef std::vector< CDOListElement  > CHANGEDDATAOBJECT_VECTOR;


class ChangedDataObjectListImpl : public ChangedDataObjectList
{

public:
    ChangedDataObjectListImpl(CHANGEDDATAOBJECT_VECTOR p);
    ChangedDataObjectListImpl(const ChangedDataObjectListImpl &pin);
	ChangedDataObjectListImpl();

	virtual ~ChangedDataObjectListImpl();
	virtual DataObjectPtr operator[] (int pos);
	virtual const DataObjectPtr operator[] (int pos) const;
	virtual DataObject* get(unsigned int pos);
	virtual ChangedDataObjectList::ChangeType getType(unsigned int pos);


	virtual int size () const;

	virtual void insert (unsigned int index, DataObject *d, ChangedDataObjectList::ChangeType type);

	virtual void append (DataObject* d, ChangedDataObjectList::ChangeType type);
	
	virtual void clear();
	
	virtual void  remove (unsigned int index);


private: 
	CHANGEDDATAOBJECT_VECTOR plist;
	CHANGEDDATAOBJECT_VECTOR getVec() const;

	void validateIndex(int index) const;
};
};
};
#endif
