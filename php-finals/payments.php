<?php
session_start();
require_once 'config.php';
requireLogin();

$payments = readJsonFile(PAYMENTS_FILE);
$tenants = readJsonFile(TENANTS_FILE);
$error = '';
$success = '';

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $action = $_POST['action'] ?? '';
    
    if ($action === 'add') {
        $tenant_id = sanitizeInput($_POST['tenant_id'] ?? '');
        $month = sanitizeInput($_POST['month'] ?? '');
        $amount = sanitizeInput($_POST['amount'] ?? '');
        $status = sanitizeInput($_POST['status'] ?? 'Unpaid');
        
        if (empty($tenant_id) || empty($month) || empty($amount)) {
            $error = 'Please fill in all fields';
        } elseif (!validateNumeric($amount)) {
            $error = 'Please enter a valid payment amount';
        } elseif (!validateMonth($month)) {
            $error = 'Please enter a valid month in YYYY-MM format';
        } else {
            $newPayment = [
                'id' => generateId(),
                'tenant_id' => $tenant_id,
                'month' => $month,
                'amount' => floatval($amount),
                'status' => $status,
                'created_at' => date('Y-m-d H:i:s')
            ];
            
            $payments[] = $newPayment;
            writeJsonFile(PAYMENTS_FILE, $payments);
            $success = 'Payment record added successfully';
            $payments = readJsonFile(PAYMENTS_FILE);
        }
    } elseif ($action === 'update_status') {
        $id = sanitizeInput($_POST['id'] ?? '');
        $status = sanitizeInput($_POST['status'] ?? '');
        
        foreach ($payments as &$payment) {
            if ($payment['id'] === $id) {
                $payment['status'] = $status;
                $payment['updated_at'] = date('Y-m-d H:i:s');
                break;
            }
        }
        writeJsonFile(PAYMENTS_FILE, $payments);
        $success = 'Payment status updated successfully';
    } elseif ($action === 'delete') {
        $id = $_POST['id'] ?? '';
        $newPayments = [];
        
        foreach ($payments as $payment) {
            if ($payment['id'] !== $id) {
                $newPayments[] = $payment;
            }
        }
        
        writeJsonFile(PAYMENTS_FILE, $newPayments);
        $success = 'Payment record deleted successfully';
        $payments = $newPayments;
    }
}

usort($payments, function($a, $b) {
    return strcmp($b['month'], $a['month']);
});
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Payments - Rental Property Management</title>
    <link rel="stylesheet" href="style.css">
