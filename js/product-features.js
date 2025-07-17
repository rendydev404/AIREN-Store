// js/product-features.js
import { showToast } from "./ui.js";
import { addToCart } from "./cart.js";

// --- Variabel Tingkat Modul ---
let featureElements = {};
let currentDisplayedCount = 8;
const initialDisplayCount = 8;
let quickViewModalInstance = null;

// =====================================================================
// BAGIAN 1: KUMPULAN FUNGSI-FUNGSI BANTU
// Semua fungsi didefinisikan di sini agar rapi.
// =====================================================================



function updateModalPriceDisplay() {
  const { quickViewModalEl, quickViewAddToCartButton } = featureElements;
  const modalQuantityInput = quickViewModalEl.querySelector(
    "#quickViewQuantityInput"
  );
  const priceDisplay = quickViewModalEl.querySelector("#quickViewPrice");
  const basePrice = parseFloat(quickViewAddToCartButton.dataset.price);
  const stock = parseInt(modalQuantityInput.getAttribute("max"));

  let quantity = parseInt(modalQuantityInput.value);
  if (isNaN(quantity) || quantity < 1) quantity = 1;
  if (quantity > stock) quantity = stock;

  modalQuantityInput.value = quantity;
  const totalPrice = basePrice * quantity;
  priceDisplay.textContent = "Rp" + totalPrice.toLocaleString("id-ID");
}

function updateVisibleProducts() {
  const { productListContainer, loadMoreBtn } = featureElements;
  const itemsInDOM = Array.from(
    productListContainer.querySelectorAll(".product-item")
  );

  itemsInDOM.forEach((item, index) => {
    item.style.display = index < currentDisplayedCount ? "" : "none";
  });

  if (loadMoreBtn) {
    loadMoreBtn.style.display =
      currentDisplayedCount >= itemsInDOM.length ? "none" : "block";
  }
}



function performSearch(searchTerm) {
  searchTerm = searchTerm.toLowerCase().trim();
  let productsFound = 0;
  const { allProductItems, loadMoreBtn, noProductFoundMessage } =
    featureElements;

  allProductItems.forEach((item) => {
    const productName = item.dataset.name.toLowerCase();
    const productDesc = item.dataset.description.toLowerCase();
    if (productName.includes(searchTerm) || productDesc.includes(searchTerm)) {
      item.style.display = "";
      productsFound++;
    } else {
      item.style.display = "none";
    }
  });

  if (productsFound === 0 && searchTerm !== "") {
    noProductFoundMessage.textContent = `Produk tidak ditemukan untuk "${searchTerm}".`;
    noProductFoundMessage.style.display = "block";
    if (loadMoreBtn) loadMoreBtn.style.display = "none";
  } else {
    noProductFoundMessage.style.display = "none";
    if (loadMoreBtn && searchTerm !== "") {
      loadMoreBtn.style.display = "none";
    }
  }

  if (searchTerm === "") {
    applyFiltersAndSort();
  }
}

// =====================================================================
// BAGIAN 2: FUNGSI INISIALISASI UTAMA
// Semua event listener dan setup awal ada di dalam fungsi ini.
// =====================================================================

export function initProductFeatures(elements) {
  // Setup awal
  featureElements = elements;
  if (featureElements.quickViewModalEl) {
    quickViewModalInstance = new bootstrap.Modal(
      featureElements.quickViewModalEl
    );
  }

  // --- Listener untuk semua event KLIK ---
  document.body.addEventListener("click", function (event) {
    const target = event.target;

    // Handler untuk tombol Quick View
    const quickViewBtn = target.closest(".btn-quick-view");
    if (quickViewBtn) {
      populateQuickViewModal(quickViewBtn.dataset.productId);
      return;
    }

    // Handler untuk Tombol Kuantitas (+ / -) di mana saja
    if (target.matches(".quantity-plus")) {
      const quantityInput = target.previousElementSibling;
      if (quantityInput && quantityInput.matches(".quantity-input")) {
        quantityInput.stepUp();
        quantityInput.dispatchEvent(new Event("input", { bubbles: true }));
      }
    } else if (target.matches(".quantity-minus")) {
      const quantityInput = target.nextElementSibling;
      if (quantityInput && quantityInput.matches(".quantity-input")) {
        quantityInput.stepDown();
        quantityInput.dispatchEvent(new Event("input", { bubbles: true }));
      }
    }
  });

  // --- Listener untuk semua event INPUT (termasuk ketik manual) ---
  document.body.addEventListener("input", function (event) {
    const target = event.target;

    if (target.matches(".quantity-input")) {
      if (target.closest("#quickViewModal")) {
        updateModalPriceDisplay();
      } else if (target.closest(".product-item")) {
        updateProductPriceDisplay(target);
      }
    }
  });

  // --- Listener untuk kontrol Filter, Sort, Search, Load More ---
  const {
    filterCategorySelect,
    minPriceInput,
    maxPriceInput,
    sortProductsSelect,
    clearFiltersButton,
    searchInputNav,
    searchInputMobile,
    loadMoreBtn,
  } = featureElements;

  filterCategorySelect?.addEventListener("change", applyFiltersAndSort);
  minPriceInput?.addEventListener("input", applyFiltersAndSort);
  maxPriceInput?.addEventListener("input", applyFiltersAndSort);
  sortProductsSelect?.addEventListener("change", applyFiltersAndSort);

  clearFiltersButton?.addEventListener("click", () => {
    filterCategorySelect.value = "";
    minPriceInput.value = "";
    maxPriceInput.value = "";
    sortProductsSelect.value = "newest";
    if (searchInputNav) searchInputNav.value = "";
    if (searchInputMobile) searchInputMobile.value = "";
    featureElements.allProductItems.forEach(
      (item) => (item.style.display = "")
    );
    applyFiltersAndSort();
    showToast("Filter telah direset.");
  });

  // Event Listeners untuk Pencarian
  searchInputNav?.addEventListener("keyup", (e) =>
    performSearch(e.target.value)
  );
  searchInputMobile?.addEventListener("keyup", (e) =>
    performSearch(e.target.value)
  );
  document
    .getElementById("searchFormNav")
    ?.addEventListener("submit", (e) => e.preventDefault());
  document
    .getElementById("searchFormMobile")
    ?.addEventListener("submit", (e) => e.preventDefault());

  loadMoreBtn?.addEventListener("click", () => {
    currentDisplayedCount += initialDisplayCount;
    updateVisibleProducts();
    if (typeof AOS !== "undefined") AOS.refresh();
  });

  // Panggil pertama kali untuk menampilkan produk
  applyFiltersAndSort();
  return quickViewModalInstance;
}

