<?php // File: admin/partials/navbar.php (Now a Sidebar)

// Ensure $current_page_name is set, e.g., $current_page_name = basename($_SERVER['PHP_SELF']);
// This should be set in each page that includes this partial, or in a common controller/bootstrap file.
if (!isset($current_page_name)) {
    $current_page_name = basename($_SERVER['PHP_SELF']); // Fallback
}
?>
<html>

<head>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/css/bootstrap.min.css" rel="stylesheet">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.5.1/css/all.min.css">
    <script src="https://code.jquery.com/jquery-3.7.1.min.js"></script>
    <link href="https://cdn.jsdelivr.net/npm/remixicon@3.5.0/fonts/remixicon.css" rel="stylesheet">
</head>

<body>
   <aside class="admin-sidebar" id="adminSidebar">
    <div class="sidebar-brand">
        <a href="index.php" title="AIREN Store Admin Dashboard">
            <i class="ri-vip-diamond-line"></i> <span class="brand-text">AIREN Store</span>
        </a>
    </div>

    <nav class="sidebar-nav">
        <ul class="nav-list">
            <li class="nav-item">
                <a class="nav-link <?= ($current_page_name == 'index.php') ? 'active' : '' ?>" href="index.php"
                    title="Dashboard">
                    <i class="ri-dashboard-line fa-fw"></i>
                    <span class="link-text">Dashboard</span>
                </a>
            </li>
            <li class="nav-item">
                <a class="nav-link <?= ($current_page_name == 'produk.php') ? 'active' : '' ?>" href="produk.php"
                    title="Produk">
                    <i class="ri-box-3-line fa-fw"></i>
                    <span class="link-text">Produk</span>
                </a>
            </li>
            <li class="nav-item">
                <a class="nav-link <?= ($current_page_name == 'slide_management.php') ? 'active' : '' ?> "
                    href="slide_management.php" title="Slide Management">
                    <i class="ri-image-line fa-fw"></i>
                    <span class="link-text">Slide Management</span>
                </a>
            </li>
            <li class="nav-item">
                <a class="nav-link <?= ($current_page_name == 'setting_brand.php') ? 'active' : '' ?>"
                    href="setting_brand.php" title="Logo Management">
                    <i class="ri-palette-line fa-fw"></i>
                    <span class="link-text">Logo Management</span>
                </a>
            </li>
        </ul>
    </nav>

    <div class="sidebar-user-area">
        <div class="dropdown">
            <a href="#" class="sidebar-user-toggle dropdown-toggle" id="adminUserSidebarDropdown" role="button"
                data-bs-toggle="dropdown" aria-expanded="false" title="Admin Menu">
                <i class="ri-shield-user-line fa-fw user-icon"></i>
                <span
                    class="user-name link-text"><?= isset($_SESSION['admin_username']) ? htmlspecialchars($_SESSION['admin_username']) : 'Admin' ?></span>
                <i class="ri-arrow-up-s-line fa-xs toggle-arrow link-text"></i>
            </a>
            <ul class="dropdown-menu dropdown-menu-dark shadow-lg" aria-labelledby="adminUserSidebarDropdown"
                data-bs-popper-config='{"placement":"top-start"}'>
                <li><a class="dropdown-item" href="#"><i class="ri-user-3-line fa-fw me-2"></i>Profil Saya</a>
                </li>
                <li><a class="dropdown-item" href="../index.php" target="_blank"><i
                            class="ri-global-line fa-fw me-2"></i>Landing Page</a>
                </li>
                <li>
                    <hr class="dropdown-divider">
                </li>
                <li><a class="dropdown-item text-danger logout-link" href="logout.php"><i
                            class="ri-logout-box-line fa-fw me-2"></i>Logout</a></li>
            </ul>
        </div>
    </div>

    <button class="sidebar-toggler" id="sidebarToggler" type="button" title="Toggle Sidebar">
        <i class="ri-arrow-left-s-line"></i>
    </button>
</aside>

</body>

</html>