<?php
/**
 * Reminders Page - Health and Fitness Management System
 */

require_once 'config/config.php';
require_once 'includes/Reminder.php';

requireLogin();

$userId = $_SESSION['user_id'];
$reminderClass = new Reminder();

$success = '';
$errors = [];

// Handle form submission
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $action = $_POST['action'] ?? '';

    if (!validateCSRFToken($_POST['csrf_token'] ?? '')) {
        $errors[] = 'Invalid request.';
    } else {
        if ($action === 'create') {
            $data = [
                'title' => sanitizeInput($_POST['title'] ?? ''),
                'description' => sanitizeInput($_POST['description'] ?? ''),
                'reminder_time' => $_POST['reminder_time'] ?? '',
                'category' => $_POST['category'] ?? 'other',
                'repeat_type' => $_POST['repeat_type'] ?? 'once'
            ];

            if (empty($data['title'])) {
                $errors[] = 'Title is required.';
            } else {
                $result = $reminderClass->createReminder($userId, $data);
                if ($result['success']) {
                    $success = $result['message'];
                } else {
                    $errors[] = $result['message'];
                }
            }
        } elseif ($action === 'delete') {
            $reminderId = intval($_POST['reminder_id'] ?? 0);
            $result = $reminderClass->deleteReminder($userId, $reminderId);
            if ($result['success']) {
                $success = $result['message'];
            }
        } elseif ($action === 'toggle') {
            $reminderId = intval($_POST['reminder_id'] ?? 0);
            $reminderClass->toggleReminder($userId, $reminderId);
        }
    }
}

$reminders = $reminderClass->getReminders($userId, false);
$activeReminders = array_filter($reminders, fn($r) => $r['is_active']);
?>
<!DOCTYPE html>
<html lang="en">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Reminders - HFMS</title>
    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link
        href="https://fonts.googleapis.com/css2?family=Inter:wght@300;400;500;600;700&family=Poppins:wght@400;500;600;700&display=swap"
        rel="stylesheet">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">
    <link rel="stylesheet" href="assets/css/style.css">
</head>

