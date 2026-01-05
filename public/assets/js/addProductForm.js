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

	// Form submission
	form?.addEventListener("submit", async (e) => {
		e.preventDefault();

		console.log("SUBMIT START");

		if (errBox) {
			errBox.hidden = true;
			errBox.textContent = "";
		}
		if (okBox) {
			okBox.hidden = true;
			okBox.textContent = "";
		}

		let success = false; 

		try {
			const res = await fetch("/addProduct", {
				method: "POST",
				body: new FormData(form),
				headers: { Accept: "application/json" },
				credentials: "same-origin",
			});

			const raw = await res.text();
			console.log("STATUS:", res.status);
			console.log("RAW:", raw);

			let data = {};
			data = raw ? JSON.parse(raw) : {};

			if (!res.ok) {
				const errs = Array.isArray(data.errors)
					? data.errors
					: ["An error occurred."];
				if (errBox) {
					errBox.textContent = errs.join(", ");
					errBox.hidden = false;
					errBox.style.display = "block";
				}
				return;
			}

			const msg = Array.isArray(data.message)
				? data.message.join(", ")
				: typeof data.message === "string"
				? data.message
				: "Saved successfully";

			console.log("SHOW SUCCESS:", msg);

			if (okBox) {
				okBox.textContent = msg;
				okBox.hidden = false;
				okBox.style.display = "block";
				window.scrollTo({ top: 0, behavior: "smooth" });
			}

			success = true; // mark success
		} catch (err) {
			console.error("SUBMIT CRASH:", err);
			if (errBox) {
				errBox.textContent = err?.message || "Unexpected error.";
				errBox.hidden = false;
				errBox.style.display = "block";
			}
			return;
		}

		if (!success) return;

		// Reset after SUCCESS only
		setTimeout(() => {
			form.reset();
			variantsWrap && (variantsWrap.innerHTML = "");
			addVariantRow();
			previewImg && (previewImg.hidden = true);
			previewPh && (previewPh.hidden = false);
			previewText && (previewText.textContent = "Choose an image to preview");
			// keep success visible a bit or hide it if you want:
			// okBox && (okBox.hidden = true);
			errBox && (errBox.hidden = true);
		}, 3000);
	});
});