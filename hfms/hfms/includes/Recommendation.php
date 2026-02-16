<?php
/**
 * Recommendation Class - Health and Fitness Management System
 */

require_once dirname(__DIR__) . '/config/config.php';

class Recommendation
{
    private $db;

    public function __construct()
    {
        $this->db = getDBConnection();
    }

    public function getRecommendations($userId)
    {
        try {
            $userInfo = $this->getUserHealthInfo($userId);

            if (!$userInfo) {
                return ['success' => false, 'message' => 'Please complete your profile first.', 'fitness' => [], 'diet' => []];
            }

            $bmiCategory = $userInfo['bmi_category'] ?? 'normal';
            $healthGoal = $userInfo['health_goal'] ?? 'maintain';
            $activityLevel = $userInfo['activity_level'] ?? 'moderate';

            $fitnessRecs = $this->getRecommendationsByCategory('fitness', $bmiCategory, $healthGoal);
            $dietRecs = $this->getRecommendationsByCategory('diet', $bmiCategory, $healthGoal);
            $personalizedFitness = $this->generateFitnessRecommendations($bmiCategory, $activityLevel, $healthGoal);
            $personalizedDiet = $this->generateDietRecommendations($bmiCategory, $healthGoal, $userInfo);

            return [
                'success' => true,
                'user_info' => $userInfo,
                'fitness' => array_merge($fitnessRecs, $personalizedFitness),
                'diet' => array_merge($dietRecs, $personalizedDiet),
                'daily_targets' => $this->calculateDailyTargets($userInfo)
            ];
        } catch (PDOException $e) {
            return ['success' => false, 'message' => 'Failed to get recommendations.', 'fitness' => [], 'diet' => []];
        }
    }

    private function getUserHealthInfo($userId)
    {
        $stmt = $this->db->prepare("SELECT up.*, br.bmi_value, br.bmi_category FROM user_profiles up LEFT JOIN (SELECT user_id, bmi_value, bmi_category FROM bmi_records WHERE (user_id, recorded_at) IN (SELECT user_id, MAX(recorded_at) FROM bmi_records GROUP BY user_id)) br ON up.user_id = br.user_id WHERE up.user_id = ?");
        $stmt->execute([$userId]);
        return $stmt->fetch();
    }

    private function getRecommendationsByCategory($category, $bmiCategory, $healthGoal)
    {
        $stmt = $this->db->prepare("SELECT title, description, priority FROM recommendations WHERE category = ? AND bmi_category = ? AND (health_goal = ? OR health_goal IS NULL) AND is_active = 1 ORDER BY priority LIMIT 5");
        $stmt->execute([$category, $bmiCategory, $healthGoal]);
        return $stmt->fetchAll();
    }

    private function generateFitnessRecommendations($bmiCategory, $activityLevel, $healthGoal)
    {
        $recommendations = [];

        $activityRecs = [
            'sedentary' => ['title' => 'Start with Walking', 'description' => 'Begin with 15-20 minute walks daily. Gradually increase to 30 minutes.'],
            'light' => ['title' => 'Add Variety', 'description' => 'Try cycling, swimming, or yoga. Aim for 30 minutes, 4-5 times per week.'],
            'moderate' => ['title' => 'Intensify Your Routine', 'description' => 'Add interval training. Mix cardio with strength training.'],
            'active' => ['title' => 'Focus on Recovery', 'description' => 'Ensure rest days. Include stretching to prevent injuries.'],
            'very_active' => ['title' => 'Optimize Performance', 'description' => 'Focus on recovery and proper nutrition for peak performance.']
        ];

        if (isset($activityRecs[$activityLevel])) {
            $recommendations[] = array_merge($activityRecs[$activityLevel], ['priority' => 1]);
        }

        return $recommendations;
    }

    private function generateDietRecommendations($bmiCategory, $healthGoal, $userInfo)
    {
        $recommendations = [];
        $bmr = $this->calculateBMR($userInfo);
        $tdee = $this->calculateTDEE($bmr, $userInfo['activity_level']);
        $water = $this->calculateWaterIntake($userInfo['weight_kg']);

        $targetCalories = $tdee;
        if ($healthGoal === 'lose_weight')
            $targetCalories = $tdee - 500;
        elseif (in_array($healthGoal, ['gain_weight', 'build_muscle']))
            $targetCalories = $tdee + 300;

        $recommendations[] = ['title' => 'Calorie Target: ' . round($targetCalories) . ' kcal/day', 'description' => 'Calculated based on your profile and goals.', 'priority' => 1];
        $recommendations[] = ['title' => 'Water Intake: ' . $water['glasses'] . ' glasses/day', 'description' => 'Drink ' . $water['liters'] . ' liters daily.', 'priority' => 2];

        return $recommendations;
    }

    private function calculateBMR($userInfo)
    {
        $weight = $userInfo['weight_kg'];
        $height = $userInfo['height_cm'];
        $age = $userInfo['age'] ?? 25;
        return ($userInfo['gender'] === 'male') ? (10 * $weight) + (6.25 * $height) - (5 * $age) + 5 : (10 * $weight) + (6.25 * $height) - (5 * $age) - 161;
    }

    private function calculateTDEE($bmr, $activityLevel)
    {
        $multipliers = ['sedentary' => 1.2, 'light' => 1.375, 'moderate' => 1.55, 'active' => 1.725, 'very_active' => 1.9];
        return $bmr * ($multipliers[$activityLevel] ?? 1.55);
    }

    private function calculateWaterIntake($weight)
    {
        $liters = round(($weight * 33) / 1000, 1);
        return ['liters' => $liters, 'glasses' => round($liters * 4)];
    }

    private function calculateDailyTargets($userInfo)
    {
        $bmr = $this->calculateBMR($userInfo);
        $tdee = $this->calculateTDEE($bmr, $userInfo['activity_level']);
        $water = $this->calculateWaterIntake($userInfo['weight_kg']);

        $calorieTarget = $tdee;
        if ($userInfo['health_goal'] === 'lose_weight')
            $calorieTarget = $tdee - 500;
        elseif (in_array($userInfo['health_goal'], ['gain_weight', 'build_muscle']))
            $calorieTarget = $tdee + 300;

        $stepsTarget = ['sedentary' => 5000, 'light' => 7500, 'moderate' => 10000, 'active' => 12500, 'very_active' => 15000][$userInfo['activity_level']] ?? 10000;
        $exerciseTarget = ['sedentary' => 15, 'light' => 30, 'moderate' => 45, 'active' => 60, 'very_active' => 90][$userInfo['activity_level']] ?? 30;

        return ['calories' => round($calorieTarget), 'water_glasses' => $water['glasses'], 'steps' => $stepsTarget, 'exercise_mins' => $exerciseTarget];
    }
}
?>