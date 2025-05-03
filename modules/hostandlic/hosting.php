<?php
error_reporting(E_ALL);
ini_set('display_errors', 1);
$conn = new mysqli("localhost", "clientzone_user", "S@utech2024!", "clientzone");

if ($conn->connect_error) die("Connection failed: " . $conn->connect_error);

// Tables
$columns = ['client_name', 'client_id', 'location', 'asset_type', 'host', 'server_name', 'os', 'cpu', 'mem', 'ram', 'sata', 'ssd', 'private_ip', 'public_ip', 'ip_address', 'username', 'password', 'spla', 'login_url', 'note'];

// Insert Hosting Record
if ($_SERVER["REQUEST_METHOD"] === "POST" && isset($_POST["submit_hosting"])) {
  $fields = $columns;
  $data = [];
  foreach ($fields as $f) $data[$f] = $conn->real_escape_string($_POST[$f] ?? '');
  $stmt = $conn->prepare("INSERT INTO hosting_assets (" . implode(',', $fields) . ") VALUES (" . str_repeat('?,', count($fields) - 1) . "?)");
  $stmt->bind_param(str_repeat("s", count($fields)), ...array_values($data));
  $stmt->execute();
}

// Add/Delete Support Values
foreach (["location", "asset_type", "host", "os"] as $type) {
  if ($_SERVER["REQUEST_METHOD"] === "POST") {
    if (isset($_POST["add_$type"])) {
      $val = $conn->real_escape_string($_POST["new_$type"]);
      $conn->query("INSERT IGNORE INTO hosting_{$type}s (name) VALUES ('$val')");
    }
    if (isset($_POST["delete_$type"])) {
      $val = $conn->real_escape_string($_POST["remove_$type"]);
      $conn->query("DELETE FROM hosting_{$type}s WHERE name = '$val'");
    }
  }
}

// Delete Hosting Record
if ($_SERVER["REQUEST_METHOD"] === "POST" && isset($_POST["delete_hosting_record"])) {
  $id = intval($_POST["hosting_record_id"]);
  $conn->query("DELETE FROM hosting_assets WHERE id = $id");
}

// CSV Export
if (isset($_GET['export']) && $_GET['export'] === 'csv') {
  header('Content-Type: text/csv');
  header('Content-Disposition: attachment;filename=hosting_export.csv');
  $out = fopen("php://output", "w");
  fputcsv($out, $columns);
  $res = $conn->query("SELECT * FROM hosting_assets");
  while ($r = $res->fetch_assoc()) fputcsv($out, array_values($r));
  fclose($out);
  exit;
}

// Filters
$filters = [];
$where = [];
foreach ($columns as $col) {
  $res = $conn->query("SELECT DISTINCT $col FROM hosting_assets ORDER BY $col");
  $filters[$col] = [];
  while ($r = $res->fetch_assoc()) $filters[$col][] = $r[$col];
  if (!empty($_GET[$col])) $where[] = "$col = '" . $conn->real_escape_string($_GET[$col]) . "'";
}
$whereSQL = $where ? "WHERE " . implode(" AND ", $where) : "";
$data = $conn->query("SELECT * FROM hosting_assets $whereSQL");

// Support dropdowns

$support_tables = ['location' => 'hosting_locations', 'asset_type' => 'hosting_asset_types', 'host' => 'hosting_hosts', 'os' => 'hosting_oss'];
$support = [];
foreach ($support_tables as $key => $table) {
  $support[$key] = [];
  $q = $conn->query("SELECT name FROM $table ORDER BY name ASC");
  if ($q) {
    while ($r = $q->fetch_assoc()) {
      $support[$key][] = $r['name'];
    }
  } else {
    echo "<div style='color:red;'>⚠️ Query failed for table: $table – " . $conn->error . "</div>";
  }
}

$clients = [];
$cl = $conn->query("SELECT id, client_name FROM clients ORDER BY client_name ASC");
while ($r = $cl->fetch_assoc()) $clients[] = $r;

