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
    $username = $_POST['username'];
    $password = $_POST['password'];
    $fname = $_POST['fname'];
    $lname = $_POST['lname'];
    $lecturerid = $_POST['lecturer_id'];
    $supervisorid = $_POST['supervisor_id'];
    $internship_code = $_POST['internship_code'];
    $programme_code = $_POST['programme_code'];
    

    if (empty($username) || empty($password)|| empty($fname) || empty($lname) || empty($internship_code)|| empty($programme_code)) {
        $error = "All fields are required.";
    } else {
        // Check if username already exists
        $check = executePreparedStatement(
            "SELECT StudentAccountID FROM studentaccountlist WHERE Username = ?",
            [$username]
        );

        if ($check->num_rows > 0) {
            $error = "Username already exists.";
        } else {
            $result = CreateStudents($username, $password, $fname, $lname, $lecturerid, $supervisorid, $internship_code, $programme_code);

            if ($result) {
                header("Location: ../StudentDB.php");
                exit;
            } else {
                $error = "Failed to create student: ";
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
    <title>Add Assessor</title>
</head>
<body>
    <h2>Add New Student</h2>

    <?php if ($message): ?>
        <p style="color:green"><?php echo $message; ?></p>
    <?php endif; ?>

    <?php if ($error): ?>
        <p style="color:red"><?php echo $error; ?></p>
    <?php endif; ?>
    <?php
        $lecturers = getAssessorsByType('Lecturer');
        $supervisors = getAssessorsByType('Supervisor');
        $internships = getInternships();
    ?>
    

    <form method="post" action="">
    <label>First Name:</label> <input type="text" name="fname" required><br>
    <label>Last Name:</label> <input type="text" name="lname" required><br>
    <label>Username:</label> <input type="text" name="username" required><br>
    <label>Password:</label> <input type="password" name="password" required><br>

    <label for="programme_code">Programme Code:</label> 
    <input type="text" name="programme_code" id="programme_code" placeholder="e.g. MPU3302" required><br><br>
    <label>Assign Lecturer:</label>
    <select name="lecturer_id">
        <option value="">-- None --</option>
        <?php while($row = $lecturers->fetch_assoc()): ?>
            <option value="<?= $row['AssessorAccountID'] ?>"><?= $row['Username'] ?></option>
        <?php endwhile; ?>
    </select><br>

    <label>Assign Supervisor:</label>
    <select name="supervisor_id">
        <option value="">-- None --</option>
        <?php while($row = $supervisors->fetch_assoc()): ?>
            <option value="<?= $row['AssessorAccountID'] ?>"><?= $row['Username'] ?></option>
        <?php endwhile; ?>
    </select><br>

    <label>Assign Internship:</label>
    <select name="internship_code" required>
        <option value="">-- Select Internship --</option>
        <?php while($row = $internships->fetch_assoc()): ?>
            <option value="<?= $row['InternshipCode'] ?>">
                <?= $row['InternshipCode'] ?> - <?= $row['CompanyName'] ?>
            </option>
        <?php endwhile; ?>
    </select><br><br>

    <input type="submit" value="Create Student and Profile">
</form>
</body>
</html>