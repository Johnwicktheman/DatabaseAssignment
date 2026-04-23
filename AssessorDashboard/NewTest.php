<?php
ini_set('display_errors', 1);
ini_set('display_startup_errors', 1);
error_reporting(E_ALL);
session_start();
include '../Connection.php';
include '../ExecutePStatement.php';
include '../AllFunctions.php';

checkAccess(['Lecturer', 'Supervisor']);

$assessorID = $_SESSION['user_id'];
$assessorType = $_SESSION['user_role'];

if ($assessorType === 'Lecturer') {
    $assessorIDField = 'AssesorAccountIDLect';
} else {
    $assessorIDField = 'AssesorAccountIDSuper';
}

// --- 1. BASE QUERY & PHP SORTING ---
$searchQuery = $_GET['search'] ?? '';
$sortOption = $_GET['sort'] ?? 'oldest'; // Changed default to Oldest First

$studentListSql = "SELECT sp.*, i.Role, i.Months_duration, i.Description, ar.AssessmentCode, ar.Internship_Score, c.CompanyName
                   FROM studentprofile sp
                   LEFT JOIN internship i ON sp.StudentAccountID = i.StudentAccountID
                   LEFT JOIN assessmentrecords ar ON sp.StudentAccountID = ar.StudentID AND ar.AssesorType = ?
                   LEFT JOIN companynamelist c ON i.CompanyINT = c.CompanyInt
                   WHERE sp." . $assessorIDField . " = ?";

$queryParams = [$assessorType, $assessorID];

// PHP Native Sorting (Helps initial page load)
switch ($sortOption) {
    case 'newest':
        $studentListSql .= " ORDER BY ar.AssessmentCode DESC";
        break;
    case 'score_desc':
        $studentListSql .= " ORDER BY ar.Internship_Score DESC";
        break;
    case 'score_asc':
        $studentListSql .= " ORDER BY ar.Internship_Score ASC";
        break;
    case 'no_record':
        $studentListSql .= " ORDER BY (ar.AssessmentCode IS NOT NULL) ASC, sp.StudentAccountID ASC";
        break;
    case 'oldest':
    default:
        $studentListSql .= " ORDER BY ar.AssessmentCode ASC";
        break;
}

$studentResult = executePreparedStatement($studentListSql, $queryParams);

// --- 2. DELETE LOGIC ---
if (isset($_GET['delete_id'])) {
    $deleteID = $_GET['delete_id'];
    $sqlDelete = "DELETE FROM assessmentrecords WHERE StudentID = ? AND AssesorType = ?";
    executePreparedStatement($sqlDelete, [$deleteID, $assessorType]);
    
    echo "<script>alert('Record Deleted Successfully!'); window.location.href='StudentDatabaseAss.php?sort=" . urlencode($sortOption) . "&search=" . urlencode($searchQuery) . "';</script>";
    exit();
}

// --- 3. MODAL LOGIC ---
$targetStudentID = isset($_GET['id']) ? $_GET['id'] : null;
$currentData = null;
$student = null;
$mode = isset($_GET['mode']) ? $_GET['mode'] : 'update';

if ($targetStudentID) {
    $sqlCurrent = "SELECT * FROM assessmentrecords WHERE StudentID = ? AND AssesorType = ?";
    $resCurrent = executePreparedStatement($sqlCurrent, [$targetStudentID, $assessorType]);
    $currentData = $resCurrent->fetch_assoc();

    $sqlStudent = "SELECT sp.FirstName, sp.LastName, i.Role, i.Months_duration, i.Description, c.CompanyName
                   FROM studentprofile sp
                   LEFT JOIN internship i ON sp.StudentAccountID = i.StudentAccountID
                   LEFT JOIN companynamelist c ON i.CompanyINT = c.CompanyInt
                   WHERE sp.StudentAccountID = ?";

    $ResultStudent = executePreparedStatement($sqlStudent, [$targetStudentID]);
    $student = $ResultStudent->fetch_assoc();

    if ($mode === 'update' && !$currentData) {
        echo "<script>alert('No record found to update.'); window.location.href='StudentDatabaseAss.php';</script>";
        exit();
    }
    if ($mode === 'create' && $currentData) {
        echo "<script>alert('Record already exists.'); window.location.href='StudentDatabaseAss.php';</script>";
        exit();
    }
}

