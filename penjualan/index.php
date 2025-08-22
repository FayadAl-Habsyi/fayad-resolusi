<?php
/**
 * Penjualan (Sales) Page
 */
require_once '../config/config.php';

// Check if user is logged in
if (!is_logged_in()) {
    redirect(base_url('auth/login.php'));
}

// Set active menu
$active_menu = 'penjualan';

// Set page title
$page_title = 'Daftar Penjualan';

// Pagination
$page = isset($_GET['page']) ? (int)$_GET['page'] : 1;
$per_page = 10;
$offset = ($page - 1) * $per_page;

// Search
$search = isset($_GET['search']) ? sanitize($_GET['search']) : '';
$search_condition = '';
if (!empty($search)) {
    $search_condition = "AND (t.no_invoice LIKE '%" . escape_string($search) . "%' OR 
                            p.nama LIKE '%" . escape_string($search) . "%' OR 
                            u.nama LIKE '%" . escape_string($search) . "%')";
}

// Date filter
$start_date = isset($_GET['start_date']) ? sanitize($_GET['start_date']) : '';
$end_date = isset($_GET['end_date']) ? sanitize($_GET['end_date']) : '';
$date_condition = '';

if (!empty($start_date) && !empty($end_date)) {
    $date_condition = "AND DATE(t.tanggal) BETWEEN '" . escape_string($start_date) . "' AND '" . escape_string($end_date) . "'";
} elseif (!empty($start_date)) {
    $date_condition = "AND DATE(t.tanggal) >= '" . escape_string($start_date) . "'";
} elseif (!empty($end_date)) {
    $date_condition = "AND DATE(t.tanggal) <= '" . escape_string($end_date) . "'";
}

// Status filter
$status = isset($_GET['status']) ? sanitize($_GET['status']) : '';
$status_condition = '';
if (!empty($status)) {
    $status_condition = "AND t.status = '" . escape_string($status) . "'";
}

// Get total records
$total_query = "SELECT COUNT(*) FROM transaksi t 
               LEFT JOIN pelanggan p ON t.id_pelanggan = p.id 
               LEFT JOIN users u ON t.id_user = u.id 
               WHERE 1=1 $search_condition $date_condition $status_condition";
$total_records = get_var($total_query);
$total_pages = ceil($total_records / $per_page);

// Get transactions
$query = "SELECT t.*, p.nama as nama_pelanggan, u.nama as nama_user 
          FROM transaksi t 
          LEFT JOIN pelanggan p ON t.id_pelanggan = p.id 
          LEFT JOIN users u ON t.id_user = u.id 
          WHERE 1=1 $search_condition $date_condition $status_condition 
          ORDER BY t.tanggal DESC 
          LIMIT $offset, $per_page";
$transactions = query($query);

// Include header
include '../includes/header.php';
?>

