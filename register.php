<?php
/**
 * PawConnect - User Registration
 */
$pageTitle = "Create an Account";
require_once __DIR__ . '/includes/header.php';

// If already logged in, redirect
if (is_logged_in()) {
    header("Location: /pets.php");
    exit;
}

$db = getDB();
$errors = [];
$name = '';
$email = '';
$phone = '';
$city = '';

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    if (!verify_csrf($_POST['csrf_token'] ?? '')) {
        $errors[] = 'Invalid security token. Please try again.';
    }

    $name = sanitize($_POST['name'] ?? '');
    $email = sanitize($_POST['email'] ?? '');
    $phone = sanitize($_POST['phone'] ?? '');
    $city = sanitize($_POST['city'] ?? '');
    $password = (string)($_POST['password'] ?? '');
    $confirmPassword = (string)($_POST['confirm_password'] ?? '');

    if (empty($name)) $errors[] = 'Full name is required.';
    if (empty($email) || !filter_var($email, FILTER_VALIDATE_EMAIL)) $errors[] = 'A valid email address is required.';
    if (strlen($password) < 6) $errors[] = 'Password must be at least 6 characters long.';
    if ($password !== $confirmPassword) $errors[] = 'Passwords do not match.';

    if (empty($errors)) {
        // Check if email already registered
        $stmt = $db->prepare("SELECT id FROM users WHERE email = ?");
        $stmt->execute([$email]);
        if ($stmt->fetch()) {
            $errors[] = 'An account with this email address already exists. Please log in instead.';
        } else {
            try {
                $hash = password_hash($password, PASSWORD_BCRYPT);
                $ins = $db->prepare("INSERT INTO users (name, email, password, phone, city, role) VALUES (?, ?, ?, ?, ?, 'user')");
                $ins->execute([$name, $email, $hash, $phone, $city]);

                $newId = $db->lastInsertId();

                // Automatically log user in
                login_user([
                    'id' => $newId,
                    'name' => $name,
                    'email' => $email,
                    'role' => 'user'
                ]);

                set_flash('success', "🎉 Welcome to PawConnect, {$name}! Your account has been created.");
                header("Location: /pets.php");
                exit;

            } catch (Exception $e) {
                $errors[] = 'Registration failed: ' . $e->getMessage();
            }
        }
    }
}
?>

<div class="container py-5">
    <div class="row justify-content-center">
        <div class="col-md-7 col-lg-6">
            <div class="form-card">
                <div class="text-center mb-4">
                    <span class="brand-icon mx-auto mb-3" style="width: 50px; height: 50px; font-size: 1.5rem;"><i class="bi bi-heart-fill"></i></span>
                    <h3 class="fw-bold">Join PawConnect</h3>
                    <p class="text-muted small">Register to discover pets and apply for adoptions</p>
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

                <form action="/register.php" method="POST">
                    <input type="hidden" name="csrf_token" value="<?= csrf_token() ?>">

                    <div class="mb-3">
                        <label class="form-label">Full Name <span class="text-danger">*</span></label>
                        <div class="input-group">
                            <span class="input-group-text bg-light"><i class="bi bi-person"></i></span>
                            <input type="text" name="name" value="<?= htmlspecialchars($name) ?>" class="form-control" placeholder="e.g. Jane Doe" required>
                        </div>
                    </div>

                    <div class="mb-3">
                        <label class="form-label">Email Address <span class="text-danger">*</span></label>
                        <div class="input-group">
                            <span class="input-group-text bg-light"><i class="bi bi-envelope"></i></span>
                            <input type="email" name="email" value="<?= htmlspecialchars($email) ?>" class="form-control" placeholder="you@example.com" required>
                        </div>
                    </div>

                    <div class="row g-3 mb-3">
                        <div class="col-md-6">
                            <label class="form-label">Phone Number</label>
                            <input type="text" name="phone" value="<?= htmlspecialchars($phone) ?>" class="form-control" placeholder="(555) 000-0000">
                        </div>
                        <div class="col-md-6">
                            <label class="form-label">City / Town</label>
                            <input type="text" name="city" value="<?= htmlspecialchars($city) ?>" class="form-control" placeholder="e.g. Austin">
                        </div>
                    </div>

                    <div class="row g-3 mb-4">
                        <div class="col-md-6">
                            <label class="form-label">Password <span class="text-danger">*</span></label>
                            <input type="password" name="password" class="form-control" placeholder="At least 6 chars" required>
                        </div>
                        <div class="col-md-6">
                            <label class="form-label">Confirm Password <span class="text-danger">*</span></label>
                            <input type="password" name="confirm_password" class="form-control" placeholder="Repeat password" required>
                        </div>
                    </div>

                    <button type="submit" class="btn btn-paw-primary w-100 py-2 fw-bold">
                        <i class="bi bi-person-check-fill me-1"></i> Create Account
                    </button>
                </form>

                <div class="text-center mt-4 pt-3 border-top">
                    <p class="small text-muted mb-0">Already have an account? <a href="/login.php" class="fw-bold">Log in here</a></p>
                </div>
            </div>
        </div>
    </div>
</div>

<?php include __DIR__ . '/includes/footer.php'; ?>
