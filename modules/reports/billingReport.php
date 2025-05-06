<?php
error_reporting(E_ALL);
ini_set('display_errors', 1);

$db_host = "localhost";
    $db_user = "client_zone";
    $db_pass = "S@utech2024!";
    $db_name = "clientzone";

$conn = new mysqli($db_host, $db_user, $db_pass, $db_name);
if ($conn->connect_error) {
    die("Connection failed: " . $conn->connect_error);
}

$clients = $conn->query("SELECT id, client_name FROM clients");

// Apply filters
$where = [];
if (!empty($_GET['client_id'])) {
    $where[] = "b.client_id = " . (int) $_GET['client_id'];
}
if (!empty($_GET['invoice_type'])) {
    $where[] = "b.invoice_type = '" . $conn->real_escape_string($_GET['invoice_type']) . "'";
}
if (!empty($_GET['currency'])) {
    $where[] = "b.currency = '" . $conn->real_escape_string($_GET['currency']) . "'";
}
if (!empty($_GET['frequency'])) {
    $where[] = "b.frequency = '" . $conn->real_escape_string($_GET['frequency']) . "'";
}

$filterSql = !empty($where) ? 'WHERE ' . implode(' AND ', $where) : '';

$billingRecords = $conn->query("
    SELECT b.*, c.client_name
    FROM billing_items b
    LEFT JOIN clients c ON c.id = b.client_id
    $filterSql
    ORDER BY b.id DESC
");
?>

<!DOCTYPE html>
<html>

<head>
    <meta charset="UTF-8">
    <title>Billing Report</title>
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.2/dist/css/bootstrap.min.css">
</head>

<body>

    <div class="">
        <div style="width:93%; margin: auto; ">
            <!-- Export -->
            <div class="mt-5 mb-5 d-flex align-items-center justify-content-between">
                <div class="d-flex align-items-center ">
                    <?php include('../components/Backbtn.php') ?>
                    <h3 class=" d-flex align-items-center">
                        <i class="bi bi-people-fill me-2 text-secondary" style="font-size: 1.5rem;"></i>
                        <span class="fw-semibold text-dark">Billing Report</span>
                    </h3>
                </div>
                <!-- Left-aligned Title -->
                <form action="export-billing.php" method="POST" class="text-end d-flex gap-2 col-md-3 ">
                    <?php foreach ($_GET as $k => $v): ?>
                        <input type="hidden" name="<?= htmlspecialchars($k) ?>" value="<?= htmlspecialchars($v) ?>">
                    <?php endforeach; ?>

                    <!-- User Logins Button -->
                    <!-- Export to Excel Button -->
                    <button type="submit" class="btn btn-success p-3 h5 py-2 w-100">Export to Excel</button>
                </form>
            </div>
        </div>
        <!-- Filters -->
        <form class="card shadow-sm p-4 mb-4" style="width:93%; margin: auto; " method="GET">
            <div class="row g-3 align-items-end">

                <div class="col-md-3">
                    <label class="form-label">Client</label>
                    <select name="client_id" class="form-select">
                        <option value="">All Clients</option>
                        <?php foreach ($clients as $client): ?>
                            <option value="<?= $client['id'] ?>" <?= isset($_GET['client_id']) && $_GET['client_id'] == $client['id'] ? 'selected' : '' ?>>
                                <?= htmlspecialchars($client['client_name']) ?>
                            </option>
                        <?php endforeach; ?>
                    </select>
                </div>

                <div class="col-md-3">
                    <label class="form-label">Invoice Type</label>
                    <select name="invoice_type" class="form-select">
                        <option value="">All</option>
                        <option value="debit" <?= ($_GET['invoice_type'] ?? '') === 'debit' ? 'selected' : '' ?>>Debit
                        </option>
                        <option value="invoice" <?= ($_GET['invoice_type'] ?? '') === 'invoice' ? 'selected' : '' ?>>
                            Invoice</option>
                    </select>
                </div>

                <div class="col-md-3">
                    <label class="form-label">Currency</label>
                    <select name="currency" class="form-select">
                        <option value="">All</option>
                        <option value="USD" <?= ($_GET['currency'] ?? '') === 'NAD' ? 'selected' : '' ?>>NAD</option>
                        <option value="PKR" <?= ($_GET['currency'] ?? '') === 'ZAR' ? 'selected' : '' ?>>Zar</option>
                        <option value="PKR" <?= ($_GET['currency'] ?? '') === 'ZAR' ? 'selected' : '' ?>>USD</option>
                    </select>
                </div>

                <div class="col-md-3">
                    <label class="form-label">Frequency</label>
                    <select name="frequency" class="form-select">
                        <option value="">All</option>
                        <option value="once_off" <?= ($_GET['frequency'] ?? '') === 'once_off' ? 'selected' : '' ?>>
                            Once Off</option>
                        <option value="monthly" <?= ($_GET['frequency'] ?? '') === 'monthly' ? 'selected' : '' ?>>
                            Monthly</option>
                        <option value="annually" <?= ($_GET['frequency'] ?? '') === 'annually' ? 'selected' : '' ?>>
                            Annually</option>
                        <option value="finance" <?= ($_GET['frequency'] ?? '') === 'finance' ? 'selected' : '' ?>>
                            Finance</option>
                    </select>
                </div>

                <div class="col-12 text-end">
                    <button type="submit" class="btn btn-primary">Apply Filters</button>
                    <a href="reports-billing.php" class="btn btn-secondary">Reset</a>
                </div>
            </div>
        </form>

        <div style="width:93%; margin: auto; ">
            <!-- Export -->
            <!-- Table -->
            <div style="max-height: 600px; overflow-y: auto;" class="table-responsive">
                <table class="table table-bordered">
                    <thead class="table-light text-center">
                        <tr>
                            <th>#</th>
                            <th>Client</th>
                            <th>Description</th>
                            <th>Qty</th>
                            <th>Price</th>
                            <th>VAT</th>
                            <th>Total</th>
                            <th>Invoice Type</th>
                            <th>Currency</th>
                            <th>Frequency</th>
                        </tr>
                    </thead>
                    <tbody>
                        <?php $i = 1;
                        while ($row = $billingRecords->fetch_assoc()):
                            $subtotal = $row['qty'] * $row['unit_price'];
                            $vat = ($row['vat_rate'] / 100) * $subtotal;
                            $total = $subtotal + $vat;
                            ?>
                            <tr>
                                <td class="text-center"><?= $i++ ?></td>
                                <td><?= htmlspecialchars($row['client_name']) ?></td>
                                <td><?= htmlspecialchars($row['description']) ?></td>
                                <td class="text-center"><?= $row['qty'] ?></td>
                                <td class="text-end"><?= number_format($row['unit_price'], 2) ?></td>
                                <td class="text-end"><?= number_format($vat, 2) ?></td>
                                <td class="text-end fw-bold"><?= number_format($total, 2) ?></td>
                                <td class="text-center"><?= $row['invoice_type'] ?></td>
                                <td class="text-center"><?= $row['currency'] ?></td>
                                <td class="text-center"><?= ucfirst($row['frequency']) ?></td>
                            </tr>
                        <?php endwhile; ?>
                    </tbody>
                </table>
            </div>

        </div>

    </div>

</body>

</html>