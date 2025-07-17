<?php
header('Content-Type: application/json');
require_once '../config/db.php'; // Sesuaikan path

// Siapkan respons default
$response = ['success' => false, 'message' => 'Request tidak valid.'];

// Pastikan folder upload ada
$uploadDir = '../uploads/hero/';
if (!is_dir($uploadDir)) {
    mkdir($uploadDir, 0777, true);
}

// --- LOGIKA CRUD BERDASARKAN METODE HTTP ---

// METHOD: GET (Untuk membaca data)
if ($_SERVER['REQUEST_METHOD'] === 'GET') {
    // Jika ada 'id', ambil satu data (Read One)
    if (isset($_GET['id'])) {
        $id = filter_input(INPUT_GET, 'id', FILTER_VALIDATE_INT);
        $stmt = $pdo->prepare("SELECT * FROM hero_sliders WHERE id = ?");
        $stmt->execute([$id]);
        $slider = $stmt->fetch(PDO::FETCH_ASSOC);
        echo json_encode($slider);
    }
    // Jika tidak ada 'id', ambil semua data (Read All)
    else {
        $stmt = $pdo->query("SELECT id, title, subtitle, image, is_active FROM hero_sliders ORDER BY id DESC");
        $sliders = $stmt->fetchAll(PDO::FETCH_ASSOC);
        echo json_encode($sliders);
    }
}

// METHOD: POST (Untuk membuat, memperbarui, atau menghapus data)
else if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $action = $_POST['action'] ?? '';

    // Aksi: Tambah Slide Baru (Create)
    if ($action === 'add') {
        $title = trim($_POST['title'] ?? '');
        $subtitle = trim($_POST['subtitle'] ?? '');
        $is_active = isset($_POST['is_active']) ? 1 : 0;

        if (empty($title)) {
            $response['message'] = 'Judul tidak boleh kosong.';
        } elseif (empty($_FILES['image']['name'])) {
            $response['message'] = 'Gambar wajib diisi.';
        } else {
            $imageName = time() . '_' . basename($_FILES['image']['name']);
            $targetFilePath = $uploadDir . $imageName;

            if (move_uploaded_file($_FILES['image']['tmp_name'], $targetFilePath)) {
                $stmt = $pdo->prepare("INSERT INTO hero_sliders (title, subtitle, image, is_active) VALUES (?, ?, ?, ?)");
                if ($stmt->execute([$title, $subtitle, $imageName, $is_active])) {
                    $response = ['success' => true, 'message' => 'Slide berhasil ditambahkan!'];
                } else {
                    $response['message'] = 'Gagal menyimpan data ke database.';
                }
            } else {
                $response['message'] = 'Gagal mengupload gambar.';
            }
        }
    }

    // Aksi: Perbarui Slide (Update)
    else if ($action === 'update') {
        $id = filter_input(INPUT_POST, 'id', FILTER_VALIDATE_INT);
        $title = trim($_POST['title'] ?? '');
        $subtitle = trim($_POST['subtitle'] ?? '');
        $is_active = isset($_POST['is_active']) ? 1 : 0;

        if (!$id) {
            $response['message'] = 'ID Slide tidak valid.';
        } elseif (empty($title)) {
            $response['message'] = 'Judul tidak boleh kosong.';
        } else {
            // Logika untuk update gambar jika ada gambar baru
            if (!empty($_FILES['image']['name'])) {
                // Hapus gambar lama dulu
                $stmt_old = $pdo->prepare("SELECT image FROM hero_sliders WHERE id = ?");
                $stmt_old->execute([$id]);
                if ($oldImage = $stmt_old->fetchColumn()) {
                    if (file_exists($uploadDir . $oldImage)) {
                        unlink($uploadDir . $oldImage);
                    }
                }

                // Upload gambar baru
                $imageName = time() . '_' . basename($_FILES['image']['name']);
                $targetFilePath = $uploadDir . $imageName;

                if (move_uploaded_file($_FILES['image']['tmp_name'], $targetFilePath)) {
                    $stmt = $pdo->prepare("UPDATE hero_sliders SET title = ?, subtitle = ?, image = ?, is_active = ? WHERE id = ?");
                    if ($stmt->execute([$title, $subtitle, $imageName, $is_active, $id])) {
                        $response = ['success' => true, 'message' => 'Slide berhasil diperbarui!'];
                    } else {
                        $response['message'] = 'Gagal memperbarui data dengan gambar baru.';
                    }
                } else {
                    $response['message'] = 'Gagal mengupload gambar baru.';
                }
            } else {
                // Update tanpa mengubah gambar
                $stmt = $pdo->prepare("UPDATE hero_sliders SET title = ?, subtitle = ?, is_active = ? WHERE id = ?");
                if ($stmt->execute([$title, $subtitle, $is_active, $id])) {
                    $response = ['success' => true, 'message' => 'Slide berhasil diperbarui!'];
                } else {
                    $response['message'] = 'Gagal memperbarui data.';
                }
            }
        }
    }

    // Aksi: Hapus Slide (Delete)
    else if ($action === 'delete') {
        $id = filter_input(INPUT_POST, 'id', FILTER_VALIDATE_INT);
        if (!$id) {
            $response['message'] = 'Error: ID slide tidak valid.';
        } else {
            // Hapus file gambar dari server
            $stmt_img = $pdo->prepare("SELECT image FROM hero_sliders WHERE id = ?");
            $stmt_img->execute([$id]);
            if ($image = $stmt_img->fetchColumn()) {
                if (file_exists($uploadDir . $image)) {
                    unlink($uploadDir . $image);
                }
            }
            // Hapus data dari database
            $stmt = $pdo->prepare("DELETE FROM hero_sliders WHERE id = ?");
            if ($stmt->execute([$id])) {
                $response = ['success' => true, 'message' => 'Slide berhasil dihapus!'];
            } else {
                $response['message'] = 'Gagal menghapus data dari database.';
            }
        }
    }

    // Jika aksi tidak dikenali
    else {
        $response['message'] = 'Aksi POST tidak dikenali.';
    }

    echo json_encode($response);
}
?>
