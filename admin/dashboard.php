<?php
require_once __DIR__ . '/../../includes/auth.php';
require_once __DIR__ . '/../../includes/header.php';

if (!isAdmin()) {
    header("Location: /login.php");
    exit();
}

$db = getDB();
$stats = [
    'users' => $db->query("SELECT COUNT(*) FROM users")->fetchColumn(),
    'teams' => $db->query("SELECT COUNT(*) FROM teams")->fetchColumn(),
    'matches' => $db->query("SELECT COUNT(*) FROM matches")->fetchColumn()
];
?>

<div class="admin-dashboard">
    <h1>Admin Dashboard</h1>
    
    <div class="stats-grid">
        <?php foreach ($stats as $key => $value): ?>
        <div class="stat-card">
            <h3><?= ucfirst($key) ?></h3>
            <div class="stat-value"><?= $value ?></div>
        </div>
        <?php endforeach; ?>
    </div>
    
    <div class="quick-actions">
        <a href="teams/create.php" class="btn btn-primary">Add Team</a>
        <a href="tournaments/create.php" class="btn btn-primary">Create Tournament</a>
    </div>
</div>

<?php require_once __DIR__ . '/../../includes/footer.php'; ?>