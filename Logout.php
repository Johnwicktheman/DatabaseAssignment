<?php
session_start(); // Access the current session

// 1. Unset all session variables
$_SESSION = array();

// 3. Destroy the session on the server
session_destroy();

// 4. Redirect to the login page or home page
header("Location: FrontPage.php"); 
exit();
?>