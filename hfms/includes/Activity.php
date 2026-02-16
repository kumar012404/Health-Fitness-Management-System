<?php
/**
 * Activity Class
 * Health and Fitness Management System
 * 
 * Handles daily activity tracking including steps, exercise, and water intake.
 */

require_once dirname(__DIR__) . '/config/config.php';

class Activity
{
    private $db;

    /**
     * Constructor - Initialize database connection
     */
    public function __construct()
    {
        $this->db = getDBConnection();
    }

    /**
     * Log daily activity
     * @param int $userId
     * @param array $data
     * @return array
     */
    public function logActivity($userId, $data)
    {
        try {
            $activityDate = $data['activity_date'] ?? date('Y-m-d');

            // Check if entry exists for this date
            $existingActivity = $this->getActivityByDate($userId, $activityDate);

            if ($existingActivity) {
                // Update existing record
                $stmt = $this->db->prepare("
                    UPDATE daily_activities SET
                        steps_count = ?,
                        exercise_type = ?,
                        exercise_duration_mins = ?,
                        calories_burned = ?,
                        water_intake_glasses = ?,
                        notes = ?,
                        updated_at = NOW()
                    WHERE user_id = ? AND activity_date = ?
                ");
                $stmt->execute([
                    $data['steps_count'] ?? 0,
                    $data['exercise_type'] ?? null,
                    $data['exercise_duration_mins'] ?? 0,
                    $data['calories_burned'] ?? 0,
                    $data['water_intake_glasses'] ?? 0,
                    $data['notes'] ?? null,
                    $userId,
                    $activityDate
                ]);
            } else {
                // Insert new record
                $stmt = $this->db->prepare("
                    INSERT INTO daily_activities 
                    (user_id, activity_date, steps_count, exercise_type, 
                     exercise_duration_mins, calories_burned, water_intake_glasses, notes)
                    VALUES (?, ?, ?, ?, ?, ?, ?, ?)
                ");
                $stmt->execute([
                    $userId,
                    $activityDate,
                    $data['steps_count'] ?? 0,
                    $data['exercise_type'] ?? null,
                    $data['exercise_duration_mins'] ?? 0,
                    $data['calories_burned'] ?? 0,
                    $data['water_intake_glasses'] ?? 0,
                    $data['notes'] ?? null
                ]);
            }

            return ['success' => true, 'message' => 'Activity logged successfully!'];

        } catch (PDOException $e) {
            error_log("Log Activity Error: " . $e->getMessage());
            return ['success' => false, 'message' => 'Failed to log activity.'];
        }
    }

    /**
     * Get activity for a specific date
     * @param int $userId
     * @param string $date
     * @return array|null
     */
    public function getActivityByDate($userId, $date)
    {
        try {
            $stmt = $this->db->prepare("
                SELECT * FROM daily_activities 
                WHERE user_id = ? AND activity_date = ?
            ");
            $stmt->execute([$userId, $date]);
            return $stmt->fetch();
        } catch (PDOException $e) {
            error_log("Get Activity Error: " . $e->getMessage());
            return null;
        }
    }

    /**
     * Get today's activity
     * @param int $userId
     * @return array|null
     */
    public function getTodayActivity($userId)
    {
        return $this->getActivityByDate($userId, date('Y-m-d'));
    }

    /**
     * Get activities for date range
     * @param int $userId
     * @param string $startDate
     * @param string $endDate
     * @return array
     */
    public function getActivitiesInRange($userId, $startDate, $endDate)
    {
        try {
            $stmt = $this->db->prepare("
                SELECT * FROM daily_activities 
                WHERE user_id = ? AND activity_date BETWEEN ? AND ?
                ORDER BY activity_date DESC
            ");
            $stmt->execute([$userId, $startDate, $endDate]);
            return $stmt->fetchAll();
        } catch (PDOException $e) {
            error_log("Get Activities Range Error: " . $e->getMessage());
            return [];
        }
    }

    /**
     * Get weekly summary
     * @param int $userId
     * @return array
     */
    public function getWeeklySummary($userId)
    {
        try {
            $startDate = date('Y-m-d', strtotime('-6 days'));
            $endDate = date('Y-m-d');

            $stmt = $this->db->prepare("
                SELECT 
                    COUNT(*) as days_logged,
                    COALESCE(SUM(steps_count), 0) as total_steps,
                    COALESCE(AVG(steps_count), 0) as avg_steps,
                    COALESCE(SUM(exercise_duration_mins), 0) as total_exercise_mins,
                    COALESCE(SUM(calories_burned), 0) as total_calories,
                    COALESCE(AVG(water_intake_glasses), 0) as avg_water
                FROM daily_activities 
                WHERE user_id = ? AND activity_date BETWEEN ? AND ?
            ");
            $stmt->execute([$userId, $startDate, $endDate]);
            $summary = $stmt->fetch();

            // Get daily data for chart
            $stmt = $this->db->prepare("
                SELECT 
                    activity_date,
                    steps_count,
                    exercise_duration_mins,
                    water_intake_glasses,
                    calories_burned
                FROM daily_activities 
                WHERE user_id = ? AND activity_date BETWEEN ? AND ?
                ORDER BY activity_date ASC
            ");
            $stmt->execute([$userId, $startDate, $endDate]);
            $dailyData = $stmt->fetchAll();

            return [
                'summary' => $summary,
                'daily_data' => $dailyData,
                'start_date' => $startDate,
                'end_date' => $endDate
            ];

        } catch (PDOException $e) {
            error_log("Get Weekly Summary Error: " . $e->getMessage());
            return ['summary' => null, 'daily_data' => []];
        }
    }

    /**
     * Get monthly summary
     * @param int $userId
     * @param int $month
     * @param int $year
     * @return array
     */
    public function getMonthlySummary($userId, $month = null, $year = null)
    {
        $month = $month ?? date('m');
        $year = $year ?? date('Y');

        try {
            $startDate = "$year-$month-01";
            $endDate = date('Y-m-t', strtotime($startDate));

            $stmt = $this->db->prepare("
                SELECT 
                    COUNT(*) as days_logged,
                    COALESCE(SUM(steps_count), 0) as total_steps,
                    COALESCE(AVG(steps_count), 0) as avg_steps,
                    COALESCE(SUM(exercise_duration_mins), 0) as total_exercise_mins,
                    COALESCE(AVG(exercise_duration_mins), 0) as avg_exercise_mins,
                    COALESCE(SUM(calories_burned), 0) as total_calories,
                    COALESCE(SUM(water_intake_glasses), 0) as total_water,
                    COALESCE(AVG(water_intake_glasses), 0) as avg_water
                FROM daily_activities 
                WHERE user_id = ? AND activity_date BETWEEN ? AND ?
            ");
            $stmt->execute([$userId, $startDate, $endDate]);
            $summary = $stmt->fetch();

            // Get weekly aggregates for chart
            $stmt = $this->db->prepare("
                SELECT 
                    WEEK(activity_date) as week_num,
                    SUM(steps_count) as total_steps,
                    SUM(exercise_duration_mins) as total_exercise,
                    AVG(water_intake_glasses) as avg_water,
                    SUM(calories_burned) as total_calories
                FROM daily_activities 
                WHERE user_id = ? AND activity_date BETWEEN ? AND ?
                GROUP BY WEEK(activity_date)
                ORDER BY week_num
            ");
            $stmt->execute([$userId, $startDate, $endDate]);
            $weeklyData = $stmt->fetchAll();

            return [
                'summary' => $summary,
                'weekly_data' => $weeklyData,
                'month' => $month,
                'year' => $year
            ];

        } catch (PDOException $e) {
            error_log("Get Monthly Summary Error: " . $e->getMessage());
            return ['summary' => null, 'weekly_data' => []];
        }
    }

    /**
     * Update water intake
     * @param int $userId
     * @param int $glasses
     * @return array
     */
    public function updateWaterIntake($userId, $glasses)
    {
        try {
            $today = date('Y-m-d');
            $existing = $this->getActivityByDate($userId, $today);

            if ($existing) {
                $stmt = $this->db->prepare("
                    UPDATE daily_activities 
                    SET water_intake_glasses = ?, updated_at = NOW()
                    WHERE user_id = ? AND activity_date = ?
                ");
                $stmt->execute([$glasses, $userId, $today]);
            } else {
                $stmt = $this->db->prepare("
                    INSERT INTO daily_activities (user_id, activity_date, water_intake_glasses)
                    VALUES (?, ?, ?)
                ");
                $stmt->execute([$userId, $today, $glasses]);
            }

            return ['success' => true, 'message' => 'Water intake updated!', 'glasses' => $glasses];

        } catch (PDOException $e) {
            error_log("Update Water Error: " . $e->getMessage());
            return ['success' => false, 'message' => 'Failed to update water intake.'];
        }
    }

    /**
     * Quick add steps
     * @param int $userId
     * @param int $steps
     * @return array
     */
    public function addSteps($userId, $steps)
    {
        try {
            $today = date('Y-m-d');
            $existing = $this->getActivityByDate($userId, $today);

            if ($existing) {
                $newSteps = $existing['steps_count'] + $steps;
                $stmt = $this->db->prepare("
                    UPDATE daily_activities 
                    SET steps_count = ?, updated_at = NOW()
                    WHERE user_id = ? AND activity_date = ?
                ");
                $stmt->execute([$newSteps, $userId, $today]);
            } else {
                $stmt = $this->db->prepare("
                    INSERT INTO daily_activities (user_id, activity_date, steps_count)
                    VALUES (?, ?, ?)
                ");
                $stmt->execute([$userId, $today, $steps]);
                $newSteps = $steps;
            }

            return ['success' => true, 'message' => 'Steps added!', 'total_steps' => $newSteps];

        } catch (PDOException $e) {
            error_log("Add Steps Error: " . $e->getMessage());
            return ['success' => false, 'message' => 'Failed to add steps.'];
        }
    }

    /**
     * Get chart data for the last 7 days
     * @param int $userId
     * @return array
     */
    public function getChartData($userId)
    {
        try {
            $labels = [];
            $stepsData = [];
            $waterData = [];
            $exerciseData = [];

            $caloriesData = [];

            // Generate last 7 days
            for ($i = 6; $i >= 0; $i--) {
                $date = date('Y-m-d', strtotime("-$i days"));
                $labels[] = date('D', strtotime($date));

                $activity = $this->getActivityByDate($userId, $date);
                $stepsData[] = $activity['steps_count'] ?? 0;
                $waterData[] = $activity['water_intake_glasses'] ?? 0;
                $exerciseData[] = $activity['exercise_duration_mins'] ?? 0;
                $caloriesData[] = $activity['calories_burned'] ?? 0;
            }

            return [
                'labels' => $labels,
                'steps' => $stepsData,
                'water' => $waterData,
                'exercise' => $exerciseData,
                'calories' => $caloriesData
            ];

        } catch (PDOException $e) {
            error_log("Get Chart Data Error: " . $e->getMessage());
            return ['labels' => [], 'steps' => [], 'water' => [], 'exercise' => [], 'calories' => []];
        }
    }
}
?>