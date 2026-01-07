<?php
require_once 'db.php';

class Helper {
    
    // Check if user is logged in
    public static function isLoggedIn() {
        return isset($_SESSION['user_id']);
    }
    
    // Check if user is admin
    public static function isAdmin() {
        return isset($_SESSION['role']) && $_SESSION['role'] === 'admin';
    }
    
    // Get current user ID
    public static function getUserId() {
        return $_SESSION['user_id'] ?? null;
    }
    
    // Sanitize input
    public static function sanitize($data) {
        if (is_array($data)) {
            return array_map([self::class, 'sanitize'], $data);
        }
        return htmlspecialchars(trim($data), ENT_QUOTES, 'UTF-8');
    }
    
    // Generate CSRF token
    public static function generateCsrfToken() {
        if (empty($_SESSION['csrf_token'])) {
            $_SESSION['csrf_token'] = bin2hex(random_bytes(32));
        }
        return $_SESSION['csrf_token'];
    }
    
    // Validate CSRF token
    public static function validateCsrfToken($token) {
        return isset($_SESSION['csrf_token']) && 
               hash_equals($_SESSION['csrf_token'], $token);
    }
    
    // Format currency
    public static function formatRupiah($amount) {
        return 'Rp ' . number_format($amount, 0, ',', '.');
    }
    
    // Generate order code
    public static function generateOrderCode() {
        return 'ORD-' . strtoupper(uniqid());
    }
    
    // Redirect with message
    public static function redirect($url, $type = null, $message = null) {
        if ($type && $message) {
            $_SESSION['flash'][$type] = $message;
        }
        header('Location: ' . BASE_URL . $url);
        exit();
    }
    
    // Get flash message
    public static function getFlash($type) {
        if (isset($_SESSION['flash'][$type])) {
            $message = $_SESSION['flash'][$type];
            unset($_SESSION['flash'][$type]);
            return $message;
        }
        return null;
    }
    
    // Upload image
    public static function uploadImage($file, $targetDir = UPLOAD_PATH) {
        if (!file_exists($targetDir)) {
            mkdir($targetDir, 0777, true);
        }
        
        $filename = uniqid() . '_' . basename($file['name']);
        $targetFile = $targetDir . $filename;
        
        $imageFileType = strtolower(pathinfo($targetFile, PATHINFO_EXTENSION));
        $allowedTypes = ['jpg', 'jpeg', 'png', 'gif', 'webp'];
        
        // Check if image file is actual image
        $check = getimagesize($file['tmp_name']);
        if ($check === false) {
            return ['error' => 'File is not an image.'];
        }
        
        // Check file size (5MB max)
        if ($file['size'] > 5000000) {
            return ['error' => 'File is too large. Max 5MB.'];
        }
        
        // Allow certain file formats
        if (!in_array($imageFileType, $allowedTypes)) {
            return ['error' => 'Only JPG, JPEG, PNG & GIF files are allowed.'];
        }
        
        if (move_uploaded_file($file['tmp_name'], $targetFile)) {
            return ['success' => true, 'filename' => $filename];
        } else {
            return ['error' => 'Failed to upload file.'];
        }
    }
    
    // Get user cart
    public static function getUserCart($userId) {
        $db = Database::getInstance();
        
        // Get or create cart
        $cart = $db->fetch(
            "SELECT * FROM carts WHERE user_id = ?",
            [$userId]
        );
        
        if (!$cart) {
            $db->execute(
                "INSERT INTO carts (user_id) VALUES (?)",
                [$userId]
            );
            $cartId = $db->lastInsertId();
            $cart = ['id' => $cartId, 'user_id' => $userId];
        }
        
        return $cart;
    }
    
    // Get cart items count
    public static function getCartItemCount($userId) {
        $db = Database::getInstance();
        $cart = self::getUserCart($userId);
        
        $result = $db->fetch(
            "SELECT SUM(qty) as total FROM cart_items WHERE cart_id = ?",
            [$cart['id']]
        );
        
        return $result['total'] ?? 0;
    }
}
?>