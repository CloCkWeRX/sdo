/*
+----------------------------------------------------------------------+
| (c) Copyright IBM Corporation 2007.                                  |
| All Rights Reserved.                                                 |
+----------------------------------------------------------------------+
|                                                                      |
| Licensed under the Apache License, Version 2.0 (the "License"); you  |
| may not use this file except in compliance with the License. You may |
| obtain a copy of the License at                                      |
| http://www.apache.org/licenses/LICENSE-2.0                           |
|                                                                      |
| Unless required by applicable law or agreed to in writing, software  |
| distributed under the License is distributed on an "AS IS" BASIS,    |
| WITHOUT WARRANTIES OR CONDITIONS OF ANY KIND, either express or      |
| implied. See the License for the specific language governing         |
| permissions and limitations under the License.                       |
+----------------------------------------------------------------------+
| Author: Caroline Maynard                                             |
+----------------------------------------------------------------------+
*/

/* $Id$ */

#ifndef PHP_SDOUSERMACROS_H
#define PHP_SDOUSERMACROS_H

/*
 * This version created to work within the PHP build environment.
 */
#include "php_reentrancy.h"
#define tuscany_localtime_r(a, b) php_localtime_r(&a, &b)


#endif //PHP_SDOUSERMACROS_H
