--TEST--
Call a local component
--INI--
display_errors=on
--FILE--
<?php
require "SCA/SCA.php";

$dir = dirname(__FILE__); // where am I?
$local_service      = SCA::getService("$dir/Component.php");
echo    $local_service->reverse('IBM');
?>

--EXPECT--
MBI
