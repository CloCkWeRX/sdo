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

#ifndef _SDO_SDODATE_H_
#define _SDO_SDODATE_H_

#include "commonj/sdo/export.h"
#include "time.h"


namespace commonj{
namespace sdo{


	///////////////////////////////////////////////////////////////////////////
    // A representation of the type of a date.
	///////////////////////////////////////////////////////////////////////////


class SDODate
{

public:


	virtual SDO_API ~SDODate();

	SDO_API SDODate(time_t inval);

	///////////////////////////////////////////////////////////////////////////
    // Returns the time_t.
	///////////////////////////////////////////////////////////////////////////
    virtual SDO_API const time_t getTime() const;

	///////////////////////////////////////////////////////////////////////////
    // A default formatter for testing.
	///////////////////////////////////////////////////////////////////////////
	virtual const char* ascTime(void) const;
  

private:
	time_t value;
};

};
};
#endif //_SDO_SDODATE_H_

