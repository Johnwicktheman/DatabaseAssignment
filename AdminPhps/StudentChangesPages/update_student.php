<?php
include '../../session.php';
include '../../connection.php';
include '../../ExecutePStatements.php';
include '../AdminFunction.php';

if (!isLoggedIn() || getCurrentUser()['role'] !== 'admin') {
    header("Location: ../../login.php");
    exit;
}

$message = "";
$error = "";

if ($_SERVER["REQUEST_METHOD"] == "POST") {
    $student_id      = $_POST['id'];
    $username        = $_POST['username'];
    $password        = $_POST['password'];
    $fname           = $_POST['fname'];
    $lname           = $_POST['lname'];
    $programme_code  = $_POST['programme_code'];
    $internship_code = $_POST['internship_code'];
    $lecturerid      = $_POST['lecturer_id'];
    $supervisorid    = $_POST['supervisor_id'];

    $lecturerid   = !empty($_POST['lecturer_id']) ? $_POST['lecturer_id'] : null;
    $supervisorid = !empty($_POST['supervisor_id']) ? $_POST['supervisor_id'] : null;

    if (empty($username) || empty($password) || empty($fname) || empty($lname) || empty($programme_code)) {
        $error = "All fields except assessors are required.";
    } else {
        // Check if username already exists for OTHER users
        $check = executePreparedStatement("SELECT StudentAccountID FROM studentaccountlist WHERE Username = ? AND StudentAccountID != ?", [$username, $student_id]);
        
        if ($check->num_rows > 0) {
            $error = "Username already exists.";
        } else {
            // Use a transaction to update both tables
            $conn->begin_transaction();
            try {
                // 1. Update Account List
                updateStudents($student_id, $username, $password);

                // 2. Update Profile List
                $sql_profile = "UPDATE studentprofile SET 
                                FirstName = ?, LastName = ?, ProgrammeCode = ?, 
                                InternshipCode = ?, AssesorAccountIDLect = ?, AssesorAccountIDSuper = ? 
                                WHERE StudentAccountID = ?";
                $params_profile = [$fname, $lname, $programme_code, $internship_code, $lecturerid, $supervisorid, $student_id];
                executePreparedStatement($sql_profile, $params_profile);

                $conn->commit();
                header("Location: ../StudentDB.php");
                exit;
            } catch (Exception $e) {
                $conn->rollback();
                $error = "Update failed: " . $e->getMessage();
            }
        }
    }
} else {
    // GET REQUEST: Load existing data
    $student_id = $_GET['id'];
    $sql = "SELECT sa.*, sp.* FROM studentaccountlist sa 
            JOIN studentprofile sp ON sa.StudentAccountID = sp.StudentAccountID 
            WHERE sa.StudentAccountID = ?";
    $result = executePreparedStatement($sql, [$student_id]);

    if ($result->num_rows > 0) {
        $row             = $result->fetch_assoc();
        $username        = $row['Username'];
        $password        = $row['Password'];
        $fname           = $row['FirstName'];
        $lname           = $row['LastName'];
        $programme_code  = $row['ProgrammeCode'];
        $internship_code = $row['InternshipCode'];
        $lecturerid      = $row['AssesorAccountIDLect'];
        $supervisorid    = $row['AssesorAccountIDSuper'];
    } else {
        echo "Student profile not found.";
        exit;
    }
}

// Fetch data for dropdowns
$lecturers   = getAssessorsByType('Lecturer');
$supervisors = getAssessorsByType('Supervisor');
$internships = getInternships();
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Update Student</title>
    <link rel="stylesheet" href="../../CssFiles/update.css">
