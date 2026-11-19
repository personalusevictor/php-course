document.addEventListener('DOMContentLoaded', () => {
  const searchInput = document.getElementById('ajax-search-input');
  const searchResults = document.getElementById('ajax-search-results');
  const cartBadge = document.getElementById('header-cart-badge');
  const toastContainer = document.getElementById('toast-container');
  const searchStatus = document.getElementById('search-status');

  function showToast(message) {
    if (!toastContainer) return;
    const toast = document.createElement('p');
    toast.className = 'toast';
    toast.textContent = message;
    toastContainer.replaceChildren(toast);
    setTimeout(() => toast.remove(), 5000);
  }

  async function addToCartRequest(button, productId, quantity = 1, basePath = '') {
    if (button.disabled) return;
    const originalLabel = button.innerHTML;
    button.disabled = true;
    button.setAttribute('aria-busy', 'true');
    button.textContent = 'Añadiendo…';
    try {
      const response = await fetch(`${basePath}cart-api.php`, {
        method: 'POST',
        headers: { 'Content-Type': 'application/json' },
        body: JSON.stringify({ action: 'add', product_id: productId, quantity }),
      });
      if (!response.ok) throw new Error('Respuesta no disponible');
      const data = await response.json();
      if (data.cart_count !== undefined && cartBadge) cartBadge.textContent = data.cart_count;
      showToast(data.message || 'No se pudo añadir el producto');
      if (data.success) {
        button.textContent = 'Añadido';
        button.classList.add('is-added');
      }
    } catch (error) {
      showToast('No se pudo conectar con el carrito. Inténtalo de nuevo.');
    } finally {
      button.removeAttribute('aria-busy');
      setTimeout(() => {
        button.innerHTML = originalLabel;
        button.classList.remove('is-added');
        button.disabled = false;
      }, 1200);
    }
  }

  document.querySelectorAll('.js-quick-add').forEach(button => {
    button.addEventListener('click', () => addToCartRequest(button, Number(button.dataset.id), 1, button.dataset.base || ''));
  });
  const detailAddButton = document.querySelector('.js-detail-add');
  if (detailAddButton) {
    detailAddButton.addEventListener('click', () => {
      const input = document.getElementById('detail-qty');
      if (input && !input.reportValidity()) return;
      addToCartRequest(detailAddButton, Number(detailAddButton.dataset.id), input ? Number(input.value) : 1);
    });
  }

  if (searchInput && searchResults) {
    let debounceTimer;
    let controller;
    let version = 0;
    const basePath = searchInput.dataset.base || '';
    const closeSearch = () => {
      searchResults.hidden = true;
      searchInput.setAttribute('aria-expanded', 'false');
    };
    searchInput.addEventListener('input', () => {
      clearTimeout(debounceTimer);
      controller?.abort();
      const currentVersion = ++version;
      const query = searchInput.value.trim();
      closeSearch();
      searchResults.replaceChildren();
      if (query.length < 2) {
        if (searchStatus) searchStatus.textContent = '';
        return;
      }
      debounceTimer = setTimeout(async () => {
        controller = new AbortController();
        try {
          const response = await fetch(`${basePath}search-ajax.php?q=${encodeURIComponent(query)}`, { signal: controller.signal });
          if (!response.ok) throw new Error('Búsqueda no disponible');
          const items = await response.json();
          if (currentVersion !== version || !Array.isArray(items)) return;
          const list = document.createElement('ul');
          items.forEach(item => {
            const li = document.createElement('li');
            const link = document.createElement('a');
            link.className = 'search-result-link';
            link.href = `${basePath}products.php?id=${Number(item.id)}`;
            const thumb = document.createElement("img");
            thumb.className = "search-result-thumb";
            thumb.src = item.image
              ? `${basePath}${item.image}`
              : `${basePath}assets/brand/mark.svg`;
            thumb.alt = "";
            thumb.width = 56;
            thumb.height = 56;
            thumb.loading = "lazy";
            const content = document.createElement("span");
            const name = document.createElement("strong");
            name.textContent = item.name;
            const category = document.createElement("small");
            category.textContent = `${item.category}${Number(item.stock) <= 0 ? " · Agotado" : ""}`;
            const price = document.createElement("span");
            price.className = "search-result-price";
            price.textContent = item.price;
            content.append(name, category);
            link.append(thumb, content, price);
            li.append(link);
            list.append(li);
          });
          searchResults.replaceChildren(list);
          if (!items.length) {
            const message = document.createElement('p');
            message.className = 'search-empty';
            message.textContent = 'No encontramos coincidencias. Prueba otra marca o modelo.';
            searchResults.replaceChildren(message);
          }
          searchResults.hidden = false;
          searchInput.setAttribute('aria-expanded', 'true');
          if (searchStatus) searchStatus.textContent = `${items.length} productos encontrados.`;
        } catch (error) {
          if (error.name !== 'AbortError') {
            closeSearch();
            if (searchStatus) searchStatus.textContent = 'La búsqueda no está disponible en este momento.';
          }
        }
      }, 250);
    });
    const searchContainer = searchInput.closest('.search-container');
    searchContainer.addEventListener('keydown', event => {
      if (event.key === 'Escape') {
        clearTimeout(debounceTimer);
        controller?.abort();
        version++;
        closeSearch();
        searchInput.focus();
        return;
      }
      if (searchResults.hidden) return;
      const links = [...searchResults.querySelectorAll('a')];
      if (!links.length) return;
      const position = links.indexOf(document.activeElement);
      if (event.key === 'ArrowDown') { event.preventDefault(); links[(position + 1) % links.length].focus(); }
      if (event.key === 'ArrowUp') { event.preventDefault(); if (position <= 0) searchInput.focus(); else links[position - 1].focus(); }
    });
    document.addEventListener('click', event => {
      if (!searchContainer.contains(event.target)) {
        clearTimeout(debounceTimer);
        controller?.abort();
        version++;
        closeSearch();
      }
    });
    document.addEventListener('keydown', event => {
      if (event.key === '/' && !event.ctrlKey && !event.metaKey && !/INPUT|TEXTAREA|SELECT/.test(document.activeElement.tagName) && !document.activeElement.isContentEditable) {
        event.preventDefault(); searchInput.focus();
      }
    });
  }

  document.querySelectorAll('[data-auth-tabs]').forEach(panel => {
    const tabList = panel.querySelector('[role="tablist"]');
    const buttons = [...panel.querySelectorAll('[data-tab]')];
    function activate(name, focus = false) {
      buttons.forEach(button => {
        const active = button.dataset.tab === name;
        button.setAttribute('aria-selected', String(active));
        button.tabIndex = active ? 0 : -1;
        const section = document.getElementById(button.getAttribute('aria-controls'));
        section.hidden = !active;
        section.setAttribute('role', 'tabpanel');
        if (active && focus) button.focus();
      });
    }
    tabList.hidden = false;
    panel.classList.add('is-tabbed');
    activate(panel.dataset.initialTab || 'login');
    buttons.forEach((button, index) => {
      button.addEventListener('click', () => activate(button.dataset.tab));
      button.addEventListener('keydown', event => {
        if (event.key === 'ArrowRight' || event.key === 'ArrowLeft') {
          event.preventDefault();
          const next = buttons[(index + 1) % buttons.length];
          activate(next.dataset.tab, true);
        }
      });
    });
  });
  document.querySelectorAll('[data-password]').forEach(button => {
    button.addEventListener('click', () => {
      const input = document.getElementById(button.dataset.password);
      const showing = input.type === 'password';
      input.type = showing ? 'text' : 'password';
      button.textContent = showing ? 'Ocultar' : 'Mostrar';
      button.setAttribute('aria-label', `${showing ? 'Ocultar' : 'Mostrar'} contraseña`);
    });
  });
});
