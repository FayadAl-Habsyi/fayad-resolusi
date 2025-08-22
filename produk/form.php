<?php
/**
 * Produk Form (Add/Edit)
 */
require_once '../config/config.php';

// Check if user is logged in
if (!is_logged_in()) {
    redirect(base_url('auth/login.php'));
}

// Set active menu
$active_menu = 'produk';

// Check if edit mode
$is_edit = isset($_GET['id']) && !empty($_GET['id']);
$id = $is_edit ? (int)$_GET['id'] : 0;

// Set page title
$page_title = $is_edit ? 'Edit Produk' : 'Tambah Produk';

// Get product data if in edit mode
$product = [];
if ($is_edit) {
    $product = get_row("SELECT * FROM produk WHERE id = $id");
    
    if (!$product) {
        set_flash_message('error', 'Produk tidak ditemukan');
        redirect(base_url('produk/'));
    }
}

// Get categories
$categories = query("SELECT * FROM kategori ORDER BY nama_kategori ASC");

// Process form submission
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    // Get form data
    $kode_produk = sanitize($_POST['kode_produk']);
    $nama_produk = sanitize($_POST['nama_produk']);
    $id_kategori = (int)$_POST['id_kategori'];
    $harga_beli = (int)str_replace('.', '', sanitize($_POST['harga_beli']));
    $harga_jual = (int)str_replace('.', '', sanitize($_POST['harga_jual']));
    $stok = (int)$_POST['stok'];
    $stok_min = (int)$_POST['stok_min'];
    $deskripsi = sanitize($_POST['deskripsi']);
    $status = isset($_POST['status']) ? 1 : 0;
    
    // Validate form data
    $errors = [];
    
    if (empty($kode_produk)) {
        $errors[] = 'Kode produk tidak boleh kosong';
    }
    
    if (empty($nama_produk)) {
        $errors[] = 'Nama produk tidak boleh kosong';
    }
    
    if ($harga_jual <= 0) {
        $errors[] = 'Harga jual harus lebih dari 0';
    }
    
    if ($harga_beli < 0) {
        $errors[] = 'Harga beli tidak boleh negatif';
    }
    
    if ($stok < 0) {
        $errors[] = 'Stok tidak boleh negatif';
    }
    
    if ($stok_min < 0) {
        $errors[] = 'Stok minimal tidak boleh negatif';
    }
    
    // Check if kode_produk already exists
    $kode_exists_query = "SELECT COUNT(*) FROM produk WHERE kode_produk = '" . escape_string($kode_produk) . "'";
    if ($is_edit) {
        $kode_exists_query .= " AND id != $id";
    }
    $kode_exists = get_var($kode_exists_query) > 0;
    
    if ($kode_exists) {
        $errors[] = 'Kode produk sudah digunakan';
    }
    
    // Process image upload
    $gambar = $is_edit ? $product['gambar'] : 'default-product.jpg';
    
    if (isset($_FILES['gambar']) && $_FILES['gambar']['error'] === UPLOAD_ERR_OK) {
        $upload_result = upload_file($_FILES['gambar'], 'products', ['jpg', 'jpeg', 'png', 'gif']);
        
        if ($upload_result['success']) {
            // Delete old image if not default
            if ($is_edit && !empty($product['gambar']) && $product['gambar'] !== 'default-product.jpg') {
                $old_image_path = ROOT_PATH . '/uploads/products/' . $product['gambar'];
                if (file_exists($old_image_path)) {
                    unlink($old_image_path);
                }
            }
            
            $gambar = $upload_result['filename'];
        } else {
            $errors[] = $upload_result['message'];
        }
    }
    
    // If no errors, save product
    if (empty($errors)) {
        if ($is_edit) {
            // Update product
            $query = "UPDATE produk SET 
                      kode_produk = '" . escape_string($kode_produk) . "', 
                      nama_produk = '" . escape_string($nama_produk) . "', 
                      id_kategori = $id_kategori, 
                      harga_beli = $harga_beli, 
                      harga_jual = $harga_jual, 
                      stok = $stok, 
                      stok_min = $stok_min, 
                      deskripsi = '" . escape_string($deskripsi) . "', 
                      gambar = '" . escape_string($gambar) . "', 
                      status = $status 
                      WHERE id = $id";
            
            if (query($query)) {
                set_flash_message('success', 'Produk berhasil diperbarui');
                redirect(base_url('produk/'));
            } else {
                $errors[] = 'Gagal memperbarui produk';
            }
        } else {
            // Insert new product
            $query = "INSERT INTO produk (kode_produk, nama_produk, id_kategori, harga_beli, harga_jual, stok, stok_min, deskripsi, gambar, status) 
                      VALUES ('" . escape_string($kode_produk) . "', 
                              '" . escape_string($nama_produk) . "', 
                              $id_kategori, 
                              $harga_beli, 
                              $harga_jual, 
                              $stok, 
                              $stok_min, 
                              '" . escape_string($deskripsi) . "', 
                              '" . escape_string($gambar) . "', 
                              $status)";
            
            if (query($query)) {
                set_flash_message('success', 'Produk berhasil ditambahkan');
                redirect(base_url('produk/'));
            } else {
                $errors[] = 'Gagal menambahkan produk';
            }
        }
    }
}

// Include header
include '../includes/header.php';
?>

