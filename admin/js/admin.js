document.addEventListener('DOMContentLoaded', function() {
    const sidebar = document.getElementById('adminSidebar');
    const sidebarToggler = document.getElementById('sidebarToggler');
    const mainContent = document.querySelector('.admin-main-content'); // Add this class to your main content wrapper
    const bodyEl = document.body;

    const SIDEBAR_COLLAPSED_KEY = 'admin_sidebar_collapsed';

    function setSidebarState(collapsed) {
        if (collapsed) {
            sidebar.classList.add('collapsed');
            bodyEl.classList.add('sidebar-collapsed'); // Add class to body
            localStorage.setItem(SIDEBAR_COLLAPSED_KEY, 'true');
        } else {
            sidebar.classList.remove('collapsed');
            bodyEl.classList.remove('sidebar-collapsed'); // Remove class from body
            localStorage.setItem(SIDEBAR_COLLAPSED_KEY, 'false');
        }
    }

    if (sidebarToggler && sidebar) {
        sidebarToggler.addEventListener('click', function() {
            setSidebarState(!sidebar.classList.contains('collapsed'));
        });

        // Apply saved sidebar state on load
        const savedSidebarState = localStorage.getItem(SIDEBAR_COLLAPSED_KEY);
        // Default to not collapsed if no saved state or if saved state is 'false'
        setSidebarState(savedSidebarState === 'true');
    }
});

 document.addEventListener('DOMContentLoaded', function() {
    // Ambil elemen yang dibutuhkan
    const mobileSidebarToggler = document.getElementById('mobileSidebarToggler');
    const sidebar = document.getElementById('adminSidebar');
    const sidebarOverlay = document.getElementById('sidebarOverlay');

    // Fungsi untuk menampilkan atau menyembunyikan sidebar di mobile
    function toggleMobileSidebar() {
        if (sidebar && sidebarOverlay) {
            sidebar.classList.toggle('active');
            sidebarOverlay.classList.toggle('active');
        }
    }

    // Pasang event listener hanya jika elemennya ada
    if (mobileSidebarToggler) {
        mobileSidebarToggler.addEventListener('click', toggleMobileSidebar);
    }
    if (sidebarOverlay) {
        sidebarOverlay.addEventListener('click', toggleMobileSidebar); // Agar bisa ditutup saat area gelap diklik
    }
});

document.addEventListener('DOMContentLoaded', function() {
    
    // --- Fungsionalitas Tombol Tema (Dark/Light Mode) ---
    const themeToggleBtn = document.getElementById('themeToggleBtn');
    const currentTheme = localStorage.getItem('theme') || 'light';
    
    document.documentElement.setAttribute('data-theme', currentTheme);

    if (themeToggleBtn) {
        themeToggleBtn.addEventListener('click', function() {
            let newTheme = document.documentElement.getAttribute('data-theme') === 'dark' ? 'light' : 'dark';
            document.documentElement.setAttribute('data-theme', newTheme);
            localStorage.setItem('theme', newTheme);
        });
    }

    // --- Fungsionalitas Menu Sidebar Mobile ---
    const mobileSidebarToggler = document.querySelector('.navbar-toggler');
    const sidebar = document.getElementById('adminSidebar');
    const sidebarOverlay = document.getElementById('sidebarOverlay');

    if (mobileSidebarToggler && sidebar && sidebarOverlay) {
        // Tampilkan/sembunyikan sidebar saat tombol hamburger di-klik
        mobileSidebarToggler.addEventListener('click', function() {
            sidebar.classList.toggle('active');
            sidebarOverlay.classList.toggle('active');
        });

        // Sembunyikan sidebar saat area overlay di-klik
        sidebarOverlay.addEventListener('click', function() {
            sidebar.classList.remove('active');
            sidebarOverlay.classList.remove('active');
        });
    }
});





//slide dinamis