function updateProductPriceDisplay(inputElement) {
  const productCard = inputElement.closest(".card-body");
  if (!productCard) return;

  const priceValueSpan = productCard.querySelector(".price-value");
  if (!priceValueSpan) return;

  const basePrice = parseFloat(priceValueSpan.dataset.basePrice);
  let quantity = parseInt(inputElement.value);
  const stock = parseInt(inputElement.getAttribute("max"));

  if (isNaN(quantity) || quantity < 1) {
    quantity = 1;
    inputElement.value = 1;
  }
  if (quantity > stock) {
    quantity = stock;
    inputElement.value = stock;
  }

  const totalPrice = basePrice * quantity;
  priceValueSpan.textContent = totalPrice.toLocaleString("id-ID");
}

function populateQuickViewModal(productId) {
  const {
    quickViewModalEl,
    quickViewImage,
    quickViewModalLabel,
    quickViewDescription,
    quickViewPrice,
    quickViewStock,
    quickViewQuantityInput,
    quickViewAddToCartButton,
  } = featureElements;
  const productItem = document.querySelector(
    `.product-item[data-id="${productId}"]`
  );

  if (productItem) {
    const name = productItem.dataset.name;
    const price = parseFloat(productItem.dataset.price);
    const stock = parseInt(productItem.dataset.stock);

    quickViewModalLabel.textContent = name;
    quickViewImage.src = productItem.dataset.image;
    quickViewDescription.textContent = productItem.dataset.description;
    quickViewPrice.textContent = "Rp" + price.toLocaleString("id-ID");
    quickViewStock.textContent = `Stok: ${stock}`;
    quickViewQuantityInput.value = 1;
    quickViewQuantityInput.setAttribute("max", stock);

    quickViewAddToCartButton.dataset.name = name;
    quickViewAddToCartButton.dataset.price = price;
    quickViewAddToCartButton.dataset.id = productId;

    const isOutOfStock = stock === 0;
    quickViewQuantityInput.disabled = isOutOfStock;
    quickViewAddToCartButton.disabled = isOutOfStock;
    quickViewAddToCartButton.innerHTML = isOutOfStock
      ? '<i class="fas fa-times-circle"></i> Stok Habis'
      : '<i class="fas fa-shopping-cart"></i> Tambah ke Keranjang';

    quickViewModalEl.querySelector(".quantity-minus").disabled = isOutOfStock;
    quickViewModalEl.querySelector(".quantity-plus").disabled = isOutOfStock;

    quickViewModalInstance.show();
  }
}


function applyFiltersAndSort() {
  const {
    filterCategorySelect,
    minPriceInput,
    maxPriceInput,
    sortProductsSelect,
    allProductItems,
    noProductFoundMessage,
  } = featureElements;

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

  featureElements.productListContainer.innerHTML = "";
  filteredProducts.forEach((item) => {
    item.style.display = "";
    featureElements.productListContainer.appendChild(item);
  });

  currentDisplayedCount = initialDisplayCount;
  updateVisibleProducts();

  if (noProductFoundMessage) {
    if (filteredProducts.length === 0) {
      noProductFoundMessage.textContent =
        "Tidak ada produk yang sesuai dengan filter Anda.";
      noProductFoundMessage.style.display = "block";
    } else {
      noProductFoundMessage.style.display = "none";
    }
  }
}