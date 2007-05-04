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

include_once 'TestService.php';
include_once 'SCA/SCA.php';
include_once 'SCA/Bindings/jsonrpc/SCA_JsonRpcServer.php';
include_once 'SCA/Bindings/jsonrpc/SCA_JsonRpcClient.php';


class SCA_JsonRpcTest extends PHPUnit_Framework_TestCase
{
    private $smd1;
    private $smd2;
    private $request;
    private $response;
    private $email_request;
    private $email_response;

    /**
     * Set up the strings representing the expected test outputs
     */
    public function setUp()
    {
//        $this->smd            = file_get_contents(dirname(__FILE__) . "/TestService.smd");    
        $this->smd1 = '{"SMDVersion":".1","serviceType":"JSON-RPC","serviceURL":"';
        $this->smd2 = '","methods":[{"name":"test","parameters":[{"name":"email","type":"EmailType"}],"return":{"type":"EmailResponseListType"}}],"types":[{"name":"EmailType","typedef":{"properties":[{"name":"address","type":"str"},{"name":"message","type":"str"}]}},{"name":"EmailResponseListType","typedef":{"properties":[{"name":"jsonemail","type":"EmailResponseType []"},{"name":"wsemail","type":"EmailResponseType"},{"name":"localemail","type":"EmailResponseType"}]}},{"name":"EmailResponseType","typedef":{"properties":[{"name":"address","type":"str"},{"name":"message","type":"str"},{"name":"reply","type":"str"}]}}]}';
        $this->request        = file_get_contents(dirname(__FILE__) . "/TestService.request");            
        $this->response       = file_get_contents(dirname(__FILE__) . "/TestService.response");            
        $this->email_request  = file_get_contents(dirname(__FILE__) . "/EmailService.request");            
        $this->email_response = file_get_contents(dirname(__FILE__) . "/EmailService.response");            
        $this->http_header_catcher = new SCA_HttpHeaderCatcher();
        SCA::setHttpHeaderCatcher($this->http_header_catcher);
    }

    /**
     * make it look to the component as if it is receiving  
     * a request for the smd and check the response is correct
     */
    public function testSMDGeneration()
    {
        global $HTTP_RAW_GET_DATA;
    
        $_SERVER['HTTP_HOST'] = 'localhost';
        $_SERVER['REQUEST_METHOD'] = 'GET';
        $_SERVER['SCRIPT_FILENAME'] = 'TestService.php';
        $_SERVER['REQUEST_URI'] = 'http://localhost/TestService.php?smd';
        $_SERVER['CONTENT_TYPE'] = 'application/text';
        $_GET['smd'] = '1';

        ob_start();
        SCA::initComponent("TestService.php");
        $out = ob_get_contents();
        ob_end_clean();

        // test the generated SMD, ignoring the service location in the midle
        $this->assertContains($this->smd1, $out);
        $this->assertContains($this->smd2, $out);
    }
    
    /**
     * make it look to the component as if it is receiving  
     * a request for a method invocation and check the response 
     * is correct
     */    
    public function testWrapperAndProxy()
    {
        $_SERVER['HTTP_HOST'] = 'localhost';
        $_SERVER['REQUEST_METHOD'] = 'POST';
        $_SERVER['SCRIPT_FILENAME'] = 'TestService.php';
        $_SERVER['REQUEST_URI'] = 'http://localhost/TestService.php';
        $_SERVER['CONTENT_TYPE'] = 'application/json-rpc';

        SCA_JsonRpcServer::$test_request = $this->request;
        SCA_JsonRpcClient::$store_test_request = true;
        SCA_JsonRpcClient::$test_response = $this->email_response;
        
        ob_start();
        SCA::initComponent("TestService.php");
        $out = ob_get_contents();
        ob_end_clean();
       
        $this->assertContains($this->response, $out);
        $this->assertContains(SCA_JsonRpcClient::$test_request, $this->email_request);
    }
      

    public static function main()
    {
        require_once "PHPUnit/TextUI/TestRunner.php";

        $suite  = new PHPUnit_Framework_TestSuite("SCA_JsonRpcTest");
        $result = PHPUnit_TextUI_TestRunner::run($suite);
    }

}

// Call SCA_JsonRpcTest::main() if this source file is executed directly.
if (!defined("PHPUnit_MAIN_METHOD")) {
    define("PHPUnit_MAIN_METHOD", "SCA_JsonRpcTest::main");
    SCA_JsonRpcTest::main();
}

?>
