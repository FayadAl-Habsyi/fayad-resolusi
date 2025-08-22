<?php
/**
 * Users Management Page
 */
require_once '../config/config.php';

// Check if user is logged in and is admin
if (!is_logged_in()) {
    redirect(base_url('auth/login.php'));
}

if (!is_admin()) {
    set_flash_message('error', 'Anda tidak memiliki akses ke halaman tersebut');
    redirect(base_url('dashboard.php'));
}

// Set active menu
$active_menu = 'pengguna';

// Set page title
$page_title = 'Manajemen Pengguna';

// Pagination
$page = isset($_GET['page']) ? (int)$_GET['page'] : 1;
$per_page = 10;
$offset = ($page - 1) * $per_page;

// Search
$search = isset($_GET['search']) ? sanitize($_GET['search']) : '';
$search_condition = '';
if (!empty($search)) {
    $search_condition = "AND (username LIKE '%" . escape_string($search) . "%' OR 
                            nama LIKE '%" . escape_string($search) . "%' OR 
                            email LIKE '%" . escape_string($search) . "%')";
}

// Role filter
$role = isset($_GET['role']) ? sanitize($_GET['role']) : '';
$role_condition = '';
if (!empty($role)) {
    $role_condition = "AND role = '" . escape_string($role) . "'";
}

// Status filter
$status = isset($_GET['status']) ? sanitize($_GET['status']) : '';
$status_condition = '';
if (!empty($status)) {
    $status_condition = "AND status = '" . escape_string($status) . "'";
}

// Get total records
$total_query = "SELECT COUNT(*) FROM users WHERE 1=1 $search_condition $role_condition $status_condition";
$total_records = get_var($total_query);
$total_pages = ceil($total_records / $per_page);

// Get users
$query = "SELECT * FROM users 
          WHERE 1=1 $search_condition $role_condition $status_condition 
          ORDER BY id DESC 
          LIMIT $offset, $per_page";
$users = query($query);

// Process delete user
if (isset($_GET['action']) && $_GET['action'] === 'delete' && isset($_GET['id'])) {
    $user_id = (int)$_GET['id'];
    
    // Check if user exists
    $user = get_row("SELECT * FROM users WHERE id = $user_id");
    
    if ($user) {
        // Don't allow deleting own account
        if ($user_id === $_SESSION['user_id']) {
            set_flash_message('error', 'Anda tidak dapat menghapus akun Anda sendiri');
        } else {
            // Check if user has transactions
            $has_transactions = get_var("SELECT COUNT(*) FROM transaksi WHERE id_user = $user_id");
            
            if ($has_transactions > 0) {
                // Just deactivate the user
                $update_result = query("UPDATE users SET status = 'nonaktif' WHERE id = $user_id");
                
                if ($update_result) {
                    set_flash_message('success', 'Pengguna telah dinonaktifkan karena memiliki transaksi terkait');
                } else {
                    set_flash_message('error', 'Gagal menonaktifkan pengguna');
                }
            } else {
                // Delete the user
                $delete_result = query("DELETE FROM users WHERE id = $user_id");
                
                if ($delete_result) {
                    set_flash_message('success', 'Pengguna berhasil dihapus');
                } else {
                    set_flash_message('error', 'Gagal menghapus pengguna');
                }
            }
        }
    } else {
        set_flash_message('error', 'Pengguna tidak ditemukan');
    }
    
    redirect(base_url('pengguna/'));
}

// Include header
include '../includes/header.php';
?>

