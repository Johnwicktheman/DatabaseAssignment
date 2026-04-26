<?php
// ini_set('display_errors', 1);
// ini_set('display_startup_errors', 1);
// error_reporting(E_ALL);
session_start();
include 'connection.php';
include 'ExecutePStatement.php';


$error = "";
//Because we have three tables need to check so need three if statements
if ($_SERVER["REQUEST_METHOD"] == "POST") {
    $username = $_POST['username'];
    $password = $_POST['password'];

    //Check Admin
    $sqlAdmin = "SELECT AdminAccount_id as id, Username FROM adminaccountlist WHERE Username = ? AND Password = ?";
    $resAdmin = executePreparedStatement($sqlAdmin, [$username, $password]);

    if ($resAdmin->num_rows > 0) {
        $row = $resAdmin->fetch_assoc();
        
        //Use the php Session function to store info
        $_SESSION['user_id']   = $row['id'];
        $_SESSION['username']  = $row['Username'];
        $_SESSION['user_role'] = 'Admin';
        
        header("Location: AdminDashboard.php");
        exit;
    }
    //Check Assessor
    $sqlAssessor = "SELECT AssessorAccountID, Username, AssesorType FROM assesoraccountlist WHERE Username = ? AND Password = ?";
    $resAssessor = executePreparedStatement($sqlAssessor, [$username, $password]);

    if ($resAssessor->num_rows > 0) {
        $row = $resAssessor->fetch_assoc();
        
        $_SESSION['user_id']   = $row['AssessorAccountID'];
        $_SESSION['username']  = $row['Username'];
        $_SESSION['user_role'] = $row['AssesorType']; // 'Lecturer' or 'Supervisor'
        
        header("Location: AssessorDashboard.php");
        exit;
    }

    //Check Student
    $sqlStudent = "SELECT StudentAccountID, Username FROM studentaccountlist WHERE Username = ? AND Password = ?";
    $resStudent = executePreparedStatement($sqlStudent, [$username, $password]);

    if ($resStudent->num_rows > 0) {
        $row = $resStudent->fetch_assoc();
        
        $_SESSION['user_id']   = $row['StudentAccountID'];
        $_SESSION['username']  = $row['Username'];
        $_SESSION['user_role'] = 'Student';
        
        header("Location: StudentDashboard.php");
        exit;
    }

    //If nothing matched
    $error = "Invalid username or password.";
}
   
?>

<!-- http://localhost/TET/FrontPage.php -->

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Document</title>
    <link rel="stylesheet" href="CssFiles/FrontPage.css">
</head>
<body>
     <div class="HeaderBar">
  
        <div class="HeaderImage">
            <img src="Assets/UniLogo.png" style="width:200px; height: auto; margin: 10px 40px;">
        </div>
        
        <div class="HeaderTitle">
            <p>Login Page</p>
        </div>
        
    </div>
    <div class="login-container">
        
        <div class="login-header">

            <img src="Assets/NottLogo2.png" class="login-logo">
            <h1> Internship Assessment System</h1> 
            <p> Please login to continue</p>

        </div>
        <form action="FrontPage.php" method ="post">
            <div class="login-form">

                <label for="Username"> Username </label>
                <input type="text"  id="username" name="username"  placeholder="Enter Username... " required>
                <label for="Password"> Password </label>
                <input type="password" id="password" name="password" placeholder="Enter Password... " required> <br>
                <button type="submit" class="btn-login"> Login </button>

            </div>
            <?php if ($error): ?>
                <p class="error-msg" id="errorMsg"><?php echo $error; ?></p>
            <?php endif; ?>

        </form>
    </div>    
</body>
</html>