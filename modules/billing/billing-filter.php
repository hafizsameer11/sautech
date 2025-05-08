<?php
error_reporting(E_ALL);
ini_set('display_errors', 1);

$db_host = "localhost";
    $db_user = "root";
    $db_pass = "";
    $db_name = "clientzone";

$conn = new mysqli($db_host, $db_user, $db_pass, $db_name);
if ($conn->connect_error) {
    die("Connection failed: " . $conn->connect_error);
}

// Build dynamic WHERE conditions
$where = "b.is_deleted = 0";
$params = [];

if (!empty($_POST['client_id'])) {
    $where .= " AND b.client_id = " . (int)$_POST['client_id'];
}
if (!empty($_POST['supplier_id'])) {
    $where .= " AND b.supplier_id = " . (int)$_POST['supplier_id'];
}
if (!empty($_POST['service_type_id'])) {
    $where .= " AND b.service_type_id = " . (int)$_POST['service_type_id'];
}
if (!empty($_POST['frequency'])) {
    $where .= " AND b.frequency = '" . $conn->real_escape_string($_POST['frequency']) . "'";
}

// Fetch filtered billing records
$sql = "
SELECT b.*, 
       c.client_name, 
       s.supplier_name AS supplier_name, 
       st.service_type_name,
       sc.category_name
FROM billing_items b
LEFT JOIN clients c ON b.client_id = c.id
LEFT JOIN billing_suppliers s ON b.supplier_id = s.id
LEFT JOIN billing_service_types st ON b.service_type_id = st.id
LEFT JOIN billing_service_categories sc ON b.service_category_id = sc.id
WHERE $where
ORDER BY b.created_at DESC
";

$result = $conn->query($sql);

if ($result && $result->num_rows > 0) {
    $i = 1;
    while ($row = $result->fetch_assoc()) {
        $subtotal = $row['qty'] * $row['unit_price'];
        $vatAmount = ($row['vat_rate'] / 100) * $subtotal;
        $total = $subtotal + $vatAmount;
        
        echo '<tr>
            <td class="text-center">' . $i++ . '</td>
            <td>' . htmlspecialchars($row['client_name']) . '</td>
            <td>' . htmlspecialchars($row['supplier_name']) . '</td>
            <td>' . htmlspecialchars($row['service_type_name']) . '</td>
            <td>' . htmlspecialchars($row['category_name']) . '</td>
            <td class="text-center">' . $row['qty'] . '</td>
            <td class="text-end">' . number_format($row['unit_price'], 2) . '</td>
            <td class="text-end">' . number_format($subtotal, 2) . '</td>
            <td class="text-end"><strong>' . number_format($total, 2) . '</strong></td>
            <td class="text-center">' . ucfirst($row['frequency']) . '</td>
            <td class="text-center">' . date('d M Y', strtotime($row['start_date'])) . '</td>
            <td class="text-center">' . ($row['end_date'] ? date('d M Y', strtotime($row['end_date'])) : '-') . '</td>
            <td class="text-center">
                <div class="btn-group" role="group">
                    <a href="javascript:void(0)" onclick="openEditModal(
                        ' . $row['id'] . ',
                        ' . $row['client_id'] . ',
                        ' . $row['supplier_id'] . ',
                        ' . $row['service_type_id'] . ',
                        ' . $row['service_category_id'] . ',
                        `' . htmlspecialchars(addslashes($row['description'])) . '`,
                        ' . $row['qty'] . ',
                        ' . $row['unit_price'] . ',
                        ' . $row['vat_rate'] . ',
                        `' . $row['frequency'] . '`,
                        `' . $row['start_date'] . '`,
                        `' . $row['end_date'] . '`,
                        ' . $row['vat_applied'] . '
                    )" class="btn btn-sm" title="Edit"><i class="fas fa-edit"></i></a>
                    <a href="javascript:void(0)" onclick="openDeleteModal(' . $row['id'] . ')" class="btn btn-sm text-danger" title="Delete"><i class="fas fa-trash-alt"></i></a>
                </div>
            </td>
        </tr>';
    }
} else {
    echo '<tr><td colspan="15" class="text-center text-muted">No billing items found matching filters.</td></tr>';
}
?>
