<?php
include '../../session.php';
include '../../connection.php';
include '../../ExecutePStatements.php';

if (!isLoggedIn() || getCurrentUser()['role'] !== 'admin') {
    header("Location: ../../login.php");
    exit;
}

$student_id = $_GET['id'] ?? null;

if (!$student_id) {
    echo "No student ID provided.";
    exit;
}

// Fetch assessment records for this student
// We JOIN with studentprofile to get the name for the header
$sql = "SELECT ar.*, sp.FirstName, sp.LastName 
        FROM assessmentrecords ar
        JOIN studentprofile sp ON ar.StudentID = sp.StudentAccountID
        WHERE ar.StudentID = ?";

$result = executePreparedStatement($sql, [$student_id]);
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <title>Assessment Record</title>
    <style>
        table { width: 100%; border-collapse: collapse; margin-top: 20px; }
        th, td { border: 1px solid #ddd; padding: 12px; text-align: left; }
        th { background-color: #f4f4f4; }
        .score { font-weight: bold; color: #2c3e50; }
    </style>
</head>
<body>
    <p><a href="student_profile.php?id=<?= $student_id ?>">← Back to Profile</a></p>
    
    <h2>Assessment Records</h2>

    <?php if ($result && $result->num_rows > 0): ?>
        <?php while($record = $result->fetch_assoc()): ?>
            <div style="border: 1px solid #eee; padding: 15px; margin-bottom: 20px; border-radius: 5px;">
                <h3>Record ID: <?= $record['AssessmentCode'] ?> (Type: <?= $record['AssesorType'] ?>)</h3>
                <table>
                    <tr>
                        <th>Criteria</th>
                        <th>Score</th>
                    </tr>
                    <tr><td>Project Understanding</td><td class="score"><?= $record['understand_project'] ?></td></tr>
                    <tr><td>Health & Safety</td><td class="score"><?= $record['health_and_safety'] ?></td></tr>
                    <tr><td>Presentation</td><td class="score"><?= $record['presentation'] ?></td></tr>
                    <tr><td>Project Management</td><td class="score"><?= $record['project_management'] ?></td></tr>
                    <tr><td>Time Management</td><td class="score"><?= $record['time_management'] ?></td></tr>
                    <tr>
                        <th style="background-color: #e8f4fd;">Total Internship Score</th>
                        <th style="background-color: #e8f4fd;"><?= $record['Internship_Score'] ?></th>
                    </tr>
                </table>
                <p><strong>Feedback:</strong> <em>"<?= htmlspecialchars($record['Feedback']) ?>"</em></p>
            </div>
        <?php endwhile; ?>
    <?php else: ?>
        <p>No assessment records found for this student yet.</p>
    <?php endif; ?>

</body>
</html>