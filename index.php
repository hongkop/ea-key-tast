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
            font-family: -apple-system, BlinkMacSystemFont, 'Segoe UI', Roboto, Oxygen, Ubuntu, sans-serif;
        }

        body {
            background: #f8f9fa;
            min-height: 100vh;
            display: flex;
            align-items: center;
            justify-content: center;
            padding: 20px;
            background-image: url('https://images.unsplash.com/photo-1618005198919-d3d4b5a92ead?ixlib=rb-4.0.3&auto=format&fit=crop&w=2000&q=80');
            background-size: cover;
            background-position: center;
            background-repeat: no-repeat;
        }

        /* Login Container */
        .login-container {
            width: 100%;
            max-width: 420px;
            background: rgba(255, 255, 255, 0.95);
            backdrop-filter: blur(10px);
            border-radius: 16px;
            box-shadow: 0 12px 40px rgba(0, 0, 0, 0.15);
            overflow: hidden;
            border: 1px solid rgba(255, 255, 255, 0.3);
        }

        /* Login Header */
        .login-header {
            background: linear-gradient(135deg, #667eea 0%, #764ba2 100%);
            color: white;
            padding: 40px 30px 30px;
            text-align: center;
        }

        .login-logo {
            font-size: 2.8rem;
            margin-bottom: 15px;
        }

        .login-header h1 {
            font-size: 1.8rem;
            font-weight: 600;
            margin-bottom: 8px;
        }

        .login-header p {
            opacity: 0.9;
            font-size: 0.95rem;
        }

        /* Login Form */
        .login-form {
            padding: 40px 35px;
        }

        .form-title {
            color: #333;
            font-size: 1.5rem;
            margin-bottom: 30px;
            text-align: center;
            font-weight: 600;
        }

        .form-group {
            margin-bottom: 24px;
        }

        .form-group label {
            display: block;
            margin-bottom: 8px;
            color: #555;
            font-weight: 500;
            font-size: 14px;
            text-transform: uppercase;
            letter-spacing: 0.5px;
        }

        .input-group {
            position: relative;
        }

        .input-group input {
            width: 100%;
            padding: 15px 15px 15px 48px;
            border: 2px solid #e1e5ee;
            border-radius: 10px;
            font-size: 16px;
            transition: all 0.3s;
            background: #f8f9fa;
            color: #333;
        }

        .input-group input:focus {
            outline: none;
            border-color: #667eea;
            background: white;
            box-shadow: 0 0 0 4px rgba(102, 126, 234, 0.1);
        }

        .input-group i {
            position: absolute;
            left: 15px;
            top: 50%;
            transform: translateY(-50%);
            color: #667eea;
            font-size: 18px;
        }

        /* Remember Me & Forgot Password */
        .form-options {
            display: flex;
            justify-content: space-between;
            align-items: center;
            margin-bottom: 25px;
            font-size: 14px;
        }

        .remember-me {
            display: flex;
            align-items: center;
            gap: 8px;
            color: #555;
        }

        .remember-me input {
            width: 16px;
            height: 16px;
            accent-color: #667eea;
        }

        .forgot-password a {
            color: #667eea;
            text-decoration: none;
            font-weight: 500;
            transition: color 0.2s;
        }

        .forgot-password a:hover {
            color: #764ba2;
            text-decoration: underline;
        }

        /* Login Button */
        .login-btn {
            width: 100%;
            padding: 16px;
            background: linear-gradient(135deg, #667eea 0%, #764ba2 100%);
            color: white;
            border: none;
            border-radius: 10px;
            font-size: 16px;
            font-weight: 600;
            cursor: pointer;
            transition: all 0.3s;
            margin-bottom: 25px;
            letter-spacing: 0.5px;
            text-transform: uppercase;
        }

        .login-btn:hover {
            transform: translateY(-2px);
            box-shadow: 0 8px 25px rgba(102, 126, 234, 0.3);
        }

        .login-btn:active {
            transform: translateY(0);
        }

        /* Register Link */
        .register-link {
            text-align: center;
            color: #666;
            font-size: 14px;
            padding-top: 20px;
            border-top: 1px solid #eee;
        }

        .register-link a {
            color: #667eea;
            text-decoration: none;
            font-weight: 600;
            margin-left: 5px;
        }

        .register-link a:hover {
            text-decoration: underline;
        }

        /* Messages */
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

        /* Loading Animation */
        .loading {
            display: inline-block;
            width: 20px;
            height: 20px;
            border: 3px solid #f3f3f3;
            border-top: 3px solid #667eea;
            border-radius: 50%;
            animation: spin 1s linear infinite;
        }

        @keyframes spin {
            0% { transform: rotate(0deg); }
            100% { transform: rotate(360deg); }
        }

        /* Responsive */
        @media (max-width: 480px) {
            .login-container {
                max-width: 100%;
                margin: 0 15px;
            }
            
            .login-form {
                padding: 30px 25px;
            }
            
            .login-header {
                padding: 30px 20px 25px;
            }
            
            .form-options {
                flex-direction: column;
                gap: 15px;
                align-items: flex-start;
            }
        }

        /* Dashboard Styles (Keep existing dashboard styles) */
        .dashboard {
            display: none;
            width: 100%;
            max-width: 1200px;
            margin: 0 auto;
            padding: 20px;
        }

        /* ... Keep all your existing dashboard CSS from the original code ... */
        /* I'm keeping your existing dashboard CSS intact below */

    </style>
</head>
<body>
    
    <!-- Login Page -->
    <div class="login-container" id="loginPage">
        <div class="login-header">
            <div class="login-logo">
                <i class="fas fa-chart-line"></i>
            </div>
            <h1>Zeahong Trading</h1>
            <p>Secure Authentication System</p>
        </div>

        <div class="login-form">
            <div class="message info" id="infoMessage">
                <i class="fas fa-info-circle"></i> Welcome to Zeahong Trading
            </div>
            
            <div class="message error" id="errorMessage"></div>
            <div class="message success" id="successMessage"></div>

            <h2 class="form-title">Login</h2>
            
            <form id="loginFormElement">
                <div class="form-group">
                    <label for="loginEmail">Username</label>
                    <div class="input-group">
                        <i class="fas fa-user"></i>
                        <input type="text" id="loginEmail" placeholder="Enter your username or email" required>
                    </div>
                </div>
                
                <div class="form-group">
                    <label for="loginPassword">Password</label>
                    <div class="input-group">
                        <i class="fas fa-lock"></i>
                        <input type="password" id="loginPassword" placeholder="Enter your password" required>
                    </div>
                </div>
                
                <div class="form-options">
                    <div class="remember-me">
                        <input type="checkbox" id="rememberMe">
                        <label for="rememberMe">Remember me</label>
                    </div>
                    <div class="forgot-password">
                        <a href="#" onclick="showForgotPassword()">Forgot password?</a>
                    </div>
                </div>
                
                <button type="submit" class="login-btn" id="loginBtn">
                    <i class="fas fa-sign-in-alt"></i> Login
                </button>
            </form>
            
            <div class="register-link">
                Don't have an account? <a href="#" onclick="showSignup()">Register</a>
            </div>
        </div>
    </div>

    <!-- Dashboard (Keep your existing dashboard HTML) -->
    <div class="dashboard" id="dashboard">
        <!-- ... Your existing dashboard HTML code remains unchanged ... -->
        
        <!-- I'm keeping your existing dashboard HTML intact -->
        <!-- Just make sure to add the necessary divs and structure -->

    </div>

    <!-- Firebase SDK and JavaScript -->
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
        const errorMessage = document.getElementById('errorMessage');
        const successMessage = document.getElementById('successMessage');
        const infoMessage = document.getElementById('infoMessage');

        // Initialize
        init();

        async function init() {
            try {
                // Setup auth state listener
                setupAuthListener();
                
                // Hide info message after 3 seconds
                setTimeout(() => {
                    if (infoMessage) infoMessage.style.display = 'none';
                }, 3000);
                
            } catch (error) {
                console.error('Initialization error:', error);
                showMessage('error', `Failed to initialize: ${error.message}`);
            }
        }

        function setupAuthListener() {
            onAuthStateChanged(auth, async (user) => {
                if (user) {
                    showDashboard(user);
                    await updateUserLicenseInfo(user.email);
                } else {
                    showLoginPage();
                }
            }, (error) => {
                console.error('Auth state error:', error);
                showMessage('error', `Authentication error: ${error.message}`);
            });
        }

        function showDashboard(user) {
            loginPage.style.display = 'none';
            dashboard.style.display = 'block';
            
            // Update user info
            const welcomeUserName = document.getElementById('welcomeUserName');
            const userEmailDisplay = document.getElementById('userEmailDisplay');
            const userAvatar = document.getElementById('userAvatar');
            
            if (welcomeUserName) {
                const userDisplayName = user.displayName || user.email.split('@')[0];
                welcomeUserName.textContent = userDisplayName;
            }
            
            if (userEmailDisplay) {
                userEmailDisplay.textContent = user.email;
            }
            
            if (userAvatar) {
                const userInitial = user.displayName?.charAt(0) || user.email.charAt(0);
                userAvatar.textContent = userInitial.toUpperCase();
            }
        }

        function showLoginPage() {
            dashboard.style.display = 'none';
            loginPage.style.display = 'block';
        }

        // Show message function
        function showMessage(type, text) {
            const errorMsg = document.getElementById('errorMessage');
            const successMsg = document.getElementById('successMessage');
            
            if (errorMsg) errorMsg.style.display = 'none';
            if (successMsg) successMsg.style.display = 'none';
            
            if (type === 'error' && errorMsg) {
                errorMsg.innerHTML = `<i class="fas fa-exclamation-circle"></i> ${text}`;
                errorMsg.style.display = 'block';
            } else if (type === 'success' && successMsg) {
                successMsg.innerHTML = `<i class="fas fa-check-circle"></i> ${text}`;
                successMsg.style.display = 'block';
                setTimeout(() => {
                    successMsg.style.display = 'none';
                }, 3000);
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
                // Try to sign in with email/password
                const userCredential = await signInWithEmailAndPassword(auth, email, password);
                
                // Store user in session
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

        // You'll need to add the signup and forgot password functionality
        window.showSignup = function() {
            // Create a simple signup modal or redirect
            alert('Signup functionality would be implemented here');
        };

        window.showForgotPassword = function() {
            const email = prompt('Please enter your email address to reset password:');
            if (email) {
                sendPasswordResetEmail(auth, email)
                    .then(() => {
                        alert('Password reset email sent! Check your inbox.');
                    })
                    .catch((error) => {
                        alert('Error: ' + error.message);
                    });
            }
        };

        // Add these functions as needed for your dashboard
        async function updateUserLicenseInfo(userEmail) {
            // Your existing license info function
        }

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

        // You'll need to copy your existing dashboard functions here
        // including copyLicenseKey, confirmDownload, etc.

    </script>
</body>
</html>
