<?php
/**
 * Logout Page - Health and Fitness Management System
 */

require_once 'config/config.php';
require_once 'includes/User.php';

$user = new User();
$user->logout();

// Start new session for flash message
session_start();
setFlashMessage('success', 'You have been logged out successfully.');

redirect(SITE_URL . '/login.php');
?>