<?php
/**
 * PawConnect - Admin Add New Pet
 */
$pageTitle = "Add New Pet";
require_once __DIR__ . '/../includes/header.php';

require_admin();
$db = getDB();

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

    $imageUrl = trim($_POST['image_url'] ?? '');

    // Handle File Upload if provided
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
    if (empty($imageUrl)) $errors[] = 'Please provide an image URL or upload a photo.';

    if (empty($errors)) {
        try {
            $stmt = $db->prepare("
                INSERT INTO pets (
                    name, category_id, shelter_id, breed, age_years, age_months, gender, size, color, 
                    status, description, image_url, adoption_fee, vaccinated, spayed_neutered, 
                    microchipped, house_trained, good_with_kids, good_with_dogs, good_with_cats, 
                    special_needs, location, featured
                ) VALUES (
                    ?, ?, ?, ?, ?, ?, ?, ?, ?, 
                    ?, ?, ?, ?, ?, ?, 
                    ?, ?, ?, ?, ?, 
                    ?, ?, ?
                )
            ");
            $stmt->execute([
                $name, $categoryId, $shelterId, $breed, $ageYears, $ageMonths, $gender, $size, $color,
                $status, $description, $imageUrl, $fee, $vaccinated, $spayedNeutered,
                $microchipped, $houseTrained, $goodWithKids, $goodWithDogs, $goodWithCats,
                $specialNeeds, $location, $featured
            ]);

            $newPetId = $db->lastInsertId();
            set_flash('success', "🎉 Pet profile for '{$name}' created successfully!");
            header("Location: /admin/pets.php");
            exit;

        } catch (Exception $e) {
            $errors[] = 'Error saving pet: ' . $e->getMessage();
        }
    }
}
?>

