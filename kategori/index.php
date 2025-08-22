<?php
/**
 * Kategori (Categories) Page
 */
require_once '../config/config.php';

// Check if user is logged in
if (!is_logged_in()) {
    redirect(base_url('auth/login.php'));
}

// Set active menu
$active_menu = 'kategori';

// Set page title
$page_title = 'Kategori Produk';

// Process form submission for add/edit
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $id = isset($_POST['id']) ? (int)$_POST['id'] : 0;
    $nama_kategori = sanitize($_POST['nama_kategori']);
    
    // Validate form data
    $errors = [];
    
    if (empty($nama_kategori)) {
        $errors[] = 'Nama kategori tidak boleh kosong';
    }
    
    // Check if category name already exists
    $name_exists_query = "SELECT COUNT(*) FROM kategori WHERE nama_kategori = '" . escape_string($nama_kategori) . "'";
    if ($id > 0) {
        $name_exists_query .= " AND id != $id";
    }
    $name_exists = get_var($name_exists_query) > 0;
    
    if ($name_exists) {
        $errors[] = 'Nama kategori sudah digunakan';
    }
    
    // If no errors, save category
    if (empty($errors)) {
        if ($id > 0) {
            // Update category
            $query = "UPDATE kategori SET nama_kategori = '" . escape_string($nama_kategori) . "' WHERE id = $id";
            
            if (query($query)) {
                set_flash_message('success', 'Kategori berhasil diperbarui');
            } else {
                set_flash_message('error', 'Gagal memperbarui kategori');
            }
        } else {
            // Insert new category
            $query = "INSERT INTO kategori (nama_kategori) VALUES ('" . escape_string($nama_kategori) . "')";
            
            if (query($query)) {
                set_flash_message('success', 'Kategori berhasil ditambahkan');
            } else {
                set_flash_message('error', 'Gagal menambahkan kategori');
            }
        }
        
        redirect(base_url('kategori/'));
    }
}

// Handle delete action
if (isset($_GET['action']) && $_GET['action'] === 'delete' && isset($_GET['id'])) {
    $id = (int)$_GET['id'];
    
    // Check if category is used in products
    $used_in_products = get_var("SELECT COUNT(*) FROM produk WHERE id_kategori = $id");
    
    if ($used_in_products > 0) {
        set_flash_message('error', 'Kategori tidak dapat dihapus karena masih digunakan oleh produk');
    } else {
        // Delete category
        $delete_query = "DELETE FROM kategori WHERE id = $id";
        if (query($delete_query)) {
            set_flash_message('success', 'Kategori berhasil dihapus');
        } else {
            set_flash_message('error', 'Gagal menghapus kategori');
        }
    }
    
    redirect(base_url('kategori/'));
}

// Get category for edit
$category = [];
if (isset($_GET['action']) && $_GET['action'] === 'edit' && isset($_GET['id'])) {
    $id = (int)$_GET['id'];
    $category = get_row("SELECT * FROM kategori WHERE id = $id");
    
    if (!$category) {
        set_flash_message('error', 'Kategori tidak ditemukan');
        redirect(base_url('kategori/'));
    }
}

// Get all categories
$categories = query("SELECT k.*, COUNT(p.id) as jumlah_produk 
                    FROM kategori k 
                    LEFT JOIN produk p ON k.id = p.id_kategori 
                    GROUP BY k.id 
                    ORDER BY k.nama_kategori ASC");

// Include header
include '../includes/header.php';
?>

<div class="row">
    <div class="col-lg-4">
        <!-- Category Form -->
        <div class="card mb-4">
            <div class="card-header">
                <h5 class="mb-0"><?= !empty($category) ? 'Edit Kategori' : 'Tambah Kategori' ?></h5>
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
                
                <form action="" method="post">
                    <?php if (!empty($category)): ?>
                    <input type="hidden" name="id" value="<?= $category['id'] ?>">
                    <?php endif; ?>
                    
                    <div class="mb-3">
                        <label for="nama_kategori" class="form-label">Nama Kategori <span class="text-danger">*</span></label>
                        <input type="text" class="form-control" id="nama_kategori" name="nama_kategori" value="<?= !empty($category) ? htmlspecialchars($category['nama_kategori']) : '' ?>" required>
                    </div>
                    
                    <div class="d-flex">
                        <button type="submit" class="btn btn-primary me-2">
                            <i class="fas fa-save me-1"></i> Simpan
                        </button>
                        <?php if (!empty($category)): ?>
                        <a href="<?= base_url('kategori/') ?>" class="btn btn-secondary">
                            <i class="fas fa-times me-1"></i> Batal
                        </a>
                        <?php endif; ?>
                    </div>
                </form>
            </div>
        </div>
    </div>
    
    <div class="col-lg-8">
        <!-- Categories List -->
        <div class="card mb-4">
            <div class="card-header">
                <h5 class="mb-0">Daftar Kategori</h5>
            </div>
            <div class="card-body">
                <div class="table-responsive">
                    <table class="table table-striped table-hover align-middle">
                        <thead>
                            <tr>
                                <th width="5%">No</th>
                                <th width="60%">Nama Kategori</th>
                                <th width="20%">Jumlah Produk</th>
                                <th width="15%">Aksi</th>
                            </tr>
                        </thead>
                        <tbody>
                            <?php if (empty($categories)): ?>
                            <tr>
                                <td colspan="4" class="text-center py-4">Tidak ada data kategori</td>
                            </tr>
                            <?php else: ?>
                                <?php $no = 1; foreach ($categories as $cat): ?>
                                <tr>
                                    <td><?= $no++ ?></td>
                                    <td><?= $cat['nama_kategori'] ?></td>
                                    <td>
                                        <span class="badge bg-info"><?= $cat['jumlah_produk'] ?></span>
                                    </td>
                                    <td>
                                        <div class="btn-group btn-group-sm">
                                            <a href="<?= base_url('kategori/index.php?action=edit&id=' . $cat['id']) ?>" class="btn btn-info" title="Edit">
                                                <i class="fas fa-edit"></i>
                                            </a>
                                            <a href="<?= base_url('kategori/index.php?action=delete&id=' . $cat['id']) ?>" class="btn btn-danger btn-delete" title="Hapus" data-confirm="Yakin ingin menghapus kategori ini?">
                                                <i class="fas fa-trash"></i>
                                            </a>
                                        </div>
                                    </td>
                                </tr>
                                <?php endforeach; ?>
                            <?php endif; ?>
                        </tbody>
                    </table>
                </div>
            </div>
        </div>
    </div>
</div>

<?php
// Include footer
include '../includes/footer.php';
?>