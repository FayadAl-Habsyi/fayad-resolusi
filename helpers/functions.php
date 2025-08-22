<?php
/**
 * Helper Functions
 * File ini berisi fungsi-fungsi pembantu untuk aplikasi
 */

/**
 * Function untuk sanitasi input
 * 
 * @param string $input Input yang akan disanitasi
 * @return string Input yang sudah disanitasi
 */
function sanitize($input) {
    $input = trim($input);
    $input = stripslashes($input);
    $input = htmlspecialchars($input, ENT_QUOTES, 'UTF-8');
    return $input;
}

/**
 * Function untuk validasi email
 * 
 * @param string $email Email yang akan divalidasi
 * @return bool True jika email valid, false jika tidak
 */
function is_valid_email($email) {
    return filter_var($email, FILTER_VALIDATE_EMAIL) !== false;
}

/**
 * Function untuk generate password hash
 * 
 * @param string $password Password yang akan di-hash
 * @return string Password yang sudah di-hash
 */
function password_hash_custom($password) {
    return password_hash($password, PASSWORD_BCRYPT);
}

/**
 * Function untuk verifikasi password
 * 
 * @param string $password Password yang akan diverifikasi
 * @param string $hash Hash password yang tersimpan
 * @return bool True jika password cocok, false jika tidak
 */
function password_verify_custom($password, $hash) {
    return password_verify($password, $hash);
}

/**
 * Function untuk generate random string
 * 
 * @param int $length Panjang string yang diinginkan
 * @return string Random string
 */
function generate_random_string($length = 10) {
    $characters = '0123456789abcdefghijklmnopqrstuvwxyzABCDEFGHIJKLMNOPQRSTUVWXYZ';
    $charactersLength = strlen($characters);
    $randomString = '';
    for ($i = 0; $i < $length; $i++) {
        $randomString .= $characters[rand(0, $charactersLength - 1)];
    }
    return $randomString;
}

/**
 * Function untuk generate nomor transaksi
 * 
 * @return string Nomor transaksi
 */
function generate_invoice_number() {
    $prefix = 'TRX';
    $date = date('Ymd');
    $random = strtoupper(substr(md5(uniqid(mt_rand(), true)), 0, 4));
    
    // Cek apakah nomor transaksi sudah ada di database
    $invoice_number = $prefix . $date . $random;
    $exists = get_row("SELECT id FROM transaksi WHERE no_transaksi = '$invoice_number'");
    
    // Jika sudah ada, generate ulang
    if ($exists) {
        return generate_invoice_number();
    }
    
    return $invoice_number;
}

/**
 * Function untuk upload file
 * 
 * @param array $file File yang akan diupload ($_FILES['nama_field'])
 * @param string $destination Direktori tujuan
 * @param array $allowed_types Array berisi tipe file yang diizinkan
 * @param int $max_size Ukuran maksimal file dalam bytes
 * @return array Array berisi status upload dan pesan/nama file
 */
function upload_file($file, $destination, $allowed_types = ['jpg', 'jpeg', 'png', 'gif'], $max_size = 2097152) {
    // Cek apakah ada error pada upload
    if ($file['error'] !== UPLOAD_ERR_OK) {
        $error_messages = [
            UPLOAD_ERR_INI_SIZE => 'Ukuran file melebihi batas maksimal yang diizinkan oleh server.',
            UPLOAD_ERR_FORM_SIZE => 'Ukuran file melebihi batas maksimal yang diizinkan oleh form.',
            UPLOAD_ERR_PARTIAL => 'File hanya terupload sebagian.',
            UPLOAD_ERR_NO_FILE => 'Tidak ada file yang diupload.',
            UPLOAD_ERR_NO_TMP_DIR => 'Direktori temporary tidak ditemukan.',
            UPLOAD_ERR_CANT_WRITE => 'Gagal menyimpan file ke disk.',
            UPLOAD_ERR_EXTENSION => 'Upload dihentikan oleh ekstensi PHP.'
        ];
        
        return [
            'status' => false,
            'message' => isset($error_messages[$file['error']]) ? $error_messages[$file['error']] : 'Terjadi kesalahan saat upload file.'
        ];
    }
    
    // Cek ukuran file
    if ($file['size'] > $max_size) {
        return [
            'status' => false,
            'message' => 'Ukuran file terlalu besar. Maksimal ' . ($max_size / 1048576) . ' MB.'
        ];
    }
    
    // Cek tipe file
    $file_ext = strtolower(pathinfo($file['name'], PATHINFO_EXTENSION));
    if (!in_array($file_ext, $allowed_types)) {
        return [
            'status' => false,
            'message' => 'Tipe file tidak diizinkan. Tipe yang diizinkan: ' . implode(', ', $allowed_types)
        ];
    }
    
    // Generate nama file baru untuk menghindari duplikasi
    $new_filename = uniqid() . '.' . $file_ext;
    $upload_path = $destination . $new_filename;
    
    // Buat direktori jika belum ada
    if (!is_dir($destination)) {
        mkdir($destination, 0755, true);
    }
    
    // Upload file
    if (move_uploaded_file($file['tmp_name'], $upload_path)) {
        return [
            'status' => true,
            'filename' => $new_filename
        ];
    } else {
        return [
            'status' => false,
            'message' => 'Gagal mengupload file.'
        ];
    }
}

