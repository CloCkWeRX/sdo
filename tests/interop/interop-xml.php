<?php
/*
+----------------------------------------------------------------------+
| (c) Copyright IBM Corporation 2006.                                  |
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
| Author: SL                                                           |
+----------------------------------------------------------------------+
$Id: interop-xml.php 229262 2007-02-07 11:26:44Z cem $
*/

/*
 * Some simple code to compare two XML documents
 * This is very rundimentary but better than eyeballing the xml files
 * It needs replacing with a proper XML comparison at some point
 * In particular beware
 *  - I am only testing for attributes in the default and xsi namespaces
 *  - Simple XML does some strange things with namespaces which I can't tie down
 *    seems to be OK if you use the default namespace in the input XML
 *    but gets confused with its counts if you use a qualified namespace
 */

/*
 * set this to 'on' to get lots of output
 */
define ( 'DEBUG',  'off');

/*
 * set this to 'on' if you want any difference between xsi:type attributes to be flagged as a warning
 * this flag is here as SDO uses LibXML2 in a way that generates xsi:types on the root element
 */
define ( 'WARN_XSITYPE_DIFFERENCE',  'on');

/*
 * checks the number of contained nodes are equal
 */
function compareNodeCount ($nodename, $node1, $node2, $reason )
{
  $count1 = count ($node1);
  $count2 = count ($node2);

  $message = $reason . "  " . $nodename . ": Node1 count = $count1   Node2 count  = $count2";

  if ($count1 != $count2  ){
      throw new Exception ($message);
  }

  if ( DEBUG == "on" ){
     echo $message . "\n";
  }

  return $count1;
}

/*
 * checks that the node names are equal
 */
function compareNodeName ($node1, $node2 )
{
  $name1 = $node1->getName ();
  $name2 = $node2->getName ();

  $message = "NAME Node1 name = $name1   Node2 name = $name2";

  if ($name1 != $name2 ){
    throw new Exception ($message);
  }

  if ( DEBUG == "on" ){
     echo $message . "\n";
  }

}

/*
 * Checks that the node values are equal
 */
function compareNodeValue ($node1, $node2 )
{

  $message = "VALUE " . $node1->getName() . ": Node1 value = " . (string)$node1 . "   Node2 value = " . (string) $node2;

  if ( (string)$node1 != (string)$node2 ){
    throw new Exception ($message);
  }

  if ( DEBUG == "on" )
  {
     echo $message . "\n";
  }
}

/*
 * checks that the node attributes are equal
 */
function compareNodeAttributes ($node1, $node2, $namespace )
{
  $attributes1 = $node1->attributes($namespace);
  $attributes2 = $node2->attributes($namespace);

  $count1 = count ($node1);
  $count2 = count ($node2);

  $message = "for node " . $node1->getName() . " in namespace " . $namespace . " - ";

  for ($i = 0; $i < $count1; $i += 1 ) {
      $att1      = $attributes1[$i];
      $att1_type = gettype($att1);
      $att1_name = null;
      $att2      = $attributes2[$i];
      $att2_type = gettype($att2);
      $att2_name = null;

      if ($att1 != null && $att1_type != "NULL" ) {
         $att1_name = $att1->getName();
      }

      if ($att2 != null && $att2_type != "NULL" ) {
         $att2_name = $att2->getName();
      }

      if ($att1_type != $att2_type || $att1_name != $att2_name ) {
          $message = $message . "Attribute $i not equal AttributeA = " . $att1_name . "  " . $att1 . " type " . $att1_type . " AttributeB = " . $att2_name . " " . $att2 . " type " . $att2_type;

          // test for a special case because we know schema location isn't copied to output
          if ($att1_name == "schemaLocation"  || $att1_name == "noNamespaceSchemaLocation" ) {
              //echo $message . "\n";
          } else {
              throw new Exception ($message);
          }
      }
  }

  if ($count1 != $count2 ) {
         $message = $message . "Attribute counts not equal count1 = " . $count1 . " count2 = " . $count2;
         throw new Exception ($message);
  }

  $attributecount = $count1;

  if ( DEBUG == "on" && $attributecount > 0){
    echo "ATTRIBUTES\n";
  }

  /*
  for ($i = 0; $i < $attributecount; $i += 1) {
    compareXMLAttribute ($attributes1[$i], $attributes2[$i]);
  }
  */

}

/*
 * checks that the node children are equal
 */
