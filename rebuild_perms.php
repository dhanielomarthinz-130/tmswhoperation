<?php
require_once 'db_config.php';

try {
    // 1. Create backup of current data
    $stmt = $pdo->query("SELECT * FROM role_permissions");
    $data = $stmt->fetchAll(PDO::FETCH_ASSOC);
    
    // 2. Drop and recreate table with proper constraints
    echo "Recreating role_permissions table...<br>";
    $pdo->exec("DROP TABLE IF EXISTS role_permissions");
    $pdo->exec("CREATE TABLE role_permissions (
        id INT AUTO_INCREMENT PRIMARY KEY,
        role_key VARCHAR(50) NOT NULL,
        menu_key VARCHAR(50) NOT NULL,
        can_access TINYINT(1) DEFAULT 0,
        can_write TINYINT(1) DEFAULT 0,
        UNIQUE KEY role_menu_unique (role_key, menu_key)
    ) ENGINE=InnoDB");
    
    // 3. Restore data (ignoring duplicates)
    echo "Restoring data...<br>";
    $stmt = $pdo->prepare("INSERT IGNORE INTO role_permissions (role_key, menu_key, can_access, can_write) VALUES (?, ?, ?, ?)");
    foreach ($data as $r) {
        $stmt->execute([$r['role_key'], $r['menu_key'], $r['can_access'], $r['can_write']]);
    }
    
    // 4. Ensure admin and controller have full access to 'roles', 'expedisi', and 'database'
    echo "Enforcing critical permissions...<br>";
    $critical = [
        ['controller', 'roles', 1, 1],
        ['controller', 'expedisi', 1, 1],
        ['controller', 'database', 1, 1],
        ['admin', 'roles', 1, 1],
        ['admin', 'expedisi', 1, 1],
        ['admin', 'database', 1, 1]
    ];
    $stmt = $pdo->prepare("INSERT INTO role_permissions (role_key, menu_key, can_access, can_write) VALUES (?, ?, ?, ?) 
            ON DUPLICATE KEY UPDATE can_access=1, can_write=1");
    foreach ($critical as $c) {
        $stmt->execute($c);
    }
    
    echo "Done! Table rebuilt and permissions enforced.";
    
} catch (Exception $e) {
    echo "ERROR: " . $e->getMessage();
}
?>
