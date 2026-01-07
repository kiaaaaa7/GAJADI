<?php
require_once '../includes/config.php';
require_once '../includes/functions.php';
require_once '../app/models/Product.php';

$productModel = new Product();
$products = $productModel->getFeaturedProducts();
$categories = $productModel->getCategories();

$pageTitle = "Beranda - " . SITE_NAME;
?>
<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title><?= $pageTitle ?></title>
    <link rel="stylesheet" href="<?= BASE_URL ?>/assets/css/main.css">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">
    <link href="https://fonts.googleapis.com/css2?family=Poppins:wght@300;400;500;600;700&family=Playfair+Display:wght@400;500;600&display=swap" rel="stylesheet">
</head>
<body>
    <!-- Header -->
    <header class="header">
        <nav class="navbar">
            <div class="container">
                <a href="<?= BASE_URL ?>/" class="logo">
                    <i class="fas fa-mug-hot"></i>
                    <span>House Cafe</span>
                </a>
                
                <div class="nav-menu">
                    <a href="<?= BASE_URL ?>/" class="nav-link active">Beranda</a>
                    <a href="<?= BASE_URL ?>/shop.php" class="nav-link">Menu</a>
                    <div class="dropdown">
                        <a href="#" class="nav-link">Kategori <i class="fas fa-chevron-down"></i></a>
                        <div class="dropdown-content">
                            <?php foreach ($categories as $cat): ?>
                                <a href="<?= BASE_URL ?>/shop.php?category=<?= $cat['slug'] ?>">
                                    <?= $cat['nama'] ?>
                                </a>
                            <?php endforeach; ?>
                        </div>
                    </div>
                    <a href="#about" class="nav-link">Tentang</a>
                    <a href="#contact" class="nav-link">Kontak</a>
                </div>
                
                <div class="nav-actions">
                    <a href="<?= BASE_URL ?>/cart.php" class="cart-btn">
                        <i class="fas fa-shopping-cart"></i>
                        <?php if (Helper::isLoggedIn()): ?>
                            <span class="cart-count"><?= Helper::getCartItemCount(Helper::getUserId()) ?></span>
                        <?php endif; ?>
                    </a>
                    
                    <?php if (Helper::isLoggedIn()): ?>
                        <div class="dropdown">
                            <a href="#" class="user-btn">
                                <i class="fas fa-user-circle"></i>
                                <span><?= $_SESSION['user_name'] ?></span>
                            </a>
                            <div class="dropdown-content">
                                <a href="<?= BASE_URL ?>/profile.php">Profile</a>
                                <a href="<?= BASE_URL ?>/orders.php">Pesanan</a>
                                <?php if (Helper::isAdmin()): ?>
                                    <a href="<?= BASE_URL ?>/admin/">Admin Panel</a>
                                <?php endif; ?>
                                <a href="<?= BASE_URL ?>/logout.php">Logout</a>
                            </div>
                        </div>
                    <?php else: ?>
                        <a href="<?= BASE_URL ?>/login.php" class="btn btn-outline">Login</a>
                        <a href="<?= BASE_URL ?>/register.php" class="btn btn-primary">Daftar</a>
                    <?php endif; ?>
                    
                    <button class="menu-toggle" id="menuToggle">
                        <i class="fas fa-bars"></i>
                    </button>
                </div>
            </div>
        </nav>
        
        <!-- Mobile Menu -->
        <div class="mobile-menu" id="mobileMenu">
            <a href="<?= BASE_URL ?>/" class="mobile-link active">Beranda</a>
            <a href="<?= BASE_URL ?>/shop.php" class="mobile-link">Menu</a>
            <?php foreach ($categories as $cat): ?>
                <a href="<?= BASE_URL ?>/shop.php?category=<?= $cat['slug'] ?>" class="mobile-link">
                    <?= $cat['nama'] ?>
                </a>
            <?php endforeach; ?>
            <a href="#about" class="mobile-link">Tentang</a>
            <a href="#contact" class="mobile-link">Kontak</a>
        </div>
    </header>

    <!-- Hero Section -->
    <section class="hero">
        <div class="container">
            <div class="hero-content">
                <h1 class="hero-title">Rasa Premium, Kenyamanan Rumah</h1>
                <p class="hero-subtitle">Nikmati kopi spesial dan cemilan lezat di House Cafe, tempat dimana setiap tegukan membawa kebahagiaan.</p>
                <div class="hero-buttons">
                    <a href="<?= BASE_URL ?>/shop.php" class="btn btn-primary btn-large">Lihat Menu</a>
                    <a href="<?= BASE_URL ?="/shop.php?category=signature" ?>" class="btn btn-outline btn-large">Signature Drinks</a>
                </div>
            </div>
            <div class="hero-image">
                <img src="<?= BASE_URL ?>/assets/img/hero-coffee.png" alt="Coffee" class="floating">
            </div>
        </div>
    </section>

    <!-- Featured Products -->
    <section class="section">
        <div class="container">
            <div class="section-header">
                <h2 class="section-title">Menu Terpopuler</h2>
                <p class="section-subtitle">Pilihan terbaik dari House Cafe</p>
            </div>
            
            <div class="products-grid">
                <?php foreach ($products as $product): ?>
                    <div class="product-card">
                        <div class="product-image">
                            <img src="<?= BASE_URL ?>/public/uploads/<?= $product['gambar'] ?: 'default.jpg' ?>" alt="<?= $product['nama'] ?>">
                            <span class="product-category"><?= $product['category_name'] ?></span>
                        </div>
                        <div class="product-info">
                            <h3 class="product-name"><?= $product['nama'] ?></h3>
                            <p class="product-desc"><?= substr($product['deskripsi'], 0, 60) ?>...</p>
                            <div class="product-footer">
                                <span class="product-price"><?= Helper::formatRupiah($product['harga']) ?></span>
                                <a href="<?= BASE_URL ?>/product.php?slug=<?= $product['slug'] ?>" class="btn btn-sm">
                                    <i class="fas fa-eye"></i> Detail
                                </a>
                            </div>
                        </div>
                    </div>
                <?php endforeach; ?>
            </div>
            
            <div class="text-center mt-40">
                <a href="<?= BASE_URL ?>/shop.php" class="btn btn-primary">Lihat Semua Menu</a>
            </div>
        </div>
    </section>

    <!-- About Section -->
    <section class="section bg-light" id="about">
        <div class="container">
            <div class="about-grid">
                <div class="about-content">
                    <h2 class="section-title">House Cafe Experience</h2>
                    <p>House Cafe adalah tempat dimana cita rasa kopi premium bertemu dengan kenyamanan rumah. Kami menggunakan biji kopi pilihan dari perkebunan terbaik di Indonesia, dipanggang dengan teknik khusus untuk mendapatkan aroma dan rasa yang optimal.</p>
                    <p>Setiap sajian dibuat dengan perhatian terhadap detail, mulai dari pemilihan bahan hingga penyajian akhir. Kami percaya bahwa secangkir kopi yang baik dapat membawa kebahagiaan dalam hari Anda.</p>
                    
                    <div class="features">
                        <div class="feature">
                            <i class="fas fa-coffee"></i>
                            <h4>Kopi Spesial</h4>
                            <p>Single origin & blend premium</p>
                        </div>
                        <div class="feature">
                            <i class="fas fa-clock"></i>
                            <h4>Buka Setiap Hari</h4>
                            <p>08:00 - 22:00 WIB</p>
                        </div>
                        <div class="feature">
                            <i class="fas fa-wifi"></i>
                            <h4>Free WiFi</h4>
                            <p>Internet cepat untuk bekerja</p>
                        </div>
                        <div class="feature">
                            <i class="fas fa-truck"></i>
                            <h4>Delivery</h4>
                            <p>Pesan antar hingga ke rumah</p>
                        </div>
                    </div>
                </div>
                <div class="about-image">
                    <img src="<?= BASE_URL ?>/assets/img/interior.jpg" alt="Cafe Interior">
                </div>
            </div>
        </div>
    </section>

    <!-- Footer -->
    <footer class="footer" id="contact">
        <div class="container">
            <div class="footer-grid">
                <div class="footer-col">
                    <h3 class="footer-title">House Cafe</h3>
                    <p class="footer-text">Tempat ngopi yang nyaman dengan suasana seperti rumah sendiri.</p>
                    <div class="social-links">
                        <a href="#" class="social-link"><i class="fab fa-instagram"></i></a>
                        <a href="#" class="social-link"><i class="fab fa-facebook"></i></a>
                        <a href="#" class="social-link"><i class="fab fa-tiktok"></i></a>
                        <a href="#" class="social-link"><i class="fab fa-whatsapp"></i></a>
                    </div>
                </div>
                
                <div class="footer-col">
                    <h3 class="footer-title">Jam Buka</h3>
                    <ul class="footer-list">
                        <li>Senin - Jumat: 08:00 - 22:00</li>
                        <li>Sabtu - Minggu: 08:00 - 23:00</li>
                        <li>Hari Libur: 10:00 - 22:00</li>
                    </ul>
                </div>
                
                <div class="footer-col">
                    <h3 class="footer-title">Kontak</h3>
                    <ul class="footer-list">
                        <li><i class="fas fa-map-marker-alt"></i> Jl. Sudirman No. 123, Jakarta</li>
                        <li><i class="fas fa-phone"></i> (021) 1234-5678</li>
                        <li><i class="fas fa-envelope"></i> info@housecafe.com</li>
                    </ul>
                </div>
                
                <div class="footer-col">
                    <h3 class="footer-title">Newsletter</h3>
                    <p class="footer-text">Dapatkan promo dan menu terbaru dari kami.</p>
                    <form class="newsletter-form">
                        <input type="email" placeholder="Email Anda" required>
                        <button type="submit" class="btn btn-primary">Subscribe</button>
                    </form>
                </div>
            </div>
            
            <div class="footer-bottom">
                <p>&copy; <?= date('Y') ?> House Cafe. All rights reserved.</p>
                <div class="footer-links">
                    <a href="#">Privacy Policy</a>
                    <a href="#">Terms of Service</a>
                    <a href="#">Sitemap</a>
                </div>
            </div>
        </div>
    </footer>

    <!-- Flash Messages -->
    <?php if ($success = Helper::getFlash('success')): ?>
        <div class="flash-message success">
            <div class="container">
                <p><?= $success ?></p>
                <button class="flash-close">&times;</button>
            </div>
        </div>
    <?php endif; ?>
    
    <?php if ($error = Helper::getFlash('error')): ?>
        <div class="flash-message error">
            <div class="container">
                <p><?= $error ?></p>
                <button class="flash-close">&times;</button>
            </div>
        </div>
    <?php endif; ?>

    <script src="<?= BASE_URL ?>/assets/js/main.js"></script>
</body>
</html>