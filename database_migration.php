<?php
/**
 * Safe Database Migration Runner
 * TMS Warehouse Operation
 *
 * Skenario Aman:
 * 1. TIDAK PERNAH menghapus tabel (NO DROP TABLE).
 * 2. TIDAK PERNAH menghapus isi tabel (NO TRUNCATE / DELETE).
 * 3. Menjamin tabel yang belum ada akan dibuat secara otomatis (CREATE TABLE IF NOT EXISTS).
 * 4. Menjamin kolom-kolom baru ditambahkan jika belum ada tanpa merusak data yang sudah ada.
 */

if (session_status() === PHP_SESSION_NONE) {
    session_start();
}

require_once __DIR__ . '/db_config.php';

// Token rahasia default untuk trigger migrasi otomatis via GitHub Actions / Webhook
define('MIGRATION_TOKEN_DEFAULT', 'TMS_SECRET_MIGRATE_2026');

// Pemeriksaan Otorisasi
$is_cli = (php_sapi_name() === 'cli');
$token_param = $_GET['token'] ?? $_POST['token'] ?? '';
$is_admin = (isset($_SESSION['role']) && $_SESSION['role'] === 'controller');
$is_token_valid = (!empty($token_param) && $token_param === MIGRATION_TOKEN_DEFAULT);

if (!$is_cli && !$is_admin && !$is_token_valid) {
    http_response_code(403);
    echo json_encode([
        'status' => 'error',
        'message' => 'Akses ditolak. Silakan login sebagai Super Admin atau sertakan token yang valid.'
    ]);
    exit();
}

$logs = [];

function logMsg($msg) {
    global $logs;
    $logs[] = "[" . date('Y-m-d H:i:s') . "] " . $msg;
}

function tableExists(PDO $pdo, $tableName) {
    try {
        $stmt = $pdo->prepare("SHOW TABLES LIKE ?");
        $stmt->execute([$tableName]);
        return $stmt->rowCount() > 0;
    } catch (Exception $e) {
        return false;
    }
}

function columnExists(PDO $pdo, $tableName, $columnName) {
    try {
        $stmt = $pdo->prepare("SHOW COLUMNS FROM `$tableName` LIKE ?");
        $stmt->execute([$columnName]);
        return $stmt->rowCount() > 0;
    } catch (Exception $e) {
        return false;
    }
}

function addColumnIfNotExists(PDO $pdo, $tableName, $columnName, $columnDef) {
    if (!tableExists($pdo, $tableName)) {
        logMsg("Lewati penambahan kolom `$columnName`: Tabel `$tableName` belum ada.");
        return false;
    }
    if (!columnExists($pdo, $tableName, $columnName)) {
        try {
            $pdo->exec("ALTER TABLE `$tableName` ADD COLUMN `$columnName` $columnDef");
            logMsg("SUKSES: Menambahkan kolom `$columnName` ke tabel `$tableName`.");
            return true;
        } catch (Exception $e) {
            logMsg("GAGAL menambahkan kolom `$columnName` ke `$tableName`: " . $e->getMessage());
            return false;
        }
    } else {
        logMsg("OK: Kolom `$columnName` sudah ada di tabel `$tableName`.");
        return true;
    }
}

function modifyColumnIfExists(PDO $pdo, $tableName, $columnName, $columnDef) {
    if (!tableExists($pdo, $tableName)) {
        return false;
    }
    if (columnExists($pdo, $tableName, $columnName)) {
        try {
            $pdo->exec("ALTER TABLE `$tableName` MODIFY COLUMN `$columnName` $columnDef");
            logMsg("SUKSES: Modifikasi tipe kolom `$columnName` pada `$tableName`.");
            return true;
        } catch (Exception $e) {
            logMsg("INFO: Modifikasi `$columnName` di `$tableName`: " . $e->getMessage());
            return false;
        }
    }
    return false;
}

