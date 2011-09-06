--TEST--
Call a local component
--FILE--
<?php
require_once "SCA/SCA.php";

$dir = dirname(__FILE__); // where am I?
$local_service      = SCA::getService("$dir/Component.php");
echo    $local_service->reverse('IBM');
?>

--EXPECT--
MBI
