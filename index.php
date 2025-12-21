<?php
// Start session for user management
session_start();

// Download URLs for each product
$download_links = [
    'trading_vps' => 'https://example.com/downloads/trading-vps-setup.zip',
    'trading_robot' => 'https://example.com/downloads/snipx2-flip-ea.zip',
    'btrader_tools' => 'https://example.com/downloads/btrader-tools-package.zip'
];

// File sizes (optional, for display)
$file_sizes = [
    'trading_vps' => '45 MB',
    'trading_robot' => '28 MB',
    'btrader_tools' => '62 MB'
];

// Check if user is logged in via Firebase
$isLoggedIn = isset($_SESSION['firebase_user']) ? true : false;
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

        /* Login Page Styles (Hidden when logged in) */
        .login-page {
            display: flex;
            align-items: center;
            justify-content: center;
            min-height: 100vh;
            padding: 20px;
            background: linear-gradient(135deg, #6a11cb 0%, #2575fc 100%);
        }

        .login-container {
            width: 100%;
            max-width: 400px;
            background: rgba(255, 255, 255, 0.95);
            border-radius: 20px;
            box-shadow: 0 20px 40px rgba(0, 0, 0, 0.2);
            overflow: hidden;
        }

        .login-header {
            background: linear-gradient(135deg, #6a11cb 0%, #2575fc 100%);
            color: white;
            padding: 30px;
            text-align: center;
        }

        .login-logo {
            font-size: 2.5rem;
            margin-bottom: 10px;
        }

        .login-header h1 {
            font-size: 1.8rem;
            font-weight: 600;
        }

        .login-header p {
            opacity: 0.9;
            margin-top: 5px;
        }

        .login-content {
            padding: 40px 30px;
        }

        .form-container {
            display: block;
        }

        .form-title {
            color: #333;
            font-size: 1.5rem;
            margin-bottom: 25px;
            text-align: center;
        }

        .form-group {
            margin-bottom: 20px;
        }

        .form-group label {
            display: block;
            margin-bottom: 8px;
            color: #555;
            font-weight: 500;
            font-size: 14px;
        }

        .input-group {
            position: relative;
        }

        .input-group i {
            position: absolute;
            left: 15px;
            top: 50%;
            transform: translateY(-50%);
            color: #666;
        }

        .input-group input {
            width: 100%;
            padding: 15px 15px 15px 45px;
            border: 2px solid #e1e5ee;
            border-radius: 10px;
            font-size: 16px;
            transition: all 0.3s;
        }

        .input-group input:focus {
            outline: none;
            border-color: #6a11cb;
            box-shadow: 0 0 0 3px rgba(106, 17, 203, 0.1);
        }

        .btn {
            width: 100%;
            padding: 16px;
            background: linear-gradient(135deg, #6a11cb 0%, #2575fc 100%);
            color: white;
            border: none;
            border-radius: 10px;
            font-size: 16px;
            font-weight: 600;
            cursor: pointer;
            transition: transform 0.3s, box-shadow 0.3s;
            margin-top: 10px;
        }

        .btn:hover {
            transform: translateY(-2px);
            box-shadow: 0 10px 20px rgba(106, 17, 203, 0.3);
        }

        .btn-secondary {
            background: transparent;
            color: #6a11cb;
            border: 2px solid #6a11cb;
        }

        .btn-secondary:hover {
            background: rgba(106, 17, 203, 0.1);
        }

        .toggle-link {
            text-align: center;
            margin-top: 25px;
            color: #666;
            font-size: 14px;
        }

        .toggle-link a {
            color: #6a11cb;
            text-decoration: none;
            font-weight: 600;
            cursor: pointer;
        }

        .toggle-link a:hover {
            text-decoration: underline;
        }

        .message {
            padding: 15px;
            border-radius: 10px;
            margin-bottom: 20px;
            display: none;
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

        .message.error {
            background: #fee;
            color: #e74c3c;
            border-left: 4px solid #e74c3c;
        }

        .message.success {
            background: #efc;
            color: #27ae60;
            border-left: 4px solid #27ae60;
        }

        .message.info {
            background: #e3f2fd;
            color: #2196f3;
            border-left: 4px solid #2196f3;
        }

        .loading {
            display: inline-block;
            width: 20px;
            height: 20px;
            border: 3px solid #f3f3f3;
            border-top: 3px solid #6a11cb;
            border-radius: 50%;
            animation: spin 1s linear infinite;
        }

        @keyframes spin {
            0% { transform: rotate(0deg); }
            100% { transform: rotate(360deg); }
        }

        .forgot-password {
            text-align: right;
            margin-top: 10px;
        }

        .forgot-password a {
            color: #666;
            font-size: 14px;
            text-decoration: none;
        }

        .forgot-password a:hover {
            color: #6a11cb;
            text-decoration: underline;
        }

        .admin-note {
            text-align: center;
            margin-top: 20px;
            padding: 10px;
            background: #f8f9fa;
            border-radius: 8px;
            font-size: 12px;
            color: #666;
        }

        .admin-note a {
            color: #6a11cb;
            text-decoration: none;
            font-weight: 600;
        }

        .admin-note a:hover {
            text-decoration: underline;
        }

        /* Dashboard Styles (Hidden when not logged in) */
        .dashboard {
            display: none;
            min-height: 100vh;
        }

        /* Dashboard Header */
        .dashboard-header {
            background: linear-gradient(135deg, #2c3e50 0%, #1a252f 100%);
            color: white;
            padding: 20px;
            display: flex;
            justify-content: space-between;
            align-items: center;
            box-shadow: 0 4px 20px rgba(0,0,0,0.1);
        }

        .dashboard-header-left h1 {
            font-size: 1.8rem;
            margin-bottom: 5px;
        }

        .dashboard-header-left p {
            color: #ecf0f1;
            opacity: 0.9;
            font-size: 14px;
        }

        .user-info {
            background: rgba(255,255,255,0.1);
            padding: 12px 20px;
            border-radius: 10px;
            display: flex;
            align-items: center;
            gap: 10px;
        }

        .user-avatar-small {
            width: 40px;
            height: 40px;
            background: linear-gradient(135deg, #6a11cb 0%, #2575fc 100%);
            border-radius: 50%;
            display: flex;
            align-items: center;
            justify-content: center;
            color: white;
            font-weight: bold;
            font-size: 18px;
        }

        .user-details {
            display: flex;
            flex-direction: column;
        }

        .user-name {
            font-weight: 600;
            font-size: 14px;
        }

        .user-email {
            font-size: 12px;
            opacity: 0.8;
        }

        .btn-logout {
            background: linear-gradient(135deg, #e74c3c 0%, #c0392b 100%);
            color: white;
            border: none;
            padding: 10px 20px;
            border-radius: 8px;
            font-weight: 600;
            cursor: pointer;
            transition: all 0.3s;
            margin-left: 10px;
        }

        .btn-logout:hover {
            transform: translateY(-2px);
            box-shadow: 0 5px 15px rgba(231, 76, 60, 0.4);
        }

        /* Dashboard Content */
        .dashboard-content {
            max-width: 1200px;
            margin: 30px auto;
            padding: 0 20px;
        }

        .welcome-message {
            background: rgba(255, 255, 255, 0.1);
            padding: 30px;
            border-radius: 15px;
            margin-bottom: 30px;
            text-align: center;
            border-left: 5px solid #4dabf7;
        }

        .welcome-message h2 {
            font-size: 2rem;
            margin-bottom: 10px;
            color: #fff;
        }

        .welcome-message p {
            color: #a0c8e0;
            font-size: 1.1rem;
        }

        /* Cards Grid */
        .cards-grid {
            display: grid;
            grid-template-columns: repeat(auto-fit, minmax(500px, 1fr));
            gap: 30px;
            margin-bottom: 50px;
        }

        @media (max-width: 1100px) {
            .cards-grid {
                grid-template-columns: 1fr;
            }
        }

        /* Card 1: Downloads */
        .downloads-card {
            background: rgba(25, 40, 50, 0.85);
            border-radius: 15px;
            padding: 30px;
            box-shadow: 0 10px 30px rgba(0, 0, 0, 0.4);
            border: 1px solid rgba(64, 128, 192, 0.3);
        }

        .downloads-card h2 {
            font-size: 1.8rem;
            color: #fff;
            margin-bottom: 25px;
            display: flex;
            align-items: center;
            gap: 10px;
        }

        .downloads-card h2 i {
            color: #4dabf7;
        }

        .download-items {
            display: flex;
            flex-direction: column;
            gap: 20px;
        }

        .download-item {
            background: rgba(0, 0, 0, 0.2);
            padding: 20px;
            border-radius: 10px;
            border-left: 4px solid #4dabf7;
        }

        .download-item-header {
            display: flex;
            justify-content: space-between;
            align-items: center;
            margin-bottom: 15px;
        }

        .download-item-title {
            font-size: 1.3rem;
            color: #fff;
            font-weight: 600;
        }

        .download-item-price {
            background: #4dabf7;
            color: white;
            padding: 5px 15px;
            border-radius: 20px;
            font-weight: 600;
            font-size: 0.9rem;
        }

        .download-item-features {
            list-style: none;
            margin-bottom: 15px;
        }

        .download-item-features li {
            padding: 5px 0;
            position: relative;
            padding-left: 20px;
            color: #a0c8e0;
        }

        .download-item-features li:before {
            content: "✓";
            position: absolute;
            left: 0;
            color: #4CAF50;
            font-weight: bold;
        }

        .download-btn {
            display: block;
            text-align: center;
            background: linear-gradient(to right, #2193b0, #6dd5ed);
            color: white;
            text-decoration: none;
            padding: 12px;
            border-radius: 8px;
            font-weight: 600;
            font-size: 1rem;
            transition: all 0.3s;
            margin-top: 10px;
        }

        .download-btn:hover {
            background: linear-gradient(to right, #1b7a93, #5bc0de);
            transform: translateY(-2px);
            box-shadow: 0 5px 15px rgba(33, 147, 176, 0.4);
        }

        /* Card 2: License Key */
        .license-card {
            background: rgba(25, 40, 50, 0.85);
            border-radius: 15px;
            padding: 30px;
            box-shadow: 0 10px 30px rgba(0, 0, 0, 0.4);
            border: 1px solid rgba(75, 181, 67, 0.3);
        }

        .license-card h2 {
            font-size: 1.8rem;
            color: #fff;
            margin-bottom: 25px;
            display: flex;
            align-items: center;
            gap: 10px;
        }

        .license-card h2 i {
            color: #4bb543;
        }

        .license-info {
            background: rgba(0, 0, 0, 0.3);
            padding: 25px;
            border-radius: 10px;
            margin-bottom: 25px;
            text-align: center;
        }

        .license-key-display {
            font-family: 'Courier New', monospace;
            font-size: 1.5rem;
            background: rgba(0, 0, 0, 0.5);
            padding: 20px;
            border-radius: 8px;
            margin: 20px 0;
            letter-spacing: 2px;
            border: 2px dashed #4bb543;
            color: #4bb543;
            font-weight: bold;
        }

        .license-status {
            display: flex;
            justify-content: space-between;
            margin: 20px 0;
            padding: 15px;
            background: rgba(0, 0, 0, 0.2);
            border-radius: 8px;
        }

        .status-item {
            text-align: center;
            flex: 1;
        }

        .status-label {
            font-size: 0.9rem;
            color: #a0c8e0;
            margin-bottom: 5px;
        }

        .status-value {
            font-size: 1.2rem;
            color: #fff;
            font-weight: 600;
        }

        .status-value.active {
            color: #4bb543;
        }

        .status-value.expired {
            color: #e74c3c;
        }

        .license-instructions {
            background: rgba(75, 181, 67, 0.1);
            padding: 20px;
            border-radius: 8px;
            margin-top: 25px;
            border-left: 4px solid #4bb543;
        }

        .license-instructions h3 {
            color: #fff;
            margin-bottom: 10px;
            font-size: 1.2rem;
        }

        .license-instructions ol {
            color: #a0c8e0;
            padding-left: 20px;
            margin-top: 10px;
        }

        .license-instructions li {
            margin-bottom: 8px;
        }

        .copy-btn {
            width: 100%;
            padding: 15px;
            background: linear-gradient(135deg, #4bb543 0%, #3a9d32 100%);
            color: white;
            border: none;
            border-radius: 8px;
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

        /* Footer */
        .dashboard-footer {
            text-align: center;
            padding: 30px 20px;
            color: #a0c8e0;
            border-top: 1px solid rgba(255, 255, 255, 0.1);
            margin-top: 50px;
        }

        .footer-links {
            display: flex;
            justify-content: center;
            gap: 30px;
            margin: 20px 0;
            flex-wrap: wrap;
        }

        .footer-links a {
            color: #4dabf7;
            text-decoration: none;
        }

        .footer-links a:hover {
            text-decoration: underline;
        }

        /* Admin Panel Link */
        .admin-panel-link {
            position: fixed;
            bottom: 20px;
            right: 20px;
            background: linear-gradient(135deg, #27ae60 0%, #219653 100%);
            color: white;
            padding: 12px 24px;
            border-radius: 50px;
            text-decoration: none;
            font-weight: 600;
            box-shadow: 0 4px 15px rgba(39, 174, 96, 0.4);
            transition: all 0.3s;
            z-index: 100;
            display: flex;
            align-items: center;
            gap: 8px;
        }

        .admin-panel-link:hover {
            transform: translateY(-3px);
            box-shadow: 0 8px 25px rgba(39, 174, 96, 0.6);
        }
    </style>
</head>
<body>
    <!-- Login Page (Initially shown) -->
    <div class="login-page" id="loginPage">
        <div class="login-container">
            <!-- Header -->
            <div class="login-header">
                <div class="login-logo">
                    <i class="fas fa-user-shield"></i>
                </div>
                <h1>Zeahong Trading</h1>
                <p>Secure Authentication System</p>
            </div>

            <!-- Content Area -->
            <div class="login-content">
                <!-- Messages -->
                <div class="message info" id="infoMessage">
                    <i class="fas fa-info-circle"></i> Initializing system...
                </div>
                
                <div class="message error" id="errorMessage"></div>
                <div class="message success" id="successMessage"></div>

                <!-- Login Form (Default) -->
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
                    
                    <div class="admin-note">
                        Admin? <a href="licenses.php">Login to Admin Panel</a>
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
            </div>
        </div>
    </div>

    <!-- Dashboard (Hidden until login) -->
    <div class="dashboard" id="dashboard">
        <!-- Admin Panel Link -->
        <a href="licenses.php" class="admin-panel-link">
            <i class="fas fa-user-shield"></i> Admin Panel
        </a>

        <!-- Header -->
        <header class="dashboard-header">
            <div class="dashboard-header-left">
                <h1><i class="fas fa-tachometer-alt"></i> Zeahong Trading Dashboard</h1>
                <p>Welcome to your trading tools and license management center</p>
            </div>
            
            <div class="header-right">
                <div class="user-info" id="userInfoDisplay">
                    <!-- User info will be populated by JavaScript -->
                </div>
                <button class="btn-logout" onclick="logout()">
                    <i class="fas fa-sign-out-alt"></i> Logout
                </button>
            </div>
        </header>

        <!-- Dashboard Content -->
        <div class="dashboard-content">
            <!-- Welcome Message -->
            <div class="welcome-message">
                <h2>Welcome, <span id="welcomeUserName">User</span>!</h2>
                <p>Access your trading tools and manage your EA license key below</p>
            </div>

            <!-- Cards Grid -->
            <div class="cards-grid">
                <!-- Card 1: Downloads -->
                <div class="downloads-card">
                    <h2><i class="fas fa-download"></i> Trading Tools Download</h2>
                    
                    <div class="download-items">
                        <!-- Trading VPS -->
                        <div class="download-item">
                            <div class="download-item-header">
                                <div class="download-item-title">Trading VPS</div>
                                <div class="download-item-price">$13.00/month</div>
                            </div>
                            <ul class="download-item-features">
                                <li>12-24 GB RAM for strong performance</li>
                                <li>24/7 uptime for bot trading</li>
                                <li>Pre-installed MT5</li>
                                <li>Remote Desktop access (RDP)</li>
                            </ul>
                            <div class="file-info">
                                <i class="fas fa-file-archive"></i> File size: <?php echo $file_sizes['trading_vps']; ?>
                            </div>
                            <a href="<?php echo $download_links['trading_vps']; ?>" class="download-btn" download onclick="return confirmDownload('Trading VPS')">
                                <i class="fas fa-download"></i> Download VPS Setup
                            </a>
                        </div>

                        <!-- Trading Robot -->
                        <div class="download-item">
                            <div class="download-item-header">
                                <div class="download-item-title">Trading Robot</div>
                                <div class="download-item-price">$20.00/month</div>
                            </div>
                            <ul class="download-item-features">
                                <li>SnIPx2 Flip EA for MetaTrader 5</li>
                                <li>Grid trading system</li>
                                <li>Dynamic lot sizing</li>
                                <li>Smart hedge & lock systems</li>
                            </ul>
                            <div class="file-info">
                                <i class="fas fa-file-archive"></i> File size: <?php echo $file_sizes['trading_robot']; ?>
                            </div>
                            <a href="<?php echo $download_links['trading_robot']; ?>" class="download-btn" download onclick="return confirmDownload('Trading Robot')">
                                <i class="fas fa-download"></i> Download EA & Settings
                            </a>
                        </div>

                        <!-- BTrader Tools -->
                        <div class="download-item">
                            <div class="download-item-header">
                                <div class="download-item-title">BTrader Tools</div>
                                <div class="download-item-price">$49.99/month</div>
                            </div>
                            <ul class="download-item-features">
                                <li>Access BTrader Tools</li>
                                <li>BTrader Toolkits</li>
                                <li>BTrader Concept & Sessions</li>
                                <li>BTrader Algo</li>
                            </ul>
                            <div class="file-info">
                                <i class="fas fa-file-archive"></i> File size: <?php echo $file_sizes['btrader_tools']; ?>
                            </div>
                            <a href="<?php echo $download_links['btrader_tools']; ?>" class="download-btn" download onclick="return confirmDownload('BTrader Tools')">
                                <i class="fas fa-download"></i> Download Toolkit
                            </a>
                        </div>
                    </div>
                </div>

                <!-- Card 2: License Key -->
                <div class="license-card">
                    <h2><i class="fas fa-key"></i> Your MT5 EA License Key</h2>
                    
                    <div class="license-info">
                        <p style="color: #a0c8e0; margin-bottom: 15px;">Use this license key to activate your Expert Advisor in MetaTrader 5:</p>
                        
                        <div class="license-key-display" id="licenseKeyDisplay">
                            <i class="fas fa-spinner fa-spin"></i> Loading license key...
                        </div>
                        
                        <button class="copy-btn" onclick="copyLicenseKey()" id="copyButton">
                            <i class="fas fa-copy"></i> Copy License Key
                        </button>
                    </div>

                    <div class="license-status">
                        <div class="status-item">
                            <div class="status-label">Status</div>
                            <div class="status-value active" id="licenseStatus">Active</div>
                        </div>
                        <div class="status-item">
                            <div class="status-label">Expires</div>
                            <div class="status-value" id="licenseExpiry">2024-12-31</div>
                        </div>
                        <div class="status-item">
                            <div class="status-label">Device</div>
                            <div class="status-value" id="deviceStatus">Not Activated</div>
                        </div>
                    </div>

                    <div class="license-instructions">
                        <h3><i class="fas fa-info-circle"></i> How to Use Your License Key:</h3>
                        <ol>
                            <li>Copy your license key above</li>
                            <li>Open MetaTrader 5 and navigate to your EA settings</li>
                            <li>Paste the license key in the designated field</li>
                            <li>Save settings and restart your EA</li>
                            <li>Your EA will be activated for one device only</li>
                        </ol>
                        <p style="color: #e74c3c; margin-top: 10px; font-size: 0.9rem;">
                            <i class="fas fa-exclamation-triangle"></i> This license is valid for one device only. Contact support if you need to change devices.
                        </p>
                    </div>
                </div>
            </div>

            <!-- Footer -->
            <footer class="dashboard-footer">
                <p>Need help with installation? Contact support@zeahong.com</p>
                <p>All downloads are for authorized customers only. Unauthorized distribution is prohibited.</p>
                
                <div class="footer-links">
                    <a href="#">Terms of Service</a>
                    <a href="#">Privacy Policy</a>
                    <a href="#">Support Center</a>
                    <a href="licenses.php">License Management</a>
                </div>
                
                <p style="margin-top: 20px;">&copy; <?php echo date('Y'); ?> Zeahong Trading. All rights reserved.</p>
            </footer>
        </div>
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
        const dashboard = document.getElementById('dashboard');
        const userInfoDisplay = document.getElementById('userInfoDisplay');
        const welcomeUserName = document.getElementById('welcomeUserName');
        const licenseKeyDisplay = document.getElementById('licenseKeyDisplay');
        const licenseStatus = document.getElementById('licenseStatus');
        const licenseExpiry = document.getElementById('licenseExpiry');
        const deviceStatus = document.getElementById('deviceStatus');
        const copyButton = document.getElementById('copyButton');

        // Initialize
        init();

        async function init() {
            try {
                showMessage('info', 'Connecting to system...');
                
                // Setup auth state listener
                setupAuthListener();
                
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

        // Setup authentication state listener
        function setupAuthListener() {
            onAuthStateChanged(auth, async (user) => {
                console.log('Auth state changed:', user ? 'User logged in' : 'User logged out');
                
                if (user) {
                    // User is signed in
                    showDashboard(user);
                    await updateUserLicenseInfo(user.email);
                } else {
                    // User is signed out
                    showLoginPage();
                }
            }, (error) => {
                console.error('Auth state error:', error);
                showMessage('error', `Authentication error: ${error.message}`);
            });
        }

        // Show dashboard with user info
        function showDashboard(user) {
            loginPage.style.display = 'none';
            dashboard.style.display = 'block';
            
            // Update user info
            const userDisplayName = user.displayName || user.email.split('@')[0];
            const userInitial = userDisplayName.charAt(0).toUpperCase();
            
            // Update header user info
            userInfoDisplay.innerHTML = `
                <div class="user-avatar-small">${userInitial}</div>
                <div class="user-details">
                    <div class="user-name">${userDisplayName}</div>
                    <div class="user-email">${user.email}</div>
                </div>
            `;
            
            // Update welcome message
            welcomeUserName.textContent = userDisplayName;
            
            // Update page title
            document.title = `Dashboard - ${userDisplayName}`;
        }

        // Show login page
        function showLoginPage() {
            dashboard.style.display = 'none';
            loginPage.style.display = 'flex';
            document.title = 'Zeahong Trading - Login';
        }

        // Update user license information
        async function updateUserLicenseInfo(userEmail) {
            try {
                // Fetch user's license from your API
                const response = await fetch(`login.php?action=get_license&email=${encodeURIComponent(userEmail)}`);
                const data = await response.text();
                
                if (data.includes('INVALID') || data.includes('NOT_FOUND')) {
                    // No license found for this user
                    licenseKeyDisplay.innerHTML = '<span style="color: #e74c3c;">No license assigned</span>';
                    licenseStatus.textContent = 'No License';
                    licenseStatus.className = 'status-value expired';
                    licenseExpiry.textContent = 'N/A';
                    deviceStatus.textContent = 'N/A';
                    copyButton.disabled = true;
                    copyButton.innerHTML = '<i class="fas fa-ban"></i> No License Available';
                    copyButton.style.background = 'linear-gradient(135deg, #95a5a6 0%, #7f8c8d 100%)';
                    
                    // Show message to contact admin
                    const licenseInfo = document.querySelector('.license-info');
                    const message = document.createElement('p');
                    message.style.color = '#e74c3c';
                    message.style.marginTop = '10px';
                    message.innerHTML = '<i class="fas fa-exclamation-triangle"></i> No license assigned. Contact admin to get a license key.';
                    licenseInfo.appendChild(message);
                } else {
                    // Parse license info (assuming format: KEY|STATUS|EXPIRY|DEVICE)
                    const parts = data.split('|');
                    if (parts.length >= 4) {
                        licenseKeyDisplay.textContent = parts[0];
                        licenseStatus.textContent = parts[1];
                        licenseStatus.className = parts[1] === 'active' ? 'status-value active' : 'status-value expired';
                        licenseExpiry.textContent = parts[2];
                        deviceStatus.textContent = parts[3] || 'Not Activated';
                        
                        // Store license key in global variable for copying
                        window.userLicenseKey = parts[0];
                    }
                }
            } catch (error) {
                console.error('Error fetching license info:', error);
                licenseKeyDisplay.innerHTML = '<span style="color: #e74c3c;">Error loading license</span>';
            }
        }

        // Show/Hide Forms
        window.showSignup = function() {
            const loginForm = document.getElementById('loginForm');
            const signupForm = document.getElementById('signupForm');
            const forgotPasswordForm = document.getElementById('forgotPasswordForm');
            
            loginForm.style.display = 'none';
            signupForm.style.display = 'block';
            forgotPasswordForm.style.display = 'none';
            clearMessages();
        };

        window.showLogin = function() {
            const loginForm = document.getElementById('loginForm');
            const signupForm = document.getElementById('signupForm');
            const forgotPasswordForm = document.getElementById('forgotPasswordForm');
            
            signupForm.style.display = 'none';
            loginForm.style.display = 'block';
            forgotPasswordForm.style.display = 'none';
            clearMessages();
        };

        window.showForgotPassword = function() {
            const loginForm = document.getElementById('loginForm');
            const signupForm = document.getElementById('signupForm');
            const forgotPasswordForm = document.getElementById('forgotPasswordForm');
            
            loginForm.style.display = 'none';
            signupForm.style.display = 'none';
            forgotPasswordForm.style.display = 'block';
            clearMessages();
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
                    
                    // Auto hide success message after 3 seconds
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
            
            // Show loading
            const loginBtn = document.getElementById('loginBtn');
            const originalText = loginBtn.innerHTML;
            loginBtn.innerHTML = '<div class="loading"></div> Signing in...';
            loginBtn.disabled = true;
            
            try {
                const userCredential = await signInWithEmailAndPassword(auth, email, password);
                console.log('Login successful:', userCredential.user);
                
                // Store user in session (for PHP)
                const response = await fetch('save_session.php', {
                    method: 'POST',
                    headers: {
                        'Content-Type': 'application/json',
                    },
                    body: JSON.stringify({
                        email: userCredential.user.email,
                        name: userCredential.user.displayName || userCredential.user.email.split('@')[0]
                    })
                });
                
                // Clear form
                document.getElementById('loginFormElement').reset();
                
                showMessage('success', 'Login successful! Redirecting...');
                
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
                    case 'auth/user-disabled':
                        errorMsg += 'This account has been disabled.';
                        break;
                    case 'auth/too-many-requests':
                        errorMsg += 'Too many attempts. Please try again later.';
                        break;
                    default:
                        errorMsg += error.message;
                }
                
                showMessage('error', errorMsg);
                
            } finally {
                // Restore button
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
            
            // Validation
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
            
            // Show loading
            const signupBtn = document.getElementById('signupBtn');
            const originalText = signupBtn.innerHTML;
            signupBtn.innerHTML = '<div class="loading"></div> Creating account...';
            signupBtn.disabled = true;
            
            try {
                // Create user
                const userCredential = await createUserWithEmailAndPassword(auth, email, password);
                console.log('User created:', userCredential.user);
                
                // Update profile with display name
                await updateProfile(userCredential.user, {
                    displayName: name
                });
                
                // Clear form
                document.getElementById('signupFormElement').reset();
                
                showMessage('success', 'Account created successfully! Welcome!');
                
                // Automatically login after signup
                showMessage('success', 'Auto-logging in...');
                
            } catch (error) {
                console.error('Signup error:', error);
                
                let errorMsg = 'Signup failed. ';
                
                switch(error.code) {
                    case 'auth/email-already-in-use':
                        errorMsg += 'This email is already registered.';
                        break;
                    case 'auth/invalid-email':
                        errorMsg += 'Invalid email address.';
                        break;
                    case 'auth/weak-password':
                        errorMsg += 'Password is too weak.';
                        break;
                    case 'auth/operation-not-allowed':
                        errorMsg += 'Email/password sign-up is not enabled. Please contact support.';
                        break;
                    case 'auth/network-request-failed':
                        errorMsg += 'Network error. Please check your connection.';
                        break;
                    default:
                        errorMsg += error.message;
                }
                
                showMessage('error', errorMsg);
                
            } finally {
                // Restore button
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
            
            // Show loading
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
                
                let errorMsg = 'Failed to send reset email. ';
                
                switch(error.code) {
                    case 'auth/user-not-found':
                        errorMsg += 'No account found with this email.';
                        break;
                    case 'auth/invalid-email':
                        errorMsg += 'Invalid email address.';
                        break;
                    case 'auth/too-many-requests':
                        errorMsg += 'Too many attempts. Please try again later.';
                        break;
                    default:
                        errorMsg += error.message;
                }
                
                showMessage('error', errorMsg);
                
            } finally {
                // Restore button
                resetBtn.innerHTML = originalText;
                resetBtn.disabled = false;
            }
        });

        // Logout function
        window.logout = async function() {
            try {
                await signOut(auth);
                // Clear PHP session
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
                // Show success feedback
                const originalText = copyButton.innerHTML;
                copyButton.innerHTML = '<i class="fas fa-check"></i> Copied!';
                copyButton.style.background = 'linear-gradient(135deg, #27ae60 0%, #219653 100%)';
                
                // Revert after 2 seconds
                setTimeout(() => {
                    copyButton.innerHTML = '<i class="fas fa-copy"></i> Copy License Key';
                    copyButton.style.background = 'linear-gradient(135deg, #4bb543 0%, #3a9d32 100%)';
                }, 2000);
                
                showMessage('success', 'License key copied to clipboard!');
            }).catch(err => {
                console.error('Failed to copy:', err);
                showMessage('error', 'Failed to copy license key');
            });
        };

        // Download confirmation
        window.confirmDownload = function(productName) {
            return confirm(`You are about to download: ${productName}\n\nMake sure you have an active subscription to use this product.`);
        };
    </script>
</body>
</html>
