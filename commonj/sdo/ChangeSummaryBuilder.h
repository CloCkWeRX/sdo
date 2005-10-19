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
| Author: Pete Robbins                                                 | 
+----------------------------------------------------------------------+ 

*/
/* $Id$ */

#ifndef _CHANGESUMMARYBUILDER_H_
#define _CHANGESUMMARYBUILDER_H_

#include "commonj/sdo/SAX2Parser.h"
#include "commonj/sdo/DataFactory.h"
#include "commonj/sdo/DataObject.h"
#include "commonj/sdo/SAX2Namespaces.h"
#include "commonj/sdo/PropertySetting.h"
#include "commonj/sdo/ChangeSummaryImpl.h"


#include <stack>

namespace commonj
{
	namespace sdo
	{

		/////////////////////////////////////////
		// Change summary storage classes - all
		// to be moved to their own location
		/////////////////////////////////////////

		class createDelete
		{
		public:
			createDelete();
			createDelete(SDOXMLString intype);
			virtual ~createDelete();
			SDOXMLString type;
			SDOXMLString value;
			int indexshift;
		};


		class changeAttribute
		{
		public:
			changeAttribute();
			changeAttribute(SDOXMLString inname,
							SDOXMLString invalue);
			virtual ~changeAttribute();
			SDOXMLString name;
			SDOXMLString value;
		};

		class changeElement
		{
		public:
			changeElement();
			changeElement(SDOXMLString inname, 
						SDOXMLString inpath, bool isRef, bool isDel);
			changeElement(SDOXMLString inname,
						SDOXMLString inpath,
				          SDOXMLString invalue, bool isRef, bool isDel);
			virtual ~changeElement();
			SDOXMLString name;
			SDOXMLString value;
			SDOXMLString path;
			bool isReference;
			bool isDeletion;
			int index;
		};



		class change
		{
		public:
			change();
			change(SDOXMLString inname, SDOXMLString ref);
			void addAttribute(changeAttribute ca);
			void addElement(changeElement ce);
			std::list<changeAttribute> attributes;
			std::list<changeElement> elements;
			SDOXMLString name;
			SDOXMLString reference;
		};
		
		class deletionAttribute
		{
		public:
			deletionAttribute();
			deletionAttribute(SDOXMLString inname,
							SDOXMLString invalue);
			virtual ~deletionAttribute();
			SDOXMLString name;
			SDOXMLString value;
		};

		class deletionElement
		{
		public:
			deletionElement();
			deletionElement(SDOXMLString inname);
			deletionElement(SDOXMLString inname,
				            SDOXMLString inpath, int inindex);
			virtual ~deletionElement();
			SDOXMLString name;
			SDOXMLString value;
			bool isDeletion;
			int index;
		};



		class deletion
		{
		public:
			deletion();
			deletion(SDOXMLString inname, SDOXMLString ref);
			void addAttribute(deletionAttribute ca);
			void addElement(deletionElement ce);
			void insertElement(deletionElement ce);
			SDOXMLString name;
			SDOXMLString reference;
			DataObjectPtr dob; /* the recreated one*/
			std::list<deletionAttribute> attributes;
			std::list<deletionElement> elements;
			bool completedprocessing;
		};

		class deletionListElement
		{
		public:
			deletionListElement();
			deletionListElement(deletion in_del, int in_index, SDOXMLString in_prev);
			SDOXMLString previous;
			deletion del;
			int index;
		};


		class ChangeSummaryBuilder
		{

			
		public:
			
			enum CsState
			{
			 baseState,
			 dealingWithCreateDelete,
			 dealingWithChange,
			 dealingWithChangeElement,
			 dealingWithDeletion,
			 dealingWithDeletionElement
			};


			ChangeSummaryBuilder(
				DataFactoryPtr df,
				DataObjectPtr& rootDO);
			
			virtual ~ChangeSummaryBuilder();

	
			virtual void processStart(
				const SDOXMLString& localname,
				const SDOXMLString& prefix,
				const SDOXMLString& URI,
				const SAX2Namespaces& namespaces,
				const SAX2Attributes& attributes);

			virtual void processChars(
				const SDOXMLString& chars);

			virtual void processEnd(
				const SDOXMLString& localname,
				const SDOXMLString& prefix,
				const SDOXMLString& URI);

			virtual void buildChangeSummary(
				 DataObjectPtr changeSummaryDO);

			virtual void populateDeletion(ChangeSummaryImpl* csi, DataObjectPtr dob,
											int index);

			virtual void handleDeletion(
							  ChangeSummaryImpl* csi, 
			                  int currentIndex, 
							  DataObjectPtr cont,
							  SDOXMLString path,
							  SDOXMLString prop);

			virtual void handleDeletion(ChangeSummaryImpl* csi,
				int index,
				SDOXMLString path);

			void shiftIndices(int index, int delta);
			
			SDOXMLString shiftedIndex(int index);


		private:
			DataFactoryPtr dataFactory;
			DataObjectPtr&  rootDataObject;
			
			CsState currentState;

			std::vector<deletionListElement> deletionList;
			std::vector<SDOXMLString> currentLocation;
			std::vector<createDelete> createDeletes;

			std::vector<change> changes;
			change currentChange;
			SDOXMLString previousChange;
			SDOXMLString currentLocalName;
			int changeIndex;

			std::vector<deletion> deletions;
			deletion currentDeletion;
			SDOXMLString previousDeletion;
			int deletionIndex;
			int deletionLevel;


		};
	} // End - namespace sdo
} // End - namespace commonj

#endif //_CHANGESUMMARYBUILDER_H_
