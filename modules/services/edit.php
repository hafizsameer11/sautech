<?php
error_reporting(E_ALL);
ini_set('display_errors', 1);

$conn = new mysqli("localhost", "root", "", "clientzone");


$id = intval($_GET['id'] ?? 0);
if ($id <= 0) {
    die("Invalid ID");
}

$result = $conn->query("SELECT * FROM client_services WHERE id = $id LIMIT 1");
$service = $result->fetch_assoc();
if (!$service) {
    die("Service not found");
}

function validateDate($date) {
    $d = DateTime::createFromFormat('Y-m-d', $date);
    return $d && $d->format('Y-m-d') === $date ? $date : null;
}

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $client     = $_POST['client_name'];
    $type       = $_POST['service_type'];
    $name       = $_POST['service_name'];
    $specs      = $_POST['specs'];
    $status     = $_POST['status'];
    $currency   = $_POST['currency'];
    $quantity   = intval($_POST['quantity']);

    $start = validateDate($_POST['start_date']);
    $end   = validateDate($_POST['end_date']);

    $excludeVAT = isset($_POST['exclude_vat']) ? 1 : 0;
    $exVAT      = floatval($_POST['ex_vat_amount']);
    $vatAmount  = $excludeVAT ? round($exVAT * 0.15, 2) : 0;
    $totalVAT   = $excludeVAT ? round($exVAT + $vatAmount, 2) : $exVAT;
    $totalVAT   = $totalVAT * max(1, $quantity);
    $vatAmount  = $vatAmount * max(1, $quantity);
    $invoice    = $_POST['invoice_link'];
    $notes      = $_POST['notes'];

    $conn->query("INSERT INTO client_service_edits (service_id, old_quantity, new_quantity, edited_at) VALUES ($id, {$service['quantity']}, $quantity, NOW())");

    $sql = "UPDATE client_services SET
                client_name=?, service_type=?, service_name=?, specs=?, status=?, currency=?, quantity=?,
                start_date=" . ($start ? "?" : "NULL") . ",
                end_date=" . ($end ? "?" : "NULL") . ",
                exclude_vat=?, ex_vat_amount=?, vat_amount=?, total_with_vat=?, invoice_link=?, notes=?
            WHERE id = ?";

    $stmt = $conn->prepare($sql);

    if (!$stmt) {
        die("Prepare failed: " . $conn->error);
    }

    $types = "sssssss" . ($start ? "s" : "") . ($end ? "s" : "") . "idddssi";
    $params = [
        &$client, &$type, &$name, &$specs, &$status, &$currency, &$quantity
    ];
    if ($start) $params[] = &$start;
    if ($end) $params[] = &$end;
    $params[] = &$excludeVAT;
    $params[] = &$exVAT;
    $params[] = &$vatAmount;
    $params[] = &$totalVAT;
    $params[] = &$invoice;
    $params[] = &$notes;
    $params[] = &$id;

    $bindNames[] = $types;
    foreach ($params as $k => &$val) {
        $bindNames[] = &$val;
    }

    call_user_func_array([$stmt, 'bind_param'], $bindNames);

    if (!$stmt->execute()) {
        echo "<pre>Debug: \nStart Date: $start\nEnd Date: $end\n";
        die("Update failed: " . $stmt->error . "</pre>");
    }

    header("Location: list.php");
    exit;
}
?>

<h2>Edit Client Service</h2>

<form method="post">
    Client Name: <input type="text" name="client_name" value="<?= htmlspecialchars($service['client_name']) ?>" required><br><br>
    Service Type: <input type="text" name="service_type" value="<?= htmlspecialchars($service['service_type']) ?>" required><br><br>
    Service Name: <input type="text" name="service_name" value="<?= htmlspecialchars($service['service_name']) ?>" required><br><br>
    Currency: <select name="currency">
        <option value="ZAR" <?= $service['currency'] === 'ZAR' ? 'selected' : '' ?>>ZAR</option>
        <option value="USD" <?= $service['currency'] === 'USD' ? 'selected' : '' ?>>USD</option>
    </select><br><br>
    Quantity: <input type="number" name="quantity" value="<?= $service['quantity'] ?>" min="1" required><br><br>
    Specs/Details: <textarea name="specs"><?= htmlspecialchars($service['specs']) ?></textarea><br><br>
    Status: <select name="status">
        <option <?= $service['status'] === 'Active' ? 'selected' : '' ?>>Active</option>
        <option <?= $service['status'] === 'Suspended' ? 'selected' : '' ?>>Suspended</option>
        <option <?= $service['status'] === 'Cancelled' ? 'selected' : '' ?>>Cancelled</option>
    </select><br><br>
    Start Date: <input type="date" name="start_date" value="<?= htmlspecialchars(date('Y-m-d', strtotime($service['start_date']))) ?>"><br>
    End Date: <input type="date" name="end_date" value="<?= htmlspecialchars(date('Y-m-d', strtotime($service['end_date']))) ?>"><br><br>
    Ex VAT Amount: <input type="number" step="0.01" name="ex_vat_amount" value="<?= $service['ex_vat_amount'] ?>" required><br><br>
    Apply VAT (15%): <input type="checkbox" name="exclude_vat" <?= $service['exclude_vat'] ? 'checked' : '' ?>><br><br>
    Invoice Link: <input type="text" name="invoice_link" value="<?= htmlspecialchars($service['invoice_link']) ?>"><br><br>
    Notes: <textarea name="notes"><?= htmlspecialchars($service['notes']) ?></textarea><br><br>
    <button type="submit">📆 Save Changes</button>
</form>
