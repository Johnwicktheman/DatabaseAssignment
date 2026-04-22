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


//error text
$error = null;
//After they press submit button what happens
if ($_SERVER["REQUEST_METHOD"] == "POST") {


    $user = $_POST['username'];
    $pass = $_POST['password'];
    $type = $_POST['type'];


    //check assessor table
    $resAssessor = executePreparedStatement("SELECT Username FROM assesoraccountlist WHERE Username = ? ", [$user]);
    $resStudent = executePreparedStatement("SELECT Username FROM studentaccountlist WHERE Username = ?", [$user]);
    $resAdmin = executePreparedStatement("SELECT Username FROM adminaccountlist WHERE Username = ?", [$user]);

    // Check if any of them found a match
    if ($resAssessor->num_rows > 0) {
        $error = "Username is already taken by another Assessor.";
    } else if ($resStudent->num_rows > 0) {
        $error = "Username is already taken by a Student.";
    } else if ($resAdmin->num_rows > 0) {
        $error = "Username is already taken by an Admin.";
    }

    if ($error) {
        //If there is error show error msg below
    } else {
        //all good
        $adminID = $_SESSION['user_id'];
        $insertSql = "INSERT INTO assesoraccountlist (Username, Password, AssesorType, AdminAccountID) VALUES (?, ?, ?, ?)";
        $insertRes = executePreparedStatement($insertSql, [$user, $pass, $type, $adminID]);

        if ($insertRes) {
            header("Location: ../Databases/AssessorDatabase.php");
            exit();
        }
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
    <?php 
        if ($error !=null){
            echo $error;
        }
    ?>
    
    <p>Add New Assessor</p>
    <form action="" method="post">
        
        <label for="username">Username:</label>
        <input type="text" id="username" name="username" required><br><br>

        <label for="password">Password:</label>
        <input type="text" id="password" name="password" required><br><br>

        <select id="type" name="type" required>
            <option value="" disabled selected>-- Select Type --</option>
            <option value="Lecturer">Lecturer</option>
            <option value="Supervisor">Supervisor</option>
        </select><br><br>


        <br><br>

        <input type="submit" value="Add Assessor">
        <a href="../Databases/AssessorDatabase.php">Cancel</a>
    </form>
</body>
</html>