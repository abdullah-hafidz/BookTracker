<?php
/**
 * Author:  Abdullah Hafidz
 * Group:   Group 1
 * Created: 17 March 2026
 * Version: 1.0
 *
 * View — Edit Book Form
 *
 * Renders a pre-filled form to update an existing book.
 * Displays the current cover image with an option to replace it.
 * On validation failure, re-renders with error messages and submitted values.
 *
 * @var array<string, mixed> $book    The original book record from the database.
 * @var string[]             $errors  Validation error messages (empty on GET or valid POST).
 * @var array<string, mixed> $input   Current form values (= $book on GET, = $_POST data on failed POST).
 */
$pageTitle = 'Edit — ' . $book['title']; ?>

<div class="page-header">
    <div>
        <h1>Edit Book</h1>
        <p class="subtitle" style="font-style:italic"><?= htmlspecialchars($book['title']) ?></p>
    </div>
    <a href="<?= APP_URL ?>/?action=view&id=<?= $book['id'] ?>" class="btn btn-ghost">&larr; Back</a>
</div>

<?php if (!empty($errors)): ?>
<div class="alert alert-error">
    <ul>
        <?php foreach ($errors as $e): ?>
            <li><?= htmlspecialchars($e) ?></li>
        <?php endforeach; ?>
    </ul>
</div>
<?php endif; ?>

<div class="form-card">
    <form method="POST" action="<?= APP_URL ?>/?action=edit&id=<?= $book['id'] ?>" enctype="multipart/form-data">
        <input type="hidden" name="csrf_token" value="<?= htmlspecialchars($csrfToken) ?>">

        <div class="form-section">
            <p class="form-section-title">Book Details</p>
            <div class="form-row">
                <div class="form-group">
                    <label for="title">Title <span class="required">*</span></label>
                    <input type="text" id="title" name="title" required
                           value="<?= htmlspecialchars($input['title'] ?? '') ?>">
                </div>
                <div class="form-group">
                    <label for="author">Author <span class="required">*</span></label>
                    <input type="text" id="author" name="author" required
                           value="<?= htmlspecialchars($input['author'] ?? '') ?>">
                </div>
            </div>
            <div class="form-group" style="max-width:320px">
                <label for="genre">Genre</label>
                <input type="text" id="genre" name="genre"
                       value="<?= htmlspecialchars($input['genre'] ?? '') ?>">
            </div>
        </div>

        <div class="form-section">
            <p class="form-section-title">Reading Status</p>
            <div class="form-group">
                <label>Reading status</label>
                <div class="status-toggle">
                    <div class="status-option">
                        <input type="radio" id="status_unread" name="status" value="unread"
                               <?= ($input['status'] ?? 'unread') === 'unread' ? 'checked' : '' ?>>
                        <label for="status_unread" class="unread-label">📌 Not yet read</label>
                    </div>
                    <div class="status-option">
                        <input type="radio" id="status_read" name="status" value="read"
                               <?= ($input['status'] ?? '') === 'read' ? 'checked' : '' ?>>
                        <label for="status_read" class="read-label">✓ Already read</label>
                    </div>
                </div>
            </div>
        </div>

        <div class="form-section">
            <p class="form-section-title">Cover Image</p>
            <div class="form-group">
                <?php if ($book['cover_url']): ?>
                    <?php $coverSrc = (CDN_URL !== '' ? CDN_URL : APP_URL) . '/' . $book['cover_url']; ?>
                    <div id="previewWrap" class="cover-preview-wrap">
                        <img id="previewImg" src="<?= htmlspecialchars($coverSrc) ?>" alt="Current cover">
                        <div class="cover-preview-info">
                            <strong>Current cover</strong>
                            <span>Upload a new file to replace it</span>
                        </div>
                    </div>
                <?php else: ?>
                    <div id="previewWrap" style="display:none" class="cover-preview-wrap">
                        <img id="previewImg" src="" alt="Cover preview">
                        <div class="cover-preview-info">
                            <strong id="previewName"></strong>
                            <span>Click below to change</span>
                        </div>
                    </div>
                <?php endif; ?>
                <label class="upload-zone">
                    <input type="file" id="cover" name="cover" accept="image/*">
                    <span class="upload-icon">🖼️</span>
                    <span class="upload-text">Click to <?= $book['cover_url'] ? 'replace' : 'choose' ?> a cover image</span>
                    <span class="upload-hint">JPG, PNG, GIF or WebP · max 5 MB</span>
                </label>
            </div>
        </div>

        <div class="form-section">
            <p class="form-section-title">Notes</p>
            <div class="form-group">
                <label for="notes">Personal notes</label>
                <textarea id="notes" name="notes"><?= htmlspecialchars($input['notes'] ?? '') ?></textarea>
            </div>
        </div>

        <div class="form-actions">
            <button type="submit" class="btn btn-primary">Save Changes</button>
            <a href="<?= APP_URL ?>/?action=view&id=<?= $book['id'] ?>" class="btn btn-ghost">Cancel</a>
        </div>

    </form>
</div>
