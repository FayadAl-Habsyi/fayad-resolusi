<?php
/**
 * Header Template
 */

// Cek apakah user sudah login
if (!is_logged_in()) {
    redirect(base_url('auth/login.php'));
}

// Ambil data user yang sedang login
$user_id = $_SESSION['user_id'];
$user = get_row("SELECT * FROM users WHERE id = '$user_id'");

// Ambil pengaturan toko
$settings = get_settings();

// Cek apakah ada flash message
$flash_message = get_flash_message();
?>
<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title><?= $page_title ?? 'Dashboard' ?> - <?= $settings['nama_toko'] ?? 'CashFlow POS' ?></title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
    <link href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css" rel="stylesheet">
    <link href="https://fonts.googleapis.com/css2?family=Poppins:wght@300;400;500;600;700&display=swap" rel="stylesheet">
    <link href="https://cdn.datatables.net/1.13.4/css/dataTables.bootstrap5.min.css" rel="stylesheet">
    <link href="https://cdn.jsdelivr.net/npm/select2@4.1.0-rc.0/dist/css/select2.min.css" rel="stylesheet">
    <link href="https://cdn.jsdelivr.net/npm/select2-bootstrap-5-theme@1.3.0/dist/select2-bootstrap-5-theme.min.css" rel="stylesheet">
    <link href="<?= asset_url('css/style.css') ?>" rel="stylesheet">
    <?php if (isset($extra_css)): ?>
    <?= $extra_css ?>
    <?php endif; ?>
    <style>
        :root {
            --primary-gradient: linear-gradient(135deg, #667eea 0%, #764ba2 100%);
            --secondary-gradient: linear-gradient(135deg, #f093fb 0%, #f5576c 100%);
            --success-gradient: linear-gradient(135deg, #4facfe 0%, #00f2fe 100%);
            --warning-gradient: linear-gradient(135deg, #43e97b 0%, #38f9d7 100%);
            --danger-gradient: linear-gradient(135deg, #fa709a 0%, #fee140 100%);
            --dark-gradient: linear-gradient(135deg, #2c3e50 0%, #34495e 100%);
            --sidebar-width: 280px;
            --sidebar-collapsed: 80px;
            --topbar-height: 70px;
        }

        * {
            margin: 0;
            padding: 0;
            box-sizing: border-box;
        }

        body {
            font-family: 'Poppins', sans-serif;
            background: linear-gradient(135deg, #667eea 0%, #764ba2 50%, #f093fb 100%);
            min-height: 100vh;
            overflow-x: hidden;
        }

        /* Sidebar Styles */
        .sidebar {
            position: fixed;
            top: 0;
            left: 0;
            width: var(--sidebar-width);
            height: 100vh;
            background: var(--dark-gradient);
            backdrop-filter: blur(20px);
            border-right: 1px solid rgba(255, 255, 255, 0.1);
            transition: all 0.3s cubic-bezier(0.4, 0, 0.2, 1);
            z-index: 1000;
            box-shadow: 0 0 30px rgba(0, 0, 0, 0.3);
        }

        .sidebar.collapsed {
            width: var(--sidebar-collapsed);
        }

        .sidebar-header {
            padding: 20px;
            text-align: center;
            border-bottom: 1px solid rgba(255, 255, 255, 0.1);
            background: var(--primary-gradient);
        }

        .sidebar-header h3 {
            color: white;
            font-weight: 700;
            font-size: 1.5rem;
            margin: 0;
            transition: all 0.3s ease;
        }

        .sidebar.collapsed .sidebar-header h3 {
            font-size: 1rem;
        }

        .sidebar-header .subtitle {
            color: rgba(255, 255, 255, 0.8);
            font-size: 0.8rem;
            margin-top: 5px;
            transition: all 0.3s ease;
        }

        .sidebar.collapsed .subtitle {
            display: none;
        }

        .sidebar-menu {
            padding: 20px 0;
            list-style: none;
        }

        .sidebar-menu li {
            margin: 5px 0;
        }

        .sidebar-menu a {
            display: flex;
            align-items: center;
            padding: 15px 20px;
            color: rgba(255, 255, 255, 0.8);
            text-decoration: none;
            transition: all 0.3s cubic-bezier(0.4, 0, 0.2, 1);
            border-radius: 0 25px 25px 0;
            margin-right: 20px;
            position: relative;
            overflow: hidden;
        }

        .sidebar-menu a::before {
            content: '';
            position: absolute;
            top: 0;
            left: -100%;
            width: 100%;
            height: 100%;
            background: var(--secondary-gradient);
            transition: all 0.3s ease;
            z-index: -1;
        }

        .sidebar-menu a:hover::before,
        .sidebar-menu a.active::before {
            left: 0;
        }

        .sidebar-menu a:hover,
        .sidebar-menu a.active {
            color: white;
            transform: translateX(10px);
        }

        .sidebar-menu i {
            font-size: 1.2rem;
            width: 25px;
            text-align: center;
            margin-right: 15px;
            transition: all 0.3s ease;
        }

        .sidebar.collapsed .sidebar-menu a {
            justify-content: center;
            margin-right: 0;
            border-radius: 15px;
            margin: 5px 10px;
        }

        .sidebar.collapsed .sidebar-menu i {
            margin-right: 0;
        }

        .sidebar.collapsed .menu-text {
            display: none;
        }

        /* Toggle Button */
        .sidebar-toggle {
            position: absolute;
            top: 20px;
            right: -15px;
            width: 30px;
            height: 30px;
            background: var(--primary-gradient);
            border: none;
            border-radius: 50%;
            color: white;
            cursor: pointer;
            transition: all 0.3s ease;
            box-shadow: 0 5px 15px rgba(0, 0, 0, 0.2);
            z-index: 1001;
        }

        .sidebar-toggle:hover {
            transform: scale(1.1);
        }

        /* Main Content */
        .main-content {
            margin-left: var(--sidebar-width);
            transition: all 0.3s cubic-bezier(0.4, 0, 0.2, 1);
            min-height: 100vh;
        }

        .main-content.expanded {
            margin-left: var(--sidebar-collapsed);
        }

        /* Topbar */
        .topbar {
            height: var(--topbar-height);
            background: rgba(255, 255, 255, 0.95);
            backdrop-filter: blur(20px);
            border-bottom: 1px solid rgba(0, 0, 0, 0.1);
            display: flex;
            align-items: center;
            justify-content: between;
            padding: 0 30px;
            box-shadow: 0 2px 20px rgba(0, 0, 0, 0.1);
            position: sticky;
            top: 0;
            z-index: 999;
        }

        .topbar-left h4 {
            margin: 0;
            background: var(--primary-gradient);
            -webkit-background-clip: text;
            -webkit-text-fill-color: transparent;
            background-clip: text;
            font-weight: 700;
        }

        .topbar-right {
            margin-left: auto;
            display: flex;
            align-items: center;
            gap: 20px;
        }

        .profile-dropdown {
            position: relative;
        }

        .profile-btn {
            display: flex;
            align-items: center;
            gap: 10px;
            background: var(--primary-gradient);
            color: white;
            border: none;
            padding: 10px 15px;
            border-radius: 25px;
            cursor: pointer;
            transition: all 0.3s ease;
        }

        .profile-btn:hover {
            transform: translateY(-2px);
            box-shadow: 0 5px 15px rgba(0, 0, 0, 0.2);
        }

        .profile-avatar {
            width: 35px;
            height: 35px;
            border-radius: 50%;
            background: rgba(255, 255, 255, 0.2);
            display: flex;
            align-items: center;
            justify-content: center;
        }

        /* Dashboard Content */
        .dashboard-content {
            padding: 30px;
            animation: fadeInUp 0.6s ease;
        }

        @keyframes fadeInUp {
            from {
                opacity: 0;
                transform: translateY(30px);
            }
            to {
                opacity: 1;
                transform: translateY(0);
            }
        }

        /* Mobile Styles */
        @media (max-width: 768px) {
            .sidebar {
                transform: translateX(-100%);
                width: var(--sidebar-width);
            }

            .sidebar.show {
                transform: translateX(0);
            }

            .main-content {
                margin-left: 0;
            }

            .main-content.expanded {
                margin-left: 0;
            }

            .mobile-menu-btn {
                display: block !important;
            }

            .sidebar-toggle {
                display: none;
            }

            .topbar {
                padding: 0 15px;
            }

            .dashboard-content {
                padding: 20px 15px;
            }
        }

        .mobile-menu-btn {
            display: none;
            background: var(--primary-gradient);
            color: white;
            border: none;
            padding: 10px;
            border-radius: 10px;
            cursor: pointer;
        }

        .mobile-overlay {
            position: fixed;
            top: 0;
            left: 0;
            width: 100%;
            height: 100%;
            background: rgba(0, 0, 0, 0.5);
            z-index: 999;
            display: none;
        }

        .mobile-overlay.show {
            display: block;
        }

        /* Responsive Typography */
        @media (max-width: 576px) {
            .topbar-left h4 {
                font-size: 1.2rem;
            }
        }

        /* Card Styles */
        .card {
            background: rgba(255, 255, 255, 0.95);
            backdrop-filter: blur(20px);
            border-radius: 20px;
            box-shadow: 0 10px 30px rgba(0, 0, 0, 0.1);
            border: 1px solid rgba(255, 255, 255, 0.2);
            overflow: hidden;
            margin-bottom: 30px;
        }

        .card-header {
            background: var(--primary-gradient);
            color: white;
            padding: 20px;
            border-bottom: none;
        }

        .card-body {
            padding: 25px;
        }

        /* Form Styles */
        .form-control, .form-select {
            border-radius: 10px;
            padding: 12px 15px;
            border: 1px solid #ddd;
            font-size: 0.9rem;
        }

        .form-control:focus, .form-select:focus {
            border-color: #667eea;
            box-shadow: 0 0 0 0.25rem rgba(102, 126, 234, 0.25);
        }

        .form-label {
            font-weight: 500;
            margin-bottom: 8px;
            color: #333;
        }

        /* Button Styles */
        .btn-primary {
            background: var(--primary-gradient);
            border: none;
            border-radius: 10px;
            padding: 10px 20px;
            font-weight: 500;
            transition: all 0.3s ease;
        }

        .btn-primary:hover {
            transform: translateY(-3px);
            box-shadow: 0 10px 20px rgba(102, 126, 234, 0.3);
        }

        .btn-success {
            background: var(--success-gradient);
            border: none;
        }

        .btn-warning {
            background: var(--warning-gradient);
            border: none;
        }

        .btn-danger {
            background: var(--danger-gradient);
            border: none;
        }

        /* Table Styles */
        .table-container {
            background: white;
            border-radius: 15px;
            overflow: hidden;
            box-shadow: 0 5px 15px rgba(0, 0, 0, 0.05);
        }

        .table thead th {
            background: var(--primary-gradient);
            color: white;
            font-weight: 600;
            border: none;
            padding: 15px;
        }

        .table tbody td {
            padding: 15px;
            border-bottom: 1px solid rgba(0, 0, 0, 0.05);
            vertical-align: middle;
        }

        .table tbody tr:hover {
            background: rgba(102, 126, 234, 0.05);
        }

        /* Alert Styles */
        .alert {
            border-radius: 15px;
            padding: 15px 20px;
            margin-bottom: 25px;
            border: none;
        }

        .alert-success {
            background: rgba(75, 172, 254, 0.1);
            color: #4facfe;
            border-left: 4px solid #4facfe;
        }

        .alert-danger {
            background: rgba(245, 87, 108, 0.1);
            color: #f5576c;
            border-left: 4px solid #f5576c;
        }

        .alert-warning {
            background: rgba(254, 225, 64, 0.1);
            color: #fea116;
            border-left: 4px solid #fea116;
        }

        .alert-info {
            background: rgba(102, 126, 234, 0.1);
            color: #667eea;
            border-left: 4px solid #667eea;
        }

        /* Badge Styles */
        .badge {
            padding: 5px 12px;
            border-radius: 20px;
            font-size: 0.8rem;
            font-weight: 600;
        }

        .badge-success {
            background: rgba(75, 172, 254, 0.1);
            color: #4facfe;
        }

        .badge-danger {
            background: rgba(245, 87, 108, 0.1);
            color: #f5576c;
        }

        .badge-warning {
            background: rgba(254, 225, 64, 0.1);
            color: #fea116;
        }

        .badge-info {
            background: rgba(102, 126, 234, 0.1);
            color: #667eea;
        }

        /* Select2 Custom Styles */
        .select2-container--bootstrap-5 .select2-selection {
            border-radius: 10px;
            padding: 8px 15px;
            border: 1px solid #ddd;
            min-height: 45px;
        }

        .select2-container--bootstrap-5 .select2-selection--single .select2-selection__rendered {
            padding: 0;
            font-size: 0.9rem;
        }

        /* DataTables Custom Styles */
        .dataTables_wrapper .dataTables_length select {
            border-radius: 10px;
            padding: 5px 10px;
            border: 1px solid #ddd;
        }

        .dataTables_wrapper .dataTables_filter input {
            border-radius: 10px;
            padding: 8px 15px;
            border: 1px solid #ddd;
        }

        .dataTables_wrapper .dataTables_paginate .paginate_button.current {
            background: var(--primary-gradient);
            color: white !important;
            border: none;
            border-radius: 5px;
        }

        .dataTables_wrapper .dataTables_paginate .paginate_button:not(.current):hover {
            background: rgba(102, 126, 234, 0.1);
            color: #667eea !important;
            border: 1px solid transparent;
        }
    </style>
</head>
<body>
    <!-- Mobile Overlay -->
    <div class="mobile-overlay" id="mobileOverlay" onclick="toggleMobileSidebar()"></div>

    <!-- Sidebar -->
    <div class="sidebar" id="sidebar">
        <button class="sidebar-toggle" onclick="toggleSidebar()">
            <i class="fas fa-chevron-left" id="toggleIcon"></i>
        </button>
        
        <div class="sidebar-header">
            <h3><i class="fas fa-bolt me-2"></i><?= $settings['nama_toko'] ?? 'CashFlow' ?></h3>
            <div class="subtitle">Modern POS System</div>
        </div>
        
        <ul class="sidebar-menu">
            <li>
                <a href="<?= base_url('dashboard.php') ?>" class="<?= $active_menu === 'dashboard' ? 'active' : '' ?>">
                    <i class="fas fa-home"></i>
                    <span class="menu-text">Dashboard</span>
                </a>
            </li>
            <li>
                <a href="<?= base_url('transaksi/index.php') ?>" class="<?= $active_menu === 'transaksi' ? 'active' : '' ?>">
                    <i class="fas fa-cash-register"></i>
                    <span class="menu-text">Kasir</span>
                </a>
            </li>
            <li>
                <a href="<?= base_url('penjualan/index.php') ?>" class="<?= $active_menu === 'penjualan' ? 'active' : '' ?>">
                    <i class="fas fa-receipt"></i>
                    <span class="menu-text">Penjualan</span>
                </a>
            </li>
            <li>
                <a href="<?= base_url('produk/index.php') ?>" class="<?= $active_menu === 'produk' ? 'active' : '' ?>">
                    <i class="fas fa-box"></i>
                    <span class="menu-text">Produk</span>
                </a>
            </li>
            <li>
                <a href="<?= base_url('kategori/index.php') ?>" class="<?= $active_menu === 'kategori' ? 'active' : '' ?>">
                    <i class="fas fa-tags"></i>
                    <span class="menu-text">Kategori</span>
                </a>
            </li>
            <li>
                <a href="<?= base_url('stok/index.php') ?>" class="<?= $active_menu === 'stok' ? 'active' : '' ?>">
                    <i class="fas fa-boxes"></i>
                    <span class="menu-text">Stok</span>
                </a>
            </li>
            <li>
                <a href="<?= base_url('pelanggan/index.php') ?>" class="<?= $active_menu === 'pelanggan' ? 'active' : '' ?>">
                    <i class="fas fa-users"></i>
                    <span class="menu-text">Pelanggan</span>
                </a>
            </li>
            <li>
                <a href="<?= base_url('laporan/index.php') ?>" class="<?= $active_menu === 'laporan' ? 'active' : '' ?>">
                    <i class="fas fa-chart-line"></i>
                    <span class="menu-text">Laporan</span>
                </a>
            </li>
            <?php if (is_admin()): ?>
            <li>
                <a href="<?= base_url('users/index.php') ?>" class="<?= $active_menu === 'users' ? 'active' : '' ?>">
                    <i class="fas fa-user-cog"></i>
                    <span class="menu-text">Pengguna</span>
                </a>
            </li>
            <li>
                <a href="<?= base_url('pengaturan/index.php') ?>" class="<?= $active_menu === 'pengaturan' ? 'active' : '' ?>">
                    <i class="fas fa-cog"></i>
                    <span class="menu-text">Pengaturan</span>
                </a>
            </li>
            <?php endif; ?>
            <li>
                <a href="<?= base_url('auth/logout.php') ?>" onclick="return confirm('Apakah Anda yakin ingin logout?')">
                    <i class="fas fa-sign-out-alt"></i>
                    <span class="menu-text">Logout</span>
                </a>
            </li>
        </ul>
    </div>

    <!-- Main Content -->
    <div class="main-content" id="mainContent">
        <!-- Topbar -->
        <div class="topbar">
            <button class="mobile-menu-btn" onclick="toggleMobileSidebar()">
                <i class="fas fa-bars"></i>
            </button>
            
            <div class="topbar-left">
                <h4><?= $page_title ?? 'Dashboard' ?></h4>
            </div>
            
            <div class="topbar-right">
                <div class="profile-dropdown">
                    <button class="profile-btn" onclick="window.location.href='<?= base_url('profile.php') ?>'">
                        <div class="profile-avatar">
                            <?php if (!empty($user['foto']) && $user['foto'] !== 'default.jpg'): ?>
                            <img src="<?= upload_url('users/' . $user['foto']) ?>" alt="<?= $user['nama_lengkap'] ?>" class="img-fluid" style="width: 35px; height: 35px; border-radius: 50%; object-fit: cover;">
                            <?php else: ?>
                            <i class="fas fa-user"></i>
                            <?php endif; ?>
                        </div>
                        <span><?= $user['nama_lengkap'] ?></span>
                        <i class="fas fa-chevron-down"></i>
                    </button>
                </div>
            </div>
        </div>

        <!-- Dashboard Content -->
        <div class="dashboard-content">
            <?php if ($flash_message): ?>
            <div class="alert alert-<?= $flash_message['type'] ?>">
                <?= $flash_message['message'] ?>
            </div>
            <?php endif; ?>