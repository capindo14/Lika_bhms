-- Barangay Health Monitoring System
-- Database Schema (Normalized to 3NF)

CREATE DATABASE IF NOT EXISTS `barangay_health` DEFAULT CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci;
USE `barangay_health`;

-- Disable foreign key checks during setup
SET FOREIGN_KEY_CHECKS = 0;

-- 1. Users Table
DROP TABLE IF EXISTS `users`;
CREATE TABLE `users` (
    `id` INT AUTO_INCREMENT PRIMARY KEY,
    `username` VARCHAR(50) NOT NULL UNIQUE,
    `password` VARCHAR(255) NOT NULL,
    `role` ENUM('Admin', 'Health Worker', 'Staff') NOT NULL,
    `fullname` VARCHAR(100) NOT NULL,
    `status` ENUM('Active', 'Inactive') DEFAULT 'Active',
    `created_at` TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    `updated_at` TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
    `deleted_at` TIMESTAMP NULL DEFAULT NULL,
    INDEX `idx_username` (`username`),
    INDEX `idx_status` (`status`)
) ENGINE=InnoDB;

-- 2. Residents Table
DROP TABLE IF EXISTS `residents`;
CREATE TABLE `residents` (
    `id` INT AUTO_INCREMENT PRIMARY KEY,
    `resident_id` VARCHAR(50) NOT NULL UNIQUE,
    `first_name` VARCHAR(50) NOT NULL,
    `middle_name` VARCHAR(50) NULL,
    `last_name` VARCHAR(50) NOT NULL,
    `gender` ENUM('Male', 'Female', 'Other') NOT NULL,
    `birthdate` DATE NOT NULL,
    `civil_status` ENUM('Single', 'Married', 'Widowed', 'Divorced') NOT NULL,
    `contact_number` VARCHAR(20) NULL,
    `address` TEXT NOT NULL,
    `barangay` VARCHAR(100) NOT NULL DEFAULT 'Barangay Health Center',
    `is_family_head` TINYINT(1) DEFAULT 0,
    `status` ENUM('Active', 'Archived') DEFAULT 'Active',
    `created_at` TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    `updated_at` TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
    `deleted_at` TIMESTAMP NULL DEFAULT NULL,
    INDEX `idx_resident_id` (`resident_id`),
    INDEX `idx_resident_name` (`last_name`, `first_name`),
    INDEX `idx_status` (`status`),
    INDEX `idx_is_family_head` (`is_family_head`)
) ENGINE=InnoDB;

-- 3. Families Table
DROP TABLE IF EXISTS `families`;
CREATE TABLE `families` (
    `id` INT AUTO_INCREMENT PRIMARY KEY,
    `family_no` VARCHAR(50) NOT NULL UNIQUE,
    `head_resident_id` INT NOT NULL,
    `address` TEXT NOT NULL,
    `occupation` VARCHAR(100) NULL,
    `educational_attainment` VARCHAR(100) NULL,
    `pregnancy_status` VARCHAR(50) NULL,
    `family_planning_status` VARCHAR(100) NULL,
    `child_feeding_type` VARCHAR(100) NULL,
    `toilet_type` VARCHAR(100) NULL,
    `water_source` VARCHAR(100) NULL,
    `food_production_activity` VARCHAR(100) NULL,
    `created_at` TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    `updated_at` TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
    `deleted_at` TIMESTAMP NULL DEFAULT NULL,
    FOREIGN KEY (`head_resident_id`) REFERENCES `residents`(`id`) ON DELETE RESTRICT,
    INDEX `idx_family_no` (`family_no`)
) ENGINE=InnoDB;

-- 4. Family Members Table
DROP TABLE IF EXISTS `family_members`;
CREATE TABLE `family_members` (
    `id` INT AUTO_INCREMENT PRIMARY KEY,
    `family_id` INT NOT NULL,
    `resident_id` INT NOT NULL UNIQUE,
    `relationship_to_head` VARCHAR(50) NOT NULL,
    `created_at` TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    FOREIGN KEY (`family_id`) REFERENCES `families`(`id`) ON DELETE CASCADE,
    FOREIGN KEY (`resident_id`) REFERENCES `residents`(`id`) ON DELETE RESTRICT
) ENGINE=InnoDB;

-- 5. Medicines Table (Includes Vaccines)
DROP TABLE IF EXISTS `medicines`;
CREATE TABLE `medicines` (
    `id` INT AUTO_INCREMENT PRIMARY KEY,
    `code` VARCHAR(50) NOT NULL UNIQUE,
    `name` VARCHAR(100) NOT NULL,
    `description` TEXT NULL,
    `category` ENUM('Medicine', 'Vaccine', 'Supply') NOT NULL,
    `stock_qty` INT NOT NULL DEFAULT 0,
    `reorder_level` INT NOT NULL DEFAULT 10,
    `created_at` TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    `updated_at` TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
    `deleted_at` TIMESTAMP NULL DEFAULT NULL,
    INDEX `idx_code` (`code`),
    INDEX `idx_category` (`category`)
) ENGINE=InnoDB;

