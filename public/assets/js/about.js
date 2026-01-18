/** @format */

(function () {
	"use strict";

	document.addEventListener("DOMContentLoaded", function () {
		const items = document.querySelectorAll(".reveal");
		if (!items.length) return;

		const io = new IntersectionObserver(
			(entries) => {
				entries.forEach((e) => {
					if (e.isIntersecting) {
						e.target.classList.add("is-visible");
						io.unobserve(e.target);
					}
				});
			},
			{ threshold: 0.12 }
		);

		items.forEach((el) => io.observe(el));
	});
})();
