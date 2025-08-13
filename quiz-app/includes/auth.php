<?php
// includes/auth.php

if (session_status() !== PHP_SESSION_ACTIVE) {
    session_start();
}

function is_logged_in(): bool {
    return isset($_SESSION['admin_id']);
}

function require_login(): void {
    if (!is_logged_in()) {
        header('Location: ' . base_path('admin/login.php'));
        exit;
    }
}

function login_admin(int $admin_id): void {
    session_regenerate_id(true);
    $_SESSION['admin_id'] = $admin_id;
}

function logout_admin(): void {
    $_SESSION = [];
    if (ini_get('session.use_cookies')) {
        $params = session_get_cookie_params();
        setcookie(session_name(), '', time() - 42000, $params['path'], $params['domain'], $params['secure'], $params['httponly']);
    }
    session_destroy();
}