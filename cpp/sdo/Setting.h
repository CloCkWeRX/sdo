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

#ifndef SETTING_H
#define SETTING_H
#include "export.h"

#include "Type.h"
#include "DataObject.h"
#include "SDODate.h"

namespace commonj{
namespace sdo {

	class Property;

 	///////////////////////////////////////////////////////////////////////////
    // A Setting encapsulates a property and a corresponding single value of 
	// the property's type.
 	///////////////////////////////////////////////////////////////////////////
	class Setting
	{
  		public:

		Setting(bool set, bool nul, void* invalue, unsigned int len, const Property& p, 
			unsigned int inindex);

		Setting(const Setting& s);

		void copy(const Setting& s);

		Setting& operator=(const Setting& s);


		virtual ~Setting();
 		///////////////////////////////////////////////////////////////////////////
		// Returns the property of the Setting.
		// @return the Setting property.
 		///////////////////////////////////////////////////////////////////////////
		SDO_API const Property& getProperty() const;

		///////////////////////////////////////////////////////////////////////////
		// Returns the type of this property
 		///////////////////////////////////////////////////////////////////////////
		SDO_API const Type& getType() const;

		///////////////////////////////////////////////////////////////////////////
		// Returns the enum for the type of this property
 		///////////////////////////////////////////////////////////////////////////
		SDO_API Type::Types getTypeEnum() const;


	 	///////////////////////////////////////////////////////////////////////////
        // Returns the value of the Setting. This must be of the right type for 
        // @return the Setting value.
 		///////////////////////////////////////////////////////////////////////////
		SDO_API bool getBooleanValue() const;
		SDO_API char getByteValue() const;
		SDO_API wchar_t getCharacterValue() const;
		SDO_API unsigned int getBytesValue(char* buffer, unsigned int max) const;
		SDO_API unsigned int getStringValue(wchar_t* buffer, unsigned int max) const;
		SDO_API short getShortValue() const;
		SDO_API long getIntegerValue() const;
		SDO_API int64_t getLongValue() const;
		SDO_API float getFloatValue() const;
		SDO_API long double getDoubleValue() const;
		SDO_API const SDODate getDateValue() const;
		SDO_API const char* getCStringValue() ;
		SDO_API DataObjectPtr getDataObjectValue() const;



	 	///////////////////////////////////////////////////////////////////////////
        // Returns the index  of the Setting, if this is many valued
        // @return the Setting value.
 		///////////////////////////////////////////////////////////////////////////
		SDO_API unsigned int getIndex() const; 


		//////////////////////////////////////////////////////////////////////////
        // Returns the length of the setting, if this is a char* or wchar*
        // Setting value.
 		///////////////////////////////////////////////////////////////////////////
		SDO_API unsigned int getLength() const ; 
		
		///////////////////////////////////////////////////////////////////////////
        // Returns whether or not the property is set.
        // @return <code>true</code> if the property is set.
 		///////////////////////////////////////////////////////////////////////////
		SDO_API bool isSet() const;

		///////////////////////////////////////////////////////////////////////////
        // Returns whether or not the property is null.
        // @return <code>true</code> if the property is set.
 		///////////////////////////////////////////////////////////////////////////
		SDO_API bool isNull() const;

		private:

			bool bisSet;
			bool bisNull;
			void* value;
			const Property* theProp;
			unsigned int length;
			unsigned int index;
			char* strbuf;
	};
};
};

#endif
