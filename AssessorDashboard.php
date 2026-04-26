<?php
session_start();
include 'Connection.php';
include 'ExecutePStatement.php';
include 'AllFunctions.php';

checkAccess(['Lecturer', 'Supervisor']);

//Get current Role and ID currently confirmed to be lecturer or supervisor
$assessorID = $_SESSION['user_id'];
$assessorType = $_SESSION['user_role'];

//Check role
if ($assessorType === 'Lecturer') {
    $assessorIDField = 'AssesorAccountIDLect';
} else {
    $assessorIDField = 'AssesorAccountIDSuper';
}

$current_user = $_SESSION['username'];
$role = $_SESSION['user_role'];
//find total assgined students
$sqlTotal = "SELECT COUNT(*) as total FROM studentprofile WHERE $assessorIDField = ?";
$resTotal = executePreparedStatement($sqlTotal, [$assessorID]);
$totalStudents = $resTotal->fetch_assoc()['total'];

//count completed assessments for students assigned to this lecturer
$sqlDone = "SELECT COUNT(*) as done FROM assessmentrecords ar
            JOIN studentprofile sp ON ar.StudentID = sp.StudentAccountID
            WHERE sp.$assessorIDField = ? AND ar.AssesorType = ?";
$resDone = executePreparedStatement($sqlDone, [$assessorID, $assessorType]);
$doneRecords = $resDone->fetch_assoc()['done'];


$pendingRecords = $totalStudents - $doneRecords;
$percentage = ($totalStudents > 0) ? round(($doneRecords / $totalStudents) * 100) : 0;

?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <link rel="stylesheet" href="CssFiles/AssessorDashBoard.css">


    <!-- Font import -->
    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
    <link href="https://fonts.googleapis.com/css2?family=Bricolage+Grotesque:opsz,wght@12..96,200..800&display=swap" rel="stylesheet">

    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.5.1/css/all.min.css"> 

    <meta name="viewport" content="width=device-width, initial-scale=1.0">

    <style>
        .stats {
            display: flex;
            gap: 20px;
            margin-top: 20px;
            margin-bottom: 30px;
        }

        .stat-box {
            background-color: white;
            flex: 1;
            padding: 25px;
            border-radius: 20px;
            box-shadow: 0 4px 15px rgba(0,0,0,0.05);
            text-align: center;
            transition: 0.3s;
        }

        .stat-box:hover {
            transform: translateY(-5px);
            box-shadow: 0 8px 25px rgba(0,0,0,0.1);
        }

        .stat-title {
            color: #aaa9a9;
            font-size: 14px;
            font-weight: bold;
            text-transform: uppercase;
            letter-spacing: 1px;
        }

        .stat-value {
            font-size: 35px;
            font-weight: 800;
            color: #154c4b;
            margin: 10px 0;
        }

        .stat-desc {
            font-size: 13px;
            color: #777;
        }
    </style>
    <title>Document</title>
</head>
<body>
        <nav>
            <p>ASSESSOR PANEL</p>
            <hr>
            <a href="#">Dashboard</a><br>
            <a href="AssessorDashboard/StudentDatabaseAss.php">Assessment Records</a><br>
            <a href="AssessorDashboard/ViewStudentDatabase.php">Student Database</a><br>
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

        <div class="stats">
            <div class="stat-box">
                <div class="stat-title">Total Students</div>
                <div class="stat-value"><?php echo $totalStudents; ?></div>
                <div class="stat-desc">Assigned to you</div>
            </div>

            <div class="stat-box">
                <div class="stat-title">Pending Records</div>
                <div class="stat-value" style="color: #e74c3c;"><?php echo $pendingRecords; ?></div>
                <div class="stat-desc">Awaiting assessment</div>
            </div>

            <div class="stat-box">
                <div class="stat-title">Completion</div>
                <div class="stat-value" style="color: #219e75;"><?php echo $percentage; ?>%</div>
                <div class="stat-desc"><?php echo $doneRecords; ?> out of <?php echo $totalStudents; ?> done</div>
            </div>
        </div>
    
        <div class ="container">
            <div class="box">
                <i class="fa-solid fa-user"></i>
                <h1>
                    Manage Students Records
                </h1>
                <p>Find students assigned to you here </p>
                <button onclick="window.location.href='AssessorDashboard/StudentDatabaseAss.php'">Continue</button>
            </div>

            <div class="box">
                <i class="fa-solid fa-eye"></i>
                <h1>
                    View Students Database
                </h1>
                <p>View all students in the system </p>
                <button onclick="window.location.href='AssessorDashboard/ViewStudentDatabase.php'">Continue</button>
            </div>
        </div>
    </div>
</body>
</html>