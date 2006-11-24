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

/**
 * Purpose
 * The SCA Exception class provides an extended calss to record errors and
 * exceptions that are cast withing the SCA classes.
 *
 * Public Methods
 * __construct - Create an instance of the class. The function requires that a
 *               message describing the problem is entered, and an optional
 *               exception code that are defined as constants at the head of the
 *               file.
 *
 * __toString  - Overides the Exception class to provide a full description of
 *               the recorded exception
 *
 * exceptionString - Provides a description of the exception without an
 *                   exception code.
 *
 * decodeCode - Decodes the exception code into a string
 *
 */

if ( ! class_exists('SCA_RuntimeException', false) ) {
    class SCA_RuntimeException extends Exception
    {
        /* SOME CODES .... needs extending                                         */
        const ATTENTION =  10 ;

        const WARNING   = 100 ;
        const SEVERE    = 101 ;
        const RETRY     = 102 ;

        const SOAPFAULT = 200 ;

        private $decodeCodes    = array( self::ATTENTION => "Attention"
        , self::WARNING   => "Warning"
        , self::SEVERE    => "Severe"
        , self::RETRY     => "Retry"
        , self::SOAPFAULT => "SoapFault"
        ) ;

        /**
        * Class constructor
        *
        * @param string $message
        * @param int $code
        */
        public function __construct( $message                     // required
        , $code = self::ATTENTION      // optional
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
            //    return __CLASS__ . ": [{$this->code}]: {$this->message}\n" ;
            // remove the display of the code unless we document its meanings
            return __CLASS__ . ": {$this->message}\n" ;
        }/* End to string function                                                 */

        /**
         * Return exception details that doe not show the code
         *
         * @return string
         */
        public function exceptionString()
        {
            return __CLASS__ . ": {$this->message}" ;
        }/* End exception string function                                          */

        /**
         * Return a string definition of the code.
         *
         * @param int $code
         * @return string
         */
        public function decodeCode( $code )
        {
            return $this->decodeCodes[ $code ] ;
        }/* End decode code value                                                  */

    }/* End of SCA_RuntimeException class                                          */

}/* End instance check                                                             */
?>