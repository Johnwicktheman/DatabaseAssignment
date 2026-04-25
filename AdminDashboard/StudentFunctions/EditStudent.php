
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
    $companyID = empty($_POST['company_id']) ? null : $_POST['company_id'];
    $role = empty($_POST['role']) ? null : $_POST['role'];
    $duration = empty($_POST['duration']) ? null : $_POST['duration'];
    $description = empty($_POST['description']) ? null : $_POST['description'];

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

            //Update Internship Record using StudentAccountID but check whether they got internship or not first
            //because once company name deleted the internship also deleted so need reassginment
            $checkIntern = executePreparedStatement("SELECT StudentAccountID FROM internship WHERE StudentAccountID = ?", [$studentID]);
            
            if ($checkIntern->num_rows > 0) {
                //Record exists then update
                $sqlIntern = "UPDATE internship SET CompanyINT = ?, Role = ?, Months_duration = ?, Description = ? WHERE StudentAccountID = ?";
                executePreparedStatement($sqlIntern, [$companyID, $role, $duration, $description, $studentID]); 
            } else if ($companyID) {
                //No record exists then insert
                $sqlIntern = "INSERT INTO internship (CompanyINT, Role, Months_duration, Description, StudentAccountID) VALUES (?, ?, ?, ?, ?)";
                executePreparedStatement($sqlIntern, [$companyID, $role, $duration, $description, $studentID]);
            }

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
    <title>Edit Student Accounts</title>

    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
    <link href="https://fonts.googleapis.com/css2?family=Bricolage+Grotesque:opsz,wght@12..96,200..800&display=swap" rel="stylesheet">

    <link rel="stylesheet" href="../../CssFiles/Add_Edit.css">

</head>
<body>

<div class="container">

    <h1 class="page-title">
        Edit Student: 
        <?php echo $studentData['FirstName'] . " " . $studentData['LastName']; ?>
    </h1>

    <p class="subtitle">
        Update student account and internship information.
    </p>

    <?php if ($error): ?>
        <div class="error"><?php echo $error; ?></div>
    <?php endif; ?>

    <div class="form-card">

        <form action="" method="post">

            <input type="hidden" name="id" value="<?php echo $studentID; ?>">
            <h2 class="section-title">Login Credentials</h2>

            <div class="form-grid">

                <div class="form-group">
                    <label>Username</label>
                    <input type="text" name="username" value="<?php echo $studentData['Username']; ?>"  required>
                </div>

                <div class="form-group">
                    <label>Password</label>
                    <input type="password" name="password" value="<?php echo $studentData['Password']; ?>" required>
                </div>

            </div>


            <h2 class="section-title">Student Profile</h2>

            <div class="form-grid">

                <div class="form-group">
                    <label>First Name</label>
                    <input type="text" name="firstname" value="<?php echo $studentData['FirstName']; ?>" required>
                </div>

                <div class="form-group">
                    <label>Last Name</label>
                    <input type="text" name="lastname" value="<?php echo $studentData['LastName']; ?>"required>
                </div>

                <div class="form-group">
                    <label>Programme</label>
                    <input type="text" name="programme" value="<?php echo $studentData['ProgrammeCode']; ?>" required>
                </div>

                <div class="form-group">
                    <label>Year of Study</label>
                    <input type="number" name="year" value="<?php echo $studentData['YearOfStudy']; ?>" min="1" max="4" required>
                </div>

                <div class="form-group">
                    <label>Lecturer</label>
                    <select name="lecturer">

                        <option value="NULL">-- Unassigned --</option>

                        <?php while($l = $lecturers->fetch_assoc()): ?>
                            <option 
                                value="<?php echo $l['AssessorAccountID']; ?>"
                                <?php if($studentData['AssesorAccountIDLect'] == $l['AssessorAccountID']) echo 'selected'; ?>
                            >
                                <?php echo $l['Username']; ?>
                            </option>
                        <?php endwhile; ?>

                    </select>
                </div>

                <div class="form-group">
                    <label>Supervisor</label>

                    <select name="supervisor">
                        <option value="NULL">-- Unassigned --</option>

                        <?php while($s = $supervisors->fetch_assoc()): ?>
                            <option 
                                value="<?php echo $s['AssessorAccountID']; ?>"
                                <?php if($studentData['AssesorAccountIDSuper'] == $s['AssessorAccountID']) echo 'selected'; ?>
                            >
                                <?php echo $s['Username']; ?>
                            </option>
                        <?php endwhile; ?>

                    </select>
                </div>

            </div>



            <h2 class="section-title">Internship Details</h2>

            <div class="form-grid">

                <div class="form-group">
                    <label>Company</label>

                    <select name="company_id" required>
                        <option value="">-- Select Company --</option>

                        <?php while($c = $companies->fetch_assoc()): ?>
                            <option 
                                value="<?php echo $c['CompanyInt']; ?>"
                                <?php if($studentData['CompanyINT'] == $c['CompanyInt']) echo 'selected'; ?>
                            >
                                <?php echo $c['CompanyName']; ?>
                            </option>
                        <?php endwhile; ?>
                    </select>
                </div>

                <div class="form-group">
                    <label>Role</label>
                    <input type="text" name="role" value="<?php echo $studentData['Role'] ?? ''; ?>" required>
                </div>

                <div class="form-group">
                    <label>Duration (Months)</label>
                    <input type="number" name="duration"  value="<?php echo $studentData['Months_duration'] ?? ''; ?>" required>
                </div>

                <div class="form-group full-width">
                    <label>Description</label>
                    <textarea name="description" rows="5" ><?php echo $studentData['Description'] ?? ''; ?></textarea>
                </div>

            </div>


            <div class="button-group">
                <button type="submit" class="btn btn-primary"> Save All Changes </button>
                <a href="../Databases/StudentDatabase.php" class="btn btn-secondary"> Cancel</a>
            </div>

        </form>

    </div>

</div>

</body>
</html>