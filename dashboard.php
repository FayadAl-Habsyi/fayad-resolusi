<?php
/**
 * Dashboard Page
 */
require_once 'config/config.php';

// Set active menu
$active_menu = 'dashboard';

// Set page title
$page_title = 'Dashboard';

// Get today's date
$today = date('Y-m-d');

// Get total sales today
$total_sales_today = get_var("SELECT COALESCE(SUM(total_akhir), 0) FROM transaksi WHERE DATE(tanggal) = '$today' AND status = 'selesai'");

// Get total transactions today
$total_transactions_today = get_var("SELECT COUNT(*) FROM transaksi WHERE DATE(tanggal) = '$today' AND status = 'selesai'");

// Get total products sold today
$total_products_sold_today = get_var("SELECT COALESCE(SUM(dt.jumlah), 0) FROM detail_transaksi dt JOIN transaksi t ON dt.id_transaksi = t.id WHERE DATE(t.tanggal) = '$today' AND t.status = 'selesai'");

// Get total customers today
$total_customers_today = get_var("SELECT COUNT(DISTINCT id_pelanggan) FROM transaksi WHERE DATE(tanggal) = '$today' AND status = 'selesai' AND id_pelanggan IS NOT NULL");

// Get recent transactions
$recent_transactions = query("SELECT t.*, u.nama_lengkap as kasir, p.nama as pelanggan FROM transaksi t LEFT JOIN users u ON t.id_user = u.id LEFT JOIN pelanggan p ON t.id_pelanggan = p.id WHERE t.status = 'selesai' ORDER BY t.tanggal DESC LIMIT 5");

// Get sales data for the last 7 days for chart
$sales_data = [];
$labels = [];

for ($i = 6; $i >= 0; $i--) {
    $date = date('Y-m-d', strtotime("-$i days"));
    $labels[] = date('d/m', strtotime($date));
    
    $sales = get_var("SELECT COALESCE(SUM(total_akhir), 0) FROM transaksi WHERE DATE(tanggal) = '$date' AND status = 'selesai'");
    $sales_data[] = $sales;
}

// Get top 5 selling products
$top_products = query("SELECT p.nama_produk, SUM(dt.jumlah) as total_sold FROM detail_transaksi dt JOIN transaksi t ON dt.id_transaksi = t.id JOIN produk p ON dt.id_produk = p.id WHERE t.status = 'selesai' GROUP BY dt.id_produk ORDER BY total_sold DESC LIMIT 5");

// Prepare chart data
$product_labels = [];
$product_data = [];

foreach ($top_products as $product) {
    $product_labels[] = $product['nama_produk'];
    $product_data[] = $product['total_sold'];
}

