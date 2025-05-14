<?php
function hasPermission($page, $action = null) {
    if (!isset($_SESSION['permissions'][$page])) return false;

    // If no action is provided, check if any permission exists for this page
    if ($action === null) {
        return count($_SESSION['permissions'][$page]) > 0;
    }
    // Check specific action
    return in_array($action, $_SESSION['permissions'][$page]);
}