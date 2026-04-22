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
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Document</title>
</head>
<body>
    <div class="filter-controls" style="margin-bottom: 20px;">
        <input type="text" id="nameSearch" onkeyup="applyJSFilters()" placeholder="Search names...">
        
        <select id="statusFilter" onchange="applyJSFilters()">
            <option value="all">All Records</option>
            <option value="marked">Marked Only</option>
            <option value="unmarked">Unmarked Only</option>
            <option value="high">Score > 20</option>
        </select>
    </div>
    <p><a href="../AssessorDashboard.php">Back to Dashboard</a></p>
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
                $id        = $row['StudentAccountID'];
                $FirstName = $row['FirstName'];
                $LastName  = $row['LastName'];
                $AssessmentCodeID = $row['AssessmentCode'];// Check if assessment record exists
                $InternshipScore = $row['Internship_Score']; // Check if Internship Score exists

                echo "<tr class='student-row'>";
                echo "<td>" . $id . "</td>";
                echo "<td class='student-name'>" . $FirstName . " " . $LastName . "</td>";
                if ($AssessmentCodeID) {
                    echo "<td class='student-status' data-status='marked'><b style='color: green;'>Record Exists</b></td>";
                } else {
                    echo "<td class='student-status' data-status='unmarked'><b style='color: gray;'>No Records</b></td>";
                }
                if ($InternshipScore !== null) {
                    echo "<td class='student-score' data-score='" . $InternshipScore . "'> <b style='color: green;'>Score: " . $InternshipScore . "</b></td>";
                } else {
                    echo "<td class='student-score' data-score='0'><b style='color: gray;'>No Score</b></td>";
                }

                echo "<td><a href='CreateRecordStudent.php?id=" . $row['StudentAccountID'] . "'>Create Record</a></td>";
                echo "<td><a href='UpdateRecordStudent.php?id=" . $row['StudentAccountID'] . "'>Update</a></td>";
                echo "</tr>";
            }
        ?>


    </table>


<script>
    
function applyJSFilters() {
    // 1. Get user input
    let searchQuery = document.getElementById('nameSearch').value.toLowerCase();
    let statusCriteria = document.getElementById('statusFilter').value;
    
    // 2. Select all rows we just echoed
    let rows = document.querySelectorAll('.student-row');

    rows.forEach(row => {
        // 3. Extract data from the classes/attributes we added in the echo
        let name = row.querySelector('.student-name').innerText.toLowerCase();
        let status = row.querySelector('.student-status').getAttribute('data-status');
        let score = parseFloat(row.querySelector('.student-score').getAttribute('data-score'));

        // 4. Determine if row should be shown
        let matchesSearch = name.includes(searchQuery);
        let matchesStatus = true;

        if (statusCriteria === 'marked') matchesStatus = (status === 'marked');
        if (statusCriteria === 'unmarked') matchesStatus = (status === 'unmarked');
        if (statusCriteria === 'high') matchesStatus = (score > 20);

        // 5. Apply visibility
        if (matchesSearch && matchesStatus) {
            row.style.display = "";
        } else {
            row.style.display = "none";
        }
    });
}
</script>
</body>
</html>