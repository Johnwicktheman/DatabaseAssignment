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


    $CompanyName = $_POST['CompanyName'];


    //check company table
    $resCompanyNAme = executePreparedStatement("SELECT CompanyName FROM companynamelist WHERE CompanyName = ? ", [$CompanyName]);
   

    // Check if any of them found a match
    if ($resCompanyNAme->num_rows > 0) {
        $error = "Company Name is already taken.";
    } 

    if ($error) {
        //If there is error show error msg below
    } else {
        //all good
        $adminID = $_SESSION['user_id'];
        $insertSql = "INSERT INTO companynamelist (CompanyName) VALUES (?)";
        $insertRes = executePreparedStatement($insertSql, [$CompanyName]);

        if ($insertRes) {
            header("Location: ../Databases/CompanyDatabase.php");
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
    
    <p>Add New Company</p>
    <form action="" method="post">
        
        <label for="CompanyName">Company Name:</label>
        <input type="text" id="CompanyName" name="CompanyName" required><br><br>

        <br><br>

        <input type="submit" value="Add Company">
        <a href="../Databases/CompanyDatabase.php">Cancel</a>
    </form>
</body>
</html>