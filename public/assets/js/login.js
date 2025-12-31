/** @format */

document.addEventListener("DOMContentLoaded", () => {
	const form = document.querySelector("#loginForm");
	const errBox = document.querySelector("#formErrors");
	const okBox = document.querySelector("#formSuccess");
	const pass = document.querySelector("#password");
	const email = document.querySelector("#email");
	const loginBtn = document.querySelector("#loginBtn");

	if (!form) return;

	const showError = (msg) => {
		if (!errBox) return;
		errBox.textContent = msg || "Something went wrong. Please try again.";
		errBox.hidden = false;
		if (okBox) okBox.hidden = true;
	};

	const showSuccess = (msg) => {
		if (!okBox) return;
		okBox.textContent = msg || "Logged in successfully.";
		okBox.hidden = false;
		if (errBox) errBox.hidden = true;
	};

	const setLoading = (on) => {
		if (!loginBtn) return;
		loginBtn.classList.toggle("is-loading", !!on);
		loginBtn.disabled = !!on;
	};

	// password toggle
	document.addEventListener("click", (e) => {
		const btn = e.target.closest("[data-toggle='password']");
		if (!btn || !pass) return;

		const hidden = pass.type === "password";
		pass.type = hidden ? "text" : "password";
		btn.setAttribute("aria-label", hidden ? "Hide password" : "Show password");
		btn.title = hidden ? "Hide password" : "Show password";
		btn.textContent = hidden ? "🙈" : "👁️";
	});

	// Ajax login (works with your controller: redirects on success, JSON on error)
	form.addEventListener("submit", async (e) => {
		e.preventDefault();

		const emailVal = (email?.value || "").trim();
		const passVal = (pass?.value || "").trim();

		if (!emailVal || !passVal) {
			showError("Email and password are required.");
			return;
		}

		setLoading(true);
		if (errBox) errBox.hidden = true;
		if (okBox) okBox.hidden = true;

		try {
			const formData = new FormData(form);

			const res = await csrfFetch(form.action, {
				method: "POST",
				body: formData,
				headers: { Accept: "application/json" },
			});

			// If your controller redirects on success, fetch follows it.
			// When it does, res.redirected is true and res.url is the final page.
			// ...after the fetch
			const data = await res.json().catch(() => ({}));

			if (!res.ok) {
				showError(data.error || "Invalid email or password.");
				return;
			}

			if (data.redirect) {
				window.location.href = data.redirect; // ✅ /products
				return;
			}

			showSuccess(data.message || "Logged in.");
		} catch (err) {
			showError("An unexpected error occurred. Please try again later.");
		} finally {
			setLoading(false);
		}
	});
});
