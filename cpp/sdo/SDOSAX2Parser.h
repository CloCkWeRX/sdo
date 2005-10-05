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

#ifndef _SDOSAX2PARSER_H_
#define _SDOSAX2PARSER_H_
#include "SAX2Parser.h"

#include "DataFactory.h"
#include "DataObject.h"
#include "SAX2Namespaces.h"
#include "PropertySetting.h"

#include <stack>

namespace commonj
{
	namespace sdo
	{

		// WORK IN PROGRESS
		//class changeElement
		//{
		//public:

		//	changeElement();

		//	changeElement(
		//		SDOXMLString location,
		//		SDOXMLString inname,
		//		int index);
		//	virtual void setValue(SDOXMLString v);

		//	virtual ~changeElement();

		//private:
		//	SDOXMLString location;
		//	SDOXMLString name;
		//	SDOXMLString value;
		//	int index;
		//};

		//class changeAttribute
		//{
		//public:
		//	changeAttribute();

		//	changeAttribute(
		//		SDOXMLString inname,
		//		SDOXMLString invalue);
		//private:
		//	SDOXMLString name;
		//	SDOXMLString value;
		//};

		//class change
		//{
		//public:
		//	change();

		//	change(SDOXMLString inName);

		//	virtual ~change();

		//	virtual SDOXMLString getName();

		//	virtual std::vector<changeElement> getElements();

		//	virtual std::vector<changeAttribute> getAttributes();

		//	virtual void setLocation(SDOXMLString instr);

		//	virtual void moveLocation(SDOXMLString instr);

		//	virtual void popLocation();


		//private:

		//	std::vector<changeElement> elements;
		//	std::vector<changeAttribute> attributes;
		//	SDOXMLString name;
		//	std::stack<SDOXMLString> location;
		//};

		//class createDelete
		//{
		//public:
		//	createDelete();

		//	createDelete(SDOXMLString intype);

		//	virtual ~createDelete();

		//	virtual SDOXMLString getValue();

		//	virtual SDOXMLString getType();

		//	void setValue(SDOXMLString s);

		//private:
		//	SDOXMLString type;
		//	SDOXMLString value;
		//};
		
		class SDOSAX2Parser : public SAX2Parser
		{
			
		public:
			
			SDOSAX2Parser(
				DataFactoryPtr df,
				const SDOXMLString& targetNamespaceURI,
				DataObjectPtr& rootDO);
			
			virtual ~SDOSAX2Parser();

			virtual void startDocument();
			virtual void endDocument();
			
			virtual void startElementNs(
				const SDOXMLString& localname,
				const SDOXMLString& prefix,
				const SDOXMLString& URI,
				const SAX2Namespaces& namespaces,
				const SAX2Attributes& attributes);
			
			virtual void endElementNs(
				const SDOXMLString& localname,
				const SDOXMLString& prefix,
				const SDOXMLString& URI);
			
			virtual void characters(const SDOXMLString& chars);

			friend std::istream& operator>>(std::istream& input, SDOSAX2Parser& parser);
			friend std::istringstream& operator>>(std::istringstream& input, SDOSAX2Parser& parser);

			
		private:
			SDOXMLString targetNamespaceURI;
			DataFactoryPtr dataFactory;
			DataObjectPtr&  rootDataObject;
			
			std::stack<DataObjectPtr>	dataObjectStack;
			
			DataObjectPtr	currentDataObject;
			bool			isDataGraph;
			
			void			setCurrentDataObject(DataObjectPtr currentDO);
			const Type*		currentDataObjectType;
			const SDOXMLString& getSDOName(const Type& type, const SDOXMLString& localName);

			PropertySetting currentPropertySetting;

			void reset();

			bool setNamespaces;
			SAX2Namespaces documentNamespaces;

			bool changeSummary;
			DataObjectPtr changeSummaryDO;
			bool changeSummaryLogging;
			//WORK IN PROGRESSbool dealingWithChangeSummary;
			// inside a change...
			//int changeLevel;
			//int elementIndex;
			//SDOXMLString currentElement;
			//change currentChange;
			// these represent the changes , deletions and creations...
			//std::vector<createDelete> createDeletes;
			//std::vector<change>       changes;

			//std::stack<SDOXMLString> changestack;

			bool ignoreEvents;
			struct ignoretag
			{
				SDOXMLString localname;
				SDOXMLString uri;
				SDOXMLString prefix;
				int			 tagCount;
			} ignoreTag;


			typedef std::map<SDOXMLString, DataObjectPtr> ID_MAP;
			ID_MAP IDMap;

			class IDRef
			{
			public:
				IDRef(DataObjectPtr dataobj,
				const SDOXMLString& prop,
				const SDOXMLString& val)
				: dataObject(dataobj), property(prop), value(val)
				{}

				DataObjectPtr dataObject;
				SDOXMLString property;
				SDOXMLString value;
			};

			typedef std::list<IDRef> ID_REFS;
			ID_REFS IDRefs;					
		};
	} // End - namespace sdo
} // End - namespace commonj

#endif //_SDOSAX2PARSER_H_