/**
 * Function untuk menghapus file
 * 
 * @param string $filepath Path lengkap file yang akan dihapus
 * @return bool True jika berhasil dihapus, false jika gagal
 */
function delete_file($filepath) {
    if (file_exists($filepath)) {
        return unlink($filepath);
    }
    return false;
}

/**
 * Function untuk mengecek apakah request adalah AJAX request
 * 
 * @return bool True jika AJAX request, false jika bukan
 */
function is_ajax_request() {
    return (!empty($_SERVER['HTTP_X_REQUESTED_WITH']) && strtolower($_SERVER['HTTP_X_REQUESTED_WITH']) === 'xmlhttprequest');
}

/**
 * Function untuk mengirim response JSON
 * 
 * @param array $data Data yang akan dikirim sebagai JSON
 * @param int $status_code HTTP status code
 * @return void
 */
function json_response($data, $status_code = 200) {
    http_response_code($status_code);
    header('Content-Type: application/json');
    echo json_encode($data);
    exit;
}

/**
 * Function untuk mendapatkan URL saat ini
 * 
 * @return string URL saat ini
 */
function current_url() {
    $protocol = (!empty($_SERVER['HTTPS']) && $_SERVER['HTTPS'] !== 'off' || $_SERVER['SERVER_PORT'] == 443) ? "https://" : "http://";
    $domain = $_SERVER['HTTP_HOST'];
    $uri = $_SERVER['REQUEST_URI'];
    return $protocol . $domain . $uri;
}

/**
 * Function untuk mendapatkan base URL
 * 
 * @return string Base URL
 */
function base_url($path = '') {
    return BASE_URL . $path;
}

/**
 * Function untuk mendapatkan asset URL
 * 
 * @param string $path Path relatif terhadap direktori assets
 * @return string Asset URL
 */
function asset_url($path = '') {
    return base_url('assets/' . $path);
}

/**
 * Function untuk mendapatkan upload URL
 * 
 * @param string $path Path relatif terhadap direktori uploads
 * @return string Upload URL
 */
function upload_url($path = '') {
    return asset_url('uploads/' . $path);
}

/**
 * Function untuk pagination
 * 
 * @param int $total_records Total records
 * @param int $per_page Records per page
 * @param int $current_page Current page
 * @param string $url_format URL format dengan placeholder {page}
 * @return array Array berisi informasi pagination
 */
function paginate($total_records, $per_page, $current_page, $url_format) {
    $total_pages = ceil($total_records / $per_page);
    
    // Validasi current page
    $current_page = max(1, min($current_page, $total_pages));
    
    // Hitung offset untuk query
    $offset = ($current_page - 1) * $per_page;
    
    // Generate links
    $links = [];
    
    // Previous link
    if ($current_page > 1) {
        $links['prev'] = str_replace('{page}', $current_page - 1, $url_format);
    } else {
        $links['prev'] = null;
    }
    
    // Page links
    $links['pages'] = [];
    $range = 2; // Jumlah halaman yang ditampilkan di kiri dan kanan current page
    
    for ($i = max(1, $current_page - $range); $i <= min($total_pages, $current_page + $range); $i++) {
        $links['pages'][$i] = str_replace('{page}', $i, $url_format);
    }
    
    // Next link
    if ($current_page < $total_pages) {
        $links['next'] = str_replace('{page}', $current_page + 1, $url_format);
    } else {
        $links['next'] = null;
    }
    
    return [
        'total_records' => $total_records,
        'per_page' => $per_page,
        'total_pages' => $total_pages,
        'current_page' => $current_page,
        'offset' => $offset,
        'links' => $links
    ];
}