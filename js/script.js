document.addEventListener("DOMContentLoaded", function () {
  const productListContainer = document.getElementById("productList");
  const initialDisplayCount = 8; // Harus sama dengan $product_display_limit_initial di PHP
  let currentDisplayedCount = initialDisplayCount;
  const allProductItems = Array.from(
    document.querySelectorAll("#productList .product-item")
  );

  // Initialize AOS
  AOS.init({
    duration: 800,
    once: true,
  });

  // --- THEME SWITCHER ---
  const themeToggleDesktopBtn = document.getElementById(
    "themeToggleDesktopBtn"
  );
  const themeToggleMobileBtn = document.getElementById("themeToggleMobileBtn");
  const currentTheme = localStorage.getItem("theme") || "light";
  document.body.setAttribute("data-theme", currentTheme);
  updateThemeButtonIcons(currentTheme);

  function updateThemeButtonIcons(theme) {
    const sunIconClass = "fa-sun";
    const moonIconClass = "fa-moon";
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

  // --- SMOOTH SCROLL & ACTIVE NAV ---
  document.querySelectorAll('a.nav-link[href^="#"]').forEach((anchor) => {
    anchor.addEventListener("click", function (e) {
      e.preventDefault();
      const targetId = this.getAttribute("href");
      const targetElement = document.querySelector(targetId);
      if (targetElement) {
        const navbarHeight = document.querySelector(".navbar").offsetHeight;
        const targetPosition =
          targetElement.getBoundingClientRect().top +
          window.pageYOffset -
          navbarHeight -
          10;
        window.scrollTo({ top: targetPosition, behavior: "smooth" });
      }
    });
  });

  const sections = document.querySelectorAll("main section[id], footer[id]");
  const navLinks = document.querySelectorAll(".navbar-nav .nav-item .nav-link");
  window.addEventListener("scroll", () => {
    let currentSectionId = "";
    const navbarHeight = document.querySelector(".navbar").offsetHeight;
    sections.forEach((section) => {
      const sectionTop = section.offsetTop - navbarHeight - 50; // Adjusted offset
      if (window.pageYOffset >= sectionTop) {
        currentSectionId = section.getAttribute("id");
      }
    });

    navLinks.forEach((link) => {
      link.classList.remove("active");
      if (link.getAttribute("href").includes(currentSectionId)) {
        link.classList.add("active");
      }
    });
    if (
      !currentSectionId &&
      window.pageYOffset < sections[0].offsetTop - navbarHeight - 20
    ) {
      document
        .querySelector('.navbar-nav .nav-item .nav-link[href="#"]')
        .classList.add("active");
    }
  });

  // --- IMAGE LAZY LOAD VISUAL CUE ---
  document.querySelectorAll(".card-img-top").forEach((img) => {
    if (img.complete) {
      img.classList.add("loaded");
    } else {
      img.addEventListener("load", () => img.classList.add("loaded"));
    }
  });

  // --- QUANTITY INPUT & PRICE UPDATE (CARD & MODAL) ---
//   function updateProductPriceDisplay(inputElement) {
//     const productCard = inputElement.closest(".card-body");
//     if (!productCard) return; // Only for product cards

//     const priceValueSpan = productCard.querySelector(".price-value");
//     const basePrice = parseFloat(priceValueSpan.dataset.basePrice);
//     let quantity = parseInt(inputElement.value);

//     if (isNaN(quantity) || quantity < 1) {
//       quantity = 1;
//       inputElement.value = 1;
//     }

//     const stock = parseInt(inputElement.getAttribute("max"));
//     if (quantity > stock) {
//       quantity = stock;
//       inputElement.value = stock;
//     }

//     const totalPrice = basePrice * quantity;
//     priceValueSpan.textContent = totalPrice.toLocaleString("id-ID");
//   }

//   function updateModalPriceAndButton(modalQuantityInput) {
//     const quickViewModal = document.getElementById("quickViewModal");
//     const priceDisplay = quickViewModal.querySelector("#quickViewPrice");
//     const waButton = quickViewModal.querySelector("#quickViewWaButton");
//     const basePrice = parseFloat(waButton.dataset.price); // Base price is on WA button
//     const stock = parseInt(modalQuantityInput.getAttribute("max"));

//     let quantity = parseInt(modalQuantityInput.value);

//     if (isNaN(quantity) || quantity < 1) {
//       quantity = 1;
//     }
//     if (quantity > stock) {
//       quantity = stock;
//     }
//     modalQuantityInput.value = quantity; // Correct the input if needed

//     const totalPrice = basePrice * quantity;
//     priceDisplay.textContent = "Rp" + totalPrice.toLocaleString("id-ID");
//     // WA button data will be updated when sending message
//   }

//   document.body.addEventListener("click", function (event) {
//     const target = event.target;
//     let quantityInput;

//     if (target.classList.contains("quantity-plus")) {
//       quantityInput = target.previousElementSibling;
//     } else if (target.classList.contains("quantity-minus")) {
//       quantityInput = target.nextElementSibling;
//     }

//     if (quantityInput && quantityInput.classList.contains("quantity-input")) {
//       const currentValue = parseInt(quantityInput.value);
//       const maxStock = parseInt(quantityInput.getAttribute("max"));

//       if (
//         target.classList.contains("quantity-plus") &&
//         currentValue < maxStock
//       ) {
//         quantityInput.stepUp();
//       } else if (
//         target.classList.contains("quantity-minus") &&
//         currentValue > 1
//       ) {
//         quantityInput.stepDown();
//       }

//       if (quantityInput.classList.contains("modal-quantity-input")) {
//         updateModalPriceAndButton(quantityInput);
//       } else {
//         updateProductPriceDisplay(quantityInput);
//       }
//     }
//   });

//   document.body.addEventListener("input", function (event) {
//     const target = event.target;
//     if (target.classList.contains("quantity-input")) {
//       if (target.classList.contains("modal-quantity-input")) {
//         updateModalPriceAndButton(target);
//       } else {
//         updateProductPriceDisplay(target);
//       }
//     }
//   });

 // =================================================================================
  // --- FUNGSI UTAMA KERANJANG BELANJA (SHOPPING CART) ---
  // =================================================================================
  let cart = JSON.parse(localStorage.getItem("airenCart")) || [];
  /**
   * Menambahkan produk ke keranjang atau memperbarui jumlah jika sudah ada.
   */
  function addToCart(productId, quantity) {
    const productElement = document.querySelector(
      `.product-item[data-id="${productId}"]`
    );
    if (!productElement) {
      console.error("Product element not found for ID:", productId);
      return;
    }
    const stock = parseInt(productElement.dataset.stock);

    const existingItem = cart.find((item) => item.id === productId);

    if (existingItem) {
      // Jika produk sudah ada di keranjang, update jumlahnya
      const newQuantity = existingItem.quantity + quantity;
      if (newQuantity > stock) {
        showToast(
          `Stok tidak mencukupi. Anda hanya bisa menambahkan ${
            stock - existingItem.quantity
          } lagi.`,
          "error"
        );
        return;
      }
      existingItem.quantity = newQuantity;
      showToast(
        `Jumlah ${productElement.dataset.name} di keranjang diperbarui.`,
        "info"
      );
    } else {
      // Jika produk belum ada, tambahkan sebagai item baru
      if (quantity > stock) {
        showToast("Stok produk tidak mencukupi.", "error");
        return;
      }
      cart.push({
        id: productId,
        name: productElement.dataset.name,
        price: parseFloat(productElement.dataset.price),
        image: productElement.dataset.image,
        quantity: quantity,
      });
      showToast(
        `${productElement.dataset.name} ditambahkan ke keranjang!`,
        "success"
      );
    }

    saveCart();
    updateCartUI();
    renderCartModal(); // Re-render isi modal keranjang
  }

  /**
   * Menyimpan data keranjang ke localStorage
   */
  function saveCart() {
    localStorage.setItem("airenCart", JSON.stringify(cart));
  }

  /**
   * Memperbarui tampilan UI keranjang (badge di navbar, tombol checkout)
   */
  function updateCartUI() {
    if (cart.length > 0) {
      cartCountBadge.textContent = cart.reduce(
        (sum, item) => sum + item.quantity,
        0
      );
      cartCountBadge.style.display = "inline-block";
      checkoutBtn.style.display = "block";
      emptyCartMessage.style.display = "none";
    } else {
      cartCountBadge.style.display = "none";
      checkoutBtn.style.display = "none";
      emptyCartMessage.style.display = "block";
    }
  }

  /**
   * Merender item di dalam modal keranjang belanja
   */
  function renderCartModal() {
    cartModalBody.innerHTML = ""; // Kosongkan dulu
    let totalHarga = 0;

    if (cart.length === 0) {
      cartModalBody.appendChild(emptyCartMessage);
      updateCartUI();
      return;
    }

    cart.forEach((item) => {
      totalHarga += item.price * item.quantity;
      const itemHTML = `
        <div class="d-flex justify-content-between align-items-center mb-3 cart-item" data-id="${
          item.id
        }">
          <div class="d-flex align-items-center">
            <img src="${item.image}" alt="${
        item.name
      }" style="width: 70px; height: 70px; object-fit: cover;" class="rounded me-3">
            <div>
              <h6 class="mb-0 text-capitalize">${item.name}</h6>
              <small class="text-muted">Rp${item.price.toLocaleString(
                "id-ID"
              )}</small>
            </div>
          </div>
          <div class="d-flex align-items-center">
             <div class="input-group quantity-input-group" style="width: 120px;">
                <button class="btn btn-outline-secondary quantity-minus-cart" type="button">-</button>
                <input type="number" class="form-control quantity-input-cart text-center" value="${
                  item.quantity
                }" min="1" data-id="${item.id}">
                <button class="btn btn-outline-secondary quantity-plus-cart" type="button">+</button>
             </div>
             <button class="btn btn-sm btn-danger ms-3 remove-from-cart-btn" data-id="${
               item.id
             }"><i class="fas fa-trash"></i></button>
          </div>
        </div>
      `;
      cartModalBody.insertAdjacentHTML("beforeend", itemHTML);
    });

    // Tambahkan Total
    cartModalBody.insertAdjacentHTML(
      "beforeend",
      `<hr><div class="d-flex justify-content-end"><h5 class="me-3">Total:</h5><h5>Rp${totalHarga.toLocaleString(
        "id-ID"
      )}</h5></div>`
    );
    updateCartUI();
  }

  /**
   * Mengubah jumlah item di keranjang
   */
  function updateCartItemQuantity(productId, newQuantity) {
    const item = cart.find((item) => item.id === productId);
    const productElement = document.querySelector(
      `.product-item[data-id="${productId}"]`
    );
    const stock = parseInt(productElement.dataset.stock);

    if (item && newQuantity > 0) {
      if (newQuantity > stock) {
        showToast(`Stok untuk ${item.name} hanya tersisa ${stock}.`, "error");
        // Reset input value di DOM
        const input = cartModalBody.querySelector(
          `.quantity-input-cart[data-id="${productId}"]`
        );
        if (input) input.value = stock;
        item.quantity = stock;
      } else {
        item.quantity = newQuantity;
      }
    }
    saveCart();
    renderCartModal();
  }

  /**
   * Menghapus item dari keranjang
   */
  function removeCartItem(productId) {
    cart = cart.filter((item) => item.id !== productId);
    saveCart();
    renderCartModal();
    showToast("Produk dihapus dari keranjang.", "info");
  }

  // Event listener untuk semua fungsionalitas di dalam MODAL KERANJANG
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
    } else if (target.classList.contains("remove-from-cart-btn")) {
      removeCartItem(productId);
    }
  });

  cartModalBody.addEventListener("change", (e) => {
    if (e.target.classList.contains("quantity-input-cart")) {
      updateCartItemQuantity(
        e.target.dataset.id,
        parseInt(e.target.value)
      );
    }
  });

  // Event listener untuk tombol 'Tambah ke Keranjang' (di modal produk atau kartu produk)
  document.body.addEventListener("click", function (event) {
    const addToCartBtn = event.target.closest(".add-to-cart-btn");
    if (addToCartBtn) {
      const productId = addToCartBtn.dataset.id;
      let quantity = 1;

      // Cek apakah tombol ada di dalam modal atau kartu produk
      const modal = addToCartBtn.closest("#quickViewModal");
      if (modal) {
        const quantityInput = modal.querySelector("#quickViewQuantityInput");
        quantity = parseInt(quantityInput.value);
      } else {
        const card = addToCartBtn.closest(".product-item");
        if (card) {
          const quantityInput = card.querySelector(".quantity-input");
          quantity = parseInt(quantityInput.value);
        }
      }

      if (isNaN(quantity) || quantity < 1) {
        quantity = 1;
      }
      addToCart(productId, quantity);
      if (modal) {
        quickViewModal.hide(); // Tutup modal quick view setelah menambahkan
      }
    }
  });

  // Panggil render saat modal keranjang dibuka
  const cartModalEl = document.getElementById("cartModal");
  if (cartModalEl) {
    cartModalEl.addEventListener("show.bs.modal", renderCartModal);
  }

  // =================================================================================
  // --- FUNGSI QUICK VIEW MODAL (Tampilan Cepat Produk) ---
  // =================================================================================

  const quickViewImage = document.getElementById("quickViewImage");
  const quickViewModalLabel = document.getElementById("quickViewModalLabel");
  const quickViewDescription = document.getElementById("quickViewDescription");
  const quickViewPrice = document.getElementById("quickViewPrice");
  const quickViewStock = document.getElementById("quickViewStock");
  const quickViewQuantityInput = document.getElementById(
    "quickViewQuantityInput"
  );
  // **PERBAIKAN KUNCI**: Gunakan tombol yang benar
  const quickViewAddToCartButton = document.getElementById(
    "quickViewAddToCartButton"
  );

  document.body.addEventListener("click", function (event) {
    const quickViewBtn = event.target.closest(".btn-quick-view");
    if (quickViewBtn) {
      const productId = quickViewBtn.dataset.productId;
      const productItem = document.querySelector(
        `.product-item[data-id="${productId}"]`
      );

      if (productItem) {
        const name = productItem.dataset.name;
        const price = parseFloat(productItem.dataset.price);
        const stock = parseInt(productItem.dataset.stock);

        quickViewModalLabel.textContent = name
          .split(" ")
          .map((word) => word.charAt(0).toUpperCase() + word.slice(1))
          .join(" ");
        quickViewImage.src = productItem.dataset.image;
        quickViewImage.alt = name;
        quickViewDescription.textContent = productItem.dataset.description;
        quickViewPrice.textContent = "Rp" + price.toLocaleString("id-ID");
        quickViewStock.textContent = `Stok: ${stock}`;

        quickViewQuantityInput.value = 1;
        quickViewQuantityInput.setAttribute("max", stock);

        // **PERBAIKAN KUNCI**: Set data ke tombol yang benar
        quickViewAddToCartButton.dataset.name = name;
        quickViewAddToCartButton.dataset.price = price;
        quickViewAddToCartButton.dataset.id = productId;

        const qtyMinusButtonInModal =
          quickViewModalEl.querySelector(".quantity-minus");
        const qtyPlusButtonInModal =
          quickViewModalEl.querySelector(".quantity-plus");

        if (stock === 0) {
          quickViewQuantityInput.disabled = true;
          quickViewAddToCartButton.disabled = true;
          quickViewAddToCartButton.innerHTML =
            '<i class="fas fa-times-circle"></i> Stok Habis';
          if (qtyMinusButtonInModal) qtyMinusButtonInModal.disabled = true;
          if (qtyPlusButtonInModal) qtyPlusButtonInModal.disabled = true;
        } else {
          quickViewQuantityInput.disabled = false;
          quickViewAddToCartButton.disabled = false;
          quickViewAddToCartButton.innerHTML =
            '<i class="fas fa-shopping-cart"></i> Tambah ke Keranjang';
          if (qtyMinusButtonInModal) qtyMinusButtonInModal.disabled = false;
          if (qtyPlusButtonInModal) qtyPlusButtonInModal.disabled = false;
        }

        quickViewModal.show();
      }
    }
  });

  // =================================================================================
  // --- FUNGSI KONTROL KUANTITAS & HARGA (Kartu Produk & Modal) ---
  // =================================================================================

  /**
   * Memperbarui total harga di kartu produk utama (bukan di modal)
   */
  function updateProductPriceDisplay(inputElement) {
    // Fungsi ini tetap sama seperti kode asli Anda
    const productCard = inputElement.closest(".card-body");
    if (!productCard) return;
    const priceValueSpan = productCard.querySelector(".price-value");
    const basePrice = parseFloat(priceValueSpan.dataset.basePrice);
    let quantity = parseInt(inputElement.value);
    if (isNaN(quantity) || quantity < 1) {
      quantity = 1;
      inputElement.value = 1;
    }
    const stock = parseInt(inputElement.getAttribute("max"));
    if (quantity > stock) {
      quantity = stock;
      inputElement.value = stock;
    }
    const totalPrice = basePrice * quantity;
    priceValueSpan.textContent = totalPrice.toLocaleString("id-ID");
  }

  /**
   * **PERBAIKAN KUNCI**: Memperbarui total harga di dalam modal Quick View
   */
  function updateModalPriceDisplay(modalQuantityInput) {
    const priceDisplay = quickViewModalEl.querySelector("#quickViewPrice");
    // **PERBAIKAN**: Ambil base price dari tombol yang benar
    const basePrice = parseFloat(quickViewAddToCartButton.dataset.price);
    const stock = parseInt(modalQuantityInput.getAttribute("max"));

    let quantity = parseInt(modalQuantityInput.value);

    if (isNaN(quantity) || quantity < 1) {
      quantity = 1;
    }
    if (quantity > stock) {
      quantity = stock;
    }
    modalQuantityInput.value = quantity;

    const totalPrice = basePrice * quantity;
    priceDisplay.textContent = "Rp" + totalPrice.toLocaleString("id-ID");
  }

  // Event listener untuk tombol plus/minus kuantitas
  document.body.addEventListener("click", function (event) {
    const target = event.target;
    let quantityInput;

    if (
      target.classList.contains("quantity-plus") &&
      !target.classList.contains("quantity-plus-cart")
    ) {
      quantityInput = target.previousElementSibling;
    } else if (
      target.classList.contains("quantity-minus") &&
      !target.classList.contains("quantity-minus-cart")
    ) {
      quantityInput = target.nextElementSibling;
    }

    if (quantityInput && quantityInput.classList.contains("quantity-input")) {
      const currentValue = parseInt(quantityInput.value);
      const maxStock = parseInt(quantityInput.getAttribute("max"));

      if (
        target.classList.contains("quantity-plus") &&
        currentValue < maxStock
      ) {
        quantityInput.stepUp();
      } else if (
        target.classList.contains("quantity-minus") &&
        currentValue > 1
      ) {
        quantityInput.stepDown();
      }

      // **PERBAIKAN**: Panggil fungsi yang benar
      if (quantityInput.classList.contains("modal-quantity-input")) {
        updateModalPriceDisplay(quantityInput);
      } else {
        updateProductPriceDisplay(quantityInput);
      }
    }
  });

  // Event listener untuk input manual kuantitas
  document.body.addEventListener("input", function (event) {
    const target = event.target;
    if (
      target.classList.contains("quantity-input") &&
      !target.classList.contains("quantity-input-cart")
    ) {
      // **PERBAIKAN**: Panggil fungsi yang benar
      if (target.classList.contains("modal-quantity-input")) {
        updateModalPriceDisplay(target);
      } else {
        updateProductPriceDisplay(target);
      }
    }
  });


  // --- SCROLL TO TOP ---
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
  }
  window.scrollToTop = function () {
    // Make it global for onclick attribute
    window.scrollTo({ top: 0, behavior: "smooth" });
  };

  // --- WISHLIST FUNCTIONALITY (localStorage) ---
  let wishlist = JSON.parse(localStorage.getItem("parfumkuWishlist")) || [];
  const wishlistNavIcon = document.getElementById("wishlistNavIcon");
  const wishlistCountBadge = document.getElementById("wishlistCountBadge");
  const wishlistModalBody = document.getElementById("wishlistModalBody");
  const emptyWishlistMessage = document.getElementById("emptyWishlistMessage");
  const clearWishlistBtn = document.getElementById("clearWishlistBtn");

  function updateWishlistIconAndBadge() {
    // Update card icons
    document.querySelectorAll(".wishlist-btn").forEach((btn) => {
      const productId = btn.dataset.productId;
      const icon = btn.querySelector("i");
      if (wishlist.includes(productId)) {
        icon.classList.remove("far");
        icon.classList.add("fas", "text-danger"); // text-danger bisa diganti warna primer jika mau
      } else {
        icon.classList.remove("fas", "text-danger");
        icon.classList.add("far");
      }
    });
    // Update navbar badge
    if (wishlist.length > 0) {
      wishlistCountBadge.textContent = wishlist.length;
      wishlistCountBadge.style.display = "inline-block";
    } else {
      wishlistCountBadge.style.display = "none";
    }
  }

  function renderWishlistModal() {
    if (!wishlistModalBody) return;
    wishlistModalBody.innerHTML = ""; // Clear current items

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
                            <h6>${name
                              .split(" ")
                              .map(
                                (word) =>
                                  word.charAt(0).toUpperCase() + word.slice(1)
                              )
                              .join(" ")}</h6>
                            <p>Rp${price.toLocaleString("id-ID")}</p>
                        </div>
                        <button class="btn btn-sm btn-remove-wishlist" data-product-id="${productId}" title="Hapus dari Wishlist">
                            <i class="fas fa-times-circle"></i>
                        </button>
                    </div>`;
        wishlistModalBody.insertAdjacentHTML("beforeend", itemHTML);
      }
    });

    // Add event listeners to new remove buttons in modal
    wishlistModalBody
      .querySelectorAll(".btn-remove-wishlist")
      .forEach((btn) => {
        btn.addEventListener("click", function () {
          toggleWishlist(this.dataset.productId);
          renderWishlistModal(); // Re-render modal after removal
        });
      });
  }

  function toggleWishlist(productId) {
    const productIndex = wishlist.indexOf(productId);
    const productElement = document.querySelector(
      `.product-item[data-id="${productId}"]`
    );
    let productName = "Produk";
    if (productElement) {
      productName = productElement.dataset.name
        .split(" ")
        .map((word) => word.charAt(0).toUpperCase() + word.slice(1))
        .join(" ");
    }

    if (productIndex > -1) {
      wishlist.splice(productIndex, 1); // Remove from wishlist
      showToast(`${productName} dihapus dari wishlist.`);
    } else {
      wishlist.push(productId); // Add to wishlist
      showToast(`${productName} ditambahkan ke wishlist!`);
    }
    localStorage.setItem("parfumkuWishlist", JSON.stringify(wishlist));
    updateWishlistIconAndBadge();
  }

  // Event listener for wishlist buttons on product cards
  document.body.addEventListener("click", function (event) {
    const wishlistBtn = event.target.closest(".wishlist-btn");
    if (wishlistBtn) {
      const productId = wishlistBtn.dataset.productId;
      toggleWishlist(productId);
    }
  });

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

  // Initialize wishlist view on page load & when modal is shown
  updateWishlistIconAndBadge();
  const wishlistModalEl = document.getElementById("wishlistModal");
  if (wishlistModalEl) {
    wishlistModalEl.addEventListener("show.bs.modal", renderWishlistModal);
  }

  // --- SEARCH PRODUCT FUNCTIONALITY ---
  function performSearch(searchTerm) {
    searchTerm = searchTerm.toLowerCase().trim();
    let productsFound = 0;

    allProductItems.forEach((item) => {
      // Only search visible items (not hidden by "load more")
      // if (item.style.display === 'none' && !item.classList.contains('product-item')) return;

      const productName = item.dataset.name.toLowerCase();
      const productDesc = item.dataset.description.toLowerCase();

      // Check against current filters as well (optional, can be complex)
      // For now, search overrides filters for simplicity of display
      if (
        productName.includes(searchTerm) ||
        productDesc.includes(searchTerm)
      ) {
        item.style.display = ""; // Show item (Bootstrap col classes handle layout)
        productsFound++;
      } else {
        item.style.display = "none";
      }
    });

    const noProductMsg = document.getElementById("noProductFoundMessage");
    const loadMoreBtn = document.getElementById("loadMoreBtn");

    if (productsFound === 0 && searchTerm !== "") {
      noProductMsg.textContent = `Produk tidak ditemukan untuk pencarian "${searchTerm}".`;
      noProductMsg.style.color = "#c7aa7f";
      noProductMsg.style.display = "block";
      if (loadMoreBtn) loadMoreBtn.style.display = "none"; // Hide load more if search yields no results
    } else {
      noProductMsg.style.display = "none";
      // Restore "Load More" if search is cleared or has results and not all products are shown
      if (
        loadMoreBtn &&
        allProductItems.filter((p) => p.style.display !== "none").length <
          allProductItems.length &&
        searchTerm === ""
      ) {
        loadMoreBtn.style.display = "block";
      } else if (loadMoreBtn && searchTerm !== "") {
        loadMoreBtn.style.display = "none"; // Hide if search is active
      }
    }
    // If search term is empty, re-apply filters and load more logic
    if (searchTerm === "") {
      applyFiltersAndSort();
    }
  }

  document
    .getElementById("searchFormNav")
    ?.addEventListener("submit", function (e) {
      e.preventDefault();
      performSearch(document.getElementById("searchInputNav").value);
    });
  document
    .getElementById("searchFormMobile")
    ?.addEventListener("submit", function (e) {
      e.preventDefault();
      performSearch(document.getElementById("searchInputMobile").value);
    });
  // Real-time search on keyup (optional, can be heavy)
  document
    .getElementById("searchInputNav")
    ?.addEventListener("keyup", function (e) {
      if (e.key === "Enter") return; // Already handled by submit
      performSearch(this.value);
    });
  document
    .getElementById("searchInputMobile")
    ?.addEventListener("keyup", function (e) {
      if (e.key === "Enter") return;
      performSearch(this.value);
    });

  // --- ADVANCED FILTERING & SORTING ---
  const filterCategorySelect = document.getElementById("filterCategory");
  const minPriceInput = document.getElementById("minPrice");
  const maxPriceInput = document.getElementById("maxPrice");
  const sortProductsSelect = document.getElementById("sortProducts");
  const clearFiltersButton = document.getElementById("clearFiltersBtn");

  function applyFiltersAndSort() {
    const categoryValue = filterCategorySelect.value;
    const minPrice = parseFloat(minPriceInput.value) || 0;
    const maxPrice = parseFloat(maxPriceInput.value) || Infinity;
    const sortValue = sortProductsSelect.value;

    let filteredProducts = allProductItems.filter((item) => {
      const itemPrice = parseFloat(item.dataset.price);
      const itemCategory = item.dataset.category;

      let show = true;
      if (categoryValue && itemCategory !== categoryValue) show = false;
      if (itemPrice < minPrice || itemPrice > maxPrice) show = false;

      return show;
    });

    // Sort
    switch (sortValue) {
      case "price_asc":
        filteredProducts.sort(
          (a, b) => parseFloat(a.dataset.price) - parseFloat(b.dataset.price)
        );
        break;
      case "price_desc":
        filteredProducts.sort(
          (a, b) => parseFloat(b.dataset.price) - parseFloat(a.dataset.price)
        );
        break;
      case "name_asc":
        filteredProducts.sort((a, b) =>
          a.dataset.name.localeCompare(b.dataset.name)
        );
        break;
      case "name_desc":
        filteredProducts.sort((a, b) =>
          b.dataset.name.localeCompare(a.dataset.name)
        );
        break;
      case "newest":
      default:
        filteredProducts.sort(
          (a, b) =>
            new Date(b.dataset.created_at || 0) -
            new Date(a.dataset.created_at || 0)
        );
        break;
    }

    // Clear product list and re-append sorted/filtered items
    productListContainer.innerHTML = ""; // Clear current display
    filteredProducts.forEach((item) => {
      // Reset display style that might have been set by search
      item.style.display = "";
      productListContainer.appendChild(item);
    });

    currentDisplayedCount = initialDisplayCount; // Reset for "Load More"
    updateVisibleProducts(filteredProducts);

    const noProductMsg = document.getElementById("noProductFoundMessage");
    if (filteredProducts.length === 0) {
      noProductMsg.textContent =
        "Tidak ada produk yang sesuai dengan filter Anda.";
      noProductMsg.style.display = "block";
    } else {
      noProductMsg.style.display = "none";
    }
  }

  if (filterCategorySelect)
    filterCategorySelect.addEventListener("change", applyFiltersAndSort);
  if (minPriceInput)
    minPriceInput.addEventListener("input", applyFiltersAndSort); // Or 'change'
  if (maxPriceInput)
    maxPriceInput.addEventListener("input", applyFiltersAndSort); // Or 'change'
  if (sortProductsSelect)
    sortProductsSelect.addEventListener("change", applyFiltersAndSort);

  if (clearFiltersButton) {
    clearFiltersButton.addEventListener("click", () => {
      filterCategorySelect.value = "";
      minPriceInput.value = "";
      maxPriceInput.value = "";
      sortProductsSelect.value = "newest";
      document.getElementById("searchInputNav").value = ""; // Clear search too
      document.getElementById("searchInputMobile").value = "";

      // Reset all items to be potentially visible before re-filtering/sorting
      allProductItems.forEach((item) => (item.style.display = ""));
      applyFiltersAndSort();
      showToast("Filter telah direset.");
    });
  }

  // --- QUICK VIEW MODAL ---
  const quickViewModalEl = document.getElementById("quickViewModal"); // Ini adalah elemen DOM
  const quickViewModal = new bootstrap.Modal(quickViewModalEl); // Ini adalah instance Bootstrap Modal

  
  const quickViewWaButton = document.getElementById("quickViewWaButton");

  document.body.addEventListener("click", function (event) {
    const quickViewBtn = event.target.closest(".btn-quick-view");
    if (quickViewBtn) {
      const productId = quickViewBtn.dataset.productId;
      const productItem = document.querySelector(
        `.product-item[data-id="${productId}"]`
      );

      if (productItem) {
        quickViewModalLabel.textContent = productItem.dataset.name
          .split(" ")
          .map((word) => word.charAt(0).toUpperCase() + word.slice(1))
          .join(" ");
        quickViewImage.src = productItem.dataset.image; // Ini akan mengambil dari data-image produk
        quickViewImage.alt = productItem.dataset.name;
        quickViewDescription.textContent = productItem.dataset.description;
        const price = parseFloat(productItem.dataset.price);
        quickViewPrice.textContent = "Rp" + price.toLocaleString("id-ID");
        const stock = parseInt(productItem.dataset.stock);
        quickViewStock.textContent = `Stok: ${stock}`;

        quickViewQuantityInput.value = 1;
        quickViewQuantityInput.setAttribute("max", stock);

        quickViewWaButton.dataset.name = productItem.dataset.name;
        quickViewWaButton.dataset.price = price;
        quickViewWaButton.dataset.id = productId;

        const qtyMinusButtonInModal =
          quickViewModalEl.querySelector(".quantity-minus"); // PERBAIKAN DI SINI
        const qtyPlusButtonInModal =
          quickViewModalEl.querySelector(".quantity-plus"); // PERBAIKAN DI SINI

        if (stock === 0) {
          quickViewQuantityInput.disabled = true;
          quickViewWaButton.disabled = true;
          quickViewWaButton.innerHTML =
            '<i class="fab fa-whatsapp"></i> Stok Habis';
          if (qtyMinusButtonInModal) qtyMinusButtonInModal.disabled = true; // PERBAIKAN DI SINI
          if (qtyPlusButtonInModal) qtyPlusButtonInModal.disabled = true; // PERBAIKAN DI SINI
        } else {
          quickViewQuantityInput.disabled = false;
          quickViewWaButton.disabled = false;
          quickViewWaButton.innerHTML =
            '<i class="fab fa-whatsapp"></i> Pesan via WhatsApp';
          if (qtyMinusButtonInModal) qtyMinusButtonInModal.disabled = false; // PERBAIKAN DI SINI
          if (qtyPlusButtonInModal) qtyPlusButtonInModal.disabled = false; // PERBAIKAN DI SINI
        }

        quickViewModal.show(); // Ini benar, memanggil .show() pada instance Bootstrap Modal
        addRecentlyViewed(productId);
      }
    }
  });

  // --- "LOAD MORE" PRODUCTS ---
  const loadMoreBtn = document.getElementById("loadMoreBtn");

  function updateVisibleProducts(sourceArray = allProductItems) {
    // This function should be called after filtering/sorting to work on the correct set of products.
    // For simplicity, this version assumes filtering logic re-appends items to productListContainer.
    // So, sourceArray will be the currently visible (and sorted) items in productListContainer.
    const itemsInDOM = Array.from(
      productListContainer.querySelectorAll(".product-item")
    );

    itemsInDOM.forEach((item, index) => {
      if (index < currentDisplayedCount) {
        item.style.display = ""; // Or whatever your column class sets
        item.classList.remove("product-hidden");
      } else {
        item.style.display = "none";
        item.classList.add("product-hidden");
      }
    });

    if (loadMoreBtn) {
      if (currentDisplayedCount >= itemsInDOM.length) {
        loadMoreBtn.style.display = "none";
      } else {
        loadMoreBtn.style.display = "block";
      }
    }
  }

  if (loadMoreBtn) {
    loadMoreBtn.addEventListener("click", () => {
      currentDisplayedCount += initialDisplayCount; // Or any other number
      // We need to get the currently *potentially* visible items after filtering/sorting.
      const currentFilteredAndSortedItems = Array.from(
        productListContainer.querySelectorAll(".product-item")
      );
      updateVisibleProducts(currentFilteredAndSortedItems);
      AOS.refresh(); // Refresh AOS to animate newly loaded items
    });
  }


  // --- NEWSLETTER FORM (DUMMY) ---
  const newsletterForm = document.getElementById("newsletterForm");
  if (newsletterForm) {
    newsletterForm.addEventListener("submit", function (e) {
      e.preventDefault();
      const emailInput = this.querySelector('input[type="email"]');
      if (emailInput.value) {
        showToast(
          `Terima kasih! ${emailInput.value} terdaftar untuk newsletter.`
        );
        emailInput.value = "";
      }
    });
  }

  // --- TOAST NOTIFICATION ---
  // (Bootstrap 5 Toast)
  function showToast(message, type = "success") {
    // type can be 'success', 'error', 'info'
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
      toastElement.remove(); // Clean up DOM
    });
  }

  const productList = document.getElementById("productList");

  if (productList) {
    // Menggunakan event delegation untuk menangani klik pada semua tombol "Beli Sekarang"
    productList.addEventListener("click", function (e) {
      // Cek apakah yang diklik adalah tombol checkout
      if (e.target.classList.contains("direct-checkout-btn")) {
        e.preventDefault();

        // Ambil elemen kartu produk terdekat
        const productCard = e.target.closest(".product-item");

        // Ambil data produk dari atribut data-*
        const productId = productCard.dataset.id;
        const productName = productCard.dataset.name;
        const productPrice = parseFloat(productCard.dataset.price);

        // Ambil kuantitas dari input di dalam kartu yang sama
        const quantityInput = productCard.querySelector(".quantity-input");
        const quantity = parseInt(quantityInput.value);

        // Validasi kuantitas
        if (isNaN(quantity) || quantity < 1) {
          Swal.fire({
            icon: "warning",
            title: "Jumlah Tidak Valid",
            text: "Silakan masukkan jumlah pembelian yang benar.",
            confirmButtonColor: "#3085d6",
          });

          return;
        }

        // Buat struktur data yang akan dikirim ke server (mirip keranjang, tapi hanya 1 item)
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
          headers: {
            "Content-Type": "application/json",
          },
          body: JSON.stringify(transactionData),
        })
          .then((response) => response.json())
          .then((data) => {
            if (data.token) {
              // Jika token diterima, buka popup pembayaran Midtrans
              window.snap.pay(data.token, {
                onSuccess: function (result) {
                  console.log("Payment Success:", result);
                  Swal.fire({
                    icon: "success",
                    title: "Pembayaran Berhasil!",
                    text: "Terima kasih, pesanan Anda sedang diproses.",
                    confirmButtonColor: "#28a745",
                  });
                  // Arahkan ke halaman terima kasih atau lakukan tindakan lain
                  // window.location.href = '/thank_you.php?order_id=' + result.order_id;
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
                onClose: function () {
                  console.log("Popup closed");
                  // Tidak melakukan apa-apa saat popup ditutup
                },
              });
            } else if (data.error) {
              Swal.fire({
                icon: "question",
                title: "Transaksi Dibatalkan",
                text: "Anda menutup halaman pembayaran sebelum menyelesaikan transaksi.",
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

  // Kode untuk quantity-plus dan quantity-minus (opsional, tapi disarankan)
  document.querySelectorAll(".quantity-plus").forEach((button) => {
    button.addEventListener("click", function () {
      const input = this.previousElementSibling;
      let value = parseInt(input.value);
      const max = parseInt(input.max);
      if (value < max) {
        input.value = value + 1;
      }
    });
  });

  document.querySelectorAll(".quantity-minus").forEach((button) => {
    button.addEventListener("click", function () {
      const input = this.nextElementSibling;
      let value = parseInt(input.value);
      if (value > 1) {
        input.value = value - 1;
      }
    });
  });

  // Initial setup calls
  applyFiltersAndSort(); // Apply default sort and display initial products
  renderRecentlyViewed(); // Render any stored recently viewed items
  updateWishlistIconAndBadge(); // Update wishlist icons on cards
}); // End DOMContentLoaded
