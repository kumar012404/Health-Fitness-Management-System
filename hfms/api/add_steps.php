<?php
/**
 * Add Steps API - Health and Fitness Management System
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
$steps = intval($data['steps'] ?? 0);

if ($steps <= 0 || $steps > 100000) {
    echo json_encode(['success' => false, 'message' => 'Invalid value']);
    exit;
}

$activity = new Activity();
$result = $activity->addSteps($userId, $steps);

echo json_encode($result);
?>