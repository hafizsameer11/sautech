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
if (isset($_GET['processed']) && $_GET['processed'] !== '') {
    $where[] = "b.processed = " . (int) $_GET['processed'];
}
if (!empty($_GET['start_date']) || !empty($_GET['end_date'])) {
    if (!empty($_GET['start_date']) && !empty($_GET['end_date'])) {
        $startDate = $conn->real_escape_string($_GET['start_date']);
        $endDate = $conn->real_escape_string($_GET['end_date']);

        // Include any billing item that is active within this range
        $where[] = "(b.start_date <= '$endDate' AND b.end_date >= '$startDate')";
    } elseif (!empty($_GET['start_date'])) {
        $startDate = $conn->real_escape_string($_GET['start_date']);
        $where[] = "b.start_date >= '$startDate'";
    } elseif (!empty($_GET['end_date'])) {
        $endDate = $conn->real_escape_string($_GET['end_date']);
        $where[] = "b.start_date <= '$endDate'";
    }
}
// if (!empty($_GET['status'])) {
//     $where[] = "b.status = '" . $conn->real_escape_string($_GET['status']) . "'";
// }
// if (!empty($_GET['serial_number'])) {
//     $where[] = "b.serial_number = '" . $conn->real_escape_string($_GET['serial_number']) . "'";
// }

$filterSql = !empty($where) ? 'WHERE ' . implode(' AND ', $where) : '';

