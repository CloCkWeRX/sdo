<?php
/* $Id: MakePackage.php 254167 2008-03-04 10:09:53Z mfp $ */
require_once 'PEAR/PackageFileManager.php';

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
    'notes' =>
      " # The ability to control the operations on a service interface through a PHP interface \n"
    . "     by specifying the PHP interface on the @service annotation - e.g. @service MyServiceInterface\n"
    . " # PECL bug 11997 - don't remove xsi:type (except on top level soap message or response)\n"
    . " # PECL bug 11996 - not showing LIBXML2 parse errors\n"
    . " # PECL bug 12193 - alphabetical order of namespaces causes failure\n"
    . " # PECL bug 12103 - saveString doesn't encode entities \n"
    . " # PECL bug 12443 - unable to access an XSD property containing a hyphen (-) \n"
    . " # PECL bug 13101 - Repeated nill elements of extended type cause \"Parser found unknown element\" exception\n"
    . " # Fix to add wsdl namespace prefix to <types> element in WSDL, without which it will not validate. \n"
    . " # Fix for Tuscany AccessViolation problem when serializing a DO\n"
    . " # Backward-compatible updates to SDO extension so that it will work with PHP 5.3\n"
    . " # Backward-compatible updates to SCA so that it will work with PHP 5.3. \n"
    . " # Fix for failures that occur when using the soap extension - see thread \"SCA Webservice in WSDL mode\"\n"
    . " # Substantial rework of the examples to illustrate more bindings - see examples/SCA/index.html\n"
     ,
   'simpleoutput' => true,
   'version' => '1.2.4',
   'baseinstalldir' => 'SDO',
   'state' => 'stable',
   'license' => 'Apache 2.0',
   'packagedirectory' => dirname(__FILE__),
   'roles' => array('*.php' => 'php', '*.cpp' => 'src'),
   'ignore' => array(
       'ajax-rss/',
       'simpledb/',
       'rss/',
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
$packagexml->addDependency('sdo_das_xml', false, 'not', 'ext');

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
    if (strstr($line, "<dir name=\"examples\">") ||
         strstr($line, "<dir name=\"tests\">")) {
        $ignore_block = true;
    }

    // find the start tag of an SCA section and
    // assuming we aren't ignoring the block
    // it appears in start buffering SCA lines
    if (strstr($line, "<dir name=\"SCA\">") && !$ignore_block) {
        $sca_buffering = true;
    }

    if ($sca_buffering) {
        $sca_buffer[] = $line;
    } else {
        $sdo_buffer[] = $line;
    }

    // find the end tag of the SCA section and stop
    // buffering SCA lines
    if (strstr($line, "</dir> <!-- /SCA -->")) {
        $sca_buffering = false;
    }

    // Stop ignoring any SCA directories we see when we
    // get to the end of the tests and examples sections
    if (strstr($line, "</dir> <!-- /examples -->") ||
         strstr($line, "</dir> <!-- /tests -->")) {
        $ignore_block = false;
    }
}

// empty the temporary output file
file_put_contents("package.xml.tmp", "");

// fill the temporary output file with the contents of the
// SCA and SDO buffers output in the correct place.
foreach ($sdo_buffer as $line) {
     file_put_contents("package.xml.tmp", $line, FILE_APPEND);

    // when we find the start of the file list output
    // the sca lines
    if (strstr($line, "<filelist>")) {
         foreach ($sca_buffer as $buffer_line) {
            file_put_contents("package.xml.tmp", $buffer_line, FILE_APPEND);
        }
    }
}

// move the files so that the modified file becomes package.xml
// and the original file becomse package.xml.orig
@unlink("package.xml.orig");
rename("package.xml", "package.xml.orig");
rename("package.xml.tmp", "package.xml");