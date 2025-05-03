<?php
$conn = new mysqli("localhost", "root", "", "clientzone");

if ($conn->connect_error) {
    die("Connection failed: " . $conn->connect_error);
}

$id = intval($_GET['id']);
$conn->query("DELETE FROM client_support WHERE id = $id");
echo "✅ Deleted";
?>
