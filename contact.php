<?php
/**
 * PawConnect - Contact Us & Shelter Network Directory
 */
$pageTitle = "Contact Shelters & Support";
require_once __DIR__ . '/includes/header.php';

$db = getDB();
$shelters = $db->query("SELECT * FROM shelters ORDER BY id ASC")->fetchAll();

$errors = [];
$success = false;

$defaultSubject = trim($_GET['subject'] ?? '');

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    if (!verify_csrf($_POST['csrf_token'] ?? '')) {
        $errors[] = 'Invalid security token.';
    }

    $name = sanitize($_POST['name'] ?? '');
    $email = sanitize($_POST['email'] ?? '');
    $subject = sanitize($_POST['subject'] ?? '');
    $message = sanitize($_POST['message'] ?? '');

    if (empty($name)) $errors[] = 'Name is required.';
    if (empty($email) || !filter_var($email, FILTER_VALIDATE_EMAIL)) $errors[] = 'Valid email is required.';
    if (empty($subject)) $errors[] = 'Subject is required.';
    if (empty($message)) $errors[] = 'Message is required.';

    if (empty($errors)) {
        try {
            $stmt = $db->prepare("INSERT INTO contact_messages (name, email, subject, message) VALUES (?, ?, ?, ?)");
            $stmt->execute([$name, $email, $subject, $message]);
            set_flash('success', 'Thank you! Your message has been sent to our adoption team. We will respond within 24 hours.');
            header("Location: /contact.php");
            exit;
        } catch (Exception $e) {
            $errors[] = 'Failed to submit message: ' . $e->getMessage();
        }
    }
}
?>

<div class="container py-5">
    <div class="text-center max-w-700 mx-auto mb-5">
        <span class="badge bg-light text-primary px-3 py-2 rounded-pill fw-bold">Get In Touch</span>
        <h1 class="display-5 fw-bold mt-2">We Are Here For You & The Animals</h1>
        <p class="text-muted">Have a question about a specific pet, adoption procedures, or volunteer opportunities? Send us a note below.</p>
    </div>

    <div class="row g-5">
        <!-- Contact Form -->
        <div class="col-lg-7">
            <div class="form-card">
                <h4 class="fw-bold mb-3"><i class="bi bi-chat-heart text-primary me-2"></i> Send An Inquiry</h4>

                <?php if (!empty($errors)): ?>
                    <div class="alert alert-danger shadow-sm mb-4">
                        <ul class="mb-0 ps-3">
                            <?php foreach ($errors as $err): ?>
                                <li><?= htmlspecialchars($err) ?></li>
                            <?php endforeach; ?>
                        </ul>
                    </div>
                <?php endif; ?>

                <form action="/contact.php" method="POST">
                    <input type="hidden" name="csrf_token" value="<?= csrf_token() ?>">

                    <div class="row g-3">
                        <div class="col-md-6">
                            <label class="form-label">Your Name <span class="text-danger">*</span></label>
                            <input type="text" name="name" value="<?= htmlspecialchars($_POST['name'] ?? current_user()['name'] ?? '') ?>" class="form-control" required>
                        </div>
                        <div class="col-md-6">
                            <label class="form-label">Email Address <span class="text-danger">*</span></label>
                            <input type="email" name="email" value="<?= htmlspecialchars($_POST['email'] ?? current_user()['email'] ?? '') ?>" class="form-control" required>
                        </div>
                        <div class="col-12">
                            <label class="form-label">Subject <span class="text-danger">*</span></label>
                            <input type="text" name="subject" value="<?= htmlspecialchars($_POST['subject'] ?? $defaultSubject) ?>" class="form-control" placeholder="e.g. Question regarding Bella's routine" required>
                        </div>
                        <div class="col-12">
                            <label class="form-label">Message <span class="text-danger">*</span></label>
                            <textarea name="message" rows="5" class="form-control" placeholder="Type your inquiry or message here..." required><?= htmlspecialchars($_POST['message'] ?? '') ?></textarea>
                        </div>
                    </div>

                    <button type="submit" class="btn btn-paw-primary btn-lg mt-4 w-100">
                        <i class="bi bi-send-fill me-2"></i> Send Message
                    </button>
                </form>
            </div>
        </div>

        <!-- Shelter Directory & Office Details -->
        <div class="col-lg-5">
            <div class="bg-white rounded-4 border shadow-sm p-4 mb-4">
                <h5 class="fw-bold mb-3"><i class="bi bi-building me-2 text-primary"></i> Partner Shelter Locations</h5>
                <div class="d-flex flex-column gap-3">
                    <?php foreach ($shelters as $s): ?>
                        <div class="p-3 bg-light rounded-3 border">
                            <h6 class="fw-bold text-dark mb-1"><?= htmlspecialchars($s['name']) ?></h6>
                            <p class="small text-muted mb-1"><i class="bi bi-geo-alt me-1 text-danger"></i> <?= htmlspecialchars($s['address']) ?>, <?= htmlspecialchars($s['city']) ?></p>
                            <p class="small text-muted mb-1"><i class="bi bi-telephone me-1 text-success"></i> <?= htmlspecialchars($s['phone']) ?></p>
                            <p class="small text-muted mb-0"><i class="bi bi-envelope me-1 text-primary"></i> <?= htmlspecialchars($s['email']) ?></p>
                        </div>
                    <?php endforeach; ?>
                </div>
            </div>

            <!-- Emergency rescue card -->
            <div class="p-4 rounded-4 bg-danger-subtle border border-danger-subtle text-danger-emphasis">
                <h6 class="fw-bold mb-2"><i class="bi bi-exclamation-triangle-fill me-2"></i> Found an Animal in Distress?</h6>
                <p class="small mb-0">
                    If you have located an injured or stray animal requiring urgent medical rescue, please call our 24/7 emergency rescue hotline immediately at <strong>(555) 911-PAWS</strong>.
                </p>
            </div>
        </div>
    </div>
</div>

<?php include __DIR__ . '/includes/footer.php'; ?>