function compareNodeChildren ($node1, $node2, $namespace )
{
  // children
  $children1 = $node1->children($namespace);
  $children2 = $node2->children($namespace);

  $reason = "CHILD COUNT FOR NAMESPACE ";

  if ($namespace == NULL ){
    $reason = $reason . "DEFAULT";
  }
  else
  {
    $reason = $reason . $namespace;
  }

  $childcount = compareNodeCount ($node1->getName(), $children1, $children2, $reason);

  if ( DEBUG == "on" && $childcount > 0 ){
    echo "CHILDREN\n";
  }

  for ($i = 0; $i < $childcount; $i += 1) {
    compareXMLNode ($children1[$i], $children2[$i]);
  }
}

/*
 * compares two attributes for equality. SimpleXML treats attributes as
 * nodes though so I defer to the node comparison function
 */
function compareXMLAttribute ($node1, $node2 )
{
   compareXMLNode ($node1, $node2);
}

/*
 * compare two nodes for equality
 */
function compareXMLNode ($node1, $node2 )
{
  try {
    compareNodeCount($node1->getName(), $node1, $node2, "NODE COUNT");

    compareNodeName($node1, $node2);

    compareNodeValue($node1, $node2);

    // Should really detect the namespaces in the following tests automatically
    compareNodeAttributes ($node1, $node2, NULL);
    compareNodeAttributes ($node1, $node2, "http://www.w3.org/2001/XMLSchema-instance");

    compareNodeChildren($node1, $node2, NULL);

 // SimpleXML is doing something strange with namespaces
 // need to go to DOM I think
 //   compareNodeChildren($node1, $node2, "http://www.apache.org/tuscany/interop");
 //   compareNodeChildren($node1, $node2, "http://www.apache.org/tuscany/interop/import");

  } catch ( Exception $ex ) {
   if (( WARN_XSITYPE_DIFFERENCE == "on" ) &&
       ( ($node1->getName() == "type" ) ||
         ($node2->getName() == "type" ) ) )
   {
     echo "WARNING: The two XML files are different because: " . $ex->getMessage() . "\n";
   }
   else
   {
     throw $ex;
   }
  }
}

/*
 * load two XML files an see if they are equal
 */
function compareXMLfiles ($file1, $file2 )
{

  if (file_exists($file1)) {
   $xml1 = simplexml_load_file($file1, NULL, LIBXML_NOBLANKS);
  } else {
     $message = "Failed to open $file1";
     throw new Exception ($message);
  }

  if (file_exists($file2)) {
   $xml2 = simplexml_load_file($file2, NULL, LIBXML_NOBLANKS);
  } else {
     $message = "Failed to open $file2";
     throw new Exception ($message);
  }

  try
  {
    // traverse all elements, attributes and values and compare them

    //print_r ($xml1);
    //print_r ($xml2);

    compareXMLNode ($xml1, $xml2);
  }
  catch ( Exception $ex )
  {
    $message = "ERROR: The two XML files are different because: " . $ex->getMessage();
    throw new Exception ($message);
  }
}



/*****************************************************************************/

/*
 * the interop tests themselves
 */
function test1ReadAndWriteXML ($commondir, $testname )
{
   $xsdfile = $commondir . $testname . ".xsd";
   $infile  = $commondir . $testname . "-in.xml";
   $outfile = $commondir . $testname . "-php-out.xml";

   try {
     echo $testname . "- read and write XML \n";
     if ( DEBUG == "on" ) {
       echo "Read the schema\n";
     }
     $xmldas = SDO_DAS_XML::create($xsdfile);
     if ( DEBUG == "on" ) {
       echo $xmldas;
       echo "Read the input XML file\n";
     }
     $document = $xmldas->loadFile($infile);
     $root_data_object = $document->getRootDataObject();
     if ( DEBUG == "on" ) {
       print_r (  $root_data_object);
       echo "Write the output XML file\n";
     }
     $xmldas->saveFile($document, $outfile);
     if ( DEBUG == "on" ) {
       echo "New file has been written:\n";
     }

     compareXMLFiles ($infile, $outfile);

  } catch (Exception $e) {
     echo "Exception in PHP Interop test: ";
     echo $e->getMessage();
     echo "\n";
  }
}

