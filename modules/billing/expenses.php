<?php
// DB connection
$db_host = "localhost";
$db_user = "clientzone_user";
$db_pass = "S@utech2024!";
$db_name = "clientzone";

$conn = new mysqli($db_host, $db_user, $db_pass, $db_name);
if ($conn->connect_error)
    die("Connection failed: " . $conn->connect_error);

// Add Expense
if (isset($_POST['add_expense'])) {
    $supplier = $_POST['supplier'];
    $account_number = $_POST['account_number'];
    $method = $_POST['payment_method'];
    // $terms = $_POST['terms'];
    $frequency = $_POST['payment_frequency'];
    $amount = floatval($_POST['amount']);
    $vat = floatval($_POST['vat']);
    $total = $amount + $amount * $vat / 100;
    $setvar = isset($_POST['set_variable']) ? $_POST['set_variable'] : 0;
    $client = $_POST['client_id'];
    $entity = isset($_POST['entity']) ? $_POST['entity'] : null;
    $bank = isset($_POST['bank']) ? $_POST['bank'] : null;
    $type = isset($_POST['account_type']) ? $_POST['account_type'] : null;
    $number = isset($_POST['acc_number']) ? $_POST['acc_number'] : null;
    $notes = isset($_POST['notes']) ? $_POST['notes'] : null;
    $supplier_contact_number = $_POST['supplier_contact_number'] ?? null;
    $account_contact = $_POST['account_contact'] ?? null;
    $payment_date = $_POST['payment_date'] ?? null;
    $invoicing_company_id = $_POST['invoicing_company_id'] ?? null;
    $set_variable_text = $_POST['set_variable_text'] ?? null;

    $stmt = $conn->prepare("INSERT INTO expenses (
    supplier_id, st_account_number, payment_method, 
    payment_frequency, amount_ex_vat, vat_percent, total, 
    is_variable, client_id, entity, bank_name, 
    account_type, account_number, notes, 
    supplier_contact_number, account_contact, payment_date, 
    invoicing_company_id, set_variable_text
) VALUES (?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?)");

    $stmt->bind_param(
        "isssdddiiisssssssss",
        $supplier,
        $account_number,
        $method,
        $frequency,
        $amount,
        $vat,
        $total,
        $setvar,
        $client,
        $entity,
        $bank,
        $type,
        $number,
        $notes,
        $supplier_contact_number,
        $account_contact,
        $payment_date,
        $invoicing_company_id,
        $set_variable_text
    );

    $stmt->execute();
    header("Location: expenses.php");
}

