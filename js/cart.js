// js/cart.js
import { showToast } from "./ui.js";

let cart = JSON.parse(localStorage.getItem("airenCart")) || [];
let cartElements = {};

function saveCart() {
  localStorage.setItem("airenCart", JSON.stringify(cart));
}

function updateCartUI() {
    // Ambil kedua elemen badge, untuk desktop dan mobile
    const cartCountBadgeDesktop = document.getElementById('cartCountBadge');
    const cartCountBadgeMobile = document.getElementById('cartCountBadgeMobile');
    const checkoutBtn = document.getElementById('checkoutBtn');
    const emptyCartMessage = document.getElementById('emptyCartMessage');

    const totalItems = cart.reduce((sum, item) => sum + item.quantity, 0);

    const updateBadge = (badgeElement) => {
        if (badgeElement) {
            if (totalItems > 0) {
                badgeElement.textContent = totalItems;
                badgeElement.style.display = 'inline-block';
            } else {
                badgeElement.style.display = 'none';
            }
        }
    };

    // Panggil fungsi update untuk kedua badge
    updateBadge(cartCountBadgeDesktop);
    updateBadge(cartCountBadgeMobile);

    // Atur visibilitas tombol checkout dan pesan kosong
    if (checkoutBtn) {
        checkoutBtn.style.display = totalItems > 0 ? 'block' : 'none';
    }
    if (emptyCartMessage) {
        emptyCartMessage.style.display = totalItems > 0 ? 'none' : 'block';
    }
}

function renderCartModal() {
  const { cartModalBody, emptyCartMessage } = cartElements;
  cartModalBody.innerHTML = "";
  let totalHarga = 0;

  if (cart.length === 0) {
    if(emptyCartMessage) cartModalBody.appendChild(emptyCartMessage);
    updateCartUI();
    return;
  }

  cart.forEach((item) => {
    totalHarga += item.price * item.quantity;
    const itemHTML = `
      <div class="d-flex justify-content-between align-items-center mb-3 cart-item" data-id="${item.id}">
        <div class="d-flex align-items-center">
          <img src="${item.image}" alt="${item.name}" style="width: 70px; height: 70px; object-fit: cover;" class="rounded me-3">
          <div>
            <h6 class="mb-0 text-capitalize">${item.name}</h6>
            <small class="text-muted">Rp${item.price.toLocaleString("id-ID")}</small>
          </div>
        </div>
        <div class="d-flex align-items-center">
           <div class="input-group quantity-input-group" style="width: 120px;">
              <button class="btn btn-outline-secondary quantity-minus-cart" type="button">-</button>
              <input type="number" class="form-control quantity-input-cart text-center" value="${item.quantity}" min="1" data-id="${item.id}">
              <button class="btn btn-outline-secondary quantity-plus-cart" type="button">+</button>
           </div>
           <button class="btn btn-sm btn-danger ms-3 remove-from-cart-btn" data-id="${item.id}"><i class="fas fa-trash"></i></button>
        </div>
      </div>`;
    cartModalBody.insertAdjacentHTML("beforeend", itemHTML);
  });

  cartModalBody.insertAdjacentHTML("beforeend", `<hr><div class="d-flex justify-content-end"><h5 class="me-3">Total:</h5><h5>Rp${totalHarga.toLocaleString("id-ID")}</h5></div>`);
  updateCartUI();
}

function updateCartItemQuantity(productId, newQuantity) {
  const item = cart.find((item) => item.id === productId);
  if (!item) return;

  const productElement = document.querySelector(`.product-item[data-id="${productId}"]`);
  const stock = productElement ? parseInt(productElement.dataset.stock) : item.stock || item.quantity;

  if (newQuantity > 0) {
    if (newQuantity > stock) {
      showToast(`Stok untuk ${item.name} hanya tersisa ${stock}.`, "error");
      const input = cartElements.cartModalBody.querySelector(`.quantity-input-cart[data-id="${productId}"]`);
      if (input) input.value = stock;
      item.quantity = stock;
    } else {
      item.quantity = newQuantity;
    }
  } else {
    // Jika jumlah menjadi 0 atau kurang, hapus item
    removeCartItem(productId);
  }
  saveCart();
  renderCartModal();
}

function removeCartItem(productId) {
  cart = cart.filter((item) => item.id !== productId);
  saveCart();
  renderCartModal();
  showToast("Produk dihapus dari keranjang.", "info");
}

// [PERBAIKAN] Fungsi `addToCart` sekarang menerima objek data lengkap dan menjadi lebih tangguh
export function addToCart(productData) {
    if (!productData || !productData.id || productData.price === undefined) {
        console.error("Data produk tidak lengkap untuk ditambahkan ke keranjang.", productData);
        return false;
    }
    
    const { id, name, price, image, quantity, stock } = productData;
    const existingItem = cart.find((item) => item.id === id);

    if (existingItem) {
        const newQuantity = existingItem.quantity + quantity;
        if (newQuantity > stock) {
            showToast(`Stok tidak mencukupi. Anda hanya bisa menambahkan ${stock - existingItem.quantity} lagi.`, "error");
            return false;
        }
        existingItem.quantity = newQuantity;
        showToast(`Jumlah ${name} di keranjang diperbarui.`, "info");
    } else {
        if (quantity > stock) {
            showToast("Stok produk tidak mencukupi.", "error");
            return false;
        }
        cart.push({
            id: id,
            name: name,
            price: price,
            image: image,
            quantity: quantity,
            stock: stock // Simpan juga stok untuk referensi
        });
        showToast(`${name} ditambahkan ke keranjang!`, "success");
    }

    saveCart();
    updateCartUI();
    renderCartModal();
    return true;
}

// initCart sekarang hanya bertugas menginisialisasi elemen dan listener di dalam modal keranjang
export function initCart(elements) {
  cartElements = elements;
  const { cartModalBody, cartModalEl } = cartElements;

  // Listener untuk membuka modal keranjang
  if (cartModalEl) {
    cartModalEl.addEventListener("show.bs.modal", renderCartModal);
  }

  // Listener untuk interaksi di dalam modal keranjang (+, -, hapus)
  cartModalBody.addEventListener("click", (e) => {
    const target = e.target;
    const itemDiv = target.closest(".cart-item");
    if (!itemDiv) return;
    const productId = itemDiv.dataset.id;

    if (target.classList.contains("quantity-plus-cart")) {
      const input = target.previousElementSibling;
      input.stepUp();
      updateCartItemQuantity(productId, parseInt(input.value));
    } else if (target.classList.contains("quantity-minus-cart")) {
      const input = target.nextElementSibling;
      input.stepDown();
      updateCartItemQuantity(productId, parseInt(input.value));
    } else if (target.closest(".remove-from-cart-btn")) {
      removeCartItem(productId);
    }
  });

  cartModalBody.addEventListener("change", (e) => {
    if (e.target.classList.contains("quantity-input-cart")) {
      updateCartItemQuantity(e.target.dataset.id, parseInt(e.target.value));
    }
  });

  updateCartUI(); // Panggil update UI di awal
}