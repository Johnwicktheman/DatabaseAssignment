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
    $assessor_id = $_POST['id'];
    $username    = $_POST['username'];
    $password    = $_POST['password'];
    $type        = $_POST['assessor_type'];

    if (empty($username) || empty($password)) {
        $error = "All fields are required.";
    } else {
        $check = executePreparedStatement("SELECT AssessorAccountID FROM assesoraccountlist WHERE Username = ? AND AssessorAccountID != ?",[$username, $assessor_id]);
        if ($check->num_rows > 0) {
            $error = "Username already exists.";
        } else {
            $result = updateAssessors($assessor_id, $username, $password, $type);
        }

        if ($result) {
            $message = "Assessor updated successfully!";
             header("Location: ../AssessorDB.php");; // redirect after 3 seconds
        } else {
            $conn->error;
        }
    }

} else {
    $assessor_id = $_GET['id'];
    $result = $conn->query("SELECT * FROM assesoraccountlist WHERE AssessorAccountID = $assessor_id");

    if ($result->num_rows > 0) {
        $row      = $result->fetch_assoc();
        $username = $row['Username'];
        $password = $row['Password'];
        $type     = $row['AssesorType'];
    } else {
        echo "Assessor not found.";
        exit;
    }
}
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Update Assessor</title>
</head>
<body>
    <h2>Update Assessor</h2>

    <?php if ($message): ?>
        <p style="color:green"><?php echo $message; ?></p>
    <?php endif; ?>

    <?php if ($error): ?>
        <p style="color:red"><?php echo $error; ?></p>
    <?php endif; ?>

    <form method="post" action="<?php echo $_SERVER['PHP_SELF']; ?>">
        <input type="hidden" name="id" value="<?php echo $assessor_id; ?>">

        <label for="username">Username:</label>
        <input type="text" name="username" id="username" value="<?php echo $username; ?>" required><br><br>

        <label for="password">Password:</label>
        <input type="text" name="password" id="password" value="<?php echo $password; ?>" required><br><br>

        <label for="assessor_type">Type:</label>
        <select name="assessor_type" id="assessor_type">
            <option value="Lecturer"   <?php echo $type == 'Lecturer'   ? 'selected' : ''; ?>>Lecturer</option>
            <option value="Supervisor" <?php echo $type == 'Supervisor' ? 'selected' : ''; ?>>Supervisor</option>
        </select><br><br>

        <input type="submit" value="Update Assessor">
        <a href="../AssessorDB.php">Cancel</a>
    </form>
</body>
</html>