// Edit Expense
if (isset($_POST['edit_expense'])) {
    $id = intval($_POST['id']); // Ensure ID is an integer
    $supplier = $_POST['supplier'];
    $account_number = $_POST['account_number'];
    $method = $_POST['payment_method'];
    // $terms = $_POST['terms'];
    $frequency = $_POST['payment_frequency'];
    $amount = floatval($_POST['amount']); // Ensure numeric value
    $vat = floatval($_POST['vat']);       // Ensure numeric value
    $total = $amount + $amount * $vat / 100; // Calculate total
    $setvar = isset($_POST['set_variable']) ? intval($_POST['set_variable']) : 0; // Ensure integer
    $client = intval($_POST['client_id']); // Ensure integer
    $entity = isset($_POST['entity']) ? $_POST['entity'] : null;
    $bank = isset($_POST['bank']) ? $_POST['bank'] : null;
    $type = isset($_POST['account_type']) ? $_POST['account_type'] : null;
    $number = isset($_POST['acc_number']) ? $_POST['acc_number'] : null;
    $notes = isset($_POST['notes']) ? $_POST['notes'] : null;
    $supplier_contact_number = $_POST['supplier_contact_number'] ?? null;
    $account_contact = $_POST['account_contact'] ?? null;
    $payment_date = $_POST['payment_date'] ?? null;
    $invoicing_company_id = $_POST['invoicing_company_id'] ?? null;
    $set_variable_text = $_POST['set_variable_text'] ?? null;

    $stmt = $conn->prepare("UPDATE expenses SET 
    supplier_id=?, st_account_number=?, payment_method=?, payment_frequency=?, 
    amount_ex_vat=?, vat_percent=?, total=?, is_variable=?, client_id=?, 
    entity=?, bank_name=?, account_type=?, account_number=?, notes=?, 
    supplier_contact_number=?, account_contact=?, payment_date=?, 
    invoicing_company_id=?, set_variable_text=? 
    WHERE id=?");

    $stmt->bind_param(
        "isssdddiiisssssssssi",
        $supplier,
        $account_number,
        $method,
        $frequency,
        $amount,
        $vat,
        $total,
        $setvar,
        $client,
        $entity,
        $bank,
        $type,
        $number,
        $notes,
        $supplier_contact_number,
        $account_contact,
        $payment_date,
        $invoicing_company_id,
        $set_variable_text,
        $id
    );




    $stmt->execute();
    header("Location: expenses.php");
    exit;
}

// Delete Expense
if (isset($_POST['delete_expense'])) {
    $id = $_POST['delete_id'];
    $conn->query("DELETE FROM expenses WHERE id = $id");
    header("Location: expenses.php");
}

// Fetch Suppliers and Clients
$suppliers = $conn->query("SELECT id, supplier_name FROM billing_suppliers");
$clients = $conn->query("SELECT id, client_name FROM clients");
// Fetch Invoicing Companies
$companies = $conn->query("SELECT id, company_name FROM billing_invoice_companies");
// Generate new account number
$latestAccount = $conn->query("SELECT st_account_number FROM expenses ORDER BY id DESC LIMIT 1");
$newAccountNumber = "STS100000";
if ($latestAccount->num_rows > 0) {
    $lastAccount = $latestAccount->fetch_assoc()['st_account_number'];
    $newAccountNumber = "STS" . (intval(str_replace("STS", "", $lastAccount)) + 1);
}
?>

<!DOCTYPE html>
<html>

<head>
    <title>Expenses Module</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
    <script src="https://code.jquery.com/jquery-3.7.1.min.js"></script>
    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script>
</head>

<body class="p-4">
    <div class="container">
        <!-- Heading and Add Button -->
        <div class="d-flex justify-content-between align-items-center mb-4">
            <div class="d-flex align-items-center ">
                <?php include('../components/Backbtn.php') ?>
                <?php include('../components/permissioncheck.php') ?>
                <h2>Expenses</h2>
            </div>
            <?php if (hasPermission('expenses', 'create')): ?>
                <button class="btn btn-success" data-bs-toggle="modal" data-bs-target="#addModal">+ Add Expense</button>
            <?php endif; ?>
        </div>

        <!-- Filters -->
        <form class="card shadow-sm p-4 mb-4" method="GET">
            <div class="row g-3 align-items-end">
                <!-- Client Filter -->
                <div class="col-md-3">
                    <label class="form-label">Client</label>
                    <select name="client_id" class="form-select">
                        <option value="">All Clients</option>
                        <?php
                        $clients->data_seek(0); // Reset pointer for reuse
                        while ($client = $clients->fetch_assoc()): ?>
                            <option value="<?= $client['id'] ?>" <?= isset($_GET['client_id']) && $_GET['client_id'] == $client['id'] ? 'selected' : '' ?>>
                                <?= htmlspecialchars($client['client_name']) ?>
                            </option>
                        <?php endwhile; ?>
                    </select>
                </div>

                <!-- Supplier Filter -->
                <div class="col-md-3">
                    <label class="form-label">Supplier</label>
                    <select name="supplier_id" class="form-select">
                        <option value="">All Suppliers</option>
                        <?php
                        $suppliers->data_seek(0); // Reset pointer for reuse
                        while ($supplier = $suppliers->fetch_assoc()): ?>
                            <option value="<?= $supplier['id'] ?>" <?= isset($_GET['supplier_id']) && $_GET['supplier_id'] == $supplier['id'] ? 'selected' : '' ?>>
                                <?= htmlspecialchars($supplier['supplier_name']) ?>
                            </option>
                        <?php endwhile; ?>
                    </select>
                </div>

                <!-- Status Filter -->
                <div class="col-md-3">
                    <label class="form-label">Status</label>
                    <select name="status" class="form-select">
                        <option value="">All Statuses</option>
                        <option value="Set" <?= ($_GET['status'] ?? '') === 'Set' ? 'selected' : '' ?>>Set</option>
                        <option value="Variable" <?= ($_GET['status'] ?? '') === 'Variable' ? 'selected' : '' ?>>Variable
                        </option>
                    </select>
                </div>

                <!-- Apply and Reset Buttons -->
                <div class="col-12 text-end">
                    <button type="submit" class="btn btn-primary">Apply Filters</button>
                    <a href="expenses.php" class="btn btn-secondary">Reset</a>
                </div>
            </div>
        </form>

        <!-- Table -->
        <table class="table table-bordered table-sm">
            <thead>
                <tr>
                    <th>ID</th>
                    <th>Account Number</th>
                    <th>Supplier</th>
                    <th>Amount</th>
                    <th>VAT %</th>
                    <th>Total</th>
                    <th>Client</th>
                    <th>Actions</th>
                </tr>
            </thead>

            <?php
            $where = [];
            if (!empty($_GET['client_id'])) {
                $where[] = "e.client_id = " . (int) $_GET['client_id'];
            }
            if (!empty($_GET['supplier_id'])) {
                $where[] = "e.supplier_id = " . (int) $_GET['supplier_id'];
            }
            if (!empty($_GET['status'])) {
                $where[] = "e.is_variable = " . ($_GET['status'] === 'Variable' ? 1 : 0);
            }

            $filterSql = !empty($where) ? 'WHERE ' . implode(' AND ', $where) : '';

            $res = $conn->query("
            SELECT e.*, s.supplier_name, c.client_name 
            FROM expenses e 
            LEFT JOIN billing_suppliers s ON e.supplier_id = s.id 
            LEFT JOIN clients c ON e.client_id = c.id
            $filterSql
            ORDER BY e.id DESC
        "); ?>
            <tbody>
                <?php while ($row = $res->fetch_assoc()): ?>
                    <tr>
                        <td class="text-center"><?= $row['id'] ?></td>
                        <td><?= htmlspecialchars($row['st_account_number']) ?></td>
                        <td><?= htmlspecialchars($row['supplier_name']) ?></td>
                        <td class="text-end"><?= number_format($row['amount_ex_vat'], 2) ?></td>
                        <td class="text-end"><?= number_format($row['vat_percent'], 2) ?>%</td>
                        <td class="text-end"><?= number_format($row['total'], 2) ?></td>
                        <td><?= htmlspecialchars($row['client_name']) ?></td>
                        <td class="text-center">
                            <div class="btn-group" role="group">
                                <?php if (hasPermission('expenses', 'edit')): ?>

                                    <button class="btn btn-sm btn-primary" onclick='loadEdit(<?= json_encode($row) ?>)'>
                                        Edit
                                    </button>
                                <?php endif; ?>

                                <?php if (hasPermission('expenses', 'delete')): ?>
                                    <button class="btn btn-sm btn-danger" onclick='loadDelete(<?= $row["id"] ?>)'>
                                        Delete
                                    </button>
                                <?php endif; ?>

                                <button class="btn btn-sm btn-info" data-bs-toggle="modal"
                                    data-bs-target="#viewExpenseModal" data-id="<?= $row['id'] ?>"
                                    data-account-number="<?= htmlspecialchars($row['st_account_number']) ?>"
                                    data-supplier="<?= htmlspecialchars($row['supplier_name']) ?>"
                                    data-client="<?= htmlspecialchars($row['client_name']) ?>"
                                    data-payment-method="<?= htmlspecialchars($row['payment_method']) ?>"
                                    data-frequency="<?= htmlspecialchars($row['payment_frequency']) ?>"
                                    data-amount="<?= $row['amount_ex_vat'] ?>" data-vat="<?= $row['vat_percent'] ?>"
                                    data-total="<?= $row['total'] ?>"
                                    data-variable="<?= $row['is_variable'] ? 'Variable' : 'Set' ?>"
                                    data-entity="<?= htmlspecialchars($row['entity']) ?>"
                                    data-bank="<?= htmlspecialchars($row['bank_name']) ?>"
                                    data-account-type="<?= htmlspecialchars($row['account_type']) ?>"
                                    data-account-number-detail="<?= htmlspecialchars($row['account_number']) ?>"
                                    data-notes="<?= htmlspecialchars($row['notes']) ?>">
                                    View
                                </button>
                            </div>
                        </td>
                    </tr>
                <?php endwhile; ?>
            </tbody>

        </table>
    </div>

    <!-- Add Modal -->
    <div class="modal fade" id="addModal" tabindex="-1">
        <div class="modal-dialog modal-lg">
            <form method="POST" class="modal-content">
                <div class="modal-header">
                    <h5>Add Expense</h5>
                </div>
                <div class="modal-body">
                    <div class="row g-3">
                        <!-- Supplier & ST Account Number -->
                        <div class="col-md-6">
                            <label>Supplier</label>
                            <select name="supplier" class="form-control" required>
                                <option value="" disabled selected>Select Supplier</option>
                                <?php $suppliers->data_seek(0);
                                while ($supplier = $suppliers->fetch_assoc()): ?>
                                    <option value="<?= $supplier['id'] ?>"><?= $supplier['supplier_name'] ?></option>
                                <?php endwhile; ?>
                            </select>
                        </div>
                        <div class="col-md-6">
                            <label>Invoicing Company</label>
                            <select name="invoicing_company_id" class="form-control" required>
                                <option value="" disabled selected>Select Invoicing Company</option>
                                <?php $companies->data_seek(0);
                                while ($company = $companies->fetch_assoc()): ?>
                                    <option value="<?= $company['id'] ?>"><?= htmlspecialchars($company['company_name']) ?>
                                    </option>
                                <?php endwhile; ?>
                            </select>
                        </div>
                        <div class="col-md-6">
                            <label>Supplier Contact Number</label>
                            <input type="text" name="supplier_contact_number" class="form-control" maxlength="50">
                        </div>
                        <div class="col-md-6 form-group">
                            <label>Client</label>
                            <select name="client_id" class="form-select">
                                <option value="">All Clients</option>
                                <?php
                                $clients->data_seek(0); // Reset pointer for reuse
                                while ($client = $clients->fetch_assoc()): ?>
                                    <option value="<?= $client['id'] ?>" <?= isset($_GET['client_id']) && $_GET['client_id'] == $client['id'] ? 'selected' : '' ?>>
                                        <?= htmlspecialchars($client['client_name']) ?>
                                    </option>
                                <?php endwhile; ?>
                            </select>
                        </div>
                        <div class="col-md-6">
                            <label>ST Account Number</label>
                            <input type="text" name="account_number" class="form-control" required>
                        </div>

                        <!-- Payment Method & Payment Date -->

                        <div class="col-md-6">
                            <label>Amount Ex VAT</label>
                            <input type="number" name="amount" step="0.01" class="form-control" required>
                        </div>

                        <!-- VAT % & Total -->
                        <div class="col-md-6">
                            <label>VAT %</label>
                            <input type="number" name="vat" step="0.01" max="100" class="form-control" required>
                        </div>
                        <div class="col-md-6">
                            <label>Total</label>
                            <input type="number" name="total" step="0.01" class="form-control" readonly>
                        </div>

                        <!-- Is Variable & Set Variable Text -->
                        <div class="col-md-6">
                            <label>Is Variable</label>
                            <select name="set_variable" class="form-control">
                                <option value="0">Set</option>
                                <option value="1">Variable</option>
                            </select>
                        </div>
                        <div class="col-md-6">
                            <label>Set Variable Text</label>
                            <input type="text" name="set_variable_text" class="form-control" maxlength="50">
                        </div>

                        <!-- Entity & Invoicing Company -->
                        <div class="col-md-6">
                            <label>Entity</label>
                            <input type="text" name="entity" class="form-control" maxlength="100">
                        </div>
                        <div class="col-md-12">
                            <label>Notes</label>
                            <textarea name="notes" class="form-control" rows="2"></textarea>
                        </div>
                        <div class="col-md-4">
                            <label>Payment Method</label>
                            <select name="payment_method" class="form-control" required>
                                <option value="">Select Payment Method</option>
                                <option value="Eft">Eft</option>
                                <option value="Debit Order">Debit Order</option>
                                <option value="Online Payment">Online Payment</option>
                                <option value="Internet Banking Payment">Internet Banking Payment</option>
                            </select>
                        </div>
                        <div class="col-md-4">
                            <label>Payment Date</label>
                            <input type="date" name="payment_date" class="form-control" required>
                        </div>

                        <!-- Payment Frequency & Amount Ex VAT -->
                        <div class="col-md-4">
                            <label>Payment Frequency</label>
                            <input type="text" name="payment_frequency" class="form-control" required>
                        </div>
                        <!-- Bank Name & Account Type -->
                        <div class="col-md-6">
                            <label>Bank Name</label>
                            <input type="text" name="bank" class="form-control" maxlength="100">
                        </div>
                        <div class="col-md-6">
                            <label>Account Type</label>
                            <input type="text" name="account_type" class="form-control" maxlength="100">
                        </div>

                        <!-- Account Number & Supplier Contact -->
                        <div class="col-md-6">
                            <label>Account Number</label>
                            <input type="text" name="acc_number" class="form-control" maxlength="100">
                        </div>


                        <!-- Account Contact & Notes -->
                        <div class="col-md-6">
                            <label>Account Contact</label>
                            <input type="text" name="account_contact" class="form-control" maxlength="50">
                        </div>

                    </div>
                </div>

                <div class="modal-footer">
                    <button class="btn btn-secondary" data-bs-dismiss="modal">Cancel</button>
                    <button type="submit" name="add_expense" class="btn btn-success">Add</button>
                </div>
            </form>
        </div>
    </div>


    <!-- Edit Modal -->
    <div class="modal fade" id="editModal" tabindex="-1">
        <div class="modal-dialog modal-lg">
            <form method="POST" class="modal-content">
                <div class="modal-header">
                    <h5>Edit Expense</h5>
                </div>
                <div class="modal-body">
                    <div class="row g-3">
                        <input type="hidden" name="id" id="edit_id">

                        <!-- Supplier & Invoicing Company -->
                        <div class="col-md-6">
                            <label>Supplier</label>
                            <select name="supplier" id="edit_supplier" class="form-control" required>
                                <option value="" disabled>Select Supplier</option>
                                <?php $suppliers->data_seek(0);
                                while ($supplier = $suppliers->fetch_assoc()): ?>
                                    <option value="<?= $supplier['id'] ?>"><?= $supplier['supplier_name'] ?></option>
                                <?php endwhile; ?>
                            </select>
                        </div>
                        <div class="col-md-6">
                            <label>Invoicing Company</label>
                            <select name="invoicing_company_id" id="edit_invoicing_company_id" class="form-control"
                                required>
                                <option value="" disabled selected>Select Invoicing Company</option>
                                <?php $companies->data_seek(0);
                                while ($company = $companies->fetch_assoc()): ?>
                                    <option value="<?= $company['id'] ?>"><?= htmlspecialchars($company['company_name']) ?>
                                    </option>
                                <?php endwhile; ?>
                            </select>
                        </div>

                        <!-- Supplier Contact & ST Account -->
                        <div class="col-md-6">
                            <label>Supplier Contact Number</label>
                            <input type="text" name="supplier_contact_number" id="edit_supplier_contact_number"
                                class="form-control" maxlength="50">
                        </div>
                        <div class="col-md-6 form-group">
                            <label>Client</label>
                            <select name="client_id" id="edit_client" class="form-select">
                                <option value="">All Clients</option>
                                <?php
                                $clients->data_seek(0); // Reset pointer for reuse
                                while ($client = $clients->fetch_assoc()): ?>
                                    <option value="<?= $client['id'] ?>" <?= isset($_GET['client_id']) && $_GET['client_id'] == $client['id'] ? 'selected' : '' ?>>
                                        <?= htmlspecialchars($client['client_name']) ?>
                                    </option>
                                <?php endwhile; ?>
                            </select>
                        </div>
                        <div class="col-md-6">
                            <label>ST Account Number</label>
                            <input type="text" name="account_number" id="edit_account_number" class="form-control"
                                required>
                        </div>

                        <!-- Amount & VAT -->
                        <div class="col-md-6">
                            <label>Amount Ex VAT</label>
                            <input type="number" name="amount" id="edit_amount" step="0.01" class="form-control"
                                required>
                        </div>
                        <div class="col-md-6">
                            <label>VAT %</label>
                            <input type="number" name="vat" id="edit_vat" step="0.01" max="100" class="form-control"
                                required>
                        </div>

                        <!-- Total & Is Variable -->
                        <div class="col-md-6">
                            <label>Total</label>
                            <input type="number" name="total" id="edit_total" step="0.01" class="form-control" readonly>
                        </div>
                        <div class="col-md-6">
                            <label>Is Variable</label>
                            <select name="set_variable" id="edit_set_variable" class="form-control">
                                <option value="0">Set</option>
                                <option value="1">Variable</option>
                            </select>
                        </div>

                        <!-- Set Variable Text & Entity -->
                        <div class="col-md-6">
                            <label>Set Variable Text</label>
                            <input type="text" name="set_variable_text" id="edit_set_variable_text" class="form-control"
                                maxlength="50">
                        </div>
                        <div class="col-md-6">
                            <label>Entity</label>
                            <input type="text" name="entity" id="edit_entity" class="form-control" maxlength="100">
                        </div>
                        <div class="col-md-12">
                            <label>Notes</label>
                            <textarea name="notes" id="edit_notes" class="form-control" rows="2"></textarea>
                        </div>
                        <!-- Payment Method, Date, Frequency -->
                        <div class="col-md-4">
                            <label>Payment Method</label>
                            <select name="payment_method" id="edit_payment_method" class="form-control" required>
                                <option value="">Select Payment Method</option>
                                <option value="Eft">Eft</option>
                                <option value="Debit Order">Debit Order</option>
                                <option value="Online Payment">Online Payment</option>
                                <option value="Internet Banking Payment">Internet Banking Payment</option>
                            </select>
                        </div>
                        <div class="col-md-4">
                            <label>Payment Date</label>
                            <input type="date" name="payment_date" id="edit_payment_date" class="form-control" required>
                        </div>
                        <div class="col-md-4">
                            <label>Payment Frequency</label>
                            <input type="text" name="payment_frequency" id="edit_payment_frequency" class="form-control"
                                required>
                        </div>

                        <!-- Bank Name & Account Type -->
                        <div class="col-md-6">
                            <label>Bank Name</label>
                            <input type="text" name="bank" id="edit_bank" class="form-control" maxlength="100">
                        </div>
                        <div class="col-md-6">
                            <label>Account Type</label>
                            <input type="text" name="account_type" id="edit_account_type" class="form-control"
                                maxlength="100">
                        </div>

                        <!-- Account Number & Contact -->
                        <div class="col-md-6">
                            <label>Account Number</label>
                            <input type="text" name="acc_number" id="edit_acc_number" class="form-control"
                                maxlength="100">
                        </div>
                        <div class="col-md-6">
                            <label>Account Contact</label>
                            <input type="text" name="account_contact" id="edit_account_contact" class="form-control"
                                maxlength="50">
                        </div>

                        <!-- Notes -->

                    </div>
                </div>
                <div class="modal-footer">
                    <button class="btn btn-secondary" data-bs-dismiss="modal">Cancel</button>
                    <button type="submit" name="edit_expense" class="btn btn-primary">Update</button>
                </div>
            </form>
        </div>
    </div>


    <!-- Delete Modal -->
    <div class="modal fade" id="deleteModal" tabindex="-1">
        <div class="modal-dialog">
            <form method="POST" class="modal-content">
                <div class="modal-header">
                    <h5>Delete Confirmation</h5>
                </div>
                <div class="modal-body">
                    Are you sure you want to delete this expense?
                    <input type="hidden" name="delete_id" id="delete_id">
                </div>
                <div class="modal-footer">
                    <button class="btn btn-secondary" data-bs-dismiss="modal">No</button>
                    <button type="submit" name="delete_expense" class="btn btn-danger">Yes, Delete</button>
                </div>
            </form>
        </div>
    </div>

    <div class="modal fade" id="viewExpenseModal" tabindex="-1" aria-labelledby="viewExpenseModalLabel"
        aria-hidden="true">
        <div class="modal-dialog modal-lg">
            <div class="modal-content">
                <div class="modal-header">
                    <h5 class="modal-title" id="viewExpenseModalLabel">Expense Details</h5>
                    <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
                </div>
                <div class="modal-body">
                    <p><strong>ID:</strong> <span id="view-id"></span></p>
                    <p><strong>Account Number:</strong> <span id="view-account-number"></span></p>
                    <p><strong>Supplier:</strong> <span id="view-supplier"></span></p>
                    <p><strong>Client:</strong> <span id="view-client"></span></p>
                    <p><strong>Payment Method:</strong> <span id="view-payment-method"></span></p>
                    <p><strong>Payment Frequency:</strong> <span id="view-frequency"></span></p>
                    <p><strong>Amount Ex VAT:</strong> <span id="view-amount"></span></p>
                    <p><strong>VAT %:</strong> <span id="view-vat"></span></p>
                    <p><strong>Total:</strong> <span id="view-total"></span></p>
                    <p><strong>Set/Variable:</strong> <span id="view-variable"></span></p>
                    <p><strong>Entity:</strong> <span id="view-entity"></span></p>
                    <p><strong>Bank Name:</strong> <span id="view-bank"></span></p>
                    <p><strong>Account Type:</strong> <span id="view-account-type"></span></p>
                    <p><strong>Account Number:</strong> <span id="view-account-number-detail"></span></p>
                    <p><strong>Notes:</strong> <span id="view-notes"></span></p>
                </div>
                <div class="modal-footer">
                    <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Close</button>
                </div>
            </div>
        </div>
    </div>

    <script>
        function loadEdit(data) {
            for (const key in data) {
                const el = document.getElementById('edit_' + key);
                if (el) el.value = data[key];
            }

            // Manually handle mismatched field names if necessary
            document.getElementById('edit_bank').value = data.bank_name || '';
            document.getElementById('edit_acc_number').value = data.account_number || '';

            // Fix for Amount Ex VAT and VAT %
            document.getElementById('edit_amount').value = data.amount_ex_vat || '';
            document.getElementById('edit_vat').value = data.vat_percent || '';
            document.getElementById('edit_client').value = data.client_id || '';

            // Also update the total field
            document.getElementById('edit_total').value = data.total || '';

            new bootstrap.Modal(document.getElementById('editModal')).show();
        }

        function loadDelete(id) {
            $('#delete_id').val(id);
            new bootstrap.Modal(document.getElementById('deleteModal')).show();
        }
        function calculateTotal(modalId) {
            const amountExVat = parseFloat(document.querySelector(`#${modalId} [name="amount"]`).value) || 0;
            const vatPercent = parseFloat(document.querySelector(`#${modalId} [name="vat"]`).value) || 0;

            const total = amountExVat + (amountExVat * vatPercent / 100);
            document.querySelector(`#${modalId} [name="total"]`).value = total.toFixed(2);
        }

        // Add event listeners for Add Modal
        document.querySelector('#addModal [name="amount"]').addEventListener('input', () => calculateTotal('addModal'));
        document.querySelector('#addModal [name="vat"]').addEventListener('input', () => calculateTotal('addModal'));

        // Add event listeners for Edit Modal
        document.querySelector('#editModal [name="amount"]').addEventListener('input', () => calculateTotal('editModal'));
        document.querySelector('#editModal [name="vat"]').addEventListener('input', () => calculateTotal('editModal'));

        document.querySelectorAll('[data-bs-target="#viewExpenseModal"]').forEach(button => {
            button.addEventListener('click', () => {
                document.getElementById('view-id').textContent = button.getAttribute('data-id');
                document.getElementById('view-account-number').textContent = button.getAttribute('data-account-number');
                document.getElementById('view-supplier').textContent = button.getAttribute('data-supplier');
                document.getElementById('view-client').textContent = button.getAttribute('data-client');
                document.getElementById('view-payment-method').textContent = button.getAttribute('data-payment-method');
                // document.getElementById('view-terms').textContent = button.getAttribute('data-terms');
                document.getElementById('view-frequency').textContent = button.getAttribute('data-frequency');
                document.getElementById('view-amount').textContent = button.getAttribute('data-amount');
                document.getElementById('view-vat').textContent = button.getAttribute('data-vat');
                document.getElementById('view-total').textContent = button.getAttribute('data-total');
                document.getElementById('view-variable').textContent = button.getAttribute('data-variable');
                document.getElementById('view-entity').textContent = button.getAttribute('data-entity');
                document.getElementById('view-bank').textContent = button.getAttribute('data-bank');
                document.getElementById('view-account-type').textContent = button.getAttribute('data-account-type');
                document.getElementById('view-account-number-detail').textContent = button.getAttribute('data-account-number-detail');
                document.getElementById('view-notes').textContent = button.getAttribute('data-notes');
            });
        });
    </script>
</body>

</html>