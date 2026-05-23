<?php
session_start();
$DB_HOST = '127.0.0.1';
$DB_NAME = 'form_2021';
$DB_USER = 'root';
$DB_PASS = '';
// Site name used in page titles and header
if (!defined('SITE_NAME')) define('SITE_NAME', 'form_2021 ruchugi secondary');
try {
    $pdo = new PDO("mysql:host=$DB_HOST;dbname=$DB_NAME;charset=utf8mb4", $DB_USER, $DB_PASS, [
        PDO::ATTR_ERRMODE => PDO::ERRMODE_EXCEPTION,
        PDO::ATTR_DEFAULT_FETCH_MODE => PDO::FETCH_ASSOC,
    ]);
    try {
        $check = $pdo->query("SHOW COLUMNS FROM `users` LIKE 'role'");
        if ($check && $check->rowCount() === 0) {
            $pdo->exec("ALTER TABLE `users` ADD COLUMN `role` ENUM('submitter','viewer') NOT NULL DEFAULT 'viewer'");
        }
        try {
            $pdo->exec("ALTER TABLE `opinions` MODIFY COLUMN `user_id` INT NULL");
        } catch (Exception $ignore) {
            // ignore if opinions table does not exist yet or already nullable
        }
    } catch (Exception $e) {
        // ignore if users table does not exist yet
    }
} catch (Exception $e) {
    die('DB connection error: ' . $e->getMessage());
}

function is_logged_in() {
    return !empty($_SESSION['user']);
}

function get_user_role() {
    if (!empty($_SESSION['user']['role'])) {
        return $_SESSION['user']['role'];
    }
    if (!empty($_SESSION['user'])) {
        $_SESSION['user']['role'] = 'viewer';
        return 'viewer';
    }
    return 'viewer';
}

function is_admin_logged_in() {
    return !empty($_SESSION['admin']);
}
