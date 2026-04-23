
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
                    comp.CompanyName
             FROM studentaccountlist acc
             JOIN studentprofile prof ON acc.StudentAccountID = prof.StudentAccountID 
             LEFT JOIN internship intern ON acc.StudentAccountID = intern.StudentAccountID
             LEFT JOIN companynamelist comp ON intern.CompanyINT = comp.CompanyInt
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
    <style>
        body { font-family: sans-serif; line-height: 1.6; padding: 20px; }
        .form-section { border: 1px solid #ccc; padding: 15px; margin-bottom: 20px; border-radius: 5px; }
        .error { color: red; font-weight: bold; }
        label { display: inline-block; width: 150px; margin-bottom: 10px; }
    </style>
</head>
<body>

    <h2>View Student: <?php echo htmlspecialchars($studentData['FirstName'] . " " . $studentData['LastName']); ?></h2>
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


</body>
</html>