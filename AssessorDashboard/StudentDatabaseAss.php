<?php
ini_set('display_errors', 1);
ini_set('display_startup_errors', 1);
error_reporting(E_ALL);
session_start();
include '../Connection.php';
include '../ExecutePStatement.php';

//see if they are loggged in and if they are admin or not
if (!isset($_SESSION['username']) || ($_SESSION['user_role'] !== 'Supervisor' && $_SESSION['user_role'] !== 'Lecturer')) {
    header("Location: ../FrontPage.php"); 
    exit();
}

//Get current Role and ID currenlty confirmed to be lecturer or supervisor
$assessorID = $_SESSION['user_id'];
$assessorType = $_SESSION['user_role'];

//Check role
if ($assessorType === 'Lecturer') {
    $assessorIDField = 'AssesorAccountIDLect';

} else {
    $assessorIDField = 'AssesorAccountIDSuper';
}

//we want to show students assgined to lecturer or supervisor and show they got record or not
$studentList = "SELECT sp.StudentAccountID, sp.FirstName, sp.LastName, ar.AssessmentCode ,ar.Internship_Score
                FROM studentprofile sp
                LEFT JOIN assessmentrecords ar ON sp.StudentAccountID = ar.StudentID AND ar.AssesorType = ?
                WHERE sp." . $assessorIDField . " = ?";

$studentResult = executePreparedStatement($studentList, [$assessorType, $assessorID]);


?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Document</title>
</head>
<body>
    <p><a href="../AssessorDashboard.php">Back to Dashboard</a></p>
    <table>
        <tr>
        <th>Student ID</th>
        <th>Name</th>
        <th>Assessment Record</th>
        <th>Internship Score</th>
        <th>Action</th>
    </tr>
        <?php
            while ($row = $studentResult->fetch_assoc()) {
                $id        = $row['StudentAccountID'];
                $FirstName = $row['FirstName'];
                $LastName  = $row['LastName'];
                $AssessmentCodeID = $row['AssessmentCode'];// Check if assessment record exists
                $InternshipScore = $row['Internship_Score']; // Check if Internship Score exists

                echo "<tr>";
                echo "<td>" . $id . "</td>";
                echo "<td>" . $FirstName . " " . $LastName . "</td>";
                if ($AssessmentCodeID) {
                    echo "<td><b style='color: green;'>Record Exists</b></td>";
                } else {
                    echo "<td><b style='color: gray;'>No Records</b></td>";
                }
                if ($InternshipScore !== null) {
                    echo "<td><b style='color: green;'>Score: " . $InternshipScore . "</b></td>";
                } else {
                    echo "<td><b style='color: gray;'>No Score</b></td>";
                }

                echo "<td><a href='CreateRecordStudent.php?id=" . $row['StudentAccountID'] . "'>Create Record</a></td>";
                echo "<td><a href='UpdateRecordStudent.php?id=" . $row['StudentAccountID'] . "'>Update</a></td>";
                echo "</tr>";
            }
        ?>


    </table>
</body>
</html>