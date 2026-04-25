<?php
ini_set('display_errors', 1);
ini_set('display_startup_errors', 1);
error_reporting(E_ALL);
session_start();
include '../../Connection.php';
include '../../ExecutePStatement.php';
include '../../AllFunctions.php';

checkAccess('Admin');

$companyID = $_GET['id'] ?? null;

if (!$companyID) {
    header("Location: ../Databases/CompanyDatabase.php");
    exit();
}

//get all data to show
$fetchSql = "SELECT * FROM companynamelist WHERE CompanyInt = ?";
$fetchRes = executePreparedStatement($fetchSql, [$companyID]);
$company = $fetchRes->fetch_assoc();

if (!$company) {
    die("Company not found.");
}

$error = null;

if ($_SERVER["REQUEST_METHOD"] == "POST") {

        //get image path to delete later
    $imageToDelete = "../../" . $company['PicturePath'];

        //delete the row from database first
    $deleteSql = "DELETE FROM companynamelist WHERE CompanyInt = ?";
    $deleteRes = executePreparedStatement($deleteSql, [$companyID]);

    if ($deleteRes) {
        //now delete the image
        if (!empty($company['PicturePath']) && file_exists($imageToDelete)) {
            unlink($imageToDelete);
        }

        header("Location: ../Databases/CompanyDatabase.php?msg=Deleted");
        exit();
    }else {
        $error = "Failed to delete the company. Please try again.";
    }   
    
}
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    
    <title>Delete Company</title>
    <link rel="stylesheet" href="../../CssFiles/Add_Edit.css">
</head>
<body>

    <div class="container">
        <h1 class="page-title">Delete Company</h1>
        <p class="subtitle">Review the details below before removal.</p>

        <div class="form-card">
            <h2 class="section-title">Confirmation Required</h2>

            <?php if ($error): ?>
                <div class="error"><?php echo $error; ?></div>
            <?php endif; ?>

            <div class="form-grid">
                <div class="form-group full-width">
                    <label>Company Logo</label>
                    <?php if (!empty($company['PicturePath'])): ?>
                        <img src="../../<?php echo $company['PicturePath']; ?>" class="logo-preview" alt="Logo" style="width: 120px; height: 120px; object-fit: contain;">
                    <?php else: ?>
                        <div class="detail-value">No Logo Uploaded</div>
                    <?php endif; ?>
                </div>

                <div class="form-group full-width">
                    <label>Company Name:</label>
                    <div class="detail-value"><?php echo $company['CompanyName']; ?></div>
                </div>

                <div class="form-group full-width">
                    <label>Industry:</label>
                    <div class="detail-value"><?php echo $company['CompanyType'] ?? 'N/A'; ?></div>
                </div>

                <div class="form-group full-width">
                    <label>Contact Number:</label>
                    <div class="detail-value"><?php echo $company['ContactNumber'] ?? 'N/A'; ?></div>
                </div>

                <div class="form-group full-width">
                    <label>Email Address:</label>
                    <div class="detail-value"><?php echo $company['EmailContact'] ?? 'N/A'; ?></div>
                </div>

                <div class="form-group full-width">
                    <label>Company Address:</label>
                    <div class="detail-value"><?php echo $company['CompanyAddress'] ?? 'N/A'; ?></div>
                </div>
            </div>

            <form action="" method="post">
                <div class="button-group">
                    <input type="submit" value="Delete" class="btn btn-secondary" style="background-color:#ff4d4d;">
                    <a href="../Databases/CompanyDatabase.php" class="btn btn-secondary" >Cancel</a>
                </div>
            </form>
        </div>
    </div>

</body>
</html>