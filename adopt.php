<?php
/**
 * PawConnect - Adoption Application Form
 */
require_once __DIR__ . '/includes/header.php';

// Authentication required
require_login();
$user = current_user();

$db = getDB();
$petId = isset($_GET['pet_id']) ? (int)$_GET['pet_id'] : (int)($_POST['pet_id'] ?? 0);

if ($petId <= 0) {
    set_flash('danger', 'Please choose a pet to adopt.');
    header('Location: /pets.php');
    exit;
}

// Fetch pet
$stmt = $db->prepare("
    SELECT p.*, c.name as category_name, s.name as shelter_name, s.city as shelter_city
    FROM pets p 
    JOIN categories c ON p.category_id = c.id
    LEFT JOIN shelters s ON p.shelter_id = s.id
    WHERE p.id = ?
");
$stmt->execute([$petId]);
$pet = $stmt->fetch();

if (!$pet) {
    set_flash('danger', 'Pet profile not found.');
    header('Location: /pets.php');
    exit;
}

if ($pet['status'] === 'Adopted') {
    set_flash('warning', 'This pet has already been adopted! Please browse our other available pets.');
    header('Location: /pets.php');
    exit;
}

// Check existing pending or review application
$checkStmt = $db->prepare("
    SELECT id, application_no, status 
    FROM adoption_applications 
    WHERE user_id = ? AND pet_id = ? AND status IN ('Pending', 'Under Review')
");
$checkStmt->execute([$user['id'], $petId]);
$existingApp = $checkStmt->fetch();

if ($existingApp) {
    set_flash('info', "You already have an active application ({$existingApp['application_no']}) for {$pet['name']}. Status: {$existingApp['status']}.");
    header('Location: /my-applications.php');
    exit;
}

// Fetch user profile info to pre-fill
$userStmt = $db->prepare("SELECT * FROM users WHERE id = ?");
$userStmt->execute([$user['id']]);
$userData = $userStmt->fetch();

$errors = [];

// Handle Form Submission
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    if (!verify_csrf($_POST['csrf_token'] ?? '')) {
        $errors[] = 'Invalid security token. Please try again.';
    }

    $fullName = sanitize($_POST['full_name'] ?? '');
    $email = sanitize($_POST['email'] ?? '');
    $phone = sanitize($_POST['phone'] ?? '');
    $address = sanitize($_POST['address'] ?? '');
    $city = sanitize($_POST['city'] ?? '');
    $state = sanitize($_POST['state'] ?? '');
    $zipCode = sanitize($_POST['zip_code'] ?? '');
    $housingType = sanitize($_POST['housing_type'] ?? '');
    $hasYard = isset($_POST['has_yard']) ? 1 : 0;
    $yardFenced = isset($_POST['yard_fenced']) ? 1 : 0;
    $landlordPermission = isset($_POST['landlord_permission']) ? 1 : 0;
    $householdAdults = (int)($_POST['household_adults'] ?? 1);
    $householdChildren = (int)($_POST['household_children'] ?? 0);
    $currentPets = sanitize($_POST['current_pets'] ?? '');
    $petExperience = sanitize($_POST['pet_experience'] ?? '');
    $hoursAlone = (int)($_POST['hours_alone_daily'] ?? 4);
    $vetNameContact = sanitize($_POST['vet_name_contact'] ?? '');
    $reason = sanitize($_POST['reason_for_adoption'] ?? '');
    $agreeTerms = isset($_POST['agree_terms']);

    // Validation
    if (empty($fullName)) $errors[] = 'Full name is required.';
    if (empty($email) || !filter_var($email, FILTER_VALIDATE_EMAIL)) $errors[] = 'Valid email address is required.';
    if (empty($phone)) $errors[] = 'Phone number is required.';
    if (empty($address) || empty($city)) $errors[] = 'Physical address and city are required.';
    if (empty($housingType)) $errors[] = 'Please select your housing type.';
    if (empty($petExperience)) $errors[] = 'Please select your pet ownership experience.';
    if (empty($reason) || strlen($reason) < 20) $errors[] = 'Please share your reason for adopting this pet (at least 20 characters).';
    if (!$agreeTerms) $errors[] = 'You must agree to the adoption terms and home check verification.';

    if (empty($errors)) {
        try {
            // Generate Application Number: PAW-YEAR-XXXX
            $appNo = 'PAW-' . date('Y') . '-' . strtoupper(substr(uniqid(), -5));

            $insertStmt = $db->prepare("
                INSERT INTO adoption_applications (
                    application_no, user_id, pet_id, full_name, email, phone, 
                    address, city, state, zip_code, housing_type, has_yard, yard_fenced, 
                    landlord_permission, household_adults, household_children, current_pets, 
                    pet_experience, hours_alone_daily, vet_name_contact, reason_for_adoption, 
                    status, applicant_feedback
                ) VALUES (
                    ?, ?, ?, ?, ?, ?, 
                    ?, ?, ?, ?, ?, ?, ?, 
                    ?, ?, ?, ?, 
                    ?, ?, ?, ?, 
                    'Pending', 'Application successfully received! Our adoption team will begin reviewing your file shortly.'
                )
            ");

            $insertStmt->execute([
                $appNo, $user['id'], $petId, $fullName, $email, $phone,
                $address, $city, $state, $zipCode, $housingType, $hasYard, $yardFenced,
                $landlordPermission, $householdAdults, $householdChildren, $currentPets,
                $petExperience, $hoursAlone, $vetNameContact, $reason
            ]);

            // Optionally mark pet status as Pending
            $updatePet = $db->prepare("UPDATE pets SET status = 'Pending' WHERE id = ? AND status = 'Available'");
            $updatePet->execute([$petId]);

            set_flash('success', "🎉 Your adoption application #{$appNo} has been submitted successfully! You can track its progress below.");
            header('Location: /my-applications.php');
            exit;

        } catch (Exception $e) {
            $errors[] = 'Failed to submit application: ' . $e->getMessage();
        }
    }
}
?>

<div class="container py-4">
    <!-- Breadcrumbs -->
    <nav aria-label="breadcrumb" class="mb-4">
        <ol class="breadcrumb">
            <li class="breadcrumb-item"><a href="/index.php">Home</a></li>
            <li class="breadcrumb-item"><a href="/pets.php">Browse Pets</a></li>
            <li class="breadcrumb-item"><a href="/pet-details.php?id=<?= $pet['id'] ?>"><?= htmlspecialchars($pet['name']) ?></a></li>
            <li class="breadcrumb-item active" aria-current="page">Adoption Application</li>
        </ol>
    </nav>

    <div class="row g-4">
        <!-- Selected Pet Summary Card (Sticky) -->
        <div class="col-lg-4 order-lg-2">
            <div class="bg-white rounded-4 border p-4 shadow-sm sticky-top" style="top: 90px;">
                <h5 class="fw-bold mb-3"><i class="bi bi-heart-fill text-danger me-2"></i> You Are Applying For</h5>
                <div class="d-flex gap-3 align-items-center mb-3">
                    <img src="<?= htmlspecialchars(get_pet_image($pet['image_url'])) ?>" alt="<?= htmlspecialchars($pet['name']) ?>" class="rounded-3 object-fit-cover" style="width: 80px; height: 80px;">
                    <div>
                        <h5 class="fw-bold mb-0"><?= htmlspecialchars($pet['name']) ?></h5>
                        <p class="text-muted small mb-1"><?= htmlspecialchars($pet['breed']) ?></p>
                        <span class="badge bg-primary-subtle text-primary"><?= htmlspecialchars($pet['category_name']) ?></span>
                    </div>
                </div>

                <ul class="list-group list-group-flush small mb-3">
                    <li class="list-group-item d-flex justify-content-between px-0">
                        <span class="text-muted">Age:</span>
                        <span class="fw-bold"><?= get_pet_age($pet['age_years'], $pet['age_months']) ?></span>
                    </li>
                    <li class="list-group-item d-flex justify-content-between px-0">
                        <span class="text-muted">Gender:</span>
                        <span class="fw-bold"><?= htmlspecialchars($pet['gender']) ?></span>
                    </li>
                    <li class="list-group-item d-flex justify-content-between px-0">
                        <span class="text-muted">Adoption Fee:</span>
                        <span class="fw-bold text-success">$<?= number_format($pet['adoption_fee'], 2) ?></span>
                    </li>
                    <li class="list-group-item d-flex justify-content-between px-0">
                        <span class="text-muted">Shelter:</span>
                        <span class="fw-bold"><?= htmlspecialchars($pet['shelter_name'] ?? 'Main Shelter') ?></span>
                    </li>
                </ul>

                <div class="alert alert-info small mb-0">
                    <i class="bi bi-info-circle-fill me-1"></i> Submitting this application does not obligate you to adopt. It allows our shelter team to review compatibility.
                </div>
            </div>
        </div>

        <!-- Application Form Card -->
        <div class="col-lg-8 order-lg-1">
            <div class="form-card">
                <div class="mb-4">
                    <span class="badge bg-light text-primary px-3 py-2 rounded-pill fw-bold">Step 3 of 4</span>
                    <h2 class="mt-2 mb-1">Adoption Application Form</h2>
                    <p class="text-muted">Please provide accurate information to help us ensure <?= htmlspecialchars($pet['name']) ?> is the perfect match for your family.</p>
                </div>

                <?php if (!empty($errors)): ?>
                    <div class="alert alert-danger shadow-sm mb-4">
                        <div class="fw-bold mb-1"><i class="bi bi-exclamation-triangle-fill me-1"></i> Please correct the following errors:</div>
                        <ul class="mb-0 ps-3">
                            <?php foreach ($errors as $err): ?>
                                <li><?= htmlspecialchars($err) ?></li>
                            <?php endforeach; ?>
                        </ul>
                    </div>
                <?php endif; ?>

                <form action="/adopt.php" method="POST">
                    <input type="hidden" name="csrf_token" value="<?= csrf_token() ?>">
                    <input type="hidden" name="pet_id" value="<?= $pet['id'] ?>">

                    <!-- Section 1: Contact Information -->
                    <h5 class="fw-bold text-dark border-bottom pb-2 mb-3">
                        <i class="bi bi-person-lines-fill text-primary me-2"></i> 1. Applicant Information
                    </h5>
                    <div class="row g-3 mb-4">
                        <div class="col-md-6">
                            <label class="form-label">Full Name <span class="text-danger">*</span></label>
                            <input type="text" name="full_name" class="form-control" value="<?= htmlspecialchars($_POST['full_name'] ?? $userData['name'] ?? '') ?>" required>
                        </div>
                        <div class="col-md-6">
                            <label class="form-label">Email Address <span class="text-danger">*</span></label>
                            <input type="email" name="email" class="form-control" value="<?= htmlspecialchars($_POST['email'] ?? $userData['email'] ?? '') ?>" required>
                        </div>
                        <div class="col-md-6">
                            <label class="form-label">Phone Number <span class="text-danger">*</span></label>
                            <input type="text" name="phone" class="form-control" placeholder="(555) 000-0000" value="<?= htmlspecialchars($_POST['phone'] ?? $userData['phone'] ?? '') ?>" required>
                        </div>
                        <div class="col-md-6">
                            <label class="form-label">Street Address <span class="text-danger">*</span></label>
                            <input type="text" name="address" class="form-control" placeholder="123 Main St, Apt 4" value="<?= htmlspecialchars($_POST['address'] ?? $userData['address'] ?? '') ?>" required>
                        </div>
                        <div class="col-md-5">
                            <label class="form-label">City <span class="text-danger">*</span></label>
                            <input type="text" name="city" class="form-control" value="<?= htmlspecialchars($_POST['city'] ?? $userData['city'] ?? '') ?>" required>
                        </div>
                        <div class="col-md-4">
                            <label class="form-label">State / Region</label>
                            <input type="text" name="state" class="form-control" placeholder="e.g. CA, TX" value="<?= htmlspecialchars($_POST['state'] ?? '') ?>">
                        </div>
                        <div class="col-md-3">
                            <label class="form-label">ZIP / Postal Code</label>
                            <input type="text" name="zip_code" class="form-control" value="<?= htmlspecialchars($_POST['zip_code'] ?? '') ?>">
                        </div>
                    </div>

                    <!-- Section 2: Living Environment -->
                    <h5 class="fw-bold text-dark border-bottom pb-2 mb-3">
                        <i class="bi bi-house-door-fill text-primary me-2"></i> 2. Housing & Household Dynamics
                    </h5>
                    <div class="row g-3 mb-4">
                        <div class="col-md-6">
                            <label class="form-label">Housing Type <span class="text-danger">*</span></label>
                            <select name="housing_type" class="form-select" required>
                                <option value="">Select Housing Type</option>
                                <option value="Own House" <?= (($_POST['housing_type'] ?? '') === 'Own House') ? 'selected' : '' ?>>Own House</option>
                                <option value="Rent House" <?= (($_POST['housing_type'] ?? '') === 'Rent House') ? 'selected' : '' ?>>Rent Single Family House</option>
                                <option value="Rent Apartment" <?= (($_POST['housing_type'] ?? '') === 'Rent Apartment') ? 'selected' : '' ?>>Rent Apartment</option>
                                <option value="Condo/Townhouse" <?= (($_POST['housing_type'] ?? '') === 'Condo/Townhouse') ? 'selected' : '' ?>>Condominium / Townhouse</option>
                                <option value="Other" <?= (($_POST['housing_type'] ?? '') === 'Other') ? 'selected' : '' ?>>Other</option>
                            </select>
                        </div>
                        <div class="col-md-3 col-6">
                            <label class="form-label">Adults (18+)</label>
                            <input type="number" name="household_adults" min="1" max="10" class="form-control" value="<?= htmlspecialchars($_POST['household_adults'] ?? '2') ?>" required>
                        </div>
                        <div class="col-md-3 col-6">
                            <label class="form-label">Children (<18)</label>
                            <input type="number" name="household_children" min="0" max="10" class="form-control" value="<?= htmlspecialchars($_POST['household_children'] ?? '0') ?>">
                        </div>

                        <div class="col-12">
                            <div class="d-flex flex-wrap gap-4 p-3 bg-light rounded-3 border">
                                <div class="form-check">
                                    <input class="form-check-input" type="checkbox" name="has_yard" value="1" id="yardCheck" <?= isset($_POST['has_yard']) ? 'checked' : '' ?>>
                                    <label class="form-check-label fw-semibold" for="yardCheck">Property has a private yard</label>
                                </div>
                                <div class="form-check">
                                    <input class="form-check-input" type="checkbox" name="yard_fenced" value="1" id="fenceCheck" <?= isset($_POST['yard_fenced']) ? 'checked' : '' ?>>
                                    <label class="form-check-label fw-semibold" for="fenceCheck">Yard is fully fenced</label>
                                </div>
                                <div class="form-check">
                                    <input class="form-check-input" type="checkbox" name="landlord_permission" value="1" id="landlordCheck" <?= (!isset($_POST['full_name']) || isset($_POST['landlord_permission'])) ? 'checked' : '' ?>>
                                    <label class="form-check-label fw-semibold" for="landlordCheck">Landlord permits pets (if renting)</label>
                                </div>
                            </div>
                        </div>
                    </div>

                    <!-- Section 3: Pet Experience & Routine -->
                    <h5 class="fw-bold text-dark border-bottom pb-2 mb-3">
                        <i class="bi bi-heart-pulse-fill text-primary me-2"></i> 3. Pet Experience & Care Routine
                    </h5>
                    <div class="row g-3 mb-4">
                        <div class="col-md-6">
                            <label class="form-label">Pet Ownership Experience <span class="text-danger">*</span></label>
                            <select name="pet_experience" class="form-select" required>
                                <option value="">Select Experience Level</option>
                                <option value="First-time owner" <?= (($_POST['pet_experience'] ?? '') === 'First-time owner') ? 'selected' : '' ?>>First-time pet owner</option>
                                <option value="Current pet owner" <?= (($_POST['pet_experience'] ?? '') === 'Current pet owner') ? 'selected' : '' ?>>Currently own pets</option>
                                <option value="Experienced" <?= (($_POST['pet_experience'] ?? '') === 'Experienced') ? 'selected' : '' ?>>Experienced (owned pets previously)</option>
                                <option value="Expert" <?= (($_POST['pet_experience'] ?? '') === 'Expert') ? 'selected' : '' ?>>Expert / Trainer / Veterinary field</option>
                            </select>
                        </div>
                        <div class="col-md-6">
                            <label class="form-label">Estimated Hours Pet Will Be Left Alone Daily <span class="text-danger">*</span></label>
                            <input type="number" name="hours_alone_daily" min="0" max="24" class="form-control" value="<?= htmlspecialchars($_POST['hours_alone_daily'] ?? '4') ?>" required>
                        </div>
                        <div class="col-12">
                            <label class="form-label">Current Pets in Household (Species, Breed, Ages, Spayed/Neutered?)</label>
                            <textarea name="current_pets" class="form-control" rows="2" placeholder="e.g. 1 senior Golden Retriever (10 yrs, neutered), 1 indoor cat (5 yrs, spayed) or None"><?= htmlspecialchars($_POST['current_pets'] ?? '') ?></textarea>
                        </div>
                        <div class="col-12">
                            <label class="form-label">Veterinary Clinic Name & Contact (If applicable)</label>
                            <input type="text" name="vet_name_contact" class="form-control" placeholder="e.g. Sunshine Animal Hospital, Dr. Miller (555) 123-4567" value="<?= htmlspecialchars($_POST['vet_name_contact'] ?? '') ?>">
                        </div>
                    </div>

                    <!-- Section 4: Motivation & Agreement -->
                    <h5 class="fw-bold text-dark border-bottom pb-2 mb-3">
                        <i class="bi bi-chat-quote-fill text-primary me-2"></i> 4. Why <?= htmlspecialchars($pet['name']) ?>?
                    </h5>
                    <div class="mb-4">
                        <label class="form-label">Tell us why you want to adopt <?= htmlspecialchars($pet['name']) ?> and how they will fit into your life <span class="text-danger">*</span></label>
                        <textarea name="reason_for_adoption" class="form-control" rows="4" placeholder="Share your daily routine, exercise plans, who will care for the pet, and why this specific pet captured your heart..." required><?= htmlspecialchars($_POST['reason_for_adoption'] ?? '') ?></textarea>
                        <div class="form-text">Minimum 20 characters. The more detail you share, the quicker shelters can assess the match!</div>
                    </div>

                    <!-- Agreement Checkbox -->
                    <div class="mb-4 p-3 bg-light rounded-3 border">
                        <div class="form-check">
                            <input class="form-check-input" type="checkbox" name="agree_terms" id="agreeCheck" required <?= isset($_POST['agree_terms']) ? 'checked' : '' ?>>
                            <label class="form-check-label small" for="agreeCheck">
                                <strong>I certify that all information provided is true and complete.</strong> I consent to an adoption counselor reviewing my application, contacting references if necessary, and arranging a meet-and-greet or home check.
                            </label>
                        </div>
                    </div>

                    <!-- Submit Button -->
                    <div class="d-flex justify-content-between align-items-center">
                        <a href="/pet-details.php?id=<?= $pet['id'] ?>" class="btn btn-outline-secondary">
                            <i class="bi bi-arrow-left me-1"></i> Back to Pet Profile
                        </a>
                        <button type="submit" class="btn btn-paw-primary btn-lg px-4">
                            <i class="bi bi-send-fill me-2"></i> Submit Adoption Application
                        </button>
                    </div>
                </form>
            </div>
        </div>
    </div>
</div>

<?php include __DIR__ . '/includes/footer.php'; ?>
