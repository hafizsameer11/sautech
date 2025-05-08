<?php
error_reporting(E_ALL);
ini_set('display_errors', 1);

// Database Connection
$db_host = "localhost";
$db_user = "root";
$db_pass = "";
$db_name = "clientzone";

$conn = new mysqli($db_host, $db_user, $db_pass, $db_name);
if ($conn->connect_error) {
    die("Connection failed: " . $conn->connect_error);
}
// Add
if (isset($_POST['action']) && $_POST['action'] === 'add') {
    $client_id = (int)($_POST['client_id'] ?? 0);
    $supplier_id = (int)($_POST['supplier_id'] ?? 0);
    $service_type_id = (int)($_POST['service_type_id'] ?? 0);
    $service_category_id = (int)($_POST['service_category_id'] ?? 0);
    $description = $_POST['description'] ?? '';
    $quantity = (int)($_POST['quantity'] ?? 1);
    $unit_price = (float)($_POST['unit_price'] ?? 0);
    $vat_rate = (float)($_POST['vat_rate'] ?? 0);
    $charge_vat = isset($_POST['charge_vat']) ? (int)$_POST['charge_vat'] : 0;
    $invoice_frequency = $_POST['invoice_frequency'] ?? 'monthly';
    $start_date = !empty($_POST['start_date']) && strtotime($_POST['start_date']) ? date('Y-m-d', strtotime($_POST['start_date'])) : null;

    // Automatically set end_date for 'once_off' or 'annually' if not provided
    if (in_array($_POST['invoice_frequency'], ['once_off', 'annually']) && $start_date !== null && empty($_POST['end_date'])) {
        $end_date = date('Y-m-d', strtotime('+1 month', strtotime($start_date)));
    } else {
        $end_date = !empty($_POST['end_date']) && strtotime($_POST['end_date']) ? date('Y-m-d', strtotime($_POST['end_date'])) : null;
    }

    if ($client_id <= 0) {
        echo "error_invalid_client_id";
        exit;
    }

    // Fetch billing_type, currency, and currency_symbol from clients table
    $billing_type = null;
    $currency = null;
    $currency_symbol = null;
    $clientName = null;

    $clientStmt = $conn->prepare("SELECT billing_type, currency, currency_symbol, client_name FROM clients WHERE id = ?");
    $clientStmt->bind_param("i", $client_id);
    $clientStmt->execute();
    $clientResult = $clientStmt->get_result();

    if ($clientData = $clientResult->fetch_assoc()) {
        $billing_type = $clientData['billing_type'];
        $currency = $clientData['currency'];
        $currency_symbol = $clientData['currency_symbol'];
        $clientName = $clientData['client_name'];

        // If currency_symbol is null or empty, use the first letter of the currency
        if (empty($currency_symbol)) {
            $currency_symbol = strtoupper(substr($currency, 0, 1));
        }
    } else {
        echo "error_client_not_found";
        exit;
    }

    // VM Fields
    $cpu = $_POST['cpu'] ?? null;
    $memory = $_POST['memory'] ?? null;
    $hdd_sata = $_POST['hdd_sata'] ?? null;
    $hdd_ssd = $_POST['hdd_ssd'] ?? null;
    $os = $_POST['os'] ?? null;
    $ip_address = $_POST['ip_address'] ?? null;

    // Escape and sanitize inputs
    $clientName = mysqli_real_escape_string($conn, $clientName);
    $description = mysqli_real_escape_string($conn, $description);
    $invoice_frequency = mysqli_real_escape_string($conn, $invoice_frequency);
    $start_date = $start_date !== null ? mysqli_real_escape_string($conn, $start_date) : null;
    $end_date = $end_date !== null ? mysqli_real_escape_string($conn, $end_date) : null;
    $cpu = mysqli_real_escape_string($conn, $cpu);
    $memory = mysqli_real_escape_string($conn, $memory);
    $hdd_sata = mysqli_real_escape_string($conn, $hdd_sata);
    $hdd_ssd = mysqli_real_escape_string($conn, $hdd_ssd);
    $os = mysqli_real_escape_string($conn, $os);
    $ip_address = mysqli_real_escape_string($conn, $ip_address);
    $billing_type = mysqli_real_escape_string($conn, $billing_type);
    $currency = mysqli_real_escape_string($conn, $currency);
    $currency_symbol = mysqli_real_escape_string($conn, $currency_symbol);

    // Insert into billing_items
    $sql = "
    INSERT INTO billing_items (
        client_name, client_id, supplier_id, service_type_id, service_category_id,
        description, qty, unit_price, vat_rate, vat_applied,
        frequency, start_date, end_date, cpu, memory,
        hdd_sata, hdd_ssd, os, ip_address, invoice_type, currency, currency_symbol
    ) VALUES (
        '$clientName', $client_id, $supplier_id, $service_type_id, $service_category_id,
        '$description', $quantity, $unit_price, $vat_rate, $charge_vat,
        '$invoice_frequency', '$start_date', '$end_date', '$cpu', '$memory',
        '$hdd_sata', '$hdd_ssd', '$os', '$ip_address', '$billing_type', '$currency', '$currency_symbol'
    )";

    if (mysqli_query($conn, $sql)) {
        echo "success";
    } else {
        echo "error_execute: " . mysqli_error($conn);
    }
    exit;
}

