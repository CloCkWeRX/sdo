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


#include "commonj/sdo/ChangedDataObjectListImpl.h"


#include <iostream>
#include "commonj/sdo/Property.h"
#include "commonj/sdo/Type.h"
#include "commonj/sdo/DataObject.h"
#include "commonj/sdo/Logger.h"
#include "commonj/sdo/SDORuntimeException.h"
#include "commonj/sdo/DataObjectImpl.h"


namespace commonj{
namespace sdo {



	CDOListElement::CDOListElement()
	{
		theObject = 0;
		theType = ChangedDataObjectList::Undefined;
	}

	CDOListElement::CDOListElement(DataObject* in, ChangedDataObjectList::ChangeType type)
	{
		theObject = in;
		theType = type;
	}

	CDOListElement::~CDOListElement()
	{
	}

	DataObject*	CDOListElement::getObject() const
	{
		return theObject;
	}

	ChangedDataObjectList::ChangeType CDOListElement::getType() const
	{
		return theType;
	}

ChangedDataObjectListImpl::ChangedDataObjectListImpl(CHANGEDDATAOBJECT_VECTOR p) : plist (p)
{
}

ChangedDataObjectListImpl::ChangedDataObjectListImpl(const ChangedDataObjectListImpl &pin)
{
	plist = std::vector< CDOListElement >(pin.getVec());
}

ChangedDataObjectListImpl::ChangedDataObjectListImpl()
{
}


ChangedDataObjectListImpl::~ChangedDataObjectListImpl()
{
}

RefCountingPointer<DataObject> ChangedDataObjectListImpl::operator[] (int pos)
{
	validateIndex(pos);
	return plist[pos].getObject();
}

const RefCountingPointer<DataObject> ChangedDataObjectListImpl::operator[] (int pos) const
{	
	validateIndex(pos);
	return  plist[pos].getObject();
}

DataObject* ChangedDataObjectListImpl::get(unsigned int pos)
{
	validateIndex(pos);
	return plist[pos].getObject();
}

int ChangedDataObjectListImpl::size () const
{
	return plist.size();
}

CHANGEDDATAOBJECT_VECTOR ChangedDataObjectListImpl::getVec() const
{
	return plist;
}

ChangedDataObjectList::ChangeType ChangedDataObjectListImpl::getType(unsigned int pos) 
{
	validateIndex(pos);
	return plist[pos].getType();
}


void ChangedDataObjectListImpl::insert (unsigned int index, 
										DataObject *d, ChangedDataObjectList::ChangeType type)
{
	plist.insert(plist.begin()+index, CDOListElement((DataObject*)d,type));
}

void ChangedDataObjectListImpl::append (DataObject *d, ChangedDataObjectList::ChangeType type)
{
	plist.insert(plist.end(),CDOListElement((DataObject*)d, type));
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

