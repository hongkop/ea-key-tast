<?php
// index.php - Web interface for managing licenses
// This file handles web interface only, no API requests

// Enable SQLite database
define('DB_FILE', 'licenses.db');

// Initialize SQLite database
function initDatabase() {
    if (!file_exists(DB_FILE)) {
        $db = new SQLite3(DB_FILE);
        
        // Create licenses table
        $db->exec("CREATE TABLE IF NOT EXISTS licenses (
            id INTEGER PRIMARY KEY AUTOINCREMENT,
            license_key TEXT UNIQUE NOT NULL,
            customer_type TEXT DEFAULT 'User',
            device_id TEXT,
            status TEXT DEFAULT 'active',
            created_at DATETIME DEFAULT CURRENT_TIMESTAMP,
            activated_at DATETIME,
            expires_at DATETIME
        )");
        
        // Create default admin license
        $default_key = generateLicenseKey();
        $expires = date('Y-m-d H:i:s', strtotime('+365 days'));
        $stmt = $db->prepare("INSERT INTO licenses (license_key, customer_type, expires_at) VALUES (?, ?, ?)");
        $stmt->bindValue(1, $default_key, SQLITE3_TEXT);
        $stmt->bindValue(2, 'Admin', SQLITE3_TEXT);
        $stmt->bindValue(3, $expires, SQLITE3_TEXT);
        $stmt->execute();
        
        $db->close();
    }
}

// Generate random license key
function generateLicenseKey() {
    $chars = 'ABCDEFGHIJKLMNOPQRSTUVWXYZ0123456789';
    $key = '';
    for ($i = 0; $i < 16; $i++) {
        $key .= $chars[rand(0, strlen($chars) - 1)];
        if (($i + 1) % 4 == 0 && $i != 15) {
            $key .= '-';
        }
    }
    return $key;
}

// Initialize database for web interface
initDatabase();
$db = new SQLite3(DB_FILE);

// Handle web actions
if (isset($_GET['action'])) {
    $action = $_GET['action'];
    
    if ($action === 'create' && isset($_POST['customer_type'])) {
        createLicense($db);
    } elseif ($action === 'edit' && isset($_POST['license_key'])) {
        editLicense($db);
    } elseif ($action === 'delete' && isset($_GET['key'])) {
        deleteLicense($db, $_GET['key']);
    } elseif ($action === 'reset' && isset($_GET['key'])) {
        resetDevice($db, $_GET['key']);
    } elseif ($action === 'regenerate' && isset($_GET['key'])) {
        regenerateKey($db, $_GET['key']);
    }
}

// Create new license
function createLicense($db) {
    $customer_type = $_POST['customer_type'];
    $duration = isset($_POST['duration']) ? intval($_POST['duration']) : 365;
    $license_key = isset($_POST['license_key']) ? trim($_POST['license_key']) : generateLicenseKey();
    
    if (empty($license_key)) {
        $license_key = generateLicenseKey();
    }
    
    // Validate license key format (XXXX-XXXX-XXXX-XXXX)
    if (!preg_match('/^[A-Z0-9]{4}-[A-Z0-9]{4}-[A-Z0-9]{4}-[A-Z0-9]{4}$/', $license_key)) {
        header("Location: index.php?error=Invalid license key format. Use format: XXXX-XXXX-XXXX-XXXX");
        exit;
    }
    
    $expires = date('Y-m-d H:i:s', strtotime("+$duration days"));
    
    $stmt = $db->prepare("INSERT INTO licenses (license_key, customer_type, expires_at) VALUES (:key, :customer_type, :expires)");
    $stmt->bindValue(':key', $license_key, SQLITE3_TEXT);
    $stmt->bindValue(':customer_type', $customer_type, SQLITE3_TEXT);
    $stmt->bindValue(':expires', $expires, SQLITE3_TEXT);
    
    if ($stmt->execute()) {
        header("Location: index.php?success=License created: $license_key");
        exit;
    } else {
        header("Location: index.php?error=License key already exists");
        exit;
    }
}

