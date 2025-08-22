<?php
/**
 * Customers Management Page
 */
require_once '../config/config.php';

// Check if user is logged in
if (!is_logged_in()) {
    redirect(base_url('auth/login.php'));
}

// Set active menu
$active_menu = 'pelanggan';

// Set page title
$page_title = 'Manajemen Pelanggan';

// Pagination
$page = isset($_GET['page']) ? (int)$_GET['page'] : 1;
$per_page = 10;
$offset = ($page - 1) * $per_page;

// Search
$search = isset($_GET['search']) ? sanitize($_GET['search']) : '';
$search_condition = '';
if (!empty($search)) {
    $search_condition = "AND (nama LIKE '%" . escape_string($search) . "%' OR 
                            telepon LIKE '%" . escape_string($search) . "%' OR 
                            email LIKE '%" . escape_string($search) . "%' OR
                            alamat LIKE '%" . escape_string($search) . "%')";
}

// Get total records
$total_query = "SELECT COUNT(*) FROM pelanggan WHERE 1=1 $search_condition";
$total_records = get_var($total_query);
$total_pages = ceil($total_records / $per_page);

// Get customers
$query = "SELECT * FROM pelanggan 
          WHERE 1=1 $search_condition 
          ORDER BY id DESC 
          LIMIT $offset, $per_page";
$customers = query($query);

// Process delete customer
if (isset($_GET['action']) && $_GET['action'] === 'delete' && isset($_GET['id'])) {
    $customer_id = (int)$_GET['id'];
    
    // Check if customer exists
    $customer = get_row("SELECT * FROM pelanggan WHERE id = $customer_id");
    
    if ($customer) {
        // Check if customer has transactions
        $has_transactions = get_var("SELECT COUNT(*) FROM transaksi WHERE id_pelanggan = $customer_id");
        
        if ($has_transactions > 0) {
            set_flash_message('error', 'Pelanggan tidak dapat dihapus karena memiliki transaksi terkait');
        } else {
            // Delete the customer
            $delete_result = query("DELETE FROM pelanggan WHERE id = $customer_id");
            
            if ($delete_result) {
                set_flash_message('success', 'Pelanggan berhasil dihapus');
            } else {
                set_flash_message('error', 'Gagal menghapus pelanggan');
            }
        }
    } else {
        set_flash_message('error', 'Pelanggan tidak ditemukan');
    }
    
    redirect(base_url('pelanggan/'));
}

// Process form submission (add/edit customer)
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $customer_id = isset($_POST['id']) ? (int)$_POST['id'] : 0;
    $nama = isset($_POST['nama']) ? sanitize($_POST['nama']) : '';
    $telepon = isset($_POST['telepon']) ? sanitize($_POST['telepon']) : '';
    $email = isset($_POST['email']) ? sanitize($_POST['email']) : '';
    $alamat = isset($_POST['alamat']) ? sanitize($_POST['alamat']) : '';
    
    // Validation
    $errors = [];
    
    if (empty($nama)) {
        $errors[] = 'Nama pelanggan harus diisi';
    }
    
    if (!empty($email) && !validate_email($email)) {
        $errors[] = 'Format email tidak valid';
    }
    
    // If no errors, save customer
    if (empty($errors)) {
        if ($customer_id > 0) {
            // Update customer
            $update_query = "UPDATE pelanggan SET 
                            nama = '" . escape_string($nama) . "', 
                            telepon = '" . escape_string($telepon) . "', 
                            email = '" . escape_string($email) . "', 
                            alamat = '" . escape_string($alamat) . "', 
                            updated_at = NOW() 
                            WHERE id = $customer_id";
            
            $result = query($update_query);
            
            if ($result) {
                set_flash_message('success', 'Pelanggan berhasil diperbarui');
                redirect(base_url('pelanggan/'));
            } else {
                $errors[] = 'Gagal memperbarui pelanggan';
            }
        } else {
            // Insert new customer
            $insert_query = "INSERT INTO pelanggan (nama, telepon, email, alamat, created_at) 
                             VALUES ('" . escape_string($nama) . "', 
                                    '" . escape_string($telepon) . "', 
                                    '" . escape_string($email) . "', 
                                    '" . escape_string($alamat) . "', 
                                    NOW())";
            
            $result = query($insert_query);
            
            if ($result) {
                set_flash_message('success', 'Pelanggan berhasil ditambahkan');
                redirect(base_url('pelanggan/'));
            } else {
                $errors[] = 'Gagal menambahkan pelanggan';
            }
        }
    }
}

