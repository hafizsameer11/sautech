<?php
error_reporting(E_ALL);
ini_set('display_errors', 1);

$conn = new mysqli("localhost", "clientzone_user", "S@utech2024!", "clientzone");


$showRemoved = isset($_GET['removed']) && $_GET['removed'] === '1';

$clientOptions = $conn->query("SELECT DISTINCT client_name FROM client_services WHERE is_deleted = 0 ORDER BY client_name ASC");
$typeOptions = $conn->query("SELECT DISTINCT service_type FROM client_services ORDER BY service_type ASC");
$serviceOptions = $conn->query("SELECT DISTINCT service_name FROM client_services ORDER BY service_name ASC");

$query = "SELECT * FROM client_services WHERE 1";
$filters = [];

if (!$showRemoved) {
    $filters[] = "is_deleted = 0";
}
if (!empty($_GET['client'])) {
    $client = $conn->real_escape_string($_GET['client']);
    $filters[] = "client_name = '$client'";
}
if (!empty($_GET['type'])) {
    $type = $conn->real_escape_string($_GET['type']);
    $filters[] = "service_type = '$type'";
}
if (!empty($_GET['service'])) {
    $service = $conn->real_escape_string($_GET['service']);
    $filters[] = "service_name = '$service'";
}
if (!empty($filters)) {
    $query .= " AND " . implode(" AND ", $filters);
}

$query .= " ORDER BY created_at DESC";
$result = $conn->query($query);
?>

<h2>💼 Client Services List</h2>
<a href="index.php">➕ Add New</a> |
<a href="list.php?removed=1">🗑 Show Removed</a><br><br>

<form method="get" style="margin-bottom: 20px;">
    <label>Client:</label>
    <select name="client">
        <option value="">All</option>
        <?php while ($row = $clientOptions->fetch_assoc()): ?>
            <option value="<?= $row['client_name'] ?>" <?= ($_GET['client'] ?? '') === $row['client_name'] ? 'selected' : '' ?>><?= $row['client_name'] ?></option>
        <?php endwhile; ?>
    </select>

    <label>Type:</label>
    <select name="type">
        <option value="">All</option>
        <?php while ($row = $typeOptions->fetch_assoc()): ?>
            <option value="<?= $row['service_type'] ?>" <?= ($_GET['type'] ?? '') === $row['service_type'] ? 'selected' : '' ?>><?= $row['service_type'] ?></option>
        <?php endwhile; ?>
    </select>

    <label>Service:</label>
    <select name="service">
        <option value="">All</option>
        <?php while ($row = $serviceOptions->fetch_assoc()): ?>
            <option value="<?= $row['service_name'] ?>" <?= ($_GET['service'] ?? '') === $row['service_name'] ? 'selected' : '' ?>><?= $row['service_name'] ?></option>
        <?php endwhile; ?>
    </select>

    <button type="submit">🔍 Filter</button>
    <a href="list.php">🔄 Reset</a>
</form>

<table border="1" cellpadding="6" cellspacing="0">
    <tr>
        <th>Actions</th>
        <th>Client</th><th>Type</th><th>Service</th><th>Specs</th><th>Status</th>
        <th>Qty</th><th>Currency</th><th>Ex VAT</th><th>VAT</th><th>Total</th>
        <th>Invoice</th><th>Billing Type</th><th>Debit Day</th><th>Notes</th><th>Created</th>
    </tr>
    <?php while ($row = $result->fetch_assoc()): ?>
        <tr style="<?= $row['is_deleted'] ? 'background-color: #fdd;' : '' ?>">
            <td>
                <a href="edit.php?id=<?= $row['id'] ?>">Edit</a> |
                <a href="delete.php?id=<?= $row['id'] ?>" onclick="return confirm('Are you sure?')">Delete</a>
            </td>
            <td><?= htmlspecialchars($row['client_name']) ?></td>
            <td><?= htmlspecialchars($row['service_type']) ?></td>
            <td><?= htmlspecialchars($row['service_name']) ?></td>
            <td><?= nl2br(htmlspecialchars($row['specs'])) ?></td>
            <td><?= htmlspecialchars($row['status']) ?></td>
            <td><?= htmlspecialchars($row['quantity']) ?></td>
            <td><?= htmlspecialchars($row['currency']) ?></td>
            <td><?= number_format($row['ex_vat_amount'], 2) ?></td>
            <td><?= number_format($row['vat_amount'], 2) ?></td>
            <td><strong><?= number_format($row['total_with_vat'], 2) ?></strong></td>
            <td><?= htmlspecialchars($row['invoice_link']) ?></td>
            <td><?= isset($row['billing_type']) ? htmlspecialchars($row['billing_type']) : '' ?></td>
            <td><?= isset($row['billing_type']) && $row['billing_type'] === 'Debit Order' ? intval($row['debit_order_day']) : '' ?></td>
            <td><?= nl2br(htmlspecialchars($row['notes'])) ?></td>
            <td><?= $row['created_at'] ?></td>
        </tr>
    <?php endwhile; ?>
</table>
