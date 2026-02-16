<?php
/**
 * Login Page - Health and Fitness Management System
 */

require_once 'config/config.php';
require_once 'includes/User.php';

// Redirect if already logged in
if (isLoggedIn()) {
    if (isset($_SESSION['role']) && $_SESSION['role'] === 'admin') {
        redirect(SITE_URL . '/admin/index.php');
    }
    redirect(SITE_URL . '/dashboard.php');
}

$error = '';
$error = '';
$success = '';

// Check if it's admin login mode
$isAdminMode = isset($_GET['role']) && $_GET['role'] === 'admin';
$pageTitle = $isAdminMode ? 'Admin Portal' : 'Login';
$welcomeTitle = $isAdminMode ? 'Admin Protocol' : 'Welcome Back!';
$welcomeText = $isAdminMode ? 'Secure access for system administrators only. Please log in to manage the application.' : 'Login to access your health dashboard, track your progress, and continue your fitness journey.';
$btnColor = $isAdminMode ? '#4f46e5' : ''; // Indigo for admin


// Handle login form submission
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    if (!validateCSRFToken($_POST['csrf_token'] ?? '')) {
        $error = 'Invalid request. Please try again.';
    } else {
        $email = sanitizeInput($_POST['email'] ?? '');
        $password = $_POST['password'] ?? '';

        $user = new User();
        $result = $user->login($email, $password);

        if ($result['success']) {
            if ($user->isAdmin()) {
                // Admin trying to login
                if ($isAdminMode) {
                    redirect(SITE_URL . '/admin/index.php');
                } else {
                    // Admin tried to login on User Page -> ERROR
                    $error = 'Admins must use the Admin Portal.';
                    $user->logout(); // Valid credentials but wrong door
                }
            } else {
                // Normal User trying to login
                if ($isAdminMode) {
                    // User tried to login on Admin Page -> ERROR
                    $error = 'Access Restricted: Admins only.';
                    $user->logout();
                } else {
                    redirect(SITE_URL . '/dashboard.php');
                }
            }
        } else {
            $error = $result['message'];
        }
    }
}

// Check for flash messages
$flash = getFlashMessage();
if ($flash && $flash['type'] === 'success') {
    $success = $flash['message'];
}
?>
<!DOCTYPE html>
<html lang="en">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title><?= $pageTitle ?> - HFMS</title>
    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link
        href="https://fonts.googleapis.com/css2?family=Inter:wght@300;400;500;600;700&family=Poppins:wght@400;500;600;700&display=swap"
        rel="stylesheet">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">
    <link rel="stylesheet" href="assets/css/style.css">
    <style>
        .auth-page {
            min-height: 100vh;
            display: flex;
            background: linear-gradient(135deg, #667eea 0%, #764ba2 100%);
        }

        .auth-left {
            flex: 1;
            display: flex;
            flex-direction: column;
            justify-content: center;
            align-items: center;
            padding: 3rem;
            color: #fff;
        }

        .auth-left h1 {
            font-size: 2.5rem;
            color: #fff;
            margin-bottom: 1rem;
        }

        .auth-left p {
            font-size: 1.1rem;
            opacity: 0.9;
            max-width: 400px;
            text-align: center;
        }

        .auth-right {
            flex: 1;
            display: flex;
            align-items: center;
            justify-content: center;
            padding: 3rem;
            background: #fff;
            border-radius: 30px 0 0 30px;
        }

        .auth-form-container {
            width: 100%;
            max-width: 400px;
        }

        .auth-form-container h2 {
            margin-bottom: 0.5rem;
        }

        .auth-form-container .subtitle {
            color: var(--gray-600);
            margin-bottom: 2rem;
        }

        .auth-form-container .form-group {
            margin-bottom: 1.5rem;
        }

        .auth-form-container .form-control {
            padding: 1rem 1rem 1rem 3rem;
        }

        .input-icon {
            position: relative;
        }

        .input-icon i {
            position: absolute;
            left: 1rem;
            top: 50%;
            transform: translateY(-50%);
            color: var(--gray-500);
        }

        .form-footer {
            display: flex;
            justify-content: space-between;
            align-items: center;
            margin-bottom: 1.5rem;
        }

        .remember-me {
            display: flex;
            align-items: center;
            gap: 0.5rem;
        }

        .forgot-link {
            color: var(--primary);
            font-weight: 500;
        }

        .auth-links {
            text-align: center;
            margin-top: 2rem;
            color: var(--gray-600);
        }

        .auth-links a {
            color: var(--primary);
            font-weight: 600;
        }

        @media (max-width: 992px) {
            .auth-left {
                display: none;
            }

            .auth-right {
                border-radius: 0;
            }
        }
    </style>
</head>

<body>
    <div class="auth-page">
        <div class="auth-left">
            <a href="index.php" style="margin-bottom: 2rem; font-size: 2rem;">
                <i class="fas fa-heartbeat"></i>
            </a>
            <h1><?= $welcomeTitle ?></h1>
            <p><?= $welcomeText ?></p>
        </div>

        <div class="auth-right">
            <div class="auth-form-container">
                <h2>Sign In</h2>
                <p class="subtitle">Enter your credentials to access your account</p>

                <?php if ($error): ?>
                    <div class="alert alert-danger">
                        <i class="fas fa-exclamation-circle"></i>
                        <?= htmlspecialchars($error) ?>
                    </div>
                <?php endif; ?>

                <?php if ($success): ?>
                    <div class="alert alert-success">
                        <i class="fas fa-check-circle"></i>
                        <?= htmlspecialchars($success) ?>
                    </div>
                <?php endif; ?>

                <form method="POST" action="" id="loginForm">
                    <input type="hidden" name="csrf_token" value="<?= generateCSRFToken() ?>">

                    <div class="form-group">
                        <label class="form-label">Email Address</label>
                        <div class="input-icon">
                            <i class="fas fa-envelope"></i>
                            <input type="email" name="email" class="form-control" placeholder="Enter your email"
                                required>
                        </div>
                    </div>

                    <div class="form-group">
                        <label class="form-label">Password</label>
                        <div class="input-icon">
                            <i class="fas fa-lock"></i>
                            <input type="password" name="password" class="form-control"
                                placeholder="Enter your password" required>
                        </div>
                    </div>

                    <div class="form-footer">
                        <label class="remember-me">
                            <input type="checkbox" name="remember">
                            <span>Remember me</span>
                        </label>
                    </div>

                    <button type="submit" class="btn btn-primary btn-block btn-lg" <?= $isAdminMode ? 'style="background: #1e3a8a; border-color: #1e3a8a;"' : '' ?>>
                        <i class="fas fa-sign-in-alt"></i>
                        <?= $isAdminMode ? 'Admin Login' : 'Sign In' ?>
                    </button>
                </form>

                <?php if (!$isAdminMode): ?>
                <div class="auth-links">
                    <p>Don't have an account? <a href="register.php">Create Account</a></p>
                </div>
                <?php endif; ?>
            </div>
        </div>
    </div>

    <script src="assets/js/validation.js"></script>
</body>

</html>