<?php
// index.php - Web interface for managing licenses with login protection
session_start();

// Enable SQLite database
define('DB_FILE', 'licenses.db');
define('ADMIN_USERNAME', '11112222');
define('ADMIN_PASSWORD', '11112222'); // Change this to a strong password!

// Check if user is logged in
function isLoggedIn() {
    return isset($_SESSION['loggedin']) && $_SESSION['loggedin'] === true;
}

// Handle login
if (isset($_POST['login'])) {
    $username = $_POST['username'] ?? '';
    $password = $_POST['password'] ?? '';
    
    if ($username === ADMIN_USERNAME && $password === ADMIN_PASSWORD) {
        $_SESSION['loggedin'] = true;
        $_SESSION['username'] = $username;
        header("Location: licenses.php");
        exit;
    } else {
        $login_error = "Invalid username or password!";
    }
}

// Handle logout
if (isset($_GET['logout'])) {
    session_destroy();
    header("Location: licenses.php");
    exit;
}

// If not logged in, show login page
if (!isLoggedIn()) {
    showLoginPage();
    exit;
}

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
        header("Location: licenses.php?error=Invalid license key format. Use format: XXXX-XXXX-XXXX-XXXX");
        exit;
    }
    
    $expires = date('Y-m-d H:i:s', strtotime("+$duration days"));
    
    $stmt = $db->prepare("INSERT INTO licenses (license_key, customer_type, expires_at) VALUES (:key, :customer_type, :expires)");
    $stmt->bindValue(':key', $license_key, SQLITE3_TEXT);
    $stmt->bindValue(':customer_type', $customer_type, SQLITE3_TEXT);
    $stmt->bindValue(':expires', $expires, SQLITE3_TEXT);
    
    if ($stmt->execute()) {
        header("Location: licenses.php?success=License created: $license_key");
        exit;
    } else {
        header("Location: licenses.php?error=License key already exists");
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
            header("Location: licenses.php?error=Invalid license key format. Use format: XXXX-XXXX-XXXX-XXXX");
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
        header("Location: licenses.php?success=License updated successfully");
        exit;
    } else {
        header("Location: licenses.php?error=Failed to update license");
        exit;
    }
}

// Delete license
function deleteLicense($db, $key) {
    $stmt = $db->prepare("DELETE FROM licenses WHERE license_key = :key");
    $stmt->bindValue(':key', $key, SQLITE3_TEXT);
    $stmt->execute();
    header("Location: licenses.php?success=License deleted");
    exit;
}

// Reset device binding
function resetDevice($db, $key) {
    $stmt = $db->prepare("UPDATE licenses SET device_id = NULL, activated_at = NULL WHERE license_key = :key");
    $stmt->bindValue(':key', $key, SQLITE3_TEXT);
    $stmt->execute();
    header("Location: licenses.php?success=Device binding reset");
    exit;
}

// Regenerate license key
function regenerateKey($db, $key) {
    $new_key = generateLicenseKey();
    $stmt = $db->prepare("UPDATE licenses SET license_key = :new_key WHERE license_key = :old_key");
    $stmt->bindValue(':new_key', $new_key, SQLITE3_TEXT);
    $stmt->bindValue(':old_key', $key, SQLITE3_TEXT);
    
    if ($stmt->execute()) {
        header("Location: licenses.php?success=License key regenerated: $new_key");
        exit;
    } else {
        header("Location: licenses.php?error=Failed to regenerate key");
        exit;
    }
}

