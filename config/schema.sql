-- Girish Pawar Portfolio Schema
-- Production Ready MySQL 8.0 / MariaDB Compatible

CREATE DATABASE IF NOT EXISTS `girish_portfolio` CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci;
USE `girish_portfolio`;

-- 1. Admin Users Table
CREATE TABLE IF NOT EXISTS `admin_users` (
    `id` INT AUTO_INCREMENT PRIMARY KEY,
    `username` VARCHAR(50) NOT NULL UNIQUE,
    `email` VARCHAR(100) NOT NULL,
    `password_hash` VARCHAR(255) NOT NULL,
    `last_login` DATETIME NULL,
    `failed_login_attempts` INT DEFAULT 0,
    `lockout_until` DATETIME NULL,
    `created_at` TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    `updated_at` TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- 2. Personal Profile Table
CREATE TABLE IF NOT EXISTS `profile` (
    `id` INT AUTO_INCREMENT PRIMARY KEY,
    `full_name` VARCHAR(100) NOT NULL,
    `primary_title` VARCHAR(100) NOT NULL DEFAULT 'Web Developer',
    `headline_titles` TEXT NOT NULL, -- JSON array of animated titles
    `bio_short` TEXT NOT NULL,
    `bio_full` TEXT NOT NULL,
    `avatar_url` VARCHAR(255) NULL,
    `location` VARCHAR(100) DEFAULT 'Maharashtra, India',
    `email` VARCHAR(100) NOT NULL,
    `phone` VARCHAR(50) NULL,
    `available_for_hire` TINYINT(1) DEFAULT 1,
    `resume_url` VARCHAR(255) NULL,
    `approach_text` TEXT NULL,
    `learning_text` TEXT NULL,
    `career_goals` TEXT NULL,
    `updated_at` TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- 3. Skill Categories Table
CREATE TABLE IF NOT EXISTS `skill_categories` (
    `id` INT AUTO_INCREMENT PRIMARY KEY,
    `name` VARCHAR(50) NOT NULL,
    `slug` VARCHAR(50) NOT NULL UNIQUE,
    `sort_order` INT DEFAULT 0,
    `created_at` TIMESTAMP DEFAULT CURRENT_TIMESTAMP
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- 4. Skills Table
CREATE TABLE IF NOT EXISTS `skills` (
    `id` INT AUTO_INCREMENT PRIMARY KEY,
    `category_id` INT NOT NULL,
    `name` VARCHAR(100) NOT NULL,
    `proficiency_level` VARCHAR(50) DEFAULT 'Proficient',
    `proficiency_percent` INT DEFAULT 85,
    `icon_class` VARCHAR(100) NULL,
    `sort_order` INT DEFAULT 0,
    `is_featured` TINYINT(1) DEFAULT 1,
    `created_at` TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    INDEX `idx_skill_cat` (`category_id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- 5. Projects Table
CREATE TABLE IF NOT EXISTS `projects` (
    `id` INT AUTO_INCREMENT PRIMARY KEY,
    `title` VARCHAR(150) NOT NULL,
    `slug` VARCHAR(150) NOT NULL UNIQUE,
    `category` VARCHAR(100) NOT NULL,
    `short_description` TEXT NOT NULL,
    `cover_image` VARCHAR(255) NULL,
    `logo_url` VARCHAR(255) NULL,
    `tech_stack` TEXT NOT NULL, -- JSON array or comma separated
    `github_url` VARCHAR(255) DEFAULT '',
    `demo_url` VARCHAR(255) DEFAULT '',
    `status` VARCHAR(50) DEFAULT 'Active',
    `development_date` VARCHAR(50) NULL,
    `key_features` TEXT NULL,
    `challenges` TEXT NULL,
    `solutions` TEXT NULL,
    `results` TEXT NULL,
    `case_study_content` TEXT NULL,
    `screenshots` TEXT NULL, -- JSON array of file paths
    `sort_order` INT DEFAULT 0,
    `is_featured` TINYINT(1) DEFAULT 1,
    `is_visible` TINYINT(1) DEFAULT 1,
    `created_at` TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    `updated_at` TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
    INDEX `idx_project_cat` (`category`),
    INDEX `idx_project_visible` (`is_visible`, `sort_order`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- 6. Experience Claims Table
CREATE TABLE IF NOT EXISTS `experience_claims` (
    `id` INT AUTO_INCREMENT PRIMARY KEY,
    `area` VARCHAR(100) NOT NULL,
    `duration` VARCHAR(50) NOT NULL,
    `description` TEXT NULL,
    `sort_order` INT DEFAULT 0,
    `created_at` TIMESTAMP DEFAULT CURRENT_TIMESTAMP
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- 7. Internships Table
CREATE TABLE IF NOT EXISTS `internships` (
    `id` INT AUTO_INCREMENT PRIMARY KEY,
    `company` VARCHAR(150) NOT NULL,
    `location` VARCHAR(100) NOT NULL,
    `domain` VARCHAR(100) NOT NULL,
    `technologies` VARCHAR(255) NOT NULL,
    `role_description` TEXT NOT NULL,
    `start_date` VARCHAR(50) NULL,
    `end_date` VARCHAR(50) NULL,
    `sort_order` INT DEFAULT 0,
    `created_at` TIMESTAMP DEFAULT CURRENT_TIMESTAMP
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- 8. Education Table
CREATE TABLE IF NOT EXISTS `education` (
    `id` INT AUTO_INCREMENT PRIMARY KEY,
    `degree` VARCHAR(150) NOT NULL,
    `institution` VARCHAR(200) NOT NULL,
    `score` VARCHAR(50) NOT NULL,
    `duration` VARCHAR(100) NOT NULL,
    `description` TEXT NULL,
    `sort_order` INT DEFAULT 0,
    `created_at` TIMESTAMP DEFAULT CURRENT_TIMESTAMP
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- 9. Certifications & Achievements Table
CREATE TABLE IF NOT EXISTS `certifications` (
    `id` INT AUTO_INCREMENT PRIMARY KEY,
    `title` VARCHAR(200) NOT NULL,
    `certificate_type` VARCHAR(100) NOT NULL,
    `issuing_organization` VARCHAR(200) NOT NULL,
    `issue_date` VARCHAR(50) NOT NULL,
    `rank_achievement` VARCHAR(100) NULL,
    `credential_id` VARCHAR(100) NULL,
    `verification_url` VARCHAR(255) NULL,
    `image_url` VARCHAR(255) NULL,
    `description` TEXT NULL,
    `sort_order` INT DEFAULT 0,
    `created_at` TIMESTAMP DEFAULT CURRENT_TIMESTAMP
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- 10. Social Links Table
CREATE TABLE IF NOT EXISTS `social_links` (
    `id` INT AUTO_INCREMENT PRIMARY KEY,
    `platform` VARCHAR(50) NOT NULL,
    `display_label` VARCHAR(100) NOT NULL,
    `url` VARCHAR(255) NOT NULL,
    `icon_class` VARCHAR(100) NOT NULL,
    `is_visible` TINYINT(1) DEFAULT 1,
    `sort_order` INT DEFAULT 0,
    `created_at` TIMESTAMP DEFAULT CURRENT_TIMESTAMP
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- 11. Contact Inquiries Table
CREATE TABLE IF NOT EXISTS `inquiries` (
    `id` INT AUTO_INCREMENT PRIMARY KEY,
    `name` VARCHAR(100) NOT NULL,
    `email` VARCHAR(150) NOT NULL,
    `subject` VARCHAR(200) NOT NULL,
    `message` TEXT NOT NULL,
    `ip_address` VARCHAR(45) NULL,
    `is_read` TINYINT(1) DEFAULT 0,
    `created_at` TIMESTAMP DEFAULT CURRENT_TIMESTAMP
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- 12. Site Settings Table
CREATE TABLE IF NOT EXISTS `settings` (
    `id` INT AUTO_INCREMENT PRIMARY KEY,
    `setting_key` VARCHAR(100) NOT NULL UNIQUE,
    `setting_value` TEXT NULL,
    `updated_at` TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
