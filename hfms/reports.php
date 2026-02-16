<?php
/**
 * Reports Page - Health and Fitness Management System
 */

require_once 'config/config.php';
require_once 'includes/Activity.php';
require_once 'includes/BMI.php';

requireLogin();

$userId = $_SESSION['user_id'];
$activity = new Activity();
$bmi = new BMI();

$weeklySummary = $activity->getWeeklySummary($userId);
$monthlySummary = $activity->getMonthlySummary($userId);
$bmiTrend = $bmi->getTrendData($userId, 6);
$chartData = $activity->getChartData($userId);
?>
<!DOCTYPE html>
<html lang="en">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Reports - HFMS</title>
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
            <a href="reminders.php" class="sidebar-link"><i class="fas fa-bell"></i><span>Reminders</span></a>
            <a href="reports.php" class="sidebar-link active"><i class="fas fa-chart-line"></i><span>Reports</span></a>
            <a href="logout.php" class="sidebar-link"><i class="fas fa-sign-out-alt"></i><span>Logout</span></a>
        </nav>
    </aside>

    <main class="main-content">
        <div class="page-header">
            <h1 class="page-title">Progress Reports</h1>
            <p class="page-subtitle">Visualize your health and fitness journey</p>
        </div>

        <!-- Weekly Summary Stats -->
        <div class="grid grid-cols-4 mb-3">
            <div class="stat-card primary">
                <div class="icon"><i class="fas fa-shoe-prints"></i></div>
                <div class="value"><?= number_format($weeklySummary['summary']['total_steps'] ?? 0) ?></div>
                <div class="label">Weekly Steps</div>
                <small class="text-muted">Avg:
                    <?= number_format($weeklySummary['summary']['avg_steps'] ?? 0) ?>/day</small>
            </div>
            <div class="stat-card secondary">
                <div class="icon"><i class="fas fa-clock"></i></div>
                <div class="value"><?= $weeklySummary['summary']['total_exercise_mins'] ?? 0 ?></div>
                <div class="label">Exercise Minutes</div>
            </div>
            <div class="stat-card accent">
                <div class="icon"><i class="fas fa-fire"></i></div>
                <div class="value"><?= number_format($weeklySummary['summary']['total_calories'] ?? 0) ?></div>
                <div class="label">Calories Burned</div>
            </div>
            <div class="stat-card warning">
                <div class="icon"><i class="fas fa-tint"></i></div>
                <div class="value"><?= round($weeklySummary['summary']['avg_water'] ?? 0, 1) ?></div>
                <div class="label">Avg Water/Day</div>
            </div>
        </div>

        <div class="grid grid-cols-2 mb-3">
            <!-- Weekly Activity Chart -->
            <div class="card">
                <div class="card-header">
                    <h4><i class="fas fa-chart-bar"></i> Weekly Activity</h4>
                </div>
                <div class="card-body">
                    <div class="chart-container">
                        <canvas id="weeklyChart"></canvas>
                    </div>
                </div>
            </div>

            <!-- Activity Distribution -->
            <div class="card">
                <div class="card-header">
                    <h4><i class="fas fa-chart-pie"></i> Goal Completion</h4>
                </div>
                <div class="card-body">
                    <div class="chart-container">
                        <canvas id="goalChart"></canvas>
                    </div>
                </div>
            </div>
        </div>

        <div class="grid grid-cols-2 mb-3">
            <!-- BMI Trend -->
            <div class="card">
                <div class="card-header">
                    <h4><i class="fas fa-weight"></i> BMI Trend (6 Months)</h4>
                </div>
                <div class="card-body">
                    <div class="chart-container">
                        <canvas id="bmiChart"></canvas>
                    </div>
                </div>
            </div>

            <!-- Water Intake Trend -->
            <div class="card">
                <div class="card-header">
                    <h4><i class="fas fa-tint"></i> Water Intake (7 Days)</h4>
                </div>
                <div class="card-body">
                    <div class="chart-container">
                        <canvas id="waterChart"></canvas>
                    </div>
                </div>
            </div>
        </div>

        <!-- Calories Burned Histogram -->
