<?php
require_once '../includes/config.php';
require_once '../includes/functions.php';
require_once '../app/models/Product.php';
require_once '../app/models/Cart.php';

$productModel = new Product();
$cartModel = new Cart();

// Get product by slug
$slug = $_GET['slug'] ?? '';
$product = $productModel->getProductBySlug($slug);

if (!$product) {
    Helper::redirect('/shop.php', 'error', 'Produk tidak ditemukan.');
}

// Handle add to cart
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['add_to_cart'])) {
    Middleware::requireAuth();
    Middleware::validateCsrf();
    
    $quantity = intval($_POST['quantity'] ?? 1);
    
    if ($quantity < 1 || $quantity > $product['stok']) {
        Helper::redirect("/product.php?slug=$slug", 'error', 'Jumlah tidak valid.');
    }
    
    $result = $cartModel->addItem(Helper::getUserId(), $product['id'], $quantity);
    
    if (isset($result['error'])) {
        Helper::redirect("/product.php?slug=$slug", 'error', $result['error']);
    } else {
        Helper::redirect("/product.php?slug=$slug", 'success', 'Produk ditambahkan ke keranjang!');
    }
}

// Get related products
$relatedProducts = $productModel->getProductsByCategory($product['category_id']);
$relatedProducts = array_filter($relatedProducts, fn($p) => $p['id'] != $product['id']);
$relatedProducts = array_slice($relatedProducts, 0, 4);

