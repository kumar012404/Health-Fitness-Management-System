<?php
/**
 * Activity Log Page - Health and Fitness Management System
 */

require_once 'config/config.php';
require_once 'includes/Activity.php';

requireLogin();

$userId = $_SESSION['user_id'];
$activity = new Activity();

$todayActivity = $activity->getTodayActivity($userId);
$weeklyData = $activity->getWeeklySummary($userId);

$success = '';
$errors = [];

// Handle form submission
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    if (!validateCSRFToken($_POST['csrf_token'] ?? '')) {
        $errors[] = 'Invalid request.';
    } else {
        $data = [
            'activity_date' => $_POST['activity_date'] ?? date('Y-m-d'),
            'steps_count' => intval($_POST['steps_count'] ?? 0),
            'exercise_type' => sanitizeInput($_POST['exercise_type'] ?? ''),
            'exercise_duration_mins' => intval($_POST['exercise_duration_mins'] ?? 0),
            'calories_burned' => intval($_POST['calories_burned'] ?? 0),
            'water_intake_glasses' => intval($_POST['water_intake_glasses'] ?? 0),
            'notes' => sanitizeInput($_POST['notes'] ?? '')
        ];

        $result = $activity->logActivity($userId, $data);
        if ($result['success']) {
            $success = $result['message'];
            $todayActivity = $activity->getTodayActivity($userId);
            $weeklyData = $activity->getWeeklySummary($userId);
        } else {
            $errors[] = $result['message'];
        }
    }
}

