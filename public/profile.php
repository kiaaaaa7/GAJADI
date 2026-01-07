<?php
require_once '../includes/config.php';
require_once '../includes/functions.php';
require_once '../app/models/User.php';

Middleware::requireAuth();

$userModel = new User();
$userId = Helper::getUserId();
$user = $userModel->getUserById($userId);

$message = '';
$error = '';

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    Middleware::validateCsrf();
    
    $data = [
        'nama' => Helper::sanitize($_POST['nama']),
        'phone' => Helper::sanitize($_POST['phone']),
        'password' => $_POST['password'],
        'confirm_password' => $_POST['confirm_password']
    ];
    
    // Validation
    if (empty($data['nama'])) {
        $error = 'Nama harus diisi';
    } elseif (empty($data['phone'])) {
        $error = 'Nomor HP harus diisi';
    } elseif ($data['password'] && $data['password'] !== $data['confirm_password']) {
        $error = 'Password tidak cocok';
    }
    
    if (!$error) {
        if ($userModel->updateProfile($userId, $data)) {
            $_SESSION['user_name'] = $data['nama'];
            $message = 'Profile berhasil diperbarui!';
            $user = $userModel->getUserById($userId); // Refresh user data
        } else {
            $error = 'Gagal memperbarui profile';
        }
    }
}

$pageTitle = "Profile - " . SITE_NAME;
?>
<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title><?= $pageTitle ?></title>
    <link rel="stylesheet" href="<?= BASE_URL ?>/assets/css/main.css">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">
