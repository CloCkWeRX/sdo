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

#include "Property.h"
#include "Type.h"
#include "SDORuntimeException.h"
#include "PropertyList.h"
#include "Sequence.h"
#include "TypeList.h"
#include "DataObject.h"
#include "DataObjectList.h"
#include "XSDHelper.h"
#include "XMLHelper.h"
#include "XpathHelper.h"
#include "SdoRuntime.h"
#include "export.h"
