
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

//Yo this query long bro and i am not chaning the javascript into php d
//since both asessors can mark students so just make it if one assessor havent marked then make all mark as null
//use AVG because two assessor give marks to one student
$fetchSql = "SELECT 
                acc.StudentAccountID as id, 
                CONCAT(prof.FirstName, ' ', prof.LastName) as name, 
                comp.CompanyName as company,
                AVG(ar.understand_project) as m1,
                AVG(ar.health_and_safety) as m2,
                AVG(ar.connectivity) as m3,
                AVG(ar.presentation) as m4,
                AVG(ar.clarity) as m5,
                AVG(ar.activities) as m6,
                AVG(ar.project_management) as m7,
                AVG(ar.time_management) as m8,
                AVG(ar.Internship_Score) as total,
                COUNT(ar.StudentID) as markCount,             
                MAX(CASE WHEN ar.AssesorType = 'Lecturer' THEN ar.Feedback END) as lectComment,
                MAX(CASE WHEN ar.AssesorType = 'Supervisor' THEN ar.Feedback END) as superComment
             FROM studentaccountlist acc
             JOIN studentprofile prof ON acc.StudentAccountID = prof.StudentAccountID 
             LEFT JOIN internship intern ON acc.StudentAccountID = intern.StudentAccountID
             LEFT JOIN companynamelist comp ON intern.CompanyINT = comp.CompanyInt
             LEFT JOIN assessmentrecords ar ON acc.StudentAccountID = ar.StudentID
             GROUP BY acc.StudentAccountID, prof.FirstName, prof.LastName, comp.CompanyName";

$AllStuff = executePreparedStatement($fetchSql, []);
$jsStudents = [];

foreach ($AllStuff as $row) {
    //get mark count by counting how many rows are in the assessmentrecord for a student
    //Max is 2
    $markCount = (int)$row['markCount'];
    
    //Dont show marks if not marked by both asessor
    if ($markCount < 2) {
        $marksArray = [0, 0, 0, 0, 0, 0, 0, 0];
    } else {
        //Otherwise grab the real averages
        $marksArray = [
            (float)($row['m1'] ?? 0), (float)($row['m2'] ?? 0), 
            (float)($row['m3'] ?? 0), (float)($row['m4'] ?? 0),
            (float)($row['m5'] ?? 0), (float)($row['m6'] ?? 0), 
            (float)($row['m7'] ?? 0), (float)($row['m8'] ?? 0)
        ];
    }

    //put them into array for javascript
    $jsStudents[] = [
        'id' => $row['id'],
        'name' => $row['name'],
        'company' => $row['company'] ?? 'N/A',
        'markCount' => $markCount,
        'marks' => $marksArray, 
        'strengths' => 'View full records for details', 
        'improve' => '',
        'lectComment' => $row['lectComment'],
        'superComment' => $row['superComment']
        
    ];
}
?>


