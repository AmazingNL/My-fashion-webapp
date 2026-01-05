/** @format */

document.addEventListener("DOMContentLoaded", async () => {
	// ==================== CONSTANTS & ELEMENTS ====================

	const productId = getProductIdFromUrl();

	const els = {
		// States
		loadingState: document.getElementById("loadingState"),
		errorState: document.getElementById("errorState"),
		mainContent: document.getElementById("mainContent"),
		toast: document.getElementById("toast"),
		cartCount: document.getElementById("cartCount"),

		// Product
		productImage: document.getElementById("productImage"),
		productName: document.getElementById("productName"),
		productCategory: document.getElementById("productCategory"),
		productPrice: document.getElementById("productPrice"),
		productDescription: document.getElementById("productDescription"),
		productDescriptionContainer: document.getElementById(
			"productDescriptionContainer"
		),
		breadcrumbProduct: document.getElementById("breadcrumbProduct"),
		productIdInput: document.getElementById("productIdInput"),

		// Form
		variantSelect: document.getElementById("variantSelect"),
		quantityInput: document.getElementById("quantity"),
		stockInfo: document.getElementById("stockInfo"),
		stockMessage: document.getElementById("stockMessage"),
		stockCount: document.getElementById("stockCount"), // ✅ add in view
		addToBasketBtn: document.getElementById("addToBasket"),

		// Quantity buttons
		qtyDecreaseBtn: document.querySelector('.qty-btn[data-action="decrease"]'),
		qtyIncreaseBtn: document.querySelector('.qty-btn[data-action="increase"]'),

		// Similar products
		similarProductsSection: document.getElementById("similarProductsSection"),
		similarProductsGrid: document.getElementById("similarProductsGrid"),
	};

	const URLS = {
		PRODUCT_DETAILS: `/api/products/${productId}`,
		ADD_TO_BASKET: "/addToBasket",
		CART_COUNT: "/getBasketCount",
	};

	// ==================== UTILITY FUNCTIONS ====================

	function getProductIdFromUrl() {
		const parts = window.location.pathname.split("/");
		return parts[parts.length - 1];
	}

	function escapeHtml(text) {
		const div = document.createElement("div");
		div.textContent = text;
		return div.innerHTML;
	}

	function showToast(msg, isError = false) {
		if (!els.toast) return;
		els.toast.textContent = msg;
		els.toast.className = isError ? "toast toast--bad" : "toast";
		els.toast.hidden = false;
		clearTimeout(showToast._t);
		showToast._t = setTimeout(() => (els.toast.hidden = true), 4000);
	}

	function showError() {
		if (els.loadingState) els.loadingState.style.display = "none";
		if (els.errorState) els.errorState.style.display = "block";
		if (els.mainContent) els.mainContent.style.display = "none";
	}

	function showContent() {
		if (els.loadingState) els.loadingState.style.display = "none";
		if (els.errorState) els.errorState.style.display = "none";
		if (els.mainContent) els.mainContent.style.display = "block";
	}

	function pulse(el) {
		if (!el) return;
		el.classList.remove("pulse");
		void el.offsetWidth;
		el.classList.add("pulse");
	}

	async function safeJson(res) {
		return res.json().catch(() => ({}));
	}

	// ==================== CART COUNT ====================

	async function updateCartCount() {
		try {
			const res = await csrfFetch(URLS.CART_COUNT, {
				headers: { Accept: "application/json" },
				credentials: "same-origin",
			});

			if (!res.ok) return;

			const data = await res.json();
			if (els.cartCount && data.count !== undefined) {
				els.cartCount.textContent = data.count;
				pulse(els.cartCount);
			}
		} catch (err) {
			console.error("Failed to update cart count:", err);
		}
	}

	// ==================== QUANTITY CONTROLS ====================

	function updateQuantityButtons() {
		if (!els.quantityInput || !els.qtyDecreaseBtn || !els.qtyIncreaseBtn)
			return;

		const currentQty = parseInt(els.quantityInput.value) || 1;
		const maxQty = parseInt(els.quantityInput.max) || 10;
		const minQty = parseInt(els.quantityInput.min) || 1;

		els.qtyDecreaseBtn.disabled = currentQty <= minQty;
		els.qtyIncreaseBtn.disabled = currentQty >= maxQty;
	}

	function clampQuantity() {
		if (!els.quantityInput) return;

		let qty = parseInt(els.quantityInput.value) || 1;
		const minQty = parseInt(els.quantityInput.min) || 1;
		const maxQty = parseInt(els.quantityInput.max) || 10;

		qty = Math.max(minQty, Math.min(maxQty, qty));
		els.quantityInput.value = qty;
	}

	function setupQuantityControls() {
		if (!els.quantityInput) return;

		if (els.qtyDecreaseBtn) {
			els.qtyDecreaseBtn.addEventListener("click", () => {
				const currentQty = parseInt(els.quantityInput.value) || 1;
				const minQty = parseInt(els.quantityInput.min) || 1;
				if (currentQty > minQty) {
					els.quantityInput.value = currentQty - 1;
					updateQuantityButtons();
				}
			});
		}

		if (els.qtyIncreaseBtn) {
			els.qtyIncreaseBtn.addEventListener("click", () => {
				const currentQty = parseInt(els.quantityInput.value) || 1;
				const maxQty = parseInt(els.quantityInput.max) || 10;
				if (currentQty < maxQty) {
					els.quantityInput.value = currentQty + 1;
					updateQuantityButtons();
				}
			});
		}

		els.quantityInput.addEventListener("input", updateQuantityButtons);
		els.quantityInput.addEventListener("change", () => {
			clampQuantity();
			updateQuantityButtons();
		});

		updateQuantityButtons();
	}

	// ==================== FETCH PRODUCT ====================

	async function fetchProductDetails() {
		const res = await csrfFetch(URLS.PRODUCT_DETAILS, {
			headers: { Accept: "application/json" },
			credentials: "same-origin",
		});

		if (!res.ok) {
			if (res.status === 404) throw new Error("Product not found");
			throw new Error("Failed to load product");
		}

		const data = await res.json();
		if (data.error) throw new Error(data.error);
		return data;
	}

	// ==================== RENDER PRODUCT ====================

	function renderProductBasics(data) {
		document.title = `${data.productName || "Product"} - Afro Catalogue`;

		if (els.breadcrumbProduct)
			els.breadcrumbProduct.textContent = data.productName || "Product";

		if (els.productImage) {
			els.productImage.src = data.image || "/assets/images/placeholder.jpg";
			els.productImage.alt = data.productName || "Product";
		}

		if (els.productName) els.productName.textContent = data.productName || "";

		if (els.productCategory)
			els.productCategory.textContent = data.category || "Uncategorized";

		if (els.productPrice) {
			const price = parseFloat(data.price) || 0;
			els.productPrice.textContent = price.toFixed(2);
		}

		if (
			data.description &&
			els.productDescription &&
			els.productDescriptionContainer
		) {
			els.productDescription.innerHTML = escapeHtml(data.description).replace(
				/\n/g,
				"<br>"
			);
			els.productDescriptionContainer.style.display = "block";
		}

		if (els.productIdInput)
			els.productIdInput.value = data.productId || productId;
		if (els.favButton)
			els.favButton.dataset.productId = data.productId || productId;
	}

	// ==================== VARIANTS + STOCK UI ====================

	function populateVariants(variants) {
		if (!els.variantSelect) return;

		els.variantSelect.innerHTML =
			'<option value="">Choose your perfect fit...</option>';

		if (!variants || variants.length === 0) {
			const opt = document.createElement("option");
			opt.disabled = true;
			opt.textContent = "No variants available";
			els.variantSelect.appendChild(opt);
			return;
		}

		variants.forEach((variant) => {
			const option = document.createElement("option");
			const stock = Number(variant.stockQuantity || 0);

			option.value = variant.variantId;
			option.dataset.stock = stock;

			const sizeColor = `${variant.size || ""} - ${variant.colour || ""}`;
			option.textContent =
				stock <= 0 ? `${sizeColor} (Out of Stock)` : sizeColor;

			if (stock <= 0) option.disabled = true;

			els.variantSelect.appendChild(option);
		});
	}

	function setSelectedOptionStock(variantId, newStock) {
		if (!els.variantSelect) return;

		const options = Array.from(els.variantSelect.options);
		const opt = options.find((o) => String(o.value) === String(variantId));
		if (opt) opt.dataset.stock = String(newStock);
	}

	function applyStockUI(variantId, stock) {
		if (!els.stockInfo || !els.stockMessage || !els.variantSelect) return;

		els.stockInfo.style.display = "flex";

		// ✅ "tag" for current selected variant
		if (els.stockCount) {
			els.stockCount.dataset.stockFor = String(variantId);
			els.stockCount.textContent = String(stock);
		}

		const inStock = stock > 0;
		els.stockInfo.classList.toggle("in-stock", inStock);
		els.stockInfo.classList.toggle("out-of-stock", !inStock);

		if (inStock) {
			els.stockMessage.textContent =
				stock === 1 ? "piece in stock" : "pieces in stock";

			if (els.quantityInput) {
				els.quantityInput.max = Math.min(stock, 10);
				if (
					parseInt(els.quantityInput.value || "1") >
					parseInt(els.quantityInput.max)
				) {
					els.quantityInput.value = els.quantityInput.max;
				}
				updateQuantityButtons();
			}

			if (els.addToBasketBtn) {
				els.addToBasketBtn.disabled = false;
				const txt = els.addToBasketBtn.querySelector(".btn-text");
				if (txt) txt.textContent = "Add to Cart";
			}
		} else {
			els.stockMessage.textContent = "Currently unavailable";

			if (els.quantityInput) {
				els.quantityInput.max = 0;
				els.quantityInput.value = 0;
				updateQuantityButtons();
			}

			if (els.addToBasketBtn) {
				els.addToBasketBtn.disabled = true;
				const txt = els.addToBasketBtn.querySelector(".btn-text");
				if (txt) txt.textContent = "Out of stock";
			}
		}
	}

	function getSelectedStock() {
		if (!els.variantSelect) return 0;
		const selectedOption =
			els.variantSelect.options[els.variantSelect.selectedIndex];
		return parseInt(selectedOption?.dataset?.stock || 0);
	}

	function setupStockIndicator() {
		if (!els.variantSelect || !els.stockInfo || !els.stockMessage) return;

		els.variantSelect.addEventListener("change", function () {
			if (!this.value) {
				els.stockInfo.style.display = "none";
				return;
			}

			const selectedOption = this.options[this.selectedIndex];
			const stock = parseInt(selectedOption.dataset.stock || 0);

			applyStockUI(this.value, stock);
		});
	}

	// ==================== ADD TO BASKET (with stock update) ====================

	function setAddButtonBusy(isBusy) {
		if (!els.addToBasketBtn) return () => {};

		const original = els.addToBasketBtn.innerHTML;
		els.addToBasketBtn.disabled = isBusy;

		if (isBusy) {
			els.addToBasketBtn.innerHTML = `
                <svg class="btn-icon" viewBox="0 0 24 24" fill="none" style="animation: spin 1s linear infinite;">
                    <circle cx="12" cy="12" r="10" stroke="currentColor" stroke-width="4" opacity="0.25"/>
                    <path d="M12 2a10 10 0 0 1 10 10" stroke="currentColor" stroke-width="4"/>
                </svg>
                <span class="btn-text">Adding...</span>
            `;
		}

		return () => {
			els.addToBasketBtn.disabled = false;
			els.addToBasketBtn.innerHTML = original;
		};
	}

	function readAddInputs() {
		const variantId = parseInt(els.variantSelect?.value || "0");
		const quantity = els.quantityInput
			? parseInt(els.quantityInput.value || "1")
			: 1;
		return { variantId, quantity };
	}

	function validateAddInputs(variantId, quantity) {
		if (!els.variantSelect || !els.variantSelect.value)
			return "Please select a size and color";
		if (variantId <= 0) return "Please select a valid variant";
		if (quantity <= 0) return "Quantity must be at least 1";

		const stock = getSelectedStock();
		if (stock <= 0) return "This variant is currently unavailable";
		if (quantity > stock) {
			return `Only ${stock} piece${stock !== 1 ? "s" : ""} available`;
		}

		return "";
	}

	async function addToBasketRequest(variantId, quantity) {
		return csrfFetch(URLS.ADD_TO_BASKET, {
			method: "POST",
			headers: {
				"Content-Type": "application/x-www-form-urlencoded",
				Accept: "application/json",
			},
			credentials: "same-origin",
			body: new URLSearchParams({
				productId: (els.productIdInput?.value || productId).toString(),
				variantId: variantId.toString(),
				quantity: quantity.toString(),
			}),
		});
	}

	function applyRemainingStockFromServer(data) {
		// Controller should return: { variantId, remainingStock }
		const vId = data?.variantId;
		const remaining = data?.remainingStock;

		if (!vId || remaining === undefined || remaining === null) return;

		const remainingInt = parseInt(remaining, 10) || 0;
		setSelectedOptionStock(vId, remainingInt);

		// If the user is still viewing the same variant, update UI immediately
		if (els.variantSelect && String(els.variantSelect.value) === String(vId)) {
			applyStockUI(vId, remainingInt);
		}
	}

	function resetQuantityAfterAdd() {
		if (!els.quantityInput) return;
		els.quantityInput.value = 1;
		updateQuantityButtons();
	}

	function setupAddToBasket() {
		if (!els.addToBasketBtn) return;

		els.addToBasketBtn.addEventListener("click", async (e) => {
			e.preventDefault();

			const { variantId, quantity } = readAddInputs();
			const errMsg = validateAddInputs(variantId, quantity);
			if (errMsg) return showToast(errMsg, true);

			console.log("ADD TO CART PAYLOAD", {
				productIdFromUrl: productId,
				productIdFromHiddenInput: els.productIdInput?.value,
				variantId,
				quantity,
			});

			const restoreBtn = setAddButtonBusy(true);

			try {
				const res = await addToBasketRequest(variantId, quantity);
				const data = await safeJson(res);

				if (!res.ok) throw new Error(data.message || "Failed to add to basket");
				if (!data.success)
					throw new Error(data.message || "Failed to add to basket");

				showToast(
					`✓ Added ${quantity} ${
						quantity === 1 ? "item" : "items"
					} to your cart`
				);

				// ✅ Update stock (virtual remaining stock)
				applyRemainingStockFromServer(data);

				// Update cart count + notify
				await updateCartCount();
				window.dispatchEvent(new Event("cartUpdated"));

				resetQuantityAfterAdd();
			} catch (err) {
				console.error("Add to basket error:", err);
				showToast(err.message || "Failed to add to basket", true);
			} finally {
				restoreBtn();
			}
		});
	}


	function attachSimilarProductListeners() {
		const similarFavButtons = document.querySelectorAll(".product-card-fav");

		similarFavButtons.forEach((btn) => {
			btn.addEventListener("click", async function (e) {
				e.preventDefault();
				e.stopPropagation();

				const pid = this.dataset.productId;
				if (!pid) {
					console.error("No product ID found on favourite button");
					return;
				}

				await toggleFavourite(this, pid);
			});
		});
	}

	// ==================== SIMILAR PRODUCTS ====================

	function populateSimilarProducts(products) {
		if (!els.similarProductsGrid || !products || products.length === 0) {
			if (els.similarProductsSection)
				els.similarProductsSection.style.display = "none";
			return;
		}

		els.similarProductsGrid.innerHTML = "";

		products.forEach((product, index) => {
			const card = createProductCard(product);
			card.style.animationDelay = `${index * 0.1}s`;
			els.similarProductsGrid.appendChild(card);
		});

		if (els.similarProductsSection)
			els.similarProductsSection.style.display = "block";

		attachSimilarProductListeners();
	}

	function createProductCard(product) {
		const article = document.createElement("article");
		article.className = "product-card";
		article.style.opacity = "0";
		article.style.animation = "fadeInUp 0.6s ease-out forwards";

		const imageDiv = document.createElement("div");
		imageDiv.className = "product-card-image";

		const img = document.createElement("img");
		img.src = product.image || "/assets/images/placeholder.jpg";
		img.alt = product.productName || product.name || "";
		imageDiv.appendChild(img);

		const favBtn = document.createElement("button");
		favBtn.className = "product-card-fav";
		favBtn.type = "button";
		favBtn.dataset.productId = product.productId;
		favBtn.setAttribute("aria-label", "Add to favourites");
		favBtn.innerHTML = `
            <svg class="heart-icon-small" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                <path stroke-linecap="round" stroke-linejoin="round" d="M20.84 4.61a5.5 5.5 0 0 0-7.78 0L12 5.67l-1.06-1.06a5.5 5.5 0 0 0-7.78 7.78l1.06 1.06L12 21.23l7.78-7.78 1.06-1.06a5.5 5.5 0 0 0 0-7.78z" />
            </svg>
        `;
		imageDiv.appendChild(favBtn);

		const overlay = document.createElement("div");
		overlay.className = "quick-view-overlay";
		const quickViewLink = document.createElement("a");
		quickViewLink.href = `/products/${product.productId}`;
		quickViewLink.className = "quick-view-btn";
		quickViewLink.textContent = "Quick View";
		overlay.appendChild(quickViewLink);
		imageDiv.appendChild(overlay);

		article.appendChild(imageDiv);

		const contentDiv = document.createElement("div");
		contentDiv.className = "product-card-content";

		const categoryDiv = document.createElement("div");
		categoryDiv.className = "product-card-category";
		categoryDiv.textContent = product.category || "";
		contentDiv.appendChild(categoryDiv);

		const title = document.createElement("h3");
		title.className = "product-card-title";
		const titleLink = document.createElement("a");
		titleLink.href = `/products/${product.productId}`;
		titleLink.textContent = product.productName || product.name || "";
		title.appendChild(titleLink);
		contentDiv.appendChild(title);

		const price = document.createElement("p");
		price.className = "product-card-price";
		const priceValue = parseFloat(product.price) || 0;
		price.textContent = `€${priceValue.toFixed(2)}`;
		contentDiv.appendChild(price);

		article.appendChild(contentDiv);

		return article;
	}

	// ==================== INITIALIZATION ====================

	function ensureAnimations() {
		if (document.getElementById("dynamic-animations")) return;

		const style = document.createElement("style");
		style.id = "dynamic-animations";
		style.textContent = `
            @keyframes fadeInUp {
                from { opacity: 0; transform: translateY(30px); }
                to { opacity: 1; transform: translateY(0); }
            }
            @keyframes spin { to { transform: rotate(360deg); } }
        `;
		document.head.appendChild(style);
	}

	async function init() {
		try {
			const productData = await fetchProductDetails();

			renderProductBasics(productData);
			populateVariants(productData.variants || []);
			populateSimilarProducts(productData.similarProducts || []);

			showContent();

			setupStockIndicator();
			setupQuantityControls();
			setupAddToBasket();

			await updateCartCount();

			window.addEventListener("cartUpdated", updateCartCount);
			window.updateCartCount = updateCartCount;
		} catch (err) {
			console.error("Failed to initialize product page:", err);
			showError();
		}
	}

	ensureAnimations();
	init();
});
