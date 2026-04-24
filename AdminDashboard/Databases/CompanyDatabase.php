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

<!-- <!DOCTYPE html>
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
</html> -->


<!DOCTYPE html>
<html lang="en">
<head>
  <meta charset="UTF-8" />
  <meta name="viewport" content="width=device-width, initial-scale=1.0"/>
  <title>Company Database – Internship Assessment System</title>
  <style>
    * { margin: 0; padding: 0; box-sizing: border-box; }

    body {
      font-family: 'Segoe UI', Tahoma, Geneva, Verdana, sans-serif;
      background-color: #f0f2f5;
      display: flex;
      min-height: 100vh;
    }

    /* ── Sidebar ── */
    .sidebar {
      width: 220px;
      background-color: #1a1a2e;
      color: #fff;
      display: flex;
      flex-direction: column;
      padding: 24px 0;
      flex-shrink: 0;
    }

    .sidebar h2 {
      font-size: 15px;
      font-weight: 700;
      padding: 0 20px 20px;
      border-bottom: 1px solid #2e2e50;
      color: #a0a0cc;
      letter-spacing: 0.5px;
      text-transform: uppercase;
    }

    .sidebar nav { margin-top: 16px; }

    .sidebar nav a {
      display: block;
      padding: 11px 20px;
      color: #ccc;
      text-decoration: none;
      font-size: 14px;
      transition: background 0.2s;
    }

    .sidebar nav a:hover { background-color: #2e2e50; color: #fff; }
    .sidebar nav a.active { background-color: #4a4adb; color: #fff; font-weight: 600; }

    .sidebar .logout {
      margin-top: auto;
      padding: 11px 20px;
      color: #ff6b6b;
      text-decoration: none;
      font-size: 14px;
      display: block;
    }
    .sidebar .logout:hover { text-decoration: underline; }

    /* ── Main ── */
    .main { flex: 1; padding: 32px; overflow-y: auto; }

    .page-header {
      display: flex;
      justify-content: space-between;
      align-items: center;
      margin-bottom: 24px;
    }

    .page-header h1 { font-size: 22px; color: #1a1a2e; }

    /* ── View toggle ── */
    .toolbar {
      display: flex;
      gap: 10px;
      margin-bottom: 20px;
      align-items: center;
      flex-wrap: wrap;
    }

    .toolbar input {
      flex: 1;
      min-width: 180px;
      padding: 9px 14px;
      border: 1px solid #ccc;
      border-radius: 6px;
      font-size: 14px;
      outline: none;
      background: #fff;
    }
    .toolbar input:focus { border-color: #4a4adb; }

    .toolbar select {
      padding: 9px 14px;
      border: 1px solid #ccc;
      border-radius: 6px;
      font-size: 14px;
      outline: none;
      background: #fff;
    }
    .toolbar select:focus { border-color: #4a4adb; }

    .view-btns { display: flex; gap: 4px; }

    .view-btn {
      padding: 8px 13px;
      border: 1px solid #ccc;
      background: #fff;
      border-radius: 6px;
      cursor: pointer;
      font-size: 16px;
      transition: background 0.2s;
    }
    .view-btn.active { background: #4a4adb; color: #fff; border-color: #4a4adb; }

    /* ── Buttons ── */
    .btn {
      padding: 9px 18px;
      border: none;
      border-radius: 6px;
      font-size: 14px;
      font-weight: 600;
      cursor: pointer;
      transition: background 0.2s;
      white-space: nowrap;
    }
    .btn-primary { background-color: #4a4adb; color: #fff; }
    .btn-primary:hover { background-color: #3737c0; }
    .btn-secondary { background-color: #555; color: #fff; }
    .btn-secondary:hover { background-color: #333; }

    .btn-sm {
      padding: 5px 12px;
      font-size: 12px;
      border: none;
      border-radius: 4px;
      cursor: pointer;
      font-weight: 600;
    }
    .btn-edit { background-color: #f0f0ff; color: #4a4adb; }
    .btn-edit:hover { background-color: #dde0ff; }
    .btn-del  { background-color: #fff0f0; color: #e74c3c; }
    .btn-del:hover  { background-color: #ffd5d5; }

    /* ── Card grid view ── */
    .card-grid {
      display: grid;
      grid-template-columns: repeat(auto-fill, minmax(240px, 1fr));
      gap: 18px;
    }

    .company-card {
      background: #fff;
      border-radius: 8px;
      box-shadow: 0 2px 8px rgba(0,0,0,0.08);
      padding: 20px;
      display: flex;
      flex-direction: column;
      gap: 10px;
    }

    .card-top {
      display: flex;
      align-items: center;
      gap: 14px;
    }

    /* Company logo / placeholder */
    .company-logo {
      width: 52px;
      height: 52px;
      border-radius: 8px;
      object-fit: contain;
      border: 1px solid #eee;
      background: #f7f7f7;
      flex-shrink: 0;
    }

    .logo-placeholder {
      width: 52px;
      height: 52px;
      border-radius: 8px;
      background: #4a4adb;
      color: #fff;
      font-size: 18px;
      font-weight: 700;
      display: flex;
      align-items: center;
      justify-content: center;
      flex-shrink: 0;
    }

    .card-name { font-size: 15px; font-weight: 700; color: #1a1a2e; }
    .card-industry { font-size: 12px; color: #888; }

    .card-details { font-size: 13px; color: #555; line-height: 1.7; }
    .card-details span { display: block; }

    .card-footer {
      display: flex;
      justify-content: space-between;
      align-items: center;
      margin-top: 4px;
    }

    .card-actions { display: flex; gap: 6px; }

    /* ── Table view ── */
    .table-view { display: none; }

    .table-wrapper {
      background: #fff;
      border-radius: 8px;
      box-shadow: 0 2px 8px rgba(0,0,0,0.08);
      overflow: hidden;
    }

    table { width: 100%; border-collapse: collapse; font-size: 14px; }

    thead { background-color: #1a1a2e; color: #fff; }
    thead th { padding: 13px 16px; text-align: left; font-weight: 600; }

    tbody tr { border-bottom: 1px solid #eee; transition: background 0.15s; }
    tbody tr:last-child { border-bottom: none; }
    tbody tr:hover { background-color: #f7f8ff; }
    tbody td { padding: 11px 16px; color: #333; vertical-align: middle; }

    .table-logo-wrap {
      display: flex;
      align-items: center;
      gap: 10px;
    }

    .table-logo {
      width: 34px;
      height: 34px;
      border-radius: 6px;
      object-fit: contain;
      border: 1px solid #eee;
      background: #f7f7f7;
    }

    .table-placeholder {
      width: 34px;
      height: 34px;
      border-radius: 6px;
      background: #4a4adb;
      color: #fff;
      font-size: 13px;
      font-weight: 700;
      display: flex;
      align-items: center;
      justify-content: center;
      flex-shrink: 0;
    }

    .action-btns { display: flex; gap: 6px; }

    /* Badges */
    .badge {
      display: inline-block;
      padding: 3px 10px;
      border-radius: 12px;
      font-size: 12px;
      font-weight: 600;
    }
    .badge-active   { background-color: #d4f5e9; color: #0f6e56; }
    .badge-inactive { background-color: #eee;    color: #888; }

    /* Slots badge */
    .slots-badge {
      font-size: 12px;
      font-weight: 700;
      color: #4a4adb;
      background: #dde0ff;
      padding: 3px 9px;
      border-radius: 10px;
    }

    /* ── Modal ── */
    .modal-overlay {
      display: none;
      position: fixed;
      inset: 0;
      background: rgba(0,0,0,0.45);
      justify-content: center;
      align-items: center;
      z-index: 100;
    }
    .modal-overlay.active { display: flex; }

    .modal {
      background: #fff;
      border-radius: 10px;
      padding: 30px 28px;
      width: 100%;
      max-width: 500px;
      box-shadow: 0 8px 32px rgba(0,0,0,0.18);
      max-height: 92vh;
      overflow-y: auto;
    }

    .modal h2 { font-size: 18px; color: #1a1a2e; margin-bottom: 20px; }

    .form-group { margin-bottom: 15px; }

    .form-group label {
      display: block;
      font-size: 13px;
      font-weight: 600;
      color: #444;
      margin-bottom: 5px;
    }

    .form-group input,
    .form-group select {
      width: 100%;
      padding: 9px 12px;
      border: 1px solid #ccc;
      border-radius: 6px;
      font-size: 14px;
      outline: none;
      background: #fff;
    }

    .form-group input:focus,
    .form-group select:focus { border-color: #4a4adb; }

    .hint-text { font-size: 11px; color: #aaa; margin-top: 4px; }

    .form-row { display: flex; gap: 12px; }
    .form-row .form-group { flex: 1; }

    /* Logo preview inside modal */
    .logo-preview-wrap {
      display: flex;
      align-items: center;
      gap: 12px;
      margin-top: 8px;
    }

    #logoPreview {
      width: 48px;
      height: 48px;
      border-radius: 8px;
      object-fit: contain;
      border: 1px solid #eee;
      background: #f7f7f7;
      display: none;
    }

    #logoPlaceholderPreview {
      width: 48px;
      height: 48px;
      border-radius: 8px;
      background: #4a4adb;
      color: #fff;
      font-size: 18px;
      font-weight: 700;
      display: flex;
      align-items: center;
      justify-content: center;
    }

    .modal-actions {
      display: flex;
      justify-content: flex-end;
      gap: 10px;
      margin-top: 20px;
    }

    .empty-state {
      text-align: center;
      color: #999;
      padding: 40px 20px;
      font-size: 14px;
    }
  </style>
</head>
<body>

  <!-- Sidebar -->
  <aside class="sidebar">
    <h2>Admin Panel</h2>
    <nav>
      <a href="../../AssessorDashboard.php">Dashboard</a>
      <a href="StudentDatabase.php">Student Accounts</a>
      <a href="AssessorDatabase.php">Assessor Accounts</a>
      <a href="CompanyDatabase.php" class="active">Company Database</a>
      <a href="results.php">Result Viewing</a>
      <a href="logout.php" class="logout" >Logout</a>
    </nav>
  </aside>

  <!-- Main -->
  <main class="main">

    <div class="page-header">
      <h1>Company Database</h1>
      <button class="btn btn-primary" onclick="openModal('add')">+ Add Company</button>
    </div>

    <!-- Toolbar: search + filter + view toggle -->
    <div class="toolbar">
      <input type="text" id="searchInput" placeholder="Search by company name or contact..." oninput="applyFilters()" />
      <select id="industryFilter" onchange="applyFilters()">
        <option value="">All Industries</option>
        <option value="Technology">Technology</option>
        <option value="Finance">Finance</option>
        <option value="Healthcare">Healthcare</option>
        <option value="Engineering">Engineering</option>
        <option value="Media">Media</option>
        <option value="Other">Other</option>
      </select>
      <select id="statusFilter" onchange="applyFilters()">
        <option value="">All Statuses</option>
        <option value="Active">Active</option>
        <option value="Inactive">Inactive</option>
      </select>
      <div class="view-btns">
        <button class="view-btn active" id="gridBtn" onclick="setView('grid')" title="Card view">⊞</button>
        <button class="view-btn"        id="listBtn" onclick="setView('list')" title="Table view">☰</button>
      </div>
    </div>

    <!-- Card grid view -->
    <div class="card-grid" id="cardGrid"></div>

    <!-- Table view -->
    <div class="table-view" id="tableView">
      <div class="table-wrapper">
        <table>
          <thead>
            <tr>
              <th>Company</th>
              <th>Industry</th>
              <th>Contact Person</th>
              <th>Email</th>
              <th>Slots</th>
              <th>Status</th>
              <th>Actions</th>
            </tr>
          </thead>
          <tbody id="tableBody"></tbody>
        </table>
      </div>
    </div>

  </main>

  <!-- Add / Edit Modal -->
  <div class="modal-overlay" id="modalOverlay">
    <div class="modal">
      <h2 id="modalTitle">Add Company</h2>

      <div class="form-group">
        <label>Company Name</label>
        <input type="text" id="mName" placeholder="e.g. TechCorp Sdn Bhd" />
      </div>

      <div class="form-row">
        <div class="form-group">
          <label>Industry</label>
          <select id="mIndustry">
            <option value="Technology">Technology</option>
            <option value="Finance">Finance</option>
            <option value="Healthcare">Healthcare</option>
            <option value="Engineering">Engineering</option>
            <option value="Media">Media</option>
            <option value="Other">Other</option>
          </select>
        </div>
        <div class="form-group">
          <label>Internship Slots</label>
          <input type="number" id="mSlots" min="1" placeholder="e.g. 3" />
        </div>
      </div>

      <div class="form-group">
        <label>Address</label>
        <input type="text" id="mAddress" placeholder="Street address, city, state" />
      </div>

      <div class="form-row">
        <div class="form-group">
          <label>Contact Person</label>
          <input type="text" id="mContact" placeholder="Full name" />
        </div>
        <div class="form-group">
          <label>Phone Number</label>
          <input type="text" id="mPhone" placeholder="e.g. 012-3456789" />
        </div>
      </div>

      <div class="form-group">
        <label>Company Email</label>
        <input type="email" id="mEmail" placeholder="hr@company.com" />
      </div>

      <div class="form-group">
        <label>Logo URL <span style="font-weight:400;color:#aaa;">(optional)</span></label>
        <input type="text" id="mLogo" placeholder="https://company.com/logo.png" oninput="previewLogo()" />
        <p class="hint-text">Paste a direct link to the company logo image. Leave blank to use initials.</p>
        <div class="logo-preview-wrap">
          <img id="logoPreview" src="" alt="Logo preview" onerror="logoError()" />
          <div id="logoPlaceholderPreview">?</div>
          <span style="font-size:12px;color:#888;">Preview</span>
        </div>
      </div>

      <div class="form-row">
        <div class="form-group">
          <label>Status</label>
          <select id="mStatus">
            <option value="Active">Active</option>
            <option value="Inactive">Inactive</option>
          </select>
        </div>
      </div>

      <div class="modal-actions">
        <button class="btn btn-secondary" onclick="closeModal()">Cancel</button>
        <button class="btn btn-primary"   onclick="saveCompany()">Save</button>
      </div>
    </div>
  </div>

  <script>
    // Sample data – in real app comes from PHP/MySQL
    var companies = [
      {
        id: 'C001', name: 'TechCorp Sdn Bhd',     industry: 'Technology',  slots: 4,
        address: 'Level 12, Menara TechCorp, Kuala Lumpur',
        contact: 'Amir Hassan',  phone: '012-3456789', email: 'hr@techcorp.com.my',
        logo: 'https://upload.wikimedia.org/wikipedia/commons/a/a9/Amazon_logo.svg',
        status: 'Active'
      },
      {
        id: 'C002', name: 'Innovate MY',           industry: 'Technology',  slots: 2,
        address: 'Cyberjaya, Selangor',
        contact: 'Lina Tan',     phone: '011-9876543', email: 'intern@innovatemy.com',
        logo: '',
        status: 'Active'
      },
      {
        id: 'C003', name: 'ByteWorks Solutions',   industry: 'Technology',  slots: 3,
        address: 'George Town, Penang',
        contact: 'Suresh Kumar', phone: '016-1122334', email: 'careers@byteworks.my',
        logo: '',
        status: 'Active'
      },
      {
        id: 'C004', name: 'DataNest Sdn Bhd',      industry: 'Finance',     slots: 2,
        address: 'Jalan Ampang, Kuala Lumpur',
        contact: 'Wong Mei Lin', phone: '019-5556677', email: 'hr@datanest.com.my',
        logo: '',
        status: 'Active'
      },
      {
        id: 'C005', name: 'MediCare Solutions',    industry: 'Healthcare',  slots: 1,
        address: 'Johor Bahru, Johor',
        contact: 'Dr. Farah',   phone: '013-8899001', email: 'intern@medicaremy.com',
        logo: '',
        status: 'Inactive'
      },
    ];

    var editingIndex = null;
    var currentView  = 'grid';

    /* ── Initials from name ── */
    function initials(name) {
      var parts = name.trim().split(' ');
      if (parts.length === 1) return parts[0][0].toUpperCase();
      return (parts[0][0] + parts[parts.length - 1][0]).toUpperCase();
    }

    /* ── Logo HTML helpers ── */
    function logoImgCard(c) {
      if (c.logo) return '<img class="company-logo" src="' + c.logo + '" alt="' + c.name + '" onerror="this.style.display=\'none\';this.nextSibling.style.display=\'flex\'">' +
                         '<div class="logo-placeholder" style="display:none">' + initials(c.name) + '</div>';
      return '<div class="logo-placeholder">' + initials(c.name) + '</div>';
    }

    function logoImgTable(c) {
      if (c.logo) return '<img class="table-logo" src="' + c.logo + '" alt="' + c.name + '" onerror="this.style.display=\'none\';this.nextSibling.style.display=\'flex\'">' +
                         '<div class="table-placeholder" style="display:none">' + initials(c.name) + '</div>';
      return '<div class="table-placeholder">' + initials(c.name) + '</div>';
    }

    /* ── Render cards ── */
    function renderCards(data) {
      var grid = document.getElementById('cardGrid');
      grid.innerHTML = '';

      if (data.length === 0) {
        grid.innerHTML = '<div class="empty-state">No companies found.</div>';
        return;
      }

      data.forEach(function(c) {
        var idx = companies.indexOf(c);
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
              '<span>📍 ' + c.address + '</span>' +
              '<span>👤 ' + c.contact + ' · ' + c.phone + '</span>' +
              '<span>✉️ ' + c.email + '</span>' +
            '</div>' +
            '<div class="card-footer">' +
              '<span class="slots-badge">' + c.slots + ' slot' + (c.slots !== 1 ? 's' : '') + '</span>' +
              '<span class="badge ' + (c.status === 'Active' ? 'badge-active' : 'badge-inactive') + '">' + c.status + '</span>' +
            '</div>' +
            '<div class="card-actions">' +
              '<button class="btn-sm btn-edit" onclick="openModal(\'edit\',' + idx + ')">Edit</button>' +
              '<button class="btn-sm btn-del"  onclick="deleteCompany(' + idx + ')">Delete</button>' +
            '</div>' +
          '</div>';
      });
    }

    /* ── Render table ── */
    function renderTable(data) {
      var tbody = document.getElementById('tableBody');
      tbody.innerHTML = '';

      if (data.length === 0) {
        tbody.innerHTML = '<tr><td colspan="7" style="text-align:center;color:#999;padding:20px;">No companies found.</td></tr>';
        return;
      }

      data.forEach(function(c) {
        var idx = companies.indexOf(c);
        tbody.innerHTML +=
          '<tr>' +
          '<td><div class="table-logo-wrap">' + logoImgTable(c) + '<span>' + c.name + '</span></div></td>' +
          '<td>' + c.industry + '</td>' +
          '<td>' + c.contact + '<br><span style="font-size:12px;color:#888;">' + c.phone + '</span></td>' +
          '<td>' + c.email + '</td>' +
          '<td><span class="slots-badge">' + c.slots + '</span></td>' +
          '<td><span class="badge ' + (c.status === 'Active' ? 'badge-active' : 'badge-inactive') + '">' + c.status + '</span></td>' +
          '<td><div class="action-btns">' +
            '<button class="btn-sm btn-edit" onclick="openModal(\'edit\',' + idx + ')">Edit</button>' +
            '<button class="btn-sm btn-del"  onclick="deleteCompany(' + idx + ')">Delete</button>' +
          '</div></td>' +
          '</tr>';
      });
    }

    /* ── Filters ── */
    function applyFilters() {
      var q        = document.getElementById('searchInput').value.toLowerCase();
      var industry = document.getElementById('industryFilter').value;
      var status   = document.getElementById('statusFilter').value;

      var filtered = companies.filter(function(c) {
        var matchText     = c.name.toLowerCase().includes(q) || c.contact.toLowerCase().includes(q);
        var matchIndustry = industry === '' || c.industry === industry;
        var matchStatus   = status   === '' || c.status   === status;
        return matchText && matchIndustry && matchStatus;
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

    /* ── Logo preview inside modal ── */
    function previewLogo() {
      var url = document.getElementById('mLogo').value.trim();
      var img = document.getElementById('logoPreview');
      var ph  = document.getElementById('logoPlaceholderPreview');
      var name = document.getElementById('mName').value.trim();

      if (url) {
        img.src = url;
        img.style.display = 'block';
        ph.style.display  = 'none';
      } else {
        img.style.display = 'none';
        ph.style.display  = 'flex';
        ph.textContent    = name ? initials(name) : '?';
      }
    }

    function logoError() {
      var img = document.getElementById('logoPreview');
      var ph  = document.getElementById('logoPlaceholderPreview');
      img.style.display = 'none';
      ph.style.display  = 'flex';
    }

    /* ── Modal ── */
    function openModal(mode, index) {
      editingIndex = null;
      document.getElementById('modalOverlay').classList.add('active');

      // Reset preview
      document.getElementById('logoPreview').style.display = 'none';
      document.getElementById('logoPlaceholderPreview').style.display = 'flex';
      document.getElementById('logoPlaceholderPreview').textContent = '?';

      if (mode === 'edit' && index !== undefined) {
        editingIndex = index;
        var c = companies[index];
        document.getElementById('modalTitle').textContent = 'Edit Company';
        document.getElementById('mName').value     = c.name;
        document.getElementById('mIndustry').value = c.industry;
        document.getElementById('mSlots').value    = c.slots;
        document.getElementById('mAddress').value  = c.address;
        document.getElementById('mContact').value  = c.contact;
        document.getElementById('mPhone').value    = c.phone;
        document.getElementById('mEmail').value    = c.email;
        document.getElementById('mLogo').value     = c.logo;
        document.getElementById('mStatus').value   = c.status;
        previewLogo();
      } else {
        document.getElementById('modalTitle').textContent = 'Add Company';
        ['mName','mAddress','mContact','mPhone','mEmail','mLogo'].forEach(function(id){ document.getElementById(id).value = ''; });
        document.getElementById('mIndustry').value = 'Technology';
        document.getElementById('mSlots').value    = '';
        document.getElementById('mStatus').value   = 'Active';
      }
    }

    function closeModal() {
      document.getElementById('modalOverlay').classList.remove('active');
    }

    function saveCompany() {
      var name     = document.getElementById('mName').value.trim();
      var industry = document.getElementById('mIndustry').value;
      var slots    = parseInt(document.getElementById('mSlots').value);
      var address  = document.getElementById('mAddress').value.trim();
      var contact  = document.getElementById('mContact').value.trim();
      var phone    = document.getElementById('mPhone').value.trim();
      var email    = document.getElementById('mEmail').value.trim();
      var logo     = document.getElementById('mLogo').value.trim();
      var status   = document.getElementById('mStatus').value;

      if (!name || !address || !contact || !phone || !email || !slots) {
        alert('Please fill in all required fields.');
        return;
      }

      var entry = {
        id: editingIndex !== null ? companies[editingIndex].id : 'C' + String(companies.length + 1).padStart(3, '0'),
        name: name, industry: industry, slots: slots,
        address: address, contact: contact, phone: phone,
        email: email, logo: logo, status: status
      };

      if (editingIndex !== null) {
        companies[editingIndex] = entry;
      } else {
        companies.push(entry);
      }

      closeModal();
      applyFilters();

      // TODO: POST to save_company.php
    }

    function deleteCompany(index) {
      if (confirm('Delete ' + companies[index].name + '? This cannot be undone.')) {
        companies.splice(index, 1);
        applyFilters();
        // TODO: POST to delete_company.php?id=...
      }
    }

    // Initial render
    applyFilters();
  </script>

</body>
</html>
