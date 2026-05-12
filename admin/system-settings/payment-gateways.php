<?php
// admin/system-settings/payment-gateways.php
require_once __DIR__ . '/../includes/admin_functions.php';

$message = '';

// Add/Edit gateway
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['save_gateway'])) {
    $id = $_POST['gateway_id'] ? (int)$_POST['gateway_id'] : null;
    $name = trim($_POST['name']);
    $type = $_POST['gateway_type'];
    $provider = trim($_POST['provider']);
    $merchant_id = trim($_POST['merchant_id']);
    $api_key = trim($_POST['api_key']);
    $api_secret = trim($_POST['api_secret']);
    $is_active = isset($_POST['is_active']) ? 1 : 0;
    $test_mode = isset($_POST['test_mode']) ? 1 : 0;

    if ($id) {
        $stmt = $conn->prepare("UPDATE payment_gateways SET name=?, gateway_type=?, provider=?, merchant_id=?, api_key=?, api_secret=?, is_active=?, test_mode=? WHERE id=?");
        $stmt->execute([$name, $type, $provider, $merchant_id, $api_key, $api_secret, $is_active, $test_mode, $id]);
    } else {
        $stmt = $conn->prepare("INSERT INTO payment_gateways (name, gateway_type, provider, merchant_id, api_key, api_secret, is_active, test_mode) VALUES (?,?,?,?,?,?,?,?)");
        $stmt->execute([$name, $type, $provider, $merchant_id, $api_key, $api_secret, $is_active, $test_mode]);
    }
    $message = '<div class="alert alert-success">Gateway saved.</div>';
}

// Delete
if (isset($_GET['delete'])) {
    $delId = (int)$_GET['delete'];
    $conn->prepare("DELETE FROM payment_gateways WHERE id = ?")->execute([$delId]);
    header("Location: payment-gateways.php?msg=deleted");
    exit;
}

