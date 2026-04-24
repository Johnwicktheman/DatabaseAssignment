<?php
function checkAccess($requiredRole) {

    $roles = (array)$requiredRole;

    if (!isset($_SESSION['username']) || !in_array($_SESSION['user_role'], $roles)) {
        echo "<div style='color: red; padding: 10px; border: 1px solid red; text-align: center; '>
                <h2>Access Denied</h2>
                <p>You do not have permission to view this page. Please log in with an authorized account.</p>
              </div>";
        exit();
    }
}
?>