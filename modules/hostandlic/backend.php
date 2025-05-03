<?php
// Database connection
$conn = new mysqli("localhost", "root", "", "clientzone");

if ($conn->connect_error) {
    die("Connection failed: " . $conn->connect_error);
}

// Check if update request is received
if ($_SERVER["REQUEST_METHOD"] === "POST" && isset($_POST["action"]) && $_POST["action"] === "edit") {
    // Retrieve updated data
    $id = intval($_POST["hosting_record_id"]);
    $client_name = $conn->real_escape_string($_POST["client_name"]);
    $os = $conn->real_escape_string($_POST["os"]);
    $cpu = $conn->real_escape_string($_POST["cpu"]);
    $memory = $conn->real_escape_string($_POST["memory"]);
    $sata = $conn->real_escape_string($_POST["sata"]);
    $ssd = $conn->real_escape_string($_POST["ssd"]);
    $ip_address = $conn->real_escape_string($_POST["ip_address"]);
    $note = $conn->real_escape_string($_POST["note"]);
    $spla = $conn->real_escape_string($_POST["spla"]);

    // Prepare SQL to update record
    $stmt = $conn->prepare("UPDATE hosting_assets SET client_name = ?,  os = ?, cpu = ?, mem = ?, sata = ?, ssd = ?, ip_address = ?, note = ?, spla = ? WHERE id = ?");
    $stmt->bind_param("sssssssssi", $client_name, $os, $cpu, $memory, $sata, $ssd, $ip_address, $note, $spla, $id);

    if ($stmt->execute()) {
        echo "success";
    } else {
        echo "error";
    }
    $stmt->close();
}
$conn->close();
?>
