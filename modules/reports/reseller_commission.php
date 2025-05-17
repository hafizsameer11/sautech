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
}
if ($reseller_id) {
    $filter .= " AND b.client_id = (SELECT client_id FROM resellers WHERE id = $reseller_id)";
}
if ($start && $end) {
    $filter .= " AND b.start_date >= '$start' AND b.end_date <= '$end'";
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
            <div class="col-md-3">
                <label>Start Date</label>
                <input type="date" name="start" class="form-control" value="<?= htmlspecialchars($start) ?>">
            </div>
            <div class="col-md-3">
                <label>End Date</label>
                <input type="date" name="end" class="form-control" value="<?= htmlspecialchars($end) ?>">
            </div>
            <div class="col-md-3 d-flex align-items-end">
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
                <table class="table table-bordered table-striped">
                    <thead class="table-dark">
                        <tr>
                            <th>Client</th>
                            <th>Description</th>
                            <th>Qty</th>
                            <th>Unit Price</th>
                            <th>Total</th>
                            <th>Start</th>
                            <th>End</th>
                        </tr>
                    </thead>
                    <tbody>
                        <?php foreach ($records as $row): ?>
                            <tr>
                                <td><?= htmlspecialchars($row['client_name']) ?></td>
                                <td><?= htmlspecialchars($row['description']) ?></td>
                                <td><?= $row['qty'] ?></td>
                                <td><?= number_format($row['unit_price'], 2) ?></td>
                                <td><?= number_format($row['qty'] * $row['unit_price'], 2) ?></td>
                                <td><?= htmlspecialchars($row['start_date']) ?></td>
                                <td><?= htmlspecialchars($row['end_date']) ?></td>
                            </tr>
                        <?php endforeach; ?>
                    </tbody>
                </table>
            </div>
        <?php else: ?>
            <p class="text-muted">No billing items found.</p>
        <?php endif; ?>
    </div>

    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.2/dist/js/bootstrap.bundle.min.js"></script>
</body>

</html>