// Show login page
function showLoginPage() {
    global $login_error;
    ?>
    <!DOCTYPE html>
    <html>
    <head>
        <title>MT5 License Manager - Login</title>
        <style>
            * { margin: 0; padding: 0; box-sizing: border-box; }
            body { 
                font-family: 'Segoe UI', Tahoma, Geneva, Verdana, sans-serif; 
                background: linear-gradient(135deg, #667eea 0%, #764ba2 100%);
                min-height: 100vh;
                display: flex;
                align-items: center;
                justify-content: center;
                padding: 20px;
            }
            .login-container {
                background: white;
                border-radius: 15px;
                box-shadow: 0 20px 60px rgba(0,0,0,0.3);
                overflow: hidden;
                width: 100%;
                max-width: 400px;
            }
            .login-header {
                background: #2c3e50;
                color: white;
                padding: 30px;
                text-align: center;
            }
            .login-header h1 {
                font-size: 24px;
                margin-bottom: 10px;
            }
            .login-header p {
                color: #ecf0f1;
                font-size: 14px;
                opacity: 0.9;
            }
            .login-form {
                padding: 30px;
            }
            .form-group {
                margin-bottom: 20px;
            }
            .form-group label {
                display: block;
                margin-bottom: 8px;
                font-weight: 600;
                color: #2c3e50;
            }
            .form-group input {
                width: 100%;
                padding: 12px 15px;
                border: 2px solid #ddd;
                border-radius: 8px;
                font-size: 16px;
                transition: border-color 0.3s;
            }
            .form-group input:focus {
                border-color: #3498db;
                outline: none;
            }
            .btn-login {
                background: linear-gradient(135deg, #667eea 0%, #764ba2 100%);
                color: white;
                border: none;
                padding: 15px;
                border-radius: 8px;
                font-size: 16px;
                font-weight: 600;
                cursor: pointer;
                width: 100%;
                transition: transform 0.3s;
            }
            .btn-login:hover {
                transform: translateY(-2px);
            }
            .error-message {
                background: #f8d7da;
                color: #721c24;
                padding: 12px;
                border-radius: 8px;
                margin-bottom: 20px;
                text-align: center;
                border-left: 4px solid #e74c3c;
            }
            .login-footer {
                text-align: center;
                margin-top: 20px;
                padding-top: 20px;
                border-top: 1px solid #eee;
                color: #7f8c8d;
                font-size: 14px;
            }
            .logo {
                text-align: center;
                margin-bottom: 20px;
            }
            .logo img {
                width: 80px;
                height: 80px;
                background: linear-gradient(135deg, #667eea 0%, #764ba2 100%);
                border-radius: 50%;
                padding: 15px;
                margin-bottom: 10px;
            }
        </style>
    </head>
    <body>
        <div class="login-container">
            <div class="login-header">
                <div class="logo">
                    <div style="width: 80px; height: 80px; background: linear-gradient(135deg, #667eea 0%, #764ba2 100%); border-radius: 50%; display: inline-flex; align-items: center; justify-content: center; margin-bottom: 10px;">
                        <span style="color: white; font-size: 32px;">🔒</span>
                    </div>
                </div>
                <h1>MT5 License Manager</h1>
                <p>Admin Panel Login</p>
            </div>
            
            <div class="login-form">
                <?php if (isset($login_error)): ?>
                    <div class="error-message">❌ <?php echo $login_error; ?></div>
                <?php endif; ?>
                
                <form method="POST" action="">
                    <div class="form-group">
                        <label for="username">Username:</label>
                        <input type="text" id="username" name="username" placeholder="Enter admin username" required>
                    </div>
                    
                    <div class="form-group">
                        <label for="password">Password:</label>
                        <input type="password" id="password" name="password" placeholder="Enter your password" required>
                    </div>
                    
                    <button type="submit" name="login" class="btn-login">Login to Dashboard</button>
                </form>
                
                <div class="login-footer">
                    
                    <p style="font-size: 12px; margin-top: 10px; color: #e74c3c;">⚠️ Telegram : ZEAHONGMOD </p>
                </div>
            </div>
        </div>
        
        <script>
            // Add focus effect to inputs
            document.querySelectorAll('input').forEach(input => {
                input.addEventListener('focus', function() {
                    this.parentElement.style.transform = 'translateY(-2px)';
                });
                input.addEventListener('blur', function() {
                    this.parentElement.style.transform = 'translateY(0)';
                });
            });
        </script>
    </body>
    </html>
    <?php
}

// Show admin panel (only if logged in)
function showAdminPanel($db) {
    ?>
    <!DOCTYPE html>
    <html>
    <head>
        <title>MT5 License Manager - Admin Panel</title>
        <style>
            * { margin: 0; padding: 0; box-sizing: border-box; }
            body { font-family: 'Segoe UI', Tahoma, Geneva, Verdana, sans-serif; background: #f5f5f5; }
            .container { max-width: 1200px; margin: 0 auto; padding: 20px; }
            
            /* Header Styles */
            .header { 
                background: linear-gradient(135deg, #2c3e50 0%, #1a252f 100%);
                color: white; 
                padding: 20px; 
                border-radius: 10px; 
                margin-bottom: 20px;
                display: flex;
                justify-content: space-between;
                align-items: center;
                box-shadow: 0 4px 20px rgba(0,0,0,0.1);
            }
            .header-left h1 { margin-bottom: 5px; font-size: 24px; }
            .header-left p { color: #ecf0f1; opacity: 0.9; font-size: 14px; }
            .user-info { 
                background: rgba(255,255,255,0.1); 
                padding: 10px 20px; 
                border-radius: 8px;
                display: flex;
                align-items: center;
                gap: 10px;
            }
            .user-info span { font-weight: 600; }
            .btn-logout { 
                background: #e74c3c; 
                color: white; 
                border: none; 
                padding: 8px 16px; 
                border-radius: 6px; 
                cursor: pointer;
                font-size: 14px;
                transition: background 0.3s;
            }
            .btn-logout:hover { background: #c0392b; }
            
            /* Stats Styles */
            .stats { 
                display: grid; 
                grid-template-columns: repeat(auto-fit, minmax(200px, 1fr)); 
                gap: 20px; 
                margin: 20px 0; 
            }
            .stat-box { 
                background: white; 
                padding: 20px; 
                border-radius: 10px; 
                text-align: center;
                box-shadow: 0 2px 10px rgba(0,0,0,0.08);
                border-top: 4px solid #3498db;
            }
            .stat-box:nth-child(2) { border-top-color: #2ecc71; }
            .stat-box:nth-child(3) { border-top-color: #f39c12; }
            .stat-number { 
                font-size: 32px; 
                font-weight: bold; 
                color: #2c3e50; 
                margin: 10px 0; 
            }
            .stat-label { 
                font-size: 14px; 
                color: #7f8c8d; 
                text-transform: uppercase;
                letter-spacing: 1px;
            }
            
            /* Tab Styles */
            .tabs { 
                background: white; 
                border-radius: 10px; 
                padding: 5px;
                margin: 20px 0;
                display: flex;
                flex-wrap: wrap;
                box-shadow: 0 2px 10px rgba(0,0,0,0.08);
            }
            .tab { 
                padding: 15px 30px; 
                background: transparent; 
                color: #7f8c8d; 
                margin-right: 5px; 
                cursor: pointer; 
                border-radius: 8px;
                font-weight: 600;
                transition: all 0.3s;
                border: none;
                font-size: 16px;
            }
            .tab.active { 
                background: #3498db; 
                color: white; 
                box-shadow: 0 4px 15px rgba(52, 152, 219, 0.3);
            }
            .tab:hover:not(.active) { 
                background: #f8f9fa; 
                color: #3498db; 
            }
            
            /* Tab Content Styles */
            .tab-content { 
                display: none; 
                padding: 30px; 
                background: white; 
                border-radius: 10px; 
                margin-top: 10px;
                box-shadow: 0 2px 20px rgba(0,0,0,0.08);
            }
            .tab-content.active { display: block; }
            
            /* Form Styles */
            input, select { 
                width: 100%; 
                padding: 12px 15px; 
                margin: 8px 0; 
                border: 2px solid #e0e0e0; 
                border-radius: 8px; 
                font-size: 16px;
                transition: border-color 0.3s;
            }
            input:focus, select:focus { 
                border-color: #3498db; 
                outline: none;
                box-shadow: 0 0 0 3px rgba(52, 152, 219, 0.1);
            }
            
            /* Button Styles */
            button { 
                background: linear-gradient(135deg, #2ecc71 0%, #27ae60 100%); 
                color: white; 
                border: none; 
                padding: 12px 24px; 
                border-radius: 8px; 
                cursor: pointer; 
                font-size: 16px;
                font-weight: 600;
                transition: all 0.3s;
            }
            button:hover { 
                transform: translateY(-2px);
                box-shadow: 0 6px 20px rgba(46, 204, 113, 0.3);
            }
            .btn-danger { 
                background: linear-gradient(135deg, #e74c3c 0%, #c0392b 100%); 
            }
            .btn-danger:hover { 
                box-shadow: 0 6px 20px rgba(231, 76, 60, 0.3);
            }
            .btn-warning { 
                background: linear-gradient(135deg, #f39c12 0%, #d35400 100%); 
            }
            .btn-warning:hover { 
                box-shadow: 0 6px 20px rgba(243, 156, 18, 0.3);
            }
            .btn-secondary { 
                background: linear-gradient(135deg, #95a5a6 0%, #7f8c8d 100%); 
            }
            .btn-secondary:hover { 
                box-shadow: 0 6px 20px rgba(149, 165, 166, 0.3);
            }
            .btn-small { padding: 6px 12px; font-size: 14px; }
            
            /* Table Styles */
            table { 
                width: 100%; 
                border-collapse: separate; 
                border-spacing: 0;
                margin-top: 20px;
                border-radius: 10px;
                overflow: hidden;
                box-shadow: 0 2px 15px rgba(0,0,0,0.05);
            }
            th, td { 
                padding: 15px; 
                text-align: left; 
                border-bottom: 1px solid #eee; 
            }
            th { 
                background: #f8f9fa; 
                font-weight: 600; 
                color: #2c3e50;
                text-transform: uppercase;
                font-size: 14px;
                letter-spacing: 0.5px;
            }
            tr:hover { background: #f8f9fa; }
            tr:last-child td { border-bottom: none; }
            
            /* Status Colors */
            .status-active { color: #27ae60; font-weight: 600; }
            .status-expired { color: #e74c3c; font-weight: 600; }
            .status-available { color: #3498db; font-weight: 600; }
            
            /* Message Styles */
            .success { 
                background: #d4edda; 
                color: #155724; 
                padding: 15px; 
                border-radius: 8px; 
                margin: 15px 0; 
                border-left: 4px solid #27ae60;
            }
            .error { 
                background: #f8d7da; 
                color: #721c24; 
                padding: 15px; 
                border-radius: 8px; 
                margin: 15px 0;
                border-left: 4px solid #e74c3c;
            }
            
            /* License Key Styles */
            .license-key { 
                font-family: 'Courier New', monospace; 
                background: #f8f9fa; 
                padding: 12px; 
                border-radius: 6px; 
                margin: 10px 0;
                border: 1px dashed #ddd;
                font-weight: 600;
                letter-spacing: 1px;
            }
            
            /* Form Layout */
            .form-inline { 
                display: flex; 
                gap: 15px; 
                align-items: flex-end; 
                margin-bottom: 20px;
            }
            .form-group { flex: 1; }
            .edit-form { 
                background: #f8f9fa; 
                padding: 25px; 
                border-radius: 10px; 
                margin: 20px 0; 
                border-left: 5px solid #3498db;
            }
            
            /* Actions */
            .actions { display: flex; gap: 8px; }
            
            /* API Info */
            .api-info { 
                background: linear-gradient(135deg, #f8f9fa 0%, #e8f4f8 100%); 
                padding: 25px; 
                border-radius: 10px; 
                margin: 25px 0; 
                border-left: 5px solid #3498db;
            }
            .api-info h3 { margin-bottom: 15px; color: #2c3e50; }
            .api-info code { 
                background: rgba(0,0,0,0.05); 
                padding: 3px 6px; 
                border-radius: 4px; 
                font-family: monospace; 
            }
            
            /* Responsive */
            @media (max-width: 768px) {
                .container { padding: 10px; }
                .header { flex-direction: column; text-align: center; gap: 15px; }
                .user-info { width: 100%; justify-content: center; }
                .stats { grid-template-columns: 1fr; }
                .tabs { flex-direction: column; }
                .tab { margin-right: 0; margin-bottom: 5px; }
                .form-inline { flex-direction: column; }
                .actions { flex-wrap: wrap; }
                table { display: block; overflow-x: auto; }
            }
        </style>
    </head>
    <body>
        <div class="container">
            <!-- Header -->
            <div class="header">
                <div class="header-left">
                    <h1>MT5 License Manager Dashboard</h1>
                    <p>Manage your Expert Advisor licenses securely</p>
                </div>
                <div class="user-info">
                    <span>👤 <?php echo htmlspecialchars($_SESSION['username']); ?></span>
                    <a href="?logout" class="btn-logout">Logout</a>
                </div>
            </div>
            
            <!-- Success/Error Messages -->
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
                <p><strong>URL:</strong> <code><?php echo 'https://' . $_SERVER['HTTP_HOST'] . dirname($_SERVER['PHP_SELF']) . '/login.php'; ?></code></p>
                <p><strong>Method:</strong> POST</p>
                <p><strong>Parameters:</strong> <code>action=check_license&key=LICENSE_KEY&device_id=DEVICE_ID</code></p>
                <p><strong>Responses:</strong> VALID, DEVICE_LIMIT_EXCEEDED, INVALID, EXPIRED</p>
            </div>
            
            <!-- Tabs -->
            <div class="tabs">
                <button class="tab active" onclick="showTab('create')">➕ Create License</button>
                <button class="tab" onclick="showTab('view')">👁️ View/Edit Licenses</button>
            </div>
            
            <!-- Create License Tab -->
            <div id="create" class="tab-content active">
                <h2>Create New License Key</h2>
                <form method="POST" action="licenses.php?action=create">
                    <div class="form-inline">
                        <div class="form-group">
                            <label>License Key:</label>
                            <input type="text" name="license_key" id="license_key" placeholder="Leave empty for random" pattern="[A-Z0-9]{4}-[A-Z0-9]{4}-[A-Z0-9]{4}-[A-Z0-9]{4}">
                        </div>
                        <div class="form-group">
                            <button type="button" class="btn-secondary" onclick="generateRandomKey()">🎲 Generate Random Key</button>
                        </div>
                    </div>
                    
                    <div class="form-group">
                        <label>Customer Type:</label>
                        <select name="customer_type" required>
                            <option value="User">User</option>
                            <option value="Admin">Admin</option>
                            <option value="Trial">Trial (7 days)</option>
                            <option value="VIP">VIP User</option>
                        </select>
                    </div>
                    
                    <div class="form-group">
                        <label>License Duration:</label>
                        <select name="duration">
                            <option value="7">7 days (Trial)</option>
                            <option value="30">30 days</option>
                            <option value="90">90 days</option>
                            <option value="180">180 days</option>
                            <option value="365" selected>1 year</option>
                            <option value="730">2 years</option>
                            <option value="9999">Lifetime</option>
                        </select>
                    </div>
                    
                    <button type="submit" class="btn">✅ Create License</button>
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
                        $is_expired = $interval->invert == 1;
                        ?>
                        <div class="edit-form">
                            <h3>✏️ Edit License: <?php echo htmlspecialchars($license['license_key']); ?></h3>
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
                                    <small style="display: block; margin-top: 5px; color: #7f8c8d;">
                                        Current expiration: <?php echo date('Y-m-d H:i', strtotime($license['expires_at'])); ?> 
                                        (<?php echo $is_expired ? "Expired " : ""; ?><?php echo $days_remaining; ?> days <?php echo $is_expired ? "ago" : "remaining"; ?>)
                                    </small>
                                </div>
                                
                                <div class="actions">
                                    <button type="submit" class="btn">💾 Save Changes</button>
                                    <a href="licenses.php" class="btn-secondary">❌ Cancel</a>
                                </div>
                            </form>
                        </div>
                        <?php
                    }
                }
                ?>
                
                <table>
                    <thead>
                        <tr>
                            <th>License Key</th>
                            <th>Customer Type</th>
                            <th>Device ID</th>
                            <th>Created</th>
                            <th>Expires</th>
                            <th>Status</th>
                            <th>Actions</th>
                        </tr>
                    </thead>
                    <tbody>
                        <?php
                        $result = $db->query("SELECT * FROM licenses ORDER BY created_at DESC");
                        while ($row = $result->fetchArray(SQLITE3_ASSOC)) {
                            // Calculate days remaining
                            $expires_date = new DateTime($row['expires_at']);
                            $now = new DateTime();
                            $interval = $now->diff($expires_date);
                            $days_remaining = $interval->days;
                            $is_expired = $interval->invert == 1;
                            
                            // Determine status
                            if ($row['status'] == 'active') {
                                if (empty($row['device_id'])) {
                                    $status_text = '<span class="status-available">Available</span>';
                                } else {
                                    $status_text = '<span class="status-active">Active</span>';
                                }
                            } else {
                                $status_text = '<span class="status-expired">' . ucfirst($row['status']) . '</span>';
                            }
                            
                            echo "<tr>";
                            echo "<td><div class='license-key'>" . htmlspecialchars($row['license_key']) . "</div></td>";
                            echo "<td>" . htmlspecialchars($row['customer_type']) . "</td>";
                            echo "<td><small>" . (empty($row['device_id']) ? 'Not activated' : substr($row['device_id'], 0, 12) . '...') . "</small></td>";
                            echo "<td>" . date('Y-m-d', strtotime($row['created_at'])) . "</td>";
                            echo "<td>" . date('Y-m-d', strtotime($row['expires_at'])) . 
                                 "<br><small>(" . ($is_expired ? "Expired " : "") . $days_remaining . " days " . ($is_expired ? "ago" : "left") . ")</small></td>";
                            echo "<td>" . $status_text . "</td>";
                            echo "<td class='actions'>";
                            echo "<a href='licenses.php?edit=" . urlencode($row['license_key']) . "' class='btn btn-small'>✏️ Edit</a>";
                            if (!empty($row['device_id'])) {
                                echo "<a href='licenses.php?action=reset&key=" . urlencode($row['license_key']) . "' onclick='return confirm(\"Reset device binding? This will allow activation on another device.\")' class='btn-warning btn-small'>🔄 Reset</a>";
                            }
                            echo "<a href='licenses.php?action=regenerate&key=" . urlencode($row['license_key']) . "' onclick='return confirm(\"Generate new key? Old key will become invalid!\")' class='btn-warning btn-small'>🔄 New Key</a>";
                            echo "<a href='licenses.php?action=delete&key=" . urlencode($row['license_key']) . "' onclick='return confirm(\"Delete this license permanently?\")' class='btn-danger btn-small'>🗑️ Delete</a>";
                            echo "</td>";
                            echo "</tr>";
                        }
                        ?>
                    </tbody>
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
                
                // Auto-hide messages after 5 seconds
                setTimeout(() => {
                    const messages = document.querySelectorAll('.success, .error');
                    messages.forEach(msg => {
                        msg.style.transition = 'opacity 0.5s';
                        msg.style.opacity = '0';
                        setTimeout(() => msg.remove(), 500);
                    });
                }, 5000);
            };
        </script>
    </body>
    </html>
    <?php
}

// Display the admin panel if logged in
if (isLoggedIn()) {
    showAdminPanel($db);
    $db->close();
}
?>
