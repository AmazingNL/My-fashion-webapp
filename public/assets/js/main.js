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
		// Counters (Cart + Favourites)
		// ============================================
		const cartCountEl = document.getElementById("cartCount");
		const favCountEl = document.getElementById("favCount");

		function pulse(el) {
			if (!el) return;
			el.classList.remove("pulse");
			void el.offsetWidth;
			el.classList.add("pulse");
			setTimeout(() => el.classList.remove("pulse"), 260);
		}

		async function updateCartCount() {
			if (!cartCountEl) return;

			try {
				const res = await csrfFetch("/getBasketCount", {
					method: "GET",
					credentials: "same-origin",
					headers: { Accept: "application/json" },
				});

				if (!res.ok) return;

				const data = await res.json().catch(() => ({}));
				const count = Number(data.count ?? 0);

				cartCountEl.textContent = String(count);
				cartCountEl.hidden = count <= 0;

				if (count > 0) pulse(cartCountEl);
			} catch (err) {
				console.error("Failed to update cart count:", err);
			}
		}

		async function updateFavCount() {
			if (!favCountEl) return;

			try {
				const res = await fetch("/api/favourites/products", {
					method: "GET",
					credentials: "same-origin",
					headers: { Accept: "application/json" },
				});

				// If not logged in, controller may return 401/403 -> hide badge
				if (!res.ok) {
					favCountEl.hidden = true;
					return;
				}

				const data = await res.json().catch(() => ({}));
				const list = Array.isArray(data.products) ? data.products : [];
				const count = list.length;

				favCountEl.textContent = String(count);
				favCountEl.hidden = count <= 0;

				if (count > 0) pulse(favCountEl);
			} catch (err) {
				console.error("Failed to update favourite count:", err);
			}
		}

		// initial load
		updateCartCount();
		updateFavCount();

		// update when pages dispatch events
		window.addEventListener("cartUpdated", updateCartCount);
		window.addEventListener("favouritesUpdated", updateFavCount);

		// allow other scripts to call
		window.updateCartCount = updateCartCount;
		window.updateFavCount = updateFavCount;
	});
})();
