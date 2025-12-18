<?php
// login.php - For MT5 EA license verification only
// This file only handles API requests, no HTML interface

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

// Handle API requests from MT5 EA
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    // Only process API requests
    if (isset($_POST['action']) && $_POST['action'] === 'check_license') {
        initDatabase();
        $db = new SQLite3(DB_FILE);
        
        header('Content-Type: text/plain');
        
        $license_key = $_POST['key'] ?? '';
        $device_id = $_POST['device_id'] ?? '';
        $action = $_POST['action'] ?? '';
        
        if ($action === 'check_license') {
            $license_key = trim($license_key);
            $device_id = trim($device_id);
            
            if (empty($license_key) || empty($device_id)) {
                echo "INVALID";
                exit;
            }
            
            // Check license
            $stmt = $db->prepare("SELECT * FROM licenses WHERE license_key = :key AND status = 'active'");
            $stmt->bindValue(':key', $license_key, SQLITE3_TEXT);
            $result = $stmt->execute()->fetchArray(SQLITE3_ASSOC);
            
            if (!$result) {
                echo "INVALID";
                exit;
            }
            
            // Check expiration
            if (strtotime($result['expires_at']) < time()) {
                echo "EXPIRED";
                exit;
            }
            
            // Check device binding
            if (empty($result['device_id'])) {
                // First activation - bind device
                $stmt = $db->prepare("UPDATE licenses SET device_id = :device_id, activated_at = datetime('now') WHERE license_key = :key");
                $stmt->bindValue(':device_id', $device_id, SQLITE3_TEXT);
                $stmt->bindValue(':key', $license_key, SQLITE3_TEXT);
                $stmt->execute();
                echo "VALID";
            } elseif ($result['device_id'] === $device_id) {
                // Same device
                echo "VALID";
            } else {
                // Different device
                echo "DEVICE_LIMIT_EXCEEDED";
            }
        }
        
        $db->close();
        exit;
    }
}

// If not an API request, return error
header('Content-Type: text/plain');
echo "ERROR: This endpoint only accepts POST requests with license verification";
?>
