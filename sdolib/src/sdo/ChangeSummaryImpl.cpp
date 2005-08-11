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

#include "ChangeSummaryImpl.h"

#include "DataObjectImpl.h"

#include "Property.h"
#include "Type.h"
#include "TypeList.h"
#include "Sequence.h"
#include "DataObject.h"
#include "DataObjectList.h"
#include "SDORuntimeException.h"
#include "Logger.h"



namespace commonj{
namespace sdo{


	// Initializes an empty change summary, so we know one is required
	ChangeSummaryImpl::ChangeSummaryImpl()
	{
		Logger::log("CS: Change SummaryImpl created");
		logging = false;
	}

	ChangeSummaryImpl::~ChangeSummaryImpl()
	{
		// Force logging off or bad things will happen!
		logging = false;

		// These remove the logitems, so cause the
		//  refcounts of the data objects to drop, and
		// delete the settings lists.
		deletedMap.clear();
		createdMap.clear();
		changedMap.clear();
		changedDataObjects.clear();
		Logger::log("CS: Change SummaryImpl Deleted");
	}

	SequencePtr ChangeSummaryImpl::getOldSequence(DataObjectPtr dob)
	{
		DELETELOG_MAP::iterator deleteLogIter;

		DataObject* ob = dob;
		deleteLogIter = deletedMap.find((DataObjectImpl*)ob);

		if (deleteLogIter != deletedMap.end())
		{
			return (deleteLogIter->second).getSequence();
		}
	
		CHANGELOG_MAP::iterator changeLogIter;

		changeLogIter = changedMap.find((DataObjectImpl*)ob);

		if (changeLogIter != changedMap.end())
		{
			return (changeLogIter->second).getSequence();
		}
		return NULL;

	}

	void ChangeSummaryImpl::removeFromChanges(DataObjectImpl* ob)
	{
		int i = changedDataObjects.size();
		while (i > 0)
		{
			i--;
			//cout << "FOUND:" << changedDataObjects[i] << endl;
			//cout << "COMPARING:" << ob << endl;

			if (changedDataObjects.get(i) == ob)
			{
				//cout << "AND REMOVING IT" << endl;
				changedDataObjects.remove(i);
			}
		}
	}

