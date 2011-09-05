<?php

/*
+----------------------------------------------------------------------+
| (c) Copyright IBM Corporation 2005,2007.                             |
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
| Author: SL                                                           |
+----------------------------------------------------------------------+
$Id$
*/

// test for the PHPUnit PEAR package by trying to include one of its files
$isavailable = include 'PHPUnit/Framework/TestCase.php';

if ($isavailable == FALSE)
{
  echo "PHPUnit PEAR package was not found\n";
  echo "The various unit tests require PHPUnit 3.0 to be present in your\n";
  echo "PHP build. PHPUnit 3.0 is available from http://www.phpunit.de/\n";
  echo "You will probably use pear to install it, and the chances are that\n" ;
  echo "you already have pear installed, but to be on the safe side, here are \n";
  echo "complete instructions:\n";
  echo "The instructions for getting pear are here:\n";
  echo "http://pear.php.net/manual/en/faq.pearinhomedir.php\n";
  echo "but basically you need to do the following\n";
  echo "1. save the script provided at http://go-pear.org/ as gopear.php\n";
  echo "2. php gopear.php\n";
  echo "   For some reason, to do with the location of php.exe in Debug_TS and the failure\n";
  echo "   of the file selection box the script presents, the script didn't work for me\n";
  echo "   I had to set PHP_PEAR_PHP_BIN=c:\simon\projects\tuscany\php\php-5.1.4\debug_ts\php.exe\n";
  echo "   and then re-run it\n";
  echo "3. pear install Benchmark\n";
  echo "   pear install Log\n";
  echo "4. pear channel-discover pear.phpunit.de \n";
  echo "   pear install phpunit/PHPUnit \n";
}

return $isavailable;

?>
