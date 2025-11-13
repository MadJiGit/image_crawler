# Architecture

## Database Design

### Schema

Two-table relational structure:

```sql
urls (
    id,
    url,
    is_active,
    created_at
)

image_counts (
    id,
    url_id,
    count,
    created_at
)
```

### Indexing

Composite index: `INDEX idx_url_date_count (url_id, created_at, count)`

**Column order rationale:**
1. `url_id` (first) - Most selective filter, narrows to ~4,380 rows per URL
2. `created_at` (second) - Enables range queries and ORDER BY
3. `count` (third) - Creates covering index, eliminates table lookups

**Covering Index:** Contains all columns needed by queries, so MySQL doesn't access table data.

### Typical Query

```sql
SELECT created_at, count
FROM `image_counts`
WHERE url_id = 5
  AND created_at >= '2024-01-01'
ORDER BY created_at;
```

**Performance:** <5ms with 1M rows, no table scan needed

---

## Technology Stack

### Backend
- **PHP 8.1+** - OOP structure
- **MySQL** - Relational database with InnoDB
- **Composer** - Dependency management

### Libraries
- `guzzlehttp/guzzle` - HTTP client, 10-second timeout, automatic redirect handling
- `symfony/dom-crawler` - HTML parsing, CSS selectors
- `vlucas/phpdotenv` - Environment variables
- `monolog/monolog` - Structured logging

### HTTP Redirect Handling

Guzzle automatically follows HTTP redirects (301/302):

```php
$this->httpClient = new Client([
    'timeout' => 10,
    'allow_redirects' => [
        'max' => 10,
        'track_redirects' => true
    ]
]);
```

- Follows up to 10 redirects automatically
- Tracks redirect chain for logging
- Final URL is used for image counting
- Redirects logged as WARNING level

---

## Frontend Implementation

### Crawler Output

Uses iframe instead of AJAX:

```php
// Detect browser vs CLI
$isBrowser = php_sapi_name() !== 'cli';

// Output colored HTML for browser
if ($isBrowser) {
    echo '<div class="log-line success">✓ Success</div>';
}
```

### UI Features

**Data filtering:**
- Client-side JavaScript filtering
- localStorage state persistence

```javascript
localStorage.setItem('hideEmptyRows', checkbox.checked);
```

**Table:**
- Sticky URL column
- Responsive design

**Output colors:**
```css
.info { color: #e2e8f0; }
.success { color: #48bb78; }
.error { color: #f56565; }
.warning { color: #ed8936; }
```

---

## Logging

### Configuration

```php
$logger = new Logger('crawler');
$logger->pushHandler(new StreamHandler('logs/crawler.log', Level::Debug));
```

### Log Levels

**INFO:**
- Crawl process start/end with timestamps
- Successful URL fetches with HTTP status code (200)
- Image counts saved to database

**WARNING:**
- HTTP redirects (301/302) with redirect chain
- No active URLs found in database

**ERROR:**
- HTTP errors: 404 (Not Found), 500 (Server Error), 403 (Forbidden)
- Network timeouts (10-second Guzzle timeout)
- DNS resolution failures
- Database connection/query errors

### Format

```
[2025-11-13 10:46:27] crawler.INFO: Crawl process started {"started_at":"2025-11-13 10:46:27"}
[2025-11-13 10:46:28] crawler.INFO: Successfully fetched URL {"url":"https://github.com","status_code":200}
[2025-11-13 10:46:30] crawler.WARNING: HTTP redirect detected {"url":"http://example.com","status_code":301}
[2025-11-13 10:46:35] crawler.ERROR: Failed to fetch URL {"url":"https://invalid.com","error":"DNS error"}
[2025-11-13 10:46:40] crawler.ERROR: HTTP error {"url":"https://httpstat.us/404","status_code":404}
```

**Structured logging:** All logs include context data (URL, status code, error details) in JSON format for easy parsing.

---

## File Structure

```
/
├── assets/
│   ├── style.css
│   ├── app.js
│   ├── favicon.png
│   └── logo.png
├── logs/
│   └── crawler.log
├── src/
│   ├── Crawler.php
│   ├── Database.php
│   └── Logger.php
├── templates/
│   └── dashboard.php
├── vendor/
├── .env
├── .env.example
├── .gitignore
├── composer.json
├── config.php
├── crawl.php
├── index.php
├── schema.sql
├── ARCHITECTURE.md
└── README.md
```

---

## Security

### Environment Variables

`.env` file with `vlucas/phpdotenv`:
- Credentials in `.env` (gitignored)
- `.env.example` template (committed)
- No hardcoded credentials

### Database

PDO configuration:
```php
PDO::ATTR_EMULATE_PREPARES => false  // SQL injection protection
PDO::ATTR_ERRMODE => PDO::ERRMODE_EXCEPTION  // Exception handling
```

---

## Future Considerations

- Chart.js/Google Charts integration
- Server-Sent Events (SSE) for real-time progress
- URL grouping with `group_id` table column
- Data retention policies and cleanup
- Additional metrics (response time, status codes, page size)
- Cron automation with email notifications
- Redis caching layer
