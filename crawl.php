<?php

/**
 * Image Crawler Entry Point
 * Fetches active URLs from database and counts images */

require_once __DIR__ . '/config.php';
require_once __DIR__ . '/src/Logger.php';
require_once __DIR__ . '/src/Database.php';
require_once __DIR__ . '/src/Crawler.php';

// Check if this is an AJAX request or browser (not CLI)
$isAjax = !empty($_SERVER['HTTP_X_REQUESTED_WITH']) && strtolower($_SERVER['HTTP_X_REQUESTED_WITH']) === 'xmlhttprequest';
$isBrowser = php_sapi_name() !== 'cli';

// Initialize output buffer for logs
$logs = [];

// Output HTML header for browser/iframe
if ($isBrowser && !$isAjax) {
    echo '<!DOCTYPE html>
<html>
<head>
    <meta charset="UTF-8">
    <style>
        body {
            background-color: #1a202c;
            color: #e2e8f0;
            font-family: "Monaco", "Courier New", monospace;
            font-size: 13px;
            padding: 20px;
            line-height: 1.8;
            margin: 0;
        }
        .log-line {
            margin: 2px 0;
            white-space: pre-wrap;
            word-wrap: break-word;
        }
        .info { color: #e2e8f0; }
        .success { color: #48bb78; }
        .error { color: #f56565; }
        .warning { color: #ed8936; }
    </style>
</head>
<body>';
}

function addLog($message, $type = 'info'): void
{
    global $logs, $isAjax, $isBrowser;
    $logs[] = ['message' => $message, 'type' => $type];

    if (!$isAjax) {
        if ($isBrowser) {
            // Output HTML with color classes
            echo '<div class="log-line ' . htmlspecialchars($type) . '">' . htmlspecialchars($message) . '</div>';
            flush();
            ob_flush();
        } else {
            // CLI output
            echo $message . "\n";
        }
    }
}

// Initialize components
try {
    $startTime = microtime(true);
    $startDate = date('Y-m-d H:i:s');

    $logger = Logger::getInstance();
    $pdo = getDbConnection();
    $database = new Database($pdo);
    $crawler = new Crawler($logger);

    $logger->info('Crawl process started', ['started_at' => $startDate]);

    addLog("Starting crawl process...", 'info');
    addLog("Started at: {$startDate}", 'info');
    addLog(str_repeat("-", 50), 'info');

    // Get all active URLs
    $urls = $database->getActiveUrls();

    if (empty($urls)) {
        $logger->warning('No active URLs found in database');
        addLog("No active URLs found in database.", 'warning');

        if ($isAjax) {
            header('Content-Type: application/json');
            echo json_encode(['success' => false, 'logs' => $logs]);
        }
        exit(0);
    }

    $totalUrls = count($urls);
    $logger->info('Found active URLs to crawl', ['total_urls' => $totalUrls]);
    addLog("Found {$totalUrls} active URL(s) to crawl.\n", 'info');

    // Process each URL
    $successCount = 0;
    $errorCount = 0;
    $errorDetails = [];

    foreach ($urls as $urlData) {
        $urlId = $urlData['id'];
        $url = $urlData['url'];

        addLog("Processing: {$url}", 'info');

        try {
            // Count images on the page
            $imageCount = $crawler->countImages($url);

            // Save to database
            $database->saveImageCount($urlId, $imageCount);

            $logger->info('Image count saved', [
                'url' => $url,
                'image_count' => $imageCount
            ]);

            addLog("  ✓ Found {$imageCount} image(s)", 'success');
            addLog("  ✓ Saved to database", 'success');
            $successCount++;
        } catch (Exception $e) {
            $logger->error('Failed to process URL', [
                'url' => $url,
                'error' => $e->getMessage()
            ]);

            addLog("  ✗ Error: " . $e->getMessage(), 'error');
            $errorCount++;
            $errorDetails[] = ['url' => $url, 'error' => $e->getMessage()];
        }

        addLog("", 'info');
    }

    $endTime = microtime(true);
    $endDate = date('Y-m-d H:i:s');
    $duration = round($endTime - $startTime, 2);

    addLog(str_repeat("-", 50), 'info');
    addLog("Crawl process completed.", 'success');
    addLog("Finished at: {$endDate}", 'info');
    addLog("Duration: {$duration} seconds", 'info');
    addLog("Total URLs: {$totalUrls}", 'info');
    addLog("Success: {$successCount}, Errors: {$errorCount}", $errorCount > 0 ? 'warning' : 'success');

    $logger->info('Crawl process completed', [
        'started_at' => $startDate,
        'finished_at' => $endDate,
        'duration_seconds' => $duration,
        'total_urls' => $totalUrls,
        'success_count' => $successCount,
        'error_count' => $errorCount
    ]);

    // Return JSON for AJAX requests
    if ($isAjax) {
        header('Content-Type: application/json');
        echo json_encode([
            'success' => true,
            'logs' => $logs,
            'stats' => [
                'started_at' => $startDate,
                'finished_at' => $endDate,
                'duration' => $duration,
                'total_urls' => $totalUrls,
                'success_count' => $successCount,
                'error_count' => $errorCount,
                'errors' => $errorDetails
            ]
        ]);
    }

    // Close HTML for browser
    if ($isBrowser && !$isAjax) {
        echo '</body></html>';
    }

} catch (PDOException $e) {
    $logger->error('Database connection failed', ['error' => $e->getMessage()]);
    addLog("Database error: " . $e->getMessage(), 'error');

    if ($isAjax) {
        header('Content-Type: application/json');
        echo json_encode(['success' => false, 'logs' => $logs, 'error' => $e->getMessage()]);
    } elseif ($isBrowser) {
        echo '</body></html>';
    }
    exit(1);
} catch (Exception $e) {
    $logger->error('Fatal error', ['error' => $e->getMessage()]);
    addLog("Error: " . $e->getMessage(), 'error');

    if ($isAjax) {
        header('Content-Type: application/json');
        echo json_encode(['success' => false, 'logs' => $logs, 'error' => $e->getMessage()]);
    } elseif ($isBrowser) {
        echo '</body></html>';
    }
    exit(1);
}
