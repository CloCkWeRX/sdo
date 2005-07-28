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

#include "DataObjectListImpl.h"


#include <iostream>
#include "Property.h"
#include "Type.h"
#include "DataObject.h"
#include "Logger.h"
#include "SDORuntimeException.h"
#include "DASDataFactory.h"
#include "DataObjectImpl.h"

namespace commonj{
namespace sdo {

DataObjectListImpl::DataObjectListImpl(DATAOBJECT_VECTOR p) : plist (p)
{
	Logger::log("DataObjectListImpl constructed from vector\n");
	theFactory = 0;
	container  = 0;
	pindex     = 0;
	isReference = false;
}

DataObjectListImpl::DataObjectListImpl(const DataObjectListImpl &pin)
{
	Logger::log("DataObjectListImpl copy constructor\n");
	plist = std::vector<RefCountingPointer<DataObjectImpl> >(pin.getVec());
	theFactory = pin.theFactory;
	container = pin.container;
	pindex = pin.pindex;
	isReference = pin.isReference;
	if (pin.typeURI != 0) {
		typeURI = new char[strlen(pin.typeURI) +1];
		strcpy(typeURI, pin.typeURI);
	}
	if (pin.typeName != 0) {
		typeName = new char[strlen(pin.typeName) +1];
		strcpy(typeName, pin.typeName);
	}
}

DataObjectListImpl::DataObjectListImpl()
{
	Logger::log("DataObjectListImpl default constructor\n");
	theFactory = 0;
	typeURI    = 0;
	typeName   = 0;
	theFactory = 0;
	container  = 0;
	pindex     = 0;
	isReference = false;
}

DataObjectListImpl::DataObjectListImpl(DASDataFactory* df, 
									   DataObjectImpl* cont,
									   unsigned int inpindex,
									   const char* intypeURI,
									   const char* intypeName)
{
	Logger::log("DataObjectListImpl constructor with data factory\n");
	container = cont;
	pindex = inpindex;
	theFactory = df;

	isReference = false;
	if (container->getPropertyFromIndex(pindex).isReference())
	{
		isReference = true;
	}

	if (intypeURI != 0) {
		typeURI = new char[strlen(intypeURI) +1];
		strcpy(typeURI, intypeURI);
	}
	else {
		typeURI = 0;
	}
	if (intypeName != 0) {
		typeName = new char[strlen(intypeName) +1];
		strcpy(typeName, intypeName);
	}
	else {
		typeName = 0;
		theFactory = 0;
	}
}

DataObjectListImpl::~DataObjectListImpl()
{
	Logger::log("DataObjectListImpl destructor\n");
	if (typeURI != 0) {
		delete typeURI;
		typeURI = 0;
	}
	if (typeName != 0) {
		delete typeName;
		typeName = 0;
	}
}

RefCountingPointer<DataObject> DataObjectListImpl::operator[] (int pos)
{
	validateIndex(pos);
	return plist[pos];
}

const RefCountingPointer<DataObject> DataObjectListImpl::operator[] (int pos) const
{
	validateIndex(pos);
	RefCountingPointer<DataObjectImpl> d = plist[pos];
	DataObjectImpl* dob = d;
	return  RefCountingPointer<DataObject>((DataObject*)dob);
}


int DataObjectListImpl::size () const
{
	return plist.size();
}

DATAOBJECT_VECTOR DataObjectListImpl::getVec() const
{
	return plist;
}


void DataObjectListImpl::insert (unsigned int index, RefCountingPointer<DataObject> d)
{
	if (container != 0)
	{
		container->logChange(pindex);
	}
	for (int i=0;i < plist.size(); i++)
	{
		if (plist[i] == d)
		{
		string msg("Insertion of object which already exists in the list:");
		msg += typeURI;
		msg += " ";
		msg += typeName;
		SDO_THROW_EXCEPTION("List insert", SDOUnsupportedOperationException,
			msg.c_str());
		}
	}
	if (strcmp(typeURI,d->getType().getURI()) 
		|| 
		strcmp(typeName,d->getType().getName()))
	{
		string msg("Insertion of object of the wrong type to a list:");
		msg += typeURI;
		msg += " ";
		msg += typeName;
		msg += " not compatible with ";
		msg += d->getType().getURI();
        msg += " ";
		msg += d->getType().getName();
		SDO_THROW_EXCEPTION("List append", SDOInvalidConversionException,
			msg.c_str());
	}

	DataObject* dob = d; // unwrap the data object ready for a downcasting hack.
	DataObjectImpl* con  = ((DataObjectImpl*)dob)->getContainerImpl();  
	if (!isReference)
	{
		if (con != 0)
		{
			if (con != container)
			{
				/* this data object is already contained somewhere else */
				string msg("Insertion of object to list, object is already contained:");
				msg += d->getType().getURI();
				msg += " ";
				msg += d->getType().getName();
				SDO_THROW_EXCEPTION("List append", SDOInvalidConversionException,
					msg.c_str());
			}
		}
		else 
		{
			((DataObjectImpl*)dob)->setContainer(container);
		}
	}

	plist.insert(plist.begin()+index, RefCountingPointer<DataObjectImpl>((DataObjectImpl*)dob));

	if (container != 0) 
	{
		if (container->getType().isSequencedType())
		{
			SequenceImpl* sq = container->getSequenceImpl();
			if (sq)sq->push(container->getPropertyFromIndex(pindex),index);
		}
	}

}

void DataObjectListImpl::append (RefCountingPointer<DataObject> d)
{
	if (container != 0)
	{
		container->logChange(pindex);
	}
	for (int i=0;i < plist.size(); i++)
	{
		if (plist[i] == d)
		{
		string msg("Append of object which already exists in the list:");
		msg += typeURI;
		msg += " ";
		msg += typeName;
		SDO_THROW_EXCEPTION("List append", SDOUnsupportedOperationException,
			msg.c_str());
		}
	}

	const Type* objectType = &d->getType();
	bool typeMatch = false;
	while (objectType != 0)
	{
		if (strcmp(typeURI,objectType->getURI()) == 0
			&& strcmp(typeName,objectType->getName())==0)
		{
			typeMatch = true;
			break;
		}
		objectType = objectType->getBaseType();
	}

	if (!typeMatch)
	{

		string msg("Append of object of the wrong type to a list:");
		msg += typeURI;
		msg += " ";
		msg += typeName;
		msg += " not compatible with ";
		msg += d->getType().getURI();
        msg += " ";
		msg += d->getType().getName();
		SDO_THROW_EXCEPTION("List append", SDOInvalidConversionException,
			msg.c_str());
	}
	DataObject* dob = d; // unwrap the data object ready for a downcasting hack.
	DataObjectImpl* con  = ((DataObjectImpl*)dob)->getContainerImpl();  
	
	if (!isReference)
	{
		if (con != 0)
		{
			if (con != container)
			{
				/* this data object is already contained somewhere else */
				string msg("Append of object to list, object is already contained:");
				msg += d->getType().getURI();
				msg += " ";
				msg += d->getType().getName();
				SDO_THROW_EXCEPTION("List append", SDOInvalidConversionException,
					msg.c_str());
			}
		}
		else 
		{
			((DataObjectImpl*)dob)->setContainer(container);
		}
	}
	plist.insert(plist.end(),RefCountingPointer<DataObjectImpl>((DataObjectImpl*)dob));

	if (container != 0) {
		if (container->getType().isSequencedType())
		{
			SequenceImpl* sq = container->getSequenceImpl();
			if (sq)sq->push(container->getPropertyFromIndex(pindex),plist.size()-1);
		}
	}
}

void DataObjectListImpl::insert (unsigned int index, bool d)
{
	if (theFactory == 0) return;
	RefCountingPointer<DataObject> dol = theFactory->create(typeURI, typeName);
	DataObject* dob = dol;
	((DataObjectImpl*)dob)->setBoolean(d);
    insert(index, dol);
}

void DataObjectListImpl::append (bool d) 
{
	if (theFactory == 0) return;
	RefCountingPointer<DataObject> dol = theFactory->create(typeURI, typeName);
	DataObject* dob = dol;
	((DataObjectImpl*)dob)->setBoolean(d);
    append( dol);
}

void DataObjectListImpl::insert (unsigned int index, char d)
{
	if (theFactory == 0) return;
	RefCountingPointer<DataObject> dol = theFactory->create(typeURI, typeName);
	DataObject* dob = dol;
	((DataObjectImpl*)dob)->setByte(d);
    insert(index, dol);
}

void DataObjectListImpl::append (char d)
{
	if (theFactory == 0) return;
	RefCountingPointer<DataObject> dol = theFactory->create(typeURI, typeName);
	DataObject* dob = dol;
	((DataObjectImpl*)dob)->setByte(d);
    append( dol);
}

void DataObjectListImpl::insert (unsigned int index, wchar_t d)
{
	if (theFactory == 0) return;
	RefCountingPointer<DataObject> dol = theFactory->create(typeURI, typeName);
	DataObject* dob = dol;
	((DataObjectImpl*)dob)->setCharacter(d);
    insert(index, dol);
}

void DataObjectListImpl::append (wchar_t d)
{
	if (theFactory == 0) return;
	RefCountingPointer<DataObject> dol = theFactory->create(typeURI, typeName);
	DataObject* dob = dol;
	((DataObjectImpl*)dob)->setCharacter(d);
    append( dol);
}

void DataObjectListImpl::insert (unsigned int index, const wchar_t* d, unsigned int length)
{
	if (theFactory == 0) return;
	RefCountingPointer<DataObject> dol = theFactory->create(typeURI, typeName);
	DataObject* dob = dol;
	((DataObjectImpl*)dob)->setString(d, length);
    insert(index, dol);
}

void DataObjectListImpl::append (const wchar_t* d, unsigned int length)
{
	if (theFactory == 0) return;
	RefCountingPointer<DataObject> dol = theFactory->create(typeURI, typeName);
	DataObject* dob = dol;
	((DataObjectImpl*)dob)->setString(d, length);
    append( dol);
}
void DataObjectListImpl::insert (unsigned int index, const char* d, unsigned int length)
{
	if (theFactory == 0) return;
	RefCountingPointer<DataObject> dol = theFactory->create(typeURI, typeName);
	DataObject* dob = dol;
	((DataObjectImpl*)dob)->setBytes(d, length);
    insert(index, dol);
}

void DataObjectListImpl::append (const char* d, unsigned int length)
{
	if (theFactory == 0) return;
	RefCountingPointer<DataObject> dol = theFactory->create(typeURI, typeName);
	DataObject* dob = dol;
	((DataObjectImpl*)dob)->setBytes(d, length);
    append( dol);
}
void DataObjectListImpl::insert (unsigned int index, const char* d)
{
	if (theFactory == 0) return;
	RefCountingPointer<DataObject> dol = theFactory->create(typeURI, typeName);
	DataObject* dob = dol;
	((DataObjectImpl*)dob)->setCString(d);
    insert(index, dol);
}

void DataObjectListImpl::append (const char* d)
{
	if (theFactory == 0) return;
	RefCountingPointer<DataObject> dol = theFactory->create(typeURI, typeName);
	DataObject* dob = dol;
	((DataObjectImpl*)dob)->setCString(d);
    append( dol);
}

void DataObjectListImpl::insert (unsigned int index, short d)
{
	if (theFactory == 0) return;
	RefCountingPointer<DataObject> dol = theFactory->create(typeURI, typeName);
	DataObject* dob = dol;
	((DataObjectImpl*)dob)->setShort(d);
    insert(index, dol);
}

void DataObjectListImpl::append (short d)
{
	if (theFactory == 0) return;
	RefCountingPointer<DataObject> dol = theFactory->create(typeURI, typeName);
	DataObject* dob = dol;
	((DataObjectImpl*)dob)->setShort(d);
    append( dol);
}

void DataObjectListImpl::insert (unsigned int index, long d)
{
	if (theFactory == 0) return;
	RefCountingPointer<DataObject> dol = theFactory->create(typeURI, typeName);
	DataObject* dob = dol;
	((DataObjectImpl*)dob)->setInteger(d);
    insert(index, dol);
}

void DataObjectListImpl::append (long d)
{
	if (theFactory == 0) return;
	RefCountingPointer<DataObject> dol = theFactory->create(typeURI, typeName);
	DataObject* dob = dol;
	((DataObjectImpl*)dob)->setInteger(d);
    append( dol);
}


void DataObjectListImpl::insert (unsigned int index, int64_t d)
{
	if (theFactory == 0) return;
	RefCountingPointer<DataObject> dol = theFactory->create(typeURI, typeName);
	DataObject* dob = dol;
	((DataObjectImpl*)dob)->setLong(d);
    insert(index, dol);
}

void DataObjectListImpl::append (int64_t d)
{
	if (theFactory == 0) return;
	RefCountingPointer<DataObject> dol = theFactory->create(typeURI, typeName);
	DataObject* dob = dol;
	((DataObjectImpl*)dob)->setLong(d);
    append( dol);
}

void DataObjectListImpl::insert (unsigned int index, float d)
{
	if (theFactory == 0) return;
	RefCountingPointer<DataObject> dol = theFactory->create(typeURI, typeName);
	DataObject* dob = dol;
	((DataObjectImpl*)dob)->setFloat(d);
    insert(index, dol);
}

void DataObjectListImpl::append (float d)
{
	if (theFactory == 0) return;
	RefCountingPointer<DataObject> dol = theFactory->create(typeURI, typeName);
	DataObject* dob = dol;
	((DataObjectImpl*)dob)->setFloat(d);
    append( dol);
}

void DataObjectListImpl::insert (unsigned int index, long double d)
{
	if (theFactory == 0) return;
	RefCountingPointer<DataObject> dol = theFactory->create(typeURI, typeName);
	DataObject* dob = dol;
	((DataObjectImpl*)dob)->setDouble(d);
    insert(index, dol);
}

void DataObjectListImpl::append (long double d)
{
	if (theFactory == 0) return;
	RefCountingPointer<DataObject> dol = theFactory->create(typeURI, typeName);
	DataObject* dob = dol;
	((DataObjectImpl*)dob)->setDouble(d);
    append( dol);
}


RefCountingPointer<DataObject> DataObjectListImpl::remove(unsigned int index)
{
	validateIndex(index);
	if (container != 0)
	{
		container->logChange(pindex);
	}
	RefCountingPointer<DataObject> d = (*this)[index];
	(getVec()[index])->logDeletion();
	plist.erase(plist.begin()+index);
	DataObject* dob = d;
	((DataObjectImpl*)dob)->setContainer(0);
	return d;
}

void DataObjectListImpl::validateIndex(int index) const
{
	if ((index < 0) || (index >= size()))
	{
		string msg("Index out of range:");
		msg += index;
		SDO_THROW_EXCEPTION("validateIndex", SDOIndexOutOfRangeException,
			msg.c_str());

	}

}

bool        DataObjectListImpl::getBoolean(unsigned int index) const
{
	validateIndex(index);
	RefCountingPointer<DataObject> d = ((*this)[index]);
	DataObject* dob = d;
	return ((DataObjectImpl*)dob)->getBoolean();
}
char        DataObjectListImpl::getByte(unsigned int index) const
{
	validateIndex(index);
	RefCountingPointer<DataObject> d = ((*this)[index]);
	DataObject* dob = d;
	return ((DataObjectImpl*)dob)->getByte();
}
wchar_t     DataObjectListImpl::getCharacter(unsigned int index) const
{
	validateIndex(index);
	RefCountingPointer<DataObject> d = ((*this)[index]);
	DataObject* dob = d;
	return ((DataObjectImpl*)dob)->getCharacter();
}
unsigned int  DataObjectListImpl::getBytes(unsigned int index, char* value, unsigned int max) const
{
	validateIndex(index);
	RefCountingPointer<DataObject> d = ((*this)[index]);
	DataObject* dob = d;
	return ((DataObjectImpl*)dob)->getBytes(value, max);
}
unsigned int  DataObjectListImpl::getString(unsigned int index, wchar_t* value, unsigned int max) const
{
	validateIndex(index);
	RefCountingPointer<DataObject> d = ((*this)[index]);
	DataObject* dob = d;
	return ((DataObjectImpl*)dob)->getString(value, max);
}
short       DataObjectListImpl::getShort(unsigned int index) const
{
	validateIndex(index);
	RefCountingPointer<DataObject> d = ((*this)[index]);
	DataObject* dob = d;
	return ((DataObjectImpl*)dob)->getShort();
}
long         DataObjectListImpl::getInteger(unsigned int index) const
{
	validateIndex(index);
	RefCountingPointer<DataObject> d = ((*this)[index]);
	DataObject* dob = d;
	return ((DataObjectImpl*)dob)->getInteger();
}
int64_t     DataObjectListImpl::getLong(unsigned int index) const
{
	validateIndex(index);
	RefCountingPointer<DataObject> d = ((*this)[index]);
	DataObject* dob = d;
	return ((DataObjectImpl*)dob)->getLong();
}
float       DataObjectListImpl::getFloat(unsigned int index) const 
{
	validateIndex(index);
	RefCountingPointer<DataObject> d = ((*this)[index]); 
	DataObject* dob = d;
	return ((DataObjectImpl*)dob)->getFloat();
}
long double DataObjectListImpl::getDouble(unsigned int index) const 
{
	validateIndex(index);
	RefCountingPointer<DataObject> d = ((*this)[index]);
	DataObject* dob = d;
	return ((DataObjectImpl*)dob)->getDouble();
}
time_t      DataObjectListImpl::getDate(unsigned int index) const
{
	validateIndex(index);
	RefCountingPointer<DataObject> d = ((*this)[index]);
	DataObject* dob = d;
	return ((DataObjectImpl*)dob)->getDate();
}
const char* DataObjectListImpl::getCString(unsigned int index) const
{
	validateIndex(index);
	RefCountingPointer<DataObject> d = ((*this)[index]);
	DataObject* dob = d;
	return ((DataObjectImpl*)dob)->getCString();
}

DataObjectPtr DataObjectListImpl::getDataObject(unsigned int index) const
{
	validateIndex(index);
	return (*this)[index];
}

void DataObjectListImpl::setBoolean(unsigned int index, bool d) 
{
	validateIndex(index);
	if (container != 0)
	{
		container->logChange(pindex);
	}
	RefCountingPointer<DataObject> dd = ((*this)[index]);
	DataObject* dob = dd;
	((DataObjectImpl*)dob)->setBoolean(d);
}
void DataObjectListImpl::setByte(unsigned int index, char d) 
{
	validateIndex(index);
	if (container != 0)
	{
		container->logChange(pindex);
	}
	RefCountingPointer<DataObject> dd = ((*this)[index]);
	DataObject* dob = dd;
	((DataObjectImpl*)dob)->setByte(d);
}
void DataObjectListImpl::setCharacter(unsigned int index, wchar_t d) 
{
	validateIndex(index);
	if (container != 0)
	{
		container->logChange(pindex);
	}
	RefCountingPointer<DataObject> dd = ((*this)[index]);
	DataObject* dob = dd;
	((DataObjectImpl*)dob)->setCharacter(d);
}

void DataObjectListImpl::setString(unsigned int index, const wchar_t* d, unsigned int len) 
{
	validateIndex(index);
	if (container != 0)
	{
		container->logChange(pindex);
	}
	RefCountingPointer<DataObject> dd = ((*this)[index]);
	DataObject* dob = dd;
	((DataObjectImpl*)dob)->setString(d, len);
}
void DataObjectListImpl::setBytes(unsigned int index, const char* d, unsigned int len) 
{
	validateIndex(index);
	if (container != 0)
	{
		container->logChange(pindex);
	}
	RefCountingPointer<DataObject> dd = ((*this)[index]);
	DataObject* dob = dd;
	((DataObjectImpl*)dob)->setBytes(d, len);
}

void DataObjectListImpl::setShort(unsigned int index, short d) 
{
	validateIndex(index);
	if (container != 0)
	{
		container->logChange(pindex);
	}
	RefCountingPointer<DataObject> dd = ((*this)[index]);
	DataObject* dob = dd;
	((DataObjectImpl*)dob)->setShort(d);
}
void DataObjectListImpl::setInteger(unsigned int index, long d) 
{
	validateIndex(index);
	if (container != 0)
	{
		container->logChange(pindex);
	}
	RefCountingPointer<DataObject> dd = ((*this)[index]);
	DataObject* dob = dd;
	((DataObjectImpl*)dob)->setInteger(d);
}
void DataObjectListImpl::setLong(unsigned int index, int64_t d) 
{
	validateIndex(index);
	if (container != 0)
	{
		container->logChange(pindex);
	}
	RefCountingPointer<DataObject> dd = ((*this)[index]);
	DataObject* dob = dd;
	((DataObjectImpl*)dob)->setLong(d);
}
void DataObjectListImpl::setFloat(unsigned int index, float d) 
{
	validateIndex(index);
	if (container != 0)
	{
		container->logChange(pindex);
	}
	RefCountingPointer<DataObject> dd = ((*this)[index]);
	DataObject* dob = dd;
	((DataObjectImpl*)dob)->setFloat(d);
}
void DataObjectListImpl::setDouble(unsigned int index, long double d) 
{
	validateIndex(index);
	if (container != 0)
	{
		container->logChange(pindex);
	}
	RefCountingPointer<DataObject> dd = ((*this)[index]);
	DataObject* dob = dd;
	((DataObjectImpl*)dob)->setDouble(d);
}
void DataObjectListImpl::setDate(unsigned int index, time_t d) 
{
	validateIndex(index);
	if (container != 0)
	{
		container->logChange(pindex);
	}
	RefCountingPointer<DataObject> dd = ((*this)[index]);
	DataObject* dob = dd;
	((DataObjectImpl*)dob)->setDate(d);
}
void DataObjectListImpl::setCString(unsigned int index, char* d) 
{
	validateIndex(index);
	if (container != 0)
	{
		container->logChange(pindex);
	}
	RefCountingPointer<DataObject> dd = ((*this)[index]);
	DataObject* dob = dd;
	((DataObjectImpl*)dob)->setCString(d);
}

void DataObjectListImpl::setDataObject(unsigned int index, DataObjectPtr dob) 
{
	validateIndex(index);
	if (container != 0)
	{
		container->logChange(pindex);
	}
	(*this)[index] = dob;
}

unsigned int DataObjectListImpl::getLength(unsigned int index) 
{
	validateIndex(index);
	DataObject* dob = (*this)[index];
	return dob->getLength();
}

};
};
