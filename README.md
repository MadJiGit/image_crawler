# Image Crawler Project

## 1. Systematic Requirements

### Goal
Create a system that periodically crawls a list of URLs, counts the images on each page, and visualizes historical data about changes over time.

### Requirements
* **URL addresses:** List of websites to crawl stored in database.
* **Periodicity:** Cron Job, executed every 2 hours.
* **Action:** Download HTML content, count `<img>` tags.
* **Data storage:** Save to DB (URL, image count, timestamp).
* **Visualization:** Chart showing historical data of changes.
* **Error handling:** Manage HTTP status codes (e.g., 301 redirects) and crawling delays.

---

## 2. Technical Implementation Proposal (Mid-Level Approach)

### A. Database Structure (MySQL)

#### Table `urls`
Stores the list of websites to crawl.

* `id` (INT, Primary Key, Auto Increment)
* `url` (VARCHAR 255)
* `is_active` (BOOLEAN)

#### Table `image_counts`
Stores the history of image counts.

* `id` (INT, Primary Key, Auto Increment)
* `url_id` (INT, Foreign Key to urls.id)
* `count` (INT)
* `created_at` (TIMESTAMP, defaults to current date)

### B. Tools and Libraries (Composer)

Using ready-made, industry-standard libraries:

```bash
composer require guzzlehttp/guzzle symfony/dom-crawler symfony/css-selector
```

* **Guzzle:** For reliable HTTP requests and automatic redirect handling.
* **Symfony DomCrawler/CssSelector:** For easy and secure HTML parsing.
* **PDO:** For database communication.

### C. PHP Crawling Script (crawl.php)

**Logic:**
* Uses Guzzle for HTTP requests
* Checks HTTP status codes
* Uses DomCrawler to count `<img>` elements
* Saves data to DB via PDO

### D. Cron Job Setup

Server configuration for execution every 2 hours:

```bash
# Execute crawl.php script every 2 hours
0 */2 * * * /usr/bin/php /path/to/your/crawl.php
```

### E. Visualization (Charts)

For charts, a PHP script is needed that:
* Extracts historical data from the `image_counts` table
* Passes the data to a JavaScript charting library on the frontend
* Recommended libraries: Chart.js or Google Charts

### F. Error Handling

* Use `try...catch` blocks around Guzzle requests and PDO operations
* Log errors (HTTP 404/500, DB errors) to a log file using Monolog library
* Handle 301/302 redirects via Guzzle (automatic)
* Implement crawling delays for rate limiting
