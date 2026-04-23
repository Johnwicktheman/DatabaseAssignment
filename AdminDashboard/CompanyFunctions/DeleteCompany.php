<?php
ini_set('display_errors', 1);
ini_set('display_startup_errors', 1);
error_reporting(E_ALL);
session_start();
include '../../Connection.php';
include '../../ExecutePStatement.php';
include '../../AllFunctions.php';

checkAccess('Admin');

// 1. Get the ID from the URL (from the "Delete" link in your table)
$companyID = $_GET['id'] ?? null;

if (!$companyID) {
    header("Location: ../Databases/CompanyDatabase.php");
    exit();
}

// 2. Fetch the company name so we can show the user what they are deleting
$fetchSql = "SELECT CompanyName FROM companynamelist WHERE CompanyInt = ?";
$fetchRes = executePreparedStatement($fetchSql, [$companyID]);
$company = $fetchRes->fetch_assoc();

if (!$company) {
    die("Company not found.");
}

$error = null;

// 3. Handle the actual deletion when the button is pressed
if ($_SERVER["REQUEST_METHOD"] == "POST") {
    try {
        $deleteSql = "DELETE FROM companynamelist WHERE CompanyInt = ?";
        $deleteRes = executePreparedStatement($deleteSql, [$companyID]);

        if ($deleteRes) {
            header("Location: ../Databases/CompanyDatabase.php?msg=Deleted");
            exit();
        }
    } catch (Exception $e) {
        // This usually triggers if a student is still linked to this company
        $error = "Cannot delete this company because students are currently assigned to it.";
    }
}
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <title>Confirm Delete</title>
    <style>
        .warning-box { background: #f8d7da; color: #721c24; padding: 20px; border: 1px solid #f5c6cb; border-radius: 5px; }
        .btn-delete { background: red; color: white; padding: 10px 20px; border: none; cursor: pointer; text-decoration: none; }
        .btn-cancel { background: #6c757d; color: white; padding: 10px 20px; text-decoration: none; margin-left: 10px; border-radius: 2px; }
    </style>
</head>
<body>

    <h2>Delete Confirmation</h2>

    <div class="warning-box">
        <?php if ($error): ?>
            <p style="font-weight: bold;"><?php echo $error; ?></p>
        <?php else: ?>
            <p>Are you sure you want to delete <strong><?php echo htmlspecialchars($company['CompanyName']); ?></strong>?</p>
            <p>This will remove the company permanently from the system.</p>
        <?php endif; ?>
    </div>

    <form action="" method="post" style="margin-top: 20px;">
        <?php if (!$error): ?>
            <button type="submit" class="btn-delete">Yes, Delete Permanently</button>
        <?php endif; ?>
        <a href="../Databases/CompanyDatabase.php" class="btn-cancel">No, Go Back</a>
    </form>

</body>
</html>