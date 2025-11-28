<?php
session_start();
require_once 'config.php';
requireLogin();

$tenants = readJsonFile(TENANTS_FILE);
$properties = readJsonFile(PROPERTIES_FILE);
$error = '';
$success = '';

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $action = $_POST['action'] ?? '';
    
    if ($action === 'add') {
        $name = sanitizeInput($_POST['name'] ?? '');
        $phone = sanitizeInput($_POST['phone'] ?? '');
        $email = sanitizeInput($_POST['email'] ?? '');
        $property_id = sanitizeInput($_POST['property_id'] ?? '');
        $monthly_rent = sanitizeInput($_POST['monthly_rent'] ?? '');
        
        if (empty($name) || empty($phone) || empty($email) || empty($property_id) || empty($monthly_rent)) {
            $error = 'Please fill in all fields';
        } elseif (!validateEmail($email)) {
            $error = 'Please enter a valid email address';
        } elseif (!validateNumeric($monthly_rent)) {
            $error = 'Please enter a valid rent amount';
        } else {
            $newTenant = [
                'id' => generateId(),
                'name' => $name,
                'phone' => $phone,
                'email' => $email,
                'property_id' => $property_id,
                'monthly_rent' => floatval($monthly_rent),
                'created_at' => date('Y-m-d H:i:s')
            ];
            
            $tenants[] = $newTenant;
            writeJsonFile(TENANTS_FILE, $tenants);
            
            foreach ($properties as &$property) {
                if ($property['id'] === $property_id) {
                    $property['status'] = 'Occupied';
                    $property['tenant_id'] = $newTenant['id'];
                    break;
                }
            }
            writeJsonFile(PROPERTIES_FILE, $properties);
            
            $success = 'Tenant added successfully';
            $tenants = readJsonFile(TENANTS_FILE);
        }
    } elseif ($action === 'delete') {
        $id = $_POST['id'] ?? '';
        $newTenants = [];
        $deletedTenant = null;
        
        foreach ($tenants as $tenant) {
            if ($tenant['id'] !== $id) {
                $newTenants[] = $tenant;
            } else {
                $deletedTenant = $tenant;
            }
        }
        
        writeJsonFile(TENANTS_FILE, $newTenants);
        
        if ($deletedTenant) {
            foreach ($properties as &$property) {
                if (isset($property['tenant_id']) && $property['tenant_id'] === $id) {
                    $property['status'] = 'Vacant';
                    unset($property['tenant_id']);
                    break;
                }
            }
            writeJsonFile(PROPERTIES_FILE, $properties);
        }
        
        $success = 'Tenant deleted successfully';
        $tenants = $newTenants;
    } elseif ($action === 'update') {
        $id = sanitizeInput($_POST['id'] ?? '');
        $name = sanitizeInput($_POST['name'] ?? '');
        $phone = sanitizeInput($_POST['phone'] ?? '');
        $email = sanitizeInput($_POST['email'] ?? '');
        $monthly_rent = sanitizeInput($_POST['monthly_rent'] ?? '');
        
        if (empty($name) || empty($phone) || empty($email) || empty($monthly_rent)) {
            $error = 'Please fill in all fields';
        } elseif (!validateEmail($email)) {
            $error = 'Please enter a valid email address';
        } elseif (!validateNumeric($monthly_rent)) {
            $error = 'Please enter a valid rent amount';
        } else {
            foreach ($tenants as &$tenant) {
                if ($tenant['id'] === $id) {
                    $tenant['name'] = $name;
                    $tenant['phone'] = $phone;
                    $tenant['email'] = $email;
                    $tenant['monthly_rent'] = floatval($monthly_rent);
                    break;
                }
            }
            writeJsonFile(TENANTS_FILE, $tenants);
            $success = 'Tenant updated successfully';
        }
    }
}

$properties = readJsonFile(PROPERTIES_FILE);
$vacantProperties = array_filter($properties, function($p) {
    return $p['status'] === 'Vacant';
});
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Tenants - Rental Property Management</title>
    <link rel="stylesheet" href="style.css">
