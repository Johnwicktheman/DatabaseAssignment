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


    //check same username but different id
    $checkSql = "SELECT * FROM studentprofile WHERE AssesorAccountIDLect = ? OR AssesorAccountIDSuper = ?";
    $checkID = executePreparedStatement($checkSql, [$id, $id]);

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
    $id = $row['AssessorAccountID'];
    $adminID = $row['AdminAccountID'];
    $currentType = $row['AssesorType'];
}
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">

    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
    <link href="https://fonts.googleapis.com/css2?family=Bricolage+Grotesque:opsz,wght@12..96,200..800&display=swap" rel="stylesheet">
    
    <link rel="stylesheet" href="../../CssFiles/Add_Edit.css">
    <title>Document</title>
</head>
<body>
    <div class="container">
        <h1 class="page-title">Delete Assessor</h1>
        <p class="subtitle">Review the details below before permanent removal.</p>
        <?php 
            if ($error !=null){
                echo '<div style="color: red; font-weight: bold;">';
                echo "ERROR Found rows: " . $checkID->num_rows;
                echo '<br>';
                echo $error;
                echo "<ul>";
                foreach ($assignedStudentNames as $student) {
                    echo "<li>" . $student . "</li>";
                }
                echo "</ul>";
                echo '</div>';
                echo '<br>';

            }
        ?>

        <div class="form-card">
            <h2 class="section-title">Confirmation Required</h2>
            <div class="form-grid">

                <div class="form-group full-width">
                    <label>Username: </label>
                    <div class="detail-value"><?php echo $Name; ?></div>
                </div>

                <div class="form-group full-width">
                    <label>Password: </label>
                    <div class="detail-value"><?php echo $Password; ?></div>
                </div>

                <div class="form-group full-width">
                    <label>ID: </label>
                    <div class="detail-value"><?php echo $id; ?></div>
                </div>

                <div class="form-group full-width">
                    <label>Added by Admin ID: </label>
                    <div class="detail-value"><?php echo $adminID; ?></div>
                </div>

                <div class="form-group full-width">
                    <label>Type: </label>
                    <div class="detail-value"><?php echo $currentType; ?></div>
                </div>
                
                <div class="form-group full-width">
                    <form action="" method="post">
                        <div class="button-group">
                            <input type="hidden" name="id" value="<?php echo $id; ?>">
                            <input type="submit" value="Delete" class="btn btn-secondary" style="background-color:#ff4d4d;">
                            <a href="../Databases/AssessorDatabase.php" class="btn btn-secondary">Cancel</a>
                        </div>
                    </form>
                </div>

            </div>
        </div>
    </div>
</body>
</html>