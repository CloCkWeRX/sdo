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
|         Chris Miller,                                                       |
|         Caroline Maynard,                                                   |
|         Simon Laws                                                          |
+-----------------------------------------------------------------------------+
$Id: SCA_LogFactory.php 254122 2008-03-03 17:56:38Z mfp $
*/

/**
 * Purpose:
 * The SCA_LogFactory class can be used in one of several modes -
 *  1) Load the SCA_Logger using default settings.
 *  2) Allow users to change the SCA_Logger to a different logger ( limited to
 *     the supported loggers below ) without effecting the log entries in SCA.
 *
 * In order to use a different logger, a mapping layer that conforms to the
 * SCA_LogInterface must be created to passthe log commands used in the SCA4PHP
 * Components to the desired logger.
 *
 * For the 'LogFactory to recognize this the following configuration items must
 * be set in 'php.ini -
 *
 *; Enable a different logger to masquerade as the SCA_Logger
 *;   sca.logger : "path/filename.php" of the mapping file ( path can be omitted )
 *;   sca.logger.parameters : ordered comma delimited list (param1,param2,param3)
 *
 * sca.logger=SCA_PHPZero_Map.php
 * sca.logger.parameters=param1,param2,param3
 *
 *  Note : All the loggers are instantiated using the singleton pattern to ensure
 *         multiple instances of the loggers are inhibited.
 *
 * Public Methods:
 * SCA_LogFactory()      - construct the link to the logger.
 * create()              - a static method to provide and instance of the logger.
 *
 * Private Methods:
 * _logger_mode()       - Are the masquerade settings in the configuration file
 * _linklog()           - Invoke the requested class.
 * _findclassname()     - extract the class name from the string.
 * _stringtoarray       - convert a comma delimited string to an array
 */
class SCA_LogFactory
{

    /* Instance of the logger class */
    private  static      $thelogger          = null;

    /* The logger masquerading as the SCA_Logger */
    private  static      $logger             = null;
    private  static      $paramargs          = null;

    /**
     * create an instance of a logger.
     * The default logger is the SCA_Logger, unless the php.ini contains a sca.logger entry
     * in which case this logger instantiated.
     *
     * @return object           Instance of the selected logger
     */
    public static function create()
    {
        self::$logger = self::_loggingmode();

        include_once self::$logger;

        self::$thelogger = self::_linkLog(self::_findclassname(self::$logger));
        return self::$thelogger;

    }

    /**
     * Link to the selected logger
     *
     * @param string $class_name classname to be invoked
     *
     * @return object                   invoked class
     */
    private static function _linkLog($class_name)
    {
        $link = array();

        /* build the correct callback for the selected logger             */
        if ($class_name === 'SCA_Logger') {
            $link = array($class_name, 'singleSCALogger');
        } else {
            $link = array($class_name, 'loadLogger');
        }

        /* link in the logger                                             */
        // Passing in empty array() to suppress warning
        return call_user_func_array($link, array());

    }

    /**
     * Find out which logger to load, and the parameters needed to run it.
     *
     * @return string               The filepath of the logger
     */
    private static function _loggingmode()
    {
        if (false !== ($logger = get_cfg_var('sca.logger'))) {
            return $logger;
        } else {
            return 'SCA/SCA_Logger.php';

        }

        if (false !== ($params = get_cfg_var('sca.logger.parameters'))) {
            self::$paramargs = self::_stringtoarray($params);
        }

    }

    /**
     * Find the classname of the php file.
     *
     * @param string $candidate instance name of the php file
     *
     * @return string candidate class name
     */
    private static function _findclassname($candidate )
    {
        $instance = "";

        //replace any backslash with forward slash
        $line        = str_replace("\\", "/", $candidate);
        $arrayOfLine = explode('/', (trim($line)));
        $bits        = count($arrayOfLine);

        if (($last = strrpos($arrayOfLine[--$bits], '.php')) > 0) {
            $instance = substr($arrayOfLine[$bits], 0, $last);

        }

        return $instance;

    }

    /**
     * Convert a comma delimited parameter string from the configuration file
     * to an array.
     *
     * 'cdstring' can look like -
     *      "parameter1, parameter2, ....., parameterN"
     *      "parameter1,parameter2, .....,parameterN"
     *      "parametervalue"
     *
     * Note : if the parameter string is not comma delimited only a single
     *        value will be returned.
     *
     * @param string $cdstring Comma delim. string
     *
     * @return array
     */
    private static function _stringtoarray($cdstring)
    {
        $token = ",";
        $array = array();

        $parameter = strtok($cdstring, $token);

        for ($i = 0; $parameter !== false; $i++) {
            $parameter = trim($parameter);
            $array[$i] = $parameter;
            $parameter = strtok($token);

        }

        return $array;

    }
}
