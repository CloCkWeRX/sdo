<?php 

/*
+----------------------------------------------------------------------+
| (c) Copyright IBM Corporation 2005, 2006.                            |
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
| Author: Anantoju V Srinivas (Srini), Matthew Peters, Caroline Maynard|
+----------------------------------------------------------------------+

*/
require_once 'PHPUnit2/Framework/TestCase.php';
require_once 'PHPUnit2/Framework/IncompleteTestError.php';


// The following globals and the error handler itself are used for catching I/O warnings when XSD or XML files not found
$XMLDASTest_error_handler_called = false;
$XMLDASTest_error_handler_severity;
$XMLDASTest_error_handler_msg;

function XMLDASTest_user_error_handler($severity, $msg, $filename, $linenum) {
	global $XMLDASTest_error_handler_called;
	global $XMLDASTest_error_handler_severity;
	global $XMLDASTest_error_handler_msg;

	$XMLDASTest_error_handler_called = true;
	$XMLDASTest_error_handler_severity = $severity;
	$XMLDASTest_error_handler_msg = $msg;
}



class XMLDASTest extends PHPUnit2_Framework_TestCase {

	public function __construct($name) {
		parent :: __construct($name);
	}

	public function setUp() {
		// is the extension loaded ?
		$loaded = extension_loaded('sdo_das_xml');
		$this->assertTrue($loaded, 'sdo_das_xml extension is not loaded.');


		// are we using the matched extension version?
		$version = phpversion('sdo_das_xml');
		$this->assertTrue(($version >= '0.7.0'), 'Incompatible version of sdo_das_xml extension.');

		// We don''t want to force direct type comparison (e.g. we want (int)100 to be the same as "100")
		$this->setLooselyTyped(true);
	}

	public function tearDown() {
		// Can add test case cleanup here.  PHPUnit2_Framework_TestCase will automatically call it
	}

	public function testCannotCallPrivateConstructor() {
		// This test is dummied out!!! We cannot actually supply a test as calling the constructor issues an E_ERROR
		// to the effect that the constructor is private, and you cannot handle E_ERRORs
		// ... but ...
		// having this empty test here makes the --testdox-text log from phpunit look good
		//		try {
		//			$xmldas = new SDO_DAS_XML();
		//		} catch (SDO_Exception $e) {
		//			$this->assertTrue(false, "testCreate - Exception  Caught" . $e->getMessage());
		//		}
	}

	public function testCreateCanBeCalledWithNoFilename() {
		global $XMLDASTest_error_handler_called;
		global $XMLDASTest_error_handler_severity;
		global $XMLDASTest_error_handler_msg;

		set_error_handler('XMLDASTest_user_error_handler');
		$XMLDASTest_error_handler_called = false;
		$exception_thrown = false;
		try {
			$xmldas = SDO_DAS_XML::create();
		} catch (Exception $e) {
			$this->assertTrue(false, "Exception was thrown from create() when it should not have been: ".$e->getMessage());
		}
		$this->assertFalse($XMLDASTest_error_handler_called, 'Error handler should not have been called for create(). Message was ' . $XMLDASTest_error_handler_msg);
	}

	public function testaddTypesAndOpenTypes() {
		global $XMLDASTest_error_handler_called;
		global $XMLDASTest_error_handler_severity;
		global $XMLDASTest_error_handler_msg;

		set_error_handler('XMLDASTest_user_error_handler');
		$XMLDASTest_error_handler_called = false;
		$exception_thrown = false;
		try {
			$xmldas = SDO_DAS_XML::create();
			$xmldas->addTypes('anyElement/jungle.xsd'); // this is an open type i.e. the xsd specifies it can contain "any" type
			$xmldas->addTypes('anyElement/animalTypes.xsd');
			$baloo 			= $xmldas->createDataObject('','bearType');
			$baloo->name 	= "Baloo";
			$baloo->weight 	= 800;

			$bagheera 		= $xmldas->createDataObject('','pantherType');
			$bagheera->name = "Bagheera";
			$bagheera->colour = 'inky black';

			$kaa 			= $xmldas->createDataObject('','snakeType');
			$kaa->name 		= "Kaa";
			$kaa->length 	= 25;

			$document 		= $xmldas->createDocument();
			$do 			= $document->getRootDataObject();
			$do->bear 		= $baloo;
			$do->panther 	= $bagheera;
			$do->snake 		= $kaa;
		} catch (Exception $e) {
			$this->assertTrue(false, "Exception was thrown from addTypes test when it should not have been: ".$e->getMessage());
		}
		$this->assertFalse($XMLDASTest_error_handler_called, 'Error handler should not have been called for add types test. Message was ' . $XMLDASTest_error_handler_msg);
	}