</head>
<body>
    <?php include 'includes/header.php'; ?>
    
    <div class="container">
        <div class="page-header">
            <h1>Payment Tracking</h1>
            <button class="btn btn-primary" onclick="document.getElementById('addModal').style.display='block'">Add Payment Record</button>
        </div>
        
        <?php if ($error): ?>
            <div class="alert alert-error"><?php echo $error; ?></div>
        <?php endif; ?>
        
        <?php if ($success): ?>
            <div class="alert alert-success"><?php echo $success; ?></div>
        <?php endif; ?>
        
        <div class="table-container">
            <table>
                <thead>
                    <tr>
                        <th>Tenant Name</th>
                        <th>Month</th>
                        <th>Amount</th>
                        <th>Status</th>
                        <th>Actions</th>
                    </tr>
                </thead>
                <tbody>
                    <?php if (empty($payments)): ?>
                        <tr>
                            <td colspan="5" class="text-center">No payment records found. Add your first payment record!</td>
                        </tr>
                    <?php else: ?>
                        <?php foreach ($payments as $payment): ?>
                            <?php
                            $tenantName = 'Unknown';
                            foreach ($tenants as $tenant) {
                                if ($tenant['id'] === $payment['tenant_id']) {
                                    $tenantName = $tenant['name'];
                                    break;
                                }
                            }
                            ?>
                            <tr>
                                <td><?php echo htmlspecialchars($tenantName); ?></td>
                                <td><?php echo date('F Y', strtotime($payment['month'] . '-01')); ?></td>
                                <td>₱<?php echo number_format($payment['amount'], 2); ?></td>
                                <td>
                                    <form method="POST" style="display:inline;">
                                        <input type="hidden" name="action" value="update_status">
                                        <input type="hidden" name="id" value="<?php echo $payment['id']; ?>">
                                        <select name="status" onchange="this.form.submit()" class="status-select status-<?php echo strtolower($payment['status']); ?>">
                                            <option value="Paid" <?php echo $payment['status'] === 'Paid' ? 'selected' : ''; ?>>Paid</option>
                                            <option value="Late" <?php echo $payment['status'] === 'Late' ? 'selected' : ''; ?>>Late</option>
                                            <option value="Unpaid" <?php echo $payment['status'] === 'Unpaid' ? 'selected' : ''; ?>>Unpaid</option>
                                        </select>
                                    </form>
                                </td>
                                <td>
                                    <form method="POST" style="display:inline;" onsubmit="return confirm('Are you sure you want to delete this payment record?');">
                                        <input type="hidden" name="action" value="delete">
                                        <input type="hidden" name="id" value="<?php echo $payment['id']; ?>">
                                        <button type="submit" class="btn btn-small btn-danger">Delete</button>
                                    </form>
                                </td>
                            </tr>
                        <?php endforeach; ?>
                    <?php endif; ?>
                </tbody>
            </table>
        </div>
        
        <div class="payment-summary mt-30">
            <h2>Payment Summary by Tenant</h2>
            <div class="table-container">
                <table>
                    <thead>
                        <tr>
                            <th>Tenant Name</th>
                            <th>Total Records</th>
                            <th>Paid</th>
                            <th>Late</th>
                            <th>Unpaid</th>
                        </tr>
                    </thead>
                    <tbody>
                        <?php
                        $tenantSummary = [];
                        foreach ($payments as $payment) {
                            $tid = $payment['tenant_id'];
                            if (!isset($tenantSummary[$tid])) {
                                $tenantSummary[$tid] = [
                                    'total' => 0,
                                    'paid' => 0,
                                    'late' => 0,
                                    'unpaid' => 0
                                ];
                            }
                            $tenantSummary[$tid]['total']++;
                            $tenantSummary[$tid][strtolower($payment['status'])]++;
                        }
                        
                        foreach ($tenantSummary as $tid => $summary):
                            $tenantName = 'Unknown';
                            foreach ($tenants as $tenant) {
                                if ($tenant['id'] === $tid) {
                                    $tenantName = $tenant['name'];
                                    break;
                                }
                            }
                        ?>
                            <tr>
                                <td><?php echo htmlspecialchars($tenantName); ?></td>
                                <td><?php echo $summary['total']; ?></td>
                                <td class="text-success"><?php echo $summary['paid']; ?></td>
                                <td class="text-warning"><?php echo $summary['late']; ?></td>
                                <td class="text-danger"><?php echo $summary['unpaid']; ?></td>
                            </tr>
                        <?php endforeach; ?>
                    </tbody>
                </table>
            </div>
        </div>
    </div>
    
    <div id="addModal" class="modal">
        <div class="modal-content">
            <span class="close" onclick="document.getElementById('addModal').style.display='none'">&times;</span>
            <h2>Add Payment Record</h2>
            <form method="POST">
                <input type="hidden" name="action" value="add">
                
                <div class="form-group">
                    <label for="tenant_id">Tenant *</label>
                    <select id="tenant_id" name="tenant_id" required>
                        <option value="">Select Tenant</option>
                        <?php foreach ($tenants as $tenant): ?>
                            <option value="<?php echo $tenant['id']; ?>"><?php echo htmlspecialchars($tenant['name']); ?></option>
                        <?php endforeach; ?>
                    </select>
                </div>
                
                <div class="form-group">
                    <label for="month">Month *</label>
                    <input type="month" id="month" name="month" required value="<?php echo date('Y-m'); ?>">
                </div>
                
                <div class="form-group">
                    <label for="amount">Amount *</label>
                    <input type="number" id="amount" name="amount" step="0.01" required>
                </div>
                
                <div class="form-group">
                    <label for="status">Status *</label>
                    <select id="status" name="status" required>
                        <option value="Unpaid">Unpaid</option>
                        <option value="Paid">Paid</option>
                        <option value="Late">Late</option>
                    </select>
                </div>
                
                <button type="submit" class="btn btn-primary">Add Payment Record</button>
            </form>
        </div>
    </div>
    
    <script>
        window.onclick = function(event) {
            if (event.target.className === 'modal') {
                event.target.style.display = 'none';
            }
        }
    </script>
</body>
</html>