document.addEventListener('DOMContentLoaded', function () {
    // === Inisialisasi Elemen & Variabel Global ===
    const sliderList = document.getElementById('sliderList');
    const addSliderBtn = document.getElementById('addSliderBtn');
    const sliderModalEl = document.getElementById('sliderModal');
    
    if (!sliderList || !addSliderBtn || !sliderModalEl) {
        console.error("Kesalahan Kritis: Satu atau lebih elemen HTML (sliderList, addSliderBtn, sliderModal) tidak ditemukan.");
        return;
    }

    const sliderModal = new bootstrap.Modal(sliderModalEl);
    const sliderForm = document.getElementById('sliderForm');
    const modalTitle = document.getElementById('sliderModalLabel');
    const submitButton = sliderForm.querySelector('button[type="submit"]');

    let listNeedsReload = false;

    // === Fungsi-Fungsi Pembantu ===
    const showToast = (message, type = 'success') => {
        const toastContainer = document.querySelector('.toast-container') || (() => {
            const container = document.createElement('div');
            container.className = 'toast-container position-fixed top-0 end-0 p-3';
            document.body.appendChild(container);
            return container;
        })();

        const toastEl = document.createElement('div');
        toastEl.className = `toast align-items-center text-bg-${type} border-0`;
        toastEl.innerHTML = `<div class="d-flex"><div class="toast-body">${message}</div><button type="button" class="btn-close btn-close-white me-2 m-auto" data-bs-dismiss="toast" aria-label="Close"></button></div>`;
        
        toastContainer.appendChild(toastEl);
        const toast = new bootstrap.Toast(toastEl, { delay: 2000 });
        toast.show();
        toastEl.addEventListener('hidden.bs.toast', () => toastEl.remove());
    };

    const loadSliders = async () => {
        sliderList.innerHTML = `<div class="d-flex justify-content-center p-5"><div class="spinner-border" role="status"><span class="visually-hidden">Loading...</span></div></div>`;
        try {
            const response = await fetch('slide_hero.php');
            const sliders = await response.json();
            
            sliderList.innerHTML = '';
            
            if (sliders && sliders.length > 0) {
                sliders.forEach(slider => {
                    const item = document.createElement('div');
                    item.className = 'list-group-item d-flex justify-content-between align-items-center';
                    item.dataset.id = slider.id;
                    item.innerHTML = `
                        <div class="d-flex align-items-center">
                            <img src="../uploads/hero/${slider.image}" alt="${slider.title}" class="rounded me-3" style="width:100px; height:50px; object-fit:cover;">
                            <div><h6 class="mb-0">${slider.title}</h6><small class="text-muted">${slider.subtitle ? slider.subtitle.substring(0, 50) + '...' : ''}</small><br><span class="badge bg-${slider.is_active == 1 ? 'success' : 'secondary'}">${slider.is_active == 1 ? 'Aktif' : 'Tidak Aktif'}</span></div>
                        </div>
                        <div>
                            <button class="btn btn-sm btn-action btn-edit" title="Edit"><i class="fas fa-edit"></i></button>
                            <button class="btn btn-sm btn-action btn-delete" title="Hapus"><i class="fas fa-trash"></i></button>
                        </div>`;
                    sliderList.appendChild(item);
                });
            } else {
                sliderList.innerHTML = '<p class="text-center text-muted">Belum ada slide.</p>';
            }
        } catch (error) {
            console.error("Fetch Error:", error);
            showToast('Gagal memuat data dari server.', 'danger');
        }
    };

    // === Event Listeners ===

    addSliderBtn.addEventListener('click', () => {
        sliderForm.reset();
        modalTitle.textContent = 'Tambah Slide Baru';
        document.getElementById('sliderId').value = '';
        document.getElementById('sliderImage').required = true;
        submitButton.textContent = 'Simpan';
        submitButton.disabled = false;
        sliderModal.show();
    });

    sliderList.addEventListener('click', async (e) => {
        // Menggunakan .closest() untuk menangkap klik pada tombol atau ikon di dalamnya
        const button = e.target.closest('.btn-edit, .btn-delete');
        if (!button) return;

        const item = button.closest('.list-group-item');
        const id = item.dataset.id;
        
        // --- PERBAIKAN DI SINI ---
        if (button.classList.contains('btn-edit')) {
            try {
                const response = await fetch(`slide_hero.php?id=${id}`);
                const slider = await response.json();
                if(slider && !slider.error) {
                    sliderForm.reset();
                    modalTitle.textContent = 'Edit Slide';
                    document.getElementById('sliderId').value = slider.id;
                    document.getElementById('sliderTitle').value = slider.title;
                    document.getElementById('sliderSubtitle').value = slider.subtitle;
                    document.getElementById('sliderIsActive').checked = (slider.is_active == 1);
                    document.getElementById('sliderImage').required = false;
                    submitButton.textContent = 'Simpan Perubahan';
                    submitButton.disabled = false;
                    sliderModal.show();
                } else {
                    showToast(slider.error || 'Gagal mengambil data slide.', 'danger');
                }
            } catch (error) {
                showToast('Terjadi kesalahan jaringan.', 'danger');
            }
        }
        
        // --- PERBAIKAN DI SINI & PENYEMPURNAAN ---
        if (button.classList.contains('btn-delete')) {
            // Menggunakan SweetAlert2 untuk konfirmasi yang lebih baik
            Swal.fire({
                title: 'Anda yakin?',
                text: "Slide yang dihapus tidak dapat dikembalikan!",
                icon: 'warning',
                showCancelButton: true,
                confirmButtonColor: '#d33',
                cancelButtonColor: '#3085d6',
                confirmButtonText: 'Ya, hapus!',
                cancelButtonText: 'Batal'
            }).then(async (result) => {
                if (result.isConfirmed) {
                    try {
                        const formData = new FormData();
                        formData.append('action', 'delete');
                        formData.append('id', id);
                        
                        const response = await fetch('slide_hero.php', { method: 'POST', body: formData });
                        const res = await response.json();
                        
                        showToast(res.message, res.success ? 'success' : 'danger');
                        if (res.success) {
                            // Hapus item dari tampilan secara langsung untuk efek instan
                            item.style.transition = 'opacity 0.5s';
                            item.style.opacity = '0';
                            setTimeout(() => item.remove(), 500);
                        }
                    } catch(error) {
                        showToast('Gagal menghubungi server.', 'danger');
                    }
                }
            });
        }
    });
    
    sliderForm.addEventListener('submit', async function(e) {
        e.preventDefault();
        const originalButtonText = submitButton.innerHTML;
        submitButton.disabled = true;
        submitButton.innerHTML = `<span class="spinner-border spinner-border-sm" role="status" aria-hidden="true"></span> Menyimpan...`;

        const formData = new FormData(this);
        // Menentukan action 'add' atau 'update' berdasarkan ada/tidaknya ID
        formData.append('action', document.getElementById('sliderId').value ? 'update' : 'add');

        try {
            const response = await fetch('slide_hero.php', { method: 'POST', body: formData });
            const result = await response.json();
            
            if (result.success) {
                listNeedsReload = true;
                sliderModal.hide();
                showToast(result.message, 'success');
            } else {
                showToast(result.message || 'Gagal menyimpan data.', 'danger');
            }
        } catch (error) {
            showToast('Terjadi kesalahan koneksi.', 'danger');
        } finally {
            submitButton.disabled = false;
            submitButton.innerHTML = originalButtonText;
        }
    });

    sliderModalEl.addEventListener('hidden.bs.modal', () => {
        if (listNeedsReload) {
            loadSliders();
            listNeedsReload = false;
        }
    });

    // === Inisialisasi Awal ===
    loadSliders();

    // Pastikan SweetAlert2 sudah dimuat sebelum digunakan
    if (typeof Swal === 'undefined') {
        console.error('SweetAlert2 belum dimuat. Pastikan Anda sudah menyertakan script-nya.');
    }
});