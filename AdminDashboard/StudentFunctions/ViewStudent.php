
<?php
ini_set('display_errors', 1);
ini_set('display_startup_errors', 1);
error_reporting(E_ALL);
session_start();

include '../../Connection.php';
include '../../ExecutePStatement.php';
include '../../AllFunctions.php';

checkAccess('Admin');

$studentID = $_GET['id'] ?? $_POST['id'] ?? null;

if (!$studentID) {
    header("Location: ../Databases/StudentDatabase.php");
    exit();
}

//Get data for prefill form
$fetchSql = "SELECT acc.Username, acc.Password, prof.*, 
                    intern.CompanyINT, intern.Role, intern.Months_duration, intern.Description,
                    comp.CompanyName,
                    ar_lect.Internship_Score AS LectScore,
                    ar_super.Internship_Score AS SuperScore
             FROM studentaccountlist acc
             JOIN studentprofile prof ON acc.StudentAccountID = prof.StudentAccountID 
             LEFT JOIN internship intern ON acc.StudentAccountID = intern.StudentAccountID
             LEFT JOIN companynamelist comp ON intern.CompanyINT = comp.CompanyInt
             LEFT JOIN assessmentrecords ar_lect ON acc.StudentAccountID = ar_lect.StudentID AND ar_lect.AssesorType = 'Lecturer'
             LEFT JOIN assessmentrecords ar_super ON acc.StudentAccountID = ar_super.StudentID AND ar_super.AssesorType = 'Supervisor'
             WHERE acc.StudentAccountID = ?";

$fetchResult = executePreparedStatement($fetchSql, [$studentID]);

if (!$fetchResult || $fetchResult->num_rows === 0) {
    header("Location: ../Databases/StudentDatabase.php");
    exit();
}
$studentData = $fetchResult->fetch_assoc();

$error = null;
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <title>View Student Record</title>

    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
    <link href="https://fonts.googleapis.com/css2?family=Bricolage+Grotesque:opsz,wght@12..96,200..800&display=swap" rel="stylesheet">

    <link rel="stylesheet" href="../../CssFiles/Add_Edit.css">
    <style>
        body{
            margin:40px auto;
        }
    </style>
</head>
<body>

    <h2 class="page-title"><?php echo htmlspecialchars($studentData['FirstName'] . " " . $studentData['LastName']); ?></h2>
    <a href="../Databases/StudentDatabase.php" class="subtitle">&larr; Back to Student Database</a>

    <div style="background-color: #f9f9f9; border: 1px solid #ddd; padding: 15px; border-radius: 5px; margin-bottom: 20px;">
        <h3>Login Information</h3>
        <p><strong>Username:</strong> <?php echo htmlspecialchars($studentData['Username']); ?></p>
        <p><strong>Password:</strong> <?php echo htmlspecialchars($studentData['Password']); ?></p>
    </div>

    <div style="background-color: #f9f9f9; border: 1px solid #ddd; padding: 15px; border-radius: 5px;">
        <h3>Personal Information</h3>
        <p><strong>Name:</strong> <?php echo htmlspecialchars($studentData['FirstName'] . " " . $studentData['LastName']); ?></p>
        <p><strong>Programme Code:</strong> <?php echo htmlspecialchars($studentData['ProgrammeCode']); ?></p>
        <p><strong>Year of Study:</strong> <?php echo htmlspecialchars($studentData['YearOfStudy']); ?></p>
    </div>

    <div style="background-color: #f9f9f9; border: 1px solid #ddd; padding: 15px; border-radius: 5px;">
        <h3>Internship Details</h3>
        <p><strong>Company:</strong> <?php echo htmlspecialchars($studentData['CompanyINT'] ?? 'N/A'); ?></p>
        <p><strong>Company:</strong> <?php echo htmlspecialchars($studentData['CompanyName'] ?? 'N/A'); ?></p>
        <p><strong>Role:</strong> <?php echo htmlspecialchars($studentData['Role'] ?? 'N/A'); ?></p>
        <p><strong>Duration (months):</strong> <?php echo htmlspecialchars($studentData['Months_duration'] ?? 'N/A'); ?></p>
        <p><strong>Description:</strong> <?php echo nl2br(htmlspecialchars($studentData['Description'] ?? 'N/A')); ?></p>
    </div>

    <div  style="background-color: #f9f9f9; border: 1px solid #ddd; padding: 15px; border-radius: 5px;">
        <h3>Assessment Scores</h3>
        <p><strong>Lecturer Score:</strong> 
            <?php 
                if (isset($studentData['LectScore'])) {
                    echo "<b>" . $studentData['LectScore'] . " / 100</b>";
                } else {
                    echo "<span>Not Graded Yet</span>";
                }
            ?>
        </p>
        <p><strong>Supervisor Score:</strong> 
            <?php 
                if (isset($studentData['SuperScore'])) {
                    echo "<b>" . $studentData['SuperScore'] . " / 100</b>";
                } else {
                    echo "<span>Not Graded Yet</span>";
                }
            ?>
        </p>
    </div>


</body>
</html>