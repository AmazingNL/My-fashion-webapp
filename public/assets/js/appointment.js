/** @format */

document.addEventListener("DOMContentLoaded", () => {
	const csrf = document.querySelector('meta[name="csrf-token"]')?.content || "";

	// shared elements (may not exist on all pages)
	const dateInput = document.getElementById("apptDate");
	const slotSelect = document.getElementById("slotId");
	const slotHelp = document.getElementById("slotHelp");

	const msgBox = document.getElementById("apptMsg");
	const msgText = document.getElementById("apptMsgText");

	// ---- helpers
	const setMsg = (type, text) => {
		if (!msgBox || !msgText) return;
		msgBox.classList.remove("success", "error");
		msgBox.classList.add(type);
		msgText.textContent = text;
		msgBox.hidden = false;
		window.scrollTo({ top: 0, behavior: "smooth" });
		setTimeout(() => (msgBox.hidden = true), 3500);
	};

	const qp = new URLSearchParams(window.location.search);
	const err = qp.get("error");
	const ok = qp.get("success");

	if (err) setMsg("error", decodeURIComponent(err));
	if (ok) {
		const map = {
			booked: "Appointment booked successfully 🎟️",
			updated: "Appointment updated successfully ✅",
			saved: "Details saved ✅",
			cancelled: "Appointment cancelled 🧹",
		};
		setMsg("success", map[ok] || "Done ✅");
	}

	async function fetchSlots(date) {
		if (!slotSelect) return;

		slotSelect.innerHTML = `<option value="">Loading slots...</option>`;
		slotSelect.disabled = true;
		if (slotHelp) slotHelp.textContent = "Fetching available times...";

		try {
			const res = await fetch(
				`/api/appointments/slots?date=${encodeURIComponent(date)}`,
				{
					headers: { "X-CSRF-TOKEN": csrf },
				}
			);

			const data = await res.json();
			if (!res.ok) throw new Error(data?.error || "Failed to load slots");

			const slots = data.slots || [];
			if (slots.length === 0) {
				slotSelect.innerHTML = `<option value="">No available slots for this date</option>`;
				if (slotHelp) slotHelp.textContent = "Try another date.";
				return;
			}

			slotSelect.innerHTML = `<option value="">Choose a time</option>`;
			for (const s of slots) {
				const label = `${s.startTime} - ${s.endTime}`;
				const opt = document.createElement("option");
				opt.value = String(s.slotId);
				opt.textContent = label;
				slotSelect.appendChild(opt);
			}

			if (slotHelp) slotHelp.textContent = "Select a time slot.";
		} catch (e) {
			slotSelect.innerHTML = `<option value="">Error loading slots</option>`;
			if (slotHelp) slotHelp.textContent = "Please refresh and try again.";
			setMsg("error", e.message || "Could not load slots");
		} finally {
			slotSelect.disabled = false;
		}
	}

	// Load slots when date changes
	dateInput?.addEventListener("change", () => {
		const d = dateInput.value;
		if (!d) return;
		fetchSlots(d);
	});

	// auto-load if date already filled
	if (dateInput?.value) {
		fetchSlots(dateInput.value);
	}

	// confirm cancel buttons
	document.querySelectorAll("[data-appt-cancel]").forEach((btn) => {
		btn.addEventListener("click", (e) => {
			const ok = confirm("Cancel this appointment?");
			if (!ok) e.preventDefault();
		});
	});
});