// Get customer for edit
$edit_customer = null;
if (isset($_GET['action']) && $_GET['action'] === 'edit' && isset($_GET['id'])) {
    $customer_id = (int)$_GET['id'];
    $edit_customer = get_row("SELECT * FROM pelanggan WHERE id = $customer_id");
    
    if (!$edit_customer) {
        set_flash_message('error', 'Pelanggan tidak ditemukan');
        redirect(base_url('pelanggan/'));
    }
}

// Include header
include '../includes/header.php';
?>

<div class="row">
    <div class="col-lg-12">
        <div class="card mb-4">
            <div class="card-header d-flex justify-content-between align-items-center">
                <h5 class="mb-0">Daftar Pelanggan</h5>
                <button type="button" class="btn btn-primary btn-sm" data-bs-toggle="modal" data-bs-target="#addCustomerModal">
                    <i class="fas fa-plus me-1"></i> Tambah Pelanggan
                </button>
            </div>
            <div class="card-body">
                <!-- Search Form -->
                <form action="" method="get" class="mb-4">
                    <div class="row g-3">
                        <div class="col-md-8">
                            <div class="input-group">
                                <span class="input-group-text"><i class="fas fa-search"></i></span>
                                <input type="text" class="form-control" name="search" placeholder="Cari nama, telepon, email, atau alamat..." value="<?= htmlspecialchars($search) ?>">
                                <button type="submit" class="btn btn-primary">Cari</button>
                                <a href="<?= base_url('pelanggan/') ?>" class="btn btn-secondary">Reset</a>
                            </div>
                        </div>
                    </div>
                </form>
                
                <!-- Customers Table -->
                <div class="table-responsive">
                    <table class="table table-striped table-hover align-middle">
                        <thead>
                            <tr>
                                <th width="5%">No</th>
                                <th width="20%">Nama</th>
                                <th width="15%">Telepon</th>
                                <th width="20%">Email</th>
                                <th width="25%">Alamat</th>
                                <th width="15%">Aksi</th>
                            </tr>
                        </thead>
                        <tbody>
                            <?php if (empty($customers)): ?>
                            <tr>
                                <td colspan="6" class="text-center py-4">Tidak ada data pelanggan</td>
                            </tr>
                            <?php else: ?>
                                <?php $no = $offset + 1; foreach ($customers as $customer): ?>
                                <tr>
                                    <td><?= $no++ ?></td>
                                    <td><?= $customer['nama'] ?></td>
                                    <td><?= $customer['telepon'] ?></td>
                                    <td><?= $customer['email'] ?></td>
                                    <td><?= $customer['alamat'] ?></td>
                                    <td>
                                        <button type="button" class="btn btn-sm btn-warning me-1 edit-customer" 
                                                data-id="<?= $customer['id'] ?>"
                                                data-nama="<?= htmlspecialchars($customer['nama']) ?>"
                                                data-telepon="<?= htmlspecialchars($customer['telepon']) ?>"
                                                data-email="<?= htmlspecialchars($customer['email']) ?>"
                                                data-alamat="<?= htmlspecialchars($customer['alamat']) ?>"
                                                data-bs-toggle="modal" data-bs-target="#addCustomerModal">
                                            <i class="fas fa-edit"></i>
                                        </button>
                                        <a href="<?= base_url('pelanggan/index.php?action=delete&id=' . $customer['id']) ?>" class="btn btn-sm btn-danger btn-delete">
                                            <i class="fas fa-trash"></i>
                                        </a>
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
                            <a class="page-link" href="?page=<?= $page - 1 ?>&search=<?= urlencode($search) ?>" aria-label="Previous">
                                <span aria-hidden="true">&laquo;</span>
                            </a>
                        </li>
                        <?php endif; ?>
                        
                        <?php for ($i = max(1, $page - 2); $i <= min($total_pages, $page + 2); $i++): ?>
                        <li class="page-item <?= $i === $page ? 'active' : '' ?>">
                            <a class="page-link" href="?page=<?= $i ?>&search=<?= urlencode($search) ?>"><?= $i ?></a>
                        </li>
                        <?php endfor; ?>
                        
                        <?php if ($page < $total_pages): ?>
                        <li class="page-item">
                            <a class="page-link" href="?page=<?= $page + 1 ?>&search=<?= urlencode($search) ?>" aria-label="Next">
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

