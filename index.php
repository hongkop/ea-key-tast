<?php
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
            display: flex;
            flex-direction: column;
            align-items: center;
        }

        /* Login Page Styles - Centered */
        .login-page {
            display: flex;
            align-items: center;
            justify-content: center;
            min-height: 100vh;
            width: 100%;
            padding: 20px;
        }

        .container {
            width: 100%;
            max-width: 400px;
            background: rgba(255, 255, 255, 0.95);
            border-radius: 20px;
            box-shadow: 0 20px 40px rgba(0, 0, 0, 0.2);
            overflow: hidden;
            margin: 0 auto;
        }

        /* Header - Centered */
        .header {
            background: linear-gradient(135deg, #6a11cb 0%, #2575fc 100%);
            color: white;
            padding: 30px;
            text-align: center;
        }

        .logo {
            font-size: 2.5rem;
            margin-bottom: 10px;
        }

        .header h1 {
            font-size: 1.8rem;
            font-weight: 600;
        }

        .header p {
            opacity: 0.9;
            margin-top: 5px;
        }

        /* Content - Centered */
        .content {
            padding: 40px 30px;
            text-align: center;
        }

        .form-container {
            display: block;
            text-align: left;
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

        .message {
            padding: 15px;
            border-radius: 10px;
            margin-bottom: 20px;
            display: none;
            animation: slideIn 0.3s ease;
            text-align: center;
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

        .admin-note {
            text-align: center;
            margin-top: 20px;
            padding: 10px;
            background: #f8f9fa;
            border-radius: 8px;
            font-size: 12px;
            color: #666;
        }

        .powered-by {
            text-align: center;
            margin-top: 30px;
            color: #999;
            font-size: 12px;
        }

        /* Dashboard - Centered Layout */
        .dashboard {
            display: none;
            width: 100%;
            max-width: 1200px;
            margin: 0 auto;
            padding: 20px;
        }

        /* Admin Panel Link - Top Right */
        .admin-panel-link {
            position: fixed;
            top: 20px;
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
            
            .dashboard {
                padding: 10px;
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
            
            .admin-panel-link {
                position: static;
                display: block;
                margin: 20px auto;
                width: fit-content;
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
        }
    </style>
</head>
<body>
    <!-- Admin Panel Link -->
    <a href="licenses.php" class="admin-panel-link">
        <i class="fas fa-user-shield"></i> Admin Panel
    </a>

    <!-- Login Page -->
    <div class="login-page" id="loginPage">
        <div class="container">
            <div class="header">
                <div class="logo">
                    <i class="fas fa-user-shield"></i>
                </div>
                <h1>Zeahong Trading</h1>
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
                    
                    <div class="admin-note">
                        Admin? <a href="licenses.php">Login to Admin Panel</a>
                    </div>
                    
                    <div class="powered-by">
                        Powered by <span>Firebase</span> • Secure Authentication
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

    <!-- Dashboard -->
    <div class="dashboard" id="dashboard">
        <!-- Header -->
        <header class="dashboard-header">
            <h1><i class="fas fa-download"></i> Trading Tools Download Center</h1>
            <p class="dashboard-subtitle">Download your purchased trading tools, expert advisors, and configurations. All files are pre-configured and ready to use with your trading platforms.</p>
            
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
        
        <!-- License Card -->
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
                    
                    <div class="license-key-display" id="licenseKeyDisplay">
                        <i class="fas fa-spinner fa-spin"></i> Loading license key...
                    </div>
                    
                    <button class="copy-btn" onclick="copyLicenseKey()" id="copyButton">
                        <i class="fas fa-copy"></i> Copy License Key
                    </button>
                    
                    <div class="license-info">
                        <div class="info-item">
                            <div class="info-label">Status</div>
                            <div class="info-value active" id="licenseStatus">Active</div>
                        </div>
                        <div class="info-item">
                            <div class="info-label">Expires</div>
                            <div class="info-value" id="licenseExpiry">2024-12-31</div>
                        </div>
                        <div class="info-item">
                            <div class="info-label">Device</div>
                            <div class="info-value" id="deviceStatus">Not Activated</div>
                        </div>
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
            </div>
        </div>
        
        <!-- Download Grid -->
        <div class="download-grid">
            <!-- Trading VPS Card -->
            <div class="download-card">
                <div class="card-header">
                    <div class="card-icon">
                        <i class="fas fa-server"></i>
                    </div>
                    <h2 class="card-title">Trading VPS</h2>
                </div>
                
                <div class="card-price">$13.00 <span>/month</span></div>
                
                <ul class="features-list">
                    <li>12-24 GB RAM for strong performance</li>
                    <li>24/7 uptime for bot trading</li>
                    <li>Pre-installed MT5</li>
                    <li>Remote Desktop access (RDP) from anywhere</li>
                    <li>Easy payment with local Cambodian banks</li>
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
                    <li>SnIPx2 Flip EA for MetaTrader 5</li>
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
                    <h2 class="card-title">BTrader Tools</h2>
                </div>
                
                <div class="card-price">$49.99 <span>/month</span></div>
                
                <ul class="features-list">
                    <li>Access BTrader Tools</li>
                    <li>BTrader Toolkits</li>
                    <li>BTrader Concept</li>
                    <li>BTrader Sessions</li>
                    <li>BTrader Algo</li>
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
            <p>Need help with installation? Contact support@example.com</p>
            <p>All downloads are for authorized customers only. Unauthorized distribution is prohibited.</p>
            
            <div class="footer-links">
                <a href="#">Terms of Service</a>
                <a href="#">Privacy Policy</a>
                <a href="#">Support Center</a>
                <a href="licenses.php">License Management</a>
            </div>
            
            <p style="margin-top: 20px;">&copy; <?php echo date('Y'); ?> Trading Tools Download Center. All rights reserved.</p>
        </footer>
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
        const welcomeUserName = document.getElementById('welcomeUserName');
        const userEmailDisplay = document.getElementById('userEmailDisplay');
        const userAvatar = document.getElementById('userAvatar');
        const licenseKeyDisplay = document.getElementById('licenseKeyDisplay');
        const licenseStatus = document.getElementById('licenseStatus');
        const licenseExpiry = document.getElementById('licenseExpiry');
        const deviceStatus = document.getElementById('deviceStatus');
        const copyButton = document.getElementById('copyButton');

        // Initialize
        init();

        async function init() {
            try {
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
            
            welcomeUserName.textContent = userDisplayName;
            userEmailDisplay.textContent = user.email;
            userAvatar.textContent = userInitial;
            
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
        console.log('Fetching license for:', userEmail);
        
        const formData = new URLSearchParams();
        formData.append('action', 'get_license');
        formData.append('email', userEmail);
        
        const response = await fetch('login.php', {
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
        
        if (data === 'NOT_FOUND' || data === 'INVALID' || !data) {
            // No license found
            licenseKeyDisplay.innerHTML = '<span style="color: #e74c3c;">No license assigned</span>';
            licenseKeyDisplay.style.fontSize = '1.2rem';
            licenseStatus.textContent = 'No License';
            licenseStatus.className = 'info-value expired';
            licenseExpiry.textContent = 'N/A';
            deviceStatus.textContent = 'N/A';
            copyButton.disabled = true;
            copyButton.innerHTML = '<i class="fas fa-ban"></i> No License Available';
            copyButton.style.background = 'linear-gradient(135deg, #95a5a6 0%, #7f8c8d 100%)';
            
        } else if (data.includes('|')) {
            // Parse license info
            const parts = data.split('|');
            console.log('Parsed parts:', parts);
            
            if (parts.length >= 4) {
                const licenseKey = parts[0];
                const status = parts[1];
                const expiry = parts[2];
                const device = parts[3];
                
                // Display license
                licenseKeyDisplay.textContent = licenseKey;
                licenseKeyDisplay.style.color = '#4bb543';
                licenseKeyDisplay.style.fontSize = '1.8rem';
                licenseStatus.textContent = status;
                licenseStatus.className = status === 'active' ? 'info-value active' : 'info-value expired';
                licenseExpiry.textContent = expiry;
                deviceStatus.textContent = device || 'Not Activated';
                
                // Enable copy button
                copyButton.disabled = false;
                copyButton.innerHTML = '<i class="fas fa-copy"></i> Copy License Key';
                copyButton.style.background = 'linear-gradient(135deg, #4bb543 0%, #3a9d32 100%)';
                
                // Store for copying
                window.userLicenseKey = licenseKey;
            }
        } else {
            throw new Error('Unexpected response format');
        }
        
    } catch (error) {
        console.error('Error fetching license info:', error);
        licenseKeyDisplay.innerHTML = '<span style="color: #e74c3c;">Error: ' + error.message + '</span>';
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
                const originalText = copyButton.innerHTML;
                copyButton.innerHTML = '<i class="fas fa-check"></i> Copied!';
                copyButton.style.background = 'linear-gradient(135deg, #27ae60 0%, #219653 100%)';
                
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
