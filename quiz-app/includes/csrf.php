<?php
// includes/csrf.php

if (session_status() !== PHP_SESSION_ACTIVE) {
    session_start();
}

function csrf_token(): string {
    if (empty($_SESSION['csrf_token'])) {
        $_SESSION['csrf_token'] = bin2hex(random_bytes(32));
    }
    return $_SESSION['csrf_token'];
}

function csrf_field(): string {
    return '<input type="hidden" name="_token" value="' . e(csrf_token()) . '">';
}

function verify_csrf(): void {
    if ($_SERVER['REQUEST_METHOD'] === 'POST') {
        $ok = isset($_POST['_token']) && hash_equals($_SESSION['csrf_token'] ?? '', $_POST['_token']);
        if (!$ok) {
            http_response_code(400);
            exit('不正なリクエストです（CSRF）');
        }
    }
}