// Add extra JS for charts
$extra_js = <<<EOT
<script>
    // Sales Chart
    const salesCtx = document.getElementById('salesChart').getContext('2d');
    const salesChart = new Chart(salesCtx, {
        type: 'line',
        data: {
            labels: ['$labels[0]', '$labels[1]', '$labels[2]', '$labels[3]', '$labels[4]', '$labels[5]', '$labels[6]'],
            datasets: [{
                label: 'Penjualan',
                data: [$sales_data[0], $sales_data[1], $sales_data[2], $sales_data[3], $sales_data[4], $sales_data[5], $sales_data[6]],
                backgroundColor: 'rgba(102, 126, 234, 0.2)',
                borderColor: 'rgba(102, 126, 234, 1)',
                borderWidth: 2,
                tension: 0.4,
                fill: true,
                pointBackgroundColor: 'rgba(102, 126, 234, 1)',
                pointBorderColor: '#fff',
                pointBorderWidth: 2,
                pointRadius: 4,
                pointHoverRadius: 6
            }]
        },
        options: {
            responsive: true,
            plugins: {
                legend: {
                    display: false
                },
                tooltip: {
                    callbacks: {
                        label: function(context) {
                            return 'Penjualan: ' + new Intl.NumberFormat('id-ID', { style: 'currency', currency: 'IDR' }).format(context.raw);
                        }
                    }
                }
            },
            scales: {
                y: {
                    beginAtZero: true,
                    ticks: {
                        callback: function(value) {
                            return new Intl.NumberFormat('id-ID', { style: 'currency', currency: 'IDR', maximumSignificantDigits: 3 }).format(value);
                        }
                    }
                }
            }
        }
    });
    
    // Products Chart
    const productsCtx = document.getElementById('productsChart').getContext('2d');
    const productsChart = new Chart(productsCtx, {
        type: 'bar',
        data: {
            labels: " . json_encode($product_labels) . ",
            datasets: [{
                label: 'Produk Terjual',
                data: " . json_encode($product_data) . ",
                backgroundColor: [
                    'rgba(102, 126, 234, 0.7)',
                    'rgba(75, 172, 254, 0.7)',
                    'rgba(245, 87, 108, 0.7)',
                    'rgba(254, 225, 64, 0.7)',
                    'rgba(67, 233, 123, 0.7)'
                ],
                borderColor: [
                    'rgba(102, 126, 234, 1)',
                    'rgba(75, 172, 254, 1)',
                    'rgba(245, 87, 108, 1)',
                    'rgba(254, 225, 64, 1)',
                    'rgba(67, 233, 123, 1)'
                ],
                borderWidth: 1
            }]
        },
        options: {
            responsive: true,
            plugins: {
                legend: {
                    display: false
                }
            },
            scales: {
                y: {
                    beginAtZero: true,
                    ticks: {
                        precision: 0
                    }
                }
            }
        }
    });
</script>
EOT;

// Include header
include 'includes/header.php';
?>

<!-- Stats Cards -->
<div class="row">
    <div class="col-md-3 mb-4">
        <div class="card h-100">
            <div class="card-body">
                <div class="d-flex align-items-center mb-3">
                    <div class="flex-shrink-0">
                        <div class="stat-icon bg-primary rounded-circle p-3">
                            <i class="fas fa-money-bill-wave text-white"></i>
                        </div>
                    </div>
                    <div class="flex-grow-1 ms-3">
                        <h5 class="card-title mb-0">Total Penjualan</h5>
                        <p class="text-muted small mb-0">Hari Ini</p>
                    </div>
                </div>
                <h3 class="fw-bold"><?= format_rupiah($total_sales_today) ?></h3>
                <div class="progress mt-2" style="height: 5px;">
                    <div class="progress-bar bg-primary" role="progressbar" style="width: 100%" aria-valuenow="100" aria-valuemin="0" aria-valuemax="100"></div>
                </div>
            </div>
        </div>
    </div>
    
    <div class="col-md-3 mb-4">
        <div class="card h-100">
            <div class="card-body">
                <div class="d-flex align-items-center mb-3">
                    <div class="flex-shrink-0">
                        <div class="stat-icon bg-success rounded-circle p-3">
                            <i class="fas fa-shopping-cart text-white"></i>
                        </div>
                    </div>
                    <div class="flex-grow-1 ms-3">
                        <h5 class="card-title mb-0">Transaksi</h5>
                        <p class="text-muted small mb-0">Hari Ini</p>
                    </div>
                </div>
                <h3 class="fw-bold"><?= $total_transactions_today ?></h3>
                <div class="progress mt-2" style="height: 5px;">
                    <div class="progress-bar bg-success" role="progressbar" style="width: 100%" aria-valuenow="100" aria-valuemin="0" aria-valuemax="100"></div>
                </div>
            </div>
        </div>
    </div>
    
    <div class="col-md-3 mb-4">
        <div class="card h-100">
            <div class="card-body">
                <div class="d-flex align-items-center mb-3">
                    <div class="flex-shrink-0">
                        <div class="stat-icon bg-warning rounded-circle p-3">
                            <i class="fas fa-box-open text-white"></i>
                        </div>
                    </div>
                    <div class="flex-grow-1 ms-3">
                        <h5 class="card-title mb-0">Produk Terjual</h5>
                        <p class="text-muted small mb-0">Hari Ini</p>
                    </div>
                </div>
                <h3 class="fw-bold"><?= $total_products_sold_today ?></h3>
                <div class="progress mt-2" style="height: 5px;">
                    <div class="progress-bar bg-warning" role="progressbar" style="width: 100%" aria-valuenow="100" aria-valuemin="0" aria-valuemax="100"></div>
                </div>
            </div>
        </div>
    </div>
    
    <div class="col-md-3 mb-4">
        <div class="card h-100">
            <div class="card-body">
                <div class="d-flex align-items-center mb-3">
                    <div class="flex-shrink-0">
                        <div class="stat-icon bg-danger rounded-circle p-3">
                            <i class="fas fa-users text-white"></i>
                        </div>
                    </div>
                    <div class="flex-grow-1 ms-3">
                        <h5 class="card-title mb-0">Pelanggan</h5>
                        <p class="text-muted small mb-0">Hari Ini</p>
                    </div>
                </div>
                <h3 class="fw-bold"><?= $total_customers_today ?></h3>
                <div class="progress mt-2" style="height: 5px;">
                    <div class="progress-bar bg-danger" role="progressbar" style="width: 100%" aria-valuenow="100" aria-valuemin="0" aria-valuemax="100"></div>
                </div>
            </div>
        </div>
    </div>
