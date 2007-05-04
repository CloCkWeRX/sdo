<?php 
/*
+----------------------------------------------------------------------+
| Copyright IBM Corporation 2006.                                      |
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
require_once "SDO/DAS/Json.php";
require_once "PHPUnit/Framework/TestCase.php";
require_once "PHPUnit/Framework/TestSuite.php";


if (!defined('PHPUnit_MAIN_METHOD')) {
    define('PHPUnit_MAIN_METHOD', 'XMLDASTest::main');
}

// The following globals and the error handler itself are used for catching I/O warnings when XSD or XML files not found
$JsonDASTest_error_handler_called = false;
$JsonDASTest_error_handler_severity;
$JsonDASTest_error_handler_msg;

function JsonDASTest_user_error_handler($severity, $msg, $filename, $linenum) {
    global $JsonDASTest_error_handler_called;
    global $JsonDASTest_error_handler_severity;
    global $JsonDASTest_error_handler_msg;

    $JsonDASTest_error_handler_called = true;
    $JsonDASTest_error_handler_severity = $severity;
    $JsonDASTest_error_handler_msg = $msg;
}


class JsonDASTest extends PHPUnit_Framework_TestCase {

    private $json_portfolio_string      = '{"holding":[{"ticker":"AAPL","number":100.5},{"ticker":"INTL","number":100.5},{"ticker":"IBM","number":100.5}],"otherA":{"x":"XXX","y":123},"otherB":"some string","otherC":123}';
    private $json_mail_string           = '{"id":1,"result":{"jsonemail":[{"address":"my.address@@company.com","message":"My message","reply":"first json email reply"},{"address":"my.address@@company.com","message":"My message","reply":"second json email reply"}],"wsemail":{"address":["my.address@@company.com","some other address"],"message":"My message","reply":"web service email reply"},"localemail":{"address":["my.address@@company.com","some other address"],"message":"My message","reply":"local email reply"}}}';
    private $json_mail_string_generic   = '{"id":1,"result":{"jsonemail":{"jsonemail0":{"address":"my.address@@company.com","message":"My message","reply":"first json email reply"},"jsonemail1":{"address":"my.address@@company.com","message":"My message","reply":"second json email reply"}},"wsemail":{"address":{"address0":"my.address@@company.com","address1":"some other address"},"message":"My message","reply":"web service email reply"},"localemail":{"address":{"address0":"my.address@@company.com","address1":"some other address"},"message":"My message","reply":"local email reply"}}}';
    
    private $namespace_map = array("EmailType"             => "http://www.example.org/email",
                                   "EmailResponseType"     => "http://www.example.org/email",
                                   "EmailResponseListType" => "http://www.example.org/email" );                    

    public static function main()
    {
        require_once "PHPUnit/TextUI/TestRunner.php";
        $suite  = new PHPUnit_Framework_TestSuite("JsonDASTest");
        $result = PHPUnit_TextUI_TestRunner::run($suite);
    }

    public function __construct($name) {
        parent :: __construct($name);
    }

    public function setUp() {
        // is the extension loaded ?
        $loaded = extension_loaded('sdo');
        $this->assertTrue($loaded, 'sdo extension is not loaded.');


        // are we using the matched extension version?
        $version = phpversion('sdo');
        $this->assertTrue(($version >= '0.7.0'), 'Incompatible version of sdo_das_xml extension.');
    }

    public function tearDown() {
    }

    public function testencode() {
        global $JsonDASTest_error_handler_called;
        global $JsonDASTest_error_handler_severity;
        global $JsonDASTest_error_handler_msg;

        set_error_handler('JsonDASTest_user_error_handler');
        $JsonDASTest_error_handler_called = false;
        $exception_thrown = false;
        try {
            $xmldas = SDO_DAS_XML::create();
            $xmldas->addTypes(dirname(__FILE__) . '/Portfolio.xsd');
            
            $xdoc = $xmldas->createDocument("http://www.example.org/Portfolio",
                                            "Portfolio");   
            $portfolio = $xdoc->getRootDataObject(); 
            
            // Test complex type array
            $holding = $portfolio->createDataObject('holding');
            
            // Test simpe types
            $holding->ticker = 'AAPL';
            $holding->number = 100.5;
            $holding = $portfolio->createDataObject('holding');
            $holding->ticker = 'INTL';
            $holding->number = 100.5;
            $holding = $portfolio->createDataObject('holding');
            $holding->ticker = 'IBM';
            $holding->number = 100.5;

            $other = $xmldas->createDataObject("http://www.example.org/Portfolio",
                                               "OtherType");
            $other->x = 'XXX';
            $other->y = 123;
            
            // Test open types
            $portfolio["otherA"] = $other;
            $portfolio["otherB"] = "some string";
            $portfolio["otherC"] = 123;

            $json_das            = new SDO_DAS_Json();
            $json_encoded_string = $json_das->encode($portfolio);          
             
            $this->assertTrue($json_encoded_string == $this->json_portfolio_string,
                              'encoded json string was: ' . $json_encoded_string . "\nbut should have been: " . $this->json_portfolio_string );
                                         
        } catch (Exception $e) {
            $this->assertTrue(false, "Exception was thrown from encode test when it should not have been: ".$e->getMessage());
        }
        $this->assertFalse($JsonDASTest_error_handler_called, 'Error handler should not have been called for encode test. Message was ' . $JsonDASTest_error_handler_msg);
    }
    
    /**
     * Decodes the input JSON string as an SDO with a generic model
     */
    public function testdecodeGeneric() {
        global $JsonDASTest_error_handler_called;
        global $JsonDASTest_error_handler_severity;
        global $JsonDASTest_error_handler_msg;

        set_error_handler('JsonDASTest_user_error_handler');
        $JsonDASTest_error_handler_called = false;
        $exception_thrown = false;
        try {  
            $json_das = new SDO_DAS_Json();
            $sdo      = $json_das->decode($this->json_mail_string);
            
            $json_encoded_string = $json_das->encode($sdo);
             
            $this->assertTrue($json_encoded_string == $this->json_mail_string_generic,
                              'encoded json string was: ' . $json_encoded_string . "\nbut should have been: " . $this->json_mail_string_generic );                     
                                           
        } catch (Exception $e) {
            $this->assertTrue(false, "Exception was thrown from decodeGeneric test when it should not have been: ".$e->getMessage());
        }
        $this->assertFalse($JsonDASTest_error_handler_called, 'Error handler should not have been called for decodeGeneric test. Message was ' . $JsonDASTest_error_handler_msg);
    }

    /**
     * Decodes the input JSON string as an SDO with a generic model
     */    
    public function testdecodeGeneric_WithRootType() {
        global $JsonDASTest_error_handler_called;
        global $JsonDASTest_error_handler_severity;
        global $JsonDASTest_error_handler_msg;

        set_error_handler('JsonDASTest_user_error_handler');
        $JsonDASTest_error_handler_called = false;
        $exception_thrown = false;
        try {  
            $json_das = new SDO_DAS_Json();
            $sdo      = $json_das->decode($this->json_mail_string,"ResponseType");          

            $json_encoded_string = $json_das->encode($sdo);
             
            $this->assertTrue($json_encoded_string == $this->json_mail_string_generic,
                              'encoded json string was: ' . $json_encoded_string . "\nbut should have been: " . $this->json_mail_string_generic );                     
                                           
        } catch (Exception $e) {
            $this->assertTrue(false, "Exception was thrown from decodeGeneric_WithRootType test when it should not have been: ".$e->getMessage());
        }
        $this->assertFalse($JsonDASTest_error_handler_called, 'Error handler should not have been called for decodeGeneric_WithRootType test. Message was ' . $JsonDASTest_error_handler_msg);
    }

    /**
     * Decodes the input JSON string as an SDO with a generic model
     */
    public function testdecodeTypedWithSMD() { 
        global $JsonDASTest_error_handler_called;
        global $JsonDASTest_error_handler_severity;
        global $JsonDASTest_error_handler_msg;

        set_error_handler('JsonDASTest_user_error_handler');
        $JsonDASTest_error_handler_called = false;
        $exception_thrown = false;
        try {  
            $json_das   = new SDO_DAS_Json();
            $smd_string = file_get_contents(dirname(__FILE__) . "/MailApplicationService.smd"); 
            $json_das->addTypesSmdString($smd_string);     
            $smd_string = file_get_contents(dirname(__FILE__) . "/Response.smd");                                  
            $json_das->addTypesSmdString($smd_string);                                
            
            $sdo = $json_das->decode($this->json_mail_string);          

            $json_encoded_string = $json_das->encode($sdo);
             
            $this->assertTrue($json_encoded_string == $this->json_mail_string_generic,
                              'encoded json string was: ' . $json_encoded_string . "\nbut should have been: " . $this->json_mail_string_generic );                     
                                           
        } catch (Exception $e) {
            $this->assertTrue(false, "Exception was thrown from decodeTypedWithSMD test when it should not have been: ".$e->getMessage());
        }
        $this->assertFalse($JsonDASTest_error_handler_called, 'Error handler should not have been called for decodeTypedWithSMD test. Message was ' . $JsonDASTest_error_handler_msg);
    }

    /**
     * Now the DAS is finally given the type to start with it is able to 
     * decodes the input JSON string as an SDO with a typed model
     */    
    public function testdecodeTypedWithSMD_WithRootType() {   
        global $JsonDASTest_error_handler_called;
        global $JsonDASTest_error_handler_severity;
        global $JsonDASTest_error_handler_msg;

        set_error_handler('JsonDASTest_user_error_handler');
        $JsonDASTest_error_handler_called = false;
        $exception_thrown = false;
        try {  
            $json_das   = new SDO_DAS_Json();
            $smd_string = file_get_contents(dirname(__FILE__) . "/MailApplicationService.smd"); 
            $json_das->addTypesSmdString($smd_string); 
            $smd_string = file_get_contents(dirname(__FILE__) . "/Response.smd");                                  
            $json_das->addTypesSmdString($smd_string);            
            
            $sdo = $json_das->decode($this->json_mail_string, "ResponseType");          

            $json_encoded_string = $json_das->encode($sdo);
             
            $this->assertTrue($json_encoded_string == $this->json_mail_string,
                              'encoded json string was: ' . $json_encoded_string . "\nbut should have been: " . $this->json_mail_string );                     
                                           
        } catch (Exception $e) {
            $this->assertTrue(false, "Exception was thrown from decodeTypedWithSMD_WithRootType test when it should not have been: ".$e->getMessage());
        }
        $this->assertFalse($JsonDASTest_error_handler_called, 'Error handler should not have been called for decodeTypedWithSMD_WithRootType test. Message was ' . $JsonDASTest_error_handler_msg);
    }
    
    /**
     * Back to having to rely on the generic model because no 
     * root type is specified
     */
    public function testdecodeTypedWithSMDAndNamespaces() {  
        global $JsonDASTest_error_handler_called;
        global $JsonDASTest_error_handler_severity;
        global $JsonDASTest_error_handler_msg;

        set_error_handler('JsonDASTest_user_error_handler');
        $JsonDASTest_error_handler_called = false;
        $exception_thrown = false;
        try {  
            $json_das   = new SDO_DAS_Json();
            $smd_string = file_get_contents(dirname(__FILE__) . "/MailApplicationService.smd"); 
            $json_das->addTypesSmdString($smd_string, $this->namespace_map);    
            $smd_string = file_get_contents(dirname(__FILE__) . "/Response.smd");                                  
            $json_das->addTypesSmdString($smd_string, $this->namespace_map);                                 
            
            $sdo = $json_das->decode($this->json_mail_string);          

            $json_encoded_string = $json_das->encode($sdo);
             
            $this->assertTrue($json_encoded_string == $this->json_mail_string_generic,
                              'encoded json string was: ' . $json_encoded_string . "\nbut should have been: " . $this->json_mail_string_generic );                     
                                           
        } catch (Exception $e) {
            $this->assertTrue(false, "Exception was thrown from decodeTypedWithSMDAndNamespaces test when it should not have been: ".$e->getMessage());
        }
        $this->assertFalse($JsonDASTest_error_handler_called, 'Error handler should not have been called for decodeTypedWithSMDAndNamespaces test. Message was ' . $JsonDASTest_error_handler_msg);
    }    
    
    public function testdecodeTypedWithSMDAndNamespaces_WithRootType() { 
        global $JsonDASTest_error_handler_called;
        global $JsonDASTest_error_handler_severity;
        global $JsonDASTest_error_handler_msg;

        set_error_handler('JsonDASTest_user_error_handler');
        $JsonDASTest_error_handler_called = false;
        $exception_thrown = false;
        try {  
            $json_das   = new SDO_DAS_Json();
            $smd_string = file_get_contents(dirname(__FILE__) . "/MailApplicationService.smd"); 
            $json_das->addTypesSmdString($smd_string, $this->namespace_map);    
            $smd_string = file_get_contents(dirname(__FILE__) . "/Response.smd");                                  
            $json_das->addTypesSmdString($smd_string, $this->namespace_map);                                 
            
            $sdo = $json_das->decode($this->json_mail_string, "ResponseType");          

            $json_encoded_string = $json_das->encode($sdo);
             
            $this->assertTrue($json_encoded_string == $this->json_mail_string,
                              'encoded json string was: ' . $json_encoded_string . "\nbut should have been: " . $this->json_mail_string );                     
                                           
        } catch (Exception $e) {
            $this->assertTrue(false, "Exception was thrown from decodeTypedWithSMDAndNamespaces_WithRootType test when it should not have been: ".$e->getMessage());
        }
        $this->assertFalse($JsonDASTest_error_handler_called, 'Error handler should not have been called for decodeTypedWithSMDAndNamespaces_WithRootType test. Message was ' . $JsonDASTest_error_handler_msg);
    }    
    
    public function testdecodeTypedWithXSD_WithRootTypeAndNamespace() { 
        global $JsonDASTest_error_handler_called;
        global $JsonDASTest_error_handler_severity;
        global $JsonDASTest_error_handler_msg;

        set_error_handler('JsonDASTest_user_error_handler');
        $JsonDASTest_error_handler_called = false;
        $exception_thrown = false;
        try {  
            $json_das   = new SDO_DAS_Json();
            $json_das->addTypesXsdFile(dirname(__FILE__) . "/Response.xsd");                                 
            
            $sdo = $json_das->decode($this->json_mail_string, "ResponseType", "http://www.example.org/jsonrpc");          

            $json_encoded_string = $json_das->encode($sdo);
             
            $this->assertTrue($json_encoded_string == $this->json_mail_string,
                              'encoded json string was: ' . $json_encoded_string . "\nbut should have been: " . $this->json_mail_string );                     
                                           
        } catch (Exception $e) {
            $this->assertTrue(false, "Exception was thrown from decodeTypedWithXSD_WithRootTypeAndNamespace test when it should not have been: ".$e->getMessage());
        }
        $this->assertFalse($JsonDASTest_error_handler_called, 'Error handler should not have been called for decodeTypedWithXSD_WithRootTypeAndNamespace test. Message was ' . $JsonDASTest_error_handler_msg);
   
    }    
}

if (PHPUnit_MAIN_METHOD == 'JsonDASTest::main') {
    XMLDASTest::main();
}

?>