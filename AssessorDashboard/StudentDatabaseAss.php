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
            background-color: #FFFFFF;
            opacity: 0;
            position: fixed;
            top:0;
            left:0;
            right:0;
            bottom:0;

            transition: all 0.3s ease-in-out;
            z-index: -1;

            display:flex;
            align-items: center;
            justify-content: center

        }

        #modal.open{
            opacity:1;
            z-index:999;
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

                            echo "<td><b style='color: teal;'>Score: " . $InternshipScore . "</b></td>";
                        } else {
                            echo "<td><b style='color: gray;'>No Score</b></td>";
                        }

                        echo "<td>
                            <a href='javascript:void(0)' class='open-btn'><i class='fas fa-plus-circle'></i>Create Record</a> 
                            <a href='UpdateRecordStudent.php?id=" . $row['StudentAccountID'] . "'><i class='fas fa-edit'></i>Update</a>
                        </td>";
                        echo "</tr>";
                    }
                ?>
    </table>

    <div id="modal">
        <h2>Hi</h2>
        <div class="form-group">
            <label>Time Management:</label>
            <input type="number" name="t_manage" min="0" max="10" value="<?php echo $currentData['time_management']; ?>" required>
            <br>
            <button type="submit" class="submit-btn">Submit Assessment</button>
            <br>
            <a href="javascript:void(0)" id="cancel-btn" class="cancel-btn">Cancel</a>
        </div>
    </div>

    <script>
    const openBtns = document.querySelectorAll(".open-btn");
    const modal = document.getElementById("modal");
    const cancelBtn = document.getElementById("cancel-btn");

    // 2. Loop through each button and add the click event
    openBtns.forEach(btn => {
        btn.addEventListener("click", () => {
            modal.classList.add("open");
        });
    });

    // 3. Add event to close the modal
    cancelBtn.addEventListener("click", () => {
        modal.classList.remove("open");
    });

    </script>
</body>
</html>