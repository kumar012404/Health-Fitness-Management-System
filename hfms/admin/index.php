<?php
$pageTitle = 'Dashboard';
require_once 'includes/header.php';

// Fetch Statistics
$db = getDBConnection();

// Total Users
$stmt = $db->query("SELECT COUNT(*) FROM users WHERE role = 'user'");
$totalUsers = $stmt->fetchColumn();

// Active Users
$stmt = $db->query("SELECT COUNT(*) FROM users WHERE role = 'user' AND is_active = 1");
$activeUsers = $stmt->fetchColumn();

// Total Reports/BMI
$stmt = $db->query("SELECT COUNT(*) FROM bmi_records");
$totalReports = $stmt->fetchColumn();

// Recent Users
$stmt = $db->query("SELECT * FROM users WHERE role = 'user' ORDER BY created_at DESC LIMIT 5");
$recentUsers = $stmt->fetchAll();
?>

<div class="stats-grid">
    <div class="stat-card">
        <div class="stat-label">Total Users</div>
        <div class="stat-value"><?= number_format($totalUsers) ?></div>
        <div class="stat-trend">
            <i class="fas fa-users"></i> Registered Users
        </div>
    </div>
    
    <div class="stat-card">
        <div class="stat-label">Active Users</div>
        <div class="stat-value"><?= number_format($activeUsers) ?></div>
        <div class="stat-trend">
            <i class="fas fa-user-check"></i> Currently Active
        </div>
    </div>

    <div class="stat-card">
        <div class="stat-label">Health Records</div>
        <div class="stat-value"><?= number_format($totalReports) ?></div>
        <div class="stat-trend" style="color: #6366f1;">
            <i class="fas fa-file-medical-alt"></i> Total BMI Checks
        </div>
    </div>
    
    <div class="stat-card">
        <div class="stat-label">System Status</div>
        <div class="stat-value" style="color: #16a34a; font-size: 1.5rem;">Operational</div>
        <div class="stat-trend">
            <i class="fas fa-server"></i> Server Online
        </div>
    </div>
</div>

<div class="card">
    <div style="display: flex; justify-content: space-between; align-items: center; margin-bottom: 1.5rem;">
        <h3 style="font-size: 1.1rem; font-weight: 600;">Recent Registrations</h3>
        <a href="users.php" class="btn btn-primary" style="font-size: 0.8rem;">View All Users</a>
    </div>
    
    <div class="table-container">
        <table>
            <thead>
                <tr>
                    <th>User</th>
                    <th>Email</th>
                    <th>Joined Date</th>
                    <th>Status</th>
                    <th>Action</th>
                </tr>
            </thead>
            <tbody>
                <?php if (count($recentUsers) > 0): ?>
                    <?php foreach ($recentUsers as $user): ?>
                    <tr>
                        <td style="font-weight: 500;">
                            <div style="display: flex; align-items: center; gap: 0.75rem;">
                                <div style="width: 32px; height: 32px; background: #f1f5f9; border-radius: 50%; display: flex; align-items: center; justify-content: center; font-size: 0.75rem; font-weight: 600; color: #64748b;">
                                    <?= strtoupper(substr($user['username'], 0, 1)) ?>
                                </div>
                                <?= htmlspecialchars($user['username']) ?>
                            </div>
                        </td>
                        <td style="color: var(--text-muted);"><?= htmlspecialchars($user['email']) ?></td>
                        <td style="color: var(--text-muted);"><?= date('M j, Y', strtotime($user['created_at'])) ?></td>
                        <td>
                            <?php if ($user['is_active']): ?>
                                <span class="badge badge-success">Active</span>
                            <?php else: ?>
                                <span class="badge badge-danger">Inactive</span>
                            <?php endif; ?>
                        </td>
                        <td>
                            <a href="users.php" style="color: var(--primary); text-decoration: none; font-weight: 500;">Manage</a>
                        </td>
                    </tr>
                    <?php endforeach; ?>
                <?php else: ?>
                    <tr>
                        <td colspan="5" style="text-align: center; padding: 2rem; color: var(--text-muted);">No users found.</td>
                    </tr>
                <?php endif; ?>
            </tbody>
        </table>
    </div>
</div>

</div> <!-- Close Content Wrapper -->
</main>
</div> <!-- Close Admin Layout -->
</body>
</html>
