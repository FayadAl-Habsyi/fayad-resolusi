<?php
/**
 * Sales Report Page
 */
require_once '../config/config.php';

// Check if user is logged in
if (!is_logged_in()) {
    redirect(base_url('auth/login.php'));
}

// Set active menu
$active_menu = 'laporan';

// Set page title
$page_title = 'Laporan Penjualan';

// Default date range (current month)
$default_start_date = date('Y-m-01'); // First day of current month
$default_end_date = date('Y-m-d'); // Today

// Get date range from request
$start_date = isset($_GET['start_date']) ? sanitize($_GET['start_date']) : $default_start_date;
$end_date = isset($_GET['end_date']) ? sanitize($_GET['end_date']) : $default_end_date;

// Get report type
$report_type = isset($_GET['report_type']) ? sanitize($_GET['report_type']) : 'daily';

// Validate dates
if (!preg_match('/^\d{4}-\d{2}-\d{2}$/', $start_date)) {
    $start_date = $default_start_date;
}

if (!preg_match('/^\d{4}-\d{2}-\d{2}$/', $end_date)) {
    $end_date = $default_end_date;
}

// Ensure end_date is not before start_date
if (strtotime($end_date) < strtotime($start_date)) {
    $end_date = $start_date;
}

// Get cashier filter
$cashier_id = isset($_GET['cashier_id']) ? (int)$_GET['cashier_id'] : 0;
$cashier_condition = $cashier_id > 0 ? "AND t.id_user = $cashier_id" : '';

// Get status filter
$status = isset($_GET['status']) ? sanitize($_GET['status']) : '';
$status_condition = !empty($status) ? "AND t.status = '" . escape_string($status) . "'" : '';

// Get all cashiers for filter dropdown
$cashiers_query = "SELECT id, nama FROM users WHERE role = 'kasir' OR role = 'admin' ORDER BY nama ASC";
$cashiers = query($cashiers_query);

// Generate report data based on type
$report_data = [];
$total_sales = 0;
$total_transactions = 0;
$total_items_sold = 0;
$total_profit = 0;

