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

#ifndef SETTINGLIST_H
#define SETTINGLIST_H


#include <vector>
#include "Setting.h"

namespace commonj{
namespace sdo{

typedef std::vector<Setting> SETTING_VECTOR;

class SettingList
{

public:
    SettingList(SETTING_VECTOR sl);
//	SettingList(const SettingList &pin);
	SettingList();

	virtual ~SettingList();
	virtual Setting& operator[] (int pos) const;

	virtual int size () const;
	virtual void insert (unsigned int index, const Setting& d);
	virtual void append (const Setting& d);
	virtual void remove (unsigned int index);


private: 
	SETTING_VECTOR slist;
	SETTING_VECTOR getVec() const;

	void validateIndex(int index) const;
};

};
};

#endif
