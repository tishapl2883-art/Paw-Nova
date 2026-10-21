<?php
/**
 * PawConnect - User Favorites / Wishlist
 */
require_once __DIR__ . '/config/db.php';
require_once __DIR__ . '/includes/functions.php';
require_once __DIR__ . '/includes/auth.php';

$db = getDB();

// Handle AJAX POST toggle
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    if (!is_logged_in()) {
        http_response_code(401);
        echo json_encode(['success' => false, 'error' => 'Login required']);
        exit;
    }

    $userId = current_user()['id'];
    $petId = (int)($_POST['pet_id'] ?? 0);

    if ($petId <= 0) {
        http_response_code(400);
        echo json_encode(['success' => false, 'error' => 'Invalid pet ID']);
        exit;
    }

    // Check if currently favorited
    $stmt = $db->prepare("SELECT id FROM favorites WHERE user_id = ? AND pet_id = ?");
    $stmt->execute([$userId, $petId]);
    $fav = $stmt->fetch();

    if ($fav) {
        // Remove
        $del = $db->prepare("DELETE FROM favorites WHERE id = ?");
        $del->execute([$fav['id']]);
        $isFav = false;
    } else {
        // Add
        $ins = $db->prepare("INSERT INTO favorites (user_id, pet_id) VALUES (?, ?)");
        $ins->execute([$userId, $petId]);
        $isFav = true;
    }

    header('Content-Type: application/json');
    echo json_encode(['success' => true, 'is_favorite' => $isFav]);
    exit;
}

// GET Page View
$pageTitle = "My Saved Pets";
require_once __DIR__ . '/includes/header.php';
require_login();

$user = current_user();

// Fetch favorited pets
$stmt = $db->prepare("
    SELECT p.*, c.name as category_name, s.name as shelter_name, s.city as shelter_city, f.created_at as saved_at
    FROM favorites f
    JOIN pets p ON f.pet_id = p.id
    JOIN categories c ON p.category_id = c.id
    LEFT JOIN shelters s ON p.shelter_id = s.id
    WHERE f.user_id = ?
    ORDER BY f.id DESC
");
$stmt->execute([$user['id']]);
$favorites = $stmt->fetchAll();
?>

<div class="container py-4">
    <!-- Breadcrumbs -->
    <nav aria-label="breadcrumb" class="mb-4">
        <ol class="breadcrumb">
            <li class="breadcrumb-item"><a href="/index.php">Home</a></li>
            <li class="breadcrumb-item active" aria-current="page">My Favorites</li>
        </ol>
    </nav>

    <div class="d-flex flex-wrap justify-content-between align-items-center mb-4">
        <div>
            <span class="badge bg-light text-danger px-3 py-2 rounded-pill fw-bold"><i class="bi bi-heart-fill me-1"></i> Wishlist</span>
            <h2 class="mt-2 mb-1">Your Favorite Friends</h2>
            <p class="text-muted">Keep track of the pets you've fallen in love with and apply when you're ready.</p>
        </div>
        <a href="/pets.php" class="btn btn-paw-outline">
            <i class="bi bi-search me-1"></i> Browse More
        </a>
    </div>

    <?php if (empty($favorites)): ?>
        <div class="bg-white rounded-4 border p-5 text-center shadow-sm">
            <div class="display-3 text-danger mb-3"><i class="bi bi-heart"></i></div>
            <h4>No pets saved yet</h4>
            <p class="text-muted">Click the heart icon on any pet card while browsing to save them here for quick access.</p>
            <a href="/pets.php" class="btn btn-paw-primary mt-2">
                <i class="bi bi-paw me-1"></i> Find Pets to Save
            </a>
        </div>
    <?php else: ?>
        <div class="row g-4">
            <?php foreach ($favorites as $pet): ?>
                <div class="col-lg-4 col-md-6" id="fav-col-<?= $pet['id'] ?>">
                    <div class="pet-card">
                        <div class="pet-card-image-wrap">
                            <img src="<?= htmlspecialchars(get_pet_image($pet['image_url'])) ?>" alt="<?= htmlspecialchars($pet['name']) ?>">
                            <div class="pet-card-badges">
                                <?= status_badge($pet['status']) ?>
                                <span class="badge bg-dark bg-opacity-75"><?= htmlspecialchars($pet['category_name']) ?></span>
                            </div>
                            <button class="pet-fav-btn active" data-pet-id="<?= $pet['id'] ?>" title="Remove from Favorites">
                                <i class="bi bi-heart-fill"></i>
                            </button>
                        </div>
                        <div class="pet-card-body">
                            <div class="d-flex justify-content-between align-items-start">
                                <h5 class="pet-card-title"><?= htmlspecialchars($pet['name']) ?></h5>
                                <span class="pet-price">$<?= number_format($pet['adoption_fee'], 0) ?></span>
                            </div>
                            <div class="pet-card-meta">
                                <i class="bi bi-tag"></i> <?= htmlspecialchars($pet['breed']) ?> &bull; 
                                <i class="bi bi-calendar3"></i> <?= get_pet_age($pet['age_years'], $pet['age_months']) ?>
                            </div>
                            <p class="text-muted small mb-3 flex-grow-1" style="display: -webkit-box; -webkit-line-clamp: 2; -webkit-box-orient: vertical; overflow: hidden;">
                                <?= htmlspecialchars($pet['description']) ?>
                            </p>
                            <div class="pet-tags">
                                <span class="pet-tag"><i class="bi bi-gender-ambiguous me-1"></i><?= htmlspecialchars($pet['gender']) ?></span>
                                <span class="pet-tag"><i class="bi bi-rulers me-1"></i><?= htmlspecialchars($pet['size']) ?></span>
                            </div>
                        </div>
                        <div class="pet-card-footer">
                            <span class="small text-muted"><i class="bi bi-geo-alt me-1"></i><?= htmlspecialchars($pet['shelter_city'] ?? 'Shelter') ?></span>
                            <div class="d-flex gap-2">
                                <a href="/pet-details.php?id=<?= $pet['id'] ?>" class="btn btn-sm btn-paw-outline">
                                    View
                                </a>
                                <?php if ($pet['status'] === 'Available'): ?>
                                    <a href="/adopt.php?pet_id=<?= $pet['id'] ?>" class="btn btn-sm btn-paw-primary">
                                        Adopt
                                    </a>
                                <?php endif; ?>
                            </div>
                        </div>
                    </div>
                </div>
            <?php endforeach; ?>
        </div>
    <?php endif; ?>
</div>

<?php include __DIR__ . '/includes/footer.php'; ?>
