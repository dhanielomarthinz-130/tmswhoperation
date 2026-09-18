<?php
$content = file_get_contents(__DIR__ . '/../assign_tasks.php');
echo "Length: " . strlen($content) . "\n";
echo "Null bytes check: " . (strpos($content, "\x00") === false ? "No null bytes" : "Has null bytes") . "\n";

$queries = ['Aksi', 'action', 'Action', 'th', 'role', 'superadmin', 'controller'];
foreach ($queries as $q) {
    $pos = strpos($content, $q);
    echo "Position of '$q': " . ($pos !== false ? $pos : "Not found") . "\n";
}
