<?php
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
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Trading Tools Download Center</title>
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
            padding: 20px;
        }

        .container {
            max-width: 1400px;
            margin: 0 auto;
        }

        /* Header Styles */
        .main-header {
            background: linear-gradient(135deg, #2c3e50 0%, #1a252f 100%);
            color: white;
            padding: 20px;
            border-radius: 15px;
            margin-bottom: 30px;
            display: flex;
            justify-content: space-between;
            align-items: center;
            box-shadow: 0 10px 30px rgba(0, 0, 0, 0.3);
        }

        .header-left h1 {
            font-size: 2.5rem;
            margin-bottom: 5px;
            color: #ffffff;
        }

        .header-left p {
            color: #ecf0f1;
            opacity: 0.9;
        }

        .header-right {
            display: flex;
            gap: 15px;
            align-items: center;
        }

        .user-info {
            background: rgba(255, 255, 255, 0.1);
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

        .btn-auth {
            background: linear-gradient(135deg, #6a11cb 0%, #2575fc 100%);
            color: white;
            border: none;
            padding: 12px 24px;
            border-radius: 10px;
            font-weight: 600;
            cursor: pointer;
            transition: all 0.3s ease;
            text-decoration: none;
            display: inline-flex;
            align-items: center;
            gap: 8px;
        }

        .btn-auth:hover {
            transform: translateY(-2px);
            box-shadow: 0 10px 20px rgba(106, 17, 203, 0.4);
        }

        .btn-logout {
            background: linear-gradient(135deg, #e74c3c 0%, #c0392b 100%);
        }

        .btn-logout:hover {
            box-shadow: 0 10px 20px rgba(231, 76, 60, 0.4);
        }

        .btn-admin {
            background: linear-gradient(135deg, #27ae60 0%, #219653 100%);
        }

        .btn-admin:hover {
            box-shadow: 0 10px 20px rgba(39, 174, 96, 0.4);
        }

        /* Login Modal */
        .login-modal {
            position: fixed;
            top: 0;
            left: 0;
            width: 100%;
            height: 100%;
            background: rgba(0, 0, 0, 0.8);
            display: none;
            align-items: center;
            justify-content: center;
            z-index: 1000;
            animation: fadeIn 0.3s ease;
        }

        @keyframes fadeIn {
            from { opacity: 0; }
            to { opacity: 1; }
        }

        .login-container {
            background: white;
            border-radius: 20px;
            width: 90%;
            max-width: 400px;
            overflow: hidden;
            box-shadow: 0 30px 60px rgba(0, 0, 0, 0.5);
            animation: slideUp 0.3s ease;
        }

        @keyframes slideUp {
            from { transform: translateY(50px); opacity: 0; }
            to { transform: translateY(0); opacity: 1; }
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

        .login-header h2 {
            font-size: 1.5rem;
            font-weight: 600;
        }

        .login-content {
            padding: 40px 30px;
        }

        .form-group {
            margin-bottom: 20px;
        }

        .form-group label {
            display: block;
            margin-bottom: 8px;
            color: #333;
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
            color: #333;
        }

        .input-group input:focus {
            outline: none;
            border-color: #6a11cb;
            box-shadow: 0 0 0 3px rgba(106, 17, 203, 0.1);
        }

        .btn-login {
            width: 100%;
            padding: 16px;
            background: linear-gradient(135deg, #6a11cb 0%, #2575fc 100%);
            color: white;
            border: none;
            border-radius: 10px;
            font-size: 16px;
            font-weight: 600;
            cursor: pointer;
            transition: all 0.3s;
            margin-top: 10px;
        }

        .btn-login:hover {
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

        .close-btn {
            position: absolute;
            top: 20px;
            right: 20px;
            background: rgba(255, 255, 255, 0.2);
            color: white;
            border: none;
            width: 40px;
            height: 40px;
            border-radius: 50%;
            font-size: 20px;
            cursor: pointer;
            z-index: 1001;
        }

        .close-btn:hover {
            background: rgba(255, 255, 255, 0.3);
        }

        /* Download Grid */
        .download-grid {
            display: grid;
            grid-template-columns: repeat(auto-fit, minmax(350px, 1fr));
            gap: 30px;
            padding: 20px;
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

        .download-btn i {
            margin-right: 10px;
        }

        .instructions {
            margin-top: 15px;
            font-size: 0.85rem;
            color: #a0c8e0;
            text-align: center;
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

        /* Footer */
        footer {
            text-align: center;
            padding: 40px 20px;
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

        .footer-links a:hover {
            text-decoration: underline;
        }

        /* Login Required Overlay */
        .login-required {
            position: fixed;
            top: 0;
            left: 0;
            width: 100%;
            height: 100%;
            background: rgba(15, 32, 39, 0.95);
            display: none;
            align-items: center;
            justify-content: center;
            z-index: 999;
        }

        .login-required-content {
            background: rgba(25, 40, 50, 0.95);
            padding: 40px;
            border-radius: 15px;
            text-align: center;
            max-width: 500px;
            border: 2px solid #4dabf7;
        }

        .login-required-content h2 {
            color: #fff;
            margin-bottom: 20px;
            font-size: 2rem;
        }

        .login-required-content p {
            color: #a0c8e0;
            margin-bottom: 30px;
            font-size: 1.1rem;
        }

        /* Responsive */
        @media (max-width: 768px) {
            .download-grid {
                grid-template-columns: 1fr;
                padding: 10px;
            }
            
            .main-header {
                flex-direction: column;
                gap: 20px;
                text-align: center;
            }
            
            .header-right {
                flex-direction: column;
                width: 100%;
            }
            
            .btn-auth {
                width: 100%;
                justify-content: center;
            }
        }
    </style>
</head>
<body>
    <!-- Login Required Overlay -->
    <div class="login-required" id="loginRequired">
        <div class="login-required-content">
            <h2>🔒 Login Required</h2>
            <p>Please login to access the download center. You need to be authenticated to download our products.</p>
            <button class="btn-auth" onclick="showLoginModal()">
                <i class="fas fa-sign-in-alt"></i> Login Now
            </button>
            <p style="margin-top: 20px; font-size: 0.9rem; color: #a0c8e0;">
                Don't have an account? <a href="#" onclick="showSignup()" style="color: #4dabf7;">Sign up for free</a>
            </p>
        </div>
    </div>

    <!-- Login Modal -->
    <div class="login-modal" id="loginModal">
        <button class="close-btn" onclick="hideLoginModal()">×</button>
        <div class="login-container">
            <!-- Header -->
            <div class="login-header">
                <div class="login-logo">
                    <i class="fas fa-user-shield"></i>
                </div>
                <h2 id="modalTitle">Welcome Back</h2>
                <p>Sign in to your account</p>
            </div>

            <!-- Content Area -->
            <div class="login-content">
                <!-- Messages -->
                <div class="message error" id="errorMessage"></div>
                <div class="message success" id="successMessage"></div>

                <!-- Login Form (Default) -->
                <div class="form-container" id="loginForm">
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
                        <button type="submit" class="btn-login" id="loginBtn">
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
                        </div>
                        <div class="form-group">
                            <label for="confirmPassword">Confirm Password</label>
                            <div class="input-group">
                                <i class="fas fa-lock"></i>
                                <input type="password" id="confirmPassword" placeholder="Confirm your password" required minlength="6">
                            </div>
                        </div>
                        <button type="submit" class="btn-login" id="signupBtn">
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
                        <button type="submit" class="btn-login" id="resetBtn">
                            <i class="fas fa-paper-plane"></i> Send Reset Link
                        </button>
                        <button type="button" class="btn-login btn-secondary" onclick="showLogin()">
                            <i class="fas fa-arrow-left"></i> Back to Login
                        </button>
                    </form>
                </div>
            </div>
        </div>
    </div>

    <!-- Main Container -->
    <div class="container" id="mainContent">
        <!-- Header -->
        <header class="main-header">
            <div class="header-left">
                <h1><i class="fas fa-download"></i> Trading Tools Download Center</h1>
                <p>Download your purchased trading tools, expert advisors, and configurations</p>
            </div>
            
            <div class="header-right" id="authButtons">
                <!-- User info will be displayed here when logged in -->
                <button class="btn-auth" onclick="showLoginModal()" id="loginButton">
                    <i class="fas fa-sign-in-alt"></i> Login
                </button>
                <a href="licenses.php" class="btn-auth btn-admin">
                    <i class="fas fa-user-shield"></i> Admin Panel
                </a>
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
                
                <a href="<?php echo $download_links['trading_vps']; ?>" class="download-btn" download onclick="return checkAuth(event, 'Trading VPS')">
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
                
                <a href="<?php echo $download_links['trading_robot']; ?>" class="download-btn" download onclick="return checkAuth(event, 'Trading Robot')">
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
                
                <a href="<?php echo $download_links['btrader_tools']; ?>" class="download-btn" download onclick="return checkAuth(event, 'BTrader Tools')">
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
        const loginModal = document.getElementById('loginModal');
        const loginRequired = document.getElementById('loginRequired');
        const authButtons = document.getElementById('authButtons');
        const loginForm = document.getElementById('loginForm');
        const signupForm = document.getElementById('signupForm');
        const forgotPasswordForm = document.getElementById('forgotPasswordForm');
        const errorMessage = document.getElementById('errorMessage');
        const successMessage = document.getElementById('successMessage');
        const modalTitle = document.getElementById('modalTitle');

        // Initialize
        init();

        async function init() {
            try {
                // Setup auth state listener
                setupAuthListener();
                
            } catch (error) {
                console.error('Initialization error:', error);
                showMessage('error', `Failed to initialize: ${error.message}`);
            }
        }

        // Setup authentication state listener
        function setupAuthListener() {
            onAuthStateChanged(auth, (user) => {
                console.log('Auth state changed:', user ? 'User logged in' : 'User logged out');
                
                if (user) {
                    // User is signed in
                    updateUIForLoggedInUser(user);
                    hideLoginRequired();
                } else {
                    // User is signed out
                    updateUIForLoggedOutUser();
                }
            }, (error) => {
                console.error('Auth state error:', error);
                showMessage('error', `Authentication error: ${error.message}`);
            });
        }

        // Update UI for logged in user
        function updateUIForLoggedInUser(user) {
            // Hide login button, show user info
            const loginButton = document.getElementById('loginButton');
            if (loginButton) loginButton.style.display = 'none';
            
            // Create user info element
            const userDisplayName = user.displayName || user.email.split('@')[0];
            const userInitial = userDisplayName.charAt(0).toUpperCase();
            
            const userInfoHTML = `
                <div class="user-info">
                    <div class="user-avatar-small">${userInitial}</div>
                    <div class="user-details">
                        <div class="user-name">${userDisplayName}</div>
                        <div class="user-email">${user.email}</div>
                    </div>
                </div>
                <button class="btn-auth btn-logout" onclick="logout()">
                    <i class="fas fa-sign-out-alt"></i> Logout
                </button>
            `;
            
            authButtons.innerHTML = userInfoHTML + authButtons.innerHTML;
        }

        // Update UI for logged out user
        function updateUIForLoggedOutUser() {
            authButtons.innerHTML = `
                <button class="btn-auth" onclick="showLoginModal()" id="loginButton">
                    <i class="fas fa-sign-in-alt"></i> Login
                </button>
                <a href="licenses.php" class="btn-auth btn-admin">
                    <i class="fas fa-user-shield"></i> Admin Panel
                </a>
            `;
        }

        // Show/Hide Forms
        window.showSignup = function() {
            loginForm.style.display = 'none';
            signupForm.style.display = 'block';
            forgotPasswordForm.style.display = 'none';
            modalTitle.textContent = 'Create Account';
            clearMessages();
        };

        window.showLogin = function() {
            signupForm.style.display = 'none';
            loginForm.style.display = 'block';
            forgotPasswordForm.style.display = 'none';
            modalTitle.textContent = 'Welcome Back';
            clearMessages();
        };

        window.showForgotPassword = function() {
            loginForm.style.display = 'none';
            signupForm.style.display = 'none';
            forgotPasswordForm.style.display = 'block';
            modalTitle.textContent = 'Reset Password';
            clearMessages();
        };

        // Modal functions
        window.showLoginModal = function() {
            loginModal.style.display = 'flex';
            showLogin(); // Show login form by default
        };

        window.hideLoginModal = function() {
            loginModal.style.display = 'none';
            clearMessages();
        };

        window.showLoginRequired = function() {
            loginRequired.style.display = 'flex';
        };

        window.hideLoginRequired = function() {
            loginRequired.style.display = 'none';
        };

        // Clear messages
        function clearMessages() {
            errorMessage.style.display = 'none';
            successMessage.style.display = 'none';
        }

        // Show message
        function showMessage(type, text) {
            clearMessages();
            
            if (type === 'error') {
                errorMessage.innerHTML = `<i class="fas fa-exclamation-circle"></i> ${text}`;
                errorMessage.style.display = 'block';
            } else if (type === 'success') {
                successMessage.innerHTML = `<i class="fas fa-check-circle"></i> ${text}`;
                successMessage.style.display = 'block';
                
                // Auto hide success message after 3 seconds
                setTimeout(() => {
                    successMessage.style.display = 'none';
                }, 3000);
            }
        }

        // Check authentication before download
        window.checkAuth = function(event, productName) {
            const user = auth.currentUser;
            
            if (!user) {
                event.preventDefault();
                showLoginRequired();
                return false;
            }
            
            // User is authenticated, proceed with download confirmation
            if (confirm(`You are about to download: ${productName}\n\nMake sure you have an active subscription to use this product.`)) {
                // Track download (you could add analytics here)
                console.log(`Download started by ${user.email}: ${productName}`);
                return true;
            } else {
                event.preventDefault();
                return false;
            }
        };

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
                
                // Clear form
                document.getElementById('loginFormElement').reset();
                
                showMessage('success', 'Login successful!');
                
                // Close modal after successful login
                setTimeout(() => {
                    hideLoginModal();
                }, 1000);
                
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
                
                // Close modal after successful signup
                setTimeout(() => {
                    hideLoginModal();
                }, 1000);
                
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
                showMessage('success', 'You have been signed out successfully.');
            } catch (error) {
                console.error('Logout error:', error);
                showMessage('error', 'Failed to sign out: ' + error.message);
            }
        };

        // Close modal when clicking outside
        loginModal.addEventListener('click', (e) => {
            if (e.target === loginModal) {
                hideLoginModal();
            }
        });

        loginRequired.addEventListener('click', (e) => {
            if (e.target === loginRequired) {
                hideLoginRequired();
            }
        });
    </script>
</body>
</html>
