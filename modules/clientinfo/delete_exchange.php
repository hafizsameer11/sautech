<?php
$db_host = "localhost";
    $db_user = "root";
    $db_pass = "";
    $db_name = "clientzone";

$conn = new mysqli($db_host, $db_user, $db_pass, $db_name);

if ($conn->connect_error) {
    die("Connection failed: " . $conn->connect_error);
}

$id = intval($_GET['id']);
$conn->query("DELETE FROM client_support WHERE id = $id");
echo "✅ Deleted";
?>
