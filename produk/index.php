<?php
/**
 * Produk (Products) Page
 */
require_once '../config/config.php';

// Check if user is logged in
if (!is_logged_in()) {
    redirect(base_url('auth/login.php'));
}

// Set active menu
$active_menu = 'produk';

// Set page title
$page_title = 'Daftar Produk';

// Pagination
$page = isset($_GET['page']) ? (int)$_GET['page'] : 1;
$per_page = 10;
$offset = ($page - 1) * $per_page;

// Search
$search = isset($_GET['search']) ? sanitize($_GET['search']) : '';
$search_condition = '';
if (!empty($search)) {
    $search_condition = "AND (p.nama_produk LIKE '%" . escape_string($search) . "%' OR 
                            p.kode_produk LIKE '%" . escape_string($search) . "%' OR 
                            k.nama_kategori LIKE '%" . escape_string($search) . "%')";
}

// Category filter
$category = isset($_GET['category']) ? (int)$_GET['category'] : 0;
$category_condition = '';
if ($category > 0) {
    $category_condition = "AND p.id_kategori = $category";
}

// Status filter
$status = isset($_GET['status']) ? (int)$_GET['status'] : -1;
$status_condition = '';
if ($status !== -1) {
    $status_condition = "AND p.status = $status";
}

// Get total records
$total_query = "SELECT COUNT(*) FROM produk p 
               LEFT JOIN kategori k ON p.id_kategori = k.id 
               WHERE 1=1 $search_condition $category_condition $status_condition";
$total_records = get_var($total_query);
$total_pages = ceil($total_records / $per_page);

// Get products
$query = "SELECT p.*, k.nama_kategori 
          FROM produk p 
          LEFT JOIN kategori k ON p.id_kategori = k.id 
          WHERE 1=1 $search_condition $category_condition $status_condition 
          ORDER BY p.nama_produk ASC 
          LIMIT $offset, $per_page";
$products = query($query);

// Get categories for filter
$categories = query("SELECT * FROM kategori ORDER BY nama_kategori ASC");

// Handle delete action
if (isset($_GET['action']) && $_GET['action'] === 'delete' && isset($_GET['id'])) {
    $id = (int)$_GET['id'];
    
    // Check if product exists
    $product = get_row("SELECT * FROM produk WHERE id = $id");
    
    if ($product) {
        // Check if product is used in transactions
        $used_in_transaction = get_var("SELECT COUNT(*) FROM detail_transaksi WHERE id_produk = $id");
        
        if ($used_in_transaction > 0) {
            // Set product status to inactive instead of deleting
            $update_query = "UPDATE produk SET status = 0 WHERE id = $id";
            if (query($update_query)) {
                set_flash_message('success', 'Produk berhasil dinonaktifkan karena sudah digunakan dalam transaksi');
            } else {
                set_flash_message('error', 'Gagal menonaktifkan produk');
            }
        } else {
            // Delete product image if exists
            if (!empty($product['gambar']) && $product['gambar'] !== 'default-product.jpg') {
                $image_path = ROOT_PATH . '/uploads/products/' . $product['gambar'];
                if (file_exists($image_path)) {
                    unlink($image_path);
                }
            }
            
            // Delete product
            $delete_query = "DELETE FROM produk WHERE id = $id";
            if (query($delete_query)) {
                set_flash_message('success', 'Produk berhasil dihapus');
            } else {
                set_flash_message('error', 'Gagal menghapus produk');
            }
        }
    } else {
        set_flash_message('error', 'Produk tidak ditemukan');
    }
    
    redirect(base_url('produk/'));
}

// Include header
include '../includes/header.php';
?>

