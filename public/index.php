<?php
require_once __DIR__ . '/../includes/header.php';
$pageTitle = 'Home';
?>

<section class="hero-section">
    <div class="hero-content">
        <h1>Welcome to Bengal Pes Federation</h1>
        <p>The premier football organization in West Bengal</p>
        <div class="hero-buttons">
            <a href="<?= BASE_URL ?>/register.php" class="btn btn-primary">Join Our Community</a>
            <a href="<?= BASE_URL ?>/tournaments.php" class="btn btn-outline">View Tournaments</a>
        </div>
    </div>
</section>

<section class="featured-section">
    <div class="section-header">
        <h2>Featured Teams</h2>
        <a href="<?= BASE_URL ?>/teams.php" class="view-all">View All Teams</a>
    </div>
    
    <div class="team-grid" id="featuredTeams">
        <!-- Loaded via AJAX -->
    </div>
</section>

<section class="featured-section">
    <div class="section-header">
        <h2>Upcoming Matches</h2>
        <a href="<?= BASE_URL ?>/tournaments.php" class="view-all">View Full Schedule</a>
    </div>
    
    <div class="matches-list" id="upcomingMatches">
        <!-- Loaded via AJAX -->
    </div>
</section>

<section class="news-section">
    <div class="section-header">
        <h2>Latest News</h2>
        <a href="<?= BASE_URL ?>/media.php" class="view-all">View All News</a>
    </div>
    
    <div class="news-grid" id="latestNews">
        <!-- Loaded via AJAX -->
    </div>
</section>

<script>
document.addEventListener('DOMContentLoaded', function() {
    // Load featured content via AJAX
    fetch('<?= BASE_URL ?>/api/teams/featured.php')
        .then(response => response.json())
        .then(teams => {
            const container = document.getElementById('featuredTeams');
            container.innerHTML = teams.map(team => `
                <div class="team-card">
                    <div class="team-logo">
                        <img src="<?= BASE_URL ?>/assets/uploads/teams/${team.logo}" alt="${team.name}">
                    </div>
                    <h3>${team.name}</h3>
                    <div class="team-meta">
                        <span><i class="fas fa-users"></i> ${team.followers} Followers</span>
                        <span><i class="fas fa-trophy"></i> ${team.trophies} Titles</span>
                    </div>
                    <a href="<?= BASE_URL ?>/team.php?id=${team.id}" class="btn btn-outline">View Team</a>
                </div>
            `).join('');
        });
    
    // Load upcoming matches
    fetch('<?= BASE_URL ?>/api/matches/upcoming.php')
        .then(response => response.json())
        .then(matches => {
            const container = document.getElementById('upcomingMatches');
            container.innerHTML = matches.map(match => `
                <div class="match-card">
                    <div class="match-teams">
                        <div class="team">
                            <img src="<?= BASE_URL ?>/assets/uploads/teams/${match.team1_logo}" alt="${match.team1_name}">
                            <span>${match.team1_name}</span>
                        </div>
                        <div class="match-info">
                            <div class="match-time">${new Date(match.scheduled_time).toLocaleString()}</div>
                            <div class="match-tournament">${match.tournament_name}</div>
                        </div>
                        <div class="team">
                            <img src="<?= BASE_URL ?>/assets/uploads/teams/${match.team2_logo}" alt="${match.team2_name}">
                            <span>${match.team2_name}</span>
                        </div>
                    </div>
                    <a href="<?= BASE_URL ?>/match.php?id=${match.id}" class="btn btn-outline">View Details</a>
                </div>
            `).join('');
        });
    
    // Load latest news
    fetch('<?= BASE_URL ?>/api/media/latest.php')
        .then(response => response.json())
        .then(newsItems => {
            const container = document.getElementById('latestNews');
            container.innerHTML = newsItems.map(item => `
                <div class="news-card">
                    <div class="news-image">
                        <img src="<?= BASE_URL ?>/assets/uploads/media/${item.thumbnail}" alt="${item.title}">
                    </div>
                    <div class="news-content">
                        <h3>${item.title}</h3>
                        <p class="news-excerpt">${item.excerpt}</p>
                        <div class="news-meta">
                            <span class="news-date">${new Date(item.created_at).toLocaleDateString()}</span>
                            <a href="<?= BASE_URL ?>/news.php?id=${item.id}" class="read-more">Read More</a>
                        </div>
                    </div>
                </div>
            `).join('');
        });
});
</script>

<?php
require_once __DIR__ . '/../includes/footer.php';
?>