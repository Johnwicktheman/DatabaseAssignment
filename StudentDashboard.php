<?php
session_start();
include 'Connection.php';
include 'ExecutePStatement.php';
include 'AllFunctions.php';

checkAccess(['Student']);

$sqlProfile = "SELECT sp.*, 
               lect.Username as LectName, 
               super.Username as SuperName
               FROM studentprofile sp
               LEFT JOIN assesoraccountlist lect ON sp.AssesorAccountIDLect = lect.AssessorAccountID
               LEFT JOIN assesoraccountlist super ON sp.AssesorAccountIDSuper = super.AssessorAccountID
               WHERE sp.StudentAccountID = ?";
$resProfile = executePreparedStatement($sqlProfile, [$_SESSION['user_id']]);
$profileData = $resProfile->fetch_assoc();


$sqlLect = "SELECT ar.* FROM assessmentrecords ar WHERE StudentID = ? AND AssesorType = 'Lecturer'";
$SeeResultsLect = executePreparedStatement($sqlLect, [$_SESSION['user_id']]);
$lectGrade = $SeeResultsLect->fetch_assoc();


$sqlSuper = "SELECT ar.* FROM assessmentrecords ar WHERE StudentID = ? AND AssesorType = 'Supervisor'";
$SeeResultsSuper = executePreparedStatement($sqlSuper, [$_SESSION['user_id']]);
$superGrade = $SeeResultsSuper->fetch_assoc();


$lectScoreNum = isset($lectGrade['Internship_Score']) ? (int)$lectGrade['Internship_Score'] : 0;
$superScoreNum = isset($superGrade['Internship_Score']) ? (int)$superGrade['Internship_Score'] : 0;
$totalScore = $lectScoreNum + $superScoreNum;


$current_user = $_SESSION['username'];
$role = $_SESSION['user_role'];

?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <link rel="stylesheet" href="CssFiles/StudentTableStyle.css">
    <link rel="stylesheet" href="CssFiles/StudentDashBoard.css">
    <link rel="stylesheet" href="CssFiles/ResultsViewing.css">

    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.5.1/css/all.min.css"> 
    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
    <link href="https://fonts.googleapis.com/css2?family=Bricolage+Grotesque:opsz,wght@12..96,200..800&display=swap" rel="stylesheet">
    <title>Student Dashboard</title>
</head>
<body>
     <nav>
        <p>STUDENT PANEL</p>
        <hr>
        <a href="#">Dashboard</a><br>
        <div id="logout">
            <a href="Logout.php" onclick="return confirm('Are you sure you want to logout?')">Logout</a>
        </div>
    </nav>

    <div class = "main">
        <div id="title">Dashboard</div>
        <hr>
        <header>Welcome Back, <?php echo $current_user. "!" ?> </header>
        <br>
        <div id="subtitle">You are logged in as <?php echo $role; ?>.</div>

        <div class="container">
            <div class="box">
                <i class="fa-solid fa-book"></i>

                <h1 style="margin-bottom:2px;">Lecturer <br> Evaluation</h1>
                <p style="margin-top:2px;">Evaluated by <?php echo $profileData['LectName']; ?></p>
                <?php if ($lectGrade): ?>
                    <p style="color:green; font-weight:bold;">Evaluation Finished!</p>
                    <button onclick="toggleBreakdown('lecturer')">Marks Breakdown</button>
                <?php else: ?>
                    <p style="color:#ff8787;">Pending Evaluation. Please wait</p>
                <?php endif; ?>
            </div>

            <div class="box">
                <i class="fa-solid fa-book"></i>
                <h1 style="margin-bottom:2px;">Supervisor Evaluation</h1>
                <p style="margin-top:2px;">Evaluated by <?php echo $profileData['SuperName']; ?></p>
                <?php if ($superGrade): ?>
                    <p style="color:green; font-weight:bold;">Evaluation Finished!</p>
                    <button onclick="toggleBreakdown('supervisor')">Marks Breakdown</button>
                <?php else: ?>
                    <p style="color:#ff8787;">Pending Evaluation. Please wait</p>
                <?php endif; ?>
            </div>
            <div class="box total-box">
                <h1>Final Internship Results</h1>
                
                <div style="display: flex; align-items: center; justify-content: center; gap: 15px; margin: 15px 0;">
                    <div id="gradeBadgeContainer"></div>
                    
                    <div style="text-align: left;">
                        <p style="font-size: 28px; margin: 0; color: #2e2e50;">
                            <strong id="finalPercentDisplay"><?php echo $totalScore/2; ?> / 100</strong>
                        </p>
                    </div>
                </div>

                <div id="statusBadgeContainer"></div>

                <p id="partialNotice" style="display:none; font-size: 12px; color: #aaa; margin-top: 15px;">
                    <em>*Final grade and status will be confirmed once both evaluations are complete.</em>
                </p>
            </div>
        </div>

    <div class="breakdown-panel" id="breakdownPanel" style="display:none; margin-top: 30px;">
    <div class="breakdown-header">
        <div>
            <h2 id="bAssessorTitle">Assessor Breakdown</h2>
            <p id="bAssessorName">Evaluated by –</p>
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
            <p id="bComments" style="font-style: italic; color: #555;">–</p>
        </div>
    </div>

    <button class="close-btn" onclick="closeBreakdown()">Close</button>
