<?php
session_start();

// Download URLs for each product
$download_links = [
    'trading_vps' => 'https://zeahong.up.railway.app/4T8_EA_Scalping.zip',
    'trading_robot' => 'https://zeahong.up.railway.app/4T8_EA_Scalping.zip',
    'btrader_tools' => 'https://zeahong.up.railway.app/500kbalance.set'
];

// File sizes (optional, for display)
$file_sizes = [
    'trading_vps' => '45 MB',
    'trading_robot' => '28 MB',
    'btrader_tools' => '62 MB'
];

// Check if user is logged in via Firebase
$isLoggedIn = isset($_SESSION['firebase_user']) ? true : false;

// Database connection for license management
$host = 'localhost';
$dbname = 'zeahong_trading';
$username = 'root';
$password = '';

try {
    $pdo = new PDO("mysql:host=$host;dbname=$dbname", $username, $password);
    $pdo->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);
} catch (PDOException $e) {
    // If database connection fails, continue without it
    $pdo = null;
}

// Function to get user license
function getUserLicense($email, $pdo) {
    if (!$pdo) {
        return generateDemoLicense($email);
    }
    
    try {
        $stmt = $pdo->prepare("SELECT * FROM licenses WHERE user_email = :email ORDER BY created_at DESC LIMIT 1");
        $stmt->execute([':email' => $email]);
        $license = $stmt->fetch(PDO::FETCH_ASSOC);
        
        if ($license) {
            $status = $license['status'];
            $expiry_date = $license['expiry_date'];
            $device_id = $license['device_id'] ?: 'Not Activated';
            
            return $license['license_key'] . '|' . $status . '|' . $expiry_date . '|' . $device_id;
        } else {
            // Check if user has purchased any product
            $stmt = $pdo->prepare("SELECT * FROM purchases WHERE user_email = :email AND status = 'completed'");
            $stmt->execute([':email' => $email]);
            $purchase = $stmt->fetch(PDO::FETCH_ASSOC);
            
            if ($purchase) {
                // Generate license for purchase
                $licenseKey = generateLicenseKey();
                $expiryDate = date('Y-m-d', strtotime('+1 year'));
                
                // Save to database
                $stmt = $pdo->prepare("INSERT INTO licenses (user_email, license_key, status, expiry_date, product_id) VALUES (:email, :key, 'active', :expiry, :product)");
                $stmt->execute([
                    ':email' => $email,
                    ':key' => $licenseKey,
                    ':expiry' => $expiryDate,
                    ':product' => $purchase['product_id']
                ]);
                
                return $licenseKey . '|active|' . $expiryDate . '|Not Activated';
            }
        }
    } catch (PDOException $e) {
        error_log("License query error: " . $e->getMessage());
    }
    
    return generateDemoLicense($email);
}

// Generate demo license for testing
function generateDemoLicense($email) {
    $hash = substr(md5($email . 'zeahong_salt_' . time()), 0, 20);
    $formatted = implode('-', str_split(strtoupper($hash), 5));
    $expiry = date('Y-m-d', strtotime('+30 days'));
    return $formatted . '|pending|' . $expiry . '|Not Activated';
}

// Generate secure license key
function generateLicenseKey() {
    $prefix = 'ZEAHONG-';
    $characters = 'ABCDEFGHIJKLMNOPQRSTUVWXYZ0123456789';
    $length = 16;
    $key = '';
    
    for ($i = 0; $i < $length; $i++) {
        $key .= $characters[rand(0, strlen($characters) - 1)];
        if ($i == 3 || $i == 7 || $i == 11) {
            $key .= '-';
        }
    }
    
    return $prefix . $key;
}