</div>

<!-- Charts -->
<div class="row">
    <div class="col-md-8 mb-4">
        <div class="card h-100">
            <div class="card-header d-flex justify-content-between align-items-center">
                <h5 class="mb-0"><i class="fas fa-chart-line me-2"></i>Grafik Penjualan</h5>
                <div>
                    <span class="badge bg-primary">7 Hari Terakhir</span>
                </div>
            </div>
            <div class="card-body">
                <canvas id="salesChart" height="250"></canvas>
            </div>
        </div>
    </div>
    
    <div class="col-md-4 mb-4">
        <div class="card h-100">
            <div class="card-header">
                <h5 class="mb-0"><i class="fas fa-trophy me-2"></i>Produk Terlaris</h5>
            </div>
            <div class="card-body">
                <canvas id="productsChart" height="250"></canvas>
            </div>
        </div>
    </div>
</div>

<!-- Recent Transactions -->
<div class="card">
    <div class="card-header d-flex justify-content-between align-items-center">
        <h5 class="mb-0"><i class="fas fa-history me-2"></i>Transaksi Terbaru</h5>
        <a href="<?= base_url('transaksi/index.php') ?>" class="btn btn-sm btn-primary">
            <i class="fas fa-plus me-1"></i>Transaksi Baru
        </a>
    </div>
    <div class="card-body">
        <div class="table-responsive">
            <table class="table table-hover">
                <thead>
                    <tr>
                        <th>No. Transaksi</th>
                        <th>Tanggal</th>
                        <th>Kasir</th>
                        <th>Pelanggan</th>
                        <th>Total</th>
                        <th>Aksi</th>
                    </tr>
                </thead>
                <tbody>
                    <?php if (empty($recent_transactions)): ?>
                    <tr>
                        <td colspan="6" class="text-center">Belum ada transaksi</td>
                    </tr>
                    <?php else: ?>
                    <?php foreach ($recent_transactions as $trx): ?>
                    <tr>
                        <td><strong><?= $trx['no_transaksi'] ?></strong></td>
                        <td><?= format_datetime($trx['tanggal']) ?></td>
                        <td><?= $trx['kasir'] ?></td>
                        <td><?= $trx['pelanggan'] ?? 'Umum' ?></td>
                        <td><strong><?= format_rupiah($trx['total_akhir']) ?></strong></td>
                        <td>
                            <a href="<?= base_url('penjualan/detail.php?id=' . $trx['id']) ?>" class="btn btn-sm btn-info">
                                <i class="fas fa-eye"></i>
                            </a>
                            <a href="<?= base_url('penjualan/cetak.php?id=' . $trx['id']) ?>" class="btn btn-sm btn-success" target="_blank">
                                <i class="fas fa-print"></i>
                            </a>
                        </td>
                    </tr>
                    <?php endforeach; ?>
                    <?php endif; ?>
                </tbody>
            </table>
        </div>
    </div>
</div>

<?php
// Include footer
include 'includes/footer.php';
?>