CREATE DATABASE IF NOT EXISTS group1_books
    CHARACTER SET utf8mb4
    COLLATE utf8mb4_unicode_ci;

USE group1_books;

CREATE TABLE IF NOT EXISTS books (
    id          INT AUTO_INCREMENT PRIMARY KEY,
    title       VARCHAR(255)                    NOT NULL,
    author      VARCHAR(255)                    NOT NULL,
    genre       VARCHAR(100)                    DEFAULT NULL,
    cover_url   VARCHAR(500)                    DEFAULT NULL,
    status      ENUM('unread','read')           NOT NULL DEFAULT 'unread',
    notes       TEXT                            DEFAULT NULL,
    created_at  TIMESTAMP                       DEFAULT CURRENT_TIMESTAMP,
    updated_at  TIMESTAMP                       DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP
);

CREATE TABLE IF NOT EXISTS settings (
    `key`   VARCHAR(50)  NOT NULL,
    `value` VARCHAR(255) NOT NULL,
    PRIMARY KEY (`key`)
);

INSERT IGNORE INTO settings (`key`, `value`) VALUES ('theme', 'dark');
