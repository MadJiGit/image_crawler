<?php
/**
 * Database Configuration
 * Loads environment variables from .env file
 */

require_once __DIR__ . '/vendor/autoload.php';

use Dotenv\Dotenv;

// Load environment variables from .env file
$dotenv = Dotenv::createImmutable(__DIR__);
$dotenv->load();

// Database credentials from environment
$dbHost = $_ENV['DB_HOST'] ?? 'localhost';
$dbName = $_ENV['DB_NAME'] ?? 'image_crawler';
$dbUser = $_ENV['DB_USER'] ?? 'root';
$dbPass = $_ENV['DB_PASS'] ?? '';
$dbCharset = $_ENV['DB_CHARSET'] ?? 'utf8mb4';

/**
 * Get PDO database connection
 *
 * @return PDO
 * @throws PDOException
 */
function getDbConnection() {
    global $dbHost, $dbName, $dbUser, $dbPass, $dbCharset;

    try {
        $dsn = "mysql:host=" . $dbHost . ";dbname=" . $dbName . ";charset=" . $dbCharset;
        $options = [
            PDO::ATTR_ERRMODE => PDO::ERRMODE_EXCEPTION,
            PDO::ATTR_DEFAULT_FETCH_MODE => PDO::FETCH_ASSOC,
            PDO::ATTR_EMULATE_PREPARES => false,
        ];

        $pdo = new PDO($dsn, $dbUser, $dbPass, $options);
        return $pdo;
    } catch (PDOException $e) {
        throw new PDOException("Database connection failed: " . $e->getMessage());
    }
}
