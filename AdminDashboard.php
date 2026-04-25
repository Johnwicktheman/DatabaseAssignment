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
    <link rel="stylesheet" href="CssFiles/AdminDashBoard2.css">

    <!-- Font import -->
    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
    <link href="https://fonts.googleapis.com/css2?family=Bricolage+Grotesque:opsz,wght@12..96,200..800&display=swap" rel="stylesheet">

    <title>Document</title>
</head>
<body>
     <nav>
        <p> ADMIN PANEL</p>
        <hr>
        <a href="AdminDashboard.php">Dashboard</a><br>
        <a href="AdminDashboard/Databases/StudentDatabase.php" class="active">Student Accounts</a><br>
        <a href="AdminDashboard/Databases/AssessorDatabase.php">Assessor Accounts</a><br>
        <a href="AdminDashboard/Databases/CompanyDatabase.php">Company Database</a><br>
        <a href="AdminDashboard/Databases/results.php">Result Viewing</a><br>
        <div id="logout">
        <a href="Logout.php" onclick="return confirm('Are you sure you want to logout?')">Logout</a>
        </div>
    </nav>

    <div class = "main">
        <div id="title">Dashboard</div>
        <hr>
        <header>Welcome Back, <?php echo $current_user. "!" ?> </header>
        <br>
        <div id="subtitle">You are logged in as <?php echo $role; ?>.</div>

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

             <div class="box">
                <i class="fa-solid fa-user"></i>
                <h1>
                    Manage Companies
                </h1>
                <button onclick="window.location.href='AdminDashboard/Databases/CompanyDatabase.php'">Continue</button>
            </div>
        </div>

        <div class="container">
            <div class="box">
                <i class="fa-solid fa-user"></i>
                <h1>
                    Result Viewing
                </h1>
                <button onclick="window.location.href='AdminDashboard/Databases/results.php'">Continue</button>
            </div>  
        </div>
    </div>
</body>
</html>