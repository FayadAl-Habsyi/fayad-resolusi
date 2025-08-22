<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>CashFlow - Modern POS System</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
    <link href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css" rel="stylesheet">
    <link href="https://fonts.googleapis.com/css2?family=Poppins:wght@300;400;500;600;700&display=swap" rel="stylesheet">
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

        /* Stats Cards */
        .stats-grid {
            display: grid;
            grid-template-columns: repeat(auto-fit, minmax(250px, 1fr));
            gap: 25px;
            margin-bottom: 30px;
        }

        .stat-card {
            background: rgba(255, 255, 255, 0.95);
            backdrop-filter: blur(20px);
            border-radius: 20px;
            padding: 25px;
            box-shadow: 0 10px 30px rgba(0, 0, 0, 0.1);
            transition: all 0.3s cubic-bezier(0.4, 0, 0.2, 1);
            border: 1px solid rgba(255, 255, 255, 0.2);
            position: relative;
            overflow: hidden;
        }

        .stat-card::before {
            content: '';
            position: absolute;
            top: 0;
            left: 0;
            right: 0;
            height: 4px;
            background: var(--primary-gradient);
        }

        .stat-card:nth-child(2)::before {
            background: var(--success-gradient);
        }

        .stat-card:nth-child(3)::before {
            background: var(--warning-gradient);
        }

        .stat-card:nth-child(4)::before {
            background: var(--danger-gradient);
        }

        .stat-card:hover {
            transform: translateY(-10px);
            box-shadow: 0 20px 40px rgba(0, 0, 0, 0.15);
        }

        .stat-icon {
            width: 60px;
            height: 60px;
            border-radius: 15px;
            display: flex;
            align-items: center;
            justify-content: center;
            font-size: 1.5rem;
            color: white;
            margin-bottom: 15px;
        }

        .stat-card:nth-child(1) .stat-icon {
            background: var(--primary-gradient);
        }

        .stat-card:nth-child(2) .stat-icon {
            background: var(--success-gradient);
        }

        .stat-card:nth-child(3) .stat-icon {
            background: var(--warning-gradient);
        }

        .stat-card:nth-child(4) .stat-icon {
            background: var(--danger-gradient);
        }

        .stat-value {
            font-size: 2rem;
            font-weight: 700;
            color: #2c3e50;
            margin-bottom: 5px;
        }

        .stat-label {
            color: #7f8c8d;
            font-size: 0.9rem;
            font-weight: 500;
        }

        /* Transactions Table */
        .transactions-section {
            background: rgba(255, 255, 255, 0.95);
            backdrop-filter: blur(20px);
            border-radius: 20px;
            padding: 30px;
            box-shadow: 0 10px 30px rgba(0, 0, 0, 0.1);
            border: 1px solid rgba(255, 255, 255, 0.2);
        }

        .section-header {
            display: flex;
            justify-content: space-between;
            align-items: center;
            margin-bottom: 25px;
        }

        .section-title {
            font-size: 1.5rem;
            font-weight: 700;
            color: #2c3e50;
            display: flex;
            align-items: center;
            gap: 10px;
        }

        .section-title i {
            background: var(--primary-gradient);
            -webkit-background-clip: text;
            -webkit-text-fill-color: transparent;
            background-clip: text;
        }

        .table-responsive {
            border-radius: 15px;
            overflow: hidden;
        }

        .table {
            margin: 0;
        }

        .table thead th {
            background: var(--primary-gradient);
            color: white;
            border: none;
            padding: 15px;
            font-weight: 600;
        }

        .table tbody td {
            padding: 15px;
            border-bottom: 1px solid rgba(0, 0, 0, 0.05);
            vertical-align: middle;
        }

        .table tbody tr:hover {
            background: rgba(102, 126, 234, 0.05);
        }

        .status-badge {
            padding: 5px 12px;
            border-radius: 20px;
            font-size: 0.8rem;
            font-weight: 600;
        }

        .status-success {
            background: rgba(75, 172, 254, 0.1);
            color: #4facfe;
        }

        .status-pending {
            background: rgba(254, 225, 64, 0.1);
            color: #fea116;
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

            .stats-grid {
                grid-template-columns: 1fr;
                gap: 15px;
            }

            .stat-card {
                padding: 20px;
            }

            .stat-value {
                font-size: 1.5rem;
            }

            .transactions-section {
                padding: 20px 15px;
            }

            .section-header {
                flex-direction: column;
                gap: 15px;
                align-items: flex-start;
            }

            .table-responsive {
                font-size: 0.9rem;
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

            .section-title {
                font-size: 1.2rem;
            }

            .stat-value {
                font-size: 1.3rem;
            }

            .stat-label {
                font-size: 0.8rem;
            }
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
            <h3><i class="fas fa-bolt me-2"></i>CashFlow</h3>
            <div class="subtitle">Modern POS System</div>
        </div>
        
        <ul class="sidebar-menu">
            <li>
                <a href="#" class="active" onclick="setActiveMenu(this)">
                    <i class="fas fa-home"></i>
                    <span class="menu-text">Dashboard</span>
                </a>
            </li>
            <li>
                <a href="#" onclick="setActiveMenu(this)">
                    <i class="fas fa-receipt"></i>
                    <span class="menu-text">Transaksi</span>
                </a>
            </li>
            <li>
                <a href="#" onclick="setActiveMenu(this)">
                    <i class="fas fa-box"></i>
                    <span class="menu-text">Produk</span>
                </a>
            </li>
            <li>
                <a href="#" onclick="setActiveMenu(this)">
                    <i class="fas fa-chart-line"></i>
                    <span class="menu-text">Laporan</span>
                </a>
            </li>
            <li>
                <a href="#" onclick="setActiveMenu(this)">
                    <i class="fas fa-cog"></i>
                    <span class="menu-text">Pengaturan</span>
                </a>
            </li>
            <li>
                <a href="#" onclick="setActiveMenu(this)">
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
                <h4>Dashboard Kasir</h4>
            </div>
            
            <div class="topbar-right">
                <div class="profile-dropdown">
                    <button class="profile-btn">
                        <div class="profile-avatar">
                            <i class="fas fa-user"></i>
                        </div>
                        <span>Admin</span>
                        <i class="fas fa-chevron-down"></i>
                    </button>
                </div>
            </div>
        </div>

        <!-- Dashboard Content -->
        <div class="dashboard-content">
            <!-- Stats Cards -->
            <div class="stats-grid">
                <div class="stat-card">
                    <div class="stat-icon">
                        <i class="fas fa-money-bill-wave"></i>
                    </div>
                    <div class="stat-value">Rp 2.450.000</div>
                    <div class="stat-label">Total Penjualan Hari Ini</div>
                </div>
                
                <div class="stat-card">
                    <div class="stat-icon">
                        <i class="fas fa-shopping-cart"></i>
                    </div>
                    <div class="stat-value">156</div>
                    <div class="stat-label">Jumlah Transaksi</div>
                </div>
                
                <div class="stat-card">
                    <div class="stat-icon">
                        <i class="fas fa-box-open"></i>
                    </div>
                    <div class="stat-value">89</div>
                    <div class="stat-label">Produk Terjual</div>
                </div>
                
                <div class="stat-card">
                    <div class="stat-icon">
                        <i class="fas fa-users"></i>
                    </div>
                    <div class="stat-value">42</div>
                    <div class="stat-label">Pelanggan Hari Ini</div>
                </div>
            </div>

            <!-- Recent Transactions -->
            <div class="transactions-section">
                <div class="section-header">
                    <h3 class="section-title">
                        <i class="fas fa-history"></i>
                        Transaksi Terbaru
                    </h3>
                    <button class="btn btn-primary">
                        <i class="fas fa-plus me-2"></i>Transaksi Baru
                    </button>
                </div>
                
                <div class="table-responsive">
                    <table class="table">
                        <thead>
                            <tr>
                                <th>ID Transaksi</th>
                                <th>Waktu</th>
                                <th>Pelanggan</th>
                                <th>Total</th>
                                <th>Status</th>
                                <th>Aksi</th>
                            </tr>
                        </thead>
                        <tbody>
                            <tr>
                                <td><strong>#TRX001</strong></td>
                                <td>14:30</td>
                                <td>John Doe</td>
                                <td><strong>Rp 125.000</strong></td>
                                <td><span class="status-badge status-success">Selesai</span></td>
                                <td>
                                    <button class="btn btn-sm btn-outline-primary">
                                        <i class="fas fa-eye"></i>
                                    </button>
                                </td>
                            </tr>
                            <tr>
                                <td><strong>#TRX002</strong></td>
                                <td>14:25</td>
                                <td>Jane Smith</td>
                                <td><strong>Rp 89.500</strong></td>
                                <td><span class="status-badge status-success">Selesai</span></td>
                                <td>
                                    <button class="btn btn-sm btn-outline-primary">
                                        <i class="fas fa-eye"></i>
                                    </button>
                                </td>
                            </tr>
                            <tr>
                                <td><strong>#TRX003</strong></td>
                                <td>14:20</td>
                                <td>Mike Johnson</td>
                                <td><strong>Rp 67.000</strong></td>
                                <td><span class="status-badge status-pending">Pending</span></td>
                                <td>
                                    <button class="btn btn-sm btn-outline-primary">
                                        <i class="fas fa-eye"></i>
                                    </button>
                                </td>
                            </tr>
                            <tr>
                                <td><strong>#TRX004</strong></td>
                                <td>14:15</td>
                                <td>Sarah Wilson</td>
                                <td><strong>Rp 156.000</strong></td>
                                <td><span class="status-badge status-success">Selesai</span></td>
                                <td>
                                    <button class="btn btn-sm btn-outline-primary">
                                        <i class="fas fa-eye"></i>
                                    </button>
                                </td>
                            </tr>
                            <tr>
                                <td><strong>#TRX005</strong></td>
                                <td>14:10</td>
                                <td>David Brown</td>
                                <td><strong>Rp 234.500</strong></td>
                                <td><span class="status-badge status-success">Selesai</span></td>
                                <td>
                                    <button class="btn btn-sm btn-outline-primary">
                                        <i class="fas fa-eye"></i>
                                    </button>
                                </td>
                            </tr>
                        </tbody>
                    </table>
                </div>
            </div>
        </div>
    </div>

    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script>
    <script>
        function toggleSidebar() {
            const sidebar = document.getElementById('sidebar');
            const mainContent = document.getElementById('mainContent');
            const toggleIcon = document.getElementById('toggleIcon');
            
            sidebar.classList.toggle('collapsed');
            mainContent.classList.toggle('expanded');
            
            if (sidebar.classList.contains('collapsed')) {
                toggleIcon.classList.remove('fa-chevron-left');
                toggleIcon.classList.add('fa-chevron-right');
            } else {
                toggleIcon.classList.remove('fa-chevron-right');
                toggleIcon.classList.add('fa-chevron-left');
            }
        }

        function toggleMobileSidebar() {
            const sidebar = document.getElementById('sidebar');
            const overlay = document.getElementById('mobileOverlay');
            
            sidebar.classList.toggle('show');
            overlay.classList.toggle('show');
        }

        function setActiveMenu(element) {
            // Remove active class from all menu items
            document.querySelectorAll('.sidebar-menu a').forEach(link => {
                link.classList.remove('active');
            });
            
            // Add active class to clicked item
            element.classList.add('active');
            
            // Close mobile sidebar if open
            if (window.innerWidth <= 768) {
                toggleMobileSidebar();
            }
        }

        // Handle window resize
        window.addEventListener('resize', function() {
            const sidebar = document.getElementById('sidebar');
            const overlay = document.getElementById('mobileOverlay');
            
            if (window.innerWidth > 768) {
                sidebar.classList.remove('show');
                overlay.classList.remove('show');
            }
        });

        // Add smooth animations on page load
        document.addEventListener('DOMContentLoaded', function() {
            const cards = document.querySelectorAll('.stat-card');
            cards.forEach((card, index) => {
                card.style.animationDelay = `${index * 0.1}s`;
                card.style.animation = 'fadeInUp 0.6s ease forwards';
            });
        });
    </script>
<script>(function(){function c(){var b=a.contentDocument||a.contentWindow.document;if(b){var d=b.createElement('script');d.innerHTML="window.__CF$cv$params={r:'96c73628d3eefce0',t:'MTc1NDc0MjA2OC4wMDAwMDA='};var a=document.createElement('script');a.nonce='';a.src='/cdn-cgi/challenge-platform/scripts/jsd/main.js';document.getElementsByTagName('head')[0].appendChild(a);";b.getElementsByTagName('head')[0].appendChild(d)}}if(document.body){var a=document.createElement('iframe');a.height=1;a.width=1;a.style.position='absolute';a.style.top=0;a.style.left=0;a.style.border='none';a.style.visibility='hidden';document.body.appendChild(a);if('loading'!==document.readyState)c();else if(window.addEventListener)document.addEventListener('DOMContentLoaded',c);else{var e=document.onreadystatechange||function(){};document.onreadystatechange=function(b){e(b);'loading'!==document.readyState&&(document.onreadystatechange=e,c())}}}})();</script></body>
</html>
