<?php
require_once __DIR__ . '/config.php';

if (session_status() === PHP_SESSION_NONE) {
    session_start();
}

function e($value) {
    return htmlspecialchars($value ?? '', ENT_QUOTES, 'UTF-8');
}

function url($path = '') {
    return APP_BASE . '/' . ltrim($path, '/');
}

function redirect($path) {
    header("Location: " . url($path));
    exit;
}

function isLoggedIn() {
    return isset($_SESSION['user_id']);
}

function isAgentOrAdmin() {
    return isset($_SESSION['role']) && in_array($_SESSION['role'], ['agent', 'admin']);
}
?>