	public function testFileExceptionThrownAndWarningIssuedWhenXsdFileIsNotPresent() {
		global $XMLDASTest_error_handler_called;
		global $XMLDASTest_error_handler_severity;
		global $XMLDASTest_error_handler_msg;

		set_error_handler('XMLDASTest_user_error_handler');
		$XMLDASTest_error_handler_called = false;
		$exception_thrown = true;
		try {
			$xmldas = SDO_DAS_XML::create("a complete load of rubbish.xsd");
			$exception_thrown = false;
		} catch (SDO_DAS_XML_FileException $e) {
		} catch (Exception $e) {
		    $this->fail("Incorrect exception thrown for file not found; expected SDO_DAS_XML_FileException, was " . get_class($e));		    
		}
		$this->assertTrue($XMLDASTest_error_handler_called, 'Error handler should have been called for file not found');
		$this->assertTrue($XMLDASTest_error_handler_severity == E_WARNING, 'Expected an E_WARNING when file not found');
		$this->assertTrue(strpos($XMLDASTest_error_handler_msg, 'I/O warning') > 0, 'Warning message not right: ' . $XMLDASTest_error_handler_msg);
		$this->assertTrue($exception_thrown,'SDO_DAS_XML_FileException should have been thrown but was not');
	}

	public function testaddTypesCanBeCalled() {
		global $XMLDASTest_error_handler_called;
		global $XMLDASTest_error_handler_severity;
		global $XMLDASTest_error_handler_msg;

		set_error_handler('XMLDASTest_user_error_handler');
		$XMLDASTest_error_handler_called = false;
		$exception_thrown = false;
		try {
			$xmldas = SDO_DAS_XML::create("company.xsd");
			$xmldas->addTypes("company.xsd");
		} catch (Exception $e) {
			$this->assertTrue(false, "Exception should not have been thrown: ".$e->getMessage());
		}
		$this->assertFalse($XMLDASTest_error_handler_called, 'Error handler should not have been called');
	}

	public function testFileExceptionThrownAndWarningIssuedWhenXmlFileIsNotPresent() {
		global $XMLDASTest_error_handler_called;
		global $XMLDASTest_error_handler_severity;
		global $XMLDASTest_error_handler_msg;

		$xmldas = SDO_DAS_XML::create("company.xsd");
		set_error_handler('XMLDASTest_user_error_handler');
		$XMLDASTest_error_handler_called = false;
        $exception_thrown = true;
		try {
			$xdoc = $xmldas->loadFile("what_a_load_of_rubbish.xml");
            $exception_thrown = false;
		} catch (SDO_DAS_XML_FileException $e) {		
		} catch (Exception $e) {
		    $this->fail("Incorrect exception thrown for file not found; expected SDO_DAS_XML_FileException, was " . get_class($e));		    
		}
		$this->assertTrue($XMLDASTest_error_handler_called, 'Error handler should have been called for file not found');
		$this->assertTrue($XMLDASTest_error_handler_severity == E_WARNING, 'Expected an E_WARNING when file not found');
		$this->assertTrue(strpos($XMLDASTest_error_handler_msg, 'I/O warning') > 0, 'Warning message not right: ' . $XMLDASTest_error_handler_msg);
		$this->assertTrue($exception_thrown,'SDO_DAS_XML_FileException should have been thrown but was not');
	}

	public function testParseExceptionThrownWhenXmlParseErrorOccursInCreateButDoNotWorryOnLinux() {
		/************************************
		* This test is known not to work on Linux
		* it should wok on Windows though
		***********************************/
		$exception_thrown = false;
		try {
			$xmldas = SDO_DAS_XML::create('parseErrors/parse_errors.xsd');
		} catch (SDO_DAS_XML_ParserException $e) {
			$exception_thrown = true;
		} catch (Exception $e) {
			$this->assertTrue(false, "Incorrect exception thrown for xml parse errors in xsd: ".$e->getMessage());
		}
		$this->assertTrue($exception_thrown, 'SDO_DAS_XML_ParserException should have been thrown');
	}

