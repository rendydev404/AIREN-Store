<?php
session_start();
// Ganti path '../config/db.php' jika lokasi file koneksi database Anda berbeda.
require_once '../config/db.php';

// Keamanan: Pastikan hanya admin yang bisa mengakses halaman ini.
if (!isset($_SESSION['role']) || $_SESSION['role'] !== 'admin') {
    header('Location: ../login.php');
    exit;
}

// Fungsi untuk mengambil semua data pengaturan dari database.
function get_all_settings($pdo)
{
    $settings = [];
    try {
        $stmt = $pdo->query("SELECT setting_name, setting_value FROM site_settings");
        while ($row = $stmt->fetch(PDO::FETCH_ASSOC)) {
            $settings[$row['setting_name']] = $row['setting_value'];
        }
    } catch (Exception $e) {
        // Jika tabel tidak ada atau error, fungsi akan mengembalikan array kosong.
    }
    return $settings;
}

// Proses penyimpanan form saat admin menekan tombol "Simpan Pengaturan".
if ($_SERVER["REQUEST_METHOD"] == "POST") {
    try {
        $pdo->beginTransaction();

        // Daftar semua pengaturan yang akan disimpan dari form.
        $all_settings = [
            'logo_type',
            'logo_text',
            'logo_icon_class',
            'logo_icon_color',
            'logo_text_color',
            'logo_font_family',
            'logo_font_weight',
            'logo_image_height',
            'logo_text_size'
        ];

        $stmt = $pdo->prepare("UPDATE site_settings SET setting_value = ? WHERE setting_name = ?");
        foreach ($all_settings as $setting_name) {
            if (isset($_POST[$setting_name])) {
                $stmt->execute([$_POST[$setting_name], $setting_name]);
            }
        }

        // Logika untuk menangani upload file gambar logo.
        if (isset($_FILES['logo_image']) && $_FILES['logo_image']['error'] == 0) {
            $target_dir = "../uploads/logo/";
            if (!is_dir($target_dir)) {
                mkdir($target_dir, 0755, true);
            }
            $image_name = "logo_" . time() . "." . pathinfo($_FILES["logo_image"]["name"], PATHINFO_EXTENSION);
            $target_file = $target_dir . $image_name;

            if (move_uploaded_file($_FILES["logo_image"]["tmp_name"], $target_file)) {
                $stmt->execute([$image_name, 'logo_image']);
            }
        }

        $pdo->commit();
        $success_message = "Pengaturan berhasil disimpan!";

    } catch (Exception $e) {
        $pdo->rollBack();
        $error_message = "Terjadi kesalahan: " . $e->getMessage();
    }
}

// Ambil data pengaturan terbaru untuk ditampilkan di form.
$settings = get_all_settings($pdo);
?>

<!DOCTYPE html>
<html lang="id">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Pengaturan Brand & Logo</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/css/bootstrap.min.css" rel="stylesheet">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.5.1/css/all.min.css">

    <link href="https://cdn.jsdelivr.net/npm/remixicon@3.5.0/fonts/remixicon.css" rel="stylesheet">
    <link rel="stylesheet" href="css/tes.css">
    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
    <link
        href="https://fonts.googleapis.com/css2?family=Lato:wght@300;400;700&family=Merriweather:wght@400;700&family=Montserrat:wght@400;500;700;900&family=Open+Sans:wght@400;600;700&family=Poppins:wght@300;400;500;600;700;800&family=Roboto:wght@300;400;500;700;900&display=swap"
        rel="stylesheet">
</head>

