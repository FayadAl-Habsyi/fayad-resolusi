<?php
/**
 * Login Page
 */
require_once '../config/config.php';

// Redirect ke dashboard jika sudah login
if (is_logged_in()) {
    redirect(base_url('dashboard.php'));
}

// Proses login
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $username = sanitize($_POST['username'] ?? '');
    $password = $_POST['password'] ?? '';
    $remember = isset($_POST['remember']);
    
    // Validasi input
    $errors = [];
    
    if (empty($username)) {
        $errors['username'] = 'Username tidak boleh kosong';
    }
    
    if (empty($password)) {
        $errors['password'] = 'Password tidak boleh kosong';
    }
    
    // Jika tidak ada error, proses login
    if (empty($errors)) {
        // Cek user di database
        $username = escape_string($username);
        $user = get_row("SELECT * FROM users WHERE username = '$username' AND status = 1");
        
        if ($user && password_verify_custom($password, $user['password'])) {
            // Set session
            $_SESSION['user_id'] = $user['id'];
            $_SESSION['user_name'] = $user['nama_lengkap'];
            $_SESSION['user_username'] = $user['username'];
            $_SESSION['user_role'] = $user['role'];
            
            // Set cookie jika remember me dicentang
            if ($remember) {
                $token = generate_random_string(32);
                $user_id = $user['id'];
                $expires = time() + (86400 * 30); // 30 hari
                
                // Simpan token di database
                $token_hash = password_hash_custom($token);
                query("INSERT INTO user_tokens (user_id, token, expires) VALUES ('$user_id', '$token_hash', FROM_UNIXTIME($expires))");
                
                // Set cookie
                setcookie('remember_token', $token, $expires, '/', '', false, true);
                setcookie('remember_user', $user_id, $expires, '/', '', false, true);
            }
            
            // Set flash message
            set_flash_message('success', 'Login berhasil. Selamat datang, ' . $user['nama_lengkap'] . '!');
            
            // Redirect ke dashboard
            redirect(base_url('dashboard.php'));
        } else {
            $errors['login'] = 'Username atau password salah';
        }
    }
}

// Cek apakah ada flash message
$flash_message = get_flash_message();

