/** @format */
document.addEventListener("DOMContentLoaded", () => {
	// image preview
	const imageInput = document.getElementById("image");
	const previewImg = document.getElementById("previewImg");
	const previewPh = document.getElementById("previewPh");

	imageInput?.addEventListener("change", () => {
		const file = imageInput.files?.[0];
		if (!file) return;

		const url = URL.createObjectURL(file);
		if (previewImg) {
			previewImg.src = url;
			previewImg.hidden = false;
		}
		if (previewPh) previewPh.hidden = true;
	});

	// variants add/remove
	const variantsWrap = document.getElementById("variantsWrap");
	const tpl = document.getElementById("variantTpl");
	const addBtn = document.getElementById("addVariantBtn");

	const bindRemove = (card) => {
		card.querySelector(".removeVariantBtn")?.addEventListener("click", () => {
			const existing = card.getAttribute("data-existing") === "1";

			if (existing) {
				// Don't remove existing row from DOM; mark for deletion.
				card.classList.add("is-deleted");
				card.querySelector(".variantDelete").value = "1";

				// disable inputs so they don't validate
				card.querySelectorAll("input").forEach((i) => {
					if (i.classList.contains("variantDelete")) return;
					i.disabled = true;
				});
			} else {
				// New row: just remove
				card.remove();
			}
		});
	};

	// bind existing cards
	document.querySelectorAll(".variantCard").forEach(bindRemove);

	addBtn?.addEventListener("click", () => {
		if (!tpl || !variantsWrap) return;
		const node = tpl.content.cloneNode(true);
		const card = node.querySelector(".variantCard");
		bindRemove(card);
		variantsWrap.appendChild(node);
	});
});
