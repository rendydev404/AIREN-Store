<?php

include 'config/db.php';

// Untuk demo, jika tidak ada koneksi, gunakan data dummy

$stmt = $pdo->query("SELECT * FROM products ORDER BY created_at DESC");
$products = $stmt->fetchAll(PDO::FETCH_ASSOC);

$new_product_limit = 3; // Jumlah produk yang dianggap "baru"
$product_counter = 0;
?>

<!DOCTYPE html>
<html lang="id">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Parfumku - Toko Parfum Online</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/css/bootstrap.min.css" rel="stylesheet">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0-beta3/css/all.min.css">
    <style>
        :root {
            --custom-primary-color: #c7aa7f;
            /* Warna primer baru Anda */
            --custom-primary-color-darker: #b3996f;
            /* Versi lebih gelap untuk hover, dll. */
            --custom-primary-text-on-bg: #333333;
            /* Warna teks untuk kontras di atas background primer */
            --custom-text-color-on-light-bg: #555;
            /* Warna teks umum pada background terang */
            --custom-text-color-headings: #333;
            /* Warna teks untuk judul */
        }

        body {
            font-family: 'Segoe UI', sans-serif;
            background-color: #f8f9fa;
            color: var(--custom-text-color-headings);
        }

        .navbar {
            background-color: #ffffff;
            box-shadow: 0 4px 6px rgba(0, 0, 0, 0.1);
            padding-top: 1rem;
            padding-bottom: 1rem;
        }

        .navbar-brand {
            font-weight: bold;
            color: var(--custom-primary-color) !important;
            /* Menggunakan warna primer baru */
        }

        .nav-link {
            color: var(--custom-text-color-on-light-bg) !important;
            margin-left: 10px;
            margin-right: 10px;
            transition: color 0.3s ease;
        }

        .nav-link:hover,
        .nav-link.active {
            color: var(--custom-primary-color) !important;
            /* Menggunakan warna primer baru */
        }

        .hero {
            background: linear-gradient(rgba(0, 0, 0, 0.5), rgba(0, 0, 0, 0.5)), url('https://d1csarkz8obe9u.cloudfront.net/posterpreviews/perfume-banner-design-template-e0681a33c8dae68510dfef525c8fe42d_screen.jpg?ts=1649115999') center/cover no-repeat;
            color: white;
            padding: 120px 20px;
            text-align: center;
            position: relative;
        }

        .hero h1 {
            font-size: 3.5rem;
            font-weight: 700;
            text-shadow: 2px 2px 4px rgba(0, 0, 0, 0.7);
        }

        .hero p {
            font-size: 1.25rem;
            margin-bottom: 0;
        }

        .section-title {
            font-weight: 700;
            color: var(--custom-text-color-headings);
            margin-bottom: 3rem;
            position: relative;
            padding-bottom: 10px;
        }

        .section-title::after {
            content: '';
            position: absolute;
            bottom: 0;
            left: 50%;
            transform: translateX(-50%);
            width: 80px;
            height: 4px;
            background-color: var(--custom-primary-color);
            /* Menggunakan warna primer baru */
            border-radius: 2px;
        }

        .card-product {
            transition: transform 0.3s ease-in-out, box-shadow 0.3s ease-in-out;
            border: none;
            border-radius: 10px;
            overflow: hidden;
            position: relative;
        }

        .card-product:hover {
            transform: translateY(-10px);
            box-shadow: 0 8px 20px rgba(0, 0, 0, 0.15);
        }

        .card-img-top {
            height: 200px;
            object-fit: cover;
        }

        @media (min-width: 576px) {
            .card-img-top {
                height: 220px;
            }
        }

        .card-body {
            display: flex;
            flex-direction: column;
            padding: 1.25rem;
        }

        .card-title {
            font-weight: 600;
            color: var(--custom-text-color-headings);
        }

        .card-text.description {
            font-size: 0.9rem;
            color: #666;
            flex-grow: 1;
            margin-bottom: 1rem;
        }

        .price {
            font-size: 1.3rem;
            font-weight: bold;
            color: var(--custom-primary-color);
            /* Menggunakan warna primer baru */
        }

        /* Tombol yang sebelumnya .btn-success kini diubah menjadi .btn-primary-custom atau sejenisnya jika ingin dipisahkan,
       atau kita bisa override .btn-success jika memang itu intensi penggunaannya sebagai tombol aksi utama.
       Untuk saat ini, saya akan tetap menggunakan .btn-success dan mengubah warnanya. */
        .btn-success {
            /* Anda bisa menamai ulang kelas ini jika .btn-success masih ingin digunakan untuk warna hijau standar Bootstrap */
            background-color: var(--custom-primary-color);
            /* Menggunakan warna primer baru */
            border-color: var(--custom-primary-color);
            /* Menggunakan warna primer baru */
            color: var(--custom-primary-text-on-bg);
            /* Warna teks baru untuk kontras */
            transition: background-color 0.3s ease, border-color 0.3s ease, color 0.3s ease;
        }

        .btn-success:hover {
            background-color: var(--custom-primary-color-darker);
            /* Versi lebih gelap */
            border-color: var(--custom-primary-color-darker);
            /* Versi lebih gelap */
            color: var(--custom-primary-text-on-bg);
        }

        .footer {
            background: #343a40;
            color: #f8f9fa;
            padding: 50px 0;
            margin-top: 3rem;
        }

        .footer p {
            margin-bottom: 0.5rem;
        }

        .footer a {
            color: var(--custom-primary-color);
            /* Menggunakan warna primer baru */
            text-decoration: none;
        }

        .footer a:hover {
            color: var(--custom-primary-color-darker);
            /* Versi lebih gelap untuk hover */
        }

        .search-form .form-control {
            border-radius: 20px 0 0 20px;
        }

        /* Tombol search juga akan menggunakan tema primer baru */
        .search-form .btn {
            border-radius: 0 20px 20px 0;
            background-color: var(--custom-primary-color);
            /* Menggunakan warna primer baru */
            border-color: var(--custom-primary-color);
            /* Menggunakan warna primer baru */
            color: var(--custom-primary-text-on-bg);
            /* Warna teks baru untuk kontras */
        }

        .search-form .btn:hover {
            background-color: var(--custom-primary-color-darker);
            /* Versi lebih gelap */
            border-color: var(--custom-primary-color-darker);
            /* Versi lebih gelap */
            color: var(--custom-primary-text-on-bg);
        }

        .badge-new {
            position: absolute;
            top: 10px;
            left: 10px;
            background-color: #ffc107 !important;
            color: #000;
            font-size: 0.8em;
            padding: 0.3em 0.6em;
            border-radius: 0.25rem;
            z-index: 10;
        }

        #scrollToTopBtn {
            display: none;
            position: fixed;
            bottom: 20px;
            right: 30px;
            z-index: 99;
            border: none;
            outline: none;
            background-color: var(--custom-primary-color);
            /* Menggunakan warna primer baru */
            color: var(--custom-primary-text-on-bg);
            /* Warna teks baru untuk kontras */
            cursor: pointer;
            padding: 12px 15px;
            border-radius: 50%;
            font-size: 18px;
            box-shadow: 0 2px 5px rgba(0, 0, 0, 0.2);
        }

        #scrollToTopBtn:hover {
            background-color: var(--custom-primary-color-darker);
            /* Versi lebih gelap */
            color: var(--custom-primary-text-on-bg);
        }

        .about-section,
        .contact-section {
            padding: 60px 0;
            background-color: #fff;
        }

        .contact-section i {
            color: var(--custom-primary-color);
            /* Menggunakan warna primer baru */
            margin-right: 10px;
        }
         .footer-contact-item i.footer-icon {
        color: var(--custom-primary-color); /* Menggunakan warna primer kustom Anda */
        margin-right: 8px; /* Memberi sedikit jarak antara ikon dan teks */
    }

    .footer-contact-item a, 
    .footer-contact-item { /* Untuk item list yang tidak memiliki link seperti alamat */
        color: #ccc; /* Warna yang sedikit lebih lembut dari putih murni untuk teks kontak */
        font-size: 0.95rem;
    }
    .footer-contact-item a:hover {
        color: var(--custom-primary-color-lighter, #fff); /* Warna hover untuk link kontak */
    }

    /* Styling untuk tombol sosial media di footer */
    .footer .btn-outline-light {
        border-color: rgba(255, 255, 255, 0.5);
        color: rgba(255, 255, 255, 0.7);
        border-radius: 50%; /* Membuat tombol ikon menjadi bulat */
        width: 40px;
        height: 40px;
        padding: 0;
        display: inline-flex;
        align-items: center;
        justify-content: center;
    }
    .footer .btn-outline-light:hover {
        border-color: #fff;
        background-color: rgba(255, 255, 255, 0.1);
        color: #fff;
    }

    </style>
</head>

<body>

    <nav class="navbar navbar-expand-lg navbar-light sticky-top">
        <div class="container">
            <a class="navbar-brand" href="#">Parfumku</a>
            <button class="navbar-toggler" type="button" data-bs-toggle="collapse" data-bs-target="#navbarNav"
                aria-controls="navbarNav" aria-expanded="false" aria-label="Toggle navigation">
                <span class="navbar-toggler-icon"></span>
            </button>
            <div class="collapse navbar-collapse" id="navbarNav">
                <ul class="navbar-nav ms-auto mb-2 mb-lg-0">
                    <li class="nav-item"><a class="nav-link active" href="#">Home</a></li>
                    <li class="nav-item"><a class="nav-link" href="#produk">Produk</a></li>
                    <li class="nav-item"><a class="nav-link" href="#tentang">Tentang Kami</a></li>
                    <li class="nav-item"><a class="nav-link" href="#kontak">Kontak</a></li>
                </ul>
                <form class="d-flex ms-lg-3 search-form" role="search">
                    <input class="form-control me-0" type="search" id="searchInput" placeholder="Cari parfum..."
                        aria-label="Search">
                    <button class="btn btn-outline-light" type="button" onclick="searchProduct()"><i
                            class="fas fa-search"></i></button>
                </form>
            </div>
        </div>
    </nav>

    <main>
        <header class="hero">
            <div class="container">
                <h1>Temukan Aroma yang Mewakili Dirimu</h1>
                <p>Nikmati pilihan parfum terbaik dengan kualitas premium dan harga terjangkau</p>
            </div>
        </header>

        <section class="container py-5" id="produk">
            <h2 class="text-center section-title">Produk Kami</h2>
            <div class="row g-4" id="productList">
                <?php if (empty($products)): ?>
                    <div class="col">
                        <p class="text-center">Belum ada produk yang tersedia saat ini.</p>
                    </div>
                <?php else: ?>
                    <?php foreach ($products as $product):
                        $product_counter++; ?>
                        <div class="col-6 col-sm-6 col-md-4 col-lg-3 product-item">
                            <div class="card card-product h-100">
                                <?php if ($product_counter <= $new_product_limit): ?>
                                    <span class="badge-new">Baru!</span>
                                <?php endif; ?>
                                <img src="uploads/<?= htmlspecialchars($product['image']) ?>" class="card-img-top"
                                    alt="<?= htmlspecialchars($product['name']) ?>" loading="lazy">
                                <div class="card-body">
                                    <h5 class="card-title product-name"><?= htmlspecialchars($product['name']) ?></h5>
                                    <p class="card-text description text-muted small">
                                        <?= htmlspecialchars($product['description']) ?></p>
                                    <div class="mb-2 price">Rp<span class="price-value"
                                            data-price="<?= $product['price'] ?>"><?= number_format($product['price'], 0, ',', '.') ?></span>
                                    </div>
                                    <div class="input-group mb-3">
                                        <span class="input-group-text">Qty</span>
                                        <input type="number" class="form-control quantity-input" min="1" value="1"
                                            data-product-id="<?= $product['id'] ?>">
                                    </div>
                                    <a href="#" class="btn btn-success mt-auto wa-button"
                                        data-name="<?= htmlspecialchars($product['name']) ?>"
                                        data-price="<?= $product['price'] ?>" data-id="<?= $product['id'] ?>">
                                        <i class="fab fa-whatsapp"></i> Beli via WhatsApp
                                    </a>
                                </div>
                            </div>
                        </div>
                    <?php endforeach; ?>
                <?php endif; ?>
            </div>
        </section>

        <section class="about-section" id="tentang">
            <div class="container">
                <h2 class="text-center section-title">Tentang Parfumku</h2>
                <div class="row align-items-center">
                    <div class="col-md-6">
                        <img src="https://images.unsplash.com/photo-1594035910387-fea47794261f?ixlib=rb-4.0.3&ixid=M3wxMjA3fDB8MHxzZWFyY2h8MTB8fHBlcmZ1bWUlMjBzaG9wfGVufDB8fDB8fHww&auto=format&fit=crop&w=500&q=60"
                            class="img-fluid rounded shadow" alt="Tentang Parfumku Store" loading="lazy">
                    </div>
                    <div class="col-md-6">
                        <p class="lead mt-4 mt-md-0">Selamat datang di <strong>Parfumku</strong>, destinasi utama Anda
                            untuk menemukan aroma parfum eksklusif dan berkualitas tinggi. Kami percaya bahwa parfum
                            bukan hanya sekadar wewangian, tetapi juga cerminan kepribadian dan gaya hidup.</p>
                        <p>Di Parfumku, kami berkomitmen untuk menyediakan koleksi parfum terbaik dari berbagai merek
                            ternama maupun parfum racikan khusus yang unik. Setiap produk dipilih dengan cermat untuk
                            memastikan kualitas dan keasliannya.</p>
                        <p>Tim kami selalu siap membantu Anda menemukan aroma yang paling sesuai dengan karakter dan
                            preferensi Anda. Jelajahi koleksi kami dan temukan parfum impian Anda hari ini!</p>
                    </div>
                </div>
            </div>
        </section>

    </main>

    <footer class="footer text-center">
        <div class="container" id="kontak">

            <div class="mt-4 mb-4">
                <h5 style="color: #f8f9fa; font-weight: 600; margin-bottom: 1rem;">Hubungi & Ikuti Kami</h5>
                <p style="color: #ccc; font-size: 0.9rem;">Punya pertanyaan atau ingin memesan? Jangan ragu untuk
                    menghubungi kami.</p>
                <ul class="list-unstyled mb-3">
                    <li class="mb-2 footer-contact-item">
                        <i class="fas fa-phone footer-icon"></i> <a href="tel:+6285885497377">+62 858-8549-7377</a>
                    </li>
                    <li class="mb-2 footer-contact-item">
                        <i class="fab fa-whatsapp footer-icon"></i> <a
                            href="https://wa.me/6285885497377?text=Halo%20Parfumku,%20saya%20ingin%20bertanya%20tentang%20produk."
                            target="_blank">Chat via WhatsApp</a>
                    </li>
                    <li class="mb-2 footer-contact-item">
                        <i class="fas fa-envelope footer-icon"></i> <a
                            href="mailto:info@parfumku.com">info@parfumku.com</a>
                    </li>
                    <li class="mb-2 footer-contact-item">
                        <i class="fas fa-map-marker-alt footer-icon"></i> Jl. Aroma Wangi No. 123, Jakarta, Indonesia
                    </li>
                </ul>

                <div>
                    <a href="#" class="btn btn-outline-light btn-floating m-1" role="button" aria-label="Facebook">
                        <i class="fab fa-facebook-f"></i>
                    </a>
                    <a href="#" class="btn btn-outline-light btn-floating m-1" role="button" aria-label="Instagram">
                        <i class="fab fa-instagram"></i>
                    </a>
                    <a href="#" class="btn btn-outline-light btn-floating m-1" role="button" aria-label="Twitter">
                        <i class="fab fa-twitter"></i>
                    </a>
                </div>
            </div>
            <hr style="border-color: rgba(255,255,255,0.1);">

            <p>&copy; <?= date('Y') ?> Parfumku. Dibuat dengan <i class="fas fa-heart text-danger"></i> untuk pecinta
                parfum.</p>

        </div>
    </footer>
    <button onclick="scrollToTop()" id="scrollToTopBtn" title="Go to top"><i class="fas fa-arrow-up"></i></button>

    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/js/bootstrap.bundle.min.js"></script>
    <script>
        document.addEventListener('DOMContentLoaded', function () {
            // Smooth scroll for nav links
            document.querySelectorAll('a.nav-link[href^="#"]').forEach(anchor => {
                anchor.addEventListener('click', function (e) {
                    e.preventDefault();
                    const targetId = this.getAttribute('href');
                    const targetElement = document.querySelector(targetId);
                    if (targetElement) {
                        targetElement.scrollIntoView({
                            behavior: 'smooth'
                        });
                    }
                });
            });

            // Quantity input changes price
            document.querySelectorAll('.quantity-input').forEach(input => {
                input.addEventListener('input', function () {
                    const pricePerUnit = parseInt(this.closest('.card-body').querySelector('.price-value').dataset.price);
                    const qty = parseInt(this.value) || 1;
                    if (qty < 1) {
                        this.value = 1; // Reset to 1 if less than 1
                        return;
                    }
                    const priceElement = this.closest('.card-body').querySelector('.price-value');
                    priceElement.textContent = (pricePerUnit * qty).toLocaleString('id-ID');
                });
            });

            // WhatsApp button
            document.querySelectorAll('.wa-button').forEach(button => {
                button.addEventListener('click', function (e) {
                    e.preventDefault();
                    const cardBody = this.closest('.card-body');
                    const qtyInput = cardBody.querySelector('.quantity-input');
                    const qty = parseInt(qtyInput.value) || 1;

                    const name = this.dataset.name;
                    const pricePerUnit = parseInt(this.dataset.price);
                    const total = pricePerUnit * qty;

                    const message = `Halo Parfumku, saya ingin memesan produk:\n\n*${name}*\nJumlah: *${qty} pcs*\nTotal Harga: *Rp${total.toLocaleString('id-ID')}*\n\nMohon info lanjut untuk pemesanan. Terima kasih.`;
                    const phoneNumber = '6285885497377'; // Ganti dengan nomor WhatsApp Anda
                    const url = `https://wa.me/${phoneNumber}?text=${encodeURIComponent(message)}`;
                    window.open(url, '_blank');
                });
            });

            // Scroll to Top button visibility
            const scrollToTopButton = document.getElementById('scrollToTopBtn');
            window.onscroll = function () {
                if (document.body.scrollTop > 100 || document.documentElement.scrollTop > 100) {
                    scrollToTopButton.style.display = "block";
                } else {
                    scrollToTopButton.style.display = "none";
                }
            };
        });

        // Function to scroll to top
        function scrollToTop() {
            window.scrollTo({ top: 0, behavior: 'smooth' });
        }

        // Search Product Function
        function searchProduct() {
            const searchInput = document.getElementById('searchInput').value.toLowerCase();
            const productItems = document.querySelectorAll('#productList .product-item');

            productItems.forEach(item => {
                const productName = item.querySelector('.product-name').textContent.toLowerCase();
                const productDesc = item.querySelector('.description').textContent.toLowerCase();
                if (productName.includes(searchInput) || productDesc.includes(searchInput)) {
                    item.style.display = ''; // Show item
                } else {
                    item.style.display = 'none'; // Hide item
                }
            });
        }
        // Listener untuk search input (agar bisa search saat mengetik / real-time)
        document.getElementById('searchInput').addEventListener('keyup', searchProduct);

    </script>
</body>

</html>