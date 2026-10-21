<?php
/**
 * PawConnect - Admin User Management
 */
$pageTitle = "User Accounts";
require_once __DIR__ . '/../includes/header.php';

require_admin();
$db = getDB();

$search = trim($_GET['q'] ?? '');

$sql = "
    SELECT u.*, 
           (SELECT COUNT(*) FROM adoption_applications WHERE user_id = u.id) as applications_count,
           (SELECT COUNT(*) FROM favorites WHERE user_id = u.id) as favorites_count
    FROM users u
    WHERE 1=1
";
$params = [];

if ($search !== '') {
    $sql .= " AND (u.name LIKE ? OR u.email LIKE ? OR u.city LIKE ? OR u.phone LIKE ?)";
    $like = "%$search%";
    $params[] = $like;
    $params[] = $like;
    $params[] = $like;
    $params[] = $like;
}

$sql .= " ORDER BY u.id ASC";
$stmt = $db->prepare($sql);
$stmt->execute($params);
$users = $stmt->fetchAll();
?>

<div class="container py-4">
    <!-- Header -->
    <div class="d-flex flex-wrap justify-content-between align-items-center mb-4 pb-2 border-bottom">
        <div>
            <span class="badge bg-danger-subtle text-danger px-3 py-1 rounded-pill fw-bold">Admin Portal</span>
            <h2 class="mt-2 mb-0 fw-bold">User Account Directory</h2>
            <p class="text-muted small">Registered adopters and administrator accounts on PawConnect.</p>
        </div>
        <div class="d-flex gap-2 mt-2 mt-md-0">
            <a href="/admin/index.php" class="btn btn-outline-secondary">
                <i class="bi bi-arrow-left me-1"></i> Dashboard
            </a>
            <a href="/admin/applications.php" class="btn btn-dark">
                <i class="bi bi-file-earmark-text me-1"></i> View Applications
            </a>
        </div>
    </div>

    <!-- Search Box -->
    <div class="bg-white p-3 rounded-4 border shadow-sm mb-4">
        <form action="/admin/users.php" method="GET" class="row g-2">
            <div class="col-md-10">
                <div class="input-group">
                    <span class="input-group-text bg-light"><i class="bi bi-search"></i></span>
                    <input type="text" name="q" value="<?= htmlspecialchars($search) ?>" class="form-control" placeholder="Search by name, email, phone, city...">
                </div>
            </div>
            <div class="col-md-2">
                <button type="submit" class="btn btn-paw-primary w-100">
                    <i class="bi bi-funnel me-1"></i> Filter
                </button>
            </div>
        </form>
    </div>

    <!-- Users Table -->
    <div class="bg-white rounded-4 border shadow-sm p-4">
        <div class="d-flex justify-content-between align-items-center mb-3">
            <h5 class="fw-bold mb-0">Total Users: <?= count($users) ?></h5>
            <?php if ($search !== ''): ?>
                <a href="/admin/users.php" class="small text-danger">Clear Search</a>
            <?php endif; ?>
        </div>

        <div class="table-responsive">
            <table class="table table-hover align-middle mb-0">
                <thead class="table-light small">
                    <tr>
                        <th>User</th>
                        <th>Role</th>
                        <th>Contact</th>
                        <th>Location</th>
                        <th>Activity</th>
                        <th>Joined</th>
                    </tr>
                </thead>
                <tbody>
                    <?php foreach ($users as $u): ?>
                        <tr>
                            <td>
                                <div class="d-flex align-items-center gap-2">
                                    <div class="rounded-circle bg-light border d-flex align-items-center justify-content-center fw-bold text-secondary" style="width: 38px; height: 38px;">
                                        <?= strtoupper(substr($u['name'], 0, 1)) ?>
                                    </div>
                                    <div>
                                        <div class="fw-bold text-dark"><?= htmlspecialchars($u['name']) ?></div>
                                        <div class="small text-muted">ID: #<?= $u['id'] ?></div>
                                    </div>
                                </div>
                            </td>
                            <td>
                                <span class="badge <?= ($u['role'] === 'admin') ? 'bg-danger' : 'bg-primary' ?> px-2 py-1">
                                    <?= ucfirst($u['role']) ?>
                                </span>
                            </td>
                            <td>
                                <div class="small fw-semibold"><?= htmlspecialchars($u['email']) ?></div>
                                <div class="small text-muted"><?= htmlspecialchars($u['phone'] ?? 'No phone') ?></div>
                            </td>
                            <td class="small text-muted">
                                <?= htmlspecialchars($u['city'] ?? 'N/A') ?>
                            </td>
                            <td>
                                <div class="small">
                                    <span class="badge bg-secondary-subtle text-dark">
                                        <?= $u['applications_count'] ?> applications
                                    </span>
                                </div>
                                <div class="small text-muted mt-1">
                                    <?= $u['favorites_count'] ?> favorited pets
                                </div>
                            </td>
                            <td class="small text-muted">
                                <?= format_date($u['created_at']) ?>
                            </td>
                        </tr>
                    <?php endforeach; ?>
                </tbody>
            </table>
        </div>
    </div>
</div>

<?php include __DIR__ . '/../includes/footer.php'; ?>
