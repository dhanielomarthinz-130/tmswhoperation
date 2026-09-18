<?php
if (session_status() === PHP_SESSION_NONE) {
    session_start();
}

require_once 'db_config.php';

// Session Timeout Implementation (1 Hour)
$timeout_duration = 3600; // 1 hour in seconds

if (isset($_SESSION['user_id'])) {
    $is_api = (basename($_SERVER['SCRIPT_NAME']) === 'api.php');

    // 1. Session Timeout Check
    if (isset($_SESSION['last_activity'])) {
        $elapsed_time = time() - $_SESSION['last_activity'];
        if ($elapsed_time > $timeout_duration) {
            session_unset();
            session_destroy();
            if ($is_api) {
                http_response_code(401);
                die(json_encode(['success' => false, 'error' => 'Sesi berakhir karena tidak aktif. Silakan login kembali.']));
            } else {
                header("Location: login.php?timeout=1");
                exit();
            }
        }
    }
    $_SESSION['last_activity'] = time();

    // 2. Real-time Account Status & Expiry Check
    try {
        if (isset($pdo)) {
            $stmtUser = $pdo->prepare("SELECT is_active, expires_at FROM users WHERE id = ?");
            $stmtUser->execute([$_SESSION['user_id']]);
            $userCheck = $stmtUser->fetch(PDO::FETCH_ASSOC);

            if (!$userCheck) {
                // User deleted
                session_unset();
                session_destroy();
                if ($is_api) {
                    http_response_code(401);
                    die(json_encode(['success' => false, 'error' => 'Akun tidak ditemukan. Silakan login kembali.']));
                } else {
                    header("Location: login.php");
                    exit();
                }
            }

            if (!$userCheck['is_active']) {
                // Account deactivated
                session_unset();
                session_destroy();
                if ($is_api) {
                    http_response_code(401);
                    die(json_encode(['success' => false, 'error' => 'Akun Anda dinonaktifkan. Silakan hubungi Admin.']));
                } else {
                    header("Location: login.php?inactive=1");
                    exit();
                }
            }

            if ($userCheck['expires_at'] && strtotime($userCheck['expires_at']) < time()) {
                // Account expired
                session_unset();
                session_destroy();
                if ($is_api) {
                    http_response_code(401);
                    die(json_encode(['success' => false, 'error' => 'Akun Anda sudah kadaluarsa. Silakan hubungi Admin.']));
                } else {
                    header("Location: login.php?expired=1");
                    exit();
                }
            }

            // 3. Real-time System Maintenance Mode Check
            try {
                if (isSystemMaintenanceMode($pdo)) {
                    $sessName = $_SESSION['name'] ?? '';
                    $sessUsername = $_SESSION['username'] ?? '';
                    if (!isDanielImsulaUser($sessName, $sessUsername)) {
                        session_unset();
                        session_destroy();
                        if ($is_api) {
                            http_response_code(503);
                            die(json_encode(['success' => false, 'error' => 'Sistem sedang dalam Mode Pemeliharaan (Maintenance). Hanya Teknisi (Daniel Imsula) yang diizinkan mengakses.']));
                        } else {
                            header("Location: login.php?maintenance=1");
                            exit();
                        }
                    }
                }
            } catch (Exception $exMaint) {}

            // Auto-ensure default database permissions exist in role_permissions
            try {
                $pdo->exec("INSERT INTO role_permissions (role_key, menu_key, can_access, can_write) VALUES ('controller', 'database', 1, 1), ('admin', 'database', 1, 1) ON DUPLICATE KEY UPDATE can_access = 1, can_write = 1");
            } catch (Exception $ex) {}
        }
    } catch (PDOException $e) {
        // Silently continue if database is temporarily unavailable during session check
    }
}

// Function to check if system is in maintenance mode
function isSystemMaintenanceMode($pdo) {
    if (!$pdo) return false;
    try {
        $stmt = $pdo->query("SELECT setting_value FROM system_settings WHERE setting_key = 'maintenance_mode'");
        $val = $stmt->fetchColumn();
        return ($val === '1' || $val === 1);
    } catch (Exception $e) {
        return false;
    }
}

// Function to check if a user is Daniel Imsula (Teknisi Master)
function isDanielImsulaUser($name, $username) {
    $name = strtolower(trim($name ?? ''));
    $username = strtolower(trim($username ?? ''));
    return (strpos($name, 'daniel imsula') !== false || ($username === 'daniel' || (strpos($username, 'daniel') !== false && strpos($username, 'imsula') !== false)));
}

// Function to check if user is logged in
function checkLogin() {
    if (!isset($_SESSION['user_id'])) {
        if (basename($_SERVER['SCRIPT_NAME']) === 'api.php') {
            http_response_code(401);
            die(json_encode(['success' => false, 'error' => 'Tidak terautentikasi. Silakan login.']));
        } else {
            header("Location: login.php");
            exit();
        }
    }
}

// Function to check role access (Legacy/Direct)
function checkRole($allowed_roles) {
    if (!isset($_SESSION['role']) || !in_array($_SESSION['role'], $allowed_roles)) {
        header("Location: mobile_home.php");
        exit();
    }
}

/**
 * Check if the current user has access to a specific menu key.
 * Redirects to dashboard_summary or mobile_home if access is denied.
 */
