document.addEventListener("DOMContentLoaded", () => {
  const searchInput = document.getElementById("ajax-search-input");
  const searchResults = document.getElementById("ajax-search-results");
  const cartBadge = document.getElementById("header-cart-badge");
  const toastContainer = document.getElementById("toast-container");

  function showToast(message) {
    if (!toastContainer) return;
    const toast = document.createElement("p");
    toast.textContent = message;
    toastContainer.appendChild(toast);
    setTimeout(() => {
      toast.remove();
    }, 3000);
  }

  async function addToCartRequest(productId, quantity = 1, basePath = "") {
    try {
      const response = await fetch(`${basePath}cart-api.php`, {
        method: "POST",
        headers: { "Content-Type": "application/json" },
        body: JSON.stringify({
          action: "add",
          product_id: productId,
          quantity: quantity,
        }),
      });
      const data = await response.json();
      if (data.success) {
        if (cartBadge) {
          cartBadge.textContent = data.cart_count;
        }
        showToast(data.message);
      } else {
        showToast(data.message || "No se pudo añadir el producto");
      }
    } catch (err) {
      showToast("Error de conexión con el carrito");
    }
  }

  document.querySelectorAll(".js-quick-add").forEach((btn) => {
    btn.addEventListener("click", () => {
      const id = parseInt(btn.dataset.id, 10);
      const base = btn.dataset.base || "";
      addToCartRequest(id, 1, base);
    });
  });

  const detailAddBtn = document.querySelector(".js-detail-add");
  if (detailAddBtn) {
    detailAddBtn.addEventListener("click", () => {
      const id = parseInt(detailAddBtn.dataset.id, 10);
      const qtyInput = document.getElementById("detail-qty");
      const qty = qtyInput ? parseInt(qtyInput.value, 10) : 1;
      addToCartRequest(id, qty, "");
    });
  }

  if (searchInput && searchResults) {
    let debounceTimer;
    const basePath = searchInput.dataset.base || "";

    searchInput.addEventListener("input", () => {
      clearTimeout(debounceTimer);
      const query = searchInput.value.trim();

      if (query.length < 2) {
        searchResults.innerHTML = "";
        searchResults.hidden = true;
        return;
      }

      debounceTimer = setTimeout(async () => {
        try {
          const res = await fetch(
            `${basePath}search-ajax.php?q=${encodeURIComponent(query)}`,
          );
          const items = await res.json();

          if (!items.length) {
            searchResults.innerHTML = "<p>No se encontraron productos</p>";
          } else {
            searchResults.innerHTML =
              "<ul>" +
              items
                .map(
                  (item) => `
                        <li>
                            <a href="${basePath}products.php?id=${item.id}">
                                <strong>${item.name}</strong> (${item.category}) - ${item.price} ${item.stock <= 0 ? "[Sin stock]" : ""}
                            </a>
                        </li>
                    `,
                )
                .join("") +
              "</ul>";
          }
          searchResults.hidden = false;
        } catch (e) {
          searchResults.hidden = true;
        }
      }, 250);
    });

    document.addEventListener("click", (e) => {
      if (
        !searchInput.contains(e.target) &&
        !searchResults.contains(e.target)
      ) {
        searchResults.hidden = true;
      }
    });
  }
});
