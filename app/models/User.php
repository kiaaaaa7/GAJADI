<?php
class User {
    private $db;
    
    public function __construct() {
        $this->db = Database::getInstance();
    }
    
    // Register new user
    public function register($data) {
        $hashedPassword = password_hash($data['password'], PASSWORD_DEFAULT);
        
        return $this->db->execute(
            "INSERT INTO users (nama, email, password, phone, role) 
             VALUES (?, ?, ?, ?, 'customer')",
            [$data['nama'], $data['email'], $hashedPassword, $data['phone']]
        );
    }
    
    // Login user
    public function login($email, $password) {
        $user = $this->db->fetch(
            "SELECT * FROM users WHERE email = ?",
            [$email]
        );
        
        if ($user && password_verify($password, $user['password'])) {
            unset($user['password']);
            return $user;
        }
        
        return false;
    }
    
    // Get user by ID
    public function getUserById($id) {
        return $this->db->fetch(
            "SELECT id, nama, email, phone, role, created_at 
             FROM users WHERE id = ?",
            [$id]
        );
    }
    
    // Update user profile
    public function updateProfile($userId, $data) {
        $sql = "UPDATE users SET nama = ?, phone = ?";
        $params = [$data['nama'], $data['phone']];
        
        if (!empty($data['password'])) {
            $sql .= ", password = ?";
            $params[] = password_hash($data['password'], PASSWORD_DEFAULT);
        }
        
        $sql .= " WHERE id = ?";
        $params[] = $userId;
        
        return $this->db->execute($sql, $params);
    }
    
    // Get all users (admin)
    public function getAllUsers() {
        return $this->db->fetchAll(
            "SELECT id, nama, email, phone, role, created_at 
             FROM users ORDER BY created_at DESC"
        );
    }
    
    // Check if email exists
    public function emailExists($email, $excludeId = null) {
        $sql = "SELECT COUNT(*) as count FROM users WHERE email = ?";
        $params = [$email];
        
        if ($excludeId) {
            $sql .= " AND id != ?";
            $params[] = $excludeId;
        }
        
        $result = $this->db->fetch($sql, $params);
        return $result['count'] > 0;
    }
}
?>