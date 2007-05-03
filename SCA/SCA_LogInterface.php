<?php
/*
+-----------------------------------------------------------------------------+
| (c) Copyright IBM Corporation 2006, 2007.                                   |
| All Rights Reserved.                                                        |
+-----------------------------------------------------------------------------+
| Licensed under the Apache License, Version 2.0 (the "License"); you may not |
| use this file except in compliance with the License. You may obtain a copy  |
| of the License at -                                                         |
|                                                                             |
|                   http://www.apache.org/licenses/LICENSE-2.0                |
|                                                                             |
| Unless required by applicable law or agreed to in writing, software         |
| distributed under the License is distributed on an "AS IS" BASIS, WITHOUT   |
| WARRANTIES OR CONDITIONS OF ANY KIND, either express or implied.            |
| See the License for the specific language governing  permissions and        |
| limitations under the License.                                              |
+-----------------------------------------------------------------------------+
| Author: Graham Charters,                                                    |
|         Matthew Peters,                                                     |
|         Megan Beynon,                                                       |
|         Chris Miller.                                                       |
|                                                                             |
+-----------------------------------------------------------------------------+
$Id$
*/

/**
 * Purpose:
 * --------
 * The log interface is to provide a minimum set of methods that must be created
 * in order to work within the SCA_LogFactory. In order to ensure that in
 * a 'non-SCA4PHP' environment the logging methods can still function to a different
 * logger.
 *
 * Public Methods:
 * ---------------
 * log()
 * stopLog()
 * startLog()
 *
 * Private Methods:
 * ----------------
 *
 */
if ( ! interface_exists('iLogInterface', false) ) {
    interface iLogInterface {

        /**
         * Send a line of text to the log file
         *
         * @param string $txtentry      entry for the log file
         * @param string $file          filename containing the line originating
         *                              the entry.
         * @param string $line          line at wich the entry is made
         * @param int    $level         level at which entry is recorded
         */
        public function log ( $txtentry
                            , $file     = ""
                            , $line     = ""
                            , $level    = null
                            ) ;

        /**
         * Stop logging
         *
         */
        public function stopLog() ;

        /**
         * Start/Restart logging
         *
         */
        public function startLog() ;

    }/* End log interface                                                    */

}/* End ensure only included once                                            */
?>