switch ($report_type) {
    case 'daily':
        // Daily sales report
        $report_query = "SELECT 
                            DATE(t.tanggal) as tanggal,
                            COUNT(DISTINCT t.id) as jumlah_transaksi,
                            SUM(t.total) as total_penjualan,
                            SUM(td.jumlah) as jumlah_item,
                            SUM((td.harga_jual - td.harga_beli) * td.jumlah) as keuntungan
                        FROM transaksi t
                        LEFT JOIN transaksi_detail td ON t.id = td.id_transaksi
                        WHERE DATE(t.tanggal) BETWEEN '$start_date' AND '$end_date'
                        AND t.status != 'batal'
                        $cashier_condition $status_condition
                        GROUP BY DATE(t.tanggal)
                        ORDER BY DATE(t.tanggal) ASC";
        $report_data = query($report_query);
        break;
        
    case 'monthly':
        // Monthly sales report
        $report_query = "SELECT 
                            DATE_FORMAT(t.tanggal, '%Y-%m') as bulan,
                            COUNT(DISTINCT t.id) as jumlah_transaksi,
                            SUM(t.total) as total_penjualan,
                            SUM(td.jumlah) as jumlah_item,
                            SUM((td.harga_jual - td.harga_beli) * td.jumlah) as keuntungan
                        FROM transaksi t
                        LEFT JOIN transaksi_detail td ON t.id = td.id_transaksi
                        WHERE DATE(t.tanggal) BETWEEN '$start_date' AND '$end_date'
                        AND t.status != 'batal'
                        $cashier_condition $status_condition
                        GROUP BY DATE_FORMAT(t.tanggal, '%Y-%m')
                        ORDER BY DATE_FORMAT(t.tanggal, '%Y-%m') ASC";
        $report_data = query($report_query);
        break;
        
    case 'product':
        // Product sales report
        $report_query = "SELECT 
                            p.id as id_produk,
                            p.kode_produk,
                            p.nama_produk,
                            k.nama as kategori,
                            SUM(td.jumlah) as jumlah_terjual,
                            SUM(td.subtotal) as total_penjualan,
                            SUM((td.harga_jual - td.harga_beli) * td.jumlah) as keuntungan
                        FROM transaksi t
                        LEFT JOIN transaksi_detail td ON t.id = td.id_transaksi
                        LEFT JOIN produk p ON td.id_produk = p.id
                        LEFT JOIN kategori k ON p.id_kategori = k.id
                        WHERE DATE(t.tanggal) BETWEEN '$start_date' AND '$end_date'
                        AND t.status != 'batal'
                        $cashier_condition $status_condition
                        GROUP BY p.id, p.kode_produk, p.nama_produk, k.nama
                        ORDER BY SUM(td.jumlah) DESC";
        $report_data = query($report_query);
        break;
        
    case 'category':
        // Category sales report
        $report_query = "SELECT 
                            k.id as id_kategori,
                            k.nama as kategori,
                            COUNT(DISTINCT p.id) as jumlah_produk,
                            SUM(td.jumlah) as jumlah_terjual,
                            SUM(td.subtotal) as total_penjualan,
                            SUM((td.harga_jual - td.harga_beli) * td.jumlah) as keuntungan
                        FROM transaksi t
                        LEFT JOIN transaksi_detail td ON t.id = td.id_transaksi
                        LEFT JOIN produk p ON td.id_produk = p.id
                        LEFT JOIN kategori k ON p.id_kategori = k.id
                        WHERE DATE(t.tanggal) BETWEEN '$start_date' AND '$end_date'
                        AND t.status != 'batal'
                        $cashier_condition $status_condition
                        GROUP BY k.id, k.nama
                        ORDER BY SUM(td.subtotal) DESC";
        $report_data = query($report_query);
        break;
        
    case 'cashier':
        // Cashier sales report
        $report_query = "SELECT 
                            u.id as id_user,
                            u.nama as nama_kasir,
                            COUNT(DISTINCT t.id) as jumlah_transaksi,
                            SUM(t.total) as total_penjualan,
                            SUM(td.jumlah) as jumlah_item,
                            SUM((td.harga_jual - td.harga_beli) * td.jumlah) as keuntungan
                        FROM transaksi t
                        LEFT JOIN transaksi_detail td ON t.id = td.id_transaksi
                        LEFT JOIN users u ON t.id_user = u.id
                        WHERE DATE(t.tanggal) BETWEEN '$start_date' AND '$end_date'
                        AND t.status != 'batal'
                        $cashier_condition $status_condition
                        GROUP BY u.id, u.nama
                        ORDER BY SUM(t.total) DESC";
        $report_data = query($report_query);
        break;
}

// Calculate totals
foreach ($report_data as $row) {
    if (isset($row['total_penjualan'])) {
        $total_sales += $row['total_penjualan'];
    }
    
    if (isset($row['jumlah_transaksi'])) {
        $total_transactions += $row['jumlah_transaksi'];
    }
    
    if (isset($row['jumlah_item']) || isset($row['jumlah_terjual'])) {
        $total_items_sold += isset($row['jumlah_item']) ? $row['jumlah_item'] : $row['jumlah_terjual'];
    }
    
    if (isset($row['keuntungan'])) {
        $total_profit += $row['keuntungan'];
    }
}

// Include header
include '../includes/header.php';
?>

