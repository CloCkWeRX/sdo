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

#define SDO4CPP_VERSION 20051108

#include "commonj/sdo/RefCountingPointer.h"

#if defined(WIN32)  || defined (_WINDOWS)

#define int64_t __int64

#ifdef SDO_EXPORTS
#pragma warning(disable: 4786)
#    define SDO_API __declspec(dllexport)
#    define SDO_SPI __declspec(dllexport)
#    define EXPIMP
#else
#    define SDO_API __declspec(dllimport)
#    define SDO_SPI __declspec(dllimport)
#    define EXPIMP extern
#endif

#else
#include <sys/time.h>
#include <inttypes.h> 
#include <stdlib.h>
#include <string.h>
#include <wchar.h>
#    define SDO_API
#    define SDO_SPI
#    define EXPIMP
#endif

