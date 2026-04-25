<?php
session_start(); 

//Unset all session variables
$_SESSION = array();

//Destroy the session on the server
session_destroy();

//Redirect to the login page or home page
header("Location: FrontPage.php"); 
exit();
?>