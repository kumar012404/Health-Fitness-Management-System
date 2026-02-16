<?php
/**
 * BMI Calculator Page - Health and Fitness Management System
 */

require_once 'config/config.php';
require_once 'includes/User.php';
require_once 'includes/BMI.php';

requireLogin();

$userId = $_SESSION['user_id'];
$user = new User();
$bmiClass = new BMI();

$userData = $user->getUserById($userId);
$bmiHistory = $bmiClass->getHistory($userId, 10);
$latestBmi = $bmiClass->getLatest($userId);
$trendData = $bmiClass->getTrendData($userId);

$result = null;
$errors = [];

// Handle form submission
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    if (!validateCSRFToken($_POST['csrf_token'] ?? '')) {
        $errors[] = 'Invalid request.';
    } else {
        $height = floatval($_POST['height'] ?? 0);
        $weight = floatval($_POST['weight'] ?? 0);
        $notes = sanitizeInput($_POST['notes'] ?? '');

        if ($height <= 0 || $height > 300)
            $errors[] = 'Enter valid height (50-300 cm).';
        if ($weight <= 0 || $weight > 500)
            $errors[] = 'Enter valid weight (20-500 kg).';

        if (empty($errors)) {
            $result = $bmiClass->calculateAndStore($userId, $weight, $height, $notes);
            if ($result['success']) {
                $bmiHistory = $bmiClass->getHistory($userId, 10);
                $latestBmi = $bmiClass->getLatest($userId);
                $trendData = $bmiClass->getTrendData($userId);
            }
        }
    }
}
?>
<!DOCTYPE html>
<html lang="en">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>BMI Calculator - HFMS</title>
    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link
        href="https://fonts.googleapis.com/css2?family=Inter:wght@300;400;500;600;700&family=Poppins:wght@400;500;600;700&display=swap"
        rel="stylesheet">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">
    <link rel="stylesheet" href="assets/css/style.css">
    <script src="https://cdn.jsdelivr.net/npm/chart.js"></script>
    <style>
        .bmi-result {
            text-align: center;
            padding: 2rem;
            background: var(--gray-50);
            border-radius: var(--radius-lg);
        }

        .bmi-value {
            font-size: 4rem;
            font-weight: 700;
            line-height: 1;
        }

        .bmi-scale {
            display: flex;
            height: 20px;
            border-radius: 10px;
            overflow: hidden;
            margin: 1.5rem 0;
        }

        .bmi-scale-section {
            flex: 1;
            position: relative;
        }

        .bmi-scale-section.underweight {
            background: #3498db;
        }

        .bmi-scale-section.normal {
            background: #2ecc71;
        }

        .bmi-scale-section.overweight {
            background: #f39c12;
        }

        .bmi-scale-section.obese {
            background: #e74c3c;
        }

        .bmi-pointer {
            position: absolute;
            top: -8px;
            width: 4px;
            height: 36px;
            background: var(--dark);
            border-radius: 2px;
            transform: translateX(-50%);
            transition: left 0.5s ease;
        }

        .advice-card {
            background: #fff;
            border-radius: var(--radius);
            padding: 1rem;
            margin-bottom: 0.75rem;
            border-left: 4px solid var(--primary);
        }
    </style>
</head>