function checkAccess($menu_key) {
    if (!canAccessMenu($menu_key)) {
        $role = $_SESSION['role'] ?? '';
        if ($role === 'driver' || $role === 'request_pickup') {
            header("Location: mobile_home");
        } else {
            header("Location: dashboard_summary");
        }
        exit();
    }
}

/**
 * Check if the current user role can access a specific menu/module
 * @param string $menu_key
 * @return bool
 */
function canAccessMenu($menu_key) {
    global $pdo;
    if (!isset($_SESSION['role'])) return false;
    
    $role = $_SESSION['role'];
    
    // Super Admin (controller) always has full access to everything
    if ($role === 'controller') return true;
    
    // Cache permissions for the current request
    static $permissions = [];
    if (!isset($permissions[$role])) {
        try {
            $stmt = $pdo->prepare("SELECT menu_key, can_access, can_write FROM role_permissions WHERE role_key = ?");
            $stmt->execute([$role]);
            $permissions[$role] = [];
            while ($row = $stmt->fetch(PDO::FETCH_ASSOC)) {
                $permissions[$role][$row['menu_key']] = [
                    'can_access' => $row['can_access'] == 1,
                    'can_write' => $row['can_write'] == 1
                ];
            }
        } catch (PDOException $e) {
            return false;
        }
    }
    
    // If permission is explicitly set in role_permissions table, respect it
    if (isset($permissions[$role][$menu_key])) {
        return $permissions[$role][$menu_key]['can_access'];
    }
    
    // Admin role fallback: default to true for web menus if not explicitly configured in DB
    if ($role === 'admin') return true;
    
    return false;
}

/**
 * Check if the current user role has write access to a specific menu/module
 * @param string $menu_key
 * @return bool
 */
function canWriteMenu($menu_key) {
    global $pdo;
    if (!isset($_SESSION['role'])) return false;
    
    $role = $_SESSION['role'];
    
    // Super Admin (controller) always has full write access
    if ($role === 'controller') return true;
    
    // Cache permissions for the current request
    static $permissions = [];
    if (!isset($permissions[$role])) {
        try {
            $stmt = $pdo->prepare("SELECT menu_key, can_access, can_write FROM role_permissions WHERE role_key = ?");
            $stmt->execute([$role]);
            $permissions[$role] = [];
            while ($row = $stmt->fetch(PDO::FETCH_ASSOC)) {
                $permissions[$role][$row['menu_key']] = [
                    'can_access' => $row['can_access'] == 1,
                    'can_write' => $row['can_write'] == 1
                ];
            }
        } catch (PDOException $e) {
            return false;
        }
    }
    
    // If permission is explicitly set in role_permissions table, respect it
    if (isset($permissions[$role][$menu_key])) {
        return $permissions[$role][$menu_key]['can_write'];
    }
    
    // Admin role fallback: default to true for write access if not explicitly configured in DB
    if ($role === 'admin') return true;
    
    return false;
}

function checkRoleApi($allowed_roles) {
    if (!isset($_SESSION['role']) || !in_array($_SESSION['role'], $allowed_roles)) {
        die(json_encode(['success' => false, 'error' => 'Unauthorized access']));
    }
}

/**
 * Check if the current user has access to a specific menu key (API version).
 * Returns JSON error if access is denied.
 */
function checkAccessApi($menu_key) {
    if (!canAccessMenu($menu_key)) {
        die(json_encode(['success' => false, 'error' => 'Akses ditolak (Izin menu diperlukan)']));
    }
}

/**
 * Check if the current user has write access to a specific menu key (API version).
 * Returns JSON error if write access is denied.
 */
function checkWriteAccessApi($menu_key) {
    if (!canWriteMenu($menu_key)) {
        die(json_encode(['success' => false, 'error' => 'Akses ditolak (Izin tulis diperlukan)']));
    }
}

/**
 * Validate dan sanitize ekstensi file upload. Return ekstensi lowercase yang aman,
 * atau null kalau ekstensi tidak ada di whitelist.
 *
 * Whitelist: gambar (jpg/jpeg/png/webp/gif/heic/heif) dan dokumen (pdf).
 * Berlaku untuk semua upload SJ/Goods/Proof di api.php.
 */
function safeUploadExtension($originalName) {
    static $allowed = ['jpg', 'jpeg', 'png', 'webp', 'gif', 'heic', 'heif', 'pdf'];
    $ext = strtolower(pathinfo($originalName, PATHINFO_EXTENSION));
    if (!$ext || !in_array($ext, $allowed, true)) {
        return null;
    }
    return $ext;
}

/**
 * Log user activity into system_logs table
 */
function logActivity($action, $description) {
    global $pdo;
    if (!isset($pdo)) return;
    
    $user_id = isset($_SESSION['user_id']) ? $_SESSION['user_id'] : null;
    $ip_address = $_SERVER['REMOTE_ADDR'] ?? 'UNKNOWN';
    
    try {
        $stmt = $pdo->prepare("INSERT INTO system_logs (user_id, action, description, ip_address) VALUES (?, ?, ?, ?)");
        $stmt->execute([$user_id, $action, $description, $ip_address]);
    } catch (PDOException $e) {
        // Silently fail
    }
}
