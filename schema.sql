-- Image Crawler Database Schema
-- Create database and tables for tracking image counts across URLs

-- Create database (optional - uncomment if needed)
-- CREATE DATABASE IF NOT EXISTS image_crawler CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci;
-- USE image_crawler;

-- Table: urls
-- Stores the list of URLs to crawl
CREATE TABLE IF NOT EXISTS urls (
    id INT AUTO_INCREMENT PRIMARY KEY,
    url VARCHAR(255) NOT NULL UNIQUE,
    is_active BOOLEAN DEFAULT TRUE,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    INDEX idx_is_active (is_active)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- Table: image_counts
-- Stores historical data of image counts for each URL
CREATE TABLE IF NOT EXISTS image_counts (
    id INT AUTO_INCREMENT PRIMARY KEY,
    url_id INT NOT NULL,
    count INT NOT NULL DEFAULT 0,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    FOREIGN KEY (url_id) REFERENCES urls(id) ON DELETE CASCADE,
    INDEX idx_url_id (url_id),
    INDEX idx_created_at (created_at)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- Insert sample URLs for testing (optional - uncomment if needed)
-- INSERT INTO urls (url) VALUES
-- ('https://example.com'),
-- ('https://google.com'),
-- ('https://github.com');
