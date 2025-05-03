<?php
error_reporting(E_ALL);
ini_set('display_errors', 1);

if (\$_SERVER['REQUEST_METHOD'] === 'POST') {
    echo "<pre>POST DATA:\n";
    print_r(\$_POST);
    echo "\nFILES:\n";
    print_r(\$_FILES);
    echo "</pre>";
}
ini_set('display_errors', 1);

// Declare all expected variables upfront
$viewing = false;
$editing = false;
$view_data = null;
$support = null;
$docs = null;
$clients = null;
$id = null;

$conn = new mysqli("localhost", "root", "", "clientzone");

if ($conn->connect_error) {
    die("Connection failed: " . $conn->connect_error);
}

// Handle new client creation
if (isset($_POST['save_client'])) {
    $stmt = $conn->prepare("INSERT INTO clients (client_name, email, contact_person, office_number, accounts_contact, accounts_email, address, notes, vat_number, registration_number, billing_type, status, sales_person, billing_country, currency, created_at) VALUES (?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, NOW())");

    if (!$stmt) {
        die("❌ SQL Prepare failed: " . $conn->error);
    }

    $stmt->bind_param("sssssssssssssss",
        $_POST['client_name'],
        $_POST['email'],
        $_POST['contact_person'],
        $_POST['office_number'],
        $_POST['accounts_contact'],
        $_POST['accounts_email'],
        $_POST['address'],
        $_POST['notes'],
        $_POST['vat_number'],
        $_POST['registration_number'],
        $_POST['billing_type'],
        $_POST['status'],
        $_POST['sales_person'],
        $_POST['billing_country'],
        $_POST['currency']
    );

    $stmt->execute();

    if ($stmt->affected_rows < 1) {
        die("❌ Insert failed. MySQL error: " . $stmt->error);
    }

    header("Location: clientinfo.php#submitted");
    exit;
}

// Handle update client
if (isset($_POST['update_client'])) {
    $stmt = $conn->prepare("UPDATE clients SET client_name=?, email=?, contact_person=?, office_number=?, accounts_contact=?, accounts_email=?, address=?, notes=?, vat_number=?, registration_number=?, billing_type=?, status=?, sales_person=?, billing_country=?, currency=? WHERE id=?");

    if (!$stmt) {
        die("❌ SQL Prepare failed (update): " . $conn->error);
    }

    $stmt->bind_param("sssssssssssssssi",
        $_POST['client_name'],
        $_POST['email'],
        $_POST['contact_person'],
        $_POST['office_number'],
        $_POST['accounts_contact'],
        $_POST['accounts_email'],
        $_POST['address'],
        $_POST['notes'],
        $_POST['vat_number'],
        $_POST['registration_number'],
        $_POST['billing_type'],
        $_POST['status'],
        $_POST['sales_person'],
        $_POST['billing_country'],
        $_POST['currency'],
        $_POST['client_id']
    );

    $stmt->execute();

    if ($stmt->affected_rows < 0) {
        die("❌ Update failed. MySQL error: " . $stmt->error);
    }

    header("Location: clientinfo.php?view=" . $_POST['client_id']);
    exit;
}

// Handle document upload
if (isset($_POST['upload_doc']) && isset($_FILES['doc_file'])) {
    $clientId = intval($_POST['client_id']);
    $docName = $_POST['doc_name'];
    $fileName = time() . '_' . basename($_FILES['doc_file']['name']);
    $targetDir = __DIR__ . "/uploads/";
    $targetFile = $targetDir . $fileName;

    if (move_uploaded_file($_FILES['doc_file']['tmp_name'], $targetFile)) {
        $conn->query("INSERT INTO client_documents (client_id, doc_name, filename) VALUES ($clientId, '" . $conn->real_escape_string($docName) . "', '" . $conn->real_escape_string($fileName) . "')");
    }
    header("Location: clientinfo.php?view=$clientId");
    exit;
}

// Handle support item add/update/delete with note field
if (isset($_POST['add_support'])) {
    $conn->query("INSERT INTO client_support_items (client_id, label, type, ip_address, username, password, note) VALUES (
        " . intval($_POST['client_id']) . ",
        '" . $conn->real_escape_string($_POST['label']) . "',
        '" . $conn->real_escape_string($_POST['type']) . "',
        '" . $conn->real_escape_string($_POST['ip_address']) . "',
        '" . $conn->real_escape_string($_POST['username']) . "',
        '" . $conn->real_escape_string($_POST['password']) . "',
        '" . $conn->real_escape_string($_POST['note']) . "'
    )");
    header("Location: clientinfo.php?view=" . intval($_POST['client_id']));
    exit;
}

