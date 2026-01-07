<?php
require_once '../includes/config.php';
require_once '../includes/functions.php';
require_once '../app/models/Product.php';

$productModel = new Product();

// Get filter parameters
$categorySlug = $_GET['category'] ?? null;
$search = $_GET['search'] ?? null;
$page = max(1, intval($_GET['page'] ?? 1));
$limit = 12;
$offset = ($page - 1) * $limit;

// Get products with pagination
if ($categorySlug || $search) {
    $products = $productModel->getAllProducts($categorySlug, $search);
    $totalProducts = count($products);
    $products = array_slice($products, $offset, $limit);
} else {
    // For all products with pagination (simplified)
    $products = $productModel->getAllProducts();
    $totalProducts = count($products);
    $products = array_slice($products, $offset, $limit);
}

$categories = $productModel->getCategories();
$totalPages = ceil($totalProducts / $limit);

// Page title
if ($categorySlug) {
    $category = array_filter($categories, fn($cat) => $cat['slug'] === $categorySlug);
    $categoryName = !empty($category) ? current($category)['nama'] : 'Kategori';
    $pageTitle = "Menu $categoryName - " . SITE_NAME;
} elseif ($search) {
    $pageTitle = "Pencarian: $search - " . SITE_NAME;
} else {
    $pageTitle = "Semua Menu - " . SITE_NAME;
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
</head>
<body>
    <?php include 'includes/header.php'; ?>
    
    <!-- Shop Header -->
    <section class="page-header">
        <div class="container">
            <div class="page-header-content">
                <h1 class="page-title">Menu House Cafe</h1>
                <p class="page-subtitle">Nikmati berbagai pilihan kopi spesial dan cemilan lezat</p>
                
                <!-- Search and Filter -->
                <div class="shop-controls">
                    <form method="GET" action="" class="search-form">
                        <div class="search-box">
                            <i class="fas fa-search"></i>
                            <input type="text" name="search" placeholder="Cari menu..." 
                                   value="<?= $search ?>">
                            <?php if ($categorySlug): ?>
                                <input type="hidden" name="category" value="<?= $categorySlug ?>">
                            <?php endif; ?>
                        </div>
                        <button type="submit" class="btn btn-primary">Cari</button>
                    </form>
                    
                    <div class="category-filters">
                        <a href="<?= BASE_URL ?>/shop.php" 
                           class="category-filter <?= !$categorySlug ? 'active' : '' ?>">
                            Semua
                        </a>
                        <?php foreach ($categories as $cat): ?>
                            <a href="<?= BASE_URL ?>/shop.php?category=<?= $cat['slug'] ?>" 
                               class="category-filter <?= $categorySlug === $cat['slug'] ? 'active' : '' ?>">
                                <?= $cat['nama'] ?>
                            </a>
                        <?php endforeach; ?>
                    </div>
                </div>
            </div>
        </div>
    </section>
    
    <!-- Products Grid -->
    <section class="section">
        <div class="container">
            <?php if (empty($products)): ?>
                <div class="empty-state">
                    <i class="fas fa-search"></i>
                    <h3>Menu tidak ditemukan</h3>
                    <p>Coba kata kunci lain atau lihat semua menu</p>
                    <a href="<?= BASE_URL ?>/shop.php" class="btn btn-primary">Lihat Semua Menu</a>
                </div>
            <?php else: ?>
                <div class="products-grid">
                    <?php foreach ($products as $product): ?>
                        <div class="product-card">
                            <div class="product-image">
                                <img src="<?= BASE_URL ?>/public/uploads/<?= $product['gambar'] ?: 'default.jpg' ?>" 
                                     alt="<?= $product['nama'] ?>">
                                <span class="product-category"><?= $product['category_name'] ?></span>
                                <?php if ($product['stok'] <= 0): ?>
                                    <div class="product-badge out-of-stock">Habis</div>
                                <?php endif; ?>
                            </div>
                            <div class="product-info">
                                <h3 class="product-name">
                                    <a href="<?= BASE_URL ?>/product.php?slug=<?= $product['slug'] ?>">
                                        <?= $product['nama'] ?>
                                    </a>
                                </h3>
                                <p class="product-desc"><?= substr($product['deskripsi'], 0, 80) ?>...</p>
                                <div class="product-footer">
                                    <div>
                                        <span class="product-price"><?= Helper::formatRupiah($product['harga']) ?></span>
                                        <?php if ($product['stok'] > 0): ?>
                                            <small class="product-stock">Stok: <?= $product['stok'] ?></small>
                                        <?php endif; ?>
                                    </div>
                                    <div class="product-actions">
                                        <a href="<?= BASE_URL ?>/product.php?slug=<?= $product['slug'] ?>" 
                                           class="btn btn-sm btn-outline">
                                            <i class="fas fa-eye"></i>
                                        </a>
                                        <?php if ($product['stok'] > 0 && Helper::isLoggedIn()): ?>
                                            <form method="POST" action="/cart.php" class="d-inline">
                                                <input type="hidden" name="action" value="add">
                                                <input type="hidden" name="product_id" value="<?= $product['id'] ?>">
                                                <input type="hidden" name="quantity" value="1">
                                                <input type="hidden" name="csrf_token" 
                                                       value="<?= Helper::generateCsrfToken() ?>">
                                                <button type="submit" class="btn btn-sm btn-primary">
                                                    <i class="fas fa-cart-plus"></i>
                                                </button>
                                            </form>
                                        <?php endif; ?>
                                    </div>
                                </div>
                            </div>
                        </div>
                    <?php endforeach; ?>
                </div>
                
                <!-- Pagination -->
                <?php if ($totalPages > 1): ?>
                    <div class="pagination">
                        <?php if ($page > 1): ?>
                            <a href="?page=<?= $page-1 ?><?= $categorySlug ? "&category=$categorySlug" : '' ?><?= $search ? "&search=$search" : '' ?>" 
                               class="pagination-link">
                                <i class="fas fa-chevron-left"></i> Sebelumnya
                            </a>
                        <?php endif; ?>
                        
                        <div class="pagination-numbers">
                            <?php for ($i = 1; $i <= $totalPages; $i++): ?>
                                <?php if ($i == $page): ?>
                                    <span class="pagination-number active"><?= $i ?></span>
                                <?php elseif (abs($i - $page) <= 2 || $i == 1 || $i == $totalPages): ?>
                                    <a href="?page=<?= $i ?><?= $categorySlug ? "&category=$categorySlug" : '' ?><?= $search ? "&search=$search" : '' ?>" 
                                       class="pagination-number">
                                        <?= $i ?>
                                    </a>
                                <?php elseif (abs($i - $page) == 3): ?>
                                    <span class="pagination-dots">...</span>
                                <?php endif; ?>
                            <?php endfor; ?>
                        </div>
                        
                        <?php if ($page < $totalPages): ?>
                            <a href="?page=<?= $page+1 ?><?= $categorySlug ? "&category=$categorySlug" : '' ?><?= $search ? "&search=$search" : '' ?>" 
                               class="pagination-link">
                                Selanjutnya <i class="fas fa-chevron-right"></i>
                            </a>
                        <?php endif; ?>
                    </div>
                <?php endif; ?>
            <?php endif; ?>
        </div>
    </section>
    
    <?php include 'includes/footer.php'; ?>
</body>
</html>