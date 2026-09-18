<?php
// Test script to see if api.php has syntax errors or outputs anything unwanted
error_reporting(E_ALL);
ini_set('display_errors', 1);

try {
    include 'api.php';
    echo "\nAPI included successfully\n";
} catch (Throwable $e) {
    echo "\nError caught: " . $e->getMessage() . "\n";
    echo "In " . $e->getFile() . " on line " . $e->getLine() . "\n";
}
?>
