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
 * Setting model.
 *
 * Provides a simple key/value store backed by the `settings` MySQL table.
 * Used to persist global app preferences (e.g. theme) across devices.
 */
class Setting
{
    /**
     * @param PDO $pdo Active PDO database connection.
     */
    public function __construct(private PDO $pdo) {}

    /**
     * Retrieve a setting value by key.
     *
     * @param  string $key      The setting key (e.g. 'theme').
     * @param  string $default  Fallback value if the key does not exist.
     * @return string           The stored value, or $default if not found.
     */
    public function get(string $key, string $default = ''): string
    {
        $stmt = $this->pdo->prepare('SELECT `value` FROM settings WHERE `key` = ?');
        $stmt->execute([$key]);
        $row = $stmt->fetch();
        return $row ? $row['value'] : $default;
    }

    /**
     * Persist a setting value (insert or update).
     *
     * Uses MySQL's ON DUPLICATE KEY UPDATE so a single call handles both
     * first-time inserts and subsequent updates.
     *
     * @param  string $key    The setting key (e.g. 'theme').
     * @param  string $value  The value to store (e.g. 'dark' or 'light').
     * @return void
     */
    public function set(string $key, string $value): void
    {
        $stmt = $this->pdo->prepare(
            'INSERT INTO settings (`key`, `value`) VALUES (?, ?)
             ON DUPLICATE KEY UPDATE `value` = VALUES(`value`)'
        );
        $stmt->execute([$key, $value]);
    }
}
