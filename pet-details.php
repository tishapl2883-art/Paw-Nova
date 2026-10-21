<?php
/**
 * PawConnect - Pet Details Profile Page
 */
require_once __DIR__ . '/includes/header.php';

$db = getDB();
$petId = isset($_GET['id']) ? (int)$_GET['id'] : 0;

if ($petId <= 0) {
    set_flash('danger', 'Invalid pet selected.');
    header('Location: /pets.php');
    exit;
}

// Fetch pet with category and shelter
$stmt = $db->prepare("
    SELECT p.*, c.name as category_name, c.slug as category_slug, 
           s.name as shelter_name, s.email as shelter_email, s.phone as shelter_phone, 
           s.address as shelter_address, s.city as shelter_city, s.website as shelter_website
    FROM pets p
    JOIN categories c ON p.category_id = c.id
    LEFT JOIN shelters s ON p.shelter_id = s.id
    WHERE p.id = ?
");
$stmt->execute([$petId]);
$pet = $stmt->fetch();

if (!$pet) {
    set_flash('danger', 'The requested pet profile was not found.');
    header('Location: /pets.php');
    exit;
}

$pageTitle = $pet['name'] . " - Adopt " . $pet['breed'];
$user = current_user();

// Check if user favorited
$isFav = $user ? is_favorite($db, $user['id'], $pet['id']) : false;

// Check if current user has an active application for this pet
$userApp = null;
if ($user && !is_admin()) {
    $stmtApp = $db->prepare("SELECT * FROM adoption_applications WHERE user_id = ? AND pet_id = ? ORDER BY id DESC LIMIT 1");
    $stmtApp->execute([$user['id'], $pet['id']]);
    $userApp = $stmtApp->fetch();
}

// Fetch similar pets
$similarStmt = $db->prepare("
    SELECT p.*, c.name as category_name 
    FROM pets p 
    JOIN categories c ON p.category_id = c.id
    WHERE p.category_id = ? AND p.id != ? AND p.status = 'Available'
    ORDER BY RAND() 
    LIMIT 3
");
$similarStmt->execute([$pet['category_id'], $pet['id']]);
$similarPets = $similarStmt->fetchAll();
?>

<div class="container py-4">
    <!-- Breadcrumbs -->
    <nav aria-label="breadcrumb" class="mb-4">
        <ol class="breadcrumb">
            <li class="breadcrumb-item"><a href="/index.php">Home</a></li>
            <li class="breadcrumb-item"><a href="/pets.php">Browse Pets</a></li>
            <li class="breadcrumb-item"><a href="/pets.php?category=<?= urlencode($pet['category_slug']) ?>"><?= htmlspecialchars($pet['category_name']) ?></a></li>
            <li class="breadcrumb-item active" aria-current="page"><?= htmlspecialchars($pet['name']) ?></li>
        </ol>
    </nav>

    <div class="row g-5">
        <!-- Left Column: Pet Photo & Gallery -->
        <div class="col-lg-6">
            <div class="pet-gallery-main position-relative">
                <img src="<?= htmlspecialchars(get_pet_image($pet['image_url'])) ?>" alt="<?= htmlspecialchars($pet['name']) ?>" class="img-fluid w-100">
                <div class="position-absolute top-0 start-0 m-3 d-flex gap-2">
                    <?= status_badge($pet['status']) ?>
                    <span class="badge bg-dark bg-opacity-75"><?= htmlspecialchars($pet['category_name']) ?></span>
                </div>
            </div>

            <!-- Health & Behavior Highlights -->
            <div class="bg-white rounded-4 border p-4 shadow-sm mt-4">
                <h5 class="fw-bold mb-3"><i class="bi bi-shield-check text-success me-2"></i> Health & Medical Records</h5>
                <div class="d-flex flex-wrap gap-2 mb-4">
                    <span class="health-badge <?= $pet['vaccinated'] ? 'verified' : '' ?>">
                        <i class="bi <?= $pet['vaccinated'] ? 'bi-check-circle-fill' : 'bi-x-circle' ?>"></i>
                        <?= $pet['vaccinated'] ? 'Vaccinations Up-To-Date' : 'Not Vaccinated' ?>
                    </span>
                    <span class="health-badge <?= $pet['spayed_neutered'] ? 'verified' : '' ?>">
                        <i class="bi <?= $pet['spayed_neutered'] ? 'bi-check-circle-fill' : 'bi-x-circle' ?>"></i>
                        <?= $pet['spayed_neutered'] ? 'Spayed / Neutered' : 'Not Spayed/Neutered' ?>
                    </span>
                    <span class="health-badge <?= $pet['microchipped'] ? 'verified' : '' ?>">
                        <i class="bi <?= $pet['microchipped'] ? 'bi-check-circle-fill' : 'bi-x-circle' ?>"></i>
                        <?= $pet['microchipped'] ? 'Microchipped' : 'No Microchip' ?>
                    </span>
                    <span class="health-badge <?= $pet['house_trained'] ? 'verified' : '' ?>">
                        <i class="bi <?= $pet['house_trained'] ? 'bi-check-circle-fill' : 'bi-x-circle' ?>"></i>
                        <?= $pet['house_trained'] ? 'House Trained' : 'Needs House Training' ?>
                    </span>
                </div>

                <h5 class="fw-bold mb-3"><i class="bi bi-heart-pulse text-danger me-2"></i> Compatibility & Temperament</h5>
                <div class="row g-2">
                    <div class="col-sm-4">
                        <div class="p-2 border rounded-3 text-center <?= $pet['good_with_kids'] ? 'bg-success-subtle text-success' : 'bg-light text-muted' ?>">
                            <i class="bi bi-people-fill d-block fs-5"></i>
                            <span class="small fw-bold"><?= $pet['good_with_kids'] ? 'Good with Kids' : 'Adults Preferred' ?></span>
                        </div>
                    </div>
                    <div class="col-sm-4">
                        <div class="p-2 border rounded-3 text-center <?= $pet['good_with_dogs'] ? 'bg-success-subtle text-success' : 'bg-light text-muted' ?>">
                            <i class="bi bi-badge-ad-fill d-block fs-5"></i>
                            <span class="small fw-bold"><?= $pet['good_with_dogs'] ? 'Good with Dogs' : 'Prefers Single Pet' ?></span>
                        </div>
                    </div>
                    <div class="col-sm-4">
                        <div class="p-2 border rounded-3 text-center <?= $pet['good_with_cats'] ? 'bg-success-subtle text-success' : 'bg-light text-muted' ?>">
                            <i class="bi bi-star-fill d-block fs-5"></i>
                            <span class="small fw-bold"><?= $pet['good_with_cats'] ? 'Good with Cats' : 'No Cats' ?></span>
                        </div>
                    </div>
                </div>

                <?php if (!empty($pet['special_needs']) && $pet['special_needs'] !== 'None'): ?>
                    <div class="alert alert-info mt-3 mb-0 small">
                        <strong><i class="bi bi-info-circle me-1"></i> Special Care Notes:</strong> <?= htmlspecialchars($pet['special_needs']) ?>
                    </div>
                <?php endif; ?>
            </div>
        </div>

        <!-- Right Column: Profile Info & Adoption Action -->
        <div class="col-lg-6">
            <div class="d-flex justify-content-between align-items-start mb-2">
                <div>
                    <h1 class="display-5 fw-bold mb-1"><?= htmlspecialchars($pet['name']) ?></h1>
                    <p class="text-muted fs-5 mb-0">
                        <i class="bi bi-tag me-1"></i> <?= htmlspecialchars($pet['breed']) ?>
                    </p>
                </div>
                <div class="text-end">
                    <div class="fs-2 fw-bold text-primary">$<?= number_format($pet['adoption_fee'], 0) ?></div>
                    <span class="small text-muted">Adoption Fee</span>
                </div>
            </div>

            <!-- Vital Stats Matrix -->
            <div class="row g-2 my-4">
                <div class="col-6 col-sm-3">
                    <div class="detail-stat-card">
                        <div class="detail-stat-label">Age</div>
                        <div class="detail-stat-value"><?= get_pet_age($pet['age_years'], $pet['age_months']) ?></div>
                    </div>
                </div>
                <div class="col-6 col-sm-3">
                    <div class="detail-stat-card">
                        <div class="detail-stat-label">Gender</div>
                        <div class="detail-stat-value"><?= htmlspecialchars($pet['gender']) ?></div>
                    </div>
                </div>
                <div class="col-6 col-sm-3">
                    <div class="detail-stat-card">
                        <div class="detail-stat-label">Size</div>
                        <div class="detail-stat-value"><?= htmlspecialchars($pet['size']) ?></div>
                    </div>
                </div>
                <div class="col-6 col-sm-3">
                    <div class="detail-stat-card">
                        <div class="detail-stat-label">Color</div>
                        <div class="detail-stat-value"><?= htmlspecialchars($pet['color'] ?? 'Standard') ?></div>
                    </div>
                </div>
            </div>

            <!-- Pet Story / Biography -->
            <div class="mb-4">
                <h4 class="fw-bold mb-3">Meet <?= htmlspecialchars($pet['name']) ?></h4>
                <div class="text-secondary lh-lg">
                    <?= nl2br(htmlspecialchars($pet['description'])) ?>
                </div>
            </div>

            <!-- Adoption Action CTA Box -->
            <div class="bg-light p-4 rounded-4 border mb-4">
                <?php if ($pet['status'] === 'Available'): ?>
                    <?php if ($userApp): ?>
                        <div class="alert alert-warning mb-3">
                            <i class="bi bi-hourglass-split me-2"></i> You already have an active application for <strong><?= htmlspecialchars($pet['name']) ?></strong> (Status: <strong><?= htmlspecialchars($userApp['status']) ?></strong>).
                        </div>
                        <a href="/my-applications.php" class="btn btn-dark w-100 py-3 fw-bold">
                            <i class="bi bi-file-earmark-check me-2"></i> View Your Application Status
                        </a>
                    <?php elseif (is_logged_in() && is_admin()): ?>
                        <div class="alert alert-info mb-3">
                            <i class="bi bi-info-circle me-1"></i> You are logged in as an <strong>Administrator</strong>.
                        </div>
                        <a href="/admin/pet-edit.php?id=<?= $pet['id'] ?>" class="btn btn-warning w-100 py-3 fw-bold">
                            <i class="bi bi-pencil-square me-2"></i> Edit This Pet Listing
                        </a>
                    <?php elseif (is_logged_in()): ?>
                        <a href="/adopt.php?pet_id=<?= $pet['id'] ?>" class="btn btn-paw-primary w-100 py-3 fw-bold fs-5 shadow">
                            <i class="bi bi-heart-fill me-2"></i> Apply To Adopt <?= htmlspecialchars($pet['name']) ?>
                        </a>
                    <?php else: ?>
                        <a href="/login.php?redirect=<?= urlencode('/adopt.php?pet_id=' . $pet['id']) ?>" class="btn btn-paw-primary w-100 py-3 fw-bold fs-5 shadow">
                            <i class="bi bi-box-arrow-in-right me-2"></i> Log In To Apply For Adoption
                        </a>
                    <?php endif; ?>
                <?php elseif ($pet['status'] === 'Pending'): ?>
                    <div class="alert alert-warning mb-0 text-center">
                        <i class="bi bi-hourglass-split fs-4 d-block mb-1"></i>
                        <strong>Adoption Pending</strong><br>
                        <?= htmlspecialchars($pet['name']) ?> is currently undergoing an adoption review with a prospective family.
                    </div>
                <?php else: ?>
                    <div class="alert alert-success mb-0 text-center">
                        <i class="bi bi-check-circle fs-4 d-block mb-1"></i>
                        <strong>Happily Adopted!</strong><br>
                        <?= htmlspecialchars($pet['name']) ?> has found their forever family!
                    </div>
                <?php endif; ?>

                <!-- Secondary Actions -->
                <div class="d-flex gap-2 mt-3">
                    <?php if (is_logged_in() && !is_admin()): ?>
                        <button class="btn btn-outline-secondary w-50 pet-fav-btn-single <?= $isFav ? 'text-danger' : '' ?>" id="btnToggleFav">
                            <i class="bi <?= $isFav ? 'bi-heart-fill' : 'bi-heart' ?> me-1"></i>
                            <span id="favText"><?= $isFav ? 'Favorited' : 'Add to Favorites' ?></span>
                        </button>
                    <?php endif; ?>
                    <a href="/contact.php?subject=Inquiry about <?= urlencode($pet['name']) ?> (ID #<?= $pet['id'] ?>)" class="btn btn-outline-secondary <?= (is_logged_in() && !is_admin()) ? 'w-50' : 'w-100' ?>">
                        <i class="bi bi-chat-dots me-1"></i> Ask a Question
                    </a>
                </div>
            </div>

            <!-- Shelter Info Box -->
            <div class="bg-white p-4 rounded-4 border shadow-sm">
                <h5 class="fw-bold mb-3"><i class="bi bi-building me-2 text-primary"></i> Caring Shelter</h5>
                <h6 class="fw-bold mb-1"><?= htmlspecialchars($pet['shelter_name'] ?? 'PawConnect Rescue Center') ?></h6>
                <p class="text-muted small mb-2"><i class="bi bi-geo-alt me-1"></i> <?= htmlspecialchars($pet['shelter_address'] ?? 'Main Shelter') ?>, <?= htmlspecialchars($pet['shelter_city'] ?? '') ?></p>
                <?php if (!empty($pet['shelter_phone'])): ?>
                    <p class="text-muted small mb-2"><i class="bi bi-telephone me-1"></i> <?= htmlspecialchars($pet['shelter_phone']) ?></p>
                <?php endif; ?>
                <?php if (!empty($pet['shelter_email'])): ?>
                    <p class="text-muted small mb-0"><i class="bi bi-envelope me-1"></i> <?= htmlspecialchars($pet['shelter_email']) ?></p>
                <?php endif; ?>
            </div>
        </div>
    </div>

    <!-- Similar Pets Recommendations -->
    <?php if (!empty($similarPets)): ?>
        <div class="mt-5 pt-4 border-top">
            <h3 class="fw-bold mb-4">Other <?= htmlspecialchars($pet['category_name']) ?> Looking For Homes</h3>
            <div class="row g-4">
                <?php foreach ($similarPets as $sim): ?>
                    <div class="col-md-4">
                        <div class="pet-card">
                            <div class="pet-card-image-wrap">
                                <img src="<?= htmlspecialchars(get_pet_image($sim['image_url'])) ?>" alt="<?= htmlspecialchars($sim['name']) ?>">
                                <div class="pet-card-badges">
                                    <?= status_badge($sim['status']) ?>
                                </div>
                            </div>
                            <div class="pet-card-body">
                                <h5 class="pet-card-title"><?= htmlspecialchars($sim['name']) ?></h5>
                                <div class="pet-card-meta">
                                    <?= htmlspecialchars($sim['breed']) ?> &bull; <?= get_pet_age($sim['age_years'], $sim['age_months']) ?>
                                </div>
                                <div class="d-flex justify-content-between align-items-center mt-auto pt-2 border-top">
                                    <span class="fw-bold text-primary">$<?= number_format($sim['adoption_fee'], 0) ?></span>
                                    <a href="/pet-details.php?id=<?= $sim['id'] ?>" class="btn btn-sm btn-paw-outline">Meet Me</a>
                                </div>
                            </div>
                        </div>
                    </div>
                <?php endforeach; ?>
            </div>
        </div>
    <?php endif; ?>
</div>

<script>
document.addEventListener('DOMContentLoaded', () => {
    const singleFavBtn = document.getElementById('btnToggleFav');
    if (singleFavBtn) {
        singleFavBtn.addEventListener('click', async () => {
            try {
                const response = await fetch('/favorites.php', {
                    method: 'POST',
                    headers: {
                        'Content-Type': 'application/x-www-form-urlencoded',
                        'X-Requested-With': 'XMLHttpRequest'
                    },
                    body: `action=toggle&pet_id=<?= $pet['id'] ?>`
                });
                const data = await response.json();
                if (data.success) {
                    const icon = singleFavBtn.querySelector('i');
                    const text = document.getElementById('favText');
                    if (data.is_favorite) {
                        singleFavBtn.classList.add('text-danger');
                        icon.className = 'bi bi-heart-fill me-1';
                        text.textContent = 'Favorited';
                    } else {
                        singleFavBtn.classList.remove('text-danger');
                        icon.className = 'bi bi-heart me-1';
                        text.textContent = 'Add to Favorites';
                    }
                }
            } catch (e) {
                console.error(e);
            }
        });
    }
});
</script>

<?php include __DIR__ . '/includes/footer.php'; ?>
