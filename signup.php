<?php
session_start();
require_once 'includes/functions.php';

// Redirect if already logged in
if (isset($_SESSION['user_id'])) {
    header('Location: index.php');
    exit;
}

$error = '';
$success = '';

// Handle signup form submission
if ($_SERVER['REQUEST_METHOD'] == 'POST') {
    $name = $_POST['name'] ?? '';
    $email = $_POST['email'] ?? '';
    $password = $_POST['password'] ?? '';
    $confirm_password = $_POST['confirm_password'] ?? '';
    $phone = $_POST['phone'] ?? '';
    $address = $_POST['address'] ?? '';
    
    if (empty($name) || empty($email) || empty($password) || empty($confirm_password)) {
        $error = 'Semua field wajib diisi!';
    } elseif ($password !== $confirm_password) {
        $error = 'Password dan konfirmasi password tidak cocok!';
    } elseif (strlen($password) < 6) {
        $error = 'Password minimal 6 karakter!';
    } else {
        $result = registerUser($name, $email, $password, $phone, $address);
        if ($result === 'success') {
            $success = 'Registrasi berhasil! Silakan login.';
        } else {
            $error = $result;
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
    <title>Daftar - Roti'O</title>
    <link href="images/logo-rotio.png" rel="icon">
    <link href="https://fonts.googleapis.com/css?family=Kaushan+Script%7CLora:400,700" rel="stylesheet">
    <link rel="stylesheet" href="plugins/font-awesome/css/font-awesome.min.css">
    <link rel="stylesheet" href="plugins/bakery-icon/style.css">
    <link rel="stylesheet" href="plugins/bootstrap/dist/css/bootstrap.min.css">
    <link rel="stylesheet" href="css/style.css">
    <style>
        .signup-container {
            min-height: 100vh;
            background: linear-gradient(135deg, #F2CB05 0%, #D97E4A 100%);
            display: flex;
            align-items: center;
            justify-content: center;
            padding: 20px;
        }
        .signup-card {
            background: white;
            border-radius: 15px;
            box-shadow: 0 15px 35px rgba(0,0,0,0.1);
            overflow: hidden;
            max-width: 500px;
            width: 100%;
        }
        .signup-header {
            background: linear-gradient(135deg, #F2E205 0%, #F2CB05 100%);
            padding: 30px;
            text-align: center;
            border-bottom: 1px solid #D97E4A;
        }
        .signup-header h2 {
            margin: 0;
            color: #402401;
            font-family: 'Kaushan Script', cursive;
        }
        .signup-body {
            padding: 30px;
        }
        .form-group {
            margin-bottom: 20px;
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
        .form-control:focus {
            border-color: #F2CB05;
            box-shadow: 0 0 0 0.2rem rgba(242, 203, 5, 0.25);
        }
        .btn-signup {
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
        .btn-signup:hover {
            transform: translateY(-2px);
            color: #402401;
        }
        .signup-footer {
            text-align: center;
            padding: 20px 30px;
            background: linear-gradient(135deg, #F2E205 0%, #F2CB05 100%);
            border-top: 1px solid #D97E4A;
        }
        .signup-footer a {
            color: #D97E4A;
            text-decoration: none;
        }
        .signup-footer a:hover {
            text-decoration: underline;
            color: #D97E4A;
        }
        .signup-footer a.back-home {
            color: #402401 !important;
            font-weight: 600;
        }
        .signup-footer a.back-home:hover {
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
    </style>
</head>
<body>
    <div class="signup-container">
        <div class="signup-card">
            <div class="signup-header">
                <h2>Buat Akun</h2>
                <p class="text-muted">Bergabung dengan komunitas kami hari ini</p>
            </div>
            <div class="signup-body">
                <?php if ($error): ?>
                    <div class="alert alert-danger">
                        <?php echo htmlspecialchars($error); ?>
                    </div>
                <?php endif; ?>
                
                <?php if ($success): ?>
                    <div class="alert alert-success">
                        <?php echo htmlspecialchars($success); ?>
                    </div>
                <?php endif; ?>
                
                <form method="POST" action="">
                    <div class="form-group">
                        <label for="name">Nama Lengkap</label>
                        <input type="text" class="form-control" id="name" name="name" 
                               value="<?php echo htmlspecialchars($_POST['name'] ?? ''); ?>" 
                               required>
                    </div>
                    
                    <div class="form-group">
                        <label for="email">Alamat Email</label>
                        <input type="email" class="form-control" id="email" name="email" 
                               value="<?php echo htmlspecialchars($_POST['email'] ?? ''); ?>" 
                               required>
                    </div>
                    
                    <div class="form-group">
                        <label for="phone">Nomor Telepon</label>
                        <input type="tel" class="form-control" id="phone" name="phone" 
                               value="<?php echo htmlspecialchars($_POST['phone'] ?? ''); ?>">
                    </div>
                    
                    <div class="form-group">
                        <label for="address">Alamat</label>
                        <textarea class="form-control" id="address" name="address" rows="3"><?php echo htmlspecialchars($_POST['address'] ?? ''); ?></textarea>
                    </div>
                    
                    <div class="form-group">
                        <label for="password">Kata Sandi</label>
                        <input type="password" class="form-control" id="password" name="password" required>
                    </div>
                    
                    <div class="form-group">
                        <label for="confirm_password">Konfirmasi Kata Sandi</label>
                        <input type="password" class="form-control" id="confirm_password" name="confirm_password" required>
                    </div>
                    
                    <div class="form-group">
                        <button type="submit" class="btn btn-signup">Buat Akun</button>
                    </div>
                </form>
            </div>
            <div class="signup-footer">
                <p>Sudah punya akun? <a href="login.php">Masuk di sini</a></p>
                <p><a href="index.php" class="back-home">← Kembali ke Beranda</a></p>
            </div>
        </div>
    </div>

    <script src="plugins/jquery/dist/jquery.min.js"></script>
    <script src="plugins/bootstrap/dist/js/bootstrap.min.js"></script>
</body>
</html> 