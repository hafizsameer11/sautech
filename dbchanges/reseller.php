<?php
// modify_column.php

$db_host = "localhost";
$db_user = "clientzone_user";
$db_pass = "S@utech2024!";
$db_name = "clientzone";

// Connect to the database
$conn = new mysqli($db_host, $db_user, $db_pass, $db_name);

// Check connection
if ($conn->connect_error) {
    die("Connection failed: " . $conn->connect_error);
}

// SQL to modify the column
$sql = "ALTER TABLE `resellers` MODIFY `client_id` JSON NOT NULL";

if ($conn->query($sql) === TRUE) {
    echo "Column `client_id` successfully modified to JSON NOT NULL.";
} else {
    echo "Error modifying column: " . $conn->error;
}

$conn->close();
?>
