/** @format */
/* AFRO-THEMED CART - ENHANCED VISUALS, SAME FUNCTIONALITY */
console.log("CART JS VERSION: 2026-01-05 A");

document.addEventListener("DOMContentLoaded", () => {
	// Run only on cart page
	const page = document.querySelector('[data-page="cart"]');
	if (!page) return;

	const notice = document.getElementById("cartNotice");
	const itemsWrap = document.getElementById("cartItems");
	const clearBtn = document.getElementById("clearCartBtn");

	const summaryTotal = document.getElementById("summaryTotal");
	const summaryItemCount = document.getElementById("summaryItemCount");
	const cartCountText = document.getElementById("cartCountText");
	const checkoutBtn = document.getElementById("checkoutBtn");

	function showNotice(msg, type = "info") {
		if (!notice) return;

		// Enhanced: Add smooth animation
		notice.style.animation = "none";
		setTimeout(() => {
			notice.style.animation = "";
		}, 10);

		notice.hidden = false;
		notice.className = `cartNotice cartNotice--${type}`;
		notice.textContent = msg;
		clearTimeout(showNotice._t);
		showNotice._t = setTimeout(() => {
			// Enhanced: Smooth fade out
			notice.style.opacity = "0";
			notice.style.transform = "translateY(-10px)";
			setTimeout(() => {
				notice.hidden = true;
				notice.style.opacity = "";
				notice.style.transform = "";
			}, 300);
		}, 3500);
	}

	const money = (n) => Number(n || 0).toFixed(2);
	const qs = (root, sel) => (root ? root.querySelector(sel) : null);

	async function postForm(url, data) {
		const body = new URLSearchParams(data);

		const res = await csrfFetch(url, {
			method: "POST",
			headers: { "Content-Type": "application/x-www-form-urlencoded" },
			body,
		});

		const json = await res.json().catch(() => null);

		if (!res.ok || !json || json.success === false) {
			throw new Error(
				(json && json.message) || `Request failed (${res.status})`
			);
		}
		return json;
	}

	function updateSummary(payload) {
		const count = Number(payload.count ?? 0);
		const total = Number(payload.total ?? 0);

		if (checkoutBtn) checkoutBtn.disabled = count <= 0;
		if (summaryItemCount) summaryItemCount.textContent = String(count);
		if (cartCountText) cartCountText.textContent = String(count);
		if (summaryTotal) summaryTotal.textContent = money(total);
		if (clearBtn) clearBtn.disabled = count <= 0;
	}

	checkoutBtn?.addEventListener("click", () => {
		if (checkoutBtn.disabled) return;
		window.location.href = "/checkout";
	});

	function updateLineTotal(card) {
		const unit = Number(card.dataset.unitPrice || 0);
		const qty = Number(qs(card, ".qtyInput")?.value || 1);
		const el = qs(card, ".lineTotalValue");

		if (el) {
			// Enhanced: Add smooth animation when updating
			el.style.transform = "scale(1.15)";
			el.style.transition = "all 0.15s ease";

			setTimeout(() => {
				el.textContent = money(unit * qty);
				setTimeout(() => {
					el.style.transform = "";
				}, 100);
			}, 150);
		}
	}

	async function removeItem(card) {
		const productId = card.dataset.productId;
		const variantId = card.dataset.variantId;

		// Enhanced: Add smooth removal animation
		card.style.opacity = "0";
		card.style.transform = "translateX(-20px) scale(0.95)";
		card.style.transition = "all 0.4s cubic-bezier(0.4, 0, 0.2, 1)";

		try {
			const payload = await postForm("/removeFromBasket", {
				productId,
				variantId,
			});

			// Wait for animation before removing
			setTimeout(() => {
				card.remove();
			}, 400);

			updateSummary(payload);

			showNotice(payload.message || "Item removed", "ok");

			if (Number(payload.count ?? 0) <= 0) {
				setTimeout(() => window.location.reload(), 500);
			}
		} catch (e) {
			// Revert animation on error
			card.style.opacity = "";
			card.style.transform = "";
			showNotice(e.message || "Failed to remove item", "bad");
		}
	}

	async function setQuantity(card, newQty) {
		const productId = card.dataset.productId;
		const variantId = card.dataset.variantId;

		const input = qs(card, ".qtyInput");
		const prevQty = Number(input?.value || 1);

		if (newQty < 1) return removeItem(card);

		try {
			const payload = await postForm("/updateQuantity", {
				productId,
				variantId,
				quantity: String(newQty),
			});

			if (input) input.value = String(newQty);

			updateLineTotal(card);
			updateSummary(payload);

			showNotice(payload.message || "Cart updated", "ok");
		} catch (err) {
			// revert UI and tell user why it failed 
			if (input) input.value = String(prevQty);
			showNotice(err.message || "Could not update quantity", "bad");
		}
	}

	function bindCard(card) {
		const dec = qs(card, ".decBtn");
		const inc = qs(card, ".incBtn");
		const input = qs(card, ".qtyInput");
		const remove = qs(card, ".removeBtn");

		dec?.addEventListener("click", () => {
			const cur = Number(input?.value || 1);
			setQuantity(card, cur - 1);
		});

		inc?.addEventListener("click", () => {
			const cur = Number(input?.value || 1);
			setQuantity(card, cur + 1);
		});

		input?.addEventListener("change", () => {
			let v = Number(input.value || 1);
			if (!Number.isFinite(v) || v < 1) v = 1;
			input.value = String(v);
			setQuantity(card, v);
		});

		remove?.addEventListener("click", () => removeItem(card));
	}

	itemsWrap?.querySelectorAll(".cartItem").forEach(bindCard);

	clearBtn?.addEventListener("click", async () => {
		try {
			const payload = await postForm("/clearBasket", {});
			updateSummary(payload);
			showNotice(payload.message || "Cart cleared", "ok");

			// Enhanced: Fade out items before reload
			const items = itemsWrap?.querySelectorAll(".cartItem");
			items?.forEach((item, index) => {
				setTimeout(() => {
					item.style.opacity = "0";
					item.style.transform = "translateY(20px)";
					item.style.transition = "all 0.4s ease";
				}, index * 100);
			});

			setTimeout(() => window.location.reload(), 800);
		} catch (e) {
			showNotice(e.message || "Failed to clear cart", "bad");
		}
	});
});
