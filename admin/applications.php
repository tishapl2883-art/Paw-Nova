<?php
/**
 * PawConnect - Admin Adoption Applications Management
 */
$pageTitle = "Adoption Requests";
require_once __DIR__ . '/../includes/header.php';

require_admin();
$db = getDB();

$status = trim($_GET['status'] ?? '');
$search = trim($_GET['q'] ?? '');
$petId = isset($_GET['pet_id']) ? (int)$_GET['pet_id'] : 0;

$sql = "
    SELECT a.*, p.name as pet_name, p.image_url as pet_image, p.breed as pet_breed,
           c.name as category_name, s.name as shelter_name
    FROM adoption_applications a
    JOIN pets p ON a.pet_id = p.id
    JOIN categories c ON p.category_id = c.id
    LEFT JOIN shelters s ON p.shelter_id = s.id
    WHERE 1=1
";
$params = [];

if ($status !== '' && $status !== 'All') {
    $sql .= " AND a.status = ?";
    $params[] = $status;
}

if ($petId > 0) {
    $sql .= " AND a.pet_id = ?";
    $params[] = $petId;
}

if ($search !== '') {
    $sql .= " AND (a.application_no LIKE ? OR a.full_name LIKE ? OR a.email LIKE ? OR a.city LIKE ? OR p.name LIKE ?)";
    $like = "%$search%";
    $params[] = $like;
    $params[] = $like;
    $params[] = $like;
    $params[] = $like;
    $params[] = $like;
}

$sql .= " ORDER BY a.id DESC";
$stmt = $db->prepare($sql);
$stmt->execute($params);
$applications = $stmt->fetchAll();

// Get counts by status for badges
$counts = [
    'all' => $db->query("SELECT COUNT(*) FROM adoption_applications")->fetchColumn(),
    'pending' => $db->query("SELECT COUNT(*) FROM adoption_applications WHERE status = 'Pending'")->fetchColumn(),
    'under_review' => $db->query("SELECT COUNT(*) FROM adoption_applications WHERE status = 'Under Review'")->fetchColumn(),
    'approved' => $db->query("SELECT COUNT(*) FROM adoption_applications WHERE status = 'Approved'")->fetchColumn(),
    'rejected' => $db->query("SELECT COUNT(*) FROM adoption_applications WHERE status = 'Rejected'")->fetchColumn(),
];
?>

