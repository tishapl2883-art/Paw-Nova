<?php
/**
 * PawConnect - Footer Include
 */
?>
</main>

<footer class="footer-paw">
    <div class="container">
        <div class="row g-4">
            <!-- Brand & Mission -->
            <div class="col-lg-4 col-md-6">
                <div class="d-flex align-items-center gap-2 mb-3">
                    <span class="brand-icon" style="width: 32px; height: 32px; font-size: 1rem;"><i class="bi bi-heart-fill"></i></span>
                    <h5 class="mb-0 text-white">PawConnect</h5>
                </div>
                <p class="small text-secondary">
                    PawConnect is dedicated to transforming pet adoption into a simple, transparent, and compassionate experience. We connect verified animal shelters with responsible adopters to give every pet the loving home they deserve.
                </p>
                <div class="d-flex gap-3 mt-3">
                    <a href="#" class="btn btn-sm btn-outline-secondary rounded-circle" style="width: 36px; height: 36px; display: inline-flex; align-items: center; justify-content: center;"><i class="bi bi-facebook"></i></a>
                    <a href="#" class="btn btn-sm btn-outline-secondary rounded-circle" style="width: 36px; height: 36px; display: inline-flex; align-items: center; justify-content: center;"><i class="bi bi-instagram"></i></a>
                    <a href="#" class="btn btn-sm btn-outline-secondary rounded-circle" style="width: 36px; height: 36px; display: inline-flex; align-items: center; justify-content: center;"><i class="bi bi-twitter-x"></i></a>
                </div>
            </div>

            <!-- Quick Navigation -->
            <div class="col-lg-2 col-md-6">
                <h5>Discover</h5>
                <ul class="list-unstyled small d-flex flex-column gap-2">
                    <li><a href="/pets.php">Browse All Pets</a></li>
                    <li><a href="/pets.php?category=dogs">Adopt a Dog</a></li>
                    <li><a href="/pets.php?category=cats">Adopt a Cat</a></li>
                    <li><a href="/pets.php?category=rabbits">Adopt a Rabbit</a></li>
                    <li><a href="/pets.php?category=birds">Adopt a Bird</a></li>
                </ul>
            </div>

            <!-- How It Works / Support -->
            <div class="col-lg-3 col-md-6">
                <h5>Adoption Guide</h5>
                <ul class="list-unstyled small d-flex flex-column gap-2">
                    <li><a href="/index.php#how-it-works">Adoption Process</a></li>
                    <li><a href="/about.php">Adoption Requirements</a></li>
                    <li><a href="/my-applications.php">Application Tracking</a></li>
                    <li><a href="/contact.php">Shelter Network</a></li>
                    <li><a href="/install.php">Database Status / Reset</a></li>
                </ul>
            </div>

            <!-- Contact & Shelter Info -->
            <div class="col-lg-3 col-md-6">
                <h5>Shelter Support</h5>
                <p class="small text-secondary mb-2">
                    <i class="bi bi-geo-alt-fill text-danger me-2"></i> 100 Animal Way, Austin, TX
                </p>
                <p class="small text-secondary mb-2">
                    <i class="bi bi-telephone-fill text-success me-2"></i> (555) 234-PAWS (7297)
                </p>
                <p class="small text-secondary mb-2">
                    <i class="bi bi-envelope-fill text-warning me-2"></i> support@pawconnect.org
                </p>
                <p class="small text-secondary">
                    <i class="bi bi-clock-fill text-info me-2"></i> Mon - Sat: 9:00 AM - 6:00 PM
                </p>
            </div>
        </div>

        <!-- Footer Bottom -->
        <div class="footer-bottom d-flex flex-wrap justify-content-between align-items-center">
            <p class="mb-0 text-secondary">&copy; <?= date('Y') ?> PawConnect. All rights reserved. Built with PHP, MySQL & Bootstrap 5.</p>
            <div class="d-flex gap-3">
                <a href="/about.php" class="small text-secondary">Privacy Policy</a>
                <a href="/about.php" class="small text-secondary">Terms of Service</a>
                <a href="/admin/index.php" class="small text-secondary">Admin Login</a>
            </div>
        </div>
    </div>
</footer>

<!-- Bootstrap 5.3 JS Bundle -->
<script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/js/bootstrap.bundle.min.js"></script>
<!-- PawConnect Custom JS -->
<script src="/assets/js/main.js"></script>
</body>
</html>
