<?php 
/* create a DataFactory */
$dmsDf = SDO_DAS_DataFactory :: getDataFactory();

define('ROOT_NS', 'phpt');
define('COMPANY_NS', 'companyNS');
define('ROOT_TYPE', 'RootType');
define('COMPANY_TYPE', 'CompanyType');
define('DEPARTMENT_TYPE', 'DepartmentType');
define('EMPLOYEE_TYPE', 'EmployeeType');

// Create the types
$dmsDf->addType(ROOT_NS, ROOT_TYPE);
$dmsDf->addType(COMPANY_NS, COMPANY_TYPE); 
$dmsDf->addType(COMPANY_NS, DEPARTMENT_TYPE);
$dmsDf->addType(COMPANY_NS, EMPLOYEE_TYPE);

// add properties to the root type
$dmsDf->addPropertyToType(ROOT_NS, ROOT_TYPE, 'company', COMPANY_NS, COMPANY_TYPE);

// add properties to the company type
$dmsDf->addPropertyToType(COMPANY_NS, COMPANY_TYPE, 'name', SDO_TYPE_NAMESPACE_URI, 'String');
$dmsDf->addPropertyToType(COMPANY_NS, COMPANY_TYPE, 'departments', COMPANY_NS, DEPARTMENT_TYPE, array('many'=>true));
$dmsDf->addPropertyToType(COMPANY_NS, COMPANY_TYPE, 'employeeOfTheMonth', COMPANY_NS, EMPLOYEE_TYPE, array('containment'=>false));
$dmsDf->addPropertyToType(COMPANY_NS, COMPANY_TYPE, 'CEO', COMPANY_NS, EMPLOYEE_TYPE);

// add properties to the department type
$dmsDf->addPropertyToType(COMPANY_NS, DEPARTMENT_TYPE, 'name', SDO_TYPE_NAMESPACE_URI, 'String');
$dmsDf->addPropertyToType(COMPANY_NS, DEPARTMENT_TYPE, 'location', SDO_TYPE_NAMESPACE_URI, 'String');
$dmsDf->addPropertyToType(COMPANY_NS, DEPARTMENT_TYPE, 'number', SDO_TYPE_NAMESPACE_URI, 'Integer');
$dmsDf->addPropertyToType(COMPANY_NS, DEPARTMENT_TYPE, 'employees', COMPANY_NS, EMPLOYEE_TYPE, array('many'=>true));

// add properties to employee type
$dmsDf->addPropertyToType(COMPANY_NS, EMPLOYEE_TYPE, 'name', SDO_TYPE_NAMESPACE_URI, 'String');
$dmsDf->addPropertyToType(COMPANY_NS, EMPLOYEE_TYPE, 'SN', SDO_TYPE_NAMESPACE_URI, 'String');
$dmsDf->addPropertyToType(COMPANY_NS, EMPLOYEE_TYPE, 'manager', SDO_TYPE_NAMESPACE_URI, 'Boolean');

// Test creation of a data object
$company = $dmsDf->create(COMPANY_NS, COMPANY_TYPE);
$company->name = 'MegaCorp';

// Test creation of a child data object
$department = $company->createDataObject('departments');
$department->name = 'Shoe';

// Test creation of a child data object
$employee = $department->createDataObject('employees');
$employee->name = "Sarah Jones";
$company->employeeOfTheMonth = $employee;

// Test use of non-containment reference
$ceo = $company->createDataObject('CEO');
$ceo->name = "Fred Smith";
?>