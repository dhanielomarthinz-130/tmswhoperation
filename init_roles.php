<?php
require_once 'db_config.php';

try {
    // 1. Create roles table
    $pdo->exec("CREATE TABLE IF NOT EXISTS roles (
        id INT AUTO_INCREMENT PRIMARY KEY,
        role_name VARCHAR(100) NOT NULL,
        role_key VARCHAR(50) NOT NULL UNIQUE,
        description TEXT,
        icon VARCHAR(50) DEFAULT 'person',
        color VARCHAR(20) DEFAULT '#6366f1',
        created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP
    )");

    // 2. Insert default roles
    $defaultRoles = [
        ['Super Admin', 'controller', 'Akses penuh seluruh sistem', 'star', '#f59e0b'],
        ['Admin', 'admin', 'Manajemen operasional harian', 'shield', '#6366f1'],
        ['Admin Request Pickup', 'admin_request_pickup', 'Manajemen data pickup gudang', 'package_2', '#10b981'],
        ['Request Pickup', 'request_pickup', 'User mobile untuk input pickup', 'smartphone', '#e11d48'],
        ['Driver', 'driver', 'User mobile untuk pengiriman', 'local_shipping', '#0ea5e9']
    ];

    $stmt = $pdo->prepare("INSERT IGNORE INTO roles (role_name, role_key, description, icon, color) VALUES (?, ?, ?, ?, ?)");
    foreach ($defaultRoles as $role) {
        $stmt->execute($role);
    }

    // 3. Ensure users table role column is VARCHAR
    $pdo->exec("ALTER TABLE users MODIFY COLUMN role VARCHAR(50) NOT NULL DEFAULT 'driver'");

    echo "Role System Initialized Successfully.";
} catch (PDOException $e) {
    echo "Error: " . $e->getMessage();
}
?>
