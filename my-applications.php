<?php
/**
 * PawConnect - User's My Adoption Applications & Real-Time Status Tracking
 */
$pageTitle = "My Adoption Applications";
require_once __DIR__ . '/includes/header.php';

require_login();
$user = current_user();
$db = getDB();

// Handle application cancellation if requested by user
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['action']) && $_POST['action'] === 'cancel') {
    if (verify_csrf($_POST['csrf_token'] ?? '')) {
        $appId = (int)($_POST['app_id'] ?? 0);
        $checkStmt = $db->prepare("SELECT pet_id FROM adoption_applications WHERE id = ? AND user_id = ? AND status = 'Pending'");
        $checkStmt->execute([$appId, $user['id']]);
        $row = $checkStmt->fetch();

        if ($row) {
            $delStmt = $db->prepare("DELETE FROM adoption_applications WHERE id = ?");
            $delStmt->execute([$appId]);

            // Release pet back to Available if no other pending applications
            $otherApps = $db->prepare("SELECT COUNT(*) FROM adoption_applications WHERE pet_id = ? AND status IN ('Pending', 'Under Review')");
            $otherApps->execute([$row['pet_id']]);
            if ($otherApps->fetchColumn() == 0) {
                $db->prepare("UPDATE pets SET status = 'Available' WHERE id = ? AND status = 'Pending'")->execute([$row['pet_id']]);
            }

            set_flash('info', 'Your application has been withdrawn.');
            header('Location: /my-applications.php');
            exit;
        }
    }
}

