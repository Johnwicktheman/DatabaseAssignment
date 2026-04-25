<?php
ini_set('display_errors', 1);
ini_set('display_startup_errors', 1);
error_reporting(E_ALL);
session_start();
include '../../Connection.php';
include '../../ExecutePStatement.php';
include '../../AllFunctions.php';

//check their current access 
checkAccess('Admin');

//error text
$error = null;

//after press submit button what happens
if ($_SERVER["REQUEST_METHOD"] == "POST") {

    //get all data from form
    $CompanyName    = $_POST['CompanyName'];
    $CompanyType    = $_POST['CompanyType'];
    $CompanyAddress = $_POST['CompanyAddress'];
    $ContactNumber  = $_POST['ContactNumber'];
    $EmailContact   = $_POST['EmailContact'];

    //check for company duplciate name
    $companycheck = "SELECT CompanyName FROM companynamelist WHERE CompanyName = ?";
    $resCompanyNAme = executePreparedStatement($companycheck, [$CompanyName]);

    if ($resCompanyNAme && $resCompanyNAme->num_rows > 0) {
        $error = "The company name '$CompanyName' is already taken.";
    } 

    //Handle image upload and set as null first
    $logoPath = null; 
    
    //check the file got upload or not and no error such as too big size
    if (!$error && isset($_FILES['CompanyLogo']) && $_FILES['CompanyLogo']['error'] === UPLOAD_ERR_OK) {
        //where to store file
        $uploadDir = '../../images/'; 

        //make the file name unique using time()_ so we dont overwrite files with the same name
        $fileName = time() . '_' . basename($_FILES['CompanyLogo']['name']);
        $targetFilePath = $uploadDir . $fileName;
        
        //choose allowed file types
        $fileType = strtolower(pathinfo($targetFilePath, PATHINFO_EXTENSION));
        $allowedTypes = array('jpg', 'jpeg', 'png');
        
        if (in_array($fileType, $allowedTypes)) {
            //move file ffrom temp location _FILES to our directory folder
            if (move_uploaded_file($_FILES['CompanyLogo']['tmp_name'], $targetFilePath)) {
                //move to root 
                $logoPath = 'images/' . $fileName; 
            } else {
                $error = "There was an error moving the uploaded file.";
            }
        } else {
            $error = "only JPG, JPEG, and PNG files are allowed for the logo.";
        }
    }

    //if all ok insert into database
    if (!$error) {
        $insertSql = "INSERT INTO companynamelist (CompanyName, CompanyAddress, CompanyType, ContactNumber, EmailContact, PicturePath) VALUES (?, ?, ?, ?, ?, ?)";
        $insertRes = executePreparedStatement($insertSql, [$CompanyName, $CompanyAddress, $CompanyType, $ContactNumber, $EmailContact, $logoPath]);

        if ($insertRes) {
            //if success go back to CompanyDatabase
            header("Location: ../Databases/CompanyDatabase.php");
            exit();
        } else {
            $error = "Failed to add company to the database.";
        }
    }
}
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Add New Company</title>
    <link rel="stylesheet" href="../../Cssfiles/Add_Edit.css">


</head>
<body>

    <div class="container">
        <h1 class="page-title">Add New Company</h1>
        <p class="subtitle">Register a new company into the internship assessment system.</p>

        <div class="form-card">
            <h2 class="section-title">Company Details</h2>

            <?php if ($error): ?>
                <div class="error">
                    <?php echo $error; ?>
                </div>
            <?php endif; ?>
            
            <form action="" method="post" enctype="multipart/form-data">
                <div class="form-grid">
                    
                    <div class="form-group full-width">
                        <label for="CompanyName">Company Name</label>
                        <input type="text" id="CompanyName" name="CompanyName" placeholder="e.g. Google" required value="<?php echo isset($_POST['CompanyName']) ? htmlspecialchars($_POST['CompanyName']) : ''; ?>">
                    </div>

                    <div class="form-group">
                        <label for="CompanyType">Industry (Company Type)</label>
                        <select id="CompanyType" name="CompanyType" required>
                            <option value="Technology">Technology</option>
                            <option value="Finance">Finance</option>
                            <option value="Healthcare">Healthcare</option>
                            <option value="Engineering">Engineering</option>
                            <option value="Media">Media</option>
                            <option value="Other">Other</option>
                        </select>
                    </div>

                    <div class="form-group">
                        <label for="CompanyLogo">Company Logo (Optional)</label>
                        <input type="file" id="CompanyLogo" name="CompanyLogo" accept=".jpg, .jpeg, .png, .gif">
                    </div>

                    <div class="form-group">
                        <label for="ContactNumber">Contact Number</label>
                        <input type="text" id="ContactNumber" name="ContactNumber" placeholder="e.g. +6012-3456789" required value="<?php echo isset($_POST['ContactNumber']) ? htmlspecialchars($_POST['ContactNumber']) : ''; ?>">
                    </div>

                    <div class="form-group">
                        <label for="EmailContact">Email Contact</label>
                        <input type="email" id="EmailContact" name="EmailContact" placeholder="hr@company.com" required value="<?php echo isset($_POST['EmailContact']) ? htmlspecialchars($_POST['EmailContact']) : ''; ?>">
                    </div>

                    <div class="form-group full-width">
                        <label for="CompanyAddress">Company Address</label>
                        <textarea id="CompanyAddress" name="CompanyAddress" rows="3" placeholder="e.g. John Doe&#10;123 Tech Lane, Cyberjaya" required><?php echo isset($_POST['CompanyAddress']) ? htmlspecialchars($_POST['CompanyAddress']) : ''; ?></textarea>
                    </div>

                </div>

                <div class="button-group">
                    <input type="submit" value="Add Company" class="btn btn-primary">
                    <a href="../Databases/CompanyDatabase.php" class="btn btn-secondary">Cancel</a>
                </div>
            </form>
        </div>
    </div>

</body>
</html>