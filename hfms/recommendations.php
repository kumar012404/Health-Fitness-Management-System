<?php
/**
 * Recommendations Page - Health and Fitness Management System
 */

require_once 'config/config.php';
require_once 'includes/User.php';
require_once 'includes/Recommendation.php';

requireLogin();

$userId = $_SESSION['user_id'];
$user = new User();
$recommendationClass = new Recommendation();

$userData = $user->getUserById($userId);
$hasProfile = $user->hasProfile($userId);
$recommendations = $recommendationClass->getRecommendations($userId);
?>
<!DOCTYPE html>
<html lang="en">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Recommendations - HFMS</title>
    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link
        href="https://fonts.googleapis.com/css2?family=Inter:wght@300;400;500;600;700&family=Poppins:wght@400;500;600;700&display=swap"
        rel="stylesheet">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">
    <link rel="stylesheet" href="assets/css/style.css">
    <style>
        .recommendation-card {
            background: #fff;
            border-radius: var(--radius-lg);
            padding: 1.5rem;
            margin-bottom: 1rem;
            border-left: 4px solid var(--primary);
            box-shadow: var(--shadow-sm);
            transition: var(--transition);
        }

        .recommendation-card:hover {
            box-shadow: var(--shadow);
            transform: translateX(5px);
        }

        .recommendation-card.fitness {
            border-left-color: #e74c3c;
        }

        .recommendation-card.diet {
            border-left-color: #2ecc71;
        }

        .recommendation-title {
            font-weight: 600;
            font-size: 1.1rem;
            margin-bottom: 0.5rem;
            display: flex;
            align-items: center;
            gap: 0.5rem;
        }

        .target-card {
            background: linear-gradient(135deg, #667eea 0%, #764ba2 100%);
            color: #fff;
            padding: 1.5rem;
            border-radius: var(--radius-lg);
            text-align: center;
        }

        .target-value {
            font-size: 2rem;
            font-weight: 700;
        }
    </style>
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
            <a href="recommendations.php" class="sidebar-link active"><i
                    class="fas fa-lightbulb"></i><span>Recommendations</span></a>
            <a href="reminders.php" class="sidebar-link"><i class="fas fa-bell"></i><span>Reminders</span></a>
            <a href="reports.php" class="sidebar-link"><i class="fas fa-chart-line"></i><span>Reports</span></a>
            <a href="logout.php" class="sidebar-link"><i class="fas fa-sign-out-alt"></i><span>Logout</span></a>
        </nav>
    </aside>

    <main class="main-content">
        <div class="page-header">
            <h1 class="page-title">Personalized Recommendations</h1>
            <p class="page-subtitle">Fitness and diet suggestions based on your profile</p>
        </div>

        <?php if (!$hasProfile): ?>
            <div class="alert alert-warning">
                <i class="fas fa-exclamation-triangle"></i>
                <div>
                    <strong>Profile Required</strong>
                    <p style="margin: 0;">Complete your health profile to get personalized recommendations.</p>
                    <a href="profile.php" class="btn btn-sm btn-warning mt-1">Complete Profile</a>
                </div>
            </div>
        <?php elseif (!$recommendations['success']): ?>
            <div class="alert alert-info">
                <i class="fas fa-info-circle"></i>
                <?= $recommendations['message'] ?>
            </div>
        <?php else: ?>

            <!-- User Summary -->
            <div class="card mb-3">
                <div class="card-body">
                    <div class="d-flex justify-between align-center" style="flex-wrap: wrap; gap: 1rem;">
                        <div>
                            <h4>Your Profile Summary</h4>
                            <p class="text-muted mb-0">
                                <strong>BMI Category:</strong>
                                <span
                                    class="badge badge-<?= ($recommendations['user_info']['bmi_category'] ?? 'normal') === 'normal' ? 'success' : 'warning' ?>">
                                    <?= ucfirst($recommendations['user_info']['bmi_category'] ?? 'Unknown') ?>
                                </span>
                                &nbsp;|&nbsp;
                                <strong>Goal:</strong>
                                <?= HEALTH_GOALS[$recommendations['user_info']['health_goal']] ?? 'Maintain' ?>
                                &nbsp;|&nbsp;
                                <strong>Activity:</strong>
                                <?= ucfirst($recommendations['user_info']['activity_level'] ?? 'Moderate') ?>
                            </p>
                        </div>
                        <a href="profile.php" class="btn btn-outline btn-sm">
                            <i class="fas fa-edit"></i> Update Profile
                        </a>
                    </div>
                </div>
            </div>

            <!-- Daily Targets -->
            <?php if (!empty($recommendations['daily_targets'])): ?>
                <h3 class="mb-2"><i class="fas fa-bullseye text-primary"></i> Your Daily Targets</h3>
                <div class="grid grid-cols-4 mb-3">
                    <div class="target-card">
                        <i class="fas fa-fire" style="font-size: 1.5rem; margin-bottom: 0.5rem;"></i>
                        <div class="target-value"><?= number_format($recommendations['daily_targets']['calories']) ?></div>
                        <div>Calories</div>
                    </div>
                    <div class="target-card" style="background: linear-gradient(135deg, #48bb78 0%, #38a169 100%);">
                        <i class="fas fa-shoe-prints" style="font-size: 1.5rem; margin-bottom: 0.5rem;"></i>
                        <div class="target-value"><?= number_format($recommendations['daily_targets']['steps']) ?></div>
                        <div>Steps</div>
                    </div>
                    <div class="target-card" style="background: linear-gradient(135deg, #4299e1 0%, #3182ce 100%);">
                        <i class="fas fa-tint" style="font-size: 1.5rem; margin-bottom: 0.5rem;"></i>
                        <div class="target-value"><?= $recommendations['daily_targets']['water_glasses'] ?></div>
                        <div>Glasses of Water</div>
                    </div>
                    <div class="target-card" style="background: linear-gradient(135deg, #ed64a6 0%, #d53f8c 100%);">
                        <i class="fas fa-clock" style="font-size: 1.5rem; margin-bottom: 0.5rem;"></i>
                        <div class="target-value"><?= $recommendations['daily_targets']['exercise_mins'] ?></div>
                        <div>Exercise Minutes</div>
                    </div>
                </div>
            <?php endif; ?>

            <div class="grid grid-cols-2">
                <!-- Fitness Recommendations -->
                <div>
                    <h3 class="mb-2"><i class="fas fa-dumbbell text-danger"></i> Fitness Recommendations</h3>
                    <?php if (empty($recommendations['fitness'])): ?>
                        <p class="text-muted">No fitness recommendations available.</p>
                    <?php else: ?>
                        <?php foreach ($recommendations['fitness'] as $rec): ?>
                            <div class="recommendation-card fitness">
                                <div class="recommendation-title">
                                    <i class="fas fa-running text-danger"></i>
                                    <?= htmlspecialchars($rec['title']) ?>
                                </div>
                                <p class="text-muted mb-0"><?= htmlspecialchars($rec['description']) ?></p>
                            </div>
                        <?php endforeach; ?>
                    <?php endif; ?>
                </div>

                <!-- Diet Recommendations -->
                <div>
                    <h3 class="mb-2"><i class="fas fa-utensils text-success"></i> Diet Recommendations</h3>
                    <?php if (empty($recommendations['diet'])): ?>
                        <p class="text-muted">No diet recommendations available.</p>
                    <?php else: ?>
                        <?php foreach ($recommendations['diet'] as $rec): ?>
                            <div class="recommendation-card diet">
                                <div class="recommendation-title">
                                    <i class="fas fa-apple-alt text-success"></i>
                                    <?= htmlspecialchars($rec['title']) ?>
                                </div>
                                <p class="text-muted mb-0"><?= htmlspecialchars($rec['description']) ?></p>
                            </div>
                        <?php endforeach; ?>
                    <?php endif; ?>
                </div>
            </div>

        <?php endif; ?>
    </main>

    <!-- Alarm System -->
    <script src="assets/js/alarm.js"></script>
</body>

</html>