<?php
/**
 * Finding out what you can about the document and document element
 * This can be quite hard to understand because there are four calls
 * Two calls are made against the document
 * Two calls are made against the root data object and its model
 * Because of the SDO-XML mapping rules and how the SDO model is derived
 * from the XML model, only three possible values can come back from these four calls.
 * Always, $document->getRootElementURI() == (type of root data object)->namespaceURI 
 * Essentiually, it all comes form the first few lines of the xsd:
 * <xsd:schema xmlns:xsd="http://www.w3.org/2001/XMLSchema"
 *   xmlns:letter="http://letterSchema"
 *   targetNamespace="http://letterSchema">
 *   <xsd:element name="letters" type="letter:FormLetter"/>
 */

$xmldas = SDO_DAS_XML::create("letter.xsd");
$document = $xmldas->loadFile("letter.xml");
$root_do = $document->getRootDataObject();

/**
 * Incidentally, the root data object type name and 
 * namespaceURI can also be obtained from the type via 
 * a reflection data object built on the root data object
 */
//$model_rdo = new SDO_Model_ReflectionDataObject($root_do);
//$type = $model_rdo->getType();
//echo "The type name of the root data object is " . $type->name . "\n";
//echo "The namespace URI of the root data object is " . $type->namespaceURI . "\n";

/**
 * The "root element name" is the element name of the document element
 * in this case 'letters'
 * This matches the 'name' attribute of the document element in the xsd and matches
 * the element name from the xml
 */
echo "The document element name is " . $document->getRootElementName() . "\n";
assert($document->getRootElementName() == 'letters'); // a property of the document

/**
 * The "root element URI" is the namespace part of the element name of the document element
 * in this case 'http://letterSchema' since 'letters' is in that namespace
 * This is taken from the xsd and matches the namespace picked up from the xml
 */
echo "The document element is in the namespace " . $document->getRootElementURI() . "\n";
assert($document->getRootElementURI() == 'http://letterSchema'); // a property of the document


/**
 * The type name is taken from the SDO model
 * The XML-SDO mapping rules make this either:
 *   The name of the complexType if there is one (in this case there is)
 *   The document element name if there no complexType
 * This is taken from the xsd 
 */
echo "The type name of the root data object is " . $root_do->getTypeName() . "\n";
assert($root_do->getTypeName() == 'FormLetter');  

/**
 * The type's namespaceURI is taken from the SDO model
 * The XML-SDO mapping rules ensure that this will always be the same as 
 * the namepace URI of the document element
 */
echo "The namespaceURI of the root data object is " . $root_do->getTypeNamespaceURI() . "\n";
assert($root_do->getTypeNamespaceURI() == 'http://letterSchema'); 

?>