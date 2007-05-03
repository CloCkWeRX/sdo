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

require 'SCA/SCA_LogInterface.php' ;

/**
 * Purpose:
 * --------
 * The following class implements, and extends the SCA_LogInterface, to provide
 * methods that control, and enter log information to a file.
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
 * singleSCA_Logger()
 * Return a singleton SCA_Logger.
 *
 * SCA_Logger()
 * The construct method accepts 3 optional arguments, the first two are the directory
 * path, and the filename ( without extension ) respectively. The third provides an
 * option to delete the existing log file, or continue updating the file with
 * messages.
 * The commands are SCA_LOGGER_DELETE, or SCA_LOGGER_UPDATE, and the command is
 * defaulted to DELETE.
 *
 * setLogLevel()
 * The seetLogLevel function provides a facility to filter out log messages
 * based on their categorisation level. Valid levels are provided by SCA_Logger
 * constants
 *     SCA_LOGGER_CRITICAL
 *     SCA_LOGGER_ERROR
 *     SCA_LOGGER_WARNING
 *     SCA_LOGGER_INFO
 *     SCA_LOGGER_DEBUG
 * The feature allows the log messages to be implanted at strategic points in the
 * code and 'switched' on or off recording their message level.
 * NOTE: 'NONE will stop the logger all the others start the logger.
 *
 * log()
 * The toLog function does what it suggests and logs a message to the log file.
 * An optional parameter allows each message to be categorised with -
 *     SCA_LOGGER_CRITICAL
 *     SCA_LOGGER_ERROR
 *     SCA_LOGGER_WARNING
 *     SCA_LOGGER_INFO
 *     SCA_LOGGER_DEBUG
 * the default is SCA_LOGGER_INFO. Depending on the setting of the
 * categorisation level ( see setLogLevel() above ) a decision is made
 * as to whether the message is logged or not.
 *
 * fromLog()
 * The fromLog function extracts all the contents of the log file to an array
 * depending on the setting of the optional parameter to select the categorisation of
 * messages that are to be returned. The default setting is SCA_LOGGER_ALL,
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
 * _tolog()
 * Format the log entry message, and write it to file.
 *
 * _getMsg()
 * Return all of or the last message in the log.
 *
 * _isLoggable()
 * Provides the filter as to whether a message is going to be written to a file
 * or read back out of the file.
 *
 * _toLevelString()
 * Puts a string representation of the current category into the status array.
 *
 * _fromLevelString()
 * Interprets a declaration and returns the integer equivelent.
 *
 */

