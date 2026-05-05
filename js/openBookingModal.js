document.addEventListener("DOMContentLoaded", function () {

    window.getSelectedBookingUserId = function () {
        const selfRadio = document.getElementById("bookForSelf");
        const childRadio = document.getElementById("bookForChild");
        const existingRadio = document.getElementById("bookForExisting");

        if (!selfRadio || !childRadio || !existingRadio) {
            return window.LOGGED_IN_USER_ID;
        }

        let userId = window.LOGGED_IN_USER_ID;

        if (existingRadio.checked) {
            const depSelect = document.getElementById("existingDependentSelect");
            if (depSelect && depSelect.value) {
                userId = depSelect.value;
            }
        }

        if (childRadio.checked) {
            userId = 0;
        }

        return userId;
    };

    setTimeout(() => {
        document.querySelectorAll(".flash-msg").forEach((el) => {
            el.style.transition = "opacity 1s ease";
            el.style.opacity = "0";
            setTimeout(() => el.remove(), 1000);
        });
    }, 10000);

    const branchSelect = document.getElementById("appointmentBranch");
    const dateSelect = document.getElementById("appointmentDate");
    const timeSelect = document.getElementById("appointmentTime");
    const servicesContainer = document.getElementById("servicesContainer");
    const dentistSelect = document.getElementById("appointmentDentist");
    const estimatedEndDisplay = document.getElementById("estimatedEnd");
    const dateError = document.getElementById("dateError");

    const CLINIC_START = { h: 9, m: 0 };
    const CLINIC_END   = { h: 16, m: 30 };
    const SLOT_STEP_MIN = 30;

    window.openBookingModal = function () {
        const modal = document.getElementById("bookingModal");
        if (!modal) return;

        modal.style.display = "block";
        document.body.classList.add("modal-open");

        const dateSelect = document.getElementById("appointmentDate");
        if (dateSelect) {
            const now = new Date();
            const lastStart = new Date();
            lastStart.setHours(15, 0, 0, 0);

            let minDate = new Date();
            if (now >= lastStart) {
                minDate.setDate(minDate.getDate() + 1);
            }
            function toLocalDateStringPH(date) {
                const y = date.getFullYear();
                const m = String(date.getMonth() + 1).padStart(2, "0");
                const d = String(date.getDate()).padStart(2, "0");
                return `${y}-${m}-${d}`;
            }

            dateSelect.min = toLocalDateStringPH(minDate);

        }
    };

    window.closeBookingModal = function () {
        const modal = document.getElementById("bookingModal");
        if (modal) {
            modal.style.display = "none";
            document.body.classList.remove("modal-open");
        }
    };

    if (!branchSelect || !dateSelect || !timeSelect || !servicesContainer || !dentistSelect) {
        return;
    }

    dateSelect.disabled = true;
    dentistSelect.disabled = true;
    timeSelect.disabled = true;
    resetServices();
    resetServiceQuantities();

    function formatDisplay(time24) {
        const [hh, mm] = time24.split(':').map(Number);
        let hour = hh % 12 || 12;
        const ampm = hh >= 12 ? "PM" : "AM";
        return `${hour}:${String(mm).padStart(2, '0')} ${ampm}`;
    }

    function buildAllSlots() {
        const slots = [];
        const start = new Date(2000,0,1, CLINIC_START.h, CLINIC_START.m);
        const end = new Date(2000,0,1, CLINIC_END.h, CLINIC_END.m);
        const step = SLOT_STEP_MIN;
        for (let s = new Date(start); s < end; s.setMinutes(s.getMinutes() + step)) {
            const hh = String(s.getHours()).padStart(2, '0');
            const mm = String(s.getMinutes()).padStart(2, '0');
            slots.push(`${hh}:${mm}`);
        }
        return slots;
    }

    function resetServices() {
        servicesContainer.innerHTML = `<p class="loading-text">Select a branch and date to load available time slots</p>`;
        servicesContainer.dataset.loaded = "false";
    }

    function resetDentist() {
        dentistSelect.disabled = true;
        dentistSelect.innerHTML = '<option value="" disabled selected hidden></option>';
    }

    function resetTime() {
        timeSelect.disabled = true;
        timeSelect.innerHTML = '<option value="" disabled selected hidden></option>';
        estimatedEndDisplay.textContent = "";
    }

    function resetDate() {
        dateSelect.value = "";
    }

    window.loadAvailableTimes = async function () {
        const branchId = branchSelect.value;
        const date = dateSelect.value;

        if (!date) {
            resetTime();
            return;
        }
        const day = new Date(date).getDay();
        if (day === 0) {
            if (dateError) dateError.style.display = "block";
            resetTime();
            return;
        } else {
            if (dateError) dateError.style.display = "none";
        }

        if (!branchId || !date) {
            resetTime();
            return;
        }

        timeSelect.disabled = false;
        timeSelect.innerHTML = '<option value="" disabled selected hidden></option>';

        try {
            const form = new FormData();
            form.append('branch_id', branchId);
            form.append('appointment_date', date);
            form.append('user_id', window.getSelectedBookingUserId());

            const res = await fetch(`${BASE_URL}/processes/load_available_times.php`, {
                method: 'POST',
                body: form
            });

            const data = await res.json();

            if (data.error) {
                console.error('loadAvailableTimes error:', data.error);
                resetTime();
                return;
            }

            const blockedSet = new Set(data.blocked || []);
            const allSlots = buildAllSlots();

            const now = new Date();
            const selectedDateObj = new Date(dateSelect.value);

            const isToday =
                selectedDateObj.getFullYear() === now.getFullYear() &&
                selectedDateObj.getMonth() === now.getMonth() &&
                selectedDateObj.getDate() === now.getDate();

            allSlots.forEach(slot => {
                const display = formatDisplay(slot);
                let isBlocked = blockedSet.has(slot);

                if (isToday) {
                    const [hh, mm] = slot.split(":").map(Number);
                    const slotTime = new Date();
                    slotTime.setHours(hh, mm, 0, 0);

                    if (slotTime <= now) {
                        isBlocked = true;
                    }
                }

                const opt = document.createElement("option");
                opt.value = slot;
                opt.disabled = isBlocked;

                if (isBlocked) {
                    opt.className = "blocked-slot";
                    opt.textContent = display;
                } else {
                    opt.textContent = display;
                }

                timeSelect.appendChild(opt);
            });

            servicesContainer.innerHTML = `<p class="loading-text">Select a time to load available services</p>`;
            servicesContainer.dataset.loaded = "false";
            resetDentist();
            estimatedEndDisplay.textContent = "";

        } catch (err) {
            console.error('loadAvailableTimes fetch error:', err);
            resetTime();
            servicesContainer.innerHTML = `<p class="error-msg">Failed to load time slots.</p>`;
        }
    }

    async function loadServicesAfterTime() {
        const branchId = branchSelect.value;
        if (!branchId) return;

        servicesContainer.innerHTML = `<p class="loading-text">Loading services...</p>`;

        try {
            const params = new URLSearchParams();
            params.append('appointmentBranch', branchId);
            const res = await fetch(`${BASE_URL}/processes/load_services.php`, {
                method: 'POST',
                headers: { 'Content-Type': 'application/x-www-form-urlencoded' },
                body: params.toString()
            });
            const html = await res.text();
            servicesContainer.innerHTML = html;
            servicesContainer.dataset.loaded = "true";
        } catch (err) {
            console.error('loadServices error:', err);
            servicesContainer.innerHTML = `<p class="error-msg">Failed to load services.</p>`;
        }
    }

    function gatherCheckedServices() {
        return Array.from(document.querySelectorAll("#servicesContainer input[type='checkbox']:checked"))
            .map(cb => cb.value);
    }

    async function loadDentists() {
        const branchId = branchSelect.value;
        const date = dateSelect.value;
        const time = timeSelect.value;

        const checkedServices = gatherCheckedServices();
        if (!branchId || !date || !time || checkedServices.length === 0) {
            resetDentist();
            return;
        }

        const fd = new FormData();
        fd.append('appointmentBranch', branchId);
        fd.append('appointmentDate', date);
        fd.append('appointmentTime', time);
        checkedServices.forEach(s => fd.append('appointmentServices[]', s));

        dentistSelect.disabled = true;
        dentistSelect.innerHTML = `<option disabled>Loading dentists...</option>`;

        try {
            const res = await fetch(`${BASE_URL}/processes/load_dentists.php`, {
                method: 'POST',
                body: fd
            });
            const html = await res.text();
            dentistSelect.innerHTML = html;
            dentistSelect.disabled = false;
        } catch (err) {
            console.error('Dentist load error:', err);
            dentistSelect.innerHTML = `<option disabled>Error loading dentists</option>`;
        }
    }

    function updateEstimatedEndTime() {
        const selectedTime = timeSelect.value;
        if (!selectedTime) {
            estimatedEndDisplay.textContent = "";
            return;
        }

        let totalDuration = 0;

        document.querySelectorAll("#servicesContainer input[type='checkbox']:checked")
            .forEach(cb => {
                const duration = parseInt(cb.dataset.duration || 0);
                const serviceId = cb.value;

                const qtyInput = document.querySelector(`input[name="serviceQuantity[${serviceId}]"]`);
                const qty = qtyInput ? parseInt(qtyInput.value || "1") : 1;

                totalDuration += duration * qty;
            });

        if (totalDuration === 0) {
            estimatedEndDisplay.textContent = "";
            return;
        }

        const [h, m] = selectedTime.split(":").map(Number);
        const start = new Date(`2000-01-01T${selectedTime}:00`);
        start.setMinutes(start.getMinutes() + totalDuration);

        const clinicClose = new Date("2000-01-01T16:30:00");

        if (start > clinicClose) {
            document.querySelectorAll("input[name='appointmentServices[]']").forEach(cb => cb.checked = false);
            document.querySelectorAll("input[name^='serviceQuantity']").forEach(q => q.value = "");
            estimatedEndDisplay.textContent = "";
            servicesError.textContent = "Selected services exceed clinic hours. Please adjust your selection.";
            servicesError.style.display = "block";
            return;
        }

        servicesError.style.display = "none";

        const formattedEnd = start.toLocaleTimeString([], { hour: "2-digit", minute: "2-digit" });

        estimatedEndDisplay.textContent =
            `Estimated End Time: ${formattedEnd} (${totalDuration} min)`;
    }

    function isEndTimeValid(startTime, totalDuration) {
        const [h, m] = startTime.split(":").map(Number);
        const start = new Date(`2000-01-01T${startTime}:00`);

        start.setMinutes(start.getMinutes() + totalDuration);

        const clinicClose = new Date("2000-01-01T16:30:00");

        return start <= clinicClose;
    }

    branchSelect.addEventListener("change", () => {
        dateSelect.disabled = false;

        resetDate();
        resetTime();
        resetDentist();
        resetServices();
    });

    dateSelect.addEventListener("change", () => {
        resetTime();
        resetDentist();
        updateEstimatedEndTime();
        if (branchSelect.value && dateSelect.value) {
            loadAvailableTimes();
        }
    });

    timeSelect.addEventListener("change", async () => {
        resetDentist();
        estimatedEndDisplay.textContent = "";
        await loadServicesAfterTime();
        updateEstimatedEndTime();
    });

    servicesContainer.addEventListener("change", (e) => {
        if (e.target.matches("input[type='checkbox']")) {
            updateEstimatedEndTime();
            loadDentists();

            const checkbox = e.target;
            const serviceId = checkbox.value;
            const qtyInput = document.querySelector(`input[name='serviceQuantity[${serviceId}]']`);

            if (qtyInput) {
                qtyInput.style.display = "none";
                qtyInput.value = "";
            }
        }
    });

    servicesContainer.addEventListener("input", (e) => {
        if (e.target.matches("input[name^='serviceQuantity']")) {
            e.target.value = "";
        }
    });

    function resetServiceQuantities() {
        document.querySelectorAll("input[name^='serviceQuantity']").forEach(q => {
            q.value = "";
            q.disabled = true;
        });
        
    timeSelect.value = "";
    estimatedEndDisplay.textContent = "";
    }
});