$billingRecords = $conn->query("
    SELECT b.*, c.client_name
    FROM billing_items b
    LEFT JOIN clients c ON c.id = b.client_id
    $filterSql
    ORDER BY b.id DESC
");
$currenceyQueries = $conn->query("
    SELECT b.*, c.client_name
    FROM billing_items b
    LEFT JOIN clients c ON c.id = b.client_id
    ORDER BY b.id DESC
");

if ($_SERVER['REQUEST_METHOD'] === 'POST' && !empty($_POST['billing_ids'])) {
    $billingIds = implode(',', array_map('intval', $_POST['billing_ids'])); // Sanitize IDs
    $sql = "UPDATE billing_items SET processed = 1 WHERE id IN ($billingIds)";

    if ($conn->query($sql)) {
        header('Location: billingReport.php?processed=0'); // Redirect to show unprocessed items
        exit;
    } else {
        echo "Error updating records: " . $conn->error;
    }
}
// else {
//     if (empty($_POST['billing_ids'])) {
//         echo "No items selected. Please select at least one item.";
//     }

?>

<!DOCTYPE html>
<html>

<head>
    <meta charset="UTF-8">
    <title>Billing Report</title>
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.2/dist/css/bootstrap.min.css">
</head>

<body>

    <div class="p-5">
        <div>
            <!-- Export -->
            <div class="mt-5 mb-5 d-flex align-items-center justify-content-between">
                <div class="d-flex align-items-center ">
                    <?php session_start(); ?>
                    <?php include('../components/permissioncheck.php') ?>
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
        <form class="card shadow-sm p-4 mb-4" method="GET">
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
                        <?php
                        $currencies = [];
                        while ($row = $currenceyQueries->fetch_assoc()):
                            if (!in_array($row['currency'], $currencies)):
                                $currencies[] = $row['currency'];
                                ?>
                                <option value="<?= $row['currency'] ?>" <?= ($_GET['currency'] ?? '') === $row['currency'] ? 'selected' : '' ?>>
                                    <?= htmlspecialchars($row['currency']) ?>
                                </option>
                                <?php
                            endif;
                        endwhile;
                        ?>
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

                <div class="col-md-3">
                    <label class="form-label">Processed</label>
                    <select name="processed" class="form-select">
                        <option value="">All</option>
                        <option value="1" <?= (isset($_GET['processed']) && $_GET['processed'] === '1') ? 'selected' : '' ?>>Yes
                        </option>
                        <option value="0" <?= (isset($_GET['processed']) && $_GET['processed'] === '0') ? 'selected' : '' ?>>No
                        </option>
                    </select>
                </div>

                <div class="col-md-3">
                    <label class="form-label">Date Range</label>
                    <input type="date" name="start_date" class="form-control"
                        value="<?= htmlspecialchars($_GET['start_date'] ?? '') ?>">
                    <input type="date" name="end_date" class="form-control mt-2"
                        value="<?= htmlspecialchars($_GET['end_date'] ?? '') ?>">
                </div>

                <!-- <div class="col-md-3">
                    <label class="form-label">Agreement Status</label>
                    <select name="status" class="form-select">
                        <option value="">All</option>
                        <option value="Active" <?= (isset($_GET['status']) && $_GET['status'] === 'Active') ? 'selected' : '' ?>>Active
                        </option>
                        <option value="Cancelled" <?= (isset($_GET['status']) && $_GET['status'] === 'Cancelled') ? 'selected' : '' ?>>Cancelled
                        </option>
                        <option value="Expired" <?= (isset($_GET['status']) && $_GET['status'] === 'Expired') ? 'selected' : '' ?>>Expired
                        </option>
                        <option value="Suspended" <?= (isset($_GET['status']) && $_GET['status'] === 'Suspended') ? 'selected' : '' ?>>Suspended
                        </option>
                    </select>
                </div>

                <div class="col-md-3">
                    <label class="form-label">Serial Number</label>
                    <input type="text" name="serial_number" class="form-control"
                        value="<?= htmlspecialchars($_GET['serial_number'] ?? '') ?>">
                </div> -->

                <div class="col-12 text-end">
                    <button type="submit" class="btn btn-primary">Apply Filters</button>
                    <a href="reports-billing.php" class="btn btn-secondary">Reset</a>
                </div>
            </div>
        </form>

        <div>
            <!-- Export -->
            <div id="alert"></div>
            <!-- Table -->
            <form method="POST" action="billingReport.php" id="billing-form">

                <div>
                    <!-- Submit Button -->
                    <!-- Table -->
                    <div style="max-height: 600px; overflow-y: auto;" class="table-responsive">
                        <table class="table table-bordered">
                            <thead class="table-light text-center">
                                <tr>
                                    <th><input type="checkbox" id="select-all"></th> <!-- Select All Checkbox -->
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
                                    <th>Start Date</th>
                                    <th>End Date</th>
                                    <th>Processed</th>
                                </tr>
                            </thead>
                            <tbody>
                                <?php $i = 1;
                                $billingRecords->data_seek(0);
                                while ($row = $billingRecords->fetch_assoc()):
                                    $subtotal = $row['qty'] * $row['unit_price'];
                                    $vat = ($row['vat_rate'] / 100) * $subtotal;
                                    $total = $subtotal + $vat;
                                    ?>
                                    <tr>
                                        <td class="text-center">
                                            <input type="checkbox" name="billing_ids[]" value="<?= $row['id'] ?>">
                                            <!-- Checkbox -->
                                        </td>
                                        <td class="text-center"><?= $i++ ?></td>
                                        <td><?= htmlspecialchars($row['client_name']) ?></td>
                                        <td><?= htmlspecialchars($row['description']) ?></td>
                                        <td class="text-center"><?= $row['qty'] ?></td>
                                        <td class="text-end"><?= $row['currency_symbol'] ?>
                                            <?= number_format($row['unit_price'], 2) ?>
                                        </td>
                                        <td class="text-end"><?= $row['currency_symbol'] ?>     <?= number_format($vat, 2) ?>
                                        </td>
                                        <td class="text-end fw-bold"><?= $row['currency_symbol'] ?>
                                            <?= number_format($total, 2) ?>
                                        </td>
                                        <td class="text-center"><?= $row['invoice_type'] ?></td>
                                        <td class="text-center"><?= $row['currency'] ?></td>
                                        <td class="text-center"><?= ucfirst($row['frequency']) ?></td>
                                        <td class="text-center text-nowrap">
                                            <!-- show start_date in fomrta form -->
                                            <?= date('d-m-Y', strtotime($row['start_date'])) ?>
                                        </td>
                                        <td class="text-center text-nowrap">
                                            <!-- show start_date in fomrta form -->
                                            <?= date('d-m-Y', strtotime($row['end_date'])) ?>
                                        </td>

                                        <td class="text-center"><?= $row['processed'] ? 'Yes' : 'No' ?></td>
                                    </tr>
                                <?php endwhile; ?>
                            </tbody>
                        </table>
                    </div>
                    <div class="text-end my-3">
                        <button type="button" id="calculate-total" class="btn btn-primary">Calculate Total</button>
                        <span class="ms-3 fw-bold">Total Amount: <span id="total-amount">0.00</span></span>
                    </div>
                    <?php if (hasPermission('billing report', 'Mark as procced')): ?>
                        <div class="text-end my-3">
                            <button type="submit" class="btn btn-success">Mark as Processed</button>
                        </div>
                    <?php endif; ?>
                </div>
            </form>

        </div>

    </div>

    <script>
        // Select all checkboxes
        document.getElementById('select-all').addEventListener('change', function () {
            const checkboxes = document.querySelectorAll('input[name="billing_ids[]"]');
            checkboxes.forEach(checkbox => checkbox.checked = this.checked);
        });

        // Attach submit handler to billing form only
        document.getElementById('billing-form').addEventListener('submit', function (e) {
            const checkboxes = document.querySelectorAll('input[name="billing_ids[]"]:checked');
            if (checkboxes.length === 0) {
                e.preventDefault();
                // Show Bootstrap alert dynamically
                const alertBox = document.createElement('div');
                alertBox.className = 'alert alert-danger mt-3';
                alertBox.textContent = 'Please select at least one item to process.';
                document.getElementById('alert').innerHTML = ''; // Clear previous alerts
                document.getElementById('alert').appendChild(alertBox);
            }
        });

        // Calculate total amount
        document.getElementById('calculate-total').addEventListener('click', function () {
            const checkboxes = document.querySelectorAll('input[name="billing_ids[]"]:checked');
            let total = 0;

            checkboxes.forEach(checkbox => {
                const row = checkbox.closest('tr');
                const totalCell = row.querySelector('td:nth-child(8)'); // Adjust column index for the "Total" column
                const totalValue = parseFloat(totalCell.textContent.replace(/[^\d.-]/g, '')); // Remove currency symbols
                if (!isNaN(totalValue)) {
                    total += totalValue;
                }
            });

            document.getElementById('total-amount').textContent = total.toFixed(2);
        });
    </script>

</body>

</html>