// Edit existing license
function editLicense($db) {
    $license_key = $_POST['license_key'];
    $new_license_key = isset($_POST['new_license_key']) ? trim($_POST['new_license_key']) : $license_key;
    $customer_type = $_POST['customer_type'];
    $duration = isset($_POST['duration']) ? intval($_POST['duration']) : 365;
    $status = $_POST['status'];
    
    // If new key is provided, validate format
    if ($new_license_key !== $license_key && !empty($new_license_key)) {
        if (!preg_match('/^[A-Z0-9]{4}-[A-Z0-9]{4}-[A-Z0-9]{4}-[A-Z0-9]{4}$/', $new_license_key)) {
            header("Location: index.php?error=Invalid license key format. Use format: XXXX-XXXX-XXXX-XXXX");
            exit;
        }
    }
    
    $expires = date('Y-m-d H:i:s', strtotime("+$duration days"));
    
    if ($new_license_key !== $license_key) {
        // Update with new key
        $stmt = $db->prepare("UPDATE licenses SET license_key = :new_key, customer_type = :customer_type, status = :status, expires_at = :expires WHERE license_key = :old_key");
        $stmt->bindValue(':new_key', $new_license_key, SQLITE3_TEXT);
        $stmt->bindValue(':customer_type', $customer_type, SQLITE3_TEXT);
        $stmt->bindValue(':status', $status, SQLITE3_TEXT);
        $stmt->bindValue(':expires', $expires, SQLITE3_TEXT);
        $stmt->bindValue(':old_key', $license_key, SQLITE3_TEXT);
    } else {
        // Update without changing key
        $stmt = $db->prepare("UPDATE licenses SET customer_type = :customer_type, status = :status, expires_at = :expires WHERE license_key = :key");
        $stmt->bindValue(':customer_type', $customer_type, SQLITE3_TEXT);
        $stmt->bindValue(':status', $status, SQLITE3_TEXT);
        $stmt->bindValue(':expires', $expires, SQLITE3_TEXT);
        $stmt->bindValue(':key', $license_key, SQLITE3_TEXT);
    }
    
    if ($stmt->execute()) {
        header("Location: index.php?success=License updated successfully");
        exit;
    } else {
        header("Location: index.php?error=Failed to update license");
        exit;
    }
}

// Delete license
function deleteLicense($db, $key) {
    $stmt = $db->prepare("DELETE FROM licenses WHERE license_key = :key");
    $stmt->bindValue(':key', $key, SQLITE3_TEXT);
    $stmt->execute();
    header("Location: index.php?success=License deleted");
    exit;
}

// Reset device binding
function resetDevice($db, $key) {
    $stmt = $db->prepare("UPDATE licenses SET device_id = NULL, activated_at = NULL WHERE license_key = :key");
    $stmt->bindValue(':key', $key, SQLITE3_TEXT);
    $stmt->execute();
    header("Location: index.php?success=Device binding reset");
    exit;
}

