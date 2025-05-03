<?php
$conn = new mysqli("localhost", "clientzone_user", "S@utech2024!", "clientzone");

$stmt = $conn->prepare("INSERT INTO client_support_items (client_id, label, type, ip_address, username, password, created_at) VALUES (?, ?, ?, ?, ?, ?, NOW())");
$stmt->bind_param("isssss", $_POST['client_id'], $_POST['label'], $_POST['type'], $_POST['ip_address'], $_POST['username'], $_POST['password']);
$stmt->execute();
header("Location: clientinfo.php?view=" . $_POST['client_id'] . "&tab=support");
exit;
?>