<body>
    <!-- Sidebar -->
    <aside class="sidebar">
        <div class="sidebar-header">
            <a href="dashboard.php" class="sidebar-brand">
                <i class="fas fa-heartbeat"></i><span>HFMS</span>
            </a>
        </div>
        <nav class="sidebar-nav">
            <a href="dashboard.php" class="sidebar-link"><i class="fas fa-home"></i><span>Dashboard</span></a>
            <a href="profile.php" class="sidebar-link"><i class="fas fa-user"></i><span>My Profile</span></a>
            <a href="bmi.php" class="sidebar-link active"><i class="fas fa-weight"></i><span>BMI Calculator</span></a>
            <a href="activity.php" class="sidebar-link"><i class="fas fa-running"></i><span>Activity Log</span></a>
            <a href="recommendations.php" class="sidebar-link"><i
                    class="fas fa-lightbulb"></i><span>Recommendations</span></a>
            <a href="reminders.php" class="sidebar-link"><i class="fas fa-bell"></i><span>Reminders</span></a>
            <a href="reports.php" class="sidebar-link"><i class="fas fa-chart-line"></i><span>Reports</span></a>
            <a href="logout.php" class="sidebar-link"><i class="fas fa-sign-out-alt"></i><span>Logout</span></a>
        </nav>
    </aside>

    <main class="main-content">
        <div class="page-header">
            <h1 class="page-title">BMI Calculator</h1>
            <p class="page-subtitle">Calculate and track your Body Mass Index</p>
        </div>

        <?php if (!empty($errors)): ?>
            <div class="alert alert-danger">
                <i class="fas fa-exclamation-circle"></i>
                <?php foreach ($errors as $e)
                    echo htmlspecialchars($e) . ' '; ?>
            </div>
        <?php endif; ?>

        <div class="grid grid-cols-2">
            <!-- Calculator Form -->
            <div class="card">
                <div class="card-header">
                    <h4><i class="fas fa-calculator"></i> Calculate BMI</h4>
                </div>
                <div class="card-body">
                    <form method="POST" id="bmiForm">
                        <input type="hidden" name="csrf_token" value="<?= generateCSRFToken() ?>">

                        <div class="form-group">
                            <label class="form-label">Height (cm)</label>
                            <input type="number" name="height" id="height" class="form-control" step="0.1" min="50"
                                max="300" value="<?= $userData['height_cm'] ?? '' ?>" required placeholder="e.g., 170">
                            <div class="form-text">Enter your height in centimeters</div>
                        </div>

                        <div class="form-group">
                            <label class="form-label">Weight (kg)</label>
                            <input type="number" name="weight" id="weight" class="form-control" step="0.1" min="20"
                                max="500" value="<?= $userData['weight_kg'] ?? '' ?>" required placeholder="e.g., 70">
                            <div class="form-text">Enter your weight in kilograms</div>
                        </div>

                        <div class="form-group">
                            <label class="form-label">Notes (optional)</label>
                            <input type="text" name="notes" class="form-control"
                                placeholder="Any notes for this reading">
                        </div>

                        <button type="submit" class="btn btn-primary btn-lg btn-block">
                            <i class="fas fa-calculator"></i> Calculate BMI
                        </button>
                    </form>

                    <!-- BMI Formula -->
                    <div class="mt-3 text-center">
                        <p class="text-muted"><strong>Formula:</strong> BMI = Weight (kg) ÷ Height (m)²</p>
                    </div>
                </div>
            </div>

            <!-- Result Display -->
            <div class="card">
                <div class="card-header">
                    <h4><i class="fas fa-chart-pie"></i> Your BMI Result</h4>
                </div>
                <div class="card-body">
                    <?php if ($result && $result['success']): ?>
                        <div class="bmi-result">
                            <div class="bmi-value" style="color: <?= $result['color'] ?>">
                                <?= $result['bmi'] ?>
                            </div>
                            <div class="badge mt-1"
                                style="background: <?= $result['color'] ?>20; color: <?= $result['color'] ?>; font-size: 1rem; padding: 0.5rem 1rem;">
                                <?= $result['label'] ?>
                            </div>

                            <!-- BMI Scale -->
                            <div class="bmi-scale mt-3">
                                <div class="bmi-scale-section underweight"></div>
                                <div class="bmi-scale-section normal"></div>
                                <div class="bmi-scale-section overweight"></div>
                                <div class="bmi-scale-section obese">
                                    <?php
                                    $bmiVal = $result['bmi'];
                                    $position = min(100, max(0, ($bmiVal - 15) / 25 * 100));
                                    ?>
                                </div>
                            </div>
                            <div class="bmi-categories">
                                <span>Underweight<br>&lt;18.5</span>
                                <span>Normal<br>18.5-24.9</span>
                                <span>Overweight<br>25-29.9</span>
                                <span>Obese<br>≥30</span>
                            </div>
                        </div>

                        <div class="mt-3">
                            <h5><i class="fas fa-lightbulb text-warning"></i> Health Advice</h5>
                            <p><?= $result['advice']['summary'] ?></p>
                            <div class="mt-2">
                                <?php foreach ($result['advice']['tips'] as $tip): ?>
                                    <div class="advice-card">
                                        <i class="fas fa-check-circle text-success"></i> <?= $tip ?>
                                    </div>
                                <?php endforeach; ?>
                            </div>
                        </div>
                    <?php elseif ($latestBmi): ?>
                        <div class="bmi-result">
                            <p class="text-muted mb-2">Your Latest BMI</p>
                            <div class="bmi-value" style="color: <?= $latestBmi['color'] ?>">
                                <?= $latestBmi['bmi_value'] ?>
                            </div>
                            <div class="badge mt-1"
                                style="background: <?= $latestBmi['color'] ?>20; color: <?= $latestBmi['color'] ?>;">
                                <?= $latestBmi['label'] ?>
                            </div>
                            <p class="text-muted mt-2" style="font-size: 0.85rem;">
                                Recorded on <?= formatDate($latestBmi['recorded_at'], 'd M Y, h:i A') ?>
                            </p>
                        </div>
                    <?php else: ?>
                        <div class="text-center text-muted" style="padding: 3rem;">
                            <i class="fas fa-calculator" style="font-size: 3rem; margin-bottom: 1rem; display: block;"></i>
                            <p>Enter your height and weight to calculate your BMI</p>
                        </div>
                    <?php endif; ?>
                </div>
            </div>
        </div>

        <!-- BMI History & Trend -->
        <div class="grid grid-cols-2 mt-3">
            <!-- Trend Chart -->
            <div class="card">
                <div class="card-header">
                    <h4><i class="fas fa-chart-line"></i> BMI Trend</h4>
                </div>
                <div class="card-body">
                    <div class="chart-container">
                        <canvas id="bmiTrendChart"></canvas>
                    </div>
                </div>
            </div>

            <!-- History Table -->
            <div class="card">
                <div class="card-header">
                    <h4><i class="fas fa-history"></i> BMI History</h4>
                </div>
                <div class="card-body" style="max-height: 350px; overflow-y: auto;">
                    <?php if (empty($bmiHistory)): ?>
                        <p class="text-center text-muted">No BMI records yet.</p>
                    <?php else: ?>
                        <table class="table">
                            <thead>
                                <tr>
                                    <th>Date</th>
                                    <th>BMI</th>
                                    <th>Weight</th>
                                    <th>Category</th>
                                </tr>
                            </thead>
                            <tbody>
                                <?php foreach ($bmiHistory as $record): ?>
                                    <tr>
                                        <td><?= formatDate($record['recorded_at'], 'd M Y') ?></td>
                                        <td><strong><?= $record['bmi_value'] ?></strong></td>
                                        <td><?= $record['weight_kg'] ?> kg</td>
                                        <td>
                                            <span
                                                class="badge badge-<?= $record['bmi_category'] === 'normal' ? 'success' : ($record['bmi_category'] === 'underweight' ? 'primary' : 'warning') ?>">
                                                <?= ucfirst($record['bmi_category']) ?>
                                            </span>
                                        </td>
                                    </tr>
                                <?php endforeach; ?>
                            </tbody>
                        </table>
                    <?php endif; ?>
                </div>
            </div>
        </div>
    </main>

    <script>
        // BMI Trend Chart
        const ctx = document.getElementById('bmiTrendChart').getContext('2d');
        new Chart(ctx, {
            type: 'line',
            data: {
                labels: <?= json_encode($trendData['labels']) ?>,
                datasets: [{
                    label: 'BMI',
                    data: <?= json_encode($trendData['bmi']) ?>,
                    borderColor: '#667eea',
                    backgroundColor: 'rgba(102, 126, 234, 0.1)',
                    fill: true,
                    tension: 0.4,
                    pointRadius: 6,
                    pointBackgroundColor: '#667eea'
                }]
            },
            options: {
                responsive: true,
                maintainAspectRatio: false,
                plugins: {
                    legend: { display: false }
                },
                scales: {
                    y: {
                        beginAtZero: false,
                        min: 15,
                        max: 40
                    }
                }
            }
        });
    </script>

    <!-- Alarm System -->
    <script src="assets/js/alarm.js"></script>
</body>

</html>