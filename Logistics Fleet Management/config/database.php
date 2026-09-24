<?php
/**
 * Database Configuration & PDO Connection
 * Fleet Management System
 */

// Database credentials
define('DB_HOST', getenv('DB_HOST') ?: '127.0.0.1');
define('DB_PORT', getenv('DB_PORT') ?: '3306');
define('DB_NAME', getenv('DB_NAME') ?: 'fleet_management');
define('DB_USER', getenv('DB_USER') ?: 'root');
define('DB_PASS', getenv('DB_PASS') !== false ? getenv('DB_PASS') : 'oussama2006');
define('DB_CHARSET', 'utf8mb4');

/**
 * Returns a singleton PDO connection instance.
 *
 * @return PDO
 */
function getDBConnection(): PDO
{
    static $pdo = null;

    if ($pdo === null) {
        $dsn = "mysql:host=" . DB_HOST . ";port=" . DB_PORT . ";dbname=" . DB_NAME . ";charset=" . DB_CHARSET;
        $options = [
            PDO::ATTR_ERRMODE            => PDO::ERRMODE_EXCEPTION,
            PDO::ATTR_DEFAULT_FETCH_MODE => PDO::FETCH_ASSOC,
            PDO::ATTR_EMULATE_PREPARES   => false,
        ];

        try {
            $pdo = new PDO($dsn, DB_USER, DB_PASS, $options);
        } catch (PDOException $e) {
            die(
                "<div style='font-family: sans-serif; max-width: 600px; margin: 40px auto; padding: 20px; border: 1px solid #f5c6cb; background: #f8d7da; color: #721c24; border-radius: 8px;'>"
                . "<h3 style='margin-top:0;'>Database Connection Error</h3>"
                . "<p>Unable to connect to the database. Please check your MySQL service and credentials in <code>config/database.php</code>.</p>"
                . "<p><small>Error details: " . htmlspecialchars($e->getMessage()) . "</small></p>"
                . "</div>"
            );
        }
    }

    return $pdo;
}
