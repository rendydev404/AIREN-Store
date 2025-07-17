

// Fungsi untuk menginisialisasi AOS (Animate On Scroll)
function initAOS() {
  AOS.init({
    duration: 800,
    once: true,
  });
}

// Fungsi untuk mengganti tema (light/dark)
function initThemeSwitcher() {
  const themeToggleDesktopBtn = document.getElementById("themeToggleDesktopBtn");
  const themeToggleMobileBtn = document.getElementById("themeToggleMobileBtn");
  const currentTheme = localStorage.getItem("theme") || "light";
  document.body.setAttribute("data-theme", currentTheme);
  updateThemeButtonIcons(currentTheme);

  function updateThemeButtonIcons(theme) {
    const sunIconClass = "ri-sun-line";
    const moonIconClass = "ri-moon-clear-line";
    if (themeToggleDesktopBtn) {
      const desktopIcon = themeToggleDesktopBtn.querySelector("i");
      desktopIcon.classList.toggle(sunIconClass, theme === "light");
      desktopIcon.classList.toggle(moonIconClass, theme === "dark");
    }
    if (themeToggleMobileBtn) {
      const mobileIcon = themeToggleMobileBtn.querySelector("i");
      mobileIcon.classList.toggle(sunIconClass, theme === "light");
      mobileIcon.classList.toggle(moonIconClass, theme === "dark");
    }
  }

  function toggleTheme() {
    let theme = document.body.getAttribute("data-theme");
    theme = theme === "light" ? "dark" : "light";
    document.body.setAttribute("data-theme", theme);
    localStorage.setItem("theme", theme);
    updateThemeButtonIcons(theme);
  }

  if (themeToggleDesktopBtn)
    themeToggleDesktopBtn.addEventListener("click", toggleTheme);
  if (themeToggleMobileBtn)
    themeToggleMobileBtn.addEventListener("click", toggleTheme);
}

// Fungsi untuk scroll ke atas halaman
function initScrollToTop() {
  const scrollToTopButton = document.getElementById("scrollToTopBtn");
  if (scrollToTopButton) {
    window.onscroll = function () {
      if (
        document.body.scrollTop > 150 ||
        document.documentElement.scrollTop > 150
      ) {
        scrollToTopButton.classList.add("visible");
      } else {
        scrollToTopButton.classList.remove("visible");
      }
    };
    // Jadikan global agar bisa diakses dari onclick HTML
    window.scrollToTop = function () {
      window.scrollTo({ top: 0, behavior: "smooth" });
    };
  }
}
// --- IMAGE LAZY LOAD VISUAL CUE ---
  document.querySelectorAll(".card-img-top").forEach((img) => {
    if (img.complete) {
      img.classList.add("loaded");
    } else {
      img.addEventListener("load", () => img.classList.add("loaded"));
    }
  });

  
// Fungsi untuk menampilkan notifikasi Toast
export function showToast(message, type = "success") {
  const toastContainer = document.querySelector(".toast-container");
  if (!toastContainer) return;

  const toastId = "toast-" + Date.now();
  let toastHeaderClass = "bg-primary text-white"; // Default
  if (type === "error") toastHeaderClass = "bg-danger text-white";
  if (type === "info") toastHeaderClass = "bg-info text-dark";

  const toastHTML = `
      <div class="toast" role="alert" aria-live="assertive" aria-atomic="true" id="${toastId}" data-bs-delay="3000">
        <div class="toast-header ${toastHeaderClass}">
             <i class="fas ${
               type === "success"
                 ? "fa-check-circle"
                 : type === "error"
                 ? "fa-times-circle"
                 : "fa-info-circle"
             } me-2"></i>
          <strong class="me-auto">AIREN Store</strong>
          <small>Baru saja</small>
          <button type="button" class="btn-close btn-close-toast" data-bs-dismiss="toast" aria-label="Close"></button>
        </div>
        <div class="toast-body">
          ${message}
        </div>
      </div>
      `;
  toastContainer.insertAdjacentHTML("beforeend", toastHTML);

  const toastElement = document.getElementById(toastId);
  const toast = new bootstrap.Toast(toastElement);
  toast.show();

  toastElement.addEventListener("hidden.bs.toast", function () {
    toastElement.remove();
  });
}

// Ekspor fungsi inisialisasi utama untuk UI
export function initUI() {
  initAOS();
  initThemeSwitcher();
  initScrollToTop();
}