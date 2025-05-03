<?php
error_reporting(E_ALL);
ini_set('display_errors', 1);

$cost = $_GET['cost'] ?? '';
$currency = $_GET['currency'] ?? 'ZAR';
$markup = $_GET['markup'] ?? '';
$interest = $_GET['interest'] ?? '';
$term = $_GET['term'] ?? '';

$result = [];

if (is_numeric($cost) && is_numeric($markup) && is_numeric($interest) && is_numeric($term) && $term > 0) {
    $cost = floatval($cost);
    $markup = floatval($markup);
    $interest = floatval($interest);
    $term = intval($term);

    $markupAmount = $cost * ($markup / 100);
    $baseTotal = $cost + $markupAmount;

    $monthlyInterestRate = $interest / 12 / 100;
    $totalWithInterest = $baseTotal * pow(1 + $monthlyInterestRate, $term);
    $monthlyPayment = $totalWithInterest / $term;

    $result = [
        'currency' => $currency,
        'cost' => $cost,
        'markup_percent' => $markup,
        'interest_annual' => $interest,
        'term_months' => $term,
        'base_total' => round($baseTotal, 2),
        'total_with_interest' => round($totalWithInterest, 2),
        'monthly_payment' => round($monthlyPayment, 2)
    ];
}
?>


<!DOCTYPE html>
<html lang="en">

<head>
    <meta charset="UTF-8">
    <title>Finance Calculator</title>

    <!-- Bootstrap 5 CSS -->
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.2/dist/css/bootstrap.min.css" rel="stylesheet">
</head>

<body style="background: #f9f9f9; padding: 20px;">

    <div class="my-4" style="width: 93%; margin: auto;">
        <h2 class="mb-4">Admin Service</h2>

        <!-- First Row of Buttons -->
        <div class="row g-4">
            <div class="col-md-3">
                <a href="supplier/supplier/supplier.php" class="btn btn-primary w-100 p-3">Manage Suppliers</a>
            </div>
            <div class="col-md-3">
                <a href="supplier/service-type/service-type.php" class="btn btn-success w-100 p-3">Manage Service
                    Types</a>
            </div>
            <div class="col-md-3">
                <a href="supplier/service-category/billing-service-category.php"
                    class="btn btn-warning w-100 p-3">Manage Service Categories</a>
            </div>
            <div class="col-md-3">
                <a href="supplier/unit-price/index.php" class="btn btn-danger w-100 p-3">Unit Prices</a>
            </div>
            <div class="col-md-3">
                <a href="manage_hosting_assets.php" class="btn btn-info w-100 p-3">Manage Hosting Assets</a>
            </div>
            <div class="col-md-3">
                <a href="supplier/invoice-company/index.php" class="btn btn-secondary w-100 p-3">Manage Invoice
                    Companies</a>
            </div>
        </div>
    </div>



    <div class=" my-5" style="width: 95%; margin: auto;">
        <div class="bg-white p-4 rounded shadow-sm">

            <h3 class="mb-4 text-dark">Finance Calculator</h3>

            <form method="get" class="row g-3">
                <div class="col-md-6">
                    <label class="form-label">Cost Price</label>
                    <input type="number" step="0.01" name="cost" class="form-control"
                        value="<?= htmlspecialchars($cost) ?>" required>
                </div>

                <div class="col-md-6">
                    <label class="form-label">Currency</label>
                    <select name="currency" class="form-select">
                        <option value="ZAR" <?= $currency === 'ZAR' ? 'selected' : '' ?>>ZAR</option>
                        <option value="USD" <?= $currency === 'USD' ? 'selected' : '' ?>>USD</option>
                    </select>
                </div>

                <div class="col-md-4">
                    <label class="form-label">Markup %</label>
                    <input type="number" step="0.01" name="markup" class="form-control"
                        value="<?= htmlspecialchars($markup) ?>" required>
                </div>

                <div class="col-md-4">
                    <label class="form-label">Annual Interest %</label>
                    <input type="number" step="0.01" name="interest" class="form-control"
                        value="<?= htmlspecialchars($interest) ?>" required>
                </div>

                <div class="col-md-4">
                    <label class="form-label">Term (Months)</label>
                    <input type="number" name="term" class="form-control" value="<?= htmlspecialchars($term) ?>"
                        required>
                </div>

                <div class="col-12 text-start mt-3">
                    <button type="submit" class="btn btn-primary">
                        Calculate
                    </button>
                </div>
            </form>

            <?php if ($result): ?>
                <hr class="my-4">
                <h3 class="text-success mb-4">📈 Results (<?= htmlspecialchars($result['currency']) ?>)</h3>
                <ul class="list-group">
                    <li class="list-group-item">
                        <strong>Cost Price:</strong> <?= htmlspecialchars($result['currency']) ?>
                        <?= number_format($result['cost'], 2) ?>
                    </li>
                    <li class="list-group-item">
                        <strong>Markup:</strong> <?= htmlspecialchars($result['markup_percent']) ?>%
                    </li>
                    <li class="list-group-item">
                        <strong>Interest (Annual):</strong> <?= htmlspecialchars($result['interest_annual']) ?>%
                    </li>
                    <li class="list-group-item">
                        <strong>Finance Term:</strong> <?= htmlspecialchars($result['term_months']) ?> months
                    </li>
                    <li class="list-group-item">
                        <strong>Total Without Interest:</strong> <?= htmlspecialchars($result['currency']) ?>
                        <?= number_format($result['base_total'], 2) ?>
                    </li>
                    <li class="list-group-item">
                        <strong>Total With Interest:</strong> <?= htmlspecialchars($result['currency']) ?>
                        <?= number_format($result['total_with_interest'], 2) ?>
                    </li>
                    <li class="list-group-item">
                        <strong>Monthly Payment:</strong> <?= htmlspecialchars($result['currency']) ?>
                        <?= number_format($result['monthly_payment'], 2) ?>
                    </li>
                </ul>
            <?php endif; ?>

        </div>
    </div>

    <!-- Bootstrap Bundle JS -->
    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.2/dist/js/bootstrap.bundle.min.js"></script>

</body>

</html>