<!DOCTYPE html>
<html lang="en">
<head>
  <meta charset="UTF-8" />
  <meta name="viewport" content="width=device-width, initial-scale=1.0"/>
  <title>Results Viewing</title>
  
  <link rel="stylesheet" href="../../CssFiles/searchbar.css">
  <link rel="stylesheet" href="../../CssFiles/AdminTableStyle.css">
  <link rel="stylesheet" href="../../CssFiles/ResultsViewing.css">
  <link rel="stylesheet" href="../../CssFiles/AdminDashBoard.css">

  <!-- font import -->
  <link rel="preconnect" href="https://fonts.googleapis.com">
  <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
  <link href="https://fonts.googleapis.com/css2?family=Bricolage+Grotesque:opsz,wght@12..96,200..800&display=swap" rel="stylesheet">

  <style>
    .back-link {
        display: inline-block;
        margin-bottom: 20px;
        color: #1e2957;
        text-decoration: none;
        font-weight: bold;
        transition: color 0.3s;
    }

    .back-link:hover {
        color: #3179c0;
        cursor:pointer;
    }
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
        <a href="ResultsViewing.php">Result Viewing</a><br>
        <div id="logout">
        <a href="../../Logout.php" onclick="return confirm('Are you sure you want to logout?')">Logout</a>
        </div>
  </nav>



  <main class="main">

    <div id="title"> Results Viewing </div>
        <hr>
        <header>Student Results Viewing</header>
        <a onclick="window.location.href='../../AdminDashboard.php'" class="back-link">&larr; Back to Dashboard</a>

    <div class="page-header">
      <h1>Student Assessment Records</h1>
    </div>
    

    <!-- Search -->
    <div class="search-bar-container">
      <label for="searchInput">Search:</label>
      <input type="text" id="searchInput" placeholder="Search by Student ID or Name..." oninput="filterTable()" />
    </div>

      <table>
        <thead>
          <tr>
            <th>Student ID</th>
            <th>Student Name</th>
            <th>Company</th>
            <th>Rounded Total Score</th>
            <th>Grade</th>
            <th>Status</th>
          </tr>
        </thead>
        <tbody id="tableBody"></tbody>
      </table>

    <p class="hint">Click on a row to view the full mark breakdown.</p>

    <div class="breakdown-panel" id="breakdownPanel">

        <div class="breakdown-header">
          <div>
            <h2 id="bStudentName">–</h2>
            
          </div>
          <div class="breakdown-total">
            <div class="label">Total Score</div>
            <div class="score" id="bTotalScore">–</div>
          </div>
        </div>

        <table class="breakdown-table">
          <thead>
            <tr>
              <th style="color: #fff;">Criteria</th>
              <th style="color: #fff;">Weight</th>
              <th style="color: #fff;">Mark / 100</th>
              <th style="color: #fff;">Weighted</th>
              <th style="color: #fff;">Bar</th>
            </tr>
          </thead>
          <tbody id="bCriteriaBody"></tbody>
        </table>

        <div class="comments-section">
          <h3>Assessor Comments</h3>

          <div class="comment-block">
            <label>Lecturer Comments</label>
            <p id="bStrengths">–</p>
          </div>

          <div class="comment-block">
            <label>Supervisor Comments</label>
            <p id="bImprove">–</p>
          </div>

        
      </div>

      <button class="close-btn" onclick="closeBreakdown()">Close</button>
    </div>

  </main>

  <script>
    //same thing as Company database
    var students = <?php echo json_encode($jsStudents); ?>;
    var criteria = [
      { name: 'Undertaking Tasks/Projects', weight: 10 },
      { name: 'Health & Safety Requirements', weight: 10 },
      { name: 'Connectivity & Theoretical Knowledge', weight: 10 },
      { name: 'Presentation of the Report', weight: 15 },
      { name: 'Clarity of Language & Illustration', weight: 10 },
      { name: 'Lifelong Learning Activities', weight: 15 },
      { name: 'Project Management', weight: 15 },
      { name: 'Time Management', weight: 15 },
    ];


  
   function calcTotal(marks) {
      var total = 0;
      for (var i = 0; i < marks.length; i++) {
        var markOutOf100 = marks[i] * 10;
        total += (markOutOf100 * criteria[i].weight) / 100; 
      }
      return total;
    }

    function getGrade(score, markCount) {
      if (markCount < 2) return { grade: '–',  cls: 'badge-pending' };
      if (score >= 80) return { grade: 'A',  cls: 'badge-pass' };
      if (score >= 60) return { grade: 'B',  cls: 'badge-pass' };
      if (score >= 40) return { grade: 'C',  cls: 'badge-pass' };
      return                  { grade: 'F',  cls: 'badge-fail' };
    }

    function statusBadge(s) {
      if (s.markCount === 0) {
        return '<span class="badge badge-pending">Not Marked</span>';
      } else if (s.markCount === 1) {
        return '<span class="badge badge-pending" style="background-color:orange;">Partially Marked</span>';
      } else {
        var total = calcTotal(s.marks);
        return total >= 40
          ? '<span class="badge badge-pass">Pass</span>'
          : '<span class="badge badge-fail">Fail</span>';
      }
    }

    function renderTable(data) {
      var tbody = document.getElementById('tableBody');
      tbody.innerHTML = '';

      if (data.length === 0) {
        tbody.innerHTML = '<tr><td colspan="6" style="text-align:center;color:#999;padding:20px;">No students found.</td></tr>';
        return;
      }

      //loop through every student in our data list
      data.forEach(function(s) {
        var total = Math.round(calcTotal(s.marks));
        var g = getGrade(total, s.markCount);

        //only show the score if both assessor have finished grading
        //else show -
        var totalDisplay = (s.markCount < 2) ? '–' : total.toFixed(2);

        tbody.innerHTML +=
          '<tr onclick="showBreakdown(\'' + s.id + '\')">' +
          '<td>' + s.id + '</td>' +
          '<td>' + s.name + '</td>' +
          '<td>' + s.company + '</td>' +
          '<td><strong>' + totalDisplay + '</strong></td>' +
          '<td><span class="badge ' + g.cls + '">' + g.grade + '</span></td>' +
          '<td>' + statusBadge(s) + '</td>' + 
          '</tr>';
      });
    }

    //filter for name and id
    function filterTable() {
      var q = document.getElementById('searchInput').value.toLowerCase();
      var filtered = students.filter(function(s) {
        
        var idString = String(s.id).toLowerCase();
        var nameString = String(s.name).toLowerCase();
        
        return nameString.includes(q) || idString.includes(q);
      });
      renderTable(filtered);
    }

    function showBreakdown(id) {

      var s = students.find(function(x){ return x.id == id; });
      if (!s) return;

      var total     = calcTotal(s.marks);
      var notMarked = s.marks.every(function(m){ return m === 0; });

      document.getElementById('bStudentName').textContent = s.name;


      var scoreEl = document.getElementById('bTotalScore');
      if (s.markCount === 0) {
        scoreEl.textContent = 'Not Marked';
        scoreEl.className   = 'score';
        scoreEl.style.color = '#ff9800';
        
      } else if (s.markCount === 1) {
        scoreEl.textContent = 'Partially Marked';
        scoreEl.className   = 'score';
        scoreEl.style.color = '#ff9800';

      } else {
        scoreEl.textContent = total.toFixed(2) + ' / 100'; 
        scoreEl.className   = total >= 60 ? 'score' : 'score fail'; 
        scoreEl.style.color = '';
      }

      if (s.markCount < 2) {
        document.getElementById('bStrengths').textContent = 'Comments are hidden until grading is complete.';
        document.getElementById('bImprove').textContent   = 'Comments are hidden until grading is complete.';
      } else {
        document.getElementById('bStrengths').textContent = s.lectComment || 'No comment entered.';
        document.getElementById('bImprove').textContent   = s.superComment || 'No comment entered.';
      }

      //All criteria
      var tbody = document.getElementById('bCriteriaBody');
      tbody.innerHTML = '';
      for (var i = 0; i < criteria.length; i++) {
        var rawMark = s.marks[i];
        var markOutOf100 = rawMark * 10;
        var weighted = ((markOutOf100 * criteria[i].weight) / 100).toFixed(2);
        
        tbody.innerHTML +=
          '<tr>' +
          '<td>' + criteria[i].name + '</td>' +
          '<td>' + criteria[i].weight + '%</td>' +
          '<td>' + (s.markCount < 2 ? '–' : markOutOf100.toFixed(2)) + '</td>' + 
          '<td>' + (s.markCount < 2 ? '–' : weighted) + '</td>' +
          '<td>' +
            '<div class="progress-bar-wrap">' +
              '<div class="progress-bar-fill" style="width:' + markOutOf100 + '%"></div>' +
            '</div>' +
          '</td>' +
          '</tr>';
      }
      var panel = document.getElementById('breakdownPanel');
      panel.classList.add('active');
      panel.scrollIntoView({ behavior: 'smooth', block: 'start' });
    }

    function closeBreakdown() {
      document.getElementById('breakdownPanel').classList.remove('active');
    }

    renderTable(students);
  </script>

</body>
</html>
