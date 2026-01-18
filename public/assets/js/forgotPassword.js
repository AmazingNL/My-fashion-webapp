/** @format */
document.addEventListener("DOMContentLoaded", () => {
	const form = document.querySelector("#forgotForm");
	const errBox = document.querySelector("#formErrors");
	const btn = document.querySelector("#sendBtn");
	if (!form) return;

	const showError = (msg) => {
		if (!errBox) return;
		errBox.textContent = msg || "Something went wrong.";
		errBox.hidden = false;
	};

	form.addEventListener("submit", async (e) => {
		e.preventDefault();
		if (errBox) errBox.hidden = true;
		btn && (btn.disabled = true);

		try {
			const res = await csrfFetch(form.action, {
				method: "POST",
				body: new FormData(form),
				headers: { Accept: "application/json" },
			});

			const data = await res.json().catch(() => ({}));

			if (!res.ok) {
				const msg = data?.errors
					? Object.values(data.errors).join(" ")
					: data.error || "Failed.";
				showError(msg);
				return;
			}

			if (data.redirect) window.location.href = data.redirect;
		} catch {
			showError("Network error. Please try again.");
		} finally {
			btn && (btn.disabled = false);
		}
	});
});
