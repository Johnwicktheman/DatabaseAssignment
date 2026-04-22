<?php
ini_set('display_errors', 1);
ini_set('display_startup_errors', 1);
error_reporting(E_ALL);
session_start();
include '../Connection.php';
include '../ExecutePStatement.php';
include '../AllFunctions.php';
checkAccess(['Lecturer', 'Supervisor']);
//Get current Role and ID currenlty confirmed to be lecturer or supervisor
$assessorID = $_SESSION['user_id'];
$assessorType = $_SESSION['user_role'];

//Check role
if ($assessorType === 'Lecturer') {
    $assessorIDField = 'AssesorAccountIDLect';

} else {
    $assessorIDField = 'AssesorAccountIDSuper';
}

//we want to show students assgined to lecturer or supervisor and show they got record or not
$studentList = "SELECT sp.StudentAccountID, sp.FirstName, sp.LastName, ar.AssessmentCode ,ar.Internship_Score
                FROM studentprofile sp
                LEFT JOIN assessmentrecords ar ON sp.StudentAccountID = ar.StudentID AND ar.AssesorType = ?
                WHERE sp." . $assessorIDField . " = ?";

$studentResult = executePreparedStatement($studentList, [$assessorType, $assessorID]);

$targetStudentID = isset($_GET['id']) ? $_GET['id'] : null;

$currentData = null;
$student = null;

if ($targetStudentID) {
    // Only fetch this if an ID is present in the URL
    $sqlCurrent = "SELECT * FROM assessmentrecords WHERE StudentID = ? AND AssesorType = ?";
    $resCurrent = executePreparedStatement($sqlCurrent, [$targetStudentID, $assessorType]);
    $currentData = $resCurrent->fetch_assoc();

    if (!$currentData) {
        header("Location: StudentDatabaseAss.php");
        exit();
    }

    $sqlStudent = "SELECT FirstName, LastName FROM studentprofile WHERE StudentAccountID = ?";
    $ResultStudent = executePreparedStatement($sqlStudent, [$targetStudentID]);
    $student = $ResultStudent->fetch_assoc();
}


