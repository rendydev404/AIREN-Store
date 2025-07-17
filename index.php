<?php
session_start();
ini_set('display_errors', 1); // Tampilkan error untuk debugging
error_reporting(E_ALL);

// Sertakan file konfigurasi Midtrans agar variabelnya bisa diakses di HTML
// Tambahkan pengecekan agar tidak error jika file belum ada
if (file_exists('config-midtrans.php')) {
    require_once 'config-midtrans.php';
} else {
    // Definisikan konstanta dummy jika file tidak ditemukan, agar HTML tidak error
    define('MIDTRANS_CLIENT_KEY', 'Mid-client-7SKzi6jrsrBHJYIN');
}

include 'config/db.php'; // Pastikan file ini ada dan berfungsi

$settings = [];
try {
    $stmt = $pdo->query("SELECT setting_name, setting_value FROM site_settings");
    while ($row = $stmt->fetch(PDO::FETCH_ASSOC)) {
        $settings[$row['setting_name']] = $row['setting_value'];
    }
} catch (Exception $e) {
    // Jika ada error (misal: tabel belum dibuat), gunakan nilai fallback untuk mencegah error.
    $settings = [
        'logo_type' => 'text',
        'logo_text' => 'AIREN Store',
        'logo_image' => '',
        'logo_image_height' => '40',
        'logo_text_size' => '24',
        'logo_font_family' => 'Poppins',
        'logo_font_weight' => '700',
        'logo_text_color' => '#000000',
        'logo_icon_class' => 'fas fa-gem',
        'logo_icon_color' => '#14b5ff'
    ];
}
// Ambil data untuk hero slider
$hero_sliders = [];
if (isset($pdo)) {
    try {
        // Ambil slider yang aktif saja, urutkan berdasarkan order_index
        $stmt_slider = $pdo->query("SELECT title, subtitle, image FROM hero_sliders WHERE is_active = 1 ORDER BY order_index ASC, id DESC");
        $hero_sliders = $stmt_slider->fetchAll(PDO::FETCH_ASSOC);
    } catch (PDOException $e) {
        error_log("Slider DB error: " . $e->getMessage());
    }
}

$products = [];
if (isset($pdo)) {
    try {
        // Ambil juga kolom stock (stok)
        $stmt = $pdo->query("SELECT id, name, description, price, image, created_at, stock FROM products ORDER BY created_at DESC");
        $products = $stmt->fetchAll(PDO::FETCH_ASSOC);
    } catch (PDOException $e) {
        error_log("Database error: " . $e->getMessage());
        // Jika error, $products akan tetap kosong, dan pesan di bawah akan muncul
    }
}

$new_product_limit = 1; // Jumlah produk yang dianggap "baru" (berdasarkan hari)
$product_display_limit_initial = 6; // Jumlah produk yang ditampilkan awalnya (untuk "Load More")

$product_counter = 0;
$unique_id_counter = 0;
?>

<!DOCTYPE html>
<html lang="id">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>AIREN Store - Toko serba ada</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/css/bootstrap.min.css" rel="stylesheet">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.5.2/css/all.min.css">
    <link href="https://cdn.jsdelivr.net/npm/remixicon@3.5.0/fonts/remixicon.css" rel="stylesheet">

    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
    <link
        href="https://fonts.googleapis.com/css2?family=Poppins:wght@300;400;500;600;700&family=Playfair+Display:wght@700&display=swap"
        rel="stylesheet">
    <link rel="stylesheet" href="css/style.css">
</head>

