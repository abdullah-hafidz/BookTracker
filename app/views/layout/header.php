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
 * @var string      $theme      Current theme ('dark' or 'light'), injected by BookController::render().
 * @var string|null $pageTitle  Optional page title set by the view; prepended to <title> if present.
 */

// $theme is injected by BookController::render() — server-side, zero flash
$theme = $theme ?? 'dark';
?>
<!DOCTYPE html>
<html lang="en" data-theme="<?= $theme ?>">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title><?= isset($pageTitle) ? htmlspecialchars($pageTitle) . ' — ' : '' ?>Group1's Book Tracker</title>
    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
    <link href="https://fonts.googleapis.com/css2?family=Roboto:wght@300;400;500;700;900&display=swap" rel="stylesheet">
    <link rel="stylesheet" href="<?= APP_URL ?>/assets/css/style.css">
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

            <!-- Theme toggle -->
            <button id="themeToggle" class="theme-toggle" aria-label="Toggle theme" data-theme="<?= $theme ?>">
                <!-- Sun (shown in dark mode, click → switch to light) -->
                <svg class="icon-sun" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.8">
                    <circle cx="12" cy="12" r="4.5"/>
                    <path d="M12 2v2M12 20v2M4.22 4.22l1.42 1.42M18.36 18.36l1.42 1.42M2 12h2M20 12h2M4.22 19.78l1.42-1.42M18.36 5.64l1.42-1.42"/>
                </svg>
                <!-- Moon (shown in light mode, click → switch to dark) -->
                <svg class="icon-moon" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.8">
                    <path d="M21 12.79A9 9 0 1111.21 3 7 7 0 0021 12.79z"/>
                </svg>
            </button>

            <a href="<?= APP_URL ?>/?action=add" class="btn-nav">
                <svg viewBox="0 0 20 20" fill="currentColor"><path d="M10.75 4.75a.75.75 0 00-1.5 0v4.5h-4.5a.75.75 0 000 1.5h4.5v4.5a.75.75 0 001.5 0v-4.5h4.5a.75.75 0 000-1.5h-4.5v-4.5z"/></svg>
                Add Book
            </a>
        </nav>
    </div>
</header>
<main class="container">
