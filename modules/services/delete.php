<?php
$conn = new mysqli("localhost", "root", "", "clientzone");

$id = intval($_GET['id']);
$conn->query("UPDATE client_services SET is_deleted = 1 WHERE id = $id");
header("Location: list.php");

