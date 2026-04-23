<?php
ini_set('display_errors', 1);
ini_set('display_startup_errors', 1);
error_reporting(E_ALL);
session_start();
include '../../Connection.php';
include '../../ExecutePStatement.php';
include '../../AllFunctions.php';

checkAccess('Admin');

// Get all lecturers for dropdown
$lecturerSql = "SELECT AssessorAccountID, Username FROM assesoraccountlist WHERE AssesorType = ?";
$lecturers = executePreparedStatement($lecturerSql, ['Lecturer']);

// Fetch all Supervisors for dropdown
$supervisorSql = "SELECT AssessorAccountID, Username FROM assesoraccountlist WHERE AssesorType = ?";
$supervisors = executePreparedStatement($supervisorSql, ['Supervisor']);

$error = null;

if ($_SERVER["REQUEST_METHOD"] == "POST") {

    $user = $_POST['username'];
    $pass = $_POST['password'];
    $AdminID = $_SESSION['user_id'];
    $fname = $_POST['firstname'];
    $lname = $_POST['lastname'];
    $programme = $_POST['programme'];
    $year = $_POST['year'];
    
    // Internship Details
    $companyID = $_POST['company_id'];
    $role = $_POST['role'];
    $duration = $_POST['duration'];
    $description = $_POST['description'];

    $lectID = ($_POST['lecturer'] === "NULL") ? null : $_POST['lecturer'];
    $superID = ($_POST['supervisor'] === "NULL") ? null : $_POST['supervisor'];

    // Username Availability Check
    $resAssessor = executePreparedStatement("SELECT Username FROM assesoraccountlist WHERE Username = ?", [$user]);
    $resStudent  = executePreparedStatement("SELECT Username FROM studentaccountlist WHERE Username = ? AND StudentAccountID != ?", [$user, $currentUserID]); // Assuming 3 is the UserTypeID for students
    $resAdmin    = executePreparedStatement("SELECT Username FROM adminaccountlist WHERE Username = ?", [$user]);

    // Determine the specific error message
    if ($resAssessor->num_rows > 0) {
        $error = "Username is already taken by an Assessor (Lecturer/Supervisor).";
    } else if ($resStudent->num_rows > 0) {
        $error = "Username is already taken by another Student.";
    } else if ($resAdmin->num_rows > 0) {
        $error = "Username is already taken by an Admin.";
    } else {
        $conn->begin_transaction();

        try {
            //Insert student account to get the StudentAccountID
            $insertAccSql = "INSERT INTO studentaccountlist (Username, Password, AdminAccountID) VALUES (?, ?, ?)";
            executePreparedStatement($insertAccSql, [$user, $pass, $AdminID]);
            $newStudentID = $conn->insert_id;

            //Insert student profile
            $insertProfSql = "INSERT INTO studentprofile 
                (StudentAccountID, FirstName, LastName, ProgrammeCode, YearOfStudy, AssesorAccountIDLect, AssesorAccountIDSuper) 
                VALUES (?, ?, ?, ?, ?, ?, ?)";
            
            executePreparedStatement($insertProfSql, [
                $newStudentID, $fname, $lname, $programme, $year, $lectID, $superID
            ]);

            //Create internship record using the StudentAccountID as the link
            $insertInternSql = "INSERT INTO internship (StudentAccountID, CompanyINT, Role, Months_duration, Description) VALUES (?, ?, ?, ?, ?)";
            executePreparedStatement($insertInternSql, [$newStudentID, $companyID, $role, $duration, $description]);

            $conn->commit();
            header("Location: ../Databases/StudentDatabase.php?msg=success");
            exit();

        } catch (Exception $e) {
            $conn->rollback();
            $error = "Failed to create record: " . $e->getMessage();
        }
    }
}
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <title>Add New Student</title>
    <style>
        body { font-family: sans-serif; padding: 20px; line-height: 1.6; }
        .error { color: red; font-weight: bold; }
        label { display: inline-block; width: 180px; }
        hr { margin: 20px 0; }
    </style>
</head>
<body>
    <h2>Register New Student</h2>
    <?php if ($error) echo "<p class='error'>$error</p>"; ?>
    
    <form action="" method="post">
        <h3>1. Login Credentials</h3>
        <label>Username:</label>
        <input type="text" name="username" required><br><br>
        
        <label>Password:</label>
        <input type="password" name="password" required><br><br>

        <hr>
        <h3>2. Student Profile</h3>
        <label>First Name:</label>
        <input type="text" name="firstname" required><br><br>

        <label>Last Name:</label>
        <input type="text" name="lastname" required><br><br>

        <label>Programme Code:</label>
        <input type="text" name="programme" placeholder="e.g. CS101" required><br><br>

        <label>Year of Study:</label>
        <input type="number" name="year" min="1" max="4" required><br><br>

        <label for="lecturer">Assign Lecturer:</label>
        <select name="lecturer" id="lecturer">
            <option value="NULL">-- No Lecturer Assigned --</option>
            <?php while($l = $lecturers->fetch_assoc()): ?>
                <option value="<?php echo $l['AssessorAccountID']; ?>"><?php echo htmlspecialchars($l['Username']); ?></option>
            <?php endwhile; ?>
        </select><br><br>

        <label for="supervisor">Assign Supervisor:</label>
        <select name="supervisor" id="supervisor">
            <option value="NULL">-- No Supervisor Assigned --</option>
            <?php while($s = $supervisors->fetch_assoc()): ?>
                <option value="<?php echo $s['AssessorAccountID']; ?>"><?php echo htmlspecialchars($s['Username']); ?></option>
            <?php endwhile; ?>
        </select>

        <hr>
        <h3>3. Internship Information</h3>
        <label>Company:</label>
        <select name="company_id" required>
            <option value="">-- Select Company --</option>
            <?php
            $compRes = $conn->query("SELECT * FROM companynamelist");
            while($comp = $compRes->fetch_assoc()) {
                echo "<option value='".$comp['CompanyInt']."'>".htmlspecialchars($comp['CompanyName'])."</option>";
            }
            ?>
        </select><br><br>

        <label>Role:</label>
        <input type="text" name="role" placeholder="e.g. Software Intern" required><br><br>

        <label>Duration (Months):</label>
        <input type="number" name="duration" min="1" required><br><br>

        <label style="vertical-align: top;">Description:</label>
        <textarea name="description" rows="4" cols="40" placeholder="Briefly describe the internship tasks..."></textarea>
        <br><br>
                
        <button type="submit" style="padding: 10px 20px;">Add Student & Internship</button>
        <a href="../Databases/StudentDatabase.php">Cancel</a>
    </form>
</body>
</html>