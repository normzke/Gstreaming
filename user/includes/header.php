<?php
// User Header - Common layout for all user pages
require_once dirname(dirname(__DIR__)) . '/includes/auth.php';
requireLogin();

$user = getCurrentUser();
?>
<!DOCTYPE html>
<html lang="en">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title><?php echo isset($page_title) ? $page_title . ' - ' : ''; ?>BingeTV User Portal</title>

    <!-- Fonts -->
    <link
        href="https://fonts.googleapis.com/css2?family=Inter:wght@300;400;500;600;700;800&family=Orbitron:wght@400;700;900&display=swap"
        rel="stylesheet">

    <!-- CSS -->
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">
    <link rel="stylesheet" href="/user/css/main.css">
    <link rel="stylesheet" href="/user/css/components.css">

    <!-- User Portal Specific CSS -->
    <style>
        :root {
            --user-primary: #8B0000;
            --user-secondary: #2D3748;
            --user-accent: #4A5568;
            --user-bg: #F7FAFC;
            --user-sidebar: #1A202C;
            --user-text: #2D3748;
            --user-text-light: #718096;
            --user-border: #E2E8F0;
            --user-shadow: 0 4px 6px -1px rgba(0, 0, 0, 0.1);
            --user-radius: 8px;
        }

        * {
            margin: 0;
            padding: 0;
            box-sizing: border-box;
        }

        body {
            font-family: 'Inter', sans-serif;
            background: var(--user-bg);
            color: var(--user-text);
            line-height: 1.6;
        }

        .user-layout {
            display: flex;
            min-height: 100vh;
        }

        /* Sidebar */
        .user-sidebar {
            width: 280px;
            background: var(--user-sidebar);
            color: white;
            position: fixed;
            height: 100vh;
            overflow-y: auto;
            z-index: 1000;
            transition: transform 0.3s ease;
        }

        .user-sidebar.collapsed {
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
            color: var(--user-primary);
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
            border-left-color: var(--user-primary);
        }

        .nav-link i {
            width: 20px;
            text-align: center;
        }

        /* Main Content */
        .user-main {
            flex: 1;
            margin-left: 280px;
            transition: margin-left 0.3s ease;
        }

        .user-main.sidebar-collapsed {
            margin-left: 0;
        }

        /* Top Bar */
        .user-topbar {
            background: white;
            border-bottom: 1px solid var(--user-border);
            padding: 1rem 2rem;
            display: flex;
            align-items: center;
            justify-content: space-between;
            box-shadow: var(--user-shadow);
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
            color: var(--user-text);
            cursor: pointer;
            padding: 0.5rem;
            border-radius: var(--user-radius);
            transition: background 0.2s ease;
        }

        .sidebar-toggle:hover {
            background: var(--user-bg);
        }

        .page-title {
            font-size: 1.5rem;
            font-weight: 600;
            color: var(--user-text);
        }

        .topbar-right {
            display: flex;
            align-items: center;
            gap: 1rem;
        }

        .user-info {
            display: flex;
            align-items: center;
            gap: 0.75rem;
            padding: 0.5rem 1rem;
            background: var(--user-bg);
            border-radius: var(--user-radius);
            cursor: pointer;
            transition: background 0.2s ease;
        }

        .user-info:hover {
            background: var(--user-border);
        }

        .user-avatar {
            width: 32px;
            height: 32px;
            background: var(--user-primary);
            border-radius: 50%;
            display: flex;
            align-items: center;
            justify-content: center;
            color: white;
            font-weight: 600;
        }

        .user-details {
            display: flex;
            flex-direction: column;
        }

        .user-name {
            font-weight: 600;
            font-size: 0.875rem;
        }

        .user-email {
            font-size: 0.75rem;
            color: var(--user-text-light);
        }

        /* Content Area */
        .user-content {
            padding: 2rem;
        }

        /* Cards */
        .user-card {
            background: white;
            border-radius: var(--user-radius);
            box-shadow: var(--user-shadow);
            margin-bottom: 1.5rem;
        }

        .card-header {
            padding: 1.5rem;
            border-bottom: 1px solid var(--user-border);
            display: flex;
            align-items: center;
            justify-content: space-between;
        }

        .card-title {
            font-size: 1.125rem;
            font-weight: 600;
            color: var(--user-text);
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
            border-radius: var(--user-radius);
            padding: 1.5rem;
            box-shadow: var(--user-shadow);
            border-left: 4px solid var(--user-primary);
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
            color: var(--user-text-light);
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
            color: var(--user-primary);
        }

        .stat-value {
            font-size: 2rem;
            font-weight: 700;
            color: var(--user-text);
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
        .user-table {
            width: 100%;
            border-collapse: collapse;
            background: white;
            border-radius: var(--user-radius);
            overflow: hidden;
            box-shadow: var(--user-shadow);
        }

        .user-table th {
            background: var(--user-bg);
            padding: 1rem;
            text-align: left;
            font-weight: 600;
            color: var(--user-text);
            border-bottom: 1px solid var(--user-border);
        }

        .user-table td {
            padding: 1rem;
            border-bottom: 1px solid var(--user-border);
        }

        .user-table tr:hover {
            background: var(--user-bg);
        }

        /* Buttons */
        .btn {
            display: inline-flex;
            align-items: center;
            gap: 0.5rem;
            padding: 0.75rem 1.5rem;
            border: none;
            border-radius: var(--user-radius);
            font-weight: 500;
            text-decoration: none;
            cursor: pointer;
            transition: all 0.2s ease;
        }

        .btn-primary {
            background: var(--user-primary);
            color: white;
        }

        .btn-primary:hover {
            background: #6B0000;
        }

        .btn-secondary {
            background: var(--user-accent);
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
            .user-sidebar {
                transform: translateX(-100%);
            }

            .user-sidebar.mobile-open {
                transform: translateX(0);
            }

            .user-main {
                margin-left: 0;
            }

            .user-content {
                padding: 1rem;
            }

            .stats-grid {
                grid-template-columns: 1fr;
            }
        }
    </style>
</head>

<body>
    <div class="user-layout">
        <!-- Sidebar -->
        <aside class="user-sidebar" id="sidebar">
            <div class="sidebar-header">
                <div class="sidebar-logo">
                    <i class="fas fa-satellite-dish"></i>
                    <span>BingeTV</span>
                </div>
            </div>

            <nav class="sidebar-nav">
                <div class="nav-section">
                    <div class="nav-section-title">Main</div>
                    <div class="nav-item">
                        <a href="/user/dashboard/"
                            class="nav-link <?php echo basename($_SERVER['PHP_SELF']) == 'index.php' && strpos($_SERVER['REQUEST_URI'], '/dashboard') !== false ? 'active' : ''; ?>">
                            <i class="fas fa-tachometer-alt"></i>
                            <span>Dashboard</span>
                        </a>
                    </div>
                    <div class="nav-item">
                        <a href="/user/channels"
                            class="nav-link <?php echo basename($_SERVER['PHP_SELF']) == 'channels.php' ? 'active' : ''; ?>">
                            <i class="fas fa-tv"></i>
                            <span>Watch Channels</span>
                        </a>
                    </div>
                    <div class="nav-item">
                        <a href="/user/downloads"
                            class="nav-link <?php echo basename($_SERVER['PHP_SELF']) == 'downloads.php' ? 'active' : ''; ?>">
                            <i class="fas fa-download"></i>
                            <span>Download Apps</span>
                        </a>
                    </div>
                    <div class="nav-item">
                        <a href="/user/gallery"
                            class="nav-link <?php echo basename($_SERVER['PHP_SELF']) == 'gallery.php' ? 'active' : ''; ?>">
                            <i class="fas fa-images"></i>
                            <span>Gallery</span>
                        </a>
                    </div>
                </div>

                <div class="nav-section">
                    <div class="nav-section-title">My Account</div>
                    <div class="nav-item">
                        <a href="/user/subscriptions"
                            class="nav-link <?php echo basename($_SERVER['PHP_SELF']) == 'subscriptions.php' ? 'active' : ''; ?>">
                            <i class="fas fa-credit-card"></i>
                            <span>Subscriptions</span>
                        </a>
                    </div>
                    <div class="nav-item">
                        <a href="/user/payments"
                            class="nav-link <?php echo basename($_SERVER['PHP_SELF']) == 'payments.php' ? 'active' : ''; ?>">
                            <i class="fas fa-receipt"></i>
                            <span>Payments</span>
                        </a>
                    </div>
                    <div class="nav-item">
                        <a href="/user/support"
                            class="nav-link <?php echo basename($_SERVER['PHP_SELF']) == 'support.php' ? 'active' : ''; ?>">
                            <i class="fas fa-headset"></i>
                            <span>Support</span>
                        </a>
                    </div>
                </div>

                <div class="nav-section">
                    <div class="nav-section-title">Quick Actions</div>
                    <div class="nav-item">
                        <a href="/user/subscriptions.php#packages" class="nav-link">
                            <i class="fas fa-plus-circle"></i>
                            <span>Subscribe Now</span>
                        </a>
                    </div>
                    <div class="nav-item">
                        <a href="/user/subscriptions#packages" class="nav-link">
                            <i class="fas fa-credit-card"></i>
                            <span>Pay Online (Card/M-Pesa)</span>
                        </a>
                    </div>
                    <div class="nav-item">
                        <a href="/user/payments/submit-mpesa"
                            class="nav-link <?php echo basename($_SERVER['PHP_SELF']) == 'submit-mpesa.php' ? 'active' : ''; ?>">
                            <i class="fas fa-mobile-alt"></i>
                            <span>Manual M-Pesa Submit</span>
                        </a>
                    </div>
                    <div class="nav-item">
                        <a href="/help" class="nav-link" target="_blank">
                            <i class="fas fa-question-circle"></i>
                            <span>Help & FAQs</span>
                        </a>
                    </div>
                </div>

                <div class="nav-section"
                    style="border-top: 1px solid rgba(255,255,255,0.1); padding-top: 1rem; margin-top: 1rem;">
                    <div class="nav-item">
                        <a href="/user/logout" class="nav-link">
                            <i class="fas fa-sign-out-alt"></i>
                            <span>Logout</span>
                        </a>
                    </div>
                </div>
            </nav>
        </aside>

        <!-- Main Content -->
        <main class="user-main" id="mainContent">
            <!-- Top Bar -->
            <header class="user-topbar">
                <div class="topbar-left">
                    <button class="sidebar-toggle" id="sidebarToggle">
                        <i class="fas fa-bars"></i>
                    </button>
                    <h1 class="page-title"><?php echo isset($page_title) ? $page_title : 'Dashboard'; ?></h1>
                </div>

                <div class="topbar-right">
                    <div class="user-info">
                        <div class="user-avatar">
                            <?php echo strtoupper(substr($user['first_name'], 0, 1)); ?>
                        </div>
                        <div class="user-details">
                            <div class="user-name">
                                <?php echo htmlspecialchars($user['first_name'] . ' ' . $user['last_name']); ?>
                            </div>
                            <div class="user-email"><?php echo htmlspecialchars($user['email']); ?></div>
                        </div>
                    </div>
                </div>
            </header>

            <!-- Content Area -->
            <div class="user-content">