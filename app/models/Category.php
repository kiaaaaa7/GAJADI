<?php
class Category {
    private $db;
    
    public function __construct() {
        $this->db = Database::getInstance();
    }
    
    // Get all categories
    public function getAllCategories() {
        return $this->db->fetchAll(
            "SELECT * FROM categories ORDER BY nama"
        );
    }
    
    // Get category by ID
    public function getCategoryById($id) {
        return $this->db->fetch(
            "SELECT * FROM categories WHERE id = ?",
            [$id]
        );
    }
    
    // Get category by slug
    public function getCategoryBySlug($slug) {
        return $this->db->fetch(
            "SELECT * FROM categories WHERE slug = ?",
            [$slug]
        );
    }
    
    // Add new category
    public function addCategory($data) {
        return $this->db->execute(
            "INSERT INTO categories (nama, slug) VALUES (?, ?)",
            [$data['nama'], $data['slug']]
        );
    }
    
    // Update category
    public function updateCategory($id, $data) {
        return $this->db->execute(
            "UPDATE categories SET nama = ?, slug = ? WHERE id = ?",
            [$data['nama'], $data['slug'], $id]
        );
    }
    
    // Delete category
    public function deleteCategory($id) {
        // Check if category has products
        $products = $this->db->fetch(
            "SELECT COUNT(*) as count FROM products WHERE category_id = ?",
            [$id]
        );
        
        if ($products['count'] > 0) {
            return ['error' => 'Kategori tidak bisa dihapus karena masih memiliki produk'];
        }
        
        $this->db->execute(
            "DELETE FROM categories WHERE id = ?",
            [$id]
        );
        
        return ['success' => true];
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
    
    // Check if slug exists
    private function slugExists($slug, $excludeId = null) {
        $sql = "SELECT COUNT(*) as count FROM categories WHERE slug = ?";
        $params = [$slug];
        
        if ($excludeId) {
            $sql .= " AND id != ?";
            $params[] = $excludeId;
        }
        
        $result = $this->db->fetch($sql, $params);
        return $result['count'] > 0;
    }
    
    // Get category with product count
    public function getCategoriesWithCount() {
        return $this->db->fetchAll(
            "SELECT c.*, COUNT(p.id) as product_count 
             FROM categories c 
             LEFT JOIN products p ON c.id = p.category_id 
             GROUP BY c.id 
             ORDER BY c.nama"
        );
    }
}
?>