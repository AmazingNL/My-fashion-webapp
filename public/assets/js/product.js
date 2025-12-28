/** @format */

document.addEventListener("DOMContentLoaded", async () => {
	const grid = document.getElementById("grid");
	const q = document.getElementById("q");
	const cartCount = document.getElementById("cartCount");

	const fav = {
		get() {
			const raw = document.cookie
				.split("; ")
				.find((x) => x.startsWith("favs="))
				?.split("=")[1];
			if (!raw) return [];
			try {
				return JSON.parse(decodeURIComponent(raw));
			} catch {
				return [];
			}
		},
		has(id) {
			return this.get().includes(id);
		},
		toggle(id) {
			const list = this.get();
			const next = list.includes(id)
				? list.filter((x) => x !== id)
				: [...list, id];
			document.cookie = `favs=${encodeURIComponent(
				JSON.stringify(next)
			)}; Path=/; Max-Age=31536000; SameSite=Lax`;
			return next.includes(id);
		},
	};

	let products = [];

	function card(p) {
		const isFav = fav.has(p.productId);
		return `
      <article class="card" data-name="${escapeHtml(p.name)}">
        <a class="card__link" href="/products/show?id=${p.productId}">
          <img class="card__img" src="${escapeHtml(p.image)}" alt="${escapeHtml(
			p.name
		)}">
          <h2 class="card__title">${escapeHtml(p.name)}</h2>
          <p class="card__meta">€${Number(p.price).toFixed(2)} • ${escapeHtml(
			p.category
		)}</p>
        </a>

        <button class="card__fav ${
					isFav ? "is-on" : ""
				}" type="button" data-fav="${p.productId}"
          aria-pressed="${
						isFav ? "true" : "false"
					}" title="Favourite">♥</button>
      </article>
    `;
	}

	function render(list) {
		if (!list.length) {
			grid.innerHTML = `<p class="muted">No products found.</p>`;
			return;
		}
		grid.innerHTML = list.map(card).join("");
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

	// load catalogue from API (JSON)
	const res = await csrfFetch("/products");
	products = await res.json();
	render(products);

	// favourite toggle (cookie)
	grid.addEventListener("click", (e) => {
		const btn = e.target.closest("[data-fav]");
		if (!btn) return;
		const id = Number(btn.dataset.fav);
		const on = fav.toggle(id);
		btn.classList.toggle("is-on", on);
		btn.setAttribute("aria-pressed", on ? "true" : "false");
	});

	// search filter
	q.addEventListener("input", () => {
		const term = q.value.trim().toLowerCase();
		const filtered = products.filter((p) =>
			p.name.toLowerCase().includes(term)
		);
		render(filtered);
	});

	// optional: show cart count if you expose it somewhere
	if (cartCount) cartCount.textContent = cartCount.textContent || "0";
});
