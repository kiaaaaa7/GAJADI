<?php
require_once '../includes/config.php';
require_once '../includes/functions.php';
require_once '../app/models/Order.php';
require_once '../app/models/Product.php';

Middleware::requireAdmin();

$orderModel = new Order();
$productModel = new Product();

// Get statistics
$stats = $orderModel->getOrderStats();
$recentOrders = $orderModel->getAllOrders(null, 5);
$lowStockProducts = $productModel->getLowStockProducts(5);

$pageTitle = "Dashboard Admin - " . SITE_NAME;
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
        .admin-container {
            display: flex;
            min-height: 100vh;
            background-color: var(--light-gray);
        }
        
        .admin-sidebar {
            width: 250px;
            background-color: var(--dark);
            color: white;
            padding: 2rem 0;
            position: fixed;
            height: 100vh;
            overflow-y: auto;
        }
        
        .admin-logo {
            padding: 0 2rem;
            margin-bottom: 2rem;
        }
        
        .admin-logo a {
            display: flex;
            align-items: center;
            gap: 12px;
            color: white;
            text-decoration: none;
            font-size: 1.5rem;
            font-weight: 600;
        }
        
        .admin-logo i {
            color: var(--primary);
        }
        
        .admin-menu {
            list-style: none;
        }
        
        .admin-menu li a {
            display: flex;
            align-items: center;
            gap: 12px;
            padding: 1rem 2rem;
            color: var(--gray);
            text-decoration: none;
            transition: all var(--transition-fast);
            border-left: 3px solid transparent;
        }
        
        .admin-menu li a:hover,
        .admin-menu li a.active {
            background-color: rgba(255, 255, 255, 0.1);
            color: white;
            border-left-color: var(--primary);
        }
        
        .admin-menu li a i {
            width: 20px;
            text-align: center;
        }
        
        .admin-content {
            flex: 1;
            margin-left: 250px;
            padding: 2rem;
        }
        
        .admin-header {
            display: flex;
            justify-content: space-between;
            align-items: center;
            margin-bottom: 2rem;
            padding-bottom: 1rem;
            border-bottom: 2px solid var(--light-gray);
        }
        
        .admin-header h1 {
            margin: 0;
            font-size: 1.75rem;
        }
        
        .user-menu {
            display: flex;
            align-items: center;
            gap: 1rem;
        }
        
        .stats-grid {
            display: grid;
            grid-template-columns: repeat(auto-fit, minmax(240px, 1fr));
            gap: 1.5rem;
            margin-bottom: 2rem;
        }
        
        .stat-card {
            background: white;
            border-radius: var(--radius-lg);
            padding: 1.5rem;
            box-shadow: var(--shadow-md);
            display: flex;
            align-items: center;
            gap: 1rem;
        }
        
        .stat-icon {
            width: 60px;
            height: 60px;
            border-radius: 50%;
            display: flex;
            align-items: center;
            justify-content: center;
            font-size: 1.5rem;
        }
        
        .stat-icon.orders { background-color: #DBEAFE; color: var(--info); }
        .stat-icon.revenue { background-color: #D1FAE5; color: var(--success); }
        .stat-icon.pending { background-color: #FEF3C7; color: var(--warning); }
        .stat-icon.products { background-color: #E0E7FF; color: var(--primary); }
        
        .stat-info h3 {
            margin: 0 0 0.25rem 0;
            font-size: 0.875rem;
            color: var(--dark-gray);
            text-transform: uppercase;
            letter-spacing: 0.05em;
        }
        
        .stat-value {
            font-size: 1.5rem;
            font-weight: 700;
            color: var(--dark);
            margin: 0;
        }
        
        .admin-section {
            background: white;
            border-radius: var(--radius-lg);
            padding: 1.5rem;
            margin-bottom: 1.5rem;
            box-shadow: var(--shadow-md);
        }
        
        .section-header {
            display: flex;
            justify-content: space-between;
            align-items: center;
            margin-bottom: 1.5rem;
        }
        
        .section-header h2 {
            margin: 0;
            font-size: 1.25rem;
        }
        
        .table {
            width: 100%;
            border-collapse: collapse;
        }
        
        .table th {
            text-align: left;
            padding: 1rem;
            background-color: var(--light-gray);
            color: var(--dark-gray);
            font-weight: 600;
            text-transform: uppercase;
            font-size: 0.75rem;
            letter-spacing: 0.05em;
        }
        
        .table td {
            padding: 1rem;
            border-bottom: 1px solid var(--light-gray);
        }
        
        .table tr:hover {
            background-color: var(--light);
        }
        
        .status-badge {
            display: inline-block;
            padding: 0.25rem 0.75rem;
            border-radius: var(--radius-full);
            font-size: 0.75rem;
            font-weight: 600;
            text-transform: uppercase;
        }
        
        .status-pending { background-color: var(--warning-light); color: var(--warning-dark); }
        .status-confirmed { background-color: var(--info-light); color: var(--info); }
        .status-preparing { background-color: var(--info-light); color: var(--info); }
        .status-ready { background-color: var(--success-light); color: var(--success-dark); }
        .status-completed { background-color: var(--success-light); color: var(--success-dark); }
        .status-cancelled { background-color: var(--error-light); color: var(--error-dark); }
        
        .btn-icon {
            padding: 0.5rem;
            border-radius: var(--radius-md);
            border: none;
            background: none;
            cursor: pointer;
            transition: all var(--transition-fast);
        }
        
        .btn-icon:hover {
            background-color: var(--light-gray);
        }
        
        .btn-icon.edit { color: var(--info); }
        .btn-icon.delete { color: var(--error); }
        .btn-icon.view { color: var(--primary); }
        
        .empty-table {
            text-align: center;
            padding: 3rem;
            color: var(--dark-gray);
        }
        
        .empty-table i {
            font-size: 3rem;
            margin-bottom: 1rem;
            color: var(--gray);
        }
        
        @media (max-width: 1024px) {
            .admin-sidebar {
                width: 200px;
            }
            
            .admin-content {
                margin-left: 200px;
            }
        }
        
        @media (max-width: 768px) {
            .admin-container {
                flex-direction: column;
            }
            
            .admin-sidebar {
                width: 100%;
                height: auto;
                position: static;
                padding: 1rem 0;
            }
            
            .admin-content {
                margin-left: 0;
                padding: 1rem;
            }
            
            .admin-menu {
                display: flex;
                overflow-x: auto;
                padding: 0 1rem;
            }
            
            .admin-menu li {
                flex-shrink: 0;
            }
            
            .admin-menu li a {
                padding: 0.5rem 1rem;
                border-left: none;
                border-bottom: 3px solid transparent;
            }
            
            .admin-menu li a:hover,
            .admin-menu li a.active {
                border-left: none;
                border-bottom-color: var(--primary);
            }
            
            .stats-grid {
                grid-template-columns: 1fr;
            }
        }
    </style>
</head>
<body>
    <div class="admin-container">
        <!-- Sidebar -->
        <aside class="admin-sidebar">
            <div class="admin-logo">
                <a href="<?= BASE_URL ?>/admin/">
                    <i class="fas fa-mug-hot"></i>
                    <span>House Cafe</span>
                </a>
            </div>
            
            <ul class="admin-menu">
                <li><a href="<?= BASE_URL ?>/admin/" class="active">
                    <i class="fas fa-tachometer-alt"></i>
                    <span>Dashboard</span>
                </a></li>
                <li><a href="<?= BASE_URL ?>/admin/products.php">
                    <i class="fas fa-coffee"></i>
                    <span>Produk</span>
                </a></li>
                <li><a href="<?= BASE_URL ?>/admin/orders.php">
                    <i class="fas fa-shopping-bag"></i>
                    <span>Pesanan</span>
                </a></li>
                <li><a href="<?= BASE_URL ?>/admin/categories.php">
                    <i class="fas fa-tags"></i>
                    <span>Kategori</span>
                </a></li>
                <li><a href="<?= BASE_URL ?>/admin/users.php">
                    <i class="fas fa-users"></i>
                    <span>Pengguna</span>
                </a></li>
                <li><a href="<?= BASE_URL ?>/admin/settings.php">
                    <i class="fas fa-cog"></i>
                    <span>Pengaturan</span>
                </a></li>
                <li><a href="<?= BASE_URL ?>/">
                    <i class="fas fa-home"></i>
                    <span>Kembali ke Toko</span>
                </a></li>
                <li><a href="<?= BASE_URL ?>/logout.php">
                    <i class="fas fa-sign-out-alt"></i>
                    <span>Logout</span>
                </a></li>
            </ul>
        </aside>
        
        <!-- Main Content -->
        <main class="admin-content">
            <header class="admin-header">
                <h1>Dashboard Admin</h1>
                <div class="user-menu">
                    <span>Halo, <?= $_SESSION['user_name'] ?></span>
                    <div class="dropdown">
                        <button class="btn btn-sm btn-outline">
                            <i class="fas fa-user-circle"></i>
                        </button>
                        <div class="dropdown-content">
                            <a href="<?= BASE_URL ?>/profile.php">Profile</a>
                            <a href="<?= BASE_URL ?>/">Toko</a>
                            <a href="<?= BASE_URL ?>/logout.php">Logout</a>
                        </div>
                    </div>
                </div>
            </header>
            
            <!-- Stats Cards -->
            <div class="stats-grid">
                <div class="stat-card">
                    <div class="stat-icon orders">
                        <i class="fas fa-shopping-bag"></i>
                    </div>
                    <div class="stat-info">
                        <h3>Total Pesanan</h3>
                        <p class="stat-value"><?= $stats['total_orders'] ?></p>
                    </div>
                </div>
                
                <div class="stat-card">
                    <div class="stat-icon revenue">
                        <i class="fas fa-chart-line"></i>
                    </div>
                    <div class="stat-info">
                        <h3>Total Pendapatan</h3>
                        <p class="stat-value"><?= Helper::formatRupiah($stats['total_revenue']) ?></p>
                    </div>
                </div>
                
                <div class="stat-card">
                    <div class="stat-icon pending">
                        <i class="fas fa-clock"></i>
                    </div>
                    <div class="stat-info">
                        <h3>Pesanan Pending</h3>
                        <p class="stat-value"><?= $stats['pending_orders'] ?></p>
                    </div>
                </div>
                
                <div class="stat-card">
                    <div class="stat-icon products">
                        <i class="fas fa-coffee"></i>
                    </div>
                    <div class="stat-info">
                        <h3>Pesanan Hari Ini</h3>
                        <p class="stat-value"><?= $stats['today_orders'] ?></p>
                    </div>
                </div>
            </div>
            
            <!-- Recent Orders -->
            <div class="admin-section">
                <div class="section-header">
                    <h2>Pesanan Terbaru</h2>
                    <a href="<?= BASE_URL ?>/admin/orders.php" class="btn btn-sm btn-primary">
                        Lihat Semua <i class="fas fa-arrow-right"></i>
                    </a>
                </div>
                
                <?php if (empty($recentOrders)): ?>
                    <div class="empty-table">
                        <i class="fas fa-shopping-bag"></i>
                        <p>Belum ada pesanan</p>
                    </div>
                <?php else: ?>
                    <div class="table-responsive">
                        <table class="table">
                            <thead>
                                <tr>
                                    <th>Kode Pesanan</th>
                                    <th>Pelanggan</th>
                                    <th>Tanggal</th>
                                    <th>Total</th>
                                    <th>Status</th>
                                    <th>Aksi</th>
                                </tr>
                            </thead>
                            <tbody>
                                <?php foreach ($recentOrders as $order): ?>
                                    <tr>
                                        <td><?= $order['order_code'] ?></td>
                                        <td><?= $order['user_name'] ?></td>
                                        <td><?= date('d/m/Y', strtotime($order['created_at'])) ?></td>
                                        <td><?= Helper::formatRupiah($order['total']) ?></td>
                                        <td>
                                            <span class="status-badge status-<?= $order['status'] ?>">
                                                <?= ucfirst($order['status']) ?>
                                            </span>
                                        </td>
                                        <td>
                                            <a href="<?= BASE_URL ?>/admin/orders.php?action=view&id=<?= $order['id'] ?>" 
                                               class="btn-icon view" title="Lihat Detail">
                                                <i class="fas fa-eye"></i>
                                            </a>
                                            <a href="<?= BASE_URL ?>/admin/orders.php?action=edit&id=<?= $order['id'] ?>" 
                                               class="btn-icon edit" title="Edit Status">
                                                <i class="fas fa-edit"></i>
                                            </a>
                                        </td>
                                    </tr>
                                <?php endforeach; ?>
                            </tbody>
                        </table>
                    </div>
                <?php endif; ?>
            </div>
            
            <!-- Low Stock Products -->
            <div class="admin-section">
                <div class="section-header">
                    <h2>Produk Stok Sedikit</h2>
                    <a href="<?= BASE_URL ?>/admin/products.php" class="btn btn-sm btn-primary">
                        Lihat Semua <i class="fas fa-arrow-right"></i>
                    </a>
                </div>
                
                <?php if (empty($lowStockProducts)): ?>
                    <div class="empty-table">
                        <i class="fas fa-check-circle"></i>
                        <p>Semua stok produk mencukupi</p>
                    </div>
                <?php else: ?>
                    <div class="table-responsive">
                        <table class="table">
                            <thead>
                                <tr>
                                    <th>Produk</th>
                                    <th>Kategori</th>
                                    <th>Stok Tersedia</th>
                                    <th>Harga</th>
                                    <th>Aksi</th>
                                </tr>
                            </thead>
                            <tbody>
                                <?php foreach ($lowStockProducts as $product): ?>
                                    <tr>
                                        <td>
                                            <div class="product-info-small">
                                                <img src="<?= BASE_URL ?>/public/uploads/<?= $product['gambar'] ?: 'default.jpg' ?>" 
                                                     alt="<?= $product['nama'] ?>" width="40" height="40">
                                                <span><?= $product['nama'] ?></span>
                                            </div>
                                        </td>
                                        <td><?= $product['category_name'] ?></td>
                                        <td>
                                            <span class="<?= $product['stok'] < 10 ? 'text-error' : 'text-warning' ?>">
                                                <?= $product['stok'] ?>
                                            </span>
                                        </td>
                                        <td><?= Helper::formatRupiah($product['harga']) ?></td>
                                        <td>
                                            <a href="<?= BASE_URL ?>/admin/products.php?action=edit&id=<?= $product['id'] ?>" 
                                               class="btn-icon edit" title="Edit Produk">
                                                <i class="fas fa-edit"></i>
                                            </a>
                                        </td>
                                    </tr>
                                <?php endforeach; ?>
                            </tbody>
                        </table>
                    </div>
                <?php endif; ?>
            </div>
        </main>
    </div>
    
    <script>
        // Admin specific JavaScript
        document.addEventListener('DOMContentLoaded', function() {
            // Table row click
            const tableRows = document.querySelectorAll('.table tbody tr');
            tableRows.forEach(row => {
                row.addEventListener('click', function(e) {
                    if (!e.target.closest('a') && !e.target.closest('button')) {
                        const viewLink = this.querySelector('a.view');
                        if (viewLink) {
                            window.location.href = viewLink.href;
                        }
                    }
                });
            });
            
            // Confirm delete actions
            const deleteButtons = document.querySelectorAll('.btn-icon.delete');
            deleteButtons.forEach(button => {
                button.addEventListener('click', function(e) {
                    e.preventDefault();
                    if (confirm('Apakah Anda yakin ingin menghapus item ini?')) {
                        window.location.href = this.href;
                    }
                });
            });
            
            // Auto-refresh dashboard every 30 seconds
            setTimeout(() => {
                window.location.reload();
            }, 30000);
        });
    </script>
</body>
</html>