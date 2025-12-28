/** @format */

document.addEventListener("DOMContentLoaded", () => {
	const root = document.querySelector(".product");
	if (!root) return;

	const productId = Number(root.dataset.productId);
	const variants = JSON.parse(root.dataset.variants || "[]");

	const sizeSel = document.getElementById("size");
	const colorSel = document.getElementById("color");
	const qty = document.getElementById("qty");
	const stockMsg = document.getElementById("stockMsg");
	const addBtn = document.getElementById("addBtn");
	const toast = document.getElementById("toast");
	const favBtn = document.getElementById("favBtn");

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

	function setToast(msg, ok = true) {
		toast.hidden = false;
		toast.textContent = msg;
		toast.classList.toggle("toast--bad", !ok);
		setTimeout(() => (toast.hidden = true), 2200);
	}

	function findVariant() {
		const s = sizeSel.value;
		const c = colorSel.value;
		if (!s || !c) return null;
		return variants.find((v) => v.size === s && v.color === c) || null;
	}

	function updateUI() {
		const v = findVariant();
		if (!sizeSel.value || !colorSel.value) {
			stockMsg.textContent = "Pick size + colour to see stock.";
			addBtn.disabled = true;
			return;
		}
		if (!v) {
			stockMsg.textContent = "That combo doesn’t exist.";
			addBtn.disabled = true;
			return;
		}
		if (v.stock <= 0) {
			stockMsg.textContent = "Out of stock.";
			addBtn.disabled = true;
			return;
		}
		stockMsg.textContent = `${v.stock} available.`;
		addBtn.disabled = false;
	}

	// init fav button
	const initFav = fav.has(productId);
	favBtn.classList.toggle("is-on", initFav);
	favBtn.setAttribute("aria-pressed", initFav ? "true" : "false");

	favBtn.addEventListener("click", () => {
		const on = fav.toggle(productId);
		favBtn.classList.toggle("is-on", on);
		favBtn.setAttribute("aria-pressed", on ? "true" : "false");
		setToast(on ? "Saved to favourites." : "Removed from favourites.");
	});

	sizeSel.addEventListener("change", updateUI);
	colorSel.addEventListener("change", updateUI);
	updateUI();

	document.getElementById("buyForm").addEventListener("submit", async (e) => {
		e.preventDefault();
		const v = findVariant();
		if (!v) return;

		const payload = new URLSearchParams({
			productId: String(productId),
			variantId: String(v.variantId),
			qty: String(Math.max(1, Number(qty.value || 1))),
		});

		const res = await csrfFetch("/api/cart/add", {
			method: "POST",
			headers: { "Content-Type": "application/x-www-form-urlencoded" },
			body: payload.toString(),
		});

		const data = await res.json().catch(() => ({}));
		if (!res.ok) {
			setToast(data.error || "Could not add to cart.", false);
			return;
		}

		setToast("Added to basket ✅");
	});
});
