<?php
session_start(); // HARUS menjadi baris pertama
require_once '../config/db.php'; 

// Cek apakah admin sudah login
if (!isset($_SESSION['admin']) || $_SESSION['admin'] !== true) {
    header('Location: login.php'); // Redirect ke halaman login jika belum
    exit;
}

$message = '';
$message_type = 'success'; // Default message type

// Proses tambah produk
if ($_SERVER["REQUEST_METHOD"] == "POST" && isset($_POST['action']) && $_POST['action'] == 'add') {
    $name = trim($_POST['name']);
    $description = trim($_POST['description']);
    $price = filter_input(INPUT_POST, 'price', FILTER_VALIDATE_INT);
    $quantity = filter_input(INPUT_POST, 'quantity', FILTER_VALIDATE_INT);

    if (empty($name) || $price === false || $price < 0 || $quantity === false || $quantity < 0) {
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
            $imageName = time() . '_' . uniqid() . '.' . $imageFileType;
            $allowed_types = ['jpg', 'jpeg', 'png', 'gif'];

            if (in_array($imageFileType, $allowed_types) && $_FILES['image']['size'] <= 5000000) { // Max 5MB
                if (move_uploaded_file($_FILES['image']['tmp_name'], $target_dir . $imageName)) {
                    // File uploaded successfully
                } else {
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

        if ($message_type == 'success') { 
            $sql_insert = "INSERT INTO `products` (`name`, `description`, `price`, `quantity`, `image`) VALUES (?, ?, ?, ?, ?)";
            $stmt = $pdo->prepare($sql_insert);
            if ($stmt->execute([$name, $description, $price, $quantity, $imageName])) {
                $message = "Produk berhasil ditambahkan!";
            } else {
                $message = "Gagal menambahkan produk ke database.";
                $message_type = "danger";
            }
        }
    }
}

// Proses edit produk
if ($_SERVER["REQUEST_METHOD"] == "POST" && isset($_POST['action']) && $_POST['action'] == 'edit') {
    $id = filter_input(INPUT_POST, 'id', FILTER_VALIDATE_INT);
    $name = trim($_POST['name']);
    $description = trim($_POST['description']);
    $price = filter_input(INPUT_POST, 'price', FILTER_VALIDATE_INT);
    $quantity = filter_input(INPUT_POST, 'quantity', FILTER_VALIDATE_INT);

    if (!$id || empty($name) || $price === false || $price < 0 || $quantity === false || $quantity < 0) {
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

            if (isset($_FILES['image']) && $_FILES['image']['error'] == UPLOAD_ERR_OK) {
                $target_dir = '../uploads/';
                // Tidak perlu cek is_dir lagi jika sudah ada di proses tambah
                $imageFileType = strtolower(pathinfo($_FILES['image']['name'], PATHINFO_EXTENSION));
                $newImageName = time() . '_' . uniqid() . '.' . $imageFileType;
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

            if ($message_type == 'success') { 
                $sql_update = "UPDATE `products` SET `name`=?, `description`=?, `price`=?, `quantity`=?, `image`=? WHERE `id`=?";
                $stmt = $pdo->prepare($sql_update);
                
                if ($stmt->execute([$name, $description, $price, $quantity, $imageName, $id])) {
                    $message = "Produk berhasil diperbarui!";
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
            if ($product['image'] && file_exists($target_dir . $product['image'])) {
                unlink($target_dir . $product['image']);
            }
            $stmt_delete = $pdo->prepare("DELETE FROM `products` WHERE `id` = ?");
            if ($stmt_delete->execute([$id])) {
                $message = "Produk berhasil dihapus!";
            } else {
                $message = "Gagal menghapus produk dari database.";
                $message_type = "danger";
            }
        } else {
            $message = "Produk yang akan dihapus tidak ditemukan.";
            $message_type = "danger";
        }
    }
}

// Ambil produk (SETELAH SEMUA PROSES POST DILAKUKAN AGAR DATA TERBARU TAMPIL)
$stmt_get_products = $pdo->query("SELECT `id`, `name`, `description`, `price`, `quantity`, `image`, DATE_FORMAT(`created_at`, '%d %b %Y %H:%i') as `created_at_formatted` FROM `products` ORDER BY `created_at` DESC");
$products = $stmt_get_products->fetchAll(PDO::FETCH_ASSOC);


$page_title = "Manajemen Produk"; // Variabel untuk judul halaman di header.php

include 'partials/header.php'; // Include header HTML, CSS, dan <head>
include 'partials/navbar.php'; // Include navbar admin
?>
<head>
    <link rel="stylesheet" href="css/style.css">
    <script src="js/admin.js"></script>
</head>
<div class="container">
   <div class="admin-main-content">
        <div class="page-header mt-4">
            <h2><?= htmlspecialchars($page_title) ?></h2>
            <button class="btn btn-admin-primary mb-3" data-bs-toggle="modal" data-bs-target="#modalTambah">
                <i class="fas fa-plus"></i> Tambah Produk
            </button>
        </div>

        <?php if ($message): ?>
            <div class="alert alert-<?= htmlspecialchars($message_type) ?> alert-dismissible fade show" role="alert">
                <?= htmlspecialchars($message) ?>
                <button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Close"></button>
            </div>
        <?php endif; ?>

        <div class="table-responsive-custom"> <table class="table table-bordered table-hover table-striped align-middle">
                <thead class="table-custom-header"> <tr>
                        <th>Gambar</th>
                        <th>Nama</th>
                        <th>Harga</th>
                        <th>Stok</th>
                        <th>Dibuat</th>
                        <th>Aksi</th>
                    </tr>
                </thead>
                <tbody>
                <?php if (empty($products)): ?>
                    <tr>
                        <td colspan="6" class="text-center">Belum ada produk.</td>
                    </tr>
                <?php else: ?>
                    <?php foreach ($products as $product): ?>
                    <tr>
                        <td>
                            <?php if (!empty($product['image'])): ?>
                                <img src="../uploads/<?= htmlspecialchars($product['image']) ?>" alt="<?= htmlspecialchars($product['name']) ?>" style="width: 60px; height: auto; border-radius: 0.25rem; border: 1px solid #eee;">
                            <?php else: ?>
                                <img src="https://via.placeholder.com/60x60.png?text=No+Image" alt="No image available" style="width: 60px; height: auto; border-radius: 0.25rem; border: 1px solid #eee;">
                            <?php endif; ?>
                        </td>
                        <td><?= htmlspecialchars($product['name']) ?></td>
                        <td>Rp<?= number_format($product['price'], 0, ',', '.') ?></td>
                        <td><?= htmlspecialchars($product['quantity']) ?></td>
                        <td><?= htmlspecialchars($product['created_at_formatted']) ?></td>
                        <td>
                            <button 
                                class="btn btn-sm btn-warning btn-edit" 
                                data-bs-toggle="modal" 
                                data-bs-target="#modalEdit"
                                data-id="<?= $product['id'] ?>"
                                data-name="<?= htmlspecialchars($product['name'], ENT_QUOTES) ?>"
                                data-description="<?= htmlspecialchars($product['description'], ENT_QUOTES) ?>"
                                data-price="<?= $product['price'] ?>"
                                data-quantity="<?= $product['quantity'] ?>"
                                data-image="<?= htmlspecialchars($product['image'], ENT_QUOTES) ?>"
                            ><i class="fas fa-edit"></i> Edit</button>

                            <button 
                                class="btn btn-sm btn-danger btn-delete" 
                                data-bs-toggle="modal" 
                                data-bs-target="#modalHapus"
                                data-id="<?= $product['id'] ?>"
                                data-name="<?= htmlspecialchars($product['name'], ENT_QUOTES) ?>"
                            ><i class="fas fa-trash"></i> Hapus</button>
                        </td>
                    </tr>
                    <?php endforeach; ?>
                <?php endif; ?>
                </tbody>
            </table>
        </div>
    </div> </div> <div class="modal fade" id="modalTambah" tabindex="-1" aria-labelledby="modalTambahLabel" aria-hidden="true">
  <div class="modal-dialog modal-lg">
    <form class="modal-content" method="post" enctype="multipart/form-data" action="produk.php"> <input type="hidden" name="action" value="add">
      <div class="modal-header custom-header"> <h5 class="modal-title" id="modalTambahLabel">Tambah Produk Baru</h5>
        <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
      </div>
      <div class="modal-body">
          <div class="mb-3">
              <label for="add-name" class="form-label">Nama Produk</label>
              <input type="text" name="name" id="add-name" class="form-control" required>
          </div>
          <div class="mb-3">
              <label for="add-description" class="form-label">Deskripsi</label>
              <textarea name="description" id="add-description" class="form-control" rows="3"></textarea>
          </div>
          <div class="row">
            <div class="col-md-6 mb-3">
                <label for="add-price" class="form-label">Harga (Rp)</label>
                <input type="number" name="price" id="add-price" class="form-control" required min="0">
            </div>
            <div class="col-md-6 mb-3">
                <label for="add-quantity" class="form-label">Stok</label>
                <input type="number" name="quantity" id="add-quantity" class="form-control" required min="0">
            </div>
          </div>
          <div class="mb-3">
              <label for="add-image" class="form-label">Gambar Produk</label>
              <input type="file" name="image" id="add-image" class="form-control" accept="image/png, image/jpeg, image/gif">
              <small class="form-text text-muted">Ukuran maks 5MB. Tipe: JPG, PNG, GIF.</small>
          </div>
      </div>
      <div class="modal-footer">
        <button type="submit" class="btn btn-success"><i class="fas fa-save"></i> Simpan</button>
        <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Batal</button>
      </div>
    </form>
  </div>
</div>

<div class="modal fade" id="modalEdit" tabindex="-1" aria-labelledby="modalEditLabel" aria-hidden="true">
  <div class="modal-dialog modal-lg">
    <form class="modal-content" method="post" enctype="multipart/form-data" action="produk.php"> <input type="hidden" name="action" value="edit">
      <input type="hidden" name="id" id="edit-id">
      <div class="modal-header custom-header"> <h5 class="modal-title" id="modalEditLabel">Edit Produk</h5>
        <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
      </div>
      <div class="modal-body">
          <div class="mb-3">
              <label for="edit-name" class="form-label">Nama Produk</label>
              <input type="text" name="name" id="edit-name" class="form-control" required>
          </div>
          <div class="mb-3">
              <label for="edit-description" class="form-label">Deskripsi</label>
              <textarea name="description" id="edit-description" class="form-control" rows="3"></textarea>
          </div>
           <div class="row">
            <div class="col-md-6 mb-3">
                <label for="edit-price" class="form-label">Harga (Rp)</label>
                <input type="number" name="price" id="edit-price" class="form-control" required min="0">
            </div>
            <div class="col-md-6 mb-3">
                <label for="edit-quantity" class="form-label">Stok</label>
                <input type="number" name="quantity" id="edit-quantity" class="form-control" required min="0">
            </div>
          </div>
          <div class="mb-3">
              <label for="edit-image-input" class="form-label">Gambar Produk (biarkan kosong jika tidak ganti)</label>
              <input type="file" name="image" id="edit-image-input" class="form-control" accept="image/png, image/jpeg, image/gif">
              <small class="form-text text-muted">Ukuran maks 5MB. Tipe: JPG, PNG, GIF.</small>
              <div class="mt-2">
                  <img id="edit-image-preview" src="" alt="Preview Gambar Lama" style="max-width: 120px; max-height: 120px; object-fit: cover; border-radius: 0.25rem; border: 1px solid #ddd;">
                  <small id="edit-current-image-text" class="form-text text-muted d-block"></small>
              </div>
          </div>
      </div>
      <div class="modal-footer">
        <button type="submit" class="btn btn-admin-primary"><i class="fas fa-save"></i> Simpan Perubahan</button>
        <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Batal</button>
      </div>
    </form>
  </div>
</div>

<div class="modal fade" id="modalHapus" tabindex="-1" aria-labelledby="modalHapusLabel" aria-hidden="true">
  <div class="modal-dialog">
    <form class="modal-content" method="post" action="produk.php"> <input type="hidden" name="action" value="delete">
      <input type="hidden" name="id" id="delete-id">
      <div class="modal-header bg-danger text-white"> <h5 class="modal-title" id="modalHapusLabel">Konfirmasi Hapus Produk</h5>
        <button type="button" class="btn-close btn-close-white" data-bs-dismiss="modal" aria-label="Close"></button>
      </div>
      <div class="modal-body">
        <p>Apakah Anda yakin ingin menghapus produk <strong id="delete-name"></strong> secara permanen? Tindakan ini tidak dapat diurungkan.</p>
      </div>
      <div class="modal-footer">
        <button type="submit" class="btn btn-danger"><i class="fas fa-trash-alt"></i> Ya, Hapus</button>
        <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Batal</button>
      </div>
    </form>
  </div>
</div>

<?php
// Hapus <script> tag yang lama dari sini karena JS Bootstrap sudah ada di footer.php
// JavaScript spesifik untuk populasi modal dan auto-dismiss alert masih relevan.
// Jika ada JS lain yang sangat spesifik untuk produk.php, bisa diletakkan di sini atau file terpisah.
$page_specific_js_script = "
<script>
document.addEventListener('DOMContentLoaded', function() {
    // Cek jika ada parameter action=add_new di URL untuk langsung buka modal tambah
    const urlParams = new URLSearchParams(window.location.search);
    if (urlParams.has('action') && urlParams.get('action') === 'add_new') {
        var modalTambah = new bootstrap.Modal(document.getElementById('modalTambah'));
        modalTambah.show();
    }

    // Edit Modal Population
    document.querySelectorAll('.btn-edit').forEach(button => {
        button.addEventListener('click', () => {
            const id = button.getAttribute('data-id');
            const name = button.getAttribute('data-name');
            const description = button.getAttribute('data-description');
            const price = button.getAttribute('data-price');
            const quantity = button.getAttribute('data-quantity');
            const image = button.getAttribute('data-image');

            document.getElementById('edit-id').value = id;
            document.getElementById('edit-name').value = name;
            document.getElementById('edit-description').value = description;
            document.getElementById('edit-price').value = price;
            document.getElementById('edit-quantity').value = quantity;
            
            const imagePreview = document.getElementById('edit-image-preview');
            const currentImageText = document.getElementById('edit-current-image-text');
            if (image && image !== 'null' && image !== '') {
                imagePreview.src = '../uploads/' + image;
                imagePreview.style.display = 'block';
                currentImageText.textContent = 'Gambar saat ini: ' + image;
            } else {
                imagePreview.src = 'https://via.placeholder.com/120x120.png?text=No+Image';
                imagePreview.style.display = 'block';
                currentImageText.textContent = 'Tidak ada gambar saat ini.';
            }
            document.getElementById('edit-image-input').value = '';
        });
    });

    // Delete Modal Population
    document.querySelectorAll('.btn-delete').forEach(button => {
        button.addEventListener('click', () => {
            const id = button.getAttribute('data-id');
            const name = button.getAttribute('data-name');
            document.getElementById('delete-id').value = id;
            document.getElementById('delete-name').textContent = name;
        });
    });

    // Dismiss alerts automatically after 5 seconds
    setTimeout(function() {
        let alertList = document.querySelectorAll('.alert-dismissible');
        alertList.forEach(function(alert) {
            if (bootstrap.Alert.getInstance(alert)) { // Cek jika instance sudah ada
                 bootstrap.Alert.getInstance(alert).close();
            } else {
                 new bootstrap.Alert(alert).close();
            }
        });
    }, 5000);
});
</script>
";

// Anda bisa echo $page_specific_js_script ini sebelum include footer.php jika mau,
// atau letakkan langsung di sini karena footer.php akan menutup tag <body> dan <html>
echo $page_specific_js_script; 

include 'partials/footer.php'; // Include footer HTML dan link JS Bootstrap
?>