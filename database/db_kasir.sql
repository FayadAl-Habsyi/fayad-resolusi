-- Database untuk Aplikasi Kasir

CREATE DATABASE IF NOT EXISTS db_kasir;
USE db_kasir;

-- Tabel Users (Admin dan Kasir)
CREATE TABLE IF NOT EXISTS users (
    id INT AUTO_INCREMENT PRIMARY KEY,
    username VARCHAR(50) NOT NULL UNIQUE,
    password VARCHAR(255) NOT NULL,
    nama_lengkap VARCHAR(100) NOT NULL,
    role ENUM('admin', 'kasir') NOT NULL,
    email VARCHAR(100),
    no_telp VARCHAR(15),
    foto VARCHAR(255) DEFAULT 'default.jpg',
    status TINYINT(1) DEFAULT 1,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    updated_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP
);

-- Tabel Kategori Produk
CREATE TABLE IF NOT EXISTS kategori (
    id INT AUTO_INCREMENT PRIMARY KEY,
    nama_kategori VARCHAR(100) NOT NULL,
    deskripsi TEXT,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    updated_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP
);

-- Tabel Produk
CREATE TABLE IF NOT EXISTS produk (
    id INT AUTO_INCREMENT PRIMARY KEY,
    kode_produk VARCHAR(50) NOT NULL UNIQUE,
    nama_produk VARCHAR(100) NOT NULL,
    id_kategori INT,
    harga_beli DECIMAL(12,2) NOT NULL,
    harga_jual DECIMAL(12,2) NOT NULL,
    stok INT NOT NULL DEFAULT 0,
    satuan VARCHAR(20),
    gambar VARCHAR(255) DEFAULT 'default-product.jpg',
    deskripsi TEXT,
    status TINYINT(1) DEFAULT 1,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    updated_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
    FOREIGN KEY (id_kategori) REFERENCES kategori(id) ON DELETE SET NULL
);

-- Tabel Pelanggan
CREATE TABLE IF NOT EXISTS pelanggan (
    id INT AUTO_INCREMENT PRIMARY KEY,
    nama VARCHAR(100) NOT NULL,
    alamat TEXT,
    no_telp VARCHAR(15),
    email VARCHAR(100),
    poin INT DEFAULT 0,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    updated_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP
);

-- Tabel Transaksi
CREATE TABLE IF NOT EXISTS transaksi (
    id INT AUTO_INCREMENT PRIMARY KEY,
    no_transaksi VARCHAR(50) NOT NULL UNIQUE,
    id_user INT NOT NULL,
    id_pelanggan INT,
    tanggal DATETIME NOT NULL,
    total_harga DECIMAL(12,2) NOT NULL,
    diskon DECIMAL(12,2) DEFAULT 0,
    total_akhir DECIMAL(12,2) NOT NULL,
    tunai DECIMAL(12,2) NOT NULL,
    kembalian DECIMAL(12,2) NOT NULL,
    catatan TEXT,
    status ENUM('pending', 'selesai', 'batal') DEFAULT 'selesai',
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    updated_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
    FOREIGN KEY (id_user) REFERENCES users(id) ON DELETE RESTRICT,
    FOREIGN KEY (id_pelanggan) REFERENCES pelanggan(id) ON DELETE SET NULL
);

-- Tabel Detail Transaksi
CREATE TABLE IF NOT EXISTS detail_transaksi (
    id INT AUTO_INCREMENT PRIMARY KEY,
    id_transaksi INT NOT NULL,
    id_produk INT NOT NULL,
    jumlah INT NOT NULL,
    harga_satuan DECIMAL(12,2) NOT NULL,
    subtotal DECIMAL(12,2) NOT NULL,
    diskon DECIMAL(12,2) DEFAULT 0,
    total DECIMAL(12,2) NOT NULL,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    FOREIGN KEY (id_transaksi) REFERENCES transaksi(id) ON DELETE CASCADE,
    FOREIGN KEY (id_produk) REFERENCES produk(id) ON DELETE RESTRICT
);

-- Tabel Stok Masuk
CREATE TABLE IF NOT EXISTS stok_masuk (
    id INT AUTO_INCREMENT PRIMARY KEY,
    id_produk INT NOT NULL,
    jumlah INT NOT NULL,
    tanggal DATE NOT NULL,
    keterangan TEXT,
    id_user INT NOT NULL,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    FOREIGN KEY (id_produk) REFERENCES produk(id) ON DELETE RESTRICT,
    FOREIGN KEY (id_user) REFERENCES users(id) ON DELETE RESTRICT
);

-- Tabel Pengaturan
CREATE TABLE IF NOT EXISTS pengaturan (
    id INT AUTO_INCREMENT PRIMARY KEY,
    nama_toko VARCHAR(100) NOT NULL,
    alamat TEXT,
    no_telp VARCHAR(15),
    email VARCHAR(100),
    logo VARCHAR(255),
    footer_struk TEXT,
    pajak DECIMAL(5,2) DEFAULT 0,
    updated_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP
);

-- Insert data default
INSERT INTO users (username, password, nama_lengkap, role, email) VALUES
('admin', '$2y$10$wCn7BjZEwMcZV.6LVEIrKOUFvxQQlPwvtmQ4Ug5s.GVpRz9.7Hpxm', 'Administrator', 'admin', 'admin@example.com'),
('kasir', '$2y$10$wCn7BjZEwMcZV.6LVEIrKOUFvxQQlPwvtmQ4Ug5s.GVpRz9.7Hpxm', 'Kasir Default', 'kasir', 'kasir@example.com');

INSERT INTO kategori (nama_kategori, deskripsi) VALUES
('Makanan', 'Kategori untuk produk makanan'),
('Minuman', 'Kategori untuk produk minuman'),
('Snack', 'Kategori untuk produk snack/cemilan'),
('ATK', 'Alat Tulis Kantor');

INSERT INTO produk (kode_produk, nama_produk, id_kategori, harga_beli, harga_jual, stok, satuan) VALUES
('P001', 'Mie Instan', 1, 2500, 3000, 50, 'pcs'),
('P002', 'Air Mineral 600ml', 2, 2000, 3000, 100, 'botol'),
('P003', 'Roti Tawar', 1, 8000, 10000, 20, 'pcs'),
('P004', 'Kopi Sachet', 2, 1500, 2000, 100, 'sachet'),
('P005', 'Keripik Kentang', 3, 6000, 8000, 30, 'pcs'),
('P006', 'Pulpen', 4, 1500, 2500, 50, 'pcs');

INSERT INTO pelanggan (nama, alamat, no_telp) VALUES
('Umum', '-', '-'),
('John Doe', 'Jl. Contoh No. 123', '081234567890'),
('Jane Smith', 'Jl. Sample No. 456', '089876543210');

INSERT INTO pengaturan (nama_toko, alamat, no_telp, email, footer_struk) VALUES
('CashFlow POS', 'Jl. Contoh No. 123, Kota Contoh', '081234567890', 'info@cashflow.com', 'Terima kasih telah berbelanja di toko kami');