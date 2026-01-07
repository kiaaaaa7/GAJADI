<?php
class Cart {
    private $db;
    
    public function __construct() {
        $this->db = Database::getInstance();
    }
    
    // Add item to cart
    public function addItem($userId, $productId, $quantity = 1) {
        $cart = Helper::getUserCart($userId);
        $product = $this->db->fetch(
            "SELECT * FROM products WHERE id = ? AND is_active = 1 AND stok > 0",
            [$productId]
        );
        
        if (!$product) {
            return ['error' => 'Produk tidak ditemukan atau stok habis'];
        }
        
        // Check if item already in cart
        $existing = $this->db->fetch(
            "SELECT * FROM cart_items 
             WHERE cart_id = ? AND product_id = ?",
            [$cart['id'], $productId]
        );
        
        if ($existing) {
            // Update quantity
            $newQty = $existing['qty'] + $quantity;
            if ($newQty > $product['stok']) {
                return ['error' => 'Stok tidak mencukupi'];
            }
            
            $this->db->execute(
                "UPDATE cart_items SET qty = ? 
                 WHERE cart_id = ? AND product_id = ?",
                [$newQty, $cart['id'], $productId]
            );
        } else {
            // Add new item
            $this->db->execute(
                "INSERT INTO cart_items (cart_id, product_id, qty, price_snapshot) 
                 VALUES (?, ?, ?, ?)",
                [$cart['id'], $productId, $quantity, $product['harga']]
            );
        }
        
        return ['success' => true];
    }
    
    // Get cart items with product details
    public function getCartItems($userId) {
        $cart = Helper::getUserCart($userId);
        
        return $this->db->fetchAll(
            "SELECT ci.*, p.nama, p.deskripsi, p.gambar, p.stok 
             FROM cart_items ci 
             JOIN products p ON ci.product_id = p.id 
             WHERE ci.cart_id = ? AND p.is_active = 1",
            [$cart['id']]
        );
    }
    
    // Update cart item quantity
    public function updateItemQuantity($userId, $itemId, $quantity) {
        $cart = Helper::getUserCart($userId);
        
        if ($quantity <= 0) {
            return $this->removeItem($userId, $itemId);
        }
        
        // Check stock
        $item = $this->db->fetch(
            "SELECT ci.*, p.stok 
             FROM cart_items ci 
             JOIN products p ON ci.product_id = p.id 
             WHERE ci.id = ? AND ci.cart_id = ?",
            [$itemId, $cart['id']]
        );
        
        if (!$item) {
            return ['error' => 'Item tidak ditemukan'];
        }
        
        if ($quantity > $item['stok']) {
            return ['error' => 'Stok tidak mencukupi'];
        }
        
        $this->db->execute(
            "UPDATE cart_items SET qty = ? 
             WHERE id = ? AND cart_id = ?",
            [$quantity, $itemId, $cart['id']]
        );
        
        return ['success' => true];
    }
    
    // Remove item from cart
    public function removeItem($userId, $itemId) {
        $cart = Helper::getUserCart($userId);
        
        $this->db->execute(
            "DELETE FROM cart_items 
             WHERE id = ? AND cart_id = ?",
            [$itemId, $cart['id']]
        );
        
        return ['success' => true];
    }
    
    // Clear cart
    public function clearCart($userId) {
        $cart = Helper::getUserCart($userId);
        
        $this->db->execute(
            "DELETE FROM cart_items WHERE cart_id = ?",
            [$cart['id']]
        );
        
        return ['success' => true];
    }
    
    // Get cart total
    public function getCartTotal($userId) {
        $items = $this->getCartItems($userId);
        $total = 0;
        
        foreach ($items as $item) {
            $total += $item['price_snapshot'] * $item['qty'];
        }
        
        return $total;
    }
}
?>