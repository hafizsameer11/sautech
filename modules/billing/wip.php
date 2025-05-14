<?php
// Database connection
$db_host = "localhost";
$db_user = "clientzone_user";
$db_pass = "S@utech2024!";
$db_name = "clientzone";

$conn = new mysqli($db_host, $db_user, $db_pass, $db_name);
if ($conn->connect_error)
  die("Connection failed: " . $conn->connect_error);

// Handle Add WIP
if (isset($_POST['add_wip'])) {
  $client = $_POST['client'];
  $quote = $_POST['quote'];
  $sales = $_POST['sales'];
  $desc = $_POST['description'];
  $terms = $_POST['terms'];
  $price = $_POST['price'];
  $status = $_POST['status'];
  // echo "<pre>";
//   print_r($_POST);
//   echo "</pre>";
//   exit;
  $stmt = $conn->prepare("INSERT INTO wip (client_id, quote_id, sales_person, description, terms, monthly_price_incl_vat, status) VALUES (?, ?, ?, ?, ?, ?, ?)");
  $stmt->bind_param("iisssds", $client, $quote, $sales, $desc, $terms, $price, $status);
  if ($stmt->execute()) {
    $alert = "success";
    $message = "WIP entry added successfully!";
  } else {
    $alert = "danger";
    $message = "Error adding WIP entry: " . $stmt->error;
  }
  $stmt->close();
}

// Handle Edit WIP
if (isset($_POST['edit_wip'])) {
  $id = $_POST['id'];
  $client = $_POST['client'];
  $quote = $_POST['quote'];
  $sales = $_POST['sales'];
  $desc = $_POST['description'];
  $terms = $_POST['terms'];
  // print_r($_POST);
  $price = $_POST['price'];
  $status = $_POST['status'];
  // echo "<pre>";
  // print_r($_POST);
  // echo "</pre>";
  // exit;

  $stmt = $conn->prepare("UPDATE wip SET client_id=?, quote_id=?, sales_person=?, description=?, terms=?, monthly_price_incl_vat=?, status=? WHERE id=?");
  $stmt->bind_param("iisssdsi", $client, $quote, $sales, $desc, $terms, $price, $status, $id);
  if ($stmt->execute()) {
    $alert = "success";
    $message = "WIP entry updated successfully!";
  } else {
    $alert = "danger";
    $message = "Error updating WIP entry: " . $stmt->error;
  }
  $stmt->close();
}

// Handle Delete WIP
if (isset($_POST['delete_wip'])) {
  $id = $_POST['delete_id'];
  if ($conn->query("DELETE FROM wip WHERE id = $id")) {
    $alert = "success";
    $message = "WIP entry deleted successfully!";
  } else {
    $alert = "danger";
    $message = "Error deleting WIP entry: " . $conn->error;
  }
}

// Fetch Clients and Quotes
$clients = $conn->query("SELECT id, client_name FROM clients");
$quotes = $conn->query("SELECT id, quote_number FROM quotes");
?>

<!DOCTYPE html>
<html>

<head>
  <title>WIP Module</title>
  <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
  <script src="https://code.jquery.com/jquery-3.7.1.min.js"></script>
  <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script>
</head>

