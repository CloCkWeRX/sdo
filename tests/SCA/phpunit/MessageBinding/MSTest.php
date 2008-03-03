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
| Author: Wangkai Zai                                                  |
+----------------------------------------------------------------------+

*/

require_once "PHPUnit/Framework/TestCase.php";
require_once "PHPUnit/Framework/TestSuite.php";

include_once 'SCA/SCA.php';
include_once 'SCA/Bindings/message/SAM_Client.php';
include_once 'MS_TestService.php';
include_once 'MS_TestServiceSDO.php';


class SCA_MessageBindingTest extends PHPUnit_Framework_TestCase
{
    private $mpd;
    private $msg1;
    private $msg2;


    /**
     * Set up the strings representing the expected test outputs
     */
    public function setUp()
    {
        $this->mpd  = file_get_contents(dirname(__FILE__) . "/TestServiceCorrectMPD");
        SCA_Bindings_message_SAMClient::$test_mode = true ;

        /*preparing test messages for testOperationSelection
             msg1 has 'scaOperationName' property, and = 'hello'
                   wrapper should invoke service's operation hello
        */
        $this->msg1 = new SAMMessage("test message 1");
        $this->msg1->header->SAM_TYPE = SAM_TEXT;
        $this->msg1->header->SAM_MESSAGEID = "id-test0001";
        $this->msg1->header->scaOperationName = "hello";
        /*
             msg2 does have 'scaOperationName' property
                   wrapper should invoke 'onMessage' by default
        */
        $this->msg2 = new SAMMessage("test message 2");
        $this->msg2->header->SAM_TYPE = SAM_TEXT;
        $this->msg2->header->SAM_MESSAGEID = "id-test0002";

        /*copy names.xsd to current directory*/
        copy(dirname(__FILE__)."/names_xsd","names.xsd");

        
    }

    public function tearDown()
    {
        if (file_exists('./MS_TestService.msd')) {
            unlink('./MS_TestService.msd');
        }
        if (file_exists('./MS_TestServiceSDO.msd')) {
            unlink('./MS_TestServiceSDO.msd');
        }
        if (file_exists('./MS_TestServiceSDO.wsdl')) {
            unlink('./MS_TestServiceSDO.wsdl');
        }
        unlink("./names.xsd");
 
    }

    public function testMPDGeneration()
    {
        //global $HTTP_RAW_GET_DATA;
    
        $_SERVER['HTTP_HOST'] = 'localhost';
        $_SERVER['REQUEST_METHOD'] = 'GET';
        $_SERVER['SCRIPT_FILENAME'] = 'MS_TestService.php';
        $_SERVER['REQUEST_URI'] = 'http://localhost/MS_TestService.php?msd';
        $_SERVER['CONTENT_TYPE'] = 'application/text';
        $_GET['msd'] = '1';

        ob_start();
        SCA::initComponent("MS_TestService.php");
        $out = ob_get_contents();
        ob_end_clean();
        // test the generated MPD
        $this->assertContains($this->mpd, $out);
    }

    public function testOperationSelection(){
        unset($_SERVER['REQUEST_METHOD']);
        
        ob_start();
        SCA_Bindings_message_SAMClient::$test_queueborker['queue://MS_TestService'] = $this->msg1;
        SCA::initComponent("MS_TestService.php");
        $out1 = ob_get_contents();
        ob_end_clean();

        ob_start();
        SCA_Bindings_message_SAMClient::$test_queueborker['queue://MS_TestService'] = $this->msg2;
        SCA::initComponent("MS_TestService.php");
        $out2 = ob_get_contents();
        ob_end_clean();
  

        $this->assertContains('hello', $out1);
        $this->assertContains('onMessage', $out2);
    }
    

    public function testWrapperAndProxy()
    {
        $binding_config = array('wsdl'=> dirname(__FILE__).'/MS_TestServiceSDO_wsdl',
                                'replyto' => 'queue://testResponse');
        $ms_service = SCA::getService(dirname(__FILE__).'/MS_TestServiceSDO_msd',"message",$binding_config);

        $namesSDO = $ms_service->createDataObject('http://example.org/names', 'people');
        // Populate the names
        $namesSDO->name[]='Cathy';
        $namesSDO->name[]='Bertie';
        $namesSDO->name[]='Fred';

        /*proxy send request*/
        $ms_service->setWaitResponseTimeout(-1);
        $ms_service->greetEveryone('hello',$namesSDO);

        /*warpper get request, process it, and send response */
        ob_start();
        SCA::initComponent("MS_TestServiceSDO.php");
        ob_end_clean();

        /*proxy get response*/
        $ms_service->setWaitResponseTimeout(0);
        $sdo = $ms_service->greetEveryone('hello',$namesSDO);

        $this->assertEquals('people',$sdo->getTypename());
        $this->assertEquals(3,sizeof($sdo->name));

    }
      

    public static function main()
    {
        require_once "PHPUnit/TextUI/TestRunner.php";

        $suite  = new PHPUnit_Framework_TestSuite("SCA_JsonRpcTest");
        $result = PHPUnit_TextUI_TestRunner::run($suite);
    }

}

// Call SCA_MSTest::main() if this source file is executed directly.
if (!defined("PHPUnit_MAIN_METHOD")) {
    define("PHPUnit_MAIN_METHOD", "SCA_MSTest::main");
    SCA_MSTest::main();
}

?>
