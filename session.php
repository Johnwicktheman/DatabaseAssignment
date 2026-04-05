<?php
session_start();

 // Always at the very top

// Call this after login to store user data
function setUserSession($id, $username, $role, $extraData = []) {
    $_SESSION['user_id']   = $id;
    $_SESSION['username']  = $username;
    $_SESSION['role']      = $role; // 'admin', 'lecturer', 'supervisor', 'student'
    $_SESSION['data']      = $extraData; // any extra info
}

// Call this to check if user is logged in
function isLoggedIn() {
    return isset($_SESSION['user_id']);
}

// Call this to get current user info anywhere
function getCurrentUser() {
    return [
        'id'       => $_SESSION['user_id']   ?? null,
        'username' => $_SESSION['username']  ?? null,
        'role'     => $_SESSION['role']      ?? null,
        'data'     => $_SESSION['data']      ?? [],
    ];
}

// Call this to logout
function logoutUser() {
    session_destroy();
    header("Location: login.php");
    exit;
}
?>