function test4ReadAndWriteXSD ($commondir, $testname )
{
  try {
     echo $testname . " - read and write XSD\n";
     if ( DEBUG == "on" ) {
       echo "Read the schema\n";
     }
     $xmldas = SDO_DAS_XML::create($commondir . $testname . ".xsd");
     if ( DEBUG == "on" ) {
       echo "Read the XML files\n";
     }
     $document = $xmldas->loadFile($commondir . $testname . "-in.xml");
     $root_data_object = $document->getRootDataObject();

     if ( DEBUG == "on" ) {
       echo "Write the output XSD file\n";
     }
     $serializedod = serialize($root_data_object);

     if (!$handle = fopen($commondir . $testname . "-php-out.xsd", 'a')) {
           $message = "Cannot open file ". $testname . "-php-out.xsd";
           throw new Exception($message);
     }

     if (fwrite($handle, $serializedod) == FALSE) {
         $message = "Cannot write to file " . $testname . "-php-out.xsd";
         throw new Exception($message);
     }
     if ( DEBUG == "on" ) {
       echo "New file has been written:\n";
     }
  } catch (Exception $e) {
     echo "Exception in PHP Interop test: ";
     echo $e->getMessage();
     echo "\n";
  }
}

$commondir = dirname(__FILE__) . "/";

// uncomment this so that a debugger can be attached
//echo "Ready to start interop tests - hit any key"; $line = fgets(STDIN);

echo "Test 1 - Read and write XML \n";

test1ReadAndWriteXML ($commondir, "interop01");
test1ReadAndWriteXML ($commondir, "interop02");
test1ReadAndWriteXML ($commondir, "interop03");
test1ReadAndWriteXML ($commondir, "interop04");
test1ReadAndWriteXML ($commondir, "interop05");
echo ">>>>>interop05 - We don't test sdoJava:package attribute in PHP\n";
echo ">>>>>            but not sure why it can't read this file\n";
test1ReadAndWriteXML ($commondir, "interop06");
echo ">>>>>interop06 - fault reported as PECL BUG 7963\n";
test1ReadAndWriteXML ($commondir, "interop07");
test1ReadAndWriteXML ($commondir, "interop08");
test1ReadAndWriteXML ($commondir, "interop09");
test1ReadAndWriteXML ($commondir, "interop10");
test1ReadAndWriteXML ($commondir, "interop11");
test1ReadAndWriteXML ($commondir, "interop12");
test1ReadAndWriteXML ($commondir, "interop13");
test1ReadAndWriteXML ($commondir, "interop14");
test1ReadAndWriteXML ($commondir, "interop15");
test1ReadAndWriteXML ($commondir, "interop16");
echo ">>>>>interop16 - fault reported as PECL BUG 8689\n";
test1ReadAndWriteXML ($commondir, "interop17");
test1ReadAndWriteXML ($commondir, "interop18");
test1ReadAndWriteXML ($commondir, "interop19");
test1ReadAndWriteXML ($commondir, "interop20");
test1ReadAndWriteXML ($commondir, "interop21");
test1ReadAndWriteXML ($commondir, "interop22");
test1ReadAndWriteXML ($commondir, "interop23");
test1ReadAndWriteXML ($commondir, "interop24");
test1ReadAndWriteXML ($commondir, "interop25");
echo ">>>>>interop25 - fault reported as PECL BUG 8690\n";
test1ReadAndWriteXML ($commondir, "interop26");
test1ReadAndWriteXML ($commondir, "interop27");
echo ">>>>>interop27 - fault reported as PECL BUG 8690 also\n";
test1ReadAndWriteXML ($commondir, "interop28");
test1ReadAndWriteXML ($commondir, "interop29");
test1ReadAndWriteXML ($commondir, "interop30");
echo ">>>>>interop30 - fault reported as PECL BUG 8691 also\n";
test1ReadAndWriteXML ($commondir, "interop31");
test1ReadAndWriteXML ($commondir, "interop32");
echo ">>>>>interop32 - fault reported as PECL BUG 8692\n";
test1ReadAndWriteXML ($commondir, "interop33");
echo ">>>>>interop33 - fault reported as PECL BUG 8692\n";
echo ">>>>>interop33 - fault reported as PECL BUG 8693\n";
test1ReadAndWriteXML ($commondir, "interop34");
echo ">>>>>interop34 - fault reported as PECL BUG 8693\n";
test1ReadAndWriteXML ($commondir, "interop35");
test1ReadAndWriteXML ($commondir, "interop36");
test1ReadAndWriteXML ($commondir, "interop37");
test1ReadAndWriteXML ($commondir, "interop38");
//
//test1ReadAndWriteXML ($commondir, "interop39");
echo ">>>>>interop39 - commented out as it crashes\n";
echo ">>>>>interop39 - fault reported as PECL BUG 8693\n";
test1ReadAndWriteXML ($commondir, "interop40");
echo ">>>>>interop40 - fault reported as PECL BUG 8695\n";
test1ReadAndWriteXML ($commondir, "interop41");
test1ReadAndWriteXML ($commondir, "interop42");
test1ReadAndWriteXML ($commondir, "interop43");
test1ReadAndWriteXML ($commondir, "interop44");
echo ">>>>>interop44 - fault reported as PECL BUG 8692\n";
test1ReadAndWriteXML ($commondir, "interop45");
echo ">>>>>interop45 - Not Yet Supported\n";
test1ReadAndWriteXML ($commondir, "interop46");
//test1ReadAndWriteXML ($commondir, "interop47");
echo ">>>>>interop47 - not sure we are supporting specific change summary types yet\n";
test1ReadAndWriteXML ($commondir, "interop50");
echo ">>>>>interop50 - fault reported as PECL BUG 8697\n";

