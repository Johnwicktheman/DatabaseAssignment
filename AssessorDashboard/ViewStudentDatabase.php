<?php
    ini_set('display_errors', 1);
    ini_set('display_startup_errors', 1);
    error_reporting(E_ALL);
    session_start();
    include '../Connection.php';
    include '../ExecutePStatement.php';
    include '../AllFunctions.php';

    //Get current Role and ID currenlty confirmed to be lecturer or supervisor
    $assessorID = $_SESSION['user_id'];
    $assessorType = $_SESSION['user_role'];

    //Check role
    if ($assessorType === 'Lecturer') {
        $assessorIDField = 'AssesorAccountIDLect';

    } else {
        $assessorIDField = 'AssesorAccountIDSuper';
    }

    // Initialize search variable
$filtervalue = isset($_GET['search']) ? $_GET['search'] : '';

if ($filtervalue !== '') {
    // Search Mode: Use LIKE with wildcards
    $searchTerm = "%" . $filtervalue . "%";
    $studentsList = "SELECT sp.StudentProfileID, sp.StudentAccountID, sp.FirstName, sp.LastName, sp.YearOfStudy, intern.Role, comp.CompanyName 
                    FROM studentprofile sp
                    LEFT JOIN internship intern ON sp.StudentAccountID = intern.StudentAccountID
                    LEFT JOIN companynamelist comp ON intern.CompanyINT = comp.CompanyInt 
                    WHERE sp.StudentAccountID LIKE ? 
                    OR sp.FirstName LIKE ? 
                    OR sp.LastName LIKE ? 
                    OR comp.CompanyName LIKE ?";
    
    // Pass the search term for every '?' placeholder
    $studentResult = executePreparedStatement($studentsList, [$searchTerm, $searchTerm, $searchTerm, $searchTerm]);
} else {
    // Default Mode: Show all students (or whatever logic you prefer)
    $studentsList = "SELECT sp.StudentProfileID, sp.StudentAccountID, sp.FirstName, sp.LastName, sp.YearOfStudy, intern.Role, comp.CompanyName 
                    FROM studentprofile sp
                    LEFT JOIN internship intern ON sp.StudentAccountID = intern.StudentAccountID
                    LEFT JOIN companynamelist comp ON intern.CompanyINT = comp.CompanyInt";
    $studentResult = executePreparedStatement($studentsList, []);
}
?>

<!DOCTYPE html>
<html>
    <head>
        <title> View Student Database </title>
        <meta charset="UTF-8">
        <!-- Font import -->
        <link rel="preconnect" href="https://fonts.googleapis.com">
        <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
        <link href="https://fonts.googleapis.com/css2?family=Bricolage+Grotesque:opsz,wght@12..96,200..800&display=swap" rel="stylesheet">

        <link rel="stylesheet" href="../CssFiles/AssessorDashBoard.css">
        <link rel="stylesheet" href="../CssFiles/AssessorTableStyle.css">

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
            color: #219e75;
            cursor:pointer;
        }
        /* Search and Filter UI */
        .search-bar-container {
            display: flex;
            align-items: center;
            gap: 20px;
            background-color: #f9f9f9;
            padding: 15px 20px;
            border-radius: 8px;
            margin-bottom: 20px;
            border: 1px solid #ddd;
        }
        .search-bar-container label {
            font-weight: bold;
            color: #154c4b;
            margin-right: 10px;
        }
        .search-bar-container input, .search-bar-container select {
            padding: 8px;
            border: 1px solid #ccc;
            border-radius: 5px;
            font-size: 15px;
            outline: none;
        }
        .search-bar-container input { width: 250px; }


        </style>
    </head>
    <body>
        <nav>
            <p>ASSESSOR PANEL</p>
            <hr>
            <a href="../AssessorDashboard.php">Dashboard</a><br>
            <a href="StudentDatabaseAss.php">Assessment Records</a><br>
            <a href="#">Student Database</a><br>
            <div id="logout">
                <a href="../Logout.php" onclick="return confirm('Are you sure you want to logout?')">Logout</a>
            </div>
        </nav>

        <div class="main">
            <div id="title">View All Student Database</div>
            <hr>
            <header>Student Databases</header>
            <a onclick="window.location.href='../AssessorDashboard.php'" class="back-link">&larr; Back to Dashboard</a>

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

            <table id="studentTable">
                <tr>
                    <th>Student ID</th>
                    <th>First name</th>
                    <th>Last name </th>
                    <th>Year of Study </th>
                    <th>Role </th>
                    <th>Company Name </th   >
                </tr>
                    <?php if ($studentResult && $studentResult->num_rows > 0): ?>
                        <?php while ($row = $studentResult->fetch_assoc()): ?>

                            <?php
                            $id = $row['StudentAccountID'];
                            $FirstName = $row['FirstName'];
                            $LastName = $row['LastName'];
                            $YearOfStudy = $row['YearOfStudy'];
                            $role = $row['Role'];
                            $company = $row['CompanyName'];
                            ?>

                            <tr class="student-row" data-id="<?= $id; ?>" data-firstName="<?= $FirstName; ?>" data-lastName="<?= $LastName; ?>">                                <td ><?= $id; ?></td>
                                <td ><?= $FirstName; ?></td>
                                <td ><?= $LastName; ?></td>
                                <td><?= $YearOfStudy; ?></td>
                                <td><?= $role; ?></td>
                                <td><?= $company; ?></td>
                            </tr>
                        <?php endwhile; ?>
                    <?php else: ?>
                        <tr>
                            <td colspan="6" style="text-align:center;">No students found matching "<?= htmlspecialchars($filtervalue) ?>"</td>
                        </tr>
                    <?php endif; ?>
            </table>
        </div>

        <script>
            //Main Search and Sort Function
            function applyFilters() {
                let searchInput = document.getElementById("jsSearch").value.toLowerCase();
                let sortType = document.getElementById("jsSort").value;
                let tbody = document.querySelector("#studentTable tbody") || document.querySelector("#studentTable");
                let rows = Array.from(tbody.querySelectorAll(".student-row"));

                //Sort the array of rows based on the dropdown selection
                rows.sort((a, b) => {
                    let aCode = parseInt(a.getAttribute("data-assessment-id"));
                    let bCode = parseInt(b.getAttribute("data-assessment-id"));
                    let aHasRec = parseInt(a.getAttribute("data-has-record"));
                    let bHasRec = parseInt(b.getAttribute("data-has-record"));

                    if (sortType === 'newest') {
                        return bCode - aCode;       //Highest ID (Newest) first
                    } 
                    else if (sortType === 'oldest') {
                        return aCode - bCode;       //Lowest ID (Oldest) first
                    } 
                    else if (sortType === 'no_record') {
                        //No record
                        return aHasRec - bHasRec; 
                    }
                });

                //Re-attach rows to the table in the new sorted order and apply serach filter
                rows.forEach(row => {
                    tbody.appendChild(row);
                    
                    // Check if it matches the search bar
                    let idTxt = row.getAttribute("data-id").toLowerCase();
                    let firstnameTxt = row.getAttribute("data-firstName").toLowerCase();
                    let lastnameTxt = row.getAttribute("data-lastName").toLowerCase();
                    
                    if (idTxt.includes(searchInput) || firstnameTxt.includes(searchInput) || lastnameTxt.includes(searchInput)) {
                        row.style.display = "";
                    } else {
                        row.style.display = "none";
                    }
                });
            }
    </script>

    </body>
</html>