<div class="row">
    <div class="col-lg-8">
        <div class="card mb-4">
            <div class="card-header d-flex justify-content-between align-items-center">
                <h5 class="mb-0"><?= $page_title ?></h5>
                <a href="<?= base_url('produk/') ?>" class="btn btn-secondary btn-sm">
                    <i class="fas fa-arrow-left me-1"></i> Kembali
                </a>
            </div>
            <div class="card-body">
                <?php if (isset($errors) && !empty($errors)): ?>
                <div class="alert alert-danger">
                    <ul class="mb-0">
                        <?php foreach ($errors as $error): ?>
                        <li><?= $error ?></li>
                        <?php endforeach; ?>
                    </ul>
                </div>
                <?php endif; ?>
                
                <form action="" method="post" enctype="multipart/form-data">
                    <div class="row mb-3">
                        <div class="col-md-6">
                            <label for="kode_produk" class="form-label">Kode Produk <span class="text-danger">*</span></label>
                            <input type="text" class="form-control" id="kode_produk" name="kode_produk" value="<?= $is_edit ? htmlspecialchars($product['kode_produk']) : '' ?>" required>
                        </div>
                        <div class="col-md-6">
                            <label for="nama_produk" class="form-label">Nama Produk <span class="text-danger">*</span></label>
                            <input type="text" class="form-control" id="nama_produk" name="nama_produk" value="<?= $is_edit ? htmlspecialchars($product['nama_produk']) : '' ?>" required>
                        </div>
                    </div>
                    
                    <div class="row mb-3">
                        <div class="col-md-6">
                            <label for="id_kategori" class="form-label">Kategori</label>
                            <select class="form-select" id="id_kategori" name="id_kategori">
                                <option value="0">Tanpa Kategori</option>
                                <?php foreach ($categories as $category): ?>
                                <option value="<?= $category['id'] ?>" <?= $is_edit && $product['id_kategori'] == $category['id'] ? 'selected' : '' ?>>
                                    <?= $category['nama_kategori'] ?>
                                </option>
                                <?php endforeach; ?>
                            </select>
                        </div>
                        <div class="col-md-6">
                            <label for="status" class="form-label">Status</label>
                            <div class="form-check form-switch mt-2">
                                <input class="form-check-input" type="checkbox" id="status" name="status" <?= !$is_edit || ($is_edit && $product['status'] == 1) ? 'checked' : '' ?>>
                                <label class="form-check-label" for="status">Aktif</label>
                            </div>
                        </div>
                    </div>
                    
                    <div class="row mb-3">
                        <div class="col-md-6">
                            <label for="harga_beli" class="form-label">Harga Beli</label>
                            <div class="input-group">
                                <span class="input-group-text">Rp</span>
                                <input type="text" class="form-control currency" id="harga_beli" name="harga_beli" value="<?= $is_edit ? number_format($product['harga_beli'], 0, ',', '.') : '0' ?>">
                            </div>
                        </div>
                        <div class="col-md-6">
                            <label for="harga_jual" class="form-label">Harga Jual <span class="text-danger">*</span></label>
                            <div class="input-group">
                                <span class="input-group-text">Rp</span>
                                <input type="text" class="form-control currency" id="harga_jual" name="harga_jual" value="<?= $is_edit ? number_format($product['harga_jual'], 0, ',', '.') : '0' ?>" required>
                            </div>
                        </div>
                    </div>
                    
                    <div class="row mb-3">
                        <div class="col-md-6">
                            <label for="stok" class="form-label">Stok</label>
                            <input type="number" class="form-control" id="stok" name="stok" value="<?= $is_edit ? $product['stok'] : '0' ?>" min="0">
                        </div>
                        <div class="col-md-6">
                            <label for="stok_min" class="form-label">Stok Minimal</label>
                            <input type="number" class="form-control" id="stok_min" name="stok_min" value="<?= $is_edit ? $product['stok_min'] : '0' ?>" min="0">
                        </div>
                    </div>
                    
                    <div class="mb-3">
                        <label for="deskripsi" class="form-label">Deskripsi</label>
                        <textarea class="form-control" id="deskripsi" name="deskripsi" rows="3"><?= $is_edit ? htmlspecialchars($product['deskripsi']) : '' ?></textarea>
                    </div>
                    
                    <div class="mb-3">
                        <label for="gambar" class="form-label">Gambar Produk</label>
                        <?php if ($is_edit && !empty($product['gambar']) && $product['gambar'] !== 'default-product.jpg'): ?>
                        <div class="mb-2">
                            <img src="<?= upload_url('products/' . $product['gambar']) ?>" alt="<?= $product['nama_produk'] ?>" class="img-thumbnail" style="max-height: 150px;">
                        </div>
                        <?php endif; ?>
                        <input type="file" class="form-control" id="gambar" name="gambar" accept="image/*">
                        <small class="text-muted">Format: JPG, JPEG, PNG, GIF. Maks: 2MB</small>
                    </div>
                    
                    <div class="mt-4">
                        <button type="submit" class="btn btn-primary">
                            <i class="fas fa-save me-1"></i> Simpan
                        </button>
                        <a href="<?= base_url('produk/') ?>" class="btn btn-secondary">
                            <i class="fas fa-times me-1"></i> Batal
                        </a>
                    </div>
                </form>
            </div>
        </div>
    </div>
</div>

<script>
    // Format currency input
    document.addEventListener('DOMContentLoaded', function() {
        const currencyInputs = document.querySelectorAll('.currency');
        
        currencyInputs.forEach(function(input) {
            input.addEventListener('input', function(e) {
                // Remove non-numeric characters
                let value = this.value.replace(/[^0-9]/g, '');
                
                // Format with thousand separator
                if (value !== '') {
                    value = parseInt(value).toLocaleString('id-ID');
                    this.value = value;
                }
            });
        });
    });
</script>

<?php
// Include footer
include '../includes/footer.php';
?>