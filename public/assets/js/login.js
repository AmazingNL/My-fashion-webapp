/** @format */

document.addEventListener("DOMContentLoaded", () => {
  const form = document.querySelector("#loginForm");
  const errBox = document.querySelector("#formErrors");
  const okBox = document.querySelector("#formSuccess");
  const pass = document.querySelector("#password");
  const email = document.querySelector("#email");
  const loginBtn = document.querySelector("#loginBtn");

  if (!form) return;

  if (errBox) errBox.hidden = true;
  if (okBox) okBox.hidden = true;

  // Helpers to update alert text without destroying its inner HTML 
  const setAlertText = (box, msg, fallback) => {
    if (!box) return;
    const textEl = box.querySelector(".af-alert__text") || box.querySelector("p") || box;
    // If your alert has a <p class="af-alert__text">...</p>, this will target it.
    // Otherwise it falls back to <p> or the box itself.
    if (textEl === box) {
      // If there's no inner text element, at least don't show empty
      box.textContent = msg || fallback;
    } else {
      textEl.textContent = msg || fallback;
    }
  };

  const showError = (msg) => {
    if (!errBox) return;
    setAlertText(errBox, msg, "Something went wrong. Please try again.");
    errBox.hidden = false;
    if (okBox) okBox.hidden = true;
  };

  const showSuccess = (msg) => {
    if (!okBox) return;
    setAlertText(okBox, msg, "Logged in successfully.");
    okBox.hidden = false;
    if (errBox) errBox.hidden = true;
  };

  const setLoading = (on) => {
    if (!loginBtn) return;
    loginBtn.classList.toggle("is-loading", !!on);
    loginBtn.disabled = !!on;
  };

  // Password toggle
  document.addEventListener("click", (e) => {
    const btn = e.target.closest("[data-toggle='password']");
    if (!btn || !pass) return;

    const hidden = pass.type === "password";
    pass.type = hidden ? "text" : "password";
    btn.setAttribute("aria-label", hidden ? "Hide password" : "Show password");
    btn.title = hidden ? "Hide password" : "Show password";
    btn.textContent = hidden ? "🙈" : "👁️";
  });

  // Ajax login
  form.addEventListener("submit", async (e) => {
    e.preventDefault();

    const emailVal = (email?.value || "").trim();
    const passVal = (pass?.value || "").trim();

    // Hide alerts before validating
    if (errBox) errBox.hidden = true;
    if (okBox) okBox.hidden = true;

    if (!emailVal || !passVal) {
      showError("Email and password are required.");
      return;
    }

    setLoading(true);

    try {
      const formData = new FormData(form);

      const res = await csrfFetch(form.action, {
        method: "POST",
        body: formData,
        headers: { Accept: "application/json" },
      });

      const data = await res.json().catch(() => ({}));

      if (!res.ok) {
        showError(data.error || "Invalid email or password.");
        return;
      }

      if (data.redirect) {
        window.location.href = data.redirect; // e.g. /products
        return;
      }

      showSuccess(data.message || "Logged in.");
    } catch (err) {
      showError("An unexpected error occurred. Please try again later.");
    } finally {
      setLoading(false);
    }
  });
});
