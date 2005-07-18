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
| Author: Matthew Peters                                               |
+----------------------------------------------------------------------+

*/

/***********************************************************************************************************
*
* ZDERunner runs the tests in TestSuite.php and makes the output appear in the debug window under ZDE
*
* It is all done because the usual TextUI Runner causes output to go to stdout and this does seem to
* disappear under ZDE. The knack is to define our own ResultPrinter which just echoes the output. 
*
* This code started off looking like the skeleton code in http://www.phpunit.de/en/phpunit2_skeleton.php
* ... plus the calls to create a TestRunner and invoke it from from PHPUnit2_TextUI_TestRunner::run
* ... plus a bit of research to understand how to see how to define a result printer
* ... but its roots are probably now unrecognisable :-)
*
************************************************************************************************************/


/**********************************************************
* Get a TestRunner
* You have to define the PHPUnit2_MAIN_METHOD constant to something - the examples show this being the main
* method for the test suite but we do not need one here. Actually as long as it is not set to NULL it's OK. 
**********************************************************/
define("PHPUnit2_MAIN_METHOD", "anythingwilldo!!!ZDERunner::main");
require_once "PHPUnit2/TextUI/TestRunner.php";
$aTestRunner = new PHPUnit2_TextUI_TestRunner;

/**********************************************************
* Get a result printer and set it on the test runner
/**********************************************************/
require_once "PHPUnit2/TextUI/ResultPrinter.php";
class MYResultPrinter extends PHPUnit2_TextUI_ResultPrinter {
	public function write($buffer) { // override the default implementation which uses fputs to stdout
	echo $buffer;
	}
}
$printer = new MYResultPrinter();
$aTestRunner->setPrinter($printer);

/**********************************************************
* Get our test suite
/**********************************************************/
require_once('TestSuite.php');
$suite = SDO_DAS_Relational_TestSuite::suite();

/**********************************************************
* Run it
/**********************************************************/
$result = $aTestRunner->doRun($suite);

/**
* can call the test runner with extra parameters also:
		$coverageDataFile = FALSE;
		$coverageHTMLFile = FALSE;
		$coverageTextFile = FALSE;
		$testdoxHTMLFile = FALSE;
		$testdoxTextFile = FALSE;
		$xmlLogfile = FALSE;
		$wait = FALSE;

		$result = $aTestRunner->doRun(
		$suite,
		$coverageDataFile,
		$coverageHTMLFile,
		$coverageTextFile,
		$testdoxHTMLFile,
		$testdoxTextFile,
		$xmlLogfile,
		$wait
		);
*/

?>