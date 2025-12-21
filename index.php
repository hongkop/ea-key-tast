<?php
// Download URLs for each product
$download_links = [
    'trading_vps' => 'https://example.com/downloads/trading-vps-setup.zip',
    'trading_robot' => '"https://zeahong.up.railway.app/4T8 EA Scalping.ex5',
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
    <title>Download Trading Tools & EAs</title>
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
            max-width: 1200px;
            margin: 0 auto;
        }

        header {
            text-align: center;
            padding: 40px 20px;
            margin-bottom: 30px;
        }

        h1 {
            font-size: 2.8rem;
            margin-bottom: 10px;
            color: #ffffff;
            text-shadow: 0 2px 10px rgba(0, 0, 0, 0.3);
        }

        .subtitle {
            font-size: 1.2rem;
            color: #a0c8e0;
            max-width: 800px;
            margin: 0 auto;
            line-height: 1.6;
        }

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

        .admin-link {
            position: fixed;
            top: 20px;
            right: 20px;
            background: rgba(52, 152, 219, 0.9);
            color: white;
            padding: 10px 20px;
            border-radius: 8px;
            text-decoration: none;
            font-weight: 600;
            z-index: 1000;
            box-shadow: 0 4px 15px rgba(52, 152, 219, 0.4);
            transition: all 0.3s ease;
        }

        .admin-link:hover {
            background: rgba(41, 128, 185, 0.9);
            transform: translateY(-2px);
            box-shadow: 0 6px 20px rgba(52, 152, 219, 0.6);
        }

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

        @media (max-width: 768px) {
            .download-grid {
                grid-template-columns: 1fr;
                padding: 10px;
            }
            
            h1 {
                font-size: 2.2rem;
            }
            
            .download-card {
                padding: 20px;
            }
            
            .admin-link {
                position: static;
                display: block;
                margin: 20px auto;
                width: fit-content;
            }
        }
    </style>
</head>
<body>
    <!-- Admin Panel Link -->
    <a href="licenses.php" class="admin-link">
        <i class="fas fa-user-shield"></i> Admin Panel
    </a>
    
    <div class="container">
        <header>
            <h1><i class="fas fa-download"></i> Trading Tools Download Center</h1>
            <p class="subtitle">Download your purchased trading tools, expert advisors, and configurations. All files are pre-configured and ready to use with your trading platforms.</p>
        </header>
        
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
                
                <a href="<?php echo $download_links['trading_vps']; ?>" class="download-btn" download>
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
                
                <a href="<?php echo $download_links['trading_robot']; ?>" class="download-btn" download>
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
                
                <a href="<?php echo $download_links['btrader_tools']; ?>" class="download-btn" download>
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

    <script>
        // Simple confirmation for download
        document.querySelectorAll('.download-btn').forEach(button => {
            button.addEventListener('click', function(e) {
                const productName = this.closest('.download-card').querySelector('.card-title').textContent;
                if(confirm(`You are about to download: ${productName}\n\nMake sure you have an active subscription to use this product.`)) {
                    // Track download (you could add analytics here)
                    console.log(`Download started: ${productName}`);
                } else {
                    e.preventDefault();
                }
            });
        });
    </script>
</body>
</html>