// Fetch all applications by this user
$stmt = $db->prepare("
    SELECT a.*, p.name as pet_name, p.breed as pet_breed, p.image_url as pet_image, 
           p.adoption_fee as pet_fee, p.status as pet_status,
           c.name as category_name, s.name as shelter_name, s.phone as shelter_phone, s.email as shelter_email
    FROM adoption_applications a
    JOIN pets p ON a.pet_id = p.id
    JOIN categories c ON p.category_id = c.id
    LEFT JOIN shelters s ON p.shelter_id = s.id
    WHERE a.user_id = ?
    ORDER BY a.id DESC
");
$stmt->execute([$user['id']]);
$applications = $stmt->fetchAll();
?>

<div class="container py-4">
    <!-- Breadcrumbs -->
    <nav aria-label="breadcrumb" class="mb-4">
        <ol class="breadcrumb">
            <li class="breadcrumb-item"><a href="/index.php">Home</a></li>
            <li class="breadcrumb-item active" aria-current="page">My Applications</li>
        </ol>
    </nav>

    <div class="d-flex flex-wrap justify-content-between align-items-center mb-4">
        <div>
            <span class="badge bg-light text-primary px-3 py-2 rounded-pill fw-bold">Applicant Dashboard</span>
            <h2 class="mt-2 mb-1">My Adoption Requests</h2>
            <p class="text-muted">Track real-time status updates and shelter communications for your pet adoptions.</p>
        </div>
        <a href="/pets.php" class="btn btn-paw-primary">
            <i class="bi bi-search-heart me-1"></i> Browse More Pets
        </a>
    </div>

    <?php if (empty($applications)): ?>
        <div class="bg-white rounded-4 border p-5 text-center shadow-sm">
            <div class="display-3 text-muted mb-3"><i class="bi bi-folder2-open"></i></div>
            <h4>You haven't submitted any adoption applications yet</h4>
            <p class="text-muted">Once you find a companion and submit an application, you can track its review progress right here.</p>
            <a href="/pets.php" class="btn btn-paw-primary mt-2">
                <i class="bi bi-heart-fill me-1"></i> Meet Available Pets
            </a>
        </div>
    <?php else: ?>
        <div class="d-flex flex-column gap-4">
            <?php foreach ($applications as $app): ?>
                <div class="bg-white rounded-4 border shadow-sm p-4">
                    <!-- Top Bar: App No, Pet, Status -->
                    <div class="row align-items-center g-3 pb-3 border-bottom">
                        <div class="col-md-7 d-flex align-items-center gap-3">
                            <img src="<?= htmlspecialchars(get_pet_image($app['pet_image'])) ?>" alt="<?= htmlspecialchars($app['pet_name']) ?>" class="rounded-3 object-fit-cover shadow-sm" style="width: 70px; height: 70px;">
                            <div>
                                <div class="d-flex align-items-center gap-2">
                                    <h4 class="mb-0 fw-bold"><?= htmlspecialchars($app['pet_name']) ?></h4>
                                    <span class="badge bg-secondary-subtle text-secondary small"><?= htmlspecialchars($app['category_name']) ?></span>
                                </div>
                                <div class="text-muted small mt-1">
                                    <span class="fw-semibold text-dark">Ref: <?= htmlspecialchars($app['application_no']) ?></span> &bull; 
                                    Submitted <?= format_date($app['created_at']) ?> (<?= time_ago($app['created_at']) ?>)
                                </div>
                            </div>
                        </div>
                        <div class="col-md-5 text-md-end">
                            <div class="mb-1">
                                <span class="small text-muted me-2">Current Status:</span>
                                <?= status_badge($app['status']) ?>
                            </div>
                            <div class="small text-muted">
                                Shelter: <strong class="text-dark"><?= htmlspecialchars($app['shelter_name'] ?? 'Local Shelter') ?></strong>
                            </div>
                        </div>
                    </div>

                    <!-- Visual Step Timeline Tracker -->
                    <div class="timeline-track px-3">
                        <!-- Node 1: Submitted -->
                        <div class="timeline-node <?= in_array($app['status'], ['Pending', 'Under Review', 'Approved', 'Completed']) ? 'completed' : 'active' ?>">
                            <div class="timeline-icon">
                                <i class="bi bi-send-check"></i>
                            </div>
                            <div class="timeline-label">Application Submitted</div>
                            <div class="small text-muted"><?= format_date($app['created_at'], 'M d') ?></div>
                        </div>

                        <!-- Node 2: Under Review -->
                        <?php 
                            $isReviewDone = in_array($app['status'], ['Approved', 'Completed']);
                            $isReviewActive = ($app['status'] === 'Under Review');
                        ?>
                        <div class="timeline-node <?= $isReviewDone ? 'completed' : ($isReviewActive ? 'active' : '') ?>">
                            <div class="timeline-icon">
                                <i class="bi bi-file-earmark-person"></i>
                            </div>
                            <div class="timeline-label">Shelter Review</div>
                            <div class="small text-muted">
                                <?= ($app['status'] === 'Pending') ? 'In Queue' : 'In Progress' ?>
                            </div>
                        </div>

                        <!-- Node 3: Decision / Approval -->
                        <?php 
                            $isFinalApproved = in_array($app['status'], ['Approved', 'Completed']);
                            $isRejected = ($app['status'] === 'Rejected');
                        ?>
                        <div class="timeline-node <?= $isFinalApproved ? 'completed' : ($isRejected ? 'rejected' : '') ?>">
                            <div class="timeline-icon">
                                <?php if ($isRejected): ?>
                                    <i class="bi bi-x-circle"></i>
                                <?php elseif ($isFinalApproved): ?>
                                    <i class="bi bi-award-fill"></i>
                                <?php else: ?>
                                    <i class="bi bi-house-heart"></i>
                                <?php endif; ?>
                            </div>
                            <div class="timeline-label">
                                <?php if ($isRejected): ?>
                                    Application Declined
                                <?php elseif ($isFinalApproved): ?>
                                    Adoption Approved!
                                <?php else: ?>
                                    Homecoming
                                <?php endif; ?>
                            </div>
                            <div class="small text-muted">
                                <?= $app['reviewed_at'] ? format_date($app['reviewed_at'], 'M d') : 'Pending Decision' ?>
                            </div>
                        </div>
                    </div>

                    <!-- Shelter Feedback / Status Alert Box -->
                    <div class="mt-4 p-3 rounded-3 border <?= ($app['status'] === 'Approved') ? 'bg-success-subtle border-success' : (($app['status'] === 'Rejected') ? 'bg-danger-subtle border-danger' : 'bg-light') ?>">
                        <div class="d-flex align-items-start gap-3">
                            <div class="fs-4">
                                <?php if ($app['status'] === 'Approved'): ?>
                                    <i class="bi bi-check-circle-fill text-success"></i>
                                <?php elseif ($app['status'] === 'Rejected'): ?>
                                    <i class="bi bi-info-circle-fill text-danger"></i>
                                <?php elseif ($app['status'] === 'Under Review'): ?>
                                    <i class="bi bi-binoculars-fill text-primary"></i>
                                <?php else: ?>
                                    <i class="bi bi-clock-history text-warning"></i>
                                <?php endif; ?>
                            </div>
                            <div class="flex-grow-1">
                                <h6 class="fw-bold mb-1">
                                    <?php if ($app['status'] === 'Approved'): ?>
                                        Great News! Your Application is Approved!
                                    <?php elseif ($app['status'] === 'Rejected'): ?>
                                        Adoption Update
                                    <?php elseif ($app['status'] === 'Under Review'): ?>
                                        Your Application is Currently Under Active Review
                                    <?php else: ?>
                                        Application Received & Queued
                                    <?php endif; ?>
                                </h6>
                                <p class="mb-0 small text-secondary">
                                    <?= nl2br(htmlspecialchars($app['applicant_feedback'] ?? 'Our adoption coordinators are reviewing your housing details and veterinarian references. Thank you for your patience!')) ?>
                                </p>
                            </div>
                        </div>
                    </div>

                    <!-- Bottom Actions -->
                    <div class="d-flex flex-wrap justify-content-between align-items-center mt-3 pt-3 border-top">
                        <div class="small text-muted">
                            Need help? Contact <?= htmlspecialchars($app['shelter_name'] ?? 'Shelter') ?>: 
                            <a href="tel:<?= htmlspecialchars($app['shelter_phone'] ?? '') ?>" class="text-decoration-none fw-semibold"><?= htmlspecialchars($app['shelter_phone'] ?? '(555) 234-PAWS') ?></a>
                        </div>
                        <div class="d-flex gap-2 mt-2 mt-sm-0">
                            <a href="/pet-details.php?id=<?= $app['pet_id'] ?>" class="btn btn-sm btn-outline-secondary">
                                <i class="bi bi-eye me-1"></i> View Pet Profile
                            </a>
                            <?php if ($app['status'] === 'Pending'): ?>
                                <form action="/my-applications.php" method="POST" class="d-inline" data-confirm="Are you sure you want to withdraw this adoption request?">
                                    <input type="hidden" name="csrf_token" value="<?= csrf_token() ?>">
                                    <input type="hidden" name="action" value="cancel">
                                    <input type="hidden" name="app_id" value="<?= $app['id'] ?>">
                                    <button type="submit" class="btn btn-sm btn-outline-danger">
                                        <i class="bi bi-x-circle me-1"></i> Withdraw Request
                                    </button>
                                </form>
                            <?php endif; ?>
                        </div>
                    </div>
                </div>
            <?php endforeach; ?>
        </div>
    <?php endif; ?>
</div>

<?php include __DIR__ . '/includes/footer.php'; ?>
