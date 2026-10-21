<?php
/**
 * PawConnect - Homepage
 */
$pageTitle = "PawConnect - Simple, Accessible & Organized Pet Adoption";
require_once __DIR__ . '/includes/header.php';

$db = getDB();

// Fetch counts
$totalPets = $db->query("SELECT COUNT(*) FROM pets WHERE status = 'Available'")->fetchColumn();
$totalAdopted = $db->query("SELECT COUNT(*) FROM pets WHERE status = 'Adopted'")->fetchColumn();
$totalShelters = $db->query("SELECT COUNT(*) FROM shelters")->fetchColumn();

// Fetch categories with pet count
$categories = $db->query("
    SELECT c.*, COUNT(p.id) as pets_count 
    FROM categories c 
    LEFT JOIN pets p ON c.id = p.category_id AND p.status = 'Available'
    GROUP BY c.id
    ORDER BY c.id ASC
")->fetchAll();

// Fetch featured pets
$featuredPets = $db->query("
    SELECT p.*, c.name as category_name, s.name as shelter_name, s.city as shelter_city
    FROM pets p
    JOIN categories c ON p.category_id = c.id
    LEFT JOIN shelters s ON p.shelter_id = s.id
    WHERE p.status = 'Available'
    ORDER BY p.featured DESC, p.created_at DESC
    LIMIT 6
")->fetchAll();
?>

<!-- Hero Section -->
<section class="hero-section">
    <div class="container">
        <div class="row align-items-center g-5">
            <div class="col-lg-7">
                <div class="hero-badge">
                    <i class="bi bi-patch-check-fill text-danger"></i> Verified Rescue Network
                </div>
                <h1 class="hero-title">
                    Every Pet Deserves A <span style="color: var(--paw-primary);">Loving Forever Home</span>
                </h1>
                <p class="hero-subtitle">
                    PawConnect connects compassionate adopters with local animal shelters. Discover rescued dogs, cats, rabbits, and birds, and track your adoption journey seamlessly from application to homecoming.
                </p>
                <div class="d-flex flex-wrap gap-3">
                    <a href="/pets.php" class="btn btn-paw-primary btn-lg">
                        <i class="bi bi-search-heart me-2"></i> Find Your Pet
                    </a>
                    <a href="#how-it-works" class="btn btn-paw-outline btn-lg">
                        <i class="bi bi-play-circle me-2"></i> How It Works
                    </a>
                </div>

                <!-- Stats summary badges -->
                <div class="row g-3 mt-4 pt-2">
                    <div class="col-auto">
                        <div class="d-flex align-items-center gap-2">
                            <span class="fs-3 fw-bold text-dark"><?= $totalPets ?>+</span>
                            <span class="small text-muted lh-1">Pets Waiting<br>For You</span>
                        </div>
                    </div>
                    <div class="col-auto border-start ps-3">
                        <div class="d-flex align-items-center gap-2">
                            <span class="fs-3 fw-bold text-dark"><?= $totalAdopted ?>+</span>
                            <span class="small text-muted lh-1">Happy Paws<br>Adopted</span>
                        </div>
                    </div>
                    <div class="col-auto border-start ps-3">
                        <div class="d-flex align-items-center gap-2">
                            <span class="fs-3 fw-bold text-dark"><?= $totalShelters ?>+</span>
                            <span class="small text-muted lh-1">Partner<br>Shelters</span>
                        </div>
                    </div>
                </div>
            </div>

            <!-- Hero Image Showcase -->
            <div class="col-lg-5">
                <div class="hero-img-box text-center">
                    <img src="https://images.unsplash.com/photo-1548199973-03cce0bbc87b?auto=format&fit=crop&w=900&q=80" alt="Happy adopted dog with companion">
                    <div class="hero-floating-card">
                        <div class="rounded-circle bg-success text-white p-2 d-flex align-items-center justify-content-center" style="width: 44px; height: 44px;">
                            <i class="bi bi-check-lg fs-5"></i>
                        </div>
                        <div class="text-start">
                            <div class="fw-bold small text-dark">Adoption Approved!</div>
                            <div class="text-muted" style="font-size: 0.78rem;">Bella found her home in Austin</div>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>
</section>

<!-- Quick Search Bar Component -->
<div class="container">
    <div class="quick-search-card">
        <form action="/pets.php" method="GET" class="row g-3 align-items-end">
            <div class="col-lg-4 col-md-6">
                <label class="form-label"><i class="bi bi-search me-1 text-primary"></i> Keyword / Breed</label>
                <input type="text" name="q" class="form-control" placeholder="e.g. Golden Retriever, Labrador, Milo...">
            </div>
            <div class="col-lg-3 col-md-6">
                <label class="form-label"><i class="bi bi-filter me-1 text-primary"></i> Species / Category</label>
                <select name="category" class="form-select">
                    <option value="">All Animals</option>
                    <?php foreach ($categories as $cat): ?>
                        <option value="<?= htmlspecialchars($cat['slug']) ?>"><?= htmlspecialchars($cat['name']) ?></option>
                    <?php endforeach; ?>
                </select>
            </div>
            <div class="col-lg-3 col-md-6">
                <label class="form-label"><i class="bi bi-geo-alt me-1 text-primary"></i> Location / City</label>
                <input type="text" name="location" class="form-control" placeholder="e.g. Austin, San Francisco...">
            </div>
            <div class="col-lg-2 col-md-6">
                <button type="submit" class="btn btn-paw-primary w-100 py-2">
                    <i class="bi bi-arrow-right-circle me-1"></i> Search
                </button>
            </div>
        </form>
    </div>
</div>

<!-- Category Browsing Section -->
<section class="py-5 mt-4">
    <div class="container">
        <div class="text-center mb-4">
            <span class="badge bg-light text-primary px-3 py-2 rounded-pill fw-bold">Explore By Species</span>
            <h2 class="mt-2">Who are you looking for?</h2>
            <p class="text-muted">Explore loving companions waiting across our shelter network.</p>
        </div>
        <div class="row g-4">
            <?php foreach ($categories as $cat): ?>
                <div class="col-lg-3 col-sm-6">
                    <a href="/pets.php?category=<?= urlencode($cat['slug']) ?>" class="category-card">
                        <div class="category-icon">
                            <i class="bi <?= htmlspecialchars($cat['icon']) ?>"></i>
                        </div>
                        <h5 class="mb-1 text-dark"><?= htmlspecialchars($cat['name']) ?></h5>
                        <p class="text-muted small mb-0"><?= (int)$cat['pets_count'] ?> available for adoption</p>
                    </a>
                </div>
            <?php endforeach; ?>
        </div>
    </div>
</section>

<!-- Featured Pets Section -->
<section class="py-5 bg-white border-top border-bottom">
    <div class="container">
        <div class="d-flex flex-wrap justify-content-between align-items-end mb-4">
            <div>
                <span class="badge bg-light text-primary px-3 py-2 rounded-pill fw-bold">Featured Friends</span>
                <h2 class="mt-2 mb-0">Pets Ready For Adoption</h2>
            </div>
            <a href="/pets.php" class="btn btn-paw-outline mt-2 mt-md-0">
                View All Pets <i class="bi bi-arrow-right ms-1"></i>
            </a>
        </div>

        <div class="row g-4">
            <?php if (empty($featuredPets)): ?>
                <div class="col-12 text-center py-5">
                    <i class="bi bi-emoji-smile text-muted fs-1"></i>
                    <p class="mt-3 text-muted">All our furry friends have found homes! Check back soon for new arrivals.</p>
                </div>
            <?php else: ?>
                <?php foreach ($featuredPets as $pet): 
                    $isFav = is_favorite($db, current_user()['id'] ?? null, $pet['id']);
                ?>
                    <div class="col-lg-4 col-md-6">
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
            <?php endif; ?>
        </div>
    </div>
</section>

<!-- System Workflow Section (Matching User's Prompt Specification) -->
<section id="how-it-works" class="py-5">
    <div class="container">
        <div class="text-center max-w-700 mx-auto mb-5">
            <span class="badge bg-light text-primary px-3 py-2 rounded-pill fw-bold">System Workflow</span>
            <h2 class="mt-2">How PawConnect Works</h2>
            <p class="text-muted">A streamlined, transparent pet adoption journey designed for adopters and shelters.</p>
        </div>

        <div class="row g-4">
            <!-- Step 1 -->
            <div class="col-lg-3 col-md-6">
                <div class="workflow-step bg-white rounded-4 p-4 shadow-sm border h-100">
                    <div class="step-num">1</div>
                    <h5 class="step-title">Register & Login</h5>
                    <p class="step-desc">
                        Create your verified adopter profile in less than a minute. Securely save your personal and household details for simple applications.
                    </p>
                </div>
            </div>

            <!-- Step 2 -->
            <div class="col-lg-3 col-md-6">
                <div class="workflow-step bg-white rounded-4 p-4 shadow-sm border h-100">
                    <div class="step-num">2</div>
                    <h5 class="step-title">Search & View Pets</h5>
                    <p class="step-desc">
                        Filter pets by species, breed, age, temperament, and health records. Explore full photo profiles and rescue background stories.
                    </p>
                </div>
            </div>

            <!-- Step 3 -->
            <div class="col-lg-3 col-md-6">
                <div class="workflow-step bg-white rounded-4 p-4 shadow-sm border h-100">
                    <div class="step-num">3</div>
                    <h5 class="step-title">Apply For Adoption</h5>
                    <p class="step-desc">
                        Fill out our structured adoption application outlining your living environment, pet experience, and veterinarian references.
                    </p>
                </div>
            </div>

            <!-- Step 4 -->
            <div class="col-lg-3 col-md-6">
                <div class="workflow-step bg-white rounded-4 p-4 shadow-sm border h-100">
                    <div class="step-num">4</div>
                    <h5 class="step-title">Review & Status</h5>
                    <p class="step-desc">
                        Shelter staff evaluate your request in real time. Track your application status live (Under Review &rarr; Approved) and bring your pet home!
                    </p>
                </div>
            </div>
        </div>
    </div>
</section>

<!-- Why Choose PawConnect -->
<section class="py-5 bg-white border-top">
    <div class="container">
        <div class="row align-items-center g-5">
            <div class="col-lg-6">
                <img src="https://images.unsplash.com/photo-1548767797-d8c844163c4c?auto=format&fit=crop&w=900&q=80" alt="Veterinarian with pet" class="img-fluid rounded-4 shadow">
            </div>
            <div class="col-lg-6">
                <span class="badge bg-light text-primary px-3 py-2 rounded-pill fw-bold">Our Commitment</span>
                <h2 class="mt-2 mb-4">Why Adopt Through PawConnect?</h2>

                <div class="d-flex gap-3 mb-4">
                    <div class="rounded-circle bg-primary-subtle text-primary p-3 d-flex align-items-center justify-content-center" style="width: 50px; height: 50px; flex-shrink: 0;">
                        <i class="bi bi-shield-check fs-4"></i>
                    </div>
                    <div>
                        <h5 class="mb-1">100% Medically Checked Pets</h5>
                        <p class="text-muted small mb-0">All pets listed through partner shelters receive vaccinations, health screening, and spay/neuter surgeries prior to adoption.</p>
                    </div>
                </div>

                <div class="d-flex gap-3 mb-4">
                    <div class="rounded-circle bg-success-subtle text-success p-3 d-flex align-items-center justify-content-center" style="width: 50px; height: 50px; flex-shrink: 0;">
                        <i class="bi bi-eye fs-4"></i>
                    </div>
                    <div>
                        <h5 class="mb-1">Transparent Live Status Updates</h5>
                        <p class="text-muted small mb-0">Never wonder what happened to your application. Our digital tracking gives you real-time updates from shelter reviews.</p>
                    </div>
                </div>

                <div class="d-flex gap-3">
                    <div class="rounded-circle bg-warning-subtle text-warning p-3 d-flex align-items-center justify-content-center" style="width: 50px; height: 50px; flex-shrink: 0;">
                        <i class="bi bi-chat-heart fs-4"></i>
                    </div>
                    <div>
                        <h5 class="mb-1">Lifetime Post-Adoption Support</h5>
                        <p class="text-muted small mb-0">Our shelters and animal specialists provide guidance on settling your new pet into their home environment.</p>
                    </div>
                </div>
            </div>
        </div>
    </div>
</section>

<!-- Call to Action Banner -->
<section class="py-5" style="background: linear-gradient(135deg, var(--paw-primary), #ff8c69);">
    <div class="container text-center text-white py-4">
        <h2 class="text-white fw-bold display-6 mb-3">Ready to Make A Difference in an Animal's Life?</h2>
        <p class="lead mb-4 opacity-90 max-w-600 mx-auto">Discover loyal companions eagerly awaiting a family like yours. Start your adoption application today.</p>
        <div class="d-flex justify-content-center gap-3">
            <a href="/pets.php" class="btn btn-light text-dark btn-lg fw-bold px-4">
                <i class="bi bi-paw me-2"></i> Browse All Pets
            </a>
            <a href="/register.php" class="btn btn-outline-light btn-lg fw-bold px-4">
                <i class="bi bi-person-plus me-2"></i> Create Free Account
            </a>
        </div>
    </div>
</section>

<?php include __DIR__ . '/includes/footer.php'; ?>
