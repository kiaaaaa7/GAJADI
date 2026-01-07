<?php
require_once '../includes/config.php';
require_once '../includes/functions.php';
require_once '../app/models/Cart.php';
require_once '../app/models/Product.php';

Middleware::requireAuth();

$cartModel = new Cart();
$productModel = new Product();
$userId = Helper::getUserId();

// Handle cart actions
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    Middleware::validateCsrf();
    
    $action = $_POST['action'] ?? '';
    $itemId = $_POST['item_id'] ?? 0;
    $productId = $_POST['product_id'] ?? 0;
    $quantity = intval($_POST['quantity'] ?? 1);
    
    switch ($action) {
        case 'add':
            $result = $cartModel->addItem($userId, $productId, $quantity);
            if (isset($result['error'])) {
                Helper::redirect('/cart.php', 'error', $result['error']);
            } else {
                Helper::redirect('/cart.php', 'success', 'Produk ditambahkan ke keranjang!');
            }
            break;
            
        case 'update':
            $result = $cartModel->updateItemQuantity($userId, $itemId, $quantity);
            if (isset($result['error'])) {
                Helper::redirect('/cart.php', 'error', $result['error']);
            } else {
                Helper::redirect('/cart.php', 'success', 'Keranjang diperbarui!');
            }
            break;
            
        case 'remove':
            $result = $cartModel->removeItem($userId, $itemId);
            if (isset($result['error'])) {
                Helper::redirect('/cart.php', 'error', $result['error']);
            } else {
                Helper::redirect('/cart.php', 'success', 'Produk dihapus dari keranjang!');
            }
            break;
            
        case 'clear':
            $result = $cartModel->clearCart($userId);
            Helper::redirect('/cart.php', 'success', 'Keranjang dikosongkan!');
            break;
    }
}

// Get cart items
$cartItems = $cartModel->getCartItems($userId);
$cartTotal = $cartModel->getCartTotal($userId);

