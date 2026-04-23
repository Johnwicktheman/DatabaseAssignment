
<?php
ini_set('display_errors', 1);
ini_set('display_startup_errors', 1);
error_reporting(E_ALL);
session_start();

include '../../Connection.php';
include '../../ExecutePStatement.php';
include '../../AllFunctions.php';

checkAccess('Admin');

$studentID = $_GET['id'] ?? $_POST['id'] ?? null;

if (!$studentID) {
    header("Location: ../Databases/StudentDatabase.php");
    exit();
}

//Get data for prefill form
$fetchSql = "SELECT acc.Username, acc.Password, prof.*, 
                    intern.CompanyINT, intern.Role, intern.Months_duration, intern.Description
             FROM studentaccountlist acc
             JOIN studentprofile prof ON acc.StudentAccountID = prof.StudentAccountID 
             LEFT JOIN internship intern ON acc.StudentAccountID = intern.StudentAccountID
             WHERE acc.StudentAccountID = ?";

$fetchResult = executePreparedStatement($fetchSql, [$studentID]);

if (!$fetchResult || $fetchResult->num_rows === 0) {
    header("Location: ../Databases/StudentDatabase.php");
    exit();
}
$studentData = $fetchResult->fetch_assoc();

//Prepare Dropdowns
$lecturers = executePreparedStatement("SELECT AssessorAccountID, Username FROM assesoraccountlist WHERE AssesorType = ?", ['Lecturer']);
$supervisors = executePreparedStatement("SELECT AssessorAccountID, Username FROM assesoraccountlist WHERE AssesorType = ?", ['Supervisor']);
$companies = $conn->query("SELECT * FROM companynamelist");

$error = null;

