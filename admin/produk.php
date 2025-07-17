<?php
session_start();
require_once '../config/db.php';

// Cek dan ambil pesan dari session jika ada (hasil dari redirect)
if (isset($_SESSION['message'])) {
    $message = $_SESSION['message'];
    $message_type = $_SESSION['message_type'];
    // Hapus session agar tidak muncul lagi saat di-refresh
    unset($_SESSION['message']);
    unset($_SESSION['message_type']);
} else {
    $message = '';
    $message_type = 'success';
}

// Cek apakah admin sudah login
if (!isset($_SESSION['role']) || $_SESSION['role'] !== 'admin') {
    header('Location: ../login.php'); // Path ke login.php
    exit;
}

// --- LOGIKA PHP UNTUK PROSES DATA (ADD, EDIT, DELETE) ---

// Proses tambah produk
if ($_SERVER["REQUEST_METHOD"] == "POST" && isset($_POST['action']) && $_POST['action'] == 'add') {
    // ... (Logika PHP untuk tambah produk Anda tetap sama, tidak perlu diubah) ...
    $name = trim($_POST['name']);
    $description = trim($_POST['description']);
    $price = filter_input(INPUT_POST, 'price', FILTER_VALIDATE_INT);
    $stock = filter_input(INPUT_POST, 'stock', FILTER_VALIDATE_INT);

    if (empty($name) || $price === false || $price < 0 || $stock === false || $stock < 0) {
        $message = "Input tidak valid. Nama, harga, dan stok harus diisi dengan benar.";
        $message_type = "danger";
    } else {
        $imageName = '';
        if (isset($_FILES['image']) && $_FILES['image']['error'] == UPLOAD_ERR_OK) {
            $target_dir = '../uploads/';
            if (!is_dir($target_dir)) {
                mkdir($target_dir, 0777, true);
            }
            $imageFileType = strtolower(pathinfo($_FILES['image']['name'], PATHINFO_EXTENSION));
            $safeFileName = preg_replace("/[^a-zA-Z0-9_.]/", "", basename($_FILES['image']['name']));
            $imageName = time() . '_' . uniqid() . '_' . $safeFileName;
            $allowed_types = ['jpg', 'jpeg', 'png', 'gif'];

            if (in_array($imageFileType, $allowed_types) && $_FILES['image']['size'] <= 5000000) { // Max 5MB
                if (!move_uploaded_file($_FILES['image']['tmp_name'], $target_dir . $imageName)) {
                    $message = "Gagal mengupload gambar.";
                    $message_type = "danger";
                    $imageName = '';
                }
            } else {
                $message = "Tipe file tidak diizinkan atau ukuran file terlalu besar (maks 5MB).";
                $message_type = "danger";
                $imageName = '';
            }
        } elseif (isset($_FILES['image']) && $_FILES['image']['error'] != UPLOAD_ERR_NO_FILE) {
            $message = "Terjadi error saat upload gambar: " . $_FILES['image']['error'];
            $message_type = "danger";
        }

        if (empty($message) || $message_type == 'success') {
            $sql_insert = "INSERT INTO `products` (`name`, `description`, `price`, `stock`, `image`) VALUES (?, ?, ?, ?, ?)";
            $stmt = $pdo->prepare($sql_insert);
            if ($stmt->execute([$name, $description, $price, $stock, $imageName])) {
                // Simpan pesan ke session untuk ditampilkan setelah redirect
                $_SESSION['message'] = "Produk berhasil ditambahkan!";
                $_SESSION['message_type'] = "success";

                // Lakukan redirect ke halaman itu sendiri
                header('Location: ' . basename($_SERVER['PHP_SELF']));
                exit; // Selalu panggil exit() setelah header redirect
            } else {
                $message = "Gagal menambahkan produk ke database.";
                $message_type = "danger";
                if (!empty($imageName) && file_exists($target_dir . $imageName)) {
                    unlink($target_dir . $imageName);
                }
            }
        }
    }
}

