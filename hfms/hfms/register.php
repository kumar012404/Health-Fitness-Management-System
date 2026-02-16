<?php
/**
 * Registration Page - Health and Fitness Management System
 */

require_once 'config/config.php';
require_once 'includes/User.php';

// Redirect if already logged in
if (isLoggedIn()) {
    redirect(SITE_URL . '/dashboard.php');
}

$errors = [];
$success = '';
$formData = ['username' => '', 'email' => ''];

// Handle registration form submission
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    if (!validateCSRFToken($_POST['csrf_token'] ?? '')) {
        $errors[] = 'Invalid request. Please try again.';
    } else {
        $username = sanitizeInput($_POST['username'] ?? '');
        $email = sanitizeInput($_POST['email'] ?? '');
        $password = $_POST['password'] ?? '';
        $confirmPassword = $_POST['confirm_password'] ?? '';

        $formData = ['username' => $username, 'email' => $email];

        // Validate confirm password
        if ($password !== $confirmPassword) {
            $errors[] = 'Passwords do not match.';
        }

        if (empty($errors)) {
            $user = new User();
            $result = $user->register($username, $email, $password);

            if ($result['success']) {
                setFlashMessage('success', $result['message']);
                redirect(SITE_URL . '/login.php');
            } else {
                $errors = $result['errors'];
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
    <title>Register - HFMS</title>
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
            background: linear-gradient(135deg, #48bb78 0%, #38a169 100%);
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
            max-width: 420px;
        }

        .auth-form-container h2 {
            margin-bottom: 0.5rem;
        }

        .auth-form-container .subtitle {
            color: var(--gray-600);
            margin-bottom: 2rem;
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

        .auth-form-container .form-control {
            padding: 1rem 1rem 1rem 3rem;
        }

        .password-strength {
            margin-top: 0.5rem;
        }

        .strength-bar {
            height: 4px;
            background: var(--gray-200);
            border-radius: 2px;
            overflow: hidden;
        }

        .strength-fill {
            height: 100%;
            transition: all 0.3s;
        }

        .strength-text {
            font-size: 0.8rem;
            margin-top: 0.25rem;
        }

        .auth-links {
            text-align: center;
            margin-top: 2rem;
            color: var(--gray-600);
        }

        .auth-links a {
            color: var(--secondary-dark);
            font-weight: 600;
        }

        .requirements {
            font-size: 0.85rem;
            color: var(--gray-600);
            margin-top: 0.5rem;
        }

        .requirements li {
            margin-bottom: 0.25rem;
        }

        .requirements li.valid {
            color: var(--success);
        }

        .requirements li.valid::before {
            content: '✓ ';
        }

        .requirements li.invalid {
            color: var(--gray-500);
        }

        .requirements li.invalid::before {
            content: '○ ';
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
            <h1>Join HFMS Today!</h1>
            <p>Create your account and start your journey towards a healthier, fitter lifestyle.</p>
            <ul style="margin-top: 2rem; list-style: none;">
                <li style="margin-bottom: 1rem;"><i class="fas fa-check-circle" style="margin-right: 0.5rem;"></i> Track
                    your health metrics</li>
                <li style="margin-bottom: 1rem;"><i class="fas fa-check-circle" style="margin-right: 0.5rem;"></i> Get
                    personalized recommendations</li>
                <li style="margin-bottom: 1rem;"><i class="fas fa-check-circle" style="margin-right: 0.5rem;"></i>
                    Visualize your progress</li>
                <li><i class="fas fa-check-circle" style="margin-right: 0.5rem;"></i> Stay motivated with reminders</li>
            </ul>
        </div>

        <div class="auth-right">
            <div class="auth-form-container">
                <h2>Create Account</h2>
                <p class="subtitle">Fill in the details to get started</p>

                <?php if (!empty($errors)): ?>
                    <div class="alert alert-danger">
                        <i class="fas fa-exclamation-circle"></i>
                        <div>
                            <?php foreach ($errors as $error): ?>
                                <div><?= htmlspecialchars($error) ?></div>
                            <?php endforeach; ?>
                        </div>
                    </div>
                <?php endif; ?>

                <form method="POST" action="" id="registerForm">
                    <input type="hidden" name="csrf_token" value="<?= generateCSRFToken() ?>">

                    <div class="form-group">
                        <label class="form-label">Username</label>
                        <div class="input-icon">
                            <i class="fas fa-user"></i>
                            <input type="text" name="username" class="form-control" placeholder="Choose a username"
                                value="<?= htmlspecialchars($formData['username']) ?>" required>
                        </div>
                    </div>

                    <div class="form-group">
                        <label class="form-label">Email Address</label>
                        <div class="input-icon">
                            <i class="fas fa-envelope"></i>
                            <input type="email" name="email" class="form-control" placeholder="Enter your email"
                                value="<?= htmlspecialchars($formData['email']) ?>" required>
                        </div>
                    </div>

                    <div class="form-group">
                        <label class="form-label">Password</label>
                        <div class="input-icon">
                            <i class="fas fa-lock"></i>
                            <input type="password" name="password" id="password" class="form-control"
                                placeholder="Create a password" required>
                        </div>
                        <div class="password-strength">
                            <div class="strength-bar">
                                <div class="strength-fill" id="strengthFill"></div>
                            </div>
                            <div class="strength-text" id="strengthText"></div>
                        </div>
                        <ul class="requirements" id="requirements">
                            <li id="req-length" class="invalid">At least 8 characters</li>
                            <li id="req-upper" class="invalid">One uppercase letter</li>
                            <li id="req-lower" class="invalid">One lowercase letter</li>
                            <li id="req-number" class="invalid">One number</li>
                        </ul>
                    </div>

                    <div class="form-group">
                        <label class="form-label">Confirm Password</label>
                        <div class="input-icon">
                            <i class="fas fa-lock"></i>
                            <input type="password" name="confirm_password" id="confirmPassword" class="form-control"
                                placeholder="Confirm your password" required>
                        </div>
                        <div class="form-text" id="passwordMatch"></div>
                    </div>

                    <button type="submit" class="btn btn-secondary btn-block btn-lg">
                        <i class="fas fa-user-plus"></i>
                        Create Account
                    </button>
                </form>

                <div class="auth-links">
                    <p>Already have an account? <a href="login.php">Sign In</a></p>
                </div>
            </div>
        </div>
    </div>

    <script>
        // Password strength checker
        const password = document.getElementById('password');
        const confirmPassword = document.getElementById('confirmPassword');
        const strengthFill = document.getElementById('strengthFill');
        const strengthText = document.getElementById('strengthText');
        const passwordMatch = document.getElementById('passwordMatch');

        password.addEventListener('input', function () {
            const value = this.value;
            let strength = 0;

            // Check requirements
            const hasLength = value.length >= 8;
            const hasUpper = /[A-Z]/.test(value);
            const hasLower = /[a-z]/.test(value);
            const hasNumber = /[0-9]/.test(value);

            document.getElementById('req-length').className = hasLength ? 'valid' : 'invalid';
            document.getElementById('req-upper').className = hasUpper ? 'valid' : 'invalid';
            document.getElementById('req-lower').className = hasLower ? 'valid' : 'invalid';
            document.getElementById('req-number').className = hasNumber ? 'valid' : 'invalid';

            if (hasLength) strength += 25;
            if (hasUpper) strength += 25;
            if (hasLower) strength += 25;
            if (hasNumber) strength += 25;

            strengthFill.style.width = strength + '%';

            if (strength <= 25) {
                strengthFill.style.background = '#f56565';
                strengthText.textContent = 'Weak';
                strengthText.style.color = '#f56565';
            } else if (strength <= 50) {
                strengthFill.style.background = '#ecc94b';
                strengthText.textContent = 'Fair';
                strengthText.style.color = '#ecc94b';
            } else if (strength <= 75) {
                strengthFill.style.background = '#4299e1';
                strengthText.textContent = 'Good';
                strengthText.style.color = '#4299e1';
            } else {
                strengthFill.style.background = '#48bb78';
                strengthText.textContent = 'Strong';
                strengthText.style.color = '#48bb78';
            }

            checkPasswordMatch();
        });

        confirmPassword.addEventListener('input', checkPasswordMatch);

        function checkPasswordMatch() {
            if (confirmPassword.value) {
                if (password.value === confirmPassword.value) {
                    passwordMatch.textContent = '✓ Passwords match';
                    passwordMatch.style.color = '#48bb78';
                } else {
                    passwordMatch.textContent = '✗ Passwords do not match';
                    passwordMatch.style.color = '#f56565';
                }
            } else {
                passwordMatch.textContent = '';
            }
        }
    </script>
</body>

</html>