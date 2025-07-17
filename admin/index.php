<?php
session_start();
require_once '../config/db.php'; // Koneksi database

// Cek apakah admin sudah login
if (!isset($_SESSION['role']) || $_SESSION['role'] !== 'admin') {
    header('Location: ../login.php');
    exit;
}

$page_title = "Dashboard";
// Variabel ini diperlukan oleh sidebar untuk menandai link aktif
$current_page_name = basename($_SERVER['PHP_SELF']);

// Ambil statistik total produk
$total_products = 0;
try {
    $stmt_products = $pdo->query("SELECT COUNT(*) FROM products");
    $total_products = $stmt_products->fetchColumn();
} catch (PDOException $e) {
    // error_log("Error fetching product count: " . $e->getMessage());
}

// Ambil statistik total stok
$total_stok = 0;
try {
    $stmt_stok = $pdo->query("SELECT SUM(stock) FROM products");
    $total_stok = $stmt_stok->fetchColumn();
    if ($total_stok === null) {
        $total_stok = 0;
    }
} catch (PDOException $e) {
    // error_log("Error fetching total stock: " . $e->getMessage());
}

$new_orders = 0;
?>
<!DOCTYPE html>
<html lang="id" data-theme="light">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title><?= htmlspecialchars($page_title) ?> | AIREN Store</title>

    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/css/bootstrap.min.css" rel="stylesheet">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.5.1/css/all.min.css">
    <link href="https://cdn.jsdelivr.net/npm/remixicon@3.5.0/fonts/remixicon.css" rel="stylesheet">
    <link href="https://fonts.googleapis.com/css2?family=Poppins:wght@300;400;500;600;700&display=swap"
        rel="stylesheet">

    <link rel="stylesheet" href="css/tes.css">
</head>

<body>

    <div class="admin-layout-wrapper">

        <?php include 'partials/sidebar.php'; ?>

        <div class="main-content-wrapper">

            <?php include 'partials/header.php'; ?>

            <main class="admin-page-content">
                <div class="container-fluid">
                    <div class="page-header mb-4">
                        <h1 class="display-5 fw-bold">Dashboard</h1>
                    </div>

                    <p class="lead mb-4 welcome-message">
                        Selamat datang kembali,
                        <span style="color: var(--custom-primary-color); font-weight: 600;">
                            <?= isset($_SESSION['username']) ? htmlspecialchars($_SESSION['username']) : 'Admin' ?>!
                        </span>
                    </p>

                    <div class="row">
                        <div class="col-xl-4 col-md-6 mb-4">
                            <div class="card dashboard-card h-100">
                                <div class="card-body d-flex align-items-center">
                                    <div class="card-icon-wrapper">
                                        <i class="ri-box-3-line fa-2x"></i>
                                    </div>
                                    <div class="stat-content">
                                        <div class="stat-label">Total Produk</div>
                                        <div class="stat-number"><?= htmlspecialchars($total_products) ?></div>
                                        <a href="produk.php" class="stretched-link card-link">
                                            Kelola Produk <i class="ri-arrow-right-line fa-xs"></i>
                                        </a>
                                    </div>
                                </div>
                            </div>
                        </div>

                        <div class="col-xl-4 col-md-6 mb-4">
                            <div class="card dashboard-card h-100">
                                <div class="card-body d-flex align-items-center">
                                    <div class="card-icon-wrapper">
                                        <i class="ri-archive-drawer-line fa-2x"></i>
                                    </div>
                                    <div class="stat-content">
                                        <div class="stat-label">Total Stok</div>
                                        <div class="stat-number"><?= htmlspecialchars($total_stok) ?></div>
                                        <a href="produk.php" class="stretched-link card-link">
                                            Kelola Stok <i class="ri-arrow-right-line fa-xs"></i>
                                        </a>
                                    </div>
                                </div>
                            </div>
                        </div>

                        <div class="col-xl-4 col-md-6 mb-4">
                            <div class="card dashboard-card h-100">
                                <div class="card-body d-flex align-items-center">
                                    <div class="card-icon-wrapper">
                                        <i class="ri-shopping-cart-line fa-2x"></i>
                                    </div>
                                    <div class="stat-content">
                                        <div class="stat-label">Pesanan Baru</div>
                                        <div class="stat-number"><?= htmlspecialchars($new_orders) ?></div>
                                        <a href="#" class="stretched-link card-link"
                                            onclick="alert('Fitur pesanan belum tersedia.'); return false;">
                                            Lihat Pesanan <i class="ri-arrow-right-line fa-xs"></i>
                                        </a>
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>

                    <div class="quick-actions-panel card mt-4">
                        <div class="card-body">
                            <h4 class="card-title mb-3">Aksi Cepat</h4>
                            <div class="d-flex flex-wrap gap-2">
                                <a href="produk.php?action=add_new" class="btn btn-admin-primary">
                                    <i class="ri-add-line"></i> Tambah Produk Baru
                                </a>
                                <a href="../index.php" target="_blank" class="btn btn-outline-secondary">
                                    <i class="ri-global-line"></i> Lihat Landing Page
                                </a>
                            </div>
                        </div>
                    </div>
                </div>
            </main>
        </div>
    </div>

    <div class="toast-container position-fixed bottom-0 end-0 p-3"></div>

    <div class="sidebar-overlay" id="sidebarOverlay"></div>

    <!-- <script src="https://code.jquery.com/jquery-3.7.1.min.js"></script> -->
    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/js/bootstrap.bundle.min.js"></script>

    <script src="js/main.js"></script>
</body>

</html>