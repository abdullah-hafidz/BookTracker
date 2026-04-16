<?php
/**
 * Author:  Abdullah Hafidz
 * Group:   Group 1
 * Created: 17 March 2026
 * Version: 1.0
 *
 * @package Group1\BookTracker\Models
 */

/**
 * Book model.
 *
 * Handles all database operations for the `books` table.
 * Uses PDO prepared statements for all queries.
 */
class Book
{
    /**
     * @param PDO $pdo Active PDO database connection.
     */
    public function __construct(private PDO $pdo) {}

    /**
     * Fetch all books ordered by most recently added.
     *
     * @return array<int, array<string, mixed>>  Array of book rows (associative).
     */
    public function getAll(): array
    {
        $stmt = $this->pdo->query('SELECT * FROM books ORDER BY created_at DESC');
        return $stmt->fetchAll();
    }

    /**
     * Fetch a single book by its primary key.
     *
     * @param  int        $id  The book ID.
     * @return array<string, mixed>|null  The book row, or null if not found.
     */
    public function getById(int $id): ?array
    {
        $stmt = $this->pdo->prepare('SELECT * FROM books WHERE id = ?');
        $stmt->execute([$id]);
        $book = $stmt->fetch();
        return $book ?: null;
    }

    /**
     * Insert a new book record.
     *
     * @param  array<string, mixed> $data  Associative array with keys:
     *                                     title, author, genre, cover_url, status, notes.
     * @return int  The auto-incremented ID of the newly created book.
     */
    public function create(array $data): int
    {
        $stmt = $this->pdo->prepare(
            'INSERT INTO books (title, author, genre, cover_url, status, notes)
             VALUES (:title, :author, :genre, :cover_url, :status, :notes)'
        );
        $stmt->execute([
            ':title'     => $data['title'],
            ':author'    => $data['author'],
            ':genre'     => $data['genre']     ?? null,
            ':cover_url' => $data['cover_url'] ?? null,
            ':status'    => $data['status']    ?? 'unread',
            ':notes'     => $data['notes']     ?? null,
        ]);
        return (int) $this->pdo->lastInsertId();
    }

    /**
     * Update an existing book record.
     *
     * @param  int                  $id    The book ID to update.
     * @param  array<string, mixed> $data  Updated fields: title, author, genre,
     *                                     cover_url, status, notes.
     * @return void
     */
    public function update(int $id, array $data): void
    {
        $stmt = $this->pdo->prepare(
            'UPDATE books
             SET title = :title, author = :author, genre = :genre,
                 cover_url = :cover_url, status = :status, notes = :notes
             WHERE id = :id'
        );
        $stmt->execute([
            ':title'     => $data['title'],
            ':author'    => $data['author'],
            ':genre'     => $data['genre']     ?? null,
            ':cover_url' => $data['cover_url'] ?? null,
            ':status'    => $data['status']    ?? 'unread',
            ':notes'     => $data['notes']     ?? null,
            ':id'        => $id,
        ]);
    }

    /**
     * Delete a book record by ID.
     *
     * @param  int  $id  The book ID to delete.
     * @return void
     */
    public function delete(int $id): void
    {
        $stmt = $this->pdo->prepare('DELETE FROM books WHERE id = ?');
        $stmt->execute([$id]);
    }

    /**
     * Toggle a book's status between 'read' and 'unread'.
     *
     * Uses a single SQL IF() expression — no extra SELECT needed.
     *
     * @param  int  $id  The book ID to toggle.
     * @return void
     */
    public function toggleStatus(int $id): void
    {
        $stmt = $this->pdo->prepare(
            "UPDATE books
             SET status = IF(status = 'read', 'unread', 'read')
             WHERE id = ?"
        );
        $stmt->execute([$id]);
    }

}
