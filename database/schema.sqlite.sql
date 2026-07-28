-- Barangay Health Monitoring System
-- Database Schema for SQLite

PRAGMA foreign_keys = OFF;

-- 1. Users Table
DROP TABLE IF EXISTS `users`;
CREATE TABLE `users` (
    `id` INTEGER PRIMARY KEY AUTOINCREMENT,
    `username` TEXT NOT NULL UNIQUE,
    `password` TEXT NOT NULL,
    `role` TEXT CHECK(`role` IN ('Admin', 'Health Worker', 'Staff')) NOT NULL,
    `fullname` TEXT NOT NULL,
    `status` TEXT CHECK(`status` IN ('Active', 'Inactive')) DEFAULT 'Active',
    `created_at` DATETIME DEFAULT CURRENT_TIMESTAMP,
    `updated_at` DATETIME DEFAULT CURRENT_TIMESTAMP,
    `deleted_at` DATETIME DEFAULT NULL
);
CREATE INDEX `idx_username` ON `users` (`username`);
CREATE INDEX `idx_status` ON `users` (`status`);

-- 2. Residents Table
DROP TABLE IF EXISTS `residents`;
CREATE TABLE `residents` (
    `id` INTEGER PRIMARY KEY AUTOINCREMENT,
    `resident_id` TEXT NOT NULL UNIQUE,
    `first_name` TEXT NOT NULL,
    `middle_name` TEXT NULL,
    `last_name` TEXT NOT NULL,
    `gender` TEXT CHECK(`gender` IN ('Male', 'Female', 'Other')) NOT NULL,
    `birthdate` DATE NOT NULL,
    `civil_status` TEXT CHECK(`civil_status` IN ('Single', 'Married', 'Widowed', 'Divorced')) NOT NULL,
    `contact_number` TEXT NULL,
    `address` TEXT NOT NULL,
    `barangay` TEXT NOT NULL DEFAULT 'Barangay Health Center',
    `is_family_head` INTEGER DEFAULT 0,
    `status` TEXT CHECK(`status` IN ('Active', 'Archived')) DEFAULT 'Active',
    `created_at` DATETIME DEFAULT CURRENT_TIMESTAMP,
    `updated_at` DATETIME DEFAULT CURRENT_TIMESTAMP,
    `deleted_at` DATETIME DEFAULT NULL
);
CREATE INDEX `idx_resident_id` ON `residents` (`resident_id`);
CREATE INDEX `idx_resident_name` ON `residents` (`last_name`, `first_name`);
CREATE INDEX `idx_residents_status` ON `residents` (`status`);
CREATE INDEX `idx_is_family_head` ON `residents` (`is_family_head`);

-- 3. Families Table
DROP TABLE IF EXISTS `families`;
CREATE TABLE `families` (
    `id` INTEGER PRIMARY KEY AUTOINCREMENT,
    `family_no` TEXT NOT NULL UNIQUE,
    `head_resident_id` INTEGER NOT NULL,
    `address` TEXT NOT NULL,
    `occupation` TEXT NULL,
    `educational_attainment` TEXT NULL,
    `pregnancy_status` TEXT NULL,
    `family_planning_status` TEXT NULL,
    `child_feeding_type` TEXT NULL,
    `toilet_type` TEXT NULL,
    `water_source` TEXT NULL,
    `food_production_activity` TEXT NULL,
    `created_at` DATETIME DEFAULT CURRENT_TIMESTAMP,
    `updated_at` DATETIME DEFAULT CURRENT_TIMESTAMP,
    `deleted_at` DATETIME DEFAULT NULL,
    FOREIGN KEY (`head_resident_id`) REFERENCES `residents`(`id`) ON DELETE RESTRICT
);
CREATE INDEX `idx_family_no` ON `families` (`family_no`);

-- 4. Family Members Table
DROP TABLE IF EXISTS `family_members`;
CREATE TABLE `family_members` (
    `id` INTEGER PRIMARY KEY AUTOINCREMENT,
    `family_id` INTEGER NOT NULL,
    `resident_id` INTEGER NOT NULL UNIQUE,
    `relationship_to_head` TEXT NOT NULL,
    `created_at` DATETIME DEFAULT CURRENT_TIMESTAMP,
    FOREIGN KEY (`family_id`) REFERENCES `families`(`id`) ON DELETE CASCADE,
    FOREIGN KEY (`resident_id`) REFERENCES `residents`(`id`) ON DELETE RESTRICT
);

-- 5. Medicines Table (Includes Vaccines)
DROP TABLE IF EXISTS `medicines`;
CREATE TABLE `medicines` (
    `id` INTEGER PRIMARY KEY AUTOINCREMENT,
    `code` TEXT NOT NULL UNIQUE,
    `name` TEXT NOT NULL,
    `description` TEXT NULL,
    `category` TEXT CHECK(`category` IN ('Medicine', 'Vaccine', 'Supply')) NOT NULL,
    `stock_qty` INTEGER NOT NULL DEFAULT 0,
    `reorder_level` INTEGER NOT NULL DEFAULT 10,
    `created_at` DATETIME DEFAULT CURRENT_TIMESTAMP,
    `updated_at` DATETIME DEFAULT CURRENT_TIMESTAMP,
    `deleted_at` DATETIME DEFAULT NULL
);
CREATE INDEX `idx_code` ON `medicines` (`code`);
CREATE INDEX `idx_category` ON `medicines` (`category`);

