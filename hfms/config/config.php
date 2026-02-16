<?php
/**
 * Main Configuration File
 * Health and Fitness Management System
 * 
 * Contains global configuration settings, constants, and helper functions.
 */

// Start session if not already started
if (session_status() === PHP_SESSION_NONE) {
    // Configure session security
    ini_set('session.cookie_httponly', 1);
    ini_set('session.use_only_cookies', 1);
    ini_set('session.cookie_secure', 0); // Set to 1 in production with HTTPS

    session_start();
}

// Error Reporting (Set to 0 in production)
error_reporting(E_ALL);
ini_set('display_errors', 1);

// Timezone Setting
date_default_timezone_set('Asia/Kolkata');

// Site Configuration
define('SITE_NAME', 'Health & Fitness Management System');
define('SITE_URL', 'http://localhost/hfms');
define('SITE_EMAIL', 'admin@hfms.com');

// Directory Paths
define('ROOT_PATH', dirname(__DIR__) . '/');
define('CONFIG_PATH', ROOT_PATH . 'config/');
define('INCLUDES_PATH', ROOT_PATH . 'includes/');
define('ASSETS_PATH', ROOT_PATH . 'assets/');
define('UPLOADS_PATH', ROOT_PATH . 'uploads/');

// Include Database Configuration
require_once CONFIG_PATH . 'database.php';

// Session Timeout (30 minutes)
define('SESSION_TIMEOUT', 1800);

// BMI Categories
define('BMI_CATEGORIES', [
    'underweight' => ['min' => 0, 'max' => 18.49, 'label' => 'Underweight', 'color' => '#3498db'],
    'normal' => ['min' => 18.5, 'max' => 24.99, 'label' => 'Normal Weight', 'color' => '#2ecc71'],
    'overweight' => ['min' => 25, 'max' => 29.99, 'label' => 'Overweight', 'color' => '#f39c12'],
    'obese' => ['min' => 30, 'max' => 100, 'label' => 'Obese', 'color' => '#e74c3c']
]);

// Activity Levels
define('ACTIVITY_LEVELS', [
    'sedentary' => 'Sedentary (Little or no exercise)',
    'light' => 'Light (Exercise 1-3 days/week)',
    'moderate' => 'Moderate (Exercise 3-5 days/week)',
    'active' => 'Active (Exercise 6-7 days/week)',
    'very_active' => 'Very Active (Hard exercise daily)'
]);

// Health Goals
define('HEALTH_GOALS', [
    'lose_weight' => 'Lose Weight',
    'gain_weight' => 'Gain Weight',
    'maintain' => 'Maintain Weight',
    'build_muscle' => 'Build Muscle',
    'improve_fitness' => 'Improve Overall Fitness'
]);

// Reminder Categories
define('REMINDER_CATEGORIES', [
    'exercise' => ['label' => 'Exercise', 'icon' => 'fa-dumbbell', 'color' => '#e74c3c'],
    'water' => ['label' => 'Water Intake', 'icon' => 'fa-tint', 'color' => '#3498db'],
    'medication' => ['label' => 'Medication', 'icon' => 'fa-pills', 'color' => '#9b59b6'],
    'meal' => ['label' => 'Meal', 'icon' => 'fa-utensils', 'color' => '#2ecc71'],
    'sleep' => ['label' => 'Sleep', 'icon' => 'fa-moon', 'color' => '#34495e'],
    'other' => ['label' => 'Other', 'icon' => 'fa-bell', 'color' => '#95a5a6']
]);

// Default Daily Goals
define('DEFAULT_GOALS', [
    'steps' => 10000,
    'water_glasses' => 8,
    'exercise_mins' => 30,
    'calories' => 500
]);

/**
 * CSRF Token Generation and Validation
 */
function generateCSRFToken()
{
    if (empty($_SESSION['csrf_token'])) {
        $_SESSION['csrf_token'] = bin2hex(random_bytes(32));
    }
    return $_SESSION['csrf_token'];
}