<div class="container py-4">
    <!-- Header -->
    <div class="d-flex flex-wrap justify-content-between align-items-center mb-4 pb-2 border-bottom">
        <div>
            <span class="badge bg-danger-subtle text-danger px-3 py-1 rounded-pill fw-bold">Admin Portal</span>
            <h2 class="mt-2 mb-0 fw-bold">Adoption Requests & Inquiries</h2>
            <p class="text-muted small">Evaluate applicant questionnaires, update review stages, and notify families.</p>
        </div>
        <div class="d-flex gap-2 mt-2 mt-md-0">
            <a href="/admin/index.php" class="btn btn-outline-secondary">
                <i class="bi bi-arrow-left me-1"></i> Dashboard
            </a>
            <a href="/admin/pets.php" class="btn btn-paw-outline">
                <i class="bi bi-heart me-1"></i> View Pet Inventory
            </a>
        </div>
    </div>

    <!-- Status Tabs -->
    <div class="d-flex flex-wrap gap-2 mb-4">
        <a href="/admin/applications.php" class="btn btn-sm <?= ($status === '' || $status === 'All') ? 'btn-dark' : 'btn-outline-secondary' ?>">
            All (<?= $counts['all'] ?>)
        </a>
        <a href="/admin/applications.php?status=Pending" class="btn btn-sm <?= ($status === 'Pending') ? 'btn-warning text-dark fw-bold' : 'btn-outline-warning text-dark' ?>">
            <i class="bi bi-clock me-1"></i> Pending (<?= $counts['pending'] ?>)
        </a>
        <a href="/admin/applications.php?status=Under+Review" class="btn btn-sm <?= ($status === 'Under Review') ? 'btn-info text-dark fw-bold' : 'btn-outline-info text-dark' ?>">
            <i class="bi bi-binoculars me-1"></i> Under Review (<?= $counts['under_review'] ?>)
        </a>
        <a href="/admin/applications.php?status=Approved" class="btn btn-sm <?= ($status === 'Approved') ? 'btn-success fw-bold' : 'btn-outline-success' ?>">
            <i class="bi bi-check-circle me-1"></i> Approved (<?= $counts['approved'] ?>)
        </a>
        <a href="/admin/applications.php?status=Rejected" class="btn btn-sm <?= ($status === 'Rejected') ? 'btn-danger fw-bold' : 'btn-outline-danger' ?>">
            <i class="bi bi-x-circle me-1"></i> Rejected (<?= $counts['rejected'] ?>)
        </a>
    </div>

    <!-- Search Box -->
    <div class="bg-white p-3 rounded-4 border shadow-sm mb-4">
        <form action="/admin/applications.php" method="GET" class="row g-2">
            <input type="hidden" name="status" value="<?= htmlspecialchars($status) ?>">
            <div class="col-md-10">
                <div class="input-group">
                    <span class="input-group-text bg-light"><i class="bi bi-search"></i></span>
                    <input type="text" name="q" value="<?= htmlspecialchars($search) ?>" class="form-control" placeholder="Search by application number, applicant name, email, city, or pet name...">
                </div>
            </div>
            <div class="col-md-2">
                <button type="submit" class="btn btn-paw-primary w-100">
                    <i class="bi bi-funnel me-1"></i> Search
                </button>
            </div>
        </form>
    </div>

    <!-- Applications Table -->
    <div class="bg-white rounded-4 border shadow-sm p-4">
        <div class="d-flex justify-content-between align-items-center mb-3">
            <h5 class="fw-bold mb-0">Total Results: <?= count($applications) ?></h5>
            <?php if ($search !== '' || $petId > 0 || ($status !== '' && $status !== 'All')): ?>
                <a href="/admin/applications.php" class="small text-danger">Reset Filters</a>
            <?php endif; ?>
        </div>

        <?php if (empty($applications)): ?>
            <div class="text-center py-5 text-muted">
                <i class="bi bi-folder2-open fs-1"></i>
                <p class="mt-2">No adoption requests found matching the selected criteria.</p>
            </div>
        <?php else: ?>
            <div class="table-responsive">
                <table class="table table-hover align-middle mb-0">
                    <thead class="table-light small">
                        <tr>
                            <th>Ref #</th>
                            <th>Applicant</th>
                            <th>Pet Requested</th>
                            <th>Housing / Experience</th>
                            <th>Submitted</th>
                            <th>Status</th>
                            <th class="text-end">Action</th>
                        </tr>
                    </thead>
                    <tbody>
                        <?php foreach ($applications as $app): ?>
                            <tr>
                                <td>
                                    <span class="fw-bold text-dark font-monospace"><?= htmlspecialchars($app['application_no']) ?></span>
                                </td>
                                <td>
                                    <div class="fw-bold text-dark"><?= htmlspecialchars($app['full_name']) ?></div>
                                    <div class="small text-muted"><?= htmlspecialchars($app['email']) ?> &bull; <?= htmlspecialchars($app['phone']) ?></div>
                                    <div class="small text-muted"><i class="bi bi-geo-alt"></i> <?= htmlspecialchars($app['city']) ?>, <?= htmlspecialchars($app['state'] ?? '') ?></div>
                                </td>
                                <td>
                                    <div class="d-flex align-items-center gap-2">
                                        <img src="<?= htmlspecialchars(get_pet_image($app['pet_image'])) ?>" alt="" class="rounded-3 object-fit-cover shadow-sm" style="width: 44px; height: 44px;">
                                        <div>
                                            <div class="fw-bold text-dark"><?= htmlspecialchars($app['pet_name']) ?></div>
                                            <div class="small text-muted"><?= htmlspecialchars($app['category_name']) ?> (<?= htmlspecialchars($app['pet_breed']) ?>)</div>
                                        </div>
                                    </div>
                                </td>
                                <td>
                                    <div class="small fw-semibold"><?= htmlspecialchars($app['housing_type']) ?></div>
                                    <div class="small text-muted">
                                        <?= $app['has_yard'] ? ($app['yard_fenced'] ? 'Fenced Yard' : 'Yard (Unfenced)') : 'No Yard' ?> &bull; 
                                        <?= htmlspecialchars($app['pet_experience']) ?>
                                    </div>
                                </td>
                                <td>
                                    <div class="small text-dark fw-semibold"><?= format_date($app['created_at']) ?></div>
                                    <div class="small text-muted"><?= time_ago($app['created_at']) ?></div>
                                </td>
                                <td>
                                    <?= status_badge($app['status']) ?>
                                </td>
                                <td class="text-end">
                                    <a href="/admin/application-view.php?id=<?= $app['id'] ?>" class="btn btn-sm btn-paw-primary">
                                        <i class="bi bi-file-earmark-person me-1"></i> Review
                                    </a>
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
