<?php

require_once "PHPUnit/Framework/TestCase.php";
require_once "PHPUnit/Framework/TestSuite.php";
require_once "PHPUnit/Framework.php";

require_once 'SCA/SCA_Logger.php' ;

/**
 * Test the SCA Logger functons
 *
 */
class SCALoggerTest extends PHPUnit_Framework_TestCase
{
    private static  $logger             = null ;

    private         $testentry          =
                    "Log Entry test string for the SCA_LoggerTest" ;

    private         $loginfo;

    private         $loglocn;

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
        self::$logger =  new SCA_Logger( $this->loginfo[ 0 ], $this->loginfo[ 1 ] ) ;
        $this->assertNotNull( self::$logger ) ;
    }

    /**
     * The default condition of the logger is 'stopped' check to ensure
     * no log entry can be made.
     *
     */
    public function testNoLogMethod()
    {
        $this->assertNotNull( self::$logger ) ;

        self::$logger->log( $this->testentry, "",  "", SCA_LOGGER_CRITICAL ) ;

        $this->assertFileNotExists( $this->loglocn ) ;

    }

    /**
     * Start the logger to complete the tests.
     *
     */
    public function testStartMethod()
    {
        $this->assertNotNull( self::$logger ) ;

        self::$logger->startLog() ;

        $status = self::$logger->logStatus() ;

        $this->assertEquals( $status[ 'run' ]
                           , "Running"
                           , "SCA_LoggerTest::testStopMethod Status is not running"
                           ) ;

    }

    /**
     * Test that a log entry is made
     *
     */
    public function testLogMethod()
    {
        $this->assertNotNull( self::$logger ) ;

        self::$logger->log( $this->testentry, "",  "", SCA_LOGGER_CRITICAL ) ;

        $this->assertFileExists( $this->loglocn ) ;

        $log = self::$logger->fromLog() ;

        $this->assertContains( $this->testentry
                             , $log[ 0 ]
                             , "SCA_LoggerTest::testLogMethod Log entry not found"
                             ) ;

    }

    /**
     * Test that the class/method name is overwritten when the alternate arguments are used.
     *
     */
    public function testLogFileMethod()
    {
        $file = __FILE__ ;
        $line = __LINE__ ;

        $this->assertNotNull( self::$logger ) ;

        self::$logger->log( $this->testentry, $file,  $line, SCA_LOGGER_CRITICAL ) ;

        $log = self::$logger->fromLog() ;

        $this->assertContains( $file
                             , $log[ 1 ]
                             , "SCA_LoggerTest::testLogFileMethod Filename not found"
                             ) ;
        $this->assertContains( "::{$line}"
                             , $log[ 1 ]
                             , "SCA_LoggerTest::testLogFileMethod Line Position not found"
                             ) ;

    }

    /**
     * Test that no log entry is made when the stop mode has been asserted
     *
     */
    public function testStopMethod()
    {
        $this->assertNotNull( self::$logger ) ;

        self::$logger->stopLog() ;

        $status = self::$logger->logStatus() ;

        $this->assertEquals( $status[ 'run' ]
                           , "Stopped"
                           , "SCA_LoggerTest::testStopMethod Status is not stopped"
                           ) ;

        $this->assertEquals( $status[ 'count' ]
                           , 2
                           , "SCA_LoggerTest::testStopMethod Invalid number of log entries"
                           ) ;

    }

    /**
     * Test that a log entry is made when the start mode has been asserted
     *
     */
    public function testRestartMethod()
    {
        $this->assertNotNull( self::$logger ) ;

        self::$logger->startLog() ;

        self::$logger->log( $this->testentry, "",  "", SCA_LOGGER_CRITICAL ) ;

        $status = self::$logger->logStatus() ;

        $this->assertEquals( $status[ 'run' ]
                           , "Running"
                           , "SCA_LoggerTest::testRestartMethod Status is not running"
                           ) ;
        $this->assertEquals( $status[ 'count' ]
                           , 3
                           , "SCA_LoggerTest::testRestartMethod Invalid number of log entries"
                           ) ;

    }
    /**
     * Test that the status returns the correct state
     *
     */
    public function testStatusMethod()
    {
        $checkArray     = array( 'run'    => "Running"
                               , 'dir'    => $this->loginfo[ 0 ]
                               , 'file'   => $this->loginfo[ 1 ]
                               , 'catgry' => "AllLevels"
                               , 'count'  => 3
                               ) ;

        $this->assertNotNull( self::$logger ) ;

        $status = self::$logger->logStatus() ;

        $this->assertEquals( $status, $checkArray ) ;

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

}/* End SCA Logger Test class                                                 */

/*=============================================================================*/

if (!defined("PHPUnit_MAIN_METHOD")) {
    define("PHPUnit_MAIN_METHOD", "SCALoggerTest::main");
    SCALoggerTest::main();
}

?>