<?php
require_once '../includes/config.php';
require_once '../includes/functions.php';
require_once '../app/models/Order.php';

Middleware::requireAuth();

$orderModel = new Order();
$userId = Helper::getUserId();

$orders = $orderModel->getUserOrders($userId);

$pageTitle = "Pesanan Saya - " . SITE_NAME;
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
                <span>Pesanan Saya</span>
            </nav>
            
            <h1 class="page-title">Riwayat Pesanan</h1>
            
            <?php if (empty($orders)): ?>
                <div class="empty-state">
                    <i class="fas fa-shopping-bag"></i>
                    <h3>Belum ada pesanan</h3>
                    <p>Yuk, pesan menu favoritmu di House Cafe!</p>
                    <a href="<?= BASE_URL ?>/shop.php" class="btn btn-primary">Lihat Menu</a>
                </div>
            <?php else: ?>
                <div class="orders-list">
                    <?php foreach ($orders as $order): ?>
                        <div class="order-card">
                            <div class="order-header">
                                <div>
                                    <h3 class="order-code"><?= $order['order_code'] ?></h3>
                                    <p class="order-date">
                                        <i class="fas fa-calendar"></i> 
                                        <?= date('d F Y H:i', strtotime($order['created_at'])) ?>
                                    </p>
                                </div>
                                <div class="order-status-container">
                                    <span class="order-status <?= $order['status'] ?>">
                                        <?= ucfirst($order['status']) ?>
                                    </span>
                                    <span class="order-total"><?= Helper::formatRupiah($order['total']) ?></span>
                                </div>
                            </div>
                            
                            <div class="order-body">
                                <p><i class="fas fa-box"></i> <?= $order['item_count'] ?> item</p>
                                <p><i class="fas fa-credit-card"></i> <?= $order['payment_method'] ?? 'Belum dipilih' ?></p>
                            </div>
                            
                            <div class="order-actions">
                                <a href="<?= BASE_URL ?>/order_detail.php?id=<?= $order['id'] ?>" 
                                   class="btn btn-sm btn-outline">
                                    <i class="fas fa-eye"></i> Lihat Detail
                                </a>
                                <?php if ($order['status'] === 'pending'): ?>
                                    <a href="<?= BASE_URL ?>/checkout.php?order_id=<?= $order['id'] ?>" 
                                       class="btn btn-sm btn-primary">
                                        <i class="fas fa-shopping-cart"></i> Lanjutkan Pembayaran
                                    </a>
                                <?php endif; ?>
                            </div>
                        </div>
                    <?php endforeach; ?>
                </div>
            <?php endif; ?>
        </div>
    </section>
    
    <?php include 'includes/footer.php'; ?>
    
    <style>
        .orders-list {
            display: flex;
            flex-direction: column;
            gap: 1.5rem;
        }
        
        .order-card {
            background: white;
            border-radius: var(--radius-lg);
            padding: 1.5rem;
            box-shadow: var(--shadow-md);
            border-left: 4px solid var(--primary);
        }
        
        .order-header {
            display: flex;
            justify-content: space-between;
            align-items: flex-start;
            margin-bottom: 1rem;
            flex-wrap: wrap;
            gap: 1rem;
        }
        
        .order-code {
            margin: 0;
            font-size: 1.25rem;
            color: var(--dark);
        }
        
        .order-date {
            color: var(--dark-gray);
            font-size: 0.875rem;
            margin: 0.25rem 0 0 0;
        }
        
        .order-status-container {
            display: flex;
            flex-direction: column;
            align-items: flex-end;
            gap: 0.5rem;
        }
        
        .order-status {
            padding: 0.25rem 1rem;
            border-radius: var(--radius-full);
            font-size: 0.75rem;
            font-weight: 600;
            text-transform: uppercase;
        }
        
        .order-status.pending { background: var(--warning-light); color: var(--warning-dark); }
        .order-status.confirmed { background: var(--info-light); color: var(--info); }
        .order-status.preparing { background: var(--info-light); color: var(--info); }
        .order-status.ready { background: var(--success-light); color: var(--success-dark); }
        .order-status.completed { background: var(--success-light); color: var(--success-dark); }
        .order-status.cancelled { background: var(--error-light); color: var(--error-dark); }
        
        .order-total {
            font-size: 1.25rem;
            font-weight: 600;
            color: var(--primary);
        }
        
        .order-body {
            display: flex;
            gap: 2rem;
            margin-bottom: 1rem;
            color: var(--dark-gray);
        }
        
        .order-body i {
            margin-right: 0.5rem;
            width: 16px;
            text-align: center;
        }
        
        .order-actions {
            display: flex;
            gap: 0.75rem;
            justify-content: flex-end;
        }
        
        @media (max-width: 768px) {
            .order-header {
                flex-direction: column;
            }
            
            .order-status-container {
                align-items: flex-start;
            }
            
            .order-body {
                flex-direction: column;
                gap: 0.5rem;
            }
            
            .order-actions {
                justify-content: flex-start;
                flex-wrap: wrap;
            }
        }
    </style>
</body>
</html>