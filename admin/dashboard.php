<?php
require_once __DIR__ . '/../includes/auth.php';

if (!isAdmin()) {
    header("Location: " . BASE_URL);
    exit();
}

$pageTitle = 'Admin Dashboard';
$customScript = 'admin-dashboard';

$db = getDB();

// Get stats for dashboard
$usersCount = $db->query("SELECT COUNT(*) FROM users")->fetchColumn();
$teamsCount = $db->query("SELECT COUNT(*) FROM teams")->fetchColumn();
$activeTournaments = $db->query("SELECT COUNT(*) FROM tournaments WHERE end_date >= CURDATE()")->fetchColumn();
$pendingApprovals = $db->query("SELECT COUNT(*) FROM users WHERE status = 'pending'")->fetchColumn();
$recentMedia = $db->query("SELECT COUNT(*) FROM media WHERE created_at >= DATE_SUB(NOW(), INTERVAL 7 DAY)")->fetchColumn();

// Get recent activity
$activityStmt = $db->query("
    (SELECT 'user' as type, id, email as title, created_at FROM users ORDER BY created_at DESC LIMIT 3)
    UNION
    (SELECT 'team' as type, id, name as title, created_at FROM teams ORDER BY created_at DESC LIMIT 3)
    UNION
    (SELECT 'tournament' as type, id, name as title, created_at FROM tournaments ORDER BY created_at DESC LIMIT 3)
    ORDER BY created_at DESC
    LIMIT 5
");
$recentActivity = $activityStmt->fetchAll();

require_once __DIR__ . '/../includes/header.php';
?>

<div class="admin-dashboard">
    <h1>Admin Dashboard</h1>
    
    <div class="stats-grid">
        <div class="stat-card">
            <div class="stat-icon">
                <i class="fas fa-users"></i>
            </div>
            <div class="stat-content">
                <h3>Total Users</h3>
                <div class="stat-value"><?= $usersCount ?></div>
                <a href="users/manage.php" class="stat-link">Manage Users</a>
            </div>
        </div>
        
        <div class="stat-card">
            <div class="stat-icon">
                <i class="fas fa-people-group"></i>
            </div>
            <div class="stat-content">
                <h3>Registered Teams</h3>
                <div class="stat-value"><?= $teamsCount ?></div>
                <a href="teams/manage.php" class="stat-link">Manage Teams</a>
            </div>
        </div>
        
        <div class="stat-card">
            <div class="stat-icon">
                <i class="fas fa-trophy"></i>
            </div>
            <div class="stat-content">
                <h3>Active Tournaments</h3>
                <div class="stat-value"><?= $activeTournaments ?></div>
                <a href="tournaments/manage.php" class="stat-link">Manage Tournaments</a>
            </div>
        </div>
        
        <div class="stat-card">
            <div class="stat-icon">
                <i class="fas fa-user-clock"></i>
            </div>
            <div class="stat-content">
                <h3>Pending Approvals</h3>
                <div class="stat-value"><?= $pendingApprovals ?></div>
                <a href="users/approvals.php" class="stat-link">Review Approvals</a>
            </div>
        </div>
        
        <div class="stat-card">
            <div class="stat-icon">
                <i class="fas fa-photo-film"></i>
            </div>
            <div class="stat-content">
                <h3>Recent Media</h3>
                <div class="stat-value"><?= $recentMedia ?></div>
                <a href="media/manage.php" class="stat-link">Manage Media</a>
            </div>
        </div>
    </div>
    
    <div class="dashboard-sections">
        <section class="recent-activity">
            <h2>Recent Activity</h2>
            <div class="activity-list">
                <?php foreach ($recentActivity as $activity): ?>
                <div class="activity-item">
                    <div class="activity-icon">
                        <i class="fas fa-<?= 
                            $activity['type'] === 'user' ? 'user' : 
                            ($activity['type'] === 'team' ? 'people-group' : 'trophy') 
                        ?>"></i>
                    </div>
                    <div class="activity-content">
                        <p>
                            New <?= $activity['type'] ?> created: 
                            <strong><?= htmlspecialchars($activity['title']) ?></strong>
                        </p>
                        <small><?= date('M j, H:i', strtotime($activity['created_at'])) ?></small>
                    </div>
                    <div class="activity-action">
                        <a href="<?= $activity['type'] ?>/edit.php?id=<?= $activity['id'] ?>" class="btn btn-small">
                            View
                        </a>
                    </div>
                </div>
                <?php endforeach; ?>
            </div>
        </section>
        
        <section class="quick-actions">
            <h2>Quick Actions</h2>
            <div class="action-buttons">
                <a href="tournaments/create.php" class="btn btn-primary">
                    <i class="fas fa-plus"></i> Create Tournament
                </a>
                <a href="media/upload.php" class="btn btn-secondary">
                    <i class="fas fa-upload"></i> Upload Media
                </a>
                <a href="users/approvals.php" class="btn btn-warning">
                    <i class="fas fa-user-check"></i> Approve Users
                </a>
                <a href="teams/create.php" class="btn btn-info">
                    <i class="fas fa-plus"></i> Add Team
                </a>
            </div>
        </section>
    </div>
</div>

<?php
require_once __DIR__ . '/../includes/footer.php';
?>