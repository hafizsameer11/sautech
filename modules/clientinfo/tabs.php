<?php
$conn = new mysqli("localhost", "root", "", "clientzone");

$stmt = $conn->prepare("INSERT INTO client_custom_tabs (client_id, tab_name, created_at) VALUES (?, ?, NOW())");
$stmt->bind_param("is", $_POST['client_id'], $_POST['tab_name']);
$stmt->execute();
header("Location: clientinfo.php?view=" . $_POST['client_id'] . "&tab=tabs");
exit;
?>

