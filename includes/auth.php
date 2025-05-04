<?php
session_start();
require_once __DIR__ . '/../config/database.php';

function isLoggedIn() {
    return isset($_SESSION['user_id']);
}

function isAdmin() {
    return isLoggedIn() && $_SESSION['user_role'] === 'admin';
}

function loginUser($user) {
    $_SESSION['user_id'] = $user['id'];
    $_SESSION['user_email'] = $user['email'];
    $_SESSION['user_role'] = $user['role'];
}

function registerUser($email, $password, $username, $role = 'fan') {
    $db = getDB();
    $hashedPassword = password_hash($password, PASSWORD_BCRYPT);
    
    $stmt = $db->prepare(
        "INSERT INTO users (email, password, username, role, status) 
         VALUES (?, ?, ?, ?, 'pending')"
    );
    $stmt->execute([$email, $hashedPassword, $username, $role]);
    return $db->lastInsertId();
}

function verifyCredentials($email, $password) {
    $db = getDB();
    $stmt = $db->prepare("SELECT * FROM users WHERE email = ?");
    $stmt->execute([$email]);
    $user = $stmt->fetch();
    
    return ($user && password_verify($password, $user['password'])) ? $user : false;
}
?>