<?php
// Error Reporting for Debugging
ini_set('display_errors', 1);
error_reporting(E_ALL);

include '../../session.php';
include '../../connection.php';
include '../../ExecutePStatements.php';
include '../AdminFunction.php';

// Access Control

if (!isLoggedIn() || getCurrentUser()['role'] !== 'admin') {
    header("Location: ../../login.php");
    exit;
}

$message = "";
$error = "";
$action = $_GET['action'] ?? 'list'; // Default view is the list

// --- POST HANDLERS (Processing Data) ---
if ($_SERVER["REQUEST_METHOD"] == "POST") {
    // 1. ADD STUDENT
    if (isset($_POST['btn_add'])) {
        $result = CreateStudents(
            $_POST['username'], $_POST['password'], $_POST['fname'], $_POST['lname'], 
            $_POST['lecturer_id'], $_POST['supervisor_id'], $_POST['internship_code'], $_POST['programme_code']
        );
        $result ? header("Location: ManageStudents.php?msg=Student Added") : $error = "Failed to create student.";
    } 
    // 2. UPDATE STUDENT
    elseif (isset($_POST['btn_update'])) {
        $student_id = $_POST['id'];
        $lect_id = !empty($_POST['lecturer_id']) ? $_POST['lecturer_id'] : null;
        $super_id = !empty($_POST['supervisor_id']) ? $_POST['supervisor_id'] : null;

        $conn->begin_transaction();
        try {
            updateStudents($student_id, $_POST['username'], $_POST['password']);
            $sql_profile = "UPDATE studentprofile SET 
                            FirstName = ?, LastName = ?, ProgrammeCode = ?, 
                            InternshipCode = ?, AssesorAccountIDLect = ?, AssesorAccountIDSuper = ? 
                            WHERE StudentAccountID = ?";
            executePreparedStatement($sql_profile, [
                $_POST['fname'], $_POST['lname'], $_POST['programme_code'], 
                $_POST['internship_code'], $lect_id, $super_id, $student_id
            ]);
            $conn->commit();
            header("Location: ManageStudents.php?msg=Update Successful");
        } catch (Exception $e) {
            $conn->rollback();
            $error = "Update failed: " . $e->getMessage();
        }
    } 
    // 3. DELETE STUDENT
    elseif (isset($_POST['btn_delete'])) {
        $result = deleteStudent($_POST['id']);
        $result ? header("Location: ManageStudents.php?msg=Student Deleted") : $error = "Delete failed.";
    }
}

// Global data for forms
$lecturers = getAssessorsByType('Lecturer');
$supervisors = getAssessorsByType('Supervisor');
$internships = getInternships();


// Get the sort preference from URL, default to ID
$currentSort = $_GET['sort'] ?? 'sa.StudentAccountID';
$res = getStudents(null, $currentSort); 

