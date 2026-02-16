<?php
/**
 * Profile Page - Health and Fitness Management System
 */

require_once 'config/config.php';
require_once 'includes/User.php';

requireLogin();

$userId = $_SESSION['user_id'];
$user = new User();
$userData = $user->getUserById($userId);

$success = '';
$errors = [];

// Handle form submission
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    if (!validateCSRFToken($_POST['csrf_token'] ?? '')) {
        $errors[] = 'Invalid request.';
    } else {
        $data = [
            'full_name' => sanitizeInput($_POST['full_name'] ?? ''),
            'date_of_birth' => $_POST['date_of_birth'] ?? '',
            'gender' => $_POST['gender'] ?? '',
            'height_cm' => floatval($_POST['height_cm'] ?? 0),
            'weight_kg' => floatval($_POST['weight_kg'] ?? 0),
            'activity_level' => $_POST['activity_level'] ?? 'moderate',
            'health_goal' => $_POST['health_goal'] ?? 'maintain',
            'medical_conditions' => sanitizeInput($_POST['medical_conditions'] ?? '')
        ];

        // Validation
        if (empty($data['full_name']))
            $errors[] = 'Full name is required.';
        if (empty($data['gender']))
            $errors[] = 'Gender is required.';
        if ($data['height_cm'] <= 0)
            $errors[] = 'Valid height is required.';
        if ($data['weight_kg'] <= 0)
            $errors[] = 'Valid weight is required.';

        if (empty($errors)) {
            $result = $user->saveProfile($userId, $data);
            if ($result['success']) {
                $success = $result['message'];
                $userData = $user->getUserById($userId);
            } else {
                $errors[] = $result['message'];
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
    <title>My Profile - HFMS</title>
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
            <a href="dashboard.php" class="sidebar-brand">
                <i class="fas fa-heartbeat"></i>
                <span>HFMS</span>
            </a>
        </div>
        <nav class="sidebar-nav">
            <a href="dashboard.php" class="sidebar-link"><i class="fas fa-home"></i><span>Dashboard</span></a>
            <a href="profile.php" class="sidebar-link active"><i class="fas fa-user"></i><span>My Profile</span></a>
            <a href="bmi.php" class="sidebar-link"><i class="fas fa-weight"></i><span>BMI Calculator</span></a>
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
            <h1 class="page-title">My Profile</h1>
            <p class="page-subtitle">Manage your health profile information</p>
        </div>

        <?php if ($success): ?>
            <div class="alert alert-success"><i class="fas fa-check-circle"></i> <?= htmlspecialchars($success) ?></div>
        <?php endif; ?>

        <?php if (!empty($errors)): ?>
            <div class="alert alert-danger">
                <i class="fas fa-exclamation-circle"></i>
                <div><?php foreach ($errors as $e)
                    echo htmlspecialchars($e) . '<br>'; ?></div>
            </div>
        <?php endif; ?>

        <div class="grid grid-cols-3">
            <!-- Profile Card -->
            <div class="card">
                <div class="card-body text-center">
                    <div
                        style="width: 120px; height: 120px; background: var(--gradient-primary); border-radius: 50%; display: flex; align-items: center; justify-content: center; margin: 0 auto 1rem; font-size: 3rem; color: #fff;">
                        <i class="fas fa-user"></i>
                    </div>
                    <h3><?= htmlspecialchars($userData['full_name'] ?? $userData['username']) ?></h3>
                    <p class="text-muted"><?= htmlspecialchars($userData['email']) ?></p>

                    <?php if ($userData['height_cm'] && $userData['weight_kg']): ?>
                        <div class="mt-2" style="display: flex; justify-content: center; gap: 1.5rem;">
                            <div>
                                <div style="font-size: 1.5rem; font-weight: 700; color: var(--primary);">
                                    <?= $userData['height_cm'] ?><small>cm</small>
                                </div>
                                <small class="text-muted">Height</small>
                            </div>
                            <div>
                                <div style="font-size: 1.5rem; font-weight: 700; color: var(--secondary);">
                                    <?= $userData['weight_kg'] ?><small>kg</small>
                                </div>
                                <small class="text-muted">Weight</small>
                            </div>
                        </div>
                    <?php endif; ?>

                    <p class="text-muted mt-2" style="font-size: 0.85rem;">
                        Member since <?= formatDate($userData['created_at'], 'M Y') ?>
                    </p>
                </div>
            </div>

            <!-- Profile Form -->
            <div class="card" style="grid-column: span 2;">
                <div class="card-header">
                    <h4><i class="fas fa-edit"></i> Edit Profile</h4>
                </div>
                <div class="card-body">
                    <form method="POST">
                        <input type="hidden" name="csrf_token" value="<?= generateCSRFToken() ?>">

                        <div class="grid grid-cols-2">
                            <div class="form-group">
                                <label class="form-label">Full Name *</label>
                                <input type="text" name="full_name" class="form-control"
                                    value="<?= htmlspecialchars($userData['full_name'] ?? '') ?>" required>
                            </div>
                            <div class="form-group">
                                <label class="form-label">Date of Birth</label>
                                <input type="date" name="date_of_birth" class="form-control"
                                    value="<?= $userData['date_of_birth'] ?? '' ?>">
                            </div>
                        </div>

                        <div class="grid grid-cols-3">
                            <div class="form-group">
                                <label class="form-label">Gender *</label>
                                <select name="gender" class="form-control" required>
                                    <option value="">Select Gender</option>
                                    <option value="male" <?= ($userData['gender'] ?? '') === 'male' ? 'selected' : '' ?>>
                                        Male</option>
                                    <option value="female" <?= ($userData['gender'] ?? '') === 'female' ? 'selected' : '' ?>>Female</option>
                                    <option value="other" <?= ($userData['gender'] ?? '') === 'other' ? 'selected' : '' ?>>
                                        Other</option>
                                </select>
                            </div>
                            <div class="form-group">
                                <label class="form-label">Height (cm) *</label>
                                <input type="number" name="height_cm" class="form-control" step="0.1" min="50" max="250"
                                    value="<?= $userData['height_cm'] ?? '' ?>" required placeholder="e.g., 170">
                            </div>
                            <div class="form-group">
                                <label class="form-label">Weight (kg) *</label>
                                <input type="number" name="weight_kg" class="form-control" step="0.1" min="20" max="300"
                                    value="<?= $userData['weight_kg'] ?? '' ?>" required placeholder="e.g., 70">
                            </div>
                        </div>

                        <div class="grid grid-cols-2">
                            <div class="form-group">
                                <label class="form-label">Activity Level</label>
                                <select name="activity_level" class="form-control">
                                    <?php foreach (ACTIVITY_LEVELS as $key => $label): ?>
                                        <option value="<?= $key ?>" <?= ($userData['activity_level'] ?? 'moderate') === $key ? 'selected' : '' ?>><?= $label ?></option>
                                    <?php endforeach; ?>
                                </select>
                            </div>
                            <div class="form-group">
                                <label class="form-label">Health Goal</label>
                                <select name="health_goal" class="form-control">
                                    <?php foreach (HEALTH_GOALS as $key => $label): ?>
                                        <option value="<?= $key ?>" <?= ($userData['health_goal'] ?? 'maintain') === $key ? 'selected' : '' ?>><?= $label ?></option>
                                    <?php endforeach; ?>
                                </select>
                            </div>
                        </div>

                        <div class="form-group">
                            <label class="form-label">Medical Conditions (if any)</label>
                            <textarea name="medical_conditions" class="form-control" rows="3"
                                placeholder="List any medical conditions..."><?= htmlspecialchars($userData['medical_conditions'] ?? '') ?></textarea>
                        </div>

                        <button type="submit" class="btn btn-primary btn-lg">
                            <i class="fas fa-save"></i> Save Profile
                        </button>
                    </form>
                </div>
            </div>
        </div>
    </main>

    <!-- Alarm System -->
    <script src="assets/js/alarm.js"></script>
</body>

</html>