// Exercise types
$exerciseTypes = ['Walking', 'Running', 'Cycling', 'Swimming', 'Yoga', 'Strength Training', 'HIIT', 'Dancing', 'Sports', 'Other'];
?>
<!DOCTYPE html>
<html lang="en">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Activity Log - HFMS</title>
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
            <a href="activity.php" class="sidebar-link active"><i class="fas fa-running"></i><span>Activity
                    Log</span></a>
            <a href="recommendations.php" class="sidebar-link"><i
                    class="fas fa-lightbulb"></i><span>Recommendations</span></a>
            <a href="reminders.php" class="sidebar-link"><i class="fas fa-bell"></i><span>Reminders</span></a>
            <a href="reports.php" class="sidebar-link"><i class="fas fa-chart-line"></i><span>Reports</span></a>
            <a href="logout.php" class="sidebar-link"><i class="fas fa-sign-out-alt"></i><span>Logout</span></a>
        </nav>
    </aside>

    <main class="main-content">
        <div class="page-header">
            <h1 class="page-title">Activity Log</h1>
            <p class="page-subtitle">Track your daily fitness activities</p>
        </div>

        <?php if ($success): ?>
            <div class="alert alert-success"><i class="fas fa-check-circle"></i> <?= htmlspecialchars($success) ?></div>
        <?php endif; ?>

        <?php if (!empty($errors)): ?>
            <div class="alert alert-danger"><i class="fas fa-exclamation-circle"></i> <?= implode(' ', $errors) ?></div>
        <?php endif; ?>

        <!-- Weekly Summary Cards -->
        <div class="grid grid-cols-4 mb-3">
            <div class="stat-card primary">
                <div class="icon"><i class="fas fa-shoe-prints"></i></div>
                <div class="value"><?= number_format($weeklyData['summary']['total_steps'] ?? 0) ?></div>
                <div class="label">Total Steps (Week)</div>
            </div>
            <div class="stat-card secondary">
                <div class="icon"><i class="fas fa-clock"></i></div>
                <div class="value"><?= $weeklyData['summary']['total_exercise_mins'] ?? 0 ?></div>
                <div class="label">Exercise Minutes</div>
            </div>
            <div class="stat-card accent">
                <div class="icon"><i class="fas fa-fire"></i></div>
                <div class="value"><?= number_format($weeklyData['summary']['total_calories'] ?? 0) ?></div>
                <div class="label">Calories Burned</div>
            </div>
            <div class="stat-card warning">
                <div class="icon"><i class="fas fa-calendar-check"></i></div>
                <div class="value"><?= $weeklyData['summary']['days_logged'] ?? 0 ?>/7</div>
                <div class="label">Days Logged</div>
            </div>
        </div>

        <div class="grid grid-cols-2">
            <!-- Activity Form -->
            <div class="card">
                <div class="card-header">
                    <h4><i class="fas fa-plus-circle"></i> Log Activity</h4>
                </div>
                <div class="card-body">
                    <form method="POST">
                        <input type="hidden" name="csrf_token" value="<?= generateCSRFToken() ?>">

                        <div class="form-group">
                            <label class="form-label">Date</label>
                            <input type="date" name="activity_date" class="form-control" value="<?= date('Y-m-d') ?>"
                                max="<?= date('Y-m-d') ?>">
                        </div>

                        <div class="grid grid-cols-2">
                            <div class="form-group">
                                <label class="form-label"><i class="fas fa-shoe-prints text-primary"></i> Steps</label>
                                <input type="number" name="steps_count" class="form-control" min="0"
                                    value="<?= $todayActivity['steps_count'] ?? '' ?>" placeholder="e.g., 5000">
                            </div>
                            <div class="form-group">
                                <label class="form-label"><i class="fas fa-tint text-info"></i> Water (glasses)</label>
                                <input type="number" name="water_intake_glasses" class="form-control" min="0" max="20"
                                    value="<?= $todayActivity['water_intake_glasses'] ?? '' ?>" placeholder="e.g., 8">
                            </div>
                        </div>

                        <div class="form-group">
                            <label class="form-label"><i class="fas fa-dumbbell text-danger"></i> Exercise Type</label>
                            <select name="exercise_type" class="form-control">
                                <option value="">Select exercise type</option>
                                <?php foreach ($exerciseTypes as $type): ?>
                                    <option value="<?= $type ?>" <?= ($todayActivity['exercise_type'] ?? '') === $type ? 'selected' : '' ?>><?= $type ?></option>
                                <?php endforeach; ?>
                            </select>
                        </div>

                        <div class="grid grid-cols-2">
                            <div class="form-group">
                                <label class="form-label"><i class="fas fa-clock text-warning"></i> Duration
                                    (mins)</label>
                                <input type="number" name="exercise_duration_mins" class="form-control" min="0"
                                    value="<?= $todayActivity['exercise_duration_mins'] ?? '' ?>"
                                    placeholder="e.g., 30">
                            </div>
                            <div class="form-group">
                                <label class="form-label"><i class="fas fa-fire text-danger"></i> Calories
                                    Burned</label>
                                <input type="number" name="calories_burned" class="form-control" min="0"
                                    value="<?= $todayActivity['calories_burned'] ?? '' ?>" placeholder="e.g., 200">
                            </div>
                        </div>

                        <div class="form-group">
                            <label class="form-label">Notes</label>
                            <textarea name="notes" class="form-control" rows="2"
                                placeholder="Any notes about today's activity..."><?= htmlspecialchars($todayActivity['notes'] ?? '') ?></textarea>
                        </div>

                        <button type="submit" class="btn btn-primary btn-lg btn-block">
                            <i class="fas fa-save"></i> Save Activity
                        </button>
                    </form>
                </div>
            </div>

            <!-- Recent Activities -->
            <div class="card">
                <div class="card-header">
                    <h4><i class="fas fa-history"></i> This Week's Activities</h4>
                </div>
                <div class="card-body" style="max-height: 500px; overflow-y: auto;">
                    <?php if (empty($weeklyData['daily_data'])): ?>
                        <p class="text-center text-muted" style="padding: 2rem;">
                            <i class="fas fa-clipboard-list"
                                style="font-size: 3rem; display: block; margin-bottom: 1rem;"></i>
                            No activities logged this week.<br>Start by logging today's activity!
                        </p>
                    <?php else: ?>
                        <?php foreach ($weeklyData['daily_data'] as $day): ?>
                            <div class="card mb-2" style="box-shadow: var(--shadow-sm);">
                                <div class="card-body" style="padding: 1rem;">
                                    <div class="d-flex justify-between align-center mb-1">
                                        <strong><?= formatDate($day['activity_date'], 'D, M j') ?></strong>
                                        <?php if ($day['activity_date'] === date('Y-m-d')): ?>
                                            <span class="badge badge-primary">Today</span>
                                        <?php endif; ?>
                                    </div>
                                    <div class="d-flex gap-2" style="flex-wrap: wrap;">
                                        <span><i class="fas fa-shoe-prints text-primary"></i>
                                            <?= number_format($day['steps_count']) ?> steps</span>
                                        <span><i class="fas fa-clock text-warning"></i> <?= $day['exercise_duration_mins'] ?>
                                            mins</span>
                                        <span><i class="fas fa-tint text-info"></i> <?= $day['water_intake_glasses'] ?>
                                            glasses</span>
                                        <span><i class="fas fa-fire text-danger"></i> <?= $day['calories_burned'] ?> cal</span>
                                    </div>
                                </div>
                            </div>
                        <?php endforeach; ?>
                    <?php endif; ?>
                </div>
            </div>
        </div>
    </main>

    <!-- Alarm System -->
    <script src="assets/js/alarm.js"></script>
</body>

</html>