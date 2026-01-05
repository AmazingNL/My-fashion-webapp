/** @format */
document.addEventListener("DOMContentLoaded", () => {
	const dataEl = document.getElementById("orderDetailsData");
	const listEl = document.getElementById("itemsList");
	const emptyEl = document.getElementById("itemsEmpty");
	const errBox = document.getElementById("itemsError");
	const totalEl = document.getElementById("itemsTotal");
	const refreshBtn = document.getElementById("refreshItemsBtn");

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

	const escapeHtml = (str) =>
		String(str)
			.replaceAll("&", "&amp;")
			.replaceAll("<", "&lt;")
			.replaceAll(">", "&gt;")
			.replaceAll('"', "&quot;")
			.replaceAll("'", "&#039;");

	let orderId = 0;
	try {
		const parsed = JSON.parse(dataEl?.textContent || "{}");
		orderId = Number(parsed.orderId || 0);
	} catch {}

	function render(items) {
		if (!listEl) return;

		if (!items?.length) {
			listEl.innerHTML = "";
			if (emptyEl) emptyEl.hidden = false;
			if (totalEl) totalEl.textContent = money(0);
			return;
		}

		if (emptyEl) emptyEl.hidden = true;

		let sum = 0;

		listEl.innerHTML = items
			.map((it) => {
				const qty = Number(it.quantity || 0);
				const price = Number(it.price || 0);
				const sub = Number(it.subtotal ?? qty * price);
				sum += sub;

				const name = it.productName || `Product #${it.productId}`;
				const category = it.productCategory || it.category || ""; // fallback if you name it differently

				const variantBits = [it.size, it.color].filter(Boolean).join(" / ");
				const variantLabel = variantBits ? `(${variantBits})` : ""; // if no size/color, show nothing

				return `
                    <div class="orderDetails__itemRow">
                        <div class="orderDetails__itemMain">
                            <div class="orderDetails__itemName">
                                ${escapeHtml(name)}
                                ${category ? `<span class="orderDetails__cat">· ${escapeHtml(category)}</span>`: ""}
                                ${variantLabel? `<span class="orderDetails__variant"> ${escapeHtml(variantLabel)}</span>`: ""}
                            </div>
                            <div class="orderDetails__itemMeta">Qty: ${qty} · Unit: ${money(price)}</div>
                        </div>
                        <div class="orderDetails__itemSub">${money(sub)}</div>
                    </div>`;
			})
			.join("");

		if (totalEl) totalEl.textContent = money(sum);
	}

	async function load() {
		if (errBox) errBox.hidden = true;

		if (!orderId) {
			show(errBox, "Invalid order id.");
			return;
		}

		try {
			const res = await fetch(`/api/orders/${orderId}/items`, {
				credentials: "same-origin",
			});
			const data = await res.json().catch(() => ({}));
			if (!res.ok) throw new Error(data?.error || "Could not load items.");

			render(data.items || []);
		} catch (e) {
			show(errBox, e?.message || "Could not load items.");
		}
	}

	refreshBtn?.addEventListener("click", load);
	load();
});
