<?php
/**
 * User Class
 * Health and Fitness Management System
 * 
 * Handles user authentication, registration, and profile management.
 */

require_once dirname(__DIR__) . '/config/config.php';

class User
{
    private $db;
    private $userId;
    private $username;
    private $email;

    /**
     * Constructor - Initialize database connection
     */
    public function __construct()
    {
        $this->db = getDBConnection();
    }

    /**
     * Register a new user
     * @param string $username
     * @param string $email
     * @param string $password
     * @return array - Result with success status and message
     */
    public function register($username, $email, $password)
    {
        // Validate input
        $errors = $this->validateRegistration($username, $email, $password);
        if (!empty($errors)) {
            return ['success' => false, 'errors' => $errors];
        }

        try {
            // Check if username exists
            $stmt = $this->db->prepare("SELECT user_id FROM users WHERE username = ?");
            $stmt->execute([$username]);
            if ($stmt->fetch()) {
                return ['success' => false, 'errors' => ['Username already exists.']];
            }

            // Check if email exists
            $stmt = $this->db->prepare("SELECT user_id FROM users WHERE email = ?");
            $stmt->execute([$email]);
            if ($stmt->fetch()) {
                return ['success' => false, 'errors' => ['Email already registered.']];
            }

            // Hash password
            $hashedPassword = password_hash($password, PASSWORD_BCRYPT, ['cost' => 12]);

            // Insert user
            $stmt = $this->db->prepare("
                INSERT INTO users (username, email, password, created_at) 
                VALUES (?, ?, ?, NOW())
            ");
            $stmt->execute([$username, $email, $hashedPassword]);

            $userId = $this->db->lastInsertId();

            return [
                'success' => true,
                'message' => 'Registration successful! Please login.',
                'user_id' => $userId
            ];

        } catch (PDOException $e) {
            error_log("Registration Error: " . $e->getMessage());
            return ['success' => false, 'errors' => ['Registration failed. Please try again.']];
        }
    }

    /**
     * Validate registration data
     * @param string $username
     * @param string $email
     * @param string $password
     * @return array - Array of errors
     */
    private function validateRegistration($username, $email, $password)
    {
        $errors = [];

        // Username validation
        if (empty($username)) {
            $errors[] = 'Username is required.';
        } elseif (strlen($username) < 3 || strlen($username) > 50) {
            $errors[] = 'Username must be between 3 and 50 characters.';
        } elseif (!preg_match('/^[a-zA-Z0-9_]+$/', $username)) {
            $errors[] = 'Username can only contain letters, numbers, and underscores.';
        }

        // Email validation
        if (empty($email)) {
            $errors[] = 'Email is required.';
        } elseif (!filter_var($email, FILTER_VALIDATE_EMAIL)) {
            $errors[] = 'Invalid email format.';
        }

        // Password validation
        if (empty($password)) {
            $errors[] = 'Password is required.';
        } elseif (strlen($password) < 8) {
            $errors[] = 'Password must be at least 8 characters.';
        } elseif (!preg_match('/[A-Z]/', $password)) {
            $errors[] = 'Password must contain at least one uppercase letter.';
        } elseif (!preg_match('/[a-z]/', $password)) {
            $errors[] = 'Password must contain at least one lowercase letter.';
        } elseif (!preg_match('/[0-9]/', $password)) {
            $errors[] = 'Password must contain at least one number.';
        }

        return $errors;
    }

    /**
     * Login user
     * @param string $email
     * @param string $password
     * @return array - Result with success status
     */
    public function login($email, $password)
    {
        if (empty($email) || empty($password)) {
            return ['success' => false, 'message' => 'Email and password are required.'];
        }

        try {
            $stmt = $this->db->prepare("
                SELECT user_id, username, email, password, role, is_active 
                FROM users WHERE email = ?
            ");
            $stmt->execute([$email]);
            $user = $stmt->fetch();

            if (!$user) {
                return ['success' => false, 'message' => 'Invalid email or password.'];
            }

            if (!$user['is_active']) {
                return ['success' => false, 'message' => 'Account is deactivated. Contact support.'];
            }

            if (!password_verify($password, $user['password'])) {
                return ['success' => false, 'message' => 'Invalid email or password.'];
            }

            // Regenerate session ID for security
            session_regenerate_id(true);

            // Set session variables
            $_SESSION['user_id'] = $user['user_id'];
            $_SESSION['username'] = $user['username'];
            $_SESSION['email'] = $user['email'];
            $_SESSION['role'] = $user['role'] ?? 'user';
            $_SESSION['logged_in'] = true;
            $_SESSION['last_activity'] = time();

            return ['success' => true, 'message' => 'Login successful!'];

        } catch (PDOException $e) {
            error_log("Login Error: " . $e->getMessage());
            return ['success' => false, 'message' => 'Login failed. Please try again.'];
        }
    }

    /**
     * Logout user
     */
    public function logout()
    {
        // Unset all session variables
        $_SESSION = array();

        // Destroy session cookie
        if (ini_get("session.use_cookies")) {
            $params = session_get_cookie_params();
            setcookie(
                session_name(),
                '',
                time() - 42000,
                $params["path"],
                $params["domain"],
                $params["secure"],
                $params["httponly"]
            );
        }

        // Destroy session
        session_destroy();
    }

    /**
     * Check if current user is admin
     * @return bool
     */
    public function isAdmin()
    {
        return isset($_SESSION['role']) && $_SESSION['role'] === 'admin';
    }

    /**
     * Get user by ID
     * @param int $userId
     * @return array|null
     */
    public function getUserById($userId)
    {
        try {
            $stmt = $this->db->prepare("
                SELECT u.user_id, u.username, u.email, u.created_at,
                       p.profile_id, p.full_name, p.date_of_birth, p.age, 
                       p.gender, p.height_cm, p.weight_kg, p.activity_level,
                       p.health_goal, p.medical_conditions, p.profile_image
                FROM users u
                LEFT JOIN user_profiles p ON u.user_id = p.user_id
                WHERE u.user_id = ?
            ");
            $stmt->execute([$userId]);
            return $stmt->fetch();
        } catch (PDOException $e) {
            error_log("Get User Error: " . $e->getMessage());
            return null;
        }
    }

    /**
     * Check if user has profile
     * @param int $userId
     * @return bool
     */
    public function hasProfile($userId)
    {
        try {
            $stmt = $this->db->prepare("SELECT profile_id FROM user_profiles WHERE user_id = ?");
            $stmt->execute([$userId]);
            return $stmt->fetch() !== false;
        } catch (PDOException $e) {
            return false;
        }
    }

    /**
     * Create or Update user profile
     * @param int $userId
     * @param array $data
     * @return array
     */
    public function saveProfile($userId, $data)
    {
        try {
            // Calculate age if date of birth provided
            $age = null;
            if (!empty($data['date_of_birth'])) {
                $age = calculateAge($data['date_of_birth']);
            }

            $hasProfile = $this->hasProfile($userId);

            if ($hasProfile) {
                // Update existing profile
                $stmt = $this->db->prepare("
                    UPDATE user_profiles SET
                        full_name = ?,
                        date_of_birth = ?,
                        age = ?,
                        gender = ?,
                        height_cm = ?,
                        weight_kg = ?,
                        activity_level = ?,
                        health_goal = ?,
                        medical_conditions = ?,
                        updated_at = NOW()
                    WHERE user_id = ?
                ");
                $stmt->execute([
                    $data['full_name'],
                    $data['date_of_birth'] ?: null,
                    $age,
                    $data['gender'],
                    $data['height_cm'],
                    $data['weight_kg'],
                    $data['activity_level'],
                    $data['health_goal'],
                    $data['medical_conditions'] ?? null,
                    $userId
                ]);
            } else {
                // Create new profile
                $stmt = $this->db->prepare("
                    INSERT INTO user_profiles 
                    (user_id, full_name, date_of_birth, age, gender, height_cm, 
                     weight_kg, activity_level, health_goal, medical_conditions)
                    VALUES (?, ?, ?, ?, ?, ?, ?, ?, ?, ?)
                ");
                $stmt->execute([
                    $userId,
                    $data['full_name'],
                    $data['date_of_birth'] ?: null,
                    $age,
                    $data['gender'],
                    $data['height_cm'],
                    $data['weight_kg'],
                    $data['activity_level'],
                    $data['health_goal'],
                    $data['medical_conditions'] ?? null
                ]);
            }

            // Calculate and store BMI
            $bmi = calculateBMI($data['weight_kg'], $data['height_cm']);
            $this->storeBMI($userId, $data['height_cm'], $data['weight_kg'], $bmi['value'], $bmi['category']);

            return ['success' => true, 'message' => 'Profile saved successfully!'];

        } catch (PDOException $e) {
            error_log("Save Profile Error: " . $e->getMessage());
            return ['success' => false, 'message' => 'Failed to save profile. Please try again.'];
        }
    }

    /**
     * Store BMI record
     * @param int $userId
     * @param float $height
     * @param float $weight
     * @param float $bmiValue
     * @param string $category
     */
    private function storeBMI($userId, $height, $weight, $bmiValue, $category)
    {
        try {
            $stmt = $this->db->prepare("
                INSERT INTO bmi_records (user_id, height_cm, weight_kg, bmi_value, bmi_category)
                VALUES (?, ?, ?, ?, ?)
            ");
            $stmt->execute([$userId, $height, $weight, $bmiValue, $category]);
        } catch (PDOException $e) {
            error_log("Store BMI Error: " . $e->getMessage());
        }
    }

    /**
     * Change user password
     * @param int $userId
     * @param string $currentPassword
     * @param string $newPassword
     * @return array
     */
    public function changePassword($userId, $currentPassword, $newPassword)
    {
        try {
            // Get current password hash
            $stmt = $this->db->prepare("SELECT password FROM users WHERE user_id = ?");
            $stmt->execute([$userId]);
            $user = $stmt->fetch();

            if (!$user || !password_verify($currentPassword, $user['password'])) {
                return ['success' => false, 'message' => 'Current password is incorrect.'];
            }

            // Validate new password
            if (strlen($newPassword) < 8) {
                return ['success' => false, 'message' => 'New password must be at least 8 characters.'];
            }

            // Update password
            $hashedPassword = password_hash($newPassword, PASSWORD_BCRYPT, ['cost' => 12]);
            $stmt = $this->db->prepare("UPDATE users SET password = ? WHERE user_id = ?");
            $stmt->execute([$hashedPassword, $userId]);

            return ['success' => true, 'message' => 'Password changed successfully!'];

        } catch (PDOException $e) {
            error_log("Change Password Error: " . $e->getMessage());
            return ['success' => false, 'message' => 'Failed to change password.'];
        }
    }
}
?>