// Proses edit produk
if ($_SERVER["REQUEST_METHOD"] == "POST" && isset($_POST['action']) && $_POST['action'] == 'edit') {
    // ... (Logika PHP untuk edit produk Anda tetap sama, tidak perlu diubah) ...
    $id = filter_input(INPUT_POST, 'id', FILTER_VALIDATE_INT);
    $name = trim($_POST['name']);
    $description = trim($_POST['description']);
    $price = filter_input(INPUT_POST, 'price', FILTER_VALIDATE_INT);
    $stock = filter_input(INPUT_POST, 'stock', FILTER_VALIDATE_INT);

    if (!$id || empty($name) || $price === false || $price < 0 || $stock === false || $stock < 0) {
        $message = "Input tidak valid untuk edit. ID, Nama, harga, dan stok harus diisi dengan benar.";
        $message_type = "danger";
    } else {
        $stmt = $pdo->prepare("SELECT `image` FROM `products` WHERE `id` = ?");
        $stmt->execute([$id]);
        $oldProduct = $stmt->fetch(PDO::FETCH_ASSOC);

        if (!$oldProduct) {
            $message = "Produk tidak ditemukan.";
            $message_type = "danger";
        } else {
            $imageName = $oldProduct['image'];
            $target_dir = '../uploads/';

            if (isset($_FILES['image']) && $_FILES['image']['error'] == UPLOAD_ERR_OK) {
                $imageFileType = strtolower(pathinfo($_FILES['image']['name'], PATHINFO_EXTENSION));
                $safeFileName = preg_replace("/[^a-zA-Z0-9_.]/", "", basename($_FILES['image']['name']));
                $newImageName = time() . '_' . uniqid() . '_' . $safeFileName;
                $allowed_types = ['jpg', 'jpeg', 'png', 'gif'];

                if (in_array($imageFileType, $allowed_types) && $_FILES['image']['size'] <= 5000000) {
                    if (move_uploaded_file($_FILES['image']['tmp_name'], $target_dir . $newImageName)) {
                        if ($oldProduct['image'] && file_exists($target_dir . $oldProduct['image'])) {
                            unlink($target_dir . $oldProduct['image']);
                        }
                        $imageName = $newImageName;
                    } else {
                        $message = "Gagal mengupload gambar baru.";
                        $message_type = "danger";
                    }
                } else {
                    $message = "Tipe file baru tidak diizinkan atau ukuran file terlalu besar (maks 5MB).";
                    $message_type = "danger";
                }
            } elseif (isset($_FILES['image']) && $_FILES['image']['error'] != UPLOAD_ERR_NO_FILE) {
                $message = "Terjadi error saat upload gambar baru: " . $_FILES['image']['error'];
                $message_type = "danger";
            }

            if (empty($message) || $message_type == 'success') {
                $sql_update = "UPDATE `products` SET `name`=?, `description`=?, `price`=?, `stock`=?, `image`=? WHERE `id`=?";
                $stmt = $pdo->prepare($sql_update);

                if ($stmt->execute([$name, $description, $price, $stock, $imageName, $id])) {
                    // Simpan pesan ke session untuk ditampilkan setelah redirect
                    $_SESSION['message'] = "Produk berhasil diperbarui!";
                    $_SESSION['message_type'] = "success";

                    // Lakukan redirect
                    header('Location: ' . basename($_SERVER['PHP_SELF']));
                    exit;
                } else {
                    $message = "Gagal memperbarui produk di database.";
                    $message_type = "danger";
                }
            }
        }
    }
}

// Proses hapus produk
if ($_SERVER["REQUEST_METHOD"] == "POST" && isset($_POST['action']) && $_POST['action'] == 'delete') {
    // ... (Logika PHP untuk hapus produk Anda tetap sama, tidak perlu diubah) ...
    $id = filter_input(INPUT_POST, 'id', FILTER_VALIDATE_INT);

    if (!$id) {
        $message = "ID produk tidak valid untuk dihapus.";
        $message_type = "danger";
    } else {
        $stmt = $pdo->prepare("SELECT `image` FROM `products` WHERE `id` = ?");
        $stmt->execute([$id]);
        $product = $stmt->fetch(PDO::FETCH_ASSOC);

        if ($product) {
            $target_dir = '../uploads/';

            $stmt_delete = $pdo->prepare("DELETE FROM `products` WHERE `id` = ?");
            if ($stmt_delete->execute([$id])) {
                if ($product['image'] && file_exists($target_dir . $product['image'])) {
                    unlink($target_dir . $product['image']);
                }
                // Simpan pesan ke session untuk ditampilkan setelah redirect
                $_SESSION['message'] = "Produk berhasil dihapus!";
                $_SESSION['message_type'] = "success";

                // Lakukan redirect
                header('Location: ' . basename($_SERVER['PHP_SELF']));
                exit;
            } else {
                // Jika gagal, tidak perlu redirect. Cukup tampilkan pesan error.
                $message = "Gagal menghapus produk dari database.";
                $message_type = "danger";
            }
        } else {
            $message = "Produk yang akan dihapus tidak ditemukan.";
            $message_type = "danger";
        }
    }
}

// --- AKHIR LOGIKA PHP ---

