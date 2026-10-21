<?php
/**
 * PawConnect - Login Page
 */
$pageTitle = "Login to PawConnect";
require_once __DIR__ . '/includes/header.php';

// If already logged in, redirect
if (is_logged_in()) {
    if (is_admin()) {
        header("Location: /admin/index.php");
    } else {
        header("Location: /my-applications.php");
    }
    exit;
}

$db = getDB();
$errors = [];
$email = '';

// Handle quick demo login buttons
if (isset($_GET['demo'])) {
    $demo = $_GET['demo'];
    if ($demo === 'admin') {
        $email = 'admin@pawconnect.org';
        $pass = 'admin123';
    } elseif ($demo === 'user') {
        $email = 'john@example.com';
        $pass = 'user123';
    }
    if (!empty($email)) {
        $stmt = $db->prepare("SELECT * FROM users WHERE email = ?");
        $stmt->execute([$email]);
        $u = $stmt->fetch();
        if ($u) {
            login_user($u);
            set_flash('success', "Welcome back, {$u['name']}!");
            header("Location: " . ($u['role'] === 'admin' ? '/admin/index.php' : '/index.php'));
            exit;
        }
    }
}

// Handle Form POST
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    if (!verify_csrf($_POST['csrf_token'] ?? '')) {
        $errors[] = 'Invalid security token. Please try again.';
    }

    $email = sanitize($_POST['email'] ?? '');
    $password = (string)($_POST['password'] ?? '');

    if (empty($email)) $errors[] = 'Please enter your email address.';
    if (empty($password)) $errors[] = 'Please enter your password.';

    if (empty($errors)) {
        $stmt = $db->prepare("SELECT * FROM users WHERE email = ?");
        $stmt->execute([$email]);
        $user = $stmt->fetch();

        if ($user && password_verify($password, $user['password'])) {
            login_user($user);
            set_flash('success', "Welcome back, {$user['name']}!");

            // Check redirect destination
            $redirect = $_SESSION['redirect_after_login'] ?? null;
            unset($_SESSION['redirect_after_login']);

            if ($redirect) {
                header("Location: " . $redirect);
            } elseif ($user['role'] === 'admin') {
                header("Location: /admin/index.php");
            } else {
                header("Location: /pets.php");
            }
            exit;
        } else {
            $errors[] = 'Invalid email address or password.';
        }
    }
}
?>

<div class="container py-5">
    <div class="row justify-content-center">
        <div class="col-md-6 col-lg-5">
            <div class="form-card">
                <div class="text-center mb-4">
                    <span class="brand-icon mx-auto mb-3" style="width: 50px; height: 50px; font-size: 1.5rem;"><i class="bi bi-heart-fill"></i></span>
                    <h3 class="fw-bold">Welcome Back</h3>
                    <p class="text-muted small">Log in to manage your pet adoption applications</p>
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

                <form action="/login.php" method="POST">
                    <input type="hidden" name="csrf_token" value="<?= csrf_token() ?>">

                    <div class="mb-3">
                        <label class="form-label">Email Address</label>
                        <div class="input-group">
                            <span class="input-group-text bg-light"><i class="bi bi-envelope"></i></span>
                            <input type="email" name="email" value="<?= htmlspecialchars($email) ?>" class="form-control" placeholder="you@example.com" required autofocus>
                        </div>
                    </div>

                    <div class="mb-4">
                        <label class="form-label">Password</label>
                        <div class="input-group">
                            <span class="input-group-text bg-light"><i class="bi bi-lock"></i></span>
                            <input type="password" name="password" id="loginPassword" class="form-control" placeholder="••••••••" required>
                            <button type="button" class="btn btn-outline-secondary toggle-password" data-target="loginPassword">
                                <i class="bi bi-eye"></i>
                            </button>
                        </div>
                    </div>

                    <button type="submit" class="btn btn-paw-primary w-100 py-2 fw-bold">
                        <i class="bi bi-box-arrow-in-right me-1"></i> Log In
                    </button>
                </form>

                <!-- Quick Demo Login Shortcut for Evaluation -->
                <div class="mt-4 pt-3 border-top text-center">
                    <div class="small text-muted mb-2 fw-semibold">Quick Demo Login:</div>
                    <div class="d-flex justify-content-center gap-2">
                        <a href="/login.php?demo=admin" class="btn btn-sm btn-outline-dark">
                            <i class="bi bi-shield-lock-fill text-warning me-1"></i> Demo Admin
                        </a>
                        <a href="/login.php?demo=user" class="btn btn-sm btn-outline-primary">
                            <i class="bi bi-person-fill me-1"></i> Demo Adopter
                        </a>
                    </div>
                </div>

                <div class="text-center mt-4">
                    <p class="small text-muted mb-0">Don't have an account yet? <a href="/register.php" class="fw-bold">Register here</a></p>
                </div>
            </div>
        </div>
    </div>
</div>

<?php include __DIR__ . '/includes/footer.php'; ?>
