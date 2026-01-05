/** @format */
document.addEventListener("DOMContentLoaded", () => {
	const grid = document.getElementById("favGrid");
	const empty = document.getElementById("favEmpty");
	const clearBtn = document.getElementById("clearFavBtn");

	const errBox = document.getElementById("favError");
	const okBox = document.getElementById("favSuccess");

	const getCsrf = () =>
		document.querySelector('meta[name="csrf-token"]')?.content ||
		document.querySelector('input[name="csrf"]')?.value ||
		"";

	const show = (el, msg) => {
		if (!el) return;
		el.textContent = msg;
		el.hidden = false;
		window.scrollTo({ top: 0, behavior: "smooth" });
		setTimeout(() => (el.hidden = true), 2500);
	};

	const money = (n) => {
		const num = Number(n);
		if (Number.isNaN(num)) return n;
		return new Intl.NumberFormat("nl-NL", {
			style: "currency",
			currency: "EUR",
		}).format(num);
	};

	function escapeHtml(str) {
		return String(str)
			.replaceAll("&", "&amp;")
			.replaceAll("<", "&lt;")
			.replaceAll(">", "&gt;")
			.replaceAll('"', "&quot;")
			.replaceAll("'", "&#039;");
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
		if (!res.ok) throw new Error(data?.error || data?.message || "Request failed");
		return data;
	}

	function render(products) {
		if (!grid) return;
		grid.innerHTML = "";

		if (!products || products.length === 0) {
			if (empty) empty.hidden = false;
			if (clearBtn) clearBtn.disabled = true;
			return;
		}

		if (empty) empty.hidden = true;
		if (clearBtn) clearBtn.disabled = false;

		for (const p of products) {
			const img = p.image || "";
			const name = p.productName || p.name || "Untitled";
			const cat = p.category || "";
			const price = money(p.price);
			const stock = typeof p.stock !== "undefined" ? p.stock : "";

			const card = document.createElement("article");
			card.className = "favCard";
			card.innerHTML = `
        <div class="favCard__img">
          ${
						img
							? `<img src="${escapeHtml(img)}" alt="${escapeHtml(name)}">`
							: `<div class="favCard__ph">No image</div>`
					}
        </div>

        <div class="favCard__body">
          <div class="favCard__top">
            <h3 class="favCard__title">${escapeHtml(name)}</h3>
            <span class="favCard__price">${price}</span>
          </div>

          <div class="favCard__meta">
            ${cat ? `<span class="pill">${escapeHtml(cat)}</span>` : ""}
            ${
							stock !== ""
								? `<span class="pill pill--soft">Stock: ${escapeHtml(
										String(stock)
								  )}</span>`
								: ""
						}
          </div>

          <div class="favCard__actions">
            <a class="btn btn--ghost" href="/products/${Number(p.productId)}">View</a>
            <button class="btn btn--danger jsRemove" data-id="${Number(
							p.productId
						)}" type="button">Remove</button>
          </div>
        </div>
      `;
			grid.appendChild(card);
		}
	}

	async function load() {
		if (errBox) errBox.hidden = true;
		if (okBox) okBox.hidden = true;

		try {
			const data = await api("/api/favourites/products");
			render(data.products || []);
		} catch (e) {
			render([]);
			show(errBox, e.message || "Could not load favourites.");
		}
	}

	grid?.addEventListener("click", async (e) => {
		const btn = e.target.closest(".jsRemove");
		if (!btn) return;

		const id = Number(btn.dataset.id || 0);
		if (!id) return;

		try {
			const csrf = getCsrf();
			const body = new URLSearchParams({ productId: String(id) });
			if (csrf) body.set("csrf", csrf);

			const data = await api("/api/favourites/toggle", {
				method: "POST",
				headers: { "Content-Type": "application/x-www-form-urlencoded" },
				body,
			});

			show(okBox, data.message || "Updated.");
			load();
		} catch (err) {
			show(errBox, err.message || "Failed to update favourite.");
		}
	});

	clearBtn?.addEventListener("click", async () => {
		try {
			const csrf = getCsrf();
			const body = new URLSearchParams();
			if (csrf) body.set("csrf", csrf);

			const data = await api("/api/favourites/clear", {
				method: "POST",
				headers: { "Content-Type": "application/x-www-form-urlencoded" },
				body,
			});

			show(okBox, data.message || "Cleared.");
			load();
		} catch (err) {
			show(errBox, err.message || "Failed to clear favourites.");
		}
	});

	load();
});
