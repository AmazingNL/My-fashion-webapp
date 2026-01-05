/** @format */

document.addEventListener("DOMContentLoaded", () => {
	const grid = document.getElementById("grid");
	const q = document.getElementById("searchInput");
	const cartCount = document.getElementById("cartCount");
	const toast = document.getElementById("toast");

	// Endpoints (keep view routes separate from API routes)
	const PRODUCTS_URL = "/productLists";
	const FAV_LIST_URL = "/api/favourites/list";
	const FAV_TOGGLE_URL = "/api/favourites/toggle";
	const CART_COUNT_URL = "/cart/count";

	let products = [];

	// ---------- helpers ----------
	const escapeHtml = (s) =>
		String(s).replace(
			/[&<>"']/g,
			(m) =>
				({
					"&": "&amp;",
					"<": "&lt;",
					">": "&gt;",
					'"': "&quot;",
					"'": "&#039;",
				}[m])
		);

	const showToast = (msg, isError = false) => {
		if (!toast) return;
		toast.textContent = msg;
		toast.className = isError ? "toast toast--bad" : "toast";
		toast.hidden = false;
		clearTimeout(showToast._t);
		showToast._t = setTimeout(() => (toast.hidden = true), 2800);
	};

	const getCsrf = () =>
		document.querySelector('meta[name="csrf-token"]')?.content ||
		document.querySelector('input[name="csrf"]')?.value ||
		"";

	async function csrfFetch(url, opts = {}) {
		const method = (opts.method || "GET").toUpperCase();
		const headers = new Headers(opts.headers || {});
		const csrf = getCsrf();

		headers.set("Accept", headers.get("Accept") || "application/json");

		// attach CSRF for non-GET
		if (method !== "GET" && csrf) {
			headers.set("X-CSRF-TOKEN", csrf);

			if (opts.body instanceof URLSearchParams) {
				if (!opts.body.has("csrf")) opts.body.set("csrf", csrf);
			}
		}

		return fetch(url, {
			credentials: "same-origin",
			...opts,
			headers,
		});
	}

	async function apiJson(url, opts = {}) {
		const res = await csrfFetch(url, opts);
		const raw = await res.text();
		const data = raw ? safeJson(raw) : {};

		if (!res.ok) {
			const msg =
				data?.error || data?.message || `Request failed (${res.status})`;
			const err = new Error(msg);
			err.status = res.status;
			err.data = data;
			throw err;
		}

		return data;
	}

	function safeJson(raw) {
		try {
			return JSON.parse(raw);
		} catch {
			return { error: raw }; // if server sent HTML/text
		}
	}

	const money = (n) => {
		const num = Number(n);
		if (Number.isNaN(num)) return `€${escapeHtml(n)}`;
		return new Intl.NumberFormat("nl-NL", {
			style: "currency",
			currency: "EUR",
		}).format(num);
	};

	// ---------- favourites ----------
	const fav = {
		set: new Set(),

		async init() {
			try {
				const data = await apiJson(FAV_LIST_URL);
				const list = Array.isArray(data?.favourites) ? data.favourites : [];
				this.set = new Set(list.map(Number));
			} catch {
				this.set = new Set(); // silent: user may not be logged in
			}
		},

		has(id) {
			return this.set.has(Number(id));
		},

		async toggle(id) {
			const body = new URLSearchParams({ productId: String(id) });

			const data = await apiJson(FAV_TOGGLE_URL, {
				method: "POST",
				headers: { "Content-Type": "application/x-www-form-urlencoded" },
				body,
			});

			const on = !!data.favourited;
			if (on) this.set.add(Number(id));
			else this.set.delete(Number(id));
			return on;
		},
	};

	// ---------- cart count ----------
	async function updateCartCount() {
		try {
			const data = await apiJson(CART_COUNT_URL);
			if (!cartCount || data?.count == null) return;

			const next = String(data.count);
			const changed = cartCount.textContent !== next;
			cartCount.textContent = next;

			if (changed) {
				cartCount.classList.add("pulse");
				setTimeout(() => cartCount.classList.remove("pulse"), 250);
			}
		} catch (e) {
			console.error("Cart count failed:", e);
		}
	}

	// ---------- rendering ----------
	function card(p) {
		const id = Number(p.productId);
		const isFav = fav.has(id);

		const name = p.productName || "Untitled";
		const imgSrc = p.image
			? escapeHtml(p.image)
			: "https://via.placeholder.com/400x500?text=No+Image";
		const category = p.category ? escapeHtml(p.category) : "Uncategorized";

		return `
      <article class="card" data-name="${escapeHtml(name)}">
        <a class="card__link" href="/products/${id}">
          <img class="card__img"
               src="${imgSrc}"
               alt="${escapeHtml(name)}"
               loading="lazy">
          <h2 class="card__title">${escapeHtml(name)}</h2>
          <p class="card__meta">
            <span class="card__price">${money(p.price)}</span>
            <span class="card__category">• ${category}</span>
          </p>
        </a>

        <button
          class="card__fav ${isFav ? "is-on" : ""}"
          type="button"
          data-fav="${id}"
          aria-pressed="${isFav ? "true" : "false"}"
          title="${isFav ? "Remove from favourites" : "Add to favourites"}"
        >♥</button>
      </article>
    `;
	}

	function render(list) {
		if (!grid) return;

		if (!list || list.length === 0) {
			grid.innerHTML = `
        <div class="empty-state">
          <div class="empty-state__icon">🔍</div>
          <p class="empty-state__text">No products found.</p>
        </div>
      `;
			return;
		}

		grid.innerHTML = list.map(card).join("");
	}

	// ---------- load ----------
	async function boot() {
		if (!grid) return;

		// 1) load favourites first (so hearts render correctly)
		await fav.init();

		// 2) load products
		try {
			const data = await apiJson(PRODUCTS_URL);
			products = Array.isArray(data) ? data : data.products || [];
			render(products);
		} catch (e) {
			console.error("Products load failed:", e);
			grid.innerHTML = `
        <div class="empty-state">
          <div class="empty-state__icon">⚠️</div>
          <p class="empty-state__text">${escapeHtml(
						e.message || "Could not load products."
					)}</p>
        </div>
      `;
			showToast(e.message || "Could not load products", true);
			return;
		}

		// 3) cart count
		await updateCartCount();
	}

	// ---------- events ----------
	grid?.addEventListener("click", async (e) => {
		const btn = e.target.closest("[data-fav]");
		if (!btn) return;

		const id = Number(btn.dataset.fav);
		if (!id) return;

		btn.disabled = true;

		try {
			const on = await fav.toggle(id);
			btn.classList.toggle("is-on", on);
			btn.setAttribute("aria-pressed", on ? "true" : "false");
			btn.title = on ? "Remove from favourites" : "Add to favourites";
			showToast(on ? "Added to favourites ✓" : "Removed from favourites");
		} catch (err) {
			console.error("Favourite toggle failed:", err);
			showToast(err.message || "Favourite failed. Try again.", true);
		} finally {
			btn.disabled = false;
		}
	});

	q?.addEventListener("input", () => {
		const term = q.value.trim().toLowerCase();
		const filtered = !term
			? products
			: products.filter((p) =>
					String(p.productName || "")
						.toLowerCase()
						.includes(term)
			  );
		render(filtered);
	});

	window.addEventListener("cartUpdated", updateCartCount);
	window.updateCartCount = updateCartCount;

	boot();
});
