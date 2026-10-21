<?php
/**
 * PawConnect - Detailed Adoption Application Review & Decision Portal
 */
$pageTitle = "Review Adoption Application";
require_once __DIR__ . '/../includes/header.php';

require_admin();
$db = getDB();

$appId = (int)($_GET['id'] ?? $_POST['app_id'] ?? 0);
if ($appId <= 0) {
    set_flash('danger', 'Invalid application ID.');
    header("Location: /admin/applications.php");
    exit;
}

// Fetch application with pet and applicant details
$stmt = $db->prepare("
    SELECT a.*, 
           p.id as pet_id, p.name as pet_name, p.breed as pet_breed, p.image_url as pet_image, 
           p.age_years, p.age_months, p.gender as pet_gender, p.status as pet_status,
           p.adoption_fee as pet_fee, c.name as category_name,
           s.name as shelter_name, s.phone as shelter_phone,
           u.email as account_email, u.name as account_name,
           r.name as reviewer_name
    FROM adoption_applications a
    JOIN pets p ON a.pet_id = p.id
    JOIN categories c ON p.category_id = c.id
    LEFT JOIN shelters s ON p.shelter_id = s.id
    JOIN users u ON a.user_id = u.id
    LEFT JOIN users r ON a.reviewed_by = r.id
    WHERE a.id = ?
");
$stmt->execute([$appId]);
$app = $stmt->fetch();

if (!$app) {
    set_flash('danger', 'Application record not found.');
    header("Location: /admin/applications.php");
    exit;
}

$currentUser = current_user();

// Handle Status & Notes Update
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    if (!verify_csrf($_POST['csrf_token'] ?? '')) {
        set_flash('danger', 'Invalid security token.');
        header("Location: /admin/application-view.php?id=" . $appId);
        exit;
    }

    $newStatus = sanitize($_POST['status'] ?? $app['status']);
    $adminNotes = sanitize($_POST['admin_notes'] ?? '');
    $applicantFeedback = sanitize($_POST['applicant_feedback'] ?? '');
    $updatePetStatus = isset($_POST['update_pet_status']) ? 1 : 0;

    $allowedStatuses = ['Pending', 'Under Review', 'Approved', 'Rejected', 'Completed'];
    if (in_array($newStatus, $allowedStatuses)) {
        try {
            $upStmt = $db->prepare("
                UPDATE adoption_applications SET
                    status = ?,
                    admin_notes = ?,
                    applicant_feedback = ?,
                    reviewed_by = ?,
                    reviewed_at = NOW()
                WHERE id = ?
            ");
            $upStmt->execute([$newStatus, $adminNotes, $applicantFeedback, $currentUser['id'], $appId]);

            // If user checked update pet status:
            if ($updatePetStatus) {
                if ($newStatus === 'Approved' || $newStatus === 'Completed') {
                    $db->prepare("UPDATE pets SET status = 'Adopted' WHERE id = ?")->execute([$app['pet_id']]);
                } elseif ($newStatus === 'Rejected') {
                    // Check if other pending applications exist
                    $countOther = $db->prepare("SELECT COUNT(*) FROM adoption_applications WHERE pet_id = ? AND id != ? AND status IN ('Pending', 'Under Review')");
                    $countOther->execute([$app['pet_id'], $appId]);
                    if ($countOther->fetchColumn() == 0) {
                        $db->prepare("UPDATE pets SET status = 'Available' WHERE id = ?")->execute([$app['pet_id']]);
                    }
                } elseif ($newStatus === 'Under Review' || $newStatus === 'Pending') {
                    $db->prepare("UPDATE pets SET status = 'Pending' WHERE id = ? AND status != 'Adopted'")->execute([$app['pet_id']]);
                }
            }

            set_flash('success', "Application #{$app['application_no']} status updated to '{$newStatus}' successfully!");
            header("Location: /admin/application-view.php?id=" . $appId);
            exit;

        } catch (Exception $e) {
            set_flash('danger', 'Error updating application: ' . $e->getMessage());
        }
    }
}
?>

<div class="container py-4">
    <!-- Breadcrumbs -->
    <nav aria-label="breadcrumb" class="mb-4">
        <ol class="breadcrumb">
            <li class="breadcrumb-item"><a href="/admin/index.php">Admin</a></li>
            <li class="breadcrumb-item"><a href="/admin/applications.php">Adoption Requests</a></li>
            <li class="breadcrumb-item active" aria-current="page"><?= htmlspecialchars($app['application_no']) ?></li>
        </ol>
    </nav>

    <!-- Top Bar -->
    <div class="d-flex flex-wrap justify-content-between align-items-center mb-4 pb-2 border-bottom">
        <div>
            <div class="d-flex align-items-center gap-2">
                <h2 class="mb-0 fw-bold"><?= htmlspecialchars($app['application_no']) ?></h2>
                <?= status_badge($app['status']) ?>
            </div>
            <p class="text-muted small mt-1 mb-0">
                Submitted by <strong class="text-dark"><?= htmlspecialchars($app['full_name']) ?></strong> on <?= format_date($app['created_at'], 'F d, Y \a\t g:i A') ?>
            </p>
        </div>
        <div class="d-flex gap-2 mt-2 mt-md-0">
            <a href="/admin/applications.php" class="btn btn-outline-secondary">
                <i class="bi bi-arrow-left me-1"></i> All Requests
            </a>
            <a href="mailto:<?= htmlspecialchars($app['email']) ?>" class="btn btn-outline-primary">
                <i class="bi bi-envelope me-1"></i> Email Applicant
            </a>
            <a href="tel:<?= htmlspecialchars($app['phone']) ?>" class="btn btn-outline-success">
                <i class="bi bi-telephone me-1"></i> Call Applicant
            </a>
        </div>
    </div>

    <div class="row g-4">
        <!-- Left: Full Questionnaire Dossier -->
        <div class="col-lg-8">
            <!-- 1. Contact & Identity -->
            <div class="bg-white rounded-4 border shadow-sm p-4 mb-4">
                <h5 class="fw-bold border-bottom pb-2 mb-3 text-primary">
                    <i class="bi bi-person-lines-fill me-2"></i> 1. Applicant Profile
                </h5>
                <div class="row g-3">
                    <div class="col-sm-6">
                        <label class="text-muted small">Full Legal Name</label>
                        <div class="fw-bold"><?= htmlspecialchars($app['full_name']) ?></div>
                    </div>
                    <div class="col-sm-6">
                        <label class="text-muted small">User Account Email</label>
                        <div class="fw-bold"><?= htmlspecialchars($app['account_email']) ?></div>
                    </div>
                    <div class="col-sm-6">
                        <label class="text-muted small">Contact Phone</label>
                        <div class="fw-bold"><a href="tel:<?= htmlspecialchars($app['phone']) ?>"><?= htmlspecialchars($app['phone']) ?></a></div>
                    </div>
                    <div class="col-sm-6">
                        <label class="text-muted small">Contact Email</label>
                        <div class="fw-bold"><a href="mailto:<?= htmlspecialchars($app['email']) ?>"><?= htmlspecialchars($app['email']) ?></a></div>
                    </div>
                    <div class="col-12">
                        <label class="text-muted small">Residence Address</label>
                        <div class="fw-bold">
                            <?= htmlspecialchars($app['address']) ?>, <?= htmlspecialchars($app['city']) ?>, <?= htmlspecialchars($app['state'] ?? '') ?> <?= htmlspecialchars($app['zip_code'] ?? '') ?>
                        </div>
                    </div>
                </div>
            </div>

            <!-- 2. Housing Environment -->
            <div class="bg-white rounded-4 border shadow-sm p-4 mb-4">
                <h5 class="fw-bold border-bottom pb-2 mb-3 text-primary">
                    <i class="bi bi-house-door-fill me-2"></i> 2. Housing & Household Dynamic
                </h5>
                <div class="row g-3">
                    <div class="col-sm-4">
                        <label class="text-muted small">Housing Type</label>
                        <div class="fw-bold"><?= htmlspecialchars($app['housing_type']) ?></div>
                    </div>
                    <div class="col-sm-4">
                        <label class="text-muted small">Adults in Household</label>
                        <div class="fw-bold"><?= $app['household_adults'] ?> adult(s)</div>
                    </div>
                    <div class="col-sm-4">
                        <label class="text-muted small">Children Under 18</label>
                        <div class="fw-bold"><?= $app['household_children'] ?> child(ren)</div>
                    </div>
                    <div class="col-sm-4">
                        <label class="text-muted small">Private Yard</label>
                        <div>
                            <span class="badge <?= $app['has_yard'] ? 'bg-success' : 'bg-secondary' ?>">
                                <?= $app['has_yard'] ? 'Yes' : 'No' ?>
                            </span>
                        </div>
                    </div>
                    <div class="col-sm-4">
                        <label class="text-muted small">Fenced Yard</label>
                        <div>
                            <span class="badge <?= $app['yard_fenced'] ? 'bg-success' : 'bg-secondary' ?>">
                                <?= $app['yard_fenced'] ? 'Yes' : 'No' ?>
                            </span>
                        </div>
                    </div>
                    <div class="col-sm-4">
                        <label class="text-muted small">Landlord Permission</label>
                        <div>
                            <span class="badge <?= $app['landlord_permission'] ? 'bg-success' : 'bg-danger' ?>">
                                <?= $app['landlord_permission'] ? 'Confirmed' : 'Not Confirmed' ?>
                            </span>
                        </div>
                    </div>
                </div>
            </div>

            <!-- 3. Pet Experience & Care Routine -->
            <div class="bg-white rounded-4 border shadow-sm p-4 mb-4">
                <h5 class="fw-bold border-bottom pb-2 mb-3 text-primary">
                    <i class="bi bi-heart-pulse-fill me-2"></i> 3. Pet Experience & Daily Lifestyle
                </h5>
                <div class="row g-3">
                    <div class="col-sm-6">
                        <label class="text-muted small">Ownership Experience Level</label>
                        <div class="fw-bold"><?= htmlspecialchars($app['pet_experience']) ?></div>
                    </div>
                    <div class="col-sm-6">
                        <label class="text-muted small">Hours Pet Will Be Alone Daily</label>
                        <div class="fw-bold"><?= $app['hours_alone_daily'] ?> hour(s) per day</div>
                    </div>
                    <div class="col-12">
                        <label class="text-muted small">Currently Owned Pets</label>
                        <div class="p-3 bg-light rounded-3 border">
                            <?= !empty($app['current_pets']) ? nl2br(htmlspecialchars($app['current_pets'])) : '<span class="text-muted">None specified</span>' ?>
                        </div>
                    </div>
                    <div class="col-12">
                        <label class="text-muted small">Veterinary Reference / Clinic</label>
                        <div class="p-3 bg-light rounded-3 border">
                            <?= !empty($app['vet_name_contact']) ? nl2br(htmlspecialchars($app['vet_name_contact'])) : '<span class="text-muted">No vet reference provided</span>' ?>
                        </div>
                    </div>
                </div>
            </div>

            <!-- 4. Reason for Adoption Essay -->
            <div class="bg-white rounded-4 border shadow-sm p-4 mb-4">
                <h5 class="fw-bold border-bottom pb-2 mb-3 text-primary">
                    <i class="bi bi-chat-quote-fill me-2"></i> 4. Applicant Statement / Motivation
                </h5>
                <div class="p-3 bg-light rounded-3 border fst-italic">
                    <?= nl2br(htmlspecialchars($app['reason_for_adoption'])) ?>
                </div>
            </div>
        </div>

        <!-- Right Column: Pet Summary & Decision Action Panel -->
        <div class="col-lg-4">
            <!-- Pet Information Card -->
            <div class="bg-white rounded-4 border shadow-sm p-4 mb-4">
                <h5 class="fw-bold mb-3"><i class="bi bi-paw me-2 text-danger"></i> Pet Requested</h5>
                <div class="text-center mb-3">
                    <img src="<?= htmlspecialchars(get_pet_image($app['pet_image'])) ?>" alt="<?= htmlspecialchars($app['pet_name']) ?>" class="img-fluid rounded-4 object-fit-cover w-100" style="max-height: 180px;">
                </div>
                <h4 class="fw-bold mb-1"><?= htmlspecialchars($app['pet_name']) ?></h4>
                <p class="text-muted small mb-2"><?= htmlspecialchars($app['category_name']) ?> &bull; <?= htmlspecialchars($app['pet_breed']) ?></p>

                <ul class="list-group list-group-flush small mb-3">
                    <li class="list-group-item d-flex justify-content-between px-0">
                        <span class="text-muted">Pet Status:</span>
                        <?= status_badge($app['pet_status']) ?>
                    </li>
                    <li class="list-group-item d-flex justify-content-between px-0">
                        <span class="text-muted">Age & Gender:</span>
                        <span class="fw-bold"><?= get_pet_age($app['age_years'], $app['age_months']) ?> (<?= htmlspecialchars($app['pet_gender']) ?>)</span>
                    </li>
                    <li class="list-group-item d-flex justify-content-between px-0">
                        <span class="text-muted">Adoption Fee:</span>
                        <span class="fw-bold text-primary">$<?= number_format($app['pet_fee'], 0) ?></span>
                    </li>
                    <li class="list-group-item d-flex justify-content-between px-0">
                        <span class="text-muted">Shelter:</span>
                        <span class="fw-bold"><?= htmlspecialchars($app['shelter_name'] ?? 'Main Shelter') ?></span>
                    </li>
                </ul>

                <a href="/pet-details.php?id=<?= $app['pet_id'] ?>" target="_blank" class="btn btn-outline-secondary btn-sm w-100">
                    <i class="bi bi-box-arrow-up-right me-1"></i> View Pet Profile
                </a>
            </div>

            <!-- Decision & Communication Form Card -->
            <div class="bg-white rounded-4 border shadow-sm p-4">
                <h5 class="fw-bold mb-3"><i class="bi bi-pencil-square me-2 text-primary"></i> Reviewer Action</h5>

                <?php if ($app['reviewed_by']): ?>
                    <div class="small text-muted mb-3 p-2 bg-light rounded-3 border">
                        <i class="bi bi-clock-history me-1"></i> Last reviewed by <strong><?= htmlspecialchars($app['reviewer_name'] ?? 'Admin') ?></strong> on <?= format_date($app['reviewed_at'], 'M d, Y g:i A') ?>
                    </div>
                <?php endif; ?>

                <form action="/admin/application-view.php?id=<?= $app['id'] ?>" method="POST">
                    <input type="hidden" name="csrf_token" value="<?= csrf_token() ?>">
                    <input type="hidden" name="app_id" value="<?= $app['id'] ?>">

                    <div class="mb-3">
                        <label class="form-label fw-bold">Update Application Status</label>
                        <select name="status" class="form-select form-select-lg fw-bold" required>
                            <option value="Pending" <?= ($app['status'] === 'Pending') ? 'selected' : '' ?>>⏳ Pending</option>
                            <option value="Under Review" <?= ($app['status'] === 'Under Review') ? 'selected' : '' ?>>🔍 Under Review</option>
                            <option value="Approved" <?= ($app['status'] === 'Approved') ? 'selected' : '' ?>>✅ Approved</option>
                            <option value="Rejected" <?= ($app['status'] === 'Rejected') ? 'selected' : '' ?>>❌ Rejected</option>
                            <option value="Completed" <?= ($app['status'] === 'Completed') ? 'selected' : '' ?>>🎉 Completed (Homecoming)</option>
                        </select>
                    </div>

                    <div class="form-check mb-3 p-2 bg-light rounded-3 border">
                        <input class="form-check-input ms-1" type="checkbox" name="update_pet_status" value="1" id="syncPetStatus" checked>
                        <label class="form-check-label small fw-bold ms-2" for="syncPetStatus">
                            Automatically update pet status (e.g. mark pet as "Adopted" when application is approved)
                        </label>
                    </div>

                    <div class="mb-3">
                        <label class="form-label fw-bold">
                            Applicant Feedback / Instructions
                            <span class="badge bg-primary-subtle text-primary ms-1 small">Visible to Applicant</span>
                        </label>
                        <textarea name="applicant_feedback" rows="4" class="form-control" placeholder="Write remarks or next steps shown on the user's 'My Applications' tracker..."><?= htmlspecialchars($app['applicant_feedback'] ?? '') ?></textarea>
                    </div>

                    <div class="mb-4">
                        <label class="form-label fw-bold">
                            Internal Shelter Notes
                            <span class="badge bg-secondary-subtle text-secondary ms-1 small">Staff Eyes Only</span>
                        </label>
                        <textarea name="admin_notes" rows="3" class="form-control" placeholder="Private internal notes regarding reference checks, home inspections, phone interviews..."><?= htmlspecialchars($app['admin_notes'] ?? '') ?></textarea>
                    </div>

                    <button type="submit" class="btn btn-paw-primary w-100 py-3 fw-bold fs-5">
                        <i class="bi bi-check2-circle me-1"></i> Save Decision & Notify
                    </button>
                </form>
            </div>
        </div>
    </div>
</div>

<?php include __DIR__ . '/../includes/footer.php'; ?>
