<?php
class Order {
    private $db;
    
    public function __construct() {
        $this->db = Database::getInstance();
    }
    
    // Create new order
    public function createOrder($userId, $data, $cartItems) {
        try {
            $this->db->getConnection()->beginTransaction();
            
            // Generate order code
            $orderCode = Helper::generateOrderCode();
            
            // Calculate total
            $total = 0;
            foreach ($cartItems as $item) {
                $total += $item['price_snapshot'] * $item['qty'];
            }
            
            // Insert order
            $this->db->execute(
                "INSERT INTO orders (user_id, order_code, total, alamat, payment_method) 
                 VALUES (?, ?, ?, ?, ?)",
                [$userId, $orderCode, $total, $data['alamat'], $data['payment_method']]
            );
            
            $orderId = $this->db->lastInsertId();
            
            // Insert order items and update stock
            foreach ($cartItems as $item) {
                $subtotal = $item['price_snapshot'] * $item['qty'];
                
                $this->db->execute(
                    "INSERT INTO order_items (order_id, product_id, qty, harga, subtotal) 
                     VALUES (?, ?, ?, ?, ?)",
                    [$orderId, $item['product_id'], $item['qty'], $item['price_snapshot'], $subtotal]
                );
                
                // Update product stock
                $this->db->execute(
                    "UPDATE products SET stok = stok - ? WHERE id = ?",
                    [$item['qty'], $item['product_id']]
                );
            }
            
            // Clear cart
            $cart = Helper::getUserCart($userId);
            $this->db->execute(
                "DELETE FROM cart_items WHERE cart_id = ?",
                [$cart['id']]
            );
            
            $this->db->getConnection()->commit();
            return $orderId;
            
        } catch (Exception $e) {
            $this->db->getConnection()->rollBack();
            throw $e;
        }
    }
    
    // Get user orders
    public function getUserOrders($userId) {
        return $this->db->fetchAll(
            "SELECT o.*, 
                    (SELECT COUNT(*) FROM order_items oi WHERE oi.order_id = o.id) as item_count
             FROM orders o 
             WHERE user_id = ? 
             ORDER BY created_at DESC",
            [$userId]
        );
    }
    
    // Get order details
    public function getOrderDetails($orderId, $userId = null) {
        $sql = "SELECT o.*, u.nama as user_name, u.email, u.phone 
                FROM orders o 
                JOIN users u ON o.user_id = u.id 
                WHERE o.id = ?";
        
        $params = [$orderId];
        
        if ($userId && !Helper::isAdmin()) {
            $sql .= " AND o.user_id = ?";
            $params[] = $userId;
        }
        
        return $this->db->fetch($sql, $params);
    }
    
    // Get order items
    public function getOrderItems($orderId) {
        return $this->db->fetchAll(
            "SELECT oi.*, p.nama as product_name, p.gambar 
             FROM order_items oi 
             JOIN products p ON oi.product_id = p.id 
             WHERE oi.order_id = ?",
            [$orderId]
        );
    }
    
    // Get all orders (admin)
    public function getAllOrders($status = null) {
        $sql = "SELECT o.*, u.nama as user_name, u.email,
                       (SELECT COUNT(*) FROM order_items oi WHERE oi.order_id = o.id) as item_count
                FROM orders o 
                JOIN users u ON o.user_id = u.id";
        
        $params = [];
        
        if ($status) {
            $sql .= " WHERE o.status = ?";
            $params[] = $status;
        }
        
        $sql .= " ORDER BY o.created_at DESC";
        
        return $this->db->fetchAll($sql, $params);
    }
    
    // Update order status (admin)
    public function updateOrderStatus($orderId, $status) {
        return $this->db->execute(
            "UPDATE orders SET status = ?, updated_at = NOW() 
             WHERE id = ?",
            [$status, $orderId]
        );
    }
    
    // Get order statistics (admin)
    public function getOrderStats() {
        $today = date('Y-m-d');
        $month = date('Y-m');
        
        return [
            'total_orders' => $this->db->fetch("SELECT COUNT(*) as count FROM orders")['count'],
            'total_revenue' => $this->db->fetch("SELECT COALESCE(SUM(total), 0) as total FROM orders")['total'],
            'today_orders' => $this->db->fetch(
                "SELECT COUNT(*) as count FROM orders WHERE DATE(created_at) = ?",
                [$today]
            )['count'],
            'pending_orders' => $this->db->fetch(
                "SELECT COUNT(*) as count FROM orders WHERE status = 'pending'"
            )['count']
        ];
    }
}
?>