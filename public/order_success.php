<?php
require_once '../includes/config.php';
require_once '../includes/functions.php';
require_once '../app/models/Order.php';

Middleware::requireAuth();

$orderId = $_GET['order_id'] ?? 0;
$orderModel = new Order();

// Get order details
$order = $orderModel->getOrderDetails($orderId, Helper::getUserId());

if (!$order) {
    Helper::redirect('/', 'error', 'Pesanan tidak ditemukan.');
}

$orderItems = $orderModel->getOrderItems($orderId);

$pageTitle = "Pesanan Berhasil - " . SITE_NAME;
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
    
    <!-- Success Section -->
    <section class="section">
        <div class="container">
            <div class="success-container">
                <div class="success-icon">
                    <i class="fas fa-check-circle"></i>
                </div>
                
                <h1 class="success-title">Pesanan Berhasil!</h1>
                <p class="success-subtitle">Terima kasih telah berbelanja di House Cafe</p>
                
                <div class="order-details-card">
                    <div class="order-header">
                        <h2>Detail Pesanan</h2>
                        <span class="order-status <?= $order['status'] ?>">
                            <?= ucfirst($order['status']) ?>
                        </span>
                    </div>
                    
                    <div class="order-info">
                        <div class="info-row">
                            <span>Kode Pesanan</span>
                            <strong><?= $order['order_code'] ?></strong>
                        </div>
                        <div class="info-row">
                            <span>Tanggal Pesanan</span>
                            <strong><?= date('d/m/Y H:i', strtotime($order['created_at'])) ?></strong>
                        </div>
                        <div class="info-row">
                            <span>Metode Pembayaran</span>
                            <strong><?= $order['payment_method'] ?></strong>
                        </div>
                        <div class="info-row">
                            <span>Total Pembayaran</span>
                            <strong class="order-total"><?= Helper::formatRupiah($order['total'] + 15000) ?></strong>
                        </div>
                    </div>
                    
                    <div class="order-items">
                        <h3>Item Pesanan</h3>
                        <?php foreach ($orderItems as $item): ?>
                            <div class="order-item">
                                <div class="order-item-image">
                                    <img src="<?= BASE_URL ?>/public/uploads/<?= $item['gambar'] ?: 'default.jpg' ?>" 
                                         alt="<?= $item['product_name'] ?>">
                                </div>
                                <div class="order-item-info">
                                    <h4><?= $item['product_name'] ?></h4>
                                    <p><?= Helper::formatRupiah($item['harga']) ?> × <?= $item['qty'] ?></p>
                                </div>
                                <div class="order-item-total">
                                    <?= Helper::formatRupiah($item['subtotal']) ?>
                                </div>
                            </div>
                        <?php endforeach; ?>
                        
                        <div class="order-summary">
                            <div class="summary-row">
                                <span>Subtotal</span>
                                <span><?= Helper::formatRupiah($order['total']) ?></span>
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
                                <span><?= Helper::formatRupiah($order['total'] + 15000) ?></span>
                            </div>
                        </div>
                    </div>
                </div>
                
                <div class="success-actions">
                    <a href="<?= BASE_URL ?>/orders.php" class="btn btn-primary">
                        <i class="fas fa-list"></i> Lihat Semua Pesanan
                    </a>
                    <a href="<?= BASE_URL ?>/" class="btn btn-outline">
                        <i class="fas fa-home"></i> Kembali ke Beranda
                    </a>
                </div>
                
                <div class="success-info">
                    <div class="info-card">
                        <i class="fas fa-clock"></i>
                        <h4>Estimasi Waktu</h4>
                        <p>Pesanan akan siap dalam 15-30 menit</p>
                    </div>
                    <div class="info-card">
                        <i class="fas fa-phone"></i>
                        <h4>Butuh Bantuan?</h4>
                        <p>Hubungi: (021) 1234-5678</p>
                    </div>
                    <div class="info-card">
                        <i class="fas fa-envelope"></i>
                        <h4>Email Konfirmasi</h4>
                        <p>Telah dikirim ke <?= $order['email'] ?></p>
                    </div>
                </div>
            </div>
        </div>
    </section>
    
    <?php include 'includes/footer.php'; ?>
</body>
</html>