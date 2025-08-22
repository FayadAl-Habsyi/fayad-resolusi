<?php
/**
 * Stok Masuk (Stock Entry) Page
 */
require_once '../config/config.php';

// Check if user is logged in
if (!is_logged_in()) {
    redirect(base_url('auth/login.php'));
}

// Set active menu
$active_menu = 'stok';

// Set page title
$page_title = 'Stok Masuk';

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
                            s.keterangan LIKE '%" . escape_string($search) . "%')";
}

// Date filter
$start_date = isset($_GET['start_date']) ? sanitize($_GET['start_date']) : '';
$end_date = isset($_GET['end_date']) ? sanitize($_GET['end_date']) : '';
$date_condition = '';

if (!empty($start_date) && !empty($end_date)) {
    $date_condition = "AND DATE(s.tanggal) BETWEEN '" . escape_string($start_date) . "' AND '" . escape_string($end_date) . "'";
} elseif (!empty($start_date)) {
    $date_condition = "AND DATE(s.tanggal) >= '" . escape_string($start_date) . "'";
} elseif (!empty($end_date)) {
    $date_condition = "AND DATE(s.tanggal) <= '" . escape_string($end_date) . "'";
}

// Get total records
$total_query = "SELECT COUNT(*) FROM stok_masuk s 
               LEFT JOIN produk p ON s.id_produk = p.id 
               WHERE 1=1 $search_condition $date_condition";
$total_records = get_var($total_query);
$total_pages = ceil($total_records / $per_page);

// Get stock entries
$query = "SELECT s.*, p.nama_produk, p.kode_produk, u.nama as nama_user 
          FROM stok_masuk s 
          LEFT JOIN produk p ON s.id_produk = p.id 
          LEFT JOIN users u ON s.id_user = u.id 
          WHERE 1=1 $search_condition $date_condition 
          ORDER BY s.tanggal DESC 
          LIMIT $offset, $per_page";
$stock_entries = query($query);

// Include header
include '../includes/header.php';
?>

<div class="row">
    <div class="col-lg-12">
        <div class="card mb-4">
            <div class="card-header d-flex justify-content-between align-items-center">
                <h5 class="mb-0">Daftar Stok Masuk</h5>
                <a href="<?= base_url('stok/form.php') ?>" class="btn btn-primary btn-sm">
                    <i class="fas fa-plus me-1"></i> Tambah Stok Masuk
                </a>
            </div>
            <div class="card-body">
                <div class="alert alert-info mb-4">
                    <strong>Informasi:</strong> Halaman ini menampilkan daftar stok masuk produk. Anda dapat mencari berdasarkan nama produk, kode produk, atau keterangan. Gunakan filter tanggal untuk melihat stok masuk dalam rentang waktu tertentu.
                </div>
                <!-- Filters -->
                <form action="" method="get" class="mb-4">
                    <div class="row g-3">
                        <div class="col-md-4">
                            <div class="input-group">
                                <span class="input-group-text"><i class="fas fa-search"></i></span>
                                <input type="text" class="form-control" name="search" placeholder="Cari..." value="<?= htmlspecialchars($search) ?>">
                            </div>
                        </div>
                        <div class="col-md-3">
                            <input type="date" class="form-control" name="start_date" placeholder="Tanggal Mulai" value="<?= htmlspecialchars($start_date) ?>">
                        </div>
                        <div class="col-md-3">
                            <input type="date" class="form-control" name="end_date" placeholder="Tanggal Akhir" value="<?= htmlspecialchars($end_date) ?>">
                        </div>
                        <div class="col-md-2">
                            <button type="submit" class="btn btn-primary me-2">
                                <i class="fas fa-filter me-1"></i> Filter
                            </button>
                            <a href="<?= base_url('stok/') ?>" class="btn btn-secondary">
                                <i class="fas fa-sync-alt me-1"></i> Reset
                            </a>
                        </div>
                    </div>
                </form>
                
                <!-- Stock Entries Table -->
                <div class="table-responsive">
                    <table class="table table-striped table-hover align-middle">
                        <thead>
                            <tr>
                                <th width="5%">No</th>
                                <th width="15%">Tanggal</th>
                                <th width="15%">Kode Produk</th>
                                <th width="20%">Nama Produk</th>
                                <th width="10%">Jumlah</th>
                                <th width="20%">Keterangan</th>
                                <th width="15%">Pengguna</th>
                            </tr>
                        </thead>
                        <tbody>
                            <?php if (empty($stock_entries)): ?>
                            <tr>
                                <td colspan="7" class="text-center py-4">Tidak ada data stok masuk</td>
                            </tr>
                            <?php else: ?>
                                <?php $no = $offset + 1; foreach ($stock_entries as $entry): ?>
                                <tr>
                                    <td><?= $no++ ?></td>
                                    <td><?= format_datetime($entry['tanggal']) ?></td>
                                    <td><?= $entry['kode_produk'] ?></td>
                                    <td><?= $entry['nama_produk'] ?></td>
                                    <td>
                                        <span class="badge bg-success"><?= $entry['jumlah'] ?></span>
                                    </td>
                                    <td><?= $entry['keterangan'] ?></td>
                                    <td><?= $entry['nama_user'] ?></td>
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
                            <a class="page-link" href="?page=<?= $page - 1 ?>&search=<?= urlencode($search) ?>&start_date=<?= urlencode($start_date) ?>&end_date=<?= urlencode($end_date) ?>" aria-label="Previous">
                                <span aria-hidden="true">&laquo;</span>
                            </a>
                        </li>
                        <?php endif; ?>
                        
                        <?php for ($i = max(1, $page - 2); $i <= min($total_pages, $page + 2); $i++): ?>
                        <li class="page-item <?= $i === $page ? 'active' : '' ?>">
                            <a class="page-link" href="?page=<?= $i ?>&search=<?= urlencode($search) ?>&start_date=<?= urlencode($start_date) ?>&end_date=<?= urlencode($end_date) ?>"><?= $i ?></a>
                        </li>
                        <?php endfor; ?>
                        
                        <?php if ($page < $total_pages): ?>
                        <li class="page-item">
                            <a class="page-link" href="?page=<?= $page + 1 ?>&search=<?= urlencode($search) ?>&start_date=<?= urlencode($start_date) ?>&end_date=<?= urlencode($end_date) ?>" aria-label="Next">
                                <span aria-hidden="true">&raquo;</span>
                            </a>
                        </li>
                        <?php endif; ?>
                    </ul>
                </nav>
                <?php endif; ?>
            </div>
        </div>
    </div>
</div>

<?php
// Include footer
include '../includes/footer.php';
?>