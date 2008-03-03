--TEST--
SDO_DAS_XML test for escaping XML output stringss
--INI--
display_errors=off
--SKIPIF--
<?php

  if (!extension_loaded('sdo'))
      print 'skip - sdo extension not loaded';
?>
--FILE--
<?php

$schema = <<<END_SCHEMA
<schema xmlns="http://www.w3.org/2001/XMLSchema">
<element name="topType">
<complexType>
<sequence>
<element name="values" type="String" minOccurs="0" maxOccurs="unbounded"/>
</sequence>
</complexType>
</element>
</schema>
END_SCHEMA;

$dirname = dirname($_SERVER['SCRIPT_FILENAME']);
$xsd_file = "${dirname}/TEMP.xsd";
file_put_contents($xsd_file, $schema);
$xmldas = SDO_DAS_XML::create($xsd_file);
unlink($xsd_file);

$xdoc = $xmldas->createDocument("topType");
$root = $xdoc->getRootDataObject();
$root->values[] = "";
$root->values[] = "Value0";
$root->values[] = "Val<ue1";
$root->values[] = "Val&lt;ue2";
$root->values[] = "Val&ue3";
$root->values[] = "Val&amp;ue4";
$root->values[] = "Val&&amp;ue5";
$root->values[] = "&amp;";
$root->values[] = "&&amp;";
$root->values[] = "&";
$root->values[] = "&amp";
$root->values[] = "<>'\"&";
$root->values[] = "A<B>C'D\"E&F";
$root->values[] = "<![CDATA[]]>";
$root->values[] = "a<![CDATA[b]]>c";
$root->values[] = "a<![CDATA[b]]";
$root->values[] = "a<![CDATA[b]";
$root->values[] = "a<![CDATA[b";
$root->values[] = "a<![CDATA[b]]>c<![CDATA[d]]>e";
$root->values[] = "a<![CDATA[b<![CDATA[c<![CDATA[d]]>e";
$root->values[] = "a<![CDATA[b&gt;]]&lt;";
$root->values[] = "a < b & '0'<![CDATA[a < b & '0']]>a < b & '0'";
$root->values[] = "a < b & '0'<![CDATA[a < b & '0']]>a < b & '0'<![CDATA[a < b & '0']]>a < b & '0'";
$root->values[] = "a < b & '0'<![CDATA[a < b & '0'<![CDATA[a < b & '0'<![CDATA[a < b & '0']]>a < b & '0'";

echo preg_replace("/([^\]]>)\s*(<[^!])/", "$1\n$2", $xdoc);
?>
--EXPECT--
<?xml version="1.0" encoding="UTF-8"?>
<topType xsi:type="topType" xmlns:xsi="http://www.w3.org/2001/XMLSchema-instance">
<values>
</values>
<values>Value0</values>
<values>Val&lt;ue1</values>
<values>Val&lt;ue2</values>
<values>Val&amp;ue3</values>
<values>Val&amp;ue4</values>
<values>Val&amp;&amp;ue5</values>
<values>&amp;</values>
<values>&amp;&amp;</values>
<values>&amp;</values>
<values>&amp;amp</values>
<values>&lt;&gt;&apos;&quot;&amp;</values>
<values>A&lt;B&gt;C&apos;D&quot;E&amp;F</values>
<values><![CDATA[]]></values>
<values>a<![CDATA[b]]>c</values>
<values>a<![CDATA[b]]</values>
<values>a<![CDATA[b]</values>
<values>a<![CDATA[b</values>
<values>a<![CDATA[b]]>c<![CDATA[d]]>e</values>
<values>a<![CDATA[b<![CDATA[c<![CDATA[d]]>e</values>
<values>a<![CDATA[b&gt;]]&lt;</values>
<values>a &lt; b &amp; &apos;0&apos;<![CDATA[a < b & '0']]>a &lt; b &amp; &apos;0&apos;</values>
<values>a &lt; b &amp; &apos;0&apos;<![CDATA[a < b & '0']]>a &lt; b &amp; &apos;0&apos;<![CDATA[a < b & '0']]>a &lt; b &amp; &apos;0&apos;</values>
<values>a &lt; b &amp; &apos;0&apos;<![CDATA[a < b & '0'<![CDATA[a < b & '0'<![CDATA[a < b & '0']]>a &lt; b &amp; &apos;0&apos;</values>
</topType>