// Ambil data produk (setelah semua proses POST selesai)
$stmt_get_products = $pdo->query("SELECT `id`, `name`, `description`, `price`, `stock`, `image`, DATE_FORMAT(`created_at`, '%d %b %Y %H:%i') as `created_at_formatted` FROM `products` ORDER BY `id` DESC");
$products = $stmt_get_products->fetchAll(PDO::FETCH_ASSOC);

$page_title = "Manajemen Produk";
$current_page_name = basename($_SERVER['PHP_SELF']); // Untuk sidebar
?>
<!DOCTYPE html>
<html lang="id" data-theme="light">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title><?= htmlspecialchars($page_title) ?> | AIREN Store</title>

    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/css/bootstrap.min.css" rel="stylesheet">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.5.1/css/all.min.css">
    <link href="https://fonts.googleapis.com/css2?family=Poppins:wght@300;400;500;600;700&display=swap"
        rel="stylesheet">
    <link rel="stylesheet" href="https://cdn.datatables.net/1.13.8/css/dataTables.bootstrap5.min.css">

    <link rel="stylesheet" href="css/tes.css">
</head>

<body>

    <div class="admin-layout-wrapper">

        <?php include 'partials/sidebar.php'; ?>

        <div class="main-content-wrapper">

            <?php include 'partials/header.php'; ?>

            <main class="admin-page-content">
                <div class="container-fluid">

                    <div class="page-header mb-4 d-flex justify-content-between align-items-center flex-wrap">
                        <h1 class="page-title mb-2 mb-md-0"><?= htmlspecialchars($page_title) ?></h1>
                        <button class="btn btn-admin-primary" data-bs-toggle="modal" data-bs-target="#modalTambah">
                            <i class="fas fa-plus me-1"></i> Tambah Produk
                        </button>
                    </div>

                    <?php if ($message): ?>
                        <div class="alert alert-<?= htmlspecialchars($message_type) ?> alert-dismissible fade show"
                            role="alert">
                            <?= htmlspecialchars($message) ?>
                            <button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Close"></button>
                        </div>
                    <?php endif; ?>

                    <div class="card table-card">
                        <div class="card-body">
                            <div class="table-responsive">
                                <table id="produkTable" class="table table-hover align-middle" style="width:100%">
                                    <thead>
                                        <tr>
                                            <th>NO</th>
                                            <th>Gambar</th>
                                            <th>Nama Produk</th>
                                            <th>Harga</th>
                                            <th>Stok</th>
                                            <th>Dibuat Pada</th>
                                            <th class="text-center">Aksi</th>
                                        </tr>
                                    </thead>
                                    <tbody>
                                        <?php $no = 1;
                                        foreach ($products as $product): ?>
                                            <tr>
                                                <td class="text-center"><?= $no++ ?></td>
                                                <td>
                                                    <?php if (!empty($product['image'])): ?>
                                                        <img src="../uploads/<?= htmlspecialchars($product['image']) ?>"
                                                            alt="<?= htmlspecialchars($product['name']) ?>"
                                                            class="table-product-img">
                                                    <?php else: ?>
                                                        <img src="https://via.placeholder.com/60x60.png?text=N/A" alt="No image"
                                                            class="table-product-img placeholder-img">
                                                    <?php endif; ?>
                                                </td>
                                                <td><?= htmlspecialchars($product['name']) ?></td>
                                                <td>Rp<?= number_format($product['price'], 0, ',', '.') ?></td>
                                                <td class="text-center"><?= htmlspecialchars($product['stock']) ?></td>
                                                <td><?= htmlspecialchars($product['created_at_formatted']) ?></td>
                                                <td class="text-center">
                                                    <button class="btn btn-sm btn-action btn-edit m-3" title="Edit Produk"
                                                        data-bs-toggle="modal" data-bs-target="#modalEdit"
                                                        data-id="<?= $product['id'] ?>"
                                                        data-name="<?= htmlspecialchars($product['name'], ENT_QUOTES) ?>"
                                                        data-description="<?= htmlspecialchars($product['description'], ENT_QUOTES) ?>"
                                                        data-price="<?= $product['price'] ?>"
                                                        data-stock="<?= $product['stock'] ?>"
                                                        data-image="<?= htmlspecialchars($product['image'], ENT_QUOTES) ?>">
                                                        <i class="fas fa-edit"></i>
                                                    </button>
                                                    <button class="btn btn-sm btn-action btn-delete" title="Hapus Produk"
                                                        data-bs-toggle="modal" data-bs-target="#modalHapus"
                                                        data-id="<?= $product['id'] ?>"
                                                        data-name="<?= htmlspecialchars($product['name'], ENT_QUOTES) ?>">
                                                        <i class="fas fa-trash"></i>
                                                    </button>
                                                </td>
                                            </tr>
                                        <?php endforeach; ?>
                                        <?php if (empty($products)): ?>
                                            <tr>
                                                <td colspan="7" class="text-center py-4">Belum ada produk. Silakan tambahkan
                                                    produk baru.</td>
                                            </tr>
                                        <?php endif; ?>
                                    </tbody>
                                </table>
                            </div>
                        </div>
                    </div>

                </div>
            </main>
        </div>
    </div>

    <div class="sidebar-overlay" id="sidebarOverlay"></div>

    <div class="modal fade" id="modalTambah" tabindex="-1" aria-labelledby="modalTambahLabel" aria-hidden="true">
        <div class="modal-dialog modal-lg modal-dialog-centered">
            <form class="modal-content" method="post" enctype="multipart/form-data" action="produk.php">
                <input type="hidden" name="action" value="add">
                <div class="modal-header">
                    <h5 class="modal-title" id="modalTambahLabel"><i class="fas fa-plus-circle me-2"></i>Tambah Produk
                        Baru</h5>
                    <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
                </div>
                <div class="modal-body">
                    <div class="mb-3"><label for="add-name" class="form-label">Nama Produk</label><input type="text"
                            name="name" id="add-name" class="form-control" required></div>
                    <div class="mb-3"><label for="add-description" class="form-label">Deskripsi</label><textarea
                            name="description" id="add-description" class="form-control" rows="3"></textarea></div>
                    <div class="row">
                        <div class="col-md-6 mb-3"><label for="add-price" class="form-label">Harga (Rp)</label><input
                                type="number" name="price" id="add-price" class="form-control" required min="0"></div>
                        <div class="col-md-6 mb-3"><label for="add-stock" class="form-label">Stok</label><input
                                type="number" name="stock" id="add-stock" class="form-control" required min="0"></div>
                    </div>
                    <div class="mb-3"><label for="add-image" class="form-label">Gambar Produk</label><input type="file"
                            name="image" id="add-image" class="form-control file-input"
                            accept="image/png, image/jpeg, image/gif"><small
                            class="form-text text-muted file-input-label">Ukuran maks 5MB. Tipe: JPG, PNG, GIF.</small>
                    </div>
                </div>
                <div class="modal-footer">
                    <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Batal</button>
                    <button type="submit" class="btn btn-admin-primary"><i class="fas fa-save me-1"></i> Simpan</button>
                </div>
            </form>
        </div>
    </div>

    <div class="modal fade" id="modalEdit" tabindex="-1" aria-labelledby="modalEditLabel" aria-hidden="true">
        <div class="modal-dialog modal-lg modal-dialog-centered">
            <form class="modal-content" method="post" enctype="multipart/form-data" action="produk.php">
                <input type="hidden" name="action" value="edit">
                <input type="hidden" name="id" id="edit-id">
                <div class="modal-header">
                    <h5 class="modal-title" id="modalEditLabel"><i class="fas fa-edit me-2"></i>Edit Produk</h5>
                    <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
                </div>
                <div class="modal-body">
                    <div class="mb-3"><label for="edit-name" class="form-label">Nama Produk</label><input type="text"
                            name="name" id="edit-name" class="form-control" required></div>
                    <div class="mb-3"><label for="edit-description" class="form-label">Deskripsi</label><textarea
                            name="description" id="edit-description" class="form-control" rows="3"></textarea></div>
                    <div class="row">
                        <div class="col-md-6 mb-3"><label for="edit-price" class="form-label">Harga (Rp)</label><input
                                type="number" name="price" id="edit-price" class="form-control" required min="0"></div>
                        <div class="col-md-6 mb-3"><label for="edit-stock" class="form-label">Stok</label><input
                                type="number" name="stock" id="edit-stock" class="form-control" required min="0"></div>
                    </div>
                    <div class="mb-3">
                        <label for="edit-image-input" class="form-label">Gambar Produk <small
                                class="text-muted">(kosongkan jika tidak ganti)</small></label>
                        <input type="file" name="image" id="edit-image-input" class="form-control file-input"
                            accept="image/png, image/jpeg, image/gif">
                        <small class="form-text text-muted file-input-label">Ukuran maks 5MB. Tipe: JPG, PNG,
                            GIF.</small>
                        <div class="mt-2 d-flex align-items-center">
                            <img id="edit-image-preview" src="https://via.placeholder.com/80x80.png?text=N/A"
                                alt="Preview" class="me-2" style="max-width: 80px; max-height: 80px; display:none;">
                            <small id="edit-current-image-text" class="form-text text-muted"></small>
                        </div>
                    </div>
                </div>
                <div class="modal-footer">
                    <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Batal</button>
                    <button type="submit" class="btn btn-admin-primary"><i class="fas fa-save me-1"></i> Simpan
                        Perubahan</button>
                </div>
            </form>
        </div>
    </div>

    <div class="modal fade" id="modalHapus" tabindex="-1" aria-labelledby="modalHapusLabel" aria-hidden="true">
        <div class="modal-dialog modal-dialog-centered">
            <form class="modal-content" method="post" action="produk.php">
                <input type="hidden" name="action" value="delete">
                <input type="hidden" name="id" id="delete-id">
                <div class="modal-header modal-header-danger">
                    <h5 class="modal-title" id="modalHapusLabel"><i
                            class="fas fa-exclamation-triangle me-2"></i>Konfirmasi Hapus</h5>
                    <button type="button" class="btn-close btn-close-white" data-bs-dismiss="modal"
                        aria-label="Close"></button>
                </div>
                <div class="modal-body">
                    <p>Apakah Anda yakin ingin menghapus produk <strong id="delete-name" class="text-danger"></strong>
                        secara permanen? Tindakan ini tidak dapat diurungkan.</p>
                </div>
                <div class="modal-footer">
                    <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Batal</button>
                    <button type="submit" class="btn btn-danger"><i class="fas fa-trash-alt me-1"></i> Ya,
                        Hapus</button>
                </div>
            </form>
        </div>
    </div>

    <script src="https://code.jquery.com/jquery-3.7.1.min.js"></script>
    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/js/bootstrap.bundle.min.js"></script>
    <script src="https://cdn.datatables.net/1.13.8/js/jquery.dataTables.min.js"></script>
    <script src="https://cdn.datatables.net/1.13.8/js/dataTables.bootstrap5.min.js"></script>

    <script src="js/main.js"></script>

    <script>
        $(document).ready(function () {
            // Initialize DataTables
            var produkTable = $('#produkTable').DataTable({
                "language": {
                    "url": "//cdn.datatables.net/plug-ins/1.13.8/i18n/id.json",
                    "searchPlaceholder": "Cari produk..."
                },
                "responsive": true,
                "autoWidth": false,
                "order": [], // Kosongkan agar urutan default dari PHP (DESC) digunakan
                "columnDefs": [
                    { "targets": [1, 6], "orderable": false, "searchable": false }, // Gambar & Aksi
                    { "width": "5%", "targets": 0 },   // NO
                    { "width": "10%", "targets": 1 },  // Gambar
                    { "width": "10%", "targets": 3 },  // Harga
                    { "width": "5%", "targets": 4 },   // Stok
                    { "width": "15%", "targets": 5 }, // Dibuat
                    { "width": "10%", "targets": 6 }  // Aksi
                ],
            });

            // Event delegation untuk tombol Edit
            $('#produkTable tbody').on('click', '.btn-edit', function () {
                const button = $(this);
                $('#edit-id').val(button.data('id'));
                $('#edit-name').val(button.data('name'));
                $('#edit-description').val(button.data('description'));
                $('#edit-price').val(button.data('price'));
                $('#edit-stock').val(button.data('stock'));

                const image = button.data('image');
                const imagePreview = $('#edit-image-preview');
                const currentImageText = $('#edit-current-image-text');
                const uploadsPath = '../uploads/';

                if (image && image !== 'null' && image !== '') {
                    imagePreview.attr('src', uploadsPath + image).show();
                    currentImageText.text('Gambar saat ini: ' + image);
                } else {
                    imagePreview.hide();
                    currentImageText.text('Tidak ada gambar saat ini.');
                }
                $('#edit-image-input').val('');
            });

            // Event delegation untuk tombol Hapus
            $('#produkTable tbody').on('click', '.btn-delete', function () {
                const button = $(this);
                $('#delete-id').val(button.data('id'));
                $('#delete-name').text(button.data('name'));
            });

            // Auto-dismiss alerts
            window.setTimeout(function () {
                $('.alert-dismissible').fadeTo(500, 0).slideUp(500, function () {
                    $(this).remove();
                });
            }, 7000);

            // Perbarui label input file
            $('.file-input').on('change', function () {
                var fileName = $(this).val().split('\\').pop();
                if (this.id === 'edit-image-input' && this.files && this.files[0]) {
                    var reader = new FileReader();
                    reader.onload = function (e) {
                        $('#edit-image-preview').attr('src', e.target.result).show();
                        $('#edit-current-image-text').text('Preview gambar baru: ' + fileName);
                    }
                    reader.readAsDataURL(this.files[0]);
                }
            });
        });
    </script>

</body>

</html>