	public function testParseExceptionThrownWhenXmlParseErrorOccursInLoadFile() {
		$xmldas = SDO_DAS_XML::create("company.xsd");
		$exception_thrown = false;
		try {
			$xdoc = $xmldas->loadFile('parseErrors/parse_errors.xml');
			$do = $xdoc->getRootDataObject(); // don't expect to get here
		} catch (SDO_DAS_XML_ParserException $e) {
			$exception_thrown = true;
		} catch (Exception $e) {
			$this->assertTrue(false, "Incorrect exception thrown for xml parse errors: ".$e->getMessage());
		}
		$this->assertTrue($exception_thrown, 'SDO_DAS_XML_ParserException should have been thrown');
	}

	public function testParseExceptionThrownWhenXmlParseErrorOccursInLoadString() {
		$xmldas = SDO_DAS_XML::create("company.xsd");
		$exception_thrown = false;
		try {
			$xdoc = $xmldas->loadString("<ugly<");
			$do = $xdoc->getRootDataObject(); // don't expect to get here
		} catch (SDO_DAS_XML_ParserException $e) {
			$exception_thrown = true;
		} catch (Exception $e) {
			$this->assertTrue(false, "Incorrect exception thrown for xml parse errors: ".$e->getMessage());
		}
		$this->assertTrue($exception_thrown, 'SDO_DAS_XML_ParserException should have been thrown');
	}

	public function testCreate_NormalPath_WorksWhenXsdFileIsPresent() {
		try {
			$xmldas = SDO_DAS_XML::create("company.xsd");
		} catch (SDO_Exception $e) {
			$this->assertTrue(false, "testCreate - Exception Caught: " . $e->getMessage());
		}
	}

	public function testCreate_TypeNotFoundExceptionThrownWhenXsdFaulty() {
		// the xsd has a company type that refers to an employe type but doesn't define the employee type
		$exception_thrown = false;
		try {
			$xmldas = SDO_DAS_XML::create('missingType/company_missing_type.xsd');
			$this->assertTrue(false, "SDO_DAS_XML::create failed to throw SDO_TypeNotFoundException. ");
		} catch (SDO_TypeNotFoundException $e) {
			$exception_thrown = true;
		} catch (SDO_Exception $e) {
			$this->assertTrue(false, "Incorrect exception thrown for SDO_DAS_XML::create: ".$e->getMessage());
		}
		$this->assertTrue($exception_thrown,'SDO_TypeNotFoundException should have been thrown but was not');
	}

	public function testLoadFile_NormalPath_WorksWhenFileIsPresent() {
		try {
			$xmldas = SDO_DAS_XML::create("company.xsd");
			$xdoc = $xmldas->loadFile("company.xml");
		} catch (SDO_Exception $e) {
			$this->assertTrue(false, "testLoadFile - Exception Caught: " . $e->getMessage());
		}
	}

	/*
	The XML for the following test looks like this:
	<company xmlns="companyNS" name="MegaCorp" employeeOfTheMonth="#/departments.0/employees.1">
	<departments name="Advanced Technologies" location="NY" number="123">
	<employees name="John Jones" SN="E0001"/>
	<employees name="Jane Doe" SN="E0003"/>
	<employees name="Al Smith" SN="E0004" manager="true"/>
	</departments>
	</company>
	*/
	public function testLoadFile_LoadedGraphCorrespondsToTheXml() {
		try {
			$xmldas = SDO_DAS_XML::create("company.xsd");
			$xdoc = $xmldas->loadFile("company.xml");
			$do = $xdoc->getRootDataObject();
			$this->assertEquals("MegaCorp", $do->name, 'Company name is not valid.');
			$this->assertEquals(1, count($do->departments), 'Wrong number of departments.');
			$this->assertEquals("Advanced Technologies", $do->departments[0]->name, 'Department name is not valid.');
			$this->assertEquals("NY", $do->departments[0]->location, 'Department location invalid.');
			$this->assertEquals(123, $do->departments[0]->number, 'Department number invalid.');
			$this->assertEquals(3, count($do->departments[0]->employees), 'Wrong number of employees.');
			$this->assertEquals("John Jones", $do->departments[0]->employees[0]->name, 'Employee name is not valid.');
			// use assertSame to check both ways to reach Jane Doe really reach the same object
			$this->assertSame($do->departments[0]->employees[1], $do->employeeOfTheMonth, 'Two ways to reach e.o.t.m do not agree.');
		} catch (SDO_Exception $e) {
			$this->assertTrue(false, "testGetRootDataObject - Exception Caught: " . $e->getMessage());
		}
	}

