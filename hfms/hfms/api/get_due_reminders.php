<?php
/**
 * Get Due Reminders API - Health and Fitness Management System
 * Returns reminders that are due within the current time window
 */

require_once '../config/config.php';
require_once '../includes/Reminder.php';

header('Content-Type: application/json');

if (!isLoggedIn()) {
    echo json_encode(['success' => false, 'message' => 'Not authenticated']);
    exit;
}

$userId = $_SESSION['user_id'];

try {
    $db = getDBConnection();
    $currentTime = date('H:i:s');
    $currentDate = date('Y-m-d');

    // Get reminders where current time matches the reminder time (same minute)
    // Compare only hours and minutes, trigger when we're at or past the reminder time within 1 minute
    $stmt = $db->prepare("
        SELECT reminder_id, title, description, reminder_time, category
        FROM reminders
        WHERE user_id = ?
        AND is_active = 1
        AND is_completed = 0
        AND (
            (repeat_type = 'daily')
            OR (repeat_type = 'once' AND (reminder_date = ? OR reminder_date IS NULL))
            OR (repeat_type = 'weekly' AND DAYOFWEEK(?) = DAYOFWEEK(created_at))
        )
        AND TIME_FORMAT(reminder_time, '%H:%i') = TIME_FORMAT(?, '%H:%i')
    ");

    $stmt->execute([$userId, $currentDate, $currentDate, $currentTime]);
    $dueReminders = $stmt->fetchAll();

    echo json_encode([
        'success' => true,
        'reminders' => $dueReminders,
        'current_time' => $currentTime
    ]);

} catch (PDOException $e) {
    echo json_encode(['success' => false, 'message' => 'Database error']);
}
?>