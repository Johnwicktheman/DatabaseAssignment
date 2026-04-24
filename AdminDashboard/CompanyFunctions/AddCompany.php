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

// Error text
$error = null;

// After they press submit button, process the form
if ($_SERVER["REQUEST_METHOD"] == "POST") {

    // 1. Grab all the text form data
    $CompanyName    = trim($_POST['CompanyName']);
    $CompanyType    = $_POST['CompanyType'];
    $CompanyAddress = trim($_POST['CompanyAddress']);
    $ContactNumber  = trim($_POST['ContactNumber']);
    $EmailContact   = trim($_POST['EmailContact']);

    // 2. Check for duplicate company names
    $resCompanyNAme = executePreparedStatement("SELECT CompanyName FROM companynamelist WHERE CompanyName = ?", [$CompanyName]);
    
    if ($resCompanyNAme && $resCompanyNAme->num_rows > 0) {
        $error = "The company name '<strong>" . htmlspecialchars($CompanyName) . "</strong>' is already taken.";
    } 

    // 3. Handle Image Upload (If no previous errors)
    $logoPath = null; // Default to null if no image is uploaded
    
    if (!$error && isset($_FILES['CompanyLogo']) && $_FILES['CompanyLogo']['error'] === UPLOAD_ERR_OK) {
        // Define where to save the image (adjust the ../ as needed based on your folder structure)
        $uploadDir = '../../images/'; 
        
        // Create the images folder if it doesn't exist yet
        if (!is_dir($uploadDir)) {
            mkdir($uploadDir, 0777, true);
        }

        // Make the file name unique using time() so we don't accidentally overwrite files with the same name
        $fileName = time() . '_' . basename($_FILES['CompanyLogo']['name']);
        $targetFilePath = $uploadDir . $fileName;
        
        // Check if it's a real image (Security Check)
        $fileType = strtolower(pathinfo($targetFilePath, PATHINFO_EXTENSION));
        $allowedTypes = array('jpg', 'jpeg', 'png', 'gif');
        
        if (in_array($fileType, $allowedTypes)) {
            // Move the file from the temporary folder to your images folder
            if (move_uploaded_file($_FILES['CompanyLogo']['tmp_name'], $targetFilePath)) {
                // Save the relative path to put in the database
                $logoPath = 'images/' . $fileName; 
            } else {
                $error = "There was an error moving the uploaded file.";
            }
        } else {
            $error = "Sorry, only JPG, JPEG, PNG, & GIF files are allowed for the logo.";
        }
    }

    // 4. If everything is completely error-free, insert into the database
    if (!$error) {
        $insertSql = "INSERT INTO companynamelist (CompanyName, CompanyAddress, CompanyType, ContactNumber, EmailContact, PicturePath) VALUES (?, ?, ?, ?, ?, ?)";
        $insertRes = executePreparedStatement($insertSql, [$CompanyName, $CompanyAddress, $CompanyType, $ContactNumber, $EmailContact, $logoPath]);

        if ($insertRes) {
            // Success! Redirect back to the database grid
            header("Location: ../Databases/CompanyDatabase.php");
            exit();
        } else {
            $error = "Failed to add company to the database. Please try again.";
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
                        <label for="CompanyAddress">Contact Person & Address</label>
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