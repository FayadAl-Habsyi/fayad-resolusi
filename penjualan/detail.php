<?php
/**
 * Detail Penjualan Page
 */
require_once '../config/config.php';

// Check if user is logged in
if (!is_logged_in()) {
    redirect(base_url('auth/login.php'));
}

// Set active menu
$active_menu = 'penjualan';

// Check if ID is provided
if (!isset($_GET['id']) || empty($_GET['id'])) {
    set_flash_message('error', 'ID transaksi tidak valid');
    redirect(base_url('penjualan/'));
}

$id = (int)$_GET['id'];

// Get transaction data
$transaction = get_row("SELECT t.*, p.nama as nama_pelanggan, p.no_telp as telp_pelanggan, u.nama as nama_user 
                       FROM transaksi t 
                       LEFT JOIN pelanggan p ON t.id_pelanggan = p.id 
                       LEFT JOIN users u ON t.id_user = u.id 
                       WHERE t.id = $id");

if (!$transaction) {
    set_flash_message('error', 'Transaksi tidak ditemukan');
    redirect(base_url('penjualan/'));
}

// Get transaction details
$details = query("SELECT d.*, p.nama_produk, p.kode_produk 
                 FROM detail_transaksi d 
                 LEFT JOIN produk p ON d.id_produk = p.id 
                 WHERE d.id_transaksi = $id");

// Set page title
$page_title = 'Detail Penjualan: ' . $transaction['no_invoice'];

// Include header
include '../includes/header.php';
?>

<div class="row">
    <div class="col-lg-8">
        <!-- Transaction Details -->
        <div class="card mb-4">
            <div class="card-header d-flex justify-content-between align-items-center">
                <h5 class="mb-0">Detail Transaksi</h5>
                <div>
                    <a href="<?= base_url('penjualan/') ?>" class="btn btn-secondary btn-sm me-2">
                        <i class="fas fa-arrow-left me-1"></i> Kembali
                    </a>
                    <a href="<?= base_url('penjualan/cetak.php?id=' . $id) ?>" class="btn btn-primary btn-sm" target="_blank">
                        <i class="fas fa-print me-1"></i> Cetak
                    </a>
                </div>
            </div>
            <div class="card-body">
                <div class="row mb-4">
                    <div class="col-md-6">
                        <h6 class="fw-bold">Informasi Transaksi</h6>
                        <table class="table table-sm table-borderless">
                            <tr>
                                <td width="40%">No. Invoice</td>
                                <td width="5%">:</td>
                                <td><strong><?= $transaction['no_invoice'] ?></strong></td>
                            </tr>
                            <tr>
                                <td>Tanggal</td>
                                <td>:</td>
                                <td><?= format_datetime($transaction['tanggal']) ?></td>
                            </tr>
                            <tr>
                                <td>Kasir</td>
                                <td>:</td>
                                <td><?= $transaction['nama_user'] ?></td>
                            </tr>
                            <tr>
                                <td>Status</td>
                                <td>:</td>
                                <td>
                                    <?php if ($transaction['status'] === 'selesai'): ?>
                                    <span class="badge bg-success">Selesai</span>
                                    <?php elseif ($transaction['status'] === 'pending'): ?>
                                    <span class="badge bg-warning">Pending</span>
                                    <?php else: ?>
                                    <span class="badge bg-danger">Batal</span>
                                    <?php endif; ?>
                                </td>
                            </tr>
                        </table>
                    </div>
                    <div class="col-md-6">
                        <h6 class="fw-bold">Informasi Pelanggan</h6>
                        <table class="table table-sm table-borderless">
                            <tr>
                                <td width="40%">Nama</td>
                                <td width="5%">:</td>
                                <td><strong><?= $transaction['nama_pelanggan'] ?? 'Umum' ?></strong></td>
                            </tr>
                            <?php if (!empty($transaction['telp_pelanggan'])): ?>
                            <tr>
                                <td>No. Telepon</td>
                                <td>:</td>
                                <td><?= $transaction['telp_pelanggan'] ?></td>
                            </tr>
                            <?php endif; ?>
                        </table>
                    </div>
                </div>
                
                <h6 class="fw-bold mb-3">Item Transaksi</h6>
                <div class="table-responsive">
                    <table class="table table-striped table-hover align-middle">
                        <thead>
                            <tr>
                                <th width="5%">No</th>
                                <th width="15%">Kode</th>
                                <th width="35%">Produk</th>
                                <th width="15%" class="text-end">Harga</th>
                                <th width="10%" class="text-center">Qty</th>
                                <th width="20%" class="text-end">Subtotal</th>
                            </tr>
                        </thead>
                        <tbody>
                            <?php if (empty($details)): ?>
                            <tr>
                                <td colspan="6" class="text-center py-4">Tidak ada data item</td>
                            </tr>
                            <?php else: ?>
                                <?php $no = 1; foreach ($details as $detail): ?>
                                <tr>
                                    <td><?= $no++ ?></td>
                                    <td><?= $detail['kode_produk'] ?></td>
                                    <td><?= $detail['nama_produk'] ?></td>
                                    <td class="text-end"><?= format_rupiah($detail['harga']) ?></td>
                                    <td class="text-center"><?= $detail['jumlah'] ?></td>
                                    <td class="text-end"><?= format_rupiah($detail['total']) ?></td>
                                </tr>
                                <?php endforeach; ?>
                            <?php endif; ?>
                        </tbody>
                        <tfoot>
                            <tr>
                                <td colspan="5" class="text-end fw-bold">Subtotal</td>
                                <td class="text-end"><?= format_rupiah($transaction['subtotal']) ?></td>
                            </tr>
                            <tr>
                                <td colspan="5" class="text-end fw-bold">Diskon</td>
                                <td class="text-end"><?= format_rupiah($transaction['diskon']) ?></td>
                            </tr>
                            <tr>
                                <td colspan="5" class="text-end fw-bold">Pajak</td>
                                <td class="text-end"><?= format_rupiah($transaction['pajak']) ?></td>
                            </tr>
                            <tr>
                                <td colspan="5" class="text-end fw-bold">Total</td>
                                <td class="text-end fw-bold"><?= format_rupiah($transaction['total']) ?></td>
                            </tr>
                            <tr>
                                <td colspan="5" class="text-end fw-bold">Bayar</td>
                                <td class="text-end"><?= format_rupiah($transaction['bayar']) ?></td>
                            </tr>
                            <tr>
                                <td colspan="5" class="text-end fw-bold">Kembali</td>
                                <td class="text-end"><?= format_rupiah($transaction['kembali']) ?></td>
                            </tr>
                        </tfoot>
                    </table>
                </div>
                
                <?php if (!empty($transaction['catatan'])): ?>
                <div class="mt-4">
                    <h6 class="fw-bold mb-2">Catatan:</h6>
                    <div class="p-3 bg-light rounded">
                        <?= nl2br(htmlspecialchars($transaction['catatan'])) ?>
                    </div>
                </div>
                <?php endif; ?>
            </div>
        </div>
    </div>
    
    <div class="col-lg-4">
        <!-- Payment Info -->
        <div class="card mb-4">
            <div class="card-header">
                <h5 class="mb-0">Informasi Pembayaran</h5>
            </div>
            <div class="card-body">
                <div class="d-flex justify-content-between mb-3">
                    <span>Subtotal:</span>
                    <span><?= format_rupiah($transaction['subtotal']) ?></span>
                </div>
                <div class="d-flex justify-content-between mb-3">
                    <span>Diskon:</span>
                    <span>- <?= format_rupiah($transaction['diskon']) ?></span>
                </div>
                <div class="d-flex justify-content-between mb-3">
                    <span>Pajak:</span>
                    <span><?= format_rupiah($transaction['pajak']) ?></span>
                </div>
                <hr>
                <div class="d-flex justify-content-between mb-3 fw-bold">
                    <span>Total:</span>
                    <span><?= format_rupiah($transaction['total']) ?></span>
                </div>
                <div class="d-flex justify-content-between mb-3">
                    <span>Bayar:</span>
                    <span><?= format_rupiah($transaction['bayar']) ?></span>
                </div>
                <div class="d-flex justify-content-between fw-bold">
                    <span>Kembali:</span>
                    <span><?= format_rupiah($transaction['kembali']) ?></span>
                </div>
            </div>
        </div>
        
        <!-- Actions -->
        <div class="card">
            <div class="card-header">
                <h5 class="mb-0">Aksi</h5>
            </div>
            <div class="card-body">
                <div class="d-grid gap-2">
                    <a href="<?= base_url('penjualan/cetak.php?id=' . $id) ?>" class="btn btn-primary" target="_blank">
                        <i class="fas fa-print me-2"></i> Cetak Struk
                    </a>
                    
                    <?php if (is_admin() && $transaction['status'] !== 'batal'): ?>
                    <a href="<?= base_url('penjualan/batalkan.php?id=' . $id) ?>" class="btn btn-danger btn-delete" data-confirm="Yakin ingin membatalkan transaksi ini?">
                        <i class="fas fa-times me-2"></i> Batalkan Transaksi
                    </a>
                    <?php endif; ?>
                </div>
            </div>
        </div>
    </div>
</div>

<?php
// Include footer
include '../includes/footer.php';
?>