<div class="row">
    <div class="col-lg-12">
        <div class="card mb-4">
            <div class="card-header d-flex justify-content-between align-items-center">
                <h5 class="mb-0">Daftar Pengguna</h5>
                <a href="<?= base_url('pengguna/form.php') ?>" class="btn btn-primary btn-sm">
                    <i class="fas fa-plus me-1"></i> Tambah Pengguna
                </a>
            </div>
            <div class="card-body">
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
                            <select name="role" class="form-select">
                                <option value="">Semua Role</option>
                                <option value="admin" <?= $role === 'admin' ? 'selected' : '' ?>>Admin</option>
                                <option value="kasir" <?= $role === 'kasir' ? 'selected' : '' ?>>Kasir</option>
                            </select>
                        </div>
                        <div class="col-md-3">
                            <select name="status" class="form-select">
                                <option value="">Semua Status</option>
                                <option value="aktif" <?= $status === 'aktif' ? 'selected' : '' ?>>Aktif</option>
                                <option value="nonaktif" <?= $status === 'nonaktif' ? 'selected' : '' ?>>Nonaktif</option>
                            </select>
                        </div>
                        <div class="col-md-2">
                            <button type="submit" class="btn btn-primary me-2">
                                <i class="fas fa-filter me-1"></i> Filter
                            </button>
                            <a href="<?= base_url('pengguna/') ?>" class="btn btn-secondary">
                                <i class="fas fa-sync-alt me-1"></i> Reset
                            </a>
                        </div>
                    </div>
                </form>
                
                <!-- Users Table -->
                <div class="table-responsive">
                    <table class="table table-striped table-hover align-middle">
                        <thead>
                            <tr>
                                <th width="5%">No</th>
                                <th width="15%">Username</th>
                                <th width="20%">Nama</th>
                                <th width="20%">Email</th>
                                <th width="10%">Role</th>
                                <th width="10%">Status</th>
                                <th width="20%">Aksi</th>
                            </tr>
                        </thead>
                        <tbody>
                            <?php if (empty($users)): ?>
                            <tr>
                                <td colspan="7" class="text-center py-4">Tidak ada data pengguna</td>
                            </tr>
                            <?php else: ?>
                                <?php $no = $offset + 1; foreach ($users as $user): ?>
                                <tr>
                                    <td><?= $no++ ?></td>
                                    <td><?= $user['username'] ?></td>
                                    <td><?= $user['nama'] ?></td>
                                    <td><?= $user['email'] ?></td>
                                    <td>
                                        <span class="badge bg-<?= $user['role'] === 'admin' ? 'danger' : 'info' ?>">
                                            <?= ucfirst($user['role']) ?>
                                        </span>
                                    </td>
                                    <td>
                                        <span class="badge bg-<?= $user['status'] === 'aktif' ? 'success' : 'secondary' ?>">
                                            <?= ucfirst($user['status']) ?>
                                        </span>
                                    </td>
                                    <td>
                                        <a href="<?= base_url('pengguna/form.php?id=' . $user['id']) ?>" class="btn btn-sm btn-warning me-1">
                                            <i class="fas fa-edit"></i>
                                        </a>
                                        <?php if ($user['id'] !== $_SESSION['user_id']): ?>
                                        <a href="<?= base_url('pengguna/index.php?action=delete&id=' . $user['id']) ?>" class="btn btn-sm btn-danger btn-delete">
                                            <i class="fas fa-trash"></i>
                                        </a>
                                        <?php endif; ?>
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
                            <a class="page-link" href="?page=<?= $page - 1 ?>&search=<?= urlencode($search) ?>&role=<?= urlencode($role) ?>&status=<?= urlencode($status) ?>" aria-label="Previous">
                                <span aria-hidden="true">&laquo;</span>
                            </a>
                        </li>
                        <?php endif; ?>
                        
                        <?php for ($i = max(1, $page - 2); $i <= min($total_pages, $page + 2); $i++): ?>
                        <li class="page-item <?= $i === $page ? 'active' : '' ?>">
                            <a class="page-link" href="?page=<?= $i ?>&search=<?= urlencode($search) ?>&role=<?= urlencode($role) ?>&status=<?= urlencode($status) ?>"><?= $i ?></a>
                        </li>
                        <?php endfor; ?>
                        
                        <?php if ($page < $total_pages): ?>
                        <li class="page-item">
                            <a class="page-link" href="?page=<?= $page + 1 ?>&search=<?= urlencode($search) ?>&role=<?= urlencode($role) ?>&status=<?= urlencode($status) ?>" aria-label="Next">
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