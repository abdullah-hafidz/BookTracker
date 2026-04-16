<?php
/**
 * Author:  Abdullah Hafidz
 * Group:   Group 1
 * Created: 17 March 2026
 * Version: 1.0
 *
 * @package Group1\BookTracker
 *
 * Front Controller — single entry point for all application requests.
 *
 * Apache mod_rewrite (via .htaccess) routes all non-file requests here.
 * Reads ?action= from the query string and dispatches to BookController,
 * with the exception of 'save-theme' which is handled inline and returns JSON.
 *
 * Dispatch table:
 *   index   → BookController::index()
 *   add     → BookController::add()
 *   edit    → BookController::edit(int $id)
 *   view    → BookController::view(int $id)
 *   delete  → BookController::delete()
 *   toggle  → BookController::toggle()
 *   (other) → BookController::index()  (default)
 */

declare(strict_types=1);

// Start session for CSRF token storage
session_start();

// Generate a CSRF token for this session if one does not exist
if (empty($_SESSION['csrf_token'])) {
    $_SESSION['csrf_token'] = bin2hex(random_bytes(32));
}

require_once __DIR__ . '/../config/db.php';
require_once __DIR__ . '/../app/models/Book.php';
require_once __DIR__ . '/../app/models/Setting.php';
require_once __DIR__ . '/../app/controllers/BookController.php';

$action     = $_GET['action'] ?? 'index';
$controller = new BookController($pdo);

// Save-theme is a lightweight JSON endpoint — handle before controller dispatch
if ($action === 'save-theme') {
    $theme   = ($_POST['theme'] ?? '') === 'light' ? 'light' : 'dark';
    $setting = new Setting($pdo);
    $setting->set('theme', $theme);
    header('Content-Type: application/json');
    echo json_encode(['ok' => true, 'theme' => $theme]);
    exit;
}

match ($action) {
    'add'    => $controller->add(),
    'edit'   => $controller->edit((int) ($_GET['id'] ?? 0)),
    'view'   => $controller->view((int) ($_GET['id'] ?? 0)),
    'delete' => $controller->delete(),
    'toggle' => $controller->toggle(),
    default  => $controller->index(),
};
