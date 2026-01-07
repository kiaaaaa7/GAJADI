<?php
require_once '../includes/config.php';
require_once '../includes/functions.php';
require_once '../app/models/Order.php';
require_once '../app/models/User.php';

Middleware::requireAdmin();

$orderModel = new Order();
$userModel = new User();

// Handle actions
$action = $_GET['action'] ?? 'list';
$id = $_GET['id'] ?? 0;
$status = $_GET['status'] ?? null;

// CSRF Token
$csrfToken = Helper::generateCsrfToken();

// Handle POST requests
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    Middleware::validateCsrf();
    
    $postAction = $_POST['action'] ?? '';
    
    switch ($postAction) {
        case 'update_status':
            $orderId = intval($_POST['order_id']);
            $newStatus = Helper::sanitize($_POST['status']);
            
            if ($orderModel->updateOrderStatus($orderId, $newStatus)) {
                Helper::redirect('/admin/orders.php', 'success', 'Status pesanan berhasil diperbarui!');
            } else {
                Helper::redirect('/admin/orders.php', 'error', 'Gagal memperbarui status pesanan!');
            }
            break;
    }
}

// Get data based on action
switch ($action) {
    case 'view':
        $order = $orderModel->getOrderDetails($id);
        if (!$order) {
            Helper::redirect('/admin/orders.php', 'error', 'Pesanan tidak ditemukan!');
        }
        $orderItems = $orderModel->getOrderItems($id);
        $pageTitle = "Detail Pesanan - Admin";
        break;
        
    case 'edit':
        $order = $orderModel->getOrderDetails($id);
        if (!$order) {
            Helper::redirect('/admin/orders.php', 'error', 'Pesanan tidak ditemukan!');
        }
        $pageTitle = "Edit Status Pesanan - Admin";
        break;
        
    default:
        $orders = $orderModel->getAllOrders($status);
        $pageTitle = "Kelola Pesanan - Admin";
        break;
}
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
        /* ... (CSS sama seperti admin/products.php) ... */
        
        .order-status-badge {
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
        
        .order-detail-card {
            background: white;
            border-radius: var(--radius-lg);
            padding: 2rem;
            margin-bottom: 2rem;
            box-shadow: var(--shadow-md);
        }
        
        .order-detail-row {
            display: flex;
            justify-content: space-between;
            padding: 1rem 0;
            border-bottom: 1px solid var(--light-gray);
        }
        
        .order-detail-row:last-child {
            border-bottom: none;
        }
        
        .order-items-table {
            width: 100%;
            border-collapse: collapse;
        }
        
        .order-items-table th {
            background-color: var(--light-gray);
            padding: 1rem;
            text-align: left;
        }
        
        .order-items-table td {
            padding: 1rem;
            border-bottom: 1px solid var(--light-gray);
        }
        
        .filter-tabs {
            display: flex;
            gap: 0.5rem;
            margin-bottom: 1.5rem;
            flex-wrap: wrap;
        }
        
        .filter-tab {
            padding: 0.5rem 1rem;
            border-radius: var(--radius-md);
            background-color: white;
            color: var(--dark);
            text-decoration: none;
            border: 2px solid var(--light-gray);
            transition: all var(--transition-fast);
        }
        
        .filter-tab:hover {
            border-color: var(--primary);
            color: var(--primary);
        }
        
        .filter-tab.active {
            background-color: var(--primary);
            color: white;
            border-color: var(--primary);
        }
    </style>
</head>
<body>
    <div class="admin-container">
        <!-- Sidebar -->
        <?php include 'includes/sidebar.php'; ?>
        
        <!-- Main Content -->
        <main class="admin-content">
            <header class="admin-header">
                <h1><?= $pageTitle ?></h1>
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
            
            <?php if ($action === 'view'): ?>
                <!-- Order Detail View -->
                <div class="order-detail-card">
                    <div class="section-header">
                        <h2>Detail Pesanan #<?= $order['order_code'] ?></h2>
                        <div>
                            <span class="order-status-badge status-<?= $order['status'] ?>">
                                <?= ucfirst($order['status']) ?>
                            </span>
                            <a href="<?= BASE_URL ?>/admin/orders.php?action=edit&id=<?= $order['id'] ?>" 
                               class="btn btn-sm btn-primary ml-3">
                                <i class="fas fa-edit"></i> Edit Status
                            </a>
                        </div>
                    </div>
                    
                    <div class="order-detail-row">
                        <span>Tanggal Pesanan</span>
                        <strong><?= date('d/m/Y H:i', strtotime($order['created_at'])) ?></strong>
                    </div>
                    
                    <div class="order-detail-row">
                        <span>Pelanggan</span>
                        <div class="text-right">
                            <strong><?= $order['user_name'] ?></strong><br>
                            <small><?= $order['email'] ?></small><br>
                            <small><?= $order['phone'] ?? 'Belum diisi' ?></small>
                        </div>
                    </div>
                    
                    <div class="order-detail-row">
                        <span>Alamat Pengiriman</span>
                        <span><?= nl2br($order['alamat'] ?? 'Belum diisi') ?></span>
                    </div>
                    
                    <div class="order-detail-row">
                        <span>Metode Pembayaran</span>
                        <strong><?= $order['payment_method'] ?? 'Belum dipilih' ?></strong>
                    </div>
                    
                    <div class="order-detail-row">
                        <span>Total Pesanan</span>
                        <strong class="text-primary" style="font-size: 1.25rem;">
                            <?= Helper::formatRupiah($order['total']) ?>
                        </strong>
                    </div>
                </div>
                
                <!-- Order Items -->
                <div class="admin-section">
                    <h2>Item Pesanan</h2>
                    <table class="order-items-table">
                        <thead>
                            <tr>
                                <th>Produk</th>
                                <th>Harga</th>
                                <th>Qty</th>
                                <th>Subtotal</th>
                            </tr>
                        </thead>
                        <tbody>
                            <?php foreach ($orderItems as $item): ?>
                                <tr>
                                    <td>
                                        <div class="d-flex align-items-center">
                                            <?php if ($item['gambar']): ?>
                                                <img src="<?= BASE_URL ?>/public/uploads/<?= $item['gambar'] ?>" 
                                                     alt="<?= $item['product_name'] ?>" 
                                                     style="width: 50px; height: 50px; object-fit: cover; 
                                                     border-radius: var(--radius-md); margin-right: 1rem;">
                                            <?php endif; ?>
                                            <span><?= $item['product_name'] ?></span>
                                        </div>
                                    </td>
                                    <td><?= Helper::formatRupiah($item['harga']) ?></td>
                                    <td><?= $item['qty'] ?></td>
                                    <td><?= Helper::formatRupiah($item['subtotal']) ?></td>
                                </tr>
                            <?php endforeach; ?>
                            <tr>
                                <td colspan="3" class="text-right"><strong>Total</strong></td>
                                <td><strong><?= Helper::formatRupiah($order['total']) ?></strong></td>
                            </tr>
                        </tbody>
                    </table>
                </div>
                
                <div class="form-actions">
                    <a href="<?= BASE_URL ?>/admin/orders.php" class="btn btn-outline">
                        <i class="fas fa-arrow-left"></i> Kembali
                    </a>
                </div>
                
            <?php elseif ($action === 'edit'): ?>
                <!-- Edit Status Form -->
                <div class="form-container">
                    <form method="POST">
                        <input type="hidden" name="csrf_token" value="<?= $csrfToken ?>">
                        <input type="hidden" name="action" value="update_status">
                        <input type="hidden" name="order_id" value="<?= $order['id'] ?>">
                        
                        <div class="form-group">
                            <label>Kode Pesanan</label>
                            <input type="text" class="form-control" value="<?= $order['order_code'] ?>" readonly>
                        </div>
                        
                        <div class="form-group">
                            <label>Pelanggan</label>
                            <input type="text" class="form-control" value="<?= $order['user_name'] ?>" readonly>
                        </div>
                        
                        <div class="form-group">
                            <label>Status Saat Ini</label>
                            <input type="text" class="form-control" 
                                   value="<?= ucfirst($order['status']) ?>" readonly>
                        </div>
                        
                        <div class="form-group">
                            <label for="status">Status Baru *</label>
                            <select id="status" name="status" class="form-control" required>
                                <option value="pending" <?= $order['status'] === 'pending' ? 'selected' : '' ?>>Pending</option>
                                <option value="confirmed" <?= $order['status'] === 'confirmed' ? 'selected' : '' ?>>Confirmed</option>
                                <option value="preparing" <?= $order['status'] === 'preparing' ? 'selected' : '' ?>>Preparing</option>
                                <option value="ready" <?= $order['status'] === 'ready' ? 'selected' : '' ?>>Ready</option>
                                <option value="completed" <?= $order['status'] === 'completed' ? 'selected' : '' ?>>Completed</option>
                                <option value="cancelled" <?= $order['status'] === 'cancelled' ? 'selected' : '' ?>>Cancelled</option>
                            </select>
                        </div>
                        
                        <div class="form-actions">
                            <button type="submit" class="btn btn-primary">
                                <i class="fas fa-save"></i> Update Status
                            </button>
                            <a href="<?= BASE_URL ?>/admin/orders.php" class="btn btn-outline">
                                <i class="fas fa-times"></i> Batal
                            </a>
                        </div>
                    </form>
                </div>
                
            <?php else: ?>
                <!-- Order List -->
                <div class="filter-tabs">
                    <a href="<?= BASE_URL ?>/admin/orders.php" 
                       class="filter-tab <?= !$status ? 'active' : '' ?>">
                        Semua
                    </a>
                    <a href="<?= BASE_URL ?>/admin/orders.php?status=pending" 
                       class="filter-tab <?= $status === 'pending' ? 'active' : '' ?>">
                        Pending
                    </a>
                    <a href="<?= BASE_URL ?>/admin/orders.php?status=confirmed" 
                       class="filter-tab <?= $status === 'confirmed' ? 'active' : '' ?>">
                        Confirmed
                    </a>
                    <a href="<?= BASE_URL ?>/admin/orders.php?status=preparing" 
                       class="filter-tab <?= $status === 'preparing' ? 'active' : '' ?>">
                        Preparing
                    </a>
                    <a href="<?= BASE_URL ?>/admin/orders.php?status=ready" 
                       class="filter-tab <?= $status === 'ready' ? 'active' : '' ?>">
                        Ready
                    </a>
                    <a href="<?= BASE_URL ?>/admin/orders.php?status=completed" 
                       class="filter-tab <?= $status === 'completed' ? 'active' : '' ?>">
                        Completed
                    </a>
                    <a href="<?= BASE_URL ?>/admin/orders.php?status=cancelled" 
                       class="filter-tab <?= $status === 'cancelled' ? 'active' : '' ?>">
                        Cancelled
                    </a>
                </div>
                
                <div class="admin-section">
                    <?php if (empty($orders)): ?>
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
                                        <th>Items</th>
                                        <th>Total</th>
                                        <th>Status</th>
                                        <th>Aksi</th>
                                    </tr>
                                </thead>
                                <tbody>
                                    <?php foreach ($orders as $order): ?>
                                        <tr>
                                            <td><?= $order['order_code'] ?></td>
                                            <td><?= $order['user_name'] ?></td>
                                            <td><?= date('d/m/Y', strtotime($order['created_at'])) ?></td>
                                            <td><?= $order['item_count'] ?></td>
                                            <td><?= Helper::formatRupiah($order['total']) ?></td>
                                            <td>
                                                <span class="order-status-badge status-<?= $order['status'] ?>">
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
            <?php endif; ?>
        </main>
    </div>
</body>
</html>