	public function testLoadFile_LoadedGraphCorrespondsToTheXmlWithNilSn() {
		try {
			$xmldas = SDO_DAS_XML::create('nillable/company_with_nillable_SN.xsd');
			$xdoc = $xmldas->loadFile('nillable/company_with_nillable_SN.xml');
			$do = $xdoc->getRootDataObject();
			$department = $do->departments[0];
			$jane = $department->employees[0];
			$this->assertTrue($jane->SN === null && isset($jane->SN), 'Serial number is not null.');
		} catch (SDO_Exception $e) {
			$this->assertTrue(false, "testLoadFile_Loaded.... - Exception Caught: " . $e->getMessage());
		}
	}

	public function testCreateDataObject_WorksCorrectly() {
		try {
			$xmldas = SDO_DAS_XML::create("company.xsd");

			$acme = $xmldas->createDataObject("companyNS", "CompanyType");
			$acme->name = "Acme";
			$this->assertEquals("Acme", $acme->name, 'testCreateDataObject - Cannot access created data object.');
		} catch (SDO_Exception $e) {
			$this->assertTrue(false, "testCreateDataObject - Exception Caught: " . $e->getMessage());
		}
	}

	public function testSaveString_SaveAndReloadWorksCorrectly() {
		try {
			$xmldas = SDO_DAS_XML::create("company.xsd");
			$xdoc = $xmldas->loadFile("company.xml");
			$do = $xdoc->getRootDataObject();

			//Change some values
			$emp_list = $do->departments[0]->employees;
			$do->employeeOfTheMonth = $emp_list[0];
			$do->departments[0]->location = "Bangalore";

			//Save the changes to xml string
			$str = $xmldas->saveString($xdoc);
			$this->assertTrue(strrpos($str,'Bangalore') > 0, 'Department location not found in saved xml.');

			//Test whether it is save correctly or not?
			$xdoc1 = $xmldas->loadString($str);
			$do1 = $xdoc1->getRootDataObject();
			$this->assertEquals("John Jones", $do->employeeOfTheMonth->name, 'Non-containment reference is not valid.');
			$this->assertEquals("Bangalore", $do->departments[0]->location, 'Can not access part of tree from root data object');
			$this->assertSame($do->departments[0]->employees[0], $do->employeeOfTheMonth, 'Two ways to reach e.o.t.m do not agree.');
		} catch (SDO_Exception $e) {
			$this->assertTrue(false, "testSaveString - Exception Caught: " . $e->getMessage());
		}
	}

	public function testSaveFile_SaveAndReloadWorksCorrectly() {
		try {
			$temp_file = tempnam($_ENV['TMP'], 'SDO');
			$xmldas = SDO_DAS_XML::create("company.xsd");
			$xdoc = $xmldas->loadFile("company.xml");
			$do = $xdoc->getRootDataObject();

			//Change some values
			$emp_list = $do->departments[0]->employees;
			$do->employeeOfTheMonth = $emp_list[0];
			$do->departments[0]->location = "Bangalore";

			//Save the changes to xml file
			$str = $xmldas->saveFile($xdoc, $temp_file);

			//Test whether it is saved correctly or not
			$xdoc1 = $xmldas->loadFile($temp_file);
			$do1 = $xdoc1->getRootDataObject();
			unlink($temp_file);
			$this->assertEquals("John Jones", $do->employeeOfTheMonth->name, 'Non-containment reference is not valid.');
			$this->assertEquals("Bangalore", $do->departments[0]->location, 'Can not access part of tree from root data object');
		} catch (SDO_Exception $e) {
			$this->assertTrue(false, "testSaveFile - Exception Caught: " . $e->getMessage());
		}
	}

	public function testXMLDocument_getRootObject() {
		try {
			$xmldas = SDO_DAS_XML::create("company.xsd");
			$xdoc = $xmldas->loadFile("company.xml");
			$this->assertEquals("MegaCorp", $xdoc->getRootDataObject()->name, 'testgetRootDataObject - was not the company');
		} catch (SDO_Exception $e) {
			$this->assertTrue(false, "testGetRootDataObject - Exception Caught: " . $e->getMessage());
		}
	}

