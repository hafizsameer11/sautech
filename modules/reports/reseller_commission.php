<?php
$db_host = "localhost";
$db_user = "clientzone_user";
$db_pass = "S@utech2024!";
$db_name = "clientzone";

$conn = new mysqli($db_host, $db_user, $db_pass, $db_name);
if ($conn->connect_error) {
    die("Connection failed: " . $conn->connect_error);
}
session_start();
require_once '../../vendor/autoload.php';
require_once '../../helper/email_helper.php';
include('../components/permissioncheck.php');
$alert = '';
$reseller_id = $_GET['reseller_id'] ?? '';
$start = $_GET['start'] ?? '';
$end = $_GET['end'] ?? '';

$resellers = $conn->query("SELECT r.id,r.name, c.client_name FROM resellers r LEFT JOIN clients c ON r.client_id = c.id");

$filter = '';
if (!hasPermission('reseller commission', 'View all')) {
    $filter = "WHERE b.created_by = $_SESSION[user_id]";

    $loginUserId = $_SESSION['user_id'];
    $getLoginUser = $conn->query("SELECT * FROM resellers WHERE register_id = $loginUserId LIMIT 1");
    $reseller = $getLoginUser ? $getLoginUser->fetch_assoc() : 'not found';
    $reseller_id = $reseller['id'] ?? '';
    $getClient = $conn->query("SELECT client_id FROM resellers WHERE id = $reseller_id");
    $client = $getClient->fetch_assoc();
    $client_id = $client['client_id'];
    $client_ids = json_decode($client['client_id'], true);
    if (is_array($client_ids)) {
        $client_ids_escaped = array_map('intval', $client_ids);
        $client_ids_str = implode(',', $client_ids_escaped);
        $filter .= " AND b.client_id IN ($client_ids_str)";
    } else {
        $client_id_escaped = intval($client['client_id']);
        $filter .= " AND b.client_id = $client_id_escaped";
    }
}
if ($reseller_id) {
    $getClient = $conn->query("SELECT client_id FROM resellers WHERE id = $reseller_id");
    $client = $getClient->fetch_assoc();
    $client_id = $client['client_id'];
    $client_ids = json_decode($client['client_id'], true);
    if (is_array($client_ids)) {
        $client_ids_escaped = array_map('intval', $client_ids);
        $client_ids_str = implode(',', $client_ids_escaped);
        $filter .= " AND b.client_id IN ($client_ids_str)";
    } else {
        $client_id_escaped = intval($client['client_id']);
        $filter .= " AND b.client_id = $client_id_escaped";
    }
}

if (!empty($_GET['start']) || !empty($_GET['end'])) {
    if (!empty($_GET['start']) && !empty($_GET['end'])) {
        $startDate = $conn->real_escape_string($_GET['start']);
        $endDate = $conn->real_escape_string($_GET['end']);
        // Include any billing item that is active within this range
        $filter .= " AND (b.start_date <= '$endDate' AND b.end_date >= '$startDate')";
    } elseif (!empty($_GET['start'])) {
        $startDate = $conn->real_escape_string($_GET['start']);
        $filter .= " AND b.start_date >= '$startDate'";
    } elseif (!empty($_GET['end'])) {
        $endDate = $conn->real_escape_string($_GET['end']);
        $filter .= " AND b.start_date <= '$endDate'";
    }
}
$sql = "SELECT b.*, c.client_name 
        FROM billing_items b 
        LEFT JOIN clients c ON b.client_id = c.id
        $filter 
        ORDER BY b.start_date DESC";

