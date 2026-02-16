<?php
/**
 * Update Water Intake API - Health and Fitness Management System
 */

require_once '../config/config.php';
require_once '../includes/Activity.php';

header('Content-Type: application/json');

if (!isLoggedIn()) {
    echo json_encode(['success' => false, 'message' => 'Not authenticated']);
    exit;
}

$userId = $_SESSION['user_id'];
$data = json_decode(file_get_contents('php://input'), true);
$glasses = intval($data['glasses'] ?? 0);

if ($glasses < 0 || $glasses > 20) {
    echo json_encode(['success' => false, 'message' => 'Invalid value']);
    exit;
}

$activity = new Activity();
$result = $activity->updateWaterIntake($userId, $glasses);

echo json_encode($result);
?>