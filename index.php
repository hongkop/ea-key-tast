<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Login Page</title>
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">
    <style>
        * {
            margin: 0;
            padding: 0;
            box-sizing: border-box;
            font-family: 'Segoe UI', Tahoma, Geneva, Verdana, sans-serif;
        }

        body {
            display: flex;
            justify-content: center;
            align-items: center;
            min-height: 100vh;
            background-color: #f0f2f5;
            padding: 20px;
        }

        .container {
            display: flex;
            width: 100%;
            max-width: 1100px;
            box-shadow: 0 10px 30px rgba(0, 0, 0, 0.1);
            border-radius: 15px;
            overflow: hidden;
        }

        .background-section {
            flex: 1;
            background-image: url('https://images.unsplash.com/photo-1497366754035-f200968a6e72?ixlib=rb-4.0.3&ixid=M3wxMjA3fDB8MHxwaG90by1wYWdlfHx8fGVufDB8fHx8fA%3D%3D&auto=format&fit=crop&w=2069&q=80');
            background-size: cover;
            background-position: center;
            position: relative;
            display: flex;
            align-items: center;
            justify-content: center;
        }

        .background-overlay {
            position: absolute;
            top: 0;
            left: 0;
            width: 100%;
            height: 100%;
            background: linear-gradient(135deg, rgba(41, 128, 185, 0.85) 0%, rgba(109, 213, 250, 0.8) 100%);
            display: flex;
            flex-direction: column;
            justify-content: center;
            align-items: center;
            color: white;
            padding: 30px;
            text-align: center;
        }

        .background-overlay h2 {
            font-size: 2.2rem;
            margin-bottom: 15px;
            font-weight: 700;
        }

        .background-overlay p {
            font-size: 1.1rem;
            opacity: 0.9;
            line-height: 1.6;
        }

        .login-section {
            flex: 1;
            background-color: white;
            padding: 60px 50px;
            display: flex;
            flex-direction: column;
            justify-content: center;
        }

        .login-header {
            margin-bottom: 40px;
        }

        .login-header h1 {
            color: #2c3e50;
            font-size: 2.5rem;
            font-weight: 700;
            margin-bottom: 10px;
        }

        .login-header p {
            color: #7f8c8d;
            font-size: 1.1rem;
        }

        .form-group {
            margin-bottom: 25px;
        }

        .form-group label {
            display: block;
            color: #2c3e50;
            font-weight: 600;
            margin-bottom: 8px;
            font-size: 1.05rem;
        }

        .input-with-icon {
            position: relative;
        }

        .input-with-icon i {
            position: absolute;
            left: 15px;
            top: 50%;
            transform: translateY(-50%);
            color: #7f8c8d;
            font-size: 1.1rem;
        }

        .form-group input {
            width: 100%;
            padding: 15px 15px 15px 45px;
            border: 2px solid #e0e0e0;
            border-radius: 8px;
            font-size: 1rem;
            transition: all 0.3s;
        }

        .form-group input:focus {
            border-color: #3498db;
            outline: none;
            box-shadow: 0 0 0 3px rgba(52, 152, 219, 0.2);
        }

        .form-options {
            display: flex;
            justify-content: space-between;
            align-items: center;
            margin-bottom: 30px;
        }

        .remember-me {
            display: flex;
            align-items: center;
        }

        .remember-me input {
            margin-right: 8px;
            transform: scale(1.2);
        }

        .remember-me label {
            color: #555;
            font-size: 0.95rem;
        }

        .forgot-password {
            color: #3498db;
            text-decoration: none;
            font-weight: 600;
            font-size: 0.95rem;
            transition: color 0.3s;
        }

        .forgot-password:hover {
            color: #2980b9;
            text-decoration: underline;
        }

        .login-button {
            background-color: #3498db;
            color: white;
            border: none;
            padding: 16px;
            border-radius: 8px;
            font-size: 1.1rem;
            font-weight: 600;
            cursor: pointer;
            transition: background-color 0.3s;
            width: 100%;
            margin-bottom: 25px;
        }

        .login-button:hover {
            background-color: #2980b9;
        }

        .register-link {
            text-align: center;
            color: #7f8c8d;
            font-size: 1rem;
        }

        .register-link a {
            color: #3498db;
            text-decoration: none;
            font-weight: 600;
        }

        .register-link a:hover {
            text-decoration: underline;
        }

        .background-controls {
            background-color: white;
            border-radius: 10px;
            padding: 20px;
            margin-top: 20px;
            width: 90%;
            box-shadow: 0 5px 15px rgba(0, 0, 0, 0.1);
        }

        .background-controls h3 {
            color: #2c3e50;
            margin-bottom: 15px;
            font-size: 1.2rem;
        }

        .url-input-group {
            display: flex;
            gap: 10px;
        }

        .url-input-group input {
            flex: 1;
            padding: 12px 15px;
            border: 2px solid #e0e0e0;
            border-radius: 8px;
            font-size: 0.95rem;
        }

        .url-input-group button {
            background-color: #2ecc71;
            color: white;
            border: none;
            padding: 12px 20px;
            border-radius: 8px;
            cursor: pointer;
            font-weight: 600;
            transition: background-color 0.3s;
        }

        .url-input-group button:hover {
            background-color: #27ae60;
        }

        .preset-backgrounds {
            display: flex;
            gap: 10px;
            margin-top: 15px;
            flex-wrap: wrap;
        }

        .preset-bg {
            width: 60px;
            height: 60px;
            border-radius: 8px;
            cursor: pointer;
            background-size: cover;
            background-position: center;
            border: 3px solid transparent;
            transition: transform 0.3s, border-color 0.3s;
        }

        .preset-bg:hover {
            transform: scale(1.05);
        }

        .preset-bg.active {
            border-color: #3498db;
        }

        @media (max-width: 900px) {
            .container {
                flex-direction: column;
            }
            
            .background-section {
                min-height: 300px;
            }
            
            .login-section {
                padding: 40px 30px;
            }
        }
    </style>