//after submit what happens
if ($_SERVER["REQUEST_METHOD"] == "POST") {
    $feedback = $_POST['feedback'];
    $u_project = $_POST['u_project'];
    $h_safety = $_POST['h_safety'];
    $connectivity = $_POST['connectivity'];
    $presentation = $_POST['presentation'];
    $clarity = $_POST['clarity'];
    $activities = $_POST['activities'];
    $p_manage = $_POST['p_manage'];
    $t_manage = $_POST['t_manage'];
    
    //find total score
    $totalScore = $u_project + $h_safety + $connectivity + $presentation + $clarity + $activities + $p_manage + $t_manage;
    $totalScore = (int)round($totalScore);

    $sqlInsert = "UPDATE assessmentrecords 
        SET Feedback = ?, understand_project = ?, health_and_safety = ?, connectivity = ?, presentation = ?, clarity = ?, activities = ?, project_management = ?, time_management = ?, Internship_Score = ?
        WHERE StudentID = ? AND AssesorType = ?";
    
    $params = [
        $feedback, $u_project, $h_safety, $connectivity, $presentation, $clarity, $activities, $p_manage, $t_manage, $totalScore,
        $targetStudentID, $assessorType,
    ];

    executePreparedStatement($sqlInsert, $params);
    
    echo "<script>alert('Record Updated Successfully!'); window.location.href='StudentDatabaseAss.php';</script>";
}
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">

    <link rel="stylesheet" href="../CssFiles/AssessorDashBoard.css">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.5.1/css/all.min.css"> 
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <style>        
        .main {
            margin-left: 250px; /* Offset for the sidebar */
        }

        nav a{
            margin-bottom:20px;
        }
        
        #title{
            color: #aaa9a9;
            font-size:30px;
            padding-bottom:8px;
        }

        .main hr {
            border: 0;
            border-top: 1px solid #aaa9a9;
        }

        header {
            font-size: 50px;
            color: #154c4b;
        }


        .back-link {
            display: inline-block;
            margin-bottom: 20px;
            color: #154c4b;
            text-decoration: none;
            font-weight: bold;
            transition: color 0.3s;
        }

        .back-link:hover {
            color: #219e75;
            cursor:pointer;
        }

        table {
            width: 100%;
            border-collapse: collapse;
            margin-top: 20px;
            background-color: white;
            border-radius: 15px;
            overflow: hidden; /* Ensures border-radius works on table */
            box-shadow: 0 4px 15px rgba(0,0,0,0.05);
        }

        th, td {
            text-align: left;
            padding: 15px 20px;
            border-bottom: 1px solid #eee;
        }

        th {
            background-color: #f8f9fa;
            color: #154c4b;
            font-size: 14px;
            letter-spacing: 1px;
            font-weight: 900;
        }
        
        .action-links a {
            text-decoration: none;
            color: #154c4b;
            font-weight: bold;
            margin-right: 15px;
            transition: color 0.3s;
        }

        tr a{
            text-decoration: none;
            color: #154c4b;
            font-weight: bold;
            margin-right: 15px;
            transition: color 0.3s;
        }

        tr a:hover {
            color: #219e75;
        }

        tr i {
            margin-right: 5px;
        }

        #modal{
            opacity: 0;
            position: fixed;
            right:0;
            left:0;
            bottom: 60px;

            transition: all 0.3s ease-in-out;
            z-index: -1;

            display:flex;
            align-items: center;
            justify-content: center;
        }

        #modal.open{
            opacity:1;
            z-index:999;
        }

        #modal-inner{
            background-color: #FFFFFF;
            width: 700px;
            height: 650px;
            border-radius:20px;
            padding: 15px 25px;
            text-align: center;
            box-shadow: 15px 25px 30px rgba(0,0,0,0.2);
        }

        #modal-inner h1{
            color: #154c4b;
        }

        form {
            display: flex;
            justify-content:center;
            text-align:center;
        }

        form h1{
            color: #aaa9a9;
            font-size:20px;
        }

        .form-collection1{
            display:inline;
            margin-right:100px;

        }

        form label{
            font-weight: bold;
            color: #555;
        }

        /* Container for slider and value readout */
        .slider-container {
            align-items: center;
            gap: 15px;
            max-width: 250px;
        }

        /* The actual slider track */
        .slider {
            width: 100%;
            height: 8px;
            border-radius: 5px;
            background: #aaa9a9;
            outline: none;
            transition: 1s;
        }

        /* The slider handle (Thumb) - Chrome, Safari, Edge */
        .slider::-webkit-slider-thumb {
            -webkit-appearance: none;
            appearance: none;
            width: 20px;
            height: 20px;
            border-radius: 50%;
            background: #154c4b; /* Your primary teal */
            cursor: pointer;
            border: 2px solid white;
            box-shadow: 0px 2px 5px rgba(0,0,0,0.2);
            transition: 0.2s;
        }

        .slider:hover::-webkit-slider-thumb {
            background: #219e75; /* Your hover teal */
            transform: scale(1.1);
        }

        /* The slider handle (Thumb) - Firefox */
        .slider::-moz-range-thumb {
            -webkit-appearance: none;
            appearance: none;
            width: 20px;
            height: 20px;
            border-radius: 50%;
            background: #154c4b; /* Your primary teal */
            cursor: pointer;
            border: 2px solid white;
            box-shadow: 0px 2px 5px rgba(0,0,0,0.2);
            transition: 0.2s;
        }

        .slider:hover::-moz-range-thumb {
            background: #219e75; /* Your hover teal */
            transform: scale(1.1);
        }

        /* Style for the number display next to slider */
        output {
            font-weight: bold;
            color: #154c4b;
            min-width: 20px;
        }

        .form-collection2 textarea {
            width: 100%;
            height:300px;
            padding: 15px;
            border: 1px solid #ccc;
            border-radius: 12px;
            font-family: inherit;
            box-sizing: border-box;
            resize: vertical;
        }

        .button-container {
            margin-top: 30px;
            display: flex;
            align-items: center;
            gap: 20px;
        }

        .submit-btn {
            background-color: #154c4b;
            color: white;
            border: none;
            padding: 12px 25px;
            font-size: 16px;
            font-weight: bold;
            border-radius: 25px;
            cursor: pointer;
            transition: 0.3s;
        }

        .submit-btn:hover {
            background-color: #219e75;
        }

        .btn-cancel {
            text-decoration: none;
            color: #721c24;
            font-weight: bold;
            font-size: 16px;
        }

        .btn-cancel:hover {
            text-decoration: underline;
        }


    </style>
    <title>Document</title>
