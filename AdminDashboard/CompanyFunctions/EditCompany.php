<?php
ini_set('display_errors', 1);
ini_set('display_startup_errors', 1);
error_reporting(E_ALL);
session_start();
include '../../Connection.php';
include '../../ExecutePStatement.php';
include '../../AllFunctions.php';

checkAccess('Admin');

$error = null;

$companyID = $_GET['id'] ?? null;

if (!$companyID) {
    header("Location: ../Databases/CompanyDatabase.php");
    exit();
}

//same stuff with add company
$fetchSql = "SELECT * FROM companynamelist WHERE CompanyInt = ?";
$fetchRes = executePreparedStatement($fetchSql, [$companyID]);
$currentCompany = $fetchRes->fetch_assoc();

if (!$currentCompany) {
    die("Company not found.");
}

if ($_SERVER["REQUEST_METHOD"] == "POST") {

    //logoPath we set as current logo first later check
    $CompanyName    = $_POST['CompanyName'];
    $CompanyType    = $_POST['CompanyType'];
    $CompanyAddress = $_POST['CompanyAddress'];
    $ContactNumber  = $_POST['ContactNumber'];
    $EmailContact   = $_POST['EmailContact'];
    $logoPath = $currentCompany['PicturePath'];
    

    //check for duplicate company name but exclude current one
    $companyCheck = "SELECT CompanyName FROM companynamelist WHERE CompanyName = ? AND CompanyInt != ?";
    $resCheck = executePreparedStatement($companyCheck, [$CompanyName, $companyID]);
    
    if ($resCheck && $resCheck->num_rows > 0) {
        $error = "The company name '$CompanyName' is already taken by another entry.";
    } 

    //Handle image upload same as Add company but we delete the old image if user upload new image
    if (!$error && isset($_FILES['CompanyLogo']) && $_FILES['CompanyLogo']['error'] === UPLOAD_ERR_OK) {
        $uploadDir = '../../images/'; 


        $fileName = time() . '_' . basename($_FILES['CompanyLogo']['name']);
        $targetFilePath = $uploadDir . $fileName;
        $fileType = strtolower(pathinfo($targetFilePath, PATHINFO_EXTENSION));
        $allowedTypes = array('jpg', 'jpeg', 'png');
        
        if (in_array($fileType, $allowedTypes)) {
            if (move_uploaded_file($_FILES['CompanyLogo']['tmp_name'], $targetFilePath)) {
                //Delete the old image if a new one is successfully uploaded
                if (!empty($currentCompany['PicturePath']) && file_exists("../../" . $currentCompany['PicturePath'])) {
                    unlink("../../" . $currentCompany['PicturePath']);
                }
                $logoPath = 'images/' . $fileName; 
            } else {
                $error = "There was an error moving the uploaded file.";
            }
        } else {
            $error = "only JPG, JPEG, and PNG files are allowed for the logo.";
        }
    }

    //if no error message then update
    if (!$error) {
        $updateSql = "UPDATE companynamelist SET CompanyName = ?, CompanyAddress = ?, CompanyType = ?, ContactNumber = ?, EmailContact = ?, PicturePath = ? WHERE CompanyInt = ?";
        $updateRes = executePreparedStatement($updateSql, [$CompanyName, $CompanyAddress, $CompanyType, $ContactNumber, $EmailContact, $logoPath, $companyID]);

        if ($updateRes) {
            header("Location: ../Databases/CompanyDatabase.php");
            exit();
        } else {
            $error = "Failed to update company.";
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
        <p class="subtitle">Modify the details for <strong><?php echo $currentCompany['CompanyName']; ?></strong>.</p>

        <div class="form-card">
            <h2 class="section-title">Edit Company Details</h2>

            <?php if ($error): ?>
                <div class="error"><?php echo $error; ?></div>
            <?php endif; ?>
            <!--enctype is for image form-->
            <form action="" method="post" enctype="multipart/form-data">
                <div class="form-grid">
                    
                    <div class="form-group full-width">
                        <label for="CompanyName">Company Name</label>
                        <input type="text" id="CompanyName" name="CompanyName" required value="<?php echo $currentCompany['CompanyName']; ?>">
                    </div>

                    <div class="form-group">
                        <label for="CompanyType">Industry (Company Type)</label>
                        <select id="CompanyType" name="CompanyType" required>
                            <?php 
                                $types = ['Technology', 'Finance', 'Healthcare', 'Engineering', 'Media', 'Other'];
                                foreach ($types as $i) {
                                    $selected = ($currentCompany['CompanyType'] == $i) ? 'selected' : '';
                                    echo "<option value='$i' $selected>$i</option>";
                                }
                            ?>
                        </select>
                    </div>

                    <div class="form-group">
                        <label for="CompanyLogo">Update Logo (Optional)</label>
                        <input type="file" id="CompanyLogo" name="CompanyLogo" accept=".jpg, .jpeg, .png, .gif">
                        <?php if(!empty($currentCompany['PicturePath'])): ?>
                            <img src="../../<?php echo $currentCompany['PicturePath']; ?>" class="current-logo" alt="Current Logo">
                        <?php endif; ?>
                    </div>

                    <div class="form-group">
                        <label for="ContactNumber">Contact Number</label>
                        <input type="text" id="ContactNumber" name="ContactNumber" required value="<?php echo $currentCompany['ContactNumber']; ?>">
                    </div>

                    <div class="form-group">
                        <label for="EmailContact">Email Contact</label>
                        <input type="email" id="EmailContact" name="EmailContact" required value="<?php echo $currentCompany['EmailContact']; ?>">
                    </div>

                    <div class="form-group full-width">
                        <label for="CompanyAddress">Company Address</label>
                        <textarea id="CompanyAddress" name="CompanyAddress" rows="3" required><?php echo $currentCompany['CompanyAddress']; ?></textarea>
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