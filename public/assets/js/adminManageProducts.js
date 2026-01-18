/** @format */
document.addEventListener("DOMContentLoaded", () => {
	const adminNavToggle = document.getElementById("adminNavToggle");
	const adminNavItems = document.getElementById("adminNavItems");
	adminNavToggle?.addEventListener("click", () => {
		adminNavItems?.classList.toggle("active");
	});

	const okBox = document.getElementById("prodOk");
	const errBox = document.getElementById("prodErr");
	const toast = (el, msg) => {
		if (!el) return;
		el.textContent = msg;
		el.hidden = false;
		window.scrollTo({ top: 0, behavior: "smooth" });
		clearTimeout(el._t);
		el._t = setTimeout(() => (el.hidden = true), 2500);
	};

	const csrf = document.querySelector('meta[name="csrf-token"]')?.content || "";

	const postForm = async (url, data) => {
		const body = new URLSearchParams();
		Object.entries(data).forEach(([k, v]) => body.set(k, String(v)));

		const res = await fetch(url, {
			method: "POST",
			headers: {
				"Content-Type": "application/x-www-form-urlencoded;charset=UTF-8",
				"X-CSRF-Token": csrf,
			},
			body,
		});

		const txt = await res.text();
		let json = null;
		try {
			json = JSON.parse(txt);
		} catch {}

		if (!res.ok) {
			const msg = json?.message || json?.error || "Request failed.";
			throw new Error(msg);
		}
		return json || { success: true };
	};

	// Filtering
	const q = document.getElementById("productSearch");
	const stockFilter = document.getElementById("stockFilter");
	const cards = Array.from(document.querySelectorAll(".admin-prod"));

	const applyFilter = () => {
		const term = (q?.value || "").trim().toLowerCase();
		const st = stockFilter?.value || "all";

		cards.forEach((card) => {
			const hay = card.getAttribute("data-search") || "";
			const band = card.getAttribute("data-stock-band") || "all";

			const okTerm = !term || hay.includes(term);
			const okStock = st === "all" || band === st;

			card.style.display = okTerm && okStock ? "" : "none";
		});
	};

	[q, stockFilter].forEach((el) => el?.addEventListener("input", applyFilter));
	applyFilter();

	// Delete
	document.querySelectorAll(".jsDeleteProduct").forEach((btn) => {
		btn.addEventListener("click", async () => {
			const card = btn.closest(".admin-prod");
			const productId = card?.getAttribute("data-product-id");
			if (!productId) return;

			if (!confirm("Delete this product? This cannot be undone.")) return;

			btn.disabled = true;
			try {
				const json = await postForm("/admin/products/delete", { productId });
				card.remove();
				toast(okBox, json?.message || "Product deleted.");
			} catch (e) {
				toast(errBox, e?.message || "Could not delete product.");
			} finally {
				btn.disabled = false;
			}
		});
	});
});
