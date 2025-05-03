<?php
header('Content-Type: text/csv');
header('Content-Disposition: attachment;filename=client_services_export.csv');

$conn = new mysqli("localhost", "clientzone_user", "S@utech2024!", "clientzone");

$result = $conn->query("SELECT * FROM client_services WHERE is_deleted = 0 ORDER BY created_at DESC");

$output = fopen('php://output', 'w');
fputcsv($output, ['Client', 'Type', 'Service', 'Specs', 'Status', 'Amount', 'Invoice', 'Notes', 'Created']);

while ($row = $result->fetch_assoc()) {
    fputcsv($output, [
        $row['client_name'],
        $row['service_type'],
        $row['service_name'],
        $row['specs'],
        $row['status'],
        number_format($row['billing_amount'], 2),
        $row['invoice_link'],
        $row['notes'],
        $row['created_at']
    ]);
}

fclose($output);

