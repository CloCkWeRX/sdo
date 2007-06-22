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
| Author: Graham Charters                                              |
+----------------------------------------------------------------------+
$Id$
*/

require_once "PHPUnit/Framework/TestCase.php";
require_once "PHPUnit/Framework/TestSuite.php";

require_once 'SCA/SCA.php';
require_once 'SCA/Bindings/ebaysoap/Proxy.php';


class SCA_eBaySoapTest extends PHPUnit_Framework_TestCase
{

    public function setUp()
    {
        if (!class_exists('SCA_Bindings_ebaysoap_Proxy')) {
            $this->markTestSkipped('Test skipped as the ebay soap binding is not loaded');
        }
        if (!SCA_Bindings_ebaysoap_Proxy::dependenciesLoaded()) {
            $this->markTestSkipped('Test skipped as the dependencies required for the ebay binding (openssl) are not loaded');
        }
    }

    public function testAGoodIniDoesNotThrowException() {
        try {
            $ebay = new SCA_Bindings_ebaysoap_Proxy(dirname(__FILE__). '/bogus.wsdl', '',
            array('config' => dirname(__FILE__). '/config/good.ini'));
        }
        catch (SCA_RuntimeException $e) {
            $this->assertTrue(false, 'Exception throw for valid eBay soap binding config (good.ini): ' . $e->getMessage());
        }
    }

    public function testWeThrowExceptionForAMissingIniFile() {

        try {
            $ebay = new SCA_Bindings_ebaysoap_Proxy(dirname(__FILE__). '/bogus.wsdl', '',
            array('config' => dirname(__FILE__). '/config/no.ini'));
            $this->assertTrue(false, 'Failed to throw exception for missing ini file.');
        }
        catch (SCA_RuntimeException $e) {
        }
    }

    public function testWeThrowExceptionForAMissingWsdlFile() {

        try {
            $ebay = new SCA_Bindings_ebaysoap_Proxy(dirname(__FILE__). '/no.wsdl', '',
            array('config' => dirname(__FILE__). '/config/good.ini'));
            $this->assertTrue(false, 'Failed to throw exception for missing WSDL file.');
        }
        catch (SCA_RuntimeException $e) {
        }
    }

    public function testWeThrowExceptionsForBadIniFiles() {

        try {
            $ebay = new SCA_Bindings_ebaysoap_Proxy(dirname(__FILE__). '/bogus.wsdl', '',
            array('config' => dirname(__FILE__). '/config/bad_siteid.ini'));
            $this->assertTrue(false, 'Failed to throw exception for missing siteid.');
        }
        catch (SCA_RuntimeException $e) {
        }
        try {
            $ebay = new SCA_Bindings_ebaysoap_Proxy(dirname(__FILE__). '/bogus.wsdl', '',
            array('config' => dirname(__FILE__). '/config/bad_appid.ini'));
            $this->assertTrue(false, 'Failed to throw exception for missing appid.');
        }
        catch (SCA_RuntimeException $e) {
        }
        try {
            $ebay = new SCA_Bindings_ebaysoap_Proxy(dirname(__FILE__). '/bogus.wsdl', '',
            array('config' => dirname(__FILE__). '/config/bad_authcert.ini'));
            $this->assertTrue(false, 'Failed to throw exception for missing authcert.');
        }
        catch (SCA_RuntimeException $e) {
        }
        try {
            $ebay = new SCA_Bindings_ebaysoap_Proxy(dirname(__FILE__). '/bogus.wsdl', '',
            array('config' => dirname(__FILE__). '/config/bad_devid.ini'));
            $this->assertTrue(false, 'Failed to throw exception for missing devid.');
        }
        catch (SCA_RuntimeException $e) {
        }
        try {
            $ebay = new SCA_Bindings_ebaysoap_Proxy(dirname(__FILE__). '/bogus.wsdl', '',
            array('config' => dirname(__FILE__). '/config/bad_location.ini'));
            $this->assertTrue(false, 'Failed to throw exception for missing location.');
        }
        catch (SCA_RuntimeException $e) {
        }
        try {
            $ebay = new SCA_Bindings_ebaysoap_Proxy(dirname(__FILE__). '/bogus.wsdl', '',
            array('config' => dirname(__FILE__). '/config/bad_routing.ini'));
            $this->assertTrue(false, 'Failed to throw exception for missing routing.');
        }
        catch (SCA_RuntimeException $e) {
        }
        try {
            $ebay = new SCA_Bindings_ebaysoap_Proxy(dirname(__FILE__). '/bogus.wsdl', '',
            array('config' => dirname(__FILE__). '/config/bad_version.ini'));
            $this->assertTrue(false, 'Failed to throw exception for missing version.');
        }
        catch (SCA_RuntimeException $e) {
        }
        try {
            $ebay = new SCA_Bindings_ebaysoap_Proxy(dirname(__FILE__). '/bogus.wsdl', '',
            array('config' => dirname(__FILE__). '/config/bad_authtoken.ini'));
            $this->assertTrue(false, 'Failed to throw exception for missing authtoken.');
        }
        catch (SCA_RuntimeException $e) {
        }

    }

    public function testOverridingIniFileSettings() {

        try {
            $ebay = new SCA_Bindings_ebaysoap_Proxy(dirname(__FILE__). '/bogus.wsdl', '',
            array('config' => dirname(__FILE__). '/config/bad_authtoken.ini',
            'authtoken' => 'XXX'));
        }
        catch (SCA_RuntimeException $e) {
            $this->assertTrue(false, 'Exception thrown for valid configuration: ' . $e->getMessage());
        }
        try {
            $ebay = new SCA_Bindings_ebaysoap_Proxy(dirname(__FILE__). '/bogus.wsdl', '',
            array('config' => dirname(__FILE__). '/config/good.ini',
            'authtoken' => ''));
            $this->assertTrue(false, 'Expected override to set authtoken to empty string but config passed validation.');
        }
        catch (SCA_RuntimeException $e) {
        }

    }

    public function testDeclarativeServiceUsingEbayBinding() {

        $this->markTestIncomplete('this test fails because the ebaysoap proxy is being passed the wrong value for immediate_caller_directory (bug in SCA::getService()');
        try {
            $ebay = SCA::getService('./eBayConsumer.php');
        }
        catch (Exception $e) {
            $this->assertTrue(false, 'Exception thrown for valid declarative service: ' . $e->getMessage());
        }
    }

    public static function main()
    {
        require_once "PHPUnit/TextUI/TestRunner.php";

        $suite  = new PHPUnit_Framework_TestSuite("SCA_eBaySoapTest");
        $result = PHPUnit_TextUI_TestRunner::run($suite);
    }

}

// Call SCA_eBaySoapTest::main() if this source file is executed directly.
if (!defined("PHPUnit_MAIN_METHOD")) {
    define("PHPUnit_MAIN_METHOD", "SCA_eBaySoapTest::main");
    SCA_eBaySoapTest::main();
}

?>
