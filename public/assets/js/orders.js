/** @format */
document.addEventListener("DOMContentLoaded", () => {
	const dataEl = document.getElementById("ordersData");
	const grid = document.getElementById("ordersGrid");
	const empty = document.getElementById("ordersEmpty");
	const refreshBtn = document.getElementById("refreshOrdersBtn");

	const filterEl = document.getElementById("statusFilter");
	const searchEl = document.getElementById("searchOrders");

	const errBox = document.getElementById("ordersError");
	const okBox = document.getElementById("ordersSuccess");

	const show = (el, msg) => {
		if (!el) return;
		el.textContent = msg;
		el.hidden = false;
		window.scrollTo({ top: 0, behavior: "smooth" });
		setTimeout(() => (el.hidden = true), 3000);
	};

	const money = (n) =>
		new Intl.NumberFormat("nl-NL", {
			style: "currency",
			currency: "EUR",
		}).format(Number(n || 0));

	let orders = [];
	try {
		const parsed = JSON.parse(dataEl?.textContent || "{}");
		orders = parsed.orders || [];
	} catch {}

	function normalizeStatus(s) {
		return String(s || "")
			.toLowerCase()
			.trim();
	}

	function escapeHtml(str) {
		return String(str)
			.replaceAll("&", "&amp;")
			.replaceAll("<", "&lt;")
			.replaceAll(">", "&gt;")
			.replaceAll('"', "&quot;")
			.replaceAll("'", "&#039;");
	}

	function badge(status) {
		const s = normalizeStatus(status);
		const cls =
			s === "cancelled"
				? "pill pill--danger"
				: s === "delivered"
				? "pill pill--ok"
				: s === "shipped"
				? "pill pill--info"
				: "pill";
		return `<span class="${cls}">${escapeHtml(s || "unknown")}</span>`;
	}

	function canCancel(status) {
		const s = normalizeStatus(status);
		return s === "pending" || s === "processing" || s === "paid";
	}

	function matches(o) {
		const q = String(searchEl?.value || "")
			.toLowerCase()
			.trim();
		const sf = normalizeStatus(filterEl?.value || "");
		const hay = [
			o.orderId,
			o.status,
			o.paymentStatus,
			o.createdAt,
			o.totalAmount,
		]
			.join(" ")
			.toLowerCase();
		return (!sf || normalizeStatus(o.status) === sf) && (!q || hay.includes(q));
	}

	function render() {
		if (!grid) return;

		const list = orders.filter(matches);

		grid.innerHTML = list
			.map((o) => {
				const id = Number(o.orderId);
				const status = normalizeStatus(o.status);
				const pay = normalizeStatus(o.paymentStatus);

				const payClass =
					pay === "completed"
						? "pill pill--ok"
						: pay === "failed"
						? "pill pill--danger"
						: "pill";

				return `
          <article class="card orders__card">
            <div class="orders__cardTop">
              <div class="orders__id">Order #${id}</div>
              <div class="orders__badges">
                ${badge(status)}

				<span class="pill ${payClass}">pay: ${escapeHtml(pay || "pending")}</span>
             </div>
            </div>

            <div class="orders__meta">
              <div><span class="orders__label">Total</span><strong>${money(
								o.totalAmount
							)}</strong></div>
              <div><span class="orders__label">Created</span><strong>${escapeHtml(
								o.createdAt || "-"
							)}</strong></div>
            </div>

            <div class="orders__cardActions">
              <a class="btn btn--ghost" href="/orders/${id}">View</a>
              <button class="btn btn--danger" data-cancel="${id}" ${
					canCancel(status) ? "" : "disabled"
				}>Cancel</button>
            </div>
          </article>
        `;
			})
			.join("");

		const nothing = list.length === 0;
		if (empty) empty.hidden = !nothing;
	}

	async function refreshOrders() {
		try {
			const res = await fetch("/api/orders", { credentials: "same-origin" });
			const data = await res.json().catch(() => ({}));
			if (!res.ok) throw new Error(data?.error || "Failed to load orders.");
			orders = data.orders || [];
			render();
			show(okBox, "Orders refreshed ✓");
		} catch (e) {
			show(errBox, e?.message || "Could not refresh orders.");
		}
	}

	async function cancelOrder(orderId) {
		try {
			const res = await window.csrfFetch(`/orders/${orderId}/cancel`, {
				method: "POST",
			});
			const data = await res.json().catch(() => ({}));
			if (!res.ok) throw new Error(data?.error || "Cancel failed.");
			show(okBox, data?.message || "Order cancelled.");
			await refreshOrders();
		} catch (e) {
			show(errBox, e?.message || "Cancel failed.");
		}
	}

	grid?.addEventListener("click", (e) => {
		const btn = e.target.closest("[data-cancel]");
		if (!btn) return;
		const id = Number(btn.getAttribute("data-cancel"));
		if (!id) return;
		cancelOrder(id);
	});

	filterEl?.addEventListener("change", render);
	searchEl?.addEventListener("input", render);
	refreshBtn?.addEventListener("click", refreshOrders);

	render(); // render initial embedded data
});