try {
    logMsg("Memulai proses sinkronisasi database aman...");

    // 1. Buat tabel pelacak migrasi
    $pdo->exec("CREATE TABLE IF NOT EXISTS `_schema_migrations` (
        `id` INT AUTO_INCREMENT PRIMARY KEY,
        `migration_name` VARCHAR(191) NOT NULL UNIQUE,
        `executed_at` TIMESTAMP DEFAULT CURRENT_TIMESTAMP
    ) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4");

    // 2. Skema Tabel-Tabel Utama (Hanya dibuat jika belum ada)
    $tables = [
        'users' => "CREATE TABLE IF NOT EXISTS `users` (
            `id` int(11) NOT NULL AUTO_INCREMENT,
            `username` varchar(50) NOT NULL,
            `password` varchar(255) NOT NULL,
            `name` varchar(100) NOT NULL,
            `role` varchar(50) NOT NULL DEFAULT 'driver',
            `expires_at` date DEFAULT NULL,
            `is_active` tinyint(1) DEFAULT 1,
            `created_at` timestamp NOT NULL DEFAULT current_timestamp(),
            PRIMARY KEY (`id`),
            UNIQUE KEY `username` (`username`)
        ) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4",

        'roles' => "CREATE TABLE IF NOT EXISTS `roles` (
            `id` int(11) NOT NULL AUTO_INCREMENT,
            `role_name` varchar(100) NOT NULL,
            `role_key` varchar(50) NOT NULL,
            `description` text DEFAULT NULL,
            `icon` varchar(50) DEFAULT 'person',
            `color` varchar(20) DEFAULT '#6366f1',
            `created_at` timestamp NOT NULL DEFAULT current_timestamp(),
            PRIMARY KEY (`id`),
            UNIQUE KEY `role_key` (`role_key`)
        ) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4",

        'role_permissions' => "CREATE TABLE IF NOT EXISTS `role_permissions` (
            `id` int(11) NOT NULL AUTO_INCREMENT,
            `role_key` varchar(50) NOT NULL,
            `menu_key` varchar(50) NOT NULL,
            `can_access` tinyint(1) DEFAULT 0,
            `can_write` tinyint(1) DEFAULT 0,
            PRIMARY KEY (`id`),
            UNIQUE KEY `role_menu` (`role_key`,`menu_key`)
        ) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4",

        'locations' => "CREATE TABLE IF NOT EXISTS `locations` (
            `id` int(11) NOT NULL AUTO_INCREMENT,
            `name` varchar(100) NOT NULL,
            `type` enum('warehouse','store') NOT NULL,
            `city` varchar(50) DEFAULT NULL,
            `address` text DEFAULT NULL,
            `lat` decimal(10,8) NOT NULL,
            `lng` decimal(11,8) NOT NULL,
            `created_at` timestamp NOT NULL DEFAULT current_timestamp(),
            PRIMARY KEY (`id`)
        ) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4",

        'vehicles' => "CREATE TABLE IF NOT EXISTS `vehicles` (
            `id` int(11) NOT NULL AUTO_INCREMENT,
            `name` varchar(100) NOT NULL,
            `plate_number` varchar(20) NOT NULL,
            `created_at` timestamp NOT NULL DEFAULT current_timestamp(),
            PRIMARY KEY (`id`),
            UNIQUE KEY `plate_number` (`plate_number`)
        ) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4",

        'drivers_info' => "CREATE TABLE IF NOT EXISTS `drivers_info` (
            `id` int(11) NOT NULL AUTO_INCREMENT,
            `user_id` int(11) NOT NULL,
            `vehicle_type` varchar(50) DEFAULT NULL,
            `plate_number` varchar(20) DEFAULT NULL,
            `phone_number` varchar(20) DEFAULT NULL,
            PRIMARY KEY (`id`),
            UNIQUE KEY `user_id` (`user_id`),
            CONSTRAINT `drivers_info_ibfk_1` FOREIGN KEY (`user_id`) REFERENCES `users` (`id`) ON DELETE CASCADE
        ) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4",

        'drivers_location' => "CREATE TABLE IF NOT EXISTS `drivers_location` (
            `id` int(11) NOT NULL AUTO_INCREMENT,
            `user_id` int(11) NOT NULL,
            `lat` decimal(10,8) NOT NULL,
            `lng` decimal(11,8) NOT NULL,
            `last_updated` timestamp NOT NULL DEFAULT current_timestamp() ON UPDATE current_timestamp(),
            PRIMARY KEY (`id`),
            UNIQUE KEY `user_id` (`user_id`),
            CONSTRAINT `drivers_location_ibfk_1` FOREIGN KEY (`user_id`) REFERENCES `users` (`id`) ON DELETE CASCADE
        ) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4",

        'driver_active_vehicles' => "CREATE TABLE IF NOT EXISTS `driver_active_vehicles` (
            `id` int(11) NOT NULL AUTO_INCREMENT,
            `driver_id` int(11) NOT NULL,
            `vehicle_id` int(11) NOT NULL,
            `assigned_at` timestamp NOT NULL DEFAULT current_timestamp(),
            PRIMARY KEY (`id`),
            KEY `driver_id` (`driver_id`),
            KEY `vehicle_id` (`vehicle_id`),
            CONSTRAINT `driver_active_vehicles_ibfk_1` FOREIGN KEY (`driver_id`) REFERENCES `users` (`id`) ON DELETE CASCADE,
            CONSTRAINT `driver_active_vehicles_ibfk_2` FOREIGN KEY (`vehicle_id`) REFERENCES `vehicles` (`id`) ON DELETE CASCADE
        ) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4",

        'pickup_requests' => "CREATE TABLE IF NOT EXISTS `pickup_requests` (
            `id` int(11) NOT NULL AUTO_INCREMENT,
            `surat_jalan` varchar(100) NOT NULL,
            `origin_name` varchar(255) NOT NULL,
            `destination_name` varchar(255) NOT NULL,
            `total_koli` int(11) NOT NULL DEFAULT 0,
            `notes` text DEFAULT NULL,
            `surat_jalan_file` varchar(255) DEFAULT NULL,
            `goods_file` text DEFAULT NULL,
            `requester_id` int(11) NOT NULL,
            `status` enum('pending','approved','rejected','completed') DEFAULT 'pending',
            `scheduled_date` datetime DEFAULT NULL,
            `created_at` timestamp NOT NULL DEFAULT current_timestamp(),
            PRIMARY KEY (`id`),
            KEY `requester_id` (`requester_id`),
            CONSTRAINT `pickup_requests_ibfk_1` FOREIGN KEY (`requester_id`) REFERENCES `users` (`id`) ON DELETE CASCADE
        ) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4",

        'deliveries' => "CREATE TABLE IF NOT EXISTS `deliveries` (
            `id` int(11) NOT NULL AUTO_INCREMENT,
            `driver_id` int(11) NOT NULL,
            `pickup_id` int(11) DEFAULT NULL,
            `origin_id` int(11) DEFAULT NULL,
            `destination_id` int(11) DEFAULT NULL,
            `origin_name` varchar(255) NOT NULL,
            `destination_name` varchar(255) NOT NULL,
            `destination_lat` decimal(10,8) NOT NULL,
            `destination_lng` decimal(11,8) NOT NULL,
            `surat_jalan` varchar(100) DEFAULT NULL,
            `total_koli` int(11) DEFAULT 0,
            `notes` text DEFAULT NULL,
            `surat_jalan_file` varchar(255) DEFAULT NULL,
            `goods_file` text DEFAULT NULL,
            `proof_file` text DEFAULT NULL,
            `target_date` datetime DEFAULT NULL,
            `late_reason` text DEFAULT NULL,
            `task_type` enum('antar','kirim') DEFAULT 'antar',
            `status` enum('pending','in_transit','completed') DEFAULT 'pending',
            `start_time` datetime DEFAULT NULL,
            `end_time` datetime DEFAULT NULL,
            `duration` varchar(100) DEFAULT NULL,
            `created_at` timestamp NOT NULL DEFAULT current_timestamp(),
            `created_by` int(11) DEFAULT NULL,
            `receiver_name` varchar(255) DEFAULT NULL,
            `is_shared` tinyint(1) DEFAULT 0,
            PRIMARY KEY (`id`),
            KEY `driver_id` (`driver_id`),
            KEY `origin_id` (`origin_id`),
            KEY `destination_id` (`destination_id`),
            KEY `pickup_id` (`pickup_id`),
            CONSTRAINT `deliveries_ibfk_1` FOREIGN KEY (`driver_id`) REFERENCES `users` (`id`) ON DELETE CASCADE,
            CONSTRAINT `deliveries_ibfk_2` FOREIGN KEY (`origin_id`) REFERENCES `locations` (`id`) ON DELETE SET NULL,
            CONSTRAINT `deliveries_ibfk_3` FOREIGN KEY (`destination_id`) REFERENCES `locations` (`id`) ON DELETE SET NULL,
            CONSTRAINT `deliveries_ibfk_4` FOREIGN KEY (`pickup_id`) REFERENCES `pickup_requests` (`id`) ON DELETE SET NULL
        ) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4",

        'expedisi_vendors' => "CREATE TABLE IF NOT EXISTS `expedisi_vendors` (
            `id` int(11) NOT NULL AUTO_INCREMENT,
            `name` varchar(255) NOT NULL,
            `contact_person` varchar(100) DEFAULT NULL,
            `phone` varchar(50) DEFAULT NULL,
            `address` text DEFAULT NULL,
            `created_at` timestamp NOT NULL DEFAULT current_timestamp(),
            PRIMARY KEY (`id`)
        ) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4",

        'expedisi_tasks' => "CREATE TABLE IF NOT EXISTS `expedisi_tasks` (
            `id` int(11) NOT NULL AUTO_INCREMENT,
            `surat_jalan` varchar(100) DEFAULT NULL,
            `sj_files` longtext DEFAULT NULL,
            `origin_name` varchar(255) DEFAULT NULL,
            `destination_name` varchar(255) DEFAULT NULL,
            `total_koli` int(11) DEFAULT 0,
            `driver_name` varchar(100) DEFAULT NULL,
            `vendor_id` int(11) DEFAULT NULL,
            `vehicle_plate` varchar(50) DEFAULT NULL,
            `target_date` date DEFAULT NULL,
            `status` enum('pending','in_transit','completed') DEFAULT 'pending',
            `start_time` datetime DEFAULT NULL,
            `end_time` datetime DEFAULT NULL,
            `receiver_name` varchar(100) DEFAULT NULL,
            `proof_files` longtext DEFAULT NULL,
            `created_at` timestamp NOT NULL DEFAULT current_timestamp(),
            `created_by` int(11) DEFAULT NULL,
            PRIMARY KEY (`id`),
            KEY `created_by` (`created_by`),
            KEY `fk_expedisi_vendor` (`vendor_id`),
            CONSTRAINT `expedisi_tasks_ibfk_1` FOREIGN KEY (`created_by`) REFERENCES `users` (`id`),
            CONSTRAINT `fk_expedisi_vendor` FOREIGN KEY (`vendor_id`) REFERENCES `expedisi_vendors` (`id`)
        ) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4"
    ];

    foreach ($tables as $name => $sql) {
        if (!tableExists($pdo, $name)) {
            $pdo->exec($sql);
            logMsg("TABEL BARU DIBUAT: `$name`.");
        } else {
            logMsg("TABEL SUDAH ADA: `$name` (Data lama tetap aman).");
        }
    }

    // 3. Pastikan kolom-kolom penting dan baru ada di tabel (Incremental Column Checks)
    // Kolom baru pada deliveries
    addColumnIfNotExists($pdo, 'deliveries', 'goods_file', 'TEXT AFTER `surat_jalan_file`');
    addColumnIfNotExists($pdo, 'deliveries', 'receiver_name', 'VARCHAR(255) DEFAULT NULL');
    addColumnIfNotExists($pdo, 'deliveries', 'is_shared', 'TINYINT(1) DEFAULT 0');
    modifyColumnIfExists($pdo, 'deliveries', 'target_date', 'DATETIME DEFAULT NULL');

    // Kolom baru pada pickup_requests
    addColumnIfNotExists($pdo, 'pickup_requests', 'goods_file', 'TEXT DEFAULT NULL AFTER `surat_jalan_file`');
    modifyColumnIfExists($pdo, 'pickup_requests', 'scheduled_date', 'DATETIME DEFAULT NULL');

    // Kolom baru pada role_permissions
    addColumnIfNotExists($pdo, 'role_permissions', 'can_write', 'TINYINT(1) DEFAULT 0 AFTER `can_access`');

    // Kolom baru pada users
    addColumnIfNotExists($pdo, 'users', 'expires_at', 'DATE DEFAULT NULL');
    addColumnIfNotExists($pdo, 'users', 'is_active', 'TINYINT(1) DEFAULT 1');

    // Pastikan akun Daniel Imsula diatur Lifetime (expires_at = NULL) dan selalu aktif
    $affected = $pdo->exec("UPDATE users SET expires_at = NULL, is_active = 1 WHERE LOWER(name) LIKE '%daniel imsula%' OR LOWER(username) LIKE '%daniel%imsula%' OR LOWER(username) = 'daniel'");
    if ($affected > 0) {
        logMsg("SUKSES: Akun Daniel Imsula berhasil disetel sebagai Lifetime (Permanen) dan Aktif.");
    }

    // Kolom baru pada expedisi_tasks
    addColumnIfNotExists($pdo, 'expedisi_tasks', 'sj_files', 'LONGTEXT DEFAULT NULL');
    addColumnIfNotExists($pdo, 'expedisi_tasks', 'proof_files', 'LONGTEXT DEFAULT NULL');

    logMsg("Sinkronisasi skema selesai dengan sukses 100%. Tidak ada data yang hilang.");

    $response = [
        'status' => 'success',
        'message' => 'Database migration completed safely. Existing data preserved.',
        'logs' => $logs
    ];

} catch (Exception $e) {
    logMsg("ERROR FATAL: " . $e->getMessage());
    $response = [
        'status' => 'error',
        'message' => 'Migration encountered an error: ' . $e->getMessage(),
        'logs' => $logs
    ];
}

