/*
 * Licensed to the Apache Software Foundation (ASF) under one
 * or more contributor license agreements.  See the NOTICE file
 * distributed with this work for additional information
 * regarding copyright ownership.  The ASF licenses this file
 * to you under the Apache License, Version 2.0 (the
 * "License"); you may not use this file except in compliance
 * with the License.  You may obtain a copy of the License at
 * 
 *   http://www.apache.org/licenses/LICENSE-2.0
 *   
 * Unless required by applicable law or agreed to in writing,
 * software distributed under the License is distributed on an
 * "AS IS" BASIS, WITHOUT WARRANTIES OR CONDITIONS OF ANY
 * KIND, either express or implied.  See the License for the
 * specific language governing permissions and limitations
 * under the License.
 */

/* $Rev: 495235 $ $Date$ */

#include "commonj/sdo/SDODate.h"

// According to Linux, localtime_r is defined as
// struct tm *localtime_r(const time_t *timep, struct tm *result);
// However, Windows doesn't have localtime_r, and actually varies what it does
// have across dfferent versions. To accommodate this we use a macro that
// resolves to the correct settings on linux and MS VC8. For other platforms
// it will be necessary to modify this file or override the macro for which we
// provide the SDOUserMacros.h file so that any required macro definition can
// supply other includes if they are needed.

#include "commonj/sdo/SDOUserMacros.h"
#ifndef tuscany_localtime_r
#if defined(WIN32)  || defined (_WINDOWS)
  #define tuscany_localtime_r(value, tmp_tm) localtime_s(&tmp_tm, &value);
#else
  #define tuscany_localtime_r(value, tmp_tm) localtime_r(&value, &tmp_tm);
#endif
#endif // tuscany_localtime_r

namespace commonj{
namespace sdo{


     SDODate::~SDODate()
     {
     }

     SDODate::SDODate(time_t inval)
     {
         value = inval;
     }

    ///////////////////////////////////////////////////////////////////////////
    //
    ///////////////////////////////////////////////////////////////////////////

    const time_t SDODate::getTime(void) const
    {
        return value;
    }

    const char* SDODate::ascTime(void) const
    {
		struct tm tmp_tm;

		tuscany_localtime_r(value, tmp_tm);

        return asctime(&tmp_tm);
    }

};
};
// end - namespace sdo

