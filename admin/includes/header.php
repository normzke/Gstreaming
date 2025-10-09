<?php
// Admin Header - Common layout for all admin pages
if (!isset($_SESSION['admin_id'])) {
    header('Location: login.php');
    exit();
}
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title><?php echo isset($page_title) ? $page_title . ' - ' : ''; ?>Admin Dashboard - BingeTV</title>
    
    <!-- Fonts -->
    <link href="https://fonts.googleapis.com/css2?family=Inter:wght@300;400;500;600;700;800&family=Orbitron:wght@400;700;900&display=swap" rel="stylesheet">
    
    <!-- CSS -->
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">
    <link rel="stylesheet" href="../css/main.css">
    <link rel="stylesheet" href="../css/components.css">
    <link rel="stylesheet" href="../css/admin-analytics.css">
    
    <!-- Admin Specific CSS -->
    <style>
        :root {
            --admin-primary: #8B0000;
            --admin-secondary: #2D3748;
            --admin-accent: #4A5568;
            --admin-bg: #F7FAFC;
            --admin-sidebar: #1A202C;
            --admin-text: #2D3748;
            --admin-text-light: #718096;
            --admin-border: #E2E8F0;
            --admin-shadow: 0 4px 6px -1px rgba(0, 0, 0, 0.1);
            --admin-radius: 8px;
        }

        * {
            margin: 0;
            padding: 0;
            box-sizing: border-box;
        }

        body {
            font-family: 'Inter', sans-serif;
            background: var(--admin-bg);
            color: var(--admin-text);
            line-height: 1.6;
        }

        .admin-layout {
            display: flex;
            min-height: 100vh;
        }

        /* Sidebar */
        .admin-sidebar {
            width: 280px;
            background: var(--admin-sidebar);
            color: white;
            position: fixed;
            height: 100vh;
            overflow-y: auto;
            z-index: 1000;
            transition: transform 0.3s ease;
        }

        .admin-sidebar.collapsed {
            transform: translateX(-100%);
        }

        .sidebar-header {
            padding: 1.5rem;
            border-bottom: 1px solid rgba(255, 255, 255, 0.1);
        }

        .sidebar-logo {
            display: flex;
            align-items: center;
            gap: 0.75rem;
            font-family: 'Orbitron', monospace;
            font-weight: 700;
            font-size: 1.25rem;
        }

        .sidebar-logo i {
            color: var(--admin-primary);
            font-size: 1.5rem;
        }

        .sidebar-nav {
            padding: 1rem 0;
        }

        .nav-section {
            margin-bottom: 2rem;
        }

        .nav-section-title {
            padding: 0 1.5rem 0.5rem;
            font-size: 0.75rem;
            font-weight: 600;
            text-transform: uppercase;
            letter-spacing: 0.05em;
            color: rgba(255, 255, 255, 0.6);
        }

        .nav-item {
            margin: 0.25rem 0;
        }

        .nav-link {
            display: flex;
            align-items: center;
            gap: 0.75rem;
            padding: 0.75rem 1.5rem;
            color: rgba(255, 255, 255, 0.8);
            text-decoration: none;
            transition: all 0.2s ease;
            border-left: 3px solid transparent;
        }

        .nav-link:hover {
            background: rgba(255, 255, 255, 0.1);
            color: white;
        }

        .nav-link.active {
            background: rgba(139, 0, 0, 0.2);
            color: white;
            border-left-color: var(--admin-primary);
        }

        .nav-link i {
            width: 20px;
            text-align: center;
        }

        /* Main Content */
        .admin-main {
            flex: 1;
            margin-left: 280px;
            transition: margin-left 0.3s ease;
        }

        .admin-main.sidebar-collapsed {
            margin-left: 0;
        }

        /* Top Bar */
        .admin-topbar {
            background: white;
            border-bottom: 1px solid var(--admin-border);
            padding: 1rem 2rem;
            display: flex;
            align-items: center;
            justify-content: space-between;
            box-shadow: var(--admin-shadow);
        }

        .topbar-left {
            display: flex;
            align-items: center;
            gap: 1rem;
        }

        .sidebar-toggle {
            background: none;
            border: none;
            font-size: 1.25rem;
            color: var(--admin-text);
            cursor: pointer;
            padding: 0.5rem;
            border-radius: var(--admin-radius);
            transition: background 0.2s ease;
        }

        .sidebar-toggle:hover {
            background: var(--admin-bg);
        }

        .page-title {
            font-size: 1.5rem;
            font-weight: 600;
            color: var(--admin-text);
        }

        .topbar-right {
            display: flex;
            align-items: center;
            gap: 1rem;
        }

        .admin-user {
            display: flex;
            align-items: center;
            gap: 0.75rem;
            padding: 0.5rem 1rem;
            background: var(--admin-bg);
            border-radius: var(--admin-radius);
            cursor: pointer;
            transition: background 0.2s ease;
        }

        .admin-user:hover {
            background: var(--admin-border);
        }

        .user-avatar {
            width: 32px;
            height: 32px;
            background: var(--admin-primary);
            border-radius: 50%;
            display: flex;
            align-items: center;
            justify-content: center;
            color: white;
            font-weight: 600;
        }

        .user-info {
            display: flex;
            flex-direction: column;
        }

        .user-name {
            font-weight: 600;
            font-size: 0.875rem;
        }

        .user-role {
            font-size: 0.75rem;
            color: var(--admin-text-light);
        }

        /* Content Area */
        .admin-content {
            padding: 2rem;
        }

        /* Cards */
        .admin-card {
            background: white;
            border-radius: var(--admin-radius);
            box-shadow: var(--admin-shadow);
            margin-bottom: 1.5rem;
        }

        .card-header {
            padding: 1.5rem;
            border-bottom: 1px solid var(--admin-border);
            display: flex;
            align-items: center;
            justify-content: space-between;
        }

        .card-title {
            font-size: 1.125rem;
            font-weight: 600;
            color: var(--admin-text);
        }

        .card-body {
            padding: 1.5rem;
        }

        /* Stats Grid */
        .stats-grid {
            display: grid;
            grid-template-columns: repeat(auto-fit, minmax(250px, 1fr));
            gap: 1.5rem;
            margin-bottom: 2rem;
        }

        .stat-card {
            background: white;
            border-radius: var(--admin-radius);
            padding: 1.5rem;
            box-shadow: var(--admin-shadow);
            border-left: 4px solid var(--admin-primary);
        }

        .stat-header {
            display: flex;
            align-items: center;
            justify-content: space-between;
            margin-bottom: 1rem;
        }

        .stat-title {
            font-size: 0.875rem;
            font-weight: 500;
            color: var(--admin-text-light);
            text-transform: uppercase;
            letter-spacing: 0.05em;
        }

        .stat-icon {
            width: 40px;
            height: 40px;
            background: rgba(139, 0, 0, 0.1);
            border-radius: 50%;
            display: flex;
            align-items: center;
            justify-content: center;
            color: var(--admin-primary);
        }

        .stat-value {
            font-size: 2rem;
            font-weight: 700;
            color: var(--admin-text);
            margin-bottom: 0.5rem;
        }

        .stat-change {
            font-size: 0.875rem;
            display: flex;
            align-items: center;
            gap: 0.25rem;
        }

        .stat-change.positive {
            color: #10B981;
        }

        .stat-change.negative {
            color: #EF4444;
        }

        /* Tables */
        .admin-table {
            width: 100%;
            border-collapse: collapse;
            background: white;
            border-radius: var(--admin-radius);
            overflow: hidden;
            box-shadow: var(--admin-shadow);
        }

        .admin-table th {
            background: var(--admin-bg);
            padding: 1rem;
            text-align: left;
            font-weight: 600;
            color: var(--admin-text);
            border-bottom: 1px solid var(--admin-border);
        }

        .admin-table td {
            padding: 1rem;
            border-bottom: 1px solid var(--admin-border);
        }

        .admin-table tr:hover {
            background: var(--admin-bg);
        }

        /* Buttons */
        .btn {
            display: inline-flex;
            align-items: center;
            gap: 0.5rem;
            padding: 0.75rem 1.5rem;
            border: none;
            border-radius: var(--admin-radius);
            font-weight: 500;
            text-decoration: none;
            cursor: pointer;
            transition: all 0.2s ease;
        }

        .btn-primary {
            background: var(--admin-primary);
            color: white;
        }

        .btn-primary:hover {
            background: #6B0000;
        }

        .btn-secondary {
            background: var(--admin-accent);
            color: white;
        }

        .btn-secondary:hover {
            background: #2D3748;
        }

        .btn-success {
            background: #10B981;
            color: white;
        }

        .btn-danger {
            background: #EF4444;
            color: white;
        }

        /* Responsive */
        @media (max-width: 768px) {
            .admin-sidebar {
                transform: translateX(-100%);
            }

            .admin-sidebar.mobile-open {
                transform: translateX(0);
            }

            .admin-main {
                margin-left: 0;
            }

            .admin-content {
                padding: 1rem;
            }

            .stats-grid {
                grid-template-columns: 1fr;
            }
        }
    </style>