//If submit then what happens
if ($_SERVER["REQUEST_METHOD"] == "POST") {
    $user = $_POST['username'];
    $pass = $_POST['password'];
    $fname = $_POST['firstname'];
    $lname = $_POST['lastname'];
    $programme = $_POST['programme'];
    $year = $_POST['year'];
    
    $lectID = ($_POST['lecturer'] === "NULL") ? null : $_POST['lecturer'];
    $superID = ($_POST['supervisor'] === "NULL") ? null : $_POST['supervisor'];

    //Internship Details
    $companyID = $_POST['company_id'];
    $role = $_POST['role'];
    $duration = $_POST['duration'];
    $description = $_POST['description'];

    //Username check
    $resAssessor = executePreparedStatement("SELECT Username FROM assesoraccountlist WHERE Username = ?", [$user]);
    $resStudent  = executePreparedStatement("SELECT Username FROM studentaccountlist WHERE Username = ? AND StudentAccountID != ?", [$user, $studentID]);
    $resAdmin    = executePreparedStatement("SELECT Username FROM adminaccountlist WHERE Username = ?", [$user]);

    //Determine the specific error message
    if ($resAssessor->num_rows > 0) {
        $error = "Username is already taken by an Assessor (Lecturer/Supervisor).";
    } else if ($resStudent->num_rows > 0) {
        $error = "Username is already taken by another Student.";
    } else if ($resAdmin->num_rows > 0) {
        $error = "Username is already taken by an Admin.";
    } else {
        $conn->begin_transaction();

        try {
            //Update Account List
            $sqlAcc = "UPDATE studentaccountlist SET Username = ?, Password = ? WHERE StudentAccountID = ?";
            executePreparedStatement($sqlAcc, [$user, $pass, $studentID]);

            //Update Internship Record using StudentAccountID
            $sqlIntern = "UPDATE internship SET CompanyINT = ?, Role = ?, Months_duration = ?, Description = ? WHERE StudentAccountID = ?";
            executePreparedStatement($sqlIntern, [$companyID, $role, $duration, $description, $studentID]);

            //Update Student Profile
            $sqlProf = "UPDATE studentprofile SET 
                        FirstName = ?, LastName = ?, ProgrammeCode = ?, YearOfStudy = ?, 
                        AssesorAccountIDLect = ?, AssesorAccountIDSuper = ? 
                        WHERE StudentAccountID = ?";
            executePreparedStatement($sqlProf, [$fname, $lname, $programme, $year, $lectID, $superID, $studentID]);

            $conn->commit();
            header("Location: ../Databases/StudentDatabase.php?msg=UpdateSuccess");
            exit();

        } catch (Exception $e) {
            $conn->rollback();
            $error = "Update failed: " . $e->getMessage();
        }
    }
}
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <title>Update Student Record</title>
    <style>
        body { font-family: sans-serif; line-height: 1.6; padding: 20px; }
        .form-section { border: 1px solid #ccc; padding: 15px; margin-bottom: 20px; border-radius: 5px; }
        .error { color: red; font-weight: bold; }
        label { display: inline-block; width: 150px; margin-bottom: 10px; }
    </style>
</head>
<body>

    <h2>Update Student: <?php echo htmlspecialchars($studentData['FirstName'] . " " . $studentData['LastName']); ?></h2>

    <?php if ($error): ?>
        <p class="error"><?php echo $error; ?></p>
    <?php endif; ?>

    <form action="" method="post">
        <input type="hidden" name="id" value="<?php echo $studentID; ?>">

        <div class="form-section">
            <h3>1. Login Credentials</h3>
            <label>Username:</label>
            <input type="text" name="username" value="<?php echo htmlspecialchars($studentData['Username']); ?>" required><br>
            
            <label>Password:</label>
            <input type="password" name="password" value="<?php echo htmlspecialchars($studentData['Password']); ?>" required>
        </div>

        <div class="form-section">
            <h3>2. Student Profile</h3>
            <label>First Name:</label>
            <input type="text" name="firstname" value="<?php echo htmlspecialchars($studentData['FirstName']); ?>" required><br>

            <label>Last Name:</label>
            <input type="text" name="lastname" value="<?php echo htmlspecialchars($studentData['LastName']); ?>" required><br>

            <label>Programme:</label>
            <input type="text" name="programme" value="<?php echo htmlspecialchars($studentData['ProgrammeCode']); ?>" required><br>

            <label>Year of Study:</label>
            <input type="number" name="year" value="<?php echo htmlspecialchars($studentData['YearOfStudy']); ?>" min="1" max="4" required><br>

            <label>Lecturer:</label>
            <select name="lecturer">
                <option value="NULL">-- Unassigned --</option>
                <?php while($l = $lecturers->fetch_assoc()): ?>
                    <option value="<?php echo $l['AssessorAccountID']; ?>" <?php if($studentData['AssesorAccountIDLect'] == $l['AssessorAccountID']) echo 'selected'; ?>>
                        <?php echo htmlspecialchars($l['Username']); ?>
                    </option>
                <?php endwhile; ?>
            </select><br>

            <label>Supervisor:</label>
            <select name="supervisor">
                <option value="NULL">-- Unassigned --</option>
                <?php while($s = $supervisors->fetch_assoc()): ?>
                    <option value="<?php echo $s['AssessorAccountID']; ?>" <?php if($studentData['AssesorAccountIDSuper'] == $s['AssessorAccountID']) echo 'selected'; ?>>
                        <?php echo htmlspecialchars($s['Username']); ?>
                    </option>
                <?php endwhile; ?>
            </select>
        </div>

        <div class="form-section">
            <h3>3. Internship Details</h3>
            <label>Company:</label>
            <select name="company_id" required>
                <option value="">-- Select Company --</option>
                <?php while($c = $companies->fetch_assoc()): ?>
                    <option value="<?php echo $c['CompanyInt']; ?>" <?php if($studentData['CompanyINT'] == $c['CompanyInt']) echo 'selected'; ?>>
                        <?php echo htmlspecialchars($c['CompanyName']); ?>
                    </option>
                <?php endwhile; ?>
            </select><br>

            <label>Role:</label>
            <input type="text" name="role" value="<?php echo htmlspecialchars($studentData['Role'] ?? ''); ?>" required><br>

            <label>Duration (Months):</label>
            <input type="number" name="duration" value="<?php echo htmlspecialchars($studentData['Months_duration'] ?? ''); ?>" required><br>

            <label style="vertical-align: top;">Description:</label>
            <textarea name="description" rows="4" cols="40"><?php echo htmlspecialchars($studentData['Description'] ?? ''); ?></textarea>
        </div>

        <div style="margin-top: 20px;">
            <button type="submit" style="padding: 10px 20px; cursor: pointer;">Save All Changes</button>
            <a href="../Databases/StudentDatabase.php" style="margin-left: 10px;">Cancel</a>
        </div>
    </form>

</body>
</html>