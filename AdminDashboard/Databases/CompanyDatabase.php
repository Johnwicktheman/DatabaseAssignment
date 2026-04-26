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


//since we are using js for this page so make it into a list first
$companiesData = [];
if ($CompanyResult && $CompanyResult->num_rows > 0) {
    while ($row = $CompanyResult->fetch_assoc()) {
        $companiesData[] = [
            'id' => $row['CompanyInt'],
            'name' => $row['CompanyName'] ?: 'N/A',
            'industry' => $row['CompanyType'] ?? 'Other',
            'address' => $row['CompanyAddress'] ?? 'N/A',
            'contact' => 'Contact Number',
            'phone' => $row['ContactNumber'] ?? 'N/A',
            'email' => $row['EmailContact'] ?? 'N/A',
            'logo' => $row['PicturePath'] ?? '',
            
        ];
    }
}
?>

<!DOCTYPE html>
<html lang="en">
<head>
  <meta charset="UTF-8" />
  <meta name="viewport" content="width=device-width, initial-scale=1.0"/>
  <title> Company Database</title>
  
  <link rel="stylesheet" href="../../CssFiles/CompanyDatabase.css">
  <link rel="stylesheet" href="../../CssFiles/AdminTableStyle.css">
  <link rel="stylesheet" href="../../CssFiles/AdminDashBoard.css">
  <link rel="stylesheet" href="../../CssFiles/searchbar.css">
  <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.5.1/css/all.min.css"> 


  <!-- Font import -->
  <link rel="preconnect" href="https://fonts.googleapis.com">
  <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
  <link href="https://fonts.googleapis.com/css2?family=Bricolage+Grotesque:opsz,wght@12..96,200..800&display=swap" rel="stylesheet">


</head>
<body>

  <nav>
        <p> ADMIN PANEL</p>
        <hr>
        <a href="../../AdminDashboard.php">Dashboard</a><br>
        <a href="StudentDatabase.php">Student Accounts</a><br>
        <a href="AssessorDatabase.php">Assessor Accounts</a><br>
        <a href="CompanyDatabase.php">Company Database</a><br>
        <a href="ResultsViewing.php">Result Viewing</a><br>
        <div id="logout">
        <a href="../../Logout.php" onclick="return confirm('Are you sure you want to logout?')">Logout</a>
        </div>
  </nav>

  
  <main class="main">
    
    <div id="title">Internship Companies</div>
    <hr>
    <header>Manage Internship Companies</header>
    <a onclick="window.location.href='../../AdminDashboard.php'" class="back-link">&larr; Back to Dashboard</a>

    <div class="page-header">
      <h1>Company Database</h1>
      <a href="../CompanyFunctions/AddCompany.php" class="btn btn-primary" style="text-decoration: none; display: inline-block;">+ Add Company</a>
    </div>
    
    <div class="search-bar-container">
      <label>Search:</label>
      <input type="text" id="searchInput" placeholder="Search by company name" oninput="applyFilters()" />

      <label>Filter / Sort By:</label>
      <select id="industryFilter" onchange="applyFilters()">
        <option value="">All Industries</option>
        <option value="Technology">Technology</option>
        <option value="Finance">Finance</option>
        <option value="Healthcare">Healthcare</option>
        <option value="Engineering">Engineering</option>
        <option value="Media">Media</option>
        <option value="Other">Other</option>
      </select>
  </div>

    <!-- <div class="card-grid" id="cardGrid"></div> -->

      <div class="table-wrapper">
        <table>
          <thead>
            <tr>
              <th>Company</th>
              <th>Industry</th>
              <th>Contact Person</th>
              <th>Email</th>
              <th>Actions</th>
            </tr>
          </thead>
          <tbody id="tableBody">
          </tbody>
        </table>
      </div>

  </main>

  <script>
    //change the data from php into javascript data
    var companies = <?php echo json_encode($companiesData) ?: '[]'; ?>;

    //this is for profile picture for initials
    function initials(name) {
      //if name empty just put ?
      if (!name || name.trim() === '') return '?';
      var parts = name.trim().split(' ');
      if (parts.length === 1) return parts[0][0].toUpperCase();

      //check whether is a two part word like Amazon Prime or just Google if Google get G else get AP
      return (parts[0][0] + parts[parts.length - 1][0]).toUpperCase();
    }

    //if no image put initial else put the company logo
    function logoImgTable(c) {
      if (c.logo) return '<img class="table-logo" src="../../' + c.logo + '" alt="' + c.name + '" onerror="this.style.display=\'none\';this.nextSibling.style.display=\'flex\'">' +
                         '<div class="table-placeholder" style="display:none">' + initials(c.name) + '</div>';
      return '<div class="table-placeholder">' + initials(c.name) + '</div>';
    }

    //for table data
    function renderTable(data) {
      var tbody = document.getElementById('tableBody');
      tbody.innerHTML = '';

      if (data.length === 0) {
        tbody.innerHTML = '<tr><td colspan="7" style="text-align:center;color:#999;padding:20px;">No companies found in database.</td></tr>';
        return;
      }

      data.forEach(function(c) {
        //make sure string like Amazon's with apotrophe work adding \\ to the '
        var safeName = c.name.replace(/'/g, "\\'");

        tbody.innerHTML +=
          '<tr>' +
          '<td><div class="table-logo-wrap">' + logoImgTable(c) + '<span>' + c.name + '</span></div></td>' +
          '<td>' + c.industry + '</td>' +
          '<td>' + c.contact + '<br><span style="font-size:12px;color:#888;">' + c.phone + '</span></td>' +
          '<td>' + c.email + '</td>' +
         
          '<td><div class="action-btns">' +
            '<a href="../CompanyFunctions/EditCompany.php?id=' + c.id + '" > <i class="fa-solid fa-pen-to-square"></i> Edit</a>' +
            '<a href="../CompanyFunctions/DeleteCompany.php?id=' + c.id + '"> <i class="fa-solid fa-trash"></i> Delete</a>' +
          '</div></td>' +
          '</tr>';
      });
    }

    //For search bar
    function applyFilters() {
      var q = document.getElementById('searchInput').value.toLowerCase();
      var industry = document.getElementById('industryFilter').value;


      var filtered = companies.filter(function(c) {
        var matchText = c.name.toLowerCase().includes(q)
        var matchIndustry = industry === '' || c.industry === industry;

        return matchText && matchIndustry;
      });

      renderTable(filtered);
    }

    // Initial render
    applyFilters();
  </script>

</body>
</html>