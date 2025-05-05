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
    $category_name = $conn->real_escape_string($_POST['category_name']);
    $notes = $conn->real_escape_string($_POST['notes']);
    $is_vm_category = isset($_POST['is_vm_category']) ? 1 : 0;

    $insert = $conn->query("
        INSERT INTO billing_service_categories (category_name, note, has_vm_fields)
        VALUES ('$category_name', '$notes', $is_vm_category)
    ");

    echo $insert ? 'success' : 'error';
    exit;
}

if ($action == 'edit') {
    $id = intval($_POST['id']);
    $category_name = $conn->real_escape_string($_POST['category_name']);
    $notes = $conn->real_escape_string($_POST['notes']);
    $is_vm_category = isset($_POST['is_vm_category']) ? 1 : 0;

    $update = $conn->query("
        UPDATE billing_service_categories
        SET category_name = '$category_name',
            note = '$notes',
            has_vm_fields = $is_vm_category
        WHERE id = $id
    ");

    echo $update ? 'success' : 'error';
    exit;
}
if ($action == 'delete') {
    $id = intval($_POST['id']);
    
    // Soft delete: mark as is_deleted = 1
    $update = $conn->query("UPDATE billing_service_categories SET is_deleted = 1 WHERE id = $id");

    echo $update ? 'success' : 'error: ' . $conn->error;
    exit;
}



if ($action == 'fetch') {
    $id = intval($_POST['id']);
    $query = $conn->query("SELECT * FROM billing_service_categories WHERE id = $id LIMIT 1");

    if ($query && $row = $query->fetch_assoc()) {
        header('Content-Type: application/json');
        echo json_encode($row);
    } else {
        header('Content-Type: application/json');
        echo json_encode(['error' => 'Service Category not found']);
    }
    exit;
}
?>
