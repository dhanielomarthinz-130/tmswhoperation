<?php
require_once 'db_config.php';

try {
    // 1. Add can_write column to role_permissions if it doesn't exist
    $pdo->exec("ALTER TABLE role_permissions ADD COLUMN IF NOT EXISTS can_write TINYINT(1) DEFAULT 0 AFTER can_access");
    
    // 2. Set can_write = 1 for admin and controller roles for existing permissions
    $pdo->exec("UPDATE role_permissions SET can_write = 1 WHERE role_key IN ('admin', 'controller')");
    
    echo "Database Fix Success:<br>";
    echo "- 'can_write' column added to role_permissions table.<br>";
    echo "- Write permissions granted to Admin and Controller roles.<br>";
    echo "<br><a href='manage_permissions.php'>Go to Permissions Management</a>";

} catch (PDOException $e) {
    echo "Fix Failed: " . $e->getMessage();
}
?>
