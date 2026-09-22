-- ============================================================
-- NAMANGA SECONDARY SCHOOL
-- ICT & COMPUTER SCIENCE DIGITAL RESOURCE CENTRE
-- Database structure
-- ============================================================
-- Import this file using phpMyAdmin or the mysql CLI.
-- It creates the database, the two tables, and the required
-- indexes. It does NOT insert a plain-text admin password --
-- see create-admin.php in the /admin/ folder (or the README)
-- for how to create the first admin account safely.
-- ============================================================

CREATE DATABASE IF NOT EXISTS namanga_resource_centre
    CHARACTER SET utf8mb4
    COLLATE utf8mb4_unicode_ci;

USE namanga_resource_centre;

-- ------------------------------------------------------------
-- admins
-- ------------------------------------------------------------
CREATE TABLE IF NOT EXISTS admins (
    id INT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
    username VARCHAR(50) NOT NULL UNIQUE,
    password VARCHAR(255) NOT NULL,
    role ENUM('admin','staff') NOT NULL DEFAULT 'staff',
    is_super_admin TINYINT(1) NOT NULL DEFAULT 0,
    created_at TIMESTAMP NOT NULL DEFAULT CURRENT_TIMESTAMP
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;

-- ------------------------------------------------------------
-- resources
-- ------------------------------------------------------------
CREATE TABLE IF NOT EXISTS resources (
    id INT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
    title VARCHAR(255) NOT NULL,
    description TEXT NULL,
    resource_type ENUM('notes','summary','theory','practical','software','others') NOT NULL,
    form_level ENUM('form1','form2','form3','form4') NULL,
    year SMALLINT UNSIGNED NULL,
    software_version VARCHAR(50) NULL,
    file_name VARCHAR(255) NOT NULL,
    file_path VARCHAR(500) NOT NULL,
    file_size BIGINT UNSIGNED NOT NULL,
    original_name VARCHAR(255) NOT NULL,
    created_at TIMESTAMP NOT NULL DEFAULT CURRENT_TIMESTAMP,
    updated_at TIMESTAMP NOT NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,

    INDEX idx_resource_type (resource_type),
    INDEX idx_form_level (form_level),
    INDEX idx_year (year),
    INDEX idx_type_form (resource_type, form_level)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;

-- ------------------------------------------------------------
-- hero_slides (homepage slideshow images, managed from the admin panel)
-- ------------------------------------------------------------
CREATE TABLE IF NOT EXISTS hero_slides (
    id INT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
    image_name VARCHAR(255) NOT NULL,
    image_path VARCHAR(500) NOT NULL,
    caption VARCHAR(255) NULL,
    sort_order INT UNSIGNED NOT NULL DEFAULT 0,
    created_at TIMESTAMP NOT NULL DEFAULT CURRENT_TIMESTAMP,

    INDEX idx_sort_order (sort_order)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;

-- ------------------------------------------------------------
-- gallery_items (public photo/video gallery, managed from the admin panel)
-- ------------------------------------------------------------
CREATE TABLE IF NOT EXISTS gallery_items (
    id INT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
    title VARCHAR(255) NOT NULL,
    media_type ENUM('image','video') NOT NULL,
    file_name VARCHAR(255) NOT NULL,
    file_path VARCHAR(500) NOT NULL,
    file_size BIGINT UNSIGNED NOT NULL,
    original_name VARCHAR(255) NOT NULL,
    created_at TIMESTAMP NOT NULL DEFAULT CURRENT_TIMESTAMP,

    INDEX idx_media_type (media_type)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;

-- ============================================================
-- NOTE ON THE FIRST ADMIN ACCOUNT
-- ============================================================
-- Do not insert a plain-text password here. After importing
-- this file, open:
--     http://localhost/namanga-resource-centre/admin/create-admin.php
-- and follow the on-screen form to create the first admin
-- account. That page hashes the password with PHP's
-- password_hash() before storing it, and it disables itself
-- automatically once an admin account already exists.
-- See the README for full details.
-- ============================================================
