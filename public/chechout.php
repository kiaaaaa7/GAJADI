<?php
require_once '../includes/config.php';
require_once '../includes/functions.php';
require_once '../app/models/Cart.php';
require_once '../app/models/Order.php';

Middleware::requireAuth();

$cartModel = new Cart();
$orderModel = new Order();
$userId = Helper::getUserId();

// Get cart items
$cartItems = $cartModel->getCartItems($userId);
$cartTotal = $cartModel->getCartTotal($userId);

if (empty($cartItems)) {
    Helper::redirect('/cart.php', 'error', 'Keranjang Anda kosong.');
}

// Handle checkout
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    Middleware::validateCsrf();
    
    $data = [
        'alamat' => Helper::sanitize($_POST['alamat']),
        'payment_method' => Helper::sanitize($_POST['payment_method'])
    ];
    
    if (empty($data['alamat'])) {
        Helper::redirect('/checkout.php', 'error', 'Alamat pengiriman harus diisi.');
    }
    
    if (empty($data['payment_method'])) {
        Helper::redirect('/checkout.php', 'error', 'Metode pembayaran harus dipilih.');
    }
    
    try {
        // Create order
        $orderId = $orderModel->createOrder($userId, $data, $cartItems);
        
        // Redirect to success page
        Helper::redirect("/order_success.php?order_id=$orderId");
        
    } catch (Exception $e) {
        Helper::redirect('/checkout.php', 'error', 'Terjadi kesalahan: ' . $e->getMessage());
    }
}

