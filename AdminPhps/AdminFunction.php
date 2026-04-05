<?php


function getAssessors($condition = null) {
global $conn;
$sql = "SELECT * FROM assesoraccountlist ";
if ($condition) {
$sql .= " WHERE $condition";
}
return executePreparedStatement($sql, []);
}

function getAssessorsByType($type) {
    $sql = "SELECT AssessorAccountID, Username FROM assesoraccountlist WHERE AssesorType = ?";
    return executePreparedStatement($sql, [$type]);
}


function updateAssessors($assessor_id, $username, $password, $type) {
    $user     = getCurrentUser();
    $admin_id = $user['id'];

    $sql = "UPDATE assesoraccountlist SET Username = ?, Password = ?, AssesorType = ?, AdminAccountID = ? WHERE AssessorAccountID = ?";
    $params = [$username, $password, $type, $admin_id, $assessor_id];
    return executePreparedStatement($sql, $params);
}

function CreateAssessors($username, $password, $type) {
    $user     = getCurrentUser();
    $admin_id = $user['id'];

    $sql = "INSERT INTO assesoraccountlist (Username, Password, AssesorType, AdminAccountID) VALUES (?, ?, ?, ?)";
    $params = [$username, $password, $type, $admin_id];
    
    return executePreparedStatement($sql, $params);
}

function deleteAssessor($assessor_id) {
    $sql = "DELETE FROM assesoraccountlist WHERE AssessorAccountID = ?";
    $params = [$assessor_id];
    return executePreparedStatement($sql, $params);
}


function getStudents($search = "",$sort = "oldest") {
    global $conn;
    $sql = "SELECT account.*, profile.FirstName, profile.LastName 
            FROM studentaccountlist AS account
            LEFT JOIN studentprofile AS profile 
            ON account.StudentAccountID = profile.StudentAccountID";
    $params = [];
    if (!empty($search)) {
        $sql .= " WHERE (account.StudentAccountID LIKE ? 
                  OR account.Username LIKE ? 
                  OR account.AdminAccountID LIKE ? 
                  OR CONCAT(profile.FirstName, ' ', profile.LastName) LIKE ?)";
        
        $searchTerm = "%$search%";
        // 5 question marks, so 5 parameters
        $params = [$searchTerm, $searchTerm, $searchTerm, $searchTerm];
    }

    // 2. Handle Sorting
    if ($sort === "name") {
        $sql .= " ORDER BY Username ASC"; // A-Z
    } else if ($sort === "recent") {
        $sql .= " ORDER BY StudentAccountID DESC"; // Newest first
    }else {
        // Default: Oldest first (Original DB order)
        $sql .= " ORDER BY StudentAccountID ASC"; 
    }
    if (!empty($params)) {
        return executePreparedStatement($sql, $params);
    } else {
        return $conn->query($sql);
    }
}


function updateStudents($student_id, $username, $password) {
    $user     = getCurrentUser();
    $admin_id = $user['id'];

    $sql = "UPDATE studentaccountlist SET Username = ?, Password = ?, AdminAccountID = ? WHERE StudentAccountID = ?";
    $params = [$username, $password, $admin_id, $student_id];
    return executePreparedStatement($sql, $params);
}

function CreateStudents($username, $password, $fname, $lname, $lect_id, $super_id, $internshipCode, $programmeCode) {
    global $conn;
    $admin_id = getCurrentUser()['id'];

    $conn->begin_transaction();
    try {
        // 1. Create Login Account
        $sql1 = "INSERT INTO studentaccountlist (Username, Password, AdminAccountID) VALUES (?, ?, ?)";
        $stmt1 = $conn->prepare($sql1);
        $stmt1->bind_param("ssi", $username, $password, $admin_id);
        $stmt1->execute();

        $new_student_id = $conn->insert_id;

        // 2. Create Profile with manual Programme Code
        // Note: YearOfStudy is hardcoded to 1 as a default starting point
        $sql2 = "INSERT INTO studentprofile 
                 (StudentAccountID, FirstName, LastName, ProgrammeCode, YearOfStudy, InternshipCode, AssesorAccountIDLect, AssesorAccountIDSuper) 
                 VALUES (?, ?, ?, ?, 1, ?, ?, ?)";
        
        $stmt2 = $conn->prepare($sql2);
        // Types: i (int), s (string), s (string), s (string), s (string), i (int), i (int)
        $stmt2->bind_param("issssii", $new_student_id, $fname, $lname, $programmeCode, $internshipCode, $lect_id, $super_id);
        $stmt2->execute();

        $conn->commit();
        return true;
    } catch (Exception $e) {
        $conn->rollback();
        return false;
    }
}


function deleteStudent($student_id) {
    $sql = "DELETE FROM studentaccountlist WHERE StudentAccountID = ?";
    $params = [$student_id];
    return executePreparedStatement($sql, $params);
}

function getInternships() {
    $sql = "SELECT InternshipCode, CompanyName FROM internship";
    return executePreparedStatement($sql, []);
}

?>


