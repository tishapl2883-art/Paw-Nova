<?php
/**
 * PawConnect - Pets Search & Discovery Catalog
 */
$pageTitle = "Browse Pets For Adoption";
require_once __DIR__ . '/includes/header.php';

$db = getDB();

// 1. Fetch categories for filters
$categories = $db->query("SELECT * FROM categories ORDER BY name ASC")->fetchAll();

// 2. Parse query parameters
$search = trim($_GET['q'] ?? '');
$categorySlug = trim($_GET['category'] ?? '');
$gender = trim($_GET['gender'] ?? '');
$size = trim($_GET['size'] ?? '');
$status = trim($_GET['status'] ?? 'Available');
$vaccinated = isset($_GET['vaccinated']) ? 1 : 0;
$goodWithKids = isset($_GET['good_with_kids']) ? 1 : 0;
$goodWithDogs = isset($_GET['good_with_dogs']) ? 1 : 0;
$sort = trim($_GET['sort'] ?? 'newest');

// 3. Build dynamic SQL query
$sql = "
    SELECT p.*, c.name as category_name, c.slug as category_slug, s.name as shelter_name, s.city as shelter_city
    FROM pets p
    JOIN categories c ON p.category_id = c.id
    LEFT JOIN shelters s ON p.shelter_id = s.id
    WHERE 1=1
";
$params = [];

if ($search !== '') {
    $sql .= " AND (p.name LIKE ? OR p.breed LIKE ? OR p.description LIKE ? OR p.location LIKE ?)";
    $like = "%$search%";
    $params[] = $like;
    $params[] = $like;
    $params[] = $like;
    $params[] = $like;
}

if ($categorySlug !== '') {
    $sql .= " AND c.slug = ?";
    $params[] = $categorySlug;
}

if ($gender !== '' && in_array($gender, ['Male', 'Female'])) {
    $sql .= " AND p.gender = ?";
    $params[] = $gender;
}

if ($size !== '' && in_array($size, ['Small', 'Medium', 'Large', 'Extra Large'])) {
    $sql .= " AND p.size = ?";
    $params[] = $size;
}

if ($status !== '' && $status !== 'All') {
    $sql .= " AND p.status = ?";
    $params[] = $status;
}

if ($vaccinated) {
    $sql .= " AND p.vaccinated = 1";
}

if ($goodWithKids) {
    $sql .= " AND p.good_with_kids = 1";
}

if ($goodWithDogs) {
    $sql .= " AND p.good_with_dogs = 1";
}

// Sorting logic
switch ($sort) {
    case 'name_asc':
        $sql .= " ORDER BY p.name ASC";
        break;
    case 'age_asc':
        $sql .= " ORDER BY (p.age_years * 12 + p.age_months) ASC";
        break;
    case 'fee_asc':
        $sql .= " ORDER BY p.adoption_fee ASC";
        break;
    case 'newest':
    default:
        $sql .= " ORDER BY p.id DESC";
        break;
}

$stmt = $db->prepare($sql);
$stmt->execute($params);
$pets = $stmt->fetchAll();
$totalFound = count($pets);
?>

