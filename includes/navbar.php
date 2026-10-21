<?php
/**
 * PawConnect - Navbar Include
 */
$currentUser = current_user();
$currentScript = basename($_SERVER['PHP_SELF']);

// Count user's pending/active applications if logged in
$activeAppsCount = 0;
if (is_logged_in() && !is_admin()) {
    try {
        $db = getDB();
        $stmt = $db->prepare("SELECT COUNT(*) FROM adoption_applications WHERE user_id = ? AND status IN ('Pending', 'Under Review')");
        $stmt->execute([$currentUser['id']]);
        $activeAppsCount = (int)$stmt->fetchColumn();
    } catch (Exception $e) {
        $activeAppsCount = 0;
    }
}
?>
<nav class="navbar navbar-expand-lg navbar-paw">
    <div class="container">
        <!-- Brand Logo -->
        <a class="navbar-brand" href="/index.php">
            <span class="brand-icon"><i class="bi bi-heart-fill"></i></span>
            <span>Paw<span style="color: var(--paw-primary);">Connect</span></span>
        </a>

        <!-- Mobile Toggle Button -->
        <button class="navbar-toggler border-0" type="button" data-bs-toggle="collapse" data-bs-target="#pawNavbar" aria-controls="pawNavbar" aria-expanded="false" aria-label="Toggle navigation">
            <span class="navbar-toggler-icon"></span>
        </button>

        <!-- Navbar Links -->
        <div class="collapse navbar-collapse" id="pawNavbar">
            <ul class="navbar-nav me-auto mb-2 mb-lg-0 ms-lg-3">
                <li class="nav-item">
                    <a class="nav-link <?= ($currentScript === 'index.php') ? 'active' : '' ?>" href="/index.php">
                        <i class="bi bi-house-door me-1"></i> Home
                    </a>
                </li>
                <li class="nav-item">
                    <a class="nav-link <?= ($currentScript === 'pets.php' || $currentScript === 'pet-details.php') ? 'active' : '' ?>" href="/pets.php">
                        <i class="bi bi-search-heart me-1"></i> Browse Pets
                    </a>
                </li>
                <li class="nav-item">
                    <a class="nav-link" href="/index.php#how-it-works">
                        <i class="bi bi-diagram-3 me-1"></i> How It Works
                    </a>
                </li>
                <li class="nav-item">
                    <a class="nav-link <?= ($currentScript === 'about.php') ? 'active' : '' ?>" href="/about.php">About</a>
                </li>
                <li class="nav-item">
                    <a class="nav-link <?= ($currentScript === 'contact.php') ? 'active' : '' ?>" href="/contact.php">Contact</a>
                </li>
            </ul>

            <!-- Right Side Auth Navigation -->
            <ul class="navbar-nav ms-auto align-items-lg-center gap-2">
                <?php if (!is_logged_in()): ?>
                    <li class="nav-item">
                        <a class="nav-link" href="/login.php">
                            <i class="bi bi-box-arrow-in-right me-1"></i> Log In
                        </a>
                    </li>
                    <li class="nav-item">
                        <a class="btn btn-paw-primary" href="/register.php">
                            <i class="bi bi-person-plus me-1"></i> Register
                        </a>
                    </li>
                <?php else: ?>
                    <?php if (is_admin()): ?>
                        <!-- Admin Links -->
                        <li class="nav-item dropdown">
                            <a class="btn btn-dark dropdown-toggle d-flex align-items-center gap-2" href="#" id="adminMenu" role="button" data-bs-toggle="dropdown" aria-expanded="false">
                                <i class="bi bi-shield-lock-fill text-warning"></i>
                                <span>Admin Portal</span>
                            </a>
                            <ul class="dropdown-menu dropdown-menu-end shadow-sm" aria-labelledby="adminMenu">
                                <li><a class="dropdown-item" href="/admin/index.php"><i class="bi bi-speedometer2 me-2"></i> Dashboard</a></li>
                                <li><a class="dropdown-item" href="/admin/pets.php"><i class="bi bi-heart me-2"></i> Manage Pets</a></li>
                                <li><a class="dropdown-item" href="/admin/pet-add.php"><i class="bi bi-plus-circle me-2"></i> Add New Pet</a></li>
                                <li><a class="dropdown-item" href="/admin/applications.php"><i class="bi bi-file-earmark-text me-2"></i> Adoption Requests</a></li>
                                <li><a class="dropdown-item" href="/admin/users.php"><i class="bi bi-people me-2"></i> Manage Users</a></li>
                                <li><hr class="dropdown-divider"></li>
                                <li><a class="dropdown-item text-danger" href="/logout.php"><i class="bi bi-box-arrow-right me-2"></i> Log Out</a></li>
                            </ul>
                        </li>
                    <?php else: ?>
                        <!-- User Links -->
                        <li class="nav-item">
                            <a class="nav-link position-relative <?= ($currentScript === 'my-applications.php') ? 'active' : '' ?>" href="/my-applications.php">
                                <i class="bi bi-file-earmark-check me-1"></i> My Applications
                                <?php if ($activeAppsCount > 0): ?>
                                    <span class="position-absolute top-1 start-100 translate-middle badge rounded-pill bg-danger">
                                        <?= $activeAppsCount ?>
                                    </span>
                                <?php endif; ?>
                            </a>
                        </li>
                        <li class="nav-item">
                            <a class="nav-link <?= ($currentScript === 'favorites.php') ? 'active' : '' ?>" href="/favorites.php" title="Saved Pets">
                                <i class="bi bi-heart me-1 text-danger"></i> Favorites
                            </a>
                        </li>
                    <?php endif; ?>

                    <!-- User Profile Dropdown -->
                    <li class="nav-item dropdown ms-lg-2">
                        <a class="nav-link dropdown-toggle d-flex align-items-center gap-2" href="#" id="userMenu" role="button" data-bs-toggle="dropdown" aria-expanded="false">
                            <div class="rounded-circle bg-light border d-inline-flex align-items-center justify-content-center text-secondary fw-bold" style="width: 34px; height: 34px;">
                                <?= strtoupper(substr($currentUser['name'], 0, 1)) ?>
                            </div>
                            <span class="fw-semibold text-truncate" style="max-width: 120px;"><?= htmlspecialchars($currentUser['name']) ?></span>
                        </a>
                        <ul class="dropdown-menu dropdown-menu-end shadow-sm" aria-labelledby="userMenu">
                            <li class="px-3 py-2 text-muted small border-bottom">
                                Signed in as<br>
                                <strong class="text-dark"><?= htmlspecialchars($currentUser['email']) ?></strong>
                            </li>
                            <li><a class="dropdown-item" href="/profile.php"><i class="bi bi-person me-2"></i> My Profile</a></li>
                            <?php if (!is_admin()): ?>
                                <li><a class="dropdown-item" href="/my-applications.php"><i class="bi bi-card-checklist me-2"></i> Track Applications</a></li>
                                <li><a class="dropdown-item" href="/favorites.php"><i class="bi bi-heart me-2 text-danger"></i> My Favorites</a></li>
                            <?php endif; ?>
                            <li><hr class="dropdown-divider"></li>
                            <li><a class="dropdown-item text-danger" href="/logout.php"><i class="bi bi-box-arrow-right me-2"></i> Log Out</a></li>
                        </ul>
                    </li>
                <?php endif; ?>
            </ul>
        </div>
    </div>
</nav>
