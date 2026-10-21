<?php
/**
 * PawConnect - Logout
 */
require_once __DIR__ . '/config/db.php';
require_once __DIR__ . '/includes/auth.php';
require_once __DIR__ . '/includes/functions.php';

logout_user();
session_start();
set_flash('info', 'You have been logged out safely.');
header("Location: /login.php");
exit;
