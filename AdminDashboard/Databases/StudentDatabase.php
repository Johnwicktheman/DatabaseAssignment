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


$studentList = "SELECT * FROM studentaccountlist";
$studentResult = executePreparedStatement($studentList, []);

?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Document</title>
</head>
<body>
    <p><a href="../../AdminDashboard.php">Back to Dashboard</a></p>
    <p><a href="../StudentFunctions/AddStudent.php">Add New Student</a></p>
    <table>
        <tr>
            <th>ID</th>
            <th>Username</th>
            <th>Admin ID</th>
            <th>Actions</th>
        </tr>
  

        <?php
            while ($row = $studentResult->fetch_assoc()) {
                $id       = $row['StudentAccountID'];
                $user     = $row['Username'];
                $adminID  = $row['AdminAccountID'];

                echo "<tr>";
                echo "<td>" . $id . "</td>";
                echo "<td>" . $user . "</td>";
                echo "<td>" . $adminID . "</td>";
                //let id = current student row id
                echo "<td><a href='../StudentFunctions/UpdateStudent.php?id=" . $row['StudentAccountID'] . "'>Edit</a></td>";
                echo "<td><a href='../StudentFunctions/ViewStudent.php?id=" . $row['StudentAccountID'] . "'>View</a></td>";
                echo "<td><a href='../StudentFunctions/DeleteStudent.php?id=" . $row['StudentAccountID'] . "'>Delete</a></td>";
                echo "</tr>";
            }
        ?>


    </table>
</body>
</html>