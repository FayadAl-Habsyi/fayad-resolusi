<?php
/**
 * User Form (Add/Edit)
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

// Check if edit mode
$edit_mode = false;
$user_id = isset($_GET['id']) ? (int)$_GET['id'] : 0;

if ($user_id > 0) {
    $edit_mode = true;
    $user = get_row("SELECT * FROM users WHERE id = $user_id");
    
    if (!$user) {
        set_flash_message('error', 'Pengguna tidak ditemukan');
        redirect(base_url('pengguna/'));
    }
    
    $page_title = 'Edit Pengguna';
} else {
    $page_title = 'Tambah Pengguna';
}

// Process form submission
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $username = isset($_POST['username']) ? sanitize($_POST['username']) : '';
    $nama = isset($_POST['nama']) ? sanitize($_POST['nama']) : '';
    $email = isset($_POST['email']) ? sanitize($_POST['email']) : '';
    $password = isset($_POST['password']) ? $_POST['password'] : ''; // Don't sanitize password
    $confirm_password = isset($_POST['confirm_password']) ? $_POST['confirm_password'] : '';
    $role = isset($_POST['role']) ? sanitize($_POST['role']) : '';
    $status = isset($_POST['status']) ? sanitize($_POST['status']) : 'aktif';
    
    // Validation
    $errors = [];
    
    if (empty($username)) {
        $errors[] = 'Username harus diisi';
    } elseif (!$edit_mode || ($edit_mode && $username !== $user['username'])) {
        // Check if username already exists
        $username_exists = get_var("SELECT COUNT(*) FROM users WHERE username = '" . escape_string($username) . "' AND id != $user_id");
        if ($username_exists > 0) {
            $errors[] = 'Username sudah digunakan';
        }
    }
    
    if (empty($nama)) {
        $errors[] = 'Nama harus diisi';
    }
    
    if (empty($email)) {
        $errors[] = 'Email harus diisi';
    } elseif (!validate_email($email)) {
        $errors[] = 'Format email tidak valid';
    } elseif (!$edit_mode || ($edit_mode && $email !== $user['email'])) {
        // Check if email already exists
        $email_exists = get_var("SELECT COUNT(*) FROM users WHERE email = '" . escape_string($email) . "' AND id != $user_id");
        if ($email_exists > 0) {
            $errors[] = 'Email sudah digunakan';
        }
    }
    
    if (!$edit_mode || !empty($password)) {
        if (empty($password)) {
            $errors[] = 'Password harus diisi';
        } elseif (strlen($password) < 6) {
            $errors[] = 'Password minimal 6 karakter';
        } elseif ($password !== $confirm_password) {
            $errors[] = 'Konfirmasi password tidak sesuai';
        }
    }
    
    if (empty($role) || !in_array($role, ['admin', 'kasir'])) {
        $errors[] = 'Role harus dipilih';
    }
    
    if (empty($status) || !in_array($status, ['aktif', 'nonaktif'])) {
        $errors[] = 'Status harus dipilih';
    }
    
    // If no errors, save user
    if (empty($errors)) {
        if ($edit_mode) {
            // Update user
            if (!empty($password)) {
                // With password change
                $hashed_password = password_hash($password, PASSWORD_DEFAULT);
                $update_query = "UPDATE users SET 
                                username = '" . escape_string($username) . "', 
                                nama = '" . escape_string($nama) . "', 
                                email = '" . escape_string($email) . "', 
                                password = '" . escape_string($hashed_password) . "', 
                                role = '" . escape_string($role) . "', 
                                status = '" . escape_string($status) . "' 
                                WHERE id = $user_id";
            } else {
                // Without password change
                $update_query = "UPDATE users SET 
                                username = '" . escape_string($username) . "', 
                                nama = '" . escape_string($nama) . "', 
                                email = '" . escape_string($email) . "', 
                                role = '" . escape_string($role) . "', 
                                status = '" . escape_string($status) . "' 
                                WHERE id = $user_id";
            }
            
            $result = query($update_query);
            
            if ($result) {
                set_flash_message('success', 'Pengguna berhasil diperbarui');
                redirect(base_url('pengguna/'));
            } else {
                $errors[] = 'Gagal memperbarui pengguna';
            }
        } else {
            // Insert new user
            $hashed_password = password_hash($password, PASSWORD_DEFAULT);
            $insert_query = "INSERT INTO users (username, nama, email, password, role, status, created_at) 
                             VALUES ('" . escape_string($username) . "', 
                                    '" . escape_string($nama) . "', 
                                    '" . escape_string($email) . "', 
                                    '" . escape_string($hashed_password) . "', 
                                    '" . escape_string($role) . "', 
                                    '" . escape_string($status) . "', 
                                    NOW())";
            
            $result = query($insert_query);
            
            if ($result) {
                set_flash_message('success', 'Pengguna berhasil ditambahkan');
                redirect(base_url('pengguna/'));
            } else {
                $errors[] = 'Gagal menambahkan pengguna';
            }
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
                <h5 class="mb-0"><?= $edit_mode ? 'Edit Pengguna' : 'Tambah Pengguna' ?></h5>
                <a href="<?= base_url('pengguna/') ?>" class="btn btn-secondary btn-sm">
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
                        <label for="username" class="form-label">Username <span class="text-danger">*</span></label>
                        <input type="text" class="form-control" id="username" name="username" value="<?= $edit_mode ? $user['username'] : (isset($_POST['username']) ? $_POST['username'] : '') ?>" required>
                    </div>
                    
                    <div class="mb-3">
                        <label for="nama" class="form-label">Nama Lengkap <span class="text-danger">*</span></label>
                        <input type="text" class="form-control" id="nama" name="nama" value="<?= $edit_mode ? $user['nama'] : (isset($_POST['nama']) ? $_POST['nama'] : '') ?>" required>
                    </div>
                    
                    <div class="mb-3">
                        <label for="email" class="form-label">Email <span class="text-danger">*</span></label>
                        <input type="email" class="form-control" id="email" name="email" value="<?= $edit_mode ? $user['email'] : (isset($_POST['email']) ? $_POST['email'] : '') ?>" required>
                    </div>
                    
                    <div class="mb-3">
                        <label for="password" class="form-label"><?= $edit_mode ? 'Password (Kosongkan jika tidak diubah)' : 'Password <span class="text-danger">*</span>' ?></label>
                        <input type="password" class="form-control" id="password" name="password" <?= $edit_mode ? '' : 'required' ?>>
                        <?php if ($edit_mode): ?>
                            <div class="form-text">Kosongkan jika tidak ingin mengubah password.</div>
                        <?php endif; ?>
                    </div>
                    
                    <div class="mb-3">
                        <label for="confirm_password" class="form-label"><?= $edit_mode ? 'Konfirmasi Password (Kosongkan jika tidak diubah)' : 'Konfirmasi Password <span class="text-danger">*</span>' ?></label>
                        <input type="password" class="form-control" id="confirm_password" name="confirm_password" <?= $edit_mode ? '' : 'required' ?>>
                    </div>
                    
                    <div class="mb-3">
                        <label for="role" class="form-label">Role <span class="text-danger">*</span></label>
                        <select class="form-select" id="role" name="role" required>
                            <option value="">Pilih Role</option>
                            <option value="admin" <?= $edit_mode && $user['role'] === 'admin' ? 'selected' : (isset($_POST['role']) && $_POST['role'] === 'admin' ? 'selected' : '') ?>>Admin</option>
                            <option value="kasir" <?= $edit_mode && $user['role'] === 'kasir' ? 'selected' : (isset($_POST['role']) && $_POST['role'] === 'kasir' ? 'selected' : '') ?>>Kasir</option>
                        </select>
                    </div>
                    
                    <div class="mb-3">
                        <label for="status" class="form-label">Status <span class="text-danger">*</span></label>
                        <select class="form-select" id="status" name="status" required>
                            <option value="aktif" <?= $edit_mode && $user['status'] === 'aktif' ? 'selected' : (isset($_POST['status']) && $_POST['status'] === 'aktif' ? 'selected' : '') ?>>Aktif</option>
                            <option value="nonaktif" <?= $edit_mode && $user['status'] === 'nonaktif' ? 'selected' : (isset($_POST['status']) && $_POST['status'] === 'nonaktif' ? 'selected' : '') ?>>Nonaktif</option>
                        </select>
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