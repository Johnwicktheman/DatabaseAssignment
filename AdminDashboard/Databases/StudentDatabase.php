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
    <title>Student Accounts</title>
</head>
 <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title> Student Accounts</title>

    <link rel="stylesheet" href="../../CssFiles/AdminDashBoard2.css">
    <link rel="stylesheet" href="../../CssFiles/AdminTableStyle.css">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.5.1/css/all.min.css"> 

    <!-- Font import -->
    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
    <link href="https://fonts.googleapis.com/css2?family=Bricolage+Grotesque:opsz,wght@12..96,200..800&display=swap" rel="stylesheet">

    <style>        
        form {
            display: flex;
            justify-content:center;
            text-align:center;
        }

        form h1{
            color: #aaa9a9;
            font-size:20px;
        }

        .form-collection1{
            display:inline;
            margin-right:100px;

        }

        form label{
            font-weight: bold;
            color: #555;
        }

        .form-group {
            display: flex;
            justify-content: space-between;
            align-items: center;
            margin-bottom: 15px;
        }

        .form-group input{
            margin-left:20px;
            border-radius:10px;
            width: 40px;
            padding: 8px;
            border: 1px solid #ccc;
            border-radius: 8px;
            font-family: inherit;
            text-align: center;
        }


        /* This is from the Add button  */
        .page-header {
            display: flex;
            justify-content: space-between;
            align-items: center;
            margin-bottom: 24px;
        }

        .page-header h1 {
            font-size: 22px;
            color: #1a1a2e;
        }


        .btn {
            padding: 9px 18px;
            border: none;
            border-radius: 6px;
            font-size: 14px;
            font-weight: 600;
            cursor: pointer;
            transition: background 0.2s;
        }

        .btn-primary { background-color: #3f9254; color: #fff; }
        .btn-primary:hover { background-color: #20532d; }


        </style>


</head>
<body>
<body>

    <!-- Sidebar-->
    <nav>
        <p> ADMIN PANEL</p>
        <hr>
        <a href="../../AdminDashboard.php">Dashboard</a><br>
        <a href="StudentDatabase.php" class="active">Student Accounts</a><br>
        <a href="AssessorDatabase.php">Assessor Accounts</a><br>
        <a href="CompanyDatabase.php">Company Database</a><br>
        <a href="results.php">Result Viewing</a><br>
        <a href="../../Logout.php" onclick="return confirm('Are you sure you want to logout?')">Logout</a>
    </nav>


    
    <main class = "main">

        <div id="title">Student Accounts </div>
        <hr>
        <header>Manage Student Accounts</header>
        <a onclick="window.location.href='../AssessorDashboard.php'" class="back-link">&larr; Back to Dashboard</a>


        <div class="page-header">
            <h1> Student Accounts </h1>
            <a href="../StudentFunctions/AddStudent.php" class="btn btn-primary">Add Student</a>
        </div>

        <!-- Table -->
            <table>
                <thead>
                    <tr>
                        <th> Student ID</th>
                        <th> Full Name</th>
                        <th> Admin ID</th>
                        <th> Role</th>
                        <th> Action</th>
                    </tr>
                </thead>
                <tbody>
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

                            //Hardcoded Role (Because student accounts are all students)
                            echo "<td> Student </td>";

                            //Combined Action Column
                            echo "<td>
                                <a href='../StudentFunctions/ViewStudent.php?id=" . $id . "'><i class='fa-solid fa-eye'></i> View</a>
                                <a href='../StudentFunctions/EditStudent.php?id=" . $id . "'><i class='fa-solid fa-pen-to-square'></i> Edit</a>
                               
                                <a href='../StudentFunctions/DeleteStudent.php?id=" . $id . "'> <i class='fa-solid fa-trash'></i> Delete</a>
                                </td>";
                            echo "</tr>";
                        }
                    ?>
                </tbody>
            </table>
         </div>

    </main>


     </div>


</body>
</html>
</body>
</html>