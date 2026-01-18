/** @format */
document.addEventListener("DOMContentLoaded", () => {
	const form = document.querySelector("#codeForm");
	const errBox = document.querySelector("#formErrors");
	const btn = document.querySelector("#verifyBtn");
	if (!form) return;

	const showError = (msg) => {
		if (!errBox) return;
		errBox.textContent = msg || "Invalid code.";
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
				showError(data.error || "Invalid or expired code/link.");
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