/* Service Component Architecture Logger Class                                */
if ( ! class_exists('SCA_Logger', false) ) {
    /* File Control                                                           */
    define( 'SCA_LOGGER_DELETE'       , 10 ) ; // Delete the existing log, and start again
    define( 'SCA_LOGGER_UPDATE'       , 11 ) ; // Append to the existing log

    /* Logging Control                                                        */
    define( 'SCA_LOGGER_START'        , true  ) ; // Start the logger
    define( 'SCA_LOGGER_STOP'         , false ) ; // Stop the logger

    /* Log Entry Message Filter Levels                                        */
    define( 'SCA_LOGGER_CRITICAL'     , 0x01 ) ; // Critical Error SCA Halted
    define( 'SCA_LOGGER_ERROR'        , 0x02 ) ; // Error - SCA may not react as expected
    define( 'SCA_LOGGER_WARNING'      , 0x04 ) ; // Warning - Computation error
    define( 'SCA_LOGGER_INFO'         , 0x08 ) ; // Information message
    define( 'SCA_LOGGER_DEBUG'        , 0x10 ) ; // Debug message

    define( 'SCA_LOGGER_ALL'          , 0x1f ) ; // all types
    define( 'SCA_LOGGER_NONE'         , 0x00 ) ; // no types ( stop logging )

    class SCA_Logger implements  iLogInterface
    {
        /* Singleton instance of the logger.                                  */
        private static $loghandle   = null ;

        private        $run         = SCA_LOGGER_STOP ;
        private        $catlevel    = SCA_LOGGER_ALL  ;

        private        $levelindex  = 4 ;

        /* File path information                                              */
        const          LASTMSG      = 'EOF' ;
        private        $dirpath     = "." ;
        private        $file        = "SCA" ;
        private        $extn        = "log"    ;
        private        $logfile     = ""       ;

        /* Line count of the log messages                                     */
        private        $msg_index    = 0 ;

        /**
         * Create a singleton Logger.
         *
         * @param string $dirpath   Optional directory ( uses current directory
         *                          when not specified )
         * @param string $file      Optional name of file ( without extension,
         *                          default is 'scalog' )
         * @param int    $command   Optional Delete or Update an existing log file.
         *                          ( default = DELETE )
         * @return object           handle to the logger.
         */
        public static function &singleSCA_Logger()
        {
            /* Make only one logger                                           */

            if ( ! (isset( self::$loghandle )) ) {
                self::$loghandle = new SCA_Logger();

            }/* End of singleton pattern                                      */

            return self::$loghandle ;

        }/* End single scs logger                                             */

        /**
         * Logger constructor to set a directory and filename that is different to
         * the default values in the file.
         *
         * @param string $dirpath   Optional directory ( uses current directory
         *                          when not specified )
         * @param string $file      Optional name of file ( without extension,
         *                          default is 'scalog' )
         * @param int    $command   Optional Delete or Update an existing log file.
         *                          ( default = DELETE )
         */
        public function SCA_Logger()
        {

        }/* End constructor                                                   */

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
        ) {

            /* Exit quickly when logging has been stopped.                    */
            if ( $this->run ) {
                /* After ensuring that the level is valid ...                 */
                if ( $level !== null ) {
                    if ( ($categorisation = $this->_isLoggable($level)) !== null ) {
                        $this->_tolog( $txtentry, $file, $line, $categorisation ) ;
                    }
                }else{
                    /* ... otherwise use the default level                    */
                    $this->_tolog( $txtentry, $file, $line, $this->_toLevelString( SCA_LOGGER_INFO ) ) ;
                }

            }/* End logging stopped test                                      */

        }/* End log function                                                  */

        /**
        * Stop any logging
        *
        */
        public function stopLog() {
            $this->run = SCA_LOGGER_STOP ;

        }/* End stop log function                                             */

        /**
        * Start/Restart the logger
        *
        */
        public function startLog() {
            $dirpath = SCA_Helper::getTempDir().'/log';
            $file = 'SCA';
            $command = SCA_LOGGER_UPDATE;
            if ( $dirpath  !== null )
            {
                $this->dirpath = $dirpath ;
                if( ! file_exists( $dirpath ) )
                {
                    mkdir( $dirpath ) ;
                }

            }

            if ( $file !== null )
            $this->file = $file ;

            $this->logfile = "{$this->dirpath}/{$this->file}.{$this->extn}" ;

            date_default_timezone_set('UTC');

            /* Delete an existing file, or get the last message index     */
            if ( $command == SCA_LOGGER_DELETE  ) {
                $this->deleteLogFile();

            } else {
                $this->msgIndex = 1;

            }/* End delete file                                               */
            $this->run = SCA_LOGGER_START ;

        }/* End start log function                                            */

        /**
        * The categorisation of the log level provides a binary filter against
        * which the 'level' of a logentry can be compared before entry into
        * the log file. As a numeric value can consist of one or more
        * binary levels combinations of levels may be built -
        *
        *  SCA_LOGGER_CRITICAL | SCA_LOGGER_INFO
        *
        * will only log critical and information entries for instance.
        *
        * @param int $level    The recording level
        * @return boolean      Success of failure
        */
        public function setLogLevel( $level ) {
            $return = true ;
            $level =  ( $level & SCA_LOGGER_ALL ) ;

            if ( $level >= SCA_LOGGER_NONE && $level <= SCA_LOGGER_ALL ) {
                $this->catlevel = $level ;

            }else{
                $return = false ;

            }

            return $return ;

        }/* End set level function                                            */

        /**
         * Return the contents of the log file in an array.
         *
         * @param  int $categorisation   (Optional) The message categorisation to be
         *                               selected
         * @return array                 Contains the contents of the log file
         */
        public function fromLog( $categorisation = null ) {
            $this->stopLog(); //lock
            $logList = array() ;

            if ( $categorisation === null )
            $categorisation = SCA_LOGGER_ALL ;

            $logList = $this->_getMsg(null, $this->logfile, $categorisation );

            $this->startLog(); //unlock

            return $logList ;

        }/* End from log function                                             */

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
        public function logStatus() {
            $this->run ? $runState = "Running" : $runState = "Stopped";

            $status    = array() ;
            $status[ 'run'    ] = $runState ;
            $status[ 'dir'    ] = $this->dirpath ;
            $status[ 'file'   ] = $this->file ;
            $status[ 'catgry' ] = $this->_toLevelString($this->catlevel);
            $status[ 'count'  ] = $this->msg_index ;

            return $status ;

        }/* End log status                                                    */

        /**
          * Delete the current log file
          *
          */
        public function deleteLogFile() {
            if ( realpath($this->logfile) ) {
                unlink($this->logfile);
                $this->msg_index = 0 ;

            }/* End file exists                                               */

        }/* End delete log file function                                      */

        /*--------------------------------------------------------------------*/

        /**
         * Enter a message into the log file
         *
         * @param string $msg (Required) message string
         * @param string $level  (Optional) categorisation level of the message
         */
        private function _tolog( $msg , $filename = "", $line = "",  $level  = null ) {

            $this->stopLog() ; //lock
            $stack_depth    = substr('....', 1, 2 ) ;
            $calling_class  = $filename ;
            $calling_method = $line ;

            /* Assumption - a line on is own is no good with out a filename   */
            if ( $filename === ""  ) {
                $backtrace      = debug_backtrace() ;
                $this->_findCallingInfo( $backtrace, $calling_class, $calling_method ) ;
                $stack_depth = substr('........................................',1,count($backtrace)) ;

            }

            ++$this->msg_index ;
            $index  = "[{$this->msg_index}]" ;
            $time   =  date('d/m/Y H:i:s') ;
            $u_secs = gettimeofday() ;
            $m_secs = $u_secs[ 'usec' ]/1000 ;

            $log_msg = sprintf( "[%3d] %s %s::%3d %s%s::%s - %s\n"
            ,  $this->msg_index
            ,  $level
            ,  $time
            ,  $m_secs
            ,  $stack_depth
            ,  $calling_class
            ,  $calling_method
            ,  $msg
            );

            file_put_contents($this->logfile, $log_msg, FILE_APPEND );

            $this->startLog() ; //unlock

        }/* End to log function                                               */

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
        ) {
            $logList = array() ;
            $i       = 0 ;

            /* Ensure that the file exists                                    */
            if ( realpath($from) ) {
                if ( ($loghandle = fopen($from, "rb"))  !== false ) {
                    fflush($loghandle);  // make sure everything is writ.
                    $cat = $this->_isLoggable($categorisation);

                    /* Walk through the file ...                              */
                    while ( !feof($loghandle) ) {
                        $msg = trim(fgets($loghandle));

                        /* Jump out when the there is no message (normally    */
                        /* EOF)                                               */
                        if ( (strlen($msg)) === 0 )
                        break ;

                        /* Save all the messages to an array or just the last */
                        /* one                                                */
                        if ( $command === null ) {
                            /* To save it or not ... that is the question     */
                            if ( $categorisation === SCA_LOGGER_ALL ) {
                                $logList[ $i++ ] = $msg ;

                            } else {
                                if ( (strpos($msg, $cat)) !== false )
                                $logList[ $i++ ] = $msg ;

                            }/* End save it                                   */

                        } else {
                            // ... or just monitor to the last message.
                            $logList[ $i ] = $msg ;

                        }/* End all of the messages                           */

                    }/*End until end of file                                  */

                    fclose($loghandle);

                } else {
                    $logList[ $i ] = " ERROR:: Unable to open the {$this->logfile} file" ;

                }/* End files opened ok                                       */
            } else {
                $logList[ $i ] = "ERROR:: {$this->logfile} file does not exist" ;

            }/* End does the file exist                                       */

            return $logList ;

        }/* End get message function                                          */

        /**
         * Check that the level of log message is recordable.
         *
         * @param int $level  Level of log message
         * @return string     Printable categorisation, or null
         */
        private function _isLoggable( $level ) {
            /* When the level of categorisation is within the categrosation   */
            /* range send the string equivalent back                          */
            $inset = ($level & $this->catlevel) ;
            return ($inset ? $this->_toLevelString( $inset ) : null) ;

        }/* End is loggable function                                          */

        /**
         * Find the calling class and method name
         * NB 'class' is more reliable than 'file', which is sometimes just plain wrong
         *
         * @param int $backtracelngth     size of outer array of backtrace data
         * @param array  $tracedata       backtrace data
         * @param string $class     [i/o] Reference for class name
         * @param string $method    [i/o] Reference for method name
         */
        private function _findCallingInfo( $tracedata, &$class, &$method ) {
            if ( array_key_exists( 'class', $tracedata[ 2 ] ) ) {
                $class = $tracedata[ 2 ][ 'class' ] ;
            } else {
                $temp = str_replace( '\\', '/', $tracedata[ 2 ][ 'file' ] ) ;
                $class =   substr( $temp, (strrpos( $temp, '/' )), (strlen( $temp )) ) ;
            }

            $method = $tracedata[ 2 ][ 'function' ] ;

        }/* End find calling info function                                    */

        /**
         * Find the string value of the defined constant
         *
         * @param int $keyvalue
         * @return string              null if not found
         */
        private function _toLevelString( $keyvalue ) {
            $leveltext   = array(  SCA_LOGGER_CRITICAL => 'Critical'
            ,  SCA_LOGGER_ERROR    => 'Error'
            ,  SCA_LOGGER_WARNING  => 'Warning'
            ,  SCA_LOGGER_INFO     => 'Info'
            ,  SCA_LOGGER_DEBUG    => 'Debug'
            ,  SCA_LOGGER_ALL      => 'AllLevels'
            ,  SCA_LOGGER_NONE     => 'NoLevels'
            ) ;

            if ( key_exists( $keyvalue, $leveltext ) ) {
                return $leveltext[ $keyvalue ] ;
            } else {
                return null ;
            }

        }/* End to level string function                                      */

        /**
         * Find the defined constant value associated with a string
         *
         * @param string $keyvalue
         * @return int                   null if not found
         */
        private function _fromLevelString( $keyvalue ) {
            $textlevel   = array( 'Critical'  => SCA_LOGGER_CRITICAL
            , 'Error'     => SCA_LOGGER_ERROR
            , 'Warning'   => SCA_LOGGER_WARNING
            , 'Info'      => SCA_LOGGER_INFO
            , 'Debug'     => SCA_LOGGER_DEBUG
            , 'AllLevels' => SCA_LOGGER_ALL
            , 'NoLevels'  => SCA_LOGGER_NONE
            ) ;

            if ( key_exists( $keyvalue, $textlevel ) ) {
                return $textlevel[ $keyvalue ] ;
            } else {
                return null ;
            }

        }/* End from level string function                                    */

    }/* End SCA Class                                                         */

}/* End instance check                                                        */

?>
