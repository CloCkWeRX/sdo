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

// XMLDAS.cpp: implementation of the XMLDAS class.
//
//////////////////////////////////////////////////////////////////////
#pragma warning(disable: 4786)
#include "commonj/sdo/xmldas/XMLDASImpl.h"

namespace commonj
{
	namespace sdo
	{
		
		namespace xmldas
		{
			//////////////////////////////////////////////////////////////////////
			// Construction/Destruction
			//////////////////////////////////////////////////////////////////////
			
			XMLDAS* XMLDAS::create(const char* schema)
			{
				return new XMLDASImpl(schema);
			}
			
			XMLDAS::~XMLDAS()
			{
			}
			
		} // End - namespace xmldas
	} // End - namespace sdo
} // End - namespace commonj
