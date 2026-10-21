<?php
/**
 * PawConnect - Database & Configuration File
 */

// Start session if not started
if (session_status() === PHP_SESSION_NONE) {
    session_start();
}

// Database configuration
define('DB_HOST', '127.0.0.1');
define('DB_NAME', 'pawconnect_db');
define('DB_USER', 'root');
define('DB_PASS', '');
define('DB_CHARSET', 'utf8mb4');

// Application configuration
define('APP_NAME', 'PawConnect');
define('APP_TAGLINE', 'Connecting Loving Homes With Pets In Need');
define('BASE_URL', '/');
define('UPLOAD_DIR', __DIR__ . '/../uploads/pets/');

// PDO Singleton / Database Connection
function getDB() {
    static $pdo = null;
    if ($pdo === null) {
        $dsn = "mysql:host=" . DB_HOST . ";dbname=" . DB_NAME . ";charset=" . DB_CHARSET;
        $options = [
            PDO::ATTR_ERRMODE            => PDO::ERRMODE_EXCEPTION,
            PDO::ATTR_DEFAULT_FETCH_MODE => PDO::FETCH_ASSOC,
            PDO::ATTR_EMULATE_PREPARES   => false,
        ];
        try {
            $pdo = new PDO($dsn, DB_USER, DB_PASS, $options);
        } catch (PDOException $e) {
            // Friendly error if database is not initialized yet
            die("<div style='font-family:sans-serif;padding:30px;background:#fff1f2;border:1px solid #fecdd3;margin:40px auto;max-width:600px;border-radius:10px;color:#9f1239;'>"
                . "<h2 style='margin-top:0;'>Database Connection Error</h2>"
                . "<p>Could not connect to MySQL server or database <code>" . DB_NAME . "</code> does not exist.</p>"
                . "<p>Error details: " . htmlspecialchars($e->getMessage()) . "</p>"
                . "<p>To initialize the database automatically, click below:</p>"
                . "<a href='/install.php' style='display:inline-block;padding:10px 20px;background:#e11d48;color:#fff;text-decoration:none;border-radius:6px;font-weight:bold;'>Run PawConnect Database Installer</a>"
                . "</div>");
        }
    }
    return $pdo;
}
