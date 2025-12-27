<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Login - Zeahong Trading</title>
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">
    <style>
        * {
            margin: 0;
            padding: 0;
            box-sizing: border-box;
            font-family: 'Segoe UI', Tahoma, Geneva, Verdana, sans-serif;
        }

        body {
            background: linear-gradient(135deg, #667eea 0%, #764ba2 100%);
            min-height: 100vh;
            display: flex;
            align-items: center;
            justify-content: center;
            padding: 20px;
        }

        .login-container {
            width: 100%;
            max-width: 420px;
        }

        .login-card {
            background: white;
            border-radius: 12px;
            box-shadow: 0 15px 35px rgba(0, 0, 0, 0.2);
            overflow: hidden;
        }

        .login-header {
            text-align: center;
            padding: 40px 30px 30px;
            background: linear-gradient(135deg, #667eea 0%, #764ba2 100%);
            color: white;
        }

        .login-header h1 {
            font-size: 28px;
            font-weight: 600;
            margin-bottom: 10px;
        }

        .login-header p {
            opacity: 0.9;
            font-size: 15px;
        }

        .login-body {
            padding: 40px 35px;
        }

        .form-group {
            margin-bottom: 24px;
        }

        .form-group label {
            display: block;
            margin-bottom: 8px;
            color: #333;
            font-weight: 500;
            font-size: 14px;
        }

        .input-with-icon {
            position: relative;
        }

        .input-with-icon i {
            position: absolute;
            left: 16px;
            top: 50%;
            transform: translateY(-50%);
            color: #667eea;
            font-size: 18px;
        }

        .input-with-icon input {
            width: 100%;
            padding: 16px 16px 16px 48px;
            border: 2px solid #e1e5ee;
            border-radius: 8px;
            font-size: 16px;
            transition: all 0.3s;
            color: #333;
        }

        .input-with-icon input:focus {
            outline: none;
            border-color: #667eea;
            box-shadow: 0 0 0 3px rgba(102, 126, 234, 0.1);
        }

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
            width: 18px;
            height: 18px;
            accent-color: #667eea;
        }

        .forgot-password {
            color: #667eea;
            text-decoration: none;
            font-weight: 500;
        }

        .forgot-password:hover {
            text-decoration: underline;
        }

        .login-button {
            width: 100%;
            padding: 16px;
            background: linear-gradient(135deg, #667eea 0%, #764ba2 100%);
            color: white;
            border: none;
            border-radius: 8px;
            font-size: 16px;
            font-weight: 600;
            cursor: pointer;
            transition: all 0.3s;
            display: flex;
            align-items: center;
            justify-content: center;
            gap: 10px;
        }

        .login-button:hover {
            transform: translateY(-2px);
            box-shadow: 0 10px 20px rgba(102, 126, 234, 0.3);
        }

        .register-link {
            text-align: center;
            margin-top: 30px;
            padding-top: 25px;
            border-top: 1px solid #eee;
            color: #666;
            font-size: 15px;
        }

        .register-link a {
            color: #667eea;
            text-decoration: none;
            font-weight: 600;
        }

        .register-link a:hover {
            text-decoration: underline;
        }

        .messages {
            margin-bottom: 20px;
        }

        .message {
            padding: 12px 16px;
            border-radius: 8px;
            margin-bottom: 16px;
            font-size: 14px;
            display: none;
        }

        .message.error {
            background-color: #fee;
            color: #e74c3c;
            border-left: 4px solid #e74c3c;
        }

        .message.success {
            background-color: #efc;
            color: #27ae60;
            border-left: 4px solid #27ae60;
        }

        .message.info {
            background-color: #e3f2fd;
            color: #2196f3;
            border-left: 4px solid #2196f3;
        }

        .loading {
            display: inline-block;
            width: 20px;
            height: 20px;
            border: 3px solid rgba(255, 255, 255, 0.3);
            border-top: 3px solid white;
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
            }
            
            .login-header {
                padding: 30px 20px 20px;
            }
            
            .login-body {
                padding: 30px 25px;
            }
            
            .login-header h1 {
                font-size: 24px;
            }
            
            .form-options {
                flex-direction: column;
                align-items: flex-start;
                gap: 12px;
            }
        }
    </style>
</head>
<body>
    <div class="login-container">
        <div class="login-card">
            <!-- Header -->
            <div class="login-header">
                <h1>Login</h1>
                <p>Access your trading dashboard</p>
            </div>
            
            <!-- Messages -->
            <div class="messages">
                <div class="message error" id="errorMessage"></div>
                <div class="message success" id="successMessage"></div>
                <div class="message info" id="infoMessage">
                    <i class="fas fa-info-circle"></i> Welcome to Zeahong Trading Platform
                </div>
            </div>
            
            <!-- Login Form -->
            <div class="login-body">
                <form id="loginForm">
                    <div class="form-group">
                        <label for="username">Username</label>
                        <div class="input-with-icon">
                            <i class="fas fa-user"></i>
                            <input type="text" id="username" placeholder="Enter your username" required>
                        </div>
                    </div>
                    
                    <div class="form-group">
                        <label for="password">Password</label>
                        <div class="input-with-icon">
                            <i class="fas fa-lock"></i>
                            <input type="password" id="password" placeholder="Enter your password" required>
                        </div>
                    </div>
                    
                    <div class="form-options">
                        <div class="remember-me">
                            <input type="checkbox" id="rememberMe">
                            <label for="rememberMe">Remember me</label>
                        </div>
                        <a href="#" class="forgot-password" onclick="showForgotPassword()">Forgot password?</a>
                    </div>
                    
                    <button type="submit" class="login-button" id="loginBtn">
                        <span id="loginBtnText">Login</span>
                    </button>
                </form>
                
                <div class="register-link">
                    Don't have an account? <a href="#" onclick="showSignup()">Register</a>
                </div>
            </div>
        </div>
    </div>

    <!-- Firebase SDK and scripts remain the same as before -->
    <script type="module">
        // Your existing Firebase and JavaScript code remains here
        // I'm keeping only the login form functionality for clarity
        
        // Import Firebase modules
        import { initializeApp } from "https://www.gstatic.com/firebasejs/10.7.1/firebase-app.js";
        import { 
            getAuth, 
            signInWithEmailAndPassword,
            onAuthStateChanged
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
        const loginForm = document.getElementById('loginForm');
        const errorMessage = document.getElementById('errorMessage');
        const successMessage = document.getElementById('successMessage');
        const infoMessage = document.getElementById('infoMessage');
        const loginBtn = document.getElementById('loginBtn');
        const loginBtnText = document.getElementById('loginBtnText');

        // Hide info message after 3 seconds
        setTimeout(() => {
            infoMessage.style.display = 'none';
        }, 3000);

        // Show message function
        function showMessage(type, text) {
            // Hide all messages first
            errorMessage.style.display = 'none';
            successMessage.style.display = 'none';
            infoMessage.style.display = 'none';
            
            if (type === 'error') {
                errorMessage.innerHTML = `<i class="fas fa-exclamation-circle"></i> ${text}`;
                errorMessage.style.display = 'block';
            } else if (type === 'success') {
                successMessage.innerHTML = `<i class="fas fa-check-circle"></i> ${text}`;
                successMessage.style.display = 'block';
                
                setTimeout(() => {
                    successMessage.style.display = 'none';
                }, 3000);
            } else if (type === 'info') {
                infoMessage.innerHTML = `<i class="fas fa-info-circle"></i> ${text}`;
                infoMessage.style.display = 'block';
            }
        }

        // Login form submission
        loginForm.addEventListener('submit', async (e) => {
            e.preventDefault();
            
            const username = document.getElementById('username').value;
            const password = document.getElementById('password').value;
            const rememberMe = document.getElementById('rememberMe').checked;
            
            if (!username || !password) {
                showMessage('error', 'Please fill in all fields');
                return;
            }
            
            // For Firebase, we use email as username
            // You might need to adjust this based on your user structure
            const email = username.includes('@') ? username : `${username}@zeahong.com`;
            
            // Show loading state
            loginBtn.innerHTML = '<div class="loading"></div>';
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
                        name: userCredential.user.displayName || userCredential.user.email.split('@')[0],
                        remember: rememberMe
                    })
                });
                
                // Clear form
                loginForm.reset();
                showMessage('success', 'Login successful! Redirecting...');
                
                // Redirect or show dashboard (you'll need to implement this)
                setTimeout(() => {
                    window.location.href = 'dashboard.php';
                }, 1500);
                
            } catch (error) {
                console.error('Login error:', error);
                
                let errorMsg = 'Login failed. ';
                
                switch(error.code) {
                    case 'auth/user-not-found':
                        errorMsg = 'No account found with this username/email.';
                        break;
                    case 'auth/wrong-password':
                        errorMsg = 'Incorrect password. Please try again.';
                        break;
                    case 'auth/invalid-email':
                        errorMsg = 'Invalid username/email format.';
                        break;
                    case 'auth/too-many-requests':
                        errorMsg = 'Too many failed attempts. Please try again later.';
                        break;
                    default:
                        errorMsg = 'An error occurred. Please try again.';
                }
                
                showMessage('error', errorMsg);
                
            } finally {
                // Restore button state
                loginBtn.innerHTML = '<span id="loginBtnText">Login</span>';
                loginBtn.disabled = false;
            }
        });

        // Setup auth state listener
        onAuthStateChanged(auth, (user) => {
            if (user) {
                // User is already logged in, redirect to dashboard
                console.log('User already logged in:', user.email);
                // You might want to redirect immediately or show a message
            }
        }, (error) => {
            console.error('Auth state error:', error);
        });

        // Show forgot password (to be implemented)
        window.showForgotPassword = function() {
            showMessage('info', 'Password reset feature will be implemented soon.');
        };

        // Show signup (to be implemented)
        window.showSignup = function() {
            showMessage('info', 'Registration feature will be implemented soon.');
        };
    </script>
</body>
</html>
