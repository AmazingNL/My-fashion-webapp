/** @format */
document.addEventListener("DOMContentLoaded", () => {
	const dataEl = document.getElementById("checkoutData");
	const listEl = document.getElementById("summaryList");
	const totalEl = document.getElementById("summaryTotal");

	const form = document.getElementById("checkoutForm");
	const errBox = document.getElementById("checkoutError");
	const okBox = document.getElementById("checkoutSuccess");

	const show = (el, msg) => {
		if (!el) return;
		el.textContent = msg;
		el.hidden = false;
		window.scrollTo({ top: 0, behavior: "smooth" });
		setTimeout(() => (el.hidden = true), 3500);
	};

	const money = (n) =>
		new Intl.NumberFormat("nl-NL", {
			style: "currency",
			currency: "EUR",
		}).format(Number(n || 0));

	let payload = null;

	try {
		payload = JSON.parse(dataEl?.textContent || "");
	} catch {
		payload = null;
	}

	if (!payload || typeof payload !== "object") {
		payload = { items: [], total: null };
	}

	// Render summary
	if (listEl) {
		listEl.innerHTML = (payload.items || [])
			.map((it) => {
				const name = it.variantLabel
					? `${it.name} (${it.variantLabel})`
					: it.name;
				return `
					<div class="checkout__row">
						<div class="checkout__rowMain">
							<div class="checkout__rowName">${escapeHtml(name)}</div>
							<div class="checkout__rowMeta">Qty: ${Number(it.qty || 1)}</div>
						</div>
						<div class="checkout__rowPrice">${money(it.price)}</div>
					</div>
				`;
			})
			.join("");
	}

	if (totalEl) {
		const n = Number(payload?.total);
		if (!Number.isNaN(n)) totalEl.textContent = money(n);
	}

	form?.addEventListener("submit", async (e) => {
		e.preventDefault();
		errBox && (errBox.hidden = true);
		okBox && (okBox.hidden = true);

		const shippingAddress = document
			.getElementById("shippingAddress")
			?.value.trim();
		const billingAddress = document
			.getElementById("billingAddress")
			?.value.trim();
		const paymentMethod =
			document.getElementById("paymentMethod")?.value || "credit_card";

		if (!shippingAddress) return show(errBox, "Shipping address is required.");

		const body = new URLSearchParams();
		body.set("shippingAddress", shippingAddress);
		if (billingAddress) body.set("billingAddress", billingAddress);
		body.set("paymentMethod", paymentMethod);

		try {
			// main layout already loads csrfFetch + meta csrf token
			const res = await window.csrfFetch("/checkout/place", {
				method: "POST",
				headers: { "Content-Type": "application/x-www-form-urlencoded" },
				body,
			});

			const data = await res.json().catch(() => ({}));
			if (!res.ok) throw new Error(data?.error || "Failed to place order.");

			show(okBox, data?.message || "Order placed successfully!");
			setTimeout(() => (window.location.href = "/orders"), 900);
		} catch (err) {
			show(errBox, err?.message || "Something went wrong.");
		}
	});

	function escapeHtml(str) {
		return String(str)
			.replaceAll("&", "&amp;")
			.replaceAll("<", "&lt;")
			.replaceAll(">", "&gt;")
			.replaceAll('"', "&quot;")
			.replaceAll("'", "&#039;");
	}
});
