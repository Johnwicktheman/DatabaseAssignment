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
    <link rel="stylesheet" href="../../CssFiles/AdminDashBoard2.css">
    <link rel="stylesheet" href="../../CssFiles/AdminTableStyle.css">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.5.1/css/all.min.css"> 

    <link rel="stylesheet" href="../../CssFiles/searchbar.css">

    <!-- Font import -->
    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
    <link href="https://fonts.googleapis.com/css2?family=Bricolage+Grotesque:opsz,wght@12..96,200..800&display=swap" rel="stylesheet">

    <style>        
        .back-link {
            display: inline-block;
            margin-bottom: 20px;
            color: #154c4b;
            text-decoration: none;
            font-weight: bold;
            transition: color 0.3s;
        }

        .back-link:hover {
            color: #3179c0;
            cursor:pointer;
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

    <nav>
        <p> ADMIN PANEL</p>
        <hr>
        <a href="../../AdminDashboard.php">Dashboard</a><br>
        <a href="StudentDatabase.php" class="active">Student Accounts</a><br>
        <a href="AssessorDatabase.php">Assessor Accounts</a><br>
        <a href="CompanyDatabase.php">Company Database</a><br>
        <a href="results.php">Result Viewing</a><br>
        <div id="logout">
        <a href="../../Logout.php" onclick="return confirm('Are you sure you want to logout?')">Logout</a>
        </div>
    </nav>

    
    <div class = "main">

        <div id="title"> Assessor Accounts </div>
        <hr>
        <header>Manage Assessor Accounts</header>
          <a onclick="window.location.href='../../AdminDashboard.php'" class="back-link">&larr; Back to Dashboard</a>
        <div class="page-header">
            <h1> Assessor Accounts </h1>
            <a href="../AssessorFunctions/AddAssessor.php" class="btn btn-primary">Add Assessor</a>
        </div>

        <div class="search-bar-container">
            <div>
                <label for="jsSearch">Search:</label>
                <input type="text" id="jsSearch" placeholder="Search ID or Name..." onkeyup="applyFilters()">
            </div>
            <div>
                <label for="jsSort">Filter / Sort By:</label>
                <select id="jsSort" onchange="applyFilters()">
                    <option value="oldest">Oldest Added (Default)</option>
                    <option value="newest">Newest Added</option>
                    <option value="no_record">No Assessment Record First</option>
                </select>
            </div>
        </div>

        <!-- Table -->
         <div class="table-wrapper">
            <table id="searchTable">
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

                            echo "<tr class='search-row' data-id='$id' data-name='$user'>";
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
                <tbody id="tableBody">
                    <tr id="noResultsRow" style="display: none;">
                        <td colspan="10" style="text-align: center; padding: 20px; color: #777;">
                            No records found matching your search.
                        </td>
                    </tr>
                </tbody>
            </table>
         </div>

    </div>

  
     </div>
    <script src="../../JSscripts/searchbar.js"></script>

</body>
</html>

