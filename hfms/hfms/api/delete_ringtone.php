<?php
/**
 * Delete Custom Ringtone API - Health and Fitness Management System
 * Deletes the user's custom ringtone
 */

require_once '../config/config.php';

header('Content-Type: application/json');

if (!isLoggedIn()) {
    echo json_encode(['success' => false, 'message' => 'Not authenticated']);
    exit;
}

$userId = $_SESSION['user_id'];

// Find and delete user's custom ringtone
$uploadDir = __DIR__ . '/../assets/audio/user_ringtones/';
$pattern = $uploadDir . 'user_' . $userId . '_*';
$files = glob($pattern);

$deleted = false;
foreach ($files as $file) {
    if (unlink($file)) {
        $deleted = true;
    }
}

if ($deleted) {
    echo json_encode([
        'success' => true,
        'message' => 'Custom ringtone deleted successfully!'
    ]);
} else {
    echo json_encode([
        'success' => true,
        'message' => 'No custom ringtone found to delete.'
    ]);
}
?>