<body>
    <div class="admin-layout-wrapper">
        <?php include 'partials/sidebar.php'; ?>
        <div class="main-content-wrapper">
            <?php include 'partials/header.php'; ?>
            <main class="admin-page-content">
                <div class="container-fluid">
                    <div class="container mt-5 mb-5">
                        <div class="card shadow-sm">
                            <div class="card-header bg-dark text-white">
                                <h3><i class="ri-palette-line me-2"></i>Pengaturan Brand & Logo</h3>
                            </div>
                            <div class="card-body">
                                <?php if (isset($success_message)): ?>
                                    <div class="alert alert-success"><?php echo $success_message; ?></div>
                                <?php endif; ?>
                                <?php if (isset($error_message)): ?>
                                    <div class="alert alert-danger"><?php echo $error_message; ?></div>
                                <?php endif; ?>

                                <form method="POST" enctype="multipart/form-data">
                                    <div class="mb-4">
                                        <label class="form-label fw-bold">Tipe Logo</label>
                                        <div>
                                            <input type="radio" class="btn-check" name="logo_type" id="type_text"
                                                value="text" <?php echo ($settings['logo_type'] ?? 'text') === 'text' ? 'checked' : ''; ?>>
                                            <label class="btn btn-outline-primary mb-3" for="type_text">
                                                <i class="ri-font-size-2 me-1"></i> Logo Teks
                                            </label>

                                            <input type="radio" class="btn-check" name="logo_type" id="type_image"
                                                value="image" <?php echo ($settings['logo_type'] ?? 'text') === 'image' ? 'checked' : ''; ?>>
                                            <label class="btn btn-outline-primary mb-3" for="type_image">
                                                <i class="ri-image-line me-1"></i> Logo Gambar
                                            </label>
                                        </div>
                                    </div>

                                    <div id="image-settings" class="mb-4 p-3 border rounded bg-light"
                                        style="display:none;">
                                        <h5>Pengaturan Logo Gambar</h5>
                                        <div class="mb-3">
                                            <label for="logo_image" class="form-label">Upload Logo Baru</label>
                                            <input class="form-control" type="file" name="logo_image"
                                                accept="image/png, image/jpeg, image/svg+xml">
                                            <small class="text-muted">Rekomendasi format: PNG transparan atau
                                                SVG.</small>
                                        </div>
                                        <div class="mb-3">
                                            <label for="logo_image_height" class="form-label">Tinggi Logo Gambar (dalam
                                                pixel)</label>
                                            <input type="number" class="form-control" name="logo_image_height"
                                                value="<?php echo htmlspecialchars($settings['logo_image_height'] ?? '40'); ?>"
                                                style="max-width: 150px;">
                                        </div>
                                        <?php if (!empty($settings['logo_image'])): ?>
                                            <p>Logo saat ini:</p>
                                            <img src="../uploads/logo/<?php echo htmlspecialchars($settings['logo_image']); ?>"
                                                alt="Logo Saat Ini"
                                                style="max-height: 50px; background: #f8f9fa; padding: 5px; border-radius: 5px;">
                                        <?php endif; ?>
                                    </div>

                                    <div id="text-settings" class="mb-4 p-3 border rounded bg-light"
                                        style="display:none;">
                                        <h5>Pengaturan Logo Teks</h5>
                                        <div class="row">
                                            <div class="col-md-4 mb-3">
                                                <label for="logo_text" class="form-label">Teks Logo</label>
                                                <input type="text" class="form-control" name="logo_text"
                                                    value="<?php echo htmlspecialchars($settings['logo_text'] ?? 'AIREN Store'); ?>">
                                            </div>
                                            <div class="col-md-4 mb-3">
                                                <label for="logo_text_size" class="form-label">Ukuran Font
                                                    (pixel)</label>
                                                <input type="number" class="form-control" name="logo_text_size"
                                                    value="<?php echo htmlspecialchars($settings['logo_text_size'] ?? '24'); ?>">
                                            </div>
                                            <div class="col-md-4 mb-3">
                                                <label for="logo_text_color" class="form-label">Warna Teks Logo</label>
                                                <input type="color" class="form-control form-control-color"
                                                    name="logo_text_color"
                                                    value="<?php echo htmlspecialchars($settings['logo_text_color'] ?? '#000000'); ?>">
                                            </div>
                                        </div>
                                        <div class="row">
                                            <div class="col-md-6 mb-3">
                                                <label for="logo_font_family" class="form-label">Font Teks Logo</label>
                                                <select class="form-select" id="logo_font_family_select"
                                                    name="logo_font_family"></select>
                                            </div>
                                            <div class="col-md-6 mb-3">
                                                <label for="logo_font_weight" class="form-label">Ketebalan Font
                                                    (Weight)</label>
                                                <select class="form-select" id="logo_font_weight_select"
                                                    name="logo_font_weight"></select>
                                            </div>
                                        </div>
                                        <div class="row">
                                            <div class="col-md-6 mb-3">
                                                <label for="logo_icon_class" class="form-label">Kelas Ikon (Remix
                                                    Icon)</label>
                                                <input type="text" class="form-control" name="logo_icon_class"
                                                    value="<?php echo htmlspecialchars($settings['logo_icon_class'] ?? 'ri-diamond-line'); ?>">
                                                <small>Cari di <a href="https://remixicon.com/" target="_blank">Remix
                                                        Icon</a>. Contoh: `ri-store-line`.</small>
                                            </div>
                                            <div class="col-md-6 mb-3">
                                                <label for="logo_icon_color" class="form-label">Warna Ikon</label>
                                                <input type="color" class="form-control form-control-color"
                                                    name="logo_icon_color"
                                                    value="<?php echo htmlspecialchars($settings['logo_icon_color'] ?? '#14b5ff'); ?>">
                                            </div>
                                        </div>
                                    </div>

                                    <button type="submit" class="btn btn-admin-primary">
                                        <i class="ri-save-line me-1"></i> Simpan Pengaturan
                                    </button>
                                </form>
                            </div>
                        </div>
                    </div>
                </div>
            </main>

        </div>

    </div>
    <div class="sidebar-overlay" id="sidebarOverlay"></div>

    <script src="js/main.js"></script>
    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/js/bootstrap.bundle.min.js"></script>
    <script>
        document.addEventListener('DOMContentLoaded', function () {
            const typeRadios = document.querySelectorAll('input[name="logo_type"]');
            const textSettings = document.getElementById('text-settings');
            const imageSettings = document.getElementById('image-settings');

            function toggleSettings(type) {
                textSettings.style.display = (type === 'text') ? 'block' : 'none';
                imageSettings.style.display = (type === 'image') ? 'block' : 'none';
            }

            const fontSelect = document.getElementById('logo_font_family_select');
            const weightSelect = document.getElementById('logo_font_weight_select');

            const fontDatabase = {
                "Poppins": { weights: { "300": "Light", "400": "Regular", "500": "Medium", "600": "Semi-Bold", "700": "Bold", "800": "Extra-Bold" } },
                "Roboto": { weights: { "300": "Light", "400": "Regular", "500": "Medium", "700": "Bold", "900": "Black" } },
                "Open Sans": { weights: { "400": "Regular", "600": "Semi-Bold", "700": "Bold" } },
                "Montserrat": { weights: { "400": "Regular", "500": "Medium", "700": "Bold", "900": "Black" } },
                "Lato": { weights: { "300": "Light", "400": "Regular", "700": "Bold" } },
                "Merriweather": { weights: { "400": "Regular", "700": "Bold" } }
            };

            function populateWeights(selectedFont) {
                weightSelect.innerHTML = '';
                const fontData = fontDatabase[selectedFont];
                if (fontData) {
                    for (const weightValue in fontData.weights) {
                        const weightText = fontData.weights[weightValue];
                        weightSelect.add(new Option(`${weightValue} - ${weightText}`, weightValue));
                    }
                }
            }

            for (const fontFamily in fontDatabase) {
                fontSelect.add(new Option(fontFamily, fontFamily));
            }

            const savedFontFamily = "<?php echo addslashes($settings['logo_font_family'] ?? 'Poppins'); ?>";
            const savedFontWeight = "<?php echo addslashes($settings['logo_font_weight'] ?? '700'); ?>";

            fontSelect.value = savedFontFamily;
            populateWeights(savedFontFamily);
            weightSelect.value = savedFontWeight;

            fontSelect.addEventListener('change', function () { populateWeights(this.value); });
            toggleSettings(document.querySelector('input[name="logo_type"]:checked').value);
            typeRadios.forEach(radio => radio.addEventListener('change', (e) => toggleSettings(e.target.value)));
        });
    </script>

</body>

</html>