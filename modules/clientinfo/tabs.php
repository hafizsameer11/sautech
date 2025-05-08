<?php
$db_host = "localhost";
    $db_user = "root";
    $db_pass = "";
    $db_name = "clientzone";

$conn = new mysqli($db_host, $db_user, $db_pass, $db_name);

$stmt = $conn->prepare("INSERT INTO client_custom_tabs (client_id, tab_name, created_at) VALUES (?, ?, NOW())");
$stmt->bind_param("is", $_POST['client_id'], $_POST['tab_name']);
$stmt->execute();
header("Location: clientinfo.php?view=" . $_POST['client_id'] . "&tab=tabs");
exit;
?>

