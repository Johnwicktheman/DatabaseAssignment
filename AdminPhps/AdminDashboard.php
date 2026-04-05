<?php
include '../session.php';
include '../connection.php';

if (!isLoggedIn() || getCurrentUser()['role'] !== 'admin') {
    header("Location: ../login.php");
    exit;
}

?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Document</title>
    <link rel="stylesheet" href="../CssFiles/DashBoardAssessor.css">
</head>
<body>
    <div class="HeaderBar">
  
        <div class="HeaderImage">
            <img src="../Assets/UniLogoBlack.png" style="width:200px; height: auto; margin: 20px 40px;">
        </div>
        
        <div class="HeaderTitle">
            <p>Dashboard</p>
        </div>

        <div class="HeaderTitle">
            
            <label for="navToggle" class="navToggleLabel"><img src="../Assets/ThreeDash.png" style="width: 50px; height: auto; margin: 20px 40px;"></label>
        </div>
        
    </div>
    <input type="checkbox" id="navToggle" class="navToggle">
    <label for="navToggle" class="overlay"></label>
    <div class="sidebar">
        <ul>
            <li><a href="AdminDashboard.php" class="SideBarContent">DashBoard</a></li>
            <li><a href="../logout.php" class="SideBarContent">Log Out</a></li>
        </ul>
    </div>

    <div class="MainContent">

        <div class="WelcomeText">
            <p>
                <?php
                $username = $_SESSION['username'];
                echo "Welcome, " . $username;
                ?>
            </p>
        </div>
        <div class="Options">
            <div class="OptionsCard">
                <div>
                    <img class="OptionIcon" src="../Assets/Bell.png">
                </div>
                <div>
                    <p class="OptionTitle">Student Database</p>
                </div>
                <div>
                    <div class="OptionButton">
                        <a href="StudentDB.php">
                            <button class="ContinueButton">Enter</button>
                        </a>
                    </div>
                </div>
            </div>
            <div class="OptionsCard" style="gap: 40px;">
                <div>
                    <img class="OptionIcon" src="../Assets/Bell.png">
                </div>
                <div >
                    <p class="OptionTitle">Assesor DataBase</p>
                </div>
                <div>
                    <div class="OptionButton">
                        <a href="AssessorDB.php">
                            <button class="ContinueButton">Enter</button>
                        </a>
                    </div>
                </div>
            </div>
            <div class="OptionsCard" style="gap: 40px;">
                <div>
                    <img class="OptionIcon" src="../Assets/Bell.png">
                </div>
                <div >
                    <p class="OptionTitle">Internship DataBase</p>
                </div>
                <div>
                    <div class="OptionButton">
                        <a href="InternshipDB.php">
                            <button class="ContinueButton">Enter</button>
                        </a>
                    </div>
                </div>
            </div>
            
        </div>
       

        
    </div>
</body>
    

</html>