<body data-theme="light">
    <!-- Navbar -->
    <nav class="navbar navbar-expand-lg navbar-light sticky-top">
        <div class="container">
            <a class="navbar-brand d-flex align-items-center" href="index.php">
                <?php
                // Kode PHP tetap sama
                $logo_type = $settings['logo_type'] ?? 'text';
                if ($logo_type === 'image' && !empty($settings['logo_image'])) {
                    $image_height = $settings['logo_image_height'] ?? '40';
                    echo '<img src="uploads/logo/' . htmlspecialchars($settings['logo_image']) . '" alt="' . htmlspecialchars($settings['logo_text'] ?? 'Site Logo') . '" style="height: ' . htmlspecialchars($image_height) . 'px;">';
                } else {
                    $font_family = $settings['logo_font_family'] ?? 'Poppins';
                    $font_weight = $settings['logo_font_weight'] ?? '700';
                    $text_size = $settings['logo_text_size'] ?? '24';
                    $text_color = $settings['logo_text_color'] ?? '#000000';
                    $icon_class = $settings['logo_icon_class'] ?? 'ri-gem-line'; // GANTI DEFAULT JUGA JIKA MASIH FA
                    $icon_color = $settings['logo_icon_color'] ?? '#14b5ff';
                    $logo_text = $settings['logo_text'] ?? 'AIREN Store';
                    $dynamic_font_size_style = 'font-size: ' . htmlspecialchars($text_size) . 'px;';
                    echo '<i class="' . htmlspecialchars($icon_class) . ' me-2" style="color: ' . htmlspecialchars($icon_color) . ';' . $dynamic_font_size_style . '"></i>';
                    echo '<span style="font-family: \'' . $font_family . '\', sans-serif; font-weight: ' . htmlspecialchars($font_weight) . '; color: ' . htmlspecialchars($text_color) . ';' . $dynamic_font_size_style . '">' . htmlspecialchars($logo_text) . '</span>';
                }
                ?>
            </a>

            <div class="collapse navbar-collapse" id="mainNav">
                <ul class="navbar-nav mx-auto">
                    <li class="nav-item"><a class="nav-link active" href="#">Home</a></li>
                    <li class="nav-item"><a class="nav-link" href="#produk">Produk</a></li>
                    <li class="nav-item"><a class="nav-link" href="#tentang">Tentang</a></li>
                    <li class="nav-item"><a class="nav-link" href="#kontak">Kontak</a></li>
                </ul>
                <ul class="navbar-nav flex-row align-items-center">
                    <li class="nav-item">
                        <a class="nav-link px-2" href="#" title="Wishlist" id="wishlistNavIcon" data-bs-toggle="modal"
                            data-bs-target="#wishlistModal">
                            <i class="ri-heart-2-line nav-icon"></i>
                            <span id="wishlistCountBadge" class="badge rounded-pill bg-danger ms-1">0</span>
                        </a>
                    </li>
                    <li class="nav-item">
                        <a class="nav-link px-2" href="#" title="Keranjang Belanja" data-bs-toggle="modal"
                            data-bs-target="#cartModal">
                            <i class="ri-shopping-cart-line nav-icon"></i>
                            <span id="cartCountBadge" class="badge rounded-pill bg-danger ms-1">0</span>
                        </a>
                    </li>
                    <li class="nav-item">
                        <button id="themeToggleDesktopBtn" class="btn nav-link px-2" title="Ganti Tema">
                            <i class="ri-sun-line"></i>
                        </button>
                    </li>
                    <li class="nav-item dropdown ms-2">
                        <?php if (isset($_SESSION['user_id'])):
                            $username = isset($_SESSION['username']) ? htmlspecialchars($_SESSION['username']) : 'User';
                            ?>
                            <a class="nav-link dropdown-toggle" href="#" id="userDropdownDesktop" role="button"
                                data-bs-toggle="dropdown" aria-expanded="false">
                                <i class="ri-user-follow-line nav-icon me-1"></i> <?= $username ?>
                            </a>
                            <ul class="dropdown-menu dropdown-menu-end" aria-labelledby="userDropdownDesktop">
                                <li>
                                    <a class="dropdown-item" href="logout.php">
                                        <i class="ri-logout-box-line fa-fw me-2"></i>Logout
                                    </a>
                                </li>
                            </ul>
                        <?php else: ?>
                            <a class="nav-link" href="login.php" title="Login">
                                <i class="ri-user-3-line nav-icon"></i>
                            </a>
                        <?php endif; ?>
                    </li>
                </ul>
            </div>

            <button class="hamburger-toggler d-lg-none" type="button" id="hamburgerToggler">
                <span class="line"></span><span class="line"></span><span class="line"></span>
            </button>
        </div>
    </nav>


    <div class="mobile-sidebar" id="mobileSidebar">
        <div class="sidebar-header">
            <?php if (isset($_SESSION['user_id'])):
                $username = isset($_SESSION['username']) ? htmlspecialchars($_SESSION['username']) : 'User';
                ?>
                <h5 class="sidebar-title">Halo, <?= $username ?></h5>
            <?php else: ?>
                <h5 class="sidebar-title">Menu Navigasi</h5>
            <?php endif; ?>
        </div>
        <div class="sidebar-body">
            <ul class="navbar-nav flex-column">
                <li class="nav-item">
                    <a class="nav-link" href="#">
                        <i class="ri-home-4-line fa-fw me-3"></i>Home
                    </a>
                </li>
                <li class="nav-item">
                    <a class="nav-link" href="#produk">
                        <i class="ri-box-3-line fa-fw me-3"></i>Produk
                    </a>
                </li>
                <li class="nav-item">
                    <a class="nav-link" href="#tentang">
                        <i class="ri-information-line fa-fw me-3"></i>Tentang
                    </a>
                </li>
                <li class="nav-item">
                    <a class="nav-link" href="#kontak">
                        <i class="ri-mail-line fa-fw me-3"></i>Kontak
                    </a>
                </li>
            </ul>
            <hr>
            <ul class="navbar-nav flex-column">
                <li class="nav-item">
                    <a class="nav-link" href="#" data-bs-toggle="modal" data-bs-target="#wishlistModal">
                        <i class="ri-heart-line fa-fw me-3"></i>Wishlist
                        <span id="wishlistCountBadgeMobile" class="badge rounded-pill bg-danger ms-auto">0</span>
                    </a>
                </li>
                <li class="nav-item">
                    <a class="nav-link" href="#" data-bs-toggle="modal" data-bs-target="#cartModal">
                        <i class="ri-shopping-cart-line fa-fw me-3"></i>Keranjang
                        <span id="cartCountBadgeMobile" class="badge rounded-pill bg-danger ms-auto">0</span>
                    </a>
                </li>
                <li class="nav-item">
                    <button id="themeToggleMobileBtn" class="btn nav-link text-start w-100">
                        <i class="ri-sun-line fa-fw me-3"></i>Ganti Tema
                    </button>
                </li>
                <?php if (isset($_SESSION['user_id'])): ?>
                    <li class="nav-item">
                        <a class="nav-link" href="logout.php">
                            <i class="ri-logout-box-line fa-fw me-3"></i>Logout
                        </a>
                    </li>
                <?php else: ?>
                    <li class="nav-item">
                        <a class="nav-link" href="login.php">
                            <i class="ri-user-add-line fa-fw me-3"></i>Login / Daftar
                        </a>
                    </li>
                <?php endif; ?>
            </ul>
        </div>
    </div>



    <div class="sidebar-overlay" id="sidebarOverlay"></div>



    <!-- Main Content -->
    <main>
        <!-- Hero Section -->
        <header class="hero">
            <?php if (!empty($hero_sliders)): ?>
                <div id="heroCarousel" class="carousel slide" data-bs-ride="carousel" data-bs-interval="3000">

                    <div class="carousel-indicators">
                        <?php foreach ($hero_sliders as $index => $slider): ?>
                            <button type="button" data-bs-target="#heroCarousel" data-bs-slide-to="<?= $index ?>"
                                class="<?= $index === 0 ? 'active' : '' ?>"
                                aria-current="<?= $index === 0 ? 'true' : 'false' ?>"
                                aria-label="Slide <?= $index + 1 ?>"></button>
                        <?php endforeach; ?>
                    </div>

                    <div class="carousel-inner">
                        <?php foreach ($hero_sliders as $index => $slider): ?>
                            <div class="carousel-item <?= $index === 0 ? 'active' : '' ?>">
                                <div class="hero-slide-item"
                                    style="background-image: url('uploads/hero/<?= htmlspecialchars($slider['image']) ?>');">
                                    <div class="hero-overlay"></div>
                                    <div class="container text-center hero-content">
                                        <h1 data-aos="fade-down"><?= htmlspecialchars($slider['title']) ?></h1>
                                        <p data-aos="fade-up" data-aos-delay="200"><?= htmlspecialchars($slider['subtitle']) ?>
                                        </p>
                                        <a href="#produk" class="btn btn-primary-custom" data-aos="fade-up"
                                            data-aos-delay="400">Lihat Koleksi</a>
                                    </div>
                                </div>
                            </div>
                        <?php endforeach; ?>
                    </div>

                    <button class="carousel-control-prev" type="button" data-bs-target="#heroCarousel" data-bs-slide="prev">
                        <span class="carousel-control-prev-icon" aria-hidden="true"></span>
                        <span class="visually-hidden">Previous</span>
                    </button>
                    <button class="carousel-control-next" type="button" data-bs-target="#heroCarousel" data-bs-slide="next">
                        <span class="carousel-control-next-icon" aria-hidden="true"></span>
                        <span class="visually-hidden">Next</span>
                    </button>

                </div>
            <?php else: ?>
                <div class="hero-overlay"></div>
                <div class="container text-center hero-content">
                    <h1 data-aos="fade-down">Semua Kebutuhan Anda dalam Satu Tempat</h1>
                    <p data-aos="fade-up" data-aos-delay="200">Jelajahi beragam koleksi produk kami yang dirancang untuk
                        memenuhi setiap kebutuhan Anda.</p>
                    <a href="#produk" class="btn btn-primary-custom" data-aos="fade-up" data-aos-delay="400">Lihat
                        Koleksi</a>
                </div>
            <?php endif; ?>
        </header>

        <!-- Product Section -->
        <section class="container py-5">
            <h2 class="text-center section-title" data-aos="fade-up">Produk Kami</h2>
            <p class="text-center section-subtitle" data-aos="fade-up" data-aos-delay="100">Produk pilihan yang dikurasi
                khusus untuk Anda.</p>

            <!-- Filter and Sort Bar -->
            <div class="filter-sort-bar row g-3 align-items-end mb-4" data-aos="fade-up" data-aos-delay="200">
                <div class="col-md-6 col-lg-3">
                    <label for="filterPrice" class="form-label">Rentang Harga</label>
                    <div class="input-group">
                        <input type="number" class="form-control" id="minPrice" placeholder="Min"
                            aria-label="Harga Minimal">
                        <span class="input-group-text">-</span>
                        <input type="number" class="form-control" id="maxPrice" placeholder="Max"
                            aria-label="Harga Maksimal">
                    </div>
                </div>
                <div class="col-md-6 col-lg-3" id="filterCategory">
                    <label for="sortProducts" class="form-label">Urutkan</label>
                    <select class="form-select" id="sortProducts">
                        <option selected value="newest">Terbaru</option>
                        <option value="price_asc">Harga: Termurah</option>
                        <option value="price_desc">Harga: Termahal</option>
                        <option value="name_asc">Nama: A-Z</option>
                        <option value="name_desc">Nama: Z-A</option>
                    </select>
                </div>
                <div class="col-lg-6 col-md-12">
                    <form id="productSearchForm" class="search-form-filter">
                        <i class="fas fa-search search-icon"></i>
                        <input type="search" id="searchInputMobile" class="form-control"
                            placeholder="Cari nama produk...">
                    </form>
                </div>
                <div id="produk" class="col-md-6 col-lg-3 d-flex align-items-end">
                    <button class="btn btn-outline-secondary w-100" id="clearFiltersBtn">Reset Filter</button>
                </div>
            </div>

            <!-- Product List -->
            <div class="row g-4" id="productList">
                <?php if (empty($products)): ?>
                    <div class="col">
                        <p class="text-center fs-5 mt-4">Mohon maaf, belum ada produk yang tersedia. Silakan cek kembali
                            nanti.</p>
                    </div>
                <?php else: ?>
                    <?php foreach ($products as $product):
                        $product['price'] = isset($product['price']) ? (float) $product['price'] : 0;
                        $product['stock'] = isset($product['stock']) ? (int) $product['stock'] : 0;
                        $product_counter++;
                        $unique_id_counter++;

                        $is_new = false;
                        if (isset($product['created_at'])) {
                            $product_date = new DateTime($product['created_at']);
                            $now = new DateTime();
                            $interval = $now->diff($product_date);
                            if ($interval->days <= $new_product_limit) {
                                $is_new = true;
                            }
                        }
                        ?>
                        <div class="col-6 col-md-4 col-lg-3 product-item" data-aos="fade-up"
                            data-aos-delay="<?= ($product_counter % 4) * 100 ?>" data-id="<?= $product['id'] ?>"
                            data-name="<?= htmlspecialchars(strtolower($product['name'])) ?>"
                            data-price="<?= $product['price'] ?>" data-category="general" data-brand="unknown"
                            data-stock="<?= $product['stock'] ?? 0 ?>"
                            data-image="uploads/<?= htmlspecialchars($product['image']) ?>"
                            data-description="<?= htmlspecialchars($product['description']) ?>"
                            data-created_at="<?= $product['created_at'] ?? '' ?>"
                            style="<?= $product_counter > $product_display_limit_initial ? 'display: none;' : '' ?>">
                            <div class="card card-product h-100">
                                <?php if ($is_new): ?>
                                    <span class="badge-new">Baru!</span>
                                <?php endif; ?>
                                <?php if (isset($product['stock']) && $product['stock'] == 0): ?>
                                    <span class="badge-sold-out">Habis</span>
                                <?php endif; ?>

                                <div class="card-img-top-wrapper">
                                    <img src="uploads/<?= htmlspecialchars($product['image']) ?>" class="card-img-top"
                                        alt="<?= htmlspecialchars($product['name']) ?>" loading="lazy">
                                    <div class="product-actions">
                                        <button class="btn btn-icon btn-quick-view" title="Lihat Detail" data-bs-toggle="modal"
                                            data-bs-target="#quickViewModal" data-product-id="<?= $product['id'] ?>">
                                            <i class="fas fa-eye"></i>
                                        </button>
                                        <button class="btn btn-icon wishlist-btn" title="Tambah ke Wishlist"
                                            data-product-id="<?= $product['id'] ?>">
                                            <i class="far fa-heart"></i>
                                        </button>
                                    </div>
                                </div>
                                <div class="card-body d-flex flex-column">
                                    <h5 class="card-title product-name">
                                        <?= htmlspecialchars($product['name']) ?>
                                    </h5>

                                    <p class="card-text description text-muted small d-none d-md-block">
                                        <?= substr(htmlspecialchars($product['description']), 0, 60) . (strlen($product['description']) > 60 ? '...' : '') ?>
                                    </p>

                                    <div class="mt-auto d-flex justify-content-between align-items-center">
                                        <div>
                                            <div class="price">Rp<span class="price-value"
                                                    data-base-price="<?= $product['price'] ?>">
                                                    <?= number_format($product['price'], 0, ',', '.') ?>
                                                </span>
                                            </div>
                                            <small class="text-muted stock-display">Stok: <?= $product['stock'] ?? 0 ?></small>
                                        </div>

                                        <div class="d-block d-md-none">
                                            <button class="btn btn-primary-custom add-to-cart-btn" title="Tambah ke Keranjang"
                                                id="quickViewAddToCartButton" data-id="<?= $product['id'] ?>"
                                                <?= ($product['stock'] == 0) ? 'disabled' : '' ?>>
                                                <i class="fas fa-cart-plus"></i>
                                            </button>
                                        </div>
                                    </div>

                                    <div class="mt-2 d-none d-md-block">
                                        <div class="input-group quantity-input-group mb-2">
                                            <button class="btn btn-outline-secondary quantity-minus" type="button"
                                                <?= (isset($product['stock']) && $product['stock'] == 0) ? 'disabled' : '' ?>>-</button>
                                            <input type="number" class="form-control quantity-input text-center" value="1"
                                                min="1" max="<?= $product['stock'] ?? 1 ?>" aria-label="Quantity"
                                                data-product-id="<?= $product['id'] ?>" <?= (isset($product['stock']) && $product['stock'] == 0) ? 'disabled' : '' ?>>
                                            <button class="btn btn-outline-secondary quantity-plus" type="button"
                                                <?= (isset($product['stock']) && $product['stock'] == 0) ? 'disabled' : '' ?>>+</button>
                                        </div>
                                        <button class="btn btn-primary-custom w-100 add-to-cart-btn"
                                            data-id="<?= $product['id'] ?>" <?= ($product['stock'] == 0) ? 'disabled' : '' ?>>
                                            <i class="fas fa-cart-plus"></i>
                                            <?= ($product['stock'] == 0) ? 'Stok Habis' : 'Tambah ke Keranjang' ?>
                                        </button>
                                    </div>
                                </div>
                            </div>
                        </div>
                    <?php endforeach; ?>
                <?php endif; ?>
            </div>
            <?php if (count($products) > $product_display_limit_initial): ?>
                <div class="text-center mt-4">
                    <button class="btn btn-outline-primary" id="loadMoreBtn">Tampilkan Lebih Banyak</button>
                </div>
            <?php endif; ?>
            <div id="noProductFoundMessage" class="text-center text-muted mt-4 col-12" style="display: none;"></div>
        </section>

        <!-- About Section -->
        <section class="about-section" id="tentang">
            <div class="container">
                <h2 class="text-center section-title" data-aos="fade-up">Tentang AIREN Store</h2>
                <p class="text-center section-subtitle" data-aos="fade-up" data-aos-delay="100">Kualitas, Kepercayaan,
                    dan Kepuasan Pelanggan.</p>
                <div class="row align-items-center">
                    <div class="col-md-6 mb-4 mb-md-0" data-aos="fade-right" data-aos-delay="200">
                        <img src="assets/about.png" class="img-fluid rounded shadow-sm" alt="Toko AIREN Store"
                            loading="lazy">
                    </div>
                    <div class="col-md-6" data-aos="fade-left" data-aos-delay="300">
                        <p class="lead">
                            <strong>AIREN Store</strong> didirikan oleh Rendy Irawan sebagai wujud impian membangun toko
                            online yang terpercaya dan berkualitas.
                        </p>
                        <p>
                            Berawal dari keinginan sederhana untuk membantu orang mendapatkan produk-produk terbaik
                            tanpa ribet, AIREN Store lahir pada tahun 2020 dengan komitmen menghadirkan pengalaman
                            belanja yang mudah, aman, dan nyaman.
                        </p>
                        <p>
                            Sejak pertama kali berdiri, AIREN Store terus berkembang dengan menyediakan berbagai
                            kebutuhan mulai dari elektronik, fashion, produk digital, hingga perlengkapan rumah tangga.
                            Dengan kerja
                            keras dan dedikasi Rendy Irawan bersama tim, kami berkomitmen memberikan pelayanan pelanggan
                            yang cepat, ramah, dan memuaskan.
                        </p>
                    </div>

                </div>
            </div>
        </section>
    </main>

    <!-- Footer -->
    <footer class="footer" id="kontak">
        <div class="container">
            <div class="row gy-4">
                <div class="col-lg-5 col-md-12">
                    <h5>AIREN Store</h5>
                    <p>Menghadirkan produk berkualitas dengan harga terbaik. Semua kebutuhan Anda dalam satu tempat.</p>
                    <div class="social-icons mt-3">
                        <a href="https://facebook.com/RendyIrawan" aria-label="Facebook"><i
                                class="fab fa-facebook-f"></i></a>
                        <a href="https://instagram.com/rendyy_404" aria-label="Instagram"><i
                                class="fab fa-instagram"></i></a>
                    </div>
                </div>
                <div class="col-lg-3 col-md-6">
                    <h5>Navigasi</h5>
                    <ul class="list-unstyled">
                        <li><a href="#">Home</a></li>
                        <li><a href="#produk">Produk</a></li>
                        <li><a href="#tentang">Tentang</a></li>
                    </ul>
                </div>
                <div class="col-lg-4 col-md-6">
                    <h5>Hubungi Kami</h5>
                    <ul class="list-unstyled">
                        <li class="d-flex"><i class="fas fa-map-marker-alt footer-icon mt-1"></i>Jl. Raya Pahlawan No.
                            45, Bogor</li>
                        <li class="d-flex"><i class="fas fa-phone footer-icon mt-1"></i><a
                                href="tel:+6285885497377">+6285885497377</a></li>
                        <li class="d-flex"><i class="fab fa-whatsapp footer-icon mt-1"></i><a
                                href="https://wa.me/6285885497377?text=Halo%AIREN Store" target="_blank">Chat
                                WhatsApp</a></li>
                    </ul>
                </div>
            </div>
            <div class="copyright">
                &copy; <?= date('Y') ?> AIREN Store. Semua hak dilindungi.
            </div>
        </div>
    </footer>

    <!-- Quick View Modal -->
    <div class="modal fade" id="quickViewModal" tabindex="-1" aria-labelledby="quickViewModalLabel" aria-hidden="true">
        <div class="modal-dialog modal-lg modal-dialog-centered">
            <div class="modal-content">
                <div class="modal-header">
                    <h5 class="modal-title" id="quickViewModalLabel">Nama Produk</h5>
                    <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
                </div>
                <div class="modal-body">
                    <div class="row">
                        <div class="col-md-5 text-center">
                            <img src="assets/default.jpeg" alt="Nama Produk" id="quickViewImage"
                                class="img-fluid rounded mb-3">
                        </div>
                        <div class="col-md-7">
                            <p id="quickViewDescription" class="text-muted">Deskripsi produk...</p>
                            <h3 class="price my-3" id="quickViewPrice">Rp0</h3>
                            <p class="small text-muted" id="quickViewStock">Stok: 0</p>

                        </div>
                    </div>
                </div>
                <div class="modal-footer">
                    <button type="button" class="btn btn-outline-secondary" data-bs-dismiss="modal">Tutup</button>
                    <!-- <button type="button" class="btn btn-primary-custom add-to-cart-btn" id="quickViewAddToCartButton"
                        data-id="<?= $product['id'] ?>">
                        <i class="fas fa-shopping-cart"></i> Tambah ke Keranjang
                    </button> -->
                </div>
            </div>
        </div>
    </div>

    <!-- Wishlist Modal -->
    <div class="modal fade" id="wishlistModal" tabindex="-1" aria-labelledby="wishlistModalLabel" aria-hidden="true">
        <div class="modal-dialog modal-lg">
            <div class="modal-content">
                <div class="modal-header">
                    <h5 class="modal-title" id="wishlistModalLabel">Wishlist Saya</h5>
                    <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
                </div>
                <div class="modal-body" id="wishlistModalBody">
                    <p id="emptyWishlistMessage">Wishlist Anda masih kosong.</p>
                    <!-- Wishlist items will be injected here -->
                </div>
                <div class="modal-footer">
                    <button type="button" class="btn btn-outline-secondary" data-bs-dismiss="modal">Tutup</button>
                    <button type="button" class="btn btn-danger" id="clearWishlistBtn" style="display:none;">Kosongkan
                        Wishlist</button>
                </div>
            </div>
        </div>
    </div>

    <!-- Cart Modal -->
    <div class="modal fade " id="cartModal" tabindex="-1" aria-labelledby="cartModalLabel" aria-hidden="true">
        <div class="modal-dialog modal-lg">
            <div class="modal-content">
                <div class="modal-header">
                    <h5 class="modal-title" id="cartModalLabel">Keranjang Belanja Saya</h5>
                    <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
                </div>
                <div class="modal-body" id="cartModalBody">
                    <p id="emptyCartMessage">Keranjang Anda masih kosong.</p>
                    <!-- Cart items will be injected here by JavaScript -->
                </div>
                <div class="modal-footer">
                    <button type="button" class="btn btn-outline-secondary" data-bs-dismiss="modal">Lanjut
                        Belanja</button>
                    <button type="button" class="btn btn-primary-custom direct-checkout-btn" id="checkoutBtn"
                        style="display:none; ">Checkout</button>
                </div>
            </div>
        </div>
    </div>


    <!-- Scroll to Top Button -->
    <button onclick="scrollToTop()" id="scrollToTopBtn" title="Kembali ke Atas"><i class="fas fa-arrow-up"></i></button>

    <!-- Toast Container -->
    <div class="toast-container position-fixed bottom-0 end-0 p-3">
        <!-- Toasts will be appended here -->
    </div>


    <!-- TAMBAHKAN SCRIPT SNAP.JS DARI MIDTRANS DI SINI -->
    <script type="text/javascript" src="https://app.midtrans.com/snap/snap.js"
        data-client-key="<?= defined('MIDTRANS_CLIENT_KEY') ? MIDTRANS_CLIENT_KEY : '' ?>"></script>

    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/js/bootstrap.bundle.min.js"></script>
    <link href="https://unpkg.com/aos@2.3.1/dist/aos.css" rel="stylesheet">
    <script src="https://unpkg.com/aos@2.3.1/dist/aos.js"></script>
    <!-- SweetAlert2 CSS & JS -->
    <script src="https://cdn.jsdelivr.net/npm/sweetalert2@11"></script>

    <script type="module" src="js/main.js"></script>
    <script>
        document.getElementById('logout-link').addEventListener('click', function (event) {
            // Mencegah link langsung dieksekusi
            event.preventDefault();

            // Simpan URL dari href
            const logoutUrl = this.href;

            // Tampilkan SweetAlert
            Swal.fire({
                title: 'Anda yakin ingin logout?',
                text: "Anda akan keluar dari sesi ini.",
                icon: 'warning',
                showCancelButton: true,
                confirmButtonColor: '#d33',
                cancelButtonColor: '#3085d6',
                confirmButtonText: 'Ya, Logout!',
                cancelButtonText: 'Batal'
            }).then((result) => {
                // Jika pengguna menekan tombol "Ya, Logout!"
                if (result.isConfirmed) {
                    // Arahkan ke halaman logout
                    window.location.href = logoutUrl;
                }
            });
        });

        AOS.init({
            duration: 800, // values from 0 to 3000, with step 50ms
            once: true, // whether animation should happen only once - while scrolling down
        });
    </script>
</body>

</html>