<div class="row">
    <div class="col-lg-12">
        <div class="card mb-4">
            <div class="card-header d-flex justify-content-between align-items-center">
                <h5 class="mb-0">Laporan Penjualan</h5>
                <div>
                    <button type="button" class="btn btn-success btn-sm" onclick="printReport()">
                        <i class="fas fa-print me-1"></i> Cetak
                    </button>
                    <button type="button" class="btn btn-primary btn-sm" onclick="exportToExcel()">
                        <i class="fas fa-file-excel me-1"></i> Export Excel
                    </button>
                </div>
            </div>
            <div class="card-body">
                <!-- Filters -->
                <form action="" method="get" class="mb-4">
                    <div class="row g-3">
                        <div class="col-md-2">
                            <label for="report_type" class="form-label">Jenis Laporan</label>
                            <select name="report_type" id="report_type" class="form-select">
                                <option value="daily" <?= $report_type === 'daily' ? 'selected' : '' ?>>Harian</option>
                                <option value="monthly" <?= $report_type === 'monthly' ? 'selected' : '' ?>>Bulanan</option>
                                <option value="product" <?= $report_type === 'product' ? 'selected' : '' ?>>Per Produk</option>
                                <option value="category" <?= $report_type === 'category' ? 'selected' : '' ?>>Per Kategori</option>
                                <option value="cashier" <?= $report_type === 'cashier' ? 'selected' : '' ?>>Per Kasir</option>
                            </select>
                        </div>
                        <div class="col-md-2">
                            <label for="start_date" class="form-label">Tanggal Mulai</label>
                            <input type="date" class="form-control" id="start_date" name="start_date" value="<?= $start_date ?>">
                        </div>
                        <div class="col-md-2">
                            <label for="end_date" class="form-label">Tanggal Akhir</label>
                            <input type="date" class="form-control" id="end_date" name="end_date" value="<?= $end_date ?>">
                        </div>
                        <div class="col-md-2">
                            <label for="cashier_id" class="form-label">Kasir</label>
                            <select name="cashier_id" id="cashier_id" class="form-select">
                                <option value="">Semua Kasir</option>
                                <?php foreach ($cashiers as $cashier): ?>
                                <option value="<?= $cashier['id'] ?>" <?= $cashier_id === (int)$cashier['id'] ? 'selected' : '' ?>>
                                    <?= $cashier['nama'] ?>
                                </option>
                                <?php endforeach; ?>
                            </select>
                        </div>
                        <div class="col-md-2">
                            <label for="status" class="form-label">Status</label>
                            <select name="status" id="status" class="form-select">
                                <option value="">Semua Status</option>
                                <option value="selesai" <?= $status === 'selesai' ? 'selected' : '' ?>>Selesai</option>
                                <option value="batal" <?= $status === 'batal' ? 'selected' : '' ?>>Batal</option>
                            </select>
                        </div>
                        <div class="col-md-2 d-flex align-items-end">
                            <button type="submit" class="btn btn-primary w-100">
                                <i class="fas fa-filter me-1"></i> Filter
                            </button>
                        </div>
                    </div>
                </form>
                
                <!-- Summary Cards -->
                <div class="row mb-4">
                    <div class="col-md-3">
                        <div class="card bg-primary text-white h-100">
                            <div class="card-body">
                                <div class="d-flex justify-content-between align-items-center">
                                    <div>
                                        <h6 class="mb-0">Total Penjualan</h6>
                                        <h4 class="mt-2 mb-0"><?= format_rupiah($total_sales) ?></h4>
                                    </div>
                                    <i class="fas fa-money-bill-wave fa-2x opacity-50"></i>
                                </div>
                            </div>
                        </div>
                    </div>
                    <div class="col-md-3">
                        <div class="card bg-success text-white h-100">
                            <div class="card-body">
                                <div class="d-flex justify-content-between align-items-center">
                                    <div>
                                        <h6 class="mb-0">Total Keuntungan</h6>
                                        <h4 class="mt-2 mb-0"><?= format_rupiah($total_profit) ?></h4>
                                    </div>
                                    <i class="fas fa-chart-line fa-2x opacity-50"></i>
                                </div>
                            </div>
                        </div>
                    </div>
                    <div class="col-md-3">
                        <div class="card bg-info text-white h-100">
                            <div class="card-body">
                                <div class="d-flex justify-content-between align-items-center">
                                    <div>
                                        <h6 class="mb-0">Jumlah Transaksi</h6>
                                        <h4 class="mt-2 mb-0"><?= number_format($total_transactions) ?></h4>
                                    </div>
                                    <i class="fas fa-receipt fa-2x opacity-50"></i>
                                </div>
                            </div>
                        </div>
                    </div>
                    <div class="col-md-3">
                        <div class="card bg-warning text-white h-100">
                            <div class="card-body">
                                <div class="d-flex justify-content-between align-items-center">
                                    <div>
                                        <h6 class="mb-0">Item Terjual</h6>
                                        <h4 class="mt-2 mb-0"><?= number_format($total_items_sold) ?></h4>
                                    </div>
                                    <i class="fas fa-shopping-cart fa-2x opacity-50"></i>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
                
                <!-- Report Table -->
                <div class="table-responsive" id="report-table">
                    <?php if ($report_type === 'daily'): ?>
                        <h5 class="mb-3">Laporan Penjualan Harian (<?= format_tanggal($start_date) ?> - <?= format_tanggal($end_date) ?>)</h5>
                        <table class="table table-striped table-hover align-middle">
                            <thead>
                                <tr>
                                    <th>No</th>
                                    <th>Tanggal</th>
                                    <th>Jumlah Transaksi</th>
                                    <th>Item Terjual</th>
                                    <th>Total Penjualan</th>
                                    <th>Keuntungan</th>
                                </tr>
                            </thead>
                            <tbody>
                                <?php if (empty($report_data)): ?>
                                <tr>
                                    <td colspan="6" class="text-center py-4">Tidak ada data penjualan</td>
                                </tr>
                                <?php else: ?>
                                    <?php $no = 1; foreach ($report_data as $row): ?>
                                    <tr>
                                        <td><?= $no++ ?></td>
                                        <td><?= format_tanggal($row['tanggal']) ?></td>
                                        <td><?= number_format($row['jumlah_transaksi']) ?></td>
                                        <td><?= number_format($row['jumlah_item']) ?></td>
                                        <td><?= format_rupiah($row['total_penjualan']) ?></td>
                                        <td><?= format_rupiah($row['keuntungan']) ?></td>
                                    </tr>
                                    <?php endforeach; ?>
                                <?php endif; ?>
                            </tbody>
                            <tfoot>
                                <tr class="fw-bold">
                                    <td colspan="2">Total</td>
                                    <td><?= number_format($total_transactions) ?></td>
                                    <td><?= number_format($total_items_sold) ?></td>
                                    <td><?= format_rupiah($total_sales) ?></td>
                                    <td><?= format_rupiah($total_profit) ?></td>
                                </tr>
                            </tfoot>
                        </table>
                    <?php elseif ($report_type === 'monthly'): ?>
                        <h5 class="mb-3">Laporan Penjualan Bulanan (<?= format_tanggal($start_date) ?> - <?= format_tanggal($end_date) ?>)</h5>
                        <table class="table table-striped table-hover align-middle">
                            <thead>
                                <tr>
                                    <th>No</th>
                                    <th>Bulan</th>
                                    <th>Jumlah Transaksi</th>
                                    <th>Item Terjual</th>
                                    <th>Total Penjualan</th>
                                    <th>Keuntungan</th>
                                </tr>
                            </thead>
                            <tbody>
                                <?php if (empty($report_data)): ?>
                                <tr>
                                    <td colspan="6" class="text-center py-4">Tidak ada data penjualan</td>
                                </tr>
                                <?php else: ?>
                                    <?php $no = 1; foreach ($report_data as $row): ?>
                                    <tr>
                                        <td><?= $no++ ?></td>
                                        <td><?= date('F Y', strtotime($row['bulan'] . '-01')) ?></td>
                                        <td><?= number_format($row['jumlah_transaksi']) ?></td>
                                        <td><?= number_format($row['jumlah_item']) ?></td>
                                        <td><?= format_rupiah($row['total_penjualan']) ?></td>
                                        <td><?= format_rupiah($row['keuntungan']) ?></td>
                                    </tr>
                                    <?php endforeach; ?>
                                <?php endif; ?>
                            </tbody>
                            <tfoot>
                                <tr class="fw-bold">
                                    <td colspan="2">Total</td>
                                    <td><?= number_format($total_transactions) ?></td>
                                    <td><?= number_format($total_items_sold) ?></td>
                                    <td><?= format_rupiah($total_sales) ?></td>
                                    <td><?= format_rupiah($total_profit) ?></td>
                                </tr>
                            </tfoot>
                        </table>
                    <?php elseif ($report_type === 'product'): ?>
                        <h5 class="mb-3">Laporan Penjualan Per Produk (<?= format_tanggal($start_date) ?> - <?= format_tanggal($end_date) ?>)</h5>
                        <table class="table table-striped table-hover align-middle">
                            <thead>
                                <tr>
                                    <th>No</th>
                                    <th>Kode Produk</th>
                                    <th>Nama Produk</th>
                                    <th>Kategori</th>
                                    <th>Jumlah Terjual</th>
                                    <th>Total Penjualan</th>
                                    <th>Keuntungan</th>
                                </tr>
                            </thead>
                            <tbody>
                                <?php if (empty($report_data)): ?>
                                <tr>
                                    <td colspan="7" class="text-center py-4">Tidak ada data penjualan</td>
                                </tr>
                                <?php else: ?>
                                    <?php $no = 1; foreach ($report_data as $row): ?>
                                    <tr>
                                        <td><?= $no++ ?></td>
                                        <td><?= $row['kode_produk'] ?></td>
                                        <td><?= $row['nama_produk'] ?></td>
                                        <td><?= $row['kategori'] ?></td>
                                        <td><?= number_format($row['jumlah_terjual']) ?></td>
                                        <td><?= format_rupiah($row['total_penjualan']) ?></td>
                                        <td><?= format_rupiah($row['keuntungan']) ?></td>
                                    </tr>
                                    <?php endforeach; ?>
                                <?php endif; ?>
                            </tbody>
                            <tfoot>
                                <tr class="fw-bold">
                                    <td colspan="4">Total</td>
                                    <td><?= number_format($total_items_sold) ?></td>
                                    <td><?= format_rupiah($total_sales) ?></td>
                                    <td><?= format_rupiah($total_profit) ?></td>
                                </tr>
                            </tfoot>
                        </table>
                    <?php elseif ($report_type === 'category'): ?>
                        <h5 class="mb-3">Laporan Penjualan Per Kategori (<?= format_tanggal($start_date) ?> - <?= format_tanggal($end_date) ?>)</h5>
                        <table class="table table-striped table-hover align-middle">
                            <thead>
                                <tr>
                                    <th>No</th>
                                    <th>Kategori</th>
                                    <th>Jumlah Produk</th>
                                    <th>Item Terjual</th>
                                    <th>Total Penjualan</th>
                                    <th>Keuntungan</th>
                                </tr>
                            </thead>
                            <tbody>
                                <?php if (empty($report_data)): ?>
                                <tr>
                                    <td colspan="6" class="text-center py-4">Tidak ada data penjualan</td>
                                </tr>
                                <?php else: ?>
                                    <?php $no = 1; foreach ($report_data as $row): ?>
                                    <tr>
                                        <td><?= $no++ ?></td>
                                        <td><?= $row['kategori'] ?></td>
                                        <td><?= number_format($row['jumlah_produk']) ?></td>
                                        <td><?= number_format($row['jumlah_terjual']) ?></td>
                                        <td><?= format_rupiah($row['total_penjualan']) ?></td>
                                        <td><?= format_rupiah($row['keuntungan']) ?></td>
                                    </tr>
                                    <?php endforeach; ?>
                                <?php endif; ?>
                            </tbody>
                            <tfoot>
                                <tr class="fw-bold">
                                    <td colspan="3">Total</td>
                                    <td><?= number_format($total_items_sold) ?></td>
                                    <td><?= format_rupiah($total_sales) ?></td>
                                    <td><?= format_rupiah($total_profit) ?></td>
                                </tr>
                            </tfoot>
                        </table>
                    <?php elseif ($report_type === 'cashier'): ?>
                        <h5 class="mb-3">Laporan Penjualan Per Kasir (<?= format_tanggal($start_date) ?> - <?= format_tanggal($end_date) ?>)</h5>
                        <table class="table table-striped table-hover align-middle">
                            <thead>
                                <tr>
                                    <th>No</th>
                                    <th>Nama Kasir</th>
                                    <th>Jumlah Transaksi</th>
                                    <th>Item Terjual</th>
                                    <th>Total Penjualan</th>
                                    <th>Keuntungan</th>
                                </tr>
                            </thead>
                            <tbody>
                                <?php if (empty($report_data)): ?>
                                <tr>
                                    <td colspan="6" class="text-center py-4">Tidak ada data penjualan</td>
                                </tr>
                                <?php else: ?>
                                    <?php $no = 1; foreach ($report_data as $row): ?>
                                    <tr>
                                        <td><?= $no++ ?></td>
                                        <td><?= $row['nama_kasir'] ?></td>
                                        <td><?= number_format($row['jumlah_transaksi']) ?></td>
                                        <td><?= number_format($row['jumlah_item']) ?></td>
                                        <td><?= format_rupiah($row['total_penjualan']) ?></td>
                                        <td><?= format_rupiah($row['keuntungan']) ?></td>
                                    </tr>
                                    <?php endforeach; ?>
                                <?php endif; ?>
                            </tbody>
                            <tfoot>
                                <tr class="fw-bold">
                                    <td colspan="2">Total</td>
                                    <td><?= number_format($total_transactions) ?></td>
                                    <td><?= number_format($total_items_sold) ?></td>
                                    <td><?= format_rupiah($total_sales) ?></td>
                                    <td><?= format_rupiah($total_profit) ?></td>
                                </tr>
                            </tfoot>
                        </table>
                    <?php endif; ?>
                </div>
            </div>
        </div>
    </div>
