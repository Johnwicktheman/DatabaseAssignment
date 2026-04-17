<?php
$servername = "localhost";
$username = "root";      // ← make sure this is correct
$password = "root";          // ← XAMPP default is empty, WAMP is empty too
$database = "courseworkdb";

$conn = new mysqli($servername, $username, $password, $database);

if ($conn->connect_error) {
    die("Connection failed: " . $conn->connect_error);
}
?>