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
 * Purpose:
 * --------
 * The following class provides methods to be able to log information to a file.
 * Information is given in the form of a message that may be categorised in order
 * to provide a measure of weighting to each message. The weighting mechanism also
 * allows a report to be compiled of the msgs that are categorised with the same
 * value.
 * Ancillary methods allow for control of the logging operations, and the current
 * status of the logger to be gathered.
 *
 *
 * Public Methods:
 * ---------------
 * __construct()
 * The construct method accepts 3 optional arguments, the first two are the directory
 * path, and the filename ( without extension ) respectively. The third provides an
 * option to delete the existing log file, or continue updating the file with 
 * messages.
 * The commands are SCA_Logger::DELETE, or SCA_Logger::UPDATE, and the command is 
 * defaulted to DELETE.
 *
 * setCategorisationLevel()
 * The setCategorisationLevel function provides a facility to filter out log messages
 * based on their categorisation level. Valid levels are provided by SCA_Logger 
 * constants
 *      SCA_Logger::INFORMATION
 *      SCA_Logger::WARNING
 *      SCA_Logger::RUNTIME
 *      SCA_Logger::BUSINESS
 *      SCA_Logger::ALL
 * The feature allows the log messages to be implanted at strategic points in the 
 * code and 'switched' on or off recording their message.
 *
 * toLog()
 * The toLog function does what it suggests and logs a message to the log file. 
 * An optional parameter allows each message to be categorised with -
 *      SCA_Logger::INFORMATION
 *      SCA_Logger::WARNING
 *      SCA_Logger::RUNTIME
 *      SCA_Logger::BUSINESS
 * the default is SCA_Logger::INFORMATION. Depending on the setting of the 
 * categorisation level ( see setCategorisationLevel() above ) a decision is made 
 * as to whether the message is logged or not.
 *
 * fromLog()
 * The fromLog function extracts all the contents of the log file to an array 
 * depending on the setting of the optional parameter to select the categorisation of
 * messages that are to be returned. The default setting is SCA_Logger::ALL, 
 * and the remainder are the same as toLog() above.
 *
 * stopLog()
 * The stopLog function stops logging activity.
 *
 * startLog()
 * The startLog function restarts ( the default is 'start' ) the logger.
 *
 * deleteLog()
 * Remove the current log file from the directory specified in the __construct
 *
 * logStatus()
 * The logStatus function returns an array containing the current state of the Logger
 * The values can be accessed through
 * 'run'    = Current state.
 * 'dir'    = Where the filename is being written to.
 * 'file'   = Filename in which the messages are being recorded.
 * 'catgry' = Level of categories of message being recorded.
 * 'count'  = Number of recorded messages.
 *
 * Private Methods:
 * ----------------
 * _getMsg()
 * Return all of or the last message in the log.
 *
 * _isLoggable()
 * Provides the filter as to whether a message is going to be written to a file
 * or read back out of the file.
 *
 * _decodeCat()
 * Puts a string representation of the current category into the status array.
 *
 */


