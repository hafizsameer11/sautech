<?php
$db_host = "localhost";
$db_user = "clientzone_user";
$db_pass = "S@utech2024!";
$db_name = "clientzone";

$conn = new mysqli($db_host, $db_user, $db_pass, $db_name);
if ($conn->connect_error) {
    die("Connection failed: " . $conn->connect_error);
}

$alert = '';
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    if (isset($_POST['add']) || isset($_POST['update'])) {
        $client_id = intval($_POST['client_id']);
        $description = $conn->real_escape_string($_POST['description']);
        $name = $conn->real_escape_string($_POST['name']);
        $email = $conn->real_escape_string($_POST['email']);
    }

    if (isset($_POST['add'])) {
        $conn->query("INSERT INTO resellers (client_id, description, name, email) VALUES ($client_id, '$description', '$name', '$email')");
        $alert = 'Reseller added successfully!';
    } elseif (isset($_POST['update'])) {
        $id = intval($_POST['id']);
        $conn->query("UPDATE resellers SET client_id=$client_id, description='$description', name='$name', email='$email' WHERE id=$id");
        $alert = 'Reseller updated successfully!';
    } elseif (isset($_POST['delete'])) {
        $id = intval($_POST['id']);
        $conn->query("DELETE FROM resellers WHERE id=$id");
        $alert = 'Reseller deleted successfully!';
    }
}


$clients = $conn->query("SELECT id, client_name FROM clients ORDER BY client_name ASC");
$resellers = $conn->query("SELECT r.*, c.client_name FROM resellers r LEFT JOIN clients c ON r.client_id = c.id");
?>
<!DOCTYPE html>
<html lang="en">

<head>
    <meta charset="UTF-8">
    <title>Reseller Management</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
</head>

<body>
    <div class="container mt-4">
        <?php if ($alert): ?>
            <div class="alert alert-success"><?= htmlspecialchars($alert) ?></div>
        <?php endif; ?>

        <div class="d-flex justify-content-between align-items-center mb-3">
            <h2>Reseller Management</h2>
            <button class="btn btn-success" data-bs-toggle="modal" data-bs-target="#addModal">Add Reseller</button>
        </div>

        <table class="table table-bordered">
            <thead>
                <tr>
                    <th>Name</th>
                    <th>Email</th>
                    <th>Client</th>
                    <th>Description</th>
                    <th>Actions</th>
                </tr>
            </thead>
            <tbody>
                <?php while ($r = $resellers->fetch_assoc()): ?>
                    <tr>
                        <td><?= htmlspecialchars($r['name']) ?></td>
                        <td><?= htmlspecialchars($r['email']) ?></td>
                        <td><?= htmlspecialchars($r['client_name']) ?></td>
                        <td><?= htmlspecialchars($r['description']) ?></td>
                        <td>
                            <button class="btn btn-primary btn-sm" data-bs-toggle="modal"
                                data-bs-target="#editModal<?= $r['id'] ?>">Edit</button>
                            <button class="btn btn-danger btn-sm" data-bs-toggle="modal"
                                data-bs-target="#deleteModal<?= $r['id'] ?>">Delete</button>
                        </td>
                    </tr>
                <?php endwhile; ?>
            </tbody>
        </table>
    </div>
    <?php
    $resellers->data_seek(0); // Reset pointer
    while ($r = $resellers->fetch_assoc()):
        ?>
        <!-- Edit Modal -->
        <div class="modal fade" id="editModal<?= $r['id'] ?>" tabindex="-1">
            <div class="modal-dialog">
                <form method="POST" class="modal-content">
                    <div class="modal-header">
                        <h5 class="modal-title">Edit Reseller</h5>
                        <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
                    </div>
                    <div class="modal-body">
                        <input type="hidden" name="id" value="<?= $r['id'] ?>">
                        <div class="mb-3">
                            <label class="form-label">Name</label>
                            <input type="text" name="name" value="<?= htmlspecialchars($r['name']) ?>" class="form-control"
                                required>
                        </div>
                        <div class="mb-3">
                            <label class="form-label">Email</label>
                            <input type="email" name="email" value="<?= htmlspecialchars($r['email']) ?>"
                                class="form-control" required>
                        </div>
                        <div class="mb-3">
                            <label class="form-label">Client</label>
                            <select name="client_id" class="form-select" required>
                                <?php
                                $clients->data_seek(0);
                                while ($c = $clients->fetch_assoc()): ?>
                                    <option value="<?= $c['id'] ?>" <?= $c['id'] == $r['client_id'] ? 'selected' : '' ?>>
                                        <?= htmlspecialchars($c['client_name']) ?>
                                    </option>
                                <?php endwhile; ?>
                            </select>
                        </div>
                        <div class="mb-3">
                            <label class="form-label">Description</label>

                            <input type="text" name="description" value="<?= htmlspecialchars($r['description']) ?>"
                                class="form-control" required>
                        </div>
                    </div>
                    <div class="modal-footer">
                        <button name="update" class="btn btn-primary">Update</button>
                        <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Cancel</button>
                    </div>
                </form>
            </div>
        </div>

        <!-- Delete Modal -->
        <!-- Delete Modal -->
        <div class="modal fade" id="deleteModal<?= $r['id'] ?>" tabindex="-1">
            <div class="modal-dialog">
                <form method="POST" class="modal-content">
                    <div class="modal-header">
                        <h5 class="modal-title">Delete Reseller</h5>
                        <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
                    </div>
                    <div class="modal-body">
                        Are you sure you want to delete <strong><?= htmlspecialchars($r['client_name']) ?></strong>?
                        <input type="hidden" name="id" value="<?= $r['id'] ?>">
                    </div>
                    <div class="modal-footer">
                        <button name="delete" class="btn btn-danger">Delete</button>
                        <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Cancel</button>
                    </div>
                </form>
            </div>
        </div>

    <?php endwhile; ?>


    <!-- Add Modal -->
    <div class="modal fade" id="addModal" tabindex="-1">
        <div class="modal-dialog">
            <form method="POST" class="modal-content">
                <div class="modal-header">
                    <h5 class="modal-title">Add Reseller</h5>
                    <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
                </div>
                <div class="modal-body">
                    <div class="mb-3">
                        <label class="form-label">Name</label>
                        <input type="text" name="name" class="form-control" required>
                    </div>
                    <div class="mb-3">
                        <label class="form-label">Email</label>
                        <input type="email" name="email" class="form-control" required>
                    </div>
                    <div class="mb-3">
                        <label class="form-label">Client</label>
                        <select name="client_id" class="form-select" required>
                            <option value="">Select Client</option>
                            <?php
                            $clients->data_seek(0);
                            while ($c = $clients->fetch_assoc()): ?>

                                <option value="<?= $c['id'] ?>"><?= htmlspecialchars($c['client_name']) ?></option>
                            <?php endwhile; ?>
                        </select>
                    </div>
                    <div class="mb-3">
                        <label class="form-label">Description</label>
                        <input type="text" name="description" class="form-control" required>
                    </div>
                </div>
                <div class="modal-footer">
                    <button name="add" class="btn btn-success">Add</button>
                    <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Cancel</button>
                </div>
            </form>
        </div>
    </div>

    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script>
</body>

</html>