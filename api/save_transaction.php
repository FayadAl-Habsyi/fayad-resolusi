<?php
/**
 * API to save transaction
 */
require_once '../config/config.php';

// Check if request method is POST
if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    http_response_json(['success' => false, 'message' => 'Method not allowed'], 405);
}

// Check if user is logged in
if (!is_logged_in()) {
    http_response_json(['success' => false, 'message' => 'Unauthorized'], 401);
}

// Get JSON data from request body
$json_data = file_get_contents('php://input');
$data = json_decode($json_data, true);

// Validate data
if (!$data || !isset($data['items']) || empty($data['items'])) {
    http_response_json(['success' => false, 'message' => 'Invalid data']);
}

// Start transaction
$conn = db_connect();
mysqli_autocommit($conn, false);

try {
    // Get current user ID
    $user_id = $_SESSION['user_id'];
    
    // Generate invoice number
    $invoice_number = generate_invoice_number();
    
    // Prepare transaction data
    $customer_id = isset($data['customer_id']) && !empty($data['customer_id']) ? $data['customer_id'] : null;
    $subtotal = $data['subtotal'] ?? 0;
    $discount = $data['discount'] ?? 0;
    $tax = $data['tax'] ?? 0;
    $total = $data['total'] ?? 0;
    $cash = $data['cash'] ?? 0;
    $change = $data['change'] ?? 0;
    $note = $data['note'] ?? '';
    $items = $data['items'] ?? [];
    
    // Insert transaction
    $query = "INSERT INTO transaksi (no_invoice, id_pelanggan, id_user, tanggal, subtotal, diskon, pajak, total, bayar, kembali, catatan, status) 
              VALUES (?, ?, ?, NOW(), ?, ?, ?, ?, ?, ?, ?, 'selesai')";
    
    $stmt = mysqli_prepare($conn, $query);
    mysqli_stmt_bind_param($stmt, 'siiiiiiiss', $invoice_number, $customer_id, $user_id, $subtotal, $discount, $tax, $total, $cash, $change, $note);
    
    if (!mysqli_stmt_execute($stmt)) {
        throw new Exception("Error saving transaction: " . mysqli_error($conn));
    }
    
    // Get transaction ID
    $transaction_id = mysqli_insert_id($conn);
    
    // Insert transaction details and update product stock
    foreach ($items as $item) {
        $product_id = $item['id'];
        $quantity = $item['quantity'];
        $price = $item['price'];
        $item_total = $item['total'];
        
        // Insert transaction detail
        $query = "INSERT INTO detail_transaksi (id_transaksi, id_produk, jumlah, harga, total) VALUES (?, ?, ?, ?, ?)";
        $stmt = mysqli_prepare($conn, $query);
        mysqli_stmt_bind_param($stmt, 'iiiii', $transaction_id, $product_id, $quantity, $price, $item_total);
        
        if (!mysqli_stmt_execute($stmt)) {
            throw new Exception("Error saving transaction detail: " . mysqli_error($conn));
        }
        
        // Update product stock
        $query = "UPDATE produk SET stok = stok - ? WHERE id = ?";
        $stmt = mysqli_prepare($conn, $query);
        mysqli_stmt_bind_param($stmt, 'ii', $quantity, $product_id);
        
        if (!mysqli_stmt_execute($stmt)) {
            throw new Exception("Error updating product stock: " . mysqli_error($conn));
        }
    }
    
    // Commit transaction
    mysqli_commit($conn);
    
    // Return success response
    http_response_json([
        'success' => true, 
        'message' => 'Transaction saved successfully', 
        'transaction_id' => $transaction_id,
        'invoice_number' => $invoice_number
    ]);
    
} catch (Exception $e) {
    // Rollback transaction
    mysqli_rollback($conn);
    
    // Return error response
    http_response_json(['success' => false, 'message' => $e->getMessage()]);
} finally {
    // Restore autocommit
    mysqli_autocommit($conn, true);
}