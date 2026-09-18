<?php
require 'db_config.php';

$updates = [
    'admin' => 'Admin (WH Operation)',
    'driver' => 'Driver (WH Operation)',
    'management' => 'Management (WH Operation)',
    'admin_request_pickup' => 'Admin Request Pickup (HO)',
    'request_pickup' => 'Request Pickup (HO)'
];

foreach ($updates as $key => $name) {
    $stmt = $pdo->prepare("UPDATE roles SET role_name = ? WHERE role_key = ?");
    $stmt->execute([$name, $key]);
    echo "Updated $key to $name\n";
}
