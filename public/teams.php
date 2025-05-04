<?php
require_once __DIR__ . '/../includes/header.php';
$pageTitle = 'Teams';
$customScript = 'teams';

// Pagination setup
$page = isset($_GET['page']) ? (int)$_GET['page'] : 1;
$perPage = 12;
$offset = ($page - 1) * $perPage;

// Get teams from database
$db = getDB();
$stmt = $db->prepare("
    SELECT t.*, COUNT(uf.id) as followers 
    FROM teams t
    LEFT JOIN user_favorites uf ON uf.team_id = t.id
    GROUP BY t.id
    ORDER BY followers DESC
    LIMIT :limit OFFSET :offset
");
$stmt->bindValue(':limit', $perPage, PDO::PARAM_INT);
$stmt->bindValue(':offset', $offset, PDO::PARAM_INT);
$stmt->execute();
$teams = $stmt->fetchAll();

// Get total count for pagination
$totalStmt = $db->query("SELECT COUNT(*) FROM teams");
$totalTeams = $totalStmt->fetchColumn();
$totalPages = ceil($totalTeams / $perPage);
?>

<section class="teams-section">
    <div class="section-header">
        <h1>Our Teams</h1>
        <div class="team-filters">
            <input type="text" id="teamSearch" placeholder="Search teams...">
            <select id="teamSort">
                <option value="popular">Most Popular</option>
                <option value="name_asc">Name (A-Z)</option>
                <option value="name_desc">Name (Z-A)</option>
                <option value="newest">Newest First</option>
                <option value="oldest">Oldest First</option>
            </select>
        </div>
    </div>
    
    <div class="team-grid">
        <?php foreach ($teams as $team): ?>
        <div class="team-card" data-team-id="<?= $team['id'] ?>">
            <div class="team-logo">
                <img src="<?= BASE_URL ?>/assets/uploads/teams/<?= $team['logo'] ?>" alt="<?= htmlspecialchars($team['name']) ?>">
            </div>
            <h3><?= htmlspecialchars($team['name']) ?></h3>
            <div class="team-meta">
                <span><i class="fas fa-users"></i> <?= $team['followers'] ?> Followers</span>
                <span><i class="fas fa-trophy"></i> <?= $team['trophies'] ?? 0 ?> Titles</span>
            </div>
            <a href="<?= BASE_URL ?>/team.php?id=<?= $team['id'] ?>" class="btn btn-outline">View Team</a>
            <?php if (isLoggedIn()): ?>
                <button class="favorite-btn <?= isTeamFavorite($team['id']) ? 'favorited' : '' ?>" 
                        data-team-id="<?= $team['id'] ?>">
                    <i class="fas fa-star"></i>
                </button>
            <?php endif; ?>
        </div>
        <?php endforeach; ?>
    </div>
    
    <?php if ($totalPages > 1): ?>
    <div class="pagination">
        <?php if ($page > 1): ?>
            <a href="?page=<?= $page - 1 ?>" class="page-link">&laquo; Previous</a>
        <?php endif; ?>
        
        <?php for ($i = 1; $i <= $totalPages; $i++): ?>
            <a href="?page=<?= $i ?>" class="page-link <?= $i === $page ? 'active' : '' ?>"><?= $i ?></a>
        <?php endfor; ?>
        
        <?php if ($page < $totalPages): ?>
            <a href="?page=<?= $page + 1 ?>" class="page-link">Next &raquo;</a>
        <?php endif; ?>
    </div>
    <?php endif; ?>
</section>

<?php
require_once __DIR__ . '/../includes/footer.php';

function isTeamFavorite($teamId) {
    if (!isLoggedIn()) return false;
    
    $db = getDB();
    $stmt = $db->prepare("
        SELECT id FROM user_favorites 
        WHERE user_id = ? AND team_id = ?
    ");
    $stmt->execute([$_SESSION['user_id'], $teamId]);
    return $stmt->fetch() !== false;
}
?>