if (isset($_POST['update_support'])) {
    $conn->query("UPDATE client_support_items SET
        label='" . $conn->real_escape_string($_POST['label']) . "',
        type='" . $conn->real_escape_string($_POST['type']) . "',
        ip_address='" . $conn->real_escape_string($_POST['ip_address']) . "',
        username='" . $conn->real_escape_string($_POST['username']) . "',
        password='" . $conn->real_escape_string($_POST['password']) . "',
        note='" . $conn->real_escape_string($_POST['note']) . "'
        WHERE id=" . intval($_POST['support_id'])
    );
    header("Location: clientinfo.php?view=" . intval($_POST['client_id']));
    exit;
}

if (isset($_GET['delete_support'])) {
    $sid = intval($_GET['delete_support']);
    $cid = intval($_GET['view']);
    $conn->query("DELETE FROM client_support_items WHERE id = $sid");
    header("Location: clientinfo.php?view=$cid");
    exit;
}

if (isset($_GET['delete_doc'])) {
    $did = intval($_GET['delete_doc']);
    $cid = intval($_GET['view']);
    $conn->query("DELETE FROM client_documents WHERE id = $did");
    header("Location: clientinfo.php?view=$cid");
    exit;
}

if (isset($_GET['delete_client'])) {
    $id = intval($_GET['delete_client']);
    $conn->query("DELETE FROM clients WHERE id = $id");
    header("Location: clientinfo.php");
    exit;
}

if (isset($_GET['view'])) {
    $viewing = true;
    $id = intval($_GET['view']);
} elseif (isset($_GET['edit'])) {
    $editing = true;
    $id = intval($_GET['edit']);
    $viewing = true;
}

if (isset($id)) {
    $result = $conn->query("SELECT * FROM clients WHERE id = $id");
    if ($result && $result->num_rows > 0) {
        $view_data = $result->fetch_assoc();
        $support = $conn->query("SELECT * FROM client_support_items WHERE client_id = $id");
        $docs = $conn->query("SELECT * FROM client_documents WHERE client_id = $id");
    }
}

$clients = $conn->query("SELECT * FROM clients ORDER BY created_at DESC");
if (!$clients) {
    die("❌ Query failed for clients: " . $conn->error);
}

?>

