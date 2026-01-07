<?php
require_once '../includes/config.php';
require_once '../includes/functions.php';
require_once '../app/models/User.php';

Middleware::preventIfLoggedIn();

$userModel = new User();
$errors = [];
$success = '';

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $data = [
        'nama' => Helper::sanitize($_POST['nama']),
        'email' => Helper::sanitize($_POST['email']),
        'password' => $_POST['password'],
        'confirm_password' => $_POST['confirm_password'],
        'phone' => Helper::sanitize($_POST['phone'])
    ];
    
    // Validation
    if (empty($data['nama'])) $errors['nama'] = 'Nama harus diisi';
    if (empty($data['email'])) $errors['email'] = 'Email harus diisi';
    elseif (!filter_var($data['email'], FILTER_VALIDATE_EMAIL)) $errors['email'] = 'Email tidak valid';
    elseif ($userModel->emailExists($data['email'])) $errors['email'] = 'Email sudah terdaftar';
    
    if (empty($data['password'])) $errors['password'] = 'Password harus diisi';
    elseif (strlen($data['password']) < 6) $errors['password'] = 'Password minimal 6 karakter';
    elseif ($data['password'] !== $data['confirm_password']) $errors['confirm_password'] = 'Password tidak cocok';
    
    if (empty($data['phone'])) $errors['phone'] = 'Nomor HP harus diisi';
    elseif (!preg_match('/^[0-9]{10,13}$/', $data['phone'])) $errors['phone'] = 'Nomor HP tidak valid';
    
    if (empty($errors)) {
        if ($userModel->register($data)) {
            Helper::redirect('/login.php', 'success', 'Pendaftaran berhasil! Silakan login.');
        } else {
            $errors['general'] = 'Terjadi kesalahan. Silakan coba lagi.';
        }
    }
}

$pageTitle = "Daftar - " . SITE_NAME;
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
            max-width: 500px;
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
        
        .form-row {
            display: grid;
            grid-template-columns: 1fr 1fr;
            gap: 20px;
        }
        
        @media (max-width: 576px) {
            .form-row {
                grid-template-columns: 1fr;
            }
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
        
        .error-message {
            color: #c33;
            font-size: 14px;
            margin-top: 5px;
            display: flex;
            align-items: center;
            gap: 5px;
        }
        
        .error-message i {
            font-size: 14px;
        }
        
        .form-control.error {
            border-color: #c33;
            background: #fee;
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
            
            <h1 class="auth-title">Buat Akun Baru</h1>
            <p class="auth-subtitle">Bergabung dengan komunitas House Cafe</p>
            
            <?php if (isset($errors['general'])): ?>
                <div class="alert">
                    <i class="fas fa-exclamation-circle"></i>
                    <span><?= $errors['general'] ?></span>
                </div>
            <?php endif; ?>
            
            <form method="POST" action="">
                <div class="form-group">
                    <label class="form-label" for="nama">Nama Lengkap</label>
                    <input type="text" id="nama" name="nama" class="form-control <?= isset($errors['nama']) ? 'error' : '' ?>" 
                           placeholder="Nama Anda" required 
                           value="<?= $_POST['nama'] ?? '' ?>">
                    <?php if (isset($errors['nama'])): ?>
                        <div class="error-message">
                            <i class="fas fa-exclamation-circle"></i>
                            <span><?= $errors['nama'] ?></span>
                        </div>
                    <?php endif; ?>
                </div>
                
                <div class="form-group">
                    <label class="form-label" for="email">Email</label>
                    <input type="email" id="email" name="email" class="form-control <?= isset($errors['email']) ? 'error' : '' ?>" 
                           placeholder="nama@email.com" required 
                           value="<?= $_POST['email'] ?? '' ?>">
                    <?php if (isset($errors['email'])): ?>
                        <div class="error-message">
                            <i class="fas fa-exclamation-circle"></i>
                            <span><?= $errors['email'] ?></span>
                        </div>
                    <?php endif; ?>
                </div>
                
                <div class="form-row">
                    <div class="form-group">
                        <label class="form-label" for="password">Password</label>
                        <input type="password" id="password" name="password" 
                               class="form-control <?= isset($errors['password']) ? 'error' : '' ?>" 
                               placeholder="••••••••" required>
                        <?php if (isset($errors['password'])): ?>
                            <div class="error-message">
                                <i class="fas fa-exclamation-circle"></i>
                                <span><?= $errors['password'] ?></span>
                            </div>
                        <?php endif; ?>
                    </div>
                    
                    <div class="form-group">
                        <label class="form-label" for="confirm_password">Konfirmasi Password</label>
                        <input type="password" id="confirm_password" name="confirm_password" 
                               class="form-control <?= isset($errors['confirm_password']) ? 'error' : '' ?>" 
                               placeholder="••••••••" required>
                        <?php if (isset($errors['confirm_password'])): ?>
                            <div class="error-message">
                                <i class="fas fa-exclamation-circle"></i>
                                <span><?= $errors['confirm_password'] ?></span>
                            </div>
                        <?php endif; ?>
                    </div>
                </div>
                
                <div class="form-group">
                    <label class="form-label" for="phone">Nomor HP / WhatsApp</label>
                    <input type="tel" id="phone" name="phone" class="form-control <?= isset($errors['phone']) ? 'error' : '' ?>" 
                           placeholder="081234567890" required 
                           value="<?= $_POST['phone'] ?? '' ?>">
                    <?php if (isset($errors['phone'])): ?>
                        <div class="error-message">
                            <i class="fas fa-exclamation-circle"></i>
                            <span><?= $errors['phone'] ?></span>
                        </div>
                    <?php endif; ?>
                </div>
                
                <div class="form-group">
                    <button type="submit" class="btn btn-primary btn-block btn-large">
                        <i class="fas fa-user-plus"></i> Daftar Sekarang
                    </button>
                </div>
                
                <div class="form-footer">
                    <p>Sudah punya akun? 
                        <a href="<?= BASE_URL ?>/login.php" class="form-link">Masuk di sini</a>
                    </p>
                </div>
            </form>
        </div>
    </div>
    
    <script>
        const form = document.querySelector('form');
        const password = document.getElementById('password');
        const confirmPassword = document.getElementById('confirm_password');
        
        function validatePassword() {
            if (password.value !== confirmPassword.value) {
                confirmPassword.setCustomValidity('Password tidak cocok');
                confirmPassword.classList.add('error');
            } else {
                confirmPassword.setCustomValidity('');
                confirmPassword.classList.remove('error');
            }
        }
        
        password.addEventListener('change', validatePassword);
        confirmPassword.addEventListener('keyup', validatePassword);
        
        form.addEventListener('submit', function(e) {
            const submitBtn = this.querySelector('button[type="submit"]');
            submitBtn.innerHTML = '<i class="fas fa-spinner fa-spin"></i> Memproses...';
            submitBtn.disabled = true;
        });
    </script>
</body>
</html>