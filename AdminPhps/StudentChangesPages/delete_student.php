<?php
include '../../session.php';
include '../../connection.php';
include '../../ExecutePStatements.php';
include '../AdminFunction.php';

if (!isLoggedIn() || getCurrentUser()['role'] !== 'admin') {
    header("Location: ../../login.php");
    exit;
}

if ($_SERVER["REQUEST_METHOD"] == "POST") {
    $student_id = $_POST['id'];

    $result = deleteStudent($student_id);

    if ($result) {
        header("Location: ../StudentDB.php");
        exit;
    } else {
        echo "Failed to delete: " . $conn->error;
    }

} else {
    $student_id = $_GET['id'];

    $result = executePreparedStatement(
        "SELECT * FROM studentaccountlist WHERE StudentAccountID = ?",
        [$student_id]
    );

    if ($result->num_rows > 0) {
        $row      = $result->fetch_assoc();
        $username = $row['Username'];
    } else {
        echo "Student not found.";
        exit;
    }
}
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Delete Assessor</title>
</head>
<body>
    <h2>Delete Student</h2>
    <p>Are you sure you want to delete this student?</p>
    <p><strong>Username:</strong> <?php echo $username; ?></p>

    <form method="post" action="<?php echo $_SERVER['PHP_SELF']; ?>">
        <input type="hidden" name="id" value="<?php echo $student_id; ?>">
        <input type="submit" value="Yes, Delete">
        <a href="../StudentDB.php">Cancel</a>
    </form>
</body>
</html>