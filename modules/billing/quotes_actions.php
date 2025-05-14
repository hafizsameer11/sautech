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
    // 📂 FETCH Service Categories
    if (isset($_POST['action']) && $_POST['action'] === 'fetch_categories') {
        $service_type_id = (int) ($_POST['service_type_id'] ?? 0);

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
    if (isset($_POST['action']) && $_POST['action'] === 'fetch_unit_price') {
        $service_category_id = (int) ($_POST['service_category_id'] ?? 0);

        if ($service_category_id > 0) {
            // Fetch unit_price and vat from the category price table
            $stmt = $conn->prepare("SELECT * FROM billing_category_prices WHERE service_category_id = ? LIMIT 1");
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
                    'unit_price' => $price['unit_price'],
                    'vat' => $price['vat_rate']
                ]);
            } else {
                echo json_encode([
                    'unit_price' => 0,
                    'vat' => 0
                ]);
            }
        } else {
            echo json_encode([
                'unit_price' => 0,
                'vat' => 0
            ]);
        }
        exit;
    }
    if (isset($_POST['action']) && $_POST['action'] === 'send_quote') {
        $quote_id = (int) $_POST['quote_id'];
        $sender_name = trim($_POST['sender_name']);
        $recipient_name = trim($_POST['recipient_name']);
        $recipient_email = trim($_POST['recipient_email']);
        $message = trim($_POST['message']);

        // Fetch quote details
        $stmt = $conn->prepare("SELECT q.*, c.client_name, c.email AS client_email, b.company_name FROM quotes q
        JOIN clients c ON q.client_id = c.id
        JOIN billing_invoice_companies b ON q.quoted_company_id = b.id
        WHERE q.id = ?");
        $stmt->bind_param("i", $quote_id);
        $stmt->execute();
        $quote = $stmt->get_result()->fetch_assoc();
        // echo "<pre>";
        // print_r($quote);
        // echo "</pre>";
        // exit;

        if ($quote) {
            $subject = "Quote {$quote['quote_number']} from $sender_name";
            $body = "Dear $recipient_name,<br><br>";
            $body .= "Please find your quote below:<br><br>";
            $body .= "<strong>Quote Number:</strong> {$quote['quote_number']}<br>";
            $body .= "<strong>Client:</strong> {$quote['client_name']}<br>";
            $body .= "<strong>Company:</strong> {$quote['company_name']}<br>";
            $body .= "<strong>Description:</strong> {$quote['description']}<br>";
            $body .= "<strong>Qty:</strong> {$quote['qty']}<br>";
            $body .= "<strong>Unit Price:</strong> {$quote['unit_price']}<br>";
            $body .= "<strong>VAT:</strong> {$quote['vat']}<br>";
            $body .= "<strong>Total:</strong> {$quote['total_incl_vat']}<br>";
            $body .= "<strong>Status:</strong> {$quote['status']}<br>";
            $body .= "<br>" . nl2br(htmlspecialchars($message)) . "<br><br>Kind regards,<br>$sender_name";

            // Send email (simple PHP mail, for production use PHPMailer)
            $headers = "MIME-Version: 1.0\r\n";
            $headers .= "Content-type:text/html;charset=UTF-8\r\n";
            $headers .= "From: $sender_name <no-reply@yourdomain.com>\r\n";

            $mailSent = mail($recipient_email, $subject, $body, $headers);

            // Log the send action
            $logStmt = $conn->prepare("INSERT INTO quote_email_log (quote_id, sender_name, recipient_name, recipient_email, sent_at, message, status) VALUES (?, ?, ?, ?, NOW(), ?, ?)");
            $status = $mailSent ? 'sent' : 'failed';
            $logStmt->bind_param("isssss", $quote_id, $sender_name, $recipient_name, $recipient_email, $message, $status);
            $logStmt->execute();

            if ($mailSent) {
                $_SESSION['success'] = "Quote sent successfully!";
            } else {
                $_SESSION['error'] = "Failed to send quote email.";
            }
        } else {
            $_SESSION['error'] = "Quote not found.";
        }
        header("Location: quotes.php");
        exit;
    }
    $action = $_POST['action'];

    // Add Quote
    if ($action === 'add') {
        $reference = $_POST['reference'];
        $sales_person = $_POST['sales_person'];
        $quote_date = $_POST['quote_date'];
        $due_date = $_POST['due_date'];
        $service_type_id = $_POST['service_type_id'];
        $service_category_id = $_POST['service_category_id'];
        $quote_number = $_POST['quote_number'];
        $client_id = (int) $_POST['client_id'];
        $quoted_company_id = (int) $_POST['quoted_company_id'];
        $description = $conn->real_escape_string($_POST['description']);
        $qty = (int) $_POST['qty'];
        $unit_price = (float) $_POST['unit_price'];
        $vat = (float) $_POST['vat'];
        $status = $conn->real_escape_string($_POST['status']);

        $price_ex_vat = $qty * $unit_price;
        $total_incl_vat = $price_ex_vat + ($price_ex_vat * $vat / 100);

        $stmt = $conn->prepare("INSERT INTO quotes (
    quote_number, client_id, quoted_company_id, description, qty, unit_price,
    price_ex_vat, vat, total_incl_vat, status, reference, sales_person,
    quote_date, due_date, service_type_id, service_category_id
    ) VALUES (?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?)");

        $stmt->bind_param(
            "siisiddddsssssii",
            $quote_number,
            $client_id,
            $quoted_company_id,
            $description,
            $qty,
            $unit_price,
            $price_ex_vat,
            $vat,
            $total_incl_vat,
            $status,
            $reference,
            $sales_person,
            $quote_date,
            $due_date,
            $service_type_id,
            $service_category_id
        );



        if ($stmt->execute()) {
            $_SESSION['success'] = "Quote added successfully.";
        } else {
            $_SESSION['error'] = "Error adding quote: " . $stmt->error;
        }
        $stmt->close();
    }

    // Edit Quote
    elseif ($action === 'edit') {
        $reference = $_POST['reference'];
        $sales_person = $_POST['sales_person'];
        $quote_date = $_POST['quote_date'];
        $due_date = $_POST['due_date'];
        $service_type_id = $_POST['service_type_id'];
        $service_category_id = $_POST['service_category_id'];
        $id = (int) $_POST['id'];
        $client_id = (int) $_POST['client_id'];
        $quoted_company_id = (int) $_POST['quoted_company_id'];
        $description = $conn->real_escape_string($_POST['description']);
        $qty = (int) $_POST['qty'];
        $unit_price = (float) $_POST['unit_price'];
        $vat = (float) $_POST['vat'];
        $status = $conn->real_escape_string($_POST['status']);

        $price_ex_vat = $qty * $unit_price;
        $total_incl_vat = $price_ex_vat + ($price_ex_vat * $vat / 100);

        $stmt = $conn->prepare("UPDATE quotes SET
    client_id = ?, quoted_company_id = ?, description = ?, qty = ?, unit_price = ?,
    price_ex_vat = ?, vat = ?, total_incl_vat = ?, status = ?, reference = ?,
    sales_person = ?, quote_date = ?, due_date = ?, service_type_id = ?, service_category_id = ?
    WHERE id = ?");
        $stmt->bind_param(
            "iisiddddssssssii",
            $client_id,
            $quoted_company_id,
            $description,
            $qty,
            $unit_price,
            $price_ex_vat,
            $vat,
            $total_incl_vat,
            $status,
            $reference,
            $sales_person,
            $quote_date,
            $due_date,
            $service_type_id,
            $service_category_id,
            $id
        );


        if ($stmt->execute()) {
            $_SESSION['success'] = "Quote updated successfully.";
        } else {
            $_SESSION['error'] = "Error updating quote: " . $stmt->error;
        }
        $stmt->close();
    }

    // Delete Quote
    elseif ($action === 'delete') {
        $id = (int) $_POST['id'];
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