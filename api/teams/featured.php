<?php
require_once __DIR__ . '/../../config/database.php';
require_once __DIR__ . '/../../includes/auth.php';

header('Content-Type: application/json');

try {
    $db = getDB();
    
    $stmt = $db->prepare("
        SELECT t.id, t.name, t.logo, COUNT(uf.id) as followers, 
               (SELECT COUNT(*) FROM team_trophies WHERE team_id = t.id) as trophies
        FROM teams t
        LEFT JOIN user_favorites uf ON t.id = uf.team_id
        WHERE t.featured = 1
        GROUP BY t.id
        ORDER BY RAND()
        LIMIT 4
    ");
    $stmt->execute();
    $teams = $stmt->fetchAll(PDO::FETCH_ASSOC);
    
    // Process logos for full URL
    foreach ($teams as &$team) {
        $team['logo'] = BASE_URL . '/assets/uploads/teams/' . $team['logo'];
    }
    
    echo json_encode($teams);
} catch (PDOException $e) {
    http_response_code(500);
    echo json_encode(['error' => 'Database error: ' . $e->getMessage()]);
}
?>