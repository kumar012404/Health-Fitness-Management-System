<?php
/**
 * Get User Ringtone API - Health and Fitness Management System
 * Returns the user's custom ringtone if exists
 */

require_once '../config/config.php';

header('Content-Type: application/json');

if (!isLoggedIn()) {
    echo json_encode(['success' => false, 'message' => 'Not authenticated']);
    exit;
}

$userId = $_SESSION['user_id'];

// Check for user's custom ringtone
$uploadDir = __DIR__ . '/../assets/audio/user_ringtones/';
$pattern = $uploadDir . 'user_' . $userId . '_*';
$files = glob($pattern);

if (!empty($files)) {
    // User has a custom ringtone
    $filename = basename($files[0]);
    $fileUrl = '/hfms/assets/audio/user_ringtones/' . $filename;

    echo json_encode([
        'success' => true,
        'has_custom' => true,
        'ringtone_url' => $fileUrl,
        'filename' => $filename
    ]);
} else {
    // No custom ringtone
    echo json_encode([
        'success' => true,
        'has_custom' => false
    ]);
}
?>