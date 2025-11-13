# Image Crawler Dashboard

A web-based system that periodically crawls URLs, counts images on each page, and visualizes historical data changes over time.

## Features

- Crawls multiple URLs and counts `<img>` tags on each page
- Stores historical image count data in MySQL database
- Web dashboard with interactive data table
- Real-time crawler console output in browser
- Filter URLs without data
- Comprehensive error handling and logging
- Works in both CLI and browser modes

## Requirements

- **PHP 8.1+** with PDO MySQL extension
- **MySQL 5.7+** or MariaDB 10.2+
- **Composer** for dependency management
- Web server (Apache/Nginx) for dashboard

## Installation

### 1. Clone the Repository

```bash
git clone <repository-url>
cd img_counter
```

### 2. Install Dependencies

```bash
composer install
```

This will install:
- `guzzlehttp/guzzle` - HTTP client
- `symfony/dom-crawler` - HTML parsing
- `symfony/css-selector` - CSS selectors
- `vlucas/phpdotenv` - Environment variables
- `monolog/monolog` - Logging

### 3. Database Setup

Create the database and tables:

```bash
mysql -u root -p < schema.sql
```

Or manually:

```sql
CREATE DATABASE IF NOT EXISTS image_crawler CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci;
USE image_crawler;
SOURCE schema.sql;
```

The schema includes:
- `urls` table with sample URLs
- `image_counts` table for historical data
- Composite index for optimal performance

### 4. Configuration

Copy the example environment file:

```bash
cp .env.example .env
```

Edit `.env` with your database credentials:

```env
DB_HOST=127.0.0.1
DB_PORT=3306
DB_NAME=image_crawler
DB_USER=your_username
DB_PASS=your_password
DB_CHARSET=utf8mb4
```

**Note:** On macOS, use `127.0.0.1` instead of `localhost` to avoid Unix socket issues.

## Usage

### Running the Crawler (CLI)

Execute the crawler from command line:

```bash
php crawl.php
```

**Output:**
```
=====================================
IMAGE CRAWLER
Started at: 2025-11-13 10:46:27
=====================================

Processing: https://github.com
✓ Success! Found 15 images (Status: 200)

Processing: https://invalid.com
✗ Error: DNS resolution failed

=====================================
Crawl completed
Duration: 12.5 seconds
Success: 10 | Errors: 1
=====================================
```

### Web Dashboard

1. **Start web server:**

   **Option A: PHP Built-in Server (for development)**
   ```bash
   php -S localhost:8000
   ```
   Then access: `http://localhost:8000/`

   **Option B: Apache/Nginx (for production)**
   - Copy project to web server document root (e.g., `/var/www/html/img_counter/`)
   - Ensure Apache/Nginx is running
   - Access: `http://localhost/img_counter/`

2. **Dashboard Features:**
   - View historical image counts in a table
   - Run crawler directly from browser
   - Hide URLs without data (saved to localStorage)
   - Real-time console output in iframe

### Logs

All crawler activity is logged to:
```
logs/crawler.log
```

**Log levels:**
- `INFO` - Successful operations, crawl start/end
- `WARNING` - HTTP redirects, no active URLs
- `ERROR` - HTTP errors, timeouts, DNS failures, database errors

**Example log:**
```
[2025-11-13 10:46:27] crawler.INFO: Crawl process started {"started_at":"2025-11-13 10:46:27"}
[2025-11-13 10:46:28] crawler.INFO: Successfully fetched URL {"url":"https://github.com","status_code":200}
[2025-11-13 10:46:35] crawler.ERROR: Failed to fetch URL {"url":"https://invalid.com","error":"DNS error"}
```

## Cron Job Setup (Optional)

To run the crawler automatically every 2 hours:

```bash
crontab -e
```

Add this line:
```bash
0 */2 * * * /usr/bin/php /path/to/img_counter/crawl.php >> /path/to/img_counter/logs/cron.log 2>&1
```

## Project Structure

```
/
├── assets/                 # Frontend assets
│   ├── style.css          # Dashboard styles
│   ├── app.js             # Dashboard JavaScript
│   ├── favicon.png        # Browser icon
│   └── logo.png           # Footer logo
├── logs/                   # Log files (auto-created)
│   └── crawler.log        # Monolog output
├── src/                    # PHP classes
│   ├── Crawler.php        # HTTP client & image counting
│   ├── Database.php       # Database operations
│   └── Logger.php         # Monolog wrapper
├── templates/              # HTML templates
│   └── dashboard.php      # Dashboard view
├── vendor/                 # Composer dependencies
├── .env                    # Environment config (create from .env.example)
├── .env.example           # Environment template
├── .gitignore             # Git exclusions
├── composer.json          # PHP dependencies
├── config.php             # Database configuration
├── crawl.php              # Crawler entry point
├── index.php              # Dashboard entry point
├── schema.sql             # Database schema
├── ARCHITECTURE.md        # Architecture decisions
└── README.md              # This file
```

## Documentation

- **[ARCHITECTURE.md](ARCHITECTURE.md)** - Detailed architectural decisions and design rationale

## Troubleshooting

### MySQL Connection Issues (macOS)

If you see "No such file or directory" error:
- Change `DB_HOST=localhost` to `DB_HOST=127.0.0.1` in `.env`
- This forces TCP/IP connection instead of Unix socket

### PHP Timeout Errors

The crawler has a 10-second timeout per URL to prevent PHP execution timeouts. If you have many URLs (50+), consider:
- Increasing PHP's `max_execution_time` in php.ini
- Running the crawler via CLI instead of browser
- Reducing the number of active URLs

### Empty Dashboard

If the dashboard shows no data:
1. Run the crawler first: `php crawl.php`
2. Check that URLs are marked as active in database
3. Check `logs/crawler.log` for errors

## License

This project is for educational and portfolio purposes.
