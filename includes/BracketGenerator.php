<?php
class BracketGenerator {
    private $db;
    
    public function __construct($db) {
        $this->db = $db;
    }
    
    public function generateSingleElimination($teams, $tournamentId) {
        $teamCount = count($teams);
        $rounds = ceil(log($teamCount, 2));
        
        $bracket = [];
        for ($round = 1; $round <= $rounds; $round++) {
            $matches = [];
            $matchesInRound = $teamCount / pow(2, $round);
            
            for ($match = 1; $match <= $matchesInRound; $match++) {
                $team1 = ($round === 1) ? $teams[($match-1)*2] : null;
                $team2 = ($round === 1 && isset($teams[($match-1)*2+1])) 
                         ? $teams[($match-1)*2+1] : null;
                
                $matches[] = [
                    'round' => $round,
                    'match_number' => count($bracket)*$matchesInRound + $match,
                    'team1' => $team1,
                    'team2' => $team2
                ];
            }
            $bracket[] = [
                'round_number' => $round,
                'matches' => $matches
            ];
        }
        
        $this->saveToDatabase($tournamentId, $bracket);
        return $bracket;
    }
    
    private function saveToDatabase($tournamentId, $bracket) {
        $db = $this->db;
        $db->beginTransaction();
        
        try {
            $stmt = $db->prepare(
                "INSERT INTO matches 
                (tournament_id, round_number, match_number, team1_id, team2_id) 
                VALUES (?, ?, ?, ?, ?)"
            );
            
            foreach ($bracket as $round) {
                foreach ($round['matches'] as $match) {
                    $stmt->execute([
                        $tournamentId,
                        $round['round_number'],
                        $match['match_number'],
                        $match['team1']['id'] ?? null,
                        $match['team2']['id'] ?? null
                    ]);
                }
            }
            $db->commit();
        } catch (PDOException $e) {
            $db->rollBack();
            throw $e;
        }
    }
}
?>