$records = $conn->query($sql);

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    if (isset($_POST['export_csv'])) {
        header('Content-Type: text/csv');
        header('Content-Disposition: attachment; filename="reseller_commission.csv"');
        $out = fopen('php://output', 'w');
        fputcsv($out, ['Client', 'Description', 'Qty', 'Unit Price', 'Total', 'Start', 'End']);
        foreach ($records as $row) {
            fputcsv($out, [
                $row['client_name'],
                $row['description'],
                $row['qty'],
                $row['unit_price'],
                $row['qty'] * $row['unit_price'],
                $row['start_date'],
                $row['end_date']
            ]);
        }
        fclose($out);
        // exit;
    }

    if (isset($_POST['send_email'])) {
        ob_start();
        $csv = fopen('php://output', 'w');
        fputcsv($csv, ['Client', 'Description', 'Qty', 'Unit Price', 'Total', 'Start', 'End']);
        foreach ($records as $row) {
            fputcsv($csv, [
                $row['client_name'],
                $row['description'],
                $row['qty'],
                $row['unit_price'],
                $row['qty'] * $row['unit_price'],
                $row['start_date'],
                $row['end_date']
            ]);
        }
        fclose($csv);
        $csvContent = ob_get_clean();

        $sendStatus = sendEmailWithAttachment(
            $_POST['recipient_email'],
            $_POST['recipient_name'],
            $_POST['sender_name'],
            $csvContent
        );

        if ($sendStatus === true) {
            $alert = '📧 Report emailed successfully!';
        } else {
            $alert = '❌ Email failed: ' . $sendStatus;
        }
    }

}
?>

<!DOCTYPE html>
<html>

<head>
    <meta charset="UTF-8">
    <title>Reseller Commission Report</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.2/dist/css/bootstrap.min.css" rel="stylesheet">
</head>

