<?php
/**
 * Author:  Abdullah Hafidz
 * Group:   Group 1
 * Created: 17 March 2026
 * Version: 1.0
 *
 * @package Group1\BookTracker
 *
 * Database configuration and bootstrap.
 *
 * Defines application-wide constants (APP_URL, CDN_URL) and creates the
 * shared PDO connection ($pdo) used throughout the application.
 *
 * Environment variables take precedence over local defaults, allowing the
 * same codebase to run on Laragon (dev) and AWS EC2 (production) without
 * any code changes.
 *
 * Constants defined:
 * @define string APP_URL  Base URL of the application (e.g. '/booktracker' or 'https://example.com').
 * @define string CDN_URL  CloudFront CDN base URL, or empty string in local dev.
 */

// Database connection — reads from environment variables.
// Local (Laragon): falls back to defaults below.
// AWS EC2: set via /etc/environment before deploying.

define('APP_URL',  getenv('APP_URL') !== false ? getenv('APP_URL') : (
    (($_SERVER['HTTP_HOST'] ?? '') === 'localhost') ? '/booktracker' : ''
));
define('CDN_URL',  getenv('CDN_URL')  ?: '');   // e.g. https://xxxxx.cloudfront.net

$host = getenv('DB_HOST') ?: '127.0.0.1';
$db   = getenv('DB_NAME') ?: 'group1_books';
$user = getenv('DB_USER') ?: 'root';
$pass = getenv('DB_PASS') ?: '';

try {
    $pdo = new PDO("mysql:host=$host;dbname=$db;charset=utf8mb4", $user, $pass, [
        PDO::ATTR_ERRMODE            => PDO::ERRMODE_EXCEPTION,
        PDO::ATTR_DEFAULT_FETCH_MODE => PDO::FETCH_ASSOC,
        PDO::ATTR_EMULATE_PREPARES   => false,
    ]);
} catch (PDOException $e) {
    error_log('DB connection failed: ' . $e->getMessage());
    http_response_code(503);
    exit('Service temporarily unavailable. Please try again later.');
}
