<!DOCTYPE html>
<html lang="en">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Slide Manajemen</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/css/bootstrap.min.css" rel="stylesheet">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.5.1/css/all.min.css">
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
                        <h1 class="page-title">Kelola Hero Slider</h1>
                        <button class="btn btn-admin-primary" id="addSliderBtn">
                            <i class="fas fa-plus me-1"></i> Tambah Slide Baru
                        </button>
                    </div>

                    <div class="card">
                        <div class="card-body">
                            <p class="text-muted">
                                Atur urutan, judul, subjudul, dan gambar yang tampil di halaman utama.
                            </p>
                            <div id="sliderList" class="list-group list-group-flush">
                                <div class="text-center p-5">
                                    <div class="spinner-border" role="status">
                                        <span class="visually-hidden">Loading...</span>
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
            </main>
        </div>
    </div>


    <div class="modal fade" id="sliderModal" tabindex="-1" aria-labelledby="sliderModalLabel" aria-hidden="true">
        <div class="modal-dialog modal-lg">
            <div class="modal-content">
                <form id="sliderForm" enctype="multipart/form-data">
                    <div class="modal-header">
                        <h5 class="modal-title" id="sliderModalLabel">Tambah Slide Baru</h5>
                        <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
                    </div>
                    <div class="modal-body">
                        <input type="hidden" id="sliderId" name="id">
                        <div class="mb-3">
                            <label for="sliderTitle" class="form-label">Judul</label>
                            <input type="text" class="form-control" id="sliderTitle" name="title">
                        </div>
                        <div class="mb-3">
                            <label for="sliderSubtitle" class="form-label">Subjudul</label>
                            <textarea class="form-control" id="sliderSubtitle" name="subtitle" rows="3"></textarea>
                        </div>
                        <div class="mb-3">
                            <label for="sliderImage" class="form-label">Gambar</label>
                            <input class="form-control" type="file" id="sliderImage" name="image" accept="image/*">
                            <small class="form-text text-muted">Kosongkan jika tidak ingin mengubah gambar saat
                                mengedit.</small>
                        </div>
                        <div class="mb-3 form-check">
                            <input type="checkbox" class="form-check-input" id="sliderIsActive" name="is_active"
                                value="1" checked>
                            <label class="form-check-label" for="sliderIsActive">Aktifkan slide ini?</label>
                        </div>
                    </div>
                    <div class="modal-footer">
                        <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Tutup</button>
                        <button type="submit" class="btn btn-primary">Simpan Perubahan</button>
                    </div>
                </form>
            </div>
        </div>
    </div>

    <div class="toast-container position-fixed bottom-0 end-0 p-3"></div>
<div class="sidebar-overlay" id="sidebarOverlay"></div>

    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/js/bootstrap.bundle.min.js"></script>
    <script src="https://cdn.jsdelivr.net/npm/sweetalert2@11"></script>
    <script src="js/main.js"></script>
    <script src="js/slider-management.js"></script>
</body>

</html>