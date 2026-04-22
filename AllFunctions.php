
<?php
function checkAccess($requiredRole) {
    //Use array to store required roles
    $roles = (array)$requiredRole;

    if (!isset($_SESSION['username']) || !in_array($_SESSION['user_role'], $roles)) {
        header("Location: FrontPage.php"); 
        exit();
    }
}
?>