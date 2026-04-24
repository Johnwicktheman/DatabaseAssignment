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
    <title>Add New Assessor</title>

    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
    <link href="https://fonts.googleapis.com/css2?family=Bricolage+Grotesque:opsz,wght@12..96,200..800&display=swap" rel="stylesheet">
    
    <link rel="stylesheet" href="../../CssFiles/Add_Edit.css">
</head>
<body>

<div class="container">

    <h1 class="page-title">Add New Assessor</h1>

    <p class="subtitle">Create a new assessor account.</p>

    <?php 
        if ($error != null){
            echo "<div class='error'>$error</div>";
        }
    ?>

    <div class="form-card">

        <form action="" method="post">

            <h2 class="section-title">Assessor Information</h2>

            <div class="form-grid">

                <div class="form-group">
                    <label for="username">Username</label>
                    <input type="text" id="username" name="username" required>
                </div>

                <div class="form-group">
                    <label for="password">Password</label>
                    <input type="text" id="password" name="password" required>
                </div>

                <div class="form-group">
                    <label for="type">Assessor Type</label>

                    <select id="type" name="type" required>
                        <option value="" disabled selected>-- Select Type --</option>
                        <option value="Lecturer">Lecturer</option>
                        <option value="Supervisor">Supervisor</option>
                    </select>
                </div>

            </div>

            <div class="button-group">
                <button type="submit" class="btn btn-primary">Add Assessor</button>
                <a href="../Databases/AssessorDatabase.php" class="btn btn-secondary">Cancel</a>
            </div>

        </form>

    </div>

</div>

</body>
</html>