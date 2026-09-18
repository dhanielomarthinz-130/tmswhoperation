<?php
require_once 'db_config.php';

try {
    // 1. Change role column from ENUM to VARCHAR to support new roles
    $pdo->exec("ALTER TABLE users MODIFY COLUMN role VARCHAR(50) DEFAULT 'driver'");
    
    // 2. Fix existing users who might have empty roles due to ENUM restrictions
    // We search for users whose name or username suggests they should be request_pickup
    $pdo->exec("UPDATE users SET role = 'request_pickup' WHERE (username LIKE '%request%' OR name LIKE '%Request%') AND (role = '' OR role IS NULL)");
    
    echo "Database Migration Success:<br>";
    echo "- 'role' column changed to VARCHAR(50).<br>";
    echo "- Updated users with 'request_pickup' role based on name/username fallback.<br>";
    echo "<br>Please refresh the Manage Users page.";

} catch (PDOException $e) {
    echo "Migration Failed: " . $e->getMessage();
}
?>