function validateCSRFToken($token)
{
    return isset($_SESSION['csrf_token']) && hash_equals($_SESSION['csrf_token'], $token);
}

/**
 * Sanitize Input Data
 * @param string $data - Input data to sanitize
 * @return string - Sanitized data
 */
function sanitizeInput($data)
{
    $data = trim($data);
    $data = stripslashes($data);
    $data = htmlspecialchars($data, ENT_QUOTES, 'UTF-8');
    return $data;
}

/**
 * Redirect to URL
 * @param string $url - URL to redirect to
 */
function redirect($url)
{
    header("Location: " . $url);
    exit();
}

/**
 * Check if user is logged in
 * @return bool
 */
function isLoggedIn()
{
    return isset($_SESSION['user_id']) && !empty($_SESSION['user_id']);
}

/**
 * Require user to be logged in
 * Redirects to login page if not authenticated
 */
function requireLogin()
{
    if (!isLoggedIn()) {
        $_SESSION['flash_message'] = [
            'type' => 'warning',
            'message' => 'Please login to access this page.'
        ];
        redirect(SITE_URL . '/login.php');
    }

    // Check session timeout
    if (
        isset($_SESSION['last_activity']) &&
        (time() - $_SESSION['last_activity'] > SESSION_TIMEOUT)
    ) {
        session_unset();
        session_destroy();
        session_start();
        $_SESSION['flash_message'] = [
            'type' => 'warning',
            'message' => 'Session expired. Please login again.'
        ];
        redirect(SITE_URL . '/login.php');
    }

    // Update last activity time
    $_SESSION['last_activity'] = time();
}

/**
 * Set Flash Message
 * @param string $type - Message type (success, error, warning, info)
 * @param string $message - Message content
 */
function setFlashMessage($type, $message)
{
    $_SESSION['flash_message'] = [
        'type' => $type,
        'message' => $message
    ];
}

/**
 * Get and Clear Flash Message
 * @return array|null
 */
function getFlashMessage()
{
    if (isset($_SESSION['flash_message'])) {
        $message = $_SESSION['flash_message'];
        unset($_SESSION['flash_message']);
        return $message;
    }
    return null;
}

/**
 * Calculate BMI
 * @param float $weight - Weight in kg
 * @param float $height - Height in cm
 * @return array - BMI value and category
 */
function calculateBMI($weight, $height)
{
    $heightM = $height / 100; // Convert cm to meters
    $bmi = $weight / ($heightM * $heightM);
    $bmi = round($bmi, 2);

    // Determine category
    $category = 'normal';
    if ($bmi < 18.5) {
        $category = 'underweight';
    } elseif ($bmi >= 25 && $bmi < 30) {
        $category = 'overweight';
    } elseif ($bmi >= 30) {
        $category = 'obese';
    }

    return [
        'value' => $bmi,
        'category' => $category,
        'label' => BMI_CATEGORIES[$category]['label'],
        'color' => BMI_CATEGORIES[$category]['color']
    ];
}

/**
 * Format Date for Display
 * @param string $date - Date string
 * @param string $format - Output format
 * @return string
 */
function formatDate($date, $format = 'd M Y')
{
    return date($format, strtotime($date));
}

/**
 * Get User's Current Age from Date of Birth
 * @param string $dob - Date of birth
 * @return int
 */
function calculateAge($dob)
{
    $birthDate = new DateTime($dob);
    $today = new DateTime('today');
    $age = $birthDate->diff($today)->y;
    return $age;
}

/**
 * Generate Random Color
 * @return string - Hex color code
 */
function getRandomColor()
{
    $colors = ['#3498db', '#2ecc71', '#e74c3c', '#f39c12', '#9b59b6', '#1abc9c', '#e91e63', '#00bcd4'];
    return $colors[array_rand($colors)];
}
?>