echo "Test 4 - Read and write XSD\n";
test4ReadAndWriteXSD ($commondir, "interop01");
test4ReadAndWriteXSD ($commondir, "interop02");
test4ReadAndWriteXSD ($commondir, "interop03");
test4ReadAndWriteXSD ($commondir, "interop04");
//test4ReadAndWriteXSD ($commondir, "interop05");
test4ReadAndWriteXSD ($commondir, "interop06");
test4ReadAndWriteXSD ($commondir, "interop07");
test4ReadAndWriteXSD ($commondir, "interop08");
test4ReadAndWriteXSD ($commondir, "interop09");
test4ReadAndWriteXSD ($commondir, "interop10");
test4ReadAndWriteXSD ($commondir, "interop11");
test4ReadAndWriteXSD ($commondir, "interop12");
test4ReadAndWriteXSD ($commondir, "interop13");
test4ReadAndWriteXSD ($commondir, "interop14");
test4ReadAndWriteXSD ($commondir, "interop15");
test4ReadAndWriteXSD ($commondir, "interop16");
test4ReadAndWriteXSD ($commondir, "interop17");
test4ReadAndWriteXSD ($commondir, "interop18");
test4ReadAndWriteXSD ($commondir, "interop19");
test4ReadAndWriteXSD ($commondir, "interop20");
test4ReadAndWriteXSD ($commondir, "interop21");
test4ReadAndWriteXSD ($commondir, "interop22");
test4ReadAndWriteXSD ($commondir, "interop23");
test4ReadAndWriteXSD ($commondir, "interop24");
test4ReadAndWriteXSD ($commondir, "interop25");
test4ReadAndWriteXSD ($commondir, "interop26");
test4ReadAndWriteXSD ($commondir, "interop27");
test4ReadAndWriteXSD ($commondir, "interop28");
test4ReadAndWriteXSD ($commondir, "interop29");
test4ReadAndWriteXSD ($commondir, "interop30");
test4ReadAndWriteXSD ($commondir, "interop31");
test4ReadAndWriteXSD ($commondir, "interop32");
test4ReadAndWriteXSD ($commondir, "interop33");
test4ReadAndWriteXSD ($commondir, "interop34");
test4ReadAndWriteXSD ($commondir, "interop35");
test4ReadAndWriteXSD ($commondir, "interop36");
test4ReadAndWriteXSD ($commondir, "interop37");
test4ReadAndWriteXSD ($commondir, "interop38");
test4ReadAndWriteXSD ($commondir, "interop39");
test4ReadAndWriteXSD ($commondir, "interop40");
test4ReadAndWriteXSD ($commondir, "interop41");
test4ReadAndWriteXSD ($commondir, "interop42");
test4ReadAndWriteXSD ($commondir, "interop43");
test4ReadAndWriteXSD ($commondir, "interop44");
test4ReadAndWriteXSD ($commondir, "interop45");
test4ReadAndWriteXSD ($commondir, "interop46");
test4ReadAndWriteXSD ($commondir, "interop47");
test4ReadAndWriteXSD ($commondir, "interop50");

// Used for testing the XML comparison functions
//compareXMLfiles ( "interop00-in.xml", "interop00-out.xml");
?>
