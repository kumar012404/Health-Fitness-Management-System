<?php
/**
 * Complete Reminder API - Health and Fitness Management System
 * Marks a reminder as completed
 */

require_once '../config/config.php';
require_once '../includes/Reminder.php';

header('Content-Type: application/json');

if (!isLoggedIn()) {
    echo json_encode(['success' => false, 'message' => 'Not authenticated']);
    exit;
}

$userId = $_SESSION['user_id'];
$data = json_decode(file_get_contents('php://input'), true);
$reminderId = intval($data['reminder_id'] ?? 0);

if ($reminderId <= 0) {
    echo json_encode(['success' => false, 'message' => 'Invalid reminder']);
    exit;
}

$reminder = new Reminder();
$result = $reminder->markComplete($userId, $reminderId);

echo json_encode($result);
?>