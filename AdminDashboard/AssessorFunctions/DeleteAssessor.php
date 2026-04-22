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


$assignedStudentNames = [];
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


//error text
$error = null;
//After they press submit button what happens
if ($_SERVER["REQUEST_METHOD"] == "POST") {
    $id   = $_POST['id'];
    $user = $_POST['username'];
    $pass = $_POST['password'];


    //check same username but different id
    $checkSql = "SELECT * FROM studentprofile WHERE AssesorAccountIDLect = ? OR AssesorAccountIDSuper = ?";
    $checkID = executePreparedStatement($checkSql, [$id, $id]);
    echo "Found rows: " . $checkID->num_rows;

    if ($checkID->num_rows > 0) {
        while ($studentRow = $checkID->fetch_assoc()) {
        // Combine First and Last name
        $fullName = $studentRow['FirstName'] . " " . $studentRow['LastName'];
        $assignedStudentNames[] = $fullName;
        
        }

        $error = "Students are assigned to this assessor. Please reassign or delete those students"; 
    }

    if ($error) {
        //If there is error show error msg below
    } else {
        // PROCEED: Everything is safe
        $deleteSql = "DELETE FROM assesoraccountlist WHERE AssessorAccountID = ?";
        $deleteRes = executePreparedStatement($deleteSql, [$id]);

        if ($deleteRes) {
            header("Location: ../Databases/AssessorDatabase.php");
            exit();
        }
    }

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
    <title>Document</title>
</head>
<body>
    <?php 
        if ($error !=null){
            echo $error;
            echo "<ul>";
            foreach ($assignedStudentNames as $student) {
                echo "<li>" . $student . "</li>";
            }
            echo "</ul>";
        }
    ?>
    
    <p>Deleting Assessor: <?php echo $Name; ?></p>
    <p> Are you sure you want to delete this assessor? This action cannot be undone.</p>
    <p>Username: <?php echo $Name; ?></p>
    <p>Password: <?php echo $Password; ?></p>
    <p>Added by Admin ID: <?php echo $adminID; ?></p>
    <p>Type: <?php echo $currentType; ?></p>
    
    <form action="" method="post">
        <input type="hidden" name="id" value="<?php echo $AssessorId; ?>">
        <input type="submit" value="Delete Assessor">
        <a href="../Databases/AssessorDatabase.php">Cancel</a>
    </form>
</body>
</html>