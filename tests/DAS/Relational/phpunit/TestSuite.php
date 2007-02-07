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
* SDORDASTestSuite.php contains all the SDORDAS tests
*
* Two ways to run it:
*    Command line with phpunit --testdox-text SDORDAS.txt SDO_DAS_Relational_TestSuite TestSuite.php
*    Under ZDE with ZDERunner.php
*
************************************************************************************************************/
if (!defined('PHPUnit_MAIN_METHOD')) {
    define('PHPUnit_MAIN_METHOD', 'SDO_DAS_Relational_TestSuite::main');
}

require_once 'PHPUnit/Framework.php';
require_once 'PHPUnit/TextUI/TestRunner.php';

require_once 'TestRelational.php';
require_once "TestTable.php";
require_once "TestForeignKey.php";
require_once "TestDatabaseModel.php";
require_once "TestContainmentReference.php";
require_once "TestReferencesModel.php";
require_once "TestObjectModel.php";
require_once 'TestDataObjectHelper.php';
require_once 'TestInsertAction.php';
require_once 'TestUpdateAction.php';
require_once 'TestDeleteAction.php';
require_once 'TestPlan.php';


class SDO_DAS_Relational_TestSuite {
    public static function main()
    {
        PHPUnit_TextUI_TestRunner::run(self::suite());
    }
    public static function suite() {
        $suite = new PHPUnit_Framework_TestSuite();
        $suite->addTest(new PHPUnit_Framework_TestSuite("TestRelational"));
        $suite->addTest(new PHPUnit_Framework_TestSuite("TestTable"));
        $suite->addTest(new PHPUnit_Framework_TestSuite("TestForeignKey"));
        $suite->addTest(new PHPUnit_Framework_TestSuite("TestDatabaseModel"));
        $suite->addTest(new PHPUnit_Framework_TestSuite("TestContainmentReference"));
        $suite->addTest(new PHPUnit_Framework_TestSuite("TestReferencesModel"));
        $suite->addTest(new PHPUnit_Framework_TestSuite("TestObjectModel"));
        $suite->addTest(new PHPUnit_Framework_TestSuite("TestDataObjectHelper"));
        $suite->addTest(new PHPUnit_Framework_TestSuite("TestInsertAction"));
        $suite->addTest(new PHPUnit_Framework_TestSuite("TestUpdateAction"));
        $suite->addTest(new PHPUnit_Framework_TestSuite("TestDeleteAction"));
        $suite->addTest(new PHPUnit_Framework_TestSuite("TestPlan"));
        return $suite;
    }

}
if (PHPUnit_MAIN_METHOD == 'SDO_DAS_Relational_TestSuite::main') {
    SDO_DAS_Relational_TestSuite::main();
}

?>
