<?php
/**
 * Batalkan Transaksi
 */
require_once '../config/config.php';

// Check if user is logged in and is admin
if (!is_logged_in() || !is_admin()) {
    set_flash_message('error', 'Anda tidak memiliki akses untuk membatalkan transaksi');
    redirect(base_url('penjualan/'));
}

// Check if ID is provided
if (!isset($_GET['id']) || empty($_GET['id'])) {
    set_flash_message('error', 'ID transaksi tidak valid');
    redirect(base_url('penjualan/'));
}

$id = (int)$_GET['id'];

// Get transaction data
$transaction = get_row("SELECT * FROM transaksi WHERE id = $id");

if (!$transaction) {
    set_flash_message('error', 'Transaksi tidak ditemukan');
    redirect(base_url('penjualan/'));
}

// Check if transaction is already canceled
if ($transaction['status'] === 'batal') {
    set_flash_message('error', 'Transaksi sudah dibatalkan sebelumnya');
    redirect(base_url('penjualan/detail.php?id=' . $id));
}

// Start transaction
$conn = db_connect();
mysqli_autocommit($conn, false);

try {
    // Update transaction status
    $query = "UPDATE transaksi SET status = 'batal' WHERE id = $id";
    if (!mysqli_query($conn, $query)) {
        throw new Exception("Error updating transaction: " . mysqli_error($conn));
    }
    
    // Get transaction details
    $details = query("SELECT * FROM detail_transaksi WHERE id_transaksi = $id");
    
    // Restore product stock
    foreach ($details as $detail) {
        $product_id = $detail['id_produk'];
        $quantity = $detail['jumlah'];
        
        $query = "UPDATE produk SET stok = stok + $quantity WHERE id = $product_id";
        if (!mysqli_query($conn, $query)) {
            throw new Exception("Error restoring product stock: " . mysqli_error($conn));
        }
    }
    
    // Commit transaction
    mysqli_commit($conn);
    
    // Set success message
    set_flash_message('success', 'Transaksi berhasil dibatalkan dan stok produk telah dikembalikan');
    
} catch (Exception $e) {
    // Rollback transaction
    mysqli_rollback($conn);
    
    // Set error message
    set_flash_message('error', 'Gagal membatalkan transaksi: ' . $e->getMessage());
} finally {
    // Restore autocommit
    mysqli_autocommit($conn, true);
}

// Redirect back to transaction detail
redirect(base_url('penjualan/detail.php?id=' . $id));