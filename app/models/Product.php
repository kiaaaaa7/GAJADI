<?php
class Product {
    private $db;
    
    public function __construct() {
        $this->db = Database::getInstance();
    }
    
    // Get all products with category
    public function getAllProducts($categorySlug = null, $search = null) {
        $sql = "SELECT p.*, c.nama as category_name, c.slug as category_slug 
                FROM products p 
                JOIN categories c ON p.category_id = c.id 
                WHERE p.is_active = 1";
        
        $params = [];
        
        if ($categorySlug) {
            $sql .= " AND c.slug = ?";
            $params[] = $categorySlug;
        }
        
        if ($search) {
            $sql .= " AND (p.nama LIKE ? OR p.deskripsi LIKE ?)";
            $params[] = "%$search%";
            $params[] = "%$search%";
        }
        
        $sql .= " ORDER BY p.created_at DESC";
        
        return $this->db->fetchAll($sql, $params);
    }
    
    // Get product by ID
    public function getProductById($id) {
        return $this->db->fetch(
            "SELECT p.*, c.nama as category_name 
             FROM products p 
             JOIN categories c ON p.category_id = c.id 
             WHERE p.id = ?",
            [$id]
        );
    }
    
    // Get product by slug
    public function getProductBySlug($slug) {
        return $this->db->fetch(
            "SELECT p.*, c.nama as category_name 
             FROM products p 
             JOIN categories c ON p.category_id = c.id 
             WHERE p.slug = ? AND p.is_active = 1",
            [$slug]
        );
    }
    
    // Get all categories
    public function getCategories() {
        return $this->db->fetchAll(
            "SELECT * FROM categories ORDER BY nama"
        );
    }
    
    // Get featured products
    public function getFeaturedProducts($limit = 6) {
        return $this->db->fetchAll(
            "SELECT p.*, c.nama as category_name 
             FROM products p 
             JOIN categories c ON p.category_id = c.id 
             WHERE p.is_active = 1 
             ORDER BY RAND() 
             LIMIT ?",
            [$limit]
        );
    }
    
    // Get products by category
    public function getProductsByCategory($categoryId) {
        return $this->db->fetchAll(
            "SELECT * FROM products 
             WHERE category_id = ? AND is_active = 1 
             ORDER BY nama",
            [$categoryId]
        );
    }
    
    // Add new product (admin)
    public function addProduct($data) {
        return $this->db->execute(
            "INSERT INTO products (category_id, nama, slug, deskripsi, harga, stok, gambar) 
             VALUES (?, ?, ?, ?, ?, ?, ?)",
            [
                $data['category_id'],
                $data['nama'],
                $data['slug'],
                $data['deskripsi'],
                $data['harga'],
                $data['stok'],
                $data['gambar']
            ]
        );
    }
    
    // Update product (admin)
    public function updateProduct($id, $data) {
        $sql = "UPDATE products SET 
                category_id = ?, 
                nama = ?, 
                slug = ?, 
                deskripsi = ?, 
                harga = ?, 
                stok = ?, 
                is_active = ?";
        
        $params = [
            $data['category_id'],
            $data['nama'],
            $data['slug'],
            $data['deskripsi'],
            $data['harga'],
            $data['stok'],
            $data['is_active']
        ];
        
        if (!empty($data['gambar'])) {
            $sql .= ", gambar = ?";
            $params[] = $data['gambar'];
        }
        
        $sql .= " WHERE id = ?";
        $params[] = $id;
        
        return $this->db->execute($sql, $params);
    }
    
    // Delete product (admin)
    public function deleteProduct($id) {
        return $this->db->execute(
            "DELETE FROM products WHERE id = ?",
            [$id]
        );
    }
    // Tambahkan method berikut ke class Product yang sudah ada:

// Get all products for admin (including inactive)
public function getAllProductsAdmin() {
    return $this->db->fetchAll(
        "SELECT p.*, c.nama as category_name 
         FROM products p 
         JOIN categories c ON p.category_id = c.id 
         ORDER BY p.created_at DESC"
    );
}

// Get products with low stock
public function getLowStockProducts($limit = 10, $threshold = 20) {
    return $this->db->fetchAll(
        "SELECT p.*, c.nama as category_name 
         FROM products p 
         JOIN categories c ON p.category_id = c.id 
         WHERE p.stok <= ? AND p.stok > 0 
         ORDER BY p.stok ASC 
         LIMIT ?",
        [$threshold, $limit]
    );
}

// Check if slug exists
public function slugExists($slug, $excludeId = null) {
    $sql = "SELECT COUNT(*) as count FROM products WHERE slug = ?";
    $params = [$slug];
    
    if ($excludeId) {
        $sql .= " AND id != ?";
        $params[] = $excludeId;
    }
    
    $result = $this->db->fetch($sql, $params);
    return $result['count'] > 0;
}

// Generate slug from name
public function generateSlug($nama) {
    $slug = strtolower($nama);
    $slug = preg_replace('/[^a-z0-9]+/', '-', $slug);
    $slug = trim($slug, '-');
    
    // Check if slug exists
    $counter = 1;
    $originalSlug = $slug;
    
    while ($this->slugExists($slug)) {
        $slug = $originalSlug . '-' . $counter;
        $counter++;
    }
    
    return $slug;
}
    // Update product stock
    public function updateStock($productId, $quantity) {
        return $this->db->execute(
            "UPDATE products SET stok = stok - ? 
             WHERE id = ? AND stok >= ?",
            [$quantity, $productId, $quantity]
        );
    }
}
?>
