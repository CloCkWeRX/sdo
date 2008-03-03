<?php

require_once "PHPUnit/Framework/TestCase.php";
require_once "PHPUnit/Framework/TestSuite.php";

require_once 'SCA/SCA.php';
require_once 'SCA/Bindings/soap/ServiceRequestHandler.php';
require_once 'SCA/Bindings/soap/ServiceDescriptionGenerator.php';


function error_handler($errno, $errstr) {
    // NOTE
    // we sometimes establish a temporary error handler, as the
    // the SoapServer calls header() and as a consequence we get the message
    // "Cannot modify header information - headers already sent"
    // this splats all over the phpunit output if we do not catch it and throw it away
    //    echo "hello $errno $errstr";
}

class SCA_Bindings_soap_HandlerTest extends PHPUnit_Framework_TestCase {

    public function setUp()
    {
        if ( ! class_exists('SCA_Bindings_soap_Proxy')) {
            $this->markTestSkipped("Cannot execute any SCA soap tests as the SCA soap binding is not loaded");
            return;
        }

        $php = <<<PHP
<?php

require "SCA/SCA.php";

/**
 * @service
 * @binding.soap
 */

class SoapHandlerTestComponent {

	/**
	 * Reverse a string
	 *
	 * @param string \$in (comment)
	 * @return string (comment)
	 */
	function reverse(\$in)
	{
		return strrev (\$in);
	}
}

?>
PHP;
        file_put_contents(dirname(__FILE__) . '/SoapHandlerTestComponent.php',$php);
        $service_description = SCA::constructServiceDescription(dirname(__FILE__) . '/SoapHandlerTestComponent.php');

        $wsdl = SCA_Bindings_soap_ServiceDescriptionGenerator::generateDocumentLiteralWrappedWsdl($service_description);
        file_put_contents(dirname(__FILE__) . '/SoapHandlerTestComponent.wsdl',$wsdl);

}

public function tearDown()
{
    $http_header_catcher = new SCA_HttpHeaderCatcher();
    SCA::setHttpHeaderCatcher($http_header_catcher);
    unlink(dirname(__FILE__) . '/SoapHandlerTestComponent.php');
    unlink(dirname(__FILE__) . '/SoapHandlerTestComponent.wsdl');
}


// TODO
// would like to do a test like:
// public function testSoapHandlerProcessesMissingMethodCorrectly()
// but do not know how to capture the SoapFault that gets emitted.
// It does not get captured by ob_start.

public function testSoapHandlerProcessesCorrectCallCorrectly()
{
    global $HTTP_RAW_POST_DATA;
    // make it look to the component as if it is on the receiving end of a SOAP request
    $_SERVER['HTTP_HOST'] = 'localhost';
    $_SERVER['REQUEST_METHOD'] = 'POST';
    $_SERVER['SCRIPT_FILENAME'] = dirname(__FILE__) . '/SoapHandlerTestComponent.php';
    $_SERVER['CONTENT_TYPE'] = 'application/soap+xml';

    $HTTP_RAW_POST_DATA = <<<EOF
<?xml version="1.0" encoding="UTF-8"?>
<SOAP-ENV:Envelope xmlns:SOAP-ENV="http://schemas.xmlsoap.org/soap/envelope/" 
                   xmlns:ns1="http://SoapHandlerTestComponent">
  <SOAP-ENV:Body>
    <ns1:reverse xmlns="http://SoapHandlerTestComponent" 
                 xmlns:tns="http://SoapHandlerTestComponent" 
                 xmlns:xsi="http://www.w3.org/2001/XMLSchema-instance" 
                 xsi:type="reverse">
      <in>IBM</in>
    </ns1:reverse>
  </SOAP-ENV:Body>
</SOAP-ENV:Envelope>


EOF;

    $handler = new SCA_Bindings_soap_ServiceRequestHandler();

    $service_description = SCA::constructServiceDescription(dirname(__FILE__) . '/SoapHandlerTestComponent.php');

    ob_start();

    set_error_handler('error_handler',E_WARNING);
    $handler->handle(dirname(__FILE__) . '/SoapHandlerTestComponent.php', $service_description);
    restore_error_handler();
    $out = ob_get_contents();
    ob_end_clean();

    $this->assertContains('<tns2:reverseReturn>MBI</tns2:reverseReturn>',$out);

}

public static function main()
{
    require_once "PHPUnit/TextUI/TestRunner.php";

    $suite  = new PHPUnit_Framework_TestSuite("SCA_Bindings_soap_HandlerTest");
    $result = PHPUnit_TextUI_TestRunner::run($suite);
}

}

// Call SCA_AnnotationRulesTest::main() if this source file is executed directly.
if (!defined("PHPUnit_MAIN_METHOD")) {
    define("PHPUnit_MAIN_METHOD", "SCA_Bindings_soap_HandlerTest::main");
    SCA_Bindings_soap_HandlerTest::main();
}

?>