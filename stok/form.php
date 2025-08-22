<?php
/**
 * Form Stok Masuk (Stock Entry Form)
 */
require_once '../config/config.php';

// Check if user is logged in
if (!is_logged_in()) {
    redirect(base_url('auth/login.php'));
}

// Set active menu
$active_menu = 'stok';

// Set page title
$page_title = 'Tambah Stok Masuk';

// Get products
$query = "SELECT id, kode_produk, nama_produk, stok FROM produk WHERE status = 'aktif' ORDER BY nama_produk ASC";
$products = query($query);

// Process form submission
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $id_produk = isset($_POST['id_produk']) ? (int)$_POST['id_produk'] : 0;
    $jumlah = isset($_POST['jumlah']) ? (int)$_POST['jumlah'] : 0;
    $keterangan = isset($_POST['keterangan']) ? sanitize($_POST['keterangan']) : '';
    $tanggal = isset($_POST['tanggal']) ? sanitize($_POST['tanggal']) : date('Y-m-d H:i:s');
    
    // Validation
    $errors = [];
    
    if (empty($id_produk)) {
        $errors[] = 'Produk harus dipilih';
    }
    
    if (empty($jumlah) || $jumlah <= 0) {
        $errors[] = 'Jumlah harus lebih dari 0';
    }
    
    if (empty($tanggal)) {
        $errors[] = 'Tanggal harus diisi';
    }
    
    // If no errors, save stock entry
    if (empty($errors)) {
        // Start transaction
        query("START TRANSACTION");
        
        try {
            // Insert stock entry
            $insert_query = "INSERT INTO stok_masuk (id_produk, jumlah, keterangan, tanggal, id_user) 
                             VALUES (?, ?, ?, ?, ?)";
            $stmt = mysqli_prepare($GLOBALS['conn'], $insert_query);
            mysqli_stmt_bind_param($stmt, 'iissi', $id_produk, $jumlah, $keterangan, $tanggal, $_SESSION['user_id']);
            $insert_result = mysqli_stmt_execute($stmt);
            
            if (!$insert_result) {
                throw new Exception("Gagal menyimpan data stok masuk");
            }
            
            // Update product stock
            $update_query = "UPDATE produk SET stok = stok + ? WHERE id = ?";
            $stmt = mysqli_prepare($GLOBALS['conn'], $update_query);
            mysqli_stmt_bind_param($stmt, 'ii', $jumlah, $id_produk);
            $update_result = mysqli_stmt_execute($stmt);
            
            if (!$update_result) {
                throw new Exception("Gagal memperbarui stok produk");
            }
            
            // Commit transaction
            query("COMMIT");
            
            // Set success message and redirect
            set_flash_message('success', 'Stok masuk berhasil ditambahkan');
            redirect(base_url('stok/'));
        } catch (Exception $e) {
            // Rollback transaction on error
            query("ROLLBACK");
            $errors[] = $e->getMessage();
        }
    }
}

// Include header
include '../includes/header.php';
?>

<div class="row">
    <div class="col-lg-8 mx-auto">
        <div class="card mb-4">
            <div class="card-header d-flex justify-content-between align-items-center">
                <h5 class="mb-0">Form Tambah Stok Masuk</h5>
                <a href="<?= base_url('stok/') ?>" class="btn btn-secondary btn-sm">
                    <i class="fas fa-arrow-left me-1"></i> Kembali
                </a>
            </div>
            <div class="card-body">
                <?php if (!empty($errors)): ?>
                    <div class="alert alert-danger">
                        <ul class="mb-0">
                            <?php foreach ($errors as $error): ?>
                                <li><?= $error ?></li>
                            <?php endforeach; ?>
                        </ul>
                    </div>
                <?php endif; ?>
                
                <form action="" method="post">
                    <div class="mb-3">
                        <label for="id_produk" class="form-label">Produk <span class="text-danger">*</span></label>
                        <select name="id_produk" id="id_produk" class="form-control select2" required>
                            <option value="">Pilih Produk</option>
                            <?php foreach ($products as $product): ?>
                                <option value="<?= $product['id'] ?>" <?= isset($_POST['id_produk']) && $_POST['id_produk'] == $product['id'] ? 'selected' : '' ?>>
                                    <?= $product['kode_produk'] ?> - <?= $product['nama_produk'] ?> (Stok: <?= $product['stok'] ?>)
                                </option>
                            <?php endforeach; ?>
                        </select>
                    </div>
                    
                    <div class="mb-3">
                        <label for="jumlah" class="form-label">Jumlah <span class="text-danger">*</span></label>
                        <input type="number" class="form-control" id="jumlah" name="jumlah" min="1" value="<?= isset($_POST['jumlah']) ? $_POST['jumlah'] : '1' ?>" required>
                    </div>
                    
                    <div class="mb-3">
                        <label for="tanggal" class="form-label">Tanggal <span class="text-danger">*</span></label>
                        <input type="datetime-local" class="form-control" id="tanggal" name="tanggal" value="<?= isset($_POST['tanggal']) ? $_POST['tanggal'] : date('Y-m-d\TH:i') ?>" required>
                    </div>
                    
                    <div class="mb-3">
                        <label for="keterangan" class="form-label">Keterangan</label>
                        <textarea class="form-control" id="keterangan" name="keterangan" rows="3"><?= isset($_POST['keterangan']) ? $_POST['keterangan'] : '' ?></textarea>
                        <div class="form-text">Contoh: Pembelian dari supplier, Koreksi stok, dll.</div>
                    </div>
                    
                    <div class="text-end">
                        <button type="reset" class="btn btn-secondary me-2">Reset</button>
                        <button type="submit" class="btn btn-primary">Simpan</button>
                    </div>
                </form>
            </div>
        </div>
    </div>
</div>

<?php
// Include footer
include '../includes/footer.php';
?>