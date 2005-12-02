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

#ifndef _CHANGESUMMARYIMPL_H_
#define _CHANGESUMMARYIMPL_H_
#pragma warning(disable: 4786)
#include "commonj/sdo/ChangedDataObjectListImpl.h"
#include "commonj/sdo/SettingList.h"
#include "commonj/sdo/ChangeSummary.h"
#include "commonj/sdo/RefCountingPointer.h"
#include "commonj/sdo/SDOXMLString.h"

#include <map>

using namespace std;
namespace commonj{
namespace sdo {


	// These classes were inner, and now need to be moved out for
	// the API to deserialization to work.

	class changeLogItem {

    public:

		changeLogItem(const Type& tp, const Property& prop,SequencePtr seq, 
			DataObjectImpl* cont = 0 );
		changeLogItem(const changeLogItem& cin); 
        ~changeLogItem();
		DataObjectImpl* getOldContainer();
		const Property& getOldContainmentProperty();
		const Type& getOldType();
		SettingList& getSettings();
		SequencePtr getSequence();

	private:

		const Property& theOldContainmentProperty;
		DataObjectImpl* theOldContainer;
		const Type& theType;
		SettingList theSettings;
		SequencePtr theSequence;
	};



	class createLogItem {

    public:

		createLogItem(const Type& tp, const Property& prop, DataObjectImpl* cont = 0);


		DataObjectImpl* getOldContainer();
		const Property& getOldContainmentProperty();
		const Type& getOldType();

	private:

		const Property& theOldContainmentProperty;
		DataObjectImpl* theOldContainer;
		const Type& theType;
	};


	class deleteLogItem {

    public:


		deleteLogItem(DataObject* dob,  const Property& prop,
						SequencePtr seq,const char *oldpath,
						DataObjectImpl* cont = 0);


		deleteLogItem(const deleteLogItem& cin); 
		~deleteLogItem();
		DataObjectImpl* getOldContainer();
		const Property& getOldContainmentProperty();
		const Type& getOldType();
		const char* getOldXpath();
		SettingList& getSettings();
		SequencePtr getSequence();
		void setSequence(SequencePtr s);

	private:

		const Property& theOldContainmentProperty;
		DataObjectImpl* theOldContainer;

		// A counting pointer to the object is held, such that the 
		// object is not freed, even if deleted. This rcp will not
		// be used to refer to the object, but makes sure that the
		// object does not reuse a memory address of a previously
		// deleted object until the change summary is destroyed.

		RefCountingPointer<DataObject> theActualObject;
		const Type& theType;
		SettingList theSettings;
		SequencePtr theSequence;
		char * theOldXpath;
	};


	///////////////////////////////////////////////////////////////////////////
    // A change summary is used to record changes to the objects in a data graph,
    // allowing applications to efficiently and incrementally update 
	//back-end storage when required.
 	///////////////////////////////////////////////////////////////////////////
 
	class ChangeSummaryImpl : public ChangeSummary
	{
  	public:
		ChangeSummaryImpl();

		virtual ~ChangeSummaryImpl();

 	///////////////////////////////////////////////////////////////////////////
    // SDO 1.1: Returns a list consisting of all the  data objects that have been 
	//	changed while logging.
    // 
    // The new and modified objects in the list are references to objects that
    // are associated with this change summary. 
    // The deleted objects in the list are references to copies of the objects 
    // as they appeared at the time that event logging was enabled; 
    // if the deleted objects have references to other objects, 
    // the references will also refer to copies of the target objects.
    // Return a list of changed data objects.
   	///////////////////////////////////////////////////////////////////////////

	virtual ChangedDataObjectList&  getChangedDataObjects();


	///////////////////////////////////////////////////////////////////////////
    // Returns a list of Settings 
    // that represent the property values of the given dataObject
    // at the point when logging began.
    // In the case of a deleted object, 
    // the list will include Settings for all the properties.
    // An old value Setting indicates the value at the
    // point logging begins.  A setting is only produced for 
    // modified objects if either the old value differs from the current value or
    // if the isSet differs from the current value. 
    // No Settings are produced for created objects.
    // Param dataObject the object in question.
    // Return a list of settings.
  	///////////////////////////////////////////////////////////////////////////
	virtual SettingList& /*ChangeSummary.Setting*/ getOldValues(DataObjectPtr dataObject);
	virtual const char* getOldXpath(RefCountingPointer<commonj::sdo::DataObject> dol);

 	///////////////////////////////////////////////////////////////////////////
    // This method is intended for use by service implementations only.
    // If logging is already on no Exception is thrown.
 	///////////////////////////////////////////////////////////////////////////
	virtual void beginLogging();

 	///////////////////////////////////////////////////////////////////////////
    // This method is intended for use by service implementations only.
    // An implementation that requires logging may throw an UnsupportedOperationException.
 	///////////////////////////////////////////////////////////////////////////
	virtual void endLogging();

  
  
