<?php
/*
+----------------------------------------------------------------------+
| Copyright IBM Corporation 2007.                                      |
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
$Id: RestRpcTest.php 241789 2007-08-24 15:20:26Z mfp $
*/

require_once "PHPUnit/Framework/TestCase.php";
require_once "PHPUnit/Framework/TestSuite.php";

include_once 'RestRpcTestService.php';
include_once 'SCA/SCA.php';


class SCA_RestRpcTest extends PHPUnit_Framework_TestCase
{
    private $response;

    /**
     * Set up the strings representing the expected test outputs
     */
    public function setUp()
    {
        $this->response = file_get_contents(dirname(__FILE__) . "/RestRpcTestService.response");
        $this->http_header_catcher = new SCA_HttpHeaderCatcher();
        SCA::setHttpHeaderCatcher($this->http_header_catcher);

    }

    /**
     * make it look to the component as if it is receiving  
     * a POST request for a method invocation and check the response 
     * is correct
     */    
    public function testPOSTForm()
    {
        $_SERVER['HTTP_HOST'] = 'localhost';
        $_SERVER['REQUEST_METHOD'] = 'POST';
        $_SERVER['SCRIPT_FILENAME'] = 'RestRpcTestService.php';
        $_SERVER['REQUEST_URI'] = 'http://localhost/TestService.php';
        $_SERVER['PATH_INFO'] = ' hello';
        $_SERVER['CONTENT_TYPE'] = 'application/x-www-form-urlencoded';
        $_POST = array("name", "Bill");

        ob_start();
        SCA::initComponent("RestRpcTestService.php");
        $out = ob_get_contents();
        ob_end_clean();

        $string1 = str_replace("\r", "", $out);
        $string2 = str_replace("\r", "", $this->response);

        $this->assertEquals($string1, $string2);
    }

    /**
     * make it look to the component as if it is receiving  
     * a GET request for a method invocation and check the response 
     * is correct
     */    
    public function testGETForm()
    {
        $_SERVER['HTTP_HOST'] = 'localhost';
        $_SERVER['REQUEST_METHOD'] = 'GET';
        $_SERVER['SCRIPT_FILENAME'] = 'RestRpcTestService.php';
        $_SERVER['REQUEST_URI'] = 'http://localhost/TestService.php?name=Bill';
        $_SERVER['PATH_INFO'] = ' hello';
        $_SERVER['CONTENT_TYPE'] = 'application/test';
        $_GET = array("name", "Bill");

        ob_start();
        SCA::initComponent("RestRpcTestService.php");
        $out = ob_get_contents();
        ob_end_clean();

        $string1 = str_replace("\r", "", $out);
        $string2 = str_replace("\r", "", $this->response);

        $this->assertEquals($string1, $string2);
    }


    public static function main()
    {
        require_once "PHPUnit/TextUI/TestRunner.php";

        $suite  = new PHPUnit_Framework_TestSuite("SCA_RestRpcTest");
        $result = PHPUnit_TextUI_TestRunner::run($suite);
    }

}

if (!defined("PHPUnit_MAIN_METHOD")) {
    define("PHPUnit_MAIN_METHOD", "SCA_RestRpcTest::main");
    SCA_RestRpcTest::main();
}

?>
