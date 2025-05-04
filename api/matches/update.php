<?php
require_once __DIR__ . '/../../config/database.php';
require_once __DIR__ . '/../../includes/auth.php';

header('Content-Type: application/json');

if (!isAdmin()) {
    http_response_code(403);
    echo json_encode(['error' => 'Unauthorized access']);
    exit();
}

$data = json_decode(file_get_contents('php://input'), true);

if (empty($data['match_id']) || !isset($data['team1_score']) || !isset($data['team2_score'])) {
    http_response_code(400);
    echo json_encode(['error' => 'Invalid input data']);
    exit();
}

try {
    $db = getDB();
    
    // Update match scores
    $stmt = $db->prepare("
        UPDATE matches 
        SET team1_score = :score1, 
            team2_score = :score2,
            status = IF(NOW() >= scheduled_time, 'in_progress', status),
            updated_at = NOW()
        WHERE id = :match_id
    ");
    $stmt->execute([
        ':score1' => $data['team1_score'],
        ':score2' => $data['team2_score'],
        ':match_id' => $data['match_id']
    ]);
    
    // Determine winner if match is completed
    if ($data['status'] === 'completed') {
        $winnerId = $data['team1_score'] > $data['team2_score'] ? 
            $data['team1_id'] : $data['team2_id'];
        
        $winnerStmt = $db->prepare("
            UPDATE matches 
            SET winner_id = :winner_id,
                status = 'completed'
            WHERE id = :match_id
        ");
        $winnerStmt->execute([
            ':winner_id' => $winnerId,
            ':match_id' => $data['match_id']
        ]);
        
        // Propagate winner to next round in tournament
        $thisMatchStmt = $db->prepare("
            SELECT tournament_id, round_number, match_number 
            FROM matches 
            WHERE id = ?
        ");
        $thisMatchStmt->execute([$data['match_id']]);
        $matchInfo = $thisMatchStmt->fetch();
        
        if ($matchInfo) {
            $nextRound = $matchInfo['round_number'] + 1;
            $nextMatchNumber = ceil($matchInfo['match_number'] / 2);
            
            $updateNextStmt = $db->prepare("
                UPDATE matches
                SET team1_id = IF(:current_position = 1, :winner_id, team1_id),
                    team2_id = IF(:current_position = 2, :winner_id, team2_id)
                WHERE tournament_id = :tournament_id
                AND round_number = :next_round
                AND match_number = :next_match
            ");
            
            $currentPosition = $matchInfo['match_number'] % 2 === 1 ? 1 : 2;
            
            $updateNextStmt->execute([
                ':winner_id' => $winnerId,
                ':current_position' => $currentPosition,
                ':tournament_id' => $matchInfo['tournament_id'],
                ':next_round' => $nextRound,
                ':next_match' => $nextMatchNumber
            ]);
        }
    }
    
    echo json_encode(['success' => true]);
} catch (PDOException $e) {
    http_response_code(500);
    echo json_encode(['error' => 'Database error: ' . $e->getMessage()]);
}
?>