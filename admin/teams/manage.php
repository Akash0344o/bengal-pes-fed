<?php
require_once __DIR__ . '/../../includes/auth.php';

if (!isAdmin()) {
    header("Location: " . BASE_URL . "/admin/login.php");
    exit();
}

$pageTitle = 'Manage Teams';
$customScript = 'admin-teams';

$db = getDB();

// Pagination setup
$page = isset($_GET['page']) ? (int)$_GET['page'] : 1;
$perPage = 10;
$offset = ($page - 1) * $perPage;

// Get teams with sorting and filtering
$search = $_GET['search'] ?? '';
$sort = $_GET['sort'] ?? 'name_asc';

$query = "SELECT t.*, COUNT(uf.id) as followers FROM teams t LEFT JOIN user_favorites uf ON t.id = uf.team_id";
$params = [];
$where = [];

if (!empty($search)) {
    $where[] = "(t.name LIKE ? OR t.description LIKE ?)";
    $params[] = "%$search%";
    $params[] = "%$search%";
}

if (!empty($where)) {
    $query .= " WHERE " . implode(' AND ', $where);
}

$query .= " GROUP BY t.id";

switch ($sort) {
    case 'name_desc':
        $query .= " ORDER BY t.name DESC";
        break;
    case 'followers_desc':
        $query .= " ORDER BY followers DESC";
        break;
    case 'followers_asc':
        $query .= " ORDER BY followers ASC";
        break;
    case 'oldest':
        $query .= " ORDER BY t.created_at ASC";
        break;
    case 'newest':
        $query .= " ORDER BY t.created_at DESC";
        break;
    default:
        $query .= " ORDER BY t.name ASC";
        break;
}

// Add pagination
$query .= " LIMIT ? OFFSET ?";
$params[] = $perPage;
$params[] = $offset;

$stmt = $db->prepare($query);
$stmt->execute($params);
$teams = $stmt->fetchAll();

// Get total count for pagination
$countQuery = "SELECT COUNT(*) FROM teams";
if (!empty($where)) {
    $countQuery .= " WHERE " . implode(' AND ', $where);
}

$countStmt = $db->prepare($countQuery);
$countStmt->execute(array_slice($params, 0, count($params) - 2));
$totalTeams = $countStmt->fetchColumn();
$totalPages = ceil($totalTeams / $perPage);

require_once __DIR__ . '/../../includes/header.php';
?>

<div class="admin-container">
    <div class="admin-header">
        <h1>Manage Teams</h1>
        <a href="create.php" class="btn btn-primary">
            <i class="fas fa-plus"></i> Add New Team
        </a>
    </div>
    
    <div class="admin-filters">
        <form method="GET" class="search-form">
            <input type="text" name="search" placeholder="Search teams..." value="<?= htmlspecialchars($search) ?>">
            <button type="submit" class="btn btn-outline">
                <i class="fas fa-search"></i> Search
            </button>
        </form>
        
        <div class="sort-options">
            <span>Sort by:</span>
            <select id="sortSelect">
                <option value="name_asc" <?= $sort === 'name_asc' ? 'selected' : '' ?>>Name (A-Z)</option>
                <option value="name_desc" <?= $sort === 'name_desc' ? 'selected' : '' ?>>Name (Z-A)</option>
                <option value="followers_desc" <?= $sort === 'followers_desc' ? 'selected' : '' ?>>Most Followers</option>
                <option value="followers_asc" <?= $sort === 'followers_asc' ? 'selected' : '' ?>>Fewest Followers</option>
                <option value="newest" <?= $sort === 'newest' ? 'selected' : '' ?>>Newest First</option>
                <option value="oldest" <?= $sort === 'oldest' ? 'selected' : '' ?>>Oldest First</option>
            </select>
        </div>
    </div>
    
    <div class="admin-table-container">
        <table class="admin-table">
            <thead>
                <tr>
                    <th>Logo</th>
                    <th>Team Name</th>
                    <th>Followers</th>
                    <th>Founded</th>
                    <th>Status</th>
                    <th>Actions</th>
                </tr>
            </thead>
            <tbody>
                <?php foreach ($teams as $team): ?>
                <tr>
                    <td>
                        <img src="<?= BASE_URL ?>/assets/uploads/teams/<?= $team['logo'] ?>" 
                             alt="<?= htmlspecialchars($team['name']) ?>" class="team-logo-small">
                    </td>
                    <td><?= htmlspecialchars($team['name']) ?></td>
                    <td><?= $team['followers'] ?></td>
                    <td><?= $team['founded_year'] ?></td>
                    <td>
                        <span class="status-badge <?= $team['status'] === 'active' ? 'active' : 'inactive' ?>">
                            <?= ucfirst($team['status']) ?>
                        </span>
                    </td>
                    <td>
                        <div class="action-buttons">
                            <a href="edit.php?id=<?= $team['id'] ?>" class="btn btn-small btn-outline">
                                <i class="fas fa-edit"></i> Edit
                            </a>
                            <button class="btn btn-small btn-danger delete-team" data-team-id="<?= $team['id'] ?>">
                                <i class="fas fa-trash"></i> Delete
                            </button>
                        </div>
                    </td>
                </tr>
                <?php endforeach; ?>
            </tbody>
        </table>
        
        <?php if (empty($teams)): ?>
            <div class="no-results">
                <p>No teams found matching your criteria.</p>
            </div>
        <?php endif; ?>
    </div>
    
    <?php if ($totalPages > 1): ?>
    <div class="pagination">
        <?php if ($page > 1): ?>
            <a href="?page=<?= $page - 1 ?>&search=<?= urlencode($search) ?>&sort=<?= $sort ?>" class="page-link">
                &laquo; Previous
            </a>
        <?php endif; ?>
        
        <?php for ($i = 1; $i <= $totalPages; $i++): ?>
            <a href="?page=<?= $i ?>&search=<?= urlencode($search) ?>&sort=<?= $sort ?>" 
               class="page-link <?= $i === $page ? 'active' : '' ?>">
                <?= $i ?>
            </a>
        <?php endfor; ?>
        
        <?php if ($page < $totalPages): ?>
            <a href="?page=<?= $page + 1 ?>&search=<?= urlencode($search) ?>&sort=<?= $sort ?>" class="page-link">
                Next &raquo;
            </a>
        <?php endif; ?>
    </div>
    <?php endif; ?>
</div>

<script>
document.addEventListener('DOMContentLoaded', function() {
    // Sort select change handler
    document.getElementById('sortSelect').addEventListener('change', function() {
        const url = new URL(window.location.href);
        url.searchParams.set('sort', this.value);
        window.location.href = url.toString();
    });
    
    // Delete team buttons
    document.querySelectorAll('.delete-team').forEach(button => {
        button.addEventListener('click', function() {
            const teamId = this.dataset.teamId;
            
            if (confirm('Are you sure you want to delete this team? This action cannot be undone.')) {
                fetch('<?= BASE_URL ?>/api/admin/teams/delete.php', {
                    method: 'POST',
                    headers: {
                        'Content-Type': 'application/json',
                    },
                    body: JSON.stringify({ team_id: teamId })
                })
                .then(response => response.json())
                .then(data => {
                    if (data.success) {
                        window.location.reload();
                    } else {
                        alert('Error deleting team: ' + data.message);
                    }
                })
                .catch(error => {
                    console.error('Error:', error);
                    alert('An error occurred while deleting the team.');
                });
            }
        });
    });
});
</script>

<?php
require_once __DIR__ . '/../../includes/footer.php';
?>