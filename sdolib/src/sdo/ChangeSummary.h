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

#ifndef _CHANGESUMMARY_H_
#define _CHANGESUMMARY_H_
#include "export.h"
#pragma warning(disable: 4786)


#include "SettingList.h"
#include "ChangedDataObjectList.h"


using namespace std;
namespace commonj{
namespace sdo {

	///////////////////////////////////////////////////////////////////////////
    // A change summary is used to record changes to the objects in a data graph,
    // allowing applications to efficiently and incrementally update 
	//back-end storage when required.
 	///////////////////////////////////////////////////////////////////////////
 
	class ChangeSummary 
	{
  	public:

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

	virtual SDO_API ChangedDataObjectList&  getChangedDataObjects() = 0;


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
	virtual SDO_API SettingList& /*ChangeSummary.Setting*/ getOldValues(DataObjectPtr dataObject) = 0;

 	///////////////////////////////////////////////////////////////////////////
    // This method is intended for use by service implementations only.
    // If logging is already on no Exception is thrown.
 	///////////////////////////////////////////////////////////////////////////
	virtual SDO_API void beginLogging() = 0;

 	///////////////////////////////////////////////////////////////////////////
    // This method is intended for use by service implementations only.
    // An implementation that requires logging may throw an UnsupportedOperationException.
 	///////////////////////////////////////////////////////////////////////////
	virtual SDO_API void endLogging() = 0;

  
  
 	///////////////////////////////////////////////////////////////////////////
    // Indicates whether change logging is on (true) or off (false).
 	///////////////////////////////////////////////////////////////////////////
	virtual SDO_API bool isLogging() = 0;


  
 	///////////////////////////////////////////////////////////////////////////
    // Returns whether or not the specified data object was created while logging.
    // Any object that was added to the data graph
    // but was not in the data graph when logging began, 
    // will be considered created.
    // Param dataObject the data object in question.
    // Return true if the specified data object was created.
 	///////////////////////////////////////////////////////////////////////////
	virtual SDO_API bool isCreated(DataObjectPtr dataObject) = 0;

 	///////////////////////////////////////////////////////////////////////////
    // Returns whether or not the specified data object was deleted while logging.
    // Any object that is not contained by the data graph will be considered 
	// deleted.
    // Param dataObject the data object in question.
    // Return true if the specified data object was deleted.
 	///////////////////////////////////////////////////////////////////////////
	virtual SDO_API bool isDeleted(DataObjectPtr dataObject) = 0;


 	///////////////////////////////////////////////////////////////////////////
    // Returns whether or not the specified data object was updated while logging.
    // An object that was contained in the data graph when logging began, 
    // and remains in the graph when logging ends will be considered for changes.
    // An object considered modified must have at least one old value Setting.
    // Param dataObject the data object in question.
    // Return true if the specified data object was modified.
 	///////////////////////////////////////////////////////////////////////////
	virtual SDO_API bool isModified(DataObjectPtr dataObject) = 0;   

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
	virtual SDO_API const Setting& getOldValue(DataObjectPtr dataObject, const Property& property) = 0;

 	///////////////////////////////////////////////////////////////////////////
    // Returns the value of the container data object
    // at the point when logging began.
    // Param dataObject the object in question.
    // Return the old container data object.
   	///////////////////////////////////////////////////////////////////////////
	virtual SDO_API DataObjectPtr getOldContainer(DataObjectPtr dataObject) = 0;

 	///////////////////////////////////////////////////////////////////////////
    // Returns the value of the containment property data object property
    // at the point when logging began.
    // Param dataObject the object in question.
    // Return the old containment property.
 	///////////////////////////////////////////////////////////////////////////
	virtual SDO_API const Property& getOldContainmentProperty(DataObjectPtr dataObject) = 0;  

 	///////////////////////////////////////////////////////////////////////////
	// This method is intended for use by service implementations only.
	// undoes all changes in the log to restore the tree of 
	// DataObjects to its original state when logging began.
	// isLogging() is unchanged.  The log is cleared.
 	///////////////////////////////////////////////////////////////////////////
	virtual SDO_API void undoChanges() = 0;

	///////////////////////////////////////////////////////////////////////////
	// This method gives back the sequence of a data object as it
	// appeared when logging was switched on. The data object may be
	// a deleted data object or a changed data object. If the
	// data object was not sequenced, this returns null.
 	///////////////////////////////////////////////////////////////////////////

	virtual SDO_API SequencePtr getOldSequence(DataObjectPtr dataObject) = 0;

};
};
};
#endif //_CHANGESUMMARY_H_
