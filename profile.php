<?php
/**
 * PawConnect - User Profile & Account Settings
 */
$pageTitle = "My Profile";
require_once __DIR__ . '/includes/header.php';

require_login();
$user = current_user();
$db = getDB();

$stmt = $db->prepare("SELECT * FROM users WHERE id = ?");
$stmt->execute([$user['id']]);
$userData = $stmt->fetch();

$errors = [];
$successMsg = '';

// Handle Profile Details Update
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['action']) && $_POST['action'] === 'update_profile') {
    if (!verify_csrf($_POST['csrf_token'] ?? '')) {
        $errors[] = 'Invalid security token.';
    }

    $name = sanitize($_POST['name'] ?? '');
    $phone = sanitize($_POST['phone'] ?? '');
    $address = sanitize($_POST['address'] ?? '');
    $city = sanitize($_POST['city'] ?? '');

    if (empty($name)) $errors[] = 'Name cannot be empty.';

    if (empty($errors)) {
        $up = $db->prepare("UPDATE users SET name = ?, phone = ?, address = ?, city = ? WHERE id = ?");
        $up->execute([$name, $phone, $address, $city, $user['id']]);
        $_SESSION['user_name'] = $name;
        $userData['name'] = $name;
        $userData['phone'] = $phone;
        $userData['address'] = $address;
        $userData['city'] = $city;
        set_flash('success', 'Profile updated successfully.');
        header('Location: /profile.php');
        exit;
    }
}

// Handle Password Change
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['action']) && $_POST['action'] === 'change_password') {
    if (!verify_csrf($_POST['csrf_token'] ?? '')) {
        $errors[] = 'Invalid security token.';
    }

    $currentPass = (string)($_POST['current_password'] ?? '');
    $newPass = (string)($_POST['new_password'] ?? '');
    $confirmPass = (string)($_POST['confirm_password'] ?? '');

    if (!password_verify($currentPass, $userData['password'])) {
        $errors[] = 'Current password does not match our records.';
    } elseif (strlen($newPass) < 6) {
        $errors[] = 'New password must be at least 6 characters.';
    } elseif ($newPass !== $confirmPass) {
        $errors[] = 'New passwords do not match.';
    } else {
        $newHash = password_hash($newPass, PASSWORD_BCRYPT);
        $upPass = $db->prepare("UPDATE users SET password = ? WHERE id = ?");
        $upPass->execute([$newHash, $user['id']]);
        set_flash('success', 'Password changed successfully!');
        header('Location: /profile.php');
        exit;
    }
}
?>

