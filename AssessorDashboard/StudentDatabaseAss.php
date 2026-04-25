<?php
ini_set('display_errors', 1);
ini_set('display_startup_errors', 1);
error_reporting(E_ALL);
session_start();
include '../Connection.php';
include '../ExecutePStatement.php';
include '../AllFunctions.php';
checkAccess(['Lecturer', 'Supervisor']);

//get current Role and ID currently confirmed to be lecturer or supervisor
$assessorID = $_SESSION['user_id'];
$assessorType = $_SESSION['user_role'];

//check role
if ($assessorType === 'Lecturer') {
    $assessorIDField = 'AssesorAccountIDLect';
} else {
    $assessorIDField = 'AssesorAccountIDSuper';
}

//we want to show students assigned to lecturer or supervisor and show they got record or not
$studentList = "SELECT sp.*, i.Role, i.Months_duration, i.Description, ar.AssessmentCode, ar.Internship_Score, c.CompanyName
                   FROM studentprofile sp
                   LEFT JOIN internship i ON sp.StudentAccountID = i.StudentAccountID
                   LEFT JOIN assessmentrecords ar ON sp.StudentAccountID = ar.StudentID AND ar.AssesorType = ?
                   LEFT JOIN companynamelist c ON i.CompanyINT = c.CompanyInt
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
    //if got targertstudent then start to select
    $sqlCurrent = "SELECT * FROM assessmentrecords WHERE StudentID = ? AND AssesorType = ?";
    $resCurrent = executePreparedStatement($sqlCurrent, [$targetStudentID, $assessorType]);
    $currentData = $resCurrent->fetch_assoc();

    $sqlStudent = "SELECT sp.FirstName, sp.LastName, 
                          i.Role, i.Months_duration, i.Description, 
                          c.CompanyName
                   FROM studentprofile sp
                   LEFT JOIN internship i ON sp.StudentAccountID = i.StudentAccountID
                   LEFT JOIN companynamelist c ON i.CompanyINT = c.CompanyInt
                   WHERE sp.StudentAccountID = ?";

    $resStudent = executePreparedStatement($sqlStudent, [$targetStudentID]);
    $student = $resStudent->fetch_assoc();

    $mode = isset($_GET['mode']) ? $_GET['mode'] : 'update';

    //just in case user somehow enter create when they already have a record, or update when they don't have a record
    if ($mode === 'update' && !$currentData) {
        echo "<script>alert('No record found to update. Please create one first.'); window.location.href='StudentDatabaseAss.php';</script>";
        exit();
    }
    
    if ($mode === 'create' && $currentData) {
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
    $totalScore = $u_project + $h_safety + $connectivity + $presentation * 1.5 + $clarity + $activities * 1.5 + $p_manage * 1.5 + $t_manage * 1.5;
    $totalScore = (int)round($totalScore);

    //check if record exists to determine action
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
    exit();
}
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">

    <link rel="stylesheet" href="../CssFiles/AssessorDashBoard.css">
    <link rel="stylesheet" href="../CssFiles/AssessorTableStyle.css">
    <link rel="stylesheet" href="../CssFiles/searchbar.css">
    <link rel="stylesheet" href="../CssFiles/StudentDatabaseAss.css">

    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.5.1/css/all.min.css"> 

    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
    <link href="https://fonts.googleapis.com/css2?family=Bricolage+Grotesque:opsz,wght@12..96,200..800&display=swap" rel="stylesheet">

    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Student Database</title>
