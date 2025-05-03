<?php
error_reporting(E_ALL);
ini_set('display_errors', 1);
$conn = new mysqli("localhost", "root", "", "clientzone");
if ($conn->connect_error) {
    die("Connection failed: " . $conn->connect_error);
}
$companies = $conn->query("SELECT * FROM billing_invoice_companies ORDER BY created_at DESC");
?>

<!DOCTYPE html>
<html lang="en">

<head>
    <meta charset="UTF-8">
    <title>Manage Invoicing Companies</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.2/dist/css/bootstrap.min.css" rel="stylesheet">
    <link href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css" rel="stylesheet">
</head>

<body>
    <div class="my-5" style="width: 93%; margin: auto;">
        <div class="d-flex justify-content-between align-items-center mb-4">
            <div class="d-flex align-items-center">
                <?php include('../../../components/Backbtn.php') ?>
                <h3 class="mb-0">Manage Invoicing Companies</h3>
            </div>
            <button class="btn btn-primary" data-bs-toggle="modal" data-bs-target="#addCompanyModal">Add
                Company</button>
        </div>

        <table class="table table-hover table-bordered text-center">
            <thead class="table-light">
                <tr>
                    <th>ID</th>
                    <th>Company Name</th>
                    <th>VAT Rate (%)</th>
                    <th>Actions</th>
                </tr>
            </thead>
            <tbody>
                <?php while ($row = $companies->fetch_assoc()): ?>
                    <tr>
                        <td><?= $row['id'] ?></td>
                        <td><?= htmlspecialchars($row['company_name']) ?></td>
                        <td><?= htmlspecialchars($row['vat_rate']) ?></td>
                        <td class="text-center">
                            <div class="btn-group" role="group" aria-label="Actions">
                                <a href="javascript:void(0);" onclick="openEditModal(<?= $row['id'] ?>)" class="btn btn-sm"
                                    title="Edit">
                                    <i class="fas fa-edit"></i>
                                </a>
                                <a href="javascript:void(0);" onclick="deleteCompany(<?= $row['id'] ?>)"
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

    <!-- Add Modal -->
    <div class="modal fade" id="addCompanyModal" tabindex="-1">
        <div class="modal-dialog">
            <form id="addCompanyForm" class="modal-content">
                <div class="modal-header">
                    <h5 class="modal-title">Add Company</h5>
                    <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
                </div>
                <div class="modal-body row g-3">
                    <div class="col-12">
                        <label class="form-label">Company Name</label>
                        <input type="text" name="company_name" class="form-control" required>
                    </div>
                    <div class="col-12">
                        <label class="form-label">VAT Rate (%)</label>
                        <input type="number" name="vat_rate" class="form-control" step="0.01" required>
                    </div>
                </div>
                <div class="modal-footer">
                    <button type="submit" class="btn btn-success">Save</button>
                </div>
            </form>
        </div>
    </div>

    <!-- Edit Modal -->
    <div class="modal fade" id="editCompanyModal" tabindex="-1">
        <div class="modal-dialog">
            <form id="editCompanyForm" class="modal-content">
                <div class="modal-header">
                    <h5 class="modal-title">Edit Company</h5>
                    <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
                </div>
                <div class="modal-body row g-3">
                    <input type="hidden" name="id" id="edit-id">
                    <div class="col-12">
                        <label class="form-label">Company Name</label>
                        <input type="text" name="company_name" id="edit-company-name" class="form-control" required>
                    </div>
                    <div class="col-12">
                        <label class="form-label">VAT Rate (%)</label>
                        <input type="number" name="vat_rate" id="edit-vat-rate" class="form-control" step="0.01"
                            required>
                    </div>
                </div>
                <div class="modal-footer">
                    <button type="submit" class="btn btn-primary">Update</button>
                </div>
            </form>
        </div>
    </div>

    <script src="https://cdn.jsdelivr.net/npm/axios/dist/axios.min.js"></script>
    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.2/dist/js/bootstrap.bundle.min.js"></script>
    <script>
        // ADD
        document.getElementById('addCompanyForm').addEventListener('submit', function (e) {
            e.preventDefault();
            const formData = new FormData(this);
            formData.append('action', 'add');
            axios.post('backend.php', formData).then(res => {
                if (res.data.trim() === 'success') {
                    alert('✅ Company Added');
                    location.reload();
                } else {
                    alert('❌ Add failed');
                }
            });
        });

        // EDIT
        document.getElementById('editCompanyForm').addEventListener('submit', function (e) {
            e.preventDefault();
            const formData = new FormData(this);
            formData.append('action', 'edit');
            axios.post('backend.php', formData).then(res => {
                if (res.data.trim() === 'success') {
                    alert('✅ Company Updated');
                    location.reload();
                } else {
                    alert('❌ Update failed');
                }
            });
        });

        // FETCH FOR EDIT
        function openEditModal(id) {
            const formData = new FormData();
            formData.append('action', 'fetch');
            formData.append('id', id);
            axios.post('backend.php', formData).then(res => {
                const c = res.data;
                document.getElementById('edit-id').value = c.id;
                document.getElementById('edit-company-name').value = c.company_name;
                document.getElementById('edit-vat-rate').value = c.vat_rate;
                new bootstrap.Modal(document.getElementById('editCompanyModal')).show();
            });
        }

        // DELETE
        function deleteCompany(id) {
            if (confirm('Delete this company?')) {
                const formData = new FormData();
                formData.append('action', 'delete');
                formData.append('id', id);
                axios.post('backend.php', formData).then(res => {
                    if (res.data.trim() === 'success') {
                        alert('✅ Deleted');
                        location.reload();
                    } else {
                        alert('❌ Failed');
                    }
                });
            }
        }
    </script>
</body>

</html>