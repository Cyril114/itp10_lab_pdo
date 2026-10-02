<?php
declare(strict_types=1);
try {

    $pdo = new PDO(
        'mysql:host=127.0.0.1;dbname=ip10_lab;charset=utf8mb4',
        'root',
        '',
        [
            PDO::ATTR_ERRMODE            => PDO::ERRMODE_EXCEPTION,
            PDO::ATTR_DEFAULT_FETCH_MODE => PDO::FETCH_ASSOC,
            PDO::ATTR_EMULATE_PREPARES   => false,
            PDO::ATTR_STRINGIFY_FETCHES  => false,

            PDO::ATTR_AUTOCOMMIT         => false,
        ]
    );
} catch (PDOException $e) {
    error_log('DB connect failed: ' . $e->getMessage());
    die('Database unavailable');
}