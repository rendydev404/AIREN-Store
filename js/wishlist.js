// js/wishlist.js
import { showToast } from "./ui.js";

// State dan Elemen DOM untuk modul ini
let wishlist = JSON.parse(localStorage.getItem("parfumkuWishlist")) || [];
let wishlistElements = {};

// Memperbarui ikon di kartu produk dan badge di navbar
function updateWishlistIconAndBadge() {
  const { wishlistCountBadge } = wishlistElements;
   const wishlistCountBadgeMobile = document.getElementById('wishlistCountBadgeMobile'); // Tambahkan ini


  // Update ikon hati di setiap kartu produk
  document.querySelectorAll(".wishlist-btn").forEach((btn) => {
    const productId = btn.dataset.productId;
    const icon = btn.querySelector("i");
    if (wishlist.includes(productId)) {
      icon.classList.remove("far");
      icon.classList.add("fas", "text-danger");
    } else {
      icon.classList.remove("fas", "text-danger");
      icon.classList.add("far");
    }
  });

 if (wishlist.length > 0) {
    if (wishlistCountBadge) { // Untuk desktop
        wishlistCountBadge.textContent = wishlist.length;
        wishlistCountBadge.style.display = 'inline-block';
    }
    if (wishlistCountBadgeMobile) { // Untuk mobile
        wishlistCountBadgeMobile.textContent = wishlist.length;
        wishlistCountBadgeMobile.style.display = 'inline-block';
    }
  } else {
    if (wishlistCountBadge) wishlistCountBadge.style.display = 'none';
    if (wishlistCountBadgeMobile) wishlistCountBadgeMobile.style.display = 'none';
  }
}

// Merender item di dalam modal wishlist
function renderWishlistModal() {
  const { wishlistModalBody, emptyWishlistMessage, clearWishlistBtn } =
    wishlistElements;
  if (!wishlistModalBody) return;

  wishlistModalBody.innerHTML = ""; // Kosongkan item

  if (wishlist.length === 0) {
    wishlistModalBody.appendChild(emptyWishlistMessage);
    emptyWishlistMessage.style.display = "block";
    if (clearWishlistBtn) clearWishlistBtn.style.display = "none";
    return;
  }

  emptyWishlistMessage.style.display = "none";
  if (clearWishlistBtn) clearWishlistBtn.style.display = "inline-block";

  wishlist.forEach((productId) => {
    const productElement = document.querySelector(
      `.product-item[data-id="${productId}"]`
    );
    if (productElement) {
      const name = productElement.dataset.name;
      const price = parseFloat(productElement.dataset.price);
      const image = productElement.dataset.image;

      const itemHTML = `
        <div class="recent-wishlist-item" data-product-id="${productId}">
          <img src="${image}" alt="${name}">
          <div class="recent-wishlist-item-info">
            <h6 class="text-capitalize">${name}</h6>
            <p>Rp${price.toLocaleString("id-ID")}</p>
          </div>
          <button class="btn btn-sm btn-remove-wishlist" data-product-id="${productId}" title="Hapus dari Wishlist">
            <i class="fas fa-times-circle"></i>
          </button>
        </div>`;
      wishlistModalBody.insertAdjacentHTML("beforeend", itemHTML);
    }
  });

  // Tambahkan event listener untuk tombol hapus di dalam modal
  wishlistModalBody
    .querySelectorAll(".btn-remove-wishlist")
    .forEach((btn) => {
      btn.addEventListener("click", function () {
        toggleWishlist(this.dataset.productId);
        renderWishlistModal(); // Re-render setelah menghapus
      });
    });
}

// Menambah atau menghapus item dari wishlist
function toggleWishlist(productId) {
  const productIndex = wishlist.indexOf(productId);
  const productElement = document.querySelector(
    `.product-item[data-id="${productId}"]`
  );
  let productName = "Produk";
  if (productElement) {
    productName = productElement.dataset.name;
  }

  if (productIndex > -1) {
    wishlist.splice(productIndex, 1);
    showToast(`${productName} dihapus dari wishlist.`);
  } else {
    wishlist.push(productId);
    showToast(`${productName} ditambahkan ke wishlist!`);
  }
  localStorage.setItem("parfumkuWishlist", JSON.stringify(wishlist));
  updateWishlistIconAndBadge();
}

// Fungsi inisialisasi utama untuk modul Wishlist
export function initWishlist(elements) {
  wishlistElements = elements;
  const { wishlistModalEl, clearWishlistBtn } = wishlistElements;

  // Event listener untuk tombol wishlist di kartu produk (delegasi)
  document.body.addEventListener("click", function (event) {
    const wishlistBtn = event.target.closest(".wishlist-btn");
    if (wishlistBtn) {
      toggleWishlist(wishlistBtn.dataset.productId);
    }
  });

  // Event listener untuk tombol "Kosongkan Wishlist"
  if (clearWishlistBtn) {
    clearWishlistBtn.addEventListener("click", () => {
      if (confirm("Anda yakin ingin mengosongkan wishlist?")) {
        wishlist = [];
        localStorage.removeItem("parfumkuWishlist");
        updateWishlistIconAndBadge();
        renderWishlistModal();
        showToast("Wishlist telah dikosongkan.");
      }
    });
  }

  // Render modal saat ditampilkan
  if (wishlistModalEl) {
    wishlistModalEl.addEventListener("show.bs.modal", renderWishlistModal);
  }

  // Panggil update UI saat pertama kali halaman dimuat
  updateWishlistIconAndBadge();
}