// unit price
// 📥 FETCH Unit Price when Service Category Selected
if (isset($_POST['action']) && $_POST['action'] === 'fetch_unit_price') {
    $service_category_id = (int)($_POST['service_category_id'] ?? 0);

    if ($service_category_id > 0) {
        $stmt = $conn->prepare("SELECT unit_price FROM billing_category_prices WHERE service_category_id = ? LIMIT 1");
        if (!$stmt) {
            echo json_encode(['error' => 'error_prepare']);
            exit;
        }

        $stmt->bind_param("i", $service_category_id);
        $stmt->execute();
        $result = $stmt->get_result();
        $price = $result->fetch_assoc();

        if ($price) {
            echo json_encode([
                'unit_price' => $price['unit_price']
            ]);
        } else {
            echo json_encode([
                'unit_price' => 0
            ]);
        }
    } else {
        echo json_encode([
            'unit_price' => 0
        ]);
    }
    exit;
}


// ✏️ Edit Billing Item
if (isset($_POST['action']) && $_POST['action'] === 'edit') {
    $id = (int)($_POST['billing_id'] ?? 0);
    $client_id = (int)($_POST['client_id'] ?? 0);
    $supplier_id = (int)($_POST['supplier_id'] ?? 0);
    $service_type_id = (int)($_POST['service_type_id'] ?? 0);
    $service_category_id = (int)($_POST['service_category_id'] ?? 0);
    $description = $_POST['description'] ?? '';
    $quantity = (int)($_POST['quantity'] ?? 1);
    $unit_price = (float)($_POST['unit_price'] ?? 0);
    $vat_rate = (float)($_POST['vat_rate'] ?? 0);
    $charge_vat = isset($_POST['charge_vat']) ? (int)$_POST['charge_vat'] : 0;
    $invoice_frequency = $_POST['invoice_frequency'] ?? 'monthly';
    $start_date = !empty($_POST['start_date']) && strtotime($_POST['start_date']) ? date('Y-m-d', strtotime($_POST['start_date'])) : null;
    if (in_array($_POST['invoice_frequency'], ['once_off', 'annually']) && $start_date !== null && empty($_POST['end_date'])) {
        $end_date = date('Y-m-d', strtotime('+1 month', strtotime($start_date)));
    } else {
        $end_date = !empty($_POST['end_date']) && strtotime($_POST['end_date']) ? date('Y-m-d', strtotime($_POST['end_date'])) : null;
    }

    if ($client_id <= 0) {
        echo "error_invalid_client_id";
        exit;
    }

    // Fetch billing_type, currency, and currency_symbol from clients table
    $billing_type = null;
    $currency = null;
    $currency_symbol = null;
    $clientName = null;
    $clientStmt = $conn->prepare("SELECT billing_type, currency, currency_symbol, client_name FROM clients WHERE id = ?");
    $clientStmt->bind_param("i", $client_id);
    $clientStmt->execute();
    $clientResult = $clientStmt->get_result();
    if ($clientData = $clientResult->fetch_assoc()) {
        $billing_type = $clientData['billing_type'];
        $currency = $clientData['currency'];
        $currency_symbol = $clientData['currency_symbol'];
        $clientName = $clientData['client_name'];

        // If currency_symbol is null or empty, use the first letter of the currency
        if (empty($currency_symbol)) {
            $currency_symbol = strtoupper(substr($currency, 0, 1));
        }
    } else {
        echo "error_client_not_found";
        exit;
    }

    // VM Fields
    $cpu = $_POST['cpu'] ?? null;
    $memory = $_POST['memory'] ?? null;
    $hdd_sata = $_POST['hdd_sata'] ?? null;
    $hdd_ssd = $_POST['hdd_ssd'] ?? null;
    $os = $_POST['os'] ?? null;
    $ip_address = $_POST['ip_address'] ?? null;

    // Escape and sanitize inputs
    $clientName         = mysqli_real_escape_string($conn, $clientName);
    $description        = mysqli_real_escape_string($conn, $description);
    $invoice_frequency  = mysqli_real_escape_string($conn, $invoice_frequency);
    $start_date         = $start_date !== null ? mysqli_real_escape_string($conn, $start_date) : null;
    $end_date           = $end_date !== null ? mysqli_real_escape_string($conn, $end_date) : null;
    $cpu                = mysqli_real_escape_string($conn, $cpu);
    $memory             = mysqli_real_escape_string($conn, $memory);
    $hdd_sata           = mysqli_real_escape_string($conn, $hdd_sata);
    $hdd_ssd            = mysqli_real_escape_string($conn, $hdd_ssd);
    $os                 = mysqli_real_escape_string($conn, $os);
    $ip_address         = mysqli_real_escape_string($conn, $ip_address);
    $billing_type       = mysqli_real_escape_string($conn, $billing_type);
    $currency           = mysqli_real_escape_string($conn, $currency);
    $currency_symbol    = mysqli_real_escape_string($conn, $currency_symbol);

    // Update Query
    $sql = "
        UPDATE billing_items 
        SET 
            client_name = '$clientName',
            client_id = $client_id, 
            supplier_id = $supplier_id, 
            service_type_id = $service_type_id, 
            service_category_id = $service_category_id, 
            description = '$description', 
            qty = $quantity, 
            unit_price = $unit_price, 
            vat_rate = $vat_rate, 
            vat_applied = $charge_vat, 
            frequency = '$invoice_frequency', 
            start_date = '$start_date', 
            end_date = '$end_date', 
            cpu = '$cpu', 
            memory = '$memory', 
            hdd_sata = '$hdd_sata', 
            hdd_ssd = '$hdd_ssd', 
            os = '$os', 
            ip_address = '$ip_address', 
            invoice_type = '$billing_type', 
            currency = '$currency',
            currency_symbol = '$currency_symbol'
        WHERE id = $id
    ";

    if (mysqli_query($conn, $sql)) {
        echo "success";
    } else {
        echo "error_execute: " . mysqli_error($conn);
    }
    exit;
}