</head>
<body>
    <?php include 'includes/header.php'; ?>
    
    <div class="container">
        <div class="page-header">
            <h1>Tenant Management</h1>
            <button class="btn btn-primary" onclick="document.getElementById('addModal').style.display='block'">Add New Tenant</button>
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
                        <th>Name</th>
                        <th>Phone</th>
                        <th>Email</th>
                        <th>Property</th>
                        <th>Monthly Rent</th>
                        <th>Actions</th>
                    </tr>
                </thead>
                <tbody>
                    <?php if (empty($tenants)): ?>
                        <tr>
                            <td colspan="6" class="text-center">No tenants found. Add your first tenant!</td>
                        </tr>
                    <?php else: ?>
                        <?php foreach ($tenants as $tenant): ?>
                            <?php
                            $propertyName = 'N/A';
                            foreach ($properties as $prop) {
                                if ($prop['id'] === $tenant['property_id']) {
                                    $propertyName = $prop['name'];
                                    break;
                                }
                            }
                            ?>
                            <tr>
                                <td><?php echo htmlspecialchars($tenant['name']); ?></td>
                                <td><?php echo htmlspecialchars($tenant['phone']); ?></td>
                                <td><?php echo htmlspecialchars($tenant['email']); ?></td>
                                <td><?php echo htmlspecialchars($propertyName); ?></td>
                                <td>₱<?php echo number_format($tenant['monthly_rent'], 2); ?></td>
                                <td>
                                    <button class="btn btn-small btn-secondary" onclick="editTenant('<?php echo $tenant['id']; ?>', '<?php echo htmlspecialchars($tenant['name'], ENT_QUOTES); ?>', '<?php echo htmlspecialchars($tenant['phone'], ENT_QUOTES); ?>', '<?php echo htmlspecialchars($tenant['email'], ENT_QUOTES); ?>', '<?php echo $tenant['monthly_rent']; ?>')">Edit</button>
                                    <form method="POST" style="display:inline;" onsubmit="return confirm('Are you sure you want to delete this tenant?');">
                                        <input type="hidden" name="action" value="delete">
                                        <input type="hidden" name="id" value="<?php echo $tenant['id']; ?>">
                                        <button type="submit" class="btn btn-small btn-danger">Delete</button>
                                    </form>
                                </td>
                            </tr>
                        <?php endforeach; ?>
                    <?php endif; ?>
                </tbody>
            </table>
        </div>
    </div>
    
    <div id="addModal" class="modal">
        <div class="modal-content">
            <span class="close" onclick="document.getElementById('addModal').style.display='none'">&times;</span>
            <h2>Add New Tenant</h2>
            <form method="POST">
                <input type="hidden" name="action" value="add">
                
                <div class="form-group">
                    <label for="name">Full Name *</label>
                    <input type="text" id="name" name="name" required>
                </div>
                
                <div class="form-group">
                    <label for="phone">Phone Number *</label>
                    <input type="tel" id="phone" name="phone" required>
                </div>
                
                <div class="form-group">
                    <label for="email">Email Address *</label>
                    <input type="email" id="email" name="email" required>
                </div>
                
                <div class="form-group">
                    <label for="property_id">Property *</label>
                    <select id="property_id" name="property_id" required>
                        <option value="">Select Property</option>
                        <?php foreach ($vacantProperties as $property): ?>
                            <option value="<?php echo $property['id']; ?>"><?php echo htmlspecialchars($property['name']); ?></option>
                        <?php endforeach; ?>
                    </select>
                </div>
                
                <div class="form-group">
                    <label for="monthly_rent">Monthly Rent *</label>
                    <input type="number" id="monthly_rent" name="monthly_rent" step="0.01" required>
                </div>
                
                <button type="submit" class="btn btn-primary">Add Tenant</button>
            </form>
        </div>
    </div>
    
    <div id="editModal" class="modal">
        <div class="modal-content">
            <span class="close" onclick="document.getElementById('editModal').style.display='none'">&times;</span>
            <h2>Edit Tenant</h2>
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
                
                <div class="form-group">
                    <label for="edit_monthly_rent">Monthly Rent *</label>
                    <input type="number" id="edit_monthly_rent" name="monthly_rent" step="0.01" required>
                </div>
                
                <button type="submit" class="btn btn-primary">Update Tenant</button>
            </form>
        </div>
    </div>
    
    <script>
        function editTenant(id, name, phone, email, rent) {
            document.getElementById('edit_id').value = id;
            document.getElementById('edit_name').value = name;
            document.getElementById('edit_phone').value = phone;
            document.getElementById('edit_email').value = email;
            document.getElementById('edit_monthly_rent').value = rent;
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
