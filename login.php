<?php
    error_reporting(E_ALL);
ini_set('display_errors', 1);
// --- SESSION CLEANUP (ensures no old session persists) ---
session_start();
session_unset();
session_destroy();
session_start();
require_once '../config/database.php';
require_once '../includes/functions.php';

$error = '';

if ($_POST) {
    $username = sanitizeInput($_POST['username']);
    $password = $_POST['password'];
    
    if (login($username, $password)) {
        header('Location: dashboard.php?refresh=' . time());
        exit();
    } else {
        $error = 'Invalid username or password.';
    }
}

// If already logged in, redirect to dashboard
if (isLoggedIn()) {
    header('Location: dashboard.php?refresh=' . time());
    exit();
}
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Admin Login - Timiza Youth Initiative</title>
    <link rel="icon" href="../assets/images/favicon.ico" type="image/x-icon">
    <!-- Fonts -->
    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
    <link href="https://fonts.googleapis.com/css2?family=Poppins:wght@400;500;700&display=swap" rel="stylesheet">
    <!-- Tailwind CSS -->
    <script src="https://cdn.tailwindcss.com"></script>
    <script>
        tailwind.config = {
            theme: {
                extend: {
                    colors: {
                        emerald: {
                            50: '#ecfdf5',
                            100: '#d1fae5',
                            200: '#a7f3d0',
                            300: '#6ee7b7',
                            400: '#34d399',
                            500: '#10b981',
                            600: '#059669',
                            700: '#047857',
                            800: '#065f46',
                            900: '#064e3b'
                        }
                    },
                    fontFamily: {
                        'poppins': ['Poppins', 'sans-serif']
                    }
                }
            }
        }
    </script>
    <!-- Font Awesome -->
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">
    <style>
        body {
            min-height: 100vh;
            font-family: 'Poppins', sans-serif;
            margin: 0;
            background: linear-gradient(120deg, #bbf7d0 0%, #22c55e 100%);
            display: flex;
            align-items: center;
            justify-content: center;
        }
        .admin-card {
            background: #f0fdf4;
            border-radius: 26px;
            padding: 2.8rem 2.2rem;
            box-shadow: 0 12px 48px -20px #05966955;
            border: 2px solid #bbf7d0;
            width: 100%;
            max-width: 410px;
        }
        .admin-title {
            color: #065f46;
            font-weight: 800;
            letter-spacing: .01em;
        }
        .logo-img {
            height: 60px; width: 60px; object-fit: contain; background: #d1fae5; border-radius: 50%; box-shadow: 0 2px 8px #22c55e13; margin-bottom: .3em;
        }
        .error-box {
            background: #fee2e2;
            color: #991b1b;
            border: 1.8px solid #fecaca;
            border-radius: 14px;
            padding: 1em 1.2em;
            margin-bottom: 1.4em;
            font-weight: 600;
            font-size: .97em;
        }
        label {
            font-weight: 600;
            color: #047857;
            margin-bottom: .41em;
            display: flex;
            align-items: center;
            gap: 0.35em;
        }
        input[type="text"], input[type="password"] {
            border-radius: 12px;
            border: 2px solid #bbf7d0;
            padding: 13px 18px;
            background: white;
            font-size: 1rem;
            width: 100%;
            transition: border .22s;
        }
        input:focus {
            border-color: #10b981;
            box-shadow: 0 0 0 2px #d1fae5;
            outline: none;
        }
        .login-btn {
            background: linear-gradient(90deg, #34d399, #059669);
            color: #fff; font-weight: bold; border: none; border-radius: 999px;
            padding: 1.1em 2em; font-size: 1.1em; margin-top: .7em;
            box-shadow: 0 2px 20px -8px #10b98161;
            cursor: pointer;
            transition: background .16s, transform .14s;
        }
        .login-btn:hover, .login-btn:active {
            background: linear-gradient(90deg, #059669, #34d399);
            transform: scale(.98);
        }
        .back-link {
            color: #047857;
            font-weight: 600;
            border-radius: 6px;
            padding: .15em .7em;
            transition: color .2s;
        }
        .back-link:hover {
            color: #059669;
            background: #d1fae5;
            text-decoration: underline;
        }
        @media (max-width: 540px) {
            .admin-card { padding: 1.5rem .5rem; }
        }
    </style>
</head>
<body>
    <div class="admin-card">
        <div class="text-center mb-7">
            <img src="../assets/images/logo.png" alt="TYI Logo" class="logo-img mx-auto mb-3">
            <h1 class="text-2xl admin-title mb-1">Admin Login</h1>
            <p class="text-emerald-700 mb-2 font-semibold">Timiza Youth Initiative</p>
        </div>
        
        <?php if ($error): ?>
        <div class="error-box">
            <?php echo htmlspecialchars($error); ?>
        </div>
        <?php endif; ?>
        
        <form method="POST" class="space-y-6">
            <div>
                <label for="username">
                    <i class="fas fa-user"></i> Username
                </label>
                <input type="text" id="username" name="username" required>
            </div>
            <div>
                <label for="password">
                    <i class="fas fa-lock"></i> Password
                </label>
                <input type="password" id="password" name="password" required>
            </div>
            <button type="submit" class="login-btn w-full">
                <i class="fas fa-sign-in-alt mr-2"></i>Login
            </button>
        </form>
        
        <div class="mt-6 text-center">
            <a href="../index.php" class="back-link text-sm">
                <i class="fas fa-arrow-left mr-1"></i>Back to Website
            </a>
        </div>
    </div>
</body>
</html>