?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <title>Manage Students</title>
    <style>
        body { font-family: sans-serif; padding: 20px; }
        table { width: 100%; border-collapse: collapse; margin-top: 15px; }
        th, td { border: 1px solid #ddd; padding: 10px; text-align: left; }
        th { background: #f4f4f4; }
        .btn { padding: 6px 12px; text-decoration: none; border: 1px solid #333; background: #eee; cursor: pointer; }
        form { background: #f9f9f9; padding: 20px; border: 1px solid #ccc; max-width: 600px; }
    </style>
</head>
<body>
    <p><a href="../AdminDashboard.php">← Back to Dashboard</a></p>
    <h2>Student Management Hub</h2>

    <?php if (isset($_GET['msg'])): ?>
        <p style="color:green; font-weight:bold;"><?= htmlspecialchars($_GET['msg']) ?></p>
    <?php endif; ?>
    <?php if ($error): ?>
        <p style="color:red; font-weight:bold;"><?= $error ?></p>
    <?php endif; ?>

    <hr>

    <?php if ($action == 'list'): ?>
        <a href="ManageStudents.php?action=add" class="btn">+ Add New Student</a>
        <table>
            <tr>
            <th><a href="ManageStudents.php?sort=sa.StudentAccountID">ID</a></th>
            <th><a href="ManageStudents.php?sort=sa.Username">Username</a></th>
            <th>Password</th> 
            <th><a href="ManageStudents.php?sort=sp.FirstName">First Name</a></th> 
            <th><a href="ManageStudents.php?sort=sp.LastName">Last Name</a></th>
            <th><a href="ManageStudents.php?sort=sp.ProgrammeCode">Programme</a></th>
            <th>Actions</th>
            </tr>
            <?php
             // Assuming this JOINs with studentprofile or you handle name fetch
            while ($row = $res->fetch_assoc()): ?>
                <tr>
                    <td><?= $row['StudentAccountID'] ?></td>
                    <td><?= htmlspecialchars($row['Username']) ?></td>
                    <td><?= htmlspecialchars($row['Password']) ?></td>
                    <td><?= htmlspecialchars($row['FirstName'] ?? 'N/A') ?></td>
                    <td><?= htmlspecialchars($row['LastName'] ?? 'N/A') ?></td>
                    <td><?= htmlspecialchars($row['ProgrammeCode'] ?? 'Pending') ?></td>
                    <td>
                        <a href="student_profile.php?id=<?= $row['StudentAccountID'] ?>">View Profile</a> |
                        <a href="ManageStudents.php?action=edit&id=<?= $row['StudentAccountID'] ?>">Edit</a> |
                        <a href="ManageStudents.php?action=delete&id=<?= $row['StudentAccountID'] ?>">Delete</a>
                    </td>
                </tr>
            <?php endwhile; ?>
        </table>

    <?php elseif ($action == 'add'): ?>
        <h3>Add New Student Profile</h3>
        <form method="post" action="ManageStudents.php">
            <label>First Name:</label> <input type="text" name="fname" required><br><br>
            <label>Last Name:</label> <input type="text" name="lname" required><br><br>
            <label>Username:</label> <input type="text" name="username" required><br><br>
            <label>Password:</label> <input type="password" name="password" required><br><br>
            <label>Programme Code:</label> <input type="text" name="programme_code" placeholder="e.g. CS101" required><br><br>
            
            <label>Internship</label>
            <select name="internship_code" required>
                <?php while($row = $internships->fetch_assoc()): ?>
                    <option value="<?= $row['InternshipCode'] ?>"><?= $row['CompanyName'] ?></option>
                <?php endwhile; ?>
            </select><br><br>

            <label>Lecturer:</label>
            <select name="lecturer_id">
                <option value="">-- None --</option>
                <?php while($row = $lecturers->fetch_assoc()): ?>
                    <option value="<?= $row['AssessorAccountID'] ?>"><?= $row['Username'] ?></option>
                <?php endwhile; ?>
            </select><br><br>

            <input type="submit" name="btn_add" value="Create Student and Profile" class="btn">
            <a href="ManageStudents.php">Cancel</a>
        </form>

    <?php elseif ($action == 'edit'): ?>
        <?php 
        $id = $_GET['id'];
        $sql = "SELECT sa.*, sp.* FROM studentaccountlist sa JOIN studentprofile sp ON sa.StudentAccountID = sp.StudentAccountID WHERE sa.StudentAccountID = ?";
        $data = executePreparedStatement($sql, [$id])->fetch_assoc();
        ?>
        <h3>Update Student: <?= htmlspecialchars($data['Username']) ?></h3>
        <form method="post" action="ManageStudents.php">
            <input type="hidden" name="id" value="<?= $id ?>">
            <label>First Name:</label> <input type="text" name="fname" value="<?= $data['FirstName'] ?>" required><br><br>
            <label>Last Name:</label> <input type="text" name="lname" value="<?= $data['LastName'] ?>" required><br><br>
            <label>Username:</label> <input type="text" name="username" value="<?= $data['Username'] ?>" required><br><br>
            <label>Password:</label> <input type="text" name="password" value="<?= $data['Password'] ?>" required><br><br>
            <label>Programme Code:</label> <input type="text" name="programme_code" value="<?= $data['ProgrammeCode'] ?>" required><br><br>
            
            <label>Internship:</label>
            <select name="internship_code" required>
                <?php $internships->data_seek(0); while($row = $internships->fetch_assoc()): ?>
                    <option value="<?= $row['InternshipCode'] ?>" <?= ($row['InternshipCode'] == $data['InternshipCode']) ? 'selected' : '' ?>>
                        <?= $row['CompanyName'] ?>
                    </option>
                <?php endwhile; ?>
            </select><br><br>

            <label>Lecturer:</label>
            <select name="lecturer_id">
                <option value="">-- None --</option>
                <?php $lecturers->data_seek(0); while($row = $lecturers->fetch_assoc()): ?>
                    <option value="<?= $row['AssessorAccountID'] ?>" <?= ($row['AssessorAccountID'] == $data['AssesorAccountIDLect']) ? 'selected' : '' ?>>
                        <?= $row['Username'] ?>
                    </option>
                <?php endwhile; ?>
            </select><br><br>

            <input type="submit" name="btn_update" value="Save Changes" class="btn">
            <a href="ManageStudents.php">Cancel</a>
        </form>

    <?php elseif ($action == 'delete'): ?>
        <div style="background:#fff0f0; border:1px solid red; padding:20px;">
            <h3>Confirm Delete</h3>
            <p>Are you sure you want to remove Student ID: <?= htmlspecialchars($_GET['id']) ?>?</p>
            <form method="post" action="ManageStudents.php">
                <input type="hidden" name="id" value="<?= $_GET['id'] ?>">
                <input type="submit" name="btn_delete" value="Yes, Delete Permanently" class="btn">
                <a href="ManageStudents.php">No, Cancel</a>
            </form>
        </div>
    <?php endif; ?>

</body>
</html>