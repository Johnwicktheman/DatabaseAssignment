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
    $resStudent  = executePreparedStatement("SELECT Username FROM studentaccountlist WHERE Username = ? ", [$user]); // Assuming 3 is the UserTypeID for students
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
    <link rel="stylesheet" href="../../CssFiles/Add_Edit.css">

</head>
<div class="container">

    <h1 class="page-title">Add New Student</h1>
    <p class="subtitle">Create a new student account and internship record.</p>

    <?php if ($error) echo "<div class='error'>$error</div>"; ?>

    <div class="form-card">

        <form action="" method="post">

            <!-- Login Credentials -->
            <h2 class="section-title">1. Login Credentials</h2>

            <div class="form-grid">

                <div class="form-group">
                    <label>Username</label>
                    <input type="text" name="username" required>
                </div>

                <div class="form-group">
                    <label>Password</label>
                    <input type="password" name="password" required>
                </div>

            </div>


            <!-- Student Profile -->
            <h2 class="section-title">2. Student Profile</h2>

            <div class="form-grid">

                <div class="form-group">
                    <label>First Name</label>
                    <input type="text" name="firstname" required>
                </div>

                <div class="form-group">
                    <label>Last Name</label>
                    <input type="text" name="lastname" required>
                </div>

                <div class="form-group">
                    <label>Programme Code</label>
                    <input type="text" name="programme" placeholder="e.g. CS101" required>
                </div>

                <div class="form-group">
                    <label>Year of Study</label>
                    <input type="number" name="year" min="1" max="4" required>
                </div>

                <div class="form-group">
                    <label>Assign Lecturer</label>
                    <select name="lecturer">

                        <option value="NULL">-- No Lecturer Assigned --</option>

                        <?php while($l = $lecturers->fetch_assoc()): ?>
                            <option value="<?php echo $l['AssessorAccountID']; ?>">
                                <?php echo $l['Username']; ?>
                            </option>
                        <?php endwhile; ?>

                    </select>
                </div>

                <div class="form-group">
                    <label>Assign Supervisor</label>

                    <select name="supervisor">

                        <option value="NULL">-- No Supervisor Assigned --</option>

                        <?php while($s = $supervisors->fetch_assoc()): ?>
                            <option value="<?php echo $s['AssessorAccountID']; ?>">
                                <?php echo $s['Username']; ?>
                            </option>
                        <?php endwhile; ?>

                    </select>
                </div>

            </div>


            <!-- Internship Information -->
            <h2 class="section-title">3. Internship Information</h2>

            <div class="form-grid">

                <div class="form-group">
                    <label>Company</label>

                    <select name="company_id" required>

                        <option value="">-- Select Company --</option>

                        <?php
                        $compRes = $conn->query("SELECT * FROM companynamelist");

                        while($comp = $compRes->fetch_assoc()) {
                            echo "<option value='".$comp['CompanyInt']."'>"
                                . $comp['CompanyName'] .
                                "</option>";
                        }
                        ?>

                    </select>
                </div>

                <div class="form-group">
                    <label>Role</label>
                    <input type="text" name="role" placeholder="e.g. Software Intern" required>
                </div>

                <div class="form-group">
                    <label>Duration (Months)</label>
                    <input type="number" name="duration" min="1" required>
                </div>

                <div class="form-group full-width">
                    <label>Description</label>

                    <textarea 
                        name="description"
                        rows="5"
                        placeholder="Briefly describe the internship tasks..."
                    ></textarea>
                </div>

            </div>


            <div class="button-group">

                <button type="submit" class="btn btn-primary">
                    Add Student
                </button>

                <a href="../Databases/StudentDatabase.php" class="btn btn-secondary">
                    Cancel
                </a>

            </div>

        </form>

    </div>

</div>
</body>
</html>