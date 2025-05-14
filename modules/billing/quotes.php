<?php
// session_start();
$db_host = "localhost";
$db_user = "clientzone_user";
$db_pass = "S@utech2024!";
$db_name = "clientzone";

$conn = new mysqli($db_host, $db_user, $db_pass, $db_name);
if ($conn->connect_error) {
    die("Connection failed: " . $conn->connect_error);
}
// making query to get all clients and companys
$clients = $conn->query("SELECT id, client_name FROM clients");
$companies = $conn->query("SELECT id, company_name FROM billing_invoice_companies");
$serviceTypes = $conn->query("SELECT * FROM billing_service_types");
$serviceCategories = $conn->query("SELECT * FROM billing_service_categories");
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
        <form class="card shadow-sm mb-4 p-4" method="GET">
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
                        <option value="Quoted" <?= ($_GET['status'] ?? '') === 'Quoted' ? 'selected' : '' ?>>Quoted
                        </option>
                        <option value="Followed up" <?= ($_GET['status'] ?? '') === 'Followed up' ? 'selected' : '' ?>>
                            Followed up</option>
                        <option value="Declined" <?= ($_GET['status'] ?? '') === 'Declined' ? 'selected' : '' ?>>Declined
                        </option>
                        <option value="Approved" <?= ($_GET['status'] ?? '') === 'Approved' ? 'selected' : '' ?>>Approved
                        </option>
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
                    SELECT 
                        q.*, 
                        c.client_name, 
                        b.company_name,
                        st.service_type_name AS service_type_name,
                        sc.category_name AS service_category_name
                    FROM quotes q
                    JOIN clients c ON q.client_id = c.id
                    JOIN billing_invoice_companies b ON q.quoted_company_id = b.id
                    LEFT JOIN billing_service_types st ON q.service_type_id = st.id
                    LEFT JOIN billing_service_categories sc ON q.service_category_id = sc.id
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
<button class='btn btn-success btn-sm' data-bs-toggle='modal' data-bs-target='#sendQuoteModal'
    data-id=" . $row['id'] . "
    data-client-name=" . htmlspecialchars($row['client_name']) . "
    data-company-name=" . htmlspecialchars($row['company_name']) . "
