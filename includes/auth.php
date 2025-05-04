<?php
require_once __DIR__ . '/../config/database.php';
require_once __DIR__ . '/../config/constants.php';

session_start();

function isLoggedIn() {
    return isset($_SESSION['user_id']);
}

function isAdmin() {
    return isLoggedIn() && $_SESSION['user_role'] === ROLE_ADMIN;
}

function isTeamOfficial() {
    return isLoggedIn() && $_SESSION['user_role'] === ROLE_TEAM_OFFICIAL;
}

function loginUser($user) {
    $_SESSION['user_id'] = $user['id'];
    $_SESSION['user_email'] = $user['email'];
    $_SESSION['user_role'] = $user['role'];
    $_SESSION['user_status'] = $user['status'];
}

function logoutUser() {
    session_unset();
    session_destroy();
}

function registerUser($email, $password, $username, $role = ROLE_FAN) {
    $db = getDB();
    $hashedPassword = password_hash($password, PASSWORD_BCRYPT);
    
    try {
        $stmt = $db->prepare(
            "INSERT INTO users (email, password, username, role, status) 
             VALUES (?, ?, ?, ?, 'pending')"
        );
        $stmt->execute([$email, $hashedPassword, $username, $role]);
        return $db->lastInsertId();
    } catch (PDOException $e) {
        error_log("Registration error: " . $e->getMessage());
        return false;
    }
}

function verifyCredentials($email, $password) {
    $db = getDB();
    $stmt = $db->prepare("SELECT * FROM users WHERE email = ?");
    $stmt->execute([$email]);
    $user = $stmt->fetch();
    
    if ($user && password_verify($password, $user['password'])) {
        return $user;
    }
    return false;
}
?>