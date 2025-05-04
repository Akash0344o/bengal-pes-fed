<?php
require_once __DIR__ . '/../includes/auth.php';

if (!isLoggedIn()) {
    header("Location: " . BASE_URL . "/login.php");
    exit();
}

$pageTitle = 'User Dashboard';
$customScript = 'user-dashboard';

$db = getDB();
$userId = $_SESSION['user_id'];

// Get user favorites
$favoritesStmt = $db->prepare("
    SELECT t.id, t.name, t.logo 
    FROM user_favorites uf
    JOIN teams t ON uf.team_id = t.id
    WHERE uf.user_id = ?
    ORDER BY uf.created_at DESC
    LIMIT 5
");
$favoritesStmt->execute([$userId]);
$favoriteTeams = $favoritesStmt->fetchAll();

// Get upcoming matches for favorite teams
$favoriteTeamIds = array_column($favoriteTeams, 'id');
$placeholders = implode(',', array_fill(0, count($favoriteTeamIds), '?'));
$matchesStmt = $db->prepare("
    SELECT m.*, t1.name as team1_name, t1.logo as team1_logo, 
           t2.name as team2_name, t2.logo as team2_logo,
           tr.name as tournament_name
    FROM matches m
    JOIN teams t1 ON m.team1_id = t1.id
    JOIN teams t2 ON m.team2_id = t2.id
    JOIN tournaments tr ON m.tournament_id = tr.id
    WHERE (m.team1_id IN ($placeholders) OR (m.team2_id IN ($placeholders))
    AND m.scheduled_time > NOW()
    ORDER BY m.scheduled_time ASC
    LIMIT 5
");
$matchesStmt->execute(array_merge($favoriteTeamIds, $favoriteTeamIds));
$upcomingMatches = $matchesStmt->fetchAll();

// Get notifications
$notificationsStmt = $db->prepare("
    SELECT * FROM notifications
    WHERE user_id = ?
    ORDER BY created_at DESC
    LIMIT 10
");
$notificationsStmt->execute([$userId]);
$notifications = $notificationsStmt->fetchAll();

require_once __DIR__ . '/../includes/header.php';
?>

<div class="user-dashboard">
    <div class="dashboard-grid">
        <div class="dashboard-col main-col">
            <section class="dashboard-section welcome-section">
                <h2>Welcome, <?= htmlspecialchars($_SESSION['user_email']) ?></h2>
                <p>Last login: <?= date('Y-m-d H:i:s') ?></p>
            </section>
            
            <section class="dashboard-section matches-section">
                <div class="section-header">
                    <h3>Upcoming Matches</h3>
                    <a href="<?= BASE_URL ?>/tournaments.php" class="view-all">View All</a>
                </div>
                
                <?php if (empty($upcomingMatches)): ?>
                    <p>No upcoming matches for your favorite teams.</p>
                <?php else: ?>
                    <div class="matches-list">
                        <?php foreach ($upcomingMatches as $match): ?>
                        <div class="match-card">
                            <div class="match-teams">
                                <div class="team">
                                    <img src="<?= BASE_URL ?>/assets/uploads/teams/<?= $match['team1_logo'] ?>" 
                                         alt="<?= htmlspecialchars($match['team1_name']) ?>">
                                    <span><?= htmlspecialchars($match['team1_name']) ?></span>
                                </div>
                                <div class="match-info">
                                    <div class="match-time"><?= date('M j, H:i', strtotime($match['scheduled_time'])) ?></div>
                                    <div class="match-tournament"><?= htmlspecialchars($match['tournament_name']) ?></div>
                                </div>
                                <div class="team">
                                    <img src="<?= BASE_URL ?>/assets/uploads/teams/<?= $match['team2_logo'] ?>" 
                                         alt="<?= htmlspecialchars($match['team2_name']) ?>">
                                    <span><?= htmlspecialchars($match['team2_name']) ?></span>
                                </div>
                            </div>
                            <a href="<?= BASE_URL ?>/match.php?id=<?= $match['id'] ?>" class="btn btn-outline">View Match</a>
                        </div>
                        <?php endforeach; ?>
                    </div>
                <?php endif; ?>
            </section>
        </div>
        
        <div class="dashboard-col sidebar-col">
            <section class="dashboard-section favorites-section">
                <div class="section-header">
                    <h3>Your Favorite Teams</h3>
                    <a href="<?= BASE_URL ?>/user/favorites.php" class="view-all">View All</a>
                </div>
                
                <?php if (empty($favoriteTeams)): ?>
                    <p>You haven't favorited any teams yet.</p>
                <?php else: ?>
                    <div class="teams-list">
                        <?php foreach ($favoriteTeams as $team): ?>
                        <div class="team-item">
                            <div class="team-logo">
                                <img src="<?= BASE_URL ?>/assets/uploads/teams/<?= $team['logo'] ?>" 
                                     alt="<?= htmlspecialchars($team['name']) ?>">
                            </div>
                            <div class="team-info">
                                <h4><?= htmlspecialchars($team['name']) ?></h4>
                                <a href="<?= BASE_URL ?>/team.php?id=<?= $team['id'] ?>" class="btn btn-small">View</a>
                            </div>
                        </div>
                        <?php endforeach; ?>
                    </div>
                <?php endif; ?>
            </section>
            
            <section class="dashboard-section notifications-section">
                <div class="section-header">
                    <h3>Notifications</h3>
                    <button id="markAllRead" class="btn btn-small">Mark All as Read</button>
                </div>
                
                <?php if (empty($notifications)): ?>
                    <p>No new notifications.</p>
                <?php else: ?>
                    <div class="notifications-list">
                        <?php foreach ($notifications as $notification): ?>
                        <div class="notification-item <?= $notification['is_read'] ? 'read' : 'unread' ?>" 
                             data-notification-id="<?= $notification['id'] ?>">
                            <div class="notification-icon">
                                <i class="fas fa-<?= getNotificationIcon($notification['type']) ?>"></i>
                            </div>
                            <div class="notification-content">
                                <p><?= htmlspecialchars($notification['content']) ?></p>
                                <small><?= date('M j, H:i', strtotime($notification['created_at'])) ?></small>
                            </div>
                        </div>
                        <?php endforeach; ?>
                    </div>
                <?php endif; ?>
            </section>
        </div>
    </div>
</div>

<?php
require_once __DIR__ . '/../includes/footer.php';

function getNotificationIcon($type) {
    $icons = [
        'match_start' => 'bell',
        'match_result' => 'trophy',
        'team_update' => 'users',
        'system' => 'info-circle',
        'message' => 'envelope'
    ];
    return $icons[$type] ?? 'bell';
}
?>