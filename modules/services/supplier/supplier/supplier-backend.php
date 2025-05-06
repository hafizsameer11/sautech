<?php
error_reporting(E_ALL);
ini_set('display_errors', 1);

$db_host = "localhost";
    $db_user = "clientzone_user";
    $db_pass = "S@utech2024!";
    $db_name = "clientzone";

$conn = new mysqli($db_host, $db_user, $db_pass, $db_name);

if ($conn->connect_error) {
    die("Connection failed: " . $conn->connect_error);
}

$action = $_POST['action'] ?? '';

if ($action == 'add') {
    $supplier_name = $conn->real_escape_string($_POST['supplier_name']);
    $contact_details = $conn->real_escape_string($_POST['contact_details']);
    $email = $conn->real_escape_string($_POST['email']);
    $sales_person = $conn->real_escape_string($_POST['sales_person']);

    $insert = $conn->query("
        INSERT INTO billing_suppliers (supplier_name, contact_details, email, salesperson)
        VALUES ('$supplier_name', '$contact_details', '$email', '$sales_person')
    ");

    echo $insert ? 'success' : 'error';
    exit;
}

if ($action == 'edit') {
    $id = intval($_POST['id']);
    $supplier_name = $conn->real_escape_string($_POST['supplier_name']);
    $contact_details = $conn->real_escape_string($_POST['contact_details']);
    $email = $conn->real_escape_string($_POST['email']);
    $sales_person = $conn->real_escape_string($_POST['sales_person']);

    $update = $conn->query("
        UPDATE billing_suppliers
        SET supplier_name = '$supplier_name',
            contact_details = '$contact_details',
            email = '$email',
            salesperson = '$sales_person'
        WHERE id = $id
    ");

    echo $update ? 'success' : 'error';
    exit;
}

if ($action == 'delete') {
    $id = intval($_POST['id']);
    $delete = $conn->query("DELETE FROM billing_suppliers WHERE id = $id");
    echo $delete ? 'success' : 'error';
    exit;
}

// Fetch single supplier for edit
if ($action == 'fetch') {
    $id = intval($_POST['id']);
    $query = $conn->query("SELECT * FROM billing_suppliers WHERE id = $id LIMIT 1");

    if ($query && $row = $query->fetch_assoc()) {
        header('Content-Type: application/json');
        echo json_encode($row);
    } else {
        header('Content-Type: application/json');
        echo json_encode(['error' => 'Supplier not found']);
    }
    exit;
}
?>
