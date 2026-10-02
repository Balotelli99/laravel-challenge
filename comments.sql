-- Tabel voor de reacties onder de video.
CREATE DATABASE IF NOT EXISTS `laravel_challenge`
    CHARACTER SET utf8mb4
    COLLATE utf8mb4_unicode_ci;

USE `laravel_challenge`;

CREATE TABLE IF NOT EXISTS `comments` (
    `id` INT UNSIGNED NOT NULL AUTO_INCREMENT,
    `name` VARCHAR(100) NOT NULL,
    `email` VARCHAR(254) NOT NULL,
    `comment` TEXT NOT NULL,
    `created_at` TIMESTAMP NOT NULL DEFAULT CURRENT_TIMESTAMP,
    PRIMARY KEY (`id`),
    INDEX `idx_comments_created_at` (`created_at`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;