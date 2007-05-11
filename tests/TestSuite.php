<?php
/*
+----------------------------------------------------------------------+
| (c) Copyright IBM Corporation 2007.                                  |
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

if (!defined('PHPUnit_MAIN_METHOD')) {
    define('PHPUnit_MAIN_METHOD', 'SCA_SDO_TestSuite::main');
}

require_once 'PHPUnit/Framework.php';
require_once 'PHPUnit/TextUI/TestRunner.php';

require_once 'SDO/phpunit/SDOAPITest.php';
require_once 'DAS/Relational/phpunit/TestSuite.php';
require_once 'DAS/XML/phpunit/XMLDASTest.php';
require_once 'DAS/Json/phpunit/JsonDASTest.php';
require_once 'SCA/phpunit/TestSuite.php';

class SCA_SDO_TestSuite {

    public static function main()
    {
        PHPUnit_TextUI_TestRunner::run(self::suite());
    }

    public static function suite() {
        $suite = new PHPUnit_Framework_TestSuite();
        $suite->setName("SCA_SDOTestSuite");

        $suite->addTest(new PHPUnit_Framework_TestSuite("SDOAPITest"));
        $suite->addTest(new PHPUnit_Framework_TestSuite("XMLDASTest"));
        $suite->addTest(new PHPUnit_Framework_TestSuite("JsonDASTest"));        
        $suite->addTest(SCA_TestSuite::suite());
        $suite->addTest(SDO_DAS_Relational_TestSuite::suite());
        return $suite;
    }

}
if (PHPUnit_MAIN_METHOD == 'SCA_SDO_TestSuite::main') {
    SCA_SDO_TestSuite::main();
}
?>