</div>

<script>
    // turn php data into json object 
    const evaluations = {
        lecturer: {
            data: <?php echo $lectGrade ? json_encode($lectGrade) : 'null'; ?>,
            name: "<?php echo $profileData['LectName']; ?>"
        },
        supervisor: {
            data: <?php echo $superGrade ? json_encode($superGrade) : 'null'; ?>,
            name: "<?php echo $profileData['SuperName']; ?>"
        }
    };

    const criteriaConfig = [
        { dbKey: 'understand_project', name: 'Undertaking Tasks/Projects', weight: 10 },
        { dbKey: 'health_and_safety',  name: 'Health & Safety Requirements', weight: 10 },
        { dbKey: 'connectivity', name: 'Connectivity & Theoretical Knowledge', weight: 10 },
        { dbKey: 'presentation', name: 'Presentation of the Report', weight: 15 },
        { dbKey: 'clarity', name: 'Clarity of Language & Illustration', weight: 10 },
        { dbKey: 'activities', name: 'Lifelong Learning Activities', weight: 15 },
        { dbKey: 'project_management', name: 'Project Management', weight: 15 },
        { dbKey: 'time_management', name: 'Time Management', weight: 15 }
    ];

    function toggleBreakdown(type) {
        const evalObj = evaluations[type];
        const data = evalObj.data;
        if (!data) return;

        // change header based on if its lecturer/supervisor
        document.getElementById('bAssessorTitle').textContent = (type === 'lecturer' ? 'Lecturer' : 'Supervisor') + " Breakdown";
        document.getElementById('bAssessorName').textContent = "Evaluated by " + evalObj.name;
        
        // get the total score of each assessor
        const scoreEl = document.getElementById('bTotalScore');
        const total = parseFloat(data.Internship_Score) || 0;
        scoreEl.textContent = total.toFixed(2) + ' / 100';
        scoreEl.style.color = total >= 40 ? '#1d9e75' : '#ff4d4d';

        // assessor comments
        document.getElementById('bComments').textContent = data.Feedback || "No feedback provided.";

        // print table out
        const tbody = document.getElementById('bCriteriaBody');
        tbody.innerHTML = '';

        criteriaConfig.forEach(c => {
            const rawMark = parseFloat(data[c.dbKey]) || 0;
            const mark100 = rawMark * 10;
            const weighted = ((mark100 * c.weight) / 100).toFixed(2);

            tbody.innerHTML += `
                <tr>
                    <td>${c.name}</td>
                    <td>${c.weight}%</td>
                    <td>${mark100.toFixed(2)}</td>
                    <td>${weighted}</td>
                    <td>
                        <div class="progress-bar-wrap">
                            <div class="progress-bar-fill" style="width:${mark100}%"></div>
                        </div>
                    </td>
                </tr>
            `;
        });

        // show panel when clicked
        const panel = document.getElementById('breakdownPanel');
        panel.style.display = 'block';
        panel.scrollIntoView({ behavior: 'smooth', block: 'start' });
    }

    //close panel when clicked
    function closeBreakdown() {
        document.getElementById('breakdownPanel').style.display = 'none';
    }

    //calculate the total points, and find the grade and status
    document.addEventListener("DOMContentLoaded", function() {
    const lect = evaluations.lecturer.data;
    const superv = evaluations.supervisor.data;
    
    let markCount = 0;
    if (lect) markCount++;
    if (superv) markCount++;
    
    const totalPoints = <?php echo $totalScore/2; ?>;
    
    //determine grade based on the total points
    function getGrade(score, count) {
        if (count < 2) return { grade: '–', cls: 'badge-pending' };
        if (score >= 80) return { grade: 'A', cls: 'badge-pass' };
        if (score >= 60) return { grade: 'B', cls: 'badge-pass' };
        if (score >= 40) return { grade: 'C', cls: 'badge-pass' };
        return { grade: 'F', cls: 'badge-fail' };
    }

    //determine status based on the total points
    function getStatus(score, count) {
        if (count === 0) {
            return '<span class="badge badge-pending">Not Marked</span>';
        } else if (count === 1) {
            return '<span class="badge badge-pending" style="background-color:orange; color:white;">Partially Marked</span>';
        } else {
            return score >= 40
                ? '<span class="badge badge-pass">Pass</span>'
                : '<span class="badge badge-fail">Fail</span>';
        }
    }

    //print inside html
    const g = getGrade(totalPoints, markCount);
    document.getElementById('gradeBadgeContainer').innerHTML = `
        <span class="badge ${g.cls}" style="font-size: 1.5em; padding: 10px 20px;">${g.grade}</span>
    `;
    
    document.getElementById('statusBadgeContainer').innerHTML = getStatus(totalPoints, markCount);

    //if only got one assessor marks, then show partial notice 
    if (markCount < 2) {
        document.getElementById('partialNotice').style.display = "block";
    }
});
</script>
    </div>
</body>
</html>