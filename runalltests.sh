#! /bin/bash
# +----------------------------------------------------------------------+
# | (c) Copyright IBM Corporation 2005, 2006.                            |
# | All Rights Reserved.                                                 |
# +----------------------------------------------------------------------+
# |                                                                      |
# | Licensed under the Apache License, Version 2.0 (the "License"); you  |
# | may not use this file except in compliance with the License. You may |
# | obtain a copy of the License at                                      |
# | http://www.apache.org/licenses/LICENSE-2.0                           |
# |                                                                      |
# | Unless required by applicable law or agreed to in writing, software  |
# | distributed under the License is distributed on an "AS IS" BASIS,    |
# | WITHOUT WARRANTIES OR CONDITIONS OF ANY KIND, either express or      |
# | implied. See the License for the specific language governing         |
# | permissions and limitations under the License.                       |
# +----------------------------------------------------------------------+
# | Author: SL                                                           |
# +----------------------------------------------------------------------+
# $Id: runalltests.sh,v 1.1 2006-09-28 13:14:15 slaws Exp $

echo +++++++++++++++++++++++++++++++++++++++++++++++++++++++++++++++++++++++++++++++
echo This script tests the php executable installed
echo in /usr/local/bin and relies on the 
echo rest of php as usually configured in 
echo /usr/local/lib. Ensure that the php 
echo include path is set to include 
echo   - /usr/local/lib/php
echo   - your pecl build directory
echo You can do this by setting include_path in 
echo the php.ini file, for example, 
echo    include_path=".:/usr/local/lib/php:/home/slaws/phpbuild-5-1-4/pecl"
echo Also ensure that the sdo project under
echo pecl is called SDO rather than sdo. 

export TEST_PHP_EXECUTABLE=/usr/local/bin/php
export PHPUNIT_EXECUTABLE=/usr/local/bin/phpunit

echo +++++++++++++++++++++++++++++++++++++++++++++++++++++++++++++++++++++++++++++++
echo SDO Core Tests
$TEST_PHP_EXECUTABLE run-tests.php tests

echo +++++++++++++++++++++++++++++++++++++++++++++++++++++++++++++++++++++++++++++++
echo Check that PHPUnit2 is installed 
$TEST_PHP_EXECUTABLE tests/SDOTestSetup.php

echo +++++++++++++++++++++++++++++++++++++++++++++++++++++++++++++++++++++++++++++++
echo SDO Core PHPUnit2 Tests
cd tests
$PHPUNIT_EXECUTABLE SDOAPITest SDOAPITest.php --log-xml SDOAIPITest.xml
cd ..

echo +++++++++++++++++++++++++++++++++++++++++++++++++++++++++++++++++++++++++++++++
echo XML DAS PHPUnit2 Tests
cd tests/XMLDAS/PHPUnitTests
$PHPUNIT_EXECUTABLE XMLDASTest XMLDASTest.php
cd ../../..

echo +++++++++++++++++++++++++++++++++++++++++++++++++++++++++++++++++++++++++++++++
echo Relational DAS PHPUnit2 Tests
cd DAS/Relational/Tests
$PHPUNIT_EXECUTABLE SDO_DAS_Relational_TestSuite TestSuite.php
cd ../../..

echo +++++++++++++++++++++++++++++++++++++++++++++++++++++++++++++++++++++++++++++++
echo You may like to run the Relational samples now which 
echo test the SDO Relational DAS against a real database. 
echo See DAS/Relational/Scenarios/README for details
echo +++++++++++++++++++++++++++++++++++++++++++++++++++++++++++++++++++++++++++++++

echo +++++++++++++++++++++++++++++++++++++++++++++++++++++++++++++++++++++++++++++++
echo SDO Interop tests
cd tests/interop
$TEST_PHP_EXECUTABLE interop-xml.php
cd ../..

echo +++++++++++++++++++++++++++++++++++++++++++++++++++++++++++++++++++++++++++++++
echo You may like to run the relational interop tests now
echo See the file tests/interop/README.txt under
echo the test5 heading. 
echo +++++++++++++++++++++++++++++++++++++++++++++++++++++++++++++++++++++++++++++++
