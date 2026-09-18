<?php
require_once 'db_config.php';

try {
    $pdo->exec("ALTER TABLE deliveries MODIFY COLUMN target_date DATETIME DEFAULT NULL");
    $pdo->exec("ALTER TABLE pickup_requests MODIFY COLUMN scheduled_date DATETIME DEFAULT NULL");
    echo "Migration successful: target_date and scheduled_date changed to DATETIME.";
} catch (Exception $e) {
    echo "Migration failed: " . $e->getMessage();
}