</head>
<body>
    <nav>
        <p>ASSESSOR PANEL</p>
        <hr>
        <a onclick="window.location.href='../AssessorDashboard.php'">Dashboard</a>
        <a href="#">Assessments</a>
        <a href="#">Students</a>
        <a href="#">Settings</a>
    </nav>

    <div class="main">
        <div id="title">Student Records</div>
        <hr>
        <header>Manage Student Records</header>

        <a onclick="window.location.href='../AssessorDashboard.php'" class="back-link">&larr; Back to Dashboard</a>

        <table>
            <tr>
            <th>Student ID</th>
            <th>Name</th>
            <th>Assessment Record</th>
            <th>Internship Score</th>
            <th>Action</th>
            </tr>
                <?php
                    while ($row = $studentResult->fetch_assoc()) {
                        $id = $row['StudentAccountID'];
                        $FirstName = $row['FirstName'];
                        $LastName = $row['LastName'];
                        $AssessmentCodeID = $row['AssessmentCode'];// Check if assessment record exists
                        $InternshipScore = $row['Internship_Score']; // Check if Internship Score exists

                        echo "<tr>";
                        echo "<td>" . $id . "</td>";
                        echo "<td>" . $FirstName . " " . $LastName . "</td>";
                        if ($AssessmentCodeID) {
                            echo "<td><b style='color: green;'>Record Exists</b></td>";
                        } else {
                            echo "<td><b style='color: gray;'>No Records</b></td>";
                        }
                        if ($InternshipScore !== null) {
                            if($InternshipScore >= 60){
                            echo "<td><b style='color: teal;'>Score: " . $InternshipScore . "</b></td>";
                            }
                            else if ($InternshipScore >= 40){
                            echo "<td><b style='color: orange;'>Score: " . $InternshipScore . "</b></td>";
                            }
                            else{
                            echo "<td><b style='color: red;'>Score: " . $InternshipScore . "</b></td>";

                            }
                        } else {
                            echo "<td><b style='color: gray;'>No Score</b></td>";
                        }

                        echo "<td>
                            <a href='CreateRecordStudent.php?id=" . $id . "' ><i class='fas fa-plus-circle'></i>Create Record</a> 
                            <a href='?id=" . $id . "' class='open-btn'><i class='fas fa-edit'></i>Update</a>
                        </td>";
                        echo "</tr>";
                    }
                ?>
    </table>

    <div id="modal">
        <div id="modal-inner">
        <h1>Update Assessment for: <?php echo $student['FirstName'] . " " . $student['LastName']; ?></h1>
    <p>Role: <strong><?php echo $assessorType; ?></strong></p>
    <hr>
        <form method="POST">            
            <div class="form-collection1">
                <h1>Grades (Scale 0-10)</h1>
                <div class="form-group">
                    <label>Understanding of Project:</label><br>
                    <div class = "slider-container">
                        <input type="range" name="u_project" class="slider" min="0" max="10" oninput="this.nextElementSibling.value = this.value" value="<?php echo $currentData ? $currentData['understand_project'] : '0'; ?>" required>
                        <output><?php echo $currentData['understand_project'] ?></output>
                    </div>
                </div>

                <div class="form-group">
                    <label>Health and Safety:</label><br>
                    <div class = "slider-container">
                        <input type="range" name="h_safety" class="slider" min="0" max="10" oninput="this.nextElementSibling.value = this.value"  value="<?php echo $currentData ? $currentData['health_and_safety'] : '0'; ?>" required>
                        <output><?php echo $currentData['health_and_safety'] ?></output>
                    </div>
                </div>

                <div class="form-group">
                    <label>Connectivity:</label><br>
                    <div class = "slider-container">
                        <input type="range" name="connectivity" class="slider" min="0" max="10" oninput="this.nextElementSibling.value = this.value"  value="<?php echo $currentData ? $currentData['connectivity'] : '0'; ?>" required>
                        <output><?php echo $currentData['connectivity'] ?></output>
                    </div>
                </div>

                <div class="form-group">
                    <label>Presentation:</label><br>
                    <div class = "slider-container">
                        <input type="range" name="presentation" class="slider" min="0" max="10" oninput="this.nextElementSibling.value = this.value"  value="<?php echo $currentData ? $currentData['presentation'] : '0'; ?>" required>
                        <output><?php echo $currentData['presentation'] ?></output>
                    </div>
                </div>

                <div class="form-group">
                    <label>Clarity:</label><br>
                    <div class = "slider-container">
                        <input type="range" name="clarity" class="slider" min="0" max="10" oninput="this.nextElementSibling.value = this.value"  value="<?php echo $currentData ? $currentData['clarity'] : '0'; ?>" required>
                        <output><?php echo $currentData['clarity'] ?></output>
                    </div>
                </div>

                <div class="form-group">
                    <label>Activities:</label><br>
                    <div class = "slider-container">
                        <input type="range" name="activities" class="slider" min="0" max="10" oninput="this.nextElementSibling.value = this.value"  value="<?php echo $currentData ? $currentData['activities'] : '0'; ?>" required>
                        <output><?php echo $currentData['activities'] ?></output>
                    </div>
                </div>

                <div class="form-group">
                    <label>Project Management:</label><br>
                    <div class = "slider-container">
                        <input type="range" name="p_manage" class="slider" min="0" max="10" oninput="this.nextElementSibling.value = this.value"  value="<?php echo $currentData ? $currentData['project_management'] : '0'; ?>" required>
                        <output><?php echo $currentData['project_management'] ?></output>
                    </div>
                </div>

                <div class="form-group">
                    <label>Time Management:</label><br>
                    <div class = "slider-container">
                        <input type="range" name="t_manage" class="slider" min="0" max="10" oninput="this.nextElementSibling.value = this.value"  value="<?php echo $currentData ? $currentData['time_management'] : '0'; ?>" required>
                        <output><?php echo $currentData['time_management'] ?></output>
                    </div>
                </div>
            </div>

            <div class="form-collection2">
                <div class="form-group">
                    <h1>General Feedback:</h1><br>
                    <textarea name="feedback" placeholder="Enter comments here..." required><?php echo $currentData ? $currentData['Feedback'] : ''; ?></textarea>            
                </div>
                <div class="button-container">
                    <button type="submit" class="submit-btn">Update Assessment</button>
                    <a href="StudentDatabaseAss.php" class="btn-cancel">Cancel</a>
                </div>
            </div>
        </form>
        </div>
    </div>

    <script>
    window.onload = function() {
        const urlParams = new URLSearchParams(window.location.search);
        if (urlParams.has('id')) {
            document.getElementById("modal").classList.add("open");
        }
    }

    </script>
</body>
</html>