<?php
error_reporting(E_ALL);

ini_set('display_errors', 1);



// Declare all expected variables upfront

$viewing = false;

$editing = false;

$view_data = null;

$support = null;

$docs = null;

$clients = null;

$id = null;



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

    $docName = $_POST['name'];

    $fileName = time() . '_' . basename($_FILES['doc_file']['name']);

    $targetDir = __DIR__ . "/uploads/";

    $targetFile = $targetDir . $fileName;



    if (move_uploaded_file($_FILES['doc_file']['tmp_name'], $targetFile)) {

        $conn->query("INSERT INTO client_documents (client_id, name, filename) VALUES ($clientId, '" . $conn->real_escape_string($docName) . "', '" . $conn->real_escape_string($fileName) . "')");

    }

    header("Location: clientinfo.php?view=$clientId");

    exit;

}



// Handle support item add/update/delete with note field

if (isset($_POST['add_support'])) {

    $extra = json_encode(['note' => $_POST['note']]);

    $conn->query("INSERT INTO client_support_items (client_id, label, type, ip_address, username, password, extra) VALUES (

        " . intval($_POST['client_id']) . ",

        '" . $conn->real_escape_string($_POST['label']) . "',

        '" . $conn->real_escape_string($_POST['type']) . "',

        '" . $conn->real_escape_string($_POST['ip_address']) . "',

        '" . $conn->real_escape_string($_POST['username']) . "',

        '" . $conn->real_escape_string($_POST['password']) . "',

        '" . $conn->real_escape_string($extra) . "'

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

if (isset($id)) {
    $result = $conn->query("SELECT * FROM clients WHERE id = $id");
    if ($result && $result->num_rows > 0) {
        $view_data = $result->fetch_assoc();
    }
}
        $support = $conn->query("SELECT * FROM client_support_items WHERE client_id = $id");

        $docs = $conn->query("SELECT * FROM client_documents WHERE client_id = $id");

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



    <script>

        function addToDropdown(id, promptText) {

            let value = prompt(promptText);

            if (value) {

                let dropdown = document.getElementById(id);

                let option = document.createElement("option");

                option.text = value;

                option.value = value;

                dropdown.add(option);

                dropdown.value = value;

            }

        }



        function removeFromDropdown(id) {

            let dropdown = document.getElementById(id);

            if (dropdown.selectedIndex > -1) {

                dropdown.remove(dropdown.selectedIndex);

            }

        }

    </script>

    

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

  <form method="post" enctype="multipart/form-data">

    <input type="hidden" name="client_id" value="<?= $id ?>">

    <input type="text" name="name" placeholder="Document Name" required>

    <input type="file" name="doc_file" required>

    <button type="submit" name="upload_doc">📎 Upload</button>

  </form>

  <ul>

    <?php while ($doc = $docs->fetch_assoc()): ?>

      <li><a href="modules/clientinfo/uploads/<?= htmlspecialchars($doc['filename']) ?>" target="_blank"><?= htmlspecialchars($doc['name']) ?></a> <a href="?view=<?= $id ?>&delete_doc=<?= $doc['id'] ?>">🗑</a></li>

    <?php endwhile; ?>

  </ul>



  <h3>🔧 Support Details</h3>

  <form method="post">

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

<?php while ($s = $support->fetch_assoc()):
    $note = '';

    if (!empty($s['extra'])) {

        $extraData = json_decode($s['extra'], true);

        if (json_last_error() === JSON_ERROR_NONE && isset($extraData['note'])) {

            $note = htmlspecialchars($extraData['note']);

        }

    }

?>

      <tr>

        <form method="post">

        <td><input name="label" value="<?= htmlspecialchars($s['label']) ?>"></td>

        <td><input name="type" value="<?= htmlspecialchars($s['type']) ?>"></td>

        <td><input name="ip_address" value="<?= htmlspecialchars($s['ip_address']) ?>"></td>

        <td><input name="username" value="<?= htmlspecialchars($s['username']) ?>"></td>

        <td><input name="password" value="<?= htmlspecialchars($s['password']) ?>"></td>

        <td><input name="note" value="<?php echo $note; ?>"></td>

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

<h2>Clients</h2>

<table>

  <tr>

    <th>Client</th>

    <th>Contact</th>

    <th>Status</th>

    <th>Actions</th>

  </tr>



<?php
if ($viewing || $editing) {

    $clientResult = $conn->query("SELECT * FROM clients WHERE id = $id");

} else {

    $clientResult = $conn->query("SELECT * FROM clients");

}



while ($row = $clientResult->fetch_assoc()):

?>

  <tr>

    <td><?= htmlspecialchars($row['client_name']) ?></td>

    <td><?= htmlspecialchars($row['contact_person']) ?></td>

    <td><?= htmlspecialchars($row['status']) ?></td>

    <td><?= htmlspecialchars($row['created_at']) ?></td>

    <td>

      <a href="?view=<?= $row['id'] ?>">👁️ View</a> |

      <a href="?edit=<?= $row['id'] ?>">✏️ Edit</a> |

      <a href="?delete=<?= $row['id'] ?>" onclick="return confirm('Delete this client?')">🗑️ Delete</a>

    </td>

  </tr>

<?php endwhile; ?>

</table>





<?php if ($viewing || $editing): ?>

<div class="support-section">

  <form id="exchangeForm" >

    <input type="hidden" name="client_id" value="<?php echo $id; ?>">



    <div class="form-group">

      <label>Domain</label>

      <select name="field1" id="domain" onchange="setDomainValue(this.value)">

<?php
        if ($domains) {

            while ($d = $domains->fetch_assoc()) {

                $domainVal = htmlspecialchars($d['field1'] ?? '');

                echo "<option value='$domainVal'>$domainVal</option>";

            }

        } else {

            echo "<option value=''>⚠️ Query Error</option>";

        }

        ?>

      </select>

    </div>





        <option value="">Select Server</option>

<?php
        }

        ?>

      </select>

    </div>



    <div class="form-group"><label>Notes</label><textarea name="note"></textarea></div>

    <div id="exchange_status" style="margin-top:10px;color:green;"></div>

  </form>



  <table border="1" cellpadding="5" cellspacing="0">

    <thead>

      <tr>

      </tr>

    </thead>

    <tbody>

<?php
    </tbody>

  </table>

</div>

<?php endif; ?>





</html>





<script>

document.addEventListener("DOMContentLoaded", function () {

  const form = document.getElementById("exchangeForm");

  form.addEventListener("submit", function (e) {

    e.preventDefault();

    const data = new FormData(form);

    fetch('/modules/clientinfo/save_exchange.php', {

      method: 'POST',

      body: data

    })

    .then(res => res.text())

    .then(response => {

      const statusBox = document.getElementById('exchange_status');

      statusBox.innerText = response;

      statusBox.style.color = 'green';

      form.reset();

      document.getElementById("domain").value = lastDomain;

      statusBox.scrollIntoView({ behavior: 'smooth' });

    })

    .catch(() => {

      const statusBox = document.getElementById('exchange_status');

      statusBox.innerText = '❌ Error saving entry.';

      statusBox.style.color = 'red';

    });

  });

});

</script>
?>