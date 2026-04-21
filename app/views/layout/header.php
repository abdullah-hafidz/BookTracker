<?php
/**
 * Author:  Abdullah Hafidz
 * Group:   Group 1
 * Created: 17 March 2026
 * Version: 1.0
 *
 * Layout — Site Header
 *
 * Renders the <!DOCTYPE>, <html data-theme>, <head>, and <header> nav bar.
 * Included at the start of every page via BookController::render().
 *
 * @var string|null $pageTitle  Optional page title set by the view; prepended to <title> if present.
 */
?>
<!DOCTYPE html>
<html lang="en" data-theme="light">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title><?= isset($pageTitle) ? htmlspecialchars($pageTitle) . ' - ' : '' ?>Group1's Book Tracker</title>
    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
    <link href="https://fonts.googleapis.com/css2?family=Roboto:wght@300;400;500;700;900&display=swap" rel="stylesheet">
    <link rel="stylesheet" href="<?= CDN_URL ?: APP_URL ?>/assets/css/style.css">
    <script>window.APP_URL = '<?= APP_URL ?>';</script>
</head>
<body>
<header class="site-header">
    <div class="container">
        <a href="<?= APP_URL ?>/?action=index" class="logo">
            <svg class="logo-icon" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.5"><path d="M12 6.042A8.967 8.967 0 006 3.75c-1.052 0-2.062.18-3 .512v14.25A8.987 8.987 0 016 18c2.305 0 4.408.867 6 2.292m0-14.25a8.966 8.966 0 016-2.292c1.052 0 2.062.18 3 .512v14.25A8.987 8.987 0 0018 18a8.967 8.967 0 00-6 2.292m0-14.25v14.25"/></svg>
            Group1's Book Tracker
        </a>
        <nav>
            <a href="<?= APP_URL ?>/?action=index" class="nav-link">My Library</a>

            <a href="<?= APP_URL ?>/?action=add" class="btn-nav">
                <svg viewBox="0 0 20 20" fill="currentColor"><path d="M10.75 4.75a.75.75 0 00-1.5 0v4.5h-4.5a.75.75 0 000 1.5h4.5v4.5a.75.75 0 001.5 0v-4.5h4.5a.75.75 0 000-1.5h-4.5v-4.5z"/></svg>
                Add Book
            </a>
        </nav>
    </div>
</header>
<main class="container">
