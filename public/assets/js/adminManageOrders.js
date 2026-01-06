/** @format */
document.addEventListener("DOMContentLoaded", () => {
	const adminNavToggle = document.getElementById("adminNavToggle");
	const adminNavItems = document.getElementById("adminNavItems");
	adminNavToggle?.addEventListener("click", () => {
		adminNavItems?.classList.toggle("active");
	});

	const okBox = document.getElementById("ordOk");
	const errBox = document.getElementById("ordErr");
	const toast = (el, msg) => {
		if (!el) return;
		el.textContent = msg;
		el.hidden = false;
		window.scrollTo({ top: 0, behavior: "smooth" });
		clearTimeout(el._t);
		el._t = setTimeout(() => (el.hidden = true), 2500);
	};

	const loading = document.getElementById("ordersLoading");
	const tableWrap = document.getElementById("ordersTableWrap");
	const empty = document.getElementById("ordersEmpty");
	const tbody = document.getElementById("ordersTbody");

	const q = document.getElementById("orderSearch");
	const statusFilter = document.getElementById("statusFilter");

	let orders = [];

	const money = (n) => {
		const num = Number(n);
		if (Number.isNaN(num)) return String(n);
		return new Intl.NumberFormat("nl-NL", {
			style: "currency",
			currency: "EUR",
		}).format(num);
	};

	const normalizeList = (json) => {
		if (Array.isArray(json)) return json;
		if (Array.isArray(json?.orders)) return json.orders;
		if (Array.isArray(json?.data)) return json.data;
		return [];
	};

	const statusClass = (s) => {
		const v = String(s || "").toLowerCase();
		if (["cancelled", "canceled", "failed"].includes(v))
			return "statusPill--off";
		if (["pending", "processing"].includes(v)) return "statusPill--warn";
		return "statusPill--ok";
	};

	const render = () => {
		const term = (q?.value || "").trim().toLowerCase();
		const st = statusFilter?.value || "all";

		const filtered = orders.filter((o) => {
			const id = String(o.orderId ?? o.id ?? "");
			const status = String(o.status ?? o.orderStatus ?? "").toLowerCase();
			const hay = `${id} ${status}`.toLowerCase();

			const okTerm = !term || hay.includes(term);
			const okStatus = st === "all" || status === st;
			return okTerm && okStatus;
		});

		tbody.innerHTML = "";

		if (!filtered.length) {
			tableWrap.hidden = true;
			empty.hidden = false;
			return;
		}

		empty.hidden = true;
		tableWrap.hidden = false;

		filtered.forEach((o) => {
			const id = o.orderId ?? o.id;
			const status = o.status ?? o.orderStatus ?? "pending";
			const total = o.total ?? o.totalAmount ?? o.grandTotal ?? 0;
			const created = o.createdAt ?? o.created_at ?? "";

			const tr = document.createElement("tr");
			tr.className = "orderRow";
			tr.dataset.orderId = String(id);

			tr.innerHTML = `
				<td class="nowrap"><strong>#${id}</strong></td>
				<td><span class="statusPill ${statusClass(status)}">${String(
				status
			)}</span></td>
				<td class="nowrap">${money(total)}</td>
				<td class="nowrap muted">${String(created)}</td>
				<td class="text-right nowrap">
					<a class="btn btn--ghost btn--sm" href="/orders/${id}">Open</a>
					<button class="btn btn--secondary btn--sm jsItems" type="button">Items</button>
				</td>
			`;

			tbody.appendChild(tr);
		});
	};

	const toggleItemsRow = async (orderId, afterTr) => {
		const existing = document.querySelector(
			`tr.orderRowDetails[data-for="${orderId}"]`
		);
		if (existing) {
			existing.remove();
			return;
		}

		const detailsTr = document.createElement("tr");
		detailsTr.className = "orderRowDetails";
		detailsTr.dataset.for = orderId;

		const td = document.createElement("td");
		td.colSpan = 5;
		td.innerHTML = `<div class="loading"><div class="loading__spinner"></div><p class="muted">Loading items…</p></div>`;
		detailsTr.appendChild(td);

		afterTr.insertAdjacentElement("afterend", detailsTr);

		try {
			const res = await fetch(`/admin/orders/${orderId}/items/api`);
			if (!res.ok) throw new Error("Could not load items.");
			const json = await res.json();
			const items = Array.isArray(json)
				? json
				: json?.items || json?.data || [];

			if (!items.length) {
				td.innerHTML = `<div class="orderItems"><div class="muted">No items found for this order.</div></div>`;
				return;
			}

			const wrap = document.createElement("div");
			wrap.className = "orderItems";

			items.forEach((it) => {
				const name =
					it.productName ?? it.name ?? `Product #${it.productId ?? ""}`;
				const qty = it.quantity ?? 0;
				const price = it.price ?? it.unitPrice ?? 0;
				const line = document.createElement("div");
				line.className = "orderItemLine";
				line.innerHTML = `
					<div>
						<strong>${String(name)}</strong>
						<div class="muted">Qty: ${qty}</div>
					</div>
					<div class="nowrap"><strong>${money(price)}</strong></div>
				`;
				wrap.appendChild(line);
			});

			td.innerHTML = "";
			td.appendChild(wrap);
		} catch (e) {
			td.innerHTML = `<div class="orderItems"><div class="notice notice--error"> ${
				e?.message || "Could not load items."
			}</div></div>`;
		}
	};

	const load = async () => {
		try {
			loading.hidden = false;
			tableWrap.hidden = true;
			empty.hidden = true;

			const res = await fetch("/admin/orders/api");
			if (!res.ok) throw new Error("Could not load orders.");
			const json = await res.json();

			orders = normalizeList(json);
			render();
		} catch (e) {
			toast(errBox, e?.message || "Could not load orders.");
			empty.hidden = false;
		} finally {
			loading.hidden = true;
		}
	};

	[q, statusFilter].forEach((el) => el?.addEventListener("input", render));

	tbody?.addEventListener("click", (e) => {
		const btn = e.target.closest(".jsItems");
		if (!btn) return;
		const tr = e.target.closest("tr");
		const orderId = tr?.dataset.orderId;
		if (!orderId || !tr) return;
		toggleItemsRow(orderId, tr);
	});

	load();
});