 	///////////////////////////////////////////////////////////////////////////
    // Indicates whether change logging is on (true) or off (false).
 	///////////////////////////////////////////////////////////////////////////
	virtual bool isLogging();


  
 	///////////////////////////////////////////////////////////////////////////
    // Returns whether or not the specified data object was created while logging.
    // Any object that was added to the data graph
    // but was not in the data graph when logging began, 
    // will be considered created.
    // Param dataObject the data object in question.
    // Return true if the specified data object was created.
 	///////////////////////////////////////////////////////////////////////////
	virtual bool isCreated(DataObjectPtr dataObject);

 	///////////////////////////////////////////////////////////////////////////
    // Returns whether or not the specified data object was deleted while logging.
    // Any object that is not contained by the data graph will be considered 
	// deleted.
    // Param dataObject the data object in question.
    // Return true if the specified data object was deleted.
 	///////////////////////////////////////////////////////////////////////////
	virtual bool isDeleted(DataObjectPtr dataObject);


 	///////////////////////////////////////////////////////////////////////////
    // Returns whether or not the specified data object was updated while logging.
    // An object that was contained in the data graph when logging began, 
    // and remains in the graph when logging ends will be considered for changes.
    // An object considered modified must have at least one old value Setting.
    // Param dataObject the data object in question.
    // Return true if the specified data object was modified.
 	///////////////////////////////////////////////////////////////////////////
	virtual bool isModified(DataObjectPtr dataObject);   

 	///////////////////////////////////////////////////////////////////////////
    // Returns a setting for the specified property
    // representing the property value of the given dataObject
    // at the point when logging began.
    // Returns null if the property has not changed and 
    // has not been deleted. 
    // Param dataObject the object in question.
    // Param property the property of the object.
    // Return the Setting for the specified property.
 	///////////////////////////////////////////////////////////////////////////
	virtual const Setting& getOldValue(DataObjectPtr dataObject, const Property& property);

 	///////////////////////////////////////////////////////////////////////////
    // Returns the value of the container data object
    // at the point when logging began.
    // Param dataObject the object in question.
    // Return the old container data object.
   	///////////////////////////////////////////////////////////////////////////
	virtual DataObjectPtr getOldContainer(DataObjectPtr dataObject);

 	///////////////////////////////////////////////////////////////////////////
    // Returns the value of the containment property data object property
    // at the point when logging began.
    // Param dataObject the object in question.
    // Return the old containment property.
 	///////////////////////////////////////////////////////////////////////////
	virtual const Property& getOldContainmentProperty(DataObjectPtr dataObject);  

 	///////////////////////////////////////////////////////////////////////////
	// This method is intended for use by service implementations only.
	// undoes all changes in the log to restore the tree of 
	// DataObjects to its original state when logging began.
	// isLogging() is unchanged.  The log is cleared.
 	///////////////////////////////////////////////////////////////////////////
	virtual void undoChanges();

	virtual SDO_API SequencePtr getOldSequence(DataObjectPtr dataObject);

	bool isInCreatedMap(DataObjectImpl* ob);

	void logDeletion(DataObjectImpl* ob,
		             DataObjectImpl* cont, const Property&  prop,
					 const char* oldpath,
					 bool loggingChildren = true
					 );

	void logCreation(DataObjectImpl* ob,
					DataObjectImpl* cont, const Property& prop
					);

	void logChange(DataObjectImpl* ob, const Property& prop
					);


	SDO_API void debugPrint();

	void removeFromChanges(DataObjectImpl* ob);


	//////////////////////////////////////////////////////////////////////////
	// API for re-creation of lists from the deserialization of
	// a change summary
	//////////////////////////////////////////////////////////////////////////

	void appendToCreations(const Property& p, 
							DataObjectPtr	dob,
							DataObjectPtr	cont);


	void appendToDeletions(const Property& p,
							DataObjectPtr dob, 
							DataObjectPtr cont,
							const char* oldpath);


	unsigned int ChangeSummaryImpl::stringConvert(
							char** value, 
							const char* c, 
							const Property& p);

	void appendToChanges(const Property& p, 
							DataObjectPtr dob, 
							SDOXMLString value,
							int index);

	void appendToChanges(const Property& p, 
							DataObjectPtr dob, 
							DataObjectPtr pdob,
							int index);

	DataObjectPtr matchDeletedObject(SDOXMLString path);


	private:


		void setPropValue(void** value, unsigned int *len, DataObjectImpl* ob, const Property& prop);
		void setManyPropValue(void** value, unsigned int *len, DataObjectImpl* ob, 
			DataObjectImpl* listob, const Property& prop);
		bool logging;

		typedef std::map<DataObjectImpl*, createLogItem>    CREATELOG_MAP;
		typedef std::map<DataObjectImpl*, deleteLogItem>    DELETELOG_MAP;
		typedef std::map<DataObjectImpl*, changeLogItem>    CHANGELOG_MAP;

		CHANGELOG_MAP changedMap;
		CREATELOG_MAP createdMap;
		DELETELOG_MAP deletedMap;

		ChangedDataObjectListImpl changedDataObjects;





};
};
};
#endif //_CHANGESUMMARYIMPL_H_
