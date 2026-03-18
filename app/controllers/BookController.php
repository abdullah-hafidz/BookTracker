<?php
/**
 * Author:  Abdullah Hafidz
 * Group:   Group 1
 * Created: 17 March 2026
 * Version: 1.0
 *
 * @package Group1\BookTracker\Controllers
 */

require_once __DIR__ . '/../models/Setting.php';

// AWS SDK for PHP — downloaded to lib/aws.phar at deploy time.
// Absent in local dev (no CDN_URL) so the conditional require is safe.
if (file_exists(__DIR__ . '/../../lib/aws.phar')) {
    require_once __DIR__ . '/../../lib/aws.phar';
}

/**
 * BookController
 *
 * Handles all HTTP actions for the book tracking application:
 * listing, adding, editing, viewing, deleting, and toggling books.
 * Also resolves the user's theme preference before rendering every view.
 */
class BookController
{
    /** @var Book $model  The Book model instance for all DB operations. */
    private Book $model;

    /**
     * @param PDO $pdo  Active database connection passed from the front controller.
     */
    public function __construct(PDO $pdo)
    {
        $this->model = new Book($pdo);
    }

    // -------------------------------------------------------------------------
    // Index — book listing with optional status filter
    // -------------------------------------------------------------------------

    /**
     * Display the library index page with optional status filter.
     *
     * Reads ?filter=all|read|unread from the query string.
     * Passes books array, stats summary, and active filter to the view.
     *
     * @return void
     */
    public function index(): void
    {
        $filter = $_GET['filter'] ?? 'all';
        $status = in_array($filter, ['read', 'unread'], true) ? $filter : null;

        $books = $this->model->getAll($status);
        $stats = $this->model->getStats();

        $this->pushMetric('PageView');
        $this->render('index', compact('books', 'stats', 'filter'));
    }

    // -------------------------------------------------------------------------
    // Add — show form (GET) or create book (POST)
    // -------------------------------------------------------------------------

    /**
     * Show the add-book form (GET) or process a new book submission (POST).
     *
     * On valid POST: sanitizes input, handles cover upload, creates the record,
     * then redirects to the library index.
     * On invalid POST: re-renders the form with validation errors and previous input.
     *
     * @return void
     */
    public function add(): void
    {
        $errors = [];
        $input  = [];

        if ($_SERVER['REQUEST_METHOD'] === 'POST') {
            $this->validateCsrf();
            $input  = $this->sanitizeInput($_POST);
            $errors = $this->validateInput($input);

            if (empty($errors)) {
                $input['cover_url'] = $this->handleUpload();
                $this->model->create($input);
                $this->pushMetric('BooksAdded');
                $this->redirect('/?action=index');
            }
        }

        $this->render('add', compact('errors', 'input'));
    }

    // -------------------------------------------------------------------------
    // Edit — show pre-filled form (GET) or update book (POST)
    // -------------------------------------------------------------------------

