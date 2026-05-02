<?php
/**
 * Author:  Abdullah Hafidz
 * Group:   Group 1
 * Created: 17 March 2026
 * Version: 1.0
 *
 * @package Group1\BookTracker\Controllers
 */

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
    // Index — book listing
    // -------------------------------------------------------------------------

    /**
     * Display the library index page.
     *
     * @return void
     */
    public function index(): void
    {
        $books = $this->model->getAll();

        $this->render('index', compact('books'));
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
                $this->runWrite(fn() => $this->model->create($input));
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
            $this->runWrite(fn() => $this->model->toggleStatus($id));
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
                $this->runWrite(fn() => $this->model->update($id, $input));
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
            $this->runWrite(fn() => $this->model->delete($id));
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
            $this->runWrite(fn() => $this->model->toggleStatus($id));
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
     * @param  string $name   Metric name ('S3UploadFailures' or 'DBWriteErrors').
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
     * Execute a database write and push a DBWriteErrors metric (0 = success, 1 = failure).
     *
     * Emitting on every operation ensures data points are always present in CloudWatch
     * regardless of error frequency. PDOExceptions are re-thrown after the metric is pushed
     * so the caller's error handling is unchanged.
     *
     * @param  callable $fn  The write operation to execute.
     * @return void
     */
    private function runWrite(callable $fn): void
    {
        try {
            $fn();
            $this->pushMetric('DBWriteErrors', 0.0);
        } catch (\PDOException $e) {
            error_log('DB write error: ' . $e->getMessage());
            $this->pushMetric('DBWriteErrors', 1.0);
            throw $e;
        }
    }

    /**
     * Render a view wrapped in the site layout (header + footer).
     *
     * All $data keys are extracted as local variables available in the view.
     *
     * @param  string               $view  View filename (without .php) inside views/books/.
     * @param  array<string, mixed> $data  Variables to pass to the view.
     * @return void
     */
    private function render(string $view, array $data = []): void
    {
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
     * In production (CDN_URL is set), also syncs the file to S3 via the AWS SDK.
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
            // Sync to S3 via the AWS SDK (aws.phar) — no AWS CLI required on the server.
            // Credentials resolved automatically from the IAM instance profile via IMDS.
            if (CDN_URL !== '' && class_exists('Aws\S3\S3Client')) {
                try {
                    $s3 = new Aws\S3\S3Client([
                        'region'  => 'ap-southeast-1',
                        'version' => 'latest',
                    ]);
                    $s3->putObject([
                        'Bucket'     => 'group1-static-assets',
                        'Key'        => 'uploads/' . $filename,
                        'SourceFile' => $dest,
                    ]);
                    $this->pushMetric('S3UploadFailures', 0.0);
                } catch (Aws\Exception\AwsException $e) {
                    error_log('S3 upload failed: ' . $e->getMessage());
                    $this->pushMetric('S3UploadFailures', 1.0);
                }
            }

            // Store relative path only — CDN_URL or APP_URL is prepended at render time.
            // This keeps cover_url portable: changing CDN domain never breaks existing records.
            return 'uploads/' . $filename;
        }

        return null;
    }
}