<body>
    <!-- Sidebar -->
    <aside class="sidebar">
        <div class="sidebar-header">
            <a href="dashboard.php" class="sidebar-brand"><i class="fas fa-heartbeat"></i><span>HFMS</span></a>
        </div>
        <nav class="sidebar-nav">
            <a href="dashboard.php" class="sidebar-link"><i class="fas fa-home"></i><span>Dashboard</span></a>
            <a href="profile.php" class="sidebar-link"><i class="fas fa-user"></i><span>My Profile</span></a>
            <a href="bmi.php" class="sidebar-link"><i class="fas fa-weight"></i><span>BMI Calculator</span></a>
            <a href="activity.php" class="sidebar-link"><i class="fas fa-running"></i><span>Activity Log</span></a>
            <a href="recommendations.php" class="sidebar-link"><i
                    class="fas fa-lightbulb"></i><span>Recommendations</span></a>
            <a href="reminders.php" class="sidebar-link active"><i class="fas fa-bell"></i><span>Reminders</span></a>
            <a href="reports.php" class="sidebar-link"><i class="fas fa-chart-line"></i><span>Reports</span></a>
            <a href="logout.php" class="sidebar-link"><i class="fas fa-sign-out-alt"></i><span>Logout</span></a>
        </nav>
    </aside>

    <main class="main-content">
        <div class="page-header"
            style="display: flex; justify-content: space-between; align-items: center; flex-wrap: wrap;">
            <div>
                <h1 class="page-title">Reminders</h1>
                <p class="page-subtitle">Set reminders to stay on track with your health goals</p>
            </div>
            <div style="display: flex; gap: 0.5rem;">
                <button class="btn btn-outline btn-sm" onclick="openRingtoneSettings()"
                    style="display: flex; align-items: center; gap: 0.5rem;">
                    <i class="fas fa-music"></i> Change Ringtone
                </button>
                <button class="btn btn-sm" onclick="testAlarm()"
                    style="background: var(--warning); color: #000; display: flex; align-items: center; gap: 0.5rem;">
                    <i class="fas fa-bell"></i> Test Alarm
                </button>
            </div>
        </div>

        <?php if ($success): ?>
            <div class="alert alert-success"><i class="fas fa-check-circle"></i> <?= htmlspecialchars($success) ?></div>
        <?php endif; ?>

        <?php if (!empty($errors)): ?>
            <div class="alert alert-danger"><i class="fas fa-exclamation-circle"></i> <?= implode(' ', $errors) ?></div>
        <?php endif; ?>

        <div class="grid grid-cols-2">
            <!-- Create Reminder Form -->
            <div class="card">
                <div class="card-header">
                    <h4><i class="fas fa-plus-circle"></i> Create Reminder</h4>
                </div>
                <div class="card-body">
                    <form method="POST">
                        <input type="hidden" name="csrf_token" value="<?= generateCSRFToken() ?>">
                        <input type="hidden" name="action" value="create">

                        <div class="form-group">
                            <label class="form-label">Title *</label>
                            <input type="text" name="title" class="form-control" required
                                placeholder="e.g., Morning Walk">
                        </div>

                        <div class="form-group">
                            <label class="form-label">Description</label>
                            <textarea name="description" class="form-control" rows="2"
                                placeholder="Optional details..."></textarea>
                        </div>

                        <div class="grid grid-cols-2">
                            <div class="form-group">
                                <label class="form-label">Time *</label>
                                <input type="time" name="reminder_time" class="form-control" required>
                            </div>
                            <div class="form-group">
                                <label class="form-label">Category</label>
                                <select name="category" class="form-control">
                                    <?php foreach (REMINDER_CATEGORIES as $key => $cat): ?>
                                        <option value="<?= $key ?>"><?= $cat['label'] ?></option>
                                    <?php endforeach; ?>
                                </select>
                            </div>
                        </div>

                        <div class="form-group">
                            <label class="form-label">Repeat</label>
                            <select name="repeat_type" class="form-control">
                                <option value="once">Once</option>
                                <option value="daily" selected>Daily</option>
                                <option value="weekly">Weekly</option>
                                <option value="monthly">Monthly</option>
                            </select>
                        </div>

                        <button type="submit" class="btn btn-primary btn-block">
                            <i class="fas fa-bell"></i> Create Reminder
                        </button>
                    </form>
                </div>
            </div>

            <!-- Reminders List -->
            <div class="card">
                <div class="card-header d-flex justify-between align-center">
                    <h4><i class="fas fa-list"></i> Your Reminders</h4>
                    <span class="badge badge-primary"><?= count($activeReminders) ?> Active</span>
                </div>
                <div class="card-body" style="max-height: 500px; overflow-y: auto;">
                    <?php if (empty($reminders)): ?>
                        <p class="text-center text-muted" style="padding: 2rem;">
                            <i class="fas fa-bell-slash" style="font-size: 3rem; display: block; margin-bottom: 1rem;"></i>
                            No reminders yet.<br>Create your first reminder!
                        </p>
                    <?php else: ?>
                        <?php foreach ($reminders as $rem): ?>
                            <?php $cat = REMINDER_CATEGORIES[$rem['category']] ?? REMINDER_CATEGORIES['other']; ?>
                            <div class="reminder-item <?= !$rem['is_active'] ? 'text-muted' : '' ?>"
                                style="<?= !$rem['is_active'] ? 'opacity: 0.6;' : '' ?>">
                                <div class="reminder-icon"
                                    style="background: <?= $cat['color'] ?>20; color: <?= $cat['color'] ?>;">
                                    <i class="fas <?= $cat['icon'] ?>"></i>
                                </div>
                                <div class="reminder-content">
                                    <div class="reminder-title"><?= htmlspecialchars($rem['title']) ?></div>
                                    <div class="reminder-time">
                                        <i class="fas fa-clock"></i> <?= date('h:i A', strtotime($rem['reminder_time'])) ?>
                                        <span class="badge badge-<?= $rem['is_active'] ? 'success' : 'warning' ?>"
                                            style="margin-left: 0.5rem; font-size: 0.7rem;">
                                            <?= ucfirst($rem['repeat_type']) ?>
                                        </span>
                                    </div>
                                </div>
                                <form method="POST" style="display: inline;">
                                    <input type="hidden" name="csrf_token" value="<?= generateCSRFToken() ?>">
                                    <input type="hidden" name="action" value="delete">
                                    <input type="hidden" name="reminder_id" value="<?= $rem['reminder_id'] ?>">
                                    <button type="submit" class="btn btn-sm" style="background: none; color: var(--danger);"
                                        onclick="return confirm('Delete this reminder?')">
                                        <i class="fas fa-trash"></i>
                                    </button>
                                </form>
                            </div>
                        <?php endforeach; ?>
                    <?php endif; ?>
                </div>
            </div>
        </div>

        <!-- Quick Reminder Presets -->
        <div class="card mt-3">
            <div class="card-header">
                <h4><i class="fas fa-magic"></i> Quick Presets</h4>
            </div>
            <div class="card-body">
                <p class="text-muted mb-2">Click to quickly add common health reminders:</p>
                <div class="d-flex gap-1" style="flex-wrap: wrap;">
                    <button class="btn btn-outline btn-sm"
                        onclick="setPreset('Drink Water', '10:00', 'water', 'daily')">
                        <i class="fas fa-tint"></i> Water Reminder
                    </button>
                    <button class="btn btn-outline btn-sm"
                        onclick="setPreset('Morning Exercise', '07:00', 'exercise', 'daily')">
                        <i class="fas fa-running"></i> Morning Exercise
                    </button>
                    <button class="btn btn-outline btn-sm"
                        onclick="setPreset('Take Vitamins', '09:00', 'medication', 'daily')">
                        <i class="fas fa-pills"></i> Vitamins
                    </button>
                    <button class="btn btn-outline btn-sm" onclick="setPreset('Lunch Time', '13:00', 'meal', 'daily')">
                        <i class="fas fa-utensils"></i> Lunch
                    </button>
                    <button class="btn btn-outline btn-sm"
                        onclick="setPreset('Evening Walk', '18:00', 'exercise', 'daily')">
                        <i class="fas fa-walking"></i> Evening Walk
                    </button>
                    <button class="btn btn-outline btn-sm" onclick="setPreset('Sleep Time', '22:00', 'sleep', 'daily')">
                        <i class="fas fa-moon"></i> Sleep Reminder
                    </button>
                </div>
            </div>
        </div>
    </main>

    <script>
        function setPreset(title, time, category, repeat) {
            document.querySelector('input[name="title"]').value = title;
            document.querySelector('input[name="reminder_time"]').value = time;
            document.querySelector('select[name="category"]').value = category;
            document.querySelector('select[name="repeat_type"]').value = repeat;
            document.querySelector('input[name="title"]').focus();
        }
    </script>

    <!-- Alarm System -->
    <script src="assets/js/alarm.js"></script>
</body>

</html>