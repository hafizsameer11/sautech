<?php
error_reporting(E_ALL);
ini_set('display_errors', 1);

file_put_contents("debug_log.txt", print_r($_POST, true));


echo "<pre>";
print_r($_POST);
echo "</pre>";

$db_host = "localhost";
    $db_user = "clientzone_user";
    $db_pass = "S@utech2024!";
    $db_name = "clientzone";

$conn = new mysqli($db_host, $db_user, $db_pass, $db_name);

if ($conn->connect_error) {
    die("Connection failed: " . $conn->connect_error);
}

$client_id = intval($_POST['client_id']);
$type = $conn->real_escape_string($_POST['support_type']);
$field1 = $conn->real_escape_string($_POST['field1']); // Domain
$field2 = $conn->real_escape_string($_POST['field2']); // Full Name
$field3 = $conn->real_escape_string($_POST['field3']); // Login
$field4 = $conn->real_escape_string($_POST['field4']); // Password
$field5 = $conn->real_escape_string($_POST['field5']); // SpamTitan
$note = $conn->real_escape_string($_POST['note']);
$exchange_id = isset($_POST['exchange_id']) ? intval($_POST['exchange_id']) : 0;

if ($exchange_id > 0) {
    $stmt = $conn->prepare("UPDATE client_support SET field1=?, field2=?, field3=?, field4=?, field5=?, note=? WHERE id=?");
    $stmt->bind_param("ssssssi", $field1, $field2, $field3, $field4, $field5, $note, $exchange_id);
    if (!$stmt->execute()) {
        die("❌ Update Failed: " . $stmt->error);
    }
} else {
    $stmt = $conn->prepare("INSERT INTO client_support (client_id, support_type, field1, field2, field3, field4, field5, note) VALUES (?, ?, ?, ?, ?, ?, ?, ?)");
    $stmt->bind_param("isssssss", $client_id, $type, $field1, $field2, $field3, $field4, $field5, $note);
    if (!$stmt->execute()) {
        die("❌ Insert Failed: " . $stmt->error);
    }
}

echo "✅ Entry saved successfully.";
?>