$pageTitle = $product['nama'] . " - " . SITE_NAME;
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
    
    <!-- Product Detail -->
    <section class="section">
        <div class="container">
            <nav class="breadcrumb">
                <a href="<?= BASE_URL ?>/">Beranda</a>
                <i class="fas fa-chevron-right"></i>
                <a href="<?= BASE_URL ?>/shop.php">Menu</a>
                <i class="fas fa-chevron-right"></i>
                <span><?= $product['nama'] ?></span>
            </nav>
            
            <div class="product-detail">
                <div class="product-gallery">
                    <div class="main-image">
                        <img src="<?= BASE_URL ?>/public/uploads/<?= $product['gambar'] ?: 'default.jpg' ?>" 
                             alt="<?= $product['nama'] ?>" id="mainImage">
                    </div>
                </div>
                
                <div class="product-info">
                    <div class="product-header">
                        <h1 class="product-title"><?= $product['nama'] ?></h1>
                        <div class="product-meta">
                            <span class="product-category"><?= $product['category_name'] ?></span>
                            <div class="product-rating">
                                <i class="fas fa-star"></i>
                                <i class="fas fa-star"></i>
                                <i class="fas fa-star"></i>
                                <i class="fas fa-star"></i>
                                <i class="fas fa-star-half-alt"></i>
                                <span>4.5 (128 reviews)</span>
                            </div>
                        </div>
                    </div>
                    
                    <div class="product-price-section">
                        <span class="product-price"><?= Helper::formatRupiah($product['harga']) ?></span>
                        <?php if ($product['stok'] > 0): ?>
                            <span class="product-stock in-stock">
                                <i class="fas fa-check-circle"></i> Stok tersedia (<?= $product['stok'] ?>)
                            </span>
                        <?php else: ?>
                            <span class="product-stock out-of-stock">
                                <i class="fas fa-times-circle"></i> Stok habis
                            </span>
                        <?php endif; ?>
                    </div>
                    
                    <div class="product-description">
                        <h3>Deskripsi</h3>
                        <p><?= nl2br($product['deskripsi']) ?></p>
                        
                        <div class="product-features">
                            <div class="feature">
                                <i class="fas fa-leaf"></i>
                                <span>100% bahan alami</span>
                            </div>
                            <div class="feature">
                                <i class="fas fa-clock"></i>
                                <span>Dibuat fresh setiap hari</span>
                            </div>
                            <div class="feature">
                                <i class="fas fa-heart"></i>
                                <span>Tanpa pengawet</span>
                            </div>
                        </div>
                    </div>
                    
                    <?php if ($product['stok'] > 0): ?>
                        <form method="POST" action="" class="add-to-cart-form">
                            <input type="hidden" name="csrf_token" value="<?= Helper::generateCsrfToken() ?>">
                            
                            <div class="quantity-selector">
                                <label for="quantity">Jumlah:</label>
                                <div class="quantity-control">
                                    <button type="button" class="quantity-btn minus" onclick="updateQuantity(-1)">
                                        <i class="fas fa-minus"></i>
                                    </button>
                                    <input type="number" id="quantity" name="quantity" value="1" min="1" 
                                           max="<?= $product['stok'] ?>">
                                    <button type="button" class="quantity-btn plus" onclick="updateQuantity(1)">
                                        <i class="fas fa-plus"></i>
                                    </button>
                                </div>
                            </div>
                            
                            <div class="action-buttons">
                                <?php if (Helper::isLoggedIn()): ?>
                                    <button type="submit" name="add_to_cart" class="btn btn-primary btn-large">
                                        <i class="fas fa-cart-plus"></i> Tambah ke Keranjang
                                    </button>
                                <?php else: ?>
                                    <a href="<?= BASE_URL ?>/login.php" class="btn btn-primary btn-large">
                                        <i class="fas fa-sign-in-alt"></i> Login untuk Memesan
                                    </a>
                                <?php endif; ?>
                                
                                <a href="<?= BASE_URL ?>/shop.php?category=<?= $product['category_slug'] ?? 'kopi' ?>" 
                                   class="btn btn-outline btn-large">
                                    <i class="fas fa-arrow-left"></i> Lihat Menu Lain
                                </a>
                            </div>
                        </form>
                    <?php else: ?>
                        <div class="out-of-stock-message">
                            <i class="fas fa-exclamation-triangle"></i>
                            <p>Maaf, produk ini sedang habis. Silakan cek kembali nanti atau lihat produk lainnya.</p>
                            <a href="<?= BASE_URL ?>/shop.php" class="btn btn-primary">Lihat Menu Lain</a>
                        </div>
                    <?php endif; ?>
                </div>
            </div>
            
            <!-- Related Products -->
            <?php if (!empty($relatedProducts)): ?>
                <div class="related-products">
                    <h2 class="section-title">Produk Terkait</h2>
                    <div class="products-grid">
                        <?php foreach ($relatedProducts as $related): ?>
                            <div class="product-card">
                                <div class="product-image">
                                    <img src="<?= BASE_URL ?>/public/uploads/<?= $related['gambar'] ?: 'default.jpg' ?>" 
                                         alt="<?= $related['nama'] ?>">
                                    <span class="product-category"><?= $related['category_name'] ?></span>
                                </div>
                                <div class="product-info">
                                    <h3 class="product-name">
                                        <a href="<?= BASE_URL ?>/product.php?slug=<?= $related['slug'] ?>">
                                            <?= $related['nama'] ?>
                                        </a>
                                    </h3>
                                    <p class="product-desc"><?= substr($related['deskripsi'], 0, 60) ?>...</p>
                                    <div class="product-footer">
                                        <span class="product-price"><?= Helper::formatRupiah($related['harga']) ?></span>
                                        <a href="<?= BASE_URL ?>/product.php?slug=<?= $related['slug'] ?>" 
                                           class="btn btn-sm">
                                            <i class="fas fa-eye"></i>
                                        </a>
                                    </div>
                                </div>
                            </div>
                        <?php endforeach; ?>
                    </div>
                </div>
            <?php endif; ?>
        </div>
    </section>
    
    <?php include 'includes/footer.php'; ?>
    
    <script>
        function updateQuantity(change) {
            const input = document.getElementById('quantity');
            const max = parseInt(input.max);
            let value = parseInt(input.value) + change;
            
            if (value < 1) value = 1;
            if (value > max) value = max;
            
            input.value = value;
        }
        
        // Update quantity input
        document.getElementById('quantity').addEventListener('change', function() {
            let value = parseInt(this.value);
            const max = parseInt(this.max);
            
            if (isNaN(value) || value < 1) value = 1;
            if (value > max) value = max;
            
            this.value = value;
        });
    </script>
</body>
</html>