	void ChangeSummaryImpl::logDeletion(DataObjectImpl* ob,
		DataObjectImpl* container, const Property& prop)
	{
		//cout << "CHS:LogDelete:" << ob << ob->getCString("name") << endl;

		// The object is about to be deleted, so we need
		// all its property Settings recorded in the list
		// of changed data objects. We also need to know
		// its old container, container property, and 
		// value.


		// find any properties which are data objects, log their
		// deletion first.

		int i;

		// Trace for change summaries

		//cout << "CS: Change Summary logs a deletion of property " 
		//	 << prop.getName() << "(many:" << prop.isMany() << ") of type " 
		//	 << prop.getType().getName()   << endl;

		// dont log a change to the property of the container
		// logChange(container, prop);

		DELETELOG_MAP::iterator deleteLogIter;

		deleteLogIter = deletedMap.find(ob);

		if (deleteLogIter != deletedMap.end())
		{
			//cout "CS - delete an already deleted object - can happen";
			return;
		}

 		PropertyList pl = ob->getProperties();
        DataObject* dob;

		for (i=0; i < pl.size(); i++) 
		{
			if (pl[i].getType().isDataObjectType())
			{
				if (pl[i].isMany()) {
					DataObjectList& dl = ob->getList(pl[i]);
					for (int i=0; i < dl.size(); i++)
					{
						dob = dl[i];
						if (dob)logDeletion((DataObjectImpl*)dob,ob,pl[i]);
					}
				}
				else {
					dob = ob->getDataObject(pl[i]);
					if (dob)logDeletion((DataObjectImpl*)(dob),ob,pl[i]);
				}
			}
		}

        CREATELOG_MAP::iterator createLogIter;

		createLogIter = createdMap.find(ob);
		if (createLogIter != createdMap.end())
		{
			// would need to remove it from the created list.
			//cout << "CS: The deletion was already created - just removing it" << endl;
			//cout << "CHS Remove a deleted object from the creations:" << ob << endl;
			removeFromChanges(ob);
			createdMap.erase(ob);
			return;
		}


 		// build a Setting list for the set properties

		deletedMap.insert(make_pair(ob,deleteLogItem((DataObject*)ob, prop,ob->getSequence(),container)));
		deleteLogIter = deletedMap.find(ob);
		SettingList& sl = (deleteLogIter->second).getSettings();

 		void* value;
		unsigned int len;

		for (i=0; i < pl.size(); i++) 
		{
			//if (!pl[i].getType().isDataType()) continue;

			if (!ob->isSet(pl[i]))
			{ 
				sl.append(Setting(false,0,0,pl[i],0));
				continue;
			}
			if (pl[i].isMany())
			{

				DataObjectList& dol = ob->getList(pl[i]);
				for (int j=0;j< dol.size(); j++)
				{
					// needs to be the data object in cases where...
					if (pl[i].getType().isDataType()) {
					    setPropValue(&value,&len,ob,pl[j]);
					    sl.append(Setting(true,value,len,pl[i],j));
					}
					else {
						value = (void*)dol[j];
						sl.append(Setting(true,value,0,pl[i],j));
					}
				}
			}
			else 
			{
				setPropValue(&value,&len,ob,pl[i]);
				sl.append(Setting(true,value,len,pl[i],0));
			}

		}

        CHANGELOG_MAP::iterator changeLogIter;

		changeLogIter = changedMap.find(ob);
		if (changeLogIter != changedMap.end())
		{
			// we have already changed this object, so we need the old values
			// from the change, and to remove the changed map entry
			//cout << "CHS found a deleted item in then changes: " << ob << endl;

			(deleteLogIter->second).setSequence((changeLogIter->second).getSequence());

			SettingList& slist = (changeLogIter->second).getSettings();

			for (int j=0 ; j < slist.size();j++)
			{
				for (int i=0;i<sl.size();i++)
				{
					if (!strcmp(slist[j].getProperty().getName(),
								   sl[i].getProperty().getName())
								   
								   
						&& slist[j].getIndex() == 
						      sl[i].getIndex())
					{
					// these are settings of the same prop/index, we
					// need the old value to get transferred.
						sl.remove(i);
						sl.insert(i,slist[j]);
					}
				}
			}
			//cout << "CHS: Erasing from changes: " << ob << endl;
			changedMap.erase(ob);
		}
		// We append deleted objects to the changed list - this list gives 
		// those data objects which have been affected - we then look at their
		// current and old property values to find out whether they have been
		// deleted or created.
		else 
		{
			changedDataObjects.append(ob);
		}
		
		//cout << "CS: Deletion being added to the list" << endl;

		return;
	}

