/** @format */

document.addEventListener("DOMContentLoaded", async () => {
	const grid = document.getElementById("grid");
	const q = document.getElementById("searchInput");
	const cartCount = document.getElementById("cartCount");
	const toast = document.getElementById("toast");

	// API endpoints
	const PRODUCTS_URL = "/productLists";
	const FAV_TOGGLE_URL = "/toggleFavourite";
	const FAV_LIST_URL = "/favourites";

	function showToast(msg, isError = false) {
		if (!toast) return;
		toast.textContent = msg;
		toast.className = isError ? "toast toast--bad" : "toast";
		toast.hidden = false;
		clearTimeout(showToast._t);
		showToast._t = setTimeout(() => (toast.hidden = true), 3000);
	}

	function escapeHtml(s) {
		return String(s).replace(
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
	}

	// Favourites management
	const fav = {
		set: new Set(),

		async init() {
			try {
				const res = await csrfFetch(FAV_LIST_URL, {
					headers: { Accept: "application/json" },
					credentials: "same-origin",
				});
				if (!res.ok) return;

				const data = await res.json();
				const list = Array.isArray(data.favourites)
					? data.favourites
					: Array.isArray(data)
					? data
					: [];
				this.set = new Set(list.map(Number));
			} catch {
				// Silently fail
			}
		},

		has(id) {
			return this.set.has(Number(id));
		},

		async toggle(id) {
			const res = await csrfFetch(FAV_TOGGLE_URL, {
				method: "POST",
				headers: {
					"Content-Type": "application/x-www-form-urlencoded",
					Accept: "application/json",
				},
				credentials: "same-origin",
				body: new URLSearchParams({ productId: String(id) }),
			});

			if (!res.ok) {
				const err = await res.json().catch(() => ({}));
				throw new Error(err.error || "Favourite toggle failed");
			}

			const data = await res.json();
			const on = !!data.favourited;

			if (on) this.set.add(Number(id));
			else this.set.delete(Number(id));

			return on;
		},
	};

	let products = [];

	function card(p) {
		const isFav = fav.has(p.productId);
		const imgSrc = p.image
			? `${escapeHtml(p.image)}`
			: "https://via.placeholder.com/400x500?text=No+Image";

		return `
                    <article class="card" data-name="${escapeHtml(
											p.productName
										)}">
                        <a class="card__link" href="/products/${p.productId}">
                            <img 
                                class="card__img" 
                                src="${imgSrc}"
                                alt="${escapeHtml(p.productName)}"
                                loading="lazy"
                            >
                            <h2 class="card__title">${escapeHtml(
															p.productName
														)}</h2>
                            <p class="card__meta">
                                <span class="card__price">€${Number(
																	p.price
																).toFixed(2)}</span>
                                <span class="card__category">• ${escapeHtml(
																	p.category
																)}</span>
                            </p>
                        </a>

                        <button 
                            class="card__fav ${isFav ? "is-on" : ""}"
                            type="button"
                            data-fav="${p.productId}"
                            aria-pressed="${isFav ? "true" : "false"}"
                            title="${
															isFav
																? "Remove from favourites"
																: "Add to favourites"
														}"
                        >♥</button>
                    </article>
                `;
	}

	function render(list) {
		if (!list.length) {
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

	// 1) Load favourites first
	await fav.init();

	// 2) Load products
	try {
		const res = await csrfFetch(PRODUCTS_URL, {
			headers: { Accept: "application/json" },
		});
		if (!res.ok) throw new Error("Could not load products");
		products = await res.json();
		render(products);
	} catch (e) {
		grid.innerHTML = `
                    <div class="empty-state">
                        <div class="empty-state__icon">⚠️</div>
                        <p class="empty-state__text">Could not load products.</p>
                    </div>
                `;
		showToast("Could not load products", true);
		return;
	}

	// 3) Favourite toggle
	grid.addEventListener("click", async (e) => {
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
			console.error(err);
			showToast("Favourite failed. Try again.", true);
		} finally {
			btn.disabled = false;
		}
	});

	// 4) Search filter
	q.addEventListener("input", () => {
		const term = q.value.trim().toLowerCase();
		const filtered = products.filter((p) =>
			p.productName.toLowerCase().includes(term)
		);
		render(filtered);
	});

	// Update cart count if needed
	if (cartCount) {
		cartCount.textContent = cartCount.textContent || "0";
	}
});
