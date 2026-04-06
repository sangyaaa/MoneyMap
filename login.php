<?php
require_once 'config.php';

// If already logged in, redirect to index
if (isLoggedIn()) {
    header('Location: index.php');
    exit();
}

$error = '';
$success = '';

// Handle login
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['action'])) {
    if ($_POST['action'] === 'login') {
        $username = trim($_POST['username']);
        $password = $_POST['password'];
        
        if (empty($username) || empty($password)) {
            $error = 'Please enter username/email and password';
        } else {
            $stmt = $conn->prepare("SELECT id, username, email, password FROM users WHERE username = ? OR email = ?");
            $stmt->bind_param("ss", $username, $username);
            $stmt->execute();
            $result = $stmt->get_result();
            
            if ($result->num_rows === 0) {
                $error = 'Invalid username or password';
            } else {
                $user = $result->fetch_assoc();
                if ($password === $user['password']) {
                    $_SESSION['user_id'] = $user['id'];
                    $_SESSION['username'] = $user['username'];
                    $_SESSION['email'] = $user['email'];
                    header('Location: index.php');
                    exit();
                } else {
                    $error = 'Invalid username or password';
                }
            }
            $stmt->close();
        }
    } elseif ($_POST['action'] === 'register') {
        $username = trim($_POST['reg_username']);
        $email = trim($_POST['reg_email']);
        $password = $_POST['reg_password'];
        
        if (empty($username) || empty($email) || empty($password)) {
            $error = 'All fields are required';
        } elseif (!filter_var($email, FILTER_VALIDATE_EMAIL)) {
            $error = 'Invalid email format';
        } elseif (strlen($password) < 6) {
            $error = 'Password must be at least 6 characters';
        } else {
            $checkStmt = $conn->prepare("SELECT id FROM users WHERE username = ? OR email = ?");
            $checkStmt->bind_param("ss", $username, $email);
            $checkStmt->execute();
            $checkResult = $checkStmt->get_result();
            
            if ($checkResult->num_rows > 0) {
                $error = 'Username or email already exists';
            } else {
                $insertStmt = $conn->prepare("INSERT INTO users (username, email, password) VALUES (?, ?, ?)");
                $insertStmt->bind_param("sss", $username, $email, $password);
                
                if ($insertStmt->execute()) {
                    $userId = $conn->insert_id;
                    
                    $budgetStmt = $conn->prepare("INSERT INTO settings (setting_key, setting_value, user_id) VALUES ('initial_budget', 0, ?)");
                    $budgetStmt->bind_param("i", $userId);
                    $budgetStmt->execute();
                    $budgetStmt->close();
                    
                    $success = 'Registration successful! Please login.';
                } else {
                    $error = 'Registration failed. Please try again.';
                }
                $insertStmt->close();
            }
            $checkStmt->close();
        }
    }
}
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>MoneyMap - Login | Track Your Finances</title>
    <link href="https://fonts.googleapis.com/css2?family=Inter:wght@300;400;500;600;700;800&display=swap" rel="stylesheet">
    <link href="https://fonts.googleapis.com/css2?family=Orbitron:wght@400;500;600;700&display=swap" rel="stylesheet">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">
    <style>
        * {
            margin: 0;
            padding: 0;
            box-sizing: border-box;
        }
        
        body {
            font-family: 'Inter', 'Segoe UI', sans-serif;
            background: linear-gradient(135deg, #e8f0f5 0%, #d4e4ed 100%);
            min-height: 100vh;
            display: flex;
            justify-content: center;
            align-items: center;
            padding: 20px;
            position: relative;
            overflow-x: hidden;
        }
        
        /* Background decorative elements */
        body::before {
            content: '';
            position: absolute;
            width: 300px;
            height: 300px;
            background: radial-gradient(circle, rgba(74, 144, 226, 0.1) 0%, transparent 70%);
            top: -100px;
            right: -100px;
            border-radius: 50%;
        }
        
        body::after {
            content: '';
            position: absolute;
            width: 400px;
            height: 400px;
            background: radial-gradient(circle, rgba(93, 173, 226, 0.08) 0%, transparent 70%);
            bottom: -150px;
            left: -150px;
            border-radius: 50%;
        }
        
        /* Main Container */
        .auth-container {
            width: 100%;
            max-width: 480px;
            position: relative;
            z-index: 1;
            animation: slideUp 0.6s cubic-bezier(0.68, -0.55, 0.265, 1.55);
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
        
        /* Card */
        .auth-card {
            background: rgba(255, 255, 255, 0.98);
            backdrop-filter: blur(10px);
            border-radius: 28px;
            box-shadow: 0 20px 40px rgba(0, 0, 0, 0.08), 0 5px 12px rgba(0, 0, 0, 0.05);
            overflow: hidden;
            transition: transform 0.3s ease, box-shadow 0.3s ease;
        }
        
        .auth-card:hover {
            transform: translateY(-5px);
            box-shadow: 0 30px 50px rgba(0, 0, 0, 0.12);
        }
        
        /* Header Section */
        .auth-header {
            background: linear-gradient(135deg, #4a90e2 0%, #5dade2 100%);
            padding: 35px 30px;
            text-align: center;
            position: relative;
            overflow: hidden;
        }
        
        .auth-header::before {
            content: '';
            position: absolute;
            top: -50%;
            right: -50%;
            width: 200%;
            height: 200%;
            background: radial-gradient(circle, rgba(255,255,255,0.1) 0%, transparent 70%);
            animation: shimmer 8s infinite;
        }
        
        @keyframes shimmer {
            0% { transform: translate(0, 0); }
            50% { transform: translate(10%, 10%); }
            100% { transform: translate(0, 0); }
        }
        
        .logo-icon {
            width: 70px;
            height: 70px;
            background: white;
            border-radius: 20px;
            display: flex;
            align-items: center;
            justify-content: center;
            margin: 0 auto 20px;
            box-shadow: 0 10px 20px rgba(0, 0, 0, 0.1);
            animation: float 3s ease-in-out infinite;
        }
        
        @keyframes float {
            0%, 100% { transform: translateY(0); }
            50% { transform: translateY(-5px); }
        }
        
        .logo-icon i {
            font-size: 2.5rem;
            color: #4a90e2;
        }
        
        .auth-header h1 {
            color: white;
            font-size: 32px;
            margin-bottom: 8px;
            font-family: 'Orbitron', monospace;
            letter-spacing: 1px;
        }
        
        .auth-header p {
            color: rgba(255, 255, 255, 0.9);
            font-size: 14px;
            font-weight: 400;
        }
        
        /* Tabs */
        .auth-tabs {
            display: flex;
            padding: 0 30px;
            background: white;
            border-bottom: 1px solid #e8ecf0;
        }
        
        .tab-btn {
            flex: 1;
            padding: 18px 20px;
            background: none;
            border: none;
            cursor: pointer;
            font-size: 16px;
            font-weight: 600;
            transition: all 0.3s ease;
            color: #7f8c8d;
            position: relative;
        }
        
        .tab-btn i {
            margin-right: 8px;
            font-size: 16px;
        }
        
        .tab-btn.active {
            color: #4a90e2;
        }
        
        .tab-btn.active::after {
            content: '';
            position: absolute;
            bottom: -1px;
            left: 0;
            right: 0;
            height: 3px;
            background: linear-gradient(90deg, #4a90e2, #5dade2);
            border-radius: 3px 3px 0 0;
            animation: slideIn 0.3s ease;
        }
        
        @keyframes slideIn {
            from { transform: scaleX(0); }
            to { transform: scaleX(1); }
        }
        
        .tab-btn:hover {
            color: #4a90e2;
            background: rgba(74, 144, 226, 0.05);
        }
        
        /* Forms */
        .auth-form {
            padding: 35px 30px;
            display: none;
            background: white;
        }
        
        .auth-form.active {
            display: block;
            animation: fadeIn 0.4s ease;
        }
        
        @keyframes fadeIn {
            from {
                opacity: 0;
                transform: translateX(10px);
            }
            to {
                opacity: 1;
                transform: translateX(0);
            }
        }
        
        /* Form Groups */
        .form-group {
            margin-bottom: 24px;
            position: relative;
        }
        
        .form-group label {
            display: block;
            margin-bottom: 8px;
            color: #2c3e50;
            font-weight: 500;
            font-size: 14px;
        }
        
        .form-group label i {
            margin-right: 8px;
            color: #4a90e2;
            width: 18px;
        }
        
        .input-wrapper {
            position: relative;
        }
        
        .input-wrapper i.input-icon {
            position: absolute;
            left: 15px;
            top: 50%;
            transform: translateY(-50%);
            color: #bdc3c7;
            font-size: 16px;
            transition: color 0.3s ease;
            pointer-events: none;
        }
        
        .form-group input {
            width: 100%;
            padding: 12px 15px 12px 45px;
            border: 2px solid #e8ecf0;
            border-radius: 12px;
            font-size: 14px;
            transition: all 0.3s ease;
            background: #f8fafc;
            color: #2c3e50;
            font-weight: 500;
        }
        
        .form-group input:focus {
            outline: none;
            border-color: #4a90e2;
            background: white;
            box-shadow: 0 0 0 4px rgba(74, 144, 226, 0.1);
        }
        
        .form-group input:focus + .input-icon i,
        .form-group input:focus ~ .input-icon i {
            color: #4a90e2;
        }
        
        /* Password Toggle */
        .password-toggle {
            position: absolute;
            right: 15px;
            top: 50%;
            transform: translateY(-50%);
            cursor: pointer;
            color: #bdc3c7;
            transition: color 0.3s ease;
            z-index: 2;
        }
        
        .password-toggle:hover {
            color: #4a90e2;
        }
        
        /* Submit Button */
        .btn-submit {
            width: 100%;
            padding: 14px;
            background: linear-gradient(135deg, #4a90e2 0%, #5dade2 100%);
            color: white;
            border: none;
            border-radius: 12px;
            font-size: 16px;
            font-weight: 600;
            cursor: pointer;
            transition: all 0.3s ease;
            display: flex;
            align-items: center;
            justify-content: center;
            gap: 10px;
            margin-top: 10px;
            position: relative;
            overflow: hidden;
        }
        
        .btn-submit::before {
            content: '';
            position: absolute;
            top: 50%;
            left: 50%;
            width: 0;
            height: 0;
            border-radius: 50%;
            background: rgba(255, 255, 255, 0.3);
            transform: translate(-50%, -50%);
            transition: width 0.6s, height 0.6s;
        }
        
        .btn-submit:hover::before {
            width: 300px;
            height: 300px;
        }
        
        .btn-submit:hover {
            transform: translateY(-2px);
            box-shadow: 0 10px 20px rgba(74, 144, 226, 0.3);
        }
        
        .btn-submit:active {
            transform: translateY(0);
        }
        
        /* Alerts */
        .alert {
            padding: 14px 18px;
            border-radius: 12px;
            margin: 0 30px 20px 30px;
            font-size: 13px;
            display: flex;
            align-items: center;
            gap: 12px;
            animation: slideDown 0.4s ease;
        }
        
        @keyframes slideDown {
            from {
                opacity: 0;
                transform: translateY(-10px);
            }
            to {
                opacity: 1;
                transform: translateY(0);
            }
        }
        
        .alert i {
            font-size: 18px;
        }
        
        .alert-error {
            background: #fee2e2;
            color: #e74c3c;
            border-left: 4px solid #e74c3c;
        }
        
        .alert-success {
            background: #e0f2e9;
            color: #27ae60;
            border-left: 4px solid #27ae60;
        }
        
        /* Footer Links */
        .auth-footer {
            text-align: center;
            padding: 20px 30px;
            background: #f8fafc;
            border-top: 1px solid #e8ecf0;
        }
        
        .auth-footer p {
            color: #7f8c8d;
            font-size: 12px;
        }
        
        /* Responsive */
        @media (max-width: 480px) {
            .auth-card {
                border-radius: 20px;
            }
            
            .auth-header {
                padding: 25px 20px;
            }
            
            .logo-icon {
                width: 55px;
                height: 55px;
            }
            
            .logo-icon i {
                font-size: 2rem;
            }
            
            .auth-header h1 {
                font-size: 26px;
            }
            
            .auth-tabs {
                padding: 0 20px;
            }
            
            .tab-btn {
                padding: 15px;
                font-size: 14px;
            }
            
            .auth-form {
                padding: 25px 20px;
            }
            
            .form-group input {
                padding: 11px 12px 11px 40px;
            }
            
            .alert {
                margin: 0 20px 15px 20px;
            }
        }
        
        /* Loading state */
        .btn-submit.loading {
            pointer-events: none;
            opacity: 0.7;
        }
        
        .btn-submit.loading i {
            animation: spin 1s linear infinite;
        }
        
        @keyframes spin {
            from { transform: rotate(0deg); }
            to { transform: rotate(360deg); }
        }
    </style>
</head>
<body>
    <div class="auth-container">
        <div class="auth-card">
            <div class="auth-header">
                <div class="logo-icon">
                    <i class="fas fa-chart-line"></i>
                </div>
                <h1>MoneyMap</h1>
                <p>visualize your financial journey</p>
            </div>
            
            <div class="auth-tabs">
                <button class="tab-btn active" onclick="switchTab('login')">
                    <i class="fas fa-sign-in-alt"></i> Login
                </button>
                <button class="tab-btn" onclick="switchTab('register')">
                    <i class="fas fa-user-plus"></i> Register
                </button>
            </div>
            
            <?php if ($error): ?>
                <div class="alert alert-error">
                    <i class="fas fa-exclamation-circle"></i>
                    <span><?php echo $error; ?></span>
                </div>
            <?php endif; ?>
            
            <?php if ($success): ?>
                <div class="alert alert-success">
                    <i class="fas fa-check-circle"></i>
                    <span><?php echo $success; ?></span>
                </div>
            <?php endif; ?>
            
            <!-- Login Form -->
            <div id="loginForm" class="auth-form active">
                <form method="POST" action="" id="loginFormElement">
                    <input type="hidden" name="action" value="login">
                    
                    <div class="form-group">
                        <label><i class="fas fa-user"></i> Username or Email</label>
                        <div class="input-wrapper">
                            <i class="fas fa-envelope input-icon"></i>
                            <input type="text" name="username" id="loginUsername" required placeholder="Enter your username or email">
                        </div>
                    </div>
                    
                    <div class="form-group">
                        <label><i class="fas fa-lock"></i> Password</label>
                        <div class="input-wrapper">
                            <i class="fas fa-key input-icon"></i>
                            <input type="password" name="password" id="loginPassword" required placeholder="Enter your password">
                            <i class="fas fa-eye password-toggle" onclick="togglePassword('loginPassword', this)"></i>
                        </div>
                    </div>
                    
                    <button type="submit" class="btn-submit" id="loginBtn">
                        <i class="fas fa-arrow-right"></i>
                        <span>Login to Dashboard</span>
                    </button>
                </form>
            </div>
            
            <!-- Register Form -->
            <div id="registerForm" class="auth-form">
                <form method="POST" action="" id="registerFormElement">
                    <input type="hidden" name="action" value="register">
                    
                    <div class="form-group">
                        <label><i class="fas fa-user"></i> Username</label>
                        <div class="input-wrapper">
                            <i class="fas fa-user input-icon"></i>
                            <input type="text" name="reg_username" id="regUsername" required placeholder="Choose a username">
                        </div>
                    </div>
                    
                    <div class="form-group">
                        <label><i class="fas fa-envelope"></i> Email Address</label>
                        <div class="input-wrapper">
                            <i class="fas fa-envelope input-icon"></i>
                            <input type="email" name="reg_email" id="regEmail" required placeholder="Enter your email">
                        </div>
                    </div>
                    
                    <div class="form-group">
                        <label><i class="fas fa-lock"></i> Password</label>
                        <div class="input-wrapper">
                            <i class="fas fa-key input-icon"></i>
                            <input type="password" name="reg_password" id="regPassword" required placeholder="Min 6 characters">
                            <i class="fas fa-eye password-toggle" onclick="togglePassword('regPassword', this)"></i>
                        </div>
                    </div>
                    
                    <button type="submit" class="btn-submit" id="registerBtn">
                        <i class="fas fa-user-plus"></i>
                        <span>Create Account</span>
                    </button>
                </form>
            </div>
            
            <div class="auth-footer">
                <p><i class="fas fa-shield-alt"></i> Secure & Free • Track all your expenses</p>
            </div>
        </div>
    </div>
    
    <script>
        // Tab switching
        function switchTab(tab) {
            const loginForm = document.getElementById('loginForm');
            const registerForm = document.getElementById('registerForm');
            const tabs = document.querySelectorAll('.tab-btn');
            
            if (tab === 'login') {
                loginForm.classList.add('active');
                registerForm.classList.remove('active');
                tabs[0].classList.add('active');
                tabs[1].classList.remove('active');
            } else {
                loginForm.classList.remove('active');
                registerForm.classList.add('active');
                tabs[0].classList.remove('active');
                tabs[1].classList.add('active');
            }
        }
        
        // Toggle password visibility
        function togglePassword(inputId, iconElement) {
            const input = document.getElementById(inputId);
            if (input.type === 'password') {
                input.type = 'text';
                iconElement.classList.remove('fa-eye');
                iconElement.classList.add('fa-eye-slash');
            } else {
                input.type = 'password';
                iconElement.classList.remove('fa-eye-slash');
                iconElement.classList.add('fa-eye');
            }
        }
        
        // Form submission loading state
        document.getElementById('loginFormElement')?.addEventListener('submit', function() {
            const btn = document.getElementById('loginBtn');
            btn.classList.add('loading');
            btn.innerHTML = '<i class="fas fa-spinner"></i> Logging in...';
        });
        
        document.getElementById('registerFormElement')?.addEventListener('submit', function() {
            const btn = document.getElementById('registerBtn');
            btn.classList.add('loading');
            btn.innerHTML = '<i class="fas fa-spinner"></i> Creating account...';
        });
        
        // Auto-hide alerts after 5 seconds
        setTimeout(() => {
            const alerts = document.querySelectorAll('.alert');
            alerts.forEach(alert => {
                alert.style.opacity = '0';
                setTimeout(() => {
                    alert.style.display = 'none';
                }, 500);
            });
        }, 5000);
        
        // Enter key navigation
        document.addEventListener('keypress', function(e) {
            if (e.key === 'Enter') {
                const activeForm = document.querySelector('.auth-form.active');
                if (activeForm) {
                    const submitBtn = activeForm.querySelector('.btn-submit');
                    if (submitBtn) {
                        submitBtn.click();
                    }
                }
            }
        });
    </script>
</body>
</html>