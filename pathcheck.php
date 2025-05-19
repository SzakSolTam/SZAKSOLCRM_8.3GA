<?php
require 'config.inc.php';
$test = realpath(__DIR__ . '/index.php');
$root = realpath($root_directory);
echo "root_directory : $root\n";
echo "index realpath : $test\n";
echo "strpos match?  : ", (strpos($test, $root) === 0 ? 'YES' : 'NO'), "\n";
?>