	void ChangeSummaryImpl::logCreation(DataObjectImpl* ob,
		DataObjectImpl* container, const Property& prop)
	{

		//cout << "CHS:LogCreate:" << ob << ob->getCString("name") << endl;
		// These we just need to record the fact that they
		// are present. We dont have an original value to
		// store.
		//cout << "CS: Change Summary logs a creation of property " 
		//	 << prop.getName() << "(many:" << prop.isMany() << ") of type " 
		//	 << prop.getType().getName()   << endl;

		// log a change to the container of this object
		//
		DELETELOG_MAP::iterator deleteLogIter;

		// not going to do this here - will do it from outside
		//logChange(container, prop);

		deleteLogIter = deletedMap.find(ob);
		if (deleteLogIter != deletedMap.end())
		{
			// can happen - a delete is really a removal from the tree.
			// Adding back an object which you held a pointer to is just
			// a re-creation.
			// I just want some trace here, as I dont think change summary can
			// handle this happening - the changed object list will show this
			// as both a creation and a deletion, and the user must know they
			// are in the reverse order to the expected.
			// cout << "Adding in a pre-deleted object";
		}

		// we should check if this object has sub-objects, they will
		// need to be created too

		PropertyList pl = ob->getProperties();
		for (int p=0;p<pl.size();p++)
		{
			Property& thisprop = pl[p];
			if (!thisprop.getType().isDataType())
			{
				if (ob->isSet(thisprop)) 
				{
					DataObject* dp;

					if (thisprop.isMany())
					{
						DataObjectList& dol = ob->getList(thisprop);
						for (int ds = 0; ds < dol.size(); ds++) 
						{
							dp = dol[ds];
							if (!dp) continue; 
							logCreation((DataObjectImpl*)dp,ob,thisprop);
						}
					}
					else 
					{
						dp = ob->getDataObject(thisprop); 
						if (dp)
						{  
							logCreation((DataObjectImpl*)dp,ob,thisprop);
						}
					}
				}
			}
		}

		CREATELOG_MAP::iterator createLogIter;

		createLogIter = createdMap.find(ob);
		if (createLogIter != createdMap.end())
		{
			// this could be a reference - we dont add it twice.
			//cout << "CHS: No need to log creation twice:" << ob << endl;
			return;
		}

		// We append created objects to the changed list - this list gives 
		// those data objects which have been affected - we then look at their
		// current and old property values to find out whether they have been
		// deleted or created.
		changedDataObjects.append(ob);

		createdMap.insert(make_pair(ob,createLogItem(ob->getType(),prop,container)));
		return;
	}


	void ChangeSummaryImpl::setPropValue(void** value, unsigned int* len, DataObjectImpl* ob, const Property& prop)
	{

		switch (prop.getTypeEnum())
		{
			case Type::BooleanType:
				*value = new long;
				*(long*)*value = (long)ob->getBoolean(prop);
				break;
			case Type::ByteType:
				*value = new long;
				*(long*)*value = (long)ob->getByte(prop);
				break;
			case Type::CharacterType:
				*value = new long;
				*(long*)*value = (long)ob->getCharacter(prop);
				break;
			case Type::IntegerType: 
				*value = new long;
				*(long*)*value = (long)ob->getInteger(prop);
				break;
			case Type::ShortType:
				*value = new long;
				*(long*)*value = (long)ob->getShort(prop);
				break;
			case Type::DoubleType:
				*value = new long double;
				*(long double*)*value = (long double)ob->getDouble(prop);
				break;
			case Type::FloatType:
				*value = new float;
				*(float*)*value = (float)ob->getFloat(prop);
				break;
			case Type::LongType:
				*value = new int64_t;
				*(int64_t*)*value = (int64_t)ob->getLong(prop);
				break;
			case Type::DateType:
				*value = new long;
				*(long*)*value = (long)(ob->getDate(prop).getTime());
				break;
			case Type::BigDecimalType: 
			case Type::BigIntegerType: 
			case Type::StringType: 
			case Type::UriType:
				{
					unsigned int siz = ob->getLength(prop);
					if (siz > 0) {
						*value = new wchar_t[siz];
						*len = ob->getString(prop,(wchar_t*)*value, siz);
					}	
					else {
						*value = 0;
						*len = 0;
					}
				}
				break;
			case Type::BytesType:
				{
					unsigned int siz = ob->getLength(prop);
					if (siz > 0) {
						*value = new char[siz];
						*len = ob->getBytes(prop,(char*)*value, siz);
					}
					else {
						*value = 0;
						*len = 0;
					}
				}
				break;
			case Type::OtherTypes:
			case Type::DataObjectType:
			case Type::ChangeSummaryType:
				*value = (void*)ob->getDataObject(prop);
				break;
			default:
				SDO_THROW_EXCEPTION("(ChangeSummary)setPropValue" ,
				SDOUnsupportedOperationException, "Type is not recognised and cannot be saved");
				break;
		}
	}

