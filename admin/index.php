<?php
/**
 * PawConnect - Admin Dashboard
 */
$pageTitle = "Admin Dashboard";
require_once __DIR__ . '/../includes/header.php';

require_admin();
$db = getDB();

// 1. Compute KPI Metrics
$totalPets = $db->query("SELECT COUNT(*) FROM pets")->fetchColumn();
$availablePets = $db->query("SELECT COUNT(*) FROM pets WHERE status = 'Available'")->fetchColumn();
$adoptedPets = $db->query("SELECT COUNT(*) FROM pets WHERE status = 'Adopted'")->fetchColumn();

$totalApps = $db->query("SELECT COUNT(*) FROM adoption_applications")->fetchColumn();
$pendingApps = $db->query("SELECT COUNT(*) FROM adoption_applications WHERE status = 'Pending'")->fetchColumn();
$underReviewApps = $db->query("SELECT COUNT(*) FROM adoption_applications WHERE status = 'Under Review'")->fetchColumn();
$approvedApps = $db->query("SELECT COUNT(*) FROM adoption_applications WHERE status = 'Approved'")->fetchColumn();

$totalUsers = $db->query("SELECT COUNT(*) FROM users WHERE role = 'user'")->fetchColumn();

// 2. Fetch Recent Applications
$recentApps = $db->query("
    SELECT a.*, p.name as pet_name, p.image_url as pet_image, c.name as category_name
    FROM adoption_applications a
    JOIN pets p ON a.pet_id = p.id
    JOIN categories c ON p.category_id = c.id
    ORDER BY a.id DESC
    LIMIT 6
")->fetchAll();

// 3. Fetch Recent Pets
$recentPets = $db->query("
    SELECT p.*, c.name as category_name
    FROM pets p
    JOIN categories c ON p.category_id = c.id
    ORDER BY p.id DESC
    LIMIT 5
")->fetchAll();
?>

<div class="container py-4">
    <!-- Admin Header -->
    <div class="d-flex flex-wrap justify-content-between align-items-center mb-4 pb-2 border-bottom">
        <div>
            <span class="badge bg-danger-subtle text-danger px-3 py-1 rounded-pill fw-bold">Admin Portal</span>
            <h2 class="mt-2 mb-0 fw-bold">Shelter & System Operations</h2>
            <p class="text-muted small">Manage animals, evaluate adoption requests, and oversee shelter workflow.</p>
        </div>
        <div class="d-flex gap-2 mt-2 mt-md-0">
            <a href="/admin/pet-add.php" class="btn btn-paw-primary">
                <i class="bi bi-plus-circle me-1"></i> Add New Pet
            </a>
            <a href="/admin/applications.php" class="btn btn-dark">
                <i class="bi bi-file-earmark-text me-1"></i> Review Requests (<?= $pendingApps ?>)
            </a>
        </div>
    </div>

    <!-- KPI Metric Cards Grid -->
    <div class="row g-3 mb-4">
        <!-- Available Pets -->
        <div class="col-sm-6 col-xl-3">
            <div class="stat-metric-card">
                <div>
                    <div class="text-muted small fw-bold text-uppercase">Available Pets</div>
                    <div class="stat-metric-num text-success"><?= $availablePets ?></div>
                    <div class="small text-muted"><?= $totalPets ?> total registered</div>
                </div>
                <div class="stat-metric-icon bg-success-subtle text-success">
                    <i class="bi bi-heart-pulse-fill"></i>
                </div>
            </div>
        </div>

        <!-- Pending Applications -->
        <div class="col-sm-6 col-xl-3">
            <div class="stat-metric-card">
                <div>
                    <div class="text-muted small fw-bold text-uppercase">Pending Review</div>
                    <div class="stat-metric-num text-warning"><?= $pendingApps ?></div>
                    <div class="small text-muted"><?= $underReviewApps ?> currently in review</div>
                </div>
                <div class="stat-metric-icon bg-warning-subtle text-warning">
                    <i class="bi bi-hourglass-split"></i>
                </div>
            </div>
        </div>

        <!-- Total Adopted -->
        <div class="col-sm-6 col-xl-3">
            <div class="stat-metric-card">
                <div>
                    <div class="text-muted small fw-bold text-uppercase">Successful Adoptions</div>
                    <div class="stat-metric-num text-primary"><?= $adoptedPets ?></div>
                    <div class="small text-muted"><?= $approvedApps ?> total approved</div>
                </div>
                <div class="stat-metric-icon bg-primary-subtle text-primary">
                    <i class="bi bi-award-fill"></i>
                </div>
            </div>
        </div>

        <!-- Registered Adopters -->
        <div class="col-sm-6 col-xl-3">
            <div class="stat-metric-card">
                <div>
                    <div class="text-muted small fw-bold text-uppercase">Registered Adopters</div>
                    <div class="stat-metric-num text-dark"><?= $totalUsers ?></div>
                    <div class="small text-muted"><?= $totalApps ?> total requests submitted</div>
                </div>
                <div class="stat-metric-icon bg-secondary-subtle text-secondary">
                    <i class="bi bi-people-fill"></i>
                </div>
            </div>
        </div>
    </div>

    <!-- Navigation Pills -->
    <div class="bg-white p-3 rounded-4 border shadow-sm mb-4">
        <ul class="nav nav-pills gap-2">
            <li class="nav-item">
                <a class="nav-link active" href="/admin/index.php"><i class="bi bi-speedometer2 me-1"></i> Dashboard Overview</a>
            </li>
            <li class="nav-item">
                <a class="nav-link" href="/admin/pets.php"><i class="bi bi-heart me-1"></i> Pet Inventory</a>
            </li>
            <li class="nav-item">
                <a class="nav-link" href="/admin/pet-add.php"><i class="bi bi-plus-lg me-1"></i> Add New Pet</a>
            </li>
            <li class="nav-item">
                <a class="nav-link" href="/admin/applications.php"><i class="bi bi-file-earmark-check me-1"></i> Adoption Requests (<?= $pendingApps ?> Pending)</a>
            </li>
            <li class="nav-item">
                <a class="nav-link" href="/admin/users.php"><i class="bi bi-people me-1"></i> User Accounts</a>
            </li>
        </ul>
    </div>

    <div class="row g-4">
        <!-- Recent Adoption Applications Table -->
        <div class="col-lg-8">
            <div class="bg-white rounded-4 border shadow-sm p-4 h-100">
                <div class="d-flex justify-content-between align-items-center mb-3">
                    <h5 class="fw-bold mb-0"><i class="bi bi-file-earmark-person me-2 text-primary"></i> Recent Adoption Requests</h5>
                    <a href="/admin/applications.php" class="small fw-bold">View All (<?= $totalApps ?>) <i class="bi bi-arrow-right"></i></a>
                </div>

                <?php if (empty($recentApps)): ?>
                    <p class="text-muted text-center py-4">No adoption requests received yet.</p>
                <?php else: ?>
                    <div class="table-responsive">
                        <table class="table table-hover align-middle mb-0">
                            <thead class="table-light small">
                                <tr>
                                    <th>Ref / Applicant</th>
                                    <th>Pet Requested</th>
                                    <th>Date</th>
                                    <th>Status</th>
                                    <th class="text-end">Action</th>
                                </tr>
                            </thead>
                            <tbody>
                                <?php foreach ($recentApps as $app): ?>
                                    <tr>
                                        <td>
                                            <div class="fw-bold text-dark"><?= htmlspecialchars($app['full_name']) ?></div>
                                            <div class="small text-muted"><?= htmlspecialchars($app['application_no']) ?></div>
                                        </td>
                                        <td>
                                            <div class="d-flex align-items-center gap-2">
                                                <img src="<?= htmlspecialchars(get_pet_image($app['pet_image'])) ?>" alt="" class="rounded-circle object-fit-cover" style="width: 32px; height: 32px;">
                                                <span class="fw-semibold"><?= htmlspecialchars($app['pet_name']) ?></span>
                                            </div>
                                        </td>
                                        <td class="small text-muted"><?= format_date($app['created_at'], 'M d, Y') ?></td>
                                        <td><?= status_badge($app['status']) ?></td>
                                        <td class="text-end">
                                            <a href="/admin/application-view.php?id=<?= $app['id'] ?>" class="btn btn-sm btn-outline-primary">
                                                Review <i class="bi bi-arrow-right"></i>
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

        <!-- Recent Pets Inventory Summary -->
        <div class="col-lg-4">
            <div class="bg-white rounded-4 border shadow-sm p-4 h-100">
                <div class="d-flex justify-content-between align-items-center mb-3">
                    <h5 class="fw-bold mb-0"><i class="bi bi-heart me-2 text-danger"></i> Recent Pets</h5>
                    <a href="/admin/pets.php" class="small fw-bold">View Inventory <i class="bi bi-arrow-right"></i></a>
                </div>

                <div class="d-flex flex-column gap-3">
                    <?php foreach ($recentPets as $p): ?>
                        <div class="d-flex align-items-center justify-content-between p-2 rounded-3 border">
                            <div class="d-flex align-items-center gap-2">
                                <img src="<?= htmlspecialchars(get_pet_image($p['image_url'])) ?>" alt="" class="rounded-3 object-fit-cover" style="width: 44px; height: 44px;">
                                <div>
                                    <div class="fw-bold small mb-0"><?= htmlspecialchars($p['name']) ?></div>
                                    <div class="text-muted" style="font-size: 0.75rem;"><?= htmlspecialchars($p['breed']) ?></div>
                                </div>
                            </div>
                            <div class="text-end">
                                <?= status_badge($p['status']) ?>
                                <div class="mt-1">
                                    <a href="/admin/pet-edit.php?id=<?= $p['id'] ?>" class="small text-decoration-none">Edit</a>
                                </div>
                            </div>
                        </div>
                    <?php endforeach; ?>
                </div>

                <div class="mt-4 pt-2">
                    <a href="/admin/pet-add.php" class="btn btn-outline-secondary btn-sm w-100">
                        <i class="bi bi-plus-circle me-1"></i> Add Another Pet
                    </a>
                </div>
            </div>
        </div>
    </div>
</div>

<?php include __DIR__ . '/../includes/footer.php'; ?>
