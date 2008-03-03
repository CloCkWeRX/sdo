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
$Id$
*/

require_once "PHPUnit/Framework/TestCase.php";
require_once "PHPUnit/Framework/TestSuite.php";

include_once 'Orders.php';
include_once 'SCA/SCA.php';


class SCA_RestResourceTest extends PHPUnit_Framework_TestCase
{
    private $enumerate_response;
    private $retrieve_response;

    /**
     * Set up the strings representing the expected test outputs
     */
    public function setUp()
    {
        $this->enumerate_response = file_get_contents(dirname(__FILE__) . "/enumerate.response");
        $this->retrieve_response = file_get_contents(dirname(__FILE__) . "/retrieve.response");        
        $this->http_header_catcher = new SCA_HttpHeaderCatcher();
        SCA::setHttpHeaderCatcher($this->http_header_catcher);

    }

    /**
     * make it look to the component as if it is receiving  
     * a GET request for URL with no resourceId
     */    
    public function testEnumerate()
    {
        $_SERVER['HTTP_HOST'] = 'localhost';
        $_SERVER['REQUEST_METHOD'] = 'GET';
        $_SERVER['SCRIPT_FILENAME'] = 'Orders.php';
        $_SERVER['REQUEST_URI'] = 'http://localhost/Orders.php';
        $_SERVER['CONTENT_TYPE'] = 'test/plain';

        ob_start();
        SCA::initComponent("Orders.php");
        $out = ob_get_contents();
        ob_end_clean();

        $string1 = str_replace("\r", "", $out);
        $string2 = str_replace("\r", "", $this->enumerate_response);
       

        $this->assertEquals($string1, $string2);
    }

    /**
     * make it look to the component as if it is receiving  
     * a GET request for a URL with a resourceId
     */    
    public function testRetrieve()
    {
        $_SERVER['HTTP_HOST'] = 'localhost';
        $_SERVER['REQUEST_METHOD'] = 'GET';
        $_SERVER['SCRIPT_FILENAME'] = 'Orders.php';
        $_SERVER['REQUEST_URI'] = 'http://localhost/Orders.php/order1';
        $_SERVER['CONTENT_TYPE'] = 'test/plain';
        $_SERVER['PATH_INFO'] = '/order1';


        ob_start();
        SCA::initComponent("Orders.php");
        $out = ob_get_contents();
        ob_end_clean();

        $string1 = str_replace("\r", "", $out);
        $string2 = str_replace("\r", "", $this->retrieve_response);
        $this->assertEquals($string1, $string2);
    }


    public static function main()
    {
        require_once "PHPUnit/TextUI/TestRunner.php";

        $suite  = new PHPUnit_Framework_TestSuite("SCA_RestResourceTest");
        $result = PHPUnit_TextUI_TestRunner::run($suite);
    }

}

if (!defined("PHPUnit_MAIN_METHOD")) {
    define("PHPUnit_MAIN_METHOD", "SCA_RestResourceTest::main");
    SCA_RestResourceTest::main();
}

?>
