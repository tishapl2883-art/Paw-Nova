<?php
/**
 * PawConnect - Admin Edit Pet Profile
 */
$pageTitle = "Edit Pet Profile";
require_once __DIR__ . '/../includes/header.php';

require_admin();
$db = getDB();

$petId = (int)($_GET['id'] ?? $_POST['pet_id'] ?? 0);
if ($petId <= 0) {
    set_flash('danger', 'Invalid pet ID.');
    header("Location: /admin/pets.php");
    exit;
}

$stmt = $db->prepare("SELECT * FROM pets WHERE id = ?");
$stmt->execute([$petId]);
$pet = $stmt->fetch();

if (!$pet) {
    set_flash('danger', 'Pet profile not found.');
    header("Location: /admin/pets.php");
    exit;
}

$categories = $db->query("SELECT * FROM categories ORDER BY name ASC")->fetchAll();
$shelters = $db->query("SELECT * FROM shelters ORDER BY name ASC")->fetchAll();

$errors = [];

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    if (!verify_csrf($_POST['csrf_token'] ?? '')) {
        $errors[] = 'Invalid security token.';
    }

    $name = sanitize($_POST['name'] ?? '');
    $categoryId = (int)($_POST['category_id'] ?? 0);
    $shelterId = !empty($_POST['shelter_id']) ? (int)$_POST['shelter_id'] : null;
    $breed = sanitize($_POST['breed'] ?? '');
    $ageYears = (int)($_POST['age_years'] ?? 0);
    $ageMonths = (int)($_POST['age_months'] ?? 0);
    $gender = sanitize($_POST['gender'] ?? 'Male');
    $size = sanitize($_POST['size'] ?? 'Medium');
    $color = sanitize($_POST['color'] ?? '');
    $fee = (float)($_POST['adoption_fee'] ?? 0);
    $status = sanitize($_POST['status'] ?? 'Available');
    $location = sanitize($_POST['location'] ?? 'Main Shelter');
    $description = sanitize($_POST['description'] ?? '');
    $specialNeeds = sanitize($_POST['special_needs'] ?? '');

    $vaccinated = isset($_POST['vaccinated']) ? 1 : 0;
    $spayedNeutered = isset($_POST['spayed_neutered']) ? 1 : 0;
    $microchipped = isset($_POST['microchipped']) ? 1 : 0;
    $houseTrained = isset($_POST['house_trained']) ? 1 : 0;
    $goodWithKids = isset($_POST['good_with_kids']) ? 1 : 0;
    $goodWithDogs = isset($_POST['good_with_dogs']) ? 1 : 0;
    $goodWithCats = isset($_POST['good_with_cats']) ? 1 : 0;
    $featured = isset($_POST['featured']) ? 1 : 0;

    $imageUrl = trim($_POST['image_url'] ?? $pet['image_url']);

    // Handle File Upload if new file provided
    if (isset($_FILES['image_file']) && $_FILES['image_file']['error'] === UPLOAD_ERR_OK) {
        $fileTmp = $_FILES['image_file']['tmp_name'];
        $fileName = $_FILES['image_file']['name'];
        $fileExt = strtolower(pathinfo($fileName, PATHINFO_EXTENSION));
        $allowedExts = ['jpg', 'jpeg', 'png', 'webp', 'gif'];

        if (!in_array($fileExt, $allowedExts)) {
            $errors[] = 'Invalid image format. Allowed: JPG, PNG, WEBP, GIF.';
        } else {
            $newFileName = 'pet_' . uniqid() . '.' . $fileExt;
            $destination = __DIR__ . '/../uploads/pets/' . $newFileName;
            if (move_uploaded_file($fileTmp, $destination)) {
                $imageUrl = 'uploads/pets/' . $newFileName;
            } else {
                $errors[] = 'Failed to save uploaded image.';
            }
        }
    }

    if (empty($name)) $errors[] = 'Pet name is required.';
    if ($categoryId <= 0) $errors[] = 'Please select a species category.';
    if (empty($breed)) $errors[] = 'Breed is required.';
    if (empty($description)) $errors[] = 'Description is required.';

    if (empty($errors)) {
        try {
            $upStmt = $db->prepare("
                UPDATE pets SET
                    name = ?, category_id = ?, shelter_id = ?, breed = ?, age_years = ?, age_months = ?, 
                    gender = ?, size = ?, color = ?, status = ?, description = ?, image_url = ?, 
                    adoption_fee = ?, vaccinated = ?, spayed_neutered = ?, microchipped = ?, 
                    house_trained = ?, good_with_kids = ?, good_with_dogs = ?, good_with_cats = ?, 
                    special_needs = ?, location = ?, featured = ?
                WHERE id = ?
            ");
            $upStmt->execute([
                $name, $categoryId, $shelterId, $breed, $ageYears, $ageMonths,
                $gender, $size, $color, $status, $description, $imageUrl,
                $fee, $vaccinated, $spayedNeutered, $microchipped,
                $houseTrained, $goodWithKids, $goodWithDogs, $goodWithCats,
                $specialNeeds, $location, $featured, $petId
            ]);

            set_flash('success', "Profile for '{$name}' updated successfully!");
            header("Location: /admin/pets.php");
            exit;

        } catch (Exception $e) {
            $errors[] = 'Error updating pet: ' . $e->getMessage();
        }
    }
}
?>