	public function testXMLDocument_getRootElementURI() {
		try {
			$xmldas = SDO_DAS_XML::create("company.xsd");
			$xdoc = $xmldas->loadFile("company.xml");
			$this->assertEquals("companyNS", $xdoc->getRootElementURI(), 'testgetRootElementURI - wrong answer ');
		} catch (SDO_Exception $e) {
			$this->assertTrue(false, "testGetRootDataObject - Exception Caught: " . $e->getMessage());
		}
	}

	public function testXMLDocument_getRootElementName() {
		try {
			$xmldas = SDO_DAS_XML::create("company.xsd");
			$xdoc = $xmldas->loadFile("company.xml");
			$this->assertEquals("company", $xdoc->getRootElementName(), 'testgetRootElementName - wrong answer ');
		} catch (SDO_Exception $e) {
			$this->assertTrue(false, "testGetRootDataObject - Exception Caught: " . $e->getMessage());
		}
	}

	public function testSetOnDocumentWorksCorrectly() {
		try {
			$xmldas = SDO_DAS_XML::create("company.xsd");
			$xdoc = $xmldas->loadFile("company.xml");
			$xdoc->setXMLVersion("1.1");
			$xdoc->setEncoding("ISO-8859-1");
			$str = $xmldas->saveString($xdoc);
			$this->assertTrue(strpos($str, '1.1') > 0, 'XML Version was apparently not set correctly');
			$this->assertTrue(strpos($str, 'ISO-8859-1') > 0, 'Encoding was apparently not set correctly');
		} catch (SDO_Exception $e) {
			$this->assertTrue(false, "testsetEncoding - Exception Caught: " . $e->getMessage());
		}
	}

	public function testCreateDocumentNoArgs() {
		try {
			$xmldas = SDO_DAS_XML::create("company.xsd");
			$xdoc = $xmldas->createDocument();
			$root_do = $xdoc->getRootDataObject();
			$rdo = new SDO_Model_ReflectionDataObject($root_do);
			$type = $rdo->getType();

			$this->assertTrue($type->name == 'CompanyType', 'type of root object should be CompanyType but was ' . $type->name);
			$this->assertTrue($type->namespaceURI == 'companyNS', 'name space of root element should be companyNS but was ' . $type->namespaceURI);
			$this->assertTrue($xdoc->getRootElementURI() == 'companyNS', 'namespace for the document should be companyNS but was ' . $xdoc->getRootElementURI());
			$this->assertTrue($xdoc->getRootElementName() == 'company', 'root element name should be company but was ' . $xdoc->getRootElementName());
		} catch (SDO_Exception $e) {
			$this->assertTrue(false, "createDocumentNoArgs - Unexpected Exception Caught: " . $e->getMessage());
		}
	}

	public function testCreateDocumentOneArg() {
		try {
			$xmldas = SDO_DAS_XML::create("company.xsd");
			$xdoc = $xmldas->createDocument('company');
			$root_do = $xdoc->getRootDataObject();
			$rdo = new SDO_Model_ReflectionDataObject($root_do);
			$type = $rdo->getType();

			$this->assertTrue($type->name == 'CompanyType', 'type of root object should be CompanyType but was ' . $type->name);
			$this->assertTrue($type->namespaceURI == 'companyNS', 'name space of root element should be companyNS but was ' . $type->namespaceURI);
			$this->assertTrue($xdoc->getRootElementURI() == 'companyNS', 'namespace for the document should be companyNS but was ' . $xdoc->getRootElementURI());
			$this->assertTrue($xdoc->getRootElementName() == 'company', 'root element name should be company but was ' . $xdoc->getRootElementName());
		} catch (SDO_Exception $e) {
			$this->assertTrue(false, "createDocumentNoArgs - Unexpected Exception Caught: " . $e->getMessage());
		}
	}
	
	public function testCreateDocumentTwoArgs() {
		try {
			$xmldas = SDO_DAS_XML::create("company.xsd");
			$xdoc = $xmldas->createDocument('companyNS','company');
			$root_do = $xdoc->getRootDataObject();
			$rdo = new SDO_Model_ReflectionDataObject($root_do);
			$type = $rdo->getType();

			$this->assertTrue($type->name == 'CompanyType', 'type of root object should be CompanyType but was ' . $type->name);
			$this->assertTrue($type->namespaceURI == 'companyNS', 'name space of root element should be companyNS but was ' . $type->namespaceURI);
			$this->assertTrue($xdoc->getRootElementURI() == 'companyNS', 'namespace for the document should be companyNS but was ' . $xdoc->getRootElementURI());
			$this->assertTrue($xdoc->getRootElementName() == 'company', 'root element name should be company but was ' . $xdoc->getRootElementName());
		} catch (SDO_Exception $e) {
			$this->assertTrue(false, "createDocumentNoArgs - Unexpected Exception Caught: " . $e->getMessage());
		}
	}
	
