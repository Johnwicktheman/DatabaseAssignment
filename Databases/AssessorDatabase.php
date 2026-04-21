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

$AssessorList = "SELECT * FROM assesoraccountlist";
$AssessorResult = executePreparedStatement($AssessorList, []);

?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Document</title>
    <link rel="stylesheet" href="CssFiles/AssessorDashBoard.css">
</head>
<body>
    <p><a href="../AdminDashboard.php">Back to Dashboard</a></p>
    <p><a href="../AssessorFunctions/AddAssessor.php">Add New Assessor</a></p>
    <table>
        <tr>
            <th>ID</th>
            <th>Username</th>
            <th>Type</th>
            <th>Admin ID</th>
            <th>Actions</th>
        </tr>
  

        <?php
            while ($row = $AssessorResult->fetch_assoc()) {
                $id       = $row['AssessorAccountID'];
                $user     = $row['Username'];
                $type     = $row['AssesorType']; 
                $adminID  = $row['AdminAccountID'];

                echo "<tr>";
                echo "<td>" . $id . "</td>";
                echo "<td>" . $user . "</td>";
                echo "<td>" . $type . "</td>";
                echo "<td>" . $adminID . "</td>";
                //let id = current assessor row id
                echo "<td><a href='../AssessorFunctions/EditAssessor.php?id=" . $row['AssessorAccountID'] . "'>Edit</a></td>";
                echo "<td><a href='../AssessorFunctions/DeleteAssessor.php?id=" . $row['AssessorAccountID'] . "'>Delete</a></td>";
                echo "</tr>";
            }
        ?>


    </table>
</body>
</html>