<div class="container py-4">
    <!-- Breadcrumbs -->
    <nav aria-label="breadcrumb" class="mb-4">
        <ol class="breadcrumb">
            <li class="breadcrumb-item"><a href="/index.php">Home</a></li>
            <li class="breadcrumb-item active" aria-current="page">Browse Pets</li>
        </ol>
    </nav>

    <div class="row g-4">
        <!-- Filter Sidebar -->
        <div class="col-lg-3">
            <div class="bg-white p-4 rounded-4 border shadow-sm sticky-top" style="top: 90px; z-index: 10;">
                <div class="d-flex justify-content-between align-items-center mb-3">
                    <h5 class="mb-0 fw-bold"><i class="bi bi-funnel me-1 text-primary"></i> Filters</h5>
                    <a href="/pets.php" class="small text-danger text-decoration-none">Reset All</a>
                </div>

                <form action="/pets.php" method="GET" id="filterForm">
                    <!-- Keyword Search -->
                    <div class="mb-3">
                        <label class="form-label small fw-bold">Search</label>
                        <input type="text" name="q" value="<?= htmlspecialchars($search) ?>" class="form-control form-control-sm" placeholder="Name, breed, keyword...">
                    </div>

                    <!-- Category / Species -->
                    <div class="mb-3">
                        <label class="form-label small fw-bold">Species</label>
                        <select name="category" class="form-select form-select-sm" onchange="this.form.submit()">
                            <option value="">All Animals</option>
                            <?php foreach ($categories as $cat): ?>
                                <option value="<?= htmlspecialchars($cat['slug']) ?>" <?= ($categorySlug === $cat['slug']) ? 'selected' : '' ?>>
                                    <?= htmlspecialchars($cat['name']) ?>
                                </option>
                            <?php endforeach; ?>
                        </select>
                    </div>

                    <!-- Gender -->
                    <div class="mb-3">
                        <label class="form-label small fw-bold">Gender</label>
                        <select name="gender" class="form-select form-select-sm" onchange="this.form.submit()">
                            <option value="">Any Gender</option>
                            <option value="Male" <?= ($gender === 'Male') ? 'selected' : '' ?>>Male</option>
                            <option value="Female" <?= ($gender === 'Female') ? 'selected' : '' ?>>Female</option>
                        </select>
                    </div>

                    <!-- Size -->
                    <div class="mb-3">
                        <label class="form-label small fw-bold">Size</label>
                        <select name="size" class="form-select form-select-sm" onchange="this.form.submit()">
                            <option value="">Any Size</option>
                            <option value="Small" <?= ($size === 'Small') ? 'selected' : '' ?>>Small</option>
                            <option value="Medium" <?= ($size === 'Medium') ? 'selected' : '' ?>>Medium</option>
                            <option value="Large" <?= ($size === 'Large') ? 'selected' : '' ?>>Large</option>
                            <option value="Extra Large" <?= ($size === 'Extra Large') ? 'selected' : '' ?>>Extra Large</option>
                        </select>
                    </div>

                    <!-- Status -->
                    <div class="mb-3">
                        <label class="form-label small fw-bold">Adoption Status</label>
                        <select name="status" class="form-select form-select-sm" onchange="this.form.submit()">
                            <option value="Available" <?= ($status === 'Available') ? 'selected' : '' ?>>Available Only</option>
                            <option value="Pending" <?= ($status === 'Pending') ? 'selected' : '' ?>>Pending Adoption</option>
                            <option value="Adopted" <?= ($status === 'Adopted') ? 'selected' : '' ?>>Already Adopted</option>
                            <option value="All" <?= ($status === 'All') ? 'selected' : '' ?>>All Pets</option>
                        </select>
                    </div>

                    <!-- Special Requirements -->
                    <div class="mb-3">
                        <label class="form-label small fw-bold">Characteristics</label>
                        <div class="form-check form-check-sm mb-1">
                            <input class="form-check-input" type="checkbox" name="vaccinated" value="1" id="cbVaccinated" <?= $vaccinated ? 'checked' : '' ?> onchange="this.form.submit()">
                            <label class="form-check-label small" for="cbVaccinated">Fully Vaccinated</label>
                        </div>
                        <div class="form-check form-check-sm mb-1">
                            <input class="form-check-input" type="checkbox" name="good_with_kids" value="1" id="cbKids" <?= $goodWithKids ? 'checked' : '' ?> onchange="this.form.submit()">
                            <label class="form-check-label small" for="cbKids">Good with Kids</label>
                        </div>
                        <div class="form-check form-check-sm">
                            <input class="form-check-input" type="checkbox" name="good_with_dogs" value="1" id="cbDogs" <?= $goodWithDogs ? 'checked' : '' ?> onchange="this.form.submit()">
                            <label class="form-check-label small" for="cbDogs">Good with Other Pets</label>
                        </div>
                    </div>

                    <button type="submit" class="btn btn-paw-primary btn-sm w-100">
                        <i class="bi bi-check2-circle me-1"></i> Apply Filters
                    </button>
                </form>
            </div>
        </div>

        <!-- Pets Grid & Controls -->
        <div class="col-lg-9">
            <!-- Header bar with count and sort -->
            <div class="d-flex flex-wrap justify-content-between align-items-center bg-white p-3 rounded-4 border shadow-sm mb-4">
                <div class="fw-semibold text-muted">
                    Found <span class="text-dark fw-bold"><?= $totalFound ?></span> pet<?= ($totalFound !== 1) ? 's' : '' ?> matching your search
                </div>
                <div class="d-flex align-items-center gap-2 mt-2 mt-sm-0">
                    <label class="small text-muted text-nowrap">Sort by:</label>
                    <select name="sort" class="form-select form-select-sm" form="filterForm" onchange="document.getElementById('filterForm').submit()">
                        <option value="newest" <?= ($sort === 'newest') ? 'selected' : '' ?>>Recently Added</option>
                        <option value="name_asc" <?= ($sort === 'name_asc') ? 'selected' : '' ?>>Name (A - Z)</option>
                        <option value="age_asc" <?= ($sort === 'age_asc') ? 'selected' : '' ?>>Age (Youngest First)</option>
                        <option value="fee_asc" <?= ($sort === 'fee_asc') ? 'selected' : '' ?>>Adoption Fee (Low to High)</option>
                    </select>
                </div>
            </div>

            <!-- Pets Cards -->
            <?php if (empty($pets)): ?>
                <div class="bg-white rounded-4 border p-5 text-center shadow-sm">
                    <div class="display-3 text-muted mb-3"><i class="bi bi-search"></i></div>
                    <h4>No pets matched your filter criteria</h4>
                    <p class="text-muted">Try relaxing some of your filters or resetting the search to see all available pets.</p>
                    <a href="/pets.php" class="btn btn-paw-primary">
                        <i class="bi bi-arrow-counterclockwise me-1"></i> View All Pets
                    </a>
                </div>
            <?php else: ?>
                <div class="row g-4">
                    <?php foreach ($pets as $pet): 
                        $isFav = is_favorite($db, current_user()['id'] ?? null, $pet['id']);
                    ?>
                        <div class="col-md-6 col-xl-4">
                            <div class="pet-card">
                                <div class="pet-card-image-wrap">
                                    <img src="<?= htmlspecialchars(get_pet_image($pet['image_url'])) ?>" alt="<?= htmlspecialchars($pet['name']) ?>">
                                    <div class="pet-card-badges">
                                        <?= status_badge($pet['status']) ?>
                                        <span class="badge bg-dark bg-opacity-75 text-white"><?= htmlspecialchars($pet['category_name']) ?></span>
                                    </div>
                                    <?php if (is_logged_in() && !is_admin()): ?>
                                        <button class="pet-fav-btn <?= $isFav ? 'active' : '' ?>" data-pet-id="<?= $pet['id'] ?>" title="Save to Favorites">
                                            <i class="bi <?= $isFav ? 'bi-heart-fill' : 'bi-heart' ?>"></i>
                                        </button>
                                    <?php endif; ?>
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
                                        <?php if ($pet['vaccinated']): ?>
                                            <span class="pet-tag text-success"><i class="bi bi-shield-check me-1"></i>Vaccinated</span>
                                        <?php endif; ?>
                                        <?php if ($pet['spayed_neutered']): ?>
                                            <span class="pet-tag text-info"><i class="bi bi-check2-circle me-1"></i>Neutered</span>
                                        <?php endif; ?>
                                    </div>
                                </div>
                                <div class="pet-card-footer">
                                    <span class="small text-muted"><i class="bi bi-geo-alt me-1"></i><?= htmlspecialchars($pet['shelter_city'] ?? 'Shelter') ?></span>
                                    <a href="/pet-details.php?id=<?= $pet['id'] ?>" class="btn btn-sm btn-paw-primary">
                                        Meet <?= htmlspecialchars($pet['name']) ?> <i class="bi bi-arrow-right"></i>
                                    </a>
                                </div>
                            </div>
                        </div>
                    <?php endforeach; ?>
                </div>
            <?php endif; ?>
        </div>
    </div>
</div>

<?php include __DIR__ . '/includes/footer.php'; ?>
