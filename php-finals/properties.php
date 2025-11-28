<?php
session_start();
require_once 'config.php';
requireLogin();

$properties = readJsonFile(PROPERTIES_FILE);
$error = '';
$success = '';

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $action = $_POST['action'] ?? '';
    
    if ($action === 'add') {
        $name = sanitizeInput($_POST['name'] ?? '');
        $address = sanitizeInput($_POST['address'] ?? '');
        $type = sanitizeInput($_POST['type'] ?? '');
        $status = sanitizeInput($_POST['status'] ?? 'Vacant');
        
        if (empty($name) || empty($address) || empty($type)) {
            $error = 'Please fill in all fields';
        } else {
            $newProperty = [
                'id' => generateId(),
                'name' => $name,
                'address' => $address,
                'type' => $type,
                'status' => $status,
                'created_at' => date('Y-m-d H:i:s')
            ];
            
            $properties[] = $newProperty;
            writeJsonFile(PROPERTIES_FILE, $properties);
            $success = 'Property added successfully';
            $properties = readJsonFile(PROPERTIES_FILE);
        }
    } elseif ($action === 'delete') {
        $id = $_POST['id'] ?? '';
        $newProperties = [];
        
        foreach ($properties as $property) {
            if ($property['id'] !== $id) {
                $newProperties[] = $property;
            }
        }
        
        writeJsonFile(PROPERTIES_FILE, $newProperties);
        $success = 'Property deleted successfully';
        $properties = $newProperties;
    } elseif ($action === 'update') {
        $id = sanitizeInput($_POST['id'] ?? '');
        $name = sanitizeInput($_POST['name'] ?? '');
        $address = sanitizeInput($_POST['address'] ?? '');
        $type = sanitizeInput($_POST['type'] ?? '');
        $status = sanitizeInput($_POST['status'] ?? '');
        
        if (empty($name) || empty($address) || empty($type) || empty($status)) {
            $error = 'Please fill in all fields';
        } else {
            foreach ($properties as &$property) {
                if ($property['id'] === $id) {
                    $property['name'] = $name;
                    $property['address'] = $address;
                    $property['type'] = $type;
                    $property['status'] = $status;
                    break;
                }
            }
            writeJsonFile(PROPERTIES_FILE, $properties);
            $success = 'Property updated successfully';
        }
    }
}
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Properties - Rental Property Management</title>
    <link rel="stylesheet" href="style.css">
</head>
<body>
    <?php include 'includes/header.php'; ?>
    
    <div class="container">
        <div class="page-header">
            <h1>Property Management</h1>
            <button class="btn btn-primary" onclick="document.getElementById('addModal').style.display='block'">Add New Property</button>
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
                        <th>Property Name</th>
                        <th>Address</th>
                        <th>Type</th>
                        <th>Status</th>
                        <th>Actions</th>
                    </tr>
                </thead>
                <tbody>
                    <?php if (empty($properties)): ?>
                        <tr>
                            <td colspan="5" class="text-center">No properties found. Add your first property!</td>
                        </tr>
                    <?php else: ?>
                        <?php foreach ($properties as $property): ?>
                            <tr>
                                <td><?php echo htmlspecialchars($property['name']); ?></td>
                                <td><?php echo htmlspecialchars($property['address']); ?></td>
                                <td><?php echo htmlspecialchars($property['type']); ?></td>
                                <td>
                                    <span class="status-badge status-<?php echo strtolower(str_replace(' ', '-', $property['status'])); ?>">
                                        <?php echo htmlspecialchars($property['status']); ?>
                                    </span>
                                </td>
                                <td>
                                    <button class="btn btn-small btn-secondary" onclick="editProperty('<?php echo $property['id']; ?>', '<?php echo htmlspecialchars($property['name'], ENT_QUOTES); ?>', '<?php echo htmlspecialchars($property['address'], ENT_QUOTES); ?>', '<?php echo htmlspecialchars($property['type'], ENT_QUOTES); ?>', '<?php echo htmlspecialchars($property['status'], ENT_QUOTES); ?>')">Edit</button>
                                    <form method="POST" style="display:inline;" onsubmit="return confirm('Are you sure you want to delete this property?');">
                                        <input type="hidden" name="action" value="delete">
                                        <input type="hidden" name="id" value="<?php echo $property['id']; ?>">
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
            <h2>Add New Property</h2>
            <form method="POST">
                <input type="hidden" name="action" value="add">
                
                <div class="form-group">
                    <label for="name">Property Name *</label>
                    <input type="text" id="name" name="name" required>
                </div>
                
                <div class="form-group">
                    <label for="address">Address *</label>
                    <input type="text" id="address" name="address" required>
                </div>
                
                <div class="form-group">
                    <label for="type">Property Type *</label>
                    <select id="type" name="type" required>
                        <option value="">Select Type</option>
                        <option value="Apartment">Apartment</option>
                        <option value="House">House</option>
                        <option value="Condo">Condo</option>
                        <option value="Studio">Studio</option>
                        <option value="Room">Room</option>
                    </select>
                </div>
                
                <div class="form-group">
                    <label for="status">Status *</label>
                    <select id="status" name="status" required>
                        <option value="Vacant">Vacant</option>
                        <option value="Occupied">Occupied</option>
                        <option value="Under Maintenance">Under Maintenance</option>
                    </select>
                </div>
                
                <button type="submit" class="btn btn-primary">Add Property</button>
            </form>
        </div>
    </div>
    
    <div id="editModal" class="modal">
        <div class="modal-content">
            <span class="close" onclick="document.getElementById('editModal').style.display='none'">&times;</span>
            <h2>Edit Property</h2>
            <form method="POST">
                <input type="hidden" name="action" value="update">
                <input type="hidden" id="edit_id" name="id">
                
                <div class="form-group">
                    <label for="edit_name">Property Name *</label>
                    <input type="text" id="edit_name" name="name" required>
                </div>
                
                <div class="form-group">
                    <label for="edit_address">Address *</label>
                    <input type="text" id="edit_address" name="address" required>
                </div>
                
                <div class="form-group">
                    <label for="edit_type">Property Type *</label>
                    <select id="edit_type" name="type" required>
                        <option value="Apartment">Apartment</option>
                        <option value="House">House</option>
                        <option value="Condo">Condo</option>
                        <option value="Studio">Studio</option>
                        <option value="Room">Room</option>
                    </select>
                </div>
                
                <div class="form-group">
                    <label for="edit_status">Status *</label>
                    <select id="edit_status" name="status" required>
                        <option value="Vacant">Vacant</option>
                        <option value="Occupied">Occupied</option>
                        <option value="Under Maintenance">Under Maintenance</option>
                    </select>
                </div>
                
                <button type="submit" class="btn btn-primary">Update Property</button>
            </form>
        </div>
    </div>
    
    <script>
        function editProperty(id, name, address, type, status) {
            document.getElementById('edit_id').value = id;
            document.getElementById('edit_name').value = name;
            document.getElementById('edit_address').value = address;
            document.getElementById('edit_type').value = type;
            document.getElementById('edit_status').value = status;
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