	void ChangeSummaryImpl::setManyPropValue(void** value, unsigned int *len, DataObjectImpl* ob, const Property& prop)
	{

		switch (prop.getTypeEnum())
		{
			case Type::BooleanType:
				*value = new long;
				*(long*)*value = (long)ob->getBoolean();
				break;
			case Type::ByteType:
				*value = new long;
				*(long*)*value = (long)ob->getByte();
				break;
			case Type::CharacterType:
				*value = new long;
				*(long*)*value = (long)ob->getCharacter();
				break;
			case Type::IntegerType: 
				*value = new long;
				*(long*)*value = (long)ob->getInteger();
				break;
			case Type::ShortType:
				*value = new long;
				*(long*)*value = (long)ob->getShort();
				break;
			case Type::DoubleType:
				*value = new long double;
				*(long double*)*value = (long double)ob->getDouble();
				break;
			case Type::FloatType:
				*value = new float;
				*(float*)*value = (float)ob->getFloat();
				break;
			case Type::LongType:
				*value = new int64_t;
				*(int64_t*)*value = (int64_t)ob->getLong();
				break;
			case Type::DateType:
				*value = new long;
				*(long*)*value = (long)(ob->getDate().getTime());
				break;
			case Type::BigDecimalType: 
			case Type::BigIntegerType: 
			case Type::StringType: 
			case Type::UriType:
				{
					unsigned int siz = ob->getLength(prop);
					if (siz > 0) {
						*value = new wchar_t[siz];
						*len = ob->getString((wchar_t*)*value, siz);
					}
				}
				break;
			case Type::BytesType:
				{
					unsigned int siz = ob->getLength(prop);
					if (siz > 0) {
						*value = new char[siz];
						*len = ob->getBytes((char*)*value, siz);
					}
				}
				break;

			case Type::OtherTypes:
			case Type::DataObjectType:
			case Type::ChangeSummaryType:
				SDO_THROW_EXCEPTION("(ChangeSummary)setManyPropValue" ,
				SDOUnsupportedOperationException, "A many prop data object value is being set");
				//*value = (void*)ob;
				break;
			default:
				SDO_THROW_EXCEPTION("(ChangeSummary)setManyPropValue" ,
				SDOUnsupportedOperationException, "Type is not recognised and cannot be saved");
				break;
		}
	}

	void ChangeSummaryImpl::logChange(DataObjectImpl* ob,
		 const Property& prop)
	{
		// need to record the old value, unless the object
		// is in the created list, in which case we store 
		// nothing.

		//cout << "CHS:LogChange:" << ob << endl;

		CREATELOG_MAP::iterator createLogIter;
		//cout << "CS: Change Summary logs a change of property " 
		//	 << prop.getName() << "(many:" << prop.isMany() << ") of type " 
		//	 << prop.getType().getName()   << endl;

		unsigned int len;

		createLogIter = createdMap.find(ob);
		if (createLogIter != createdMap.end())
		{
			//cout << "CHS: no need to log change" << ob << endl;
			return;
		}

		DELETELOG_MAP::iterator deleteLogIter;

		deleteLogIter = deletedMap.find(ob);
		if (deleteLogIter != deletedMap.end())
		{
			//cout << "CHS: no need to log change - already deleted" << ob << endl;
			return;
		}

		CHANGELOG_MAP::iterator changeLogIter;

		changeLogIter = changedMap.find(ob);
		if (changeLogIter == changedMap.end())
		{
			//cout << "CS: A change to an object which was not previously changed" << endl;
            changedMap.insert(make_pair(ob, changeLogItem(ob->getType(),prop, 
				              ob->getSequence(), ob)));
			changedDataObjects.append(ob);
		}
		else 
		{
			//cout << "CS: A change to an object which has already been changed" << endl;
		}

		changeLogIter = changedMap.find(ob);
		if (changeLogIter == changedMap.end())
		{
			//cout << "CS: Problem - no changes to append to" << endl;
			return;
		}

		SettingList& slist = (changeLogIter->second).getSettings();
	
        void* value;

		for (int i=0;i<slist.size();i++)
		{
			if (!strcmp(slist[i].getProperty().getName(),prop.getName()))
			{
				//cout << "CS: Change of a property which was already changed - ignore" << endl;
				return;
			}
		}

		// need to check if the property has already been set,
		// There could be many Settings if the item was a list,
		// but we dont care here about that.

		if (!ob->isSet(prop))
		{ 
			slist.append(Setting(false,0,0,prop,0));
			return;
		}

		if (prop.isMany())
		{
			//  We are appending, not modifying
			//	we need to store the list as it was.
			//cout << "CS: logging a change to a many valued property" << endl;
			DataObjectList& dol = ob->getList(prop);
			for (int i=0;i< dol.size(); i++)
			{
				DataObject* dob = dol[i];
				if (prop.getType().isDataType()) {
					setManyPropValue(&value, &len, (DataObjectImpl*)dob,prop);
					slist.append(Setting(true,value,len,prop,i));
				}
				else{
					value = (void*)dob;
					slist.append(Setting(true,value,0,prop,i));
				}
			}
		}
		else 
		{
			setPropValue(&value,&len,ob,prop);
			slist.append(Setting(true,value,len,prop,0));
		}

		return;
	}