// Ambil pengaturan toko
$settings = get_settings();
?>
<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Login - <?= $settings['nama_toko'] ?? 'CashFlow POS' ?></title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
    <link href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css" rel="stylesheet">
    <link href="https://fonts.googleapis.com/css2?family=Poppins:wght@300;400;500;600;700&display=swap" rel="stylesheet">
    <style>
        :root {
            --primary-gradient: linear-gradient(135deg, #667eea 0%, #764ba2 100%);
            --secondary-gradient: linear-gradient(135deg, #f093fb 0%, #f5576c 100%);
        }
        
        * {
            margin: 0;
            padding: 0;
            box-sizing: border-box;
        }
        
        body {
            font-family: 'Poppins', sans-serif;
            background: linear-gradient(135deg, #667eea 0%, #764ba2 50%, #f093fb 100%);
            min-height: 100vh;
            display: flex;
            align-items: center;
            justify-content: center;
            padding: 20px;
        }
        
        .login-container {
            background: rgba(255, 255, 255, 0.95);
            backdrop-filter: blur(20px);
            border-radius: 20px;
            box-shadow: 0 15px 35px rgba(0, 0, 0, 0.2);
            overflow: hidden;
            width: 100%;
            max-width: 900px;
            display: flex;
            flex-direction: row-reverse;
        }
        
        .login-image {
            flex: 1;
            background: var(--primary-gradient);
            padding: 40px;
            display: flex;
            flex-direction: column;
            justify-content: center;
            align-items: center;
            color: white;
            text-align: center;
        }
        
        .login-image img {
            max-width: 80%;
            margin-bottom: 30px;
        }
        
        .login-image h2 {
            font-weight: 700;
            margin-bottom: 15px;
        }
        
        .login-image p {
            opacity: 0.9;
            font-size: 0.9rem;
            line-height: 1.6;
        }
        
        .login-form {
            flex: 1;
            padding: 40px;
        }
        
        .login-header {
            text-align: center;
            margin-bottom: 30px;
        }
        
        .login-header h3 {
            font-weight: 700;
            color: #333;
            margin-bottom: 10px;
        }
        
        .login-header p {
            color: #777;
            font-size: 0.9rem;
        }
        
        .form-floating {
            margin-bottom: 20px;
        }
        
        .form-control {
            border-radius: 10px;
            padding: 12px 15px;
            height: auto;
            font-size: 1rem;
            border: 1px solid #ddd;
        }
        
        .form-control:focus {
            border-color: #667eea;
            box-shadow: 0 0 0 0.25rem rgba(102, 126, 234, 0.25);
        }
        
        .form-floating > label {
            padding: 12px 15px;
        }
        
        .btn-login {
            background: var(--primary-gradient);
            border: none;
            border-radius: 10px;
            padding: 12px 20px;
            font-weight: 600;
            width: 100%;
            margin-top: 10px;
            transition: all 0.3s ease;
        }
        
        .btn-login:hover {
            transform: translateY(-3px);
            box-shadow: 0 10px 20px rgba(102, 126, 234, 0.3);
        }
        
        .login-footer {
            text-align: center;
            margin-top: 30px;
            color: #777;
            font-size: 0.9rem;
        }
        
        .login-footer a {
            color: #667eea;
            text-decoration: none;
            font-weight: 600;
        }
        
        .login-footer a:hover {
            text-decoration: underline;
        }
        
        .alert {
            border-radius: 10px;
            padding: 15px;
            margin-bottom: 20px;
        }
        
        @media (max-width: 768px) {
            .login-container {
                flex-direction: column;
                max-width: 500px;
            }
            
            .login-image {
                padding: 30px;
            }
            
            .login-form {
                padding: 30px;
            }
        }
    </style>
</head>
<body>
    <div class="login-container">
        <div class="login-image">
            <img src="<?= asset_url('img/pos-illustration.svg') ?>" alt="POS System" onerror="this.src='https://via.placeholder.com/400x300?text=POS+System'">
            <h2><?= $settings['nama_toko'] ?? 'CashFlow POS' ?></h2>
            <p>Sistem kasir modern untuk bisnis Anda. Kelola penjualan, stok, dan laporan dengan mudah.</p>
        </div>
        
        <div class="login-form">
            <div class="login-header">
                <h3>Login</h3>
                <p>Masukkan username dan password Anda</p>
            </div>
            
            <?php if ($flash_message): ?>
            <div class="alert alert-<?= $flash_message['type'] ?>">
                <?= $flash_message['message'] ?>
            </div>
            <?php endif; ?>
            
            <?php if (isset($errors['login'])): ?>
            <div class="alert alert-danger">
                <?= $errors['login'] ?>
            </div>
            <?php endif; ?>
            
            <form method="post" action="">
                <div class="form-floating">
                    <input type="text" class="form-control <?= isset($errors['username']) ? 'is-invalid' : '' ?>" id="username" name="username" placeholder="Username" value="<?= $_POST['username'] ?? '' ?>">
                    <label for="username">Username</label>
                    <?php if (isset($errors['username'])): ?>
                    <div class="invalid-feedback">
                        <?= $errors['username'] ?>
                    </div>
                    <?php endif; ?>
                </div>
                
                <div class="form-floating">
                    <input type="password" class="form-control <?= isset($errors['password']) ? 'is-invalid' : '' ?>" id="password" name="password" placeholder="Password">
                    <label for="password">Password</label>
                    <?php if (isset($errors['password'])): ?>
                    <div class="invalid-feedback">
                        <?= $errors['password'] ?>
                    </div>
                    <?php endif; ?>
                </div>
                
                <div class="form-check mb-3">
                    <input class="form-check-input" type="checkbox" id="remember" name="remember">
                    <label class="form-check-label" for="remember">Ingat saya</label>
                </div>
                
                <button type="submit" class="btn btn-primary btn-login">
                    <i class="fas fa-sign-in-alt me-2"></i>Login
                </button>
            </form>
            
            <div class="login-footer">
                <p>&copy; <?= date('Y') ?> <?= $settings['nama_toko'] ?? 'CashFlow POS' ?>. All rights reserved.</p>
            </div>
        </div>
    </div>
    
    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script>
</body>
</html>