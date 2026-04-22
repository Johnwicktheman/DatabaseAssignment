<?php
ini_set('display_errors', 1);
ini_set('display_startup_errors', 1);
error_reporting(E_ALL);
session_start();
include '../../Connection.php';
include '../../ExecutePStatement.php';
include '../../AllFunctions.php';

//see if they are loggged in and if they are admin or not
checkAccess('Admin');


$studentID = $_GET['id'] ?? $_POST['id'] ?? null;

if (!$studentID) {
    header("Location: ../Databases/StudentDatabase.php");
    exit();
}

$fetchSql = "SELECT studentaccountlist.Username, studentaccountlist.Password, studentprofile.* FROM studentaccountlist 
             JOIN studentprofile ON studentaccountlist.StudentAccountID = studentprofile.StudentAccountID 
             WHERE studentaccountlist.StudentAccountID = ?";

/*$fetchSql = "SELECT studentaccountlist.Username, studentaccountlist.Password, studentprofile.* FROM studentaccountlist, studentprofile 
            WHERE studentaccountlist.StudentAccountID = studentprofile.StudentAccountID 
            AND studentaccountlist.StudentAccountID = ?*/


$fetchResult = executePreparedStatement($fetchSql, [$studentID]);

if (!$fetchResult || $fetchResult->num_rows === 0) {
    header("Location: ../Databases/StudentDatabase.php");
    exit();
}
$studentData = $fetchResult->fetch_assoc();


//Get all lecturer and supervisor for dropdown list
$lecturerSql = "SELECT AssessorAccountID, Username FROM assesoraccountlist WHERE AssesorType = ?";
$lecturers = executePreparedStatement($lecturerSql, ['Lecturer']);

// Fetch all Supervisors
$supervisorSql = "SELECT AssessorAccountID, Username FROM assesoraccountlist WHERE AssesorType = ?";
$supervisors = executePreparedStatement($supervisorSql, ['Supervisor']);

$internshipSql = "SELECT InternshipCode FROM internship";
$internships = executePreparedStatement($internshipSql, []);

//error text
$error = null;
//After they press submit button what happens
if ($_SERVER["REQUEST_METHOD"] == "POST") {

    $user = $_POST['username'];
    $pass = $_POST['password'];
    $AdminID = $_SESSION['user_id'];
    $fname = $_POST['firstname'];
    $lname = $_POST['lastname'];
    $programme = $_POST['programme'];
    $year = $_POST['year'];
    $internCode = $_POST['InternshipCode'];
    $lectID = $_POST['lecturer'];
    $superID = $_POST['supervisor'];

    //if NULL change it into "NULL" string for sql
    $internCode = ($internCode === "NULL") ? null : $internCode;
    $lectID = ($lectID === "NULL") ? null : $lectID;
    $superID = ($superID === "NULL") ? null : $superID;

    //cannot have same username for studentaccount
    $checkSql = "SELECT * FROM studentaccountlist WHERE Username = ? AND StudentAccountID != ?";
    $checkRes = executePreparedStatement($checkSql, [$user, $studentID]);

    if ($checkRes->num_rows > 0) {
        $error = "Username already exists. Please choose a different one."; 
    } else {
        //if ok continue insert student account list
        $adminID = $_SESSION['user_id'];
        $insertAccSql = "UPDATE studentaccountlist SET Username = ?, Password = ?, AdminAccountID = ? WHERE StudentAccountID = ?";
        $insertAccRes = executePreparedStatement($insertAccSql, [$user, $pass, $AdminID, $studentID]);

        if ($insertAccRes) {
            //if ok get ID of this new account to put it inside student profile

            //Update student profile
            $insertProfSql = "UPDATE studentprofile SET FirstName = ?, LastName = ?, ProgrammeCode = ?, YearOfStudy = ?, InternshipCode = ?, AssesorAccountIDLect = ?, AssesorAccountIDSuper = ? WHERE StudentAccountID = ?";
            
            $insertProfRes = executePreparedStatement($insertProfSql, [
                $fname, $lname, $programme, $year, $internCode, $lectID, $superID, $studentID
            ]);

            if ($insertProfRes) {
                header("Location: ../Databases/StudentDatabase.php");
                exit();
            } else {
                $error = "Account created, but profile failed to save.";
            }
        }
    }
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
    <?php 
        if ($error !=null){
            echo $error;
        }
    ?>
    

   <form action="" method="post">
        <input type="hidden" name="id" value="<?php echo $studentID; ?>">
        <h3>Login Credentials</h3>
        <label>Username:</label>
        <input type="text" name="username" value="<?php echo htmlspecialchars($studentData['Username']); ?>" required><br><br>
        
        <label>Password:</label>
        <input type="password" name="password" value="<?php echo htmlspecialchars($studentData['Password']); ?>" required><br><br>

        <hr>
        <h3>Student Profile</h3>
        <label>First Name:</label>
        <input type="text" name="firstname" value="<?php echo htmlspecialchars($studentData['FirstName']); ?>" required><br><br>

        <label>Last Name:</label>
        <input type="text" name="lastname" value="<?php echo htmlspecialchars($studentData['LastName']); ?>" required><br><br>

        <label>Programme Code:</label>
        <input type="text" name="programme" value="<?php echo htmlspecialchars($studentData['ProgrammeCode']); ?>" placeholder="e.g. CS101" required><br><br>

        <label>Year of Study:</label>
        <input type="number" name="year" value="<?php echo htmlspecialchars($studentData['YearOfStudy']); ?>" min="1" max="4" required><br><br>



        <!--So loop through the whole internship table and fecth each row "internship code"
        if studentdata internship code same as this row internship code then echo selected and then echo that code-->
        <label for="Internship">Assign Internship:</label>
            <select name="InternshipCode" id="internship">
                <option value="NULL">-- No Internship Assigned --</option>
                <?php while($internship = $internships->fetch_assoc()): ?>
                    <option value="<?php echo $internship['InternshipCode']; ?>" 
                        <?php if($studentData['InternshipCode'] == $internship['InternshipCode']) echo 'selected'; ?>>
                        <?php echo $internship['InternshipCode']; ?>
                    </option>
                <?php endwhile; ?>
        </select>

        //same as above logic
        <label for="lecturer">Assign Lecturer:</label>
        <select name="lecturer" id="lecturer">
            <option value="NULL">-- No Lecturer Assigned --</option>
            <?php while($lect = $lecturers->fetch_assoc()): ?>
                <option value="<?php echo $lect['AssessorAccountID']; ?>" 
                    <?php if($studentData['AssesorAccountIDLect'] == $lect['AssessorAccountID']) echo 'selected'; ?>>
                    <?php echo $lect['Username']; ?>
                </option>
            <?php endwhile; ?>
        </select>

        <br><br>

        <label for="supervisor">Assign Supervisor:</label>
        <select name="supervisor" id="supervisor">
            <option value="NULL">-- No Supervisor Assigned --</option>
            <?php while($super = $supervisors->fetch_assoc()): ?>
                <option value="<?php echo $super['AssessorAccountID']; ?>" 
                    <?php if($studentData['AssesorAccountIDSuper'] == $super['AssessorAccountID']) echo 'selected'; ?>>
                    <?php echo $super['Username']; ?>
                </option>
            <?php endwhile; ?>
        </select>
            

        <label>Submit</label>
        <input type="submit" value="Update Student">
        <a href="../Databases/StudentDatabase.php">Cancel</a>
    </form>
</body>
</html>