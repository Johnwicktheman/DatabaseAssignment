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
        <link rel="stylesheet" href="../CssFiles/TableStyle.css">

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

        </style>
    </head>
    <body>
        <nav>
            <p>ASSESSOR PANEL</p>
            <hr>
            <a href="../AssessorDashboard.php">Dashboard</a><br>
            <a href="StudentDatabaseAss.php">Assessment Records</a><br>
            <a href="#">Student Database</a><br>
            <a href="../Logout.php" style="color: #ff4d4d; font-weight: bold;" onclick="return confirm('Are you sure you want to logout?')">Logout</a>
        </nav>

        <div class="main">
            <div id="title">View All Student Database</div>
            <hr>
            <header>Student Databases</header>
            <a onclick="window.location.href='../AssessorDashboard.php'" class="back-link">&larr; Back to Dashboard</a>

        <form action="" method="GET">
            <div class="input-group ">
                <input type="text" name="search" value="<?php if(isset($_GET['search'])){echo $_GET['search'];} ?>" class="form-control" placeholder="Search Student">
                <button type="submit" class="btn btn-primary">Search</button>
            </div>
        </form>    

            <table>
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

                            <tr>
                                <td><?= $id; ?></td>
                                <td><?= $FirstName; ?></td>
                                <td><?= $LastName; ?></td>
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
    </body>
</html>