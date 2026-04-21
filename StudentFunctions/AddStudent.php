<?php
ini_set('display_errors', 1);
ini_set('display_startup_errors', 1);
error_reporting(E_ALL);
session_start();
include '../Connection.php';
include '../ExecutePStatement.php';

//see if they are loggged in and if they are admin or not
if (!isset($_SESSION['username']) || $_SESSION['user_role'] !== 'Admin') {
    header("Location: ../FrontPage.php"); 
    exit();
}


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
    $checkSql = "SELECT * FROM studentaccountlist WHERE Username = ?";
    $checkRes = executePreparedStatement($checkSql, [$user]);

    if ($checkRes->num_rows > 0) {
        $error = "Username already exists. Please choose a different one."; 
    } else {
        //if ok continue insert student account list
        $adminID = $_SESSION['user_id'];
        $insertAccSql = "INSERT INTO studentaccountlist (Username, Password, AdminAccountID) VALUES (?, ?, ?)";
        $insertAccRes = executePreparedStatement($insertAccSql, [$user, $pass, $AdminID]);

        if ($insertAccRes) {
            //if ok get ID of this new account to put it inside student profile
            //insert_id is primary key and auto increment
            $newStudentID = $conn->insert_id;

            //insert into student profile
            $insertProfSql = "INSERT INTO studentprofile 
                (StudentAccountID, FirstName, LastName, ProgrammeCode, YearOfStudy, InternshipCode, AssesorAccountIDLect, AssesorAccountIDSuper) 
                VALUES (?, ?, ?, ?, ?, ?, ?, ?)";
            
            $insertProfRes = executePreparedStatement($insertProfSql, [
                $newStudentID, $fname, $lname, $programme, $year, $internCode, $lectID, $superID
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
    
    <p>Add New Assessor</p>
   <form action="" method="post">
        <h3>Login Credentials</h3>
        <label>Username:</label>
        <input type="text" name="username" required><br><br>
        
        <label>Password:</label>
        <input type="password" name="password" required><br><br>

        <hr>
        <h3>Student Profile</h3>
        <label>First Name:</label>
        <input type="text" name="firstname" required><br><br>

        <label>Last Name:</label>
        <input type="text" name="lastname" required><br><br>

        <label>Programme Code:</label>
        <input type="text" name="programme" placeholder="e.g. CS101" required><br><br>

        <label>Year of Study:</label>
        <input type="number" name="year" min="1" max="4" required><br><br>



        <label for="Internship">Assign Internship:</label>
        <select name="InternshipCode" id="internship">
            <option value="NULL">-- No Internship Assigned --</option>
            <?php while($internship = $internships->fetch_assoc()): ?>
                <option value="<?php echo $internship['InternshipCode']; ?>">
                    <?php echo $internship['InternshipCode']; ?>
                </option>
            <?php endwhile; ?>
        </select>

        <label for="lecturer">Assign Lecturer:</label>
        <select name="lecturer" id="lecturer">
            <option value="NULL">-- No Lecturer Assigned --</option>
            <?php while($lect = $lecturers->fetch_assoc()): ?>
                <option value="<?php echo $lect['AssessorAccountID']; ?>">
                    <?php echo $lect['Username']; ?>
                </option>
            <?php endwhile; ?>
        </select>

        <br><br>

        <label for="supervisor">Assign Supervisor:</label>
        <select name="supervisor" id="supervisor">
            <option value="NULL">-- No Supervisor Assigned --</option>
            <?php while($super = $supervisors->fetch_assoc()): ?>
                <option value="<?php echo $super['AssessorAccountID']; ?>">
                    <?php echo $super['Username']; ?>
                </option>
            <?php endwhile; ?>
        </select>
        

        <label>Submit</label>
        <input type="submit" value="Add Student">
        <a href="../Databases/StudentDatabase.php">Cancel</a>
    </form>
</body>
</html>