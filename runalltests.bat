echo off
rem +----------------------------------------------------------------------+
rem | (c) Copyright IBM Corporation 2005, 2006.                            |
rem | All Rights Reserved.                                                 |
rem +----------------------------------------------------------------------+
rem |                                                                      |
rem | Licensed under the Apache License, Version 2.0 (the "License"); you  |
rem | may not use this file except in compliance with the License. You may |
rem | obtain a copy of the License at                                      |
rem | http://www.apache.org/licenses/LICENSE-2.0                           |
rem |                                                                      |
rem | Unless required by applicable law or agreed to in writing, software  |
rem | distributed under the License is distributed on an "AS IS" BASIS,    |
rem | WITHOUT WARRANTIES OR CONDITIONS OF ANY KIND, either express or      |
rem | implied. See the License for the specific language governing         |
rem | permissions and limitations under the License.                       |
rem +----------------------------------------------------------------------+
rem | Author: SL                                                           |
rem +----------------------------------------------------------------------+
rem $Id: runalltests.bat,v 1.4 2007-02-07 11:25:35 cem Exp $

echo +++++++++++++++++++++++++++++++++++++++++++++++++++++++++++++++++++++++++++++++
echo This script runs all of the PHP SDO tests that can
echo be run automatically. 
echo You need to edit this script to set the 
echo home directory of PHP and the path to the 
echo directory holding the php.exe you are testing against.
echo Go in and edit 
echo PHP_HOME - the root directory for the php build
echo PHP_BIN_HOME - the directory holding the binary to be tested
scho TMP - the directory where temporary files will be written during testing

set PHP_HOME=C:\simon\Projects\Tuscany\php\php-5.2.0
set PHP_BIN_HOME=%PHP_HOME%\Debug_TS
set TMP=C:\temp

echo You also need to ensure that the php 
echo include path is set to include at least
echo   - the directory holding the pear extensions
echo   - your pecl build directory
echo You can do this by setting include_path in 
echo the php.ini file, for example, 
echo    include_path=".;C:\php\php-5.1.4\pear;C:\php\pecl"

rem set up some other environment variables based on the above
set TEST_PHP_EXECUTABLE=%PHP_BIN_HOME%\php.exe
set PHPUNIT_EXECUTABLE=%PHP_HOME%\phpunit.bat
set PATH=%PHP_BIN_HOME%

echo +++++++++++++++++++++++++++++++++++++++++++++++++++++++++++++++++++++++++++++++
echo PHPT Tests
call %TEST_PHP_EXECUTABLE% %PHP_HOME%\run-tests.php tests

echo +++++++++++++++++++++++++++++++++++++++++++++++++++++++++++++++++++++++++++++++
echo PHPUnit2 Tests
cd tests
call %TEST_PHP_EXECUTABLE% AllTests.php
cd ..

echo +++++++++++++++++++++++++++++++++++++++++++++++++++++++++++++++++++++++++++++++
echo You may like to run the Relational samples now which 
echo test the SDO Relational DAS against a real database. 
echo See DAS/Relational/Scenarios/README for details

echo +++++++++++++++++++++++++++++++++++++++++++++++++++++++++++++++++++++++++++++++
echo You may like to run the relational interop tests now
echo See the file tests\interop\README.txt under
echo the test5 heading. 