$pageTitle = "Keranjang - " . SITE_NAME;
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
    
    <!-- Cart Section -->
    <section class="section">
        <div class="container">
            <nav class="breadcrumb">
                <a href="<?= BASE_URL ?>/">Beranda</a>
                <i class="fas fa-chevron-right"></i>
                <span>Keranjang Belanja</span>
            </nav>
            
            <h1 class="page-title">Keranjang Belanja</h1>
            
            <?php if (empty($cartItems)): ?>
                <div class="empty-cart">
                    <i class="fas fa-shopping-cart"></i>
                    <h3>Keranjang Anda kosong</h3>
                    <p>Tambahkan produk favorit Anda dari menu kami</p>
                    <a href="<?= BASE_URL ?>/shop.php" class="btn btn-primary">
                        <i class="fas fa-shopping-bag"></i> Belanja Sekarang
                    </a>
                </div>
            <?php else: ?>
                <div class="cart-container">
                    <div class="cart-items">
                        <div class="cart-header">
                            <h2>Produk (<?= count($cartItems) ?>)</h2>
                            <form method="POST" action="" class="d-inline">
                                <input type="hidden" name="csrf_token" value="<?= Helper::generateCsrfToken() ?>">
                                <input type="hidden" name="action" value="clear">
                                <button type="submit" class="btn btn-sm btn-outline" 
                                        onclick="return confirm('Hapus semua item dari keranjang?')">
                                    <i class="fas fa-trash"></i> Kosongkan Keranjang
                                </button>
                            </form>
                        </div>
                        
                        <?php foreach ($cartItems as $item): ?>
                            <div class="cart-item">
                                <div class="cart-item-image">
                                    <img src="<?= BASE_URL ?>/public/uploads/<?= $item['gambar'] ?: 'default.jpg' ?>" 
                                         alt="<?= $item['nama'] ?>">
                                </div>
                                
                                <div class="cart-item-info">
                                    <h3 class="cart-item-name"><?= $item['nama'] ?></h3>
                                    <p class="cart-item-desc"><?= substr($item['deskripsi'], 0, 100) ?>...</p>
                                    <div class="cart-item-meta">
                                        <span class="cart-item-price"><?= Helper::formatRupiah($item['price_snapshot']) ?></span>
                                        <span class="cart-item-stock">Stok: <?= $item['stok'] ?></span>
                                    </div>
                                </div>
                                
                                <div class="cart-item-quantity">
                                    <form method="POST" action="" class="quantity-form">
                                        <input type="hidden" name="csrf_token" value="<?= Helper::generateCsrfToken() ?>">
                                        <input type="hidden" name="action" value="update">
                                        <input type="hidden" name="item_id" value="<?= $item['id'] ?>">
                                        <div class="quantity-control">
                                            <button type="button" class="quantity-btn minus" 
                                                    onclick="this.form.querySelector('input[name=quantity]').stepDown(); this.form.submit();">
                                                <i class="fas fa-minus"></i>
                                            </button>
                                            <input type="number" name="quantity" value="<?= $item['qty'] ?>" 
                                                   min="1" max="<?= $item['stok'] ?>" 
                                                   onchange="this.form.submit()">
                                            <button type="button" class="quantity-btn plus" 
                                                    onclick="this.form.querySelector('input[name=quantity]').stepUp(); this.form.submit();">
                                                <i class="fas fa-plus"></i>
                                            </button>
                                        </div>
                                    </form>
                                </div>
                                
                                <div class="cart-item-total">
                                    <span><?= Helper::formatRupiah($item['price_snapshot'] * $item['qty']) ?></span>
                                </div>
                                
                                <div class="cart-item-actions">
                                    <form method="POST" action="">
                                        <input type="hidden" name="csrf_token" value="<?= Helper::generateCsrfToken() ?>">
                                        <input type="hidden" name="action" value="remove">
                                        <input type="hidden" name="item_id" value="<?= $item['id'] ?>">
                                        <button type="submit" class="btn btn-sm btn-outline" 
                                                onclick="return confirm('Hapus produk ini dari keranjang?')">
                                            <i class="fas fa-trash"></i>
                                        </button>
                                    </form>
                                </div>
                            </div>
                        <?php endforeach; ?>
                    </div>
                    
                    <div class="cart-summary">
                        <h2 class="summary-title">Ringkasan Pesanan</h2>
                        
                        <div class="summary-details">
                            <div class="summary-row">
                                <span>Subtotal</span>
                                <span><?= Helper::formatRupiah($cartTotal) ?></span>
                            </div>
                            <div class="summary-row">
                                <span>Biaya Pengiriman</span>
                                <span>Rp 10.000</span>
                            </div>
                            <div class="summary-row">
                                <span>Biaya Layanan</span>
                                <span>Rp 5.000</span>
                            </div>
                            <div class="summary-divider"></div>
                            <div class="summary-row total">
                                <span>Total</span>
                                <span><?= Helper::formatRupiah($cartTotal + 15000) ?></span>
                            </div>
                        </div>
                        
                        <div class="summary-actions">
                            <a href="<?= BASE_URL ?>/shop.php" class="btn btn-outline btn-block">
                                <i class="fas fa-arrow-left"></i> Lanjutkan Belanja
                            </a>
                            <a href="<?= BASE_URL ?>/checkout.php" class="btn btn-primary btn-block">
                                <i class="fas fa-shopping-bag"></i> Lanjut ke Checkout
                            </a>
                        </div>
                        
                        <div class="payment-methods">
                            <p class="payment-title">Metode Pembayaran:</p>
                            <div class="payment-icons">
                                <i class="fab fa-cc-visa" title="Visa"></i>
                                <i class="fab fa-cc-mastercard" title="Mastercard"></i>
                                <i class="fab fa-cc-paypal" title="PayPal"></i>
                                <i class="fas fa-qrcode" title="QRIS"></i>
                                <i class="fas fa-money-bill-wave" title="Tunai"></i>
                            </div>
                        </div>
                    </div>
                </div>
            <?php endif; ?>
        </div>
    </section>
    
    <?php include 'includes/footer.php'; ?>
</body>
</html>