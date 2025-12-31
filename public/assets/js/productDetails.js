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
	const stockInfo = document.querySelector(".stock-info");
	const addBtn = document.getElementById("addBtn");
	const toast = document.getElementById("toast");
	const favBtn = document.getElementById("favBtn");

	// ===== Favorites Management (Cookie-based) =====
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

	// ===== Toast Notification =====
	function setToast(msg, ok = true) {
		toast.hidden = false;
		toast.textContent = msg;
		toast.classList.toggle("toast--bad", !ok);
		setTimeout(() => (toast.hidden = true), 2200);
	}

	// ===== Find Variant by Size and Color =====
	function findVariant() {
		const s = sizeSel.value;
		const c = colorSel.value;
		if (!s || !c) return null;
		return variants.find((v) => v.size === s && v.color === c) || null;
	}

	// ===== Update UI Based on Selection =====
	function updateUI() {
		const v = findVariant();

		// Reset stock info classes
		stockInfo.classList.remove("in-stock", "out-of-stock");

		if (!sizeSel.value || !colorSel.value) {
			stockMsg.textContent = "Pick size + color to see stock.";
			addBtn.disabled = true;
			return;
		}

		if (!v) {
			stockMsg.textContent = "That combination doesn't exist.";
			addBtn.disabled = true;
			return;
		}

		if (v.stock <= 0) {
			stockMsg.textContent = "Out of stock.";
			stockInfo.classList.add("out-of-stock");
			addBtn.disabled = true;
			return;
		}

		// In stock
		const stockText =
			v.stock === 1 ? "1 item available" : `${v.stock} items available`;
		stockMsg.textContent = stockText;
		stockInfo.classList.add("in-stock");
		addBtn.disabled = false;

		// Update qty input max
		qty.max = v.stock;
		if (Number(qty.value) > v.stock) {
			qty.value = v.stock;
		}
	}

	// ===== Initialize Favorite Button =====
	const initFav = fav.has(productId);
	favBtn.classList.toggle("is-on", initFav);
	favBtn.setAttribute("aria-pressed", initFav ? "true" : "false");

	favBtn.addEventListener("click", () => {
		const on = fav.toggle(productId);
		favBtn.classList.toggle("is-on", on);
		favBtn.setAttribute("aria-pressed", on ? "true" : "false");
		setToast(on ? "Saved to favourites ❤️" : "Removed from favourites.");
	});

	// ===== Event Listeners =====
	sizeSel.addEventListener("change", updateUI);
	colorSel.addEventListener("change", updateUI);

	// FIXED: Validate on blur (when user leaves the field)
	qty.addEventListener("blur", () => {
		const v = findVariant();
		const value = qty.value.trim();

		// If empty or invalid, set to 1
		if (value === "" || Number(value) < 1 || isNaN(Number(value))) {
			qty.value = 1;
			return;
		}

		// If exceeds stock, set to stock limit
		if (v && Number(value) > v.stock) {
			qty.value = v.stock;
			setToast(`Only ${v.stock} available`, false);
		}
	});

	// FIXED: Allow clearing but prevent negative numbers while typing
	qty.addEventListener("input", () => {
		// Only prevent negative sign, allow empty field
		if (qty.value.includes("-")) {
			qty.value = qty.value.replace("-", "");
		}
	});

	// Initial UI update
	updateUI();

	// ===== Add to Basket Form Submission =====
	document.getElementById("buyForm").addEventListener("submit", async (e) => {
		e.preventDefault();
		const v = findVariant();
		if (!v) {
			setToast("Please select size and color", false);
			return;
		}

		const quantity = Math.max(1, Number(qty.value || 1));

		// Check if quantity exceeds stock
		if (quantity > v.stock) {
			setToast(`Only ${v.stock} items available`, false);
			qty.value = v.stock;
			return;
		}

		// Disable button during request
		addBtn.disabled = true;
		const originalHTML = addBtn.innerHTML;
		addBtn.innerHTML = `
			<svg class="btn-icon" style="animation: spin 0.8s linear infinite;" viewBox="0 0 24 24" fill="none" stroke="currentColor">
				<circle cx="12" cy="12" r="10" stroke-opacity="0.25"/>
				<path d="M12 2a10 10 0 0 1 10 10" stroke-linecap="round"/>
			</svg>
			Adding...
		`;

		const payload = new URLSearchParams({
			productId: String(productId),
			variantId: String(v.variantId),
			qty: String(quantity),
		});

		try {
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

			// Reset quantity to 1 after successful add
			qty.value = 1;

			// Optional: Update cart count in header if you have one
			updateCartCount();
		} catch (error) {
			setToast("Network error. Please try again.", false);
		} finally {
			// Re-enable button
			addBtn.innerHTML = originalHTML;
			updateUI(); // This will properly set disabled state based on selection
		}
	});

	// ===== Update Cart Count (Optional) =====
	function updateCartCount() {
		// If you have a cart count badge in your header, update it here
		const cartBadge = document.querySelector(".cart-count");
		if (cartBadge) {
			// Fetch cart count from server or increment the existing value
			const currentCount = parseInt(cartBadge.textContent || "0");
			cartBadge.textContent = currentCount + 1;

			// Add animation
			cartBadge.style.animation = "none";
			setTimeout(() => {
				cartBadge.style.animation = "pulse 0.3s ease";
			}, 10);
		}
	}

	// ===== Similar Products Favorite Buttons =====
	document.querySelectorAll(".product-card-fav").forEach((btn) => {
		const productId = Number(btn.dataset.productId);

		// Initialize state
		const isFav = fav.has(productId);
		btn.classList.toggle("is-on", isFav);
		btn.setAttribute("aria-pressed", isFav ? "true" : "false");

		// Handle click
		btn.addEventListener("click", (e) => {
			e.preventDefault();
			e.stopPropagation();

			const on = fav.toggle(productId);
			btn.classList.toggle("is-on", on);
			btn.setAttribute("aria-pressed", on ? "true" : "false");
			setToast(on ? "Saved to favourites ❤️" : "Removed from favourites.");
		});
	});

	// ===== Keyboard Navigation Enhancement =====
	document.addEventListener("keydown", (e) => {
		// Press 'f' to toggle favorite
		if (
			e.key === "f" &&
			!e.ctrlKey &&
			!e.metaKey &&
			document.activeElement.tagName !== "INPUT" &&
			document.activeElement.tagName !== "SELECT"
		) {
			favBtn.click();
		}
	});
});

// ===== CSS Animation for Spinner =====
const style = document.createElement("style");
style.textContent = `
	@keyframes spin {
		to { transform: rotate(360deg); }
	}
	@keyframes pulse {
		0%, 100% { transform: scale(1); }
		50% { transform: scale(1.2); }
	}
`;
document.head.appendChild(style);