	void ChangeSummaryImpl::undoChanges()
	{
		// TODO 
		/* Plan for undoChanges:
		
		There are three lists of information. 
		The created list is a list of data objects which were created during logging.
		These data objects will have current state, and will represent the value of a 
		property of their containing object. As they had no state before they were 
		created, the undoChanges needs to unSet the property values for these first.

		The deleted list contains a list of objects which were deleted. The objects 
		themselves are not valid, but can be used to obtain a list of settings
		representing the values of the properties at the time the object was
		deleted. Here are recursive create is needed to re-create an object 
		similar to the deleted one, and set its properties using the settings. 
		The deleted object may well contain other deleted objects as its 
		property values, so these too will have entries in the deleted list, and
		need to be found and re-created.

		The changed list holds a list of settings for properties of data objects
		which have been changed. These objects may also be in the deleted list, so#
		may not be valid. They will not be in the created list. First check that
		the object is not in the deleted list. If it is, then the changes need to
		be applied to the data object which we created when undoing the deleted list,
		otherwise we just apply the change to a data object which exists.
        */

		/* what about items in many-valued properties? I guess we need to check and
		search the values returned for the list to find the object to delete*/
		return;
	}

	
	void ChangeSummaryImpl::beginLogging()
	{

		// TODO - clear down the lists and their contents, if there was already
		// some activity.
		if (logging) endLogging();
		changedMap.clear();
		deletedMap.clear();
		createdMap.clear();
		changedDataObjects.clear();

		//cout <<  "CS: logging switched on" <<endl;

		logging = true;
  		return;
	}

	void ChangeSummaryImpl::endLogging()
	{
		logging = false;

		//debugPrint();

		//cout << "CS: logging switched off" << endl;

  		return;
	}

	bool ChangeSummaryImpl::isLogging()
	{
  		return logging;
	}

