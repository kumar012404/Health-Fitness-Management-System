<?php
/**
 * Dashboard Page - Health and Fitness Management System
 */

require_once 'config/config.php';
require_once 'includes/User.php';
require_once 'includes/Activity.php';
require_once 'includes/BMI.php';
require_once 'includes/Reminder.php';
require_once 'includes/Recommendation.php';

requireLogin();

$userId = $_SESSION['user_id'];
$user = new User();
$activity = new Activity();
$bmi = new BMI();
$reminder = new Reminder();
$recommendation = new Recommendation();

// Get user data
$userData = $user->getUserById($userId);
$todayActivity = $activity->getTodayActivity($userId);
$latestBmi = $bmi->getLatest($userId);
$todayReminders = $reminder->getTodayReminders($userId);
$chartData = $activity->getChartData($userId);

// Check if profile is complete
$hasProfile = $user->hasProfile($userId);

// Get recommendations
$recommendations = $recommendation->getRecommendations($userId);
$dailyTargets = $recommendations['daily_targets'] ?? ['steps' => 10000, 'water_glasses' => 8, 'exercise_mins' => 30];

// Calculate progress percentages
$stepsProgress = $todayActivity ? min(100, ($todayActivity['steps_count'] / $dailyTargets['steps']) * 100) : 0;
$waterProgress = $todayActivity ? min(100, ($todayActivity['water_intake_glasses'] / $dailyTargets['water_glasses']) * 100) : 0;
$exerciseProgress = $todayActivity ? min(100, ($todayActivity['exercise_duration_mins'] / $dailyTargets['exercise_mins']) * 100) : 0;
?>
<!DOCTYPE html>
<html lang="en">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Dashboard - HFMS</title>
    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link
        href="https://fonts.googleapis.com/css2?family=Inter:wght@300;400;500;600;700&family=Poppins:wght@400;500;600;700&display=swap"
        rel="stylesheet">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">
    <link rel="stylesheet" href="assets/css/style.css">
    <script src="https://cdn.jsdelivr.net/npm/chart.js"></script>
</head>

