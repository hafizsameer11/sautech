<?php
// session_start();
$localhost = ($_SERVER['SERVER_NAME'] == 'localhost');

if ($localhost) {
    // Local development settings
    $db_host = "localhost";
    $db_user = "root";
    $db_pass = "";
    $db_name = "clientzone";
} else {
    // Live server settings
    $db_host = "localhost";
    $db_user = "clientzone_user";
    $db_pass = "S@utech2024!";
    $db_name = "clientzone";
}

$conn = new mysqli($db_host, $db_user, $db_pass, $db_name);
if ($conn->connect_error) {
    die("Connection failed: " . $conn->connect_error);
}
// making query to get all clients and companys
$clients = $conn->query("SELECT id, client_name FROM clients");
$companies = $conn->query("SELECT id, company_name FROM billing_invoice_companies");
?>
<!DOCTYPE html>
<html lang="en">

<head>
    <title>Quotes Module</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script>
</head>

<body class="p-4">

    <div class="container pt-4">
        <?php if (isset($_SESSION['success'])): ?>
            <div class="alert alert-success">
                <?= $_SESSION['success']; ?>
            </div>
            <?php unset($_SESSION['success']); ?>
        <?php endif; ?>

        <?php if (isset($_SESSION['error'])): ?>
            <div class="alert alert-danger">
                <?= $_SESSION['error']; ?>
            </div>
            <?php unset($_SESSION['error']); ?>
        <?php endif; ?>
        <div class="d-flex justify-content-between align-items-center mb-4">
            <div class="d-flex align-items-center">
                <?php include('../components/Backbtn.php') ?>
                <h2 class="">Quotes Module</h2>
            </div>

            <!-- Add Quote Button -->
            <button class="btn btn-primary" data-bs-toggle="modal" data-bs-target="#addQuoteModal">Add Quote</button>
        </div>

        <!-- Filter Form -->
        <form class="card shadow-sm mb-4 p-4"  method="GET">
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

                <!-- Company Filter -->
                <div class="col-md-3">
                    <label class="form-label">Company</label>
                    <select name="company_id" class="form-select">
                        <option value="">All Companies</option>
                        <?php
                        $companies->data_seek(0); // Reset pointer for reuse
                        while ($company = $companies->fetch_assoc()): ?>
                            <option value="<?= $company['id'] ?>" <?= isset($_GET['company_id']) && $_GET['company_id'] == $company['id'] ? 'selected' : '' ?>>
                                <?= htmlspecialchars($company['company_name']) ?>
                            </option>
                        <?php endwhile; ?>
                    </select>
                </div>

                <!-- Status Filter -->
                <div class="col-md-3">
                    <label class="form-label">Status</label>
                    <select name="status" class="form-select">
                        <option value="">All Statuses</option>
                        <option value="Quoted" <?= ($_GET['status'] ?? '') === 'Quoted' ? 'selected' : '' ?>>Quoted</option>
                        <option value="Followed up" <?= ($_GET['status'] ?? '') === 'Followed up' ? 'selected' : '' ?>>Followed up</option>
                        <option value="Declined" <?= ($_GET['status'] ?? '') === 'Declined' ? 'selected' : '' ?>>Declined</option>
                        <option value="Approved" <?= ($_GET['status'] ?? '') === 'Approved' ? 'selected' : '' ?>>Approved</option>
                    </select>
                </div>

                <!-- Apply and Reset Buttons -->
                <div class="col-12 text-end">
                    <button type="submit" class="btn btn-primary">Apply Filters</button>
                    <a href="quotes.php" class="btn btn-secondary">Reset</a>
                </div>
            </div>
        </form>

        <!-- Quotes Table -->
        <h4>All Quotes</h4>
        <table class="table table-bordered table-hover">
            <thead>
                <tr>
                    <th>#</th>
                    <th>Quote No</th>
                    <th>Client</th>
                    <th>Company</th>
                    <th>Qty</th>
                    <th>Price</th>
                    <th>VAT</th>
                    <th>Total</th>
                    <th>Status</th>
                    <th>Actions</th>
                </tr>
            </thead>
            <tbody>
                <?php
                $where = [];
                if (!empty($_GET['client_id'])) {
                    $where[] = "q.client_id = " . (int) $_GET['client_id'];
                }
                if (!empty($_GET['company_id'])) {
                    $where[] = "q.quoted_company_id = " . (int) $_GET['company_id'];
                }
                if (!empty($_GET['status'])) {
                    $where[] = "q.status = '" . $conn->real_escape_string($_GET['status']) . "'";
                }

                $filterSql = !empty($where) ? 'WHERE ' . implode(' AND ', $where) : '';

                $quotes = $conn->query("
                    SELECT q.*, c.client_name, b.company_name 
                    FROM quotes q
                    JOIN clients c ON q.client_id = c.id
                    JOIN billing_invoice_companies b ON q.quoted_company_id = b.id
                    $filterSql
                    ORDER BY q.id DESC
                ");
                while ($row = $quotes->fetch_assoc()) {
                    echo "<tr>
          <td>{$row['id']}</td>
          <td>{$row['quote_number']}</td>
          <td>{$row['client_name']}</td>
          <td>{$row['company_name']}</td>
          <td>{$row['qty']}</td>
          <td>{$row['price_ex_vat']}</td>
          <td>{$row['vat']}</td>
          <td>{$row['total_incl_vat']}</td>
          <td>{$row['status']}</td>
          <td>
            <button class='btn btn-warning btn-sm' data-bs-toggle='modal' data-bs-target='#editQuoteModal' 
              data-id='{$row['id']}' data-quote-number='{$row['quote_number']}' data-client-id='{$row['client_id']}'
              data-company-id='{$row['quoted_company_id']}' data-description='{$row['description']}'
              data-qty='{$row['qty']}' data-unit-price='{$row['unit_price']}' data-vat='{$row['vat']}'
              data-status='{$row['status']}'>Edit</button>
            <button class='btn btn-danger btn-sm' data-bs-toggle='modal' data-bs-target='#deleteQuoteModal' 
              data-id='{$row['id']}'>Delete</button>
            <button class='btn btn-info btn-sm' data-bs-toggle='modal' data-bs-target='#viewQuoteModal' 
              data-id='{$row['id']}' data-quote-number='{$row['quote_number']}' 
              data-client-name='" . htmlspecialchars($row['client_name']) . "' 
              data-company-name='" . htmlspecialchars($row['company_name']) . "' 
              data-description='" . htmlspecialchars($row['description']) . "' 
              data-qty='{$row['qty']}' data-unit-price='{$row['unit_price']}' 
              data-vat='{$row['vat']}' data-total='{$row['total_incl_vat']}' 
              data-status='{$row['status']}'>View</button>
          </td>
        </tr>";
                }
                ?>
            </tbody>
        </table>
    </div>

    <!-- Add Quote Modal -->
    <div class="modal fade" id="addQuoteModal" tabindex="-1" aria-labelledby="addQuoteModalLabel" aria-hidden="true">
        <div class="modal-dialog">
            <form method="POST" action="quotes_actions.php">
                <div class="modal-content">
                    <div class="modal-header">
                        <h5 class="modal-title" id="addQuoteModalLabel">Add Quote</h5>
                        <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
                    </div>
                    <div class="modal-body">
                        <?php
                        // Auto-generate quote number
                        $latest = $conn->query("SELECT quote_number FROM quotes ORDER BY id DESC LIMIT 1");
                        $newQuoteNumber = "STQ-110000";
                        if ($latest->num_rows > 0) {
                            $last = $latest->fetch_assoc()['quote_number'];
                            $num = intval(str_replace("STQ-", "", $last)) + 1;
                            $newQuoteNumber = "STQ-" . $num;
                        }
                        ?>
                        <input type="hidden" name="action" value="add">
                        <div class="mb-3">
                            <label>Quote Number</label>
                            <input type="text" name="quote_number" class="form-control" value="<?= $newQuoteNumber ?>"
                                readonly>
                        </div>
                        <div class="mb-3">
                            <label>Client</label>
                            <select name="client_id" id="add-client-id" class="form-select" required>
                                <option value="" disabled selected>Select a client</option>
                                <?php
                                $clients->data_seek(0);
                                while ($client = $clients->fetch_assoc()): ?>
                                    <option value="<?= $client['id'] ?>"><?= $client['client_name'] ?></option>
                                <?php endwhile; ?>
                            </select>
                        </div>
                        <div class="mb-3">
                            <label>Company</label>
                            <select name="quoted_company_id" id="add-company-id" class="form-select" required>
                                <option value="" disabled selected>Select a company</option>
                                <?php
                                $companies->data_seek(0);
                                while ($company = $companies->fetch_assoc()): ?>
                                    <option value="<?= $company['id'] ?>"><?= $company['company_name'] ?></option>
                                <?php endwhile; ?>
                            </select>
                        </div>
                        <div class="mb-3">
                            <label>Description</label>
                            <textarea name="description" class="form-control" required></textarea>
                        </div>
                        <div class="mb-3">
                            <label>Quantity</label>
                            <input type="number" name="qty" class="form-control" value="1">
                        </div>
                        <div class="mb-3">
                            <label>Unit Price</label>
                            <input type="number" step="0.01" name="unit_price" class="form-control" required>
                        </div>
                        <div class="mb-3">
                            <label>VAT</label>
                            <input type="number" step="0.01" name="vat" class="form-control" required>
                        </div>
                        <div class="mb-3">
                            <label>Status</label>
                            <select name="status" class="form-select">
                                <option>Quoted</option>
                                <option>Followed up</option>
                                <option>Declined</option>
                                <option>Approved</option>
                            </select>
                        </div>
                    </div>
                    <div class="modal-footer">
                        <button type="submit" class="btn btn-primary">Add Quote</button>
                    </div>
                </div>
            </form>
        </div>
    </div>

    <!-- Edit Quote Modal -->
    <div class="modal fade" id="editQuoteModal" tabindex="-1" aria-labelledby="editQuoteModalLabel" aria-hidden="true">
        <div class="modal-dialog">
            <form method="POST" action="quotes_actions.php">
                <div class="modal-content">
                    <div class="modal-header">
                        <h5 class="modal-title" id="editQuoteModalLabel">Edit Quote</h5>
                        <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
                    </div>
                    <div class="modal-body">
                        <input type="hidden" name="action" value="edit">
                        <input type="hidden" name="id" id="edit-id">
                        <div class="mb-3">
                            <label>Quote Number</label>
                            <input type="text" name="quote_number" id="edit-quote-number" class="form-control" readonly>
                        </div>
                        <div class="mb-3">
                            <label>Client</label>
                            <select name="client_id" id="edit-client-id" class="form-select" required>
                                <option value="" disabled selected>Select a client</option>
                                <?php
                                $clients->data_seek(0);
                                while ($client = $clients->fetch_assoc()): ?>
                                    <option value="<?= $client['id'] ?>"><?= $client['client_name'] ?></option>
                                <?php endwhile; ?>
                            </select>
                        </div>
                        <div class="mb-3">
                            <label>Company</label>
                            <select name="quoted_company_id" id="edit-company-id" class="form-select" required>
                                <option value="" disabled selected>Select a company</option>
                                <?php
                                $companies->data_seek(0); // Reset the pointer to the beginning of the result set
                                while ($company = $companies->fetch_assoc()): ?>
                                    <option value="<?= $company['id'] ?>"><?= $company['company_name'] ?></option>
                                <?php endwhile; ?>
                            </select>
                        </div>
                        <div class="mb-3">
                            <label>Description</label>
                            <textarea name="description" id="edit-description" class="form-control" required></textarea>
                        </div>
                        <div class="mb-3">
                            <label>Quantity</label>
                            <input type="number" name="qty" id="edit-qty" class="form-control">
                        </div>
                        <div class="mb-3">
                            <label>Unit Price</label>
                            <input type="number" step="0.01" name="unit_price" id="edit-unit-price" class="form-control"
                                required>
                        </div>
                        <div class="mb-3">
                            <label>VAT</label>
                            <input type="number" step="0.01" name="vat" id="edit-vat" class="form-control" required>
                        </div>
                        <div class="mb-3">
                            <label>Status</label>
                            <select name="status" id="edit-status" class="form-select">
                                <option>Quoted</option>
                                <option>Followed up</option>
                                <option>Declined</option>
                                <option>Approved</option>
                            </select>
                        </div>
                    </div>
                    <div class="modal-footer">
                        <button type="submit" class="btn btn-warning">Update Quote</button>
                    </div>
                </div>
            </form>
        </div>
    </div>

    <!-- Delete Quote Modal -->
    <div class="modal fade" id="deleteQuoteModal" tabindex="-1" aria-labelledby="deleteQuoteModalLabel"
        aria-hidden="true">
        <div class="modal-dialog">
            <form method="POST" action="quotes_actions.php">
                <div class="modal-content">
                    <div class="modal-header">
                        <h5 class="modal-title" id="deleteQuoteModalLabel">Delete Quote</h5>
                        <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
                    </div>
                    <div class="modal-body">
                        <input type="hidden" name="action" value="delete">
                        <input type="hidden" name="id" id="delete-id">
                        <p>Are you sure you want to delete this quote?</p>
                    </div>
                    <div class="modal-footer">
                        <button type="submit" class="btn btn-danger">Delete</button>
                    </div>
                </div>
            </form>
        </div>
    </div>

    <div class="modal fade" id="viewQuoteModal" tabindex="-1" aria-labelledby="viewQuoteModalLabel" aria-hidden="true">
        <div class="modal-dialog">
            <div class="modal-content">
                <div class="modal-header">
                    <h5 class="modal-title" id="viewQuoteModalLabel">Quote Details</h5>
                    <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
                </div>
                <div class="modal-body">
                    <p><strong>Quote Number:</strong> <span id="view-quote-number"></span></p>
                    <p><strong>Client:</strong> <span id="view-client-name"></span></p>
                    <p><strong>Company:</strong> <span id="view-company-name"></span></p>
                    <p><strong>Description:</strong> <span id="view-description"></span></p>
                    <p><strong>Quantity:</strong> <span id="view-qty"></span></p>
                    <p><strong>Unit Price:</strong> <span id="view-unit-price"></span></p>
                    <p><strong>VAT:</strong> <span id="view-vat"></span></p>
                    <p><strong>Total:</strong> <span id="view-total"></span></p>
                    <p><strong>Status:</strong> <span id="view-status"></span></p>
                </div>
                <div class="modal-footer">
                    <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Close</button>
                </div>
            </div>
        </div>
    </div>

    <script>
        // Populate Edit Modal
        document.querySelectorAll('[data-bs-target="#editQuoteModal"]').forEach(button => {
            button.addEventListener('click', () => {
                // Set values for the Edit Modal fields
                document.getElementById('edit-id').value = button.getAttribute('data-id');
                document.getElementById('edit-quote-number').value = button.getAttribute('data-quote-number');
                document.getElementById('edit-description').value = button.getAttribute('data-description');
                document.getElementById('edit-qty').value = button.getAttribute('data-qty');
                document.getElementById('edit-unit-price').value = button.getAttribute('data-unit-price');
                document.getElementById('edit-vat').value = button.getAttribute('data-vat');
                document.getElementById('edit-status').value = button.getAttribute('data-status');

                // Set the selected value for the Client dropdown
                const clientId = button.getAttribute('data-client-id');
                const clientDropdown = document.getElementById('edit-client-id');
                Array.from(clientDropdown.options).forEach(option => {
                    option.selected = option.value === clientId;
                });

                // Set the selected value for the Company dropdown
                const companyId = button.getAttribute('data-company-id');
                const companyDropdown = document.getElementById('edit-company-id');
                Array.from(companyDropdown.options).forEach(option => {
                    option.selected = option.value === companyId;
                });
            });
        });

        // Populate Delete Modal
        document.querySelectorAll('[data-bs-target="#deleteQuoteModal"]').forEach(button => {
            button.addEventListener('click', () => {
            const deleteId = button.getAttribute('data-id');
            document.getElementById('delete-id').value = deleteId;
            });
        });

        // Populate View Modal
        document.querySelectorAll('[data-bs-target="#viewQuoteModal"]').forEach(button => {
            button.addEventListener('click', () => {
                document.getElementById('view-quote-number').textContent = button.getAttribute('data-quote-number');
                document.getElementById('view-client-name').textContent = button.getAttribute('data-client-name');
                document.getElementById('view-company-name').textContent = button.getAttribute('data-company-name');
                document.getElementById('view-description').textContent = button.getAttribute('data-description');
                document.getElementById('view-qty').textContent = button.getAttribute('data-qty');
                document.getElementById('view-unit-price').textContent = button.getAttribute('data-unit-price');
                document.getElementById('view-vat').textContent = button.getAttribute('data-vat');
                document.getElementById('view-total').textContent = button.getAttribute('data-total');
                document.getElementById('view-status').textContent = button.getAttribute('data-status');
            });
        });

        // Ensure the backdrop is removed when the modal is closed
        document.querySelectorAll('.modal').forEach(modal => {
            modal.addEventListener('hidden.bs.modal', () => {
                const backdrop = document.querySelector('.modal-backdrop');
                if (backdrop) {
                    backdrop.remove();
                }
            });
        });
    </script>

</body>

</html>