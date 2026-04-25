
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

//Get data to show
$fetchSql = "SELECT acc.Username, acc.Password, prof.*, 
                    intern.CompanyINT, intern.Role, intern.Months_duration, intern.Description,
                    comp.CompanyName,
                    ar_lect.Internship_Score AS LectScore,
                    ar_super.Internship_Score AS SuperScore,
                    aa_lect.Username AS LectName,
                    aa_super.Username AS SuperName,
                    aa_lect.AssessorAccountID AS LectID,
                    aa_super.AssessorAccountID AS SuperID
             FROM studentaccountlist acc
             JOIN studentprofile prof ON acc.StudentAccountID = prof.StudentAccountID 
             LEFT JOIN internship intern ON acc.StudentAccountID = intern.StudentAccountID
             LEFT JOIN companynamelist comp ON intern.CompanyINT = comp.CompanyInt
             LEFT JOIN assessmentrecords ar_lect ON acc.StudentAccountID = ar_lect.StudentID AND ar_lect.AssesorType = 'Lecturer'
             LEFT JOIN assessmentrecords ar_super ON acc.StudentAccountID = ar_super.StudentID AND ar_super.AssesorType = 'Supervisor'
             LEFT JOIN assesoraccountlist aa_lect ON aa_lect.AssessorAccountID = prof.AssesorAccountIDLect
             LEFT JOIN assesoraccountlist aa_super ON aa_super.AssessorAccountID = prof.AssesorAccountIDSuper
             WHERE prof.StudentProfileID = ?";

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
            margin:20px 200px;
            margin: 40px auto;
            max-width: 1000px;
            padding: 0 20px;
        }

        .grid{
        
        }
        .container-group{
            display:flex;
            justify-content:flex-start;
            gap:30px;
            align-items:center;
        }

        .container{
            background-color: #f9f9f9; 
            border: 1px solid #ddd; 
            padding: 30px; 
            border-radius: 5px; 
            margin-bottom: 20px;
            margin-left:0px;
            width: 100%;
            height:auto;
            border-radius:20px;
        }

        .container-group .container{
            flex: 1;
            margin-left:0;
            min-width:0;
        }

        header{
            color: #0f4f4f;
            font-size: 21px;
            font-weight:bold;
            margin-bottom:10px;
            border-bottom: 2px solid #f0f2f5;
            padding-bottom:10px
        }

        .container p{
            margin-bottom:4px;
        }

        .group-small-container{
            display:flex;
            justify-content:center;
            gap:10px;
            align-items:center;
        }

        .small-container{
            background-color: #f7f7f7; 
            border: 1px solid #ddd; 
            padding: 20px; 
            border-radius: 5px; 
            margin-left:0px;
            width: 100%;
            height:auto;
            border-radius:10px;
            text-align: center;
            margin-bottom:15px;
        }

        .small-container header{
            font-size: 15px;
            border-bottom: 2px solid #ebebeb;
            margin-bottom: 0px;
        }

        .small-container p{
            font-size:20px;
            font-weight:bold;
        }

        .description{
            background: #ebebeb;
            padding: 15px;
            border-radius: 8px;
            border-left: 4px solid #0f4f4f;
            color: #555;
        }

    </style>
</head>
<body>

    <a href="../Databases/StudentDatabase.php" class="back-link">&larr; Back to Student Database</a>
    <h2 class="page-title"><?php echo $studentData['FirstName'] . " " . $studentData['LastName']; ?></h2>

    <div class="grid">
        <div class="container-group">
            <div class="container">
                <header>Login Information</header>
                <p><strong>Username:</strong> <?php echo $studentData['Username']; ?></p>
                <p><strong>Password:</strong> <?php echo $studentData['Password']; ?></p>
            </div>

            <div class="container">
                <header>Personal Information</header>
                <p><strong>Programme Code:</strong> <?php echo $studentData['ProgrammeCode']; ?></p>
                <p><strong>Year of Study:</strong> <?php echo $studentData['YearOfStudy']; ?></p>
            </div>
        </div>

        <div class="container">
            <header>Internship Details</header>
            <div class="group-small-container">
                <div class="small-container">
                    <header>Company:</header> <br>
                    <p><?php echo $studentData['CompanyName'] ?? 'N/A'; ?></p>
                </div>
                <div class="small-container">
                    <header>Role:</header> <br>
                    <p><?php echo $studentData['Role'] ?? 'N/A'; ?></p>
                </div>
                <div class="small-container">
                    <header>Duration (months):</header> <br>
                    <p><?php echo $studentData['Months_duration'] ?? 'N/A'; ?></p>
                </div>
            </div>
                <p><strong>Description:</strong> <br>
                <p class="description"><?php echo $studentData['Description'] ?? 'N/A'; ?></p>
        </div>

        <div  class="container">
            <header>Assessment Scores</header>

            <div class="group-small-container">
                <div class="small-container">
                    <header>Lecturer Score <br>
                        <span style=" color: grey ">by <?php echo $studentData['LectName']. " ID: " . $studentData['LectID'] ?? 'N/A'; ?></span>
                    </header> 
                    <br>
                    <p>
                        <?php 
                            if (isset($studentData['LectScore'])) {
                                echo "<b>" . $studentData['LectScore'] . " / 100</b>";
                            } else {
                                echo "<span>Not Graded Yet</span>";
                            }
                        ?>
                    </p>
                </div>
                <div class="small-container">
                    <header>Supervisor Score <br>
                        <span style=" color: grey ">by <?php echo $studentData['SuperName']. " ID: " . $studentData['SuperID'] ?? 'N/A'; ?></span>
                    </header> <br>
                    <p>
                        <?php 
                            if (isset($studentData['SuperScore'])) {
                                echo "<b>" . $studentData['SuperScore'] . " / 100</b>";
                            } else {
                                echo "<span>Not Graded Yet</span>";
                            }
                        ?>
                    </p>
                </div>
            </div>
        </div>
    </div>


</body>
</html>