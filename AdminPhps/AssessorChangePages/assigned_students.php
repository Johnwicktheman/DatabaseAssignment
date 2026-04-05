<?php

ini_set('display_errors', 1);
ini_set('display_startup_errors', 1);
error_reporting(E_ALL);
include '../../session.php';
include '../../connection.php';
include '../../ExecutePStatements.php';

if (!isLoggedIn() || getCurrentUser()['role'] !== 'admin') {
    header("Location: ../../login.php");
    exit;
}

$assessor_id = $_GET['id'];
$type        = $_GET['type']; // 'Lecturer' or 'Supervisor'

// Determine which column in studentprofile to filter by
$column = ($type === 'Lecturer') ? 'AssesorAccountIDLect' : 'AssesorAccountIDSuper';

// Query to get student details and their profile info
$sql = "SELECT sp.*, sa.Username 
        FROM studentprofile sp
        JOIN studentaccountlist sa ON sp.StudentAccountID = sa.StudentAccountID
        WHERE sp.$column = ?";

$result = executePreparedStatement($sql, [$assessor_id]);

// Fetch assessor name for the title
$assessor_info = executePreparedStatement("SELECT Username FROM assesoraccountlist WHERE AssessorAccountID = ?", [$assessor_id])->fetch_assoc();
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <title>Assigned Students</title>
    <style>
        table { width: 100%; border-collapse: collapse; }
        th, td { border: 1px solid #ddd; padding: 8px; text-align: left; }
        th { background-color: #f2f2f2; }
    </style>
</head>
<body>
    <header>
    <p><a href="../AssessorDB.php">← Back to Assessors</a></p>
    
    <h2>Students assigned to <?= $type ?>: <?= htmlspecialchars($assessor_info['Username']) ?></h2>

    <table>
        <tr>
            <th>Student Name</th>
            <th>Username</th>
            <th>Programme</th>
            <th>Internship</th>
            <th>Actions</th>
        </tr>
        <?php if ($result->num_rows > 0): ?>
            <?php while($row = $result->fetch_assoc()): ?>
                <tr>
                    <td><?= htmlspecialchars($row['FirstName'] . " " . $row['LastName']) ?></td>
                    <td><?= htmlspecialchars($row['Username']) ?></td>
                    <td><?= htmlspecialchars($row['ProgrammeCode']) ?></td>
                    <td><?= htmlspecialchars($row['InternshipCode']) ?></td>
                    <td>
                        <a href="../StudentChangesPages/student_profile.php?id=<?= $row['StudentAccountID'] ?>">View Profile</a>
                    </td>
                </tr>
            <?php endwhile; ?>
        <?php else: ?>
            <tr><td colspan="5">No students assigned to this <?= strtolower($type) ?> yet.</td></tr>
        <?php endif; ?>
    </table>
</body>
</html>