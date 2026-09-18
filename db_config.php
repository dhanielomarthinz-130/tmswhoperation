<?php
// Database configuration
$host = 'sql202.infinityfree.com';
$db   = 'if0_38464190_tms_wh_operation';
$user = 'if0_38464190';
$pass = 'Dhaniel0';
$charset = 'utf8mb4';

$dsn = "mysql:host=$host;dbname=$db;charset=$charset";
$options = [
    PDO::ATTR_ERRMODE            => PDO::ERRMODE_EXCEPTION,
    PDO::ATTR_DEFAULT_FETCH_MODE => PDO::FETCH_ASSOC,
    PDO::ATTR_EMULATE_PREPARES   => false,
];

try {
     $pdo = new PDO($dsn, $user, $pass, $options);
     
     // Set Global Timezone to WIB (Asia/Jakarta)
     date_default_timezone_set('Asia/Jakarta');
     $pdo->exec("SET time_zone = '+07:00'");
} catch (\PDOException $e) {
     throw new \PDOException($e->getMessage(), (int)$e->getCode());
}
