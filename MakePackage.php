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
       'package' => 'SCA_SDO',
       'summary' => 'Service Component Architecture (SCA) and Service Data Objects (SDO) for PHP',
       'description' => 
            'Service Data Objects (SDOs) enable PHP applications to work with data from different sources ' .
            '(typically a database query or an XML file) using a single interface. ' .
            'SCA for PHP allows a PHP programmer to write reusable components (classes) in PHP, which can be called ' .
            'either locally, or in a a variety of ways remotely (soap web services, xml-rpc, json-rpc, REST, etc), ' .
            'but always with the same interface. ' ,
//       'notes' => 
//            'This is the first release of SDO for PHP. It contains the core SDO extension and two Data' .
//            ' Access Services: an XML DAS written in C and a Relational DAS to work with relational databases,' .
//            ' which is written in PHP and uses PDO.' .
//            ' The SDO extension requires a recent version of PHP 5.1.' .
//            ' It has been tested on both 5.1.0b2 and 5.1.0b3.' .
//            ' The core SDO extension and XML DAS work with 5.1.0b2.' .
//            ' The Relational DAS requires PHP 5.1.0b3.',
//       'notes' => 'Now includes support for DB2 on both Windows and Linux as well as MySQL.' . "\n" .
//                    'Added some tests for the XML DAS.',
//       'notes' => "This release fixes a number of bugs:\n"
//               . "- The XML DAS now throws a more meaningful exception when the xsd or xml file is not found\n"
//               . "- The interface to SDO_DAS_DataFactory::addPropertyToType has changed and the previous interface is deprecated\n"
//               . "- The interface to SDO_DAS_DataFactory::addProperty now supports the setting of default values\n"
//               . "- The unit tests for the XML DAS have been added to\n"
//               . "- The Relational DAS adapts to whether PDO constants are using old-style PDO_* or new-style PDO::*\n"
//               . "- The Relational DAS contains a workaround for a problem with PDO_Statement::RowCount and ODBC driver\n"
//               . "- Some SDO_DAS_ChangeSummary* constants, which were probably only used by the Relational DAS, have been changed",
//      'notes' => "This release adds a small number of new features:\n"
//             .  "- The Relational DAS now supports nulls: an SQL NULL in the database is represented as a PHP null in the data object and vice versa\n"
//             .  "- The important SDO classes all support toString()\n"
//             .  "- The SDO_DataObject class now supports clone()",
//      'notes' => "Improved reporting of Schema and XML parse errors (libxml2 errors surfaced in an XML Data Access Service SDO_DAS_XML_ParserException).\n"
//             . "Various bug fixes, including PECL bugs 6002 and 6006.\n"
//             . "Support for XML Schema 'nillable'.\n"
//             . "Support to build and run against PHP 6.0 (only with unicode semantics off)." ,
//      'notes' => "This release adds support for reflection on a data object. " 
//             . "The SDO_Model_ReflectionDataObject gives the programmer access to " 
//             . "the type and structure information in a data object's model. "
//             . "This can help with debugging, or be used in dynamic user interface generation.",
//       'notes' => "This release adds support for open types. These are types which " 
//             . "can have additional properties added to a runtime instance, for example "
//             . "to support an XML <any/> element.\n"
//             . "Also various bug fixes. ",
//'notes' => 
//"The following changes have been made between 0.7.1 and this release:\n"
//. "A) The changes which are visible at the programming interface are:\n"
//. " 1) The interface to the XML Data Access Service has been revised:\n"
//. "   a) The names of the methods to load and save documents have changed to improve consistency with other packages.\n" 
//. "   b) A new method, createDocument(), has been added to enable creation of a document from scratch.\n"
//. "   c) The saveDataObjectToFile()/String() methods have been replaced by saveFile() and saveString() methods on the XML DAS object.\n"
//. "   d) Some getters and setters on the Document have been fixed or removed.\n"
//. "   e) The XML Data Access Service has added support for the following XML Schema:\n"
//. "    - Open types: support for <any> element and <anyAttribute>\n"
//. "    - Type inheritance: both simple and complex types can be derived by restriction or extension\n"
//. "    - Abstract types: the use of abstract types in the schema is supported\n"
//. "  2) The XML DAS now supports printing its SDO type and property model using print or echo.\n"
//. "  3) The XML DAS can now produce formatted Document (see optional formatting argument on saveFile() and saveString())\n"
//. "  4) The getType() method on a DataObject has been replaced with getTypeName() and getTypeNamespaceURI() methods.\n" 
//. "\n"
//. "B) Other changes in this release:\n"
//. " 1) The memory management in the sdo and sdo_das_xml extensions has been overhauled to squeeze out any memory leaks\n"
//. " 2) Exception messages from the extension have been improved so that they never refer to the underlying C/C++ code\n"
//. " 3) PropertyNotSetException has been improved so that it replicates the way arrays and objects behave as closely as possible\n"
//. " 4) The parsing that the XML DAS performs on both XML Schema and instance documents has been improved so that problems are picked up and reported earlier.\n"
// ,
//'notes' => "First stable release.\n" 
//. "Minor improvements and fixes over 0.9.0.",
//'notes' => "Minor increments and fixes over 1.0.0:\n"
//. "- allow data objects to be copied between data factories\n"
//. "- remove memory leaks in _get_properties methods\n"
//. "- remove memory leak reading value from Sequence\n", 
//       'notes' => "Minor increments and fixes over 1.0.1:\n"
//. "- fix defect 7458\n"
//. "- eliminate use of the C++ XMLDAS implementation\n"
//. "- fix build errors with PHP 5.2\n", 
//     'notes' => "Compatibility with Tuscany C++/SDO M1 release and some bug fixes over 1.0.2\n"
//              . " - Update the base C++/SDO implementation to be the Tuscany CPP Milestone 1 release: cpp-0.1.incubating-M1\n"
//              . " - Tested with Linux AMD 64-bit architecture\n"
//              . " - new 3-argument version of SDO_DAS_XML::createDocument() allows an SDO_DAS_XML_Document to be created from an SDO\n"
//              . " - fix defect 7878 Silent failure with malformed SQL\n"
//              . " - fix defect 7879 Improve error message in SDO_DAS_Relational_DatabaseHelper:executeStatement\n"
//              . " - fix defect 8280 Remove spaces from source files\n"
//              . " - fix defect 8300 Optimistic concurrency failure\n"                
//              . " - fix defect 8374 Exception hierarchy (temporary fix)",
//       'notes' => "Simplified build and install, updated Tuscany release, bug fixes\n"
//           . " - simplified the install by merging the sdo_das_xml library into the sdo core library: note you must remove sdo_das_xml from the extension list in your php.ini\n"
//           . " - fix bug #8493 WSDL with double elements\n"
//           . " - fixed several memory leaks, in iterator objects and others\n"
//           . " - update to Apache Tuscany C++ SDO revision level 433676\n"
//           . " - improved and extended the interoperability tests (see tests/interop in CVS)\n",
//       'notes' => "This is the first release under the new project name SCA_SDO (renamed from SDO).\n"
//           . "The rename reflects the fact that this project now implements the Service Component Architecture (SCA)\n"
//           . "(see http://osoa.org/display/PHP/SCA+with+PHP for more information).\n"
//           . "The project's stable state refers to the SDO component.\n"
//           . "The SCA component is currently alpha quality and experimental.\n"
//           . "The main changes since 1.0.4 are:\n"
//           . " - inclusion of the Service Component Architecture (SCA) component\n"
//           . " - new PEAR packaging to install SCA and SDO as peer directories (PEAR/SCA and PEAR/SDO)\n"
//           . " - update to Apache Tuscany C++ SDO revision level 478193\n"
//           . " - new function in SDO_DAS_Relational to support multiple root data objects\n"
//           . " - new function in SDO_DAS_XML to support CDATA sections (not yet complete)\n"
//           . " - fixes for bugs #9287, #9289, #9339\n",
//       'notes' => " Fix for bug #9498 - invalid WSDL generation\n" 
//           . "Fix for bug 9426 - printing open types\n" 
//           . "Update to Apache Tuscany C++ SDO revision level 483149 - includes various fixes for sequenced and open data types.\n",
//       'notes' => "Fix for bug #9845 - Relational DAS is failing when one parent and two children\n"
//           . "Changed from namespaceURI.type to namespaceURI#type (conform to spec)\n"
//           . "Update to Apache Tuscany C++ SDO revision level 495327 (namespace fixes, performance improvements)\n",
//  'notes' =>
//     "* Pluggable bindings support\n"
//  .  "    This support is all in the core. There are now fewer files in the SCA directory and all code specific to"
//  .  " a given binding (local, soap, jsonrpc etc.) goes in a subdirectory under the Bindings subdirectory."
//  .  " The SCA core code now just knows how to use the SCA_BindingsFactory object to pull in the classes it"
//  .  " needs to service an incoming request. The names of the desired classes are derived from the annotations"
//  .  " e.g. if a component has an @binding.soap annotation, the SCA core code will look in SCA/Bindings/soap for"
//  .  " the classes it needs. This is probably of limited interest unless you plan to write a binding of your own."
//  .  " We plan an article to describe how this works.\n"
//  .  "\n"
//  .  "* Refactored bindings based on the pluggable binding support:\n"
//  .  "    o jsonrpc\n"
//  .  "    o local (php to php binding)\n"
//  .  "    o restrpc (RPC based on HTTP GET or POST)\n"
//  .  "    o soap (SOAP web services)\n"
//  .  "    o xmlrpc \n"
//  .  "\n"
//  .  "* Latest drop of SDO code from Tuscany (currently revision level 532769) including:\n"
//  .  "    o performance enhancements\n"
//  .  "    o set of fixes to DataObject destructor to eliminate crashes when the graph is not freed in the default order\n"
//  .  "\n"
//  .  "* Updates to SDO extension:\n"
//  .  "    o fix memory leak from SDO_DataObject (depends on Tuscany fixes above)\n"
//  .  "    o add debug trace macros for debugging memory allocation\n"
//  .  "    o new signature for SDO_DAS_XML::create() allows an array of schema files to be passed in\n"
//  .  "\n"
//  .  "* Bug fixes \n"
//  .  "    o http://pecl.php.net/bugs/bug.php?id=8428\n"
//  .  "    o http://pecl.php.net/bugs/bug.php?id=9243\n"
//  .  "    o http://pecl.php.net/bugs/bug.php?id=9487\n"
//  .  "    o http://pecl.php.net/bugs/bug.php?id=9991\n"
//  .  "    o http://pecl.php.net/bugs/bug.php?id=10049\n"
//  .  "             \n"
//  .  "* Examples\n"
//  .  "    o More SCA examples that exercise some of the new bindings, and some of the old, including HelloWorlds and Email scenarios.\n",
  'notes' =>
     " * Fix for spaces in service description URLs (pecl defect #11006).\n"
  .  " * Experimental support for service names following the PEAR coding standard\n"
  .  " * Experimental support for a manual service request dispatching interface on SCA.php\n"
  ,
       'simpleoutput' => true,
       'version' => '1.2.1',
       'baseinstalldir' => 'SDO',
       'state' => 'stable',
       'license' => 'Apache 2.0',
       'packagedirectory' => dirname(__FILE__),
       'roles' => array('*.php' => 'php', '*.cpp' => 'src'),
       'ignore' => array(
           'ajax-rss/',
           'ebaysoap/',
           'autom4te.cache/',
           'build/',
           'CVS/',
           'include/',
           'interop/',
           'modules/',
           '.project',
           'acinclude.m4',
           'aclocal.m4',
           'company-metadata.inc.php',
           'config.cache',
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
           'SCA.txt',
           'wsdl-all.xsd',
           'DEV_BRANCH',
                        // packaging
           'MakePackage.php',
           'package.xml',                      
           'php.ini',                  
                        // wildcards
           'Copy of*',                        
           '*.la',
           '*.lo',
           '*~',
           '*.orig',
           '*tgz'
         ),
     'dir_roles' => array(
         '/'        => 'src',
         'examples' => 'test', 
         'tests'    => 'test', 
         'DAS'      => 'php',
         'SCA'      => 'php')       
     )
        
    );
    $packagexml->addMaintainer('gcc','lead','Graham Charters','charters@uk.ibm.com');
    $packagexml->addMaintainer('cem','lead','Caroline Maynard','caroline.maynard@uk.ibm.com');
    $packagexml->addMaintainer('mfp','lead','Matthew Peters','matthew_peters@uk.ibm.com');
    $packagexml->addMaintainer('slaws','lead','Simon Laws','simonslaws@googlemail.com');
    
    $packagexml->addDependency('php', '5.1.0', 'ge', 'php');
    $packagexml->addDependency('sdo', false, 'not', 'pkg');
    
    if (PEAR::isError($e)) {
        echo $e->getMessage();
        die();
    }

    $e = $packagexml->writePackageFile();
    if (PEAR::isError($e)) {
        echo $e->getMessage();
        die();
    }

    /**
     * Move the SCA code out to be a peer of the SDO code. 
     * When we deploy in PEAR we want it to look like. 
     *
     * PEAR
     *   SCA
     *   SDO
     *
     * This is a bit unatural as we are deploying a single sca_sdo package 
     * and we want it to appear as two packages under PEAR
     * 
     * The input package.xml looks like
     *
     * <filelist>
     *   <dir name="/" baseinstalldir="SDO">
     *     ...
     *     <dir name="SCA">
     *   </dir>
     *  </dir>
     * </filelist>
     * 
     * The output package.xml needs to look like
     *
     * <filelist>
     *   <dir name="/">    
     *     <dir name="SCA">
     *       ...
     *     </dir>
     *   </dir>
     *   <dir name="/" baseinstalldir="SDO">    
     *     ...
     *   </dir>
     * </filelist>
     *
     * Note. We have to do two passes across the file because the
     * the SCA files have to come before the SDO files. This is 
     * because the SDO files uses the "baseinstalldir" directive
     * to position the files and pear install assumes this to be 
     * true for all subsequent files even though our SCA files are
     * in a separate peer <dir> element. You can't remove the 
     * baseinstalldir directive because pear install won't put the SDO
     * files in the right place and you can't simply enclose the SDO
     * files with a <dir name="SDO"> element because this is not where the 
     * files are in the input SDO directory. We are fighting to achieve a
     * somewhat out of the ordinary difference between the layout
     * of the input files compared to how we want to see them when PEAR has
     * installed them. 
     */
    
    // all the lines from package.xml
    $in_lines       = file('package.xml');
    
    // sometimes we have to ignore a block of lines from the
    // package.xml file listing because it contains an SCA
    // directory we don't want to move
    $ignore_block   = false;
 
    // when set true lines are recorded in $sca_buffer. When
    // set false lines are recorded in $sdo_buffer
    $sca_buffering  = false;

    // The two buffers used to separate SCA lines from SDO lines
    $sca_buffer     = array();
    $sdo_buffer     = array();

    // separate SCA lines from SDO lines
    foreach ($in_lines as $line) {
        // We have to check that we are not in the examples
        // or tests sections as both of these sections have 
        // SCA directories that we want to leave alone
        if ( strstr($line, "<dir name=\"examples\">") ||
             strstr($line, "<dir name=\"tests\">")      ) {
            $ignore_block = true;
        }

        // find the start tag of an SCA section and 
        // assuming we aren't ignoring the block
        // it appears in start buffering SCA lines
        if ( strstr($line, "<dir name=\"SCA\">") &&
             $ignore_block == false ) {
            $sca_buffering = true;
        }
               
        if ( $sca_buffering ) {
            $sca_buffer[] = $line;
        } else { 
            $sdo_buffer[] = $line;
        }

        // find the end tag of the SCA section and stop
        // buffering SCA lines
        if ( strstr($line, "</dir> <!-- /SCA -->") ) {
            $sca_buffering = false;
        }        

        // Stop ignoring any SCA directories we see when we
        // get to the end of the tests and examples sections
        if ( strstr($line, "</dir> <!-- /examples -->") ||
             strstr($line, "</dir> <!-- /tests -->") ) {
            $ignore_block = false;
        }        
    }

    // empty the temporary output file
    file_put_contents("package.xml.tmp", "");

    // fill the temporary output file with the contents of the
    // SCA and SDO buffers output in the correct place. 
    foreach ( $sdo_buffer as $line ) {
         file_put_contents("package.xml.tmp", $line, FILE_APPEND);

        // when we find the start of the file list output
        // the sca lines
        if ( strstr($line, "<filelist>") ) {
             foreach($sca_buffer as $buffer_line) {
                file_put_contents("package.xml.tmp", $buffer_line, FILE_APPEND);
            }
        }
    }
         
    // move the files so that the modified file becomes package.xml 
    // and the original file becomse package.xml.orig
    @unlink("package.xml.orig");
    rename("package.xml", "package.xml.orig");
    rename("package.xml.tmp", "package.xml");
    
?>