$pageTitle = "Checkout - " . SITE_NAME;
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
    
    <!-- Checkout Section -->
    <section class="section">
        <div class="container">
            <nav class="breadcrumb">
                <a href="<?= BASE_URL ?>/">Beranda</a>
                <i class="fas fa-chevron-right"></i>
                <a href="<?= BASE_URL ?>/cart.php">Keranjang</a>
                <i class="fas fa-chevron-right"></i>
                <span>Checkout</span>
            </nav>
            
            <h1 class="page-title">Checkout</h1>
            
            <div class="checkout-container">
                <form method="POST" action="" class="checkout-form">
                    <input type="hidden" name="csrf_token" value="<?= Helper::generateCsrfToken() ?>">
                    
                    <div class="checkout-columns">
                        <!-- Left Column - Shipping & Payment -->
                        <div class="checkout-left">
                            <!-- Shipping Address -->
                            <div class="checkout-section">
                                <h2 class="section-title">
                                    <i class="fas fa-map-marker-alt"></i> Alamat Pengiriman
                                </h2>
                                
                                <div class="form-group">
                                    <label for="alamat">Alamat Lengkap *</label>
                                    <textarea id="alamat" name="alamat" rows="4" 
                                              class="form-control" 
                                              placeholder="Contoh: Jl. Sudirman No. 123, RT 01/RW 02, Jakarta Pusat" 
                                              required></textarea>
                                    <small class="form-text">Tambahkan detail seperti nama gedung, lantai, atau patokan.</small>
                                </div>
                                
                                <div class="form-row">
                                    <div class="form-group">
                                        <label for="nama">Nama Penerima</label>
                                        <input type="text" id="nama" class="form-control" 
                                               value="<?= $_SESSION['user_name'] ?>" readonly>
                                    </div>
                                    <div class="form-group">
                                        <label for="phone">Nomor HP</label>
                                        <input type="text" id="phone" class="form-control" 
                                               value="<?= $_SESSION['user_phone'] ?? '' ?>" 
                                               placeholder="Masukkan nomor HP" required>
                                    </div>
                                </div>
                            </div>
                            
                            <!-- Payment Method -->
                            <div class="checkout-section">
                                <h2 class="section-title">
                                    <i class="fas fa-credit-card"></i> Metode Pembayaran
                                </h2>
                                
                                <div class="payment-methods">
                                    <label class="payment-method">
                                        <input type="radio" name="payment_method" value="QRIS" required>
                                        <div class="payment-content">
                                            <div class="payment-icon">
                                                <i class="fas fa-qrcode"></i>
                                            </div>
                                            <div class="payment-info">
                                                <h4>QRIS</h4>
                                                <p>Bayar dengan scan QR code</p>
                                            </div>
                                        </div>
                                    </label>
                                    
                                    <label class="payment-method">
                                        <input type="radio" name="payment_method" value="Transfer Bank">
                                        <div class="payment-content">
                                            <div class="payment-icon">
                                                <i class="fas fa-university"></i>
                                            </div>
                                            <div class="payment-info">
                                                <h4>Transfer Bank</h4>
                                                <p>BCA, Mandiri, BNI, BRI</p>
                                            </div>
                                        </div>
                                    </label>
                                    
                                    <label class="payment-method">
                                        <input type="radio" name="payment_method" value="COD">
                                        <div class="payment-content">
                                            <div class="payment-icon">
                                                <i class="fas fa-money-bill-wave"></i>
                                            </div>
                                            <div class="payment-info">
                                                <h4>Cash on Delivery</h4>
                                                <p>Bayar saat pesanan tiba</p>
                                            </div>
                                        </div>
                                    </label>
                                    
                                    <label class="payment-method">
                                        <input type="radio" name="payment_method" value="E-Wallet">
                                        <div class="payment-content">
                                            <div class="payment-icon">
                                                <i class="fas fa-wallet"></i>
                                            </div>
                                            <div class="payment-info">
                                                <h4>E-Wallet</h4>
                                                <p>GoPay, OVO, Dana, ShopeePay</p>
                                            </div>
                                        </div>
                                    </label>
                                </div>
                            </div>
                            
                            <!-- Order Notes -->
                            <div class="checkout-section">
                                <h2 class="section-title">
                                    <i class="fas fa-sticky-note"></i> Catatan Pesanan
                                </h2>
                                <div class="form-group">
                                    <label for="notes">Catatan untuk penjual (opsional)</label>
                                    <textarea id="notes" name="notes" rows="3" 
                                              class="form-control" 
                                              placeholder="Contoh: Kurangi gula, extra ice, dll."></textarea>
                                </div>
                            </div>
                        </div>
                        
                        <!-- Right Column - Order Summary -->
                        <div class="checkout-right">
                            <div class="order-summary">
                                <h2 class="summary-title">Ringkasan Pesanan</h2>
                                
                                <div class="order-items">
                                    <?php foreach ($cartItems as $item): ?>
                                        <div class="order-item">
                                            <div class="order-item-info">
                                                <h4><?= $item['nama'] ?></h4>
                                                <span><?= Helper::formatRupiah($item['price_snapshot']) ?> × <?= $item['qty'] ?></span>
                                            </div>
                                            <span class="order-item-total">
                                                <?= Helper::formatRupiah($item['price_snapshot'] * $item['qty']) ?>
                                            </span>
                                        </div>
                                    <?php endforeach; ?>
                                </div>
                                
                                <div class="order-summary-details">
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
                                        <span>Total Pembayaran</span>
                                        <span><?= Helper::formatRupiah($cartTotal + 15000) ?></span>
                                    </div>
                                </div>
                                
                                <div class="order-terms">
                                    <label class="checkbox">
                                        <input type="checkbox" required>
                                        <span>Saya menyetujui <a href="#">Syarat & Ketentuan</a> dan <a href="#">Kebijakan Privasi</a></span>
                                    </label>
                                </div>
                                
                                <button type="submit" class="btn btn-primary btn-large btn-block">
                                    <i class="fas fa-lock"></i> Buat Pesanan
                                </button>
                                
                                <p class="order-note">
                                    <i class="fas fa-info-circle"></i>
                                    Pesanan akan diproses setelah pembayaran dikonfirmasi.
                                </p>
                            </div>
                        </div>
                    </div>
                </form>
            </div>
        </div>
    </section>
    
    <?php include 'includes/footer.php'; ?>
    
    <script>
        // Auto-save form data
        const form = document.querySelector('.checkout-form');
        const inputs = form.querySelectorAll('input, textarea, select');
        
        inputs.forEach(input => {
            // Load saved data
            const saved = localStorage.getItem(`checkout_${input.name}`);
            if (saved && input.type !== 'radio' && input.type !== 'checkbox') {
                input.value = saved;
            }
            
            if (input.type === 'radio' && saved === input.value) {
                input.checked = true;
            }
            
            // Save on change
            input.addEventListener('change', function() {
                if (this.type === 'radio' && this.checked) {
                    localStorage.setItem(`checkout_${this.name}`, this.value);
                } else if (this.type !== 'radio' && this.type !== 'checkbox') {
                    localStorage.setItem(`checkout_${this.name}`, this.value);
                }
            });
        });
        
        // Form submission
        form.addEventListener('submit', function(e) {
            const submitBtn = this.querySelector('button[type="submit"]');
            submitBtn.innerHTML = '<i class="fas fa-spinner fa-spin"></i> Memproses...';
            submitBtn.disabled = true;
            
            // Clear saved data
            inputs.forEach(input => {
                localStorage.removeItem(`checkout_${input.name}`);
            });
        });
    </script>
</body>
</html>