<div class="container py-4">
    <div class="d-flex flex-wrap justify-content-between align-items-center mb-4 pb-2 border-bottom">
        <div>
            <span class="badge bg-danger-subtle text-danger px-3 py-1 rounded-pill fw-bold">Admin Portal</span>
            <h2 class="mt-2 mb-0 fw-bold">Add New Pet Profile</h2>
            <p class="text-muted small">Register a new animal looking for a forever home.</p>
        </div>
        <a href="/admin/pets.php" class="btn btn-outline-secondary">
            <i class="bi bi-arrow-left me-1"></i> Back to Inventory
        </a>
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

    <form action="/admin/pet-add.php" method="POST" enctype="multipart/form-data">
        <input type="hidden" name="csrf_token" value="<?= csrf_token() ?>">

        <div class="row g-4">
            <!-- Left Column: Primary Details -->
            <div class="col-lg-8">
                <div class="form-card mb-4">
                    <h5 class="fw-bold mb-3"><i class="bi bi-info-circle text-primary me-2"></i> Basic Information</h5>
                    
                    <div class="row g-3">
                        <div class="col-md-6">
                            <label class="form-label">Pet Name <span class="text-danger">*</span></label>
                            <input type="text" name="name" value="<?= htmlspecialchars($_POST['name'] ?? '') ?>" class="form-control" placeholder="e.g. Charlie" required>
                        </div>
                        <div class="col-md-6">
                            <label class="form-label">Species / Category <span class="text-danger">*</span></label>
                            <select name="category_id" class="form-select" required>
                                <option value="">Select Species</option>
                                <?php foreach ($categories as $cat): ?>
                                    <option value="<?= $cat['id'] ?>" <?= (($_POST['category_id'] ?? '') == $cat['id']) ? 'selected' : '' ?>>
                                        <?= htmlspecialchars($cat['name']) ?>
                                    </option>
                                <?php endforeach; ?>
                            </select>
                        </div>

                        <div class="col-md-6">
                            <label class="form-label">Breed <span class="text-danger">*</span></label>
                            <input type="text" name="breed" value="<?= htmlspecialchars($_POST['breed'] ?? '') ?>" class="form-control" placeholder="e.g. Labrador Retriever" required>
                        </div>
                        <div class="col-md-3 col-6">
                            <label class="form-label">Age (Years)</label>
                            <input type="number" name="age_years" min="0" max="30" value="<?= htmlspecialchars($_POST['age_years'] ?? '1') ?>" class="form-control">
                        </div>
                        <div class="col-md-3 col-6">
                            <label class="form-label">Age (Months)</label>
                            <input type="number" name="age_months" min="0" max="11" value="<?= htmlspecialchars($_POST['age_months'] ?? '0') ?>" class="form-control">
                        </div>

                        <div class="col-md-4">
                            <label class="form-label">Gender <span class="text-danger">*</span></label>
                            <select name="gender" class="form-select" required>
                                <option value="Male" <?= (($_POST['gender'] ?? '') === 'Male') ? 'selected' : '' ?>>Male</option>
                                <option value="Female" <?= (($_POST['gender'] ?? '') === 'Female') ? 'selected' : '' ?>>Female</option>
                            </select>
                        </div>
                        <div class="col-md-4">
                            <label class="form-label">Size <span class="text-danger">*</span></label>
                            <select name="size" class="form-select" required>
                                <option value="Small" <?= (($_POST['size'] ?? '') === 'Small') ? 'selected' : '' ?>>Small</option>
                                <option value="Medium" <?= (($_POST['size'] ?? 'Medium') === 'Medium') ? 'selected' : '' ?>>Medium</option>
                                <option value="Large" <?= (($_POST['size'] ?? '') === 'Large') ? 'selected' : '' ?>>Large</option>
                                <option value="Extra Large" <?= (($_POST['size'] ?? '') === 'Extra Large') ? 'selected' : '' ?>>Extra Large</option>
                            </select>
                        </div>
                        <div class="col-md-4">
                            <label class="form-label">Color / Markings</label>
                            <input type="text" name="color" value="<?= htmlspecialchars($_POST['color'] ?? '') ?>" class="form-control" placeholder="e.g. Golden, Tricolor">
                        </div>

                        <div class="col-12">
                            <label class="form-label">Biography & Story <span class="text-danger">*</span></label>
                            <textarea name="description" rows="4" class="form-control" placeholder="Describe the pet's temperament, favorite activities, rescue story, and ideal home..." required><?= htmlspecialchars($_POST['description'] ?? '') ?></textarea>
                        </div>
                    </div>
                </div>

                <!-- Health & Behavioral Toggles -->
                <div class="form-card">
                    <h5 class="fw-bold mb-3"><i class="bi bi-shield-check text-success me-2"></i> Health & Behavioral Attributes</h5>
                    
                    <div class="row g-3 mb-3">
                        <div class="col-sm-6">
                            <div class="form-check form-switch p-2 ps-5 border rounded-3 bg-light">
                                <input class="form-check-input" type="checkbox" name="vaccinated" value="1" id="vaxCheck" <?= (!isset($_POST['name']) || isset($_POST['vaccinated'])) ? 'checked' : '' ?>>
                                <label class="form-check-label fw-semibold" for="vaxCheck">Vaccinations Up to Date</label>
                            </div>
                        </div>
                        <div class="col-sm-6">
                            <div class="form-check form-switch p-2 ps-5 border rounded-3 bg-light">
                                <input class="form-check-input" type="checkbox" name="spayed_neutered" value="1" id="spayCheck" <?= (!isset($_POST['name']) || isset($_POST['spayed_neutered'])) ? 'checked' : '' ?>>
                                <label class="form-check-label fw-semibold" for="spayCheck">Spayed / Neutered</label>
                            </div>
                        </div>
                        <div class="col-sm-6">
                            <div class="form-check form-switch p-2 ps-5 border rounded-3 bg-light">
                                <input class="form-check-input" type="checkbox" name="microchipped" value="1" id="chipCheck" <?= (!isset($_POST['name']) || isset($_POST['microchipped'])) ? 'checked' : '' ?>>
                                <label class="form-check-label fw-semibold" for="chipCheck">Microchipped</label>
                            </div>
                        </div>
                        <div class="col-sm-6">
                            <div class="form-check form-switch p-2 ps-5 border rounded-3 bg-light">
                                <input class="form-check-input" type="checkbox" name="house_trained" value="1" id="trainCheck" <?= (!isset($_POST['name']) || isset($_POST['house_trained'])) ? 'checked' : '' ?>>
                                <label class="form-check-label fw-semibold" for="trainCheck">House / Litter Trained</label>
                            </div>
                        </div>
                    </div>

                    <h6 class="fw-bold text-muted small text-uppercase mt-4 mb-2">Compatibility with:</h6>
                    <div class="d-flex flex-wrap gap-3 mb-3">
                        <div class="form-check">
                            <input class="form-check-input" type="checkbox" name="good_with_kids" value="1" id="kidsCheck" <?= (!isset($_POST['name']) || isset($_POST['good_with_kids'])) ? 'checked' : '' ?>>
                            <label class="form-check-label" for="kidsCheck">Children</label>
                        </div>
                        <div class="form-check">
                            <input class="form-check-input" type="checkbox" name="good_with_dogs" value="1" id="dogsCheck" <?= (!isset($_POST['name']) || isset($_POST['good_with_dogs'])) ? 'checked' : '' ?>>
                            <label class="form-check-label" for="dogsCheck">Dogs</label>
                        </div>
                        <div class="form-check">
                            <input class="form-check-input" type="checkbox" name="good_with_cats" value="1" id="catsCheck" <?= (!isset($_POST['name']) || isset($_POST['good_with_cats'])) ? 'checked' : '' ?>>
                            <label class="form-check-label" for="catsCheck">Cats</label>
                        </div>
                    </div>

                    <div class="mt-3">
                        <label class="form-label">Special Needs / Medication (Optional)</label>
                        <input type="text" name="special_needs" value="<?= htmlspecialchars($_POST['special_needs'] ?? '') ?>" class="form-control" placeholder="e.g. Daily ear drops, special diet, or None">
                    </div>
                </div>
            </div>

            <!-- Right Column: Media, Shelter & Status -->
            <div class="col-lg-4">
                <!-- Image Card -->
                <div class="form-card mb-4 text-center">
                    <h5 class="fw-bold text-start mb-3"><i class="bi bi-image text-primary me-2"></i> Pet Photo</h5>
                    <img id="image_preview" src="https://images.unsplash.com/photo-1543466835-00a7907e9de1?auto=format&fit=crop&w=800&q=80" alt="Preview" class="img-fluid rounded-4 mb-3 object-fit-cover w-100" style="max-height: 220px;">
                    
                    <div class="mb-3 text-start">
                        <label class="form-label small fw-bold">Image URL</label>
                        <input type="url" name="image_url" id="image_url" value="<?= htmlspecialchars($_POST['image_url'] ?? '') ?>" class="form-control form-control-sm" placeholder="https://images.unsplash.com/...">
                        <div class="form-text">Direct high-res photo link from Unsplash, Imgur, etc.</div>
                    </div>

                    <div class="text-start">
                        <label class="form-label small fw-bold">Or Upload File</label>
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
                                <option value="<?= $s['id'] ?>" <?= (($_POST['shelter_id'] ?? '') == $s['id']) ? 'selected' : '' ?>>
                                    <?= htmlspecialchars($s['name']) ?> (<?= htmlspecialchars($s['city']) ?>)
                                </option>
                            <?php endforeach; ?>
                        </select>
                    </div>

                    <div class="mb-3">
                        <label class="form-label">Adoption Fee ($)</label>
                        <input type="number" step="0.01" name="adoption_fee" value="<?= htmlspecialchars($_POST['adoption_fee'] ?? '100.00') ?>" class="form-control">
                    </div>

                    <div class="mb-3">
                        <label class="form-label">Location Description</label>
                        <input type="text" name="location" value="<?= htmlspecialchars($_POST['location'] ?? 'Main Shelter Campus') ?>" class="form-control">
                    </div>

                    <div class="mb-3">
                        <label class="form-label">Status</label>
                        <select name="status" class="form-select">
                            <option value="Available" <?= (($_POST['status'] ?? 'Available') === 'Available') ? 'selected' : '' ?>>Available</option>
                            <option value="Pending" <?= (($_POST['status'] ?? '') === 'Pending') ? 'selected' : '' ?>>Pending Adoption</option>
                            <option value="Adopted" <?= (($_POST['status'] ?? '') === 'Adopted') ? 'selected' : '' ?>>Adopted</option>
                        </select>
                    </div>

                    <div class="form-check mt-3">
                        <input class="form-check-input" type="checkbox" name="featured" value="1" id="featCheck" <?= isset($_POST['featured']) ? 'checked' : '' ?>>
                        <label class="form-check-label fw-bold" for="featCheck">Feature on Homepage</label>
                    </div>
                </div>

                <button type="submit" class="btn btn-paw-primary w-100 py-3 fw-bold fs-5">
                    <i class="bi bi-cloud-arrow-up-fill me-1"></i> Save & Publish Pet
                </button>
            </div>
        </div>
    </form>
</div>

<?php include __DIR__ . '/../includes/footer.php'; ?>