	void ChangeSummaryImpl::debugPrint()
	{
		ChangedDataObjectList& dol = getChangedDataObjects();

		CREATELOG_MAP::iterator createLogIter;
		DELETELOG_MAP::iterator deleteLogIter;
		CHANGELOG_MAP::iterator changeLogIter;

		for (int i=0;i<dol.size(); i++)
		{
			DataObject* dob = dol[i];
			createLogIter = createdMap.find((DataObjectImpl*)dob);
			if (createLogIter != createdMap.end())
			{
				cout << "Found a created object " << dol[i] << endl;
				return;
			}
			deleteLogIter = deletedMap.find((DataObjectImpl*)dob);
			if (deleteLogIter != deletedMap.end())
			{
				cout << "Found a deleted object " << dol[i] << endl;
				return;
			}
			changeLogIter = changedMap.find((DataObjectImpl*)dob);
			if (changeLogIter != changedMap.end())
			{
				cout << "Found a modified object " << dol[i] << endl;
				SettingList& sl = getOldValues(dob);
				for (int j=0; j < sl.size(); j++)
				{
					cout << "Old Value of " << sl[j].getProperty().getName();
					if (sl[j].getProperty().isMany())
					{
                        cout << "[" << sl[j].getIndex() << "]" ;
					}
					cout <<  endl;
					switch (sl[j].getProperty().getTypeEnum()) 
					{
						case Type::BooleanType:
						cout << "Boolean:" << sl[j].getBooleanValue();
						break;
						case Type::ByteType:
						cout << "Byte:" << sl[j].getByteValue();
						break;
						case Type::CharacterType:
							cout << "Character:" << sl[j].getCharacterValue();
						break;
						case Type::IntegerType: 
							cout << "Integer:" << sl[j].getIntegerValue();
						break;
						case Type::ShortType:
							cout << "Short:" << sl[j].getShortValue();
						break;
						case Type::DoubleType:
							cout << "Double:" << sl[j].getDoubleValue();
						break;
						case Type::FloatType:
							cout << "Float:" << sl[j].getFloatValue();
						break;
						case Type::LongType:
							cout << "Int64: (cant print)"; // << (*sl)[j]->getLongValue();
						break;
						case Type::DateType:
							cout << "Date:" << sl[j].getDateValue().getTime();
						break;
						case Type::BigDecimalType: 
						case Type::BigIntegerType: 
						case Type::StringType: 
						case Type::UriType:
							{
							unsigned int len = sl[j].getLength();
							if (len > 0) {
								wchar_t* buf = new wchar_t[len];
								len = sl[j].getStringValue(buf,len);
								cout <<"String type" ;
								for (int i=0;i<len;i++) 
								{
									cout << ":" << buf[i];
								}
								delete buf;
							}
							break;
							}
						case Type::BytesType:
							{
							unsigned int len = sl[j].getLength();
							if (len > 0) {
								char* buf = new char[len];
								len = sl[j].getBytesValue(buf,len);
								cout <<"Bytes type" ;
								for (int i=0;i<len;i++) 
								{
									cout << buf[i];
								}
								delete buf;
							}
							break;
							}
						case Type::OtherTypes:
						case Type::DataObjectType:
						case Type::ChangeSummaryType:
							cout << "DataObject:" << sl[j].getDataObjectValue();
						break;
						default:
							cout << "Unspecified type found in setting";
						break;
					}
					cout << endl;
				}
				return;
			}
			cout << "Found an object which was not in the changes " << dol[i] << endl;

		}
	}

 
	ChangedDataObjectList&  ChangeSummaryImpl::getChangedDataObjects()
	{
		// build a list of all the changes, in the same order
		// as the actions occured,

		return changedDataObjects;

	}

	bool ChangeSummaryImpl::isCreated(RefCountingPointer<commonj::sdo::DataObject> dol)
	{
		CREATELOG_MAP::iterator createLogIter;
		DataObject* dob = dol;
		createLogIter = createdMap.find((DataObjectImpl*)dob);
        
		if (createLogIter != createdMap.end())
		    return true;
		return false;
	}

	bool ChangeSummaryImpl::isDeleted(RefCountingPointer<commonj::sdo::DataObject> dol)
	{
		DELETELOG_MAP::iterator deleteLogIter;
		DataObject* dob = dol;
		deleteLogIter = deletedMap.find((DataObjectImpl*)dob);
        
		if (deleteLogIter != deletedMap.end())
		    return true;
		return false;
	}

