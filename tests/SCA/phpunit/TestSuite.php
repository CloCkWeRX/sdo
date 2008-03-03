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

if (!defined('PHPUnit_MAIN_METHOD')) {
    define('PHPUnit_MAIN_METHOD', 'SCA_TestSuite::main');
}

require_once 'PHPUnit/Framework.php';
require_once 'PHPUnit/TextUI/TestRunner.php';

require_once 'AnnotationTests/ServiceAnnotationTest.php';
require_once 'AnnotationTests/BindingAnnotationTest.php';
require_once 'AnnotationTests/PublicMethodsTest.php';
require_once 'AnnotationTests/ParamAnnotationTest.php';
require_once 'AnnotationTests/ReturnAnnotationTest.php';
require_once 'AnnotationTests/TypesAnnotationTest.php';
require_once 'AnnotationTests/ReferenceAnnotationTest.php';
require_once 'TabsAndSpaces/SCA_TabsAndSpacesTest.php';
require_once 'LocalProxy/SCA_LocalProxyTest.php';
require_once 'SoapBinding/ProxyTest.php';
require_once 'SoapBinding/HandlerTest.php';
require_once 'SoapBinding/WSDLTest.php';
require_once 'SoapBinding/MapperTest.php';
require_once 'GetService/PathsTest.php';
require_once 'JsonRpc/JsonRpcTest.php';
require_once 'XmlRpc/XmlRpcTest.php';
require_once 'RestRpc/RestRpcTest.php';
require_once 'eBaySoap/eBaySoapTest.php';
require_once 'TargetTests/SCA_TargetTest.php';
require_once 'RegressionTests/SCA_RegressionTestSuite.php';
require_once 'SCA/SCATest.php';
//require_once 'LogTests/SCALoggerTest.php' ;
//require_once 'LogTests/SCALogFilterTest.php' ;

class SCA_TestSuite {

    public static function main()
    {
        PHPUnit_TextUI_TestRunner::run(self::suite());
    }

    public static function suite() {
        $suite = new PHPUnit_Framework_TestSuite();
        $suite->addTest(new PHPUnit_Framework_TestSuite("SCA_Annotation_ServiceTest"));
        $suite->addTest(new PHPUnit_Framework_TestSuite("SCA_Annotation_BindingTest"));
        $suite->addTest(new PHPUnit_Framework_TestSuite("SCA_Annotation_MethodTest"));
        $suite->addTest(new PHPUnit_Framework_TestSuite("SCA_Annotation_ParamTest"));
        $suite->addTest(new PHPUnit_Framework_TestSuite("SCA_Annotation_ReturnTest"));
        $suite->addTest(new PHPUnit_Framework_TestSuite("SCA_Annotation_TypesTest"));
        $suite->addTest(new PHPUnit_Framework_TestSuite("SCA_Annotation_ReferenceTest"));
        $suite->addTest(new PHPUnit_Framework_TestSuite("SCA_TabsAndSpacesTest"));
        $suite->addTest(new PHPUnit_Framework_TestSuite("SCA_LocalProxyTest"));
        $suite->addTest(new PHPUnit_Framework_TestSuite("SCA_Bindings_soap_ProxyTest"));
        $suite->addTest(new PHPUnit_Framework_TestSuite("SCA_Bindings_soap_HandlerTest"));
        $suite->addTest(new PHPUnit_Framework_TestSuite("SCA_Bindings_soap_WSDLTest"));
        $suite->addTest(new PHPUnit_Framework_TestSuite("SCA_Bindings_soap_MapperTest"));
        $suite->addTest(new PHPUnit_Framework_TestSuite("SCA_GetServicePathsTest"));
        $suite->addTest(new PHPUnit_Framework_TestSuite("SCA_XmlRpcTest"));
        $suite->addTest(new PHPUnit_Framework_TestSuite("SCA_RestRpcTest"));
        $suite->addTest(new PHPUnit_Framework_TestSuite("SCA_eBaySoapTest"));
        $suite->addTest(new PHPUnit_Framework_TestSuite("SCA_JsonRpcTest"));
        $suite->addTest(new PHPUnit_Framework_TestSuite("SCA_TargetTest"));
        $suite->addTest(new PHPUnit_Framework_TestSuite("SCA_Test"));

        //        $suite->addTest(new PHPUnit_Framework_TestSuite("Bug11774Test"));
        //        $suite->addTest(new PHPUnit_Framework_TestSuite("Bug12193Test"));

        $suite->addTest(SCA_RegressionTestSuite::suite());

        // TODO
        // interface to logger has changed - rework the tests
        //        $suite->addTest( new PHPUnit_Framework_TestSuite("SCALoggerTest"));
        //        $suite->addTest( new PHPUnit_Framework_TestSuite("SCALogFilterTest"));

        return $suite;
    }

}
if (PHPUnit_MAIN_METHOD == 'SCA_TestSuite::main') {
    SCA_TestSuite::main();
}
?>
