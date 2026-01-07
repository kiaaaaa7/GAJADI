<?php
require_once '../includes/config.php';
require_once '../includes/functions.php';
require_once '../app/models/User.php';

Middleware::preventIfLoggedIn();

$userModel = new User();
$error = '';

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $email = Helper::sanitize($_POST['email']);
    $password = $_POST['password'];
    
    if (empty($email) || empty($password)) {
        $error = 'Email dan password harus diisi';
    } else {
        $user = $userModel->login($email, $password);
        
        if ($user) {
            // Set session
            $_SESSION['user_id'] = $user['id'];
            $_SESSION['user_name'] = $user['nama'];
            $_SESSION['user_email'] = $user['email'];
            $_SESSION['role'] = $user['role'];
            
            // Redirect based on role
            if ($user['role'] === 'admin') {
                Helper::redirect('/admin/', 'success', 'Selamat datang, Admin!');
            } else {
                Helper::redirect('/', 'success', 'Login berhasil!');
            }
        } else {
            $error = 'Email atau password salah';
        }
    }
}

$pageTitle = "Login - " . SITE_NAME;
?>
<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title><?= $pageTitle ?></title>
    <link rel="stylesheet" href="<?= BASE_URL ?>/assets/css/main.css">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">
    <style>
        .auth-container {
            min-height: 100vh;
            display: flex;
            align-items: center;
            justify-content: center;
            background: linear-gradient(135deg, #f5f7fa 0%, #e4edf5 100%);
            padding: 40px 20px;
        }
        
        .auth-card {
            background: white;
            border-radius: 20px;
            box-shadow: 0 10px 40px rgba(0, 0, 0, 0.08);
            padding: 50px;
            width: 100%;
            max-width: 450px;
            position: relative;
            overflow: hidden;
        }
        
        .auth-card::before {
            content: '';
            position: absolute;
            top: 0;
            left: 0;
            right: 0;
            height: 4px;
            background: linear-gradient(90deg, var(--primary), var(--secondary));
        }
        
        .auth-logo {
            text-align: center;
            margin-bottom: 40px;
        }
        
        .auth-logo a {
            display: inline-flex;
            align-items: center;
            gap: 12px;
            font-size: 28px;
            font-weight: 700;
            color: var(--dark);
            text-decoration: none;
        }
        
        .auth-logo i {
            color: var(--primary);
            font-size: 32px;
        }
        
        .auth-title {
            font-size: 28px;
            font-weight: 600;
            color: var(--dark);
            margin-bottom: 10px;
            text-align: center;
        }
        
        .auth-subtitle {
            color: var(--gray);
            text-align: center;
            margin-bottom: 40px;
        }
        
        .form-group {
            margin-bottom: 24px;
        }
        
        .form-label {
            display: block;
            margin-bottom: 8px;
            font-weight: 500;
            color: var(--dark);
        }
        
        .form-control {
            width: 100%;
            padding: 15px 20px;
            border: 2px solid #e1e5eb;
            border-radius: 12px;
            font-size: 16px;
            transition: all 0.3s;
            background: #f8fafc;
        }
        
        .form-control:focus {
            outline: none;
            border-color: var(--primary);
            background: white;
            box-shadow: 0 0 0 4px rgba(139, 92, 246, 0.1);
        }
        
        .form-footer {
            margin-top: 30px;
            text-align: center;
        }
        
        .form-link {
            color: var(--primary);
            text-decoration: none;
            font-weight: 500;
            transition: color 0.3s;
        }
        
        .form-link:hover {
            color: var(--primary-dark);
            text-decoration: underline;
        }
        
        .alert {
            padding: 15px 20px;
            border-radius: 12px;
            margin-bottom: 30px;
            display: flex;
            align-items: center;
            gap: 12px;
            background: #fee;
            color: #c33;
            border-left: 4px solid #c33;
        }
        
        .alert i {
            font-size: 20px;
        }
        
        @media (max-width: 768px) {
            .auth-card {
                padding: 40px 30px;
            }
        }
    </style>
</head>
<body>
    <div class="auth-container">
        <div class="auth-card">
            <div class="auth-logo">
                <a href="<?= BASE_URL ?>/">
                    <i class="fas fa-mug-hot"></i>
                    <span>House Cafe</span>
                </a>
            </div>
            
            <h1 class="auth-title">Masuk ke Akun</h1>
            <p class="auth-subtitle">Selamat datang kembali di House Cafe</p>
            
            <?php if ($error): ?>
                <div class="alert">
                    <i class="fas fa-exclamation-circle"></i>
                    <span><?= $error ?></span>
                </div>
            <?php endif; ?>
            
            <form method="POST" action="">
                <div class="form-group">
                    <label class="form-label" for="email">Email</label>
                    <input type="email" id="email" name="email" class="form-control" 
                           placeholder="nama@email.com" required 
                           value="<?= $_POST['email'] ?? '' ?>">
                </div>
                
                <div class="form-group">
                    <label class="form-label" for="password">Password</label>
                    <input type="password" id="password" name="password" 
                           class="form-control" placeholder="••••••••" required>
                </div>
                
                <div class="form-group">
                    <button type="submit" class="btn btn-primary btn-block btn-large">
                        <i class="fas fa-sign-in-alt"></i> Masuk
                    </button>
                </div>
                
                <div class="form-footer">
                    <p>Belum punya akun? 
                        <a href="<?= BASE_URL ?>/register.php" class="form-link">Daftar di sini</a>
                    </p>
                </div>
            </form>
        </div>
    </div>
    
    <script>
        // Add floating animation to form
        const form = document.querySelector('form');
        form.addEventListener('submit', function(e) {
            const submitBtn = this.querySelector('button[type="submit"]');
            submitBtn.innerHTML = '<i class="fas fa-spinner fa-spin"></i> Memproses...';
            submitBtn.disabled = true;
        });
    </script>
</body>
</html>