<?php
error_reporting(E_ALL);
ini_set('display_errors', 1);

// Database Connection
$conn = new mysqli("localhost", "clientzone_user", "S@utech2024!", "clientzone");
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
    $end_date = ($_POST['invoice_frequency'] === 'once_off') ? null : (!empty($_POST['end_date']) && strtotime($_POST['end_date']) ? date('Y-m-d', strtotime($_POST['end_date'])) : null);

    if ($client_id <= 0) {
        echo "error_invalid_client_id";
        exit;
    }

    // Fetch billing_type and currency from clients table
    $billing_type = null;
    $currency = null;
    $clientName= null ;
    $clientStmt = $conn->prepare("SELECT billing_type, currency,client_name FROM clients WHERE id = ?");
    $clientStmt->bind_param("i", $client_id);
    $clientStmt->execute();
    $clientResult = $clientStmt->get_result();
    if ($clientData = $clientResult->fetch_assoc()) {
        $billing_type = $clientData['billing_type'];
        $currency = $clientData['currency'];
        $clientName = $clientData['client_name'];
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

    $stmt = $conn->prepare("
        INSERT INTO billing_items 
        (client_name,client_id, supplier_id, service_type_id, service_category_id, description, qty, unit_price, vat_rate, vat_applied, frequency, start_date, end_date, cpu, memory, hdd_sata, hdd_ssd, os, ip_address, invoice_type, currency)
        VALUES (?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?)
    ");

    if (!$stmt) {
        echo "error_prepare";
        exit;
    }

    $stmt->bind_param(
        "iiiisiddssssssssssss",
        $clientName,
        $client_id,
        $supplier_id,
        $service_type_id,
        $service_category_id,
        $description,
        $quantity,
        $unit_price,
        $vat_rate,
        $charge_vat,
        $invoice_frequency,
        $start_date,
        $end_date,
        $cpu,
        $memory,
        $hdd_sata,
        $hdd_ssd,
        $os,
        $ip_address,
        $billing_type,
        $currency
    );

    if ($stmt->execute()) {
        echo "success";
    } else {
        echo "error_execute";
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
    $start_date = !empty($_POST['start_date']) ? $_POST['start_date'] : null;
    $end_date = ($_POST['invoice_frequency'] === 'once_off') ? null : (!empty($_POST['end_date']) && strtotime($_POST['end_date']) ? date('Y-m-d', strtotime($_POST['end_date'])) : null);

    // VM Fields
    $cpu = $_POST['cpu'] ?? null;
    $memory = $_POST['memory'] ?? null;
    $hdd_sata = $_POST['hdd_sata'] ?? null;
    $hdd_ssd = $_POST['hdd_ssd'] ?? null;
    $os = $_POST['os'] ?? null;
    $ip_address = $_POST['ip_address'] ?? null;

    if ($client_id <= 0) {
        echo "error_invalid_client_id";
        exit;
    }

    // ✅ Fetch billing_type and currency from clients table
    $billing_type = null;
    $currency = null;
    $clientName=null;
    $clientStmt = $conn->prepare("SELECT billing_type, currency FROM clients WHERE id = ?");
    $clientStmt->bind_param("i", $client_id);
    $clientStmt->execute();
    $clientResult = $clientStmt->get_result();
    if ($clientRow = $clientResult->fetch_assoc()) {
        $billing_type = $clientRow['billing_type'];
        $currency = $clientRow['currency'];
        $clientName = $clientRow['client_name'];
    } else {
        echo "error_client_not_found";
        exit;
    }

    // 🔁 Update Query with invoice_type and currency
    $stmt = $conn->prepare("
        UPDATE billing_items 
        SET client_name = ?,
        client_id = ?, 
            supplier_id = ?, 
            service_type_id = ?, 
            service_category_id = ?, 
            description = ?, 
            qty = ?, 
            unit_price = ?, 
            vat_rate = ?, 
            vat_applied = ?, 
            frequency = ?, 
            start_date = ?, 
            end_date = ?, 
            cpu = ?, 
            memory = ?, 
            hdd_sata = ?, 
            hdd_ssd = ?, 
            os = ?, 
            ip_address = ?, 
            invoice_type = ?, 
            currency = ?
        WHERE id = ?
    ");

    if (!$stmt) {
        echo "error_prepare";
        exit;
    }

    $stmt->bind_param(
        "iiiisiddisssssssssssi",
        $clientName,
        $client_id,
        $supplier_id,
        $service_type_id,
        $service_category_id,
        $description,
        $quantity,
        $unit_price,
        $vat_rate,
        $charge_vat,
        $invoice_frequency,
        $start_date,
        $end_date,
        $cpu,
        $memory,
        $hdd_sata,
        $hdd_ssd,
        $os,
        $ip_address,
        $billing_type,
        $currency,
        $id
    );

    if ($stmt->execute()) {
        echo "success";
    } else {
        echo "error_execute";
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