// --- 4. FORM SUBMIT LOGIC ---
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
    
    $totalScore = (int)round($u_project + $h_safety + $connectivity + $presentation + $clarity + $activities + $p_manage + $t_manage);

    $checkSql = "SELECT AssessmentCode FROM assessmentrecords WHERE StudentID = ? AND AssesorType = ?";
    $checkRes = executePreparedStatement($checkSql, [$targetStudentID, $assessorType]);

    if ($checkRes->num_rows > 0) { 
        $sql = "UPDATE assessmentrecords SET Feedback=?, understand_project=?, health_and_safety=?, connectivity=?, presentation=?, clarity=?, activities=?, project_management=?, time_management=?, Internship_Score=? WHERE StudentID=? AND AssesorType=?";
    } else { 
        $sql = "INSERT INTO assessmentrecords (Feedback, understand_project, health_and_safety, connectivity, presentation, clarity, activities, project_management, time_management, Internship_Score, StudentID, AssesorType) VALUES (?,?,?,?,?,?,?,?,?,?,?,?)";
    }

    $params = [$feedback, $u_project, $h_safety, $connectivity, $presentation, $clarity, $activities, $p_manage, $t_manage, $totalScore, $targetStudentID, $assessorType];
    executePreparedStatement($sql, $params);
    
    // Redirect back preserving sorting memory
    echo "<script>alert('Record Updated Successfully!'); window.location.href='StudentDatabaseAss.php?sort=" . urlencode($sortOption) . "';</script>";
    exit();
}
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <link rel="stylesheet" href="../CssFiles/AssessorDashBoard.css">
    <link rel="stylesheet" href="../CssFiles/TableStyle.css">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.5.1/css/all.min.css"> 
    <link href="https://fonts.googleapis.com/css2?family=Bricolage+Grotesque:opsz,wght@12..96,200..800&display=swap" rel="stylesheet">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <style>
        /* (Keep all your existing CSS here exactly as it was) */
        nav a{ margin-bottom:20px; }
        #title{ color: #aaa9a9; font-size:30px; padding-bottom:8px; }
        .main hr { border: 0; border-top: 1px solid #aaa9a9; }
        header { font-size: 50px; color: #154c4b; }
        .back-link { display: inline-block; margin-bottom: 20px; color: #154c4b; text-decoration: none; font-weight: bold; transition: color 0.3s; }
        .back-link:hover { color: #219e75; cursor:pointer; }
        .delete-btn { color: #e74c3c; font-weight: bold; text-decoration: none; transition: 0.3s; }
        .delete-btn:hover { color: #c0392b; text-decoration: underline; cursor: pointer; }
        tr a{ text-decoration: none; color: #154c4b; font-weight: bold; margin-right: 15px; transition: color 0.3s; }
        tr a:hover { color: #219e75; }
        tr i { margin-right: 5px; }

        /* Search and Filter Bar Styles */
        .search-bar-container {
            display: flex;
            justify-content: space-between;
            align-items: center;
            background-color: #f9f9f9;
            padding: 15px;
            border-radius: 8px;
            margin-bottom: 20px;
            border: 1px solid #ddd;
        }
        .search-bar-container input, .search-bar-container select {
            padding: 10px; border: 1px solid #ccc; border-radius: 5px; font-size: 16px;
        }
        .search-bar-container input { width: 250px; }

        #modal{ opacity: 0; position: fixed; right:0; left:0; bottom: 60px; transition: all 0.3s ease-in-out; z-index: -1; display:flex; align-items: center; justify-content: center; }
        #modal.open{ opacity:1; z-index:999; }
        #modal-inner{ background-color: #FFFFFF; width: 700px; height: 650px; border-radius:20px; padding: 15px 25px; text-align: center; box-shadow: 15px 25px 30px rgba(0,0,0,0.2); overflow-y: auto;}
        #modal-inner h1{ color: #154c4b; }
        form { display: flex; justify-content:center; text-align:center; }
        form h1{ color: #aaa9a9; font-size:20px; }
        .form-collection1{ display:inline; margin-right:100px; }
        form label{ font-weight: bold; color: #555; }
        .form-group { display: flex; justify-content: space-between; align-items: center; margin-bottom: 15px; }
        .form-group input{ margin-left:20px; border-radius:10px; width: 50px; padding: 8px; border: 1px solid #ccc; text-align: center; }
        input[type=number]::-webkit-inner-spin-button, input[type=number]::-webkit-outer-spin-button { -webkit-appearance: none; margin: 0; }
        .form-collection2 textarea { width: 100%; height:300px; padding: 15px; border: 1px solid #ccc; border-radius: 12px; resize: vertical; }
        .button-container { margin-top: 30px; display: flex; align-items: center; gap: 20px; }
        .submit-btn { background-color: #154c4b; color: white; border: none; padding: 12px 25px; font-size: 16px; font-weight: bold; border-radius: 25px; cursor: pointer; transition: 0.3s; }
        .submit-btn:hover { background-color: #219e75; }
        .btn-cancel { text-decoration: none; color: #721c24; font-weight: bold; font-size: 16px; }
    </style>
    <title>Student Database</title>
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

        <div class="search-bar-container">
            <div>
                <label for="jsSearch">Search:</label>
                <input type="text" id="jsSearch" placeholder="ID or Name..." onkeyup="filterTable()">
            </div>
            <div>
                <label for="jsSort">Sort By:</label>
                <select id="jsSort" onchange="sortTable()">
                    <option value="oldest" <?php if($sortOption == 'oldest') echo 'selected'; ?>>Oldest Assessment First</option>
                    <option value="newest" <?php if($sortOption == 'newest') echo 'selected'; ?>>Newest Assessment First</option>
                    <option value="score_desc" <?php if($sortOption == 'score_desc') echo 'selected'; ?>>Score (Highest to Lowest)</option>
                    <option value="score_asc" <?php if($sortOption == 'score_asc') echo 'selected'; ?>>Score (Lowest to Highest)</option>
                    <option value="no_record" <?php if($sortOption == 'no_record') echo 'selected'; ?>>Null Assessment Record First</option>
                </select>
            </div>
        </div>

        <table id="studentTable">
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

                        // Track ID so JS can sort chronologically. 0 acts mathematically as lowest/oldest.
                        $assessmentSortID = $AssessmentCodeID ? $AssessmentCodeID : 0;

                        echo "<tr data-assessment-id='$assessmentSortID'>";
                        echo "<td>" . $id . "</td>";
                        echo "<td>" . $FirstName . " " . $LastName . "</td>";
                        
                        // Used for sorting logic in JS
                        $recordStatus = $AssessmentCodeID ? "Exists" : "None";
                        
                        if ($AssessmentCodeID) {
                            echo "<td data-record='1'><b style='color: green;'>Record Exists</b></td>";
                        } else {
                            echo "<td data-record='0'><b style='color: gray;'>No Records</b></td>";
                        }

                        if ($InternshipScore !== null) {
                            if($InternshipScore >= 60) echo "<td data-score='$InternshipScore'><b style='color: teal;'>Score: $InternshipScore</b></td>";
                            else if ($InternshipScore >= 40) echo "<td data-score='$InternshipScore'><b style='color: orange;'>Score: $InternshipScore</b></td>";
                            else echo "<td data-score='$InternshipScore'><b style='color: red;'>Score: $InternshipScore</b></td>";
                        } else {
                            // Empty score acts as -1, so it won't force to bottom artificially.
                            echo "<td data-score='-1'><b style='color: gray;'>No Score</b></td>";
                        }

                        echo "<td>";
                            if ($AssessmentCodeID) {
                                echo "<span style='color: #ccc; cursor: not-allowed; margin-right: 15px;' title='Record already exists'><i class='fas fa-plus-circle'></i> Create</span>";
                                echo "<a href='StudentDatabaseAss.php?id=" . $id . "&mode=update&sort=" . urlencode($sortOption) . "' class='open-btn'><i class='fas fa-edit'></i> Update</a>";
                                echo "<a href='#' onclick='confirmDelete(" . $id . ")' class='delete-btn'><i class='fas fa-delete-left'></i> Delete</a>";
                            } else {
                                echo "<a href='StudentDatabaseAss.php?id=" . $id . "&mode=create&sort=" . urlencode($sortOption) . "' class='open-btn'><i class='fas fa-plus-circle'></i> Create</a>";
                                echo "<span style='color: #ccc; cursor: not-allowed; margin-right: 15px;' title='Create a record first'><i class='fas fa-edit'></i> Update</span>";
                                echo "<span style='color: #ccc; cursor: not-allowed; margin-right: 15px;' title='Create a record first'><i class='fas fa-delete-left'></i> Delete</span>";
                            }
                        echo "</td>";
                        echo "</tr>";
                    }
                ?>
            </tbody>
        </table>

        <div id="modal">
            <div id="modal-inner">
                <h1><?php echo (isset($_GET['mode']) && $_GET['mode'] == 'create') ? 'Create' : 'Update'; ?> Assessment for: <?php echo $student['FirstName'] ?? '' . " " . $student['LastName'] ?? ''; ?></h1>
                <p>Role: <strong><?php echo $assessorType; ?></strong></p>
                <p><strong>Company:</strong> <?php echo htmlspecialchars($student['CompanyName'] ?? 'N/A'); ?>
                <strong>Role:</strong> <?php echo htmlspecialchars($student['Role'] ?? 'N/A'); ?>
                <strong>Duration:</strong> <?php echo htmlspecialchars($student['Months_duration'] ?? '0'); ?> Months</p>
                <p><strong>Tasks/Description:</strong> <?php echo htmlspecialchars($student['Description'] ?? 'N/A'); ?></p>
                <hr>
                
                <form method="POST" action="StudentDatabaseAss.php?id=<?php echo htmlspecialchars($targetStudentID); ?>&mode=<?php echo htmlspecialchars($mode); ?>&sort=<?php echo urlencode($sortOption); ?>">            
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
                            <a href="StudentDatabaseAss.php?sort=<?php echo urlencode($sortOption); ?>" class="btn-cancel">Cancel</a>
                        </div>
                    </div>
                </form>
            </div>
        </div>

    </div>

    <script>
    window.onload = function() {
        const urlParams = new URLSearchParams(window.location.search);
        if (urlParams.has('id')) {
            document.getElementById("modal").classList.add("open");
        }
        
        // Initial sorting to ensure JS matches the default selected state
        sortTable(); 
    }

    function confirmDelete(studentID){
        if (confirm("Are you sure you want to delete this assessment record? This action cannot be undone.")) {
            const sort = document.getElementById("jsSort").value;
            window.location.href = "StudentDatabaseAss.php?delete_id=" + studentID + "&sort=" + encodeURIComponent(sort);
        }
    }

    function filterTable() {
        let input = document.getElementById("jsSearch").value.toLowerCase();
        let table = document.getElementById("studentTable");
        let tr = table.getElementsByTagName("tbody")[0].getElementsByTagName("tr");

        for (let i = 0; i < tr.length; i++) {
            let idCol = tr[i].getElementsByTagName("td")[0];
            let nameCol = tr[i].getElementsByTagName("td")[1];
            
            if (idCol || nameCol) {
                let idTxt = idCol.textContent || idCol.innerText;
                let nameTxt = nameCol.textContent || nameCol.innerText;
                
                if (idTxt.toLowerCase().indexOf(input) > -1 || nameTxt.toLowerCase().indexOf(input) > -1) {
                    tr[i].style.display = "";
                } else {
                    tr[i].style.display = "none";
                }
            }       
        }
    }

    function sortTable() {
        let table = document.getElementById("studentTable");
        let tbody = table.getElementsByTagName("tbody")[0];
        let rows = Array.from(tbody.getElementsByTagName("tr"));
        let sortType = document.getElementById("jsSort").value;

        rows.sort((a, b) => {
            let valA, valB;

            switch(sortType) {
                case 'newest':
                    valA = parseInt(a.getAttribute('data-assessment-id'));
                    valB = parseInt(b.getAttribute('data-assessment-id'));
                    return valB - valA; 

                case 'oldest':
                    valA = parseInt(a.getAttribute('data-assessment-id'));
                    valB = parseInt(b.getAttribute('data-assessment-id'));
                    return valA - valB; 

                case 'score_desc':
                    valA = parseFloat(a.cells[3].getAttribute('data-score'));
                    valB = parseFloat(b.cells[3].getAttribute('data-score'));
                    return valB - valA; 

                case 'score_asc':
                    valA = parseFloat(a.cells[3].getAttribute('data-score'));
                    valB = parseFloat(b.cells[3].getAttribute('data-score'));
                    return valA - valB; 

                case 'no_record':
                    valA = parseInt(a.cells[2].getAttribute('data-record'));
                    valB = parseInt(b.cells[2].getAttribute('data-record'));
                    return valA - valB; 
            }
        });

        // Re-append sorted rows back into table
        rows.forEach(row => tbody.appendChild(row));
    }
    </script>
</body>
</html>