<div class="card mb-4">
    <div class="card-header d-flex justify-content-between align-items-center">
        <h5 class="mb-0">Daftar Penjualan</h5>
        <a href="<?= base_url('transaksi/') ?>" class="btn btn-primary btn-sm">
            <i class="fas fa-plus me-1"></i> Transaksi Baru
        </a>
    </div>
    <div class="card-body">
        <!-- Filters -->
        <form action="" method="get" class="mb-4">
            <div class="row g-3">
                <div class="col-md-3">
                    <div class="input-group">
                        <span class="input-group-text"><i class="fas fa-search"></i></span>
                        <input type="text" class="form-control" name="search" placeholder="Cari..." value="<?= htmlspecialchars($search) ?>">
                    </div>
                </div>
                <div class="col-md-2">
                    <input type="date" class="form-control" name="start_date" placeholder="Tanggal Mulai" value="<?= htmlspecialchars($start_date) ?>">
                </div>
                <div class="col-md-2">
                    <input type="date" class="form-control" name="end_date" placeholder="Tanggal Akhir" value="<?= htmlspecialchars($end_date) ?>">
                </div>
                <div class="col-md-2">
                    <select class="form-select" name="status">
                        <option value="" <?= $status === '' ? 'selected' : '' ?>>Semua Status</option>
                        <option value="pending" <?= $status === 'pending' ? 'selected' : '' ?>>Pending</option>
                        <option value="selesai" <?= $status === 'selesai' ? 'selected' : '' ?>>Selesai</option>
                        <option value="batal" <?= $status === 'batal' ? 'selected' : '' ?>>Batal</option>
                    </select>
                </div>
                <div class="col-md-3">
                    <button type="submit" class="btn btn-primary me-2">
                        <i class="fas fa-filter me-1"></i> Filter
                    </button>
                    <a href="<?= base_url('penjualan/') ?>" class="btn btn-secondary">
                        <i class="fas fa-sync-alt me-1"></i> Reset
                    </a>
                </div>
            </div>
        </form>
        
        <!-- Transactions Table -->
        <div class="table-responsive">
            <table class="table table-striped table-hover align-middle">
                <thead>
                    <tr>
                        <th>No. Invoice</th>
                        <th>Tanggal</th>
                        <th>Pelanggan</th>
                        <th>Kasir</th>
                        <th>Total</th>
                        <th>Status</th>
                        <th>Aksi</th>
                    </tr>
                </thead>
                <tbody>
                    <?php if (empty($transactions)): ?>
                    <tr>
                        <td colspan="7" class="text-center py-4">Tidak ada data penjualan</td>
                    </tr>
                    <?php else: ?>
                        <?php foreach ($transactions as $transaction): ?>
                        <tr>
                            <td><?= $transaction['no_invoice'] ?></td>
                            <td><?= format_datetime($transaction['tanggal']) ?></td>
                            <td><?= $transaction['nama_pelanggan'] ?? 'Umum' ?></td>
                            <td><?= $transaction['nama_user'] ?></td>
                            <td><?= format_rupiah($transaction['total']) ?></td>
                            <td>
                                <?php if ($transaction['status'] === 'selesai'): ?>
                                <span class="badge bg-success">Selesai</span>
                                <?php elseif ($transaction['status'] === 'pending'): ?>
                                <span class="badge bg-warning">Pending</span>
                                <?php else: ?>
                                <span class="badge bg-danger">Batal</span>
                                <?php endif; ?>
                            </td>
                            <td>
                                <div class="btn-group btn-group-sm">
                                    <a href="<?= base_url('penjualan/detail.php?id=' . $transaction['id']) ?>" class="btn btn-info" title="Detail">
                                        <i class="fas fa-eye"></i>
                                    </a>
                                    <a href="<?= base_url('penjualan/cetak.php?id=' . $transaction['id']) ?>" class="btn btn-primary" title="Cetak" target="_blank">
                                        <i class="fas fa-print"></i>
                                    </a>
                                    <?php if (is_admin() && $transaction['status'] !== 'batal'): ?>
                                    <a href="<?= base_url('penjualan/batalkan.php?id=' . $transaction['id']) ?>" class="btn btn-danger btn-delete" title="Batalkan" data-confirm="Yakin ingin membatalkan transaksi ini?">
                                        <i class="fas fa-times"></i>
                                    </a>
                                    <?php endif; ?>
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
                    <a class="page-link" href="?page=<?= $page - 1 ?>&search=<?= urlencode($search) ?>&start_date=<?= urlencode($start_date) ?>&end_date=<?= urlencode($end_date) ?>&status=<?= urlencode($status) ?>" aria-label="Previous">
                        <span aria-hidden="true">&laquo;</span>
                    </a>
                </li>
                <?php endif; ?>
                
                <?php for ($i = max(1, $page - 2); $i <= min($total_pages, $page + 2); $i++): ?>
                <li class="page-item <?= $i === $page ? 'active' : '' ?>">
                    <a class="page-link" href="?page=<?= $i ?>&search=<?= urlencode($search) ?>&start_date=<?= urlencode($start_date) ?>&end_date=<?= urlencode($end_date) ?>&status=<?= urlencode($status) ?>"><?= $i ?></a>
                </li>
                <?php endfor; ?>
                
                <?php if ($page < $total_pages): ?>
                <li class="page-item">
                    <a class="page-link" href="?page=<?= $page + 1 ?>&search=<?= urlencode($search) ?>&start_date=<?= urlencode($start_date) ?>&end_date=<?= urlencode($end_date) ?>&status=<?= urlencode($status) ?>" aria-label="Next">
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