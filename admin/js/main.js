document.addEventListener("DOMContentLoaded", function () {
  // === LOGIKA UNTUK SEMUA HALAMAN ADMIN ===

  // 1. Logika untuk Sidebar Mobile
  const mobileSidebarToggler = document.getElementById("mobileSidebarToggler");
  const sidebar = document.getElementById("adminSidebar");
  const sidebarOverlay = document.getElementById("sidebarOverlay");

  function toggleMobileSidebar() {
    if (sidebar && sidebarOverlay) {
      sidebar.classList.toggle("active");
      sidebarOverlay.classList.toggle("active");
    }
  }

  if (mobileSidebarToggler) {
    mobileSidebarToggler.addEventListener("click", toggleMobileSidebar);
  }
  if (sidebarOverlay) {
    sidebarOverlay.addEventListener("click", toggleMobileSidebar);
  }

  // 2. Logika untuk Ganti Tema (Dark/Light)
  const themeToggleBtn = document.getElementById("themeToggleBtn");
  const htmlEl = document.documentElement;

  function setTheme(theme) {
    htmlEl.setAttribute("data-theme", theme);
    localStorage.setItem("admin_theme", theme);
  }

  if (themeToggleBtn) {
    themeToggleBtn.addEventListener("click", function () {
      const currentTheme = htmlEl.getAttribute("data-theme") || "light";
      const newTheme = currentTheme === "light" ? "dark" : "light";
      setTheme(newTheme);
    });
  }

  // Terapkan tema yang tersimpan saat halaman dimuat
  const savedTheme = localStorage.getItem("admin_theme") || "light";
  setTheme(savedTheme);
});
