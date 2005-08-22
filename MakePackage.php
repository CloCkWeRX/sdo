 <?php
 /* $Id$ */
    require_once('PEAR/PackageFileManager.php');
    $packagexml = new PEAR_PackageFileManager;
    $e = $packagexml->setOptions(
      array(
       'package' => 'sdo',
       'summary' => 'Service Data Objects (SDOs) for PHP',
       'description' => 
            'Service Data Objects (SDOs) enable PHP applications to work with data from different sources ' .
            '(like a database query, an XML file, or a spreadsheet) using a single interface. ',
//       'notes' => 
//            'This is the first release of SDO for PHP. It contains the core SDO extension and two Data' .
//            ' Access Services: an XML DAS written in C and a Relational DAS to work with relational databases,' .
//            ' which is written in PHP and uses PDO.' .
//            ' The SDO extension requires a recent version of PHP 5.1.' .
//            ' It has been tested on both 5.1.0b2 and 5.1.0b3.' .
//            ' The core SDO extension and XML DAS work with 5.1.0b2.' .
//            ' The Relational DAS requires PHP 5.1.0b3.',
       'notes' => 'Now includes support for DB2 on both Windows and Linux as well as MySQL.' . "\n" .
       		      'Added some tests for the XML DAS.',
       'simpleoutput' => true,
       'version' => '0.5.1',
       'baseinstalldir' => 'SDO',
       'state' => 'beta',
       'license' => 'Apache 2.0',
       'packagedirectory' => 'C:/eclipse-3.0/workspace/sdo/',
       'roles' => array('*.php' => 'php', '*.cpp' => 'src'),
       'ignore' => array('CVS/','sdo*tgz','.project','MakePackage.php',
       
               "aclocal.m4",
        "config.guess",
        "config.sub",
        "configure",
        "configure.ac",
        "depcomp",
        "install-sh",
        "ltmain.sh",
        "Makefile.am",
        "Makefile.in",
        "missing",
        "mkinstalldirs"),
        
        
     'dir_roles' => array('/' => 'src','tests'=> 'test', 'DAS' => 'php'),
       'filelistgenerator' => 'cvs' // generate from cvs, use file for directory
        )
     );
//     'ignore' => array('*.), // ignore TODO, all files in tests/
     //'installexceptions' => array('phpdoc' => '/*'), // baseinstalldir ="/" for phpdoc
     //'exceptions' => array('README' => 'doc', // README would be data, now is doc
     //                      'PHPLICENSE.txt' => 'doc'))); // same for the license
	$packagexml->addMaintainer('gcc',0,'Graham Charters','charters@uk.ibm.com');
	$packagexml->addMaintainer('cem',0,'Caroline Maynard','caroline.maynard@uk.ibm.com');
	$packagexml->addMaintainer('ansriniv',0,'Anantoju Veera Srinivas','srinivas.anantoju@in.ibm.com');
	$packagexml->addMaintainer('mfp',0,'Matthew Peters','matthew_peters@uk.ibm.com');
//	$packagexml->addRole('php','php');
//	$packagexml->addRole('c','src');
//	$packagexml->addRole('h','src');
//	$packagexml->addRole('cpp','src');
//	$packagexml->addRole('sql','data');
//	$packagexml->addDependency('php','5.1.0b2','ge','php');
     if (PEAR::isError($e)) {
        echo $e->getMessage();
        die();
    }
//    $e = $test->addPlatformException('pear-phpdoc.bat', 'windows');
//    if (PEAR::isError($e)) {
//        echo $e->getMessage();
//        exit;
//    }
//    $packagexml->addRole('pkg', 'doc'); // add a new role mapping
//    if (PEAR::isError($e)) {
//        echo $e->getMessage();
//        exit;
//    }
//    // replace @PHP-BIN@ in this file with the path to php executable!  pretty neat
//    $e = $test->addReplacement('pear-phpdoc', 'pear-config', '@PHP-BIN@', 'php_bin');
//    if (PEAR::isError($e)) {
//        echo $e->getMessage();
//        exit;
//    }
//    $e = $test->addReplacement('pear-phpdoc.bat', 'pear-config', '@PHP-BIN@', 'php_bin');
//    if (PEAR::isError($e)) {
//        echo $e->getMessage();
//        exit;
//    }

    // note use of debugPackageFile() - this is VERY important
//    if (isset($_GET['make']) || $_SERVER['argv'][1] == 'make') {
        $e = $packagexml->writePackageFile();
//    } else {
//        $e = $packagexml->debugPackageFile();
//    }
    if (PEAR::isError($e)) {
        echo $e->getMessage();
        die();
    }
?>
