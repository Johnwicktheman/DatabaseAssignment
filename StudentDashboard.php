<?php
session_start();
include 'Connection.php';
include 'ExecutePStatement.php';
include 'AllFunctions.php';

checkAccess(['Student']);

$sqlLect = "SELECT * FROM assessmentrecords WHERE StudentID = ? AND AssesorType = 'Lecturer'";
$SeeResultsLect = executePreparedStatement($sqlLect, [$_SESSION['user_id']]);
$lectGrade = $SeeResultsLect->fetch_assoc();


$sqlSuper = "SELECT * FROM assessmentrecords WHERE StudentID = ? AND AssesorType = 'Supervisor'";
$SeeResultsSuper = executePreparedStatement($sqlSuper, [$_SESSION['user_id']]);
$superGrade = $SeeResultsSuper->fetch_assoc();


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

    <div class = "main">
        <div id="title">Dashboard</div>
        <hr>
        <header>Welcome Back, <?php echo $current_user. "!" ?> </header>
        <header>You are logged in as <?php echo $role; ?>.</header>
        <br>
        <div id="subtitle">manage your students internship blah blah blah</div>

        <div class="container">
            <div class="box">
                <h1>Lecturer Evaluation</h1>
                <?php if ($lectGrade): ?>
                    <p>Score: <strong><?php echo $lectGrade['Internship_Score']; ?>/80</strong></p>
                    <p>Feedback: <?php echo $lectGrade['Feedback']; ?></p>
                <?php else: ?>
                    <p>Pending lecturer evaluation.</p>
                <?php endif; ?>
            </div>

            <div class="box">
                <h1>Supervisor Evaluation</h1>
                <?php if ($superGrade): ?>
                    <p>Score: <strong><?php echo $superGrade['Internship_Score']; ?>/80</strong></p>
                    <p>Feedback: <?php echo $superGrade['Feedback']; ?></p>
                <?php else: ?>
                    <p>Pending supervisor evaluation.</p>
                <?php endif; ?>
            </div>
        </div>
    </div>
</body>
</html>