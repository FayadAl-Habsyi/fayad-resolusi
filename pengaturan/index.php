<?php
/**
 * Settings Page
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
$active_menu = 'pengaturan';

// Set page title
$page_title = 'Pengaturan Aplikasi';

// Get current settings
$query = "SELECT * FROM pengaturan";
$settings = query($query);

// Convert settings array to key-value pairs
$settings_data = [];
foreach ($settings as $setting) {
    $settings_data[$setting['kunci']] = $setting['nilai'];
}

// Process form submission
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    // Store settings
    $nama_toko = isset($_POST['nama_toko']) ? sanitize($_POST['nama_toko']) : '';
    $alamat_toko = isset($_POST['alamat_toko']) ? sanitize($_POST['alamat_toko']) : '';
    $telepon_toko = isset($_POST['telepon_toko']) ? sanitize($_POST['telepon_toko']) : '';
    $email_toko = isset($_POST['email_toko']) ? sanitize($_POST['email_toko']) : '';
    $mata_uang = isset($_POST['mata_uang']) ? sanitize($_POST['mata_uang']) : 'Rp';
    $pajak = isset($_POST['pajak']) ? (float)$_POST['pajak'] : 0;
    $logo_toko = isset($_FILES['logo_toko']) ? $_FILES['logo_toko'] : null;
    $footer_struk = isset($_POST['footer_struk']) ? sanitize($_POST['footer_struk']) : '';
    
    // Validation
    $errors = [];
    
    if (empty($nama_toko)) {
        $errors[] = 'Nama toko harus diisi';
    }
    
    if (!empty($email_toko) && !validate_email($email_toko)) {
        $errors[] = 'Format email tidak valid';
    }
    
    if ($pajak < 0 || $pajak > 100) {
        $errors[] = 'Pajak harus antara 0-100%';
    }
    
    // Process logo upload if provided
    $logo_path = isset($settings_data['logo_toko']) ? $settings_data['logo_toko'] : '';
    
    if ($logo_toko && $logo_toko['size'] > 0) {
        $allowed_types = ['image/jpeg', 'image/png', 'image/gif'];
        $max_size = 2 * 1024 * 1024; // 2MB
        
        if (!in_array($logo_toko['type'], $allowed_types)) {
            $errors[] = 'Tipe file logo tidak valid. Hanya JPG, PNG, dan GIF yang diperbolehkan';
        } elseif ($logo_toko['size'] > $max_size) {
            $errors[] = 'Ukuran file logo terlalu besar. Maksimal 2MB';
        } else {
            // Upload new logo
            $upload_dir = UPLOAD_PATH . '/logo/';
            
            // Create directory if not exists
            if (!file_exists($upload_dir)) {
                mkdir($upload_dir, 0777, true);
            }
            
            $file_name = 'logo_' . time() . '_' . basename($logo_toko['name']);
            $upload_path = $upload_dir . $file_name;
            
            if (move_uploaded_file($logo_toko['tmp_name'], $upload_path)) {
                // Delete old logo if exists
                if (!empty($logo_path) && file_exists(ROOT_PATH . $logo_path)) {
                    unlink(ROOT_PATH . $logo_path);
                }
                
                $logo_path = '/uploads/logo/' . $file_name;
            } else {
                $errors[] = 'Gagal mengupload logo';
            }
        }
    }
    
    // If no errors, save settings
    if (empty($errors)) {
        // Settings to update
        $settings_to_update = [
            'nama_toko' => $nama_toko,
            'alamat_toko' => $alamat_toko,
            'telepon_toko' => $telepon_toko,
            'email_toko' => $email_toko,
            'mata_uang' => $mata_uang,
            'pajak' => $pajak,
            'logo_toko' => $logo_path,
            'footer_struk' => $footer_struk
        ];
        
        $success = true;
        
        foreach ($settings_to_update as $key => $value) {
            $update_query = "UPDATE pengaturan SET nilai = '" . escape_string($value) . "' WHERE kunci = '" . escape_string($key) . "'";
            $result = query($update_query);
            
            if (!$result) {
                $success = false;
                break;
            }
        }
        
        if ($success) {
            set_flash_message('success', 'Pengaturan berhasil disimpan');
            redirect(base_url('pengaturan/'));
        } else {
            $errors[] = 'Gagal menyimpan pengaturan';
        }
    }
}

// Include header
include '../includes/header.php';
?>

<div class="row">
    <div class="col-lg-10 mx-auto">
        <div class="card mb-4">
            <div class="card-header">
                <h5 class="mb-0">Pengaturan Aplikasi</h5>
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
                
                <form action="" method="post" enctype="multipart/form-data">
                    <div class="row">
                        <!-- Left Column -->
                        <div class="col-md-6">
                            <h5 class="border-bottom pb-2 mb-3">Informasi Toko</h5>
                            
                            <div class="mb-3">
                                <label for="nama_toko" class="form-label">Nama Toko <span class="text-danger">*</span></label>
                                <input type="text" class="form-control" id="nama_toko" name="nama_toko" value="<?= isset($settings_data['nama_toko']) ? $settings_data['nama_toko'] : '' ?>" required>
                            </div>
                            
                            <div class="mb-3">
                                <label for="alamat_toko" class="form-label">Alamat Toko</label>
                                <textarea class="form-control" id="alamat_toko" name="alamat_toko" rows="3"><?= isset($settings_data['alamat_toko']) ? $settings_data['alamat_toko'] : '' ?></textarea>
                            </div>
                            
                            <div class="mb-3">
                                <label for="telepon_toko" class="form-label">Telepon Toko</label>
                                <input type="text" class="form-control" id="telepon_toko" name="telepon_toko" value="<?= isset($settings_data['telepon_toko']) ? $settings_data['telepon_toko'] : '' ?>">
                            </div>
                            
                            <div class="mb-3">
                                <label for="email_toko" class="form-label">Email Toko</label>
                                <input type="email" class="form-control" id="email_toko" name="email_toko" value="<?= isset($settings_data['email_toko']) ? $settings_data['email_toko'] : '' ?>">
                            </div>
                            
                            <div class="mb-3">
                                <label for="logo_toko" class="form-label">Logo Toko</label>
                                <input type="file" class="form-control" id="logo_toko" name="logo_toko" accept="image/*">
                                <div class="form-text">Format: JPG, PNG, GIF. Maksimal 2MB.</div>
                                
                                <?php if (!empty($settings_data['logo_toko'])): ?>
                                <div class="mt-2">
                                    <img src="<?= base_url($settings_data['logo_toko']) ?>" alt="Logo Toko" class="img-thumbnail" style="max-height: 100px;">
                                </div>
                                <?php endif; ?>
                            </div>
                        </div>
                        
                        <!-- Right Column -->
                        <div class="col-md-6">
                            <h5 class="border-bottom pb-2 mb-3">Pengaturan Aplikasi</h5>
                            
                            <div class="mb-3">
                                <label for="mata_uang" class="form-label">Mata Uang</label>
                                <input type="text" class="form-control" id="mata_uang" name="mata_uang" value="<?= isset($settings_data['mata_uang']) ? $settings_data['mata_uang'] : 'Rp' ?>">
                            </div>
                            
                            <div class="mb-3">
                                <label for="pajak" class="form-label">Pajak (%)</label>
                                <input type="number" class="form-control" id="pajak" name="pajak" min="0" max="100" step="0.1" value="<?= isset($settings_data['pajak']) ? $settings_data['pajak'] : '0' ?>">
                                <div class="form-text">Masukkan nilai pajak dalam persen (0-100).</div>
                            </div>
                            
                            <div class="mb-3">
                                <label for="footer_struk" class="form-label">Footer Struk</label>
                                <textarea class="form-control" id="footer_struk" name="footer_struk" rows="3"><?= isset($settings_data['footer_struk']) ? $settings_data['footer_struk'] : '' ?></textarea>
                                <div class="form-text">Teks yang akan ditampilkan di bagian bawah struk.</div>
                            </div>
                        </div>
                    </div>
                    
                    <div class="text-end mt-4">
                        <button type="reset" class="btn btn-secondary me-2">Reset</button>
                        <button type="submit" class="btn btn-primary">Simpan Pengaturan</button>
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