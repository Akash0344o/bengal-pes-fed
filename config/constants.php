<?php
// Application Constants
define('APP_NAME', 'Bengal Pes Federation');
define('APP_VERSION', '1.0.0');
define('BASE_URL', 'http://localhost/bengal-pes-fed');

// File Upload Constants
define('MAX_UPLOAD_SIZE', 5242880); // 5MB
define('ALLOWED_IMAGE_TYPES', ['jpg', 'jpeg', 'png', 'gif']);
define('ALLOWED_MEDIA_TYPES', ['mp4', 'webm', 'mov']);

// User Roles
define('ROLE_ADMIN', 'admin');
define('ROLE_TEAM_OFFICIAL', 'team_official');
define('ROLE_FAN', 'fan');

// Tournament Statuses
define('TOURNAMENT_UPCOMING', 'upcoming');
define('TOURNAMENT_ONGOING', 'ongoing');
define('TOURNAMENT_COMPLETED', 'completed');
?>