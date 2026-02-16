-- =====================================================
-- HEALTH AND FITNESS MANAGEMENT SYSTEM
-- Database Schema
-- MySQL Database Design
-- =====================================================

-- Create Database
CREATE DATABASE IF NOT EXISTS hfms_db;
USE hfms_db;

-- =====================================================
-- TABLE 1: USERS
-- Purpose: Store user authentication information
-- =====================================================
CREATE TABLE users (
    user_id INT AUTO_INCREMENT PRIMARY KEY,
    username VARCHAR(50) NOT NULL UNIQUE,
    email VARCHAR(100) NOT NULL UNIQUE,
    password VARCHAR(255) NOT NULL,
    role ENUM('user', 'admin') DEFAULT 'user',
    is_active TINYINT(1) DEFAULT 1,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    updated_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
    
    INDEX idx_email (email),
    INDEX idx_username (username)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;

-- =====================================================
-- TABLE 2: USER_PROFILES
-- Purpose: Store detailed health profile information
-- =====================================================
CREATE TABLE user_profiles (
    profile_id INT AUTO_INCREMENT PRIMARY KEY,
    user_id INT NOT NULL UNIQUE,
    full_name VARCHAR(100) NOT NULL,
    date_of_birth DATE,
    age INT,
    gender ENUM('male', 'female', 'other') NOT NULL,
    height_cm DECIMAL(5,2) NOT NULL COMMENT 'Height in centimeters',
    weight_kg DECIMAL(5,2) NOT NULL COMMENT 'Weight in kilograms',
    activity_level ENUM('sedentary', 'light', 'moderate', 'active', 'very_active') DEFAULT 'moderate',
    health_goal ENUM('lose_weight', 'gain_weight', 'maintain', 'build_muscle', 'improve_fitness') DEFAULT 'maintain',
    medical_conditions TEXT,
    profile_image VARCHAR(255) DEFAULT 'default.png',
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    updated_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
    
    FOREIGN KEY (user_id) REFERENCES users(user_id) ON DELETE CASCADE,
    INDEX idx_user_id (user_id)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;

-- =====================================================
-- TABLE 3: BMI_RECORDS
-- Purpose: Store BMI calculation history
-- =====================================================
CREATE TABLE bmi_records (
    bmi_id INT AUTO_INCREMENT PRIMARY KEY,
    user_id INT NOT NULL,
    height_cm DECIMAL(5,2) NOT NULL,
    weight_kg DECIMAL(5,2) NOT NULL,
    bmi_value DECIMAL(4,2) NOT NULL,
    bmi_category ENUM('underweight', 'normal', 'overweight', 'obese') NOT NULL,
    recorded_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    notes TEXT,
    
    FOREIGN KEY (user_id) REFERENCES users(user_id) ON DELETE CASCADE,
    INDEX idx_user_bmi (user_id, recorded_at)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;

-- =====================================================
-- TABLE 4: DAILY_ACTIVITIES
-- Purpose: Store daily activity logs
-- =====================================================
CREATE TABLE daily_activities (
    activity_id INT AUTO_INCREMENT PRIMARY KEY,
    user_id INT NOT NULL,
    activity_date DATE NOT NULL,
    steps_count INT DEFAULT 0,
    exercise_type VARCHAR(100),
    exercise_duration_mins INT DEFAULT 0 COMMENT 'Duration in minutes',
    calories_burned INT DEFAULT 0,
    water_intake_glasses INT DEFAULT 0 COMMENT 'Number of glasses (250ml each)',
    notes TEXT,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    updated_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
    
    FOREIGN KEY (user_id) REFERENCES users(user_id) ON DELETE CASCADE,
    UNIQUE KEY unique_user_date (user_id, activity_date),
    INDEX idx_activity_date (activity_date)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;

-- =====================================================
-- TABLE 5: REMINDERS
-- Purpose: Store user health reminders
-- =====================================================
CREATE TABLE reminders (
    reminder_id INT AUTO_INCREMENT PRIMARY KEY,
    user_id INT NOT NULL,
    title VARCHAR(100) NOT NULL,
    description TEXT,
    reminder_time TIME NOT NULL,
    reminder_date DATE,
    category ENUM('exercise', 'water', 'medication', 'meal', 'sleep', 'other') DEFAULT 'other',
    repeat_type ENUM('once', 'daily', 'weekly', 'monthly') DEFAULT 'once',
    is_active TINYINT(1) DEFAULT 1,
    is_completed TINYINT(1) DEFAULT 0,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    updated_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
    
    FOREIGN KEY (user_id) REFERENCES users(user_id) ON DELETE CASCADE,
    INDEX idx_user_reminder (user_id, is_active)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;

-- =====================================================
-- TABLE 6: RECOMMENDATIONS
-- Purpose: Store fitness and diet recommendations
-- =====================================================
CREATE TABLE recommendations (
    recommendation_id INT AUTO_INCREMENT PRIMARY KEY,
    category ENUM('fitness', 'diet') NOT NULL,
    bmi_category ENUM('underweight', 'normal', 'overweight', 'obese') NOT NULL,
    activity_level ENUM('sedentary', 'light', 'moderate', 'active', 'very_active'),
    health_goal ENUM('lose_weight', 'gain_weight', 'maintain', 'build_muscle', 'improve_fitness'),
    title VARCHAR(100) NOT NULL,
    description TEXT NOT NULL,
    priority INT DEFAULT 1,
    is_active TINYINT(1) DEFAULT 1,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;

-- =====================================================
-- TABLE 7: USER_GOALS
-- Purpose: Store user-defined health goals
-- =====================================================
CREATE TABLE user_goals (
    goal_id INT AUTO_INCREMENT PRIMARY KEY,
    user_id INT NOT NULL,
    goal_type ENUM('weight', 'steps', 'water', 'exercise', 'calories') NOT NULL,
    target_value DECIMAL(10,2) NOT NULL,
    current_value DECIMAL(10,2) DEFAULT 0,
    target_date DATE,
    is_achieved TINYINT(1) DEFAULT 0,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    updated_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
    
    FOREIGN KEY (user_id) REFERENCES users(user_id) ON DELETE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;

-- =====================================================
-- TABLE 8: WEIGHT_LOG
-- Purpose: Track weight changes over time
-- =====================================================
CREATE TABLE weight_log (
    log_id INT AUTO_INCREMENT PRIMARY KEY,
    user_id INT NOT NULL,
    weight_kg DECIMAL(5,2) NOT NULL,
    recorded_date DATE NOT NULL,
    notes TEXT,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    FOREIGN KEY (user_id) REFERENCES users(user_id) ON DELETE CASCADE,
    UNIQUE KEY unique_user_weight_date (user_id, recorded_date)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;

-- =====================================================
-- TABLE 9: CONTACT_QUERIES
-- Purpose: Store contact form submissions
-- =====================================================
CREATE TABLE contact_queries (
    query_id INT AUTO_INCREMENT PRIMARY KEY,
    name VARCHAR(100) NOT NULL,
    email VARCHAR(100) NOT NULL,
    subject VARCHAR(255),
    message TEXT NOT NULL,
    status ENUM('pending', 'responded', 'resolved') DEFAULT 'pending',
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;

-- =====================================================
-- TABLE 10: NOTIFICATIONS
-- Purpose: Store system notifications for users
-- =====================================================
CREATE TABLE notifications (
    notification_id INT AUTO_INCREMENT PRIMARY KEY,
    user_id INT NOT NULL,
    title VARCHAR(255) NOT NULL,
    message TEXT NOT NULL,
    is_read TINYINT(1) DEFAULT 0,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    
    FOREIGN KEY (user_id) REFERENCES users(user_id) ON DELETE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;

-- =====================================================
-- TABLE 11: REVIEWS
-- Purpose: Store user reviews and feedback
-- =====================================================
CREATE TABLE reviews (
    review_id INT AUTO_INCREMENT PRIMARY KEY,
    user_id INT NOT NULL,
    rating INT NOT NULL CHECK (rating >= 1 AND rating <= 5),
    comment TEXT,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,

    FOREIGN KEY (user_id) REFERENCES users(user_id) ON DELETE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;

-- =====================================================
-- INSERT DEFAULT RECOMMENDATIONS DATA
-- =====================================================

-- Fitness Recommendations for Underweight
INSERT INTO recommendations (category, bmi_category, health_goal, title, description, priority) VALUES
('fitness', 'underweight', 'gain_weight', 'Strength Training', 'Focus on compound exercises like squats, deadlifts, and bench press. Aim for 3-4 sessions per week with moderate weights and 8-12 reps.', 1),
('fitness', 'underweight', 'gain_weight', 'Limited Cardio', 'Reduce cardio to 1-2 light sessions per week. Focus on short walks or light cycling for 15-20 minutes.', 2),
('fitness', 'underweight', 'build_muscle', 'Progressive Overload', 'Gradually increase weights each week. Focus on muscle groups: chest, back, shoulders, legs, and arms.', 1);

-- Diet Recommendations for Underweight
INSERT INTO recommendations (category, bmi_category, health_goal, title, description, priority) VALUES
('diet', 'underweight', 'gain_weight', 'Calorie Surplus Diet', 'Consume 300-500 calories more than your daily requirement. Include whole grains, lean proteins, healthy fats, and nuts.', 1),
('diet', 'underweight', 'gain_weight', 'Protein-Rich Foods', 'Include eggs, chicken, fish, dairy, legumes, and protein shakes. Aim for 1.6-2.2g protein per kg of body weight.', 2),
('diet', 'underweight', 'build_muscle', 'Frequent Meals', 'Eat 5-6 smaller meals throughout the day instead of 3 large meals. Include healthy snacks like almonds and bananas.', 1);

-- Fitness Recommendations for Normal Weight
INSERT INTO recommendations (category, bmi_category, health_goal, title, description, priority) VALUES
('fitness', 'normal', 'maintain', 'Balanced Exercise', 'Combine cardio (30 mins, 3x/week) with strength training (2x/week). Include flexibility exercises like yoga.', 1),
('fitness', 'normal', 'improve_fitness', 'HIIT Training', 'Incorporate High-Intensity Interval Training for improved cardiovascular health. 20-30 minute sessions.', 2),
('fitness', 'normal', 'build_muscle', 'Resistance Training', 'Focus on progressive resistance training with proper form. Target each muscle group twice per week.', 1);

-- Diet Recommendations for Normal Weight
INSERT INTO recommendations (category, bmi_category, health_goal, title, description, priority) VALUES
('diet', 'normal', 'maintain', 'Balanced Diet', 'Follow a balanced diet with 50% carbs, 25% protein, and 25% healthy fats. Include plenty of vegetables and fruits.', 1),
('diet', 'normal', 'improve_fitness', 'Performance Nutrition', 'Time your meals around workouts. Consume carbs before and protein after exercise for optimal results.', 2),
('diet', 'normal', 'build_muscle', 'Muscle Building Diet', 'Increase protein intake to 1.8g per kg body weight. Include complex carbs and healthy fats.', 1);

-- Fitness Recommendations for Overweight
INSERT INTO recommendations (category, bmi_category, health_goal, title, description, priority) VALUES
('fitness', 'overweight', 'lose_weight', 'Cardio Focus', 'Start with low-impact cardio like walking, swimming, or cycling. Aim for 45-60 minutes, 5 days a week.', 1),
('fitness', 'overweight', 'lose_weight', 'Strength Training', 'Include 2-3 days of resistance training to build muscle and boost metabolism.', 2),
('fitness', 'overweight', 'improve_fitness', 'Progressive Walking', 'Start with 20-minute walks and gradually increase to 60 minutes. Track steps daily aiming for 10,000 steps.', 1);

-- Diet Recommendations for Overweight
INSERT INTO recommendations (category, bmi_category, health_goal, title, description, priority) VALUES
('diet', 'overweight', 'lose_weight', 'Calorie Deficit Diet', 'Create a moderate calorie deficit of 500-750 calories daily. Focus on nutrient-dense, low-calorie foods.', 1),
('diet', 'overweight', 'lose_weight', 'Reduce Processed Foods', 'Minimize sugar, refined carbs, and processed foods. Replace with whole grains, vegetables, and lean proteins.', 2),
('diet', 'overweight', 'improve_fitness', 'Portion Control', 'Use smaller plates and practice mindful eating. Stop eating when 80% full.', 1);

-- Fitness Recommendations for Obese
INSERT INTO recommendations (category, bmi_category, health_goal, title, description, priority) VALUES
('fitness', 'obese', 'lose_weight', 'Low Impact Start', 'Begin with very low-impact exercises like water aerobics, seated exercises, or gentle walking. Start with 10-15 minutes.', 1),
('fitness', 'obese', 'lose_weight', 'Medical Supervision', 'Consider working with a physical therapist or fitness professional. Get medical clearance before starting any exercise program.', 2),
('fitness', 'obese', 'improve_fitness', 'Daily Movement', 'Focus on increasing daily movement. Take short walks after meals, use stairs when possible.', 1);

-- Diet Recommendations for Obese
INSERT INTO recommendations (category, bmi_category, health_goal, title, description, priority) VALUES
('diet', 'obese', 'lose_weight', 'Medical Diet Plan', 'Consult a registered dietitian for a personalized meal plan. Focus on sustainable, long-term changes.', 1),
('diet', 'obese', 'lose_weight', 'Eliminate Sugary Drinks', 'Replace sodas and sugary beverages with water, unsweetened tea, or infused water.', 2),
('diet', 'obese', 'improve_fitness', 'Whole Foods Focus', 'Base your diet on vegetables, fruits, lean proteins, and whole grains. Avoid fast food and processed snacks.', 1);

-- =====================================================
-- INSERT SAMPLE USER DATA (Optional - for testing)
-- =====================================================

-- Admin User (Password: Admin@123)
INSERT INTO users (username, email, password, role) VALUES
('admin', 'admin@hfms.com', '$2y$10$i7L9.f2BrWPvfbOayOh6TuBpUGCmNkvapnYmXOdj8VpEemqy2bWj2', 'admin');

-- Sample User (Password: password123)
INSERT INTO users (username, email, password, role) VALUES
('testuser', 'test@example.com', '$2y$10$92IXUNpkjO0rOQ5byMi.Ye4oKoEa3Ro9llC/.og/at2.uheWG/igi', 'user');

-- Sample Profile for test user
INSERT INTO user_profiles (user_id, full_name, date_of_birth, age, gender, height_cm, weight_kg, activity_level, health_goal) VALUES
(1, 'John Doe', '1998-05-15', 26, 'male', 175.00, 70.00, 'moderate', 'maintain');

-- Sample BMI Record
INSERT INTO bmi_records (user_id, height_cm, weight_kg, bmi_value, bmi_category, notes) VALUES
(1, 175.00, 70.00, 22.86, 'normal', 'Initial BMI calculation');

-- Sample Daily Activity
INSERT INTO daily_activities (user_id, activity_date, steps_count, exercise_type, exercise_duration_mins, calories_burned, water_intake_glasses) VALUES
(1, CURDATE(), 8500, 'Walking', 45, 320, 8);

-- Sample Reminder
INSERT INTO reminders (user_id, title, description, reminder_time, category, repeat_type) VALUES
(1, 'Morning Walk', 'Take a 30-minute morning walk', '07:00:00', 'exercise', 'daily'),
(1, 'Drink Water', 'Remember to drink water', '10:00:00', 'water', 'daily'),
(1, 'Evening Workout', 'Complete evening exercise routine', '18:00:00', 'exercise', 'daily');

-- =====================================================
-- VIEWS FOR REPORTS
-- =====================================================

-- View for Weekly Activity Summary
CREATE VIEW weekly_activity_summary AS
SELECT 
    user_id,
    YEARWEEK(activity_date) as week_number,
    SUM(steps_count) as total_steps,
    SUM(exercise_duration_mins) as total_exercise_mins,
    SUM(calories_burned) as total_calories,
    AVG(water_intake_glasses) as avg_water_intake,
    COUNT(*) as days_logged
FROM daily_activities
GROUP BY user_id, YEARWEEK(activity_date);

-- View for User Dashboard Stats
CREATE VIEW user_dashboard_stats AS
SELECT 
    u.user_id,
    u.username,
    up.full_name,
    up.height_cm,
    up.weight_kg,
    COALESCE(br.bmi_value, 0) as current_bmi,
    COALESCE(br.bmi_category, 'unknown') as bmi_category,
    COALESCE(da.steps_count, 0) as today_steps,
    COALESCE(da.water_intake_glasses, 0) as today_water
FROM users u
LEFT JOIN user_profiles up ON u.user_id = up.user_id
LEFT JOIN (
    SELECT user_id, bmi_value, bmi_category 
    FROM bmi_records 
    WHERE (user_id, recorded_at) IN (
        SELECT user_id, MAX(recorded_at) FROM bmi_records GROUP BY user_id
    )
) br ON u.user_id = br.user_id
LEFT JOIN daily_activities da ON u.user_id = da.user_id AND da.activity_date = CURDATE();

-- =====================================================
-- STORED PROCEDURES
-- =====================================================

DELIMITER //

-- Procedure to calculate and store BMI
CREATE PROCEDURE CalculateAndStoreBMI(
    IN p_user_id INT,
    IN p_height_cm DECIMAL(5,2),
    IN p_weight_kg DECIMAL(5,2)
)
BEGIN
    DECLARE v_bmi DECIMAL(4,2);
    DECLARE v_category VARCHAR(20);
    DECLARE v_height_m DECIMAL(4,2);
    
    -- Calculate height in meters
    SET v_height_m = p_height_cm / 100;
    
    -- Calculate BMI
    SET v_bmi = p_weight_kg / (v_height_m * v_height_m);
    
    -- Determine category
    IF v_bmi < 18.5 THEN
        SET v_category = 'underweight';
    ELSEIF v_bmi >= 18.5 AND v_bmi < 25 THEN
        SET v_category = 'normal';
    ELSEIF v_bmi >= 25 AND v_bmi < 30 THEN
        SET v_category = 'overweight';
    ELSE
        SET v_category = 'obese';
    END IF;
    
    -- Insert BMI record
    INSERT INTO bmi_records (user_id, height_cm, weight_kg, bmi_value, bmi_category)
    VALUES (p_user_id, p_height_cm, p_weight_kg, v_bmi, v_category);
    
    -- Return the calculated values
    SELECT v_bmi as bmi_value, v_category as bmi_category;
END //

-- Procedure to get user recommendations
CREATE PROCEDURE GetUserRecommendations(IN p_user_id INT)
BEGIN
    DECLARE v_bmi_category VARCHAR(20);
    DECLARE v_health_goal VARCHAR(30);
    DECLARE v_activity_level VARCHAR(20);
    
    -- Get user's current status
    SELECT 
        br.bmi_category,
        up.health_goal,
        up.activity_level
    INTO v_bmi_category, v_health_goal, v_activity_level
    FROM users u
    LEFT JOIN user_profiles up ON u.user_id = up.user_id
    LEFT JOIN (
        SELECT user_id, bmi_category 
        FROM bmi_records 
        WHERE (user_id, recorded_at) IN (
            SELECT user_id, MAX(recorded_at) FROM bmi_records GROUP BY user_id
        )
    ) br ON u.user_id = br.user_id
    WHERE u.user_id = p_user_id;
    
    -- Get matching recommendations
    SELECT * FROM recommendations
    WHERE bmi_category = v_bmi_category
    AND (health_goal = v_health_goal OR health_goal IS NULL)
    AND is_active = 1
    ORDER BY category, priority;
END //

DELIMITER ;

-- =====================================================
-- End of Database Schema
-- =====================================================
