<?php
/*
+-----------------------------------------------------------------------------+
| Copyright IBM Corporation 2006.                                             |
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

require 'SCA/SCA_RuntimeException.php';


if ( ! class_exists('SCA_ServiceUnavailableException', false) ) {
    class SCA_ServiceUnavailableException extends SCA_RuntimeException
    {
        /**
         * Class constructor
         *
         * @param string $message
         * @param int $code
         */
        public function __construct( $message       // required
        , $code = 10     // optional
        )
        {
            parent::__construct($message, $code);
        }/* End constructor function                                               */

        /**
         * Overridden functon to return string containing the exception details
         *
         * @return string
         */
        public function __toString()
        {
            return __CLASS__ . ": [{$this->code}]: {$this->message}\n" ;
        }/* End to string function                                                 */

    }/* End of SCA_ServiceUnavailableException class                              */

}/* End instance check                                                        */

?>