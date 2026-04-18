<?php
session_start();
include 'Connection.php';
include 'ExecutePStatement.php';

//see if they are loggged in and if they are admin or not
if (!isset($_SESSION['username']) || $_SESSION['user_role'] !== 'admin') {
    header("Location: ../FrontPage.php"); 
    exit();
}

$current_user = $_SESSION['username'];
$role = $_SESSION['user_role'];

?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Document</title>
</head>
<body>
    <p>hi, 
    <?php echo $current_user. "!" ?>
    You are logged in as 
    <?php echo $role; ?>.
    </p>

    <p>Here are your options:</p>
    <ul>

        <li><a href="Databases/AssessorDatabase.php">Assessor Database</a></li>
        <li><a href="Databases/StudentDatabase.php">Student Database</a></li>
</body>
</html>