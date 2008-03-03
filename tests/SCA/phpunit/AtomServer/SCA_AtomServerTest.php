<?php

require_once "PHPUnit/Framework/TestCase.php";
require_once "PHPUnit/Framework/TestSuite.php";
require "SCA/Bindings/atom/SCA_AtomServer.php";
require "SCA/Bindings/atom/SCA_ServiceWrapperAtom.php";
require "SCA/SCA.php";
require "SCA/SCA_HttpHeaderCatcher.php";



//require_once 'SCA/SCA_AnnotationRules.php';
//require_once 'SCA/SCA.php';
//require_once 'SCA/Bindings/WS/ServiceDescriptionGenerator.php';
//require_once 'SCA/Bindings/WS/Proxy.php';

class SCA_AtomServerTest extends PHPUnit_Framework_TestCase {

    private $http_header_catcher;
    public function setUp()
    {

        $this->http_header_catcher = new SCA_HttpHeaderCatcher();
        SCA::setHttpHeaderCatcher($this->http_header_catcher);

    }

    //delete the created files.
    public function tearDown()
    {
        //Not needed at the moment
    }

    public function testCallingCreateOnComponentWithNoCreateMethod()
    {

        //POST requests are translated to 'create() calls'
        $_SERVER['REQUEST_METHOD'] = 'POST';

        $file_name = dirname(__FILE__).'/ComponentNoCreateMethod.php';
        include $file_name;
        $class_name = SCA_Helper::guessClassName($file_name);
        //        $instance = new $class_name;

        //        $sw = new SCA_ServiceWrapperAtom($instance, $class_name, null);
        $sw = new SCA_ServiceWrapperAtom($class_name);

        $as = new SCA_AtomServer($sw);

        $input = dirname(__FILE__).'/AtomInputFile.txt';

        $as->setInputStream($input);

        $as->handle();

        $this->assertContains("HTTP/1.1 405 Method Not Allowed",$this->http_header_catcher->headers);

    }

    public function testCreateNoXmlInRequestBody()
    {

        $_SERVER['REQUEST_METHOD'] = 'POST';

        //set up the service wrapper and atom server
        $file_name = dirname(__FILE__).'/ComponentCreate.php';
        include $file_name;
        $class_name = SCA_Helper::guessClassName($file_name);
        $sw = new SCA_ServiceWrapperAtom($class_name);

        $as = new SCA_AtomServer($sw);

        $input = dirname(__FILE__).'/AtomInputFile.txt';

        $as->setInputStream($input);

        ob_start();
        //TODO - should capture and test the body coming back
        $out = $as->handle();
        $ob = ob_get_contents();
        ob_end_clean();

        $this->assertContains("HTTP/1.1 400 Bad Request",$this->http_header_catcher->headers);
        //$this->assertContains("correct body contents",$ob;
    }

    public function testCreateValidNonAtomXmlInRequestBody()
    {

        $_SERVER['REQUEST_METHOD'] = 'POST';

        //set up the service wrapper and atom server
        $file_name = dirname(__FILE__).'/ComponentCreate2.php';
        include $file_name;
        $class_name = SCA_Helper::guessClassName($file_name);
        $class_name = SCA_Helper::guessClassName($file_name);
        $sw = new SCA_ServiceWrapperAtom($class_name);

        $as = new SCA_AtomServer($sw);

        $input = dirname(__FILE__).'/AtomInputFile2.txt';

        $as->setInputStream($input);

        ob_start();

        //TODO - should capture and test the body coming back
        $out = $as->handle();       //capture what is returned
        $ob = ob_get_contents();    //capture whatever is echoed from AtomServer

        ob_end_clean();

        $this->assertContains("HTTP/1.1 400 Bad Request",$this->http_header_catcher->headers);
        //$this->assertContains("correct body contents",$ob;


    }

    public function testCreateValidAtomXmlInButIncorrectResponse()
    {

        $_SERVER['REQUEST_METHOD'] = 'POST';

        //set up the service wrapper and atom server
        $file_name = dirname(__FILE__).'/ComponentCreate3.php';
        include $file_name;
        $class_name = SCA_Helper::guessClassName($file_name);
        $sw = new SCA_ServiceWrapperAtom($class_name);

        $as = new SCA_AtomServer($sw);

        $input = dirname(__FILE__).'/AtomInputFile3.txt';

        $as->setInputStream($input);

        ob_start();

        //TODO - should capture and test the body coming back
        $out = $as->handle();
        $ob = ob_get_contents();
        ob_end_clean();

        $this->assertContains("HTTP/1.1 500 Internal Server Error",$this->http_header_catcher->headers);
        //$this->assertContains("correct body contents",$ob;
    }

