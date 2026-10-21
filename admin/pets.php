<?php
/**
 * PawConnect - Admin Pet Management Table
 */
$pageTitle = "Pet Management";
require_once __DIR__ . '/../includes/header.php';

require_admin();
$db = getDB();

$search = trim($_GET['q'] ?? '');
$status = trim($_GET['status'] ?? '');
$category = trim($_GET['category'] ?? '');

$categories = $db->query("SELECT * FROM categories ORDER BY name ASC")->fetchAll();

$sql = "
    SELECT p.*, c.name as category_name, s.name as shelter_name,
           (SELECT COUNT(*) FROM adoption_applications WHERE pet_id = p.id) as applications_count
    FROM pets p
    JOIN categories c ON p.category_id = c.id
    LEFT JOIN shelters s ON p.shelter_id = s.id
    WHERE 1=1
";
$params = [];

if ($search !== '') {
    $sql .= " AND (p.name LIKE ? OR p.breed LIKE ? OR p.location LIKE ?)";
    $like = "%$search%";
    $params[] = $like;
    $params[] = $like;
    $params[] = $like;
}

if ($status !== '') {
    $sql .= " AND p.status = ?";
    $params[] = $status;
}

if ($category !== '') {
    $sql .= " AND c.slug = ?";
    $params[] = $category;
}

$sql .= " ORDER BY p.id DESC";
$stmt = $db->prepare($sql);
$stmt->execute($params);
$pets = $stmt->fetchAll();
?>