// Fetch gateways
$gateways = $conn->query("SELECT * FROM payment_gateways ORDER BY name")->fetchAll(PDO::FETCH_ASSOC);
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <title>Payment Gateways | CFCI Admin</title>
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.5.1/css/all.min.css">
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.2/dist/css/bootstrap.min.css" rel="stylesheet">
    <link href="https://fonts.googleapis.com/css2?family=Inter:wght@300;400;500;600;700&display=swap" rel="stylesheet">
    <style>
        body { font-family: 'Inter', sans-serif; background: #f4f6f9; }
        .admin-layout { display: flex; min-height: 100vh; }
        .admin-main { flex: 1; margin-left: 260px; padding: 1.5rem 2rem; }
        .card { background: #fff; border-radius: 14px; box-shadow: 0 4px 12px rgba(0,0,0,0.04); border: none; }
        .badge-status { padding: 0.2em 0.8em; border-radius: 30px; font-size: 0.75rem; }
    </style>
</head>
<body>
<div class="admin-layout">
    <?php include __DIR__ . '/../includes/admin_sidebar.php'; ?>
    <main class="admin-main">
        <?php include __DIR__ . '/../includes/admin_topbar.php'; ?>
        <h4 class="fw-bold mb-4">Payment Gateways</h4>
        <?= $message ?>

        <!-- Add/Edit Form -->
        <div class="card p-4 mb-4">
            <h5 class="fw-semibold mb-3" id="formTitle">Add Gateway</h5>
            <form method="post" id="gatewayForm">
                <input type="hidden" name="gateway_id" id="gateway_id" value="">
                <div class="row g-3">
                    <div class="col-md-6">
                        <label class="form-label">Name</label>
                        <input type="text" name="name" id="g_name" class="form-control" required>
                    </div>
                    <div class="col-md-3">
                        <label class="form-label">Type</label>
                        <select name="gateway_type" id="g_type" class="form-select">
                            <option value="mobile_money">Mobile Money</option>
                            <option value="card">Card</option>
                            <option value="bank">Bank</option>
                            <option value="wallet">Wallet</option>
                        </select>
                    </div>
                    <div class="col-md-3">
                        <label class="form-label">Provider</label>
                        <input type="text" name="provider" id="g_provider" class="form-control" required>
                    </div>
                    <div class="col-md-4">
                        <label class="form-label">Merchant ID</label>
                        <input type="text" name="merchant_id" id="g_merchant" class="form-control">
                    </div>
                    <div class="col-md-4">
                        <label class="form-label">API Key</label>
                        <input type="text" name="api_key" id="g_apikey" class="form-control">
                    </div>
                    <div class="col-md-4">
                        <label class="form-label">API Secret</label>
                        <input type="text" name="api_secret" id="g_apisecret" class="form-control">
                    </div>
                    <div class="col-md-6 d-flex align-items-end gap-4">
                        <div class="form-check form-switch">
                            <input class="form-check-input" type="checkbox" id="g_active" name="is_active" checked>
                            <label class="form-check-label" for="g_active">Active</label>
                        </div>
                        <div class="form-check form-switch">
                            <input class="form-check-input" type="checkbox" id="g_test" name="test_mode">
                            <label class="form-check-label" for="g_test">Test Mode</label>
                        </div>
                    </div>
                </div>
                <div class="mt-3 d-flex gap-2">
                    <button type="submit" name="save_gateway" class="btn btn-primary"><i class="fas fa-save me-1"></i> Save Gateway</button>
                    <button type="button" class="btn btn-outline-secondary" onclick="clearForm()">Clear</button>
                </div>
            </form>
        </div>

        <!-- Gateway List -->
        <div class="card p-3">
            <div class="table-responsive">
                <table class="table align-middle">
                    <thead>
                        <tr>
                            <th>Name</th>
                            <th>Type</th>
                            <th>Provider</th>
                            <th>Status</th>
                            <th>Test Mode</th>
                            <th class="text-end">Actions</th>
                        </tr>
                    </thead>
                    <tbody>
                        <?php foreach ($gateways as $gw): ?>
                        <tr>
                            <td class="fw-semibold"><?= htmlspecialchars($gw['name']) ?></td>
                            <td><?= $gw['gateway_type'] ?></td>
                            <td><?= htmlspecialchars($gw['provider']) ?></td>
                            <td>
                                <?php if ($gw['is_active']): ?>
                                    <span class="badge-status bg-success bg-opacity-10 text-success">Active</span>
                                <?php else: ?>
                                    <span class="badge-status bg-secondary bg-opacity-10 text-secondary">Inactive</span>
                                <?php endif; ?>
                            </td>
                            <td><?= $gw['test_mode'] ? 'Yes' : 'No' ?></td>
                            <td class="text-end">
                                <button class="btn btn-outline-secondary btn-sm" onclick="editGateway(<?= htmlspecialchars(json_encode($gw)) ?>)"><i class="fas fa-edit"></i></button>
                                <a href="?delete=<?= $gw['id'] ?>" class="btn btn-outline-danger btn-sm" onclick="return confirm('Delete gateway?')"><i class="fas fa-trash"></i></a>
                            </td>
                        </tr>
                        <?php endforeach; ?>
                        <?php if (empty($gateways)): ?>
                        <tr><td colspan="6" class="text-center text-muted py-4">No gateways configured.</td></tr>
                        <?php endif; ?>
                    </tbody>
                </table>
            </div>
        </div>
    </main>
</div>
<script>
function editGateway(gw) {
    document.getElementById('formTitle').textContent = 'Edit Gateway';
    document.getElementById('gateway_id').value = gw.id;
    document.getElementById('g_name').value = gw.name;
    document.getElementById('g_type').value = gw.gateway_type;
    document.getElementById('g_provider').value = gw.provider;
    document.getElementById('g_merchant').value = gw.merchant_id || '';
    document.getElementById('g_apikey').value = gw.api_key || '';
    document.getElementById('g_apisecret').value = gw.api_secret || '';
    document.getElementById('g_active').checked = gw.is_active == 1;
    document.getElementById('g_test').checked = gw.test_mode == 1;
    window.scrollTo({ top: 0, behavior: 'smooth' });
}
function clearForm() {
    document.getElementById('formTitle').textContent = 'Add Gateway';
    document.getElementById('gateway_id').value = '';
    ['g_name','g_provider','g_merchant','g_apikey','g_apisecret'].forEach(id => document.getElementById(id).value = '');
    document.getElementById('g_type').value = 'mobile_money';
    document.getElementById('g_active').checked = true;
    document.getElementById('g_test').checked = false;
}
</script>
<script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.2/dist/js/bootstrap.bundle.min.js"></script>
</body>
</html>