<?php
/**
 * Konfigurasi Database
 * File ini berisi konfigurasi untuk koneksi ke database MySQL
 */

// Konfigurasi database
define('DB_HOST', 'localhost');
define('DB_USER', 'root'); // Sesuaikan dengan username database Anda
define('DB_PASS', ''); // Sesuaikan dengan password database Anda
define('DB_NAME', 'db_kasir');

// Membuat koneksi ke database
try {
    $conn = new mysqli(DB_HOST, DB_USER, DB_PASS, DB_NAME);
    
    // Cek koneksi
    if ($conn->connect_error) {
        throw new Exception("Koneksi database gagal: " . $conn->connect_error);
    }
    
    // Set charset ke utf8
    $conn->set_charset("utf8");
    
} catch (Exception $e) {
    // Tampilkan pesan error jika koneksi gagal
    die("Error: " . $e->getMessage());
}

/**
 * Function untuk menjalankan query dan mengembalikan hasilnya
 * 
 * @param string $sql Query SQL yang akan dijalankan
 * @return mixed Hasil query (array assoc untuk SELECT, boolean untuk query lainnya)
 */
function query($sql) {
    global $conn;
    $result = $conn->query($sql);
    
    if (!$result) {
        die("Query Error: " . $conn->error . "<br>Query: " . $sql);
    }
    
    // Jika query adalah SELECT, kembalikan hasilnya sebagai array
    if (strpos(strtoupper($sql), 'SELECT') === 0) {
        $data = [];
        while ($row = $result->fetch_assoc()) {
            $data[] = $row;
        }
        return $data;
    }
    
    // Untuk query INSERT, UPDATE, DELETE, kembalikan true jika berhasil
    return true;
}

/**
 * Function untuk mendapatkan satu baris data dari hasil query
 * 
 * @param string $sql Query SQL yang akan dijalankan
 * @return array|null Baris data sebagai array assoc atau null jika tidak ada data
 */
function get_row($sql) {
    global $conn;
    $result = $conn->query($sql);
    
    if (!$result) {
        die("Query Error: " . $conn->error . "<br>Query: " . $sql);
    }
    
    return $result->fetch_assoc();
}

/**
 * Function untuk mendapatkan satu nilai dari hasil query
 * 
 * @param string $sql Query SQL yang akan dijalankan
 * @return mixed Nilai dari kolom pertama baris pertama hasil query atau null
 */
function get_var($sql) {
    global $conn;
    $result = $conn->query($sql);
    
    if (!$result) {
        die("Query Error: " . $conn->error . "<br>Query: " . $sql);
    }
    
    $row = $result->fetch_row();
    return $row ? $row[0] : null;
}

/**
 * Function untuk mendapatkan ID terakhir dari operasi INSERT
 * 
 * @return int ID terakhir dari operasi INSERT
 */
function last_insert_id() {
    global $conn;
    return $conn->insert_id;
}

/**
 * Function untuk melakukan escape string untuk mencegah SQL Injection
 * 
 * @param string $str String yang akan di-escape
 * @return string String yang sudah di-escape
 */
function escape_string($str) {
    global $conn;
    return $conn->real_escape_string($str);
}