	bool ChangeSummaryImpl::isModified(RefCountingPointer<commonj::sdo::DataObject> dol)
	{
		CHANGELOG_MAP::iterator changeLogIter;
		DataObject* dob = dol;
		changeLogIter = changedMap.find((DataObjectImpl*)dob);
        
		if (changeLogIter != changedMap.end())
		    return true;
		return false;
	}   
	/////////////////////////////////////////////////////////////
	// log items are for all lists, but contain different things
	// depending on whether they are deletions, additions...
    /////////////////////////////////////////////////////////////

	ChangeSummaryImpl::createLogItem::createLogItem(const Type& tp, const Property& prop,
		 DataObjectImpl* cont) :
			theOldContainmentProperty(prop), theOldContainer(cont),
			theType(tp)
	{
	}

	DataObjectImpl* ChangeSummaryImpl::createLogItem::getOldContainer()
	{
		return theOldContainer;
	}

	const Type& ChangeSummaryImpl::createLogItem::getOldType()
	{
		return theType;
	}

	const Property& ChangeSummaryImpl::createLogItem::getOldContainmentProperty()
	{
		return  theOldContainmentProperty;
	}



	ChangeSummaryImpl::changeLogItem::changeLogItem(const Type& tp, 
		const Property& prop,
		SequencePtr seq, 
		DataObjectImpl* cont) :
			theOldContainmentProperty(prop), theOldContainer(cont),
			theType(tp)
	{
		if (seq) 
		{
			theSequence = new SequenceImpl((SequenceImpl*)seq);
		}
		else 
		{
			theSequence = 0;
		}
	}

	ChangeSummaryImpl::changeLogItem::changeLogItem(const changeLogItem& cin) :
	theOldContainmentProperty(cin.theOldContainmentProperty),
	theType(cin.theType)
	{
		if (cin.theSequence) theSequence = new SequenceImpl((SequenceImpl*)cin.theSequence);
		else theSequence = 0;
		theOldContainer = cin.theOldContainer;
		theSettings = cin.theSettings;

	}

	ChangeSummaryImpl::changeLogItem::~changeLogItem()
	{
		if (theSequence) delete theSequence;
	}

	DataObjectImpl* ChangeSummaryImpl::changeLogItem::getOldContainer()
	{
		return theOldContainer;
	}

	const Type& ChangeSummaryImpl::changeLogItem::getOldType()
	{
		return theType;
	}

	const Property& ChangeSummaryImpl::changeLogItem::getOldContainmentProperty()
	{
		return  theOldContainmentProperty;
	}

	SettingList& ChangeSummaryImpl::changeLogItem::getSettings()
	{
		return theSettings;
	}

	SequencePtr ChangeSummaryImpl::changeLogItem::getSequence()
	{
		return theSequence;
	}


	ChangeSummaryImpl::deleteLogItem::deleteLogItem(DataObject* dob, 
		 const Property& prop, SequencePtr seq ,DataObjectImpl* cont) :
			theOldContainmentProperty(prop), theOldContainer(cont),
			theActualObject(dob),
			theType(dob->getType())
	{
		if (seq) 
		{
			theSequence = new SequenceImpl((SequenceImpl*)seq);
		}
		else 
		{
			theSequence = 0;
		}
	}

	DataObjectImpl* ChangeSummaryImpl::deleteLogItem::getOldContainer()
	{
		return theOldContainer;
	}

	ChangeSummaryImpl::deleteLogItem::deleteLogItem(const deleteLogItem& cin):
		theOldContainmentProperty(cin.theOldContainmentProperty),
		theType(cin.theType), theActualObject(cin.theActualObject)
	{
		if (cin.theSequence) theSequence = new SequenceImpl((SequenceImpl*)cin.theSequence);
		else theSequence = 0;
		theOldContainer = cin.theOldContainer;
		theSettings = cin.theSettings;
	}

	ChangeSummaryImpl::deleteLogItem::~deleteLogItem()
	{
		theActualObject = 0;
		if (theSequence) delete theSequence;
	}

	const Type& ChangeSummaryImpl::deleteLogItem::getOldType()
	{
		return theType;
	}

