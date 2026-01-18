/** @format */

document.addEventListener("DOMContentLoaded", () => {
	const grid = document.getElementById("grid");
	const search = document.getElementById("searchInput"); // optional
	const toast = document.getElementById("toast");

	const categoryBox = document.getElementById("categoryFilters");
	const priceBox = document.getElementById("priceRangeFilters");
	const minPrice = document.getElementById("minPrice");
	const maxPrice = document.getElementById("maxPrice");

	const filterPanel = document.getElementById("filterPanel");
	const filterToggle = document.getElementById("filterToggle"); // optional
	const filterClose = document.getElementById("filterClose"); // optional
	const clearFilters = document.getElementById("clearFilters");

	const favCountEl = document.getElementById("favCount"); // navbar badge (add this id)

	const PRODUCTS_URL = "/productLists";

	let products = [];
	const favSet = new Set(); // productIds currently favourited

	const PRICE_RANGES = [
		{ id: "0-50", label: "€0 – €50", min: 0, max: 50 },
		{ id: "50-100", label: "€50 – €100", min: 50, max: 100 },
		{ id: "100-200", label: "€100 – €200", min: 100, max: 200 },
		{ id: "200+", label: "€200+", min: 200, max: Infinity },
	];

	const getCsrf = () =>
		document.querySelector('meta[name="csrf-token"]')?.content ||
		document.querySelector('input[name="csrf"]')?.value ||
		"";

	function showToast(msg, bad = false) {
		if (!toast) return;
		toast.textContent = msg;
		toast.classList.toggle("toast--bad", bad);
		toast.hidden = false;
		clearTimeout(showToast._t);
		showToast._t = setTimeout(() => (toast.hidden = true), 2500);
	}

	function catTokens(cat) {
		return String(cat || "")
			.split(",")
			.map((s) => s.trim().toLowerCase())
			.filter(Boolean);
	}

	function prettyToken(t) {
		return String(t)
			.split(" ")
			.map((w) => w.charAt(0).toUpperCase() + w.slice(1))
			.join(" ");
	}

	async function api(url, options = {}) {
		const method = (options.method || "GET").toUpperCase();
		const csrf = getCsrf();

		const res = await fetch(url, {
			credentials: "same-origin",
			headers: {
				Accept: "application/json",
				...(options.headers || {}),
				...(method !== "GET" && csrf ? { "X-CSRF-TOKEN": csrf } : {}),
			},
			...options,
		});

		const data = await res.json().catch(() => ({}));
		if (!res.ok)
			throw new Error(data?.error || data?.message || "Request failed");
		return data;
	}

	function updateFavBadge(count) {
		if (!favCountEl) return;
		favCountEl.textContent = String(count);
		favCountEl.hidden = Number(count) <= 0;
	}

	async function refreshFavState() {
		try {
			const data = await api("/api/favourites/products"); 
			const list = data.products || [];

			favSet.clear();
			for (const p of list) favSet.add(Number(p.productId));

			updateFavBadge(favSet.size);

			// update buttons if grid already rendered
			grid?.querySelectorAll(".card__fav[data-id]").forEach((btn) => {
				const id = Number(btn.dataset.id);
				const on = favSet.has(id);
				btn.classList.toggle("is-on", on);
				btn.setAttribute("aria-pressed", on ? "true" : "false");
				btn.title = on ? "Remove from favourites" : "Add to favourites";
			});
		} catch {
			// ignore if not logged in, etc.
			updateFavBadge(0);
		}
	}

	function money(v) {
		const n = Number(v);
		return Number.isFinite(n) ? `€${n.toFixed(2)}` : `€${v}`;
	}

	function render(list) {
		if (!grid) return;

		if (!list.length) {
			grid.innerHTML = `
        <div class="empty-state">
          <div class="empty-state__icon">🔍</div>
          <p class="empty-state__text">No products found</p>
        </div>`;
			return;
		}

		grid.innerHTML = list
			.map((p) => {
				const id = Number(p.productId);
				const isOn = favSet.has(id);

				return `
          <article class="card">
            <button
              class="card__fav ${isOn ? "is-on" : ""}"
              data-id="${id}"
              type="button"
              aria-pressed="${isOn ? "true" : "false"}"
              title="${isOn ? "Remove from favourites" : "Add to favourites"}"
            >♥</button>

            <a class="card__link" href="/products/${id}">
              <img class="card__img"
                   src="${p.image ?? "https://via.placeholder.com/400x400"}"
                   alt="${p.productName ?? "Product"}">

              <h2 class="card__title">${p.productName ?? "Untitled"}</h2>

              <div class="card__meta">
                <span class="card__price">${money(p.price)}</span>
                <span class="card__category">${p.category ?? ""}</span>
              </div>
            </a>
          </article>
        `;
			})
			.join("");
	}

	function buildCategories() {
		if (!categoryBox) return;

		const tokens = [
			...new Set(products.flatMap((p) => catTokens(p.category))),
		].sort((a, b) => a.localeCompare(b));

		categoryBox.innerHTML = tokens
			.map(
				(t) => `
        <label class="filters__item">
          <input type="checkbox" value="${t}">
          ${prettyToken(t)}
        </label>
      `
			)
			.join("");
	}

	function buildPriceRanges() {
		if (!priceBox) return;

		priceBox.innerHTML = PRICE_RANGES.map(
			(r) => `
        <label class="filters__item">
          <input type="checkbox" data-min="${r.min}" data-max="${r.max}">
          ${r.label}
        </label>
      `
		).join("");
	}

	function applyFilters() {
		const q = (search?.value ?? "").toLowerCase();

		const selectedCats = categoryBox
			? [...categoryBox.querySelectorAll("input:checked")].map((i) => i.value)
			: [];

		const selectedRanges = priceBox
			? [...priceBox.querySelectorAll("input:checked")].map((i) => ({
					min: Number(i.dataset.min),
					max: Number(i.dataset.max),
			  }))
			: [];

		const min = minPrice?.value ? Number(minPrice.value) : null;
		const max = maxPrice?.value ? Number(maxPrice.value) : null;

		const filtered = products.filter((p) => {
			const name = (p.productName ?? "").toLowerCase();
			const desc = (p.description ?? "").toLowerCase();
			const cat = (p.category ?? "").toLowerCase();
			const price = Number(p.price);

			if (
				q &&
				!name.includes(q) &&
				!desc.includes(q) &&
				!cat.includes(q) &&
				!String(price).includes(q)
			)
				return false;

			if (selectedCats.length) {
				const tokens = catTokens(p.category);
				const match = selectedCats.some((c) =>
					tokens.includes(String(c).toLowerCase())
				);
				if (!match) return false;
			}

			if (selectedRanges.length) {
				const matchRange = selectedRanges.some(
					(r) => price >= r.min && price <= r.max
				);
				if (!matchRange) return false;
			}

			if (min !== null && price < min) return false;
			if (max !== null && price > max) return false;

			return true;
		});

		render(filtered);
	}

	async function loadProducts() {
		try {
			const res = await fetch(PRODUCTS_URL, { credentials: "same-origin" });
			if (!res.ok) throw new Error(`Failed to load products (${res.status})`);
			products = await res.json();

			buildCategories();
			buildPriceRanges();

			// render once (fav state may still be empty), then refresh fav state and re-render
			render(products);
			await refreshFavState();
			render(products);
		} catch (e) {
			console.error(e);
			if (grid) {
				grid.innerHTML = `
          <div class="empty-state">
            <div class="empty-state__icon">⚠️</div>
            <p class="empty-state__text">${e.message}</p>
          </div>`;
			}
		}
	}

	// --- events ---
	search?.addEventListener("input", applyFilters);
	categoryBox?.addEventListener("change", applyFilters);
	priceBox?.addEventListener("change", applyFilters);
	minPrice?.addEventListener("input", applyFilters);
	maxPrice?.addEventListener("input", applyFilters);

	clearFilters?.addEventListener("click", () => {
		if (search) search.value = "";
		if (minPrice) minPrice.value = "";
		if (maxPrice) maxPrice.value = "";

		document
			.querySelectorAll("#filterPanel input[type=checkbox]")
			.forEach((c) => (c.checked = false));
		applyFilters();
	});

	filterToggle?.addEventListener("click", () => {
		filterPanel?.classList.toggle("is-open");
	});

	filterClose?.addEventListener("click", () => {
		filterPanel?.classList.remove("is-open");
	});

	// Heart click handler 
	grid?.addEventListener("click", async (e) => {
		const btn = e.target.closest(".card__fav");
		if (!btn) return;

		const id = Number(btn.dataset.id || 0);
		if (!id) return;

		try {
			btn.disabled = true;

			const csrf = getCsrf();
			const body = new URLSearchParams({ productId: String(id) });
			if (csrf) body.set("csrf", csrf);

			const data = await api("/api/favourites/toggle", {
				method: "POST",
				headers: { "Content-Type": "application/x-www-form-urlencoded" },
				body,
			});

			// toggle local state for instant UI
			const on = !!data.favourited;
			if (on) favSet.add(id);
			else favSet.delete(id);

			btn.classList.toggle("is-on", on);
			btn.setAttribute("aria-pressed", on ? "true" : "false");
			btn.title = on ? "Remove from favourites" : "Add to favourites";

			updateFavBadge(favSet.size);
			window.dispatchEvent(new Event("favouritesUpdated"));
			showToast(
				data.message ||
					(on ? "Added to favourites ✓" : "Removed from favourites ✓"),
				false
			);
		} catch (err) {
			console.error(err);
			showToast(err.message || "Failed to update favourite", true);
		} finally {
			btn.disabled = false;
		}
	});

	loadProducts();
});