<body>
    <div class="px-5 mt-5">
        <?php if ($alert): ?>
            <div class="alert alert-success"><?= htmlspecialchars($alert) ?></div>
        <?php endif;
        session_abort();
        ?>

        <div class="d-flex align-items-center">
            <?php session_start(); ?>
            <h2>Reseller Commission Report</h2>
        </div>

        <form method="GET" class="row g-3 mb-4">
            <?php if (hasPermission('reseller commission', 'View all')): ?>
            <div class="col-md-3">
                <label>Reseller</label>
                <select name="reseller_id" class="form-select">
                    <option value="">All Resellers</option>
                    <?php while ($r = $resellers->fetch_assoc()): ?>
                        <option value="<?= $r['id'] ?>" <?= $r['id'] == $reseller_id ? 'selected' : '' ?>>
                            <?= htmlspecialchars($r['name']) ?>
                        </option>
                    <?php endwhile; ?>
                </select>
            </div>
            <?php endif; ?>
            <div class="<?= (!hasPermission('reseller commission', 'View all')) ? 'col-md-4' : 'col-md-3'  ?>">
                <label>Start Date</label>
                <input type="date" name="start" class="form-control" value="<?= htmlspecialchars($start) ?>">
            </div>
            <div class="<?= (!hasPermission('reseller commission', 'View all')) ? 'col-md-4' : 'col-md-3'  ?>">
                <label>End Date</label>
                <input type="date" name="end" class="form-control" value="<?= htmlspecialchars($end) ?>">
            </div>
            <div class="<?= (!hasPermission('reseller commission', 'View all')) ? 'col-md-4' : 'col-md-3'  ?> d-flex align-items-end">
                <button type="submit" class="btn btn-primary w-100">Apply Filter</button>
            </div>
        </form>

        <?php if ($records && $records->num_rows > 0): ?>
            <form method="POST" class="mb-4">
                <input type="hidden" name="reseller_id" value="<?= htmlspecialchars($reseller_id) ?>">
                <input type="hidden" name="start" value="<?= htmlspecialchars($start) ?>">
                <input type="hidden" name="end" value="<?= htmlspecialchars($end) ?>">

                <div class="row g-2 mb-3">
                    <?php if (hasPermission('reseller commission', 'Send Email')): ?>

                        <div class="col-md-3">
                            <label>Sender Name</label>
                            <input type="text" name="sender_name" class="form-control">
                        </div>
                        <div class="col-md-3">
                            <label>Recipient Name</label>
                            <input type="text" name="recipient_name" class="form-control">
                        </div>
                        <div class="col-md-3">
                            <label>Recipient Email</label>
                            <input type="email" name="recipient_email" class="form-control">
                        </div>
                    <?php endif; ?>
                    <div class="col-md-3 d-flex align-items-end">
                        <?php if (hasPermission('reseller commission', 'Send Email')): ?>
                            <div class="form-check me-2">
                                <input class="form-check-input" type="checkbox" name="send_email" id="send_email">
                                <label class="form-check-label" for="send_email">Email Report</label>
                            </div>
                        <?php endif; ?>
                        <button type="submit" name="export_csv" class="btn btn-success">Export CSV</button>
                    </div>
                </div>
            </form>

            <div class="table-responsive">
                <table class="table table-striped">
                    <thead class="table-dark">
                        <tr>
                            <th>Client</th>
                            <th>Description</th>
                            <th>Qty</th>
                            <th>Unit Price</th>
                            <th>Total Ex VAT</th> <!-- New column -->
                            <th>VAT (15%)</th> <!-- New column -->
                            <th>Total Incl VAT</th> <!-- New column -->
                            <th>Start</th>
                            <th>End</th>
                        </tr>
                    </thead>
                    <tbody>
                        <?php
                        $subTotalExVat = 0;
                        $subTotalVat = 0;
                        $subTotalInclVat = 0;

                        foreach ($records as $row):
                            $totalExVat = $row['qty'] * $row['unit_price'];
                            $vat = $totalExVat * 0.15; // Assuming 15% VAT
                            $totalInclVat = $totalExVat + $vat;

                            $subTotalExVat += $totalExVat;
                            $subTotalVat += $vat;
                            $subTotalInclVat += $totalInclVat;
                            ?>
                            <tr>
                                <td><?= htmlspecialchars($row['client_name']) ?></td>
                                <td><?= htmlspecialchars($row['description']) ?></td>
                                <td><?= $row['qty'] ?></td>
                                <td><?= number_format($row['unit_price'], 2) ?></td>
                                <td><?= number_format($totalExVat, 2) ?></td> <!-- Total Ex VAT -->
                                <td><?= number_format($vat, 2) ?></td> <!-- VAT -->
                                <td><?= number_format($totalInclVat, 2) ?></td> <!-- Total Incl VAT -->
                                <td><?= htmlspecialchars($row['start_date']) ?></td>
                                <td><?= htmlspecialchars($row['end_date']) ?></td>
                            </tr>
                        <?php endforeach; ?>
                    </tbody>
                    <tfoot>
                        <tr>
                            <td colspan="9" style="border:none;">
                                <div style="width: 100%;">
                                    <!-- Left spacer to push totals right -->
                                    <div style="width: 60%; display: inline-block;"></div>
                                    <!-- Totals Section -->
                                    <div
                                        style="width: 38%; display: inline-block; vertical-align: top; border: 1px solid #ddd; padding: 10px; font-size: 10pt;">
                                        <div style="text-align: left; font-weight: bold; margin-bottom: 10px;">
                                            Sub Total:
                                        </div>

                                        <div style="display: flex; justify-content: space-between; margin-bottom: 5px;">
                                            <span><strong>Sub Total Ex VAT</strong></span>
                                            <span><?= number_format($subTotalExVat, 2) ?></span>
                                        </div>

                                        <div style="display: flex; justify-content: space-between; margin-bottom: 5px;">
                                            <span><strong>Sub Total VAT</strong></span>
                                            <span><?= number_format($subTotalVat, 2) ?></span>
                                        </div>

                                        <div
                                            style="display: flex; justify-content: space-between; border-top: 1px solid #000; padding-top: 8px;">
                                            <span><strong>Sub Total Incl VAT</strong></span>
                                            <span><strong><?= number_format($subTotalInclVat, 2) ?></strong></span>
                                        </div>
                                    </div>
                                </div>
                            </td>
                        </tr>
                    </tfoot>
                </table>
            <?php else: ?>
                <p class="text-muted">No billing items found.</p>
            <?php endif; ?>

        </div>

        <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.2/dist/js/bootstrap.bundle.min.js"></script>
</body>

</html>