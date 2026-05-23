<?php
session_start([
    'cookie_httponly' => true,
    'cookie_samesite' => 'Strict',
    'cookie_secure' => isset($_SERVER['HTTPS']),
    'use_strict_mode' => true
]);

$DB_HOST = '127.0.0.1';
$DB_NAME = 'form_2021';
$DB_USER = 'root';
$DB_PASS = '';

if (!defined('SITE_NAME')) define('SITE_NAME', 'form_2021 ruchugi secondary');

// CSRF Protection
function generate_csrf_token() {
    if (empty($_SESSION['csrf_token'])) {
        $_SESSION['csrf_token'] = bin2hex(random_bytes(32));
    }
    return $_SESSION['csrf_token'];
}

function verify_csrf_token($token) {
    return isset($_SESSION['csrf_token']) && hash_equals($_SESSION['csrf_token'], $token);
}

// Rate limiting
function check_rate_limit($key, $limit = 5, $window = 60) {
    $rate_key = "rate_limit_{$key}";
    if (!isset($_SESSION[$rate_key])) {
        $_SESSION[$rate_key] = ['count' => 1, 'first_attempt' => time()];
        return true;
    }
    
    $data = $_SESSION[$rate_key];
    if ($data['first_attempt'] + $window > time()) {
        if ($data['count'] >= $limit) {
            return false;
        }
        $_SESSION[$rate_key]['count']++;
    } else {
        $_SESSION[$rate_key] = ['count' => 1, 'first_attempt' => time()];
    }
    return true;
}

try {
    $pdo = new PDO("mysql:host=$DB_HOST;dbname=$DB_NAME;charset=utf8mb4", $DB_USER, $DB_PASS, [
        PDO::ATTR_ERRMODE => PDO::ERRMODE_EXCEPTION,
        PDO::ATTR_DEFAULT_FETCH_MODE => PDO::FETCH_ASSOC,
        PDO::ATTR_EMULATE_PREPARES => false,
    ]);
    
    try {
        $check = $pdo->query("SHOW COLUMNS FROM `users` LIKE 'role'");
        if ($check && $check->rowCount() === 0) {
            $pdo->exec("ALTER TABLE `users` ADD COLUMN `role` ENUM('submitter','viewer') NOT NULL DEFAULT 'viewer'");
        }
        try {
            $pdo->exec("ALTER TABLE `opinions` MODIFY COLUMN `user_id` INT NULL");
        } catch (Exception $ignore) {}
    } catch (Exception $e) {}
} catch (Exception $e) {
    error_log("DB connection error: " . $e->getMessage());
    die('System error. Please try again later.');
}

function is_logged_in() {
    return !empty($_SESSION['user']['id']);
}

function get_user_role() {
    return $_SESSION['user']['role'] ?? 'viewer';
}

function is_admin_logged_in() {
    return !empty($_SESSION['admin']) && $_SESSION['admin'] === true;
}

// Input sanitization
function sanitize_input($data) {
    $data = trim($data);
    $data = stripslashes($data);
    $data = htmlspecialchars($data, ENT_QUOTES, 'UTF-8');
    return $data;
}

// Log admin actions
function log_admin_action($action, $pdo) {
    $ip = $_SERVER['REMOTE_ADDR'] ?? 'unknown';
    $stmt = $pdo->prepare("INSERT INTO admin_logs (admin_user, action, ip_address, created_at) VALUES (?, ?, ?, NOW())");
    $stmt->execute(['admin', $action, $ip]);
}
?>