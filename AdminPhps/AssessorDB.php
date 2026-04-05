<?php
include '../session.php';
include '../connection.php';
include '../ExecutePStatements.php';
include 'AdminFunction.php';

if (!isLoggedIn() || getCurrentUser()['role'] !== 'admin') {
    header("Location: ../login.php");
    exit;
}

?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Assessor Database</title>
</head>
<body>
    <p><a href="AdminDashboard.php">back</a></p>
    <a href="AssessorChangePages/add_assessor.php">+ Add New Assessor</a>
    <table>
        <tr>
            <th>Assessor ID</th>
            <th>Username</th>
            <th>Password</th>
            <th>Added By</th> 
            <th>Type</th>
        </tr>
        <?php
            $result = getAssessors();
            if ($result->num_rows > 0) {
                while ($row = $result->fetch_assoc()) {
                    echo "<tr>";
                    echo "<td>" . $row['AssessorAccountID'] . "</td>";
                    echo "<td>" . $row['Username'] . "</td>";
                    echo "<td>" . $row['Password'] . "</td>";
                    echo "<td>" . $row['AdminAccountID'] . "</td>";
                    echo "<td>" . $row['AssesorType'] . "</td>";
                    echo "<td>
                                <a href='AssessorChangePages/assigned_students.php?id=" . $row['AssessorAccountID'] . "&type=" . $row['AssesorType'] . "'>
                                    View Assigned Students
                                </a>
                            </td>";
                    echo "<td>
                            <a href='AssessorChangePages/update_assessor.php?id=" . $row['AssessorAccountID'] ."'>Update</a>
                            <a href='AssessorChangePages/delete_assessor.php?id=" . $row['AssessorAccountID'] . "'>Delete</a>
                         </td>"; 
                        
                        
                    echo "</tr>";
                    
                }
            } else {
                echo "<tr><td colspan='7'>No assessors found.</td></tr>";
            }
        ?>
    </table>
</body>
</html>