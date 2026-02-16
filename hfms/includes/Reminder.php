<?php
/**
 * Reminder Class - Health and Fitness Management System
 */

require_once dirname(__DIR__) . '/config/config.php';

class Reminder
{
    private $db;

    public function __construct()
    {
        $this->db = getDBConnection();
    }

    public function createReminder($userId, $data)
    {
        try {
            $stmt = $this->db->prepare("INSERT INTO reminders (user_id, title, description, reminder_time, reminder_date, category, repeat_type) VALUES (?, ?, ?, ?, ?, ?, ?)");
            $stmt->execute([
                $userId,
                $data['title'],
                $data['description'] ?? null,
                $data['reminder_time'],
                $data['reminder_date'] ?? null,
                $data['category'] ?? 'other',
                $data['repeat_type'] ?? 'once'
            ]);
            return ['success' => true, 'message' => 'Reminder created!', 'id' => $this->db->lastInsertId()];
        } catch (PDOException $e) {
            return ['success' => false, 'message' => 'Failed to create reminder.'];
        }
    }

    public function getReminders($userId, $activeOnly = true)
    {
        try {
            $sql = "SELECT * FROM reminders WHERE user_id = ?";
            if ($activeOnly)
                $sql .= " AND is_active = 1";
            $sql .= " ORDER BY reminder_time ASC";
            $stmt = $this->db->prepare($sql);
            $stmt->execute([$userId]);
            return $stmt->fetchAll();
        } catch (PDOException $e) {
            return [];
        }
    }

    public function getTodayReminders($userId)
    {
        try {
            $stmt = $this->db->prepare("SELECT * FROM reminders WHERE user_id = ? AND is_active = 1 AND (reminder_date = CURDATE() OR reminder_date IS NULL OR repeat_type != 'once') ORDER BY reminder_time");
            $stmt->execute([$userId]);
            return $stmt->fetchAll();
        } catch (PDOException $e) {
            return [];
        }
    }

    public function updateReminder($userId, $reminderId, $data)
    {
        try {
            $stmt = $this->db->prepare("UPDATE reminders SET title = ?, description = ?, reminder_time = ?, category = ?, repeat_type = ?, updated_at = NOW() WHERE reminder_id = ? AND user_id = ?");
            $stmt->execute([$data['title'], $data['description'] ?? null, $data['reminder_time'], $data['category'], $data['repeat_type'], $reminderId, $userId]);
            return ['success' => true, 'message' => 'Reminder updated!'];
        } catch (PDOException $e) {
            return ['success' => false, 'message' => 'Failed to update.'];
        }
    }

    public function deleteReminder($userId, $reminderId)
    {
        try {
            $stmt = $this->db->prepare("DELETE FROM reminders WHERE reminder_id = ? AND user_id = ?");
            $stmt->execute([$reminderId, $userId]);
            return ['success' => $stmt->rowCount() > 0, 'message' => $stmt->rowCount() > 0 ? 'Deleted!' : 'Not found.'];
        } catch (PDOException $e) {
            return ['success' => false, 'message' => 'Failed to delete.'];
        }
    }

    public function toggleReminder($userId, $reminderId)
    {
        try {
            $stmt = $this->db->prepare("UPDATE reminders SET is_active = NOT is_active WHERE reminder_id = ? AND user_id = ?");
            $stmt->execute([$reminderId, $userId]);
            return ['success' => true, 'message' => 'Reminder toggled!'];
        } catch (PDOException $e) {
            return ['success' => false, 'message' => 'Failed to toggle.'];
        }
    }

    public function markComplete($userId, $reminderId)
    {
        try {
            $stmt = $this->db->prepare("UPDATE reminders SET is_completed = 1 WHERE reminder_id = ? AND user_id = ?");
            $stmt->execute([$reminderId, $userId]);
            return ['success' => true, 'message' => 'Marked complete!'];
        } catch (PDOException $e) {
            return ['success' => false, 'message' => 'Failed.'];
        }
    }
}
?>