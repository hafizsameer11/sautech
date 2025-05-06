<?php
error_reporting(E_ALL);
ini_set('display_errors', 1);

$db_host = "localhost";
    $db_user = "clientzone_user";
    $db_pass = "S@utech2024!";
    $db_name = "clientzone";

$conn = new mysqli($db_host, $db_user, $db_pass, $db_name);

if ($conn->connect_error) {
    die("Connection failed: " . $conn->connect_error);
}

// Fetch suppliers
$suppliers = $conn->query("SELECT * FROM billing_suppliers ORDER BY created_at DESC");
?>

<!DOCTYPE html>
<html lang="en">

<head>
    <meta charset="UTF-8">
    <title>Manage Suppliers</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.2/dist/css/bootstrap.min.css" rel="stylesheet">
    <link href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css" rel="stylesheet">
</head>

<body>

    <div class=" my-5" style="width: 93%; margin: auto;">
        <div class="d-flex justify-content-between align-items-center mb-4">
            <div class="d-flex align-items-center">
                <?php include('../../../components/Backbtn.php') ?>
                <h2 class="mb-0">Manage Suppliers</h2>
            </div>
            <button class="btn btn-primary" data-bs-toggle="modal" data-bs-target="#addSupplierModal">
                Add Supplier
            </button>
        </div>


        <table class="table table-hover table-bordered text-center">
            <thead class="table-light">
                <tr>
                    <th>ID</th>
                    <th>Supplier Name</th>
                    <th>Contact Details</th>
                    <th>Email</th>
                    <th>Sales Person</th>
                    <th>Actions</th>
                </tr>
            </thead>
            <tbody>
                <?php while ($supplier = $suppliers->fetch_assoc()): ?>
                    <tr>
                        <td><?= $supplier['id'] ?></td>
                        <td><?= htmlspecialchars($supplier['supplier_name']) ?></td>
                        <td><?= htmlspecialchars($supplier['contact_details']) ?></td>
                        <td><?= htmlspecialchars($supplier['email']) ?></td>
                        <td><?= htmlspecialchars($supplier['salesperson']) ?></td>
                        <td class="text-center">
                            <div class="btn-group" role="group" aria-label="Actions">
                                <a href="javascript:void(0);" onclick="openEditModal(<?= $supplier['id'] ?>)"
                                    class="btn btn-sm" title="Edit">
                                    <i class="fas fa-edit"></i>
                                </a>
                                <a href="javascript:void(0);" onclick="deleteSupplier(<?= $supplier['id'] ?>)"
                                    class="btn btn-sm text-danger" title="Delete">
                                    <i class="fas fa-trash-alt"></i>
                                </a>
                            </div>
                        </td>

                    </tr>
                <?php endwhile; ?>
            </tbody>
        </table>
    </div>

    <!-- Add Supplier Modal -->
    <div class="modal fade" id="addSupplierModal" tabindex="-1" aria-labelledby="addSupplierModalLabel"
        aria-hidden="true">
        <div class="modal-dialog">
            <form id="addSupplierForm" class="modal-content">
                <div class="modal-header">
                    <h5 class="modal-title"> Add Supplier</h5>
                    <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
                </div>

                <div class="modal-body row g-3">
                    <div class="col-12">
                        <label class="form-label">Supplier Name</label>
                        <input type="text" name="supplier_name" class="form-control" required>
                    </div>

                    <div class="col-12">
                        <label class="form-label">Contact Details</label>
                        <textarea name="contact_details" class="form-control"></textarea>
                    </div>

                    <div class="col-12">
                        <label class="form-label">Email Address</label>
                        <input type="email" name="email" class="form-control">
                    </div>

                    <div class="col-12">
                        <label class="form-label">Sales Person</label>
                        <input type="text" name="sales_person" class="form-control">
                    </div>
                </div>

                <div class="modal-footer">
                    <button type="submit" class="btn btn-success">Save Supplier</button>
                </div>
            </form>
        </div>
    </div>

    <!-- Edit Supplier Modal -->
    <div class="modal fade" id="editSupplierModal" tabindex="-1" aria-labelledby="editSupplierModalLabel"
        aria-hidden="true">
        <div class="modal-dialog">
            <form id="editSupplierForm" class="modal-content">
                <div class="modal-header">
                    <h5 class="modal-title"> Edit Supplier</h5>
                    <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
                </div>

                <div class="modal-body row g-3">
                    <input type="hidden" name="id" id="edit-id">

                    <div class="col-12">
                        <label class="form-label">Supplier Name</label>
                        <input type="text" name="supplier_name" id="edit-supplier-name" class="form-control" required>
                    </div>

                    <div class="col-12">
                        <label class="form-label">Contact Details</label>
                        <textarea name="contact_details" id="edit-contact-details" class="form-control"></textarea>
                    </div>

                    <div class="col-12">
                        <label class="form-label">Email Address</label>
                        <input type="email" name="email" id="edit-email" class="form-control">
                    </div>

                    <div class="col-12">
                        <label class="form-label">Sales Person</label>
                        <input type="text" name="sales_person" id="edit-sales-person" class="form-control">
                    </div>
                </div>

                <div class="modal-footer">
                    <button type="submit" class="btn btn-primary">Update Supplier</button>
                </div>
            </form>
        </div>
    </div>

    <script src="https://cdn.jsdelivr.net/npm/axios/dist/axios.min.js"></script>
    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.2/dist/js/bootstrap.bundle.min.js"></script>

    <script>
        // Handle Add Supplier
        document.getElementById('addSupplierForm').addEventListener('submit', function (e) {
            e.preventDefault();
            const formData = new FormData(this);
            formData.append('action', 'add');

            axios.post('supplier-backend.php', formData)
                .then(response => {
                    if (response.data.trim() === 'success') {
                        alert('Supplier Added Successfully ✅');
                        location.reload();
                    } else {
                        alert('Failed to add supplier ❌');
                    }
                })
                .catch(error => {
                    alert('Server Error ❌');
                    console.error(error);
                });
        });

        // Handle Edit Supplier
        document.getElementById('editSupplierForm').addEventListener('submit', function (e) {
            e.preventDefault();
            const formData = new FormData(this);
            formData.append('action', 'edit');

            axios.post('supplier-backend.php', formData)
                .then(response => {
                    if (response.data.trim() === 'success') {
                        alert('Supplier Updated Successfully ✅');
                        location.reload();
                    } else {
                        alert('Failed to update supplier ❌');
                    }
                })
                .catch(error => {
                    alert('Server Error ❌');
                    console.error(error);
                });
        });

        // Open Edit Modal
        function openEditModal(id) {
            const formData = new FormData();
            formData.append('action', 'fetch');
            formData.append('id', id);

            axios.post('supplier-backend.php', formData)
                .then(response => {
                    const supplier = response.data;

                    document.getElementById('edit-id').value = supplier.id;
                    document.getElementById('edit-supplier-name').value = supplier.supplier_name;
                    document.getElementById('edit-contact-details').value = supplier.contact_details;
                    document.getElementById('edit-email').value = supplier.email;
                    document.getElementById('edit-sales-person').value = supplier.sales_person;

                    var editModal = new bootstrap.Modal(document.getElementById('editSupplierModal'));
                    editModal.show();
                })
                .catch(error => {
                    alert('Failed to fetch supplier details ❌');
                    console.error(error);
                });
        }

        // Delete Supplier
        function deleteSupplier(id) {
            if (confirm('Are you sure you want to delete this supplier?')) {
                const formData = new FormData();
                formData.append('action', 'delete');
                formData.append('id', id);

                axios.post('supplier-backend.php', formData)
                    .then(response => {
                        if (response.data.trim() === 'success') {
                            alert('Supplier Deleted Successfully ✅');
                            location.reload();
                        } else {
                            alert('Failed to delete supplier ❌');
                        }
                    })
                    .catch(error => {
                        alert('Server Error ❌');
                        console.error(error);
                    });
            }
        }
    </script>

</body>

</html>