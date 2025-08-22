<?php
/**
 * Cetak Struk Penjualan
 */
require_once '../config/config.php';

// Check if user is logged in
if (!is_logged_in()) {
    redirect(base_url('auth/login.php'));
}

// Check if ID is provided
if (!isset($_GET['id']) || empty($_GET['id'])) {
    die('ID transaksi tidak valid');
}

$id = (int)$_GET['id'];

// Get transaction data
$transaction = get_row("SELECT t.*, p.nama as nama_pelanggan, p.no_telp as telp_pelanggan, u.nama as nama_user 
                       FROM transaksi t 
                       LEFT JOIN pelanggan p ON t.id_pelanggan = p.id 
                       LEFT JOIN users u ON t.id_user = u.id 
                       WHERE t.id = $id");

if (!$transaction) {
    die('Transaksi tidak ditemukan');
}

// Get transaction details
$details = query("SELECT d.*, p.nama_produk, p.kode_produk 
                 FROM detail_transaksi d 
                 LEFT JOIN produk p ON d.id_produk = p.id 
                 WHERE d.id_transaksi = $id");

// Get store settings
$settings = get_settings();
?>
<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Struk - <?= $transaction['no_invoice'] ?></title>
    <style>
        body {
            font-family: 'Courier New', Courier, monospace;
            font-size: 12px;
            margin: 0;
            padding: 0;
            width: 80mm;
            margin: 0 auto;
        }
        .receipt {
            padding: 10px;
        }
        .header {
            text-align: center;
            margin-bottom: 10px;
        }
        .store-name {
            font-size: 16px;
            font-weight: bold;
            margin-bottom: 5px;
        }
        .store-address {
            margin-bottom: 5px;
        }
        .store-contact {
            margin-bottom: 10px;
        }
        .invoice-info {
            margin-bottom: 10px;
        }
        .invoice-info div {
            margin-bottom: 3px;
        }
        .divider {
            border-top: 1px dashed #000;
            margin: 10px 0;
        }
        .items {
            width: 100%;
            margin-bottom: 10px;
        }
        .items th {
            text-align: left;
            padding-bottom: 5px;
        }
        .items td {
            padding-bottom: 5px;
        }
        .items .right {
            text-align: right;
        }
        .items .center {
            text-align: center;
        }
        .summary {
            width: 100%;
            margin-bottom: 10px;
        }
        .summary td {
            padding-bottom: 3px;
        }
        .summary .right {
            text-align: right;
        }
        .footer {
            text-align: center;
            margin-top: 10px;
        }
        .bold {
            font-weight: bold;
        }
        @media print {
            body {
                width: 80mm;
                margin: 0;
            }
            .no-print {
                display: none;
            }
        }
    </style>
</head>
<body>
    <div class="receipt">
        <div class="header">
            <div class="store-name"><?= $settings['nama_toko'] ?></div>
            <div class="store-address"><?= $settings['alamat_toko'] ?></div>
            <div class="store-contact"><?= $settings['telepon'] ?></div>
        </div>
        
        <div class="invoice-info">
            <div><strong>No. Invoice:</strong> <?= $transaction['no_invoice'] ?></div>
            <div><strong>Tanggal:</strong> <?= format_datetime($transaction['tanggal']) ?></div>
            <div><strong>Kasir:</strong> <?= $transaction['nama_user'] ?></div>
            <div><strong>Pelanggan:</strong> <?= $transaction['nama_pelanggan'] ?? 'Umum' ?></div>
        </div>
        
        <div class="divider"></div>
        
        <table class="items" cellspacing="0" cellpadding="0">
            <thead>
                <tr>
                    <th width="50%">Item</th>
                    <th width="15%" class="center">Qty</th>
                    <th width="35%" class="right">Subtotal</th>
                </tr>
            </thead>
            <tbody>
                <?php foreach ($details as $detail): ?>
                <tr>
                    <td>
                        <?= $detail['nama_produk'] ?><br>
                        <small><?= format_rupiah($detail['harga']) ?></small>
                    </td>
                    <td class="center"><?= $detail['jumlah'] ?></td>
                    <td class="right"><?= format_rupiah($detail['total']) ?></td>
                </tr>
                <?php endforeach; ?>
            </tbody>
        </table>
        
        <div class="divider"></div>
        
        <table class="summary" cellspacing="0" cellpadding="0">
            <tr>
                <td width="60%">Subtotal</td>
                <td width="40%" class="right"><?= format_rupiah($transaction['subtotal']) ?></td>
            </tr>
            <?php if ($transaction['diskon'] > 0): ?>
            <tr>
                <td>Diskon</td>
                <td class="right">- <?= format_rupiah($transaction['diskon']) ?></td>
            </tr>
            <?php endif; ?>
            <?php if ($transaction['pajak'] > 0): ?>
            <tr>
                <td>Pajak</td>
                <td class="right"><?= format_rupiah($transaction['pajak']) ?></td>
            </tr>
            <?php endif; ?>
            <tr class="bold">
                <td>Total</td>
                <td class="right"><?= format_rupiah($transaction['total']) ?></td>
            </tr>
            <tr>
                <td>Bayar</td>
                <td class="right"><?= format_rupiah($transaction['bayar']) ?></td>
            </tr>
            <tr>
                <td>Kembali</td>
                <td class="right"><?= format_rupiah($transaction['kembali']) ?></td>
            </tr>
        </table>
        
        <?php if (!empty($transaction['catatan'])): ?>
        <div class="divider"></div>
        <div>
            <strong>Catatan:</strong><br>
            <?= nl2br(htmlspecialchars($transaction['catatan'])) ?>
        </div>
        <?php endif; ?>
        
        <div class="divider"></div>
        
        <div class="footer">
            <p>Terima Kasih Atas Kunjungan Anda</p>
            <p><?= $settings['footer_struk'] ?></p>
        </div>
        
        <div class="no-print" style="margin-top: 20px; text-align: center;">
            <button onclick="window.print()" style="padding: 10px 20px; cursor: pointer;">Cetak Struk</button>
        </div>
    </div>
    
    <script>
        // Auto print when page loads
        window.onload = function() {
            window.print();
        }
    </script>
</body>
</html>