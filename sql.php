<?php
$servername = "localhost";
$username = "root";
$password = "";
$dbname = "clientzone";

// Create connection
$conn = new mysqli($servername, $username, $password, $dbname);

// Check connection
if ($conn->connect_error) {
    die("❌ Connection failed: " . $conn->connect_error);
}

// SQL to create table
$sql = <<<SQL
CREATE TABLE IF NOT EXISTS `hosting_logins` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `client_id` int(11) NOT NULL,
  `device_type` varchar(255) NOT NULL,
  `device_ip` varchar(255) NOT NULL,
  `location` varchar(255) NOT NULL,
  `username` varchar(255) NOT NULL,
  `password` varchar(255) NOT NULL,
  `url` varchar(255) NOT NULL,
  `note` text NOT NULL,
  `created_at` timestamp NOT NULL DEFAULT current_timestamp(),
  `updated_at` timestamp NOT NULL DEFAULT current_timestamp() ON UPDATE current_timestamp(),
  PRIMARY KEY (`id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;
SQL;

if ($conn->query($sql) === TRUE) {
    echo "✅ Table `hosting_logins` created successfully";
} else {
    echo "❌ Error creating table: " . $conn->error;
}

$conn->close();
?>
