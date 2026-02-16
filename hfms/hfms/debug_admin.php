<?php
require_once 'config/config.php';

// Force session start if not already started
if (session_status() === PHP_SESSION_NONE) {
    session_start();
}

echo "<h1>Debug Admin & Session</h1>"; // Corrected title

$db = getDBConnection();

echo "<h2>1. Current Session Data</h2>";
echo "<pre>";
print_r($_SESSION);
echo "</pre>";

echo "<h2>2. Database Check (admin@hfms.com)</h2>";
$stmt = $db->prepare("SELECT user_id, username, email, role, is_active FROM users WHERE email = ?");
$stmt->execute(['admin@hfms.com']);
$adminUser = $stmt->fetch(PDO::FETCH_ASSOC);

if ($adminUser) {
    echo "<pre>";
    print_r($adminUser);
    echo "</pre>";
    
    if ($adminUser['role'] === 'admin') {
        echo "<p style='color: green;'>✅ Database role is correctly set to 'admin'.</p>";
    } else {
        echo "<p style='color: red;'>❌ Database role is '" . htmlspecialchars($adminUser['role']) . "' (Expected: 'admin').</p>";
        // Attempt to fix
        $db->exec("UPDATE users SET role='admin' WHERE email='admin@hfms.com'");
        echo "<p>🔄 Attempted to fix role. Please refresh.</p>";
    }
} else {
    echo "<p style='color: red;'>❌ Admin user not found in database!</p>";
}

echo "<h2>3. Actions</h2>";
echo '<a href="logout.php" style="background: red; color: white; padding: 10px; display: inline-block;">Force Logout (Try this first)</a><br><br>';
echo '<a href="login.php">Go to Login Page</a>';
?>