>Send Quote</button>
            <button class='btn btn-warning btn-sm' data-bs-toggle='modal' data-bs-target='#editQuoteModal' 
                data-id='{$row['id']}'
                data-quote-number='{$row['quote_number']}'
                data-client-id='{$row['client_id']}'
                data-company-id='{$row['quoted_company_id']}'
                data-description='" . htmlspecialchars($row['description']) . "'
                data-qty='{$row['qty']}'
                data-unit-price='{$row['unit_price']}'
                data-total='{$row['total_incl_vat']}'
                data-vat='{$row['vat']}'
                data-status='{$row['status']}'
                data-reference='{$row['reference']}'
                data-sales-person='{$row['sales_person']}'
                data-quote-date='{$row['quote_date']}'
                data-due-date='{$row['due_date']}'
                data-service-type-id='{$row['service_type_id']}'
                data-service-category-id='{$row['service_category_id']}'
                >Edit</button>

            <button class='btn btn-danger btn-sm' data-bs-toggle='modal' data-bs-target='#deleteQuoteModal' 
              data-id='{$row['id']}'>Delete</button>
            <button class='btn btn-info btn-sm' data-bs-toggle='modal' data-bs-target='#viewQuoteModal' 
              data-id='{$row['id']}' data-quote-number='{$row['quote_number']}' 
              data-client-name='" . htmlspecialchars($row['client_name']) . "' 
              data-company-name='" . htmlspecialchars($row['company_name']) . "' 
              data-description='" . htmlspecialchars($row['description']) . "' 
              data-qty='{$row['qty']}' data-unit-price='{$row['unit_price']}' 
              data-vat='{$row['vat']}' data-total='{$row['total_incl_vat']}'
                data-sales-person=" . $row['sales_person'] . "
                data-reference=" . $row['reference'] . "
                data-quote-date=" . $row['quote_date'] . "
                data-due-date=" . $row['due_date'] . "
                data-service-type=" . $row['service_type_name'] . "
                data-service-category=" . $row['service_category_name'] . "

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
        <div class="modal-dialog modal-lg"> <!-- wider modal -->
            <form method="POST" action="quotes_actions.php">
                <div class="modal-content">
                    <div class="modal-header">
                        <h5 class="modal-title" id="addQuoteModalLabel">Add Quote</h5>
                        <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
                    </div>
                    <div class="modal-body">
                        <?php
                        $latest = $conn->query("SELECT quote_number FROM quotes ORDER BY id DESC LIMIT 1");
                        $newQuoteNumber = "STQ-110000";
                        if ($latest->num_rows > 0) {
                            $last = $latest->fetch_assoc()['quote_number'];
                            $num = intval(str_replace("STQ-", "", $last)) + 1;
                            $newQuoteNumber = "STQ-" . $num;
                        }
                        ?>
                        <input type="hidden" name="action" value="add">

                        <div class="row g-3">
                            <div class="col-md-6">
                                <label class="form-label">Quote Number</label>
                                <input type="text" name="quote_number" class="form-control"
                                    value="<?= $newQuoteNumber ?>" readonly>
                            </div>
                            <div class="col-md-6">
                                <label class="form-label">Reference</label>
                                <input type="text" name="reference" class="form-control"
                                    placeholder="Enter customer reference">
                            </div>

                            <div class="col-md-6">
                                <label class="form-label">Client</label>
                                <select name="client_id" class="form-select" required>
                                    <option value="" disabled selected>Select a client</option>
                                    <?php $clients->data_seek(0);
                                    while ($client = $clients->fetch_assoc()): ?>
                                        <option value="<?= $client['id'] ?>"><?= $client['client_name'] ?></option>
                                    <?php endwhile; ?>
                                </select>
                            </div>
                            <div class="col-md-6">
                                <label class="form-label">Company</label>
                                <select name="quoted_company_id" class="form-select" required>
                                    <option value="" disabled selected>Select a company</option>
                                    <?php $companies->data_seek(0);
                                    while ($company = $companies->fetch_assoc()): ?>
                                        <option value="<?= $company['id'] ?>"><?= $company['company_name'] ?></option>
                                    <?php endwhile; ?>
                                </select>
                            </div>

                            <div class="col-md-6">
                                <label class="form-label">Quote Date</label>
                                <input type="date" name="quote_date" class="form-control" required>
                            </div>
                            <div class="col-md-6">
                                <label class="form-label">Due Date</label>
                                <input type="date" name="due_date" class="form-control" required>
                            </div>

                            <div class="col-md-6">
                                <label class="form-label">Sales Person</label>
                                <input type="text" name="sales_person" class="form-control"
                                    placeholder="Sales person name">
                            </div>
                            <div class="col-md-6">
                                <label class="form-label">Status</label>
                                <select name="status" class="form-select">
                                    <option>Quoted</option>
                                    <option>Followed up</option>
                                    <option>Declined</option>
                                    <option>Approved</option>
                                </select>
                            </div>
                            <div class="col-12">
                                <label class="form-label">Description</label>
                                <textarea name="description" class="form-control" required></textarea>
                            </div>

                            <div class="col-md-6">
                                <label class="form-label">Service Type</label>
                                <!-- Service Type -->
                                <select name="service_type_id" id="add-service-type-id" class="form-select"
                                    onchange="fetchQuoteCategories(this.value, 'add')" required>
                                    <option value="">Select Service Type</option>
                                    <?php while ($row = $serviceTypes->fetch_assoc()): ?>
                                        <option value="<?= $row['id'] ?>"><?= $row['service_type_name'] ?></option>
                                    <?php endwhile; ?>
                                </select>
                            </div>
                            <div class="col-md-6">
                                <label class="form-label">Service Category</label>
                                <!-- Service Category -->
                                <select name="service_category_id" id="add-service-category-id" class="form-select"
                                    onchange="fetchQuoteUnitPrice(this.value, 'add')" required>

                                    <option value="">Select Service Category</option>
                                </select>
                            </div>

                            <div class="col-md-4">
                                <label class="form-label">Quantity</label>
                                <input type="number" onkeyup="updateQuoteTotalLive('add')" name="qty" id="add-qty"
                                    class="form-control" value="1">
                            </div>
                            <div class="col-md-4">
                                <label class="form-label">Unit Price</label>
                                <input type="number" step="0.01" onkeyup="handleUnit('ex_unit_price','add')"
                                    name="unit_price" id="add-unit-price" class="form-control" required>
                                <span class="valid-feedback" id="add-ex_unit_price" style="display: none;"></span>
                            </div>
                            <div class="col-md-4">
                                <label class="form-label">VAT</label>
                                <input type="number" step="0.01" onkeyup="handleUnit('ex_vat','add')" name="vat"
                                    id="add-vat" class="form-control" required>
                                <span class="valid-feedback" id="add-ex_vat" style="display: none;"></span>
                            </div>
                            <h4><strong>Total:-</strong><span id="add-quote_total"></span></h4>
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
        <div class="modal-dialog modal-lg">
            <form method="POST" action="quotes_actions.php">
                <div class="modal-content">
                    <div class="modal-header">
                        <h5 class="modal-title" id="editQuoteModalLabel">Edit Quote</h5>
                        <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
                    </div>
                    <div class="modal-body row">
                        <input type="hidden" name="action" value="edit">
                        <input type="hidden" name="id" id="edit-id">
                        <div class="mb-3 col-md-6">
                            <label>Quote Number</label>
                            <input type="text" name="quote_number" id="edit-quote-number" class="form-control" readonly>
                        </div>
                        <div class="mb-3 col-md-6">
                            <label>Reference</label>
                            <input type="text" name="reference" id="edit-reference" class="form-control"
                                placeholder="Enter customer reference">
                        </div>
                        <div class="mb-3 col-md-6">
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
                        <div class="mb-3 col-md-6">
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


                        <div class="mb-3 col-md-6">
                            <label>Quote Date</label>
                            <input type="date" name="quote_date" id="edit-quote-date" class="form-control" required>
                        </div>
                        <div class="mb-3 col-md-6">
                            <label>Due Date</label>
                            <input type="date" name="due_date" id="edit-due-date" class="form-control" required>
                        </div>
                        <div class="mb-3 col-md-6">
                            <label>Sales Person</label>
                            <input type="text" name="sales_person" id="edit-sales-person" class="form-control"
                                placeholder="Sales person name">
                        </div>
                        <div class="mb-3 col-md-6">
                            <label>Status</label>
                            <select name="status" id="edit-status" class="form-select">
                                <option>Quoted</option>
                                <option>Followed up</option>
                                <option>Declined</option>
                                <option>Approved</option>
                            </select>
                        </div>
                        <div class="mb-3 col-md-12">
                            <label>Description</label>
                            <textarea name="description" id="edit-description" class="form-control" required></textarea>
                        </div>
                        <div class="mb-3 col-md-6">
                            <label>Service Type</label>
                            <select name="service_type_id" id="edit-service-type-id" class="form-select"
                                onchange="fetchQuoteCategories(this.value, 'edit')" required>
                                <option value="">Select Service Type</option>
                                <?php
                                $serviceTypes->data_seek(0); // Reset the pointer to the beginning of the result set
                                while ($row = $serviceTypes->fetch_assoc()) {
                                    echo "<option value=\"{$row['id']}\">{$row['service_type_name']}</option>";
                                }
                                ?>
                            </select>
                        </div>
                        <div class="mb-3 col-md-6">
                            <label>Service Category</label>
                            <select name="service_category_id" id="edit-service-category-id" class="form-select"
                                onchange="fetchQuoteUnitPrice(this.value, 'edit')" required>
                                <option value="">Select Service Category</option>
                                <?php
                                $serviceCategories->data_seek(0); // Reset the pointer to the beginning of the result set
                                while ($row = $serviceCategories->fetch_assoc()) {
                                    echo "<option value=\"{$row['id']}\">{$row['category_name']}</option>";
                                }
                                ?>
                            </select>
                        </div>

                        <div class="mb-3 col-md-4">
                            <label>Quantity</label>
                            <input type="number" name="qty" onkeyup="updateQuoteTotalLive('edit')" id="edit-qty"
                                class="form-control">
                        </div>
                        <div class="mb-3 col-md-4">
                            <label>Unit Price</label>
                            <input type="number" step="0.01" name="unit_price"
                                onkeyup="handleUnit('ex_unit_price','edit')" id="edit-unit-price" class="form-control"
                                required>
                            <span class="valid-feedback" id="edit-ex_unit_price" style="display: none;"></span>
                        </div>
                        <div class="mb-3 col-md-4">
                            <label>VAT</label>
                            <input type="number" step="0.01" name="vat" onkeyup="handleUnit('ex_vat','edit')"
                                id="edit-vat" class="form-control" required>
                            <span class="valid-feedback" id="edit-ex_vat" style="display: none;"></span>
                        </div>
                        <h4 class="mb-3 col-md-12"><strong>Total:-</strong><span id="edit-quote_total"></span></h4>
                        
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
                    <div class="row g-3">
                        <div class="col-md-6"><strong>Quote Number:</strong> <span id="view-quote-number"></span></div>
                        <div class="col-md-6"><strong>Client:</strong> <span id="view-client-name"></span></div>
                        <div class="col-md-6"><strong>Company:</strong> <span id="view-company-name"></span></div>
                        <div class="col-md-6"><strong>Status:</strong> <span id="view-status"></span></div>
                        <div class="col-md-6"><strong>Sales Person:</strong> <span id="view-sales-person"></span></div>
                        <div class="col-md-6"><strong>Reference:</strong> <span id="view-reference"></span></div>
                        <div class="col-md-6"><strong>Quote Date:</strong> <span id="view-quote-date"></span></div>
                        <div class="col-md-6"><strong>Due Date:</strong> <span id="view-due-date"></span></div>
                        <div class="col-12"><strong>Description:</strong> <span id="view-description"></span></div>
                        <div class="col-md-4"><strong>Quantity:</strong> <span id="view-qty"></span></div>
                        <div class="col-md-4"><strong>Unit Price:</strong> <span id="view-unit-price"></span></div>
                        <div class="col-md-4"><strong>VAT:</strong> <span id="view-vat"></span></div>
                        <div class="col-md-4"><strong>Total:</strong> <span id="view-total"></span></div>
                        <div class="col-md-4"><strong>Service Type:</strong> <span id="view-service-type"></span></div>
                        <div class="col-md-6"><strong>Service Category:</strong> <span
                                id="view-service-category"></span></div>
                    </div>
                </div>

                <div class="modal-footer">
                    <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Close</button>
                </div>
            </div>
        </div>
    </div>

    <!-- send email -->
    <!-- Send Quote Modal -->
    <div class="modal fade" id="sendQuoteModal" tabindex="-1" aria-labelledby="sendQuoteModalLabel" aria-hidden="true">
        <div class="modal-dialog">
            <form method="POST" action="quotes_actions.php">
                <div class="modal-content">
                    <div class="modal-header">
                        <h5 class="modal-title">Send Quote</h5>
                        <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
                    </div>
                    <div class="modal-body">
                        <input type="hidden" name="action" value="send_quote">
                        <input type="hidden" name="quote_id" id="send-quote-id">
                        <div class="mb-3">
                            <label>Sender Name</label>
                            <input type="text" name="sender_name" class="form-control" required>
                        </div>
                        <div class="mb-3">
                            <label>Recipient Name</label>
                            <input type="text" name="recipient_name" class="form-control" required>
                        </div>
                        <div class="mb-3">
                            <label>Recipient Email</label>
                            <input type="email" name="recipient_email" id="send-recipient-email" class="form-control"
                                required>
                        </div>
                        <div class="mb-3">
                            <label>Message (optional)</label>
                            <textarea name="message" class="form-control"></textarea>
                        </div>
                    </div>
                    <div class="modal-footer">
                        <button type="submit" class="btn btn-success">Send Quote</button>
                    </div>
                </div>
            </form>
        </div>
    </div>
    <script src="https://cdn.jsdelivr.net/npm/axios/dist/axios.min.js"></script>
    <script>
        function updateQuoteTotalLive(mode) {
            const qty = parseFloat(document.getElementById(`${mode}-qty`).value) || 0;
            const unitPrice = parseFloat(document.getElementById(`${mode}-unit-price`).value) || 0;
            const vat = parseFloat(document.getElementById(`${mode}-vat`).value) || 0;

            // Calculate per unit total (including VAT)
            const perOne = unitPrice + (unitPrice * vat / 100);
            const total = qty * perOne;

            // Update UI
            document.getElementById(`${mode}-quote_total`).innerText = total.toFixed(2);
        }
        function handleUnit(spanId, mode) {
            const span = document.getElementById(mode + "-" + spanId);
            updateQuoteTotalLive(mode);
            if (span) {
                span.style.display = 'block'; // Make the span visible
            } else {
                console.log('error')
            }
        }

        document.querySelectorAll('[data-bs-target="#sendQuoteModal"]').forEach(button => {
            button.addEventListener('click', () => {
                document.getElementById('send-quote-id').value = button.getAttribute('data-id');
                document.getElementById('send-recipient-email').value = button.getAttribute('data-email') || '';
            });
        });
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
                document.getElementById('edit-reference').value = button.getAttribute('data-reference');
                document.getElementById('edit-sales-person').value = button.getAttribute('data-sales-person');
                document.getElementById('edit-quote-date').value = button.getAttribute('data-quote-date');
                document.getElementById('edit-due-date').value = button.getAttribute('data-due-date');
                document.getElementById('edit-service-type-id').value = button.getAttribute('data-service-type-id');
                document.getElementById('edit-service-category-id').value = button.getAttribute('data-service-category-id');
                let total = button.getAttribute('data-total');
                document.getElementById(`edit-ex_unit_price`).innerText = "Ex Unit price : " + button.getAttribute('data-unit-price');
                        document.getElementById(`edit-ex_vat`).innerText = "Ex Vat : " + button.getAttribute('data-vat');


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
                document.getElementById(`edit-quote_total`).innerText = total;



            });
        });

        // Populate Delete Modal
        document.querySelectorAll('[data-bs-target="#deleteQuoteModal"]').forEach(button => {
            button.addEventListener('click', () => {
                const deleteId = button.getAttribute('data-id');
                document.getElementById('delete-id').value = deleteId;
            });
        });
        function fetchQuoteCategories(typeId, mode) {
            if (!typeId) return;

            const formData = new FormData();
            formData.append('action', 'fetch_categories');
            formData.append('service_type_id', typeId);

            axios.post('quotes_actions.php', formData)
                .then(response => {
                    const categories = response.data;
                    const select = document.getElementById(`${mode}-service-category-id`);
                    select.innerHTML = '<option value="">Select Service Category</option>';

                    categories.forEach(category => {
                        const opt = document.createElement('option');
                        opt.value = category.id;
                        opt.textContent = category.category_name;
                        select.appendChild(opt);
                    });
                })
                .catch(err => console.error('Category Fetch Failed:', err));
        }

        function fetchQuoteUnitPrice(categoryId, mode) {
            if (!categoryId) return;

            const formData = new FormData();
            formData.append('action', 'fetch_unit_price');
            formData.append('service_category_id', categoryId);

            axios.post('quotes_actions.php', formData)
                .then(response => {
                    const data = response.data;
                    if (data.unit_price !== undefined && data.vat !== undefined) {
                        document.getElementById(`${mode}-unit-price`).value = data.unit_price;
                        document.getElementById(`${mode}-vat`).value = data.vat;

                        document.getElementById(`${mode}-ex_unit_price`).innerText = "Ex Unit price : " + data.unit_price;
                        document.getElementById(`${mode}-ex_vat`).innerText = "Ex Vat : " + data.vat;
                        let qtyValue = document.getElementById(`${mode}-qty`).value;

                        let unitprice = parseFloat(data.unit_price);
                        let vat = parseFloat(data.vat);
                        let perOne = unitprice + (unitprice * vat / 100); // Correct VAT calculation
                        let total = parseFloat(qtyValue) * perOne;
                        document.getElementById(`${mode}-quote_total`).innerText = total.toFixed(2); // Display total with 2 decimal places
                    }
                })
                .catch(err => console.error('Unit Price Fetch Failed:', err));
        }

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
                document.getElementById('view-sales-person').textContent = button.getAttribute('data-sales-person');
                document.getElementById('view-reference').textContent = button.getAttribute('data-reference');
                document.getElementById('view-quote-date').textContent = button.getAttribute('data-quote-date');
                document.getElementById('view-due-date').textContent = button.getAttribute('data-due-date');
                document.getElementById('view-service-type').textContent = button.getAttribute('data-service-type');
                document.getElementById('view-service-category').textContent = button.getAttribute('data-service-category');

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