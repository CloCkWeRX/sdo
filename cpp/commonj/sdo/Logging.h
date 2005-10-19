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
| Author: Colin Thorne / Pete Robbins                                  | 
+----------------------------------------------------------------------+ 

*/
/* $Id$ */

#include "commonj/sdo/Logger.h"
#include "commonj/sdo/SDORuntimeException.h"

#ifndef SDO_LOGGING_H
#define SDO_LOGGING_H

#define HIGHVOLUME 40
#define INFO 30
#define WARNING 20
#define ERROR 10 

#define INDENT 1
#define OUTDENT -1
#define NODENT 0

#ifdef _DEBUG
#define LOGENTRY(level, methodName) \
if (Logger::loggingLevel >= level) \
Logger::logArgs(INDENT, level, "Entering: %s", methodName);

#define LOGEXIT(level, methodName) \
if (Logger::loggingLevel >= level) \
Logger::logArgs(OUTDENT, level, "Exiting: %s" ,methodName);

#define LOGINFO(level, message) \
if (Logger::loggingLevel >= level) \
Logger::log(NODENT, level, message);

#define LOGINFO_1(level, message, arg1) \
if (Logger::loggingLevel >= level) \
Logger::logArgs(NODENT,level, message, arg1);

#define LOGINFO_2(level, message, arg1, arg2) \
if (Logger::loggingLevel >= level) \
Logger::logArgs(NODENT,level, message, arg1, arg2);

#define LOGERROR(level, message) \
if (Logger::loggingLevel >= level) \
Logger::log(NODENT,level, message);

#define LOGERROR_1(level, message, arg1) \
if (Logger::loggingLevel >= level) \
Logger::logArgs(NODENT,level, message, arg1);

#define LOGERROR_2(level, message, arg1, arg2) \
if (Logger::loggingLevel >= level) \
Logger::logArgs(NODENT,level, message, arg1, arg2);

#define LOGSDOEXCEPTION(level, message, arg1) \
if (Logger::loggingLevel >= level) \
Logger::log(NODENT,level, message);\
Logger::logArgs(NODENT,level, "%s:%s\nIn %s\nAt %s line %ld\n",\
				((SDORuntimeException)arg1).getEClassName(),\
                ((SDORuntimeException)arg1).getMessageText(),\
                ((SDORuntimeException)arg1).getFunctionName(),\
				((SDORuntimeException)arg1).getFileName(),\
				((SDORuntimeException)arg1).getLineNumber());

#else // Not DEBUG

#define LOGSDOEXCEPTION(level, message, arg1) 

#define LOGENTRY(level, methodName)

#define LOGEXIT(level, methodName)

#define LOGINFO(level, message)

#define LOGINFO_1(level, message, arg1)

#define LOGINFO_2(level, message, arg1, arg2)
#define LOGERROR(level, message)
#define LOGERROR_1(level, message, arg1)
#define LOGERROR_2(level, message, arg1, arg2)

#endif
#endif // SDO_LOGGING_H
