-- Run this in MySQL (e.g., via phpMyAdmin) to create DB and tables
CREATE DATABASE IF NOT EXISTS `form_2021` CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci;
USE `form_2021`;

CREATE TABLE IF NOT EXISTS `users` (
  `id` INT AUTO_INCREMENT PRIMARY KEY,
  `name` VARCHAR(100) NOT NULL,
  `email` VARCHAR(150) NOT NULL UNIQUE,
  `password` VARCHAR(255) NOT NULL,
  `role` ENUM('submitter','viewer') NOT NULL DEFAULT 'viewer',
  `created_at` TIMESTAMP DEFAULT CURRENT_TIMESTAMP
);

-- If you already have the database, run this SQL once to add the role column:
-- ALTER TABLE `users` ADD COLUMN `role` ENUM('submitter','viewer') NOT NULL DEFAULT 'viewer';

CREATE TABLE IF NOT EXISTS `projects` (
  `id` INT AUTO_INCREMENT PRIMARY KEY,
  `title` VARCHAR(255) NOT NULL,
  `description` TEXT,
  `creator_id` INT NOT NULL,
  `created_at` TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
  FOREIGN KEY (`creator_id`) REFERENCES `users`(`id`) ON DELETE CASCADE
);

CREATE TABLE IF NOT EXISTS `opinions` (
  `id` INT AUTO_INCREMENT PRIMARY KEY,
  `project_id` INT NOT NULL,
  `user_id` INT NULL,
  `type` ENUM('opinion','planning') NOT NULL DEFAULT 'opinion',
  `content` TEXT NOT NULL,
  `created_at` TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
  FOREIGN KEY (`project_id`) REFERENCES `projects`(`id`) ON DELETE CASCADE,
  FOREIGN KEY (`user_id`) REFERENCES `users`(`id`) ON DELETE CASCADE
);

-- If your table already exists, run this SQL once to allow anonymous comments:
-- ALTER TABLE `opinions` MODIFY COLUMN `user_id` INT NULL;
