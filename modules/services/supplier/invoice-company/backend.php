<?php
error_reporting(E_ALL);
ini_set('display_errors', 1);
$localhost = ($_SERVER['SERVER_NAME'] == 'localhost');

if ($localhost) {
    // Local development settings
    $db_host = "localhost";
    $db_user = "root";
    $db_pass = "";
    $db_name = "clientzone";
} else {
    // Live server settings
    $db_host = "localhost";
    $db_user = "clientzone_user";
    $db_pass = "S@utech2024!";
    $db_name = "clientzone";
}

$conn = new mysqli($db_host, $db_user, $db_pass, $db_name);
if ($conn->connect_error) {
    die("Connection failed: " . $conn->connect_error);
}

$action = $_POST['action'] ?? '';

if ($action == 'add') {
    $name = $conn->real_escape_string($_POST['company_name']);
    $vat = (float)($_POST['vat_rate']);
    $result = $conn->query("INSERT INTO billing_invoice_companies (company_name, vat_rate) VALUES ('$name', $vat)");
    echo $result ? 'success' : 'error';
    exit;
}

if ($action == 'edit') {
    $id = (int)$_POST['id'];
    $name = $conn->real_escape_string($_POST['company_name']);
    $vat = (float)$_POST['vat_rate'];
    $result = $conn->query("UPDATE billing_invoice_companies SET company_name = '$name', vat_rate = $vat WHERE id = $id");
    echo $result ? 'success' : 'error';
    exit;
}

if ($action == 'delete') {
    $id = (int)$_POST['id'];
    $result = $conn->query("DELETE FROM billing_invoice_companies WHERE id = $id");
    echo $result ? 'success' : 'error';
    exit;
}

if ($action == 'fetch') {
    $id = (int)$_POST['id'];
    $result = $conn->query("SELECT * FROM billing_invoice_companies WHERE id = $id LIMIT 1");
    echo json_encode($result->fetch_assoc());
    exit;
}
?>