// Handle license API request
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['action'])) {
    if ($_POST['action'] === 'get_license' && isset($_POST['email'])) {
        $licenseInfo = getUserLicense($_POST['email'], $pdo);
        echo $licenseInfo;
        exit;
    }
}
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Zeahong Trading Platform</title>
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">
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

        /* Navigation Menu */
        .main-nav {
            background: linear-gradient(135deg, #2c3e50 0%, #1a252f 100%);
            padding: 0 20px;
            position: sticky;
            top: 0;
            z-index: 1000;
            box-shadow: 0 4px 20px rgba(0, 0, 0, 0.3);
            border-bottom: 1px solid rgba(255, 255, 255, 0.1);
        }

        .nav-container {
            max-width: 1200px;
            margin: 0 auto;
            display: flex;
            justify-content: space-between;
            align-items: center;
            padding: 15px 0;
        }

        .nav-logo {
            display: flex;
            align-items: center;
            gap: 12px;
            text-decoration: none;
        }

        .nav-logo i {
            font-size: 28px;
            color: #4dabf7;
        }

        .nav-logo-text {
            font-size: 24px;
            font-weight: 700;
            background: linear-gradient(135deg, #4dabf7 0%, #2193b0 100%);
            -webkit-background-clip: text;
            -webkit-text-fill-color: transparent;
            background-clip: text;
        }

        .nav-menu {
            display: flex;
            list-style: none;
            gap: 30px;
            align-items: center;
        }

        .nav-item {
            position: relative;
        }

        .nav-link {
            color: #e0e0e0;
            text-decoration: none;
            font-size: 16px;
            font-weight: 600;
            padding: 10px 0;
            transition: all 0.3s;
            display: flex;
            align-items: center;
            gap: 8px;
        }

        .nav-link:hover {
            color: #4dabf7;
        }

        .nav-link.active {
            color: #4dabf7;
        }

        .nav-link.active::after {
            content: '';
            position: absolute;
            bottom: -5px;
            left: 0;
            width: 100%;
            height: 3px;
            background: linear-gradient(135deg, #4dabf7 0%, #2193b0 100%);
            border-radius: 2px;
        }

        .nav-auth {
            display: flex;
            align-items: center;
            gap: 15px;
        }

        .nav-user {
            display: flex;
            align-items: center;
            gap: 10px;
        }

        .user-avatar-small {
            width: 35px;
            height: 35px;
            background: linear-gradient(135deg, #6a11cb 0%, #2575fc 100%);
            border-radius: 50%;
            display: flex;
            align-items: center;
            justify-content: center;
            color: white;
            font-weight: bold;
            font-size: 16px;
        }

        .user-email {
            font-size: 14px;
            color: #a0c8e0;
        }

        .nav-logout {
            background: linear-gradient(135deg, #e74c3c 0%, #c0392b 100%);
            color: white;
            border: none;
            padding: 8px 16px;
            border-radius: 8px;
            font-weight: 600;
            cursor: pointer;
            transition: all 0.3s;
            display: flex;
            align-items: center;
            gap: 6px;
        }

        .nav-logout:hover {
            transform: translateY(-2px);
            box-shadow: 0 5px 15px rgba(231, 76, 60, 0.4);
        }

        /* Mobile Menu Toggle */
        .mobile-menu-btn {
            display: none;
            background: none;
            border: none;
            color: #e0e0e0;
            font-size: 24px;
            cursor: pointer;
        }

        /* Login Page Styles - Modern Gradient Background */
        .login-page {
            display: flex;
            align-items: center;
            justify-content: center;
            min-height: 100vh;
            width: 100%;
            background: linear-gradient(135deg, #6a11cb 0%, #2575fc 100%);
            padding: 20px;
            position: relative;
            overflow: hidden;
        }

        .login-page::before {
            content: '';
            position: absolute;
            top: 0;
            left: 0;
            right: 0;
            bottom: 0;
            background: url('data:image/svg+xml,<svg xmlns="http://www.w3.org/2000/svg" viewBox="0 0 100 100" preserveAspectRatio="none"><path d="M0,0 L100,0 L100,100 Z" fill="rgba(255,255,255,0.05)"/></svg>');
            background-size: cover;
        }

        .container {
            width: 100%;
            max-width: 450px;
            background: rgba(255, 255, 255, 0.95);
            border-radius: 24px;
            box-shadow: 0 25px 50px rgba(0, 0, 0, 0.3);
            overflow: hidden;
            backdrop-filter: blur(10px);
            border: 1px solid rgba(255, 255, 255, 0.2);
            margin: 0 auto;
            position: relative;
            z-index: 1;
            animation: slideUp 0.6s ease;
        }

        @keyframes slideUp {
            from {
                opacity: 0;
                transform: translateY(30px);
            }
            to {
                opacity: 1;
                transform: translateY(0);
            }
        }

        /* Header - Modern Design */
        .header {
            background: linear-gradient(135deg, #2c3e50 0%, #1a252f 100%);
            color: white;
            padding: 40px 30px;
            text-align: center;
            position: relative;
            overflow: hidden;
        }

        .header::before {
            content: '';
            position: absolute;
            top: -50%;
            left: -50%;
            width: 200%;
            height: 200%;
            background: radial-gradient(circle, rgba(255,255,255,0.1) 0%, transparent 70%);
        }

        .logo {
            font-size: 3rem;
            margin-bottom: 15px;
            position: relative;
            z-index: 1;
            animation: float 6s ease-in-out infinite;
        }

        @keyframes float {
            0%, 100% { transform: translateY(0); }
            50% { transform: translateY(-10px); }
        }

        .header h1 {
            font-size: 2.2rem;
            font-weight: 700;
            margin-bottom: 8px;
            position: relative;
            z-index: 1;
        }

        .header p {
            opacity: 0.9;
            font-size: 1rem;
            position: relative;
            z-index: 1;
        }

        /* Content - Modern Design */
        .content {
            padding: 40px 35px;
            text-align: center;
        }

        .form-container {
            display: block;
            text-align: left;
        }

        .form-title {
            color: #2c3e50;
            font-size: 1.8rem;
            margin-bottom: 30px;
            text-align: center;
            font-weight: 700;
            position: relative;
        }

        .form-title::after {
            content: '';
            display: block;
            width: 60px;
            height: 4px;
            background: linear-gradient(135deg, #6a11cb 0%, #2575fc 100%);
            margin: 10px auto 0;
            border-radius: 2px;
        }

        .form-group {
            margin-bottom: 25px;
        }

        .form-group label {
            display: block;
            margin-bottom: 10px;
            color: #2c3e50;
            font-weight: 600;
            font-size: 15px;
            transition: color 0.3s;
        }

        .input-group {
            position: relative;
        }

        .input-group i {
            position: absolute;
            left: 18px;
            top: 50%;
            transform: translateY(-50%);
            color: #6a11cb;
            font-size: 18px;
            transition: all 0.3s;
        }

        .input-group input {
            width: 100%;
            padding: 16px 20px 16px 55px;
            border: 2px solid #e1e8ed;
            border-radius: 12px;
            font-size: 16px;
            transition: all 0.3s;
            background: white;
            color: #333;
        }

        .input-group input:focus {
            outline: none;
            border-color: #6a11cb;
            box-shadow: 0 0 0 4px rgba(106, 17, 203, 0.15);
        }

        .input-group input:focus + i {
            color: #2575fc;
        }

        .btn {
            width: 100%;
            padding: 18px;
            background: linear-gradient(135deg, #6a11cb 0%, #2575fc 100%);
            color: white;
            border: none;
            border-radius: 12px;
            font-size: 17px;
            font-weight: 600;
            cursor: pointer;
            transition: all 0.3s;
            margin-top: 10px;
            display: flex;
            align-items: center;
            justify-content: center;
            gap: 10px;
            letter-spacing: 0.5px;
        }

        .btn:hover {
            transform: translateY(-3px);
            box-shadow: 0 15px 25px rgba(106, 17, 203, 0.3);
        }

        .btn:active {
            transform: translateY(-1px);
        }

        .btn-secondary {
            background: transparent;
            color: #6a11cb;
            border: 2px solid #6a11cb;
            margin-top: 15px;
        }

        .btn-secondary:hover {
            background: rgba(106, 17, 203, 0.1);
            box-shadow: 0 10px 20px rgba(106, 17, 203, 0.2);
        }

        .toggle-link {
            text-align: center;
            margin-top: 30px;
            color: #666;
            font-size: 15px;
        }

        .toggle-link a {
            color: #6a11cb;
            text-decoration: none;
            font-weight: 700;
            cursor: pointer;
            transition: all 0.3s;
            padding: 5px 10px;
            border-radius: 6px;
        }

        .toggle-link a:hover {
            background: rgba(106, 17, 203, 0.1);
            text-decoration: underline;
        }

        .message {
            padding: 18px;
            border-radius: 12px;
            margin-bottom: 25px;
            display: none;
            animation: slideIn 0.3s ease;
            text-align: left;
            border-left: 5px solid;
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

        .message.error {
            background: rgba(231, 76, 60, 0.1);
            color: #e74c3c;
            border-left-color: #e74c3c;
        }

        .message.success {
            background: rgba(39, 174, 96, 0.1);
            color: #27ae60;
            border-left-color: #27ae60;
        }

        .message.info {
            background: rgba(52, 152, 219, 0.1);
            color: #3498db;
            border-left-color: #3498db;
        }

        .loading {
            display: inline-block;
            width: 22px;
            height: 22px;
            border: 3px solid rgba(255, 255, 255, 0.3);
            border-top: 3px solid white;
            border-radius: 50%;
            animation: spin 1s linear infinite;
        }

        @keyframes spin {
            0% { transform: rotate(0deg); }
            100% { transform: rotate(360deg); }
        }

        .forgot-password {
            text-align: right;
            margin-top: 12px;
        }

        .forgot-password a {
            color: #6a11cb;
            font-size: 14px;
            text-decoration: none;
            font-weight: 500;
            transition: all 0.3s;
        }

        .forgot-password a:hover {
            text-decoration: underline;
        }

        .admin-note {
            text-align: center;
            margin-top: 25px;
            padding: 15px;
            background: #f8f9fa;
            border-radius: 10px;
            font-size: 13px;
            color: #666;
            border-left: 4px solid #6a11cb;
        }

        .powered-by {
            text-align: center;
            margin-top: 35px;
            color: #999;
            font-size: 13px;
            padding-top: 20px;
            border-top: 1px solid #eee;
        }

        .powered-by span {
            color: #6a11cb;
            font-weight: 600;
        }

        /* Form Switch Animation */
        .form-container {
            transition: transform 0.4s ease, opacity 0.4s ease;
        }

        .form-container.hidden {
            display: none;
        }

        /* Password Strength Indicator */
        .password-strength {
            height: 4px;
            background: #eee;
            border-radius: 2px;
            margin-top: 8px;
            overflow: hidden;
        }

        .strength-bar {
            height: 100%;
            width: 0%;
            background: #e74c3c;
            transition: width 0.3s, background 0.3s;
        }

        /* Main Content Sections */
        .main-content {
            display: none;
            width: 100%;
            max-width: 1200px;
            margin: 0 auto;
            padding: 30px 20px;
            min-height: calc(100vh - 80px);
        }

        .section {
            display: none;
            animation: fadeIn 0.5s ease;
        }

        .section.active {
            display: block;
        }

        @keyframes fadeIn {
            from {
                opacity: 0;
                transform: translateY(20px);
            }
            to {
                opacity: 1;
                transform: translateY(0);
            }
        }

        /* Dashboard Header - Centered */
        .dashboard-header {
            background: linear-gradient(135deg, #2c3e50 0%, #1a252f 100%);
            color: white;
            padding: 30px;
            border-radius: 15px;
            margin-bottom: 30px;
            text-align: center;
            box-shadow: 0 10px 30px rgba(0, 0, 0, 0.3);
            width: 100%;
        }

        .dashboard-header h1 {
            font-size: 2.8rem;
            margin-bottom: 10px;
            color: #ffffff;
            text-shadow: 0 2px 10px rgba(0, 0, 0, 0.3);
        }

        .dashboard-subtitle {
            font-size: 1.2rem;
            color: #a0c8e0;
            max-width: 800px;
            margin: 0 auto;
            line-height: 1.6;
        }

        /* User Info - Centered */
        .user-header-info {
            display: flex;
            justify-content: space-between;
            align-items: center;
            margin-top: 20px;
            padding-top: 20px;
            border-top: 1px solid rgba(255, 255, 255, 0.1);
        }

        .user-welcome {
            text-align: left;
        }

        .user-welcome h3 {
            font-size: 1.4rem;
            margin-bottom: 5px;
        }

        .user-welcome p {
            color: #a0c8e0;
            font-size: 0.9rem;
        }

        .user-actions {
            display: flex;
            gap: 15px;
            align-items: center;
        }

        .user-avatar {
            width: 50px;
            height: 50px;
            background: linear-gradient(135deg, #6a11cb 0%, #2575fc 100%);
            border-radius: 50%;
            display: flex;
            align-items: center;
            justify-content: center;
            color: white;
            font-weight: bold;
            font-size: 20px;
        }

        .logout-btn {
            background: linear-gradient(135deg, #e74c3c 0%, #c0392b 100%);
            color: white;
            border: none;
            padding: 10px 20px;
            border-radius: 8px;
            font-weight: 600;
            cursor: pointer;
            transition: all 0.3s;
        }

        .logout-btn:hover {
            transform: translateY(-2px);
            box-shadow: 0 5px 15px rgba(231, 76, 60, 0.4);
        }

        /* License Section - Centered */
        .license-section {
            width: 100%;
            margin: 40px 0;
        }

        .license-card {
            background: rgba(25, 40, 50, 0.85);
            border-radius: 15px;
            padding: 30px;
            box-shadow: 0 10px 30px rgba(0, 0, 0, 0.4);
            border: 1px solid rgba(75, 181, 67, 0.3);
            text-align: center;
        }

        .license-header {
            display: flex;
            align-items: center;
            justify-content: center;
            margin-bottom: 25px;
            padding-bottom: 20px;
            border-bottom: 1px solid rgba(255, 255, 255, 0.1);
        }

        .license-icon {
            font-size: 2.5rem;
            margin-right: 15px;
            color: #4bb543;
        }

        .license-title {
            font-size: 1.8rem;
            font-weight: 700;
            color: #ffffff;
        }

        .license-key-box {
            background: rgba(0, 0, 0, 0.3);
            padding: 25px;
            border-radius: 10px;
            margin-bottom: 25px;
            text-align: center;
            border: 2px dashed #4bb543;
        }

        .license-key-display {
            font-family: 'Courier New', monospace;
            font-size: 1.8rem;
            background: rgba(0, 0, 0, 0.5);
            padding: 20px;
            border-radius: 8px;
            margin: 20px 0;
            letter-spacing: 3px;
            color: #4bb543;
            font-weight: bold;
            word-break: break-all;
            text-align: center;
        }

        .copy-btn {
            width: 100%;
            max-width: 300px;
            margin: 0 auto;
            padding: 16px;
            background: linear-gradient(135deg, #4bb543 0%, #3a9d32 100%);
            color: white;
            border: none;
            border-radius: 10px;
            font-size: 1.1rem;
            font-weight: 600;
            cursor: pointer;
            transition: all 0.3s;
            margin-top: 15px;
            display: flex;
            align-items: center;
            justify-content: center;
            gap: 10px;
        }

        .copy-btn:hover {
            transform: translateY(-2px);
            box-shadow: 0 5px 15px rgba(75, 181, 67, 0.4);
        }

        .copy-btn:disabled {
            background: linear-gradient(135deg, #95a5a6 0%, #7f8c8d 100%);
            cursor: not-allowed;
        }

        .license-info {
            display: grid;
            grid-template-columns: repeat(auto-fit, minmax(200px, 1fr));
            gap: 20px;
            margin: 25px 0;
            max-width: 800px;
            margin-left: auto;
            margin-right: auto;
        }

        .info-item {
            background: rgba(0, 0, 0, 0.2);
            padding: 20px;
            border-radius: 10px;
            text-align: center;
        }

        .info-label {
            font-size: 0.9rem;
            color: #a0c8e0;
            margin-bottom: 10px;
        }

        .info-value {
            font-size: 1.3rem;
            color: #fff;
            font-weight: 600;
        }

        .info-value.active {
            color: #4bb543;
        }

        .info-value.expired {
            color: #e74c3c;
        }

        .info-value.pending {
            color: #f39c12;
        }

        /* Download Grid - Centered */
        .download-grid {
            display: grid;
            grid-template-columns: repeat(auto-fit, minmax(350px, 1fr));
            gap: 30px;
            width: 100%;
            margin: 0 auto;
            padding: 20px 0;
        }

        .download-card {
            background: rgba(25, 40, 50, 0.85);
            border-radius: 15px;
            padding: 30px;
            box-shadow: 0 10px 30px rgba(0, 0, 0, 0.4);
            transition: transform 0.3s ease, box-shadow 0.3s ease;
            border: 1px solid rgba(64, 128, 192, 0.3);
            display: flex;
            flex-direction: column;
            height: 100%;
            text-align: left;
        }

        .download-card:hover {
            transform: translateY(-10px);
            box-shadow: 0 15px 35px rgba(0, 0, 0, 0.5);
            border-color: rgba(64, 128, 192, 0.6);
        }

        .card-header {
            display: flex;
            align-items: center;
            margin-bottom: 20px;
            padding-bottom: 20px;
            border-bottom: 1px solid rgba(255, 255, 255, 0.1);
        }

        .card-icon {
            font-size: 2.5rem;
            margin-right: 15px;
            color: #4dabf7;
        }

        .card-title {
            font-size: 1.8rem;
            font-weight: 700;
            color: #ffffff;
        }

        .card-price {
            font-size: 1.5rem;
            color: #4dabf7;
            margin-bottom: 15px;
            font-weight: 600;
        }

        .card-price span {
            font-size: 1rem;
            color: #a0c8e0;
        }

        .features-list {
            list-style: none;
            margin-bottom: 25px;
            flex-grow: 1;
        }

        .features-list li {
            padding: 8px 0;
            position: relative;
            padding-left: 25px;
        }

        .features-list li:before {
            content: "✓";
            position: absolute;
            left: 0;
            color: #4CAF50;
            font-weight: bold;
        }

        .file-info {
            background: rgba(0, 0, 0, 0.2);
            padding: 10px 15px;
            border-radius: 8px;
            margin-bottom: 20px;
            font-size: 0.9rem;
            color: #a0c8e0;
        }

        .file-info i {
            margin-right: 8px;
            color: #4dabf7;
        }

        .download-btn {
            display: block;
            text-align: center;
            background: linear-gradient(to right, #2193b0, #6dd5ed);
            color: white;
            text-decoration: none;
            padding: 16px;
            border-radius: 10px;
            font-weight: 700;
            font-size: 1.1rem;
            transition: all 0.3s ease;
            text-transform: uppercase;
            letter-spacing: 1px;
            box-shadow: 0 5px 15px rgba(33, 147, 176, 0.4);
        }

        .download-btn:hover {
            background: linear-gradient(to right, #1b7a93, #5bc0de);
            box-shadow: 0 7px 20px rgba(33, 147, 176, 0.6);
            transform: translateY(-2px);
        }

        .instructions {
            margin-top: 15px;
            font-size: 0.85rem;
            color: #a0c8e0;
            text-align: center;
        }

        /* Shop Page Styles */
        .shop-grid {
            display: grid;
            grid-template-columns: repeat(auto-fill, minmax(280px, 1fr));
            gap: 25px;
            margin-top: 30px;
        }

        .shop-item {
            background: rgba(25, 40, 50, 0.85);
            border-radius: 15px;
            padding: 25px;
            box-shadow: 0 8px 25px rgba(0, 0, 0, 0.4);
            transition: all 0.3s ease;
            border: 1px solid rgba(64, 128, 192, 0.3);
            text-align: center;
        }

        .shop-item:hover {
            transform: translateY(-8px);
            box-shadow: 0 12px 30px rgba(0, 0, 0, 0.5);
            border-color: rgba(64, 128, 192, 0.6);
        }

        .shop-item-icon {
            font-size: 3rem;
            color: #4dabf7;
            margin-bottom: 15px;
        }

        .shop-item-title {
            font-size: 1.5rem;
            font-weight: 700;
            color: #ffffff;
            margin-bottom: 10px;
        }

        .shop-item-price {
            font-size: 1.8rem;
            color: #4CAF50;
            font-weight: 700;
            margin: 15px 0;
        }

        .shop-item-features {
            list-style: none;
            margin: 20px 0;
        }

        .shop-item-features li {
            padding: 8px 0;
            color: #a0c8e0;
            border-bottom: 1px solid rgba(255, 255, 255, 0.1);
        }

        .shop-item-features li:last-child {
            border-bottom: none;
        }

        .buy-btn {
            background: linear-gradient(135deg, #27ae60 0%, #219653 100%);
            color: white;
            border: none;
            padding: 12px 25px;
            border-radius: 10px;
            font-weight: 600;
            cursor: pointer;
            transition: all 0.3s;
            width: 100%;
            margin-top: 15px;
        }

        .buy-btn:hover {
            background: linear-gradient(135deg, #219653 0%, #1e8449 100%);
            transform: translateY(-2px);
            box-shadow: 0 5px 15px rgba(39, 174, 96, 0.4);
        }

        /* Blog Page Styles */
        .blog-grid {
            display: grid;
            grid-template-columns: repeat(auto-fill, minmax(300px, 1fr));
            gap: 25px;
            margin-top: 30px;
        }

        .blog-post {
            background: rgba(25, 40, 50, 0.85);
            border-radius: 15px;
            overflow: hidden;
            box-shadow: 0 8px 25px rgba(0, 0, 0, 0.4);
            transition: all 0.3s ease;
        }

        .blog-post:hover {
            transform: translateY(-8px);
            box-shadow: 0 12px 30px rgba(0, 0, 0, 0.5);
        }

        .blog-post-img {
            height: 180px;
            background: linear-gradient(135deg, #667eea 0%, #764ba2 100%);
            display: flex;
            align-items: center;
            justify-content: center;
            color: white;
            font-size: 3rem;
        }

        .blog-post-content {
            padding: 25px;
        }

        .blog-post-title {
            font-size: 1.4rem;
            font-weight: 700;
            color: #ffffff;
            margin-bottom: 10px;
        }

        .blog-post-date {
            color: #4dabf7;
            font-size: 0.9rem;
            margin-bottom: 15px;
            display: flex;
            align-items: center;
            gap: 5px;
        }

        .blog-post-excerpt {
            color: #a0c8e0;
            line-height: 1.6;
            margin-bottom: 15px;
        }

        .read-more {
            color: #4dabf7;
            text-decoration: none;
            font-weight: 600;
            display: flex;
            align-items: center;
            gap: 5px;
        }

        /* License Actions */
        .license-actions {
            display: flex;
            gap: 15px;
            justify-content: center;
            margin-top: 20px;
            flex-wrap: wrap;
        }

        .license-action-btn {
            padding: 12px 25px;
            border-radius: 10px;
            font-weight: 600;
            cursor: pointer;
            transition: all 0.3s;
            border: none;
            display: flex;
            align-items: center;
            gap: 8px;
        }

        .license-action-btn.activate {
            background: linear-gradient(135deg, #3498db 0%, #2980b9 100%);
            color: white;
        }

        .license-action-btn.renew {
            background: linear-gradient(135deg, #f39c12 0%, #e67e22 100%);
            color: white;
        }

        .license-action-btn.transfer {
            background: linear-gradient(135deg, #9b59b6 0%, #8e44ad 100%);
            color: white;
        }

        .license-action-btn:hover {
            transform: translateY(-2px);
            box-shadow: 0 5px 15px rgba(0, 0, 0, 0.3);
        }

        /* Footer - Centered */
        footer {
            width: 100%;
            text-align: center;
            padding: 40px 0;
            margin-top: 40px;
            color: #a0c8e0;
            border-top: 1px solid rgba(255, 255, 255, 0.1);
        }

        .footer-links {
            display: flex;
            justify-content: center;
            gap: 30px;
            margin-top: 20px;
            flex-wrap: wrap;
        }

        .footer-links a {
            color: #4dabf7;
            text-decoration: none;
        }

        /* Responsive - Centered */
        @media (max-width: 768px) {
            .container {
                margin: 10px;
            }
            
            .main-content {
                padding: 20px 15px;
            }
            
            .nav-menu {
                position: fixed;
                top: 70px;
                left: 0;
                right: 0;
                background: linear-gradient(135deg, #2c3e50 0%, #1a252f 100%);
                flex-direction: column;
                padding: 20px;
                gap: 15px;
                transform: translateY(-100%);
                opacity: 0;
                transition: all 0.3s;
                box-shadow: 0 10px 30px rgba(0, 0, 0, 0.3);
                z-index: 999;
            }
            
            .nav-menu.active {
                transform: translateY(0);
                opacity: 1;
            }
            
            .mobile-menu-btn {
                display: block;
            }
            
            .nav-auth {
                flex-direction: column;
                align-items: flex-start;
            }
            
            .nav-user {
                flex-direction: column;
                align-items: flex-start;
                gap: 5px;
            }
            
            .user-email {
                font-size: 12px;
            }
            
            .dashboard-header h1 {
                font-size: 2.2rem;
            }
            
            .user-header-info {
                flex-direction: column;
                gap: 15px;
                text-align: center;
            }
            
            .user-welcome {
                text-align: center;
            }
            
            .download-grid {
                grid-template-columns: 1fr;
                padding: 0 10px;
            }
            
            .download-card {
                padding: 20px;
            }
            
            .license-key-display {
                font-size: 1.3rem;
                padding: 15px;
            }
            
            .license-info {
                grid-template-columns: 1fr;
            }
            
            .login-page {
                padding: 15px;
            }
            
            .content {
                padding: 30px 25px;
            }
            
            .header {
                padding: 30px 20px;
            }
            
            .header h1 {
                font-size: 1.8rem;
            }
            
            .logo {
                font-size: 2.5rem;
            }
            
            .shop-grid,
            .blog-grid {
                grid-template-columns: 1fr;
            }
            
            .license-actions {
                flex-direction: column;
                align-items: center;
            }
            
            .license-action-btn {
                width: 100%;
                max-width: 300px;
            }
        }

        @media (max-width: 480px) {
            .dashboard-header h1 {
                font-size: 1.8rem;
            }
            
            .license-title {
                font-size: 1.5rem;
            }
            
            .card-title {
                font-size: 1.5rem;
            }
            
            .footer-links {
                flex-direction: column;
                gap: 15px;
            }
            
            .container {
                max-width: 100%;
                border-radius: 20px;
            }
            
            .content {
                padding: 25px 20px;
            }
            
            .btn {
                padding: 16px;
                font-size: 16px;
            }
            
            .form-title {
                font-size: 1.6rem;
            }
        }
    </style>
</head>
<body>
    
    <!-- Navigation Menu -->
    <nav class="main-nav" id="mainNav" style="display: none;">
        <div class="nav-container">
            <a href="#" class="nav-logo" onclick="showSection('home')">
                <i class="fas fa-chart-line"></i>
                <span class="nav-logo-text">ZEAHONG TRADING</span>
            </a>
            
            <button class="mobile-menu-btn" onclick="toggleMobileMenu()">
                <i class="fas fa-bars"></i>
            </button>
            
            <ul class="nav-menu" id="navMenu">
                <li class="nav-item">
                    <a href="#" class="nav-link active" onclick="showSection('home')">
                        <i class="fas fa-home"></i> HOME
                    </a>
                </li>
                <li class="nav-item">
                    <a href="#" class="nav-link" onclick="showSection('shop')">
                        <i class="fas fa-shopping-cart"></i> SHOP
                    </a>
                </li>
                <li class="nav-item">
                    <a href="#" class="nav-link" onclick="showSection('blog')">
                        <i class="fas fa-blog"></i> BLOG
                    </a>
                </li>
                <li class="nav-item">
                    <a href="#" class="nav-link" onclick="showSection('license')">
                        <i class="fas fa-key"></i> MY LICENSE
                    </a>
                </li>
            </ul>
            
            <div class="nav-auth" id="navAuth">
                <!-- User info will be populated here -->
            </div>
        </div>
    </nav>

    <!-- Login Page -->
    <div class="login-page" id="loginPage">
        <div class="container">
            <div class="header">
                <div class="logo">
                    <i class="fas fa-user-shield"></i>
                </div>
                <h1>Zeahong Trading 111</h1>
                <p>Secure Authentication System</p>
            </div>

            <div class="content">
                <div class="message info" id="infoMessage">
                    <i class="fas fa-info-circle"></i> Initializing system...
                </div>
                
                <div class="message error" id="errorMessage"></div>
                <div class="message success" id="successMessage"></div>

                <!-- Login Form -->
                <div class="form-container" id="loginForm">
                    <h2 class="form-title">Welcome Back</h2>
                    <form id="loginFormElement">
                        <div class="form-group">
                            <label for="loginEmail">Email Address</label>
                            <div class="input-group">
                                <i class="fas fa-envelope"></i>
                                <input type="email" id="loginEmail" placeholder="Enter your email" required>
                            </div>
                        </div>
                        <div class="form-group">
                            <label for="loginPassword">Password</label>
                            <div class="input-group">
                                <i class="fas fa-lock"></i>
                                <input type="password" id="loginPassword" placeholder="Enter your password" required>
                            </div>
                            <div class="forgot-password">
                                <a href="#" onclick="showForgotPassword()">Forgot password?</a>
                            </div>
                        </div>
                        <button type="submit" class="btn" id="loginBtn">
                            <i class="fas fa-sign-in-alt"></i> Sign In
                        </button>
                    </form>
                    <div class="toggle-link">
                        Don't have an account? <a onclick="showSignup()">Sign up now</a>
                    </div>
                </div>

                <!-- Signup Form -->
                <div class="form-container" id="signupForm" style="display: none;">
                    <h2 class="form-title">Create Account</h2>
                    <form id="signupFormElement">
                        <div class="form-group">
                            <label for="signupName">Full Name</label>
                            <div class="input-group">
                                <i class="fas fa-user"></i>
                                <input type="text" id="signupName" placeholder="Enter your full name" required>
                            </div>
                        </div>
                        <div class="form-group">
                            <label for="signupEmail">Email Address</label>
                            <div class="input-group">
                                <i class="fas fa-envelope"></i>
                                <input type="email" id="signupEmail" placeholder="Enter your email" required>
                            </div>
                        </div>
                        <div class="form-group">
                            <label for="signupPassword">Password</label>
                            <div class="input-group">
                                <i class="fas fa-lock"></i>
                                <input type="password" id="signupPassword" placeholder="Create password (min. 6 chars)" required minlength="6">
                            </div>
                            <div class="password-strength">
                                <div class="strength-bar" id="passwordStrength"></div>
                            </div>
                        </div>
                        <div class="form-group">
                            <label for="confirmPassword">Confirm Password</label>
                            <div class="input-group">
                                <i class="fas fa-lock"></i>
                                <input type="password" id="confirmPassword" placeholder="Confirm your password" required minlength="6">
                            </div>
                        </div>
                        <button type="submit" class="btn" id="signupBtn">
                            <i class="fas fa-user-plus"></i> Create Account
                        </button>
                    </form>
                    <div class="toggle-link">
                        Already have an account? <a onclick="showLogin()">Sign in here</a>
                    </div>
                </div>

                <!-- Forgot Password Form -->
                <div class="form-container" id="forgotPasswordForm" style="display: none;">
                    <h2 class="form-title">Reset Password</h2>
                    <form id="forgotPasswordFormElement">
                        <div class="form-group">
                            <label for="resetEmail">Email Address</label>
                            <div class="input-group">
                                <i class="fas fa-envelope"></i>
                                <input type="email" id="resetEmail" placeholder="Enter your email" required>
                            </div>
                            <p style="color: #666; font-size: 14px; margin-top: 10px;">
                                We'll send you a link to reset your password.
                            </p>
                        </div>
                        <button type="submit" class="btn" id="resetBtn">
                            <i class="fas fa-paper-plane"></i> Send Reset Link
                        </button>
                        <button type="button" class="btn btn-secondary" onclick="showLogin()">
                            <i class="fas fa-arrow-left"></i> Back to Login
                        </button>
                    </form>
                </div>

                <div class="powered-by">
                    Powered by <span>Firebase Authentication</span>
                </div>
            </div>
        </div>
    </div>

    <!-- Main Content -->
    <div class="main-content" id="mainContent">
        <!-- Home Section -->
        <section class="section active" id="homeSection">
            <header class="dashboard-header">
                <h1><i class="fas fa-download"></i> Trading Tools Download Center</h1>
                <p class="dashboard-subtitle">Download your purchased trading tools, expert advisors, and configurations. All files are pre-configured and ready to use with your trading platforms.</p>
                <a href="https://t.me/ZEAHONGMOD" style="color: #4dabf7; text-decoration: none; font-weight: 600; margin-top: 10px; display: inline-block;">Get License</a>
                <div class="user-header-info">
                    <div class="user-welcome">
                        <h3>Welcome back, <span id="welcomeUserName">User</span>!</h3>
                        <p>Signed in as: <span id="userEmailDisplay"></span></p>
                    </div>
                    <div class="user-actions">
                        <div class="user-avatar" id="userAvatar">U</div>
                        <button class="logout-btn" onclick="logout()">
                            <i class="fas fa-sign-out-alt"></i> Logout
                        </button>
                    </div>
                </div>
            </header>
            
            <!-- Download Grid -->
            <div class="download-grid">
                <!-- Trading VPS Card -->
                <div class="download-card">
                    <div class="card-header">
                        <div class="card-icon">
                            <i class="fas fa-server"></i>
                        </div>
                       <h2 class="card-title">Indicator Tview</h2>
                    </div>
                    
                    <div class="card-price">$15.00 <span>/LifeTime</span></div>
                    
                    <ul class="features-list">
                        <li>Order Block</li>
                        <li>Volume Block</li>
                        <li>Pre-installed TradingView</li>
                    </ul>
                    
                    <div class="file-info">
                        <i class="fas fa-file-archive"></i> File size: <?php echo $file_sizes['trading_vps']; ?> • ZIP format
                    </div>
                    
                    <a href="<?php echo $download_links['trading_vps']; ?>" class="download-btn" download onclick="return confirmDownload('Trading VPS')">
                        <i class="fas fa-download"></i> Download VPS Setup
                    </a>
                    <p class="instructions">Includes setup guide and configuration files</p>
                </div>
                
                <!-- Trading Robot Card -->
                <div class="download-card">
                    <div class="card-header">
                        <div class="card-icon">
                            <i class="fas fa-robot"></i>
                        </div>
                        <h2 class="card-title">Trading Robot</h2>
                    </div>
                    
                    <div class="card-price">$20.00 <span>/month</span></div>
                    
                    <ul class="features-list">
                        <li>EA for MetaTrader 5</li>
                        <li>Grid trading system</li>
                        <li>Dynamic lot sizing</li>
                        <li>Adaptive trading</li>
                        <li>Smart hedge system</li>
                        <li>Smart lock system</li>
                        <li>+9 more advanced features</li>
                    </ul>
                    
                    <div class="file-info">
                        <i class="fas fa-file-archive"></i> File size: <?php echo $file_sizes['trading_robot']; ?> • Includes settings files
                    </div>
                    
                    <a href="<?php echo $download_links['trading_robot']; ?>" class="download-btn" download onclick="return confirmDownload('Trading Robot')">
                        <i class="fas fa-download"></i> Download EA & Settings
                    </a>
                    <p class="instructions">Extract to your MT5 Experts folder</p>
                </div>
                
                <!-- BTrader Tools Card -->
                <div class="download-card">
                    <div class="card-header">
                        <div class="card-icon">
                            <i class="fas fa-tools"></i>
                        </div>
                       <h2 class="card-title">Setting Tools</h2>
                    </div>
                    
                    <div class="card-price">$00.00 <span>/month</span></div>
                    
                    <ul class="features-list">
                        <li>3k-5K Balance</li>
                        <li>30k-50K Balance</li>
                        <li>300k-500K Balance</li>
                    </ul>
                    
                    <div class="file-info">
                        <i class="fas fa-file-archive"></i> File size: <?php echo $file_sizes['btrader_tools']; ?> • Complete package
                    </div>
                    
                    <a href="<?php echo $download_links['btrader_tools']; ?>" class="download-btn" download onclick="return confirmDownload('BTrader Tools')">
                        <i class="fas fa-download"></i> Download Toolkit
                    </a>
                    <p class="instructions">Full trading toolkit with installation guide</p>
                </div>
            </div>
            
            <footer>
                <p>Need help with installation? Contact support Telegram @ZEAHONGMOD</p>
                <p>All downloads are for authorized customers only. Unauthorized distribution is prohibited.</p>
                
                <div class="footer-links">
                    <a href="#">Terms of Service</a>
                    <a href="#">Privacy Policy</a>
                    <a href="https://t.me/ZEAHONGMOD">Support Center</a>
                    <a href="https://t.me/ZEAHONGMOD">Get License</a>
                </div>
                
                <p style="margin-top: 20px;">&copy; <?php echo date('Y'); ?> Trading Tools Download Center. All rights reserved.</p>
            </footer>
        </section>

        <!-- Shop Section -->
        <section class="section" id="shopSection">
            <header class="dashboard-header">
                <h1><i class="fas fa-shopping-cart"></i> Trading Tools Shop</h1>
                <p class="dashboard-subtitle">Browse and purchase premium trading tools, expert advisors, and indicators to enhance your trading experience.</p>
            </header>
            
            <div class="shop-grid">
                <div class="shop-item">
                    <div class="shop-item-icon">
                        <i class="fas fa-chart-line"></i>
                    </div>
                    <h3 class="shop-item-title">Pro Indicators Pack</h3>
                    <div class="shop-item-price">$49.99</div>
                    <ul class="shop-item-features">
                        <li>15+ Advanced Indicators</li>
                        <li>Real-time Alerts</li>
                        <li>Custom Timeframes</li>
                        <li>Lifetime Updates</li>
                        <li>24/7 Support</li>
                    </ul>
                    <button class="buy-btn" onclick="purchaseProduct('Pro Indicators Pack')">
                        <i class="fas fa-shopping-cart"></i> Buy Now
                    </button>
                </div>
                
                <div class="shop-item">
                    <div class="shop-item-icon">
                        <i class="fas fa-robot"></i>
                    </div>
                    <h3 class="shop-item-title">Gold Robot EA</h3>
                    <div class="shop-item-price">$99.99</div>
                    <ul class="shop-item-features">
                        <li>Fully Automated Trading</li>
                        <li>Risk Management System</li>
                        <li>Multi-currency Support</li>
                        <li>Backtesting Included</li>
                        <li>1 Year Support</li>
                    </ul>
                    <button class="buy-btn" onclick="purchaseProduct('Gold Robot EA')">
                        <i class="fas fa-shopping-cart"></i> Buy Now
                    </button>
                </div>
                
                <div class="shop-item">
                    <div class="shop-item-icon">
                        <i class="fas fa-tools"></i>
                    </div>
                    <h3 class="shop-item-title">Complete Toolkit</h3>
                    <div class="shop-item-price">$149.99</div>
                    <ul class="shop-item-features">
                        <li>All Indicators + EA</li>
                        <li>Custom Scripts</li>
                        <li>Templates & Layouts</li>
                        <li>Video Tutorials</li>
                        <li>Priority Support</li>
                    </ul>
                    <button class="buy-btn" onclick="purchaseProduct('Complete Toolkit')">
                        <i class="fas fa-shopping-cart"></i> Buy Now
                    </button>
                </div>
                
                <div class="shop-item">
                    <div class="shop-item-icon">
                        <i class="fas fa-graduation-cap"></i>
                    </div>
                    <h3 class="shop-item-title">Master Course</h3>
                    <div class="shop-item-price">$199.99</div>
                    <ul class="shop-item-features">
                        <li>20+ Hours Video Content</li>
                        <li>Live Trading Sessions</li>
                        <li>Private Community Access</li>
                        <li>Personal Mentoring</li>
                        <li>Certification</li>
                    </ul>
                    <button class="buy-btn" onclick="purchaseProduct('Master Course')">
                        <i class="fas fa-shopping-cart"></i> Buy Now
                    </button>
                </div>
            </div>
        </section>

        <!-- Blog Section -->
        <section class="section" id="blogSection">
            <header class="dashboard-header">
                <h1><i class="fas fa-blog"></i> Trading Blog & News</h1>
                <p class="dashboard-subtitle">Latest updates, trading strategies, market analysis, and educational content to help you succeed in trading.</p>
            </header>
            
            <div class="blog-grid">
                <div class="blog-post">
                    <div class="blog-post-img">
                        <i class="fas fa-chart-bar"></i>
                    </div>
                    <div class="blog-post-content">
                        <h3 class="blog-post-title">Market Analysis: Q4 2024 Trends</h3>
                        <div class="blog-post-date">
                            <i class="far fa-calendar"></i> December 15, 2024
                        </div>
                        <p class="blog-post-excerpt">
                            Discover the key market trends and trading opportunities for the final quarter of 2024. Our expert analysis covers forex, crypto, and stock markets.
                        </p>
                        <a href="#" class="read-more" onclick="readBlogPost('Market Analysis')">
                            Read More <i class="fas fa-arrow-right"></i>
                        </a>
                    </div>
                </div>
                
                <div class="blog-post">
                    <div class="blog-post-img">
                        <i class="fas fa-robot"></i>
                    </div>
                    <div class="blog-post-content">
                        <h3 class="blog-post-title">How to Optimize Your EA Settings</h3>
                        <div class="blog-post-date">
                            <i class="far fa-calendar"></i> December 10, 2024
                        </div>
                        <p class="blog-post-excerpt">
                            Learn the best practices for configuring your Expert Advisor for maximum profitability and minimal risk in different market conditions.
                        </p>
                        <a href="#" class="read-more" onclick="readBlogPost('EA Optimization')">
                            Read More <i class="fas fa-arrow-right"></i>
                        </a>
                    </div>
                </div>
                
                <div class="blog-post">
                    <div class="blog-post-img">
                        <i class="fas fa-shield-alt"></i>
                    </div>
                    <div class="blog-post-content">
                        <h3 class="blog-post-title">Risk Management Strategies for 2025</h3>
                        <div class="blog-post-date">
                            <i class="far fa-calendar"></i> December 5, 2024
                        </div>
                        <p class="blog-post-excerpt">
                            Essential risk management techniques every trader should implement to protect their capital and ensure long-term success in trading.
                        </p>
                        <a href="#" class="read-more" onclick="readBlogPost('Risk Management')">
                            Read More <i class="fas fa-arrow-right"></i>
                        </a>
                    </div>
                </div>
                
                <div class="blog-post">
                    <div class="blog-post-img">
                        <i class="fas fa-mobile-alt"></i>
                    </div>
                    <div class="blog-post-content">
                        <h3 class="blog-post-title">Mobile Trading: Tips & Tricks</h3>
                        <div class="blog-post-date">
                            <i class="far fa-calendar"></i> November 28, 2024
                        </div>
                        <p class="blog-post-excerpt">
                            Master mobile trading with our comprehensive guide. Learn how to trade effectively from anywhere using your smartphone or tablet.
                        </p>
                        <a href="#" class="read-more" onclick="readBlogPost('Mobile Trading')">
                            Read More <i class="fas fa-arrow-right"></i>
                        </a>
                    </div>
                </div>
            </div>
        </section>

        <!-- License Section -->
        <section class="section" id="licenseSection">
            <header class="dashboard-header">
                <h1><i class="fas fa-key"></i> My License Management</h1>
                <p class="dashboard-subtitle">Manage your licenses, check activation status, and get support for your purchased products.</p>
            </header>
            
            <div class="license-section">
                <div class="license-card">
                    <div class="license-header">
                        <div class="license-icon">
                            <i class="fas fa-key"></i>
                        </div>
                        <h2 class="license-title">Your MT5 EA License Key</h2>
                    </div>
                    
                    <div class="license-key-box">
                        <p style="color: #a0c8e0; margin-bottom: 15px;">Use this license key to activate your Expert Advisor in MetaTrader 5:</p>
                        
                        <div class="license-key-display" id="licenseKeyDisplayMain">
                            <i class="fas fa-spinner fa-spin"></i> Loading license key...
                        </div>
                        
                        <button class="copy-btn" onclick="copyLicenseKey()" id="copyButtonMain">
                            <i class="fas fa-copy"></i> Copy License Key
                        </button>
                        
                        <div class="license-info">
                            <div class="info-item">
                                <div class="info-label">Status</div>
                                <div class="info-value active" id="licenseStatusMain">Loading...</div>
                            </div>
                            <div class="info-item">
                                <div class="info-label">Expires</div>
                                <div class="info-value" id="licenseExpiryMain">Loading...</div>
                            </div>
                            <div class="info-item">
                                <div class="info-label">Device</div>
                                <div class="info-value" id="deviceStatusMain">Loading...</div>
                            </div>
                        </div>
                        
                        <div class="license-actions">
                            <button class="license-action-btn activate" onclick="activateLicense()">
                                <i class="fas fa-play-circle"></i> Activate License
                            </button>
                            <button class="license-action-btn renew" onclick="renewLicense()">
                                <i class="fas fa-sync-alt"></i> Renew License
                            </button>
                            <button class="license-action-btn transfer" onclick="transferLicense()">
                                <i class="fas fa-exchange-alt"></i> Transfer License
                            </button>
                        </div>
                    </div>
                    
                    <div style="margin-top: 25px; padding: 20px; background: rgba(75, 181, 67, 0.1); border-radius: 10px; border-left: 4px solid #4bb543; text-align: left;">
                        <h3 style="color: #fff; margin-bottom: 10px; display: flex; align-items: center; gap: 10px;">
                            <i class="fas fa-info-circle"></i> How to Use Your License:
                        </h3>
                        <ol style="color: #a0c8e0; padding-left: 20px; margin-top: 10px;">
                            <li style="margin-bottom: 8px;">Copy your license key above</li>
                            <li style="margin-bottom: 8px;">Open MetaTrader 5 and navigate to your EA settings</li>
                            <li style="margin-bottom: 8px;">Paste the license key in the designated field</li>
                            <li style="margin-bottom: 8px;">Save settings and restart your EA</li>
                            <li>Your EA will be activated for one device only</li>
                        </ol>
                    </div>
                    
                    <div style="margin-top: 30px; padding: 25px; background: rgba(52, 152, 219, 0.1); border-radius: 10px; border-left: 4px solid #3498db;">
                        <h3 style="color: #fff; margin-bottom: 15px; display: flex; align-items: center; gap: 10px;">
                            <i class="fas fa-headset"></i> License Support
                        </h3>
                        <p style="color: #a0c8e0; margin-bottom: 15px;">
                            Need help with your license? Contact our support team for assistance:
                        </p>
                        <div style="display: flex; gap: 15px; flex-wrap: wrap;">
                            <button class="btn" onclick="contactSupport()" style="max-width: 200px;">
                                <i class="fas fa-envelope"></i> Email Support
                            </button>
                            <button class="btn btn-secondary" onclick="window.open('https://t.me/ZEAHONGMOD', '_blank')" style="max-width: 200px;">
                                <i class="fab fa-telegram"></i> Telegram Support
                            </button>
                        </div>
                    </div>
                    
                    <!-- Purchase License Section -->
                    <div style="margin-top: 30px; padding: 25px; background: rgba(155, 89, 182, 0.1); border-radius: 10px; border-left: 4px solid #9b59b6;">
                        <h3 style="color: #fff; margin-bottom: 15px; display: flex; align-items: center; gap: 10px;">
                            <i class="fas fa-shopping-cart"></i> Purchase New License
                        </h3>
                        <p style="color: #a0c8e0; margin-bottom: 15px;">
                            Don't have a license yet? Purchase one to unlock all features:
                        </p>
                        <div style="display: flex; gap: 15px; flex-wrap: wrap;">
                            <button class="btn" onclick="purchaseLicense('basic')" style="max-width: 200px; background: linear-gradient(135deg, #3498db 0%, #2980b9 100%);">
                                <i class="fas fa-star"></i> Basic License
                            </button>
                            <button class="btn" onclick="purchaseLicense('pro')" style="max-width: 200px; background: linear-gradient(135deg, #f39c12 0%, #e67e22 100%);">
                                <i class="fas fa-crown"></i> Pro License
                            </button>
                            <button class="btn" onclick="purchaseLicense('ultimate')" style="max-width: 200px; background: linear-gradient(135deg, #e74c3c 0%, #c0392b 100%);">
                                <i class="fas fa-rocket"></i> Ultimate License
                            </button>
                        </div>
                    </div>
                </div>
            </div>
        </section>
    </div>

    <!-- Firebase SDK -->
    <script type="module">
        // Import Firebase modules
        import { initializeApp } from "https://www.gstatic.com/firebasejs/10.7.1/firebase-app.js";
        import { 
            getAuth, 
            createUserWithEmailAndPassword, 
            signInWithEmailAndPassword,
            signOut,
            onAuthStateChanged,
            updateProfile,
            sendPasswordResetEmail
        } from "https://www.gstatic.com/firebasejs/10.7.1/firebase-auth.js";

        // Firebase Configuration
        const firebaseConfig = {
            apiKey: "AIzaSyC-t-EcFuBQFndXsLrXD_iYc1c8wQz1-RU",
            authDomain: "zeahong-5c2a1.firebaseapp.com",
            databaseURL: "https://zeahong-5c2a1-default-rtdb.asia-southeast1.firebasedatabase.app",
            projectId: "zeahong-5c2a1",
            storageBucket: "zeahong-5c2a1.appspot.com",
            messagingSenderId: "41602694535",
            appId: "1:41602694535:web:def692222d0d736f906722"
        };

        // Initialize Firebase
        const app = initializeApp(firebaseConfig);
        const auth = getAuth(app);

        // DOM Elements
        const loginPage = document.getElementById('loginPage');
        const mainNav = document.getElementById('mainNav');
        const mainContent = document.getElementById('mainContent');
        const welcomeUserName = document.getElementById('welcomeUserName');
        const userEmailDisplay = document.getElementById('userEmailDisplay');
        const userAvatar = document.getElementById('userAvatar');
        const navAuth = document.getElementById('navAuth');
        const navMenu = document.getElementById('navMenu');

        // Global variable for user license
        window.userLicenseData = null;

        // Initialize
        init();

        async function init() {
            try {
                // Setup auth state listener
                setupAuthListener();
                
                // Setup password strength indicator
                setupPasswordStrength();
                
                // Hide info message after 2 seconds
                setTimeout(() => {
                    const infoMsg = document.getElementById('infoMessage');
                    if (infoMsg) infoMsg.style.display = 'none';
                }, 2000);
                
            } catch (error) {
                console.error('Initialization error:', error);
                showMessage('error', `Failed to initialize: ${error.message}`);
            }
        }

        // Setup password strength indicator
        function setupPasswordStrength() {
            const passwordInput = document.getElementById('signupPassword');
            const strengthBar = document.getElementById('passwordStrength');
            
            if (passwordInput && strengthBar) {
                passwordInput.addEventListener('input', function() {
                    const password = this.value;
                    let strength = 0;
                    
                    if (password.length >= 6) strength += 25;
                    if (/[A-Z]/.test(password)) strength += 25;
                    if (/[0-9]/.test(password)) strength += 25;
                    if (/[^A-Za-z0-9]/.test(password)) strength += 25;
                    
                    strengthBar.style.width = strength + '%';
                    
                    if (strength < 50) {
                        strengthBar.style.background = '#e74c3c';
                    } else if (strength < 75) {
                        strengthBar.style.background = '#f39c12';
                    } else {
                        strengthBar.style.background = '#2ecc71';
                    }
                });
            }
        }

        // Setup authentication state listener
        function setupAuthListener() {
            onAuthStateChanged(auth, async (user) => {
                if (user) {
                    // User is signed in
                    showMainApp(user);
                    await updateUserLicenseInfo(user.email);
                    updateNavUserInfo(user);
                } else {
                    // User is signed out
                    showLoginPage();
                }
            }, (error) => {
                console.error('Auth state error:', error);
                showMessage('error', `Authentication error: ${error.message}`);
            });
        }

        // Show main app with navigation
        function showMainApp(user) {
            loginPage.style.display = 'none';
            mainNav.style.display = 'block';
            mainContent.style.display = 'block';
            
            // Update user info
            const userDisplayName = user.displayName || user.email.split('@')[0];
            const userInitial = userDisplayName.charAt(0).toUpperCase();
            
            welcomeUserName.textContent = userDisplayName;
            userEmailDisplay.textContent = user.email;
            userAvatar.textContent = userInitial;
            
            // Update page title
            document.title = `Zeahong Trading - Dashboard`;
            
            // Show home section by default
            showSection('home');
        }

        // Update navigation user info
        function updateNavUserInfo(user) {
            const userDisplayName = user.displayName || user.email.split('@')[0];
            const userInitial = userDisplayName.charAt(0).toUpperCase();
            
            navAuth.innerHTML = `
                <div class="nav-user">
                    <div class="user-avatar-small">${userInitial}</div>
                    <div>
                        <div style="color: white; font-weight: 600;">${userDisplayName}</div>
                        <div class="user-email">${user.email}</div>
                    </div>
                </div>
                <button class="nav-logout" onclick="logout()">
                    <i class="fas fa-sign-out-alt"></i> Logout
                </button>
            `;
        }

        // Show login page
        function showLoginPage() {
            mainNav.style.display = 'none';
            mainContent.style.display = 'none';
            loginPage.style.display = 'flex';
            document.title = 'Zeahong Trading - Login';
        }

        // Show/Hide sections
        window.showSection = function(sectionId) {
            // Hide all sections
            document.querySelectorAll('.section').forEach(section => {
                section.classList.remove('active');
            });
            
            // Remove active class from all nav links
            document.querySelectorAll('.nav-link').forEach(link => {
                link.classList.remove('active');
            });
            
            // Show selected section
            const section = document.getElementById(sectionId + 'Section');
            if (section) {
                section.classList.add('active');
            }
            
            // Update active nav link
            const navLink = document.querySelector(`[onclick="showSection('${sectionId}')"]`);
            if (navLink) {
                navLink.classList.add('active');
            }
            
            // Update page title
            const sectionNames = {
                'home': 'Home',
                'shop': 'Shop',
                'blog': 'Blog',
                'license': 'My License'
            };
            document.title = `Zeahong Trading - ${sectionNames[sectionId]}`;
            
            // Close mobile menu if open
            if (window.innerWidth <= 768) {
                navMenu.classList.remove('active');
            }
            
            // Refresh license info when license section is shown
            if (sectionId === 'license' && auth.currentUser) {
                updateUserLicenseInfo(auth.currentUser.email);
            }
        };

        // Toggle mobile menu
        window.toggleMobileMenu = function() {
            navMenu.classList.toggle('active');
        };

        // Close mobile menu when clicking outside
        document.addEventListener('click', function(event) {
            if (window.innerWidth <= 768) {
                const isClickInsideNav = mainNav.contains(event.target);
                const isMobileMenuBtn = event.target.closest('.mobile-menu-btn');
                
                if (!isClickInsideNav && !isMobileMenuBtn) {
                    navMenu.classList.remove('active');
                }
            }
        });

        // Update user license information
        async function updateUserLicenseInfo(userEmail) {
            try {
                console.log('Fetching license for:', userEmail);
                
                const formData = new URLSearchParams();
                formData.append('action', 'get_license');
                formData.append('email', userEmail);
                
                const response = await fetch('', {
                    method: 'POST',
                    headers: {
                        'Content-Type': 'application/x-www-form-urlencoded',
                    },
                    body: formData
                });
                
                if (!response.ok) {
                    throw new Error(`HTTP error! status: ${response.status}`);
                }
                
                const data = await response.text();
                console.log('License response:', data);
                
                // Store license data globally
                window.userLicenseData = data;
                
                // Update both license displays
                updateLicenseDisplay(data, 'licenseKeyDisplay', 'licenseStatus', 'licenseExpiry', 'deviceStatus', 'copyButton');
                updateLicenseDisplay(data, 'licenseKeyDisplayMain', 'licenseStatusMain', 'licenseExpiryMain', 'deviceStatusMain', 'copyButtonMain');
                
            } catch (error) {
                console.error('Error fetching license info:', error);
                const errorHtml = '<span style="color: #e74c3c;">Error loading license. Please try again.</span>';
                document.getElementById('licenseKeyDisplay').innerHTML = errorHtml;
                document.getElementById('licenseKeyDisplayMain').innerHTML = errorHtml;
                
                // Update status displays
                document.getElementById('licenseStatus').textContent = 'Error';
                document.getElementById('licenseStatus').className = 'info-value expired';
                document.getElementById('licenseExpiry').textContent = 'N/A';
                document.getElementById('deviceStatus').textContent = 'N/A';
                
                document.getElementById('licenseStatusMain').textContent = 'Error';
                document.getElementById('licenseStatusMain').className = 'info-value expired';
                document.getElementById('licenseExpiryMain').textContent = 'N/A';
                document.getElementById('deviceStatusMain').textContent = 'N/A';
            }
        }

        function updateLicenseDisplay(data, keyDisplayId, statusId, expiryId, deviceId, copyBtnId) {
            const keyDisplay = document.getElementById(keyDisplayId);
            const statusDisplay = document.getElementById(statusId);
            const expiryDisplay = document.getElementById(expiryId);
            const deviceDisplay = document.getElementById(deviceId);
            const copyBtn = document.getElementById(copyBtnId);
            
            if (!data || data === 'NOT_FOUND' || data === 'INVALID') {
                // No license found
                keyDisplay.innerHTML = '<span style="color: #e74c3c;">No license assigned</span>';
                keyDisplay.style.fontSize = '1.2rem';
                statusDisplay.textContent = 'No License';
                statusDisplay.className = 'info-value expired';
                expiryDisplay.textContent = 'N/A';
                deviceDisplay.textContent = 'N/A';
                
                if (copyBtn) {
                    copyBtn.disabled = true;
                    copyBtn.innerHTML = '<i class="fas fa-ban"></i> No License Available';
                    copyBtn.style.background = 'linear-gradient(135deg, #95a5a6 0%, #7f8c8d 100%)';
                }
                
                window.userLicenseKey = null;
                
            } else if (data.includes('|')) {
                // Parse license info
                const parts = data.split('|');
                console.log('Parsed license parts:', parts);
                
                if (parts.length >= 4) {
                    const licenseKey = parts[0];
                    const status = parts[1];
                    const expiry = parts[2];
                    const device = parts[3];
                    
                    // Display license
                    keyDisplay.textContent = licenseKey;
                    keyDisplay.style.color = '#4bb543';
                    keyDisplay.style.fontSize = '1.8rem';
                    
                    statusDisplay.textContent = status;
                    
                    // Set appropriate status class
                    if (status === 'active') {
                        statusDisplay.className = 'info-value active';
                    } else if (status === 'expired') {
                        statusDisplay.className = 'info-value expired';
                    } else if (status === 'pending') {
                        statusDisplay.className = 'info-value pending';
                    } else {
                        statusDisplay.className = 'info-value expired';
                    }
                    
                    expiryDisplay.textContent = expiry;
                    deviceDisplay.textContent = device || 'Not Activated';
                    
                    // Enable copy button
                    if (copyBtn) {
                        copyBtn.disabled = false;
                        copyBtn.innerHTML = '<i class="fas fa-copy"></i> Copy License Key';
                        copyBtn.style.background = 'linear-gradient(135deg, #4bb543 0%, #3a9d32 100%)';
                    }
                    
                    // Store for copying
                    window.userLicenseKey = licenseKey;
                } else {
                    throw new Error('Invalid license data format');
                }
            } else {
                throw new Error('Unexpected license response format');
            }
        }

        // Show/Hide Forms with animation
        window.showSignup = function() {
            const loginForm = document.getElementById('loginForm');
            const signupForm = document.getElementById('signupForm');
            const forgotPasswordForm = document.getElementById('forgotPasswordForm');
            
            loginForm.style.display = 'none';
            signupForm.style.display = 'block';
            forgotPasswordForm.style.display = 'none';
            clearMessages();
            
            // Focus on first input
            setTimeout(() => {
                document.getElementById('signupName').focus();
            }, 100);
        };

        window.showLogin = function() {
            const loginForm = document.getElementById('loginForm');
            const signupForm = document.getElementById('signupForm');
            const forgotPasswordForm = document.getElementById('forgotPasswordForm');
            
            signupForm.style.display = 'none';
            loginForm.style.display = 'block';
            forgotPasswordForm.style.display = 'none';
            clearMessages();
            
            // Focus on email input
            setTimeout(() => {
                document.getElementById('loginEmail').focus();
            }, 100);
        };

        window.showForgotPassword = function() {
            const loginForm = document.getElementById('loginForm');
            const signupForm = document.getElementById('signupForm');
            const forgotPasswordForm = document.getElementById('forgotPasswordForm');
            
            loginForm.style.display = 'none';
            signupForm.style.display = 'none';
            forgotPasswordForm.style.display = 'block';
            clearMessages();
            
            // Focus on email input
            setTimeout(() => {
                document.getElementById('resetEmail').focus();
            }, 100);
        };

        // Clear messages
        function clearMessages() {
            const errorMsg = document.getElementById('errorMessage');
            const successMsg = document.getElementById('successMessage');
            
            if (errorMsg) errorMsg.style.display = 'none';
            if (successMsg) successMsg.style.display = 'none';
        }

        // Show message
        function showMessage(type, text) {
            clearMessages();
            
            if (type === 'error') {
                const errorMsg = document.getElementById('errorMessage');
                if (errorMsg) {
                    errorMsg.innerHTML = `<i class="fas fa-exclamation-circle"></i> ${text}`;
                    errorMsg.style.display = 'block';
                }
            } else if (type === 'success') {
                const successMsg = document.getElementById('successMessage');
                if (successMsg) {
                    successMsg.innerHTML = `<i class="fas fa-check-circle"></i> ${text}`;
                    successMsg.style.display = 'block';
                    
                    setTimeout(() => {
                        successMsg.style.display = 'none';
                    }, 3000);
                }
            }
        }

        // Login form submission
        document.getElementById('loginFormElement').addEventListener('submit', async (e) => {
            e.preventDefault();
            
            const email = document.getElementById('loginEmail').value;
            const password = document.getElementById('loginPassword').value;
            
            if (!email || !password) {
                showMessage('error', 'Please fill in all fields');
                return;
            }
            
            const loginBtn = document.getElementById('loginBtn');
            const originalText = loginBtn.innerHTML;
            loginBtn.innerHTML = '<div class="loading"></div> Signing in...';
            loginBtn.disabled = true;
            
            try {
                const userCredential = await signInWithEmailAndPassword(auth, email, password);
                
                // Store user in session (for PHP)
                await fetch('save_session.php', {
                    method: 'POST',
                    headers: {
                        'Content-Type': 'application/json',
                    },
                    body: JSON.stringify({
                        email: userCredential.user.email,
                        name: userCredential.user.displayName || userCredential.user.email.split('@')[0]
                    })
                });
                
                document.getElementById('loginFormElement').reset();
                showMessage('success', 'Login successful!');
                
            } catch (error) {
                console.error('Login error:', error);
                
                let errorMsg = 'Login failed. ';
                
                switch(error.code) {
                    case 'auth/user-not-found':
                        errorMsg += 'No account found with this email.';
                        break;
                    case 'auth/wrong-password':
                        errorMsg += 'Incorrect password.';
                        break;
                    case 'auth/invalid-email':
                        errorMsg += 'Invalid email address.';
                        break;
                    default:
                        errorMsg += error.message;
                }
                
                showMessage('error', errorMsg);
                
            } finally {
                loginBtn.innerHTML = originalText;
                loginBtn.disabled = false;
            }
        });

        // Signup form submission
        document.getElementById('signupFormElement').addEventListener('submit', async (e) => {
            e.preventDefault();
            
            const name = document.getElementById('signupName').value;
            const email = document.getElementById('signupEmail').value;
            const password = document.getElementById('signupPassword').value;
            const confirmPassword = document.getElementById('confirmPassword').value;
            
            if (!name || !email || !password || !confirmPassword) {
                showMessage('error', 'Please fill in all fields');
                return;
            }
            
            if (password !== confirmPassword) {
                showMessage('error', 'Passwords do not match');
                return;
            }
            
            if (password.length < 6) {
                showMessage('error', 'Password must be at least 6 characters');
                return;
            }
            
            const signupBtn = document.getElementById('signupBtn');
            const originalText = signupBtn.innerHTML;
            signupBtn.innerHTML = '<div class="loading"></div> Creating account...';
            signupBtn.disabled = true;
            
            try {
                const userCredential = await createUserWithEmailAndPassword(auth, email, password);
                
                await updateProfile(userCredential.user, {
                    displayName: name
                });
                
                document.getElementById('signupFormElement').reset();
                showMessage('success', 'Account created successfully! Welcome!');
                
            } catch (error) {
                console.error('Signup error:', error);
                
                let errorMsg = 'Signup failed. ';
                
                switch(error.code) {
                    case 'auth/email-already-in-use':
                        errorMsg += 'This email is already registered.';
                        break;
                    default:
                        errorMsg += error.message;
                }
                
                showMessage('error', errorMsg);
                
            } finally {
                signupBtn.innerHTML = originalText;
                signupBtn.disabled = false;
            }
        });

        // Forgot password form submission
        document.getElementById('forgotPasswordFormElement').addEventListener('submit', async (e) => {
            e.preventDefault();
            
            const email = document.getElementById('resetEmail').value;
            
            if (!email) {
                showMessage('error', 'Please enter your email address');
                return;
            }
            
            const resetBtn = document.getElementById('resetBtn');
            const originalText = resetBtn.innerHTML;
            resetBtn.innerHTML = '<div class="loading"></div> Sending...';
            resetBtn.disabled = true;
            
            try {
                await sendPasswordResetEmail(auth, email);
                showMessage('success', `Password reset email sent to ${email}. Check your inbox.`);
                document.getElementById('resetEmail').value = '';
                
            } catch (error) {
                console.error('Reset password error:', error);
                showMessage('error', 'Failed to send reset email: ' + error.message);
                
            } finally {
                resetBtn.innerHTML = originalText;
                resetBtn.disabled = false;
            }
        });

        // Logout function
        window.logout = async function() {
            try {
                await signOut(auth);
                await fetch('logout.php');
                showMessage('success', 'You have been signed out successfully.');
            } catch (error) {
                console.error('Logout error:', error);
                showMessage('error', 'Failed to sign out: ' + error.message);
            }
        };

        // Copy license key function
        window.copyLicenseKey = function() {
            if (!window.userLicenseKey) {
                showMessage('error', 'No license key available to copy');
                return;
            }
            
            navigator.clipboard.writeText(window.userLicenseKey).then(() => {
                // Update both copy buttons
                const copyButtons = [document.getElementById('copyButton'), document.getElementById('copyButtonMain')];
                copyButtons.forEach(button => {
                    if (button) {
                        const originalText = button.innerHTML;
                        button.innerHTML = '<i class="fas fa-check"></i> Copied!';
                        button.style.background = 'linear-gradient(135deg, #27ae60 0%, #219653 100%)';
                        
                        setTimeout(() => {
                            button.innerHTML = '<i class="fas fa-copy"></i> Copy License Key';
                            button.style.background = 'linear-gradient(135deg, #4bb543 0%, #3a9d32 100%)';
                        }, 2000);
                    }
                });
                
                showMessage('success', 'License key copied to clipboard!');
            }).catch(err => {
                console.error('Failed to copy:', err);
                showMessage('error', 'Failed to copy license key');
            });
        };

        // Download confirmation
        window.confirmDownload = function(productName) {
            if (!window.userLicenseKey || window.userLicenseKey === 'No license assigned') {
                alert('You need a valid license to download this product. Please get a license first.');
                window.open('https://t.me/ZEAHONGMOD', '_blank');
                return false;
            }
            
            if (!confirm(`You are about to download: ${productName}\n\nMake sure you have an active subscription to use this product.`)) {
                return false;
            }
            
            return true;
        };

        // Shop functions
        window.purchaseProduct = function(productName) {
            alert(`Thank you for your interest in ${productName}!\n\nPlease contact @ZEAHONGMOD on Telegram to complete your purchase.`);
        };

        // Blog functions
        window.readBlogPost = function(postTitle) {
            alert(`"${postTitle}" - Blog Post\n\nThis feature is coming soon! Check back later to read the full article.`);
        };

        // Support functions
        window.contactSupport = function() {
            alert('Please email your license issues to: support@zeahongtrading.com\n\nWe will respond within 24 hours.');
        };

        // License Management Functions
        window.activateLicense = function() {
            if (!window.userLicenseKey) {
                alert('You need a license to activate. Please purchase a license first.');
                return;
            }
            
            const deviceId = prompt('Enter your device ID (usually your MT5 account number or PC ID):');
            if (deviceId) {
                alert(`License activation request sent for device: ${deviceId}\n\nPlease wait for admin approval.`);
                // Here you would normally send this to your server
            }
        };

        window.renewLicense = function() {
            if (!window.userLicenseKey) {
                alert('You need a license to renew. Please purchase a license first.');
                return;
            }
            
            alert('License renewal requested!\n\nPlease contact @ZEAHONGMOD on Telegram to renew your license.');
        };

        window.transferLicense = function() {
            if (!window.userLicenseKey) {
                alert('You need a license to transfer. Please purchase a license first.');
                return;
            }
            
            const newEmail = prompt('Enter the email address to transfer the license to:');
            if (newEmail) {
                alert(`License transfer request sent to: ${newEmail}\n\nPlease wait for admin approval.`);
            }
        };

        window.purchaseLicense = function(licenseType) {
            const prices = {
                'basic': '$49.99',
                'pro': '$99.99',
                'ultimate': '$199.99'
            };
            
            const features = {
                'basic': 'Basic features, 1 device, 1 year',
                'pro': 'All features, 2 devices, 2 years',
                'ultimate': 'All features + priority support, 5 devices, lifetime'
            };
            
            alert(`Purchase ${licenseType.toUpperCase()} License\n\nPrice: ${prices[licenseType]}\nFeatures: ${features[licenseType]}\n\nPlease contact @ZEAHONGMOD on Telegram to complete your purchase.`);
        };
    </script>
</body>
</html>
