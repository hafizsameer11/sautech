<?php
session_start(); // Start the session
$db_host = "localhost";
    $db_user = "clientzone_user";
    $db_pass = "S@utech2024!";
    $db_name = "clientzone";

$conn = new mysqli($db_host, $db_user, $db_pass, $db_name);
if ($conn->connect_error) {
    die("Connection failed: " . $conn->connect_error);
}

// Check if an action is provided
if (isset($_POST['action'])) {
    $action = $_POST['action'];

    // Add Quote
    if ($action === 'add') {
        $quote_number = $_POST['quote_number'];
        $client_id = (int)$_POST['client_id'];
        $quoted_company_id = (int)$_POST['quoted_company_id'];
        $description = $conn->real_escape_string($_POST['description']);
        $qty = (int)$_POST['qty'];
        $unit_price = (float)$_POST['unit_price'];
        $vat = (float)$_POST['vat'];
        $status = $conn->real_escape_string($_POST['status']);

        $price_ex_vat = $qty * $unit_price;
        $total_incl_vat = $price_ex_vat + ($price_ex_vat * $vat / 100);

        $stmt = $conn->prepare("INSERT INTO quotes (quote_number, client_id, quoted_company_id, description, qty, unit_price, price_ex_vat, vat, total_incl_vat, status) VALUES (?, ?, ?, ?, ?, ?, ?, ?, ?, ?)");
        $stmt->bind_param("siisidddds", $quote_number, $client_id, $quoted_company_id, $description, $qty, $unit_price, $price_ex_vat, $vat, $total_incl_vat, $status);

        if ($stmt->execute()) {
            $_SESSION['success'] = "Quote added successfully.";
        } else {
            $_SESSION['error'] = "Error adding quote: " . $stmt->error;
        }
        $stmt->close();
    }

    // Edit Quote
    elseif ($action === 'edit') {
        $id = (int)$_POST['id'];
        $client_id = (int)$_POST['client_id'];
        $quoted_company_id = (int)$_POST['quoted_company_id'];
        $description = $conn->real_escape_string($_POST['description']);
        $qty = (int)$_POST['qty'];
        $unit_price = (float)$_POST['unit_price'];
        $vat = (float)$_POST['vat'];
        $status = $conn->real_escape_string($_POST['status']);

        $price_ex_vat = $qty * $unit_price;
        $total_incl_vat = $price_ex_vat + ($price_ex_vat * $vat / 100);

        $stmt = $conn->prepare("UPDATE quotes SET client_id = ?, quoted_company_id = ?, description = ?, qty = ?, unit_price = ?, price_ex_vat = ?, vat = ?, total_incl_vat = ?, status = ? WHERE id = ?");
        $stmt->bind_param("iisiddddsi", $client_id, $quoted_company_id, $description, $qty, $unit_price, $price_ex_vat, $vat, $total_incl_vat, $status, $id);

        if ($stmt->execute()) {
            $_SESSION['success'] = "Quote updated successfully.";
        } else {
            $_SESSION['error'] = "Error updating quote: " . $stmt->error;
        }
        $stmt->close();
    }

    // Delete Quote
    elseif ($action === 'delete') {
        $id = (int)$_POST['id'];
        // echo "Deleting quote with ID: $id"; // Debugging line
        // return ;
        $stmt = $conn->prepare("DELETE FROM quotes WHERE id = ?");
        $stmt->bind_param("i", $id);

        if ($stmt->execute()) {
            $_SESSION['success'] = "Quote deleted successfully.";
            header("Location: quotes.php");
        } else {
            $_SESSION['error'] = "Error deleting quote: " . $stmt->error;
        }
        $stmt->close();
    }
}

// Redirect back to quotes.php
header("Location: quotes.php");
exit();
?>