<div class="container py-4">
    <!-- Breadcrumbs -->
    <nav aria-label="breadcrumb" class="mb-4">
        <ol class="breadcrumb">
            <li class="breadcrumb-item"><a href="/index.php">Home</a></li>
            <li class="breadcrumb-item active" aria-current="page">My Profile</li>
        </ol>
    </nav>

    <div class="row g-4">
        <!-- Sidebar Summary -->
        <div class="col-lg-4">
            <div class="bg-white rounded-4 border p-4 shadow-sm text-center">
                <div class="rounded-circle bg-primary-subtle text-primary fw-bold d-inline-flex align-items-center justify-content-center mb-3" style="width: 80px; height: 80px; font-size: 2rem;">
                    <?= strtoupper(substr($userData['name'], 0, 1)) ?>
                </div>
                <h4 class="fw-bold mb-1"><?= htmlspecialchars($userData['name']) ?></h4>
                <p class="text-muted small mb-2"><?= htmlspecialchars($userData['email']) ?></p>
                <div>
                    <span class="badge <?= ($userData['role'] === 'admin') ? 'bg-danger' : 'bg-primary' ?> px-3 py-1 rounded-pill">
                        <?= ucfirst($userData['role']) ?> Account
                    </span>
                </div>

                <hr class="my-4">

                <div class="text-start small text-muted">
                    <p class="mb-2"><i class="bi bi-calendar3 me-2"></i> Member since: <strong class="text-dark"><?= format_date($userData['created_at']) ?></strong></p>
                    <p class="mb-0"><i class="bi bi-geo-alt me-2"></i> Location: <strong class="text-dark"><?= htmlspecialchars($userData['city'] ?? 'Not set') ?></strong></p>
                </div>
            </div>
        </div>

        <!-- Main Edit Forms -->
        <div class="col-lg-8">
            <?php if (!empty($errors)): ?>
                <div class="alert alert-danger shadow-sm mb-4">
                    <ul class="mb-0 ps-3">
                        <?php foreach ($errors as $err): ?>
                            <li><?= htmlspecialchars($err) ?></li>
                        <?php endforeach; ?>
                    </ul>
                </div>
            <?php endif; ?>

            <!-- Personal Info Card -->
            <div class="form-card mb-4">
                <h5 class="fw-bold mb-3"><i class="bi bi-person-gear text-primary me-2"></i> Personal Details</h5>
                <form action="/profile.php" method="POST">
                    <input type="hidden" name="csrf_token" value="<?= csrf_token() ?>">
                    <input type="hidden" name="action" value="update_profile">

                    <div class="row g-3">
                        <div class="col-md-6">
                            <label class="form-label">Full Name</label>
                            <input type="text" name="name" value="<?= htmlspecialchars($userData['name']) ?>" class="form-control" required>
                        </div>
                        <div class="col-md-6">
                            <label class="form-label">Email Address (Read-Only)</label>
                            <input type="email" value="<?= htmlspecialchars($userData['email']) ?>" class="form-control bg-light" readonly>
                        </div>
                        <div class="col-md-6">
                            <label class="form-label">Phone Number</label>
                            <input type="text" name="phone" value="<?= htmlspecialchars($userData['phone'] ?? '') ?>" class="form-control" placeholder="(555) 000-0000">
                        </div>
                        <div class="col-md-6">
                            <label class="form-label">City / Town</label>
                            <input type="text" name="city" value="<?= htmlspecialchars($userData['city'] ?? '') ?>" class="form-control" placeholder="e.g. Austin">
                        </div>
                        <div class="col-12">
                            <label class="form-label">Default Street Address</label>
                            <textarea name="address" class="form-control" rows="2" placeholder="Street address for adoption applications"><?= htmlspecialchars($userData['address'] ?? '') ?></textarea>
                        </div>
                    </div>

                    <div class="text-end mt-4">
                        <button type="submit" class="btn btn-paw-primary">
                            <i class="bi bi-check-lg me-1"></i> Save Changes
                        </button>
                    </div>
                </form>
            </div>

            <!-- Password Change Card -->
            <div class="form-card">
                <h5 class="fw-bold mb-3"><i class="bi bi-key-fill text-warning me-2"></i> Change Password</h5>
                <form action="/profile.php" method="POST">
                    <input type="hidden" name="csrf_token" value="<?= csrf_token() ?>">
                    <input type="hidden" name="action" value="change_password">

                    <div class="row g-3">
                        <div class="col-md-12">
                            <label class="form-label">Current Password</label>
                            <input type="password" name="current_password" class="form-control" required>
                        </div>
                        <div class="col-md-6">
                            <label class="form-label">New Password</label>
                            <input type="password" name="new_password" class="form-control" placeholder="At least 6 characters" required>
                        </div>
                        <div class="col-md-6">
                            <label class="form-label">Confirm New Password</label>
                            <input type="password" name="confirm_password" class="form-control" placeholder="Re-type new password" required>
                        </div>
                    </div>

                    <div class="text-end mt-4">
                        <button type="submit" class="btn btn-dark">
                            <i class="bi bi-shield-check me-1"></i> Update Password
                        </button>
                    </div>
                </form>
            </div>
        </div>
    </div>
</div>

<?php include __DIR__ . '/includes/footer.php'; ?>