-- 6. Consultations Table
DROP TABLE IF EXISTS `consultations`;
CREATE TABLE `consultations` (
    `id` INT AUTO_INCREMENT PRIMARY KEY,
    `consultation_no` VARCHAR(50) NOT NULL UNIQUE,
    `resident_id` INT NOT NULL,
    `user_id` INT NOT NULL,
    `symptoms` TEXT NOT NULL,
    `diagnosis` TEXT NOT NULL,
    `treatment` TEXT NOT NULL,
    `medicine_id` INT NULL,
    `medicine_qty` INT NULL,
    `consultation_date` DATE NOT NULL,
    `status` ENUM('Pending', 'Completed', 'Cancelled') DEFAULT 'Completed',
    `created_at` TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    `updated_at` TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
    `deleted_at` TIMESTAMP NULL DEFAULT NULL,
    FOREIGN KEY (`resident_id`) REFERENCES `residents`(`id`) ON DELETE RESTRICT,
    FOREIGN KEY (`user_id`) REFERENCES `users`(`id`) ON DELETE RESTRICT,
    FOREIGN KEY (`medicine_id`) REFERENCES `medicines`(`id`) ON DELETE SET NULL,
    INDEX `idx_consultation_no` (`consultation_no`),
    INDEX `idx_consultation_date` (`consultation_date`)
) ENGINE=InnoDB;

-- 7. Immunizations Table
DROP TABLE IF EXISTS `immunizations`;
CREATE TABLE `immunizations` (
    `id` INT AUTO_INCREMENT PRIMARY KEY,
    `resident_id` INT NOT NULL,
    `vaccine_id` INT NOT NULL,
    `dose` VARCHAR(50) NOT NULL,
    `date_given` DATE NULL,
    `next_schedule` DATE NULL,
    `user_id` INT NOT NULL,
    `status` ENUM('Upcoming', 'Missed', 'Completed') DEFAULT 'Upcoming',
    `created_at` TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    `updated_at` TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
    `deleted_at` TIMESTAMP NULL DEFAULT NULL,
    FOREIGN KEY (`resident_id`) REFERENCES `residents`(`id`) ON DELETE RESTRICT,
    FOREIGN KEY (`vaccine_id`) REFERENCES `medicines`(`id`) ON DELETE RESTRICT,
    FOREIGN KEY (`user_id`) REFERENCES `users`(`id`) ON DELETE RESTRICT,
    INDEX `idx_status` (`status`),
    INDEX `idx_next_schedule` (`next_schedule`)
) ENGINE=InnoDB;

-- 8. Medicine Distributions Table
DROP TABLE IF EXISTS `medicine_distributions`;
CREATE TABLE `medicine_distributions` (
    `id` INT AUTO_INCREMENT PRIMARY KEY,
    `medicine_id` INT NOT NULL,
    `resident_id` INT NOT NULL,
    `quantity` INT NOT NULL,
    `distribution_date` DATE NOT NULL,
    `user_id` INT NOT NULL,
    `created_at` TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    `updated_at` TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
    `deleted_at` TIMESTAMP NULL DEFAULT NULL,
    FOREIGN KEY (`medicine_id`) REFERENCES `medicines`(`id`) ON DELETE RESTRICT,
    FOREIGN KEY (`resident_id`) REFERENCES `residents`(`id`) ON DELETE RESTRICT,
    FOREIGN KEY (`user_id`) REFERENCES `users`(`id`) ON DELETE RESTRICT,
    INDEX `idx_distribution_date` (`distribution_date`)
) ENGINE=InnoDB;

-- 9. Activity Logs Table
DROP TABLE IF EXISTS `activity_logs`;
CREATE TABLE `activity_logs` (
    `id` INT AUTO_INCREMENT PRIMARY KEY,
    `user_id` INT NULL,
    `action` VARCHAR(100) NOT NULL,
    `description` TEXT NOT NULL,
    `ip_address` VARCHAR(45) NOT NULL,
    `user_agent` VARCHAR(255) NULL,
    `created_at` TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    FOREIGN KEY (`user_id`) REFERENCES `users`(`id`) ON DELETE SET NULL,
    INDEX `idx_created_at` (`created_at`)
) ENGINE=InnoDB;

-- Re-enable foreign key checks
SET FOREIGN_KEY_CHECKS = 1;