<div class="container py-4">
    <div class="d-flex flex-wrap justify-content-between align-items-center mb-4 pb-2 border-bottom">
        <div>
            <span class="badge bg-danger-subtle text-danger px-3 py-1 rounded-pill fw-bold">Admin Portal</span>
            <h2 class="mt-2 mb-0 fw-bold">Pet Inventory Management</h2>
            <p class="text-muted small">Add, update, and manage all rescue animals in the system.</p>
        </div>
        <div class="d-flex gap-2 mt-2 mt-md-0">
            <a href="/admin/index.php" class="btn btn-outline-secondary">
                <i class="bi bi-arrow-left me-1"></i> Dashboard
            </a>
            <a href="/admin/pet-add.php" class="btn btn-paw-primary">
                <i class="bi bi-plus-circle me-1"></i> Add New Pet
            </a>
        </div>
    </div>

    <!-- Filters & Search Toolbar -->
    <div class="bg-white p-3 rounded-4 border shadow-sm mb-4">
        <form action="/admin/pets.php" method="GET" class="row g-2 align-items-center">
            <div class="col-md-5">
                <div class="input-group">
                    <span class="input-group-text bg-light"><i class="bi bi-search"></i></span>
                    <input type="text" name="q" value="<?= htmlspecialchars($search) ?>" class="form-control" placeholder="Search by name, breed, location...">
                </div>
            </div>
            <div class="col-md-3">
                <select name="status" class="form-select" onchange="this.form.submit()">
                    <option value="">All Statuses</option>
                    <option value="Available" <?= ($status === 'Available') ? 'selected' : '' ?>>Available</option>
                    <option value="Pending" <?= ($status === 'Pending') ? 'selected' : '' ?>>Pending</option>
                    <option value="Adopted" <?= ($status === 'Adopted') ? 'selected' : '' ?>>Adopted</option>
                </select>
            </div>
            <div class="col-md-3">
                <select name="category" class="form-select" onchange="this.form.submit()">
                    <option value="">All Species</option>
                    <?php foreach ($categories as $cat): ?>
                        <option value="<?= htmlspecialchars($cat['slug']) ?>" <?= ($category === $cat['slug']) ? 'selected' : '' ?>>
                            <?= htmlspecialchars($cat['name']) ?>
                        </option>
                    <?php endforeach; ?>
                </select>
            </div>
            <div class="col-md-1">
                <button type="submit" class="btn btn-dark w-100"><i class="bi bi-funnel"></i></button>
            </div>
        </form>
    </div>

    <!-- Pets Table -->
    <div class="bg-white rounded-4 border shadow-sm p-4">
        <div class="d-flex justify-content-between align-items-center mb-3">
            <h5 class="fw-bold mb-0">Total Pets: <?= count($pets) ?></h5>
            <?php if ($search !== '' || $status !== '' || $category !== ''): ?>
                <a href="/admin/pets.php" class="small text-danger">Clear Filters</a>
            <?php endif; ?>
        </div>

        <?php if (empty($pets)): ?>
            <div class="text-center py-5 text-muted">
                <i class="bi bi-search fs-1"></i>
                <p class="mt-2">No pets match your criteria.</p>
            </div>
        <?php else: ?>
            <div class="table-responsive">
                <table class="table table-hover align-middle mb-0">
                    <thead class="table-light small">
                        <tr>
                            <th>Pet</th>
                            <th>Species</th>
                            <th>Breed & Age</th>
                            <th>Fee</th>
                            <th>Shelter / Location</th>
                            <th>Status</th>
                            <th>Applications</th>
                            <th class="text-end">Actions</th>
                        </tr>
                    </thead>
                    <tbody>
                        <?php foreach ($pets as $p): ?>
                            <tr>
                                <td>
                                    <div class="d-flex align-items-center gap-2">
                                        <img src="<?= htmlspecialchars(get_pet_image($p['image_url'])) ?>" alt="" class="rounded-3 object-fit-cover shadow-sm" style="width: 46px; height: 46px;">
                                        <div>
                                            <div class="fw-bold text-dark"><?= htmlspecialchars($p['name']) ?></div>
                                            <div class="small text-muted">ID: #<?= $p['id'] ?></div>
                                        </div>
                                    </div>
                                </td>
                                <td><span class="badge bg-secondary-subtle text-dark"><?= htmlspecialchars($p['category_name']) ?></span></td>
                                <td>
                                    <div class="small fw-semibold"><?= htmlspecialchars($p['breed']) ?></div>
                                    <div class="small text-muted"><?= get_pet_age($p['age_years'], $p['age_months']) ?> &bull; <?= htmlspecialchars($p['gender']) ?></div>
                                </td>
                                <td class="fw-bold text-primary">$<?= number_format($p['adoption_fee'], 0) ?></td>
                                <td class="small text-muted"><?= htmlspecialchars($p['location']) ?></td>
                                <td><?= status_badge($p['status']) ?></td>
                                <td>
                                    <?php if ($p['applications_count'] > 0): ?>
                                        <a href="/admin/applications.php?pet_id=<?= $p['id'] ?>" class="badge bg-warning-subtle text-warning-emphasis text-decoration-none">
                                            <?= $p['applications_count'] ?> request<?= $p['applications_count'] > 1 ? 's' : '' ?>
                                        </a>
                                    <?php else: ?>
                                        <span class="text-muted small">0</span>
                                    <?php endif; ?>
                                </td>
                                <td class="text-end">
                                    <div class="d-inline-flex gap-1">
                                        <a href="/pet-details.php?id=<?= $p['id'] ?>" target="_blank" class="btn btn-sm btn-outline-secondary" title="View Public Profile">
                                            <i class="bi bi-box-arrow-up-right"></i>
                                        </a>
                                        <a href="/admin/pet-edit.php?id=<?= $p['id'] ?>" class="btn btn-sm btn-outline-primary" title="Edit Pet">
                                            <i class="bi bi-pencil-square"></i>
                                        </a>
                                        <form action="/admin/pet-delete.php" method="POST" class="d-inline" data-confirm="Are you sure you want to delete this pet profile? This cannot be undone.">
                                            <input type="hidden" name="csrf_token" value="<?= csrf_token() ?>">
                                            <input type="hidden" name="pet_id" value="<?= $p['id'] ?>">
                                            <button type="submit" class="btn btn-sm btn-outline-danger" title="Delete Pet">
                                                <i class="bi bi-trash"></i>
                                            </button>
                                        </form>
                                    </div>
                                </td>
                            </tr>
                        <?php endforeach; ?>
                    </tbody>
                </table>
            </div>
        <?php endif; ?>
    </div>
</div>

<?php include __DIR__ . '/../includes/footer.php'; ?>