+       <div class="card mb-3">
+            <div class="card-header">
+                <h4><i class="fas fa-chart-area"></i> Calories Burned Distribution (Histogram)</h4>
+            </div>
+            <div class="card-body">
+                <div class="chart-container" style="height: 300px;">
+                    <canvas id="caloriesHistogram"></canvas>
+                </div>
+            </div>
+        </div>
+
+        <!-- Monthly Summary -->
        <div class="card">
            <div class="card-header">
                <h4><i class="fas fa-calendar-alt"></i> Monthly Summary - <?= date('F Y') ?></h4>
            </div>
            <div class="card-body">
                <div class="grid grid-cols-4">
                    <div class="text-center">
                        <div style="font-size: 2rem; font-weight: 700; color: var(--primary);">
                            <?= number_format($monthlySummary['summary']['total_steps'] ?? 0) ?>
                        </div>
                        <div class="text-muted">Total Steps</div>
                    </div>
                    <div class="text-center">
                        <div style="font-size: 2rem; font-weight: 700; color: var(--secondary);">
                            <?= $monthlySummary['summary']['total_exercise_mins'] ?? 0 ?>
                        </div>
                        <div class="text-muted">Exercise Minutes</div>
                    </div>
                    <div class="text-center">
                        <div style="font-size: 2rem; font-weight: 700; color: var(--accent);">
                            <?= number_format($monthlySummary['summary']['total_calories'] ?? 0) ?>
                        </div>
                        <div class="text-muted">Calories Burned</div>
                    </div>
                    <div class="text-center">
                        <div style="font-size: 2rem; font-weight: 700; color: var(--info);">
                            <?= $monthlySummary['summary']['days_logged'] ?? 0 ?>
                        </div>
                        <div class="text-muted">Days Logged</div>
                    </div>
                </div>
            </div>
        </div>
    </main>

    <script>
        // Weekly Activity Chart
        new Chart(document.getElementById('weeklyChart'), {
            type: 'bar',
            data: {
                labels: <?= json_encode($chartData['labels']) ?>,
                datasets: [{
                    label: 'Steps (÷100)',
                    data: <?= json_encode(array_map(fn($s) => $s / 100, $chartData['steps'])) ?>,
                    backgroundColor: 'rgba(102, 126, 234, 0.8)',
                    borderRadius: 8
                }, {
                    label: 'Exercise (mins)',
                    data: <?= json_encode($chartData['exercise']) ?>,
                    backgroundColor: 'rgba(237, 100, 166, 0.8)',
                    borderRadius: 8
                }]
            },
            options: {
                responsive: true,
                maintainAspectRatio: false,
                plugins: { legend: { position: 'top' } },
                scales: { y: { beginAtZero: true } }
            }
        });

        // Goal Completion Doughnut
        const stepsGoal = <?= $weeklySummary['summary']['total_steps'] ?? 0 ?> / 70000 * 100;
        const exerciseGoal = <?= $weeklySummary['summary']['total_exercise_mins'] ?? 0 ?> / 210 * 100;
        const waterGoal = (<?= $weeklySummary['summary']['avg_water'] ?? 0 ?> / 8) * 100;

        new Chart(document.getElementById('goalChart'), {
            type: 'bar',
            data: {
                labels: ['Steps Goal', 'Exercise Goal', 'Water Goal'],
                datasets: [{
                    label: 'Achievement %',
                    data: [Math.min(100, stepsGoal), Math.min(100, exerciseGoal), Math.min(100, waterGoal)],
                    backgroundColor: ['rgba(102, 126, 234, 0.8)', 'rgba(237, 100, 166, 0.8)', 'rgba(72, 187, 120, 0.8)'],
                    borderRadius: 8,
                    barPercentage: 0.6
                }]
            },
            options: {
                responsive: true,
                maintainAspectRatio: false,
                plugins: {
                    legend: { display: false },
                    tooltip: {
                        callbacks: {
                            label: function (context) {
                                return context.label + ': ' + context.raw.toFixed(1) + '%';
                            }
                        }
                    }
                },
                scales: {
                    y: {
                        beginAtZero: true,
                        max: 100,
                        title: { display: true, text: 'Completion (%)' }
                    }
                }
            }
        });

        // BMI Trend
        new Chart(document.getElementById('bmiChart'), {
            type: 'line',
            data: {
                labels: <?= json_encode($bmiTrend['labels']) ?>,
                datasets: [{
                    label: 'BMI',
                    data: <?= json_encode($bmiTrend['bmi']) ?>,
                    borderColor: '#667eea',
                    backgroundColor: 'rgba(102, 126, 234, 0.1)',
                    fill: true,
                    tension: 0.4,
                    pointRadius: 6
                }]
            },
            options: {
                responsive: true,
                maintainAspectRatio: false,
                plugins: { legend: { display: false } },
                scales: { y: { min: 15, max: 40 } }
            }
        });

        // Water Chart
        new Chart(document.getElementById('waterChart'), {
            type: 'bar',
            data: {
                labels: <?= json_encode($chartData['labels']) ?>,
                datasets: [{
                    label: 'Glasses',
                    data: <?= json_encode($chartData['water']) ?>,
                    backgroundColor: 'rgba(66, 153, 225, 0.8)',
                    borderRadius: 8
                }]
            },
            options: {
                responsive: true,
                maintainAspectRatio: false,
                plugins: { legend: { display: false } },
                scales: { y: { beginAtZero: true, max: 12 } }
            }
        });

        // Calories Histogram (Frequency Distribution)
        const calories = <?= json_encode($chartData['calories']) ?>;
        const bins = [0, 100, 200, 300, 400, 500, 600];
        const counts = new Array(bins.length - 1).fill(0);
        
        calories.forEach(val => {
            for (let i = 0; i < bins.length - 1; i++) {
                if (val >= bins[i] && val < bins[i+1]) {
                    counts[i]++;
                    break;
                }
                if (i === bins.length - 2 && val >= bins[i+1]) {
                    counts[i]++; // Put larger values in the last bin
                }
            }
        });

        new Chart(document.getElementById('caloriesHistogram'), {
            type: 'bar',
            data: {
                labels: ['0-100', '100-200', '200-300', '300-400', '400-500', '500+'],
                datasets: [{
                    label: 'Frequency (Days)',
                    data: counts,
                    backgroundColor: 'rgba(72, 187, 120, 0.7)',
                    borderColor: 'rgba(72, 187, 120, 1)',
                    borderWidth: 1,
                    barPercentage: 1.0,
                    categoryPercentage: 1.0
                }]
            },
            options: {
                responsive: true,
                maintainAspectRatio: false,
                plugins: {
                    legend: { display: false },
                    tooltip: {
                        callbacks: {
                            label: function(context) {
                                return context.raw + ' days in this range';
                            }
                        }
                    }
                },
                scales: {
                    x: {
                        title: { display: true, text: 'Calories Burned Range' },
                        grid: { display: false }
                    },
                    y: {
                        beginAtZero: true,
                        ticks: { stepSize: 1 },
                        title: { display: true, text: 'Number of Days' }
                    }
                }
            }
        });
    </script>

    <!-- Alarm System -->
    <script src="assets/js/alarm.js"></script>
</body>

</html>