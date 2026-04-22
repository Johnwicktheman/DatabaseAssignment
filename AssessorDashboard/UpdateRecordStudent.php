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

if (!isset($_GET['id'])) {
    die("Error: No student selected.");
}
$targetStudentID = $_GET['id'];


$sqlCurrent = "SELECT * FROM assessmentrecords WHERE StudentID = ? AND AssesorType = ?";
$resCurrent = executePreparedStatement($sqlCurrent, [$targetStudentID, $assessorType]);
$currentData = $resCurrent->fetch_assoc();
//If no data they shouldnt even be here
if (!$currentData) {
    header("Location: StudentDatabaseAss.php");
    exit();
}



$sqlStudent = "SELECT FirstName, LastName FROM studentprofile WHERE StudentAccountID = ?";
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
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Document</title>
</head>
<body>
    <h2>Create Assessment for: <?php echo $student['FirstName'] . " " . $student['LastName']; ?></h2>
    <p>Role: <strong><?php echo $assessorType; ?></strong></p>
    <hr>

    <form method="POST">
        <h3>Grades (Scale 1-10)</h3>
        
        <div class="form-group">
            <label>Understanding of Project:</label>
            <input type="number" name="u_project" min="0" max="10" value="<?php echo $currentData['understand_project']; ?>" required>
        </div>

        <div class="form-group">
            <label>Health and Safety:</label>
            <input type="number" name="h_safety" min="0" max="10" value="<?php echo $currentData['health_and_safety']; ?>" required>
        </div>

        <div class="form-group">
            <label>Connectivity:</label>
            <input type="number" name="connectivity" min="0" max="10" value="<?php echo $currentData['connectivity']; ?>" required>
        </div>

        <div class="form-group">
            <label>Presentation:</label>
            <input type="number" name="presentation" min="0" max="10" value="<?php echo $currentData['presentation']; ?>" required>
        </div>

        <div class="form-group">
            <label>Clarity:</label>
            <input type="number" name="clarity" min="0" max="10" value="<?php echo $currentData['clarity']; ?>" required>
        </div>

        <div class="form-group">
            <label>Activities:</label>
            <input type="number" name="activities" min="0" max="10" value="<?php echo $currentData['activities']; ?>" required>
        </div>

        <div class="form-group">
            <label>Project Management:</label>
            <input type="number" name="p_manage" min="0" max="10" value="<?php echo $currentData['project_management']; ?>" required>
        </div>

        <div class="form-group">
            <label>Time Management:</label>
            <input type="number" name="t_manage" min="0" max="10" value="<?php echo $currentData['time_management']; ?>" required>
        </div>

        <div class="form-group">
            <label>General Feedback:</label>
            <textarea name="feedback" placeholder="Enter comments here..." required><?php echo $currentData['Feedback']; ?></textarea>
        </div>

        <button type="submit" class="submit-btn">Update Assessment</button>
        <a href="StudentDatabaseAss.php">Cancel</a>
    </form>
    
</body>
</html>