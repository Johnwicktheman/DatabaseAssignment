<?php
include '../session.php';
include '../connection.php';
include '../ExecutePStatements.php';
include 'AdminFunction.php';

if (!isLoggedIn() || getCurrentUser()['role'] !== 'admin') {
    header("Location: ../login.php");
    exit;
}
$searchTerm = $_GET['search'] ?? "";
$sortType = $_GET['sort'] ?? "oldest"; // This captures the sort button click

// Fetch the results using both variables
$result = getStudents($searchTerm, $sortType);

?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Student Database</title>
    <link rel="stylesheet" href="../CssFiles/StudentDB.css">
</head>
<body>
    <input type="checkbox" id="navToggle" class="navToggle">
    <label for="navToggle" class="overlay"></label>
    <div class="sidebar">
        <ul>
            <li><a href="AdminDashboard.php" class="SideBarContent">DashBoard</a></li>
            <li><a href="../logout.php" class="SideBarContent">Log Out</a></li>
        </ul>
    </div>

    <div class="HeaderBar">
        <div class="HeaderImage">
            <img src="../Assets/UniLogoBlack.png " style="width:200px; height: auto; margin: 20px 40px;">
        </div>
        <div class="HeaderTitle">
            <p>Student Database</p>
        </div>
        <div class="HeaderTitle">
            
            <label for="navToggle" class="navToggleLabel"><img src="../Assets/ThreeDash.png" style="width: 50px; height: auto; margin: 20px 40px;"></label>
        </div>

    </div>

    <div class="MainContainer">
        
        <div class="PageActions">
            <a href="AdminDashboard.php" class="Btn-Back">← Back to Dashboard</a>
            <div class="FilterContainer">
                <button class="Btn-Filter" onclick="toggleFilter()">Filter ▼</button>
                <div id="filterDropdown" class="DropdownContent">
                    <p class="DropdownHeader">Sort By:</p>
                    <a href="?sort=oldest&search=<?php echo urlencode($searchTerm); ?>" 
                    class="<?php echo ($sortType === 'oldest' || $sortType === '') ? 'active-sort' : ''; ?>">
                    Default (Oldest)
                    </a>
                    <a href="?sort=recent&search=<?php echo urlencode($searchTerm); ?>" 
                    class="<?php echo ($sortType === 'recent') ? 'active-sort' : ''; ?>">
                    Newest First
                    </a>
                    <a href="?sort=name&search=<?php echo urlencode($searchTerm); ?>" 
                    class="<?php echo ($sortType === 'name') ? 'active-sort' : ''; ?>">
                    A-Z (Username)
                    </a>
                </div>
            </div>

            <form action="" method="GET" class="SearchForm">

            <input type="text" name="search" placeholder="Search ID or Username..." 
                   value="<?php echo isset($_GET['search']) ? htmlspecialchars($_GET['search']) : ''; ?>">
            <button type="submit" class="Btn-Search">Search</button>
           
            </form>

            <a href="StudentChangesPages/add_student.php" class="Btn-Add">+ Add New Student</a>
        </div>

        <table>
            <thead>
                <tr>
                    <th>Student ID</th>
                    <th>Username</th>
                    <th>Password</th>
                    <th>Full Name</th>
                    <th>Added By</th>
                    <th>Actions</th>
                    <th>Full Details</th>
                </tr>
            </thead>
            <tbody>
                <?php
                   
                    
                    if ($result->num_rows > 0) {
                        while ($row = $result->fetch_assoc()) {
                            echo "<tr>";
                            echo "<td>" . htmlspecialchars($row['StudentAccountID']) . "</td>";
                            echo "<td>" . htmlspecialchars($row['Username']) . "</td>";
                            echo "<td>••••••••</td>"; // Security tip: Don't show plain text passwords if possible
                            echo "<td>" . htmlspecialchars($row['FirstName'])  ." ". htmlspecialchars($row['LastName']) . "</td>";
                            echo "<td>Admin ID: " . htmlspecialchars($row['AdminAccountID']) . "</td>";
                            
                            echo "<td>
                                    <a class='action-link' href='StudentChangesPages/update_student.php?id=" . $row['StudentAccountID'] ."'>Update</a>
                                    <a class='action-link' style='color: #b22222;' href='StudentChangesPages/delete_student.php?id=" . $row['StudentAccountID'] . "'>Delete</a>
                                  </td>"; 

                            echo "<td>
                                     <a class='profile-link' href='StudentChangesPages/student_profile.php?id=" . $row['StudentAccountID'] . "'>View Profile</a>
                                  </td>";
                            echo "</tr>";
                        }
                    } else {
                        echo "<tr><td colspan='6' style='text-align:center;'>No students found.</td></tr>";
                    }
                ?>
            </tbody>
        </table>
    </div>

</body>
</html>
<script>
function toggleFilter() {
    document.getElementById("filterDropdown").classList.toggle("show");
}

// Close the dropdown if the user clicks outside of it
window.onclick = function(event) {
  if (!event.target.matches('.Btn-Filter')) {
    var dropdowns = document.getElementsByClassName("DropdownContent");
    for (var i = 0; i < dropdowns.length; i++) {
      var openDropdown = dropdowns[i];
      if (openDropdown.classList.contains('show')) {
        openDropdown.classList.remove('show');
      }
    }
  }
}
</script>