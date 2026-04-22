<?php
ini_set('display_errors', 1);
ini_set('display_startup_errors', 1);
error_reporting(E_ALL);
session_start();
include '../Connection.php';
include '../ExecutePStatement.php';

//see if they are loggged in and if they are admin or not
if (!isset($_SESSION['username']) || ($_SESSION['user_role'] !== 'Supervisor' && $_SESSION['user_role'] !== 'Lecturer')) {
    header("Location: ../FrontPage.php"); 
    exit();
}

//Get current Role and ID currenlty confirmed to be lecturer or supervisor
$assessorID = $_SESSION['user_id'];
$assessorType = $_SESSION['user_role'];

//Check role
if ($assessorType === 'Lecturer') {
    $assessorIDField = 'AssesorAccountIDLect';

} else {
    $assessorIDField = 'AssesorAccountIDSuper';
}

if (!isset($_GET['id'])) {
    die("Error: No student selected.");
}
$targetStudentID = $_GET['id'];

//Check whether a record for this student already exist for this assessor type
$sqlCheck = "SELECT AssessmentCode FROM assessmentrecords WHERE StudentID = ? AND AssesorType = ?";
$resCheck = executePreparedStatement($sqlCheck, [$targetStudentID, $assessorType]);

//If exist kick them out
if ($resCheck->num_rows > 0) {
    echo "<script>
            alert('A record for this student has already been created by a $assessorType. You will be redirected back.');
            window.location.href='StudentDatabaseAss.php';
          </script>";
    exit(); 
}



//This is to just get name of student and internship details for display
$sqlStudent = "SELECT prof.FirstName, prof.LastName, intern.Role, intern.Months_duration, intern.Description, comp.CompanyName 
               FROM studentprofile prof
               LEFT JOIN internship intern ON prof.StudentAccountID = intern.StudentAccountID
               LEFT JOIN companynamelist comp ON intern.CompanyINT = comp.CompanyInt
               WHERE prof.StudentAccountID = ?";

$ResultStudent = executePreparedStatement($sqlStudent, [$targetStudentID]);
$student = $ResultStudent->fetch_assoc();

if (!$student) {
    die("Error: Student profile not found.");
}
$ResultStudent = executePreparedStatement($sqlStudent, [$targetStudentID]);
$student = $ResultStudent->fetch_assoc();


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

    $sqlInsert = "INSERT INTO assessmentrecords 
        (StudentID, AssesorType, Feedback, understand_project, health_and_safety, connectivity, presentation, clarity, activities, project_management, time_management, Internship_Score) 
        VALUES (?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?)";
    
    $params = [
        $targetStudentID, $assessorType, $feedback, 
        $u_project, $h_safety, $connectivity, $presentation, 
        $clarity, $activities, $p_manage, $t_manage, $totalScore
    ];

    executePreparedStatement($sqlInsert, $params);
    
    echo "<script>alert('Record Created Successfully!'); window.location.href='StudentDatabaseAss.php';</script>";
}
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Document</title>
</head>
<body>
    <h2>Create Assessment for: <?php echo $student['FirstName'] . " " . $student['LastName']; ?></h2>
    <div style="background-color: #f9f9f9; border: 1px solid #ddd; padding: 15px; border-radius: 5px; margin-bottom: 20px;">
        <h3>Student Internship Context</h3>
        <p><strong>Company:</strong> <?php echo htmlspecialchars($student['CompanyName'] ?? 'N/A'); ?></p>
        <p><strong>Role:</strong> <?php echo htmlspecialchars($student['Role'] ?? 'N/A'); ?></p>
        <p><strong>Duration:</strong> <?php echo htmlspecialchars($student['Months_duration'] ?? '0'); ?> Months</p>
        <p><strong>Tasks/Description:</strong><br>
            <?php echo (htmlspecialchars($student['Description'] ?? 'No description provided.')); ?>
        </p>
    </div>

    <hr>
    <p>Role: <strong><?php echo $assessorType; ?></strong></p>
    <hr>

    <form method="POST">
        <h3>Grades (Scale 1-10)</h3>
        
        <div class="form-group">
            <label>Understanding of Project:</label>
            <input type="number" name="u_project" min="0" max="10" required>
        </div>

        <div class="form-group">
            <label>Health and Safety:</label>
            <input type="number" name="h_safety" min="0" max="10" required>
        </div>

        <div class="form-group">
            <label>Connectivity:</label>
            <input type="number" name="connectivity" min="0" max="10" required>
        </div>

        <div class="form-group">
            <label>Presentation:</label>
            <input type="number" name="presentation" min="0" max="10" required>
        </div>

        <div class="form-group">
            <label>Clarity:</label>
            <input type="number" name="clarity" min="0" max="10" required>
        </div>

        <div class="form-group">
            <label>Activities:</label>
            <input type="number" name="activities" min="0" max="10" required>
        </div>

        <div class="form-group">
            <label>Project Management:</label>
            <input type="number" name="p_manage" min="0" max="10" required>
        </div>

        <div class="form-group">
            <label>Time Management:</label>
            <input type="number" name="t_manage" min="0" max="10" required>
        </div>

        <div class="form-group">
            <label>General Feedback:</label>
            <textarea name="feedback" placeholder="Enter comments here..." required></textarea>
        </div>

        <button type="submit" class="submit-btn">Submit Assessment</button>
        <a href="StudentDatabaseAss.php">Cancel</a>
    </form>
    
</body>
</html>