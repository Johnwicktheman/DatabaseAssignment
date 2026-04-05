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
    $type     = $_POST['assessor_type'];

    if (empty($username) || empty($password) || empty($type)) {
        $error = "All fields are required.";
    } else {
        // Check if username already exists
        $check = executePreparedStatement(
            "SELECT AssessorAccountID FROM assesoraccountlist WHERE Username = ?",
            [$username]
        );

        if ($check->num_rows > 0) {
            $error = "Username already exists.";
        } else {
            $result = CreateAssessors($username, $password, $type);

            if ($result) {
                header("Location: ../AssessorDB.php");
                exit;
            } else {
                $error = "Failed to create assessor: " . $conn->error;
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
    <h2>Add New Assessor</h2>

    <?php if ($message): ?>
        <p style="color:green"><?php echo $message; ?></p>
    <?php endif; ?>

    <?php if ($error): ?>
        <p style="color:red"><?php echo $error; ?></p>
    <?php endif; ?>

    <form method="post" action="<?php echo $_SERVER['PHP_SELF']; ?>">

        <label for="username">Username:</label>
        <input type="text" name="username" id="username" required><br><br>

        <label for="password">Password:</label>
        <input type="text" name="password" id="password" required><br><br>

        <label for="assessor_type">Type:</label>
        <select name="assessor_type" id="assessor_type" required>
            <option value="">-- Select Type --</option>
            <option value="Lecturer">Lecturer</option>
            <option value="Supervisor">Supervisor</option>
        </select><br><br>

        <input type="submit" value="Add Assessor">
        <a href="../AssessorDB.php">Cancel</a>

    </form>
</body>
</html>