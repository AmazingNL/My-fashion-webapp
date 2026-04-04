/** @format */

(function () {
	"use strict";

	document.addEventListener("DOMContentLoaded", function () {
		//  Menu Toggle
		const navToggle = document.getElementById("navToggle");
		const navMenu = document.getElementById("navMenu");
		const logoutForm = document.getElementById("logoutForm");
		const logoutBtn = document.getElementById("logoutBtn");

		if (navToggle && navMenu) {
			navToggle.addEventListener("click", function () {
				navToggle.classList.toggle("active");
				navMenu.classList.toggle("active");
				document.body.style.overflow = navMenu.classList.contains("active")
					? "hidden"
					: "";
			});

			const navLinks = navMenu.querySelectorAll(".navbar__link");
			navLinks.forEach(function (link) {
				link.addEventListener("click", function () {
					navToggle.classList.remove("active");
					navMenu.classList.remove("active");
					document.body.style.overflow = "";
				});
			});

			document.addEventListener("click", function (event) {
				const isClickInsideMenu = navMenu.contains(event.target);
				const isClickOnToggle = navToggle.contains(event.target);

				if (
					!isClickInsideMenu &&
					!isClickOnToggle &&
					navMenu.classList.contains("active")
				) {
					navToggle.classList.remove("active");
					navMenu.classList.remove("active");
					document.body.style.overflow = "";
				}
			});
		}

		// User Dropdown 
		const userMenuBtn = document.getElementById("userMenuBtn");
		const userDropdown = document.getElementById("userDropdown");

		if (userMenuBtn && userDropdown) {
			userMenuBtn.addEventListener("click", function (e) {
				e.preventDefault();
				e.stopPropagation();

				if (userDropdown.style.opacity === "1") {
					userDropdown.style.opacity = "0";
					userDropdown.style.visibility = "hidden";
					userDropdown.style.transform = "translateY(-10px)";
				} else {
					userDropdown.style.opacity = "1";
					userDropdown.style.visibility = "visible";
					userDropdown.style.transform = "translateY(0)";
				}
			});

			document.addEventListener("click", function (event) {
				const isClickInside =
					userMenuBtn.contains(event.target) ||
					userDropdown.contains(event.target);

				if (!isClickInside && userDropdown.style.opacity === "1") {
					userDropdown.style.opacity = "0";
					userDropdown.style.visibility = "hidden";
					userDropdown.style.transform = "translateY(-10px)";
				}
			});
		}

		// Navbar shadow on scroll
		const navbar = document.querySelector(".navbar");
		if (navbar) {
			window.addEventListener("scroll", function () {
				const currentScroll = window.pageYOffset;
				navbar.style.boxShadow =
					currentScroll > 100
						? "0 4px 24px rgba(92, 61, 46, 0.15)"
						: "0 4px 20px rgba(92, 61, 46, 0.08)";
			});
		}

		// Logout warning
		if (logoutForm && logoutBtn) {
			logoutForm.addEventListener("submit", function (e) {
				const msg =
					"Logging out will clear your cart and favourites (they are not saved).\n\nDo you want to continue?";
				if (!confirm(msg)) e.preventDefault();
			});
		}

		// ============================================
		// Favourites counter
		// ============================================
		const favCountEl = document.getElementById("favCount");

		function pulse(el) {
			if (!el) return;
			el.classList.remove("pulse");
			void el.offsetWidth;
			el.classList.add("pulse");
			setTimeout(() => el.classList.remove("pulse"), 260);
		}

		function updateFavCount() {
			if (!favCountEl) return;

			const count = Number(
				favCountEl.dataset.count || favCountEl.textContent || 0
			);

			favCountEl.textContent = String(count);
			favCountEl.hidden = count <= 0;

			if (count > 0) pulse(favCountEl);
		}

		// initial load
		updateFavCount();

		// update when pages dispatch events
		window.addEventListener("favouritesUpdated", updateFavCount);

		// allow other scripts to call
		window.updateFavCount = updateFavCount;

		// ============================================
		// Quantity controls (product details)
		// ============================================
		function clampQuantity(input) {
			const min = Number(input.min || 1);
			const max = Number(input.max || 999);
			const value = Number(input.value || min);
			const clamped = Math.max(min, Math.min(max, value));
			input.value = String(clamped);
		}

		document
			.querySelectorAll(".quantity-input-wrapper")
			.forEach(function (wrapper) {
				if (wrapper.dataset.qtyBound === "1") return;
				const input = wrapper.querySelector(".quantity-input");
				const decreaseBtn = wrapper.querySelector(
					'.qty-btn[data-action="decrease"]'
				);
				const increaseBtn = wrapper.querySelector(
					'.qty-btn[data-action="increase"]'
				);

				if (!input) return;

				decreaseBtn?.addEventListener("click", function (event) {
					event.preventDefault();
					const current = Number(input.value || input.min || 1);
					input.value = String(current - 1);
					clampQuantity(input);
				});

				increaseBtn?.addEventListener("click", function (event) {
					event.preventDefault();
					const current = Number(input.value || input.min || 1);
					input.value = String(current + 1);
					clampQuantity(input);
				});

				wrapper.dataset.qtyBound = "1";
			});

		document.addEventListener("input", function (event) {
			const input = event.target.closest(".quantity-input");
			if (!input) return;
			clampQuantity(input);
		});
	});
})();