</head>
<body>
    <nav>
        <p>ASSESSOR PANEL</p>
        <hr>
        <a href="../AssessorDashboard.php">Dashboard</a><br>
        <a href="StudentDatabaseAss.php">Assessment Records</a><br>
        <a href="ViewStudentDatabase.php">Student Database</a><br>
        <div id="logout">
            <a href="../Logout.php" onclick="return confirm('Are you sure you want to logout?')">Logout</a>
        </div>
    </nav>

    <div class="main">
        <div id="title">Student Records</div>
        <hr>
        <header>Manage Student Records</header>

        <a onclick="window.location.href='../AssessorDashboard.php'" class="back-link">&larr; Back to Dashboard</a>

        <div class="search-bar-container">
            <div>
                <label for="jsSearch">Search:</label>
                <input type="text" id="jsSearch" placeholder="Search ID or Name..." onkeyup="applyFilters()">
            </div>
            <div>
                <label for="jsSort">Filter / Sort By:</label>
                <select id="jsSort" onchange="applyFilters()">
                    <option value="oldest">Oldest Added (Default)</option>
                    <option value="newest">Newest Added</option>
                    <option value="no_record">No Assessment Record First</option>
                </select>
            </div>
        </div>

        <table id="searchTable">
            <thead>
                <tr>
                    <th>Student ID</th>
                    <th>Name</th>
                    <th>Assessment Record</th>
                    <th>Internship Score</th>
                    <th>Action</th>
                </tr>
            </thead>
            <tbody>
                <?php
                    while ($row = $studentResult->fetch_assoc()) {
                        $id = $row['StudentAccountID'];
                        $FirstName = $row['FirstName'];
                        $LastName = $row['LastName'];
                        $AssessmentCodeID = $row['AssessmentCode'];
                        $InternshipScore = $row['Internship_Score'];

                        
                        $assessmentSortID = $AssessmentCodeID ? $AssessmentCodeID : 0;
                        $hasRecord = $AssessmentCodeID ? 1 : 0;
                        $fullName = strtolower($FirstName . " " . $LastName);

                        //attach the hidden id directly to the table row
                        echo "<tr class='search-row' data-id='$id' data-name='$fullName' data-assessment-id='$assessmentSortID' data-has-record='$hasRecord'>";
                        
                        echo "<td>" . $id . "</td>";
                        echo "<td>" . $FirstName . " " . $LastName . "</td>";
                        
                        if ($AssessmentCodeID) {
                            echo "<td><b style='color: green;'>Record Exists</b></td>";
                        } else {
                            echo "<td><b style='color: gray;'>No Records</b></td>";
                        }
                        
                        if ($InternshipScore !== null) {
                            if($InternshipScore >= 70){
                                echo "<td><b style='color: teal;'>Score: " . $InternshipScore . "</b></td>";
                            }
                            else if ($InternshipScore >= 50){
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
            </tbody>
            <tbody id="tableBody">
                    <tr id="noResultsRow" style="display: none;">
                        <td colspan="10" style="text-align: center; padding: 20px; color: #777;">
                            No records found matching your search.
                        </td>
                    </tr>
                </tbody>
        </table>

        <div id="modal">
            <div id="modal-inner">
                <h1><?php echo (isset($_GET['mode']) && $_GET['mode'] == 'create') ? 'Create' : 'Update'; ?> Assessment for: <?php echo ($student['FirstName'] ?? '') . " " . ($student['LastName'] ?? ''); ?></h1>
                <p>Role: <strong><?php echo $assessorType; ?></strong></p>
                <p><strong>Company:</strong> <?php echo $student['CompanyName'] ?? 'N/A'; ?>
                <strong>Role:</strong> <?php echo $student['Role'] ?? 'N/A'; ?>
                <strong>Duration:</strong> <?php $student['Months_duration'] ?? '0'; ?> Months</p>
                <p><strong>Tasks/Description:</strong> <?php echo $student['Description'] ?? 'N/A'; ?></p>
                <hr>
                
                <form method="POST" action="StudentDatabaseAss.php?id=<?php echo $targetStudentID ?? ''; ?>&mode=<?php echo $mode ?? ''; ?>">            
                    <div class="form-collection1">
                        <h1>Grades (Scale 0-10)</h1>
                        <div class="form-group">
                            <label>Understanding of Project:</label><br>
                            <input type="number" step="any" name="u_project" min="0" max="10" value="<?php echo $currentData ? $currentData['understand_project'] : '0'; ?>" required>
                        </div>
                        <div class="form-group">
                            <label>Health and Safety:</label><br>
                            <input type="number" step="any" name="h_safety" min="0" max="10" value="<?php echo $currentData ? $currentData['health_and_safety'] : '0'; ?>" required>
                        </div>
                        <div class="form-group">
                            <label>Connectivity:</label><br>
                            <input type="number" step="any" name="connectivity" min="0" max="10" value="<?php echo $currentData ? $currentData['connectivity'] : '0'; ?>" required>
                        </div>
                        <div class="form-group">
                            <label>Presentation:</label><br>
                            <input type="number" step="any" name="presentation" min="0" max="10" value="<?php echo $currentData ? $currentData['presentation'] : '0'; ?>" required>
                        </div>
                        <div class="form-group">
                            <label>Clarity:</label><br>
                            <input type="number" step="any" name="clarity" min="0" max="10" value="<?php echo $currentData ? $currentData['clarity'] : '0'; ?>" required>
                        </div>
                        <div class="form-group">
                            <label>Activities:</label><br>
                            <input type="number" step="any" name="activities" min="0" max="10" value="<?php echo $currentData ? $currentData['activities'] : '0'; ?>" required>
                        </div>
                        <div class="form-group">
                            <label>Project Management:</label><br>
                            <input type="number" step="any" name="p_manage" min="0" max="10" value="<?php echo $currentData ? $currentData['project_management'] : '0'; ?>" required>
                        </div>
                        <div class="form-group">
                            <label>Time Management:</label><br>
                            <input type="number" step="any" name="t_manage" min="0" max="10" value="<?php echo $currentData ? $currentData['time_management'] : '0'; ?>" required>
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

    </div>
    <script src="../JSscripts/searchbar.js"></script>
    <script>
    //delete verification
    function confirmDelete(studentID){
        if (confirm("Are you sure you want to delete this assessment record? This action cannot be undone.")) {
            window.location.href = "StudentDatabaseAss.php?delete_id=" + studentID;
        }
    }
        window.onload = function() {
        const urlParams = new URLSearchParams(window.location.search);
        if (urlParams.has('id')) {
            document.getElementById("modal").classList.add("open");
        }
        //run the sort function immediately to enforce the "Oldest First" default
        applyFilters();
    }
    </script>

</body>
</html>