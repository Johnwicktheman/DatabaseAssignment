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

//check if user wants to delete first
if (isset($_GET['delete_id'])) {
    $deleteID = $_GET['delete_id'];
    
    $sqlDelete = "DELETE FROM assessmentrecords WHERE StudentID = ? AND AssesorType = ?";
    executePreparedStatement($sqlDelete, [$deleteID, $assessorType]);
    
    echo "<script>alert('Record Deleted Successfully!'); window.location.href='StudentDatabaseAss.php';</script>";
    exit();
}

$targetStudentID = isset($_GET['id']) ? $_GET['id'] : null;

$currentData = null;
$student = null;

if ($targetStudentID) {
    // Only fetch this if an ID is present in the URL
    $sqlCurrent = "SELECT * FROM assessmentrecords WHERE StudentID = ? AND AssesorType = ?";
    $resCurrent = executePreparedStatement($sqlCurrent, [$targetStudentID, $assessorType]);
    $currentData = $resCurrent->fetch_assoc();

    $sqlStudent = "SELECT FirstName, LastName FROM studentprofile WHERE StudentAccountID = ?";
    $ResultStudent = executePreparedStatement($sqlStudent, [$targetStudentID]);
    $student = $ResultStudent->fetch_assoc();

    $mode = isset($_GET['mode']) ? $_GET['mode'] : 'update';

    //Just in case user somehow enter create when they already have a record, or update when they don't have a record
    if ($mode === 'update' && !$currentData) {
        // Trying to update a non-existent record
        echo "<script>alert('No record found to update. Please create one first.'); window.location.href='StudentDatabaseAss.php';</script>";
        exit();
    }
    
    if ($mode === 'create' && $currentData) {
        // Trying to create a record that already exists
        echo "<script>alert('Record already exists for this student.'); window.location.href='StudentDatabaseAss.php';</script>";
        exit();
    }
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

    // Check if record exists to determine action
    $checkSql = "SELECT AssessmentCode FROM assessmentrecords WHERE StudentID = ? AND AssesorType = ?";
    $checkRes = executePreparedStatement($checkSql, [$targetStudentID, $assessorType]);

    if ($checkRes->num_rows > 0) {//update 
        $sql = "UPDATE assessmentrecords SET Feedback=?, understand_project=?, health_and_safety=?, connectivity=?, presentation=?, clarity=?, activities=?, project_management=?, time_management=?, Internship_Score=? WHERE StudentID=? AND AssesorType=?";
        $params = [$feedback, $u_project, $h_safety, $connectivity, $presentation, $clarity, $activities, $p_manage, $t_manage, $totalScore, $targetStudentID, $assessorType];
    } 
    else {//create
        $sql = "INSERT INTO assessmentrecords (Feedback, understand_project, health_and_safety, connectivity, presentation, clarity, activities, project_management, time_management, Internship_Score, StudentID, AssesorType) VALUES (?,?,?,?,?,?,?,?,?,?,?,?)";
        $params = [$feedback, $u_project, $h_safety, $connectivity, $presentation, $clarity, $activities, $p_manage, $t_manage, $totalScore, $targetStudentID, $assessorType];
    }

    executePreparedStatement($sql, $params);
    
    echo "<script>alert('Record Updated Successfully!'); window.location.href='StudentDatabaseAss.php';</script>";
}
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">

    <link rel="stylesheet" href="../CssFiles/AssessorDashBoard.css">
    <link rel="stylesheet" href="../CssFiles/TableStyle.css">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.5.1/css/all.min.css"> 

    <!-- Font import -->
    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
    <link href="https://fonts.googleapis.com/css2?family=Bricolage+Grotesque:opsz,wght@12..96,200..800&display=swap" rel="stylesheet">

    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <style>        

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

        
        .action-links a {
            text-decoration: none;
            color: #154c4b;
            font-weight: bold;
            margin-right: 15px;
            transition: color 0.3s;
        }

        .delete-btn {
            color: #e74c3c;
            font-weight: bold;
            text-decoration: none;
            transition: 0.3s;
        }

        .delete-btn:hover {
            color: #c0392b;
            text-decoration: underline;
            cursor: pointer;
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

        .form-group {
            display: flex;
            justify-content: space-between;
            align-items: center;
            margin-bottom: 15px;
        }

        .form-group input{
            margin-left:20px;
            border-radius:10px;
            width: 40px;
            padding: 8px;
            border: 1px solid #ccc;
            border-radius: 8px;
            font-family: inherit;
            text-align: center;
        }

        

        /* THIS IS FOR SLIDERS */


        /* Container for slider and value readout */
        /*
        .slider-container {
            align-items: center;
            gap: 15px;
            max-width: 250px;
        }

        .slider {
            width: 100%;
            height: 8px;
            border-radius: 5px;
            background: #aaa9a9;
            outline: none;
            transition: 1s;
        }

        .slider::-webkit-slider-thumb {
            -webkit-appearance: none;
            appearance: none;
            width: 20px;
            height: 20px;
            border-radius: 50%;
            background: #154c4b; 
            cursor: pointer;
            border: 2px solid white;
            box-shadow: 0px 2px 5px rgba(0,0,0,0.2);
            transition: 0.2s;
        }

        .slider:hover::-webkit-slider-thumb {
            background: #219e75;
            transform: scale(1.1);
        }

        .slider::-moz-range-thumb {
            -webkit-appearance: none;
            appearance: none;
            width: 20px;
            height: 20px;
            border-radius: 50%;
            background: #154c4b; 
            cursor: pointer;
            border: 2px solid white;
            box-shadow: 0px 2px 5px rgba(0,0,0,0.2);
            transition: 0.2s;
        }

        .slider:hover::-moz-range-thumb {
            background: #219e75;
            transform: scale(1.1);
        }
        */

        /* THIS IS FOR INPUT BOXES */

        ./* Chrome, Safari, Edge, Opera */
        input::-webkit-outer-spin-button,
        input::-webkit-inner-spin-button {
        display:flex;
        -webkit-appearance: none;
        margin: 0;
        }

        /* Firefox */
        input[type=number] {
        -moz-appearance: textfield;
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
        <a href="../AssessorDashboard.php">Dashboard</a><br>
        <a href="StudentDatabaseAss.php">Assessment Records</a><br>
        <a href="#">Student Database</a><br>
        <a href="../Logout.php" style="color: #ff4d4d; font-weight: bold;" onclick="return confirm('Are you sure you want to logout?')">Logout</a>
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

                        echo "<td>";
                            if ($AssessmentCodeID) {
                                // record found, can only update and delete
                                echo "<span style='color: #ccc; cursor: not-allowed; margin-right: 15px;' title='Record already exists'>
                                        <i class='fas fa-plus-circle'></i> Create
                                    </span>";

                                echo "<a href='StudentDatabaseAss.php?id=" . $id . "&mode=update' class='open-btn'>
                                        <i class='fas fa-edit'></i> Update
                                    </a>";

                                echo "<a href='#' onclick='confirmDelete(" . $id . ")' class='delete-btn'>
                                        <i class='fas fa-delete-left'></i> Delete
                                    </a>";

                            } else {
                                // no record found, can only create
                                echo "<a href='StudentDatabaseAss.php?id=" . $id . "&mode=create' class='open-btn'>
                                        <i class='fas fa-plus-circle'></i> Create
                                    </a>";
                                echo "<span style='color: #ccc; cursor: not-allowed; margin-right: 15px;' title='Create a record first'>
                                        <i class='fas fa-edit'></i> Update
                                    </span>";

                                echo "<span style='color: #ccc; cursor: not-allowed; margin-right: 15px;' title='Create a record first'>
                                        <i class='fas fa-delete-left'></i> Delete
                                    </span>";
                            }

                        echo "</td>";
                        echo "</tr>";
                    }
                ?>
    </table>

    <div id="modal">
        <div id="modal-inner">
        <h1><?php echo (isset($_GET['mode']) && $_GET['mode'] == 'create') ? 'Create' : 'Update'; ?> Assessment for: <?php echo $student['FirstName'] . " " . $student['LastName']; ?></h1>
    <p>Role: <strong><?php echo $assessorType; ?></strong></p>
    <hr>
    <!--
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
        -->
            <form method="POST">            
            <div class="form-collection1">
                <h1>Grades (Scale 0-10)</h1>
                <div class="form-group">
                    <label>Understanding of Project:</label><br>
                    <div class = "input-container">
                        <input type="number" step="any" name="u_project" class="input" min="0" max="10" value="<?php echo $currentData ? $currentData['understand_project'] : '0'; ?>" required>
                    </div>
                </div>

                <div class="form-group">
                    <label>Health and Safety:</label><br>
                    <div class = "input-container">
                        <input type="number" step="any" name="h_safety" class="input" min="0" max="10" value="<?php echo $currentData ? $currentData['health_and_safety'] : '0'; ?>" required>
                    </div>
                </div>

                <div class="form-group">
                    <label>Connectivity:</label><br>
                    <div class = "input-container">
                        <input type="number" step="any" name="connectivity" class="input" min="0" max="10" value="<?php echo $currentData ? $currentData['connectivity'] : '0'; ?>" required>
                    </div>
                </div>

                <div class="form-group">
                    <label>Presentation:</label><br>
                    <div class = "input-container">
                        <input type="number" step="any" name="presentation" class="input" min="0" max="10" oninput="this.nextElementSibling.value = this.value"  value="<?php echo $currentData ? $currentData['presentation'] : '0'; ?>" required>
                    </div>
                </div>

                <div class="form-group">
                    <label>Clarity:</label><br>
                    <div class = "input-container">
                        <input type="number" step="any" name="clarity" class="input" min="0" max="10" oninput="this.nextElementSibling.value = this.value"  value="<?php echo $currentData ? $currentData['clarity'] : '0'; ?>" required>
                    </div>
                </div>

                <div class="form-group">
                    <label>Activities:</label><br>
                    <div class = "input-container">
                        <input type="number" step="any" name="activities" class="input" min="0" max="10" oninput="this.nextElementSibling.value = this.value"  value="<?php echo $currentData ? $currentData['activities'] : '0'; ?>" required>
                    </div>
                </div>

                <div class="form-group">
                    <label>Project Management:</label><br>
                    <div class = "input-container">
                        <input type="number" step="any" name="p_manage" class="input" min="0" max="10" oninput="this.nextElementSibling.value = this.value"  value="<?php echo $currentData ? $currentData['project_management'] : '0'; ?>" required>
                    </div>
                </div>

                <div class="form-group">
                    <label>Time Management:</label><br>
                    <div class = "input-container">
                        <input type="number" step="any" name="t_manage" class="input" min="0" max="10" oninput="this.nextElementSibling.value = this.value"  value="<?php echo $currentData ? $currentData['time_management'] : '0'; ?>" required>
                    </div>
                </div>
            </div>

            <div class="form-collection2">
                <div class="form-group2">
                    <h1>General Feedback:</h1><br>
                    <textarea name="feedback" placeholder="Enter comments here..." required><?php echo $currentData ? $currentData['Feedback'] : ''; ?></textarea>            
                </div>
                <div class="button-container">
                    <button type="submit" class="submit-btn"><?php echo (isset($_GET['mode']) && $_GET['mode'] == 'create') ? 'Create' : 'Update'; ?> Assessment</button>
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

    function confirmDelete(studentID){
        if (confirm("Are you sure you want to delete this assessment record? This action cannot be undone.")) {
            window.location.href = "StudentDatabaseAss.php?delete_id=" + studentID;
        }
    }


    </script>
</body>
</html>