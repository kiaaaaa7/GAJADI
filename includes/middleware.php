<?php
require_once 'functions.php';

class Middleware {
    
    // Require authentication
    public static function requireAuth() {
        if (!Helper::isLoggedIn()) {
            Helper::redirect('/login.php', 'error', 'Silakan login terlebih dahulu.');
        }
    }
    
    // Require admin access
    public static function requireAdmin() {
        self::requireAuth();
        if (!Helper::isAdmin()) {
            Helper::redirect('/', 'error', 'Akses ditolak. Hanya admin yang dapat mengakses halaman ini.');
        }
    }
    
    // Prevent logged in users from accessing auth pages
    public static function preventIfLoggedIn() {
        if (Helper::isLoggedIn()) {
            Helper::redirect('/', 'info', 'Anda sudah login.');
        }
    }
    
    // Check if request is POST
    public static function requirePost() {
        if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
            Helper::redirect('/', 'error', 'Method tidak diizinkan.');
        }
    }
    
    // Validate CSRF token
    public static function validateCsrf() {
        $token = $_POST['csrf_token'] ?? '';
        if (!Helper::validateCsrfToken($token)) {
            Helper::redirect('/', 'error', 'Token CSRF tidak valid.');
        }
    }
}
?>