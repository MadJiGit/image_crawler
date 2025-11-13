-- Image Crawler Database Schema

CREATE DATABASE IF NOT EXISTS image_crawler
    CHARACTER SET utf8mb4
    COLLATE utf8mb4_unicode_ci;

USE image_crawler;

-- Table: urls
-- Stores the list of URLs to crawl
CREATE TABLE IF NOT EXISTS urls
(
    id         INT AUTO_INCREMENT PRIMARY KEY,
    url        VARCHAR(255) NOT NULL UNIQUE,
    is_active  BOOLEAN   DEFAULT TRUE,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    INDEX idx_is_active (is_active)
) ENGINE = InnoDB
  DEFAULT CHARSET = utf8mb4
  COLLATE = utf8mb4_unicode_ci;

-- Table: image_counts
-- Stores historical data of image counts for each URL
CREATE TABLE IF NOT EXISTS image_counts
(
    id         INT AUTO_INCREMENT PRIMARY KEY,
    url_id     INT NOT NULL,
    count      INT NOT NULL DEFAULT 0,
    created_at TIMESTAMP    DEFAULT CURRENT_TIMESTAMP,
    FOREIGN KEY (url_id) REFERENCES urls (id) ON DELETE CASCADE,
    INDEX idx_url_date_count (url_id, created_at, count)
) ENGINE = InnoDB
  DEFAULT CHARSET = utf8mb4
  COLLATE = utf8mb4_unicode_ci;

-- Insert test URLs (valid)
INSERT INTO urls (url) VALUES
('https://mladenraykov.com'),
('https://github.com'),
('https://unsplash.com'),
('https://blocklang.org'),
('https://app.blocklang.org'),
('https://body-language.org');

-- Insert test URLs (invalid - for error testing)
INSERT INTO urls (url) VALUES
('https://thissitedoesnotexist12345.com'),  -- DNS error
('https://httpstat.us/404'),                 -- HTTP 404 error
('https://httpstat.us/500'),                 -- HTTP 500 server error
('https://httpstat.us/403'),                 -- HTTP 403 forbidden
('http://10.255.255.1');                     -- Timeout error

-- Insert URLs with frequently changing images
INSERT INTO urls (url) VALUES
('https://news.ycombinator.com'),            -- Hacker News
('https://www.reddit.com'),                  -- Reddit
('https://news.bg'),                         -- Bulgarian news
('https://www.dnes.bg'),                     -- Bulgarian portal
('https://www.amazon.com'),                  -- E-commerce
('https://www.ebay.com'),                    -- E-commerce
('https://www.pexels.com'),                  -- Photo gallery
('https://stocksnap.io'),                    -- Photo gallery
('https://dev.to'),                          -- Dev community
('https://stackoverflow.com');               -- Stack Overflow