/* Service Component Architecture Logger Class                                */
if ( ! class_exists('SCA_Logger', false) ) {
    class SCA_Logger
    {
        /* File Control                                                            */
        const DELETE              = 10 ;
        const UPDATE              = 11 ;

        /* Logging Control                                                         */
        const START               = true  ;
        const STOP                = false ;

        private      $run         = self::START ;

        /* Message Categorisation                                                  */
        const INFORMATION         = 0x01 ;
        const WARNING             = 0x02 ;
        const RUNTIME             = 0x04 ;
        const BUSINESS            = 0x08 ;
        const ALL                 = 0x0f ;
        const MASK                = 0x0f ;

        private      $catLevel    = self::ALL  ;

        private      $levelText   = array(  '1' => "Info"
        ,  '2' => "Warning"
        ,  '4' => "Runtime"
        ,  '8' => "Business"
        ,  '15' => "ALL"
        ) ;

        private      $levelIndex  = 4 ;

        /* File path information                                                   */
        const        LASTMSG      = 'EOF' ;
        private      $dirpath     = "." ;
        private      $file        = "scalog" ;
        private      $extn        = "log"    ;
        private      $logfile     = ""       ;

        /* Line count of the log messages                                          */
        private      $msgIndex    = 0 ;

        /**
         * Logger constructor to set a directory and filename that is different to 
         * the default values in the file.
         *
         * @param string $dirpath   Optional directory ( uses current directory 
         * when not specified )
         * @param string $file      Optional name of file ( without extension, 
         * default is 'scalog' )
         * @param int    $command   Optional Delete or Update an existing log file. 
         * ( default = DELETE )
         */
        public function __construct( $dirpath  = null
        , $file     = null
        , $command  = self::DELETE
        )
        {
            if ( $dirpath  !== null )
            $this->dirpath = $dirpath ;

            if ( $file !== null )
            $this->file = $file ;

            $this->logfile = "{$this->dirpath}/{$this->file}.{$this->extn}" ;

            date_default_timezone_set('UTC');

            /* Delete an existing file, or get the last message index              */
            if ( $command == self::DELETE  ) {
                $this->deleteLogFile();

            } else {
                $msg      = $this->_getMsg(SCA_Logger::LASTMSG, $this->logfile, SCA_Logger::ALL);
                $start    = strpos($msg[0], '[') + 1;
                $end      = strpos($msg[0], ']') - 1;
                $strIndex = substr($msg[0], $start, $end);
                $this->msgIndex = (int)$strIndex;

            }/* End delete file                                                    */

        }/* End constructor                                                        */

        /**
         * Set, or alter the categorisation level the logger to filter messages that
         * are to be logged to file.
         * The level can be -
         *  INFORMATION
         *  WARNING
         *  RUNTIME
         *  BUSINESS
         *  ALL
         *
         * or a combination such as
         *
         * (INFORMATION | RUNTIME ) or (BUSINESS | WARNING) ... etc
         *
         * @param int $level    The recording level
         * @return boolean      Success of failure
         */
        public function setCategorisationLevel( $level )
        {
            $return = true ;

            if ( $level === self::MASK )
            $level = self::ALL ;

            if ( $level >= self::INFORMATION && $level <= self::ALL )
            $this->catLevel =  ( $level & self::MASK ) ;
            else
            $return = false ;

            return $return ;

        }/* End set level function                                                 */

        /**
         * Enter a message into the log file
         *
         * @param string $msg (Required) message string
         * @param int $level  (Optional) categorisation level of the message
         */
        public function toLog( $msg
        , $level  = null
        )
        {
            /* When logging is underway ...                                        */
            if ( $this->run ) {
                if ($level === null)
                $level = self::INFORMATION  ;

                /*  ... and is this message selected for recording                 */
                if ( ($categorisation = $this->_isLoggable($level)) !== null ) {
                    $callingFile = "" ;
                    $backtrace   = debug_backtrace();
                    if ( count($backtrace) > 0 )
                    $callingFile = $backtrace[ 0 ][ 'file' ] ;

                    ++$this->msgIndex ;
                    $index = "[{$this->msgIndex}]" ;

                    $time  = getDate();
                    $mSecs = gettimeofday();

                    $timeMsg = "{$time[ 'mday' ]}/{$time[ 'mon' ]}/{$time[ 'year' ]} "
                    . "{$time[ 'hours' ]}:{$time[ 'minutes' ]}:{$time[ 'seconds' ]}"
                    . "::{$mSecs[ 'usec' ]} " ;

                    $logMsg = "{$index} {$categorisation} {$timeMsg} {$callingFile} - {$msg}\n" ;
                    file_put_contents($this->logfile, $logMsg, FILE_APPEND);

                }/* End record this                                                */

            }/* End running                                                        */

        }/* End to log function                                                   */

        /**
         * Return the contents of the log file in an array.
         *
         * @param  int $categorisation   (Optional) The message categorisation to be 
         * selected
         * @return array                 Contains the contents of the log file
         */
        public function fromLog( $categorisation = null )
        {
            $this->stopLog();
            $logList = array() ;

            if ( $categorisation === null )
            $categorisation = self::ALL ;

            $logList = $this->_getMsg(null, $this->logfile, $categorisation);

            $this->startLog();

            return $logList ;

        }/* End from log function                                                  */


        /**
        * Stop any logging
        *
        */
        public function stopLog()
        {
            $this->run = self::STOP ;

        }/* End stop log function                                                  */

        /**
        * Start/Restart the logger
        *
        */
        public function startLog()
        {
            $this->run = self::START ;

        }/* End start log function                                                 */

        /**
         * Return the internal setup of the logger in an indexed array
         * 'run'    = Current state.
         * 'dir'    = Where the filename is being written to.
         * 'file'   = Filename in which the messages are being recorded.
         * 'catgry' = Level of categories of message being recorded.
         * 'count'  = Number of recorded messages.
         *
         * @return array    Containing the current log settings
         */
        public function logStatus()
        {
            $this->run ? $runState = "Running" : $runState = "Stopped";

            $status    = array() ;
            $status[ 'run'    ] = $runState ;
            $status[ 'dir'    ] = $this->dirpath ;
            $status[ 'file'   ] = $this->file ;
            $status[ 'catgry' ] = $this->_decodeCat($this->catLevel);
            $status[ 'count'  ] = $this->msgIndex ;

            return $status ;

        }/* End log status                                                         */

        /**
          * Delete the current log file
          *
          */
        public function deleteLogFile()
        {
            if ( realpath($this->logfile) ) {
                unlink($this->logfile);
            }/* End file exists                                                    */

        }/* End delete log file function                                           */

        /*-------------------------------------------------------------------------*/

        /**
         * Get the last message or an array of messages from the log file
         *
         * @param int $command        null = from the top 'EndOf' the last msg
         * @param string $from        path name of the file to be read
         * @param int $categorisation level of categorisation to be filtered
         * @return array              Containing the Messages, the last message 
         * or an error.
         */
        private function _getMsg( $command
        , $from
        , $categorisation
        )
        {
            $logList = array() ;
            $i       = 0 ;

            /* Ensure that the file exists                                         */
            if ( realpath($from) ) {
                if ( ($logHandle = fopen($from, "rb"))  !== false ) {
                    fflush($logHandle);  // make sure everything is writ.
                    $cat = $this->_isLoggable($categorisation);

                    /* Walk through the file ...                                   */
                    while ( !feof($logHandle) ) {
                        $msg = trim(fgets($logHandle));

                        /* Jump out when the there is no message ( normally EOF )  */
                        if ( (strlen($msg)) === 0 )
                        break ;

                        /* Save all the messages to an array or just the last one  */
                        if ( $command === null ) {
                            /* To save it or not ... that is the question          */
                            if ( $categorisation === self::ALL )
                            $logList[ $i++ ] = $msg ;

                            else
                            {
                                if ( (strpos($msg, $cat)) !== false )
                                $logList[ $i++ ] = $msg ;

                            }/* End save it                                        */

                        } else {
                            // ... or just monitor to the last message.
                            $logList[ $i ] = $msg ;

                        }/* End all of the messages                                */

                    }/*End until end of file                                       */

                    fclose($logHandle);

                } else {
                    $logList[ $i ] = " ERROR:: Unable to open the {$this->logfile} file" ;

                }/* End files opened ok                                            */
            } else {
                $logList[ $i ] = "ERROR:: {$this->logfile} file does not exist" ;

            }/* End does the file exist                                            */

            return $logList ;

        }/* End get message function                                               */

        /**
         * Check that the level of log message is recordable.
         *
         * @param int $level  Level of log message
         * @return string     Printable categorisation, or null
         */
        private function _isLoggable( $level )
        {
            /* When the level of categorisation is within the categrosation range  */
            if ( !($level < self::INFORMATION) && !($level > self::BUSINESS) ) {
                /*  ... and when the current level accepts that category ...       */
                $x = $this->catLevel & $level ;
                if ( $x != 0 ) {
                    return $this->levelText[ "$level" ] ;

                }/* End within current level                                       */

            }/* End in range                                                       */

            return  null ;

        }/* End is loggable function                                               */

        /**
         * Return a string showing the level of categorisation of the recorded msgs
         *
         * @param int $level    Current categorisation level
         * @return string       Containing the settings
         */
        private function _decodeCat( $level )
        {
            $levelString = 'ALL' ;

            if ( $level < self::ALL  ) {
                $levelString = "" ;
                if ( $level & self::BUSINESS )
                $levelString .= "Business " ;
                if ( $level  & self::RUNTIME )
                $levelString .= "Runtime " ;
                if ( $level & self::WARNING )
                $levelString .= "Warning " ;
                if ( $level & self::INFORMATION )
                $levelString .= "Information" ;

            }/* End when not the default                                           */

            return $levelString ;

        }/* End decode categorisation level                                        */

    }/* End SCA Class                                                              */

}/* End instance check                                                             */

?>