<div class="container py-4">
    <div class="d-flex flex-wrap justify-content-between align-items-center mb-4 pb-2 border-bottom">
        <div>
            <span class="badge bg-danger-subtle text-danger px-3 py-1 rounded-pill fw-bold">Admin Portal</span>
            <h2 class="mt-2 mb-0 fw-bold">Edit Profile: <?= htmlspecialchars($pet['name']) ?></h2>
            <p class="text-muted small">Update animal data, adoption status, and images.</p>
        </div>
        <div class="d-flex gap-2">
            <a href="/pet-details.php?id=<?= $pet['id'] ?>" target="_blank" class="btn btn-outline-secondary">
                <i class="bi bi-eye me-1"></i> View Live
            </a>
            <a href="/admin/pets.php" class="btn btn-outline-secondary">
                <i class="bi bi-arrow-left me-1"></i> Back to Inventory
            </a>
        </div>
    </div>

    <?php if (!empty($errors)): ?>
        <div class="alert alert-danger shadow-sm mb-4">
            <ul class="mb-0 ps-3">
                <?php foreach ($errors as $err): ?>
                    <li><?= htmlspecialchars($err) ?></li>
                <?php endforeach; ?>
            </ul>
        </div>
    <?php endif; ?>

    <form action="/admin/pet-edit.php?id=<?= $pet['id'] ?>" method="POST" enctype="multipart/form-data">
        <input type="hidden" name="csrf_token" value="<?= csrf_token() ?>">
        <input type="hidden" name="pet_id" value="<?= $pet['id'] ?>">

        <div class="row g-4">
            <!-- Left Column: Primary Details -->
            <div class="col-lg-8">
                <div class="form-card mb-4">
                    <h5 class="fw-bold mb-3"><i class="bi bi-info-circle text-primary me-2"></i> Basic Information</h5>
                    
                    <div class="row g-3">
                        <div class="col-md-6">
                            <label class="form-label">Pet Name <span class="text-danger">*</span></label>
                            <input type="text" name="name" value="<?= htmlspecialchars($_POST['name'] ?? $pet['name']) ?>" class="form-control" required>
                        </div>
                        <div class="col-md-6">
                            <label class="form-label">Species / Category <span class="text-danger">*</span></label>
                            <select name="category_id" class="form-select" required>
                                <?php foreach ($categories as $cat): ?>
                                    <option value="<?= $cat['id'] ?>" <?= (($_POST['category_id'] ?? $pet['category_id']) == $cat['id']) ? 'selected' : '' ?>>
                                        <?= htmlspecialchars($cat['name']) ?>
                                    </option>
                                <?php endforeach; ?>
                            </select>
                        </div>

                        <div class="col-md-6">
                            <label class="form-label">Breed <span class="text-danger">*</span></label>
                            <input type="text" name="breed" value="<?= htmlspecialchars($_POST['breed'] ?? $pet['breed']) ?>" class="form-control" required>
                        </div>
                        <div class="col-md-3 col-6">
                            <label class="form-label">Age (Years)</label>
                            <input type="number" name="age_years" min="0" max="30" value="<?= htmlspecialchars($_POST['age_years'] ?? $pet['age_years']) ?>" class="form-control">
                        </div>
                        <div class="col-md-3 col-6">
                            <label class="form-label">Age (Months)</label>
                            <input type="number" name="age_months" min="0" max="11" value="<?= htmlspecialchars($_POST['age_months'] ?? $pet['age_months']) ?>" class="form-control">
                        </div>

                        <div class="col-md-4">
                            <label class="form-label">Gender <span class="text-danger">*</span></label>
                            <select name="gender" class="form-select" required>
                                <option value="Male" <?= (($_POST['gender'] ?? $pet['gender']) === 'Male') ? 'selected' : '' ?>>Male</option>
                                <option value="Female" <?= (($_POST['gender'] ?? $pet['gender']) === 'Female') ? 'selected' : '' ?>>Female</option>
                            </select>
                        </div>
                        <div class="col-md-4">
                            <label class="form-label">Size <span class="text-danger">*</span></label>
                            <select name="size" class="form-select" required>
                                <option value="Small" <?= (($_POST['size'] ?? $pet['size']) === 'Small') ? 'selected' : '' ?>>Small</option>
                                <option value="Medium" <?= (($_POST['size'] ?? $pet['size']) === 'Medium') ? 'selected' : '' ?>>Medium</option>
                                <option value="Large" <?= (($_POST['size'] ?? $pet['size']) === 'Large') ? 'selected' : '' ?>>Large</option>
                                <option value="Extra Large" <?= (($_POST['size'] ?? $pet['size']) === 'Extra Large') ? 'selected' : '' ?>>Extra Large</option>
                            </select>
                        </div>
                        <div class="col-md-4">
                            <label class="form-label">Color / Markings</label>
                            <input type="text" name="color" value="<?= htmlspecialchars($_POST['color'] ?? $pet['color']) ?>" class="form-control">
                        </div>

                        <div class="col-12">
                            <label class="form-label">Biography & Story <span class="text-danger">*</span></label>
                            <textarea name="description" rows="4" class="form-control" required><?= htmlspecialchars($_POST['description'] ?? $pet['description']) ?></textarea>
                        </div>
                    </div>
                </div>

                <!-- Health & Behavioral Toggles -->
                <div class="form-card">
                    <h5 class="fw-bold mb-3"><i class="bi bi-shield-check text-success me-2"></i> Health & Behavioral Attributes</h5>
                    
                    <div class="row g-3 mb-3">
                        <div class="col-sm-6">
                            <div class="form-check form-switch p-2 ps-5 border rounded-3 bg-light">
                                <input class="form-check-input" type="checkbox" name="vaccinated" value="1" id="vaxCheck" <?= (isset($_POST['vaccinated']) ? true : $pet['vaccinated']) ? 'checked' : '' ?>>
                                <label class="form-check-label fw-semibold" for="vaxCheck">Vaccinations Up to Date</label>
                            </div>
                        </div>
                        <div class="col-sm-6">
                            <div class="form-check form-switch p-2 ps-5 border rounded-3 bg-light">
                                <input class="form-check-input" type="checkbox" name="spayed_neutered" value="1" id="spayCheck" <?= (isset($_POST['spayed_neutered']) ? true : $pet['spayed_neutered']) ? 'checked' : '' ?>>
                                <label class="form-check-label fw-semibold" for="spayCheck">Spayed / Neutered</label>
                            </div>
                        </div>
                        <div class="col-sm-6">
                            <div class="form-check form-switch p-2 ps-5 border rounded-3 bg-light">
                                <input class="form-check-input" type="checkbox" name="microchipped" value="1" id="chipCheck" <?= (isset($_POST['microchipped']) ? true : $pet['microchipped']) ? 'checked' : '' ?>>
                                <label class="form-check-label fw-semibold" for="chipCheck">Microchipped</label>
                            </div>
                        </div>
                        <div class="col-sm-6">
                            <div class="form-check form-switch p-2 ps-5 border rounded-3 bg-light">
                                <input class="form-check-input" type="checkbox" name="house_trained" value="1" id="trainCheck" <?= (isset($_POST['house_trained']) ? true : $pet['house_trained']) ? 'checked' : '' ?>>
                                <label class="form-check-label fw-semibold" for="trainCheck">House / Litter Trained</label>
                            </div>
                        </div>
                    </div>

                    <h6 class="fw-bold text-muted small text-uppercase mt-4 mb-2">Compatibility with:</h6>
                    <div class="d-flex flex-wrap gap-3 mb-3">
                        <div class="form-check">
                            <input class="form-check-input" type="checkbox" name="good_with_kids" value="1" id="kidsCheck" <?= (isset($_POST['good_with_kids']) ? true : $pet['good_with_kids']) ? 'checked' : '' ?>>
                            <label class="form-check-label" for="kidsCheck">Children</label>
                        </div>
                        <div class="form-check">
                            <input class="form-check-input" type="checkbox" name="good_with_dogs" value="1" id="dogsCheck" <?= (isset($_POST['good_with_dogs']) ? true : $pet['good_with_dogs']) ? 'checked' : '' ?>>
                            <label class="form-check-label" for="dogsCheck">Dogs</label>
                        </div>
                        <div class="form-check">
                            <input class="form-check-input" type="checkbox" name="good_with_cats" value="1" id="catsCheck" <?= (isset($_POST['good_with_cats']) ? true : $pet['good_with_cats']) ? 'checked' : '' ?>>
                            <label class="form-check-label" for="catsCheck">Cats</label>
                        </div>
                    </div>

                    <div class="mt-3">
                        <label class="form-label">Special Needs / Medication (Optional)</label>
                        <input type="text" name="special_needs" value="<?= htmlspecialchars($_POST['special_needs'] ?? $pet['special_needs']) ?>" class="form-control">
                    </div>
                </div>
            </div>

            <!-- Right Column: Media, Shelter & Status -->
            <div class="col-lg-4">
                <!-- Image Card -->
                <div class="form-card mb-4 text-center">
                    <h5 class="fw-bold text-start mb-3"><i class="bi bi-image text-primary me-2"></i> Photo</h5>
                    <img id="image_preview" src="<?= htmlspecialchars(get_pet_image($pet['image_url'])) ?>" alt="Preview" class="img-fluid rounded-4 mb-3 object-fit-cover w-100" style="max-height: 220px;">
                    
                    <div class="mb-3 text-start">
                        <label class="form-label small fw-bold">Image URL</label>
                        <input type="text" name="image_url" id="image_url" value="<?= htmlspecialchars($_POST['image_url'] ?? $pet['image_url']) ?>" class="form-control form-control-sm">
                    </div>

                    <div class="text-start">
                        <label class="form-label small fw-bold">Replace with File Upload</label>
                        <input type="file" name="image_file" id="image_file" accept="image/*" class="form-control form-control-sm">
                    </div>
                </div>

                <!-- Shelter & Operational Details -->
                <div class="form-card mb-4">
                    <h5 class="fw-bold mb-3"><i class="bi bi-building text-primary me-2"></i> Shelter & Status</h5>

                    <div class="mb-3">
                        <label class="form-label">Assigned Shelter</label>
                        <select name="shelter_id" class="form-select">
                            <option value="">Main PawConnect Rescue</option>
                            <?php foreach ($shelters as $s): ?>
                                <option value="<?= $s['id'] ?>" <?= (($_POST['shelter_id'] ?? $pet['shelter_id']) == $s['id']) ? 'selected' : '' ?>>
                                    <?= htmlspecialchars($s['name']) ?> (<?= htmlspecialchars($s['city']) ?>)
                                </option>
                            <?php endforeach; ?>
                        </select>
                    </div>

                    <div class="mb-3">
                        <label class="form-label">Adoption Fee ($)</label>
                        <input type="number" step="0.01" name="adoption_fee" value="<?= htmlspecialchars($_POST['adoption_fee'] ?? $pet['adoption_fee']) ?>" class="form-control">
                    </div>

                    <div class="mb-3">
                        <label class="form-label">Location Description</label>
                        <input type="text" name="location" value="<?= htmlspecialchars($_POST['location'] ?? $pet['location']) ?>" class="form-control">
                    </div>

                    <div class="mb-3">
                        <label class="form-label">Adoption Status</label>
                        <select name="status" class="form-select">
                            <option value="Available" <?= (($_POST['status'] ?? $pet['status']) === 'Available') ? 'selected' : '' ?>>Available</option>
                            <option value="Pending" <?= (($_POST['status'] ?? $pet['status']) === 'Pending') ? 'selected' : '' ?>>Pending Adoption</option>
                            <option value="Adopted" <?= (($_POST['status'] ?? $pet['status']) === 'Adopted') ? 'selected' : '' ?>>Adopted</option>
                        </select>
                    </div>

                    <div class="form-check mt-3">
                        <input class="form-check-input" type="checkbox" name="featured" value="1" id="featCheck" <?= (isset($_POST['featured']) ? true : $pet['featured']) ? 'checked' : '' ?>>
                        <label class="form-check-label fw-bold" for="featCheck">Feature on Homepage</label>
                    </div>
                </div>

                <button type="submit" class="btn btn-paw-primary w-100 py-3 fw-bold fs-5">
                    <i class="bi bi-check2-circle me-1"></i> Update Pet Profile
                </button>
            </div>
        </div>
    </form>
</div>

<?php include __DIR__ . '/../includes/footer.php'; ?>
