<?php
session_start();
require_once 'config.php';
requireLogin();

$tenants = readJsonFile(TENANTS_FILE);
$error = '';
$success = '';

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $action = $_POST['action'] ?? '';
    
    if ($action === 'update') {
        $id = sanitizeInput($_POST['id'] ?? '');
        $name = sanitizeInput($_POST['name'] ?? '');
        $phone = sanitizeInput($_POST['phone'] ?? '');
        $email = sanitizeInput($_POST['email'] ?? '');
        
        if (empty($name) || empty($phone) || empty($email)) {
            $error = 'Please fill in all fields';
        } elseif (!validateEmail($email)) {
            $error = 'Please enter a valid email address';
        } else {
            foreach ($tenants as &$tenant) {
                if ($tenant['id'] === $id) {
                    $tenant['name'] = $name;
                    $tenant['phone'] = $phone;
                    $tenant['email'] = $email;
                    break;
                }
            }
            writeJsonFile(TENANTS_FILE, $tenants);
            $success = 'Contact information updated successfully';
        }
    }
}

$searchQuery = $_GET['search'] ?? '';
$filteredTenants = $tenants;

if (!empty($searchQuery)) {
    $filteredTenants = array_filter($tenants, function($tenant) use ($searchQuery) {
        return stripos($tenant['name'], $searchQuery) !== false ||
               stripos($tenant['phone'], $searchQuery) !== false ||
               stripos($tenant['email'], $searchQuery) !== false;
    });
}
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Contact Directory - Rental Property Management</title>
    <link rel="stylesheet" href="style.css">
</head>
<body>
    <?php include 'includes/header.php'; ?>
    
    <div class="container">
        <div class="page-header">
            <h1>Tenant Contact Directory</h1>
            <form method="GET" class="search-form">
                <input type="text" name="search" placeholder="Search by name, phone, or email..." value="<?php echo htmlspecialchars($searchQuery); ?>">
                <button type="submit" class="btn btn-primary">Search</button>
                <?php if (!empty($searchQuery)): ?>
                    <a href="contact_directory.php" class="btn btn-secondary">Clear</a>
                <?php endif; ?>
            </form>
        </div>
        
        <?php if ($error): ?>
            <div class="alert alert-error"><?php echo $error; ?></div>
        <?php endif; ?>
        
        <?php if ($success): ?>
            <div class="alert alert-success"><?php echo $success; ?></div>
        <?php endif; ?>
        
        <?php if (!empty($searchQuery) && empty($filteredTenants)): ?>
            <div class="alert alert-info">No tenants found matching "<?php echo htmlspecialchars($searchQuery); ?>"</div>
        <?php endif; ?>
        
        <div class="contact-grid">
            <?php if (empty($filteredTenants)): ?>
                <div class="no-data">
                    <p>No tenants found in the directory.</p>
                    <a href="tenants.php" class="btn btn-primary">Add Tenant</a>
                </div>
            <?php else: ?>
                <?php foreach ($filteredTenants as $tenant): ?>
                    <div class="contact-card">
                        <div class="contact-header">
                            <div class="contact-avatar"><?php echo strtoupper(substr($tenant['name'], 0, 1)); ?></div>
                            <h3><?php echo htmlspecialchars($tenant['name']); ?></h3>
                        </div>
                        <div class="contact-info">
                            <div class="contact-item">
                                <strong>Phone:</strong>
                                <a href="tel:<?php echo htmlspecialchars($tenant['phone']); ?>"><?php echo htmlspecialchars($tenant['phone']); ?></a>
                            </div>
                            <div class="contact-item">
                                <strong>Email:</strong>
                                <a href="mailto:<?php echo htmlspecialchars($tenant['email']); ?>"><?php echo htmlspecialchars($tenant['email']); ?></a>
                            </div>
                            <?php if (isset($tenant['monthly_rent'])): ?>
                                <div class="contact-item">
                                    <strong>Monthly Rent:</strong>
                                    ₱<?php echo number_format($tenant['monthly_rent'], 2); ?>
                                </div>
                            <?php endif; ?>
                        </div>
                        <div class="contact-actions">
                            <button class="btn btn-small btn-secondary" onclick="editContact('<?php echo $tenant['id']; ?>', '<?php echo htmlspecialchars($tenant['name'], ENT_QUOTES); ?>', '<?php echo htmlspecialchars($tenant['phone'], ENT_QUOTES); ?>', '<?php echo htmlspecialchars($tenant['email'], ENT_QUOTES); ?>')">Edit Contact</button>
                        </div>
                    </div>
                <?php endforeach; ?>
            <?php endif; ?>
        </div>
    </div>
    
    <div id="editModal" class="modal">
        <div class="modal-content">
            <span class="close" onclick="document.getElementById('editModal').style.display='none'">&times;</span>
            <h2>Edit Contact Information</h2>
            <form method="POST">
                <input type="hidden" name="action" value="update">
                <input type="hidden" id="edit_id" name="id">
                
                <div class="form-group">
                    <label for="edit_name">Full Name *</label>
                    <input type="text" id="edit_name" name="name" required>
                </div>
                
                <div class="form-group">
                    <label for="edit_phone">Phone Number *</label>
                    <input type="tel" id="edit_phone" name="phone" required>
                </div>
                
                <div class="form-group">
                    <label for="edit_email">Email Address *</label>
                    <input type="email" id="edit_email" name="email" required>
                </div>
                
                <button type="submit" class="btn btn-primary">Update Contact</button>
            </form>
        </div>
    </div>
    
    <script>
        function editContact(id, name, phone, email) {
            document.getElementById('edit_id').value = id;
            document.getElementById('edit_name').value = name;
            document.getElementById('edit_phone').value = phone;
            document.getElementById('edit_email').value = email;
            document.getElementById('editModal').style.display = 'block';
        }
        
        window.onclick = function(event) {
            if (event.target.className === 'modal') {
                event.target.style.display = 'none';
            }
        }
    </script>
</body>
</html>
