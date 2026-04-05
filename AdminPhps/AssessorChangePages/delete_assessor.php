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
    $assessor_id = $_POST['id'];

    $result = deleteAssessor($assessor_id);

    if ($result) {
        header("Location: ../AssessorDB.php");
        exit;
    } else {
        echo "Failed to delete: " . $conn->error;
    }

} else {
    $assessor_id = $_GET['id'];

    $result = executePreparedStatement(
        "SELECT * FROM assesoraccountlist WHERE AssessorAccountID = ?",
        [$assessor_id]
    );

    if ($result->num_rows > 0) {
        $row      = $result->fetch_assoc();
        $username = $row['Username'];
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
    <title>Delete Assessor</title>
</head>
<body>
    <h2>Delete Assessor</h2>
    <p>Are you sure you want to delete this assessor?</p>
    <p><strong>Username:</strong> <?php echo $username; ?></p>
    <p><strong>Type:</strong> <?php echo $type; ?></p>

    <form method="post" action="<?php echo $_SERVER['PHP_SELF']; ?>">
        <input type="hidden" name="id" value="<?php echo $assessor_id; ?>">
        <input type="submit" value="Yes, Delete">
        <a href="../AssessorDB.php">Cancel</a>
    </form>
</body>
</html>