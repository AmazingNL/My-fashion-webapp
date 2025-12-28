/** @format */

document.addEventListener("DOMContentLoaded", async () => {
	const imageInput = document.getElementById("image");
	const previewImg = document.getElementById("previewImg");
	const previewText = document.getElementById("previewText");
	const errBox = document.querySelector("#formErrors");
	const okBox = document.querySelector("#formSuccess");
	const form = document.querySelector("#addProductForm");

	if (imageInput) {
		imageInput.addEventListener("input", () => {
			const url = imageInput.value.trim();

			if (!url) {
				previewImg.hidden = true;
				previewText.textContent = "Paste an image URL to preview";
				return;
			}

			previewImg.src = url;

			previewImg.onload = () => {
				previewImg.hidden = false;
				previewText.textContent = "Image loaded successfully";
			};

			previewImg.onerror = () => {
				previewImg.hidden = true;
				previewText.textContent = "Invalid image URL";
			};
		});
	}

	form.addEventListener("submit", async (e) => {
		e.preventDefault(); // IMPORTANT

		errBox.hidden = true;
		okBox.hidden = true;

		let res;
		try {
			res = await csrfFetch("/addProduct", {
				method: "POST",
				body: new FormData(form),
				headers: { Accept: "application/json" },
			});
		} catch (err) {
			errBox.textContent = "Network error. Please try again.";
			errBox.hidden = false;
			return;
		}

		const raw = await res.text();

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

		if (!res.ok) {
			const errors = data.errors ?? ["Something went wrong."];
			const list = Array.isArray(errors) ? errors : [String(errors)];
			errBox.innerHTML = `<ul>${list
				.map((m) => `<li>${escapeHtml(m)}</li>`)
				.join("")}</ul>`;
			errBox.hidden = false;
			return;
		}

		okBox.textContent = data.message || "Product added successfully!";
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