    public function testCreateSimpleRequestAndResponse()
    {

        $_SERVER['REQUEST_METHOD'] = 'POST';

        //set up the service wrapper and atom server
        $file_name = dirname(__FILE__).'/ComponentCreateResponse.php';
        include $file_name;
        $class_name = SCA_Helper::guessClassName($file_name);
        $sw = new SCA_ServiceWrapperAtom($class_name);

        $as = new SCA_AtomServer($sw);

        $input = dirname(__FILE__).'/AtomInputFile3.txt';

        $as->setInputStream($input);


        //TODO - should capture and test the body coming back
        ob_start();
        $out = $as->handle();
        $ob = ob_get_contents();
        ob_end_clean();

        //var_dump($this->http_header_catcher->headers);

        $this->assertContains("HTTP/1.1 201 Created",$this->http_header_catcher->headers);
        $this->assertContains("Location:http://www.guardian.co.uk/worldlatest/story/0,,-6490291,00.html",$this->http_header_catcher->headers);
        //$this->assertContains("correct body contents",$ob;
    }

    public function testCreateLinkFormat()
    {

        $_SERVER['REQUEST_METHOD'] = 'POST';

        //set up the service wrapper and atom server
        $file_name = dirname(__FILE__).'/ComponentCreateLinkFormat.php';
        include $file_name;
        $class_name = SCA_Helper::guessClassName($file_name);
        $sw = new SCA_ServiceWrapperAtom($class_name);

        $as = new SCA_AtomServer($sw);

        $input = dirname(__FILE__).'/AtomInputFile3.txt';

        $as->setInputStream($input);


        //TODO - should capture and test the body coming back
        ob_start();
        $out = $as->handle();
        $ob = ob_get_contents();
        ob_end_clean();

        //var_dump($this->http_header_catcher->headers);

        $this->assertContains("HTTP/1.1 201 Created",$this->http_header_catcher->headers);
        $this->assertContains("Location:http://localhost:1112/MegTest3.php/1",$this->http_header_catcher->headers);
        //$this->assertContains("correct body contents",$ob;
    }

    public function testEnumerateRequestAndResponse()
    {
        //GET requests with no PATH_INFO are translated to 'enumerate()' calls
        $_SERVER['REQUEST_METHOD'] = 'GET';

        //set up the service wrapper and atom server
        $file_name = dirname(__FILE__).'/ComponentEnumerateResponse.php';
        include $file_name;
        $class_name = SCA_Helper::guessClassName($file_name);
        $sw = new SCA_ServiceWrapperAtom($class_name);

        $as = new SCA_AtomServer($sw);

        $input = dirname(__FILE__).'/EmptyRequestBody.txt';

        $as->setInputStream($input);

        //TODO - should capture and test the body coming back#
        ob_start();
        $out = $as->handle();
        $ob = ob_get_contents();
        ob_end_clean();

        //response headers should be like retrieve()
        $this->assertContains("HTTP/1.1 200 OK",$this->http_header_catcher->headers);
        $this->assertContains("Content-Type: application/atom+xml",$this->http_header_catcher->headers);
        //$this->assertContains("correct body contents",$ob;
    }

    public function testEnumerateFeedFormat()
    {
        //GET requests with no PATH_INFO are translated to 'enumerate()' calls
        $_SERVER['REQUEST_METHOD'] = 'GET';

        //set up the service wrapper and atom server
        $file_name = dirname(__FILE__).'/ComponentEnumerateFeedFormat.php';
        include $file_name;
        $class_name = SCA_Helper::guessClassName($file_name);
        $sw = new SCA_ServiceWrapperAtom($class_name);

        $as = new SCA_AtomServer($sw);

        $input = dirname(__FILE__).'/EmptyRequestBody.txt';

        $as->setInputStream($input);

        //TODO - should capture and test the body coming back#
        ob_start();
        $out = $as->handle();
        $ob = ob_get_contents();
        ob_end_clean();

        //response headers should be like retrieve()
        $this->assertContains("HTTP/1.1 200 OK",$this->http_header_catcher->headers);
        $this->assertContains("Content-Type: application/atom+xml",$this->http_header_catcher->headers);
        //$this->assertContains("correct body contents",$ob;
    }