</head>
<body>
    <div class="admin-layout">
        <!-- Sidebar -->
        <aside class="admin-sidebar" id="sidebar">
            <div class="sidebar-header">
                <div class="sidebar-logo">
                    <i class="fas fa-satellite-dish"></i>
                    <span>BingeTV Admin</span>
                </div>
            </div>
            
            <nav class="sidebar-nav">
                <div class="nav-section">
                    <div class="nav-section-title">Dashboard</div>
                    <div class="nav-item">
                        <a href="index.php" class="nav-link <?php echo basename($_SERVER['PHP_SELF']) == 'index.php' ? 'active' : ''; ?>">
                            <i class="fas fa-tachometer-alt"></i>
                            <span>Overview</span>
                        </a>
                    </div>
                </div>
                
                <div class="nav-section">
                    <div class="nav-section-title">Content</div>
                    <div class="nav-item">
                        <a href="packages.php" class="nav-link <?php echo basename($_SERVER['PHP_SELF']) == 'packages.php' ? 'active' : ''; ?>">
                            <i class="fas fa-box"></i>
                            <span>Packages</span>
                        </a>
                    </div>
                    <div class="nav-item">
                        <a href="channels.php" class="nav-link <?php echo basename($_SERVER['PHP_SELF']) == 'channels.php' ? 'active' : ''; ?>">
                            <i class="fas fa-tv"></i>
                            <span>Channels</span>
                        </a>
                    </div>
                </div>
                
                <div class="nav-section">
                    <div class="nav-section-title">Users</div>
                    <div class="nav-item">
                        <a href="users.php" class="nav-link <?php echo basename($_SERVER['PHP_SELF']) == 'users.php' ? 'active' : ''; ?>">
                            <i class="fas fa-users"></i>
                            <span>All Users</span>
                        </a>
                    </div>
                    <div class="nav-item">
                        <a href="subscriptions.php" class="nav-link <?php echo basename($_SERVER['PHP_SELF']) == 'subscriptions.php' ? 'active' : ''; ?>">
                            <i class="fas fa-credit-card"></i>
                            <span>Subscriptions</span>
                        </a>
                    </div>
                </div>
                
                <div class="nav-section">
                    <div class="nav-section-title">Financial</div>
                    <div class="nav-item">
                        <a href="payments.php" class="nav-link <?php echo basename($_SERVER['PHP_SELF']) == 'payments.php' ? 'active' : ''; ?>">
                            <i class="fas fa-money-bill-wave"></i>
                            <span>Payments</span>
                        </a>
                    </div>
                    <div class="nav-item">
                        <a href="manual-payments.php" class="nav-link <?php echo basename($_SERVER['PHP_SELF']) == 'manual-payments.php' ? 'active' : ''; ?>">
                            <i class="fas fa-check-circle"></i>
                            <span>Manual M-Pesa</span>
                        </a>
                    </div>
                    <div class="nav-item">
                        <a href="analytics.php" class="nav-link <?php echo basename($_SERVER['PHP_SELF']) == 'analytics.php' ? 'active' : ''; ?>">
                            <i class="fas fa-chart-bar"></i>
                            <span>Analytics</span>
                        </a>
                    </div>
                </div>
                
                <div class="nav-section">
                    <div class="nav-section-title">Settings</div>
                    <div class="nav-item">
                        <a href="mpesa-config.php" class="nav-link <?php echo basename($_SERVER['PHP_SELF']) == 'mpesa-config.php' ? 'active' : ''; ?>">
                            <i class="fas fa-cog"></i>
                            <span>M-PESA Config</span>
                        </a>
                    </div>
                    <div class="nav-item">
                        <a href="social-media.php" class="nav-link <?php echo basename($_SERVER['PHP_SELF']) == 'social-media.php' ? 'active' : ''; ?>">
                            <i class="fas fa-share-alt"></i>
                            <span>Social Media</span>
                        </a>
                    </div>
                </div>
            </nav>
        </aside>
        
        <!-- Main Content -->
        <main class="admin-main" id="mainContent">
            <!-- Top Bar -->
            <header class="admin-topbar">
                <div class="topbar-left">
                    <button class="sidebar-toggle" id="sidebarToggle" aria-label="Toggle sidebar" aria-expanded="false">
                        <i class="fas fa-bars"></i>
                    </button>
                    <h1 class="page-title"><?php echo isset($page_title) ? $page_title : 'Dashboard'; ?></h1>
                </div>
                
                <div class="topbar-right">
                    <div class="admin-user">
                        <div class="user-avatar">
                            <?php echo strtoupper(substr($_SESSION['admin_name'], 0, 1)); ?>
                        </div>
                        <div class="user-info">
                            <div class="user-name"><?php echo htmlspecialchars($_SESSION['admin_name']); ?></div>
                            <div class="user-role"><?php echo htmlspecialchars($_SESSION['admin_role']); ?></div>
                        </div>
                        <a href="logout.php" class="btn btn-danger" style="margin-left: 1rem;">
                            <i class="fas fa-sign-out-alt"></i>
                            Logout
                        </a>
                    </div>
                </div>
            </header>
            
            <!-- Content Area -->
            <div class="admin-content">
