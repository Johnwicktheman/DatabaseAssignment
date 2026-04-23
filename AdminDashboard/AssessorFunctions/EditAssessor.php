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

//error text
$error = null;
//After they press submit button what happens
if ($_SERVER["REQUEST_METHOD"] == "POST") {
    $id   = $_POST['id'];
    $user = $_POST['username'];
    $pass = $_POST['password'];


    //check all table for names make sur eno duplicate username for accounts
    $resAssessor = executePreparedStatement("SELECT Username FROM assesoraccountlist WHERE Username = ? AND AssessorAccountID != ?", [$user, $id]);
    $resStudent = executePreparedStatement("SELECT Username FROM studentaccountlist WHERE Username = ?", [$user]);
    $resAdmin = executePreparedStatement("SELECT Username FROM adminaccountlist WHERE Username = ?", [$user]);

    //Check if any of them found a match
    if ($resAssessor->num_rows > 0) {
        $error = "Username is already taken by another Assessor.";
    } else if ($resStudent->num_rows > 0) {
        $error = "Username is already taken by a Student.";
    } else if ($resAdmin->num_rows > 0) {
        $error = "Username is already taken by an Admin.";
    }


    if ($error) {
        //If there is error show error msg below
    } else {
        // PROCEED: Everything is safe
        $updateSql = "UPDATE assesoraccountlist SET Username = ?, Password = ? WHERE AssessorAccountID = ?";
        $updateRes = executePreparedStatement($updateSql, [$user, $pass, $id]);

        if ($updateRes) {
            header("Location: ../Databases/AssessorDatabase.php");
            exit();
        }
    }

}

//Check two stuff did we get id from Post method(previous webpage)
//Did we get id from current page()
if (isset($_POST['id'])) {
    $AssessorId = $_POST['id'];
} 
//Did we get id from Post method(previous webpage)
else if (isset($_GET['id'])) {
    $AssessorId = $_GET['id'];
} 
//If it in neither, set it to null and redirect back
else {
    $AssessorId = null;
}
if (!$AssessorId) {
    header("Location: ../Databases/AssessorDatabase.php");
    exit();
}


//This is get Data for current assessor
//Get id from url which is from AdminDashboard
$AssessorName = "SELECT * FROM assesoraccountlist WHERE AssessorAccountID = ?";
$AssessorResult = executePreparedStatement($AssessorName, [$AssessorId]);

if ($AssessorResult->num_rows > 0) {
    $row = $AssessorResult->fetch_assoc();
    $Name = $row['Username'];
    $Password = $row['Password'];
    $adminID = $row['AdminAccountID'];
    $currentType = $row['AssesorType'];
}
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Edit Assessor</title>
    <link rel="stylesheet" href="../../CssFiles/Add_Edit.css">
</head>
<body>

<div class="container">

    <h1 class="page-title">Edit Assessor</h1>

    <p class="subtitle">Updating Assessor: <?php echo $Name; ?></p>

    <?php if ($error != null): ?>
        <div class="error"><?php echo $error; ?></div>
    <?php endif; ?>

    <div class="form-card">

        <form action="" method="post">

            <input type="hidden" name="id" value="<?php echo $AssessorId; ?>">

            <h2 class="section-title">Assessor Information</h2>

            <div class="form-grid">

                <div class="form-group">
                    <label for="username">Username</label>
                    <input type="text" id="username" name="username" value="<?php echo $Name; ?>" required>
                </div>

                <div class="form-group">
                    <label for="password">Password</label>
                    <input type="text" id="password" name="password" value="<?php echo $Password; ?>" required>
                </div>

            </div>

            <div class="button-group">
                <button type="submit" class="btn btn-primary">Update Assessor</button>
                <a href="../Databases/AssessorDatabase.php" class="btn btn-secondary">Cancel</a>
            </div>

        </form>

    </div>

</div>

</body>
</html>