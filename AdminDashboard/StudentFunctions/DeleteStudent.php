<?php
ini_set('display_errors', 1);
ini_set('display_startup_errors', 1);
error_reporting(E_ALL);
session_start();
include '../../Connection.php';
include '../../ExecutePStatement.php';
include '../../AllFunctions.php';

//see if they are loggged in and if they are admin or not
checkAccess('Admin');


$studentID = $_GET['id'] ?? $_POST['id'] ?? null;

if (!$studentID) {
    header("Location: ../Databases/StudentDatabase.php");
    exit();
}



$fetchSql = "SELECT studentaccountlist.Username, studentaccountlist.Password, studentprofile.* FROM studentaccountlist 
             JOIN studentprofile ON studentaccountlist.StudentAccountID = studentprofile.StudentAccountID 
             WHERE studentprofile.StudentProfileID = ?";

$fetchResult = executePreparedStatement($fetchSql, [$studentID]);
if ($fetchResult->num_rows > 0) {
    $row = $fetchResult->fetch_assoc();
    $fName = $row['FirstName'];
    $lName = $row['LastName'];
    $uName = $row['Username'];
}
//After they press submit button what happens
if ($_SERVER["REQUEST_METHOD"] == "POST") {
    $id   = $_POST['id'];


    //Delete the Student Profile first 
    $deleteProfileSql = "DELETE FROM studentprofile WHERE StudentAccountID = ?";
    executePreparedStatement($deleteProfileSql, [$id]);

    // Delete Student Account 
    $deleteAccountSql = "DELETE FROM studentaccountlist WHERE StudentAccountID = ?";
    $deleteRes = executePreparedStatement($deleteAccountSql, [$id]);

    if ($deleteRes) {
        // Redirect back to the database list
        header("Location: ../Databases/StudentDatabase.php");
        exit();
    } else {
        $error = "Failed to delete student account.";
    }
}



?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Delete student</title>

    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
    <link href="https://fonts.googleapis.com/css2?family=Bricolage+Grotesque:opsz,wght@12..96,200..800&display=swap" rel="stylesheet">

    <link rel="stylesheet" href="../../CssFiles/Add_Edit.css">
    
</head>
<body>
    <div class="container">
        <h1 class="page-title">Delete Student</h1>
        <p class="subtitle">Review the details below before removal.</p>

        <div class="form-card">
            <h2 class="section-title">Confirmation Required</h2>
            <div class="form-grid">
                    <div class="form-group full-width">
                        <label>Are you sure you want to delete student:</label>
                        <div class="detail-value"><?php echo $fName . " " . $lName; ?></div>
                    </div>

                    <div class="form-group full-width">
                        <label>Username: </label>
                        <div class="detail-value"><?php echo $uName; ?></div>
                    </div>

                </div>

                <div class="form-group full-width">
                    <form action="" method="post">
                        <div class="button-group">
                            <input type="hidden" name="id" value="<?php echo $studentID; ?>">
                            <input type="submit" value="Delete" class="btn btn-secondary" style="background-color:#ff4d4d;">
                            <a href="../Databases/StudentDatabase.php" class="btn btn-secondary">Cancel</a>
                        </div>
                    </form>
                </div>
            </div>
    </div>
</body>
</html>
</body>
</html>