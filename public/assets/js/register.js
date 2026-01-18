/** @format */

document.addEventListener("DOMContentLoaded", () => {
	const form = document.querySelector("#registerForm");
	const errBox = document.querySelector("#formErrors");
	const okBox = document.querySelector("#formSuccess");
	const pass = document.querySelector("#password");

	if (!form) return;

	// password toggle
	document.addEventListener("click", (e) => {
		const btn = e.target.closest("[data-toggle='password']");
		if (!btn || !pass) return;

		const hidden = pass.type === "password";
		pass.type = hidden ? "text" : "password";
		btn.textContent = hidden ? "Hide" : "Show";
	});

	form.addEventListener("submit", async (e) => {
		e.preventDefault();

		errBox.hidden = true;
		okBox.hidden = true;

		// client-side required check
		const requiredFields = form.querySelectorAll("[required]");
		const missing = [];

		requiredFields.forEach((input) => {
			if (!input.value.trim()) {
				missing.push(input.previousElementSibling?.textContent || input.name);
				input.classList.add("field--error");
			} else {
				input.classList.remove("field--error");
			}
		});

		if (missing.length > 0) {
			errBox.innerHTML = `<ul>${missing
				.map((m) => `<li>${escapeHtml(m)} is required.</li>`)
				.join("")}</ul>`;
			errBox.hidden = false;
			return;
		}

		// only reaches here if everything is filled

		let res;
		try {
			res = await csrfFetch(form.action, {
				method: "POST",
				body: new FormData(form),
				headers: { Accept: "application/json" },
			});
		} catch (err) {
			errBox.textContent = "Network error. Please try again.";
			errBox.hidden = false;
			return;
		}

		// Read raw text first (prevents json() crashes)
		const raw = await res.text();

		// Try parse JSON
		let data = {};
		try {
			data = raw ? JSON.parse(raw) : {};
		} catch (err) {
			errBox.innerHTML =
				"Server did not return JSON. Response was:<br><pre>" +
				escapeHtml(raw.slice(0, 500)) +
				"</pre>";
			errBox.hidden = false;
			return;
		}

		// Correct logic: if NOT ok => show errors
		if (!res.ok) {
			const errors = data.errors ?? "Something went wrong.";

			let list = [];

			if (Array.isArray(errors)) {
				list = errors;
			} else if (errors && typeof errors === "object") {
				for (const key in errors) {
					const val = errors[key];
					if (Array.isArray(val)) list.push(...val);
					else list.push(String(val));
				}
			} else {
				list = [String(errors)];
			}

			errBox.innerHTML = `<ul>${list
				.map((m) => `<li>${escapeHtml(m)}</li>`)
				.join("")}</ul>`;
			errBox.hidden = false;
			return;
		}

		// Success
		okBox.textContent = data.message || "Registration successful!";
		okBox.hidden = false;
		form.reset();
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
