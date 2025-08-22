<?php
/**
 * Konfigurasi Aplikasi
 * File ini berisi konfigurasi umum untuk aplikasi
 */

// Mulai session
session_start();

// Zona waktu
date_default_timezone_set('Asia/Jakarta');

// Base URL - Sesuaikan dengan URL aplikasi Anda
define('BASE_URL', 'http://localhost/tampilan_kasir/');

// Path direktori
define('ROOT_PATH', dirname(__DIR__) . DIRECTORY_SEPARATOR);
define('ASSETS_PATH', ROOT_PATH . 'assets' . DIRECTORY_SEPARATOR);
define('UPLOAD_PATH', ASSETS_PATH . 'uploads' . DIRECTORY_SEPARATOR);

// Include file database
require_once ROOT_PATH . 'config/database.php';

// Include file helper
require_once ROOT_PATH . 'helpers/functions.php';

/**
 * Function untuk mengecek apakah user sudah login
 * 
 * @return bool True jika user sudah login, false jika belum
 */
function is_logged_in() {
    return isset($_SESSION['user_id']);
}

/**
 * Function untuk mengecek apakah user adalah admin
 * 
 * @return bool True jika user adalah admin, false jika bukan
 */
function is_admin() {
    return isset($_SESSION['user_role']) && $_SESSION['user_role'] === 'admin';
}

/**
 * Function untuk redirect ke halaman tertentu
 * 
 * @param string $url URL tujuan
 * @return void
 */
function redirect($url) {
    header("Location: " . $url);
    exit;
}

/**
 * Function untuk menampilkan pesan flash
 * 
 * @param string $type Tipe pesan (success, danger, warning, info)
 * @param string $message Isi pesan
 * @return void
 */
function set_flash_message($type, $message) {
    $_SESSION['flash_message'] = [
        'type' => $type,
        'message' => $message
    ];
}

/**
 * Function untuk mendapatkan pesan flash
 * 
 * @return array|null Array berisi tipe dan isi pesan atau null jika tidak ada pesan
 */
function get_flash_message() {
    if (isset($_SESSION['flash_message'])) {
        $flash = $_SESSION['flash_message'];
        unset($_SESSION['flash_message']);
        return $flash;
    }
    return null;
}

/**
 * Function untuk mendapatkan pengaturan aplikasi dari database
 * 
 * @return array Pengaturan aplikasi
 */
function get_settings() {
    static $settings = null;
    
    if ($settings === null) {
        $result = get_row("SELECT * FROM pengaturan WHERE id = 1");
        $settings = $result ?: [];
    }
    
    return $settings;
}

/**
 * Function untuk format angka ke format mata uang
 * 
 * @param float $number Angka yang akan diformat
 * @return string Angka dalam format mata uang
 */
function format_rupiah($number) {
    return 'Rp ' . number_format($number, 0, ',', '.');
}

/**
 * Function untuk mendapatkan tanggal dalam format Indonesia
 * 
 * @param string $date Tanggal dalam format Y-m-d
 * @return string Tanggal dalam format Indonesia (d-m-Y)
 */
function format_tanggal($date) {
    $timestamp = strtotime($date);
    $bulan = [
        '', 'Januari', 'Februari', 'Maret', 'April', 'Mei', 'Juni', 'Juli', 'Agustus', 'September', 'Oktober', 'November', 'Desember'
    ];
    
    $tanggal = date('j', $timestamp);
    $bulan_index = date('n', $timestamp);
    $tahun = date('Y', $timestamp);
    
    return $tanggal . ' ' . $bulan[$bulan_index] . ' ' . $tahun;
}

/**
 * Function untuk mendapatkan waktu dalam format Indonesia
 * 
 * @param string $datetime Tanggal dan waktu dalam format Y-m-d H:i:s
 * @return string Tanggal dan waktu dalam format Indonesia
 */
function format_datetime($datetime) {
    $timestamp = strtotime($datetime);
    return format_tanggal(date('Y-m-d', $timestamp)) . ' ' . date('H:i', $timestamp);
}