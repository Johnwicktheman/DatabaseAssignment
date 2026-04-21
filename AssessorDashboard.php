<?php
session_start();
include 'Connection.php';
include 'ExecutePStatement.php';

//see if they are loggged in and if they are Assessor or not
if (!isset($_SESSION['username']) || ($_SESSION['user_role'] !== 'Supervisor' && $_SESSION['user_role'] !== 'Lecturer')) {
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
    <link rel="stylesheet" href="CssFiles/AssessorDashBoard.css">
    <title>Document</title>
</head>
<body>
     <nav>
        <p>ASSESSOR PANEL</p>
        <hr>
        <a href="#">Dashboard</a><br>
        <a href="#">Dashboard</a><br>
        <a href="#">Dashboard</a>
    </nav>

    <div class = "main">
        <div id="title">Dashboard</div>
        <hr>
        <header>Welcome Back, <?php echo $current_user. "!" ?> </header>
        <header>You are logged in as <?php echo $role; ?>.</header>
        <br>
        <div id="subtitle">manage your students internship blah blah blah</div>

        <div class ="container">
            <div class="box">
                <i class="fa-solid fa-user"></i>
                <h1>
                    Manage Students Records
                </h1>
                <button onclick="window.location.href='AssessorDashboard/StudentDatabaseAss.php'">Continue</button>
            </div>

            <div class="box">
                <i class="fa-solid fa-user"></i>
                <h1>
                    Manage Students
                </h1>
                <button onclick="window.location.href='Databases/StudentDatabase.php'">Continue</button>
            </div>
        </div>
    </div>
</body>
</html>