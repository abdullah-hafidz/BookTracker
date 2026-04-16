<?php
/**
 * Author:  Abdullah Hafidz
 * Group:   Group 1
 * Created: 17 March 2026
 * Version: 1.0
 *
 * View — Book Detail
 *
 * Renders the full detail page for a single book: cover image, title, author,
 * genre, status badge, added/updated dates, personal notes, and action buttons
 * (toggle status, edit, delete).
 *
 * @var array<string, mixed> $book  The book record from the database.
 *                                  Keys: id, title, author, genre, cover_url,
 *                                        status, notes, created_at.
 */
$pageTitle = $book['title']; ?>

<div class="page-header">
    <a href="<?= APP_URL ?>/?action=index" class="btn btn-ghost">&larr; Back to Library</a>
</div>

<div class="book-detail-wrap">

    <!-- Cover -->
    <div class="book-detail-cover">
        <?php if ($book['cover_url']): ?>
            <?php $coverSrc = (CDN_URL !== '' ? CDN_URL : APP_URL) . '/' . $book['cover_url']; ?>
            <img src="<?= htmlspecialchars($coverSrc) ?>"
                 alt="Cover of <?= htmlspecialchars($book['title']) ?>">
        <?php else: ?>
            <div class="book-cover-placeholder large">📖</div>
        <?php endif; ?>
    </div>

    <!-- Info -->
    <div class="book-detail-info">

        <span class="badge badge-<?= $book['status'] ?> badge-lg"><?= ucfirst($book['status']) ?></span>

        <h1 class="book-detail-title"><?= htmlspecialchars($book['title']) ?></h1>
        <p class="book-detail-author">by <?= htmlspecialchars($book['author']) ?></p>

        <div class="meta-grid">
            <?php if ($book['genre']): ?>
            <div class="meta-item">
                <div class="meta-label">Genre</div>
                <div class="meta-value"><?= htmlspecialchars($book['genre']) ?></div>
            </div>
            <?php endif; ?>
            <div class="meta-item">
                <div class="meta-label">Status</div>
                <div class="meta-value"><?= ucfirst($book['status']) ?></div>
            </div>
            <div class="meta-item">
                <div class="meta-label">Added</div>
                <div class="meta-value"><?= date('d M Y', strtotime($book['created_at'])) ?></div>
            </div>
        </div>

        <?php if ($book['notes']): ?>
        <div class="book-notes-section">
            <h3>My Notes</h3>
            <p><?= nl2br(htmlspecialchars($book['notes'])) ?></p>
        </div>
        <?php endif; ?>

        <div class="detail-actions">
            <!-- Toggle status -->
            <form method="POST" action="<?= APP_URL ?>/?action=toggle">
                <input type="hidden" name="csrf_token" value="<?= htmlspecialchars($csrfToken) ?>">
                <input type="hidden" name="id" value="<?= $book['id'] ?>">
                <button type="submit" class="btn btn-<?= $book['status'] === 'read' ? 'ghost' : 'success' ?>">
                    <?= $book['status'] === 'read' ? 'Mark as Unread' : '✓ Mark as Read' ?>
                </button>
            </form>

            <a href="<?= APP_URL ?>/?action=edit&id=<?= $book['id'] ?>" class="btn btn-ghost">Edit Book</a>

            <!-- Delete -->
            <form method="POST" action="<?= APP_URL ?>/?action=delete">
                <input type="hidden" name="csrf_token" value="<?= htmlspecialchars($csrfToken) ?>">
                <input type="hidden" name="id" value="<?= $book['id'] ?>">
                <button type="submit" class="btn btn-danger"
                        data-confirm="Delete &quot;<?= htmlspecialchars($book['title'], ENT_QUOTES) ?>&quot;? This cannot be undone.">
                    Delete
                </button>
            </form>
        </div>

    </div>
</div>