// ❌ Delete Billing Item (Soft Delete)
if (isset($_POST['action']) && $_POST['action'] === 'delete') {
    $id = (int)($_POST['id'] ?? 0);

    if ($id > 0) {
        $stmt = $conn->prepare("UPDATE billing_items SET is_deleted = 1 WHERE id = ?");
        if (!$stmt) {
            echo "error_prepare";
            exit;
        }

        $stmt->bind_param("i", $id);

        if ($stmt->execute()) {
            echo "success";
        } else {
            echo "error_execute";
        }
    } else {
        echo "error_invalid_id";
    }
    exit;
}

// 📂 FETCH Service Categories
if (isset($_POST['action']) && $_POST['action'] === 'fetch_categories') {
    $service_type_id = (int)($_POST['service_type_id'] ?? 0);

    if ($service_type_id > 0) {
        $stmt = $conn->prepare("SELECT id, category_name, has_vm_fields FROM billing_service_categories WHERE service_type_id = ? AND is_deleted = 0 ORDER BY category_name ASC");
        if (!$stmt) {
            echo json_encode(['error' => 'error_prepare']);
            exit;
        }

        $stmt->bind_param("i", $service_type_id);
        $stmt->execute();
        $result = $stmt->get_result();
        $categories = [];

        while ($row = $result->fetch_assoc()) {
            $categories[] = $row;
        }

        echo json_encode($categories);
    } else {
        echo json_encode([]);
    }
    exit;
}
