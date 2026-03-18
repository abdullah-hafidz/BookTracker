<?php
/**
 * Author:  Abdullah Hafidz
 * Group:   Group 1
 * Created: 17 March 2026
 * Version: 1.0
 *
 * @package Group1\BookTracker
 *
 * ALB Health Check Endpoint
 *
 * Returns HTTP 200 with a JSON payload confirming the web server is running
 * and the database connection is healthy. The ALB Target Group uses this
 * endpoint to determine whether to route traffic to this instance.
 *
 * Path: /health.php (served directly by Apache — bypasses front controller)
 * ALB Target Group: Health check path = /health.php
 */

declare(strict_types=1);

header('Content-Type: application/json');
header('Cache-Control: no-store');

$status = 'healthy';
$dbOk   = false;
$error  = null;

// Verify database connectivity
try {
    $host = getenv('DB_HOST') ?: '127.0.0.1';
    $db   = getenv('DB_NAME') ?: 'group1_books';
    $user = getenv('DB_USER') ?: 'root';
    $pass = getenv('DB_PASS') ?: '';

    $pdo = new PDO(
        "mysql:host={$host};dbname={$db};charset=utf8mb4",
        $user,
        $pass,
        [PDO::ATTR_TIMEOUT => 3]
    );
    $pdo->query('SELECT 1');
    $dbOk = true;
} catch (PDOException $e) {
    $status = 'degraded';
    $error  = 'db_unreachable';
}

$code = $dbOk ? 200 : 503;
http_response_code($code);

echo json_encode([
    'status'    => $status,
    'db'        => $dbOk ? 'ok' : 'error',
    'timestamp' => time(),
    'error'     => $error,
], JSON_UNESCAPED_SLASHES);
