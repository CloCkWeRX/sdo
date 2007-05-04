<?php

require_once "PHPUnit/Framework/TestCase.php";
require_once "PHPUnit/Framework/TestSuite.php";
require_once "PHPUnit/Framework.php";

require_once 'SCA/SCA_Logger.php';

/**
 * Test the filter functions of the Logger
 *
 */
class SCALogFilterTest extends PHPUnit_Framework_TestCase
{
    private static  $logger             = null ;

    private         $testentry          =
                    "Log Entry test string for the SCA_LoggerTest" ;


    private         $loginfo;

    private         $loglocn;

    /**
     * Initialize the log location values
     */
    public function __construct() {
        $tmpdir = SCA_Helper::getTempDir();
        $this->loginfo = array($tmpdir . '/log', 'SCAUnitTest');
        $this->loglocn = $tmpdir . '/log/SCAUnitTest.log';
    }

    /**
     * Test that SCA_Logger is created
     *
     */
    public function testDefaultLoggerCreated()
    {
        self::$logger = new SCA_Logger( $this->loginfo[ 0 ], $this->loginfo[ 1 ]   ) ;

        $this->assertNotNull( self::$logger ) ;

        self::$logger->startLog() ;

        $status = self::$logger->logStatus() ;

        $this->assertEquals( $status[ 'run' ]
                           , "Running"
                           , "SCA_LoggerTest::testStopMethod Status is not running"
                           ) ;
    }

    /**
     * Set the filter level to include any log entry that is -
     *
     *      - critical
     *      - warning
     *      - debug
     *
     */
    public function testSetFilter()
    {
        $this->assertNotNull( self::$logger ) ;

        self::$logger->setLogLevel( SCA_LOGGER_CRITICAL | SCA_LOGGER_WARNING | SCA_LOGGER_DEBUG ) ;

        self::$logger->log( $this->testentry, "", "", SCA_LOGGER_CRITICAL ) ;
        self::$logger->log( $this->testentry, "", "", SCA_LOGGER_ERROR ) ;
        self::$logger->log( $this->testentry, "", "", SCA_LOGGER_WARNING ) ;
        self::$logger->log( $this->testentry, "", "", SCA_LOGGER_INFO ) ;
        self::$logger->log( $this->testentry, "", "", SCA_LOGGER_DEBUG ) ;

        $status = self::$logger->logStatus() ;

        $this->assertEquals( $status[ 'count' ]
                           , 3
                           , "SCA_LoggerTest::testSetFilterMethod Invalid number of log entries"
                           ) ;

    }

    /**
     * Read back and check that the critical message exists.
     *
     */
    public function testReadCritical()
    {
        $this->assertNotNull( self::$logger ) ;

        $critical = self::$logger->fromLog( SCA_LOGGER_CRITICAL ) ;

        $msgs = sizeof( $critical ) ;

        $this->assertEquals( $msgs
                           , 1
                           , "SCA_LogFilterTest::testReadCritical Invalid number of CRITICAL Messages"
                           ) ;

        $this->assertContains( 'Critical'
                             , $critical[ 0 ]
                             , "SCA_LogFilterTest::testReadCritical CRITICAL message not found"
                             ) ;

    }
    /**
     * Read back and check that an error message does not exist.
     *
     */
    public function testReadError()
    {
        $this->assertNotNull( self::$logger ) ;

        $error = self::$logger->fromLog( SCA_LOGGER_ERROR ) ;

        $msgs = sizeof( $error ) ;

        $this->assertEquals( $msgs
                           , 0
                           , "SCA_LogFilterTest::testReadCritical NO ERROR Message should exist"
                           ) ;


    }

    /**
     * Read back and check that the warning message exists.
     *
     */
    public function testReadWarning()
    {
        $this->assertNotNull( self::$logger ) ;

        $warning = self::$logger->fromLog( SCA_LOGGER_WARNING ) ;

        $msgs = sizeof( $warning ) ;

        $this->assertEquals( $msgs
                           , 1
                           , "SCA_LogFilterTest::testReadWarning Invalid number of WARNING Messages"
                           ) ;

        $this->assertContains( 'Warning'
                             , $warning[ 0 ]
                             , "SCA_LogFilterTest::testReadWarning WARNING message not found"
                             ) ;

    }

    /**
     * Read back and check that the information message does not exist.
     *
     */
    public function testReadInformation()
    {
        $this->assertNotNull( self::$logger ) ;

        $info = self::$logger->fromLog( SCA_LOGGER_INFO ) ;

        $msgs = sizeof( $info ) ;

        $this->assertEquals( $msgs
                           , 0
                           , "SCA_LogFilterTest::testReadInformation Invalid number of INFO Messages"
                           ) ;


    }

    /**
     * Read back and check that the debug message exists.
     *
     */
    public function testReadDebug()
    {
        $this->assertNotNull( self::$logger ) ;

        $debug = self::$logger->fromLog( SCA_LOGGER_DEBUG ) ;

        $msgs = sizeof( $debug ) ;

        $this->assertEquals( $msgs
                           , 1
                           , "SCA_LogFilterTest::testReadDebug Invalid number of DEBUG Messages"
                           ) ;

        $this->assertContains( 'Debug'
                             , $debug[ 0 ]
                             , "SCA_LogFilterTest::testReadDebug DEBUG message not found"
                             ) ;

    }

    /**
     * Ensure that the log file is deleted
     *
     */
    public function testDeleteLogMethod()
    {
        $this->assertNotNull( self::$logger ) ;

        self::$logger->deleteLogFile() ;

        $this->assertFileNotExists( $this->loglocn ) ;

    }

}/* End SCA Log FilterTest class                                               */
/*=============================================================================*/

if (!defined("PHPUnit_MAIN_METHOD")) {
    define("PHPUnit_MAIN_METHOD", "SCALogFilterTest::main");
    SCALogFilterTest::main();
}

?>