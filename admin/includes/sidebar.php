<?php
// Admin sidebar partial
?>
<aside class="admin-sidebar">
    <div class="admin-logo">
        <a href="<?= BASE_URL ?>/admin/">
            <i class="fas fa-mug-hot"></i>
            <span>House Cafe</span>
        </a>
    </div>
    
    <ul class="admin-menu">
        <li><a href="<?= BASE_URL ?>/admin/" 
               class="<?= basename($_SERVER['PHP_SELF']) == 'index.php' ? 'active' : '' ?>">
            <i class="fas fa-tachometer-alt"></i>
            <span>Dashboard</span>
        </a></li>
        <li><a href="<?= BASE_URL ?>/admin/products.php" 
               class="<?= basename($_SERVER['PHP_SELF']) == 'products.php' ? 'active' : '' ?>">
            <i class="fas fa-coffee"></i>
            <span>Produk</span>
        </a></li>
        <li><a href="<?= BASE_URL ?>/admin/orders.php" 
               class="<?= basename($_SERVER['PHP_SELF']) == 'orders.php' ? 'active' : '' ?>">
            <i class="fas fa-shopping-bag"></i>
            <span>Pesanan</span>
        </a></li>
        <li><a href="<?= BASE_URL ?>/admin/categories.php" 
               class="<?= basename($_SERVER['PHP_SELF']) == 'categories.php' ? 'active' : '' ?>">
            <i class="fas fa-tags"></i>
            <span>Kategori</span>
        </a></li>
        <li><a href="<?= BASE_URL ?>/admin/users.php" 
               class="<?= basename($_SERVER['PHP_SELF']) == 'users.php' ? 'active' : '' ?>">
            <i class="fas fa-users"></i>
            <span>Pengguna</span>
        </a></li>
        <li><a href="<?= BASE_URL ?>/admin/settings.php" 
               class="<?= basename($_SERVER['PHP_SELF']) == 'settings.php' ? 'active' : '' ?>">
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