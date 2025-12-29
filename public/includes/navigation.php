<?php
/**
 * Standard Navigation Header for Public Pages
 * Include this in all public/*.php files
 */
?>
<nav class="navbar">
    <div class="nav-container">
        <div class="nav-logo">
            <i class="fas fa-satellite-dish"></i>
            <span class="logo-text">BingeTV</span>
        </div>

        <ul class="nav-menu">
            <li class="nav-item">
                <a href="/"
                    class="nav-link <?php echo basename($_SERVER['PHP_SELF']) == 'index.php' ? 'active' : ''; ?>">Home</a>
            </li>
            <li class="nav-item">
                <a href="/channels"
                    class="nav-link <?php echo basename($_SERVER['PHP_SELF']) == 'channels.php' ? 'active' : ''; ?>">Channels</a>
            </li>
            <li class="nav-item">
                <a href="/gallery"
                    class="nav-link <?php echo basename($_SERVER['PHP_SELF']) == 'gallery.php' ? 'active' : ''; ?>">Gallery</a>
            </li>
            <li class="nav-item">
                <a href="/support"
                    class="nav-link <?php echo basename($_SERVER['PHP_SELF']) == 'support.php' ? 'active' : ''; ?>">Support</a>
            </li>
            <li class="nav-item">
                <a href="/apps"
                    class="nav-link <?php echo basename($_SERVER['PHP_SELF']) == 'apps.php' ? 'active' : ''; ?>">Apps</a>
            </li>
            <li class="nav-item">
                <a href="/login"
                    class="nav-link btn-login <?php echo basename($_SERVER['PHP_SELF']) == 'login.php' ? 'active' : ''; ?>">Login</a>
            </li>
            <li class="nav-item">
                <a href="/register"
                    class="nav-link btn-register <?php echo basename($_SERVER['PHP_SELF']) == 'register.php' ? 'active' : ''; ?>">Get
                    Started</a>
            </li>
        </ul>

        <div class="hamburger">
            <span class="bar"></span>
            <span class="bar"></span>
            <span class="bar"></span>
        </div>
    </div>
</nav>