$records = $conn->query("SELECT id, client_name, server_name FROM hosting_assets ORDER BY id DESC");
?>

<!DOCTYPE html>
<html lang="en">

<head>
  <meta charset="UTF-8">
  <title>Hosting Manager</title>

  <!-- Bootstrap 5 -->
  <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.2/dist/css/bootstrap.min.css" rel="stylesheet">
  <!-- Custom Hosting CSS -->
  <link href="../../assets/css/hosting.css" rel="stylesheet">
  <link href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css" rel="stylesheet">
</head>

<body>

  <div class="my-5" style="width: 93%; margin: auto; height: 100vh; " >
    <h3 class="text-dark mb-4 d-flex justify-content-between align-items-center">
      <div class="d-flex align-items-center">
        <?php include('../components/Backbtn.php') ?>
        <span class="ml-2">Hosting</span>
      </div>
      <!-- <a href="login/register.php" class="btn btn-primary">Logins</a> -->
    </h3>

    <!-- Add Hosting Record -->
    <div class="card p-4 shadow-sm mb-5">
      <h4 class="text-success mb-4">Add Hosting Record</h4>
      <form method="POST" class="row g-4">
        <?php foreach ($columns as $col): ?>
          <div class="col-md-4">
            <?php if (in_array($col, ['host', 'os', 'location', 'asset_type'])): ?>
              <select name="<?= $col ?>" class="form-select">
                <option value="">Select <?= ucwords(str_replace('_', ' ', $col)) ?></option>
                <?php foreach ($support[$col] as $val): ?>
                  <option value="<?= $val ?>"><?= $val ?></option>
                <?php endforeach; ?>
              </select>
            <?php elseif ($col === "spla"): ?>
              <select name="<?= $col ?>" class="form-select">
                <option value="No">No Spla</option>
                <option value="Yes">Yes Spla</option>
              </select>
            <?php elseif ($col === "note"): ?>
              <textarea name="note" class="form-control" placeholder="Note"></textarea>
            <?php elseif ($col === "client_id"): ?>
              <!-- <label for="client_id" class="form-label">Client</label> -->
              <select name="client_id" class="form-select" required>
                <option value="">Select Client</option>
                <?php foreach ($clients as $client): ?>
                  <option value="<?= $client['id'] ?>"><?= htmlspecialchars($client['client_name']) ?> (ID: <?= $client['id'] ?>)</option>
                <?php endforeach; ?>
              </select>
            <?php else: ?>
              <input type="text" name="<?= $col ?>" class="form-control" placeholder="<?= ucwords(str_replace('_', ' ', $col)) ?>">
            <?php endif; ?>
          </div>
        <?php endforeach; ?>
        <div class="col-12 text-start">
          <button type="submit" name="submit_hosting" class="btn btn-success">
            Add Hosting Record
          </button>
        </div>
      </form>
    </div>

    <!-- Delete Hosting Record -->
    <div class="card p-4 shadow-sm mb-5">
      <h4 class="text-danger mb-4">Delete Hosting Record</h4>
      <form method="POST" class="row g-3 align-items-center">
        <div class="col-md-12">
          <select name="hosting_record_id" class="form-select">
            <option value="">Select Hosting Record</option>
            <?php while ($r = $records->fetch_assoc()): ?>
              <option value="<?= $r['id'] ?>"><?= htmlspecialchars($r['client_name']) ?> – <?= htmlspecialchars($r['server_name']) ?> (ID: <?= $r['id'] ?>)</option>
            <?php endwhile; ?>
          </select>
        </div>
        <div class="col-md-3">
          <button type="submit" name="delete_hosting_record" class="btn btn-danger w-100"> Delete Hosting Record</button>
        </div>
      </form>
    </div>

    <!-- Filters Section -->
    <div class="card p-4 shadow-sm mb-5">
      <h4 class="text-primary mb-4">Filters</h4>
      <form method="GET" class="row g-4">
        <?php foreach ($filters as $col => $vals): ?>
          <div class="col-md-3">
            <select name="<?= $col ?>" class="form-select">
              <option value="">Filter <?= ucwords(str_replace('_', ' ', $col)) ?></option>
              <?php foreach ($vals as $v): ?>
                <option value="<?= $v ?>" <?= isset($_GET[$col]) && $_GET[$col] === $v ? 'selected' : '' ?>><?= $v ?></option>
              <?php endforeach; ?>
            </select>
          </div>
        <?php endforeach; ?>
        <div class="col-md-12 text-start">
          <button class="btn btn-primary">Apply Filters</button>
          <a href="?export=csv" class="btn btn-success">Export CSV</a>
        </div>
      </form>
    </div>

    <!-- Hosting Records Table -->
    <div class="card p-4 shadow-sm">
      <h4 class="text-primary mb-4">Hosting Records</h4>
      <div class="table-responsive">
        <table class="table table-bordered table-hover text-center align-middle">
          <thead class="table-light">
            <tr>
              <?php foreach ($columns as $col): ?>
                <th><?= ucwords(str_replace('_', ' ', $col)) ?></th>
              <?php endforeach; ?>
              <th>Action</th>
            </tr>
          </thead>
          <tbody>
            <?php
            $limit = 10; // Only show 10 records initially
            $count = 0;
            while ($r = $data->fetch_assoc()):
              if ($count >= $limit) break;
            ?>
              <tr>
                <?php foreach ($columns as $col): ?>
                  <td>
                    <?= $col === 'password' ? '******' : htmlspecialchars($r[$col]) ?>
                  </td>
                <?php endforeach; ?>
                <td>
                  <div class="btn-group" role="group" aria-label="Actions">
                    <button type="button" class="btn btn-sm text-info" title="Edit"
                      onclick="openEditModal('<?= $r['id'] ?>', '<?= htmlspecialchars($r['client_name']) ?>', '<?= htmlspecialchars($r['server_name']) ?>', '<?= htmlspecialchars($r['os']) ?>', '<?= htmlspecialchars($r['cpu']) ?>', '<?= htmlspecialchars($r['mem']) ?>', '<?= htmlspecialchars($r['sata']) ?>', '<?= htmlspecialchars($r['ssd']) ?>', '<?= htmlspecialchars($r['ip_address']) ?>', '<?= htmlspecialchars($r['note']) ?>', '<?= htmlspecialchars($r['spla']) ?>')">
                      <i class="fas fa-edit"></i>
                    </button>
                    <form method="POST" class="d-inline" onsubmit="return confirm('Are you sure you want to delete this record?');">
                      <input type="hidden" name="hosting_record_id" value="<?= $r['id'] ?>">
                      <button type="submit" name="delete_hosting_record" class="btn btn-sm text-danger" title="Delete">
                        <i class="fas fa-trash-alt"></i>
                      </button>
                    </form>
                  </div>

                </td>
              </tr>
            <?php
              $count++;
            endwhile; ?>
          </tbody>
        </table>

        <!-- View More Button -->
        <?php if ($data->num_rows > $limit): ?>
          <div class="text-center mt-3">
            <a href="full_hosting_list.php" class="btn btn-primary">
              View All Records
            </a>
          </div>
        <?php endif; ?>
      </div>
    </div>

    <!-- Edit Hosting Modal -->
    <div class="modal fade" id="editHostingModal" tabindex="-1" aria-labelledby="editHostingModalLabel" aria-hidden="true">
      <div class="modal-dialog">
        <div class="modal-content">
          <form id="editHostingForm" method="POST">
            <div class="modal-header">
              <h5 class="modal-title" id="editHostingModalLabel">Edit Hosting Record</h5>
              <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
            </div>
            <div class="modal-body">
              <!-- Hidden Hosting Record ID -->
              <input type="hidden" id="hosting-record-id" name="hosting_record_id">

              <!-- Editable Fields -->
              <div class="mb-3">
                <label for="edit-client-name" class="form-label">Client Name</label>
                <input type="text" class="form-control" id="edit-client-name" name="client_name">
              </div>
              <div class="mb-3">
                <label for="edit-os" class="form-label">OS</label>
                <input type="text" class="form-control" id="edit-os" name="os">
              </div>
              <div class="mb-3">
                <label for="edit-cpu" class="form-label">CPU</label>
                <input type="text" class="form-control" id="edit-cpu" name="cpu">
              </div>
              <div class="mb-3">
                <label for="edit-memory" class="form-label">Memory</label>
                <input type="text" class="form-control" id="edit-memory" name="memory">
              </div>
              <div class="mb-3">
                <label for="edit-hdd-sata" class="form-label">HDD SATA</label>
                <input type="text" class="form-control" id="edit-hdd-sata" name="sata">
              </div>
              <div class="mb-3">
                <label for="edit-hdd-ssd" class="form-label">HDD SSD</label>
                <input type="text" class="form-control" id="edit-hdd-ssd" name="ssd">
              </div>
              <div class="mb-3">
                <label for="edit-ip-address" class="form-label">IP Address</label>
                <input type="text" class="form-control" id="edit-ip-address" name="ip_address">
              </div>
              <div class="mb-3">
                <label for="edit-note" class="form-label">Note</label>
                <textarea class="form-control" id="edit-note" name="note"></textarea>
              </div>
              <div class="mb-3">
                <label for="edit-spla" class="form-label">SPLA</label>
                <select class="form-select" id="edit-spla" name="spla">
                  <option value="Yes">Yes</option>
                  <option value="No">No</option>
                </select>
              </div>
            </div>
            <div class="modal-footer">
              <button type="submit" class="btn btn-primary">Save Changes</button>
              <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Close</button>
            </div>
          </form>
        </div>
      </div>
    </div>


  </div>

  <!-- Bootstrap 5 Bundle JS -->
  <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.2/dist/js/bootstrap.bundle.min.js"></script>
  <script src="https://cdn.jsdelivr.net/npm/axios/dist/axios.min.js"></script>

  <script>
    function openEditModal(id, client_name, supplier_name, os, cpu, memory, hdd_sata, hdd_ssd, ip_address, note, spla) {
      // Set the hidden hosting record ID
      document.getElementById('hosting-record-id').value = id;

      // Set the input fields in the modal
      document.getElementById('edit-client-name').value = client_name;
      document.getElementById('edit-os').value = os;
      document.getElementById('edit-cpu').value = cpu;
      document.getElementById('edit-memory').value = memory;
      document.getElementById('edit-hdd-sata').value = hdd_sata;
      document.getElementById('edit-hdd-ssd').value = hdd_ssd;
      document.getElementById('edit-ip-address').value = ip_address;
      document.getElementById('edit-note').value = note;
      document.getElementById('edit-spla').value = spla;

      // Show the modal
      var editModal = new bootstrap.Modal(document.getElementById('editHostingModal'));
      editModal.show();
    }

    document.getElementById('editHostingForm').addEventListener('submit', function(e) {
      e.preventDefault();

      const formData = new FormData(this);
      formData.append('action', 'edit'); // Action to indicate it's an update request

      // Log the form data to check if it's being captured
      for (let pair of formData.entries()) {
        console.log(pair[0] + ": " + pair[1]);
      }

      axios.post('backend.php', formData)
        .then(response => {
          console.log(response.data); // Check if response data is being returned
          if (response.data.trim() === 'success') {
            alert('Hosting Record Updated Successfully ✅');
            location.reload(); // Reload page to reflect changes
          } else {
            alert('Error updating hosting record ❌');
          }
        })
        .catch(error => {
          alert('Server Error ❌');
          console.error(error);
        });
    });
  </script>


</body>

</html>