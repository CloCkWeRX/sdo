<?php 

/* 
+----------------------------------------------------------------------+
| (c) Copyright IBM Corporation 2005.                                  | 
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
*/

// test for the PHPUnit2 PEAR package by trying to include one of its files
$isavailable = include 'PHPUnit2/Framework/TestCase.php';

if ($isavailable == FALSE) 
{
  echo "PHPUnit2 PEAR package was not found\n";
  echo "The SDO unit tests require PHPUnit2 to be present in your\n";
  echo "PHP build. To get this you need pear. The instructions\n";
  echo "for getting pear are here http://pear.php.net/manual/en/faq.pearinhomedir.php\n"; 
  echo "but basically you need to do the following\n";
  echo "1. save the script provided at http://go-pear.org/ as gopear.php\n";
  echo "2. php gopear.php\n";
  echo "   For some reason, to do with the location of php.exe in Debug_TS and the failure\n";
  echo "   of the file selection box the script presents, the script didn't work for me\n";
  echo "   I had to set PHP_PEAR_PHP_BIN=c:\simon\projects\tuscany\php\php-5.1.4\debug_ts\php.exe\n";
  echo "   and then re-run it\n";
  echo "3. pear install Benchmark\n";
  echo "   pear install Log\n";
  echo "   pear install phpunit2 \n";
}

return $isavailable;

?>
