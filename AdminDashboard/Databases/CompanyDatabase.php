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

$companiesData = [];
if ($CompanyResult && $CompanyResult->num_rows > 0) {
    while ($row = $CompanyResult->fetch_assoc()) {
        $companiesData[] = [
            'id'       => $row['CompanyInt'],
            // Fallback to "Unknown" if name is missing so JS doesn't crash
            'name'     => $row['CompanyName'] ?: 'Unknown Company',
            'industry' => $row['CompanyType'] ?? 'Other',
            'address'  => $row['CompanyAddress'] ?? 'N/A',
            'contact'  => 'Contact Number',
            'phone'    => $row['ContactNumber'] ?? 'N/A',
            'email'    => $row['EmailContact'] ?? 'N/A',
            'logo'     => $row['PicturePath'] ?? '',
            
        ];
    }
}
?>

<!DOCTYPE html>
<html lang="en">
<head>
  <meta charset="UTF-8" />
  <meta name="viewport" content="width=device-width, initial-scale=1.0"/>
  <title>Company Database – Internship Assessment System</title>
  <link rel="stylesheet" href="../../CssFiles/CompanyDatabase.css">
  <style>
   
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

  <main class="main">


    <div class="page-header">
      <h1>Company Database</h1>
      <a href="../CompanyFunctions/AddCompany.php" class="btn btn-primary" style="text-decoration: none; display: inline-block;">+ Add Company</a>
    </div>
    

    <div class="toolbar">
      <input type="text" id="searchInput" placeholder="Search by company name" oninput="applyFilters()" />
      <select id="industryFilter" onchange="applyFilters()">
        <option value="">All Industries</option>
        <option value="Technology">Technology</option>
        <option value="Finance">Finance</option>
        <option value="Healthcare">Healthcare</option>
        <option value="Engineering">Engineering</option>
        <option value="Media">Media</option>
        <option value="Other">Other</option>
      </select>

      <div class="view-btns">
        <button class="view-btn active" id="gridBtn" onclick="setView('grid')" title="Card view">⊞</button>
        <button class="view-btn"        id="listBtn" onclick="setView('list')" title="Table view">☰</button>
      </div>
    </div>

    <div class="card-grid" id="cardGrid"></div>

    <div class="table-view" id="tableView">
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
    </div>

  </main>

  <script>
    // Pull the data straight from your PHP Database (Added fallback `?: '[]'` so it never crashes)
    var companies = <?php echo json_encode($companiesData) ?: '[]'; ?>;
    var currentView  = 'grid';

    /* ── Initials from name ── */
    function initials(name) {
      // Added safety check in case name is empty!
      if (!name || name.trim() === '') return '?';
      var parts = name.trim().split(' ');
      if (parts.length === 1) return parts[0][0].toUpperCase();
      return (parts[0][0] + parts[parts.length - 1][0]).toUpperCase();
    }

    /* ── Logo HTML helpers ── */
    function logoImgCard(c) {
      if (c.logo) return '<img class="company-logo" src="../../' + c.logo + '" alt="' + c.name + '" onerror="this.style.display=\'none\';this.nextSibling.style.display=\'flex\'">' +
                         '<div class="logo-placeholder" style="display:none">' + initials(c.name) + '</div>';
      return '<div class="logo-placeholder">' + initials(c.name) + '</div>';
    }

    function logoImgTable(c) {
      if (c.logo) return '<img class="table-logo" src="../../' + c.logo + '" alt="' + c.name + '" onerror="this.style.display=\'none\';this.nextSibling.style.display=\'flex\'">' +
                         '<div class="table-placeholder" style="display:none">' + initials(c.name) + '</div>';
      return '<div class="table-placeholder">' + initials(c.name) + '</div>';
    }

    /* ── Render cards ── */
    function renderCards(data) {
      var grid = document.getElementById('cardGrid');
      grid.innerHTML = '';

      if (data.length === 0) {
        grid.innerHTML = '<div class="empty-state">No companies found in database.</div>';
        return;
      }

      data.forEach(function(c) {
        // Safe string to stop names like "Bob's" from breaking the delete button
        var safeName = c.name.replace(/'/g, "\\'");

        grid.innerHTML +=
          '<div class="company-card">' +
            '<div class="card-top">' +
              logoImgCard(c) +
              '<div>' +
                '<div class="card-name">' + c.name + '</div>' +
                '<div class="card-industry">' + c.industry + '</div>' +
              '</div>' +
            '</div>' +
            '<div class="card-details">' +
              '<span>📍 ' + 'Address: ' + c.address + '</span>' +
              '<span>👤 ' + c.contact + ' : ' + c.phone + '</span>' +
              '<span>✉️ ' + 'Email: ' + c.email + '</span>' +
            '</div>' +
            '<div class="card-footer">' +
              
             
            '</div>' +
            '<div class="card-actions">' +
              '<a href="../CompanyFunctions/EditCompany.php?id=' + c.id + '" class="btn-sm btn-edit" style="text-decoration:none;">Edit</a>' +
              '<a href="../CompanyFunctions/DeleteCompany.php?id=' + c.id + '" class="btn-sm btn-del" style="text-decoration:none;")">Delete</a>' +
            '</div>' +
          '</div>';
      });
    }

    /* ── Render table ── */
    function renderTable(data) {
      var tbody = document.getElementById('tableBody');
      tbody.innerHTML = '';

      if (data.length === 0) {
        tbody.innerHTML = '<tr><td colspan="7" style="text-align:center;color:#999;padding:20px;">No companies found in database.</td></tr>';
        return;
      }

      data.forEach(function(c) {
        // Safe string to stop names like "Bob's" from breaking the delete button
        var safeName = c.name.replace(/'/g, "\\'");

        tbody.innerHTML +=
          '<tr>' +
          '<td><div class="table-logo-wrap">' + logoImgTable(c) + '<span>' + c.name + '</span></div></td>' +
          '<td>' + c.industry + '</td>' +
          '<td>' + c.contact + '<br><span style="font-size:12px;color:#888;">' + c.phone + '</span></td>' +
          '<td>' + c.email + '</td>' +
         
          '<td><div class="action-btns">' +
            '<a href="../CompanyFunctions/EditCompany.php?id=' + c.id + '" class="btn-sm btn-edit" style="text-decoration:none;">Edit</a>' +
            '<a href="../CompanyFunctions/DeleteCompany.php?id=' + c.id + '" class="btn-sm btn-del" style="text-decoration:none;" onclick="return confirm(\'Are you sure you want to delete ' + safeName + '?\')">Delete</a>' +
          '</div></td>' +
          '</tr>';
      });
    }

    /* ── Filters ── */
    function applyFilters() {
      var q        = document.getElementById('searchInput').value.toLowerCase();
      var industry = document.getElementById('industryFilter').value;


      var filtered = companies.filter(function(c) {
        var matchText     = c.name.toLowerCase().includes(q) || c.contact.toLowerCase().includes(q);
        var matchIndustry = industry === '' || c.industry === industry;

        return matchText && matchIndustry;
      });

      renderCards(filtered);
      renderTable(filtered);
    }

    /* ── View toggle ── */
    function setView(view) {
      currentView = view;
      if (view === 'grid') {
        document.getElementById('cardGrid').style.display  = 'grid';
        document.getElementById('tableView').style.display = 'none';
        document.getElementById('gridBtn').classList.add('active');
        document.getElementById('listBtn').classList.remove('active');
      } else {
        document.getElementById('cardGrid').style.display  = 'none';
        document.getElementById('tableView').style.display = 'block';
        document.getElementById('gridBtn').classList.remove('active');
        document.getElementById('listBtn').classList.add('active');
      }
    }

    // Initial render
    applyFilters();
  </script>

</body>
</html>