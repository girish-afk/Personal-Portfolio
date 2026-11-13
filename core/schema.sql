-- ========================================================================
-- Girish Pawar — Personal Developer Portfolio & CMS Database Schema
-- Production-Ready MySQL 8.0 / MariaDB 10.4+ Compatible
-- Single Authoritative Source of Truth
-- 
-- IMPORTANT FOR INFINITYFREE DEPLOYMENT:
-- Do NOT add CREATE DATABASE or USE statements here.
-- InfinityFree creates your database automatically via vPanel.
-- This file is portable and can be imported directly into any database name
-- via phpMyAdmin or executed programmatically through PHP PDO.
-- ========================================================================

SET FOREIGN_KEY_CHECKS = 0;
SET SQL_MODE = "NO_AUTO_VALUE_ON_ZERO";
SET time_zone = "+05:30";

-- ------------------------------------------------------------------------
-- 1. Table: admin_users
-- Private administrative owner authentication accounts
-- ------------------------------------------------------------------------
CREATE TABLE IF NOT EXISTS `admin_users` (
    `id` INT AUTO_INCREMENT PRIMARY KEY,
    `username` VARCHAR(50) NOT NULL,
    `email` VARCHAR(100) NOT NULL,
    `password_hash` VARCHAR(255) NOT NULL,
    `last_login` DATETIME NULL DEFAULT NULL,
    `failed_login_attempts` INT NOT NULL DEFAULT 0,
    `lockout_until` DATETIME NULL DEFAULT NULL,
    `created_at` TIMESTAMP NOT NULL DEFAULT CURRENT_TIMESTAMP,
    `updated_at` TIMESTAMP NOT NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
    UNIQUE KEY `uk_admin_username` (`username`),
    INDEX `idx_admin_login` (`username`, `lockout_until`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci COMMENT='Owner authentication and brute-force lockout states';

-- ------------------------------------------------------------------------
-- 2. Table: profile
-- Primary personal profile, hero headline transitions, and bio data
-- ------------------------------------------------------------------------
CREATE TABLE IF NOT EXISTS `profile` (
    `id` INT AUTO_INCREMENT PRIMARY KEY,
    `full_name` VARCHAR(100) NOT NULL,
    `primary_title` VARCHAR(100) NOT NULL DEFAULT 'Web Developer',
    `headline_titles` TEXT NOT NULL COMMENT 'JSON array of rotating hero headline titles',
    `bio_short` TEXT NOT NULL,
    `bio_full` TEXT NOT NULL,
    `avatar_url` VARCHAR(255) NULL DEFAULT 'assets/img/girish-avatar.svg',
    `location` VARCHAR(100) NOT NULL DEFAULT 'Maharashtra, India',
    `email` VARCHAR(100) NOT NULL,
    `phone` VARCHAR(50) NULL DEFAULT '',
    `available_for_hire` TINYINT(1) NOT NULL DEFAULT 1,
    `resume_url` VARCHAR(255) NULL DEFAULT 'uploads/resume/Girish_Pawar_Resume.pdf',
    `approach_text` TEXT NULL,
    `learning_text` TEXT NULL,
    `career_goals` TEXT NULL,
    `created_at` TIMESTAMP NOT NULL DEFAULT CURRENT_TIMESTAMP,
    `updated_at` TIMESTAMP NOT NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci COMMENT='Authoritative personal profile and biographic narrative';

-- ------------------------------------------------------------------------
-- 3. Table: skill_categories
-- Classification taxonomy for technical proficiencies
-- ------------------------------------------------------------------------
CREATE TABLE IF NOT EXISTS `skill_categories` (
    `id` INT AUTO_INCREMENT PRIMARY KEY,
    `name` VARCHAR(50) NOT NULL,
    `slug` VARCHAR(50) NOT NULL,
    `sort_order` INT NOT NULL DEFAULT 0,
    `created_at` TIMESTAMP NOT NULL DEFAULT CURRENT_TIMESTAMP,
    `updated_at` TIMESTAMP NOT NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
    UNIQUE KEY `uk_skill_cat_slug` (`slug`),
    INDEX `idx_skill_cat_order` (`sort_order`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci COMMENT='Taxonomy categories for technical skills';

-- ------------------------------------------------------------------------
-- 4. Table: skills
-- Technical skills, proficiency scores, FontAwesome icons & category mapping
-- ------------------------------------------------------------------------
CREATE TABLE IF NOT EXISTS `skills` (
    `id` INT AUTO_INCREMENT PRIMARY KEY,
    `category_id` INT NOT NULL,
    `name` VARCHAR(100) NOT NULL,
    `proficiency_level` VARCHAR(50) NOT NULL DEFAULT 'Proficient',
    `proficiency_percent` INT NOT NULL DEFAULT 85,
    `icon_class` VARCHAR(100) NOT NULL DEFAULT 'fas fa-code',
    `sort_order` INT NOT NULL DEFAULT 0,
    `is_featured` TINYINT(1) NOT NULL DEFAULT 1,
    `is_visible` TINYINT(1) NOT NULL DEFAULT 1,
    `created_at` TIMESTAMP NOT NULL DEFAULT CURRENT_TIMESTAMP,
    `updated_at` TIMESTAMP NOT NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
    INDEX `idx_skill_cat` (`category_id`),
    INDEX `idx_skill_visibility` (`is_visible`, `sort_order`),
    INDEX `idx_skill_featured` (`is_featured`),
    CONSTRAINT `fk_skills_category` FOREIGN KEY (`category_id`) 
        REFERENCES `skill_categories` (`id`) 
        ON DELETE CASCADE 
        ON UPDATE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci COMMENT='Technical proficiencies and skills matrix';

-- ------------------------------------------------------------------------
-- 5. Table: projects
-- Featured applications, architectural case studies, challenges & solutions
-- ------------------------------------------------------------------------
CREATE TABLE IF NOT EXISTS `projects` (
    `id` INT AUTO_INCREMENT PRIMARY KEY,
    `title` VARCHAR(150) NOT NULL,
    `slug` VARCHAR(150) NOT NULL,
    `category` VARCHAR(100) NOT NULL,
    `short_description` TEXT NOT NULL,
    `cover_image` VARCHAR(255) NULL DEFAULT 'assets/img/projects/agrishield.svg',
    `logo_url` VARCHAR(255) NULL DEFAULT '',
    `tech_stack` TEXT NOT NULL COMMENT 'JSON array of technologies utilized',
    `github_url` VARCHAR(255) NOT NULL DEFAULT '',
    `demo_url` VARCHAR(255) NOT NULL DEFAULT '',
    `status` VARCHAR(50) NOT NULL DEFAULT 'Active',
    `development_date` VARCHAR(50) NULL DEFAULT NULL,
    `key_features` TEXT NULL,
    `challenges` TEXT NULL,
    `solutions` TEXT NULL,
    `results` TEXT NULL,
    `case_study_content` TEXT NULL,
    `screenshots` TEXT NULL COMMENT 'JSON array of screenshot image paths',
    `sort_order` INT NOT NULL DEFAULT 0,
    `is_featured` TINYINT(1) NOT NULL DEFAULT 1,
    `is_visible` TINYINT(1) NOT NULL DEFAULT 1,
    `created_at` TIMESTAMP NOT NULL DEFAULT CURRENT_TIMESTAMP,
    `updated_at` TIMESTAMP NOT NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
    UNIQUE KEY `uk_project_slug` (`slug`),
    INDEX `idx_project_cat` (`category`),
    INDEX `idx_project_visible` (`is_visible`, `sort_order`),
    INDEX `idx_project_featured` (`is_featured`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci COMMENT='Portfolio projects, engineering case studies and demos';

-- ------------------------------------------------------------------------
-- 6. Table: experience_claims
-- Core technical experience claims (e.g., Full-Stack, AI Engineering)
-- ------------------------------------------------------------------------
CREATE TABLE IF NOT EXISTS `experience_claims` (
    `id` INT AUTO_INCREMENT PRIMARY KEY,
    `area` VARCHAR(100) NOT NULL,
    `duration` VARCHAR(50) NOT NULL,
    `description` TEXT NULL,
    `sort_order` INT NOT NULL DEFAULT 0,
    `is_visible` TINYINT(1) NOT NULL DEFAULT 1,
    `created_at` TIMESTAMP NOT NULL DEFAULT CURRENT_TIMESTAMP,
    `updated_at` TIMESTAMP NOT NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
    INDEX `idx_exp_claim_order` (`is_visible`, `sort_order`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci COMMENT='High-level technical domain development experience claims';

-- ------------------------------------------------------------------------
-- 7. Table: internships
-- Verified professional industry internships and roles
-- ------------------------------------------------------------------------
CREATE TABLE IF NOT EXISTS `internships` (
    `id` INT AUTO_INCREMENT PRIMARY KEY,
    `company` VARCHAR(150) NOT NULL,
    `location` VARCHAR(100) NOT NULL,
    `domain` VARCHAR(100) NOT NULL,
    `technologies` VARCHAR(255) NOT NULL,
    `role_description` TEXT NOT NULL,
    `start_date` VARCHAR(50) NULL DEFAULT NULL,
    `end_date` VARCHAR(50) NULL DEFAULT NULL,
    `sort_order` INT NOT NULL DEFAULT 0,
    `is_visible` TINYINT(1) NOT NULL DEFAULT 1,
    `created_at` TIMESTAMP NOT NULL DEFAULT CURRENT_TIMESTAMP,
    `updated_at` TIMESTAMP NOT NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
    INDEX `idx_internship_order` (`is_visible`, `sort_order`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci COMMENT='Chronological industry internship timeline';

-- ------------------------------------------------------------------------
-- 8. Table: education
-- Formal diplomas, degrees, institutions and academic scores
-- ------------------------------------------------------------------------
CREATE TABLE IF NOT EXISTS `education` (
    `id` INT AUTO_INCREMENT PRIMARY KEY,
    `degree` VARCHAR(150) NOT NULL,
    `institution` VARCHAR(200) NOT NULL,
    `score` VARCHAR(50) NOT NULL,
    `duration` VARCHAR(100) NOT NULL,
    `description` TEXT NULL,
    `sort_order` INT NOT NULL DEFAULT 0,
    `is_visible` TINYINT(1) NOT NULL DEFAULT 1,
    `created_at` TIMESTAMP NOT NULL DEFAULT CURRENT_TIMESTAMP,
    `updated_at` TIMESTAMP NOT NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
    INDEX `idx_edu_order` (`is_visible`, `sort_order`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci COMMENT='Academic qualifications and institutional history';

-- ------------------------------------------------------------------------
-- 9. Table: certifications
-- Verified certifications, honors, awards & credential verification links
-- ------------------------------------------------------------------------
CREATE TABLE IF NOT EXISTS `certifications` (
    `id` INT AUTO_INCREMENT PRIMARY KEY,
    `title` VARCHAR(200) NOT NULL,
    `certificate_type` VARCHAR(100) NOT NULL DEFAULT 'Certificate of Participation',
    `issuing_organization` VARCHAR(200) NOT NULL,
    `issue_date` VARCHAR(50) NOT NULL,
    `rank_achievement` VARCHAR(100) NULL DEFAULT NULL,
    `credential_id` VARCHAR(100) NULL DEFAULT NULL,
    `verification_url` VARCHAR(255) NULL DEFAULT NULL,
    `image_url` VARCHAR(255) NULL DEFAULT 'assets/img/ieee-certificate.svg',
    `description` TEXT NULL,
    `sort_order` INT NOT NULL DEFAULT 0,
    `is_visible` TINYINT(1) NOT NULL DEFAULT 1,
    `created_at` TIMESTAMP NOT NULL DEFAULT CURRENT_TIMESTAMP,
    `updated_at` TIMESTAMP NOT NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
    INDEX `idx_cert_order` (`is_visible`, `sort_order`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci COMMENT='Certifications, competitive programming honors and credentials';

-- ------------------------------------------------------------------------
-- 10. Table: social_links
-- External social handles and professional profile URLs
-- ------------------------------------------------------------------------
CREATE TABLE IF NOT EXISTS `social_links` (
    `id` INT AUTO_INCREMENT PRIMARY KEY,
    `platform` VARCHAR(50) NOT NULL,
    `display_label` VARCHAR(100) NOT NULL,
    `url` VARCHAR(255) NOT NULL,
    `icon_class` VARCHAR(100) NOT NULL DEFAULT 'fas fa-link',
    `is_visible` TINYINT(1) NOT NULL DEFAULT 1,
    `sort_order` INT NOT NULL DEFAULT 0,
    `created_at` TIMESTAMP NOT NULL DEFAULT CURRENT_TIMESTAMP,
    `updated_at` TIMESTAMP NOT NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
    INDEX `idx_social_visibility` (`is_visible`, `sort_order`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci COMMENT='External professional and social media links';

-- ------------------------------------------------------------------------
-- 11. Table: inquiries
-- Inbound visitor inquiries, recruiters, collaboration requests
-- ------------------------------------------------------------------------
CREATE TABLE IF NOT EXISTS `inquiries` (
    `id` INT AUTO_INCREMENT PRIMARY KEY,
    `name` VARCHAR(100) NOT NULL,
    `email` VARCHAR(150) NOT NULL,
    `subject` VARCHAR(200) NOT NULL,
    `message` TEXT NOT NULL,
    `ip_address` VARCHAR(45) NULL DEFAULT NULL,
    `is_read` TINYINT(1) NOT NULL DEFAULT 0,
    `created_at` TIMESTAMP NOT NULL DEFAULT CURRENT_TIMESTAMP,
    `updated_at` TIMESTAMP NOT NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
    INDEX `idx_inquiries_read` (`is_read`),
    INDEX `idx_inquiries_ip` (`ip_address`),
    INDEX `idx_inquiries_created` (`created_at`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci COMMENT='Inbound messages submitted via public contact form';

-- ------------------------------------------------------------------------
-- 12. Table: settings
-- Global key-value website settings, SEO meta tags and notification flags
-- ------------------------------------------------------------------------
CREATE TABLE IF NOT EXISTS `settings` (
    `id` INT AUTO_INCREMENT PRIMARY KEY,
    `setting_key` VARCHAR(100) NOT NULL,
    `setting_value` TEXT NULL,
    `created_at` TIMESTAMP NOT NULL DEFAULT CURRENT_TIMESTAMP,
    `updated_at` TIMESTAMP NOT NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
    UNIQUE KEY `uk_setting_key` (`setting_key`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci COMMENT='Key-value site settings and search engine metadata';

SET FOREIGN_KEY_CHECKS = 1;
