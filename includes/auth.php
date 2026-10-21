<?php
/**
 * PawConnect - Authentication Helper Functions
 */

require_once __DIR__ . '/../config/db.php';

// Check if user is logged in
function is_logged_in() {
    return isset($_SESSION['user_id']) && !empty($_SESSION['user_id']);
}

// Check if current user is an admin
function is_admin() {
    return is_logged_in() && isset($_SESSION['user_role']) && $_SESSION['user_role'] === 'admin';
}

// Get logged-in user array or null
function current_user() {
    if (!is_logged_in()) {
        return null;
    }
    return [
        'id'    => $_SESSION['user_id'],
        'name'  => $_SESSION['user_name'] ?? '',
        'email' => $_SESSION['user_email'] ?? '',
        'role'  => $_SESSION['user_role'] ?? 'user'
    ];
}

// Require user to be logged in, otherwise redirect to login
function require_login($redirectUrl = null) {
    if (!is_logged_in()) {
        if ($redirectUrl === null) {
            $redirectUrl = $_SERVER['REQUEST_URI'];
        }
        $_SESSION['redirect_after_login'] = $redirectUrl;
        $_SESSION['flash'] = [
            'type' => 'warning',
            'message' => 'Please log in to continue.'
        ];
        header("Location: /login.php");
        exit;
    }
}

// Require user to be admin, otherwise redirect with error
function require_admin() {
    if (!is_admin()) {
        $_SESSION['flash'] = [
            'type' => 'danger',
            'message' => 'Access denied. Administrator privileges required.'
        ];
        header("Location: /login.php");
        exit;
    }
}

// Establish session for logged in user
function login_user($user) {
    $_SESSION['user_id'] = $user['id'];
    $_SESSION['user_name'] = $user['name'];
    $_SESSION['user_email'] = $user['email'];
    $_SESSION['user_role'] = $user['role'];
    session_regenerate_id(true);
}

// Log out user
function logout_user() {
    $_SESSION = [];
    if (ini_get("session.use_cookies")) {
        $params = session_get_cookie_params();
        setcookie(session_name(), '', time() - 42000,
            $params["path"], $params["domain"],
            $params["secure"], $params["httponly"]
        );
    }
    session_destroy();
}
