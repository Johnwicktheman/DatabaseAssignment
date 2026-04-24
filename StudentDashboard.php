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
    <link rel="stylesheet" href="CssFiles/StudentDashBoard.css">
    <link rel="stylesheet" href="CssFiles/StudentTableStyle.css">

    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
    <link href="https://fonts.googleapis.com/css2?family=Bricolage+Grotesque:opsz,wght@12..96,200..800&display=swap" rel="stylesheet">
    <title>Document</title>
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
                <h1 style="margin-bottom:2px;">Lecturer <br> Evaluation</h1>
                <p style="margin-top:2px;">Evaluated by <?php echo $profileData['LectName']; ?></p>
                <?php if ($lectGrade): ?>
                    <p style="color:green; font-weight:bold;">Evaluation Finished!</p>
                    <button onclick="toggleBreakdown('lecturer')">Marks Breakdown</button>
                <?php else: ?>
                    <p style="color:#ff8787;">Pending Evaluation</p>
                <?php endif; ?>
            </div>

            <div class="box">
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
                <h1>Total Internship Score</h1>
                <?php if ($lectGrade || $superGrade): ?>
                    <p style="font-size: 36px; margin: 10px 0; color: #2e2e50;"><strong><?php echo $totalScore/2; ?> / 100</strong></p>
                    
                    <?php if (!$lectGrade || !$superGrade): ?>
                        <p style="font-size: 14px; color: #ccc;"><em>*Score is currently partial. Waiting on final evaluation.</em></p>
                    <?php endif; ?>

                <?php else: ?>
                    <p>No evaluations have been submitted yet.</p>
                <?php endif; ?>
            </div>
        </div>

        <div id="breakdown-display" style="display:none; margin-top: 30px; animation: slideDown 0.4s ease-out;">
                <div class="box" style="width: 1100px; text-align: left; padding: 30px;">
                    <h2 id="breakdown-title" style="color: #2e2e50; margin-top: 0;">Breakdown</h2>
                    <hr>
                    <table id="breakdown-table" style="width: 1000px; border-collapse: collapse;">
                    </table>
                    <button onclick="hideBreakdown()" style="margin-top: 20px; width: 150px; background-color: #aaa9a9;">Close</button>
                </div>
            </div>

            <script>
                // Prepare data from PHP
                const evaluations = {
                    lecturer: <?php echo $lectGrade ? json_encode($lectGrade) : 'null'; ?>,
                    supervisor: <?php echo $superGrade ? json_encode($superGrade) : 'null'; ?>
                };

                // Mapping Database Keys to Display Names and Weights
                const criteria = [
                    { dbKey: 'understand_project',   name: 'Understanding of Project', weight: 10 },
                    { dbKey: 'health_and_safety',    name: 'Health and Safety',        weight: 10 },
                    { dbKey: 'connectivity',         name: 'Connectivity',             weight: 10 },
                    { dbKey: 'presentation',         name: 'Presentation',             weight: 15 },
                    { dbKey: 'clarity',              name: 'Clarity',                  weight: 15 },
                    { dbKey: 'activities',           name: 'Activities',               weight: 10 },
                    { dbKey: 'project_management',   name: 'Project Management',       weight: 15 },
                    { dbKey: 'time_management',      name: 'Time Management',          weight: 15 }
                ];

                function toggleBreakdown(type) {
                    const data = evaluations[type];
                    const display = document.getElementById('breakdown-display');
                    const table = document.getElementById('breakdown-table');
                    const title = document.getElementById('breakdown-title');

                    if (!data) return;

                    title.innerText = (type === 'lecturer' ? 'Lecturer' : 'Supervisor') + " Marks Breakdown";
                    
                    let tableHTML = `
                    <thead>
                        <tr>
                            <th>CRITERIA</td>
                            <th>WEIGHT</td>
                            <th>MARK (0-10)</td>
                            <th>WEIGHTED SCORE</td>
                        </tr>
                    </thead>
                    `;

                    let totalWeighted = 0;

                    criteria.forEach(item => {
                        const rawMark = parseFloat(data[item.dbKey]) || 0;
                        const weightedScore = (rawMark * item.weight) / 10;
                        totalWeighted += weightedScore;

                        tableHTML += `
                            <tr style="border-bottom: 1px solid #eee;">
                                <td style="padding: 12px; font-weight: bold; color: #555;">${item.name}</td>
                                <td style="padding: 12px; text-align: center;">${item.weight}%</td>
                                <td style="padding: 12px; text-align: center;">${rawMark} / 10</td>
                                <td style="padding: 12px; text-align: center; color: #2e2e50; font-weight: 800;">${weightedScore.toFixed(2)}</td>
                            </tr>
                        `;
                    });
                    
                    if (totalWeighted >= 70){
                    tableHTML += `
                        <tr style="background-color: #f0f1f5;">
                            <td colspan="3" style="padding: 12px; font-weight: 900; color: #2e2e50; text-align: right;">FINAL CALCULATED SCORE: </td>
                            <td style="padding: 12px; text-align: center; color: #1d9e75; font-weight: 900; font-size: 1.2em;">${totalWeighted.toFixed(2)} / 100</td>
                        </tr>
                    `;
                    }

                    else if (totalWeighted >= 50){
                    tableHTML += `
                        <tr style="background-color: #f0f1f5;">
                            <td colspan="3" style="padding: 12px; font-weight: 900; color: #2e2e50; text-align: right;">FINAL CALCULATED SCORE: </td>
                            <td style="padding: 12px; text-align: center; color: orange; font-weight: 900; font-size: 1.2em;">${totalWeighted.toFixed(2)} / 100</td>
                        </tr>
                    `;
                    }

                    else{
                    tableHTML += `
                        <tr style="background-color: #f0f1f5;">
                            <td colspan="3" style="padding: 12px; font-weight: 900; color: #2e2e50; text-align: right;">FINAL CALCULATED SCORE: </td>
                            <td style="padding: 12px; text-align: center; color: red; font-weight: 900; font-size: 1.2em;">${totalWeighted.toFixed(2)} / 100</td>
                        </tr>
                    `;
                    }

                    table.innerHTML = tableHTML;
                    display.style.display = 'block';
                    display.scrollIntoView({ behavior: 'smooth' });
                }

                function hideBreakdown() {
                    document.getElementById('breakdown-display').style.display = 'none';
                }
            </script>
    </div>
</body>
</html>