</head>
<body>
    <div class="HeaderBar">
  
        <div class="HeaderImage">
            <img src="../../Assets/UniLogoBlack.png" style="width:200px; height: auto; margin: 20px 40px;">
        </div>
        
        <div class="HeaderTitle">
            <p>Update Student</p>
        </div>

        <div class="HeaderTitle">
            
            <label for="navToggle" class="navToggleLabel"><img src="../../Assets/ThreeDash.png" style="width: 50px; height: auto; margin: 20px 40px;"></label>
        </div>
        
    </div>
    <input type="checkbox" id="navToggle" class="navToggle">
    <label for="navToggle" class="overlay"></label>
    <div class="sidebar">
        <ul>
            <li><a href="../AdminDashboard.php" class="SideBarContent">DashBoard</a></li>
            <li><a href="../../logout.php" class="SideBarContent">Log Out</a></li>
        </ul>
    </div>
   <div class="MainWrapper">
    <div class="FormCard">
        <div class="FormHeader">
            <h2>Update Student Profile</h2>
            
        </div>

        <?php if ($error): ?>
            <div class="error-box"><?= $error; ?></div>
        <?php endif; ?>

        <form method="post" action="<?= htmlspecialchars($_SERVER['PHP_SELF'] . "?id=" . $student_id); ?>">
            <input type="hidden" name="id" value="<?= $student_id; ?>">

            <div class="InputSection">
                <h3>Account Credentials</h3>
                <div class="field">
                    <label>Username</label>
                    <input type="text" name="username" value="<?= $username; ?>" required>
                </div>
                <div class="field">
                    <label>Password</label>
                    <input type="text" name="password" value="<?= $password; ?>" required>
                </div>
            </div>

            <div class="InputSection">
                <h3>Personal Information</h3>
                <div class="row">
                    <div class="field">
                        <label>First Name</label>
                        <input type="text" name="fname" value="<?= $fname; ?>" required>
                    </div>
                    <div class="field">
                        <label>Last Name</label>
                        <input type="text" name="lname" value="<?= $lname; ?>" required>
                    </div>
                </div>
                <div class="field">
                    <label>Programme Code</label>
                    <input type="text" name="programme_code" value="<?= $programme_code; ?>" required>
                </div>
            </div>

            <div class="InputSection">
                <h3>Assignments</h3>
                <div class="field">
                    <label>Internship Placement</label>
                    <select name="internship_code" required>
                        <?php while($row = $internships->fetch_assoc()): ?>
                            <option value="<?= $row['InternshipCode'] ?>" <?= ($row['InternshipCode'] == $internship_code) ? 'selected' : '' ?>>
                                <?= $row['InternshipCode'] ?> - <?= $row['CompanyName'] ?>
                            </option>
                        <?php endwhile; ?>
                    </select>
                </div>
                
                <div class="row">
                    <div class="field">
                        <label>Lecturer</label>
                        <select name="lecturer_id">
                            <option value="">-- None --</option>
                            <?php $lecturers->data_seek(0); ?>
                            <?php while($row = $lecturers->fetch_assoc()): ?>
                                <option value="<?= $row['AssessorAccountID'] ?>" <?= ($row['AssessorAccountID'] == $lecturerid) ? 'selected' : '' ?>>
                                    <?= $row['Username'] ?>
                                </option>
                            <?php endwhile; ?>
                        </select>
                    </div>
                    <div class="field">
                        <label>Supervisor</label>
                        <select name="supervisor_id">
                            <option value="">-- None --</option>
                            <?php $supervisors->data_seek(0); ?>
                            <?php while($row = $supervisors->fetch_assoc()): ?>
                                <option value="<?= $row['AssessorAccountID'] ?>" <?= ($row['AssessorAccountID'] == $supervisorid) ? 'selected' : '' ?>>
                                    <?= $row['Username'] ?>
                                </option>
                            <?php endwhile; ?>
                        </select>
                    </div>
                </div>
            </div>

            <div class="FormActions">
                <input type="submit" value="Save Changes" class="Btn-Submit">
                <a href="../StudentDB.php" class="Btn-Cancel">Cancel</a>
            </div>
        </form>
    </div>
</div>

</html>