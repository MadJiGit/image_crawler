<?php
/**
 * Dashboard - Image Count History
 * Displays historical image counts for all URLs in a table format
 */

require_once __DIR__ . '/config.php';
require_once __DIR__ . '/src/Database.php';

try {
    $pdo = getDbConnection();
    $database = new Database($pdo);

    // Get all URLs
    $urls = $pdo->query("SELECT id, url FROM urls ORDER BY id ASC")->fetchAll();

    // Get all unique dates from image_counts
    $dates = $pdo->query("
        SELECT DISTINCT DATE_FORMAT(created_at, '%Y-%m-%d %H:%i') as date_time
        FROM `image_counts`
        ORDER BY date_time ASC
    ")->fetchAll(PDO::FETCH_COLUMN);

    // Build data structure: [url_id][date] = count
    $data = [];
    foreach ($urls as $url) {
        $data[$url['id']] = [
            'url' => $url['url'],
            'counts' => []
        ];
    }

    // Fetch all image counts
    $counts = $pdo->query("
        SELECT url_id, DATE_FORMAT(created_at, '%Y-%m-%d %H:%i') as date_time, count
        FROM image_counts
        ORDER BY date_time ASC
    ")->fetchAll();

    foreach ($counts as $row) {
        $data[$row['url_id']]['counts'][$row['date_time']] = $row['count'];
    }

} catch (PDOException $e) {
    die("Database error: " . $e->getMessage());
}

// Load template
require_once __DIR__ . '/templates/dashboard.php';
