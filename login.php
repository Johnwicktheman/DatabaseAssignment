<?php
include 'session.php';
include 'connection.php';
include 'ExecutePStatements.php';

/*if (isLoggedIn()) {
    $user = getCurrentUser();
    // Redirect to correct dashboard if already logged in
    if ($user['role'] == 'admin') {
        header("Location: AdminPhps/AdminDashboard.php");
    } elseif ($user['role'] == 'lecturer') {
        header("Location: AdminPhps/LecturerDashboard.php");
    } elseif ($user['role'] == 'supervisor') {
        header("Location: AdminPhps/SupervisorDashboard.php");
    } elseif ($user['role'] == 'student') {
        header("Location: AdminPhps/StudentDashboard.php");
    }
    exit;
}*/

$error = "";


//Detect admin in admin table
if ($_SERVER["REQUEST_METHOD"] == "POST") {
    $username = $_POST['username'];
    $password = $_POST['password'];

    // ── Check Admin ──
    $sqlAdmin = "SELECT AdminAccount_id as id, Username FROM adminaccountlist WHERE Username = ? AND Password = ?";
    $resAdmin = executePreparedStatement($sqlAdmin, [$username, $password]);

    if ($resAdmin->num_rows > 0) {
        $row = $resAdmin->fetch_assoc();
        setUserSession($row['id'], $row['Username'], 'admin');
        header("Location: AdminPhps/AdminDashboard.php");
        exit;
    }

    // ── Check Assessor (Lecturer or Supervisor) ──
    $sqlAssessor = "SELECT AssessorAccountID, Username, AssesorType FROM assesoraccountlist WHERE Username = ? AND Password = ?";
    $resAssessor = executePreparedStatement($sqlAssessor, [$username, $password]);

    if ($resAssessor->num_rows > 0) {
        $row = $resAssessor->fetch_assoc();
        setUserSession($row['AssessorAccountID'], $row['Username'], strtolower($row['AssesorType']));
        header("Location: AdminPhps/AdminDashboard.php");
        exit;
    }

    // ── Check Student ──
    $sqlStudent = "SELECT StudentAccountID, Username FROM studentaccountlist WHERE Username = ? AND Password = ?";
    $resStudent = executePreparedStatement($sqlStudent, [$username, $password]);

    if ($resStudent->num_rows > 0) {
        $row = $resStudent->fetch_assoc();
        setUserSession($row['StudentAccountID'], $row['Username'], 'student');
        header("Location: AdminPhps/AdminDashboard.php");
        exit;
    }

    // If nothing matched
    $error = "Invalid username or password.";
}
   
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Document</title>
    <link rel="stylesheet" href="CssFiles/FirstPage.css">
</head>
<body>
    <div class="HeaderBar">
  
        <div class="HeaderImage">
            <img src="Assets/UniLogo.png" style="width:200px; height: auto; margin: 20px 40px;">
        </div>
        
        <div class="HeaderTitle">
            <p>Home Page</p>
        </div>
        
    </div>

    <div class="MainContent">

        <div class="InternText">
            <p style="word-break: normal;">
                Internship <br>Management
            </p>
        </div>

        <div class="InputBox">
            <div class="LogInText">
                <p style="font-size: 50px;">
                <strong>Log in your account</strong>
            </div>
            <div class="InputFormBox">
                <form class="InputForm" action="login.php" method="POST">
                    <input class="Inputs"  name="username" type="text" placeholder="Enter Username... " required>
                    <input class="Inputs"  name="password" type="password" placeholder="Enter Password..." required>
                    <div>
                        <input class="InputRemember"type="checkbox" id="remember">
                        <label class="LabelRemember"for="remember">Remember me</label>
                    </div>
                    
                    <button class="ContinueButton" type="submit" >Continue</button>
                    
                </form>
            </div>
            <?php if ($error): ?>
                <p style="color: red; text-align: center; font-weight: bold;"><?php echo $error; ?></p>
            <?php endif; ?>
           
        </div>

    </div>
</body>
    

</html>
