<!DOCTYPE html>
<html>

<head>
  <meta charset="UTF-8">
  <title>Billing Report</title>
  <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.2/dist/css/bootstrap.min.css">
</head>

<body>

  <div class="">
    <div style="width:93%; margin: auto; ">
      <!-- Export -->
      <div class="mt-5 mb-5">
        <div class="d-flex align-items-center mb-3">
            <?php include('../components/Backbtn.php') ?>
            <!-- Left-aligned Title -->
            <h3 class="mb-2 d-flex align-items-center">
              <i class="bi bi-people-fill me-2 text-secondary" style="font-size: 1.5rem;"></i>
              <span class="fw-semibold text-dark">Billing Report</span>
            </h3>
        </div>
      </div>
    </div>
    <!-- Filters -->
    <form class="card shadow-sm p-4 mb-4" style="width:93%; margin: auto; " method="GET">
      <div class="row g-3 align-items-end">

        <div class="col-md-3">
          <label class="form-label">Client</label>
          <select name="client_id" class="form-select">
            <option value="">All Clients</option>
            <?php foreach ($clients as $client): ?>
              <option value="<?= $client['id'] ?>" <?= isset($_GET['client_id']) && $_GET['client_id'] == $client['id'] ? 'selected' : '' ?>>
                <?= htmlspecialchars($client['client_name']) ?>
              </option>
            <?php endforeach; ?>
          </select>
        </div>

        <div class="col-md-3">
          <label class="form-label">Invoice Type</label>
          <select name="invoice_type" class="form-select">
            <option value="">All</option>
            <option value="debit" <?= ($_GET['invoice_type'] ?? '') === 'debit' ? 'selected' : '' ?>>Debit</option>
            <option value="invoice" <?= ($_GET['invoice_type'] ?? '') === 'invoice' ? 'selected' : '' ?>>Invoice</option>
          </select>
        </div>

        <div class="col-md-3">
          <label class="form-label">Currency</label>
          <select name="currency" class="form-select">
            <option value="">All</option>
            <option value="USD" <?= ($_GET['currency'] ?? '') === 'NAD' ? 'selected' : '' ?>>NAD</option>
            <option value="PKR" <?= ($_GET['currency'] ?? '') === 'ZAR' ? 'selected' : '' ?>>Zar</option>
            <option value="PKR" <?= ($_GET['currency'] ?? '') === 'ZAR' ? 'selected' : '' ?>>USD</option>
          </select>
        </div>

        <div class="col-md-3">
          <label class="form-label">Frequency</label>
          <select name="frequency" class="form-select">
            <option value="">All</option>
            <option value="once_off" <?= ($_GET['frequency'] ?? '') === 'once_off' ? 'selected' : '' ?>>Once Off</option>
            <option value="monthly" <?= ($_GET['frequency'] ?? '') === 'monthly' ? 'selected' : '' ?>>Monthly</option>
            <option value="annually" <?= ($_GET['frequency'] ?? '') === 'annually' ? 'selected' : '' ?>>Annually</option>
            <option value="finance" <?= ($_GET['frequency'] ?? '') === 'finance' ? 'selected' : '' ?>>Finance</option>
          </select>
        </div>

        <div class="col-12 text-end">
          <button type="submit" class="btn btn-primary">Apply Filters</button>
          <a href="reports-billing.php" class="btn btn-secondary">Reset</a>
        </div>
      </div>
    </form>
  </div>

</body>

</html>