<?php
include '../../session.php';
include '../../connection.php';
include '../../ExecutePStatements.php';
include '../AdminFunction.php';

if (!isLoggedIn() || getCurrentUser()['role'] !== 'admin') {
    header("Location: ../../login.php");
    exit;
}

$student_id = $_GET['id'];

if (!$student_id) {
    echo "No student ID provided.";
    exit;
}
$sql = "SELECT sp.*, sa.Username, 
               al.Username as LecturerName, 
               asup.Username as SupervisorName
        FROM studentprofile sp
        JOIN studentaccountlist sa ON sp.StudentAccountID = sa.StudentAccountID
        LEFT JOIN assesoraccountlist al ON sp.AssesorAccountIDLect = al.AssessorAccountID
        LEFT JOIN assesoraccountlist asup ON sp.AssesorAccountIDSuper = asup.AssessorAccountID
        WHERE sp.StudentAccountID = ?";

$result = executePreparedStatement($sql, [$student_id]);

// CRITICAL: You must fetch the data into $row
if ($result && $result->num_rows > 0) {
    $row = $result->fetch_assoc();
} else {
    echo "Student profile not found.";
    exit;
}
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Document</title>
</head>
<body>
    <h2>Profile for <?= $row['FirstName'] . " " . $row['LastName'] ?></h2>
    <p><strong>Programme:</strong> <?= $row['ProgrammeCode'] ?></p>
    <p><strong>Lecturer:</strong> <?= $row['LecturerName'] ?? 'Not Assigned' ?></p>
    <p><strong>Supervisor:</strong> <?= $row['SupervisorName'] ?? 'Not Assigned' ?></p>
    <p><strong>Internship:</strong> <?= $row['InternshipCode'] ?></p>
    <strong>Assessment:</strong> 
    <a href="student_assessment.php?id=<?= $row['StudentAccountID'] ?>" style="color: blue; font-weight: bold;">
        View Assessment Records →
    </a>
    <br>
    <a href="../AdminDashBoard.php" style="color: blue; font-weight: bold;">
        back
    </a>
</body>
</html>