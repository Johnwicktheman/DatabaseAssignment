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


$CList = "SELECT * FROM companynamelist";
$CompanyResult = executePreparedStatement($CList, []);

?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Document</title>
    <link rel="stylesheet" href="CssFiles/CompanyDashBoard.css">
</head>
<body>
    <p><a href="../../AdminDashboard.php">Back to Dashboard</a></p>
    <p><a href="../CompanyFunctions/AddCompany.php">Add New Company</a></p>
    <table>
        <tr>
            <th>ID</th>
            <th>Company Name</th>

        </tr>
  

        <?php
            while ($row = $CompanyResult->fetch_assoc()) {
                $id       = $row['CompanyInt'];
                $CompanyName     = $row['CompanyName'];

                echo "<tr>";
                echo "<td>" . $id . "</td>";
                echo "<td>" . $CompanyName . "</td>";
                echo "<td><a href='../CompanyFunctions/DeleteCompany.php?id=" . $id . "'>Delete</a></td>";

            
                echo "</tr>";
            }
        ?>


    </table>
</body>
</html>