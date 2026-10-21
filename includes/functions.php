<?php
/**
 * PawConnect - Helper Utility Functions
 */

require_once __DIR__ . '/../config/db.php';

// Sanitize user string input
function sanitize($data) {
    if (is_array($data)) {
        return array_map('sanitize', $data);
    }
    return htmlspecialchars(trim((string)$data), ENT_QUOTES, 'UTF-8');
}

// Flash message setter
function set_flash($type, $message) {
    $_SESSION['flash'] = [
        'type' => $type, // success, danger, warning, info
        'message' => $message
    ];
}

// Display flash message
function display_flash() {
    if (isset($_SESSION['flash'])) {
        $flash = $_SESSION['flash'];
        unset($_SESSION['flash']);
        $icon = 'bi-info-circle-fill';
        if ($flash['type'] === 'success') $icon = 'bi-check-circle-fill';
        elseif ($flash['type'] === 'danger') $icon = 'bi-exclamation-triangle-fill';
        elseif ($flash['type'] === 'warning') $icon = 'bi-exclamation-diamond-fill';

        echo "<div class='alert alert-{$flash['type']} alert-dismissible fade show shadow-sm' role='alert'>
                <i class='bi {$icon} me-2'></i> " . htmlspecialchars($flash['message']) . "
                <button type='button' class='btn-close' data-bs-dismiss='alert' aria-label='Close'></button>
              </div>";
    }
}

// Format date nicely
function format_date($datetime, $format = 'M d, Y') {
    if (!$datetime) return 'N/A';
    return date($format, strtotime($datetime));
}

// Time ago helper
function time_ago($datetime) {
    if (!$datetime) return 'N/A';
    $time = strtotime($datetime);
    $diff = time() - $time;
    if ($diff < 60) return 'Just now';
    if ($diff < 3600) return floor($diff / 60) . ' mins ago';
    if ($diff < 86400) return floor($diff / 3600) . ' hours ago';
    if ($diff < 604800) return floor($diff / 86400) . ' days ago';
    return date('M d, Y', $time);
}

// Status badge helper for Pet and Application status
function status_badge($status) {
    $status = ucfirst(trim($status));
    $classes = [
        'Available' => 'badge-success-soft',
        'Pending' => 'badge-warning-soft',
        'Adopted' => 'badge-primary-soft',
        'Under Review' => 'badge-info-soft',
        'Approved' => 'badge-success-soft',
        'Rejected' => 'badge-danger-soft',
        'Completed' => 'badge-secondary-soft',
    ];
    $cls = $classes[$status] ?? 'badge-secondary-soft';
    return "<span class='badge-status {$cls}'>" . htmlspecialchars($status) . "</span>";
}

// Format pet age
function get_pet_age($years, $months) {
    $years = (int)$years;
    $months = (int)$months;
    if ($years === 0 && $months === 0) return 'Under 1 month';
    $parts = [];
    if ($years > 0) {
        $parts[] = $years . ' ' . ($years === 1 ? 'yr' : 'yrs');
    }
    if ($months > 0) {
        $parts[] = $months . ' ' . ($months === 1 ? 'mo' : 'mos');
    }
    return implode(', ', $parts);
}

// Fallback pet image handler
function get_pet_image($imageUrl) {
    if (!empty($imageUrl)) {
        if (str_starts_with($imageUrl, 'http://') || str_starts_with($imageUrl, 'https://')) {
            return $imageUrl;
        }
        if (file_exists(__DIR__ . '/../' . ltrim($imageUrl, '/'))) {
            return '/' . ltrim($imageUrl, '/');
        }
    }
    return 'https://images.unsplash.com/photo-1543466835-00a7907e9de1?auto=format&fit=crop&w=800&q=80';
}

// CSRF token generation
function csrf_token() {
    if (empty($_SESSION['csrf_token'])) {
        $_SESSION['csrf_token'] = bin2hex(random_bytes(32));
    }
    return $_SESSION['csrf_token'];
}

// CSRF token verification
function verify_csrf($token) {
    return isset($_SESSION['csrf_token']) && hash_equals($_SESSION['csrf_token'], (string)$token);
}

// Check if user favorited a pet
function is_favorite($pdo, $userId, $petId) {
    if (!$userId) return false;
    $stmt = $pdo->prepare("SELECT 1 FROM favorites WHERE user_id = ? AND pet_id = ? LIMIT 1");
    $stmt->execute([$userId, $petId]);
    return (bool)$stmt->fetchColumn();
}