-- 6. Consultations Table
DROP TABLE IF EXISTS `consultations`;
CREATE TABLE `consultations` (
    `id` INTEGER PRIMARY KEY AUTOINCREMENT,
    `consultation_no` TEXT NOT NULL UNIQUE,
    `resident_id` INTEGER NOT NULL,
    `user_id` INTEGER NOT NULL,
    `symptoms` TEXT NOT NULL,
    `diagnosis` TEXT NOT NULL,
    `treatment` TEXT NOT NULL,
    `medicine_id` INTEGER NULL,
    `medicine_qty` INTEGER NULL,
    `consultation_date` DATE NOT NULL,
    `status` TEXT CHECK(`status` IN ('Pending', 'Completed', 'Cancelled')) DEFAULT 'Completed',
    `created_at` DATETIME DEFAULT CURRENT_TIMESTAMP,
    `updated_at` DATETIME DEFAULT CURRENT_TIMESTAMP,
    `deleted_at` DATETIME DEFAULT NULL,
    FOREIGN KEY (`resident_id`) REFERENCES `residents`(`id`) ON DELETE RESTRICT,
    FOREIGN KEY (`user_id`) REFERENCES `users`(`id`) ON DELETE RESTRICT,
    FOREIGN KEY (`medicine_id`) REFERENCES `medicines`(`id`) ON DELETE SET NULL
);
CREATE INDEX `idx_consultation_no` ON `consultations` (`consultation_no`);
CREATE INDEX `idx_consultation_date` ON `consultations` (`consultation_date`);

-- 7. Immunizations Table
DROP TABLE IF EXISTS `immunizations`;
CREATE TABLE `immunizations` (
    `id` INTEGER PRIMARY KEY AUTOINCREMENT,
    `resident_id` INTEGER NOT NULL,
    `vaccine_id` INTEGER NOT NULL,
    `dose` TEXT NOT NULL,
    `date_given` DATE NULL,
    `next_schedule` DATE NULL,
    `user_id` INTEGER NOT NULL,
    `status` TEXT CHECK(`status` IN ('Upcoming', 'Missed', 'Completed')) DEFAULT 'Upcoming',
    `created_at` DATETIME DEFAULT CURRENT_TIMESTAMP,
    `updated_at` DATETIME DEFAULT CURRENT_TIMESTAMP,
    `deleted_at` DATETIME DEFAULT NULL,
    FOREIGN KEY (`resident_id`) REFERENCES `residents`(`id`) ON DELETE RESTRICT,
    FOREIGN KEY (`vaccine_id`) REFERENCES `medicines`(`id`) ON DELETE RESTRICT,
    FOREIGN KEY (`user_id`) REFERENCES `users`(`id`) ON DELETE RESTRICT
);
CREATE INDEX `idx_immunization_status` ON `immunizations` (`status`);
CREATE INDEX `idx_next_schedule` ON `immunizations` (`next_schedule`);

-- 8. Medicine Distributions Table
DROP TABLE IF EXISTS `medicine_distributions`;
CREATE TABLE `medicine_distributions` (
    `id` INTEGER PRIMARY KEY AUTOINCREMENT,
    `medicine_id` INTEGER NOT NULL,
    `resident_id` INTEGER NOT NULL,
    `quantity` INTEGER NOT NULL,
    `distribution_date` DATE NOT NULL,
    `user_id` INTEGER NOT NULL,
    `created_at` DATETIME DEFAULT CURRENT_TIMESTAMP,
    `updated_at` DATETIME DEFAULT CURRENT_TIMESTAMP,
    `deleted_at` DATETIME DEFAULT NULL,
    FOREIGN KEY (`medicine_id`) REFERENCES `medicines`(`id`) ON DELETE RESTRICT,
    FOREIGN KEY (`resident_id`) REFERENCES `residents`(`id`) ON DELETE RESTRICT,
    FOREIGN KEY (`user_id`) REFERENCES `users`(`id`) ON DELETE RESTRICT
);
CREATE INDEX `idx_distribution_date` ON `medicine_distributions` (`distribution_date`);

-- 9. Activity Logs Table
DROP TABLE IF EXISTS `activity_logs`;
CREATE TABLE `activity_logs` (
    `id` INTEGER PRIMARY KEY AUTOINCREMENT,
    `user_id` INTEGER NULL,
    `action` TEXT NOT NULL,
    `description` TEXT NOT NULL,
    `ip_address` TEXT NOT NULL,
    `user_agent` TEXT NULL,
    `created_at` DATETIME DEFAULT CURRENT_TIMESTAMP,
    FOREIGN KEY (`user_id`) REFERENCES `users`(`id`) ON DELETE SET NULL
);
CREATE INDEX `idx_activity_created_at` ON `activity_logs` (`created_at`);

PRAGMA foreign_keys = ON;
