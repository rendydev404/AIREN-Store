// File: js/checkout.js
document.addEventListener("DOMContentLoaded", () => {
  const cart = JSON.parse(localStorage.getItem("airenCart")) || [];
  if (cart.length === 0) {
    window.location.href = "index.php";
    return;
  }

  const orderSummary = document.getElementById("order-summary");
  const payButton = document.getElementById("pay-button");
  const bankList = document.getElementById("bank-list");
  const paymentResultEl = document.getElementById("payment-result");
  const paymentSelectionEl = document.getElementById("payment-selection");
  const paymentResultDetails = document.getElementById(
    "payment-result-details"
  );

  let selectedBank = null;
  let total = 0;

  // Fungsi untuk memvalidasi dan membersihkan data harga
  function sanitizeCartData(items) {
    return items
      .map((item) => {
        const price = parseFloat(item.price);
        const quantity = parseInt(item.quantity);

        // Log untuk debugging
        console.log("Item:", item.name, "Price:", item.price, "→", price);

        return {
          ...item,
          price: isNaN(price) ? 0 : price,
          quantity: isNaN(quantity) || quantity < 1 ? 1 : quantity,
        };
      })
      .filter((item) => item.price > 0);
  }

  // Membersihkan data cart dari localStorage
  const sanitizedCart = sanitizeCartData(cart);

  if (sanitizedCart.length === 0) {
    alert("Tidak ada produk dengan harga valid di keranjang");
    window.location.href = "index.php";
    return;
  }

  // Tampilkan ringkasan pesanan
  sanitizedCart.forEach((item) => {
    const li = document.createElement("li");
    li.className = "list-group-item d-flex justify-content-between";
    li.innerHTML = `
            <span>${item.name} (x${item.quantity})</span> 
            <span>Rp${(item.price * item.quantity).toLocaleString(
              "id-ID"
            )}</span>
        `;
    orderSummary.appendChild(li);
    total += item.price * item.quantity;
  });

  // Validasi total harga
  if (isNaN(total) || total <= 0) {
    console.error("Total harga tidak valid:", total);
    alert("Terjadi kesalahan dalam menghitung total harga");
    window.location.href = "index.php";
    return;
  }

  const totalLi = document.createElement("li");
  totalLi.className = "list-group-item d-flex justify-content-between active";
  totalLi.innerHTML = `<strong>Total</strong> <strong>Rp${total.toLocaleString(
    "id-ID"
  )}</strong>`;
  orderSummary.appendChild(totalLi);

  // Handler untuk memilih bank
  bankList.addEventListener("click", (e) => {
    const target = e.target.closest(".payment-method");
    if (!target) return;

    bankList
      .querySelectorAll(".payment-method")
      .forEach((el) => el.classList.remove("active"));
    target.classList.add("active");
    selectedBank = target.dataset.bank;
  });

  // Handler untuk tombol bayar
  payButton.addEventListener("click", async () => {
    if (!selectedBank) {
      alert("Silakan pilih bank terlebih dahulu.");
      return;
    }
    payButton.disabled = true;
    payButton.textContent = "Memproses...";

    // 🔁 GANTI bagian transactionData menjadi:
    const transactionData = {
      items: sanitizedCart.map((item) => ({
        id: item.id,
        price: item.price,
        quantity: item.quantity,
        name: item.name,
      })),
      total: total,
      payment_method: selectedBank, // ← HAPUS _va suffix
    };

    try {
      const response = await fetch("process_core_payment.php", {
        method: "POST",
        headers: { "Content-Type": "application/json" },
        body: JSON.stringify(transactionData),
      });

      const result = await response.json();

      if (result.error) {
        throw new Error(result.message || "Terjadi kesalahan di server.");
      }

      // Tampilkan hasil
      paymentSelectionEl.style.display = "none";
      paymentResultEl.style.display = "block";

      let detailsHTML = `<p>Selesaikan pembayaran sejumlah <strong>Rp${total.toLocaleString(
        "id-ID"
      )}</strong></p>`;

      if (result.va_numbers && result.va_numbers.length > 0) {
        detailsHTML += `<p>Nomor Virtual Account: <br><strong class="fs-4">${result.va_numbers[0].va_number}</strong></p>`;
      } else if (result.bill_key && result.biller_code) {
        detailsHTML += `<p>Kode Perusahaan: <strong class="fs-4">${result.biller_code}</strong></p>`;
        detailsHTML += `<p>Kode Pembayaran: <strong class="fs-4">${result.bill_key}</strong></p>`;
      } else if (
        result.actions &&
        result.actions.find((a) => a.name === "generate-qr-code")
      ) {
        const qrUrl = result.actions.find(
          (a) => a.name === "generate-qr-code"
        ).url;
        detailsHTML += `<img src="${qrUrl}" alt="QR Code" class="img-fluid mb-3">`;
      }

      paymentResultDetails.innerHTML = detailsHTML;
      localStorage.removeItem("airenCart");
    } catch (error) {
      console.error("Payment error:", error);
      alert(`Error: ${error.message}`);
      payButton.disabled = false;
      payButton.textContent = "Bayar Sekarang";
    }
  });
});
