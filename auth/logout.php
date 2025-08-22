<?php
/**
 * Logout Page
 */
require_once '../config/config.php';

// Hapus token jika ada
if (isset($_COOKIE['remember_token']) && isset($_COOKIE['remember_user'])) {
    $user_id = $_COOKIE['remember_user'];
    $token = $_COOKIE['remember_token'];
    
    // Hapus token dari database
    query("DELETE FROM user_tokens WHERE user_id = '$user_id'");
    
    // Hapus cookie
    setcookie('remember_token', '', time() - 3600, '/', '', false, true);
    setcookie('remember_user', '', time() - 3600, '/', '', false, true);
}

// Hapus semua data session
session_unset();

// Hancurkan session
session_destroy();

// Set flash message
session_start();
set_flash_message('success', 'Logout berhasil. Sampai jumpa kembali!');

// Redirect ke halaman login
redirect(base_url('auth/login.php'));