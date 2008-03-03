--TEST--
SCA - test for WSDL which imports schema with no schemaLocation attribute
--INI--
display_errors=on

--SKIPIF--
<?php
if (phpversion('sdo') <= '1.2.4')
    echo "skip test requires version > 1.2.4"; // need fix for bug13296
else {
  define('URI', 'http://api.urbandictionary.com/soap');
  if (@fopen(URI, 'r') === false)
      print 'skip - ' . URI . ' is unreachable';
}
?>
--FILE--
<?php
require 'SCA/SCA.php';

try {
    $service = SCA::getService('http://api.urbandictionary.com/soap?wsdl');
    $result = $service->get_daily_definition();
    echo strlen($result->word) > 0;
} catch (Exception $e) {
    echo 0;
}

?>
--EXPECT--
1