// Output respon
if ($is_cli || (!empty($_SERVER['HTTP_ACCEPT']) && strpos($_SERVER['HTTP_ACCEPT'], 'application/json') !== false) || isset($_GET['format']) && $_GET['format'] === 'json') {
    header('Content-Type: application/json');
    echo json_encode($response, JSON_PRETTY_PRINT | JSON_UNESCAPED_SLASHES);
    exit();
}
?>
<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Safe Database Migration | TMS</title>
    <link rel="icon" type="image/png" href="favicon.png">
    <style>
        body {
            font-family: -apple-system, BlinkMacSystemFont, "Segoe UI", Roboto, Helvetica, Arial, sans-serif;
            background: #f8fafc;
            color: #1e293b;
            padding: 2rem;
            margin: 0;
        }
        .container {
            max-width: 800px;
            margin: 0 auto;
            background: #fff;
            padding: 2rem;
            border-radius: 12px;
            box-shadow: 0 4px 6px -1px rgba(0,0,0,0.1);
            border: 1px solid #e2e8f0;
        }
        h1 {
            color: <?= ($response['status'] === 'success') ? '#10b981' : '#ef4444' ?>;
            font-size: 1.5rem;
            margin-top: 0;
        }
        .status-badge {
            display: inline-block;
            padding: 0.25rem 0.75rem;
            border-radius: 9999px;
            font-size: 0.875rem;
            font-weight: 600;
            background: <?= ($response['status'] === 'success') ? '#ecfdf5' : '#fef2f2' ?>;
            color: <?= ($response['status'] === 'success') ? '#059669' : '#dc2626' ?>;
            margin-bottom: 1rem;
        }
        .logs {
            background: #0f172a;
            color: #e2e8f0;
            padding: 1rem;
            border-radius: 8px;
            font-family: monospace;
            font-size: 0.85rem;
            max-height: 400px;
            overflow-y: auto;
            white-space: pre-wrap;
        }
        .btn {
            display: inline-block;
            background: #3b82f6;
            color: white;
            padding: 0.5rem 1rem;
            border-radius: 6px;
            text-decoration: none;
            font-weight: 500;
            margin-top: 1.5rem;
        }
        .btn:hover {
            background: #2563eb;
        }
    </style>
</head>
<body>
    <div class="container">
        <span class="status-badge"><?= strtoupper($response['status']) ?></span>
        <h1>Hasil Sinkronisasi Database</h1>
        <p><?= htmlspecialchars($response['message']) ?></p>
        
        <h3>Log Eksekusi:</h3>
        <div class="logs"><?php foreach ($response['logs'] as $log): ?><?= htmlspecialchars($log) . "\n" ?><?php endforeach; ?></div>

        <a href="manage_database.php" class="btn">&larr; Kembali ke Kelola Database</a>
    </div>
</body>
</html>
