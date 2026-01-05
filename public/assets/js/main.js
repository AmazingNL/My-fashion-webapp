/**
 * Navbar JavaScript - Mobile Menu Toggle & User Dropdown
 * Afro Elegance Custom Clothing Application
 */

(function () {
	"use strict";

	// Wait for DOM to load
	document.addEventListener("DOMContentLoaded", function () {
		// Mobile Menu Toggle
		const navToggle = document.getElementById("navToggle");
		const navMenu = document.getElementById("navMenu");

		if (navToggle && navMenu) {
			navToggle.addEventListener("click", function () {
				// Toggle active class on button
				navToggle.classList.toggle("active");

				// Toggle active class on menu
				navMenu.classList.toggle("active");

				// Prevent body scroll when menu is open
				if (navMenu.classList.contains("active")) {
					document.body.style.overflow = "hidden";
				} else {
					document.body.style.overflow = "";
				}
			});

			// Close menu when clicking on a link
			const navLinks = navMenu.querySelectorAll(".navbar__link");
			navLinks.forEach(function (link) {
				link.addEventListener("click", function () {
					navToggle.classList.remove("active");
					navMenu.classList.remove("active");
					document.body.style.overflow = "";
				});
			});

			// Close menu when clicking outside
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

		// User Dropdown Enhancement (optional - CSS handles hover)
		// This adds click functionality for touch devices
		const userMenuBtn = document.getElementById("userMenuBtn");
		const userDropdown = document.getElementById("userDropdown");

		if (userMenuBtn && userDropdown) {
			userMenuBtn.addEventListener("click", function (e) {
				e.preventDefault();
				e.stopPropagation();

				// Toggle visibility on touch devices
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

			// Close dropdown when clicking outside
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

		// Navbar Scroll Effect (optional - adds shadow on scroll)
		const navbar = document.querySelector(".navbar");
		if (navbar) {
			let lastScroll = 0;

			window.addEventListener("scroll", function () {
				const currentScroll = window.pageYOffset;

				if (currentScroll > 100) {
					navbar.style.boxShadow = "0 4px 24px rgba(92, 61, 46, 0.15)";
				} else {
					navbar.style.boxShadow = "0 4px 20px rgba(92, 61, 46, 0.08)";
				}

				lastScroll = currentScroll;
			});
		}
	});
})();