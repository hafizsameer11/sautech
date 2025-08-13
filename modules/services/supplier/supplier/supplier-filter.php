<?php
error_reporting(E_ALL);
ini_set('display_errors', 1);

include_once '../../../config.php'; // Adjust path as needed

header('Content-Type: application/json');

if ($conn->connect_error) {
    die(json_encode(['error' => 'Database connection failed']));
}

$supplier_id = $_POST['supplier_id'] ?? '';
$where ;

if (!empty($supplier_id)) {
    $where = "id = " . (int)$supplier_id;
}

$sql = "SELECT * FROM billing_suppliers where $where ORDER BY supplier_name ASC";
$result = $conn->query($sql);

$suppliers = [];

if ($result && $result->num_rows > 0) {
    while ($row = $result->fetch_assoc()) {
        $suppliers[] = $row;
    }
}

echo json_encode($suppliers);
exit;
