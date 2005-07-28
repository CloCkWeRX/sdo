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

#ifndef _COPYHELPER_H_
#define _COPYHELPER_H_

#include "DataObject.h"
#include "RefCountingPointer.h"

namespace commonj{
namespace sdo{
class CopyHelper
{
	public:
	///////////////////////////////////////////////////////////////////////////
    //  create a shallow copy of the DataObject:
    //    creates a new DataObject with the same values
    //      as the source dataObject for each property where
    //        property.getType().isDataObjectType() is false.
    //    The copied value is copiedDo.set(property, sourceDO.get(property))
    //    After copy for each value of a single-valued properties of non-DataObject type
    //      sourceDO.get(property) == copiedDO.get(property)
    //      and the same for each member of a List for multi-valued properties.
    //    The copied Object is unset for each property where
    //        property.getType().isDataObjectType() is true.
    //    Properties where property.getType().isDataObjectType() is true
    //      are not copied.
    //    A copied object shares metadata with the source object
    //      sourceDO.getType() == copiedDO.getType()
    //    If a ChangeSummary is part of the source DataObject
    //     the copy has a new, empty ChangeSummary.
    //      logging state is the same as the source ChangeSummary.
    //  
    //  @param dataObject to be copied
    //  @return copy of dataObject 
	///////////////////////////////////////////////////////////////////////////
    static SDO_API DataObjectPtr copyShallow(DataObjectPtr dataObject);
    static void transfer(DataObjectPtr to, DataObjectPtr from, Type::Types t);
    
	///////////////////////////////////////////////////////////////////////////
    // create a deep copy of the DataObject tree:
    //    Copies the source dataObject and all the contained
    //      DataObjects (property.isContainment() is true) recursively.
    //    Values of properties are copied as in shallow copy,
    //      and values of properties where 
    //        property.getType().isDataObjectType() is true
    //      are copied where each value copied must be a
    //      DataObject contained by the source dataObject.
    //    The source dataObject tree must be closed. 
    //    If any DataObject referenced is not in the containment
    //      tree an IllegalArgumentException is thrown.
    //    If a ChangeSummary is part of the copy tree the new 
    //      ChangeSummary refers to objects in the new DataObject tree.
    //      logging state is the same as the source ChangeSummary.
    //  
    //  @param dataObject to be copied.
    //  @return copy of dataObject
    //  @throws IllegalArgumentException if any referenced DataObject
    //    is not part of the contaiment tree.
	///////////////////////////////////////////////////////////////////////////
    static SDO_API DataObjectPtr copy(DataObjectPtr dataObject);

    
};
};
};

#endif //_COPYHELPER_H_
