<?php
// licenses.php - Admin panel with user license assignment
session_start();

// Enable SQLite database
define('DB_FILE', 'licenses.db');

// Admin credentials (Change these!)
define('ADMIN_USERNAME', 'admin');
define('ADMIN_PASSWORD', 'admin123');

// Check if admin is logged in
function isAdminLoggedIn() {
    return isset($_SESSION['admin_loggedin']) && $_SESSION['admin_loggedin'] === true;
}

// Handle admin login
if (isset($_POST['admin_login'])) {
    $username = $_POST['username'] ?? '';
    $password = $_POST['password'] ?? '';
    
    if ($username === ADMIN_USERNAME && $password === ADMIN_PASSWORD) {
        $_SESSION['admin_loggedin'] = true;
        $_SESSION['admin_username'] = $username;
        header("Location: licenses.php");
        exit;
    } else {
        $login_error = "Invalid admin credentials!";
    }
}

// Handle logout
if (isset($_GET['logout'])) {
    session_destroy();
    header("Location: licenses.php");
    exit;
}

// If not logged in, show admin login page
if (!isAdminLoggedIn()) {
    showAdminLoginPage();
    exit;
}

// Initialize SQLite database
function initDatabase() {
    if (!file_exists(DB_FILE)) {
        $db = new SQLite3(DB_FILE);
        
        // Create licenses table with user_email field
        $db->exec("CREATE TABLE IF NOT EXISTS licenses (
            id INTEGER PRIMARY KEY AUTOINCREMENT,
            license_key TEXT UNIQUE NOT NULL,
            customer_info TEXT,
            user_email TEXT,
            customer_type TEXT DEFAULT 'User',
            device_id TEXT,
            status TEXT DEFAULT 'active',
            created_at DATETIME DEFAULT CURRENT_TIMESTAMP,
            activated_at DATETIME,
            expires_at DATETIME,
            last_updated DATETIME DEFAULT CURRENT_TIMESTAMP
        )");
        
        // Create users table to track Firebase users
        $db->exec("CREATE TABLE IF NOT EXISTS users (
            id INTEGER PRIMARY KEY AUTOINCREMENT,
            firebase_uid TEXT,
            email TEXT UNIQUE NOT NULL,
            display_name TEXT,
            created_at DATETIME DEFAULT CURRENT_TIMESTAMP,
            last_login DATETIME
        )");
        
        // Create default admin license
        $default_key = generateLicenseKey();
        $expires = date('Y-m-d H:i:s', strtotime('+365 days'));
        $stmt = $db->prepare("INSERT INTO licenses (license_key, customer_info, expires_at) VALUES (?, ?, ?)");
        $stmt->bindValue(1, $default_key, SQLITE3_TEXT);
        $stmt->bindValue(2, 'Admin Account', SQLITE3_TEXT);
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

// Initialize database
initDatabase();
$db = new SQLite3(DB_FILE);

// Handle admin actions
if (isset($_GET['action'])) {
    $action = $_GET['action'];
    
    if ($action === 'create' && isset($_POST['customer_type'])) {
        createLicense($db);
    } elseif ($action === 'edit' && isset($_POST['license_key'])) {
        editLicense($db);
    } elseif ($action === 'assign' && isset($_POST['license_key'])) {
        assignLicenseToUser($db);
    } elseif ($action === 'delete' && isset($_GET['key'])) {
        deleteLicense($db, $_GET['key']);
    } elseif ($action === 'reset' && isset($_GET['key'])) {
        resetDevice($db, $_GET['key']);
    } elseif ($action === 'regenerate' && isset($_GET['key'])) {
        regenerateKey($db, $_GET['key']);
    } elseif ($action === 'unassign' && isset($_GET['key'])) {
        unassignLicense($db, $_GET['key']);
    }
}

// Create new license
function createLicense($db) {
    $customer_info = $_POST['customer_info'] ?? '';
    $user_email = $_POST['user_email'] ?? '';
    $customer_type = $_POST['customer_type'];
    $duration = isset($_POST['duration']) ? intval($_POST['duration']) : 365;
    $license_key = isset($_POST['license_key']) ? trim($_POST['license_key']) : generateLicenseKey();
    
    if (empty($license_key)) {
        $license_key = generateLicenseKey();
    }
    
    // Validate license key format
    if (!preg_match('/^[A-Z0-9]{4}-[A-Z0-9]{4}-[A-Z0-9]{4}-[A-Z0-9]{4}$/', $license_key)) {
        header("Location: licenses.php?error=Invalid license key format. Use format: XXXX-XXXX-XXXX-XXXX");
        exit;
    }
    
    $expires = date('Y-m-d H:i:s', strtotime("+$duration days"));
    
    $stmt = $db->prepare("INSERT INTO licenses (license_key, customer_info, user_email, customer_type, expires_at) VALUES (:key, :customer_info, :user_email, :customer_type, :expires)");
    $stmt->bindValue(':key', $license_key, SQLITE3_TEXT);
    $stmt->bindValue(':customer_info', $customer_info, SQLITE3_TEXT);
    $stmt->bindValue(':user_email', $user_email, SQLITE3_TEXT);
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
    $customer_info = $_POST['customer_info'] ?? '';
    $user_email = $_POST['user_email'] ?? '';
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
        $stmt = $db->prepare("UPDATE licenses SET license_key = :new_key, customer_info = :customer_info, user_email = :user_email, customer_type = :customer_type, status = :status, expires_at = :expires, last_updated = datetime('now') WHERE license_key = :old_key");
        $stmt->bindValue(':new_key', $new_license_key, SQLITE3_TEXT);
        $stmt->bindValue(':customer_info', $customer_info, SQLITE3_TEXT);
        $stmt->bindValue(':user_email', $user_email, SQLITE3_TEXT);
        $stmt->bindValue(':customer_type', $customer_type, SQLITE3_TEXT);
        $stmt->bindValue(':status', $status, SQLITE3_TEXT);
        $stmt->bindValue(':expires', $expires, SQLITE3_TEXT);
        $stmt->bindValue(':old_key', $license_key, SQLITE3_TEXT);
    } else {
        // Update without changing key
        $stmt = $db->prepare("UPDATE licenses SET customer_info = :customer_info, user_email = :user_email, customer_type = :customer_type, status = :status, expires_at = :expires, last_updated = datetime('now') WHERE license_key = :key");
        $stmt->bindValue(':customer_info', $customer_info, SQLITE3_TEXT);
        $stmt->bindValue(':user_email', $user_email, SQLITE3_TEXT);
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

// Assign license to user by email
function assignLicenseToUser($db) {
    $license_key = $_POST['license_key'];
    $user_email = trim($_POST['assign_email']);
    
    if (empty($user_email) || !filter_var($user_email, FILTER_VALIDATE_EMAIL)) {
        header("Location: licenses.php?error=Please enter a valid email address");
        exit;
    }
    
    // Check if user already has a license
    $check_stmt = $db->prepare("SELECT * FROM licenses WHERE user_email = :email AND status = 'active'");
    $check_stmt->bindValue(':email', $user_email, SQLITE3_TEXT);
    $existing = $check_stmt->execute()->fetchArray(SQLITE3_ASSOC);
    
    if ($existing) {
        header("Location: licenses.php?error=User $user_email already has an active license: " . $existing['license_key']);
        exit;
    }
    
    // Assign license to user
    $stmt = $db->prepare("UPDATE licenses SET user_email = :email, last_updated = datetime('now') WHERE license_key = :key");
    $stmt->bindValue(':email', $user_email, SQLITE3_TEXT);
    $stmt->bindValue(':key', $license_key, SQLITE3_TEXT);
    
    if ($stmt->execute()) {
        // Update customer info if empty
        $update_info = $db->prepare("UPDATE licenses SET customer_info = :info WHERE license_key = :key AND (customer_info IS NULL OR customer_info = '')");
        $update_info->bindValue(':info', $user_email, SQLITE3_TEXT);
        $update_info->bindValue(':key', $license_key, SQLITE3_TEXT);
        $update_info->execute();
        
        header("Location: licenses.php?success=License $license_key assigned to $user_email");
        exit;
    } else {
        header("Location: licenses.php?error=Failed to assign license");
        exit;
    }
}

// Unassign license from user
function unassignLicense($db, $key) {
    $stmt = $db->prepare("UPDATE licenses SET user_email = NULL, last_updated = datetime('now') WHERE license_key = :key");
    $stmt->bindValue(':key', $key, SQLITE3_TEXT);
    $stmt->execute();
    header("Location: licenses.php?success=License unassigned from user");
    exit;
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
    $stmt = $db->prepare("UPDATE licenses SET device_id = NULL, activated_at = NULL, last_updated = datetime('now') WHERE license_key = :key");
    $stmt->bindValue(':key', $key, SQLITE3_TEXT);
    $stmt->execute();
    header("Location: licenses.php?success=Device binding reset");
    exit;
}

// Regenerate license key
function regenerateKey($db, $key) {
    $new_key = generateLicenseKey();
    $stmt = $db->prepare("UPDATE licenses SET license_key = :new_key, last_updated = datetime('now') WHERE license_key = :old_key");
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

// Show admin login page
function showAdminLoginPage() {
    global $login_error;
    ?>
    <!DOCTYPE html>
    <html>
    <head>
        <title>Admin Login - License Manager</title>
        <style>
            * { margin: 0; padding: 0; box-sizing: border-box; font-family: 'Segoe UI', Tahoma, Geneva, Verdana, sans-serif; }
            body { 
                background: linear-gradient(135deg, #1a1a2e 0%, #16213e 100%);
                min-height: 100vh;
                display: flex;
                align-items: center;
                justify-content: center;
                padding: 20px;
            }
            .admin-login-container {
                background: rgba(255, 255, 255, 0.1);
                backdrop-filter: blur(10px);
                border-radius: 20px;
                padding: 40px;
                width: 100%;
                max-width: 400px;
                border: 1px solid rgba(255, 255, 255, 0.2);
                box-shadow: 0 20px 40px rgba(0, 0, 0, 0.3);
            }
            .admin-header {
                text-align: center;
                margin-bottom: 30px;
            }
            .admin-header h1 {
                color: white;
                font-size: 2rem;
                margin-bottom: 10px;
            }
            .admin-header p {
                color: #a0c8e0;
                font-size: 0.9rem;
            }
            .form-group {
                margin-bottom: 20px;
            }
            .form-group label {
                display: block;
                color: white;
                margin-bottom: 8px;
                font-weight: 500;
            }
            .form-group input {
                width: 100%;
                padding: 12px 15px;
                background: rgba(255, 255, 255, 0.1);
                border: 1px solid rgba(255, 255, 255, 0.3);
                border-radius: 8px;
                color: white;
                font-size: 16px;
            }
            .form-group input:focus {
                outline: none;
                border-color: #4dabf7;
            }
            .admin-btn {
                width: 100%;
                padding: 14px;
                background: linear-gradient(135deg, #4dabf7 0%, #2196f3 100%);
                color: white;
                border: none;
                border-radius: 8px;
                font-size: 16px;
                font-weight: 600;
                cursor: pointer;
                transition: all 0.3s;
            }
            .admin-btn:hover {
                transform: translateY(-2px);
                box-shadow: 0 10px 20px rgba(77, 171, 247, 0.4);
            }
            .error-message {
                background: rgba(231, 76, 60, 0.2);
                color: #ff6b6b;
                padding: 12px;
                border-radius: 8px;
                margin-bottom: 20px;
                text-align: center;
                border-left: 4px solid #e74c3c;
            }
            .back-link {
                text-align: center;
                margin-top: 20px;
            }
            .back-link a {
                color: #4dabf7;
                text-decoration: none;
            }
            .back-link a:hover {
                text-decoration: underline;
            }
        </style>
    </head>
    <body>
        <div class="admin-login-container">
            <div class="admin-header">
                <h1>🔒 Admin Panel</h1>
                <p>License Management System</p>
            </div>
            
            <?php if (isset($login_error)): ?>
                <div class="error-message">❌ <?php echo $login_error; ?></div>
            <?php endif; ?>
            
            <form method="POST" action="">
                <div class="form-group">
                    <label for="username">Admin Username:</label>
                    <input type="text" id="username" name="username" placeholder="Enter admin username" required>
                </div>
                
                <div class="form-group">
                    <label for="password">Password:</label>
                    <input type="password" id="password" name="password" placeholder="Enter admin password" required>
                </div>
                
                <button type="submit" name="admin_login" class="admin-btn">Login as Admin</button>
            </form>
            
            <div class="back-link">
                <a href="index.php">← Back to Main Site</a>
            </div>
        </div>
    </body>
    </html>
    <?php
}

// Show admin panel
function showAdminPanel($db) {
    ?>
    <!DOCTYPE html>
    <html>
    <head>
        <title>License Manager - Admin Panel</title>
        <style>
            * {
                margin: 0;
                padding: 0;
                box-sizing: border-box;
                font-family: 'Segoe UI', Tahoma, Geneva, Verdana, sans-serif;
            }

            body {
                background: linear-gradient(135deg, #0f2027 0%, #203a43 50%, #2c5364 100%);
                color: #e0e0e0;
                min-height: 100vh;
            }

            .admin-container {
                max-width: 1400px;
                margin: 0 auto;
                padding: 20px;
            }

            /* Admin Header */
            .admin-header {
                background: linear-gradient(135deg, #2c3e50 0%, #1a252f 100%);
                color: white;
                padding: 25px;
                border-radius: 15px;
                margin-bottom: 30px;
                display: flex;
                justify-content: space-between;
                align-items: center;
                box-shadow: 0 10px 30px rgba(0, 0, 0, 0.3);
            }

            .admin-header-left h1 {
                font-size: 2.2rem;
                margin-bottom: 5px;
            }

            .admin-header-left p {
                color: #ecf0f1;
                opacity: 0.9;
                font-size: 14px;
            }

            .admin-user-info {
                display: flex;
                align-items: center;
                gap: 15px;
            }

            .admin-actions {
                display: flex;
                gap: 10px;
            }

            .btn {
                padding: 10px 20px;
                border-radius: 8px;
                border: none;
                font-weight: 600;
                cursor: pointer;
                transition: all 0.3s;
                text-decoration: none;
                display: inline-flex;
                align-items: center;
                gap: 8px;
            }

            .btn-primary {
                background: linear-gradient(135deg, #4dabf7 0%, #2196f3 100%);
                color: white;
            }

            .btn-primary:hover {
                transform: translateY(-2px);
                box-shadow: 0 5px 15px rgba(77, 171, 247, 0.4);
            }

            .btn-danger {
                background: linear-gradient(135deg, #e74c3c 0%, #c0392b 100%);
                color: white;
            }

            .btn-danger:hover {
                transform: translateY(-2px);
                box-shadow: 0 5px 15px rgba(231, 76, 60, 0.4);
            }

            .btn-success {
                background: linear-gradient(135deg, #27ae60 0%, #219653 100%);
                color: white;
            }

            .btn-success:hover {
                transform: translateY(-2px);
                box-shadow: 0 5px 15px rgba(39, 174, 96, 0.4);
            }

            .btn-warning {
                background: linear-gradient(135deg, #f39c12 0%, #d35400 100%);
                color: white;
            }

            .btn-warning:hover {
                transform: translateY(-2px);
                box-shadow: 0 5px 15px rgba(243, 156, 18, 0.4);
            }

            .btn-secondary {
                background: linear-gradient(135deg, #95a5a6 0%, #7f8c8d 100%);
                color: white;
            }

            /* Messages */
            .message {
                padding: 15px;
                border-radius: 10px;
                margin-bottom: 20px;
                animation: slideIn 0.3s ease;
            }

            @keyframes slideIn {
                from {
                    opacity: 0;
                    transform: translateY(-10px);
                }
                to {
                    opacity: 1;
                    transform: translateY(0);
                }
            }

            .message.success {
                background: rgba(39, 174, 96, 0.2);
                color: #27ae60;
                border-left: 4px solid #27ae60;
            }

            .message.error {
                background: rgba(231, 76, 60, 0.2);
                color: #e74c3c;
                border-left: 4px solid #e74c3c;
            }

            /* Admin Content */
            .admin-content {
                display: grid;
                grid-template-columns: 1fr;
                gap: 30px;
            }

            /* License Creation Form */
            .creation-form {
                background: rgba(25, 40, 50, 0.85);
                border-radius: 15px;
                padding: 30px;
                box-shadow: 0 10px 30px rgba(0, 0, 0, 0.4);
            }

            .creation-form h2 {
                font-size: 1.8rem;
                color: #fff;
                margin-bottom: 25px;
                display: flex;
                align-items: center;
                gap: 10px;
            }

            .form-grid {
                display: grid;
                grid-template-columns: repeat(auto-fit, minmax(250px, 1fr));
                gap: 20px;
                margin-bottom: 25px;
            }

            .form-group {
                margin-bottom: 20px;
            }

            .form-group label {
                display: block;
                margin-bottom: 8px;
                color: #a0c8e0;
                font-weight: 500;
            }

            .form-group input,
            .form-group select,
            .form-group textarea {
                width: 100%;
                padding: 12px 15px;
                background: rgba(0, 0, 0, 0.3);
                border: 1px solid rgba(255, 255, 255, 0.1);
                border-radius: 8px;
                color: white;
                font-size: 16px;
            }

            .form-group input:focus,
            .form-group select:focus,
            .form-group textarea:focus {
                outline: none;
                border-color: #4dabf7;
            }

            .form-group textarea {
                resize: vertical;
                min-height: 80px;
            }

            /* License Table */
            .license-table-container {
                background: rgba(25, 40, 50, 0.85);
                border-radius: 15px;
                padding: 30px;
                box-shadow: 0 10px 30px rgba(0, 0, 0, 0.4);
                overflow-x: auto;
            }

            .license-table-container h2 {
                font-size: 1.8rem;
                color: #fff;
                margin-bottom: 25px;
                display: flex;
                align-items: center;
                gap: 10px;
            }

            table {
                width: 100%;
                border-collapse: collapse;
                margin-top: 20px;
            }

            th {
                background: rgba(0, 0, 0, 0.3);
                color: #a0c8e0;
                font-weight: 600;
                text-align: left;
                padding: 15px;
                border-bottom: 2px solid rgba(255, 255, 255, 0.1);
            }

            td {
                padding: 15px;
                border-bottom: 1px solid rgba(255, 255, 255, 0.05);
            }

            tr:hover {
                background: rgba(255, 255, 255, 0.05);
            }

            .license-key-cell {
                font-family: 'Courier New', monospace;
                font-weight: bold;
                color: #4dabf7;
            }

            .user-email-cell {
                color: #4bb543;
                font-weight: 500;
            }

            .status-badge {
                display: inline-block;
                padding: 5px 12px;
                border-radius: 20px;
                font-size: 0.85rem;
                font-weight: 600;
            }

            .status-active {
                background: rgba(39, 174, 96, 0.2);
                color: #27ae60;
            }

            .status-expired {
                background: rgba(231, 76, 60, 0.2);
                color: #e74c3c;
            }

            .status-available {
                background: rgba(52, 152, 219, 0.2);
                color: #3498db;
            }

            .actions-cell {
                display: flex;
                gap: 8px;
                flex-wrap: wrap;
            }

            .btn-small {
                padding: 6px 12px;
                font-size: 0.85rem;
            }

            /* Quick Stats */
            .stats-grid {
                display: grid;
                grid-template-columns: repeat(auto-fit, minmax(200px, 1fr));
                gap: 20px;
                margin-bottom: 30px;
            }

            .stat-card {
                background: rgba(25, 40, 50, 0.85);
                border-radius: 10px;
                padding: 25px;
                text-align: center;
                box-shadow: 0 5px 15px rgba(0, 0, 0, 0.3);
            }

            .stat-number {
                font-size: 2.5rem;
                font-weight: bold;
                color: #4dabf7;
                margin: 10px 0;
            }

            .stat-label {
                color: #a0c8e0;
                font-size: 0.9rem;
                text-transform: uppercase;
                letter-spacing: 1px;
            }

            /* Assign License Form */
            .assign-form {
                background: rgba(25, 40, 50, 0.85);
                border-radius: 15px;
                padding: 25px;
                margin-top: 20px;
                border-left: 4px solid #4bb543;
            }

            .assign-form h3 {
                color: #fff;
                margin-bottom: 15px;
                font-size: 1.2rem;
            }

            .assign-form .form-grid {
                margin-bottom: 15px;
            }

            /* Responsive */
            @media (max-width: 768px) {
                .admin-header {
                    flex-direction: column;
                    gap: 15px;
                    text-align: center;
                }

                .admin-user-info {
                    flex-direction: column;
                }

                .admin-actions {
                    flex-wrap: wrap;
                    justify-content: center;
                }

                .form-grid {
                    grid-template-columns: 1fr;
                }

                table {
                    display: block;
                    overflow-x: auto;
                }

                .actions-cell {
                    flex-direction: column;
                }
            }
        </style>
    </head>
    <body>
        <div class="admin-container">
            <!-- Admin Header -->
            <header class="admin-header">
                <div class="admin-header-left">
                    <h1>📋 License Manager - Admin Panel</h1>
                    <p>Manage licenses and assign them to users</p>
                </div>
                <div class="admin-user-info">
                    <div class="admin-actions">
                        <a href="index.php" class="btn btn-secondary">
                            <i class="fas fa-home"></i> Main Site
                        </a>
                        <a href="licenses.php?logout" class="btn btn-danger">
                            <i class="fas fa-sign-out-alt"></i> Logout
                        </a>
                    </div>
                </div>
            </header>

            <!-- Messages -->
            <?php if (isset($_GET['success'])): ?>
                <div class="message success">
                    <i class="fas fa-check-circle"></i> <?php echo htmlspecialchars($_GET['success']); ?>
                </div>
            <?php endif; ?>

            <?php if (isset($_GET['error'])): ?>
                <div class="message error">
                    <i class="fas fa-exclamation-circle"></i> <?php echo htmlspecialchars($_GET['error']); ?>
                </div>
            <?php endif; ?>

            <!-- Quick Stats -->
            <div class="stats-grid">
                <?php
                $total = $db->querySingle("SELECT COUNT(*) FROM licenses");
                $active = $db->querySingle("SELECT COUNT(*) FROM licenses WHERE status = 'active'");
                $assigned = $db->querySingle("SELECT COUNT(*) FROM licenses WHERE user_email IS NOT NULL AND user_email != ''");
                $available = $db->querySingle("SELECT COUNT(*) FROM licenses WHERE user_email IS NULL OR user_email = ''");
                ?>
                <div class="stat-card">
                    <div class="stat-number"><?php echo $total; ?></div>
                    <div class="stat-label">Total Licenses</div>
                </div>
                <div class="stat-card">
                    <div class="stat-number"><?php echo $active; ?></div>
                    <div class="stat-label">Active Licenses</div>
                </div>
                <div class="stat-card">
                    <div class="stat-number"><?php echo $assigned; ?></div>
                    <div class="stat-label">Assigned to Users</div>
                </div>
                <div class="stat-card">
                    <div class="stat-number"><?php echo $available; ?></div>
                    <div class="stat-label">Available Licenses</div>
                </div>
            </div>

            <!-- Admin Content -->
            <div class="admin-content">
                <!-- License Creation Form -->
                <div class="creation-form">
                    <h2><i class="fas fa-plus-circle"></i> Create New License</h2>
                    <form method="POST" action="licenses.php?action=create">
                        <div class="form-grid">
                            <div class="form-group">
                                <label for="license_key">License Key:</label>
                                <input type="text" id="license_key" name="license_key" placeholder="Leave empty for random" pattern="[A-Z0-9]{4}-[A-Z0-9]{4}-[A-Z0-9]{4}-[A-Z0-9]{4}">
                                <button type="button" class="btn btn-secondary" style="margin-top: 10px; width: 100%;" onclick="generateRandomKey()">
                                    <i class="fas fa-random"></i> Generate Random Key
                                </button>
                            </div>
                            
                            <div class="form-group">
                                <label for="user_email">Assign to User Email:</label>
                                <input type="email" id="user_email" name="user_email" placeholder="user@example.com">
                            </div>
                            
                            <div class="form-group">
                                <label for="customer_info">Customer Info (Optional):</label>
                                <input type="text" id="customer_info" name="customer_info" placeholder="Customer name or notes">
                            </div>
                            
                            <div class="form-group">
                                <label for="customer_type">Customer Type:</label>
                                <select name="customer_type" required>
                                    <option value="User">User</option>
                                    <option value="Admin">Admin</option>
                                    <option value="Trial">Trial</option>
                                    <option value="VIP">VIP</option>
                                </select>
                            </div>
                            
                            <div class="form-group">
                                <label for="duration">License Duration:</label>
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
                        </div>
                        
                        <button type="submit" class="btn btn-success" style="width: 100%;">
                            <i class="fas fa-plus"></i> Create License
                        </button>
                    </form>
                </div>

                <!-- License Management Table -->
                <div class="license-table-container">
                    <h2><i class="fas fa-list"></i> All Licenses</h2>
                    
                    <table>
                        <thead>
                            <tr>
                                <th>License Key</th>
                                <th>User Email</th>
                                <th>Customer Info</th>
                                <th>Type</th>
                                <th>Expires</th>
                                <th>Device</th>
                                <th>Status</th>
                                <th>Actions</th>
                            </tr>
                        </thead>
                        <tbody>
                            <?php
                            $result = $db->query("SELECT * FROM licenses ORDER BY created_at DESC");
                            while ($row = $result->fetchArray(SQLITE3_ASSOC)) {
                                // Determine status badge
                                $status_class = 'status-available';
                                $status_text = 'Available';
                                
                                if ($row['status'] == 'active') {
                                    if (!empty($row['user_email'])) {
                                        $status_class = 'status-active';
                                        $status_text = 'Active';
                                    }
                                } else {
                                    $status_class = 'status-expired';
                                    $status_text = ucfirst($row['status']);
                                }
                                
                                // Calculate days remaining
                                $expires_date = new DateTime($row['expires_at']);
                                $now = new DateTime();
                                $interval = $now->diff($expires_date);
                                $days_remaining = $interval->days;
                                $is_expired = $interval->invert == 1;
                                
                                echo "<tr>";
                                echo "<td class='license-key-cell'>" . htmlspecialchars($row['license_key']) . "</td>";
                                echo "<td class='user-email-cell'>" . (!empty($row['user_email']) ? htmlspecialchars($row['user_email']) : '<span style=\"color: #95a5a6;\">Not assigned</span>') . "</td>";
                                echo "<td>" . htmlspecialchars($row['customer_info']) . "</td>";
                                echo "<td>" . htmlspecialchars($row['customer_type']) . "</td>";
                                echo "<td>" . date('Y-m-d', strtotime($row['expires_at'])) . "<br><small>(" . ($is_expired ? "Expired " : "") . $days_remaining . " days " . ($is_expired ? "ago" : "left") . ")</small></td>";
                                echo "<td>" . (!empty($row['device_id']) ? '<span style=\"color: #4bb543;\">✓ Activated</span>' : '<span style=\"color: #95a5a6;\">Not activated</span>') . "</td>";
                                echo "<td><span class='status-badge $status_class'>$status_text</span></td>";
                                echo "<td class='actions-cell'>";
                                echo "<a href='licenses.php?edit=" . urlencode($row['license_key']) . "' class='btn btn-primary btn-small'><i class='fas fa-edit'></i> Edit</a>";
                                
                                if (empty($row['user_email'])) {
                                    echo "<a href='#' onclick='showAssignForm(\"" . htmlspecialchars($row['license_key']) . "\")' class='btn btn-success btn-small'><i class='fas fa-user-plus'></i> Assign</a>";
                                } else {
                                    echo "<a href='licenses.php?action=unassign&key=" . urlencode($row['license_key']) . "' onclick='return confirm(\"Unassign license from user?\")' class='btn btn-warning btn-small'><i class='fas fa-user-minus'></i> Unassign</a>";
                                }
                                
                                if (!empty($row['device_id'])) {
                                    echo "<a href='licenses.php?action=reset&key=" . urlencode($row['license_key']) . "' onclick='return confirm(\"Reset device binding?\")' class='btn btn-warning btn-small'><i class='fas fa-sync'></i> Reset Device</a>";
                                }
                                
                                echo "<a href='licenses.php?action=regenerate&key=" . urlencode($row['license_key']) . "' onclick='return confirm(\"Generate new key? Old key will be invalid!\")' class='btn btn-warning btn-small'><i class='fas fa-redo'></i> New Key</a>";
                                echo "<a href='licenses.php?action=delete&key=" . urlencode($row['license_key']) . "' onclick='return confirm(\"Delete this license permanently?\")' class='btn btn-danger btn-small'><i class='fas fa-trash'></i> Delete</a>";
                                echo "</td>";
                                echo "</tr>";
                            }
                            ?>
                        </tbody>
                    </table>
                </div>
            </div>
        </div>

        <!-- Assign License Form (Hidden by default) -->
        <div id="assignFormModal" style="display: none; position: fixed; top: 0; left: 0; width: 100%; height: 100%; background: rgba(0,0,0,0.8); z-index: 1000; align-items: center; justify-content: center;">
            <div style="background: rgba(25, 40, 50, 0.95); padding: 30px; border-radius: 15px; width: 90%; max-width: 500px;">
                <h3 style="color: white; margin-bottom: 20px;"><i class="fas fa-user-plus"></i> Assign License to User</h3>
                <form method="POST" action="licenses.php?action=assign" id="assignLicenseForm">
                    <input type="hidden" name="license_key" id="assignLicenseKey">
                    
                    <div class="form-group">
                        <label for="assign_email">User Email:</label>
                        <input type="email" id="assign_email" name="assign_email" placeholder="user@example.com" required style="width: 100%; padding: 12px; background: rgba(0,0,0,0.3); border: 1px solid rgba(255,255,255,0.1); color: white; border-radius: 8px;">
                    </div>
                    
                    <div style="display: flex; gap: 10px; margin-top: 20px;">
                        <button type="submit" class="btn btn-success" style="flex: 1;">
                            <i class="fas fa-check"></i> Assign License
                        </button>
                        <button type="button" class="btn btn-secondary" onclick="hideAssignForm()" style="flex: 1;">
                            <i class="fas fa-times"></i> Cancel
                        </button>
                    </div>
                </form>
            </div>
        </div>

        <script>
            // Generate random license key
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

            // Show assign license form
            function showAssignForm(licenseKey) {
                document.getElementById('assignLicenseKey').value = licenseKey;
                document.getElementById('assignFormModal').style.display = 'flex';
            }

            // Hide assign license form
            function hideAssignForm() {
                document.getElementById('assignFormModal').style.display = 'none';
            }

            // Auto-hide messages after 5 seconds
            setTimeout(() => {
                const messages = document.querySelectorAll('.message');
                messages.forEach(msg => {
                    msg.style.transition = 'opacity 0.5s';
                    msg.style.opacity = '0';
                    setTimeout(() => msg.remove(), 500);
                });
            }, 5000);

            // Close modal when clicking outside
            document.getElementById('assignFormModal').addEventListener('click', (e) => {
                if (e.target === document.getElementById('assignFormModal')) {
                    hideAssignForm();
                }
            });
        </script>
    </body>
    </html>
    <?php
}

// Display the admin panel if logged in
if (isAdminLoggedIn()) {
    showAdminPanel($db);
    $db->close();
}
?>
