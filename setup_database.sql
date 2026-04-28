-- Setup script for fitness_tracker database
-- Run this in phpMyAdmin or MySQL CLI to create/recreate the tables

CREATE DATABASE IF NOT EXISTS `fitness_tracker`;
USE `fitness_tracker`;

-- Drop corrupted tables if they exist
DROP TABLE IF EXISTS `daily_activity`;
DROP TABLE IF EXISTS `goals`;
DROP TABLE IF EXISTS `users`;

-- Users table
CREATE TABLE `users` (
    `user_id` INT AUTO_INCREMENT PRIMARY KEY,
    `name` VARCHAR(100) NOT NULL,
    `age` INT NOT NULL,
    `email` VARCHAR(150) NOT NULL UNIQUE,
    `password` VARCHAR(255) NOT NULL,
    `weight` DECIMAL(5,1),
    `height` DECIMAL(5,1)
) ENGINE=InnoDB;

-- Daily activity table
CREATE TABLE `daily_activity` (
    `id` INT AUTO_INCREMENT PRIMARY KEY,
    `user_id` INT NOT NULL,
    `date` DATE NOT NULL,
    `steps` INT DEFAULT 0,
    `calories` INT DEFAULT 0,
    `distance` DECIMAL(6,1) DEFAULT 0,
    `sleep` DECIMAL(3,1) DEFAULT 0,
    FOREIGN KEY (`user_id`) REFERENCES `users`(`user_id`) ON DELETE CASCADE,
    UNIQUE KEY `unique_user_date` (`user_id`, `date`)
) ENGINE=InnoDB;

-- Goals table
CREATE TABLE `goals` (
    `user_id` INT PRIMARY KEY,
    `daily_step_goal` INT NOT NULL,
    FOREIGN KEY (`user_id`) REFERENCES `users`(`user_id`) ON DELETE CASCADE
) ENGINE=InnoDB;
