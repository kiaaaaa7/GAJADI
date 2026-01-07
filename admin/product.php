<?php
require_once '../includes/config.php';
require_once '../includes/functions.php';
require_once '../app/models/Product.php';
require_once '../app/models/Category.php';

Middleware::requireAdmin();

$productModel = new Product();
$categoryModel = new Category();

// Handle actions
$action = $_GET['action'] ?? 'list';
$id = $_GET['id'] ?? 0;
$message = '';
$error = '';

// CSRF Token
$csrfToken = Helper::generateCsrfToken();

// Handle POST requests
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    Middleware::validateCsrf();
    
    $postAction = $_POST['action'] ?? '';
    
    switch ($postAction) {
        case 'add':
        case 'edit':
            $data = [
                'nama' => Helper::sanitize($_POST['nama']),
                'category_id' => intval($_POST['category_id']),
                'deskripsi' => Helper::sanitize($_POST['deskripsi']),
                'harga' => intval($_POST['harga']),
                'stok' => intval($_POST['stok']),
                'is_active' => isset($_POST['is_active']) ? 1 : 0
            ];
            
            // Generate slug
            $data['slug'] = $productModel->generateSlug($data['nama']);
            
            // Handle image upload
            if (!empty($_FILES['gambar']['name'])) {
                $upload = Helper::uploadImage($_FILES['gambar']);
                if (isset($upload['error'])) {
                    $error = $upload['error'];
                    break;
                }
                $data['gambar'] = $upload['filename'];
            } elseif ($postAction === 'edit' && empty($_POST['remove_image'])) {
                // Keep existing image
                $existing = $productModel->getProductById($id);
                $data['gambar'] = $existing['gambar'];
            } else {
                $data['gambar'] = null;
            }
            
            if ($postAction === 'add') {
                if ($productModel->addProduct($data)) {
                    Helper::redirect('/admin/products.php', 'success', 'Produk berhasil ditambahkan!');
                } else {
                    $error = 'Gagal menambahkan produk';
                }
            } else {
                if ($productModel->updateProduct($id, $data)) {
                    Helper::redirect('/admin/products.php', 'success', 'Produk berhasil diperbarui!');
                } else {
                    $error = 'Gagal memperbarui produk';
                }
            }
            break;
            
        case 'delete':
            if ($productModel->deleteProduct($id)) {
                Helper::redirect('/admin/products.php', 'success', 'Produk berhasil dihapus!');
            } else {
                $error = 'Gagal menghapus produk';
            }
            break;
    }
}

