<?php
/**
 * Author:  Abdullah Hafidz
 * Group:   Group 1
 * Created: 17 March 2026
 * Version: 1.0
 *
 * View — Library Index
 *
 * Displays the full book collection as a responsive card grid.
 *
 * @var array<int, array<string, mixed>> $books  All books from the database.
 */

$pageTitle = 'My Library';
?>

<div class="page-header">
    <div>
        <h1>My Library</h1>
        <p class="subtitle">Your personal book collection</p>
    </div>
    <a href="<?= APP_URL ?>/?action=add" class="btn btn-primary">
        <svg width="15" height="15" viewBox="0 0 20 20" fill="currentColor"><path d="M10.75 4.75a.75.75 0 00-1.5 0v4.5h-4.5a.75.75 0 000 1.5h4.5v4.5a.75.75 0 001.5 0v-4.5h4.5a.75.75 0 000-1.5h-4.5v-4.5z"/></svg>
        Add Book
    </a>
</div>

<!-- Book grid -->
<?php if (empty($books)): ?>
    <div class="empty-state">
        <span class="empty-icon">📚</span>
        <h2>Your library is empty</h2>
        <p>Start building your collection. Add the first book!</p>
        <a href="<?= APP_URL ?>/?action=add" class="btn btn-primary">Add your first book</a>
    </div>
<?php else: ?>
<div class="book-grid">
    <?php foreach ($books as $book): ?>
    <div class="book-card">

        <!-- Cover with overlay -->
        <div class="book-cover-wrap">
            <?php if ($book['cover_url']): ?>
                <?php $coverSrc = (CDN_URL !== '' ? CDN_URL : APP_URL) . '/' . $book['cover_url']; ?>
                <img src="<?= htmlspecialchars($coverSrc) ?>"
                     alt="<?= htmlspecialchars($book['title']) ?>"
                     class="book-cover">
            <?php else: ?>
                <div class="book-cover-placeholder">📖</div>
            <?php endif; ?>

            <!-- Status ribbon -->
            <span class="status-ribbon <?= $book['status'] ?>">
                <?= $book['status'] === 'read' ? '✓ Read' : 'Unread' ?>
            </span>

            <!-- Hover action overlay -->
            <div class="book-overlay">
                <div class="overlay-title"><?= htmlspecialchars($book['title']) ?></div>
                <div class="overlay-author"><?= htmlspecialchars($book['author']) ?></div>
                <div class="overlay-actions">
                    <a href="<?= APP_URL ?>/?action=view&id=<?= $book['id'] ?>" class="btn btn-sm btn-ghost">View</a>

                    <form method="POST" action="<?= APP_URL ?>/?action=toggle">
                        <input type="hidden" name="csrf_token" value="<?= htmlspecialchars($csrfToken) ?>">
                        <input type="hidden" name="id" value="<?= $book['id'] ?>">
                        <button type="submit" class="btn btn-sm btn-<?= $book['status'] === 'read' ? 'ghost' : 'success' ?>">
                            <?= $book['status'] === 'read' ? 'Unread' : '✓ Read' ?>
                        </button>
                    </form>

                    <a href="<?= APP_URL ?>/?action=edit&id=<?= $book['id'] ?>" class="btn btn-sm btn-ghost">Edit</a>

                    <form method="POST" action="<?= APP_URL ?>/?action=delete">
                        <input type="hidden" name="csrf_token" value="<?= htmlspecialchars($csrfToken) ?>">
                        <input type="hidden" name="id" value="<?= $book['id'] ?>">
                        <button type="submit" class="btn btn-sm btn-danger"
                                data-confirm="Delete &quot;<?= htmlspecialchars($book['title'], ENT_QUOTES) ?>&quot;?">
                            Delete
                        </button>
                    </form>
                </div>
            </div>
        </div>

        <!-- Card info -->
        <div class="book-info">
            <div class="book-title">
                <a href="<?= APP_URL ?>/?action=view&id=<?= $book['id'] ?>">
                    <?= htmlspecialchars($book['title']) ?>
                </a>
            </div>
            <div class="book-author"><?= htmlspecialchars($book['author']) ?></div>
            <?php if ($book['genre']): ?>
                <span class="book-genre"><?= htmlspecialchars($book['genre']) ?></span>
            <?php endif; ?>
        </div>

    </div>
    <?php endforeach; ?>
</div>
<?php endif; ?>