</head>
<body>
    <div class="container">
        <div class="background-section" id="backgroundSection">
            <div class="background-overlay">
                <h2>Welcome Back</h2>
                <p>Sign in to access your account and explore our platform's features. We're glad to have you back.</p>
                
                <div class="background-controls">
                    <h3>Change Background Image</h3>
                    <div class="url-input-group">
                        <input type="text" id="bgUrlInput" placeholder="Enter image URL">
                        <button id="applyBgBtn">Apply</button>
                    </div>
                    <div class="preset-backgrounds">
                        <div class="preset-bg active" style="background-image: url('https://images.unsplash.com/photo-1497366754035-f200968a6e72?ixlib=rb-4.0.3&auto=format&fit=crop&w=2069&q=80');" data-url="https://images.unsplash.com/photo-1497366754035-f200968a6e72?ixlib=rb-4.0.3&auto=format&fit=crop&w=2069&q=80"></div>
                        <div class="preset-bg" style="background-image: url('https://images.unsplash.com/photo-1506905925346-21bda4d32df4?ixlib=rb-4.0.3&auto=format&fit=crop&w=2070&q=80');" data-url="https://images.unsplash.com/photo-1506905925346-21bda4d32df4?ixlib=rb-4.0.3&auto=format&fit=crop&w=2070&q=80"></div>
                        <div class="preset-bg" style="background-image: url('https://images.unsplash.com/photo-1519681393784-d120267933ba?ixlib=rb-4.0.3&auto=format&fit=crop&w=2070&q=80');" data-url="https://images.unsplash.com/photo-1519681393784-d120267933ba?ixlib=rb-4.0.3&auto=format&fit=crop&w=2070&q=80"></div>
                        <div class="preset-bg" style="background-image: url('https://images.unsplash.com/photo-1518837695005-2083093ee35b?ixlib=rb-4.0.3&auto=format&fit=crop&w=2070&q=80');" data-url="https://images.unsplash.com/photo-1518837695005-2083093ee35b?ixlib=rb-4.0.3&auto=format&fit=crop&w=2070&q=80"></div>
                    </div>
                </div>
            </div>
        </div>

        <div class="login-section">
            <div class="login-header">
                <h1>Login</h1>
                <p>Enter your credentials to access your account</p>
            </div>

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
                    <a href="#" class="forgot-password">Forgot password?</a>
                </div>

                <button type="submit" class="login-button">Login</button>

                <div class="register-link">
                    Don't have an account? <a href="#">Register</a>
                </div>
            </form>
        </div>
    </div>

    <script>
        document.addEventListener('DOMContentLoaded', function() {
            const loginForm = document.getElementById('loginForm');
            const backgroundSection = document.getElementById('backgroundSection');
            const bgUrlInput = document.getElementById('bgUrlInput');
            const applyBgBtn = document.getElementById('applyBgBtn');
            const presetBackgrounds = document.querySelectorAll('.preset-bg');
            
            // Login form submission
            loginForm.addEventListener('submit', function(e) {
                e.preventDefault();
                
                const username = document.getElementById('username').value;
                const password = document.getElementById('password').value;
                const rememberMe = document.getElementById('rememberMe').checked;
                
                // Simple validation
                if (username && password) {
                    // In a real application, you would send this data to a server
                    alert(`Login attempted with:\nUsername: ${username}\nRemember me: ${rememberMe ? 'Yes' : 'No'}\n\n(In a real app, this would authenticate with a server.)`);
                    
                    // Clear form (in a real app, you would redirect on success)
                    loginForm.reset();
                } else {
                    alert('Please fill in both username and password fields.');
                }
            });
            
            // Apply custom background URL
            applyBgBtn.addEventListener('click', function() {
                const url = bgUrlInput.value.trim();
                
                if (url) {
                    // Test if the URL is valid
                    const img = new Image();
                    img.onload = function() {
                        backgroundSection.style.backgroundImage = `url('${url}')`;
                        
                        // Update active preset
                        presetBackgrounds.forEach(bg => bg.classList.remove('active'));
                        
                        // Show success message
                        alert('Background image updated successfully!');
                    };
                    
                    img.onerror = function() {
                        alert('Could not load image from the provided URL. Please check the URL and try again.');
                    };
                    
                    img.src = url;
                } else {
                    alert('Please enter a valid image URL.');
                }
            });
            
            // Allow pressing Enter in the URL input
            bgUrlInput.addEventListener('keypress', function(e) {
                if (e.key === 'Enter') {
                    applyBgBtn.click();
                }
            });
            
            // Set up preset background selection
            presetBackgrounds.forEach(bg => {
                bg.addEventListener('click', function() {
                    const url = this.getAttribute('data-url');
                    
                    // Update background
                    backgroundSection.style.backgroundImage = `url('${url}')`;
                    
                    // Update active class
                    presetBackgrounds.forEach(b => b.classList.remove('active'));
                    this.classList.add('active');
                    
                    // Update URL input
                    bgUrlInput.value = url;
                });
            });
            
            // Forgot password link
            document.querySelector('.forgot-password').addEventListener('click', function(e) {
                e.preventDefault();
                alert('Password reset functionality would be implemented here. In a real app, this would redirect to a password reset page.');
            });
            
            // Register link
            document.querySelector('.register-link a').addEventListener('click', function(e) {
                e.preventDefault();
                alert('Registration functionality would be implemented here. In a real app, this would redirect to a registration page.');
            });
        });
    </script>
</body>
</html>
