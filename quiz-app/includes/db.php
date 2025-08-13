<?php
// includes/db.php

// ★ 環境に応じて調整
const DB_HOST = 'localhost';
const DB_NAME = 'quiz_db';
const DB_USER = 'root';        // MAMP 既定: 'root'
const DB_PASS = 'root';        // MAMP 既定: 'root'
const DB_PORT = 8889;          // MAMP 既定: 8889

function pdo(): PDO {
    static $pdo = null;
    if ($pdo === null) {
        $dsn = 'mysql:host=' . DB_HOST . ';port=' . DB_PORT . ';dbname=' . DB_NAME . ';charset=utf8mb4';
        $pdo = new PDO($dsn, DB_USER, DB_PASS, [
            PDO::ATTR_ERRMODE => PDO::ERRMODE_EXCEPTION,
            PDO::ATTR_DEFAULT_FETCH_MODE => PDO::FETCH_ASSOC,
        ]);
    }
    return $pdo;
}

function e(?string $s): string {
    return htmlspecialchars($s ?? '', ENT_QUOTES | ENT_SUBSTITUTE, 'UTF-8');
}

function base_path(string $path = ''): string {
    // public/ をドキュメントルートにした想定
    return '/' . ltrim($path, '/');
}