<body>
    <!-- Sidebar -->
    <aside class="sidebar" id="sidebar">
        <div class="sidebar-header">
            <a href="dashboard.php" class="sidebar-brand">
                <i class="fas fa-heartbeat"></i>
                <span>HFMS</span>
            </a>
        </div>
        <nav class="sidebar-nav">
            <a href="dashboard.php" class="sidebar-link active">
                <i class="fas fa-home"></i>
                <span>Dashboard</span>
            </a>
            <a href="profile.php" class="sidebar-link">
                <i class="fas fa-user"></i>
                <span>My Profile</span>
            </a>
            <a href="bmi.php" class="sidebar-link">
                <i class="fas fa-weight"></i>
                <span>BMI Calculator</span>
            </a>
            <a href="activity.php" class="sidebar-link">
                <i class="fas fa-running"></i>
                <span>Activity Log</span>
            </a>
            <a href="recommendations.php" class="sidebar-link">
                <i class="fas fa-lightbulb"></i>
                <span>Recommendations</span>
            </a>
            <a href="reminders.php" class="sidebar-link">
                <i class="fas fa-bell"></i>
                <span>Reminders</span>
            </a>
            <a href="reports.php" class="sidebar-link">
                <i class="fas fa-chart-line"></i>
                <span>Reports</span>
            </a>
            <a href="logout.php" class="sidebar-link" style="margin-top: auto;">
                <i class="fas fa-sign-out-alt"></i>
                <span>Logout</span>
            </a>
        </nav>
    </aside>

    <!-- Main Content -->
    <main class="main-content">
        <!-- Page Header -->
        <div class="page-header d-flex justify-between align-center">
            <div>
                <h1 class="page-title">Welcome,
                    <?= htmlspecialchars($userData['full_name'] ?? $userData['username']) ?>!
                </h1>
                <p class="page-subtitle"><?= date('l, F j, Y') ?></p>
            </div>
            <button class="btn btn-outline" id="sidebarToggle" style="display: none;">
                <i class="fas fa-bars"></i>
            </button>
        </div>

        <?php if (!$hasProfile): ?>
            <div class="alert alert-warning mb-3">
                <i class="fas fa-exclamation-triangle"></i>
                <div>
                    <strong>Complete Your Profile</strong>
                    <p style="margin: 0;">Please complete your health profile to get personalized recommendations.</p>
                    <a href="profile.php" class="btn btn-sm btn-warning mt-1">Complete Profile</a>
                </div>
            </div>
        <?php endif; ?>

        <!-- Stats Cards -->
        <div class="grid grid-cols-4 mb-3">
            <div class="stat-card primary">
                <div class="icon"><i class="fas fa-shoe-prints"></i></div>
                <div class="value"><?= number_format($todayActivity['steps_count'] ?? 0) ?></div>
                <div class="label">Steps Today</div>
                <div class="progress mt-1">
                    <div class="progress-bar primary" style="width: <?= $stepsProgress ?>%"></div>
                </div>
                <small class="text-muted"><?= round($stepsProgress) ?>% of <?= number_format($dailyTargets['steps']) ?>
                    goal</small>
            </div>

            <div class="stat-card secondary">
                <div class="icon"><i class="fas fa-tint"></i></div>
                <div class="value"><?= $todayActivity['water_intake_glasses'] ?? 0 ?></div>
                <div class="label">Glasses of Water</div>
                <div class="progress mt-1">
                    <div class="progress-bar secondary" style="width: <?= $waterProgress ?>%"></div>
                </div>
                <small class="text-muted"><?= round($waterProgress) ?>% of <?= $dailyTargets['water_glasses'] ?>
                    glasses</small>
            </div>

            <div class="stat-card accent">
                <div class="icon"><i class="fas fa-clock"></i></div>
                <div class="value"><?= $todayActivity['exercise_duration_mins'] ?? 0 ?></div>
                <div class="label">Exercise Minutes</div>
                <div class="progress mt-1">
                    <div class="progress-bar accent" style="width: <?= $exerciseProgress ?>%"></div>
                </div>
                <small class="text-muted"><?= round($exerciseProgress) ?>% of <?= $dailyTargets['exercise_mins'] ?>
                    mins</small>
            </div>

            <div class="stat-card warning">
                <div class="icon"><i class="fas fa-weight"></i></div>
                <div class="value"><?= $latestBmi ? $latestBmi['bmi_value'] : '--' ?></div>
                <div class="label">Current BMI</div>
                <?php if ($latestBmi): ?>
                    <span
                        class="badge badge-<?= $latestBmi['bmi_category'] === 'normal' ? 'success' : ($latestBmi['bmi_category'] === 'underweight' ? 'primary' : 'warning') ?> mt-1">
                        <?= ucfirst($latestBmi['bmi_category']) ?>
                    </span>
                <?php endif; ?>
            </div>
        </div>

        <!-- Quick Actions -->
        <div class="card mb-3">
            <div class="card-body">
                <h4 class="mb-2">Quick Actions</h4>
                <div class="d-flex gap-2" style="flex-wrap: wrap;">
                    <button class="btn btn-primary btn-sm" onclick="openWaterModal()">
                        <i class="fas fa-tint"></i> Log Water
                    </button>
                    <button class="btn btn-secondary btn-sm" onclick="openStepsModal()">
                        <i class="fas fa-shoe-prints"></i> Add Steps
                    </button>
                    <a href="activity.php" class="btn btn-accent btn-sm">
                        <i class="fas fa-plus"></i> Log Activity
                    </a>
                    <a href="bmi.php" class="btn btn-outline btn-sm">
                        <i class="fas fa-calculator"></i> Calculate BMI
                    </a>
                </div>
            </div>
        </div>

        <div class="grid grid-cols-2">
            <!-- Activity Chart -->
            <div class="card">
                <div class="card-header">
                    <h4>Weekly Activity Overview</h4>
                </div>
                <div class="card-body">
                    <div class="chart-container">
                        <canvas id="activityChart"></canvas>
                    </div>
                </div>
            </div>

            <!-- Today's Reminders -->
            <div class="card">
                <div class="card-header d-flex justify-between align-center">
                    <h4>Today's Reminders</h4>
                    <a href="reminders.php" class="btn btn-sm btn-outline">View All</a>
                </div>
                <div class="card-body">
                    <?php if (empty($todayReminders)): ?>
                        <p class="text-center text-muted">
                            <i class="fas fa-bell-slash"
                                style="font-size: 2rem; display: block; margin-bottom: 0.5rem;"></i>
                            No reminders for today
                        </p>
                    <?php else: ?>
                        <?php foreach (array_slice($todayReminders, 0, 4) as $rem): ?>
                            <div class="reminder-item">
                                <div class="reminder-icon"
                                    style="background: <?= REMINDER_CATEGORIES[$rem['category']]['color'] ?>20; color: <?= REMINDER_CATEGORIES[$rem['category']]['color'] ?>;">
                                    <i class="fas <?= REMINDER_CATEGORIES[$rem['category']]['icon'] ?>"></i>
                                </div>
                                <div class="reminder-content">
                                    <div class="reminder-title"><?= htmlspecialchars($rem['title']) ?></div>
                                    <div class="reminder-time">
                                        <i class="fas fa-clock"></i> <?= date('h:i A', strtotime($rem['reminder_time'])) ?>
                                    </div>
                                </div>
                            </div>
                        <?php endforeach; ?>
                    <?php endif; ?>
                </div>
            </div>
        </div>

        <!-- Water Tracker -->
        <div class="card mt-3">
            <div class="card-header">
                <h4><i class="fas fa-tint text-primary"></i> Water Tracker</h4>
            </div>
            <div class="card-body">
                <div class="water-tracker" id="waterTracker">
                    <?php for ($i = 1; $i <= 8; $i++): ?>
                        <div class="water-glass <?= ($todayActivity['water_intake_glasses'] ?? 0) >= $i ? 'filled' : '' ?>"
                            data-glass="<?= $i ?>" onclick="updateWater(<?= $i ?>)">
                        </div>
                    <?php endfor; ?>
                </div>
                <p class="text-center text-muted mt-1">Click on glasses to update your water intake</p>
            </div>
        </div>
    </main>

    <!-- Water Modal -->
    <div class="modal-overlay" id="waterModal">
        <div class="modal">
            <div class="modal-header">
                <h3 class="modal-title"><i class="fas fa-tint text-primary"></i> Log Water Intake</h3>
                <button class="modal-close" onclick="closeModal('waterModal')">&times;</button>
            </div>
            <div class="modal-body">
                <div class="form-group">
                    <label class="form-label">Number of Glasses (250ml each)</label>
                    <input type="number" id="waterGlasses" class="form-control" min="0" max="20"
                        value="<?= $todayActivity['water_intake_glasses'] ?? 0 ?>">
                </div>
            </div>
            <div class="modal-footer">
                <button class="btn btn-outline" onclick="closeModal('waterModal')">Cancel</button>
                <button class="btn btn-primary" onclick="saveWater()">Save</button>
            </div>
        </div>
    </div>

    <!-- Steps Modal -->
    <div class="modal-overlay" id="stepsModal">
        <div class="modal">
            <div class="modal-header">
                <h3 class="modal-title"><i class="fas fa-shoe-prints text-secondary"></i> Add Steps</h3>
                <button class="modal-close" onclick="closeModal('stepsModal')">&times;</button>
            </div>
            <div class="modal-body">
                <div class="form-group">
                    <label class="form-label">Steps to Add</label>
                    <input type="number" id="stepsToAdd" class="form-control" min="0" placeholder="Enter steps">
                </div>
            </div>
            <div class="modal-footer">
                <button class="btn btn-outline" onclick="closeModal('stepsModal')">Cancel</button>
                <button class="btn btn-secondary" onclick="saveSteps()">Add Steps</button>
            </div>
        </div>
    </div>

    <script>
        // Chart.js - Activity Chart
        const ctx = document.getElementById('activityChart').getContext('2d');
        const activityChart = new Chart(ctx, {
            type: 'bar',
            data: {
                labels: <?= json_encode($chartData['labels']) ?>,
                datasets: [
                    {
                        label: 'Steps (÷100)',
                        data: <?= json_encode(array_map(fn($s) => $s / 100, $chartData['steps'])) ?>,
                        backgroundColor: 'rgba(102, 126, 234, 0.8)',
                        borderRadius: 8
                    },
                    {
                        label: 'Exercise (mins)',
                        data: <?= json_encode($chartData['exercise']) ?>,
                        backgroundColor: 'rgba(237, 100, 166, 0.8)',
                        borderRadius: 8
                    },
                    {
                        label: 'Water (glasses)',
                        data: <?= json_encode($chartData['water']) ?>,
                        backgroundColor: 'rgba(72, 187, 120, 0.8)',
                        borderRadius: 8
                    }
                ]
            },
            options: {
                responsive: true,
                maintainAspectRatio: false,
                plugins: {
                    legend: { position: 'top' }
                },
                scales: {
                    y: { beginAtZero: true }
                }
            }
        });

        // Modal Functions
        function openWaterModal() {
            document.getElementById('waterModal').classList.add('active');
        }

        function openStepsModal() {
            document.getElementById('stepsModal').classList.add('active');
        }

        function closeModal(id) {
            document.getElementById(id).classList.remove('active');
        }

        // Water Tracker
        function updateWater(glasses) {
            fetch('api/update_water.php', {
                method: 'POST',
                headers: { 'Content-Type': 'application/json' },
                body: JSON.stringify({ glasses: glasses })
            })
                .then(res => res.json())
                .then(data => {
                    if (data.success) {
                        document.querySelectorAll('.water-glass').forEach((glass, index) => {
                            glass.classList.toggle('filled', index < glasses);
                        });
                    }
                });
        }

        function saveWater() {
            const glasses = document.getElementById('waterGlasses').value;
            updateWater(parseInt(glasses));
            closeModal('waterModal');
        }

        function saveSteps() {
            const steps = document.getElementById('stepsToAdd').value;
            fetch('api/add_steps.php', {
                method: 'POST',
                headers: { 'Content-Type': 'application/json' },
                body: JSON.stringify({ steps: parseInt(steps) })
            })
                .then(res => res.json())
                .then(data => {
                    if (data.success) {
                        location.reload();
                    }
                });
            closeModal('stepsModal');
        }

        // Sidebar Toggle
        document.getElementById('sidebarToggle')?.addEventListener('click', () => {
            document.getElementById('sidebar').classList.toggle('active');
        });
    </script>

    <!-- Alarm System for Reminders -->
    <script src="assets/js/alarm.js"></script>
</body>

</html>