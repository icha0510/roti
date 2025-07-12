<?php
session_start();
require_once 'includes/functions.php';

// Redirect if already logged in
if (isset($_SESSION['user_id'])) {
    header('Location: index.php');
    exit;
}

$error = '';

// Handle login form submission
if ($_SERVER['REQUEST_METHOD'] == 'POST') {
    $email = $_POST['email'] ?? '';
    $password = $_POST['password'] ?? '';
    
    if (empty($email) || empty($password)) {
        $error = 'Email dan password harus diisi!';
    } else {
        $user = authenticateUser($email, $password);
        if ($user) {
            $_SESSION['user_id'] = $user['id'];
            $_SESSION['user_name'] = $user['name'];
            $_SESSION['user_email'] = $user['email'];
            
            // Redirect to intended page or home
            $redirect = $_GET['redirect'] ?? 'index.php';
            header('Location: ' . $redirect);
            exit;
        } else {
            $error = 'Email atau password salah!';
        }
    }
}
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="utf-8">
    <meta http-equiv="X-UA-Compatible" content="IE=edge">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <link href="images/logo-rotio.png" rel="icon">
    <title>Login - Roti'O</title>
    <link href="https://fonts.googleapis.com/css?family=Kaushan+Script%7CLora:400,700" rel="stylesheet">
    <link rel="stylesheet" href="plugins/font-awesome/css/font-awesome.min.css">
    <link rel="stylesheet" href="plugins/bakery-icon/style.css">
    <link rel="stylesheet" href="plugins/bootstrap/dist/css/bootstrap.min.css">
    <link rel="stylesheet" href="css/style.css">
    <link rel="stylesheet" href="css/header-nav.css">
    <style>
        .login-container {
            min-height: 100vh;
            background: linear-gradient(135deg, #F2CB05 0%, #D97E4A 100%);
            display: flex;
            align-items: center;
            justify-content: center;
            padding: 20px;
        }
        .login-card {
            background: white;
            border-radius: 15px;
            box-shadow: 0 15px 35px rgba(96, 77, 19, 0.48);
            overflow: hidden;
            max-width: 400px;
            width: 100%;
        }
        .login-header {
            background: linear-gradient(135deg, #F2E205 0%, #F2CB05 100%);
            padding: 30px;
            text-align: center;
            border-bottom: 1px solid #D97E4A;
        }
        .login-header h2 {
            margin: 0;
            color: #402401;
            font-family: 'Kaushan Script', cursive;
        }
        .login-body {
            padding: 30px;
        }
        .form-group {
            margin-bottom: 20px;
        }
        .form-control:focus {
            border-color: #F2CB05;
            box-shadow: 0 0 0 0.2rem rgba(242, 203, 5, 0.56);
        }
        .btn-login {
            background: linear-gradient(135deg, #F2CB05 0%, #D97E4A 100%);
            border: none;
            border-radius: 8px;
            padding: 12px;
            font-size: 16px;
            font-weight: 600;
            color: #402401;
            width: 100%;
            transition: transform 0.3s ease;
        }
        .btn-login:hover {
            transform: translateY(-2px);
            color: #402401;
        }
        .login-footer {
            text-align: center;
            padding: 20px 30px;
            background: linear-gradient(135deg, #F2E205 0%, #F2CB05 100%);
            border-top: 1px solid #D97E4A;
        }
        .login-footer a {
            color: #D97E4A;
            text-decoration: none;
        }
        .login-footer a:hover {
            text-decoration: underline;
            color: #D97E4A;
        }
        .login-footer a.back-home {
            color: #402401 !important;
            font-weight: 600;
        }
        .login-footer a.back-home:hover {
            color: #D97E4A !important;
        }
        .alert {
            border-radius: 8px;
            margin-bottom: 20px;
        }
        .alert-danger {
            border-color: #D97E4A;
            background-color: rgba(217, 126, 74, 0.1);
        }
        .form-control {
            border: 2px solid #F2E205;
            border-radius: 8px;
            padding: 12px 15px;
            font-size: 16px;
            transition: all 0.3s ease;
        }
        .form-control:hover {
            border-color: #F2CB05;
        }
    </style>
</head>
<body> 
    <div class="login-container">
        <div class="login-card">
            <div class="login-header">
                <h2>Selamat Datang </h2>
                <p class="text-muted">Masuk ke akun Anda</p>
            </div>
            <div class="login-body">
                <?php if ($error): ?>
                    <div class="alert alert-danger">
                        <?php echo htmlspecialchars($error); ?>
                    </div>
                <?php endif; ?>
                
                <form method="POST" action="">
                    <div class="form-group">
                        <label for="email">Alamat Email</label>
                        <input type="email" class="form-control" id="email" name="email" 
                               value="<?php echo htmlspecialchars($_POST['email'] ?? ''); ?>" 
                               required>
                    </div>
                    
                    <div class="form-group">
                        <label for="password">Kata Sandi</label>
                        <input type="password" class="form-control" id="password" name="password" required>
                    </div>
                    
                    <div class="form-group">
                        <button type="submit" class="btn btn-login">Masuk</button>
                    </div>
                </form>
            </div>
            <div class="login-footer">
                <p>Belum punya akun? <a href="signup.php">Daftar di sini</a></p>
                <p><a href="index.php" class="back-home">← Kembali ke Beranda</a></p>
            </div>
        </div>
    </div>

    <script src="plugins/jquery/dist/jquery.min.js"></script>
    <script src="plugins/bootstrap/dist/js/bootstrap.min.js"></script>
</body>
</html> 