</head>
<body>
    <?php include 'includes/header.php'; ?>
    
    <section class="section">
        <div class="container">
            <nav class="breadcrumb">
                <a href="<?= BASE_URL ?>/">Beranda</a>
                <i class="fas fa-chevron-right"></i>
                <span>Profile</span>
            </nav>
            
            <h1 class="page-title">Profile Saya</h1>
            
            <div class="profile-container">
                <div class="profile-sidebar">
                    <div class="profile-avatar">
                        <i class="fas fa-user-circle"></i>
                        <h3><?= $user['nama'] ?></h3>
                        <p><?= $user['email'] ?></p>
                        <span class="badge <?= $user['role'] === 'admin' ? 'badge-primary' : 'badge-secondary' ?>">
                            <?= ucfirst($user['role']) ?>
                        </span>
                    </div>
                    
                    <div class="profile-menu">
                        <a href="<?= BASE_URL ?>/profile.php" class="active">
                            <i class="fas fa-user"></i> Profile
                        </a>
                        <a href="<?= BASE_URL ?>/orders.php">
                            <i class="fas fa-shopping-bag"></i> Pesanan Saya
                        </a>
                        <a href="<?= BASE_URL ?>/cart.php">
                            <i class="fas fa-shopping-cart"></i> Keranjang
                        </a>
                        <?php if (Helper::isAdmin()): ?>
                            <a href="<?= BASE_URL ?>/admin/">
                                <i class="fas fa-cog"></i> Admin Panel
                            </a>
                        <?php endif; ?>
                    </div>
                </div>
                
                <div class="profile-content">
                    <?php if ($message): ?>
                        <div class="alert alert-success">
                            <i class="fas fa-check-circle"></i> <?= $message ?>
                        </div>
                    <?php endif; ?>
                    
                    <?php if ($error): ?>
                        <div class="alert alert-error">
                            <i class="fas fa-exclamation-circle"></i> <?= $error ?>
                        </div>
                    <?php endif; ?>
                    
                    <form method="POST" class="profile-form">
                        <input type="hidden" name="csrf_token" value="<?= Helper::generateCsrfToken() ?>">
                        
                        <div class="form-group">
                            <label for="nama">Nama Lengkap</label>
                            <input type="text" id="nama" name="nama" class="form-control" 
                                   value="<?= $user['nama'] ?>" required>
                        </div>
                        
                        <div class="form-group">
                            <label for="email">Email</label>
                            <input type="email" id="email" class="form-control" 
                                   value="<?= $user['email'] ?>" readonly>
                            <small class="form-text">Email tidak dapat diubah</small>
                        </div>
                        
                        <div class="form-group">
                            <label for="phone">Nomor HP / WhatsApp</label>
                            <input type="tel" id="phone" name="phone" class="form-control" 
                                   value="<?= $user['phone'] ?? '' ?>" required>
                        </div>
                        
                        <div class="form-row">
                            <div class="form-group">
                                <label for="password">Password Baru (opsional)</label>
                                <input type="password" id="password" name="password" 
                                       class="form-control" placeholder="••••••••">
                                <small class="form-text">Kosongkan jika tidak ingin mengubah password</small>
                            </div>
                            
                            <div class="form-group">
                                <label for="confirm_password">Konfirmasi Password</label>
                                <input type="password" id="confirm_password" name="confirm_password" 
                                       class="form-control" placeholder="••••••••">
                            </div>
                        </div>
                        
                        <div class="form-actions">
                            <button type="submit" class="btn btn-primary">
                                <i class="fas fa-save"></i> Simpan Perubahan
                            </button>
                        </div>
                    </form>
                    
                    <div class="profile-info">
                        <h3>Informasi Akun</h3>
                        <div class="info-grid">
                            <div class="info-item">
                                <i class="fas fa-calendar"></i>
                                <div>
                                    <small>Bergabung Sejak</small>
                                    <p><?= date('d F Y', strtotime($user['created_at'])) ?></p>
                                </div>
                            </div>
                            <div class="info-item">
                                <i class="fas fa-user-tag"></i>
                                <div>
                                    <small>Role</small>
                                    <p><?= ucfirst($user['role']) ?></p>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </section>
    
    <?php include 'includes/footer.php'; ?>
    
    <style>
        .profile-container {
            display: grid;
            grid-template-columns: 300px 1fr;
            gap: 2rem;
            margin-top: 2rem;
        }
        
        @media (max-width: 768px) {
            .profile-container {
                grid-template-columns: 1fr;
            }
        }
        
        .profile-sidebar {
            background: white;
            border-radius: var(--radius-lg);
            padding: 2rem;
            box-shadow: var(--shadow-md);
            height: fit-content;
        }
        
        .profile-avatar {
            text-align: center;
            margin-bottom: 2rem;
        }
        
        .profile-avatar i {
            font-size: 4rem;
            color: var(--primary);
            margin-bottom: 1rem;
        }
        
        .profile-avatar h3 {
            margin: 0.5rem 0;
        }
        
        .profile-avatar p {
            color: var(--dark-gray);
            margin: 0.25rem 0 1rem 0;
        }
        
        .badge {
            display: inline-block;
            padding: 0.25rem 0.75rem;
            border-radius: var(--radius-full);
            font-size: 0.75rem;
            font-weight: 600;
        }
        
        .badge-primary {
            background-color: var(--primary-light);
            color: var(--primary-dark);
        }
        
        .badge-secondary {
            background-color: var(--light-gray);
            color: var(--dark-gray);
        }
        
        .profile-menu {
            display: flex;
            flex-direction: column;
            gap: 0.5rem;
        }
        
        .profile-menu a {
            display: flex;
            align-items: center;
            gap: 0.75rem;
            padding: 0.75rem 1rem;
            border-radius: var(--radius-md);
            color: var(--dark);
            text-decoration: none;
            transition: all var(--transition-fast);
        }
        
        .profile-menu a:hover,
        .profile-menu a.active {
            background-color: var(--light-gray);
            color: var(--primary);
        }
        
        .profile-content {
            background: white;
            border-radius: var(--radius-lg);
            padding: 2rem;
            box-shadow: var(--shadow-md);
        }
        
        .profile-form {
            margin-bottom: 2rem;
        }
        
        .profile-info h3 {
            margin-bottom: 1.5rem;
            padding-bottom: 1rem;
            border-bottom: 2px solid var(--light-gray);
        }
        
        .info-grid {
            display: grid;
            grid-template-columns: repeat(auto-fit, minmax(200px, 1fr));
            gap: 1.5rem;
        }
        
        .info-item {
            display: flex;
            align-items: center;
            gap: 1rem;
        }
        
        .info-item i {
            font-size: 1.5rem;
            color: var(--primary);
            width: 40px;
            height: 40px;
            display: flex;
            align-items: center;
            justify-content: center;
            background-color: var(--light-gray);
            border-radius: 50%;
        }
        
        .info-item small {
            display: block;
            color: var(--dark-gray);
            font-size: 0.75rem;
            text-transform: uppercase;
            letter-spacing: 0.05em;
        }
        
        .info-item p {
            margin: 0;
            font-weight: 500;
        }
        
        .alert {
            padding: 1rem;
            border-radius: var(--radius-md);
            margin-bottom: 1.5rem;
            display: flex;
            align-items: center;
            gap: 0.75rem;
        }
        
        .alert-success {
            background-color: var(--success-light);
            color: var(--success-dark);
            border-left: 4px solid var(--success);
        }
        
        .alert-error {
            background-color: var(--error-light);
            color: var(--error-dark);
            border-left: 4px solid var(--error);
        }
        
        .alert i {
            font-size: 1.25rem;
        }
    </style>
</body>
</html>