</div>

<!-- Print styles -->
<style type="text/css" media="print">
    @page {
        size: landscape;
    }
    body {
        padding: 20px;
    }
    .no-print {
        display: none !important;
    }
    .card {
        border: none !important;
        box-shadow: none !important;
    }
    .card-header, .card-footer {
        display: none;
    }
    .sidebar, .topbar, .footer, form, .btn, .nav {
        display: none !important;
    }
    .main-content {
        margin-left: 0 !important;
        padding: 0 !important;
    }
    .container-fluid {
        padding: 0 !important;
    }
    table {
        width: 100% !important;
    }
</style>

<script>
    // Print report
    function printReport() {
        window.print();
    }
    
    // Export to Excel
    function exportToExcel() {
        // Get report type
        const reportType = document.getElementById('report_type').value;
        const startDate = document.getElementById('start_date').value;
        const endDate = document.getElementById('end_date').value;
        
        // Create a title based on report type
        let title = 'Laporan Penjualan ';
        switch (reportType) {
            case 'daily':
                title += 'Harian';
                break;
            case 'monthly':
                title += 'Bulanan';
                break;
            case 'product':
                title += 'Per Produk';
                break;
            case 'category':
                title += 'Per Kategori';
                break;
            case 'cashier':
                title += 'Per Kasir';
                break;
        }
        
        // Add date range to title
        title += ` (${startDate} - ${endDate})`;
        
        // Get the table HTML
        const table = document.querySelector('#report-table table');
        if (!table) {
            alert('Tidak ada data untuk diekspor');
            return;
        }
        
        // Convert table to CSV
        let csv = [];
        const rows = table.querySelectorAll('tr');
        
        // Add title row
        csv.push([title]);
        csv.push([]);
        
        for (let i = 0; i < rows.length; i++) {
            const row = [], cols = rows[i].querySelectorAll('td, th');
            
            for (let j = 0; j < cols.length; j++) {
                // Get the text content and clean it
                let data = cols[j].textContent.trim();
                // Remove currency formatting and thousands separators for numbers
                if (data.includes('Rp')) {
                    data = data.replace('Rp', '').replace(/\./g, '').replace(',', '.').trim();
                }
                // Wrap in quotes to handle commas in the data
                row.push('"' + data + '"');
            }
            
            csv.push(row.join(','));
        }
        
        // Create CSV file
        const csvContent = csv.join('\n');
        const blob = new Blob([csvContent], { type: 'text/csv;charset=utf-8;' });
        const url = URL.createObjectURL(blob);
        
        // Create download link and click it
        const link = document.createElement('a');
        link.setAttribute('href', url);
        link.setAttribute('download', `${title.replace(/[^a-z0-9]/gi, '_').toLowerCase()}.csv`);
        link.style.visibility = 'hidden';
        document.body.appendChild(link);
        link.click();
        document.body.removeChild(link);
    }
</script>

<?php
// Include footer
include '../includes/footer.php';
?>