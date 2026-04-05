<?php
// Error Reporting for Debugging
ini_set('display_errors', 1);
error_reporting(E_ALL);

include '../../session.php';
include '../../connection.php';
include '../../ExecutePStatements.php';
include '../AdminFunction.php';

// Access Control [cite: 13, 22]
if (!isLoggedIn() || getCurrentUser()['role'] !== 'admin') {
    header("Location: ../../login.php");
    exit;
}

$message = "";
$error = "";
$action = $_GET['action'] ?? 'list'; // Default action is to show the list

// --- POST HANDLERS (Processing Data) ---
if ($_SERVER["REQUEST_METHOD"] == "POST") {
    if (isset($_POST['btn_add'])) {
        $result = CreateAssessors($_POST['username'], $_POST['password'], $_POST['assessor_type']);
        $result ? header("Location: ManageAssessors.php?msg=added") : $error = "Create failed.";
    } 
    elseif (isset($_POST['btn_update'])) {
        $result = updateAssessors($_POST['id'], $_POST['username'], $_POST['password'], $_POST['assessor_type']);
        $result ? header("Location: ManageAssessors.php?msg=updated") : $error = "Update failed.";
    } 
    elseif (isset($_POST['btn_delete'])) {
        $result = deleteAssessor($_POST['id']);
        $result ? header("Location: ManageAssessors.php?msg=deleted") : $error = "Delete failed.";
    }
}

// --- VIEW LOGIC ---
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <title>Manage Assessors</title>
    <style>
        table { width: 100%; border-collapse: collapse; margin-top: 10px; }
        th, td { border: 1px solid #ddd; padding: 10px; text-align: left; }
        .btn { padding: 5px 10px; text-decoration: none; border: 1px solid #000; background: #eee; color: #000; }
    </style>
</head>
<body>
    <p><a href="../AdminDashboard.php">← Back to Dashboard</a></p>
    <h2>Assessor Management</h2>

    <?php if (isset($_GET['msg'])): ?>
        <p style="color:green">Action completed: <?= htmlspecialchars($_GET['msg']) ?></p>
    <?php endif; ?>
    <?php if ($error): ?>
        <p style="color:red"><?= $error ?></p>
    <?php endif; ?>

    <hr>

    <?php if ($action == 'list'): ?>
        <a href="ManageAssessors.php?action=add" class="btn">+ Add New Assessor</a>
        <table>
            <tr>
                <th>ID</th><th>Username</th><th>Type</th><th>Actions</th>
            </tr>
            <?php
            $res = getAssessors();
            while ($row = $res->fetch_assoc()): ?>
                <tr>
                    <td><?= $row['AssessorAccountID'] ?></td>
                    <td><?= htmlspecialchars($row['Username']) ?></td>
                    <td><?= $row['AssesorType'] ?></td>
                    <td>
                        <a href="assigned_students.php?id=<?= $row['AssessorAccountID'] ?>&type=<?= $row['AssesorType'] ?>">View Students</a> |
                        <a href="ManageAssessors.php?action=edit&id=<?= $row['AssessorAccountID'] ?>">Edit</a> |
                        <a href="ManageAssessors.php?action=delete&id=<?= $row['AssessorAccountID'] ?>">Delete</a>
                    </td>
                </tr>
            <?php endwhile; ?>
        </table>

    <?php elseif ($action == 'add'): ?>
        <h3>Add New Assessor</h3>
        <form method="post" action="ManageAssessors.php">
            <input type="text" name="username" placeholder="Username" required><br><br>
            <input type="password" name="password" placeholder="Password" required><br><br>
            <select name="assessor_type" required>
                <option value="Lecturer">Lecturer</option>
                <option value="Supervisor">Supervisor</option>
            </select><br><br>
            <input type="submit" name="btn_add" value="Create Account">
            <a href="ManageAssessors.php">Cancel</a>
        </form>

    <?php elseif ($action == 'edit'): ?>
        <?php 
        $id = $_GET['id'];
        $data = executePreparedStatement("SELECT * FROM assesoraccountlist WHERE AssessorAccountID=?", [$id])->fetch_assoc();
        ?>
        <h3>Update Assessor: <?= htmlspecialchars($data['Username']) ?></h3>
        <form method="post" action="ManageAssessors.php">
            <input type="hidden" name="id" value="<?= $id ?>">
            <label>Username:</label><br>
            <input type="text" name="username" value="<?= $data['Username'] ?>" required><br><br>
            <label>Password:</label><br>
            <input type="text" name="password" value="<?= $data['Password'] ?>" required><br><br>
            <label>Type:</label><br>
            <select name="assessor_type">
                <option value="Lecturer" <?= $data['AssesorType'] == 'Lecturer' ? 'selected' : '' ?>>Lecturer</option>
                <option value="Supervisor" <?= $data['AssesorType'] == 'Supervisor' ? 'selected' : '' ?>>Supervisor</option>
            </select><br><br>
            <input type="submit" name="btn_update" value="Save Changes">
            <a href="ManageAssessors.php">Cancel</a>
        </form>

    <?php elseif ($action == 'delete'): ?>
        <?php $id = $_GET['id']; ?>
        <h3 style="color:red">Confirm Delete</h3>
        <p>Are you sure you want to remove Assessor ID: <?= htmlspecialchars($id) ?>?</p>
        <form method="post" action="ManageAssessors.php">
            <input type="hidden" name="id" value="<?= $id ?>">
            <input type="submit" name="btn_delete" value="Yes, Delete">
            <a href="ManageAssessors.php">No, Cancel</a>
        </form>
    <?php endif; ?>

</body>
</html>