<div class="card mb-4">
    <div class="card-header d-flex justify-content-between align-items-center">
        <h5 class="mb-0">Daftar Produk</h5>
        <a href="<?= base_url('produk/form.php') ?>" class="btn btn-primary btn-sm">
            <i class="fas fa-plus me-1"></i> Tambah Produk
        </a>
    </div>
    <div class="card-body">
        <!-- Filters -->
        <form action="" method="get" class="mb-4">
            <div class="row g-3">
                <div class="col-md-4">
                    <div class="input-group">
                        <span class="input-group-text"><i class="fas fa-search"></i></span>
                        <input type="text" class="form-control" name="search" placeholder="Cari produk..." value="<?= htmlspecialchars($search) ?>">
                    </div>
                </div>
                <div class="col-md-3">
                    <select class="form-select" name="category">
                        <option value="0">Semua Kategori</option>
                        <?php foreach ($categories as $cat): ?>
                        <option value="<?= $cat['id'] ?>" <?= $category === (int)$cat['id'] ? 'selected' : '' ?>>
                            <?= $cat['nama_kategori'] ?>
                        </option>
                        <?php endforeach; ?>
                    </select>
                </div>
                <div class="col-md-2">
                    <select class="form-select" name="status">
                        <option value="-1" <?= $status === -1 ? 'selected' : '' ?>>Semua Status</option>
                        <option value="1" <?= $status === 1 ? 'selected' : '' ?>>Aktif</option>
                        <option value="0" <?= $status === 0 ? 'selected' : '' ?>>Tidak Aktif</option>
                    </select>
                </div>
                <div class="col-md-3">
                    <button type="submit" class="btn btn-primary me-2">
                        <i class="fas fa-filter me-1"></i> Filter
                    </button>
                    <a href="<?= base_url('produk/') ?>" class="btn btn-secondary">
                        <i class="fas fa-sync-alt me-1"></i> Reset
                    </a>
                </div>
            </div>
        </form>
        
        <!-- Products Table -->
        <div class="table-responsive">
            <table class="table table-striped table-hover align-middle">
                <thead>
                    <tr>
                        <th width="5%">No</th>
                        <th width="15%">Gambar</th>
                        <th width="15%">Kode</th>
                        <th width="20%">Nama Produk</th>
                        <th width="15%">Kategori</th>
                        <th width="10%">Harga</th>
                        <th width="5%">Stok</th>
                        <th width="5%">Status</th>
                        <th width="10%">Aksi</th>
                    </tr>
                </thead>
                <tbody>
                    <?php if (empty($products)): ?>
                    <tr>
                        <td colspan="9" class="text-center py-4">Tidak ada data produk</td>
                    </tr>
                    <?php else: ?>
                        <?php $no = $offset + 1; foreach ($products as $product): ?>
                        <tr>
                            <td><?= $no++ ?></td>
                            <td>
                                <?php if (!empty($product['gambar']) && $product['gambar'] !== 'default-product.jpg'): ?>
                                <img src="<?= upload_url('products/' . $product['gambar']) ?>" alt="<?= $product['nama_produk'] ?>" class="img-thumbnail" style="width: 60px; height: 60px; object-fit: cover;">
                                <?php else: ?>
                                <img src="https://via.placeholder.com/60?text=<?= urlencode($product['nama_produk']) ?>" alt="<?= $product['nama_produk'] ?>" class="img-thumbnail" style="width: 60px; height: 60px; object-fit: cover;">
                                <?php endif; ?>
                            </td>
                            <td><?= $product['kode_produk'] ?></td>
                            <td><?= $product['nama_produk'] ?></td>
                            <td><?= $product['nama_kategori'] ?? 'Tanpa Kategori' ?></td>
                            <td><?= format_rupiah($product['harga_jual']) ?></td>
                            <td>
                                <?php if ($product['stok'] <= $product['stok_min']): ?>
                                <span class="badge bg-danger"><?= $product['stok'] ?></span>
                                <?php else: ?>
                                <span class="badge bg-success"><?= $product['stok'] ?></span>
                                <?php endif; ?>
                            </td>
                            <td>
                                <?php if ($product['status'] == 1): ?>
                                <span class="badge bg-success">Aktif</span>
                                <?php else: ?>
                                <span class="badge bg-danger">Tidak Aktif</span>
                                <?php endif; ?>
                            </td>
                            <td>
                                <div class="btn-group btn-group-sm">
                                    <a href="<?= base_url('produk/form.php?id=' . $product['id']) ?>" class="btn btn-info" title="Edit">
                                        <i class="fas fa-edit"></i>
                                    </a>
                                    <a href="<?= base_url('produk/index.php?action=delete&id=' . $product['id']) ?>" class="btn btn-danger btn-delete" title="Hapus" data-confirm="Yakin ingin menghapus produk ini?">
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
        
        <!-- Pagination -->
        <?php if ($total_pages > 1): ?>
        <nav aria-label="Page navigation">
            <ul class="pagination justify-content-center mt-4">
                <?php if ($page > 1): ?>
                <li class="page-item">
                    <a class="page-link" href="?page=<?= $page - 1 ?>&search=<?= urlencode($search) ?>&category=<?= $category ?>&status=<?= $status ?>" aria-label="Previous">
                        <span aria-hidden="true">&laquo;</span>
                    </a>
                </li>
                <?php endif; ?>
                
                <?php for ($i = max(1, $page - 2); $i <= min($total_pages, $page + 2); $i++): ?>
                <li class="page-item <?= $i === $page ? 'active' : '' ?>">
                    <a class="page-link" href="?page=<?= $i ?>&search=<?= urlencode($search) ?>&category=<?= $category ?>&status=<?= $status ?>"><?= $i ?></a>
                </li>
                <?php endfor; ?>
                
                <?php if ($page < $total_pages): ?>
                <li class="page-item">
                    <a class="page-link" href="?page=<?= $page + 1 ?>&search=<?= urlencode($search) ?>&category=<?= $category ?>&status=<?= $status ?>" aria-label="Next">
                        <span aria-hidden="true">&raquo;</span>
                    </a>
                </li>
                <?php endif; ?>
            </ul>
        </nav>
        <?php endif; ?>
    </div>
</div>

<?php
// Include footer
include '../includes/footer.php';
?>