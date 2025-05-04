<?php
require_once __DIR__ . '/../../config/database.php';
header('Content-Type: text/event-stream');
header('Cache-Control: no-cache');

$matchId = $_GET['match_id'];
$db = getDB();

while (true) {
    $stmt = $db->prepare("
        SELECT m.*, t1.name as team1_name, t2.name as team2_name
        FROM matches m
        LEFT JOIN teams t1 ON m.team1_id = t1.id
        LEFT JOIN teams t2 ON m.team2_id = t2.id
        WHERE m.id = ?
    ");
    $stmt->execute([$matchId]);
    $match = $stmt->fetch();
    
    echo "data: " . json_encode($match) . "\n\n";
    ob_flush();
    flush();
    
    sleep(5); // Update every 5 seconds
}
?>