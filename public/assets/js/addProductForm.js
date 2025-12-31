/** @format */

document.addEventListener("DOMContentLoaded", () => {
	const imageInput = document.getElementById("image");
	const previewImg = document.getElementById("previewImg");
	const previewText = document.getElementById("previewText");
	const previewPh = document.getElementById("previewPh");

	const errBox = document.querySelector("#formErrors");
	const okBox = document.querySelector("#formSuccess");
	const form = document.querySelector("#addProductForm");

	// Variants UI
	const variantsWrap = document.getElementById("variantsWrap");
	const variantTpl = document.getElementById("variantTpl");
	const addVariantBtn = document.getElementById("addVariantBtn");

	function addVariantRow() {
		if (!variantsWrap || !variantTpl) return;

		const node = variantTpl.content.cloneNode(true);
		const card = node.querySelector(".variantCard");
		card
			.querySelector(".removeVariantBtn")
			?.addEventListener("click", () => card.remove());
		variantsWrap.appendChild(node);
	}

	addVariantBtn?.addEventListener("click", addVariantRow);
	if (variantsWrap && variantTpl) addVariantRow();

	// Image preview
	if (imageInput) {
		imageInput.addEventListener("change", () => {
			const file = imageInput.files?.[0];

			if (!file) {
				previewImg.hidden = true;
				if (previewPh) previewPh.hidden = false;
				previewText.textContent = "Choose an image to preview";
				return;
			}

			const url = URL.createObjectURL(file);
			previewImg.src = url;

			previewImg.onload = () => {
				previewImg.hidden = false;
				if (previewPh) previewPh.hidden = true;
				previewText.textContent = "Image ready ✓";
				URL.revokeObjectURL(url);
			};

			previewImg.onerror = () => {
				previewImg.hidden = true;
				if (previewPh) previewPh.hidden = false;
				previewText.textContent = "Could not preview image ✗";
				URL.revokeObjectURL(url);
			};
		});
	}

	// Form submission (simplified for demo)
	// Form submission (simplified for demo)
	form?.addEventListener("submit", async (e) => {
		e.preventDefault();

		if (errBox) errBox.hidden = true;
		if (okBox) okBox.hidden = true;

		let res;
		try {
			res = await csrfFetch("/addProduct", {
				// Don't use csrfFetch with FormData containing files
				method: "POST",
				body: new FormData(form), // This will include the file
				headers: { Accept: "application/json" },
				credentials: "same-origin",
			});

			// CHECK IF RESPONSE IS OK - THIS IS THE KEY PART
			if (!res.ok) {
				const errorData = await res.json();
				if (errBox) {
					// Display errors from your PHP response
					if (errorData.errors && Array.isArray(errorData.errors)) {
						errBox.textContent = errorData.errors.join(", ");
					} else {
						errBox.textContent = "An error occurred. Please try again.";
					}
					errBox.hidden = false;
				}
				return;
			}

			// Success case
			const data = await res.json();
			if (okBox && data.message) {
				okBox.textContent = data.message.join(", ");
				okBox.hidden = false;
			}
		} catch {
			// Only network errors end up here
			if (errBox) {
				errBox.textContent = "Network error. Please try again.";
				errBox.hidden = false;
			}
			return;
		}

		// Reset after success
		setTimeout(() => {
			form.reset();
			if (variantsWrap) variantsWrap.innerHTML = "";
			addVariantRow();
			if (previewImg) previewImg.hidden = true;
			if (previewPh) previewPh.hidden = false;
			if (previewText)
				previewText.textContent = "Paste an image URL to preview";
			if (okBox) okBox.hidden = true;
		}, 2000);
	});
});
