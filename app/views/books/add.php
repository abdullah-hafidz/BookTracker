<?php
/**
 * Author:  Abdullah Hafidz
 * Group:   Group 1
 * Created: 17 March 2026
 * Version: 1.0
 *
 * View — Add Book Form
 *
 * Renders the form to create a new book entry.
 * On validation failure, re-renders with error messages and previously entered values.
 *
 * @var string[]             $errors  Validation error messages (empty on first load or valid POST).
 * @var array<string, mixed> $input   Previously submitted form values for sticky re-population.
 */
$pageTitle = 'Add a Book'; ?>

<div class="page-header">
    <div>
        <h1>Add a Book</h1>
        <p class="subtitle">Add a new book to your library</p>
    </div>
    <a href="<?= APP_URL ?>/?action=index" class="btn btn-ghost">&larr; Back to Library</a>
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
    <form method="POST" action="<?= APP_URL ?>/?action=add" enctype="multipart/form-data">
        <input type="hidden" name="csrf_token" value="<?= htmlspecialchars($csrfToken) ?>">

        <div class="form-section">
            <p class="form-section-title">Book Details</p>
            <div class="form-row">
                <div class="form-group">
                    <label for="title">Title <span class="required">*</span></label>
                    <input type="text" id="title" name="title" required
                           placeholder="e.g. The Great Gatsby"
                           value="<?= htmlspecialchars($input['title'] ?? '') ?>">
                </div>
                <div class="form-group">
                    <label for="author">Author <span class="required">*</span></label>
                    <input type="text" id="author" name="author" required
                           placeholder="e.g. F. Scott Fitzgerald"
                           value="<?= htmlspecialchars($input['author'] ?? '') ?>">
                </div>
            </div>
            <div class="form-group" style="max-width:320px">
                <label for="genre">Genre</label>
                <input type="text" id="genre" name="genre"
                       placeholder="e.g. Fiction, Science, History"
                       value="<?= htmlspecialchars($input['genre'] ?? '') ?>">
            </div>
        </div>

        <div class="form-section">
            <p class="form-section-title">Reading Status</p>
            <div class="form-group">
                <label>Have you read this book?</label>
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
                <div id="previewWrap" style="display:none" class="cover-preview-wrap">
                    <img id="previewImg" src="" alt="Cover preview">
                    <div class="cover-preview-info">
                        <strong id="previewName"></strong>
                        <span>Click below to change</span>
                    </div>
                </div>
                <label class="upload-zone">
                    <input type="file" id="cover" name="cover" accept="image/*">
                    <span class="upload-icon">🖼️</span>
                    <span class="upload-text">Click to choose a cover image</span>
                    <span class="upload-hint">JPG, PNG, GIF or WebP · max 5 MB</span>
                </label>
            </div>
        </div>

        <div class="form-section">
            <p class="form-section-title">Notes</p>
            <div class="form-group">
                <label for="notes">Personal notes <span style="font-weight:300;text-transform:none;letter-spacing:0">(optional)</span></label>
                <textarea id="notes" name="notes"
                          placeholder="Your thoughts, quotes, or reminders..."><?= htmlspecialchars($input['notes'] ?? '') ?></textarea>
            </div>
        </div>

        <div class="form-actions">
            <button type="submit" class="btn btn-primary">Add to Library</button>
            <a href="<?= APP_URL ?>/?action=index" class="btn btn-ghost">Cancel</a>
        </div>

    </form>
</div>
