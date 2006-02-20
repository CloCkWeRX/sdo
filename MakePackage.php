 <?php
 /* $Id$ */
    require_once('PEAR/PackageFileManager.php');
    $packagexml = new PEAR_PackageFileManager;
    if (PEAR::isError($packagexml)) {
      echo $packagexml->getMessage();
      exit; 
    }
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
//       'notes' => 'Now includes support for DB2 on both Windows and Linux as well as MySQL.' . "\n" .
//       		      'Added some tests for the XML DAS.',
//       'notes' => "This release fixes a number of bugs:\n"
//               . "- The XML DAS now throws a more meaningful exception when the xsd or xml file is not found\n"
//               . "- The interface to SDO_DAS_DataFactory::addPropertyToType has changed and the previous interface is deprecated\n"
//               . "- The interface to SDO_DAS_DataFactory::addProperty now supports the setting of default values\n"
//               . "- The unit tests for the XML DAS have been added to\n"
//               . "- The Relational DAS adapts to whether PDO constants are using old-style PDO_* or new-style PDO::*\n"
//               . "- The Relational DAS contains a workaround for a problem with PDO_Statement::RowCount and ODBC driver\n"
//               . "- Some SDO_DAS_ChangeSummary* constants, which were probably only used by the Relational DAS, have been changed",
//		'notes' => "This release adds a small number of new features:\n"
//				.  "- The Relational DAS now supports nulls: an SQL NULL in the database is represented as a PHP null in the data object and vice versa\n"
//                .  "- The important SDO classes all support toString()\n"
//                .  "- The SDO_DataObject class now supports clone()",
//		'notes' => "Improved reporting of Schema and XML parse errors (libxml2 errors surfaced in an XML Data Access Service SDO_DAS_XML_ParserException).\n"
//                 . "Various bug fixes, including PECL bugs 6002 and 6006.\n"
//                 . "Support for XML Schema 'nillable'.\n"
//                 . "Support to build and run against PHP 6.0 (only with unicode semantics off)." ,
// 'notes'  => "This release adds support for reflection on a data object. " 
//           . "The SDO_Model_ReflectionDataObject gives the programmer access to " 
//           . "the type and structure information in a data object's model. "
//           . "This can help with debugging, or be used in dynamic user interface generation.",
//       'notes' => "This release adds support for open types. These are types which " 
//             . "can have additional properties added to a runtime instance, for example "
//             . "to support an XML <any/> element.\n"
//             . "Also various bug fixes. ",

'notes' => 
"The following changes have been made between 0.7.1 and this release:\n"
. "I) The changes which are visible at the programming interface are:\n"
. " 1) The interface to the XML Data Access Service has been revised:\n"
. "   a) The names of the methods to load and save documents have chanmged to improve consistency with other packages.\n" 
. "   b) A new method, createDocument(), has been added to enable creation of a document from scratch.\n"
. "   c) The saveDataObjectToFile()/String() methods have been replaced by saveFile() and saveString() methods on the XML DAS object.\n"
. "   d) Some getters and setters on the Document have been fixed or removed.\n"
. "   e) The XML Data Access Service has added support for the following XML Schema:\n"
. "    - Open types: support for <any> element and <anyAttribute>\n"
. "    - Type inheritance: both simple and complex types can be derived by restriction or extension\n"
. "    - Abstract types: the use of abstract types in the schema is supported\n"
. "  2) The XML DAS now supports printing its SDO type and property model using print or echo.\n"
. "  3) The XML DAS can now produce formatted Document (see optional formatting argument on saveFile() and saveString())\n"
. "  4) The getType() method on a DataObject has been replaced with getTypeName() and getTypeNamespaceURI() methods.\n" 
. "\n"
. "II) Other changes in this release:\n"
. " 1) The memory management in the sdo and sdo_das_xml extensions has been overhauled to squeeze out any memory leaks\n"
. " 2) Exception messages from the extension have been improved so that they never refer to the underlying C/C++ code\n"
. " 3) PropertyNotSetException has been improved so that it replicates the way arrays and objects behave as closely as possible\n"
. " 4) The parsing that the XML DAS performs on both XML Schema and instance documents has been improved so that problems are picked up and reported earlier.\n"
 ,
       'simpleoutput' => true,
       'version' => '0.9.0',
       'baseinstalldir' => 'SDO',
       'state' => 'beta',
       'license' => 'Apache 2.0',
       'packagedirectory' => dirname(__FILE__),
       'roles' => array('*.php' => 'php', '*.cpp' => 'src'),
       'ignore' => array(
           'autom4te.cache/',
           'build/',
           'CVS/',
           'include/',
           'modules/',
           '.project',
           'acinclude.m4',
           'aclocal.m4',
           'config.guess',
           'config.h',
           'config.h.in',
           'config.log',
           'config.nice',
           'config.status',
           'config.sub',
           'configure',
           'configure.in',
           'install.sh',
           'install-sh',
           'libtool',
           'ltmain.sh',
           'Makefile',
           'Makefile.fragments',
           'Makefile.global',
           'Makefile.objects',
           'missing',
           'mkinstalldirs',
           'run-tests.php',
                        // packaging
           'MakePackage.php',
           'package.xml',                      
                        // wildcards
           '*.la',
           '*.lo',
           'sdo*tgz'
         ),
                
     'dir_roles' => array('/' => 'src','tests'=> 'test', 'DAS' => 'php'),
       'filelistgenerator' => 'file' // generate from cvs, use file for directory
        )
     );
	$packagexml->addMaintainer('gcc','lead','Graham Charters','charters@uk.ibm.com');
	$packagexml->addMaintainer('cem','lead','Caroline Maynard','caroline.maynard@uk.ibm.com');
	$packagexml->addMaintainer('mfp','lead','Matthew Peters','matthew_peters@uk.ibm.com');
    if (PEAR::isError($e)) {
        echo $e->getMessage();
        die();
    }

    $e = $packagexml->writePackageFile();
    if (PEAR::isError($e)) {
        echo $e->getMessage();
        die();
    }
?>
