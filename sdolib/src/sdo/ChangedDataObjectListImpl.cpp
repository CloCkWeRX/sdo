/* 
+----------------------------------------------------------------------+
| (c) Copyright IBM Corporation 2005.                                  | 
| All Rights Reserved.                                                 |
+----------------------------------------------------------------------+ 
|                                                                      | 
| Licensed under the Apache License, Version 2.0 (the "License"); you  | 
| may not use this file except in compliance with the License. You may | 
| obtain a copy of the License at                                      | 
|http://www.apache.org/licenses/LICENSE-2.0                            |
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
#pragma warning(disable: 4786)


#include "ChangedDataObjectListImpl.h"


#include <iostream>
#include "Property.h"
#include "Type.h"
#include "DataObject.h"
#include "Logger.h"
#include "SDORuntimeException.h"
#include "DataObjectImpl.h"

namespace commonj{
namespace sdo {

ChangedDataObjectListImpl::ChangedDataObjectListImpl(CHANGEDDATAOBJECT_VECTOR p) : plist (p)
{
}

ChangedDataObjectListImpl::ChangedDataObjectListImpl(const ChangedDataObjectListImpl &pin)
{
	Logger::log("ChangedDataObjectListImpl copy constructor\n");
	plist = std::vector< DataObject* >(pin.getVec());
}

ChangedDataObjectListImpl::ChangedDataObjectListImpl()
{
	Logger::log("ChangedDataObjectListImpl default constructor\n");
}


ChangedDataObjectListImpl::~ChangedDataObjectListImpl()
{
	Logger::log("ChangedDataObjectListImpl destructor\n");
}

RefCountingPointer<DataObject> ChangedDataObjectListImpl::operator[] (int pos)
{
	validateIndex(pos);
	return plist[pos];
}

const RefCountingPointer<DataObject> ChangedDataObjectListImpl::operator[] (int pos) const
{	
	validateIndex(pos);
	return  plist[pos];
}

DataObject* ChangedDataObjectListImpl::get(unsigned int pos)
{
	validateIndex(pos);
	return plist[pos];
}

int ChangedDataObjectListImpl::size () const
{
	return plist.size();
}

CHANGEDDATAOBJECT_VECTOR ChangedDataObjectListImpl::getVec() const
{
	return plist;
}


void ChangedDataObjectListImpl::insert (unsigned int index, DataObject *d)
{
	plist.insert(plist.begin()+index, (DataObjectImpl*)d);
}

void ChangedDataObjectListImpl::append (DataObject *d)
{
	plist.insert(plist.end(),(DataObjectImpl*)d);
}

void ChangedDataObjectListImpl::clear ()
{
	plist.clear();
}


void ChangedDataObjectListImpl::remove(unsigned int index)
{
	validateIndex(index);
	plist.erase(plist.begin() +index);
	return ;
}

void ChangedDataObjectListImpl::validateIndex(int index) const
{
	if ((index < 0) || (index >= size()))
	{
		string msg("Invalid index : ");
		msg += index;
		SDO_THROW_EXCEPTION("(ChangeSummary)validateIndex", SDOIndexOutOfRangeException,
			msg.c_str());

	}

}


};
};

