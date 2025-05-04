<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title><?= APP_NAME ?> - <?= $pageTitle ?? 'Home' ?></title>
    <link rel="stylesheet" href="<?= BASE_URL ?>/assets/css/main.css">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0/css/all.min.css">
</head>
<body>
    <header class="main-header">
        <div class="container">
            <div class="logo">
                <a href="<?= BASE_URL ?>">
                    <img src="<?= BASE_URL ?>/assets/images/logo.png" alt="Bengal Pes Federation">
                </a>
            </div>
            
            <nav class="main-nav">
                <ul>
                    <li><a href="<?= BASE_URL ?>/teams.php">Teams</a></li>
                    <li><a href="<?= BASE_URL ?>/tournaments.php">Tournaments</a></li>
                    <li><a href="<?= BASE_URL ?>/media.php">Media</a></li>
                    <li><a href="<?= BASE_URL ?>/contact.php">Contact</a></li>
                </ul>
            </nav>
            
            <div class="auth-buttons">
                <?php if (isLoggedIn()): ?>
                    <div class="user-dropdown">
                        <button class="user-btn">
                            <i class="fas fa-user-circle"></i>
                            <?= $_SESSION['user_email'] ?>
                        </button>
                        <div class="dropdown-content">
                            <a href="<?= BASE_URL ?>/user/dashboard.php">Dashboard</a>
                            <?php if (isAdmin()): ?>
                                <a href="<?= BASE_URL ?>/admin/dashboard.php">Admin Panel</a>
                            <?php endif; ?>
                            <a href="<?= BASE_URL ?>/user/profile.php">Profile</a>
                            <a href="<?= BASE_URL ?>/logout.php">Logout</a>
                        </div>
                    </div>
                <?php else: ?>
                    <a href="<?= BASE_URL ?>/login.php" class="btn btn-outline">Login</a>
                    <a href="<?= BASE_URL ?>/register.php" class="btn btn-primary">Register</a>
                <?php endif; ?>
            </div>
            
            <button class="mobile-menu-btn">
                <i class="fas fa-bars"></i>
            </button>
        </div>
    </header>

    <main class="container">