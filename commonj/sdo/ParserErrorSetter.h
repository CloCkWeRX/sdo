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
// ParserErrorSetter.h: class allowing parser to push errors back.
//
//////////////////////////////////////////////////////////////////////

#ifndef _PARSER_ERROR_SETTER_H_
#define _PARSER_ERROR_SETTER_H_

#include "commonj/sdo/export.h"

#include <vector>
using namespace std;

namespace commonj{
namespace sdo{

class ParserErrorSetter
{
public:
	virtual ~ParserErrorSetter();
	virtual void setError(const char* message) = 0;
};
};
};

#endif
