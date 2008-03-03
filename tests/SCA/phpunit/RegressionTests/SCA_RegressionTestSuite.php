<?php
/*
+----------------------------------------------------------------------+
| (c) Copyright IBM Corporation 2005, 2007.                            |
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
| Author: Matthew Peters                                               |
+----------------------------------------------------------------------+

*/
/***********************************************************************************************************
*
*
************************************************************************************************************/

if (!defined('PHPUnit_MAIN_METHOD')) {
    define('PHPUnit_MAIN_METHOD', 'SCA_RegressionTestSuite::main');
}

require_once 'PHPUnit/Framework.php';
require_once 'PHPUnit/TextUI/TestRunner.php';

//require_once 'Tuscany1566Test.php';
require_once 'Bug11774Test.php';
require_once 'Bug12193Test.php';

class SCA_RegressionTestSuite {

    public static function main()
    {
        PHPUnit_TextUI_TestRunner::run(self::suite());
    }

    public static function suite() {
        $suite = new PHPUnit_Framework_TestSuite();
//        $suite->addTest(new PHPUnit_Framework_TestSuite("Tuscany1566Test"));
        $suite->addTest(new PHPUnit_Framework_TestSuite("Bug11774Test"));
        $suite->addTest(new PHPUnit_Framework_TestSuite("Bug12193Test"));

        return $suite;
    }

}
if (PHPUnit_MAIN_METHOD == 'SCA_RegressionTestSuite::main') {
    SCA_RegressionTestSuite::main();
}
?>