<!-- Add/Edit Customer Modal -->
<div class="modal fade" id="addCustomerModal" tabindex="-1" aria-labelledby="addCustomerModalLabel" aria-hidden="true">
    <div class="modal-dialog">
        <div class="modal-content">
            <form action="" method="post">
                <input type="hidden" name="id" id="customer_id" value="">
                <div class="modal-header">
                    <h5 class="modal-title" id="addCustomerModalLabel">Tambah Pelanggan</h5>
                    <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
                </div>
                <div class="modal-body">
                    <?php if (!empty($errors)): ?>
                        <div class="alert alert-danger">
                            <ul class="mb-0">
                                <?php foreach ($errors as $error): ?>
                                    <li><?= $error ?></li>
                                <?php endforeach; ?>
                            </ul>
                        </div>
                    <?php endif; ?>
                    
                    <div class="mb-3">
                        <label for="nama" class="form-label">Nama <span class="text-danger">*</span></label>
                        <input type="text" class="form-control" id="nama" name="nama" required>
                    </div>
                    
                    <div class="mb-3">
                        <label for="telepon" class="form-label">Telepon</label>
                        <input type="text" class="form-control" id="telepon" name="telepon">
                    </div>
                    
                    <div class="mb-3">
                        <label for="email" class="form-label">Email</label>
                        <input type="email" class="form-control" id="email" name="email">
                    </div>
                    
                    <div class="mb-3">
                        <label for="alamat" class="form-label">Alamat</label>
                        <textarea class="form-control" id="alamat" name="alamat" rows="3"></textarea>
                    </div>
                </div>
                <div class="modal-footer">
                    <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Batal</button>
                    <button type="submit" class="btn btn-primary">Simpan</button>
                </div>
            </form>
        </div>
    </div>
</div>

<script>
    document.addEventListener('DOMContentLoaded', function() {
        // Edit customer
        const editButtons = document.querySelectorAll('.edit-customer');
        const modalTitle = document.getElementById('addCustomerModalLabel');
        const customerIdInput = document.getElementById('customer_id');
        const namaInput = document.getElementById('nama');
        const teleponInput = document.getElementById('telepon');
        const emailInput = document.getElementById('email');
        const alamatInput = document.getElementById('alamat');
        
        editButtons.forEach(button => {
            button.addEventListener('click', function() {
                const id = this.getAttribute('data-id');
                const nama = this.getAttribute('data-nama');
                const telepon = this.getAttribute('data-telepon');
                const email = this.getAttribute('data-email');
                const alamat = this.getAttribute('data-alamat');
                
                modalTitle.textContent = 'Edit Pelanggan';
                customerIdInput.value = id;
                namaInput.value = nama;
                teleponInput.value = telepon;
                emailInput.value = email;
                alamatInput.value = alamat;
            });
        });
        
        // Reset modal on close
        const addCustomerModal = document.getElementById('addCustomerModal');
        addCustomerModal.addEventListener('hidden.bs.modal', function() {
            modalTitle.textContent = 'Tambah Pelanggan';
            customerIdInput.value = '';
            namaInput.value = '';
            teleponInput.value = '';
            emailInput.value = '';
            alamatInput.value = '';
        });
    });
</script>

<?php
// Include footer
include '../includes/footer.php';
?>