	const Property& ChangeSummaryImpl::deleteLogItem::getOldContainmentProperty()
	{
		return  theOldContainmentProperty;
	}

	SettingList& ChangeSummaryImpl::deleteLogItem::getSettings()
	{
		return theSettings;
	}

	SequencePtr ChangeSummaryImpl::deleteLogItem::getSequence()
	{
		return theSequence;
	}

	void ChangeSummaryImpl::deleteLogItem::setSequence(SequencePtr s)
	{
		if (theSequence) delete theSequence;
		if (s) {
		    theSequence = new SequenceImpl((SequenceImpl*)s);
		}
		else {
			theSequence = 0;
		}
		return; 
	}

 
	SettingList& ChangeSummaryImpl::getOldValues(RefCountingPointer<commonj::sdo::DataObject> dol)
	{
		CHANGELOG_MAP::iterator changeLogIter;
		DELETELOG_MAP::iterator deleteLogIter;
		DataObject* dob = dol;
		// start with the deleted map...
		deleteLogIter = deletedMap.find((DataObjectImpl*)dob);
		if (deleteLogIter != deletedMap.end()){
			return ((deleteLogIter->second).getSettings());
		}
		changeLogIter = changedMap.find((DataObjectImpl*)dob);
		if (changeLogIter != changedMap.end()){
			return ((changeLogIter->second).getSettings());
		}
		SDO_THROW_EXCEPTION("(ChangeSummary(getOldValues" ,
		SDOIndexOutOfRangeException, "Data object is not in the change summary");
	} 


	const Setting& ChangeSummaryImpl::getOldValue(RefCountingPointer<commonj::sdo::DataObject> dol, const Property& property)
	{ 
		DELETELOG_MAP::iterator deleteLogIter;
		CHANGELOG_MAP::iterator changeLogIter;
		DataObject* dob = dol;

		deleteLogIter = deletedMap.find((DataObjectImpl*)dob);
		if (deleteLogIter != deletedMap.end())
		{
			SettingList& sl = (deleteLogIter->second).getSettings();
			for (int i=0;i < sl.size(); i++)
			{
				if (!strcmp(property.getName(),
					sl[i].getProperty().getName()))
					return (sl[i]);
			}
		}

		changeLogIter = changedMap.find((DataObjectImpl*)dob);
		if (changeLogIter != changedMap.end())
		{
			SettingList& sl = (changeLogIter->second).getSettings();
			for (int i=0;i < sl.size(); i++)
			{
				if (!strcmp(property.getName(),
					sl[i].getProperty().getName()))
					return (sl[i]);
			}
		}

		SDO_THROW_EXCEPTION("(ChangeSummary(getOldValue)" ,
		SDOIndexOutOfRangeException, "Data object is not in the change summary");
	}

	RefCountingPointer<commonj::sdo::DataObject> ChangeSummaryImpl::getOldContainer(RefCountingPointer<commonj::sdo::DataObject> dol)
	{
		CHANGELOG_MAP::iterator changeLogIter;
		DataObject* dob = dol;
		changeLogIter = changedMap.find((DataObjectImpl*)dob);
       
		if (changeLogIter != changedMap.end())
		{
			return (changeLogIter->second).getOldContainer();
		}
  		return 0;
	}

	const Property& ChangeSummaryImpl::getOldContainmentProperty(RefCountingPointer<commonj::sdo::DataObject> dol)
  	{
		CHANGELOG_MAP::iterator changeLogIter;
		DataObject* dob = dol;
		changeLogIter = changedMap.find((DataObjectImpl*)dob);
       
		if (changeLogIter == changedMap.end())
		{
			SDO_THROW_EXCEPTION("(ChangeSummary(getOldContainmentProperty)" ,
			SDOIndexOutOfRangeException, "Data object is not in the change summary");
		}
		return (changeLogIter->second).getOldContainmentProperty();
	}
};
};