    /**
     * Show the edit form pre-filled with existing data (GET), or update the book (POST).
     *
     * Also handles the quick status toggle POST from the listing page
     * (when $_POST['toggle_status'] is set).
     * Redirects to index if the book ID is not found.
     *
     * @param  int  $id  The ID of the book to edit.
     * @return void
     */
    public function edit(int $id): void
    {
        $book = $this->model->getById($id);
        if (!$book) {
            $this->redirect('/?action=index');
        }

        // Handle quick toggle from listing page
        if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['toggle_status'])) {
            $this->validateCsrf();
            $this->model->toggleStatus($id);
            $this->redirect('/?action=index');
        }

        $errors = [];
        $input  = $book; // pre-fill form with existing data

        if ($_SERVER['REQUEST_METHOD'] === 'POST') {
            $this->validateCsrf();
            $input  = $this->sanitizeInput($_POST);
            $errors = $this->validateInput($input);

            if (empty($errors)) {
                // Keep existing cover if no new file uploaded
                $newCover = $this->handleUpload();
                $input['cover_url'] = $newCover ?: $book['cover_url'];
                $this->model->update($id, $input);
                $this->redirect('/?action=view&id=' . $id);
            }
        }

        $this->render('edit', compact('book', 'errors', 'input'));
    }

    // -------------------------------------------------------------------------
    // View — single book detail page
    // -------------------------------------------------------------------------

    /**
     * Display the detail page for a single book.
     *
     * Redirects to the library index if the book is not found.
     *
     * @param  int  $id  The ID of the book to display.
     * @return void
     */
    public function view(int $id): void
    {
        $book = $this->model->getById($id);
        if (!$book) {
            $this->redirect('/?action=index');
        }

        $this->render('view', compact('book'));
    }

    // -------------------------------------------------------------------------
    // Delete — POST only, then redirect
    // -------------------------------------------------------------------------

    /**
     * Delete a book. Accepts POST requests only.
     *
     * Reads the book ID from $_POST['id'].
     * Non-POST requests are redirected to the index without action.
     *
     * @return void
     */
    public function delete(): void
    {
        if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
            $this->redirect('/?action=index');
        }

        $this->validateCsrf();
        $id = (int) ($_POST['id'] ?? 0);
        if ($id > 0) {
            $this->model->delete($id);
            $this->pushMetric('BooksDeleted');
        }

        $this->redirect('/?action=index');
    }

    // -------------------------------------------------------------------------
    // Toggle — POST only, flip read/unread then redirect
    // -------------------------------------------------------------------------

    /**
     * Toggle a book's read/unread status. Accepts POST requests only.
     *
     * Reads the book ID from $_POST['id'].
     * Non-POST requests are redirected to the index without action.
     *
     * @return void
     */
    public function toggle(): void
    {
        if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
            $this->redirect('/?action=index');
        }

        $this->validateCsrf();
        $id = (int) ($_POST['id'] ?? 0);
        if ($id > 0) {
            $this->model->toggleStatus($id);
            $this->pushMetric('StatusToggled');
        }

        $this->redirect('/?action=index');
    }

    // -------------------------------------------------------------------------
    // Private helpers
    // -------------------------------------------------------------------------

    /**
     * Push a custom metric to CloudWatch via the AWS SDK for PHP.
     *
     * Uses the EC2 IAM instance role for authentication — no credentials in code.
     * Silent no-op in local dev (CDN_URL is empty or the SDK phar is not present).
     * Errors are logged but never surface to the user.
     *
     * @param  string $name   Metric name (e.g. 'BooksAdded').
     * @param  float  $value  Metric value (default 1.0).
     * @return void
     */
    private function pushMetric(string $name, float $value = 1.0): void
    {
        if (CDN_URL === '' || !class_exists('Aws\CloudWatch\CloudWatchClient')) {
            return; // Local dev — SDK not loaded / no IAM role
        }

        try {
            $client = new Aws\CloudWatch\CloudWatchClient([
                'region'  => 'ap-southeast-1',
                'version' => 'latest',
            ]);

            $client->putMetricData([
                'Namespace'  => 'Group1/AppMetrics',
                'MetricData' => [[
                    'MetricName' => $name,
                    'Value'      => $value,
                    'Unit'       => 'Count',
                ]],
            ]);
        } catch (Aws\Exception\AwsException $e) {
            error_log('CloudWatch metric failed: ' . $e->getMessage());
        }
    }

    /**
     * Render a view wrapped in the site layout (header + footer).
     *
     * Resolves the current theme from the database and injects it into $data
     * so header.php can set data-theme on <html> server-side (no flash).
     * All $data keys are extracted as local variables available in the view.
     *
     * @param  string               $view  View filename (without .php) inside views/books/.
     * @param  array<string, mixed> $data  Variables to pass to the view.
     * @return void
     */
    private function render(string $view, array $data = []): void
    {
        // Resolve theme server-side so header.php can set data-theme on <html>
        $setting = new Setting($this->model->getPdo());
        $data['theme'] = $setting->get('theme', 'dark');

        // Inject CSRF token so views can embed it in form hidden inputs
        $data['csrfToken'] = $_SESSION['csrf_token'] ?? '';

        extract($data);
        require __DIR__ . '/../views/layout/header.php';
        require __DIR__ . "/../views/books/{$view}.php";
        require __DIR__ . '/../views/layout/footer.php';
    }

    /**
     * Redirect to a path relative to APP_URL and terminate execution.
     *
     * @param  string $path  Path starting with '/' (e.g. '/?action=index').
     * @return never
     */
    private function redirect(string $path): never
    {
        header('Location: ' . APP_URL . $path);
        exit;
    }

    /**
     * Validate the CSRF token submitted with a POST request.
     *
     * Compares the submitted token against the session token using a
     * constant-time comparison to prevent timing attacks.
     * Terminates with 403 if the token is missing or does not match.
     *
     * @return void
     */
    private function validateCsrf(): void
    {
        $submitted = $_POST['csrf_token'] ?? '';
        $expected  = $_SESSION['csrf_token'] ?? '';

        if (!hash_equals($expected, $submitted)) {
            http_response_code(403);
            exit('Invalid CSRF token.');
        }
    }

    /**
     * Sanitize raw POST data into safe, typed input.
     *
     * Trims all text fields and validates the status enum against allowed values.
     *
     * @param  array<string, mixed> $post  Raw $_POST data.
     * @return array{title: string, author: string, genre: string, notes: string, status: string}
     */
    private function sanitizeInput(array $post): array
    {
        return [
            'title'  => trim($post['title']  ?? ''),
            'author' => trim($post['author'] ?? ''),
            'genre'  => trim($post['genre']  ?? ''),
            'notes'  => trim($post['notes']  ?? ''),
            'status' => in_array($post['status'] ?? '', ['read', 'unread'], true)
                            ? $post['status']
                            : 'unread',
        ];
    }

    /**
     * Validate sanitized input and return any error messages.
     *
     * @param  array<string, string> $input  Sanitized input from sanitizeInput().
     * @return string[]  Array of human-readable error messages. Empty array means valid.
     */
    private function validateInput(array $input): array
    {
        $errors = [];
        if ($input['title'] === '') {
            $errors[] = 'Title is required.';
        }
        if ($input['author'] === '') {
            $errors[] = 'Author is required.';
        }
        return $errors;
    }

    /**
     * Handle a cover image upload from $_FILES['cover'].
     *
     * Validates MIME type (jpeg/png/gif/webp) and file size (max 5 MB).
     * Generates a unique filename and moves the file to /uploads/.
     * In production (CDN_URL is set), also syncs the file to S3 via the AWS CLI.
     * Returns a relative path only — CDN_URL or APP_URL is prepended at render time in views.
     *
     * @return string|null  Relative path of the uploaded file (e.g. 'uploads/cover_xxx.jpg'),
     *                      or null if no file was uploaded or validation failed.
     */
    private function handleUpload(): ?string
    {
        if (empty($_FILES['cover']['name'])) {
            return null;
        }

        $file    = $_FILES['cover'];
        $allowed = ['image/jpeg', 'image/png', 'image/gif', 'image/webp'];

        if (!in_array($file['type'], $allowed, true)) {
            return null; // silently ignore invalid types
        }

        if ($file['size'] > 5 * 1024 * 1024) { // 5 MB max
            return null;
        }

        $ext      = pathinfo($file['name'], PATHINFO_EXTENSION);
        $filename = uniqid('cover_', true) . '.' . strtolower($ext);
        $dest     = __DIR__ . '/../../uploads/' . $filename;

        if (!is_dir(dirname($dest))) {
            mkdir(dirname($dest), 0755, true);
        }

        if (move_uploaded_file($file['tmp_name'], $dest)) {
            // Sync to S3 if running on EC2 with an IAM role (CDN_URL is set in production).
            // Uses AWS CLI — authenticated automatically via the EC2 instance profile.
            // No credentials in code; silent failure is intentional (local copy already saved).
            if (CDN_URL !== '') {
                $s3Path = 's3://group1-static-assets/uploads/' . escapeshellarg($filename);
                $localPath = escapeshellarg($dest);
                shell_exec("aws s3 cp {$localPath} {$s3Path} --region ap-southeast-1 2>/dev/null");
            }

            // Store relative path only — CDN_URL or APP_URL is prepended at render time.
            // This keeps cover_url portable: changing CDN domain never breaks existing records.
            return 'uploads/' . $filename;
        }

        return null;
    }
}
