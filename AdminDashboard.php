<?php
session_start();
include 'Connection.php';
include 'ExecutePStatement.php';
include 'AllFunctions.php';

checkAccess('Admin');

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
        <a href="Dashboard.php">Dashboard</a><br>
        <a href="Logout.php" style="color: #ff4d4d; font-weight: bold;" onclick="return confirm('Are you sure you want to logout?')">Logout</a>
</nav>
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
                    Manage Assessors
                </h1>
                <button onclick="window.location.href='AdminDashboard/Databases/AssessorDatabase.php'">Continue</button>
            </div>

            <div class="box">
                <i class="fa-solid fa-user"></i>
                <h1>
                    Manage Students
                </h1>
                <button onclick="window.location.href='AdminDashboard/Databases/StudentDatabase.php'">Continue</button>
            </div>
        </div>
    </div>
</body>
</html>