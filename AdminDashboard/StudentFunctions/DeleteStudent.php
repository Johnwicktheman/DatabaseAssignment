<?php
ini_set('display_errors', 1);
ini_set('display_startup_errors', 1);
error_reporting(E_ALL);
session_start();
include '../../Connection.php';
include '../../ExecutePStatement.php';
include '../../AllFunctions.php';

//see if they are loggged in and if they are admin or not
checkAccess('Admin');


$studentID = $_GET['id'] ?? $_POST['id'] ?? null;

if (!$studentID) {
    header("Location: ../Databases/StudentDatabase.php");
    exit();
}



$fetchSql = "SELECT studentaccountlist.Username, studentaccountlist.Password, studentprofile.* FROM studentaccountlist 
             JOIN studentprofile ON studentaccountlist.StudentAccountID = studentprofile.StudentAccountID 
             WHERE studentaccountlist.StudentAccountID = ?";

$fetchResult = executePreparedStatement($fetchSql, [$studentID]);
if ($fetchResult->num_rows > 0) {
    $row = $fetchResult->fetch_assoc();
    $fName = $row['FirstName'];
    $lName = $row['LastName'];
    $uName = $row['Username'];
}
//After they press submit button what happens
if ($_SERVER["REQUEST_METHOD"] == "POST") {
    $id   = $_POST['id'];


    //Delete the Student Profile first 
    $deleteProfileSql = "DELETE FROM studentprofile WHERE StudentAccountID = ?";
    executePreparedStatement($deleteProfileSql, [$id]);

    // Delete Student Account 
    $deleteAccountSql = "DELETE FROM studentaccountlist WHERE StudentAccountID = ?";
    $deleteRes = executePreparedStatement($deleteAccountSql, [$id]);

    if ($deleteRes) {
        // Redirect back to the database list
        header("Location: ../Databases/StudentDatabase.php");
        exit();
    } else {
        $error = "Failed to delete student account.";
    }
}



?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Document</title>
      <style>
        * {
            margin: 0;
            padding: 0;
            box-sizing: border-box;
            font-family: Arial, sans-serif;
        }

        body {
            background-color: #f4f5f7;
            color: #333;
            padding: 40px;
        }

        .container {
            max-width: 1100px;
            margin: auto;
        }

        .page-title {
            font-size: 42px;
            color: #0f4f4f;
            margin-bottom: 10px;
        }

        .subtitle {
            color: #666;
            margin-bottom: 30px;
            font-size: 18px;
        }

        .form-card {
            background: white;
            padding: 35px;
            border-radius: 18px;
            box-shadow: 0 4px 15px rgba(0,0,0,0.08);
        }

        .section-title {
            font-size: 24px;
            color: #0f4f4f;
            margin-bottom: 25px;
            border-bottom: 2px solid #e5e5e5;
            padding-bottom: 10px;
        }

        .form-grid {
            display: grid;
            grid-template-columns: 1fr 1fr;
            gap: 25px;
            margin-bottom: 35px;
        }

        .form-group {
            display: flex;
            flex-direction: column;
        }

        .form-group.full-width {
            grid-column: span 2;
        }

        label {
            margin-bottom: 8px;
            font-weight: 600;
            color: #333;
        }

        input,
        select,
        textarea {
            padding: 12px;
            border: 1px solid #ccc;
            border-radius: 10px;
            font-size: 15px;
            transition: 0.2s ease;
        }

        input:focus,
        select:focus,
        textarea:focus {
            outline: none;
            border-color: #0f4f4f;
            box-shadow: 0 0 0 3px rgba(15, 79, 79, 0.15);
        }

        textarea {
            resize: vertical;
        }

        .error {
            background: #ffe5e5;
            color: #c62828;
            padding: 12px;
            border-radius: 10px;
            margin-bottom: 20px;
            font-weight: bold;
        }

        .button-group {
            display: flex;
            gap: 15px;
            margin-top: 20px;
        }

        .btn {
            padding: 12px 24px;
            border: none;
            border-radius: 10px;
            cursor: pointer;
            font-size: 15px;
            font-weight: bold;
            text-decoration: none;
            transition: 0.2s ease;
        }

        .btn-primary {
            background-color: #1e7c45;
            color: white;
        }

        .btn-primary:hover {
            background-color: #166437;
        }

        .btn-secondary {
            background-color: #dcdcdc;
            color: #333;
        }

        .btn-secondary:hover {
            background-color: #c7c7c7;
        }

        @media (max-width: 768px) {
            .form-grid {
                grid-template-columns: 1fr;
            }

            .form-group.full-width {
                grid-column: span 1;
            }
        }
    </style>
</head>
<body>
    <h2>Confirm Deletion</h2>
    <p>Are you sure you want to delete student: <?php echo $fName . " " . $lName; ?></strong>?</p>
    <p>Username: <?php echo $uName; ?></p>

    <form action="" method="post">
        <input type="hidden" name="id" value="<?php echo $studentID; ?>">
        <input type="submit" value="Yes, Delete Student">
        <a href="../Databases/StudentDatabase.php">No, Cancel</a>
    </form>
</body>
</html>
</body>
</html>