<!DOCTYPE html>
<html lang="en">
<head>
  <meta charset="UTF-8">
  <title>Client Management</title>
  <style>
    body { font-family: Arial, sans-serif; padding: 20px; background: #f9f9f9; }
    h2 { color: #333; }
    table { width: 100%; border-collapse: collapse; margin-top: 20px; }
    th, td { padding: 10px; border: 1px solid #ccc; text-align: left; }
    th { background-color: #eee; }
    form.client-form { display: none; margin-bottom: 20px; background: #fff; padding: 15px; border: 1px solid #ddd; }
    input, select, textarea { width: 100%; padding: 5px; margin-bottom: 10px; }
    button { padding: 8px 12px; }
    .view-box { margin-top: 30px; padding: 15px; background: #fff; border: 1px solid #ccc; }
  </style>
</head>
<body>

<button id="showClientForm">➕ Add New Client</button>
<script>
document.getElementById("showClientForm").addEventListener("click", function() {
  document.querySelector(".client-form").style.display = "block";
});
</script>
<script>
  document.addEventListener("DOMContentLoaded", function() {
  const form = document.querySelector('.client-form');
  if (window.location.hash === '#add') {
    form.style.display = 'block';
  }
  if (window.location.hash === '#submitted') {
    form.style.display = 'none';
  }
});
  });
</script>

<?php if ($editing && $view_data): ?>
<form method="post" class="client-form" style="display:block;">
  <h2>Edit Client</h2>
  <input type="text" name="client_name" value="<?= htmlspecialchars($view_data['client_name']) ?>" required>
  <input type="text" name="email" value="<?= htmlspecialchars($view_data['email']) ?>">
  <input type="text" name="contact_person" value="<?= htmlspecialchars($view_data['contact_person']) ?>">
  <input type="text" name="office_number" value="<?= htmlspecialchars($view_data['office_number']) ?>">
  <input type="text" name="accounts_contact" value="<?= htmlspecialchars($view_data['accounts_contact']) ?>">
  <input type="text" name="accounts_email" value="<?= htmlspecialchars($view_data['accounts_email']) ?>">
  <input type="text" name="address" value="<?= htmlspecialchars($view_data['address']) ?>">
  <textarea name="notes"><?= htmlspecialchars($view_data['notes']) ?></textarea>
  <input type="text" name="vat_number" value="<?= htmlspecialchars($view_data['vat_number']) ?>">
  <input type="text" name="registration_number" value="<?= htmlspecialchars($view_data['registration_number']) ?>">
  <select name="billing_type">
    <option value="Invoice" <?= $view_data['billing_type'] == 'Invoice' ? 'selected' : '' ?>>Invoice</option>
    <option value="Debit Order" <?= $view_data['billing_type'] == 'Debit Order' ? 'selected' : '' ?>>Debit Order</option>
  </select>
  <select name="status">
    <option value="Active" <?= $view_data['status'] == 'Active' ? 'selected' : '' ?>>Active</option>
    <option value="Suspended" <?= $view_data['status'] == 'Suspended' ? 'selected' : '' ?>>Suspended</option>
    <option value="Cancelled" <?= $view_data['status'] == 'Cancelled' ? 'selected' : '' ?>>Cancelled</option>
    <option value="Lead" <?= $view_data['status'] == 'Lead' ? 'selected' : '' ?>>Lead</option>
  </select>
  <input type="text" name="sales_person" value="<?= htmlspecialchars($view_data['sales_person']) ?>">
  <select name="billing_country">
    <option value="RSA" <?= $view_data['billing_country'] == 'RSA' ? 'selected' : '' ?>>RSA</option>
    <option value="Namibia" <?= $view_data['billing_country'] == 'Namibia' ? 'selected' : '' ?>>Namibia</option>
  </select>
  <select name="currency">
    <option value="ZAR" <?= $view_data['currency'] == 'ZAR' ? 'selected' : '' ?>>ZAR</option>
    <option value="NAD" <?= $view_data['currency'] == 'NAD' ? 'selected' : '' ?>>NAD</option>
    <option value="USD" <?= $view_data['currency'] == 'USD' ? 'selected' : '' ?>>USD</option>
  </select>
  <input type="hidden" name="client_id" value="<?= $id ?>">
  <button type="submit" name="update_client">💾 Update Client</button>
</form>
<?php else: ?>
<form method="post" class="client-form" style="display:none;">
  <h2>Add New Client</h2>
  <input type="text" name="client_name" placeholder="Client Name" required>
  <input type="text" name="email" placeholder="Email">
  <input type="text" name="contact_person" placeholder="Contact Person">
  <input type="text" name="office_number" placeholder="Office Number">
  <input type="text" name="accounts_contact" placeholder="Accounts Contact">
  <input type="text" name="accounts_email" placeholder="Accounts Email">
  <input type="text" name="address" placeholder="Address">
  <textarea name="notes" placeholder="Notes"></textarea>
  <input type="text" name="vat_number" placeholder="VAT Number">
  <input type="text" name="registration_number" placeholder="Company Registration Number">
  <select name="billing_type">
    <option value="Invoice">Invoice</option>
    <option value="Debit Order">Debit Order</option>
  </select>
  <select name="status">
    <option value="Active">Active</option>
    <option value="Suspended">Suspended</option>
    <option value="Cancelled">Cancelled</option>
    <option value="Lead">Lead</option>
  </select>
  <input type="text" name="sales_person" placeholder="Sales Person">
  <select name="billing_country">
    <option value="RSA">RSA</option>
    <option value="Namibia">Namibia</option>
  </select>
  <select name="currency">
    <option value="ZAR">ZAR</option>
    <option value="NAD">NAD</option>
    <option value="USD">USD</option>
  </select>
  <button type="submit" name="save_client">💾 Save New Client</button>
</form>
<?php endif; ?>

<?php if ($viewing && $view_data): ?>
<div class="view-box">
  <h2>👁 Viewing Client: <?= htmlspecialchars($view_data['client_name']) ?></h2>
  <p><strong>Email:</strong> <?= htmlspecialchars($view_data['email']) ?></p>
  <p><strong>Contact Person:</strong> <?= htmlspecialchars($view_data['contact_person']) ?></p>
  <p><strong>Status:</strong> <?= htmlspecialchars($view_data['status']) ?></p>
  <p><strong>Billing Type:</strong> <?= htmlspecialchars($view_data['billing_type']) ?></p>
  <p><strong>Billing Country:</strong> <?= htmlspecialchars($view_data['billing_country']) ?></p>
  <p><strong>Currency:</strong> <?= htmlspecialchars($view_data['currency']) ?></p>
  <p><strong>Sales Person:</strong> <?= htmlspecialchars($view_data['sales_person']) ?></p>
  <p><strong>Address:</strong> <?= htmlspecialchars($view_data['address']) ?></p>
  <p><strong>Notes:</strong><br><?= nl2br(htmlspecialchars($view_data['notes'])) ?></p>

  <h3>📁 Documents</h3>
  <form method="post" enctype="multipart/form-data" action="clientinfo.php?view=<?= $id ?>">
    <input type="hidden" name="client_id" value="<?= $id ?>">
    <input type="text" name="doc_name" placeholder="Document Name" required>
    <input type="file" name="doc_file" required>
    <button type="submit" name="upload_doc">📎 Upload</button>
  </form>
  <ul>
    <?php while ($doc = $docs->fetch_assoc()): ?>
      <li><a href="modules/clientinfo/uploads/<?= htmlspecialchars($doc['filename']) ?>" target="_blank"><?= htmlspecialchars($doc['doc_name']) ?></a> <a href="?view=<?= $id ?>&delete_doc=<?= $doc['id'] ?>">🗑</a></li>
    <?php endwhile; ?>
  </ul>

  <h3>🔧 Support Details</h3>
  <form method="post" action="clientinfo.php?view=<?= $id ?>">
    <input type="hidden" name="client_id" value="<?= $id ?>">
    <input type="text" name="label" placeholder="Label" required>
    <input type="text" name="type" placeholder="Type" required>
    <input type="text" name="ip_address" placeholder="IP">
    <input type="text" name="username" placeholder="User">
    <input type="text" name="password" placeholder="Pass">
    <input type="text" name="note" placeholder="Note">
    <button type="submit" name="add_support">➕ Add</button>
  </form>
  <table>
    <tr><th>Label</th><th>Type</th><th>IP</th><th>User</th><th>Pass</th><th>Note</th><th>Actions</th></tr>
    <?php while ($s = $support->fetch_assoc()): ?>
      <tr>
        <form method="post" action="clientinfo.php?view=<?= $id ?>">
        <td><input name="label" value="<?= htmlspecialchars($s['label']) ?>"></td>
        <td><input name="type" value="<?= htmlspecialchars($s['type']) ?>"></td>
        <td><input name="ip_address" value="<?= htmlspecialchars($s['ip_address']) ?>"></td>
        <td><input name="username" value="<?= htmlspecialchars($s['username']) ?>"></td>
        <td><input name="password" value="<?= htmlspecialchars($s['password']) ?>"></td>
        <td><input name="note" value="<?= htmlspecialchars($s['note']) ?>"></td>
        <td>
          <input type="hidden" name="support_id" value="<?= $s['id'] ?>">
          <input type="hidden" name="client_id" value="<?= $id ?>">
          <button name="update_support">💾</button>
          <a href="?view=<?= $id ?>&delete_support=<?= $s['id'] ?>">🗑</a>
        </td>
        </form>
      </tr>
    <?php endwhile; ?>
  </table>
</div>
<?php endif; ?>

<h2>📋 Clients</h2>
<table>
  <tr><th>Client</th><th>Contact</th><th>Status</th><th>Created</th><th>Actions</th></tr>
  <?php while ($c = $clients->fetch_assoc()): ?>
    <tr>
      <td><?= htmlspecialchars($c['client_name']) ?></td>
      <td><?= htmlspecialchars($c['contact_person']) ?></td>
      <td><?= htmlspecialchars($c['status']) ?></td>
      <td><?= $c['created_at'] ?></td>
      <td>
        <a href="clientinfo.php?view=<?= $c['id'] ?>">👁 View</a> |
        <a href="clientinfo.php?edit=<?= $c['id'] ?>">✏️ Edit</a> |
        <a href="clientinfo.php?delete_client=<?= $c['id'] ?>" onclick="return confirm('Delete this client?')">🗑 Delete</a>
      </td>
    </tr>
  <?php endwhile; ?>
</table>

</body>
</html>
