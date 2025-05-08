<?php
// DB connection
$db_host = "localhost";
    $db_user = "root";
    $db_pass = "";
    $db_name = "clientzone";

$conn = new mysqli($db_host, $db_user, $db_pass, $db_name);
if ($conn->connect_error) die("Connection failed: " . $conn->connect_error);

// Add Expense
if (isset($_POST['add_expense'])) {
    $supplier = $_POST['supplier'];
    $account_number = $_POST['account_number'];
    $method = $_POST['payment_method'];
    $terms = $_POST['terms'];
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

    $stmt = $conn->prepare("INSERT INTO expenses (supplier_id, st_account_number, payment_method, terms, payment_frequency, amount_ex_vat, vat_percent, total, is_variable, client_id, entity, bank_name, account_type, account_number, notes) VALUES (?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?)");
    $stmt->bind_param("issssddddisssss", $supplier, $account_number, $method, $terms, $frequency, $amount, $vat, $total, $setvar, $client, $entity, $bank, $type, $number, $notes);
    $stmt->execute();
    header("Location: expenses.php");
}

// Edit Expense
if (isset($_POST['edit_expense'])) {
    $id = intval($_POST['id']); // Ensure ID is an integer
    $supplier = $_POST['supplier'];
    $account_number = $_POST['account_number'];
    $method = $_POST['payment_method'];
    $terms = $_POST['terms'];
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

    $stmt = $conn->prepare("UPDATE expenses SET supplier_id=?, st_account_number=?, payment_method=?, terms=?, payment_frequency=?, amount_ex_vat=?, vat_percent=?, total=?, is_variable=?, client_id=?, entity=?, bank_name=?, account_type=?, account_number=?, notes=? WHERE id=?");
    $stmt->bind_param("issssddddisssssi", $supplier, $account_number, $method, $terms, $frequency, $amount, $vat, $total, $setvar, $client, $entity, $bank, $type, $number, $notes, $id);



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
            <h2>Expenses</h2>
        </div>
        <button class="btn btn-success" data-bs-toggle="modal" data-bs-target="#addModal">+ Add Expense</button>
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
                    <option value="Variable" <?= ($_GET['status'] ?? '') === 'Variable' ? 'selected' : '' ?>>Variable</option>
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
        <tbody>
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
        ");
        while ($row = $res->fetch_assoc()) {
            echo "<tr>
                <td>{$row['id']}</td>
                <td>{$row['st_account_number']}</td>
                <td>{$row['supplier_name']}</td>
                <td>{$row['amount_ex_vat']}</td>
                <td>{$row['vat_percent']}</td>
                <td>{$row['total']}</td>
                <td>{$row['client_name']}</td>
                <td>
                    <button class='btn btn-primary btn-sm' onclick='loadEdit(".json_encode($row).")'>Edit</button>
                    <button class='btn btn-danger btn-sm' onclick='loadDelete({$row['id']})'>Delete</button>
                    <button class='btn btn-info btn-sm' data-bs-toggle='modal' data-bs-target='#viewExpenseModal'
                        data-id='{$row['id']}'
                        data-account-number='".htmlspecialchars($row['st_account_number'])."'
                        data-supplier='".htmlspecialchars($row['supplier_name'])."'
                        data-client='".htmlspecialchars($row['client_name'])."'
                        data-payment-method='".htmlspecialchars($row['payment_method'])."'
                        data-terms='".htmlspecialchars($row['terms'])."'
                        data-frequency='".htmlspecialchars($row['payment_frequency'])."'
                        data-amount='{$row['amount_ex_vat']}'
                        data-vat='{$row['vat_percent']}'
                        data-total='{$row['total']}'
                        data-variable='".($row['is_variable'] ? 'Variable' : 'Set')."'
                        data-entity='".htmlspecialchars($row['entity'])."'
                        data-bank='".htmlspecialchars($row['bank_name'])."'
                        data-account-type='".htmlspecialchars($row['account_type'])."'
                        data-account-number-detail='".htmlspecialchars($row['account_number'])."'
                        data-notes='".htmlspecialchars($row['notes'])."'>
                        View
                    </button>
                </td>
            </tr>";
        }
        ?>
        </tbody>
    </table>
</div>

<!-- Add Modal -->
<div class="modal fade" id="addModal" tabindex="-1">
  <div class="modal-dialog modal-lg">
    <form method="POST" class="modal-content">
      <div class="modal-header"><h5>Add Expense</h5></div>
      <div class="modal-body row g-2">
        <!-- Supplier Dropdown -->
        <select name="supplier" class="form-control mb-2" required>
            <option value="" disabled selected>Select Supplier</option>
            <?php 
            $suppliers->data_seek(0);
            while ($supplier = $suppliers->fetch_assoc()): ?>
                <option value="<?= $supplier['id'] ?>"><?= $supplier['supplier_name'] ?></option>
            <?php endwhile; ?>
        </select>

        <!-- Client Dropdown -->
        <select name="client_id" class="form-control mb-2" required>
            <option value="" disabled selected>Select Client</option>
            <?php 
            $clients->data_seek(0);
            while ($client = $clients->fetch_assoc()): ?>
                <option value="<?= $client['id'] ?>"><?= $client['client_name'] ?></option>
            <?php endwhile; ?>
        </select>

        <!-- ST Account Number -->
        <input type="text" name="account_number" class="form-control mb-2" value="<?= $newAccountNumber ?>" readonly>

        <!-- Payment Method -->
        <input type="text" name="payment_method" class="form-control mb-2" placeholder="Payment Method" maxlength="100">

        <!-- Terms -->
        <input type="text" name="terms" class="form-control mb-2" placeholder="Terms" maxlength="100">

        <!-- Payment Frequency -->
        <input type="text" name="payment_frequency" class="form-control mb-2" placeholder="Payment Frequency" maxlength="100">

        <!-- Amount Ex VAT -->
        <input type="number" name="amount" step="0.01" class="form-control mb-2" placeholder="Amount Ex VAT">

        <!-- VAT Percentage -->
        <input type="number" name="vat" step="0.01" class="form-control mb-2" placeholder="VAT %" max="100">

        <!-- Total -->
        <input type="number" name="total" step="0.01" class="form-control mb-2" placeholder="Total" readonly>

        <!-- Is Variable -->
        <select name="set_variable" class="form-control mb-2">
            <option value="0">Set</option>
            <option value="1">Variable</option>
        </select>

        <!-- Entity -->
        <input type="text" name="entity" class="form-control mb-2" placeholder="Entity" maxlength="100">

        <!-- Bank Name -->
        <input type="text" name="bank" class="form-control mb-2" placeholder="Bank Name" maxlength="100">

        <!-- Account Type -->
        <input type="text" name="account_type" class="form-control mb-2" placeholder="Account Type" maxlength="100">

        <!-- Account Number -->
        <input type="text" name="acc_number" class="form-control mb-2" placeholder="Account Number" maxlength="100">

        <!-- Notes -->
        <textarea name="notes" class="form-control mb-2" placeholder="Notes"></textarea>
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
      <div class="modal-header"><h5>Edit Expense</h5></div>
      <div class="modal-body row g-2">
        <input type="hidden" name="id" id="edit_id">

        <!-- Supplier Dropdown -->
        <select name="supplier" id="edit_supplier" class="form-control mb-2" required>
            <option value="" disabled>Select Supplier</option>
            <?php
            $suppliers->data_seek(0); // Reset pointer for reuse
            while ($supplier = $suppliers->fetch_assoc()): ?>
                <option value="<?= $supplier['id'] ?>"><?= $supplier['supplier_name'] ?></option>
            <?php endwhile; ?>
        </select>

        <!-- Client Dropdown -->
        <select name="client_id" id="edit_client_id" class="form-control mb-2" required>
            <option value="" disabled>Select Client</option>
            <?php
            $clients->data_seek(0); // Reset pointer for reuse
            while ($client = $clients->fetch_assoc()): ?>
                <option value="<?= $client['id'] ?>"><?= $client['client_name'] ?></option>
            <?php endwhile; ?>
        </select>

        <!-- Account Number -->
        <input type="text" name="account_number" id="edit_account_number" class="form-control mb-2" placeholder="Account Number" maxlength="50" required>

        <!-- Payment Method -->
        <input type="text" name="payment_method" id="edit_payment_method" class="form-control mb-2" placeholder="Payment Method" maxlength="100">

        <!-- Terms -->
        <input type="text" name="terms" id="edit_terms" class="form-control mb-2" placeholder="Terms" maxlength="100">

        <!-- Payment Frequency -->
        <input type="text" name="payment_frequency" id="edit_payment_frequency" class="form-control mb-2" placeholder="Payment Frequency" maxlength="100">

        <!-- Amount Ex VAT -->
        <input type="number" name="amount" id="edit_amount" step="0.01" class="form-control mb-2" placeholder="Amount Ex VAT">

        <!-- VAT Percentage -->
        <input type="number" name="vat" id="edit_vat" step="0.01" class="form-control mb-2" placeholder="VAT %" max="100">

        <!-- Total -->
        <input type="number" name="total" id="edit_total" step="0.01" class="form-control mb-2" placeholder="Total" readonly>

        <!-- Is Variable -->
        <select name="set_variable" id="edit_set_variable" class="form-control mb-2">
            <option value="0">Set</option>
            <option value="1">Variable</option>
        </select>

        <!-- Entity -->
        <input type="text" name="entity" id="edit_entity" class="form-control mb-2" placeholder="Entity" maxlength="100">

        <!-- Bank Name -->
        <input type="text" name="bank" id="edit_bank" class="form-control mb-2" placeholder="Bank Name" maxlength="100">

        <!-- Account Type -->
        <input type="text" name="account_type" id="edit_account_type" class="form-control mb-2" placeholder="Account Type" maxlength="100">

        <!-- Account Number -->
        <input type="text" name="acc_number" id="edit_acc_number" class="form-control mb-2" placeholder="Account Number" maxlength="100">

        <!-- Notes -->
        <textarea name="notes" id="edit_notes" class="form-control mb-2" placeholder="Notes"></textarea>
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
      <div class="modal-header"><h5>Delete Confirmation</h5></div>
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

<div class="modal fade" id="viewExpenseModal" tabindex="-1" aria-labelledby="viewExpenseModalLabel" aria-hidden="true">
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
                <p><strong>Terms:</strong> <span id="view-terms"></span></p>
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
        document.getElementById('view-terms').textContent = button.getAttribute('data-terms');
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