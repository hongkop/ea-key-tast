<?php
// login.php - Complete API for license management
define('DB_FILE', 'licenses.db');

// Initialize database
function initDatabase() {
    if (!file_exists(DB_FILE)) {
        $db = new SQLite3(DB_FILE);
        
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
        
        // Create default license
        $default_key = 'ABCD-EFGH-IJKL-MNOP';
        $expires = date('Y-m-d H:i:s', strtotime('+365 days'));
        $stmt = $db->prepare("INSERT OR IGNORE INTO licenses (license_key, customer_info, expires_at) VALUES (?, ?, ?)");
        $stmt->bindValue(1, $default_key, SQLITE3_TEXT);
        $stmt->bindValue(2, 'Default License', SQLITE3_TEXT);
        $stmt->bindValue(3, $expires, SQLITE3_TEXT);
        $stmt->execute();
        
        $db->close();
    }
}

// Handle API requests
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    initDatabase();
    $db = new SQLite3(DB_FILE);
    
    header('Content-Type: text/plain');
    
    $action = $_POST['action'] ?? '';
    $license_key = $_POST['key'] ?? '';
    $device_id = $_POST['device_id'] ?? '';
    $email = $_POST['email'] ?? '';
    
    if ($action === 'check_license') {
        // MT5 EA license check
        if (empty($license_key) || empty($device_id)) {
            echo "INVALID";
            exit;
        }
        
        $stmt = $db->prepare("SELECT * FROM licenses WHERE license_key = :key AND status = 'active'");
        $stmt->bindValue(':key', $license_key, SQLITE3_TEXT);
        $result = $stmt->execute()->fetchArray(SQLITE3_ASSOC);
        
        if (!$result) {
            echo "INVALID";
            exit;
        }
        
        if (strtotime($result['expires_at']) < time()) {
            echo "EXPIRED";
            exit;
        }
        
        if (empty($result['device_id'])) {
            // First activation
            $stmt = $db->prepare("UPDATE licenses SET device_id = :device_id, activated_at = datetime('now') WHERE license_key = :key");
            $stmt->bindValue(':device_id', $device_id, SQLITE3_TEXT);
            $stmt->bindValue(':key', $license_key, SQLITE3_TEXT);
            $stmt->execute();
            echo "VALID";
        } elseif ($result['device_id'] === $device_id) {
            echo "VALID";
        } else {
            echo "DEVICE_LIMIT_EXCEEDED";
        }
        
    } elseif ($action === 'get_license') {
        // Get user's license by email
        if (empty($email)) {
            echo "INVALID";
            exit;
        }
        
        $stmt = $db->prepare("SELECT * FROM licenses WHERE user_email = :email AND status = 'active' LIMIT 1");
        $stmt->bindValue(':email', $email, SQLITE3_TEXT);
        $result = $stmt->execute()->fetchArray(SQLITE3_ASSOC);
        
        if ($result) {
            echo $result['license_key'] . "|" . 
                 $result['status'] . "|" . 
                 date('Y-m-d', strtotime($result['expires_at'])) . "|" . 
                 (!empty($result['device_id']) ? 'Activated' : 'Not Activated');
        } else {
            echo "NOT_FOUND";
        }
    }
    
    $db->close();
    exit;
}

echo "ERROR: Invalid request";
?>