// Regenerate license key
function regenerateKey($db, $key) {
    $new_key = generateLicenseKey();
    $stmt = $db->prepare("UPDATE licenses SET license_key = :new_key WHERE license_key = :old_key");
    $stmt->bindValue(':new_key', $new_key, SQLITE3_TEXT);
    $stmt->bindValue(':old_key', $key, SQLITE3_TEXT);
    
    if ($stmt->execute()) {
        header("Location: index.php?success=License key regenerated: $new_key");
        exit;
    } else {
        header("Location: index.php?error=Failed to regenerate key");
        exit;
    }
}
?>
<!DOCTYPE html>
<html>
<head>
    <title>MT5 License Manager - Admin Panel</title>
    <style>
        body { font-family: Arial, sans-serif; margin: 20px; background: #f5f5f5; }
        .container { max-width: 1000px; margin: 0 auto; background: white; padding: 20px; border-radius: 10px; box-shadow: 0 0 10px rgba(0,0,0,0.1); }
        .header { background: #2c3e50; color: white; padding: 20px; border-radius: 5px; margin-bottom: 20px; }
        .tab { display: inline-block; padding: 10px 20px; background: #3498db; color: white; margin-right: 5px; cursor: pointer; border-radius: 5px; }
        .tab.active { background: #2980b9; }
        .tab-content { display: none; padding: 20px; border: 1px solid #ddd; border-radius: 5px; margin-top: 10px; }
        .tab-content.active { display: block; }
        input, select { width: 100%; padding: 10px; margin: 5px 0; border: 1px solid #ddd; border-radius: 5px; }
        button { background: #27ae60; color: white; border: none; padding: 10px 20px; border-radius: 5px; cursor: pointer; }
        button:hover { background: #219653; }
        .btn-danger { background: #e74c3c; }
        .btn-danger:hover { background: #c0392b; }
        .btn-warning { background: #f39c12; }
        .btn-warning:hover { background: #d35400; }
        .btn-secondary { background: #95a5a6; }
        .btn-secondary:hover { background: #7f8c8d; }
        .btn-small { padding: 5px 10px; font-size: 12px; }
        table { width: 100%; border-collapse: collapse; margin-top: 20px; }
        th, td { padding: 10px; text-align: left; border-bottom: 1px solid #ddd; }
        th { background: #f8f9fa; }
        .success { background: #d4edda; color: #155724; padding: 10px; border-radius: 5px; margin: 10px 0; }
        .error { background: #f8d7da; color: #721c24; padding: 10px; border-radius: 5px; margin: 10px 0; }
        .license-key { font-family: monospace; background: #f8f9fa; padding: 10px; border-radius: 5px; margin: 10px 0; }
        .form-inline { display: flex; gap: 10px; align-items: flex-end; }
        .form-group { flex: 1; }
        .edit-form { background: #f8f9fa; padding: 15px; border-radius: 5px; margin: 10px 0; border-left: 4px solid #3498db; }
        .actions { display: flex; gap: 5px; }
        .api-info { background: #e8f4f8; padding: 15px; border-radius: 5px; margin: 20px 0; border-left: 4px solid #3498db; }
        .stats { display: grid; grid-template-columns: repeat(3, 1fr); gap: 15px; margin: 20px 0; }
        .stat-box { background: #f8f9fa; padding: 15px; border-radius: 5px; text-align: center; }
        .stat-number { font-size: 24px; font-weight: bold; color: #2c3e50; }
        .stat-label { font-size: 12px; color: #7f8c8d; margin-top: 5px; }
    </style>
</head>
<body>
    <div class="container">
        <div class="header">
            <h1>MT5 EA License Manager - Admin Panel</h1>
            <p>Manage licenses for your Expert Advisor</p>
        </div>
        
        <?php if (isset($_GET['success'])): ?>
            <div class="success">✅ <?php echo htmlspecialchars($_GET['success']); ?></div>
        <?php endif; ?>
        
        <?php if (isset($_GET['error'])): ?>
            <div class="error">❌ <?php echo htmlspecialchars($_GET['error']); ?></div>
        <?php endif; ?>
        
        <!-- Statistics -->
        <div class="stats">
            <?php
            $total = $db->querySingle("SELECT COUNT(*) FROM licenses");
            $active = $db->querySingle("SELECT COUNT(*) FROM licenses WHERE status = 'active' AND device_id IS NOT NULL");
            $available = $db->querySingle("SELECT COUNT(*) FROM licenses WHERE status = 'active' AND device_id IS NULL");
            ?>
            <div class="stat-box">
                <div class="stat-number"><?php echo $total; ?></div>
                <div class="stat-label">Total Licenses</div>
            </div>
            <div class="stat-box">
                <div class="stat-number"><?php echo $active; ?></div>
                <div class="stat-label">Active Devices</div>
            </div>
            <div class="stat-box">
                <div class="stat-number"><?php echo $available; ?></div>
                <div class="stat-label">Available Licenses</div>
            </div>
        </div>
        
        <!-- API Information -->
        <div class="api-info">
            <h3>🔌 API Endpoint for MT5 EA:</h3>
            <p><strong>URL:</strong> <code><?php echo $_SERVER['HTTP_HOST'] . dirname($_SERVER['PHP_SELF']) . '/login.php'; ?></code></p>
            <p><strong>Method:</strong> POST</p>
            <p><strong>Parameters:</strong> <code>action=check_license&key=LICENSE_KEY&device_id=DEVICE_ID</code></p>
        </div>
        
        <div class="tabs">
            <div class="tab active" onclick="showTab('create')">➕ Create License</div>
            <div class="tab" onclick="showTab('view')">👁️ View/Edit Licenses</div>
        </div>
        
        <!-- Create License Tab -->
        <div id="create" class="tab-content active">
            <h2>Create New License</h2>
            <form method="POST" action="index.php?action=create">
                <div class="form-inline">
                    <div class="form-group">
                        <label>License Key:</label>
                        <input type="text" name="license_key" id="license_key" placeholder="Leave empty for random" pattern="[A-Z0-9]{4}-[A-Z0-9]{4}-[A-Z0-9]{4}-[A-Z0-9]{4}">
                    </div>
                    <div class="form-group">
                        <button type="button" class="btn-secondary" onclick="generateRandomKey()">Generate Random Key</button>
                    </div>
                </div>
                
                <div class="form-group">
                    <label>Customer Type:</label>
                    <select name="customer_type" required>
                        <option value="User">User</option>
                        <option value="Admin">Admin</option>
                        <option value="Trial">Trial</option>
                        <option value="VIP">VIP</option>
                    </select>
                </div>
                
                <div class="form-group">
                    <label>Duration:</label>
                    <select name="duration">
                        <option value="7">7 days</option>
                        <option value="30">30 days</option>
                        <option value="90">90 days</option>
                        <option value="180">180 days</option>
                        <option value="365" selected>1 year</option>
                        <option value="730">2 years</option>
                        <option value="9999">Lifetime</option>
                    </select>
                </div>
                
                <button type="submit">Create License</button>
            </form>
        </div>
        
        <!-- View/Edit Licenses Tab -->
        <div id="view" class="tab-content">
            <h2>Current Licenses</h2>
            
            <?php
            // Check if editing a specific license
            $edit_key = $_GET['edit'] ?? '';
            if ($edit_key) {
                $stmt = $db->prepare("SELECT * FROM licenses WHERE license_key = :key");
                $stmt->bindValue(':key', $edit_key, SQLITE3_TEXT);
                $license = $stmt->execute()->fetchArray(SQLITE3_ASSOC);
                
                if ($license) {
                    // Calculate remaining days
                    $expires_date = new DateTime($license['expires_at']);
                    $now = new DateTime();
                    $interval = $now->diff($expires_date);
                    $days_remaining = $interval->days;
                    ?>
                    <div class="edit-form">
                        <h3>Edit License: <?php echo htmlspecialchars($license['license_key']); ?></h3>
                        <form method="POST" action="index.php?action=edit">
                            <input type="hidden" name="license_key" value="<?php echo htmlspecialchars($license['license_key']); ?>">
                            
                            <div class="form-group">
                                <label>New License Key (leave unchanged to keep current):</label>
                                <input type="text" name="new_license_key" value="<?php echo htmlspecialchars($license['license_key']); ?>" pattern="[A-Z0-9]{4}-[A-Z0-9]{4}-[A-Z0-9]{4}-[A-Z0-9]{4}">
                            </div>
                            
                            <div class="form-group">
                                <label>Customer Type:</label>
                                <select name="customer_type">
                                    <option value="User" <?php echo $license['customer_type'] == 'User' ? 'selected' : ''; ?>>User</option>
                                    <option value="Admin" <?php echo $license['customer_type'] == 'Admin' ? 'selected' : ''; ?>>Admin</option>
                                    <option value="Trial" <?php echo $license['customer_type'] == 'Trial' ? 'selected' : ''; ?>>Trial</option>
                                    <option value="VIP" <?php echo $license['customer_type'] == 'VIP' ? 'selected' : ''; ?>>VIP</option>
                                </select>
                            </div>
                            
                            <div class="form-group">
                                <label>Status:</label>
                                <select name="status">
                                    <option value="active" <?php echo $license['status'] == 'active' ? 'selected' : ''; ?>>Active</option>
                                    <option value="expired" <?php echo $license['status'] == 'expired' ? 'selected' : ''; ?>>Expired</option>
                                    <option value="revoked" <?php echo $license['status'] == 'revoked' ? 'selected' : ''; ?>>Revoked</option>
                                </select>
                            </div>
                            
                            <div class="form-group">
                                <label>Extend License by:</label>
                                <select name="duration">
                                    <option value="7">7 days</option>
                                    <option value="30">30 days</option>
                                    <option value="90">90 days</option>
                                    <option value="180">180 days</option>
                                    <option value="365" selected>1 year</option>
                                    <option value="730">2 years</option>
                                </select>
                                <small>Current expiration: <?php echo $license['expires_at']; ?> (<?php echo $days_remaining; ?> days remaining)</small>
                            </div>
                            
                            <div class="actions">
                                <button type="submit" class="btn">Save Changes</button>
                                <a href="index.php" class="btn-secondary">Cancel</a>
                            </div>
                        </form>
                    </div>
                    <?php
                }
            }
            ?>
            
            <table>
                <tr>
                    <th>License Key</th>
                    <th>Customer Type</th>
                    <th>Device ID</th>
                    <th>Created</th>
                    <th>Expires</th>
                    <th>Status</th>
                    <th>Actions</th>
                </tr>
                <?php
                $result = $db->query("SELECT * FROM licenses ORDER BY created_at DESC");
                while ($row = $result->fetchArray(SQLITE3_ASSOC)) {
                    $status_color = '';
                    if ($row['status'] == 'active') {
                        $status_color = empty($row['device_id']) ? 'color:#3498db;' : 'color:green;';
                    } else {
                        $status_color = 'color:red;';
                    }
                    
                    // Calculate days remaining
                    $expires_date = new DateTime($row['expires_at']);
                    $now = new DateTime();
                    $interval = $now->diff($expires_date);
                    $days_remaining = $interval->days;
                    $is_expired = $interval->invert == 1;
                    
                    echo "<tr>";
                    echo "<td><code>" . htmlspecialchars($row['license_key']) . "</code></td>";
                    echo "<td>" . htmlspecialchars($row['customer_type']) . "</td>";
                    echo "<td><small>" . (empty($row['device_id']) ? 'Not activated' : substr($row['device_id'], 0, 12) . '...') . "</small></td>";
                    echo "<td>" . date('Y-m-d', strtotime($row['created_at'])) . "</td>";
                    echo "<td>" . date('Y-m-d', strtotime($row['expires_at'])) . 
                         "<br><small>(" . ($is_expired ? "Expired" : "$days_remaining days left") . ")</small></td>";
                    echo "<td style='$status_color font-weight:bold;'>" . $row['status'] . "</td>";
                    echo "<td class='actions'>";
                    echo "<a href='index.php?edit=" . urlencode($row['license_key']) . "' class='btn btn-small'>✏️ Edit</a>";
                    if (!empty($row['device_id'])) {
                        echo "<a href='index.php?action=reset&key=" . urlencode($row['license_key']) . "' onclick='return confirm(\"Reset device binding?\")' class='btn-warning btn-small'>🔄 Reset</a>";
                    }
                    echo "<a href='index.php?action=regenerate&key=" . urlencode($row['license_key']) . "' onclick='return confirm(\"Regenerate new key? Old key will be invalid!\")' class='btn-warning btn-small'>🔄 New Key</a>";
                    echo "<a href='index.php?action=delete&key=" . urlencode($row['license_key']) . "' onclick='return confirm(\"Delete this license permanently?\")' class='btn-danger btn-small'>🗑️ Delete</a>";
                    echo "</td>";
                    echo "</tr>";
                }
                ?>
            </table>
        </div>
    </div>
    
    <script>
        function showTab(tabId) {
            // Hide all tabs
            document.querySelectorAll('.tab-content').forEach(tab => {
                tab.classList.remove('active');
            });
            
            // Show selected tab
            document.getElementById(tabId).classList.add('active');
            
            // Update tab buttons
            document.querySelectorAll('.tab').forEach(tab => {
                tab.classList.remove('active');
            });
            event.target.classList.add('active');
        }
        
        function generateRandomKey() {
            const chars = 'ABCDEFGHIJKLMNOPQRSTUVWXYZ0123456789';
            let key = '';
            for (let i = 0; i < 16; i++) {
                key += chars.charAt(Math.floor(Math.random() * chars.length));
                if ((i + 1) % 4 === 0 && i !== 15) {
                    key += '-';
                }
            }
            document.getElementById('license_key').value = key;
        }
        
        // Generate random key on page load if field is empty
        window.onload = function() {
            const keyField = document.getElementById('license_key');
            if (keyField && keyField.value === '') {
                generateRandomKey();
            }
        };
    </script>
    
    <?php $db->close(); ?>
</body>
</html>
