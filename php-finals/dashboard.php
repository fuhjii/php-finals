<?php
session_start();
require_once 'config.php';
requireLogin();

$tenants = readJsonFile(TENANTS_FILE);
$properties = readJsonFile(PROPERTIES_FILE);
$payments = readJsonFile(PAYMENTS_FILE);

$totalProperties = count($properties);
$occupied = 0;
$vacant = 0;
$maintenance = 0;

foreach ($properties as $property) {
    switch ($property['status']) {
        case 'Occupied':
            $occupied++;
            break;
        case 'Vacant':
            $vacant++;
            break;
        case 'Under Maintenance':
            $maintenance++;
            break;
    }
}

$currentMonth = date('Y-m');
$paidThisMonth = 0;
$unpaidThisMonth = 0;
$lateThisMonth = 0;

foreach ($payments as $payment) {
    if ($payment['month'] === $currentMonth) {
        switch ($payment['status']) {
            case 'Paid':
                $paidThisMonth++;
                break;
            case 'Unpaid':
                $unpaidThisMonth++;
                break;
            case 'Late':
                $lateThisMonth++;
                break;
        }
    }
}
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Dashboard - Rental Property Management</title>
    <link rel="stylesheet" href="style.css">
</head>
<body>
    <?php include 'includes/header.php'; ?>
    
    <div class="container">
        <h1>Dashboard</h1>
        
        <div class="stats-grid">
            <div class="stat-card">
                <h3>Total Properties</h3>
                <div class="stat-number"><?php echo $totalProperties; ?></div>
            </div>
            
            <div class="stat-card stat-occupied">
                <h3>Occupied Units</h3>
                <div class="stat-number"><?php echo $occupied; ?></div>
            </div>
            
            <div class="stat-card stat-vacant">
                <h3>Vacant Units</h3>
                <div class="stat-number"><?php echo $vacant; ?></div>
            </div>
            
            <div class="stat-card stat-maintenance">
                <h3>Under Maintenance</h3>
                <div class="stat-number"><?php echo $maintenance; ?></div>
            </div>
        </div>
        
        <div class="stats-grid mt-30">
            <div class="stat-card stat-success">
                <h3>Paid This Month</h3>
                <div class="stat-number"><?php echo $paidThisMonth; ?></div>
            </div>
            
            <div class="stat-card stat-warning">
                <h3>Late Payments</h3>
                <div class="stat-number"><?php echo $lateThisMonth; ?></div>
            </div>
            
            <div class="stat-card stat-danger">
                <h3>Unpaid This Month</h3>
                <div class="stat-number"><?php echo $unpaidThisMonth; ?></div>
            </div>
            
            <div class="stat-card">
                <h3>Total Tenants</h3>
                <div class="stat-number"><?php echo count($tenants); ?></div>
            </div>
        </div>
        
        <div class="quick-links mt-30">
            <h2>Quick Actions</h2>
            <div class="button-group">
                <a href="tenants.php" class="btn btn-primary">Manage Tenants</a>
                <a href="properties.php" class="btn btn-primary">Manage Properties</a>
                <a href="payments.php" class="btn btn-primary">Track Payments</a>
                <a href="contact_directory.php" class="btn btn-primary">Contact Directory</a>
            </div>
        </div>
    </div>
</body>
</html>
