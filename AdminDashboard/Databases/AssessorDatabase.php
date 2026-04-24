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


$AssessorList = "SELECT * FROM assesoraccountlist";
$AssessorResult = executePreparedStatement($AssessorList, []);

?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title> Assessor Accounts </title>

    <link rel="stylesheet" href="user_management.css">
    <link rel="stylesheet" href="../../CssFiles/AssessorDashBoard.css">
    <link rel="stylesheet" href="../../CssFiles/TableStyle.css">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.5.1/css/all.min.css"> 

    <!-- Font import -->
    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
    <link href="https://fonts.googleapis.com/css2?family=Bricolage+Grotesque:opsz,wght@12..96,200..800&display=swap" rel="stylesheet">

    <style>        

        nav a{
            margin-bottom:20px;
        }
        
        #title{
            color: #aaa9a9;
            font-size:30px;
            padding-bottom:8px;
        }

        .main hr {
            border: 0;
            border-top: 1px solid #aaa9a9;
        }

        header {
            font-size: 50px;
            color: #154c4b;
        }


        .back-link {
            display: inline-block;
            margin-bottom: 20px;
            color: #154c4b;
            text-decoration: none;
            font-weight: bold;
            transition: color 0.3s;
        }

        .back-link:hover {
            color: #219e75;
            cursor:pointer;
        }

        
        .action-links a {
            text-decoration: none;
            color: #154c4b;
            font-weight: bold;
            margin-right: 15px;
            transition: color 0.3s;
        }

        .delete-btn {
            color: #e74c3c;
            font-weight: bold;
            text-decoration: none;
            transition: 0.3s;
        }

        .delete-btn:hover {
            color: #c0392b;
            text-decoration: underline;
            cursor: pointer;
        }

        tr a{
            text-decoration: none;
            color: #154c4b;
            font-weight: bold;
            margin-right: 15px;
            transition: color 0.3s;
        }

        tr a:hover {
            color: #219e75;
        }

        tr i {
            margin-right: 5px;
        }


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

    <!-- Sidebar-->


    <nav>
        <p> ADMIN PANEL</p>
        <hr>
        <a href="../../AdminDashboard.php">Dashboard</a>
        <a href="StudentDatabase.php" class="active">Student Accounts</a>
        <a href="AssessorDatabase.php">Assessor Accounts</a>
        <a href="CompanyDatabase.php">Company Database</a>
        <a href="results.php">Result Viewing</a>
        <div id="logout">
        <a href="../Logout.php" style="color: #ff4d4d; font-weight: bold;" onclick="return confirm('Are you sure you want to logout?')">Logout</a>
        </div>
    </nav>

    
    <main class = "main">

        <div id="title"> Assessor Accounts </div>
        <hr>
        <header>Manage Assessor Accounts</header>

        <div class="page-header">
            <h1> Assessor Accounts </h1>
            <a href="../AssessorFunctions/AddAssessor.php" class="btn btn-primary">Add Assessor</a>
        </div>


        <!-- Table -->
         <div class="table-wrapper">
            <table id="userTable">
                <thead>
                    <tr>
                        <th> Assessor ID</th>
                        <th> Full Name</th>

                        <th> Role</th>
                        <th> Action</th>
                    </tr>
                </thead>
                <tbody id="tableBody">
                    <!-- Insert the rows here through PHP--> 
                    <?php
                        while ($row = $AssessorResult->fetch_assoc()) {
                            $id       = $row['AssessorAccountID'];
                            $user     = $row['Username'];

                            $type     = $row['AssesorType']; 

                            echo "<tr>";
                            echo "<td>" . $id . "</td>";
                            echo "<td>" . $user . "</td>";
    
                            echo "<td>" . $type . "</td>";
                            //let id = current assessor row id


                            echo "<td>
                                <a href='../AssessorFunctions/EditAssessor.php?id=" . $row['AssessorAccountID'] . "'><i class='fa-solid fa-pen-to-square'></i> Edit</a>
                                <a href='../AssessorFunctions/DeleteAssessor.php?id=" . $row['AssessorAccountID'] . "'><i class='fa-solid fa-trash'></i> Delete</a>
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

