/** @format */

(function () {
	function getCsrfToken() {
		const meta = document.querySelector('meta[name="csrf-token"]');
		return meta ? meta.getAttribute("content") : null;
	}

	window.csrfFetch = function (url, options = {}) {
		const token = getCsrfToken();

        options.credentials = "same-origin";
		options.headers ??= {};

		// Only attach CSRF for state-changing requests
		const method = (options.method || "GET").toUpperCase();
		if (token && method !== "GET") {
			options.headers["X-CSRF-Token"] = token;
		}

		return fetch(url, options);
	};
})();
