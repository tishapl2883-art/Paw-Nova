<?php
// PawConnect Database Setup Script (CLI & Web)
$host = '127.0.0.1';
$user = 'root';
$pass = '';

echo "=== PawConnect Database Setup ===\n";

try {
    // 1. Connect without database to create it
    $pdo = new PDO("mysql:host=$host;charset=utf8mb4", $user, $pass, [
        PDO::ATTR_ERRMODE => PDO::ERRMODE_EXCEPTION
    ]);
    echo "[✓] Connected to MySQL successfully.\n";

    // 2. Read SQL file
    $sqlFile = __DIR__ . '/config/setup.sql';
    if (!file_exists($sqlFile)) {
        die("[✗] Error: config/setup.sql file not found!\n");
    }

    $sql = file_get_contents($sqlFile);

    // 3. Execute SQL batch
    $pdo->exec($sql);
    echo "[✓] Database `pawconnect_db` created and tables populated successfully!\n";

    // Reconnect to verify
    $pdo->query("USE `pawconnect_db`");
    $usersCount = $pdo->query("SELECT COUNT(*) FROM users")->fetchColumn();
    $petsCount = $pdo->query("SELECT COUNT(*) FROM pets")->fetchColumn();
    $appsCount = $pdo->query("SELECT COUNT(*) FROM adoption_applications")->fetchColumn();

    echo "[✓] Seed data verified:\n";
    echo "    - Users: $usersCount\n";
    echo "    - Pets: $petsCount\n";
    echo "    - Applications: $appsCount\n";
    echo "\n=== Setup Completed Successfully! ===\n";
    echo "Admin Login: admin@pawconnect.org / admin123\n";
    echo "User Login:  john@example.com / user123\n";

} catch (PDOException $e) {
    echo "[✗] Database setup failed: " . $e->getMessage() . "\n";
    exit(1);
}
