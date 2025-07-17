document.addEventListener("DOMContentLoaded", function () {
  // === LOGIKA KHUSUS UNTUK HALAMAN MANAJEMEN SLIDE ===

  const sliderList = document.getElementById("sliderList");
  const addSliderBtn = document.getElementById("addSliderBtn");
  const sliderModalEl = document.getElementById("sliderModal");

  // Hentikan eksekusi kode jika elemen-elemen ini tidak ada di halaman.
  // Ini mencegah error di halaman lain.
  if (!sliderList || !addSliderBtn || !sliderModalEl) {
    return;
  }

  const sliderModal = new bootstrap.Modal(sliderModalEl);
  const sliderForm = document.getElementById("sliderForm");
  const modalTitle = document.getElementById("sliderModalLabel");
  const submitButton = sliderForm.querySelector('button[type="submit"]');

  let listNeedsReload = false;

  const showToast = (message, type = "success") => {
    const toastContainer =
      document.querySelector(".toast-container") ||
      (() => {
        const container = document.createElement("div");
        container.className = "toast-container position-fixed top-0 end-0 p-3";
        document.body.appendChild(container);
        return container;
      })();

    const toastEl = document.createElement("div");
    toastEl.className = `toast align-items-center text-bg-${type} border-0`;
    toastEl.innerHTML = `<div class="d-flex"><div class="toast-body">${message}</div><button type="button" class="btn-close btn-close-white me-2 m-auto" data-bs-dismiss="toast" aria-label="Close"></button></div>`;

    toastContainer.appendChild(toastEl);
    const toast = new bootstrap.Toast(toastEl, { delay: 1500 });
    toast.show();
    toastEl.addEventListener("hidden.bs.toast", () => toastEl.remove());
  };

  const loadSliders = async () => {
    sliderList.innerHTML = `<div class="d-flex justify-content-center p-5"><div class="spinner-border" role="status"></div></div>`;
    try {
      const response = await fetch("slide_hero.php");
      const sliders = await response.json();

      sliderList.innerHTML = "";

      if (sliders && sliders.length > 0) {
        sliders.forEach((slider) => {
          const item = document.createElement("div");

          // --- PERUBAHAN DI SINI ---
          // Mengubah kelas agar bisa vertikal di mobile (flex-column) dan horizontal di desktop (flex-md-row)
          item.className =
            "list-group-item d-flex flex-column flex-md-row justify-content-between align-items-md-center";
          item.dataset.id = slider.id;

         item.innerHTML = `
    <div class="d-flex align-items-center">
        <img src="../uploads/hero/${slider.image}" alt="${slider.title}" class="me-3">
        <div>
            <h6 class="mb-1 fw-bold">${slider.title}</h6>
            <span class="badge bg-${slider.is_active == 1 ? 'success' : 'secondary'}">
                ${slider.is_active == 1 ? 'Aktif' : 'Tidak Aktif'}
            </span>
        </div>
    </div>

    <div>
        <button class="btn btn-sm btn-action btn-edit" title="Edit"><i class="fas fa-edit"></i></button>
        <button class="btn btn-sm btn-action btn-delete" title="Hapus"><i class="fas fa-trash"></i></button>
    </div>
`;
          sliderList.appendChild(item);
        });
      } else {
        sliderList.innerHTML =
          '<p class="text-center text-muted">Belum ada slide.</p>';
      }
    } catch (error) {
      console.error("Fetch error:", error); // Tambahkan console.error untuk debug
      showToast("Gagal memuat data dari server.", "danger");
    }
  };

  addSliderBtn.addEventListener("click", () => {
    sliderForm.reset();
    modalTitle.textContent = "Tambah Slide Baru";
    document.getElementById("sliderId").value = "";
    document.getElementById("sliderImage").required = true;
    submitButton.textContent = "Simpan";
    submitButton.disabled = false;
    sliderModal.show();
  });

  sliderList.addEventListener("click", async (e) => {
    const button = e.target.closest(".btn-edit, .btn-delete"); // Koreksi: selektor lebih spesifik
    if (!button) return;

    const id = button.closest(".list-group-item").dataset.id;

    if (button.classList.contains("btn-edit")) {
      // Koreksi: gunakan nama kelas yang benar
      const response = await fetch(`slide_hero.php?id=${id}`);
      const slider = await response.json();
      if (slider) {
        sliderForm.reset();
        modalTitle.textContent = "Edit Slide";
        document.getElementById("sliderId").value = slider.id;
        document.getElementById("sliderTitle").value = slider.title;
        document.getElementById("sliderSubtitle").value = slider.subtitle;
        document.getElementById("sliderIsActive").checked =
          slider.is_active == 1;
        document.getElementById("sliderImage").required = false;
        submitButton.textContent = "Simpan Perubahan";
        submitButton.disabled = false;
        sliderModal.show();
      }
    }

    if (button.classList.contains("btn-delete")) {
      // Koreksi: gunakan nama kelas yang benar
      if (confirm("Anda yakin ingin menghapus slide ini?")) {
        const formData = new FormData();
        formData.append("action", "delete");
        formData.append("id", id);
        const response = await fetch("slide_hero.php", {
          method: "POST",
          body: formData,
        });
        const result = await response.json();
        showToast(result.message, result.success ? "success" : "danger");
        if (result.success) loadSliders();
      }
    }
  });

  sliderForm.addEventListener("submit", async function (e) {
    e.preventDefault();
    submitButton.disabled = true;

    const formData = new FormData(this);
    formData.append("action", formData.get("id") ? "update" : "add");

    const response = await fetch("slide_hero.php", {
      method: "POST",
      body: formData,
    });
    const result = await response.json();

    if (result.success) {
      listNeedsReload = true;
      sliderModal.hide();
      showToast(result.message, "success");
    } else {
      showToast(result.message || "Gagal menyimpan.", "danger");
      submitButton.disabled = false;
    }
  });

  sliderModalEl.addEventListener("hidden.bs.modal", () => {
    if (listNeedsReload) {
      loadSliders();
      listNeedsReload = false;
    }
  });

  loadSliders();
});