	public function testCreateDocumentThreeArgs() {
	    try {
			$xmldas = SDO_DAS_XML::create("company.xsd");
			$root_do = $xmldas->createDataObject('companyNS', 'CompanyType');
			$xdoc = $xmldas->createDocument('newNS', 'newElement', $root_do);
			$rdo = new SDO_Model_ReflectionDataObject($root_do);
			$type = $rdo->getType();
			
			$this->assertTrue($type->name == 'CompanyType', 'type of root object should be CompanyType but was ' . $type->name);
			$this->assertTrue($type->namespaceURI == 'companyNS', 'name space of root element should be companyNS but was ' . $type->namespaceURI);
			$this->assertTrue($xdoc->getRootElementURI() == 'newNS', 'namespace for the document should be newNS but was ' . $xdoc->getRootElementURI());
			$this->assertTrue($xdoc->getRootElementName() == 'newElement', 'root element name should be newElement but was ' . $xdoc->getRootElementName());
		} catch (SDO_Exception $e) {
			$this->assertTrue(false, "createDocumentNoArgs - Unexpected Exception Caught: " . $e->getMessage() . ' '. $e->getCause());
		}
	}

	public function testExtendedComplexType() {
		try {
			$xmldas = SDO_DAS_XML::create("extendedComplexType/person.xsd");
			$xdoc = $xmldas->loadFile("extendedComplexType/person.xml");
			$do = $xdoc->getRootDataObject();
			$this->assertTrue($do->first == 'William', 'first name should have been William');
			$this->assertTrue($do->gender == 'male', 'gender should have been male');
			$this->assertTrue($xdoc->getRootElementURI() == 'TNS', 'namespace for the document should be TNS but was ' . $xdoc->getRootElementURI());
			$this->assertTrue($xdoc->getRootElementName() == 'person', 'root element name should be person but was ' . $xdoc->getRootElementName());
		} catch (SDO_Exception $e) {
			$this->assertTrue(false, "testExtendedComplexType - Unexpected Exception Caught: " . $e->getMessage());
		}
	}

	public function testRestrictedComplexType() {
		try {
			$xmldas = SDO_DAS_XML::create('restrictedComplexType/name.xsd');
			$xdoc = $xmldas->loadFile('restrictedComplexType/name.xml');
			$do = $xdoc->getRootDataObject();
			$this->assertTrue($do->last == 'Smith', 'last name should have been Smith');
			$this->assertTrue($do->title == 'Mr.', 'title should have been Mr.');
			$this->assertTrue($xdoc->getRootElementURI() == 'TNS', 'namespace for the document should be TNS but was ' . $xdoc->getRootElementURI());
			$this->assertTrue($xdoc->getRootElementName() == 'formalname', 'root element name should be formalname but was ' . $xdoc->getRootElementName());
		} catch (SDO_Exception $e) {
			$this->assertTrue(false, "testRestrictedComplexType - Unexpected Exception Caught: " . $e->getMessage());
		}
	}

	public function testAbstractTypeCannotBeInstantiated() {
		global $XMLDASTest_error_handler_called;
		global $XMLDASTest_error_handler_severity;
		global $XMLDASTest_error_handler_msg;

		$xmldas = SDO_DAS_XML::create('abstractComplexType/abstract.xsd');
		set_error_handler('XMLDASTest_user_error_handler');
		$XMLDASTest_error_handler_called = false;
		$exception_thrown = false;
		$created_first_object_ok = false;
		try {
			$person = $xmldas->createDataObject('TNS','fullpersoninfo'); // OK
			$created_first_object_ok = true;
			$person = $xmldas->createDataObject('TNS','personinfo'); // should fail
		} catch (Exception $e) {
			$this->assertTrue(strpos($e->getMessage(), 'abstract type') > 0, 'Wrong exception text: ' . $e->getMessage());
			$exception_thrown = true;
		}
		$this->assertTrue($created_first_object_ok == true, 'Failed to create object of concrete type OK');
		$this->assertTrue($exception_thrown, 'testAbstractTypeCannotBeInstantiated - exception should have been thrown');
	}

}
?>
