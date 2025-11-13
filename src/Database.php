<?php

/**
 * Database Class
 * Handles all database operations for URLs and image counts
 */
class Database
{
    private PDO $pdo;

    public function __construct(PDO $pdo)
    {
        $this->pdo = $pdo;
    }

    /**
     * Get all active URLs from database
     *
     * @return array Array of URL records
     */
    public function getActiveUrls(): array
    {
        $stmt = $this->pdo->prepare("
            SELECT id, url
            FROM urls
            WHERE is_active = TRUE
        ");
        $stmt->execute();
        return $stmt->fetchAll();
    }

    /**
     * Save image count for a URL
     *
     * @param int $urlId URL ID from urls table
     * @param int $count Number of images found
     * @return bool Success status
     */
    public function saveImageCount(int $urlId, int $count): bool
    {
        $stmt = $this->pdo->prepare("
            INSERT INTO image_counts (url_id, count)
            VALUES (:url_id, :count)
        ");
        return $stmt->execute([
            'url_id' => $urlId,
            'count' => $count
        ]);
    }

    /**
     * Get image count history for a specific URL
     *
     * @param int $urlId URL ID
     * @param string|null $fromDate Optional start date (Y-m-d format)
     * @return array Array of historical counts
     */
    public function getImageCountHistory(int $urlId, ?string $fromDate = null): array
    {
        $sql = "
            SELECT created_at, count
            FROM image_counts
            WHERE url_id = :url_id
        ";

        if ($fromDate) {
            $sql .= " AND created_at >= :from_date";
        }

        $sql .= " ORDER BY created_at ASC";

        $stmt = $this->pdo->prepare($sql);
        $params = ['url_id' => $urlId];

        if ($fromDate) {
            $params['from_date'] = $fromDate;
        }

        $stmt->execute($params);
        return $stmt->fetchAll();
    }
}
