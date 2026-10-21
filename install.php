<?php
/**
 * PawConnect - One-Click Database Installer & Reset Utility
 */
define('DB_HOST', '127.0.0.1');
define('DB_USER', 'root');
define('DB_PASS', '');

$message = '';
$messageType = '';
$dbInstalled = false;
$stats = [];

// Handle Install / Reset Action
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['action']) && $_POST['action'] === 'install') {
    try {
        $pdo = new PDO("mysql:host=" . DB_HOST . ";charset=utf8mb4", DB_USER, DB_PASS, [
            PDO::ATTR_ERRMODE => PDO::ERRMODE_EXCEPTION
        ]);

        $sqlFile = __DIR__ . '/config/setup.sql';
        if (!file_exists($sqlFile)) {
            throw new Exception("Setup SQL file not found at: {$sqlFile}");
        }

        $sql = file_get_contents($sqlFile);
        $pdo->exec($sql);

        $pdo->query("USE `pawconnect_db`");
        $stats['users'] = $pdo->query("SELECT COUNT(*) FROM users")->fetchColumn();
        $stats['pets'] = $pdo->query("SELECT COUNT(*) FROM pets")->fetchColumn();
        $stats['categories'] = $pdo->query("SELECT COUNT(*) FROM categories")->fetchColumn();
        $stats['applications'] = $pdo->query("SELECT COUNT(*) FROM adoption_applications")->fetchColumn();

        $message = "Database `pawconnect_db` was successfully created and seeded with demo data!";
        $messageType = "success";
        $dbInstalled = true;

    } catch (Exception $e) {
        $message = "Installation Error: " . $e->getMessage();
        $messageType = "danger";
    }
} else {
    // Check if database already exists
    try {
        $testPdo = new PDO("mysql:host=" . DB_HOST . ";dbname=pawconnect_db;charset=utf8mb4", DB_USER, DB_PASS, [
            PDO::ATTR_ERRMODE => PDO::ERRMODE_EXCEPTION
        ]);
        $stats['users'] = $testPdo->query("SELECT COUNT(*) FROM users")->fetchColumn();
        $stats['pets'] = $testPdo->query("SELECT COUNT(*) FROM pets")->fetchColumn();
        $stats['categories'] = $testPdo->query("SELECT COUNT(*) FROM categories")->fetchColumn();
        $stats['applications'] = $testPdo->query("SELECT COUNT(*) FROM adoption_applications")->fetchColumn();
        $dbInstalled = true;
    } catch (Exception $e) {
        $dbInstalled = false;
    }
}
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>PawConnect - Database Installer</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/css/bootstrap.min.css" rel="stylesheet">
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.11.3/font/bootstrap-icons.min.css">
    <link href="https://fonts.googleapis.com/css2?family=Plus+Jakarta+Sans:wght@400;600;700;800&display=swap" rel="stylesheet">
    <style>
        body { font-family: 'Plus Jakarta Sans', sans-serif; background: #f8fafc; color: #1e293b; min-height: 100vh; display: flex; align-items: center; justify-content: center; padding: 20px; }
        .installer-card { background: #fff; border-radius: 20px; border: 1px solid #e2e8f0; max-width: 650px; width: 100%; padding: 40px; box-shadow: 0 10px 30px rgba(0,0,0,0.06); }
        .brand-icon { width: 50px; height: 50px; border-radius: 14px; background: linear-gradient(135deg, #f26440, #ff8c69); color: #fff; display: inline-flex; align-items: center; justify-content: center; font-size: 1.5rem; margin-bottom: 15px; }
    </style>
</head>
<body>

<div class="installer-card">
    <div class="text-center mb-4">
        <div class="brand-icon"><i class="bi bi-heart-fill"></i></div>
        <h2 class="fw-bold">PawConnect Database Setup</h2>
        <p class="text-muted">One-click database initialization and seed data manager</p>
    </div>

    <?php if (!empty($message)): ?>
        <div class="alert alert-<?= $messageType ?> shadow-sm">
            <i class="bi bi-info-circle-fill me-2"></i> <?= htmlspecialchars($message) ?>
        </div>
    <?php endif; ?>

    <div class="p-3 bg-light rounded-3 border mb-4">
        <h6 class="fw-bold mb-2"><i class="bi bi-hdd-network text-primary me-2"></i> System Diagnostics</h6>
        <ul class="list-unstyled mb-0 small">
            <li class="mb-1"><i class="bi bi-check-circle-fill text-success me-2"></i> PHP Version: <strong><?= PHP_VERSION ?></strong></li>
            <li class="mb-1"><i class="bi bi-check-circle-fill text-success me-2"></i> PDO MySQL Extension: <strong><?= extension_loaded('pdo_mysql') ? 'Enabled' : 'Disabled' ?></strong></li>
            <li class="mb-1"><i class="bi bi-check-circle-fill text-success me-2"></i> Target Database: <strong>pawconnect_db (MySQL @ 127.0.0.1:3306)</strong></li>
            <li><i class="bi <?= $dbInstalled ? 'bi-check-circle-fill text-success' : 'bi-exclamation-circle-fill text-warning' ?> me-2"></i> Database Status: <strong><?= $dbInstalled ? 'Ready & Populated' : 'Not Initialized' ?></strong></li>
        </ul>
    </div>

    <?php if ($dbInstalled && !empty($stats)): ?>
        <div class="row g-2 text-center mb-4">
            <div class="col-3">
                <div class="p-2 bg-light border rounded-3">
                    <div class="fw-bold fs-4 text-dark"><?= $stats['users'] ?></div>
                    <div class="small text-muted">Users</div>
                </div>
            </div>
            <div class="col-3">
                <div class="p-2 bg-light border rounded-3">
                    <div class="fw-bold fs-4 text-primary"><?= $stats['pets'] ?></div>
                    <div class="small text-muted">Pets</div>
                </div>
            </div>
            <div class="col-3">
                <div class="p-2 bg-light border rounded-3">
                    <div class="fw-bold fs-4 text-success"><?= $stats['categories'] ?></div>
                    <div class="small text-muted">Species</div>
                </div>
            </div>
            <div class="col-3">
                <div class="p-2 bg-light border rounded-3">
                    <div class="fw-bold fs-4 text-warning"><?= $stats['applications'] ?></div>
                    <div class="small text-muted">Applications</div>
                </div>
            </div>
        </div>

        <div class="p-3 border rounded-3 mb-4 bg-light small">
            <div class="fw-bold text-dark mb-1"><i class="bi bi-key-fill text-warning me-1"></i> Pre-Configured Demo Accounts:</div>
            <div>&bull; <strong>Administrator:</strong> <code>admin@pawconnect.org</code> / password: <code>admin123</code></div>
            <div>&bull; <strong>Adopter User:</strong> <code>john@example.com</code> / password: <code>user123</code></div>
        </div>
    <?php endif; ?>

    <form action="/install.php" method="POST" onsubmit="return confirm('This will create or reset all tables in `pawconnect_db` with fresh seed data. Continue?');">
        <input type="hidden" name="action" value="install">
        <button type="submit" class="btn btn-primary w-100 py-3 fw-bold mb-3" style="background: #f26440; border-color: #f26440;">
            <i class="bi bi-database-check me-2"></i> <?= $dbInstalled ? 'Re-Initialize & Re-Seed Database' : 'Initialize Database Now' ?>
        </button>
    </form>

    <?php if ($dbInstalled): ?>
        <div class="d-flex gap-2">
            <a href="/index.php" class="btn btn-outline-dark w-50 py-2">
                <i class="bi bi-house-door me-1"></i> Go to Homepage
            </a>
            <a href="/login.php?demo=admin" class="btn btn-dark w-50 py-2">
                <i class="bi bi-shield-lock-fill text-warning me-1"></i> Open Admin Panel
            </a>
        </div>
    <?php endif; ?>
</div>

</body>
</html>
