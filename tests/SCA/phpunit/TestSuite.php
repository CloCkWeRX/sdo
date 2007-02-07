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
/***********************************************************************************************************
*
*
************************************************************************************************************/

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
require_once 'SoapProxy/SCA_SoapProxyTest.php';
require_once 'WSDLGeneration/SCA_WSDLTest.php';
require_once 'SDO_TypeHandler/TypeHandlerTest.php';
require_once 'GetService/PathsTest.php';

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
        $suite->addTest(new PHPUnit_Framework_TestSuite("SCA_SoapProxyTest"));
        $suite->addTest(new PHPUnit_Framework_TestSuite("SCA_WSDLTest"));
        $suite->addTest(new PHPUnit_Framework_TestSuite("SDO_TypeHandlerTest"));
        $suite->addTest(new PHPUnit_Framework_TestSuite("SCA_GetServicePathsTest"));
        
		return $suite;
    }

}
if (PHPUnit_MAIN_METHOD == 'SCA_TestSuite::main') {
    SCA_TestSuite::main();
}
?>
