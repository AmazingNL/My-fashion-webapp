/** @format */

(() => {
	if (window.__manageOrdersLoaded) return;
	window.__manageOrdersLoaded = true;

	const alertBox = document.getElementById("ordersAlert");
	const csrf = document.getElementById("csrfToken")?.value || "";

	const showAlert = (msg, type = "success") => {
		if (!alertBox) return;
		alertBox.textContent = msg;
		alertBox.style.display = "block";

		alertBox.classList.remove("admin-alert--success", "admin-alert--error");
		alertBox.classList.add(
			type === "error" ? "admin-alert--error" : "admin-alert--success"
		);

		clearTimeout(showAlert._t);
		showAlert._t = setTimeout(() => {
			alertBox.style.display = "none";
			alertBox.textContent = "";
		}, 2500);
	};

	const setLoading = (orderId, on) => {
		const spinner = document.querySelector(
			`[data-status-spinner="${orderId}"]`
		);
		const select = document.querySelector(
			`.statusSelect[data-order-id="${orderId}"]`
		);
		if (spinner) spinner.style.display = on ? "inline-block" : "none";
		if (select) select.disabled = !!on;
	};

	const setPill = (orderId, status) => {
		const pill = document.querySelector(`[data-status-pill="${orderId}"]`);
		if (!pill) return;
		pill.textContent = status;
		pill.setAttribute("data-status", status);
	};

	const updateStatus = async (orderId, status) => {
		const res = await fetch(`/admin/orders/${orderId}/status/api`, {
			method: "POST",
			headers: {
				"Content-Type": "application/json",
				Accept: "application/json",
				"X-CSRF-Token": csrf,
			},
			credentials: "same-origin",
			body: JSON.stringify({ status }),
		});

		const data = await res.json().catch(() => ({}));
		if (!res.ok) {
			throw new Error(data?.error || "Could not update order status.");
		}
		return data;
	};

	document.addEventListener("change", async (e) => {
		const select = e.target.closest(".statusSelect");
		if (!select) return;

		const orderId = select.dataset.orderId;
		const next = (select.value || "").toLowerCase();
		const prev = (select.dataset.originalStatus || "").toLowerCase();

		if (!orderId) {
			showAlert("Order ID missing on status dropdown.", "error");
			return;
		}

		if (next === prev) return;

		setLoading(orderId, true);
		setPill(orderId, next);

		try {
			await updateStatus(orderId, next);
			select.dataset.originalStatus = next;
			showAlert(`Order #${orderId} updated to ${next}.`, "success");
		} catch (err) {
			select.value = prev || "pending";
			setPill(orderId, prev || "pending");
			showAlert(err.message || "Update failed.", "error");
		} finally {
			setLoading(orderId, false);
		}
	});
})();