// Get data based on action
switch ($action) {
    case 'add':
        $categories = $categoryModel->getAllCategories();
        $pageTitle = "Tambah Produk - Admin";
        break;
        
    case 'edit':
        $product = $productModel->getProductById($id);
        if (!$product) {
            Helper::redirect('/admin/products.php', 'error', 'Produk tidak ditemukan!');
        }
        $categories = $categoryModel->getAllCategories();
        $pageTitle = "Edit Produk - Admin";
        break;
        
    case 'delete':
        if ($productModel->deleteProduct($id)) {
            Helper::redirect('/admin/products.php', 'success', 'Produk berhasil dihapus!');
        } else {
            Helper::redirect('/admin/products.php', 'error', 'Gagal menghapus produk!');
        }
        break;
        
    default:
        $products = $productModel->getAllProductsAdmin();
        $pageTitle = "Kelola Produk - Admin";
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
        
        .admin-content {
            flex: 1;
            margin-left: 250px;
            padding: 2rem;
        }
        
        /* ... (sisa CSS sama seperti di admin/index.php) ... */
        
        .form-container {
            max-width: 800px;
            margin: 0 auto;
            background: white;
            border-radius: var(--radius-lg);
            padding: 2rem;
            box-shadow: var(--shadow-md);
        }
        
        .image-preview {
            width: 200px;
            height: 200px;
            border: 2px dashed var(--gray);
            border-radius: var(--radius-md);
            display: flex;
            align-items: center;
            justify-content: center;
            overflow: hidden;
            margin-bottom: 1rem;
            background-color: var(--light);
        }
        
        .image-preview img {
            max-width: 100%;
            max-height: 100%;
            object-fit: cover;
        }
        
        .form-actions {
            display: flex;
            gap: 1rem;
            margin-top: 2rem;
            padding-top: 2rem;
            border-top: 2px solid var(--light-gray);
        }
        
        .switch {
            position: relative;
            display: inline-block;
            width: 60px;
            height: 34px;
        }
        
        .switch input {
            opacity: 0;
            width: 0;
            height: 0;
        }
        
        .slider {
            position: absolute;
            cursor: pointer;
            top: 0;
            left: 0;
            right: 0;
            bottom: 0;
            background-color: #ccc;
            transition: .4s;
            border-radius: 34px;
        }
        
        .slider:before {
            position: absolute;
            content: "";
            height: 26px;
            width: 26px;
            left: 4px;
            bottom: 4px;
            background-color: white;
            transition: .4s;
            border-radius: 50%;
        }
        
        input:checked + .slider {
            background-color: var(--primary);
        }
        
        input:checked + .slider:before {
            transform: translateX(26px);
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
            
            <?php if ($error): ?>
                <div class="alert alert-error">
                    <i class="fas fa-exclamation-circle"></i> <?= $error ?>
                </div>
            <?php endif; ?>
            
            <?php if ($action === 'add' || $action === 'edit'): ?>
                <!-- Add/Edit Form -->
                <div class="form-container">
                    <form method="POST" enctype="multipart/form-data">
                        <input type="hidden" name="csrf_token" value="<?= $csrfToken ?>">
                        <input type="hidden" name="action" value="<?= $action ?>">
                        
                        <div class="form-row">
                            <div class="form-group">
                                <label for="nama">Nama Produk *</label>
                                <input type="text" id="nama" name="nama" class="form-control" 
                                       value="<?= $product['nama'] ?? '' ?>" required>
                            </div>
                            
                            <div class="form-group">
                                <label for="category_id">Kategori *</label>
                                <select id="category_id" name="category_id" class="form-control" required>
                                    <option value="">Pilih Kategori</option>
                                    <?php foreach ($categories as $cat): ?>
                                        <option value="<?= $cat['id'] ?>" 
                                            <?= (isset($product['category_id']) && $product['category_id'] == $cat['id']) ? 'selected' : '' ?>>
                                            <?= $cat['nama'] ?>
                                        </option>
                                    <?php endforeach; ?>
                                </select>
                            </div>
                        </div>
                        
                        <div class="form-group">
                            <label for="deskripsi">Deskripsi *</label>
                            <textarea id="deskripsi" name="deskripsi" class="form-control" 
                                      rows="4" required><?= $product['deskripsi'] ?? '' ?></textarea>
                        </div>
                        
                        <div class="form-row">
                            <div class="form-group">
                                <label for="harga">Harga (Rp) *</label>
                                <input type="number" id="harga" name="harga" class="form-control" 
                                       min="0" step="500" value="<?= $product['harga'] ?? '' ?>" required>
                            </div>
                            
                            <div class="form-group">
                                <label for="stok">Stok *</label>
                                <input type="number" id="stok" name="stok" class="form-control" 
                                       min="0" value="<?= $product['stok'] ?? 0 ?>" required>
                            </div>
                        </div>
                        
                        <div class="form-group">
                            <label for="gambar">Gambar Produk</label>
                            <div class="image-preview" id="imagePreview">
                                <?php if (isset($product['gambar']) && $product['gambar']): ?>
                                    <img src="<?= BASE_URL ?>/public/uploads/<?= $product['gambar'] ?>" 
                                         alt="Preview">
                                <?php else: ?>
                                    <i class="fas fa-image" style="font-size: 3rem; color: var(--gray);"></i>
                                <?php endif; ?>
                            </div>
                            <input type="file" id="gambar" name="gambar" class="form-control" 
                                   accept="image/*" onchange="previewImage(event)">
                            <?php if (isset($product['gambar']) && $product['gambar']): ?>
                                <div class="form-check mt-2">
                                    <input type="checkbox" id="remove_image" name="remove_image" 
                                           class="form-check-input">
                                    <label for="remove_image" class="form-check-label">
                                        Hapus gambar yang ada
                                    </label>
                                </div>
                            <?php endif; ?>
                            <small class="form-text">Format: JPG, PNG, GIF. Maksimal 5MB.</small>
                        </div>
                        
                        <div class="form-group">
                            <label class="d-flex align-items-center">
                                <span class="mr-3">Status Aktif</span>
                                <label class="switch">
                                    <input type="checkbox" name="is_active" 
                                           <?= (!isset($product['is_active']) || $product['is_active'] == 1) ? 'checked' : '' ?>>
                                    <span class="slider"></span>
                                </label>
                            </label>
                        </div>
                        
                        <div class="form-actions">
                            <button type="submit" class="btn btn-primary">
                                <i class="fas fa-save"></i> Simpan
                            </button>
                            <a href="<?= BASE_URL ?>/admin/products.php" class="btn btn-outline">
                                <i class="fas fa-times"></i> Batal
                            </a>
                        </div>
                    </form>
                </div>
                
                <script>
                    function previewImage(event) {
                        const preview = document.getElementById('imagePreview');
                        const file = event.target.files[0];
                        
                        if (file) {
                            const reader = new FileReader();
                            reader.onload = function(e) {
                                preview.innerHTML = `<img src="${e.target.result}" alt="Preview">`;
                            }
                            reader.readAsDataURL(file);
                        } else {
                            preview.innerHTML = '<i class="fas fa-image" style="font-size: 3rem; color: var(--gray);"></i>';
                        }
                    }
                </script>
                
            <?php else: ?>
                <!-- Product List -->
                <div class="admin-section">
                    <div class="section-header">
                        <h2>Daftar Produk</h2>
                        <a href="<?= BASE_URL ?>/admin/products.php?action=add" class="btn btn-primary">
                            <i class="fas fa-plus"></i> Tambah Produk
                        </a>
                    </div>
                    
                    <?php if (empty($products)): ?>
                        <div class="empty-table">
                            <i class="fas fa-coffee"></i>
                            <p>Belum ada produk</p>
                        </div>
                    <?php else: ?>
                        <div class="table-responsive">
                            <table class="table">
                                <thead>
                                    <tr>
                                        <th>Gambar</th>
                                        <th>Nama Produk</th>
                                        <th>Kategori</th>
                                        <th>Harga</th>
                                        <th>Stok</th>
                                        <th>Status</th>
                                        <th>Aksi</th>
                                    </tr>
                                </thead>
                                <tbody>
                                    <?php foreach ($products as $product): ?>
                                        <tr>
                                            <td>
                                                <?php if ($product['gambar']): ?>
                                                    <img src="<?= BASE_URL ?>/public/uploads/<?= $product['gambar'] ?>" 
                                                         alt="<?= $product['nama'] ?>" 
                                                         style="width: 50px; height: 50px; object-fit: cover; border-radius: var(--radius-md);">
                                                <?php else: ?>
                                                    <div style="width: 50px; height: 50px; background: var(--light-gray); 
                                                         border-radius: var(--radius-md); display: flex; align-items: center; 
                                                         justify-content: center;">
                                                        <i class="fas fa-image text-gray"></i>
                                                    </div>
                                                <?php endif; ?>
                                            </td>
                                            <td><?= $product['nama'] ?></td>
                                            <td><?= $product['category_name'] ?></td>
                                            <td><?= Helper::formatRupiah($product['harga']) ?></td>
                                            <td>
                                                <span class="<?= $product['stok'] < 10 ? 'text-error' : '' ?>">
                                                    <?= $product['stok'] ?>
                                                </span>
                                            </td>
                                            <td>
                                                <?php if ($product['is_active']): ?>
                                                    <span class="status-badge status-active">Aktif</span>
                                                <?php else: ?>
                                                    <span class="status-badge status-inactive">Nonaktif</span>
                                                <?php endif; ?>
                                            </td>
                                            <td>
                                                <a href="<?= BASE_URL ?>/product.php?slug=<?= $product['slug'] ?>" 
                                                   class="btn-icon view" title="Lihat di Toko" target="_blank">
                                                    <i class="fas fa-eye"></i>
                                                </a>
                                                <a href="<?= BASE_URL ?>/admin/products.php?action=edit&id=<?= $product['id'] ?>" 
                                                   class="btn-icon edit" title="Edit">
                                                    <i class="fas fa-edit"></i>
                                                </a>
                                                <a href="<?= BASE_URL ?>/admin/products.php?action=delete&id=<?= $product['id'] ?>" 
                                                   class="btn-icon delete" title="Hapus" 
                                                   onclick="return confirm('Hapus produk ini?')">
                                                    <i class="fas fa-trash"></i>
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
    
    <script>
        // Konfirmasi sebelum menghapus
        document.addEventListener('DOMContentLoaded', function() {
            const deleteLinks = document.querySelectorAll('a.delete');
            deleteLinks.forEach(link => {
                link.addEventListener('click', function(e) {
                    if (!confirm('Apakah Anda yakin ingin menghapus produk ini?')) {
                        e.preventDefault();
                    }
                });
            });
        });
    </script>
</body>
</html>