<body class="p-4">

  <div class="container">
    <div class="d-flex align-items-center justify-content-between mb-3">
      <div class="d-flex align-items-center">
        <?php include('../components/Backbtn.php') ?>
        <?php include('../components/permissioncheck.php') ?>
        <h2 class="">Work In Progress</h2>
      </div>
      <?php if (hasPermission('wip', 'create')): ?>
        <!-- Add Button -->
        <button class="btn btn-success mb-3" data-bs-toggle="modal" data-bs-target="#addModal">+ Add WIP</button>
      <?php endif; ?>
    </div>
    <!-- Alerts -->
    <?php if (isset($alert) && isset($message)): ?>
      <div class="alert alert-<?= $alert ?> alert-dismissible fade show" role="alert">
        <?= $message ?>
        <button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Close"></button>
      </div>
    <?php endif; ?>


    <!-- Filter Form -->
    <form class="card shadow-sm p-4 my-4" method="GET">
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

        <!-- Quote Filter -->
        <div class="col-md-3">
          <label class="form-label">Quote</label>
          <select name="quote_id" class="form-select">
            <option value="">All Quotes</option>
            <?php
            $quotes->data_seek(0); // Reset pointer for reuse
            while ($quote = $quotes->fetch_assoc()): ?>
              <option value="<?= $quote['id'] ?>" <?= isset($_GET['quote_id']) && $_GET['quote_id'] == $quote['id'] ? 'selected' : '' ?>>
                <?= htmlspecialchars($quote['quote_number']) ?>
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
            <option value="Followed up" <?= ($_GET['status'] ?? '') === 'Followed up' ? 'selected' : '' ?>>Followed up
            </option>
            <option value="Declined" <?= ($_GET['status'] ?? '') === 'Declined' ? 'selected' : '' ?>>Declined</option>
            <option value="Approved" <?= ($_GET['status'] ?? '') === 'Approved' ? 'selected' : '' ?>>Approved</option>
          </select>
        </div>

        <!-- Apply and Reset Buttons -->
        <div class="col-12 text-end">
          <button type="submit" class="btn btn-primary">Apply Filters</button>
          <a href="wip.php" class="btn btn-secondary">Reset</a>
        </div>
      </div>
    </form>

    <!-- Table -->
    <table class="table table-bordered">
      <thead>
        <tr>
          <th>ID</th>
          <th>Client</th>
          <th>Quote #</th>
          <th>Sales Person</th>
          <th>Description</th>
          <th>Price</th>
          <th>Terms</th>
          <th>Status</th>
          <th>Actions</th>
        </tr>
      </thead>
      <?php
      $where = [];
      if (!empty($_GET['client_id'])) {
        $where[] = "w.client_id = " . (int) $_GET['client_id'];
      }
      if (!empty($_GET['quote_id'])) {
        $where[] = "w.quote_id = " . (int) $_GET['quote_id'];
      }
      if (!empty($_GET['status'])) {
        $where[] = "w.status = '" . $conn->real_escape_string($_GET['status']) . "'";
      }

      $filterSql = !empty($where) ? 'WHERE ' . implode(' AND ', $where) : '';

      $res = $conn->query("
            SELECT w.*, c.client_name, q.quote_number 
            FROM wip w
            LEFT JOIN clients c ON w.client_id = c.id
            LEFT JOIN quotes q ON w.quote_id = q.id
            $filterSql
            ORDER BY w.id DESC
        ");
      ?>
      <tbody>
        <?php
        $i = 1;
        while ($row = $res->fetch_assoc()):
          ?>
          <tr>
            <td class="text-center"><?= $i++ ?></td>
            <td><?= htmlspecialchars($row['client_name']) ?></td>
            <td><?= htmlspecialchars($row['quote_number']) ?></td>
            <td><?= htmlspecialchars($row['sales_person']) ?></td>
            <td><?= htmlspecialchars($row['description']) ?></td>
            <td class="text-end"><?= number_format($row['monthly_price_incl_vat'], 2) ?></td>
            <td class="text-center"><?= htmlspecialchars($row['terms']) ?></td>
            <td class="text-center"><?= htmlspecialchars($row['status']) ?></td>
            <td class="text-center">
              <div class="btn-group" role="group" aria-label="Actions">
                <button class="btn btn-sm text-info" data-bs-toggle="modal" data-bs-target="#viewWipModal"
                  data-id="<?= $row['id'] ?>" data-client="<?= htmlspecialchars($row['client_name']) ?>"
                  data-quote="<?= htmlspecialchars($row['quote_number']) ?>"
                  data-sales="<?= htmlspecialchars($row['sales_person']) ?>"
                  data-description="<?= htmlspecialchars($row['description']) ?>"
                  data-price="<?= $row['monthly_price_incl_vat'] ?>"
                  data-status="<?= htmlspecialchars($row['status']) ?>">
                  <i class="fas fa-eye"></i>
                </button>

                <?php if (hasPermission('wip', 'update')): ?>
                  <button class="btn btn-sm" onclick='loadEdit(<?= json_encode($row) ?>)' title="Edit">
                    <i class="fas fa-edit"></i>
                  </button>
                <?php endif; ?>

                <?php if (hasPermission('wip', 'delete')): ?>
                  <button class="btn btn-sm text-danger" onclick='loadDelete(<?= $row['id'] ?>)' title="Delete">
                    <i class="fas fa-trash-alt"></i>
                  </button>
                <?php endif; ?>
              </div>
            </td>
          </tr>
        <?php endwhile; ?>
      </tbody>

    </table>
  </div>

  <!-- Add Modal -->
  <div class="modal fade" id="addModal" tabindex="-1">
    <div class="modal-dialog">
      <form method="POST" class="modal-content">
        <div class="modal-header">
          <h5>Add WIP</h5>
        </div>
        <div class="modal-body">
          <select name="client" class="form-control mb-2" required>
            <option value="" disabled selected>Select Client</option>
            <?php
            $clients->data_seek(0);
            while ($client = $clients->fetch_assoc()): ?>
              <option value="<?= $client['id'] ?>"><?= $client['client_name'] ?></option>
            <?php endwhile; ?>
          </select>
          <select name="quote" class="form-control mb-2">
            <option value="" disabled selected>Select Quote</option>
            <?php
            $quotes->data_seek(0);
            while ($quote = $quotes->fetch_assoc()): ?>
              <option value="<?= $quote['id'] ?>"><?= $quote['quote_number'] ?></option>
            <?php endwhile; ?>
          </select>
          <input type="text" name="sales" class="form-control mb-2" placeholder="Sales Person" required>
          <textarea name="description" class="form-control mb-2" placeholder="Description"></textarea>
          <select name="terms" class="form-control mb-2" required>
            <!-- <option value="" disabled selected>Terms</option> -->
            <option value="once_off">Once off</option>
            <option value="monthly">Monthly</option>
          </select>

          <input type="number" name="price" step="0.01" class="form-control mb-2" placeholder="Price incl VAT">
          <select name="status" class="form-control mb-2" required>
            <!-- <option disabled selected>status</option> -->
            <option>Quoted</option>
            <option>Followed up</option>
            <option>Declined</option>
            <option>Approved</option>
          </select>
        </div>
        <div class="modal-footer">
          <button class="btn btn-secondary" data-bs-dismiss="modal">Cancel</button>
          <button type="submit" name="add_wip" class="btn btn-success">Add</button>
        </div>
      </form>
    </div>
  </div>

  <!-- Edit Modal -->
  <div class="modal fade" id="editModal" tabindex="-1">
    <div class="modal-dialog">
      <form method="POST" class="modal-content">
        <div class="modal-header">
          <h5>Edit WIP</h5>
        </div>
        <div class="modal-body">
          <input type="hidden" name="id" id="edit_id">
          <select name="client" id="edit_client" class="form-control mb-2" required>
            <option value="" disabled>Select Client</option>
            <?php
            $clients->data_seek(0); // Reset pointer for reuse
            while ($client = $clients->fetch_assoc()): ?>
              <option value="<?= $client['id'] ?>"><?= $client['client_name'] ?></option>
            <?php endwhile; ?>
          </select>
          <select name="quote" id="edit_quote" class="form-control mb-2">
            <option value="" disabled>Select Quote</option>
            <?php
            $quotes->data_seek(0); // Reset pointer for reuse
            while ($quote = $quotes->fetch_assoc()): ?>
              <option value="<?= $quote['id'] ?>"><?= $quote['quote_number'] ?></option>
            <?php endwhile; ?>
          </select>
          <input type="text" name="sales" id="edit_sales" class="form-control mb-2" required>
          <textarea name="description" id="edit_description" class="form-control mb-2"></textarea>
          <select name="terms" id="edit_terms" class="form-control mb-2" required>
            <option value="once_off">Once off</option>
            <option value="monthly">Monthly</option>
          </select>

          <input type="number" name="price" id="edit_price" step="0.01" class="form-control mb-2">
          <select name="status" id="edit_status" class="form-control mb-2">
            <option>Quoted</option>
            <option>Followed up</option>
            <option>Declined</option>
            <option>Approved</option>
          </select>
        </div>
        <div class="modal-footer">
          <button class="btn btn-secondary" data-bs-dismiss="modal">Cancel</button>
          <button type="submit" name="edit_wip" class="btn btn-primary">Update</button>
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
          Are you sure you want to delete this WIP entry?
          <input type="hidden" name="delete_id" id="delete_id">
        </div>
        <div class="modal-footer">
          <button class="btn btn-secondary" data-bs-dismiss="modal">No</button>
          <button type="submit" name="delete_wip" class="btn btn-danger">Yes, Delete</button>
        </div>
      </form>
    </div>
  </div>

  <div class="modal fade" id="viewWipModal" tabindex="-1" aria-labelledby="viewWipModalLabel" aria-hidden="true">
    <div class="modal-dialog modal-lg">
      <div class="modal-content">
        <div class="modal-header">
          <h5 class="modal-title" id="viewWipModalLabel">WIP Details</h5>
          <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
        </div>
        <div class="modal-body">
          <p><strong>ID:</strong> <span id="view-id"></span></p>
          <p><strong>Client:</strong> <span id="view-client"></span></p>
          <p><strong>Quote:</strong> <span id="view-quote"></span></p>
          <p><strong>Sales Person:</strong> <span id="view-sales"></span></p>
          <p><strong>Description:</strong> <span id="view-description"></span></p>
          <p><strong>Terms:</strong> <span id="view-terms"></span></p>
          <p><strong>Price incl VAT:</strong> <span id="view-price"></span></p>
          <p><strong>Status:</strong> <span id="view-status"></span></p>
        </div>
        <div class="modal-footer">
          <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Close</button>
        </div>
      </div>
    </div>
  </div>

  <script>
    function loadEdit(data) {
      $('#edit_id').val(data.id);
      $('#edit_client').val(data.client_id);
      $('#edit_quote').val(data.quote_id);
      $('#edit_sales').val(data.sales_person);
      $('#edit_description').val(data.description);
      $('#edit_price').val(data.monthly_price_incl_vat);
      $('#edit_status').val(data.status);
      $('#edit_terms').val(data.terms);
      new bootstrap.Modal(document.getElementById('editModal')).show();
    }

    function loadDelete(id) {
      $('#delete_id').val(id);
      new bootstrap.Modal(document.getElementById('deleteModal')).show();
    }

    document.querySelectorAll('[data-bs-target="#viewWipModal"]').forEach(button => {
      button.addEventListener('click', () => {
        document.getElementById('view-terms').textContent = button.getAttribute('data-terms');
        document.getElementById('view-id').textContent = button.getAttribute('data-id');
        document.getElementById('view-client').textContent = button.getAttribute('data-client');
        document.getElementById('view-quote').textContent = button.getAttribute('data-quote');
        document.getElementById('view-sales').textContent = button.getAttribute('data-sales');
        document.getElementById('view-description').textContent = button.getAttribute('data-description');
        document.getElementById('view-price').textContent = button.getAttribute('data-price');
        document.getElementById('view-status').textContent = button.getAttribute('data-status');
      });
    });
  </script>

</body>

</html>