    public function testRetrieveRequestAndResponse()
    {
        //GET requests are translated to 'retrieve()' calls when PATH_INFO is set
        $_SERVER['REQUEST_METHOD'] = 'GET';
        $_SERVER['PATH_INFO'] = '/TEST_ID';

        //set up the service wrapper and atom server
        $file_name = dirname(__FILE__).'/ComponentRetrieveResponse.php';
        include $file_name;
        $class_name = SCA_Helper::guessClassName($file_name);
        $sw = new SCA_ServiceWrapperAtom($class_name);

        $as = new SCA_AtomServer($sw);

        $input = dirname(__FILE__).'/EmptyRequestBody.txt';

        $as->setInputStream($input);

        //TODO Expect a body back  - capture it - test it
        ob_start();
        $out = $as->handle();
        $ob = ob_get_contents();
        ob_end_clean();

        //$this->assertContains("correct body contents",$ob;
        $this->assertContains("HTTP/1.1 200 OK",$this->http_header_catcher->headers);
        $this->assertContains("Content-Type: application/atom+xml",$this->http_header_catcher->headers);

    }

    public function testUpdateRequestAndEmptyResponse()
    {
        //PUT requests are translated to 'update()' calls
        $_SERVER['REQUEST_METHOD'] = 'PUT';
        $_SERVER['PATH_INFO'] = 'TEST_ID';


        //set up the service wrapper and atom server
        $file_name = dirname(__FILE__).'/ComponentUpdateEmptyResponse.php';
        include $file_name;
        $class_name = SCA_Helper::guessClassName($file_name);
        $sw = new SCA_ServiceWrapperAtom($class_name);

        $as = new SCA_AtomServer($sw);

        $input = dirname(__FILE__).'/AtomInputFile3.txt';

        $as->setInputStream($input);

        //Not expecting a body
        $as->handle();

        $this->assertContains("HTTP/1.1 500 Internal Server Error",$this->http_header_catcher->headers);

    }

    public function testUpdateRequestAndSimpleReturnResponse()
    {
        //PUT requests are translated to 'update()' calls
        $_SERVER['REQUEST_METHOD'] = 'PUT';
        $_SERVER['PATH_INFO'] = 'TEST_ID';


        //set up the service wrapper and atom server
        $file_name = dirname(__FILE__).'/ComponentUpdateSimpleReturnResponse.php';
        include $file_name;
        $class_name = SCA_Helper::guessClassName($file_name);
        $sw = new SCA_ServiceWrapperAtom($class_name);

        $as = new SCA_AtomServer($sw);

        $input = dirname(__FILE__).'/AtomInputFile3.txt';

        $as->setInputStream($input);

        //Not expecting a body
        $as->handle();

        $this->assertContains("HTTP/1.1 500 Internal Server Error",$this->http_header_catcher->headers);



    }

    public function testUpdateRequestAndTrueResponse()
    {

        $_SERVER['REQUEST_METHOD'] = 'PUT';
        $_SERVER['PATH_INFO'] = 'TEST_ID';


        //set up the service wrapper and atom server
        $file_name = dirname(__FILE__).'/ComponentUpdateReturnTrueResponse.php';
        include $file_name;
        $class_name = SCA_Helper::guessClassName($file_name);
        $sw = new SCA_ServiceWrapperAtom($class_name);

        $as = new SCA_AtomServer($sw);

        $input = dirname(__FILE__).'/AtomInputFile3.txt';

        $as->setInputStream($input);

        //Not expecting a body
        $as->handle();

        $this->assertContains("HTTP/1.1 200 OK",$this->http_header_catcher->headers);
    }

    public function testDeleteRequestAndResponse()
    {
        //DELETE requests are translated to 'delete()' calls
        $_SERVER['REQUEST_METHOD'] = 'DELETE';
        $_SERVER['PATH_INFO'] = 'TEST_ID';


        //set up the service wrapper and atom server
        $file_name = dirname(__FILE__).'/ComponentDeleteReturnTrueResponse.php';
        include $file_name;
        $class_name = SCA_Helper::guessClassName($file_name);
        $sw = new SCA_ServiceWrapperAtom($class_name);

        $as = new SCA_AtomServer($sw);

        $input = dirname(__FILE__).'/AtomInputFile3.txt';

        $as->setInputStream($input);

        //Not expecting a body
        $as->handle();

        $this->assertContains("HTTP/1.1 200 OK",$this->http_header_catcher->headers);



    }




    public static function main()
    {
        require_once "PHPUnit/TextUI/TestRunner.php";

        $suite  = new PHPUnit_Framework_TestSuite("SCA_AtomServerTest");
        $result = PHPUnit_TextUI_TestRunner::run($suite);
    }

}

// Call SCA_AnnotationRulesTest::main() if this source file is executed directly.
if (!defined("PHPUnit_MAIN_METHOD")) {
    define("PHPUnit_MAIN_METHOD", "SCA_AtomServerTest::main");
    SCA_AtomServerTest::main();
}

?>
