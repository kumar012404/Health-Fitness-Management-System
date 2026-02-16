<?php
/**
 * Upload Custom Ringtone API - Health and Fitness Management System
 * Allows users to upload their own alarm ringtone
 */

require_once '../config/config.php';

header('Content-Type: application/json');

if (!isLoggedIn()) {
    echo json_encode(['success' => false, 'message' => 'Not authenticated']);
    exit;
}

$userId = $_SESSION['user_id'];

// Check if file was uploaded
if (!isset($_FILES['ringtone']) || $_FILES['ringtone']['error'] !== UPLOAD_ERR_OK) {
    $errorMessages = [
        UPLOAD_ERR_INI_SIZE => 'File is too large (server limit)',
        UPLOAD_ERR_FORM_SIZE => 'File is too large',
        UPLOAD_ERR_PARTIAL => 'File was only partially uploaded',
        UPLOAD_ERR_NO_FILE => 'No file was uploaded',
        UPLOAD_ERR_NO_TMP_DIR => 'Server error: Missing temp folder',
        UPLOAD_ERR_CANT_WRITE => 'Server error: Failed to write file',
        UPLOAD_ERR_EXTENSION => 'File upload blocked by extension'
    ];

    $error = $_FILES['ringtone']['error'] ?? UPLOAD_ERR_NO_FILE;
    $message = $errorMessages[$error] ?? 'Unknown upload error';

    echo json_encode(['success' => false, 'message' => $message]);
    exit;
}

$file = $_FILES['ringtone'];

// Validate file type
$allowedTypes = ['audio/mpeg', 'audio/mp3', 'audio/wav', 'audio/x-wav', 'audio/ogg', 'audio/webm'];
$allowedExtensions = ['mp3', 'wav', 'ogg', 'webm'];

$fileExtension = strtolower(pathinfo($file['name'], PATHINFO_EXTENSION));
$mimeType = mime_content_type($file['tmp_name']);

if (!in_array($mimeType, $allowedTypes) && !in_array($fileExtension, $allowedExtensions)) {
    echo json_encode(['success' => false, 'message' => 'Invalid file type. Please upload MP3, WAV, OGG, or WebM files only.']);
    exit;
}

// Validate file size (max 2MB)
$maxSize = 2 * 1024 * 1024; // 2MB
if ($file['size'] > $maxSize) {
    echo json_encode(['success' => false, 'message' => 'File is too large. Maximum size is 2MB.']);
    exit;
}

// Create user ringtone directory if not exists
$uploadDir = __DIR__ . '/../assets/audio/user_ringtones/';
if (!is_dir($uploadDir)) {
    mkdir($uploadDir, 0755, true);
}

// Delete old custom ringtone if exists
$pattern = $uploadDir . 'user_' . $userId . '_*';
foreach (glob($pattern) as $oldFile) {
    unlink($oldFile);
}

// Generate unique filename
$newFilename = 'user_' . $userId . '_' . time() . '.' . $fileExtension;
$destination = $uploadDir . $newFilename;

// Move uploaded file
if (!move_uploaded_file($file['tmp_name'], $destination)) {
    echo json_encode(['success' => false, 'message' => 'Failed to save the file. Please try again.']);
    exit;
}

// Return success with file URL
$fileUrl = '/hfms/assets/audio/user_ringtones/' . $newFilename;

echo json_encode([
    'success' => true,
    'message' => 'Ringtone uploaded successfully!',
    'ringtone_url' => $fileUrl,
    'filename' => $newFilename
]);
?>