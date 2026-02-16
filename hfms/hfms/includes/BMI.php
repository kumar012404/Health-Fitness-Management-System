<?php
/**
 * BMI Class
 * Health and Fitness Management System
 * 
 * Handles BMI calculation, storage, and history management.
 */

require_once dirname(__DIR__) . '/config/config.php';

class BMI
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
     * Calculate BMI and return detailed result
     * @param float $weight - Weight in kg
     * @param float $height - Height in cm
     * @return array
     */
    public function calculate($weight, $height)
    {
        // Validate inputs
        if ($weight <= 0 || $height <= 0) {
            return ['success' => false, 'message' => 'Invalid weight or height values.'];
        }

        // Convert height to meters
        $heightM = $height / 100;

        // Calculate BMI
        $bmiValue = $weight / ($heightM * $heightM);
        $bmiValue = round($bmiValue, 2);

        // Determine category and get details
        $categoryInfo = $this->getCategoryInfo($bmiValue);

        // Get health advice
        $advice = $this->getHealthAdvice($categoryInfo['category']);

        return [
            'success' => true,
            'bmi' => $bmiValue,
            'category' => $categoryInfo['category'],
            'label' => $categoryInfo['label'],
            'color' => $categoryInfo['color'],
            'range' => $categoryInfo['range'],
            'advice' => $advice,
            'height' => $height,
            'weight' => $weight
        ];
    }

    /**
     * Calculate and store BMI for user
     * @param int $userId
     * @param float $weight
     * @param float $height
     * @param string|null $notes
     * @return array
     */
    public function calculateAndStore($userId, $weight, $height, $notes = null)
    {
        $result = $this->calculate($weight, $height);

        if (!$result['success']) {
            return $result;
        }

        try {
            $stmt = $this->db->prepare("
                INSERT INTO bmi_records 
                (user_id, height_cm, weight_kg, bmi_value, bmi_category, notes)
                VALUES (?, ?, ?, ?, ?, ?)
            ");
            $stmt->execute([
                $userId,
                $height,
                $weight,
                $result['bmi'],
                $result['category'],
                $notes
            ]);

            $result['record_id'] = $this->db->lastInsertId();
            $result['message'] = 'BMI calculated and saved successfully!';

            // Update profile weight if it changed
            $this->updateProfileWeight($userId, $weight);

            return $result;

        } catch (PDOException $e) {
            error_log("Store BMI Error: " . $e->getMessage());
            $result['message'] = 'BMI calculated but failed to save.';
            return $result;
        }
    }

    /**
     * Get category information based on BMI value
     * @param float $bmiValue
     * @return array
     */
    private function getCategoryInfo($bmiValue)
    {
        if ($bmiValue < 18.5) {
            return [
                'category' => 'underweight',
                'label' => 'Underweight',
                'color' => '#3498db',
                'range' => 'Below 18.5'
            ];
        } elseif ($bmiValue < 25) {
            return [
                'category' => 'normal',
                'label' => 'Normal Weight',
                'color' => '#2ecc71',
                'range' => '18.5 - 24.9'
            ];
        } elseif ($bmiValue < 30) {
            return [
                'category' => 'overweight',
                'label' => 'Overweight',
                'color' => '#f39c12',
                'range' => '25.0 - 29.9'
            ];
        } else {
            return [
                'category' => 'obese',
                'label' => 'Obese',
                'color' => '#e74c3c',
                'range' => '30.0 and above'
            ];
        }
    }

    /**
     * Get health advice based on BMI category
     * @param string $category
     * @return array
     */
    private function getHealthAdvice($category)
    {
        $advice = [
            'underweight' => [
                'summary' => 'You are underweight. Consider consulting a healthcare provider.',
                'tips' => [
                    'Increase calorie intake with nutrient-rich foods',
                    'Add healthy fats like nuts, avocados, and olive oil',
                    'Focus on strength training to build muscle mass',
                    'Eat more frequently - 5-6 smaller meals per day',
                    'Include protein-rich foods in every meal'
                ]
            ],
            'normal' => [
                'summary' => 'Congratulations! Your weight is in the healthy range.',
                'tips' => [
                    'Maintain your current healthy habits',
                    'Continue regular physical activity',
                    'Eat a balanced diet with variety',
                    'Stay hydrated with adequate water intake',
                    'Get regular health check-ups'
                ]
            ],
            'overweight' => [
                'summary' => 'You are slightly overweight. Consider lifestyle modifications.',
                'tips' => [
                    'Reduce portion sizes gradually',
                    'Increase physical activity to 150+ minutes per week',
                    'Limit processed foods and sugary drinks',
                    'Choose whole grains over refined carbohydrates',
                    'Monitor your progress regularly'
                ]
            ],
            'obese' => [
                'summary' => 'Your BMI indicates obesity. Please consult a healthcare provider.',
                'tips' => [
                    'Seek guidance from a healthcare professional',
                    'Start with low-impact exercises like walking or swimming',
                    'Focus on sustainable, long-term dietary changes',
                    'Consider working with a registered dietitian',
                    'Set realistic, achievable weight loss goals'
                ]
            ]
        ];

        return $advice[$category] ?? $advice['normal'];
    }

    /**
     * Get BMI history for user
     * @param int $userId
     * @param int $limit
     * @return array
     */
    public function getHistory($userId, $limit = 10)
    {
        try {
            $stmt = $this->db->prepare("
                SELECT bmi_id, height_cm, weight_kg, bmi_value, bmi_category, 
                       recorded_at, notes
                FROM bmi_records 
                WHERE user_id = ?
                ORDER BY recorded_at DESC
                LIMIT ?
            ");
            $stmt->execute([$userId, $limit]);
            return $stmt->fetchAll();
        } catch (PDOException $e) {
            error_log("Get BMI History Error: " . $e->getMessage());
            return [];
        }
    }

    /**
     * Get latest BMI record for user
     * @param int $userId
     * @return array|null
     */
    public function getLatest($userId)
    {
        try {
            $stmt = $this->db->prepare("
                SELECT bmi_id, height_cm, weight_kg, bmi_value, bmi_category, 
                       recorded_at, notes
                FROM bmi_records 
                WHERE user_id = ?
                ORDER BY recorded_at DESC
                LIMIT 1
            ");
            $stmt->execute([$userId]);
            $record = $stmt->fetch();

            if ($record) {
                $categoryInfo = $this->getCategoryInfo($record['bmi_value']);
                $record['label'] = $categoryInfo['label'];
                $record['color'] = $categoryInfo['color'];
            }

            return $record;
        } catch (PDOException $e) {
            error_log("Get Latest BMI Error: " . $e->getMessage());
            return null;
        }
    }

    /**
     * Get BMI trend data for charts
     * @param int $userId
     * @param int $months
     * @return array
     */
    public function getTrendData($userId, $months = 6)
    {
        try {
            $startDate = date('Y-m-d', strtotime("-$months months"));

            $stmt = $this->db->prepare("
                SELECT DATE(recorded_at) as date, bmi_value, weight_kg
                FROM bmi_records 
                WHERE user_id = ? AND recorded_at >= ?
                ORDER BY recorded_at ASC
            ");
            $stmt->execute([$userId, $startDate]);
            $records = $stmt->fetchAll();

            $labels = [];
            $bmiData = [];
            $weightData = [];

            foreach ($records as $record) {
                $labels[] = date('M d', strtotime($record['date']));
                $bmiData[] = $record['bmi_value'];
                $weightData[] = $record['weight_kg'];
            }

            return [
                'labels' => $labels,
                'bmi' => $bmiData,
                'weight' => $weightData
            ];

        } catch (PDOException $e) {
            error_log("Get BMI Trend Error: " . $e->getMessage());
            return ['labels' => [], 'bmi' => [], 'weight' => []];
        }
    }

    /**
     * Delete BMI record
     * @param int $userId
     * @param int $bmiId
     * @return array
     */
    public function deleteRecord($userId, $bmiId)
    {
        try {
            $stmt = $this->db->prepare("
                DELETE FROM bmi_records 
                WHERE bmi_id = ? AND user_id = ?
            ");
            $stmt->execute([$bmiId, $userId]);

            if ($stmt->rowCount() > 0) {
                return ['success' => true, 'message' => 'Record deleted successfully.'];
            }
            return ['success' => false, 'message' => 'Record not found.'];

        } catch (PDOException $e) {
            error_log("Delete BMI Error: " . $e->getMessage());
            return ['success' => false, 'message' => 'Failed to delete record.'];
        }
    }

    /**
     * Update user profile weight
     * @param int $userId
     * @param float $weight
     */
    private function updateProfileWeight($userId, $weight)
    {
        try {
            $stmt = $this->db->prepare("
                UPDATE user_profiles SET weight_kg = ?, updated_at = NOW()
                WHERE user_id = ?
            ");
            $stmt->execute([$weight, $userId]);

            // Also log to weight_log table
            $this->logWeight($userId, $weight);

        } catch (PDOException $e) {
            error_log("Update Profile Weight Error: " . $e->getMessage());
        }
    }

    /**
     * Log weight to weight_log table
     * @param int $userId
     * @param float $weight
     */
    private function logWeight($userId, $weight)
    {
        try {
            $today = date('Y-m-d');

            // Check if already logged today
            $stmt = $this->db->prepare("
                SELECT log_id FROM weight_log 
                WHERE user_id = ? AND recorded_date = ?
            ");
            $stmt->execute([$userId, $today]);

            if ($stmt->fetch()) {
                // Update existing
                $stmt = $this->db->prepare("
                    UPDATE weight_log SET weight_kg = ?
                    WHERE user_id = ? AND recorded_date = ?
                ");
                $stmt->execute([$weight, $userId, $today]);
            } else {
                // Insert new
                $stmt = $this->db->prepare("
                    INSERT INTO weight_log (user_id, weight_kg, recorded_date)
                    VALUES (?, ?, ?)
                ");
                $stmt->execute([$userId, $weight, $today]);
            }
        } catch (PDOException $e) {
            error_log("Log Weight Error: " . $e->getMessage());
        }
    }

    /**
     * Get ideal weight range based on height
     * @param float $height - Height in cm
     * @return array
     */
    public function getIdealWeightRange($height)
    {
        $heightM = $height / 100;

        // Using BMI range 18.5 - 24.9 for healthy weight
        $minWeight = round(18.5 * $heightM * $heightM, 1);
        $maxWeight = round(24.9 * $heightM * $heightM, 1);

        return [
            'min' => $minWeight,
            'max' => $maxWeight,
            'message' => "For your height of {$height}cm, a healthy weight range is {$minWeight}kg - {$maxWeight}kg"
        ];
    }
}
?>