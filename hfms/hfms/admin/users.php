<?php
$pageTitle = 'User Management';
require_once 'includes/header.php';

$db = getDBConnection();

// Handle Delete Action
if (isset($_POST['delete_user'])) {
    $userId = $_POST['user_id'];
    
    // Security Check: Verify user exists and is not an admin
    $stmt = $db->prepare("SELECT role FROM users WHERE user_id = ?");
    $stmt->execute([$userId]);
    $targetRole = $stmt->fetchColumn();
    
    if ($targetRole && $targetRole !== 'admin') {
        // Delete user
        try {
            $stmt = $db->prepare("DELETE FROM users WHERE user_id = ?");
            $stmt->execute([$userId]);
            echo "<div class='alert' style='background: #dcfce7; color: #166534; padding: 1rem; margin-bottom: 1rem; border-radius: 8px;'>User deleted successfully.</div>";
        } catch (Exception $e) {
             echo "<div class='alert' style='background: #fee2e2; color: #991b1b; padding: 1rem; margin-bottom: 1rem; border-radius: 8px;'>Error deleting user: " . $e->getMessage() . "</div>";
        }
    } else {
         echo "<div class='alert' style='background: #fee2e2; color: #991b1b; padding: 1rem; margin-bottom: 1rem; border-radius: 8px;'>Cannot delete admin users or invalid user.</div>";
    }
}

// Fetch All Regular Users
$stmt = $db->query("SELECT * FROM users WHERE role = 'user' ORDER BY created_at DESC");
$users = $stmt->fetchAll();
?>

<div class="card">
    <div style="margin-bottom: 1rem;">
        <h3 style="font-size: 1.1rem; font-weight: 600;">Registered Application Users</h3>
        <p style="color: var(--text-muted); font-size: 0.875rem;">Manage and view all registered users.</p>
    </div>

    <div class="table-container">
        <table>
            <thead>
                <tr>
                    <th>ID</th>
                    <th>User Info</th>
                    <th>Contact</th>
                    <th>Joined Status</th>
                    <th style="text-align: right;">Actions</th>
                </tr>
            </thead>
            <tbody>
                <?php if (count($users) > 0): ?>
                    <?php foreach ($users as $user): ?>
                    <tr>
                        <td style="color: var(--text-muted);">#<?= $user['user_id'] ?></td>
                        <td>
                            <div style="font-weight: 600; color: var(--text-main);"><?= htmlspecialchars($user['username']) ?></div>
                            <div style="font-size: 0.75rem; color: var(--text-muted);">Role: <?= htmlspecialchars($user['role'] ?? 'User') ?></div>
                        </td>
                        <td>
                            <div style="color: var(--text-main);"><?= htmlspecialchars($user['email']) ?></div>
                        </td>
                        <td>
                            <div style="margin-bottom: 0.25rem;"><?= date('M j, Y', strtotime($user['created_at'])) ?></div>
                            <?php if ($user['is_active']): ?>
                                <span class="badge badge-success">Active</span>
                            <?php else: ?>
                                <span class="badge badge-danger">Inactive</span>
                            <?php endif; ?>
                        </td>
                        <td style="text-align: right;">
                            <form method="POST" style="display: inline-block;" onsubmit="return confirm('Are you sure you want to permanently delete this user? All their records will be lost.');">
                                <input type="hidden" name="user_id" value="<?= $user['user_id'] ?>">
                                <button type="submit" name="delete_user" class="btn btn-danger" style="padding: 0.4rem 0.8rem; font-size: 0.75rem;">
                                    <i class="fas fa-trash-alt"></i> Delete
                                </button>
                            </form>
                        </td>
                    </tr>
                    <?php endforeach; ?>
                <?php else: ?>
                    <tr>
                        <td colspan="5" style="text-align: center; padding: 3rem; color: var(--text-muted);">
                            <i class="fas fa-users" style="font-size: 2rem; margin-bottom: 1rem; display: block; opacity: 0.5;"></i>
                            No registered users found.
                        </td>
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
