<?php
ini_set('display_errors', 1);
ini_set('display_startup_errors', 1);
error_reporting(E_ALL);
session_start();
include '../Connection.php';
include '../ExecutePStatement.php';

//see if they are loggged in and if they are admin or not
if (!isset($_SESSION['username']) || $_SESSION['user_role'] !== 'admin') {
    header("Location: ../FrontPage.php"); 
    exit();
}

$studentID = $_GET['id'] ?? $_POST['id'] ?? null;

if (!$studentID) {
    header("Location: ../Databases/StudentDatabase.php");
    exit();
}



$fetchSql = "SELECT studentaccountlist.Username, studentaccountlist.Password, studentprofile.* FROM studentaccountlist 
             JOIN studentprofile ON studentaccountlist.StudentAccountID = studentprofile.StudentAccountID 
             WHERE studentaccountlist.StudentAccountID = ?";

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
    <title>Document</title>
</head>
<body>
    <h2>Confirm Deletion</h2>
    <p>Are you sure you want to delete student: <?php echo $fName . " " . $lName; ?></strong>?</p>
    <p>Username: <?php echo $uName; ?></p>

    <form action="" method="post">
        <input type="hidden" name="id" value="<?php echo $studentID; ?>">
        <input type="submit" value="Yes, Delete Student">
        <a href="../Databases/StudentDatabase.php">No, Cancel</a>
    </form>
</body>
</html>
</body>
</html>