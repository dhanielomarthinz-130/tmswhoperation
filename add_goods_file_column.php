<?php
require_once 'db_config.php';

try {
    // Add goods_file column to deliveries table
    $pdo->exec("ALTER TABLE deliveries ADD COLUMN goods_file TEXT AFTER surat_jalan_file");
    echo "Column 'goods_file' added successfully to 'deliveries' table.\n";
} catch (PDOException $e) {
    if (strpos($e->getMessage(), 'Duplicate column name') !== false) {
        echo "Column 'goods_file' already exists.\n";
    } else {
        echo "Error: " . $e->getMessage() . "\n";
    }
}
?>
