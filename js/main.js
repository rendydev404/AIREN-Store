// js/main.js
import { initUI, showToast } from "./ui.js";
import { initCart, addToCart } from "./cart.js";
import { initWishlist } from "./wishlist.js";
import { initProductFeatures } from "./product-features.js";

document.addEventListener("DOMContentLoaded", function () {
  // 1. Kumpulkan semua elemen DOM yang dibutuhkan oleh modul lain
  const wishlistElements = {
    wishlistModalBody: document.getElementById("wishlistModalBody"),
    emptyWishlistMessage: document.getElementById("emptyWishlistMessage"),
    clearWishlistBtn: document.getElementById("clearWishlistBtn"),
    wishlistCountBadge: document.getElementById("wishlistCountBadge"),
    wishlistModalEl: document.getElementById("wishlistModal"),
  };

  const cartElements = {
    cartModalBody: document.getElementById("cartModalBody"),
    emptyCartMessage: document.getElementById("emptyCartMessage"),
    checkoutBtn: document.getElementById("checkoutBtn"),
    cartCountBadge: document.getElementById("cartCountBadge"),
    cartModalEl: document.getElementById("cartModal"),
  };

  const productFeatureElements = {
    allProductItems: Array.from(
      document.querySelectorAll("#productList .product-item")
    ),
    productListContainer: document.getElementById("productList"),
    filterCategorySelect: document.getElementById("filterCategory"),
    minPriceInput: document.getElementById("minPrice"),
    maxPriceInput: document.getElementById("maxPrice"),
    sortProductsSelect: document.getElementById("sortProducts"),
    clearFiltersButton: document.getElementById("clearFiltersBtn"),
    searchInputNav: document.getElementById("searchInputNav"),
    searchInputMobile: document.getElementById("searchInputMobile"),
    loadMoreBtn: document.getElementById("loadMoreBtn"),
    noProductFoundMessage: document.getElementById("noProductFoundMessage"),
    quickViewModalEl: document.getElementById("quickViewModal"),
    quickViewImage: document.getElementById("quickViewImage"),
    quickViewModalLabel: document.getElementById("quickViewModalLabel"),
    quickViewDescription: document.getElementById("quickViewDescription"),
    quickViewPrice: document.getElementById("quickViewPrice"),
    quickViewStock: document.getElementById("quickViewStock"),
    quickViewQuantityInput: document.getElementById("quickViewQuantityInput"),
    quickViewAddToCartButton: document.getElementById(
      "quickViewAddToCartButton"
    ),
  };

  const hamburgerToggler = document.getElementById("hamburgerToggler");
  const mobileSidebar = document.getElementById("mobileSidebar");
  const sidebarOverlay = document.getElementById("sidebarOverlay");

  // Fungsi untuk membuka/menutup sidebar
  const toggleSidebar = () => {
    hamburgerToggler.classList.toggle("active");
    mobileSidebar.classList.toggle("active");
    sidebarOverlay.classList.toggle("active");
    // Mencegah body scroll saat sidebar terbuka
    document.body.style.overflow = mobileSidebar.classList.contains("active")
      ? "hidden"
      : "";
  };

  if (hamburgerToggler && mobileSidebar && sidebarOverlay) {
    // Klik tombol hamburger
    hamburgerToggler.addEventListener("click", toggleSidebar);

    // Klik overlay untuk menutup
    sidebarOverlay.addEventListener("click", toggleSidebar);
  }

  // 2. Inisialisasi setiap modul dengan variabel yang benar
  initUI();
  initWishlist(wishlistElements);
  const quickViewModalInstance = initProductFeatures(productFeatureElements);
  initCart(cartElements); // initCart TIDAK perlu instance modal lagi, karena listener dipusatkan di sini

  // ======================================================================
  // LISTENER KLIK TERPUSAT UNTUK SEMUA AKSI "ADD TO CART"
  // ======================================================================
  document.body.addEventListener("click", function (event) {
    const addToCartBtn = event.target.closest(".add-to-cart-btn");
    if (addToCartBtn) {
      event.preventDefault();
      const productId = addToCartBtn.dataset.id;
      let productData = { id: productId }; // Siapkan objek data

      const modal = addToCartBtn.closest("#quickViewModal");
      const card = addToCartBtn.closest(".product-item");

      if (modal) {
        // KASUS 1: Tombol di dalam Modal Quick View
        // Ambil data langsung dari elemen modal
        productData.quantity =
          parseInt(productFeatureElements.quickViewQuantityInput.value) || 1;
        productData.name =
          productFeatureElements.quickViewModalLabel.textContent;
        productData.price = parseFloat(
          productFeatureElements.quickViewAddToCartButton.dataset.price
        );
        productData.image = productFeatureElements.quickViewImage.src;
        productData.stock = parseInt(
          productFeatureElements.quickViewStock.textContent.replace(
            "Stok: ",
            ""
          )
        );

        if (addToCart(productData)) {
          quickViewModalInstance?.hide(); // Tutup modal jika berhasil
        }
      } else if (card) {
        // KASUS 2: Tombol di dalam Kartu Produk
        // Ambil data dari atribut data-* kartu produk
        productData.quantity = parseInt(
          card.querySelector(".quantity-input")?.value || 1
        );
        productData.name = card.dataset.name;
        productData.price = parseFloat(card.dataset.price);
        productData.image = card.dataset.image;
        productData.stock = parseInt(card.dataset.stock);

        addToCart(productData);
      }
    }
  });

  // --- Logika lain yang spesifik untuk halaman utama ---

  

  // [PERBAIKAN] Listener untuk checkout sekarang mengarahkan ke halaman baru
  if (cartElements.checkoutBtn) {
    cartElements.checkoutBtn.addEventListener("click", function () {
      // 1. Cek dulu apakah pengguna sudah login
      fetch("api/check_session.php")
        .then((response) => response.json())
        .then((sessionData) => {
          if (sessionData.loggedIn) {
            // 2. Jika sudah login, langsung arahkan ke halaman checkout
            window.location.href = "checkout.php";
          } else {
            // 3. Jika belum login, tampilkan pop-up untuk login
            Swal.fire({
              title: "Login Diperlukan",
              text: "Anda harus login untuk melanjutkan ke pembayaran.",
              icon: "warning",
              showCancelButton: true,
              confirmButtonText: "Login Sekarang!",
              cancelButtonText: "Nanti Saja",
            }).then((result) => {
              if (result.isConfirmed) {
                // Arahkan ke halaman login jika pengguna setuju
                window.location.href = "login.php";
              }
            });
          }
        })
        .catch((error) => {
          console.error("Error checking session:", error);
          Swal.fire(
            "Error",
            "Gagal memeriksa status login. Silakan coba lagi.",
            "error"
          );
        });
    });
  }

  // Listener untuk "Beli Sekarang"
  const productList = document.getElementById("productList");
  if (productList) {
    productList.addEventListener("click", function (e) {
      if (e.target.closest(".direct-checkout-btn")) {
        e.preventDefault();
        const productCard = e.target.closest(".product-item");
        const productId = productCard.dataset.id;
        const productName = productCard.dataset.name;
        const productPrice = parseFloat(productCard.dataset.price);
        const quantityInput = productCard.querySelector(".quantity-input");
        const quantity = parseInt(quantityInput.value);

        if (isNaN(quantity) || quantity < 1) {
          Swal.fire({ icon: "warning", title: "Jumlah Tidak Valid" });
          return;
        }

        const transactionData = {
          items: [
            {
              id: productId,
              name: productName,
              price: productPrice,
              quantity: quantity,
            },
          ],
          total: productPrice * quantity,
        };

        // Panggil server untuk mendapatkan Snap Token
        fetch("process_core_payment.php", {
          method: "POST",
          headers: { "Content-Type": "application/json" },
          body: JSON.stringify(transactionData),
        })
          .then((response) => response.json())
          .then((data) => {
            if (data.token) {
              window.snap.pay(data.token, {
                onSuccess: function (result) {
                  console.log("Payment Success:", result);
                  Swal.fire({
                    icon: "success",
                    title: "Pembayaran Berhasil!",
                    text: "Terima kasih, pesanan Anda sedang diproses.",
                    confirmButtonColor: "#28a745",
                  });
                },
                onPending: function (result) {
                  console.log("Payment Pending:", result);
                  Swal.fire({
                    icon: "info",
                    title: "Menunggu Pembayaran",
                    text: "Silakan selesaikan pembayaran sesuai metode yang dipilih.",
                    confirmButtonColor: "#17a2b8",
                  });
                },
                onError: function (result) {
                  console.log("Payment Error:", result);
                  Swal.fire({
                    icon: "error",
                    title: "Pembayaran Gagal",
                    text: "Terjadi kesalahan saat memproses transaksi. Silakan coba kembali.",
                    confirmButtonColor: "#dc3545",
                  });
                },
              });
            } else if (data.error) {
              Swal.fire({
                icon: "error",
                title: "Error",
                text: data.error,
                confirmButtonColor: "#6c757d",
              });
            }
          })
          .catch((error) => {
            console.error("Fetch Error:", error);
            alert("Terjadi kesalahan saat menghubungi server pembayaran.");
          });
      }
    });
  }
});
