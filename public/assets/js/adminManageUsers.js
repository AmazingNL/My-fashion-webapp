/** @format */
document.addEventListener("DOMContentLoaded", () => {
	// Mobile admin nav toggle
	const adminNavToggle = document.getElementById("adminNavToggle");
	const adminNavItems = document.getElementById("adminNavItems");
	adminNavToggle?.addEventListener("click", () => {
		adminNavItems?.classList.toggle("active");
	});

	const okBox = document.getElementById("userOk");
	const errBox = document.getElementById("userErr");

	const toast = (el, msg) => {
		if (!el) return;

		// hide the other toast (so they can't both show)
		const other = el === okBox ? errBox : okBox;
		if (other) {
			other.hidden = true;
			clearTimeout(other._t);
		}

		el.textContent = msg;
		el.hidden = false;

		window.scrollTo({ top: 0, behavior: "smooth" });

		clearTimeout(el._t);
		el._t = setTimeout(() => (el.hidden = true), 2500);
	};

	const postForm = async (url, data) => {
		const body = new URLSearchParams();
		Object.entries(data).forEach(([k, v]) => body.set(k, String(v)));

		const res = await csrfFetch(url, {
			method: "POST",
			headers: {
				"Content-Type": "application/x-www-form-urlencoded;charset=UTF-8",
			},
			body,
		});

		const txt = await res.text();
		let json = null;
		try {
			json = JSON.parse(txt);
		} catch {
			// non-json response
		}

		if (!res.ok) {
			const msg = json?.message || json?.error || "Request failed.";
			throw new Error(msg);
		}
		return json || { success: true };
	};

	// Filtering
	const q = document.getElementById("userSearch");
	const statusFilter = document.getElementById("userStatusFilter");
	const roleFilter = document.getElementById("userRoleFilter");
	const rows = Array.from(document.querySelectorAll("#usersTable tbody tr"));

	const applyFilter = () => {
		const term = (q?.value || "").trim().toLowerCase();
		const st = statusFilter?.value || "all";
		const rl = roleFilter?.value || "all";

		rows.forEach((tr) => {
			const hay = tr.getAttribute("data-search") || "";
			const r = tr.getAttribute("data-role") || "";
			const s = tr.getAttribute("data-status") || "";

			const okTerm = !term || hay.includes(term);
			const okRole = rl === "all" || r === rl;
			const okStatus = st === "all" || s === st;

			tr.style.display = okTerm && okRole && okStatus ? "" : "none";
		});
	};

	[q, statusFilter, roleFilter].forEach((el) =>
		el?.addEventListener("input", applyFilter)
	);
	applyFilter();

	// Status toggle
	document.querySelectorAll(".jsToggleUser").forEach((btn) => {
		if (btn.dataset.bound === "1") return; // <-- guard
		btn.dataset.bound = "1";
		btn.addEventListener("click", async () => {
			const tr = btn.closest("tr");
			const userId = tr?.getAttribute("data-user-id");
			const next = btn.getAttribute("data-next"); // "1" or "0"
			if (!userId) return;

			btn.disabled = true;

			try {
				const json = await postForm("/admin/users/status", {
					userId,
					isActive: next,
				});

				// update UI
				const isActive = next === "1";
				tr.setAttribute("data-status", isActive ? "active" : "inactive");

				const pill = tr.querySelector(".statusPill");
				if (pill) {
					pill.textContent = isActive ? "Active" : "Inactive";
					pill.classList.toggle("statusPill--ok", isActive);
					pill.classList.toggle("statusPill--off", !isActive);
				}

				btn.textContent = isActive ? "Deactivate" : "Activate";
				btn.setAttribute("data-next", isActive ? "0" : "1");

				toast(okBox, json?.message || "User status updated.");
				applyFilter();
			} catch (e) {
				toast(errBox, e?.message || "Could not update user.");
			} finally {
				btn.disabled = false;
			}
		});
	});
});
