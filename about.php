<?php
/**
 * PawConnect - About Page
 */
$pageTitle = "About Us & Adoption Workflow";
require_once __DIR__ . '/includes/header.php';
?>

<div class="container py-5">
    <!-- Hero / Mission -->
    <div class="row align-items-center g-5 mb-5">
        <div class="col-lg-6">
            <span class="badge bg-light text-primary px-3 py-2 rounded-pill fw-bold">Our Mission</span>
            <h1 class="display-5 fw-bold mt-2 mb-3">Connecting Loving Homes With Pets In Need</h1>
            <p class="lead text-secondary">
                PawConnect is built on a simple belief: every animal deserves a safe, compassionate, and permanent home. We bridge the gap between animal rescue organizations and hopeful adopters through technology.
            </p>
            <p class="text-muted">
                Traditional adoption processes are often fragmented, paper-heavy, and lack communication. PawConnect organizes pet information, simplifies discovery, and provides transparent, real-time application tracking so you always know where you stand.
            </p>
            <div class="d-flex gap-3 mt-4">
                <a href="/pets.php" class="btn btn-paw-primary">
                    <i class="bi bi-search-heart me-1"></i> Meet Our Pets
                </a>
                <a href="/contact.php" class="btn btn-outline-secondary">
                    <i class="bi bi-envelope me-1"></i> Contact Shelters
                </a>
            </div>
        </div>
        <div class="col-lg-6">
            <img src="https://images.unsplash.com/photo-1548767797-d8c844163c4c?auto=format&fit=crop&w=900&q=80" alt="Veterinarian holding smiling dog" class="img-fluid rounded-4 shadow">
        </div>
    </div>

    <!-- The 4-Stage Adoption Workflow (User Prompt Concept) -->
    <div class="bg-white rounded-4 border shadow-sm p-4 p-md-5 mb-5">
        <div class="text-center max-w-700 mx-auto mb-5">
            <span class="badge bg-light text-primary px-3 py-2 rounded-pill fw-bold">System Workflow</span>
            <h2 class="fw-bold mt-2">How The PawConnect Workflow Operates</h2>
            <p class="text-muted">From the moment you create your profile to bringing your companion home.</p>
        </div>

        <div class="row g-4">
            <div class="col-md-6 col-lg-3">
                <div class="p-3 text-center">
                    <div class="rounded-circle bg-primary-subtle text-primary d-inline-flex align-items-center justify-content-center mb-3" style="width: 60px; height: 60px; font-size: 1.5rem;">
                        <i class="bi bi-person-check-fill"></i>
                    </div>
                    <h5 class="fw-bold">1. Register & Login</h5>
                    <p class="text-muted small">
                        Adopters set up an account with housing and contact information. Your details are securely preserved for easy applications.
                    </p>
                </div>
            </div>

            <div class="col-md-6 col-lg-3">
                <div class="p-3 text-center">
                    <div class="rounded-circle bg-warning-subtle text-warning-emphasis d-inline-flex align-items-center justify-content-center mb-3" style="width: 60px; height: 60px; font-size: 1.5rem;">
                        <i class="bi bi-search"></i>
                    </div>
                    <h5 class="fw-bold">2. Search & View Details</h5>
                    <p class="text-muted small">
                        Explore animals by species, breed, age, size, and temperament. Review health records, vaccination status, and shelter bios.
                    </p>
                </div>
            </div>

            <div class="col-md-6 col-lg-3">
                <div class="p-3 text-center">
                    <div class="rounded-circle bg-info-subtle text-info-emphasis d-inline-flex align-items-center justify-content-center mb-3" style="width: 60px; height: 60px; font-size: 1.5rem;">
                        <i class="bi bi-file-earmark-text"></i>
                    </div>
                    <h5 class="fw-bold">3. Apply for Adoption</h5>
                    <p class="text-muted small">
                        Complete a structured application covering living space, yard fencing, daily care routine, vet history, and your reason for adopting.
                    </p>
                </div>
            </div>

            <div class="col-md-6 col-lg-3">
                <div class="p-3 text-center">
                    <div class="rounded-circle bg-success-subtle text-success d-inline-flex align-items-center justify-content-center mb-3" style="width: 60px; height: 60px; font-size: 1.5rem;">
                        <i class="bi bi-award-fill"></i>
                    </div>
                    <h5 class="fw-bold">4. Review & Status</h5>
                    <p class="text-muted small">
                        Shelters review submissions with direct feedback. Track each milestone (Pending &rarr; Under Review &rarr; Approved) on your dashboard!
                    </p>
                </div>
            </div>
        </div>
    </div>

    <!-- Frequently Asked Questions -->
    <div class="max-w-800 mx-auto">
        <div class="text-center mb-4">
            <span class="badge bg-light text-primary px-3 py-2 rounded-pill fw-bold">FAQ</span>
            <h2 class="fw-bold mt-2">Frequently Asked Questions</h2>
        </div>

        <div class="accordion shadow-sm rounded-4 overflow-hidden" id="faqAccordion">
            <div class="accordion-item">
                <h2 class="accordion-header" id="headingOne">
                    <button class="accordion-button fw-bold" type="button" data-bs-toggle="collapse" data-bs-target="#collapseOne" aria-expanded="true" aria-controls="collapseOne">
                        What are the adoption fees and what do they cover?
                    </button>
                </h2>
                <div id="collapseOne" class="accordion-collapse collapse show" aria-labelledby="headingOne" data-bs-parent="#faqAccordion">
                    <div class="accordion-body text-secondary small">
                        Adoption fees range between $35 and $200 depending on the animal's species, age, and medical care requirements. 100% of the adoption fee goes towards veterinary checkups, core vaccinations, spay/neuter surgery, deworming, microchipping, and daily food and shelter upkeep.
                    </div>
                </div>
            </div>

            <div class="accordion-item">
                <h2 class="accordion-header" id="headingTwo">
                    <button class="accordion-button collapsed fw-bold" type="button" data-bs-toggle="collapse" data-bs-target="#collapseTwo" aria-expanded="false" aria-controls="collapseTwo">
                        How long does application review take?
                    </button>
                </h2>
                <div id="collapseTwo" class="accordion-collapse collapse" aria-labelledby="headingTwo" data-bs-parent="#faqAccordion">
                    <div class="accordion-body text-secondary small">
                        Most shelters process applications within 24 to 72 hours. You can monitor progress on your <strong>My Applications</strong> screen, where shelter coordinators provide status updates and contact you to schedule an introduction.
                    </div>
                </div>
            </div>

            <div class="accordion-item">
                <h2 class="accordion-header" id="headingThree">
                    <button class="accordion-button collapsed fw-bold" type="button" data-bs-toggle="collapse" data-bs-target="#collapseThree" aria-expanded="false" aria-controls="collapseThree">
                        Can renters adopt pets through PawConnect?
                    </button>
                </h2>
                <div id="collapseThree" class="accordion-collapse collapse" aria-labelledby="headingThree" data-bs-parent="#faqAccordion">
                    <div class="accordion-body text-secondary small">
                        Absolutely! Renters are warmly welcomed to adopt. We simply ask that you verify with your landlord that your lease permits pets and complies with any building breed or weight restrictions.
                    </div>
                </div>
            </div>

            <div class="accordion-item">
                <h2 class="accordion-header" id="headingFour">
                    <button class="accordion-button collapsed fw-bold" type="button" data-bs-toggle="collapse" data-bs-target="#collapseFour" aria-expanded="false" aria-controls="collapseFour">
                        Can I meet the pet in person before completing the adoption?
                    </button>
                </h2>
                <div id="collapseFour" class="accordion-collapse collapse" aria-labelledby="headingFour" data-bs-parent="#faqAccordion">
                    <div class="accordion-body text-secondary small">
                        Yes! In fact, shelters encourage in-person meet-and-greets so family members and existing pets can interact with the animal in a safe, guided setting before final homecoming.
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>

<?php include __DIR__ . '/includes/footer.php'; ?>
