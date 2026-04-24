<?php
ini_set('display_errors', 1);
ini_set('display_startup_errors', 1);
error_reporting(E_ALL);
session_start();
include '../../Connection.php';
include '../../ExecutePStatement.php';
include '../../AllFunctions.php';

// See if they are logged in and if they are admin or not
checkAccess('Admin');

$error = null;

// 1. Get the ID from the URL
$companyID = $_GET['id'] ?? null;

if (!$companyID) {
    header("Location: ../Databases/CompanyDatabase.php");
    exit();
}

// 2. Fetch current data to pre-fill the form
$fetchSql = "SELECT * FROM companynamelist WHERE CompanyInt = ?";
$fetchRes = executePreparedStatement($fetchSql, [$companyID]);
$currentCompany = $fetchRes->fetch_assoc();

if (!$currentCompany) {
    die("Company not found.");
}

// 3. Process the form update
if ($_SERVER["REQUEST_METHOD"] == "POST") {

    $CompanyName    = trim($_POST['CompanyName']);
    $CompanyType    = $_POST['CompanyType'];
    $CompanyAddress = trim($_POST['CompanyAddress']);
    $ContactNumber  = trim($_POST['ContactNumber']);
    $EmailContact   = trim($_POST['EmailContact']);
    
    // Default the logo path to what is currently in the DB
    $logoPath = $currentCompany['PicturePath']; 

    // Check for duplicate names (excluding this current record)
    $resCheck = executePreparedStatement("SELECT CompanyName FROM companynamelist WHERE CompanyName = ? AND CompanyInt != ?", [$CompanyName, $companyID]);
    
    if ($resCheck && $resCheck->num_rows > 0) {
        $error = "The company name '<strong>" . htmlspecialchars($CompanyName) . "</strong>' is already taken by another entry.";
    } 

    // 4. Handle Image Upload
    if (!$error && isset($_FILES['CompanyLogo']) && $_FILES['CompanyLogo']['error'] === UPLOAD_ERR_OK) {
        $uploadDir = '../../images/'; 
        if (!is_dir($uploadDir)) { mkdir($uploadDir, 0777, true); }

        $fileName = time() . '_' . basename($_FILES['CompanyLogo']['name']);
        $targetFilePath = $uploadDir . $fileName;
        $fileType = strtolower(pathinfo($targetFilePath, PATHINFO_EXTENSION));
        $allowedTypes = array('jpg', 'jpeg', 'png', 'gif');
        
        if (in_array($fileType, $allowedTypes)) {
            if (move_uploaded_file($_FILES['CompanyLogo']['tmp_name'], $targetFilePath)) {
                // Delete the OLD image file if a new one is successfully uploaded
                if (!empty($currentCompany['PicturePath']) && file_exists("../../" . $currentCompany['PicturePath'])) {
                    unlink("../../" . $currentCompany['PicturePath']);
                }
                $logoPath = 'images/' . $fileName; 
            } else {
                $error = "There was an error moving the uploaded file.";
            }
        } else {
            $error = "Only JPG, JPEG, PNG, & GIF files are allowed.";
        }
    }

    // 5. Update the database
    if (!$error) {
        $updateSql = "UPDATE companynamelist SET CompanyName = ?, CompanyAddress = ?, CompanyType = ?, ContactNumber = ?, EmailContact = ?, PicturePath = ? WHERE CompanyInt = ?";
        $updateRes = executePreparedStatement($updateSql, [$CompanyName, $CompanyAddress, $CompanyType, $ContactNumber, $EmailContact, $logoPath, $companyID]);

        if ($updateRes) {
            header("Location: ../Databases/CompanyDatabase.php?msg=Updated");
            exit();
        } else {
            $error = "Failed to update company. No changes were made or database error.";
        }
    }
}
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Update Company</title>
    <link rel="stylesheet" href="../../Cssfiles/Add_Edit.css">
    <style>
        .current-logo { width: 60px; height: 60px; object-fit: contain; margin-top: 10px; border: 1px solid #ddd; border-radius: 5px; }
    </style>
</head>
<body>

    <div class="container">
        <h1 class="page-title">Update Company</h1>
        <p class="subtitle">Modify the details for <strong><?php echo htmlspecialchars($currentCompany['CompanyName']); ?></strong>.</p>

        <div class="form-card">
            <h2 class="section-title">Edit Company Details</h2>

            <?php if ($error): ?>
                <div class="error"><?php echo $error; ?></div>
            <?php endif; ?>
            
            <form action="" method="post" enctype="multipart/form-data">
                <div class="form-grid">
                    
                    <div class="form-group full-width">
                        <label for="CompanyName">Company Name</label>
                        <input type="text" id="CompanyName" name="CompanyName" required value="<?php echo htmlspecialchars($currentCompany['CompanyName']); ?>">
                    </div>

                    <div class="form-group">
                        <label for="CompanyType">Industry (Company Type)</label>
                        <select id="CompanyType" name="CompanyType" required>
                            <?php 
                                $types = ['Technology', 'Finance', 'Healthcare', 'Engineering', 'Media', 'Other'];
                                foreach ($types as $t) {
                                    $selected = ($currentCompany['CompanyType'] == $t) ? 'selected' : '';
                                    echo "<option value='$t' $selected>$t</option>";
                                }
                            ?>
                        </select>
                    </div>

                    <div class="form-group">
                        <label for="CompanyLogo">Update Logo (Leave blank to keep current)</label>
                        <input type="file" id="CompanyLogo" name="CompanyLogo" accept=".jpg, .jpeg, .png, .gif">
                        <?php if(!empty($currentCompany['PicturePath'])): ?>
                            <img src="../../<?php echo htmlspecialchars($currentCompany['PicturePath']); ?>" class="current-logo" alt="Current Logo">
                        <?php endif; ?>
                    </div>

                    <div class="form-group">
                        <label for="ContactNumber">Contact Number</label>
                        <input type="text" id="ContactNumber" name="ContactNumber" required value="<?php echo htmlspecialchars($currentCompany['ContactNumber']); ?>">
                    </div>

                    <div class="form-group">
                        <label for="EmailContact">Email Contact</label>
                        <input type="email" id="EmailContact" name="EmailContact" required value="<?php echo htmlspecialchars($currentCompany['EmailContact']); ?>">
                    </div>

                    <div class="form-group full-width">
                        <label for="CompanyAddress">Contact Person & Address</label>
                        <textarea id="CompanyAddress" name="CompanyAddress" rows="3" required><?php echo htmlspecialchars($currentCompany['CompanyAddress']); ?></textarea>
                    </div>

                </div>

                <div class="button-group">
                    <input type="submit" value="Update" class="btn btn-primary">
                    <a href="../Databases/CompanyDatabase.php" class="btn btn-secondary">Cancel</a>
                </div>
            </form>
        </div>
    </div>

</body>
</html>