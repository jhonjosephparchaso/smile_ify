document.addEventListener("DOMContentLoaded", function () {
    const appointmentCard = document.getElementById("appointmentCard");
    if (!appointmentCard) return;

    fetch(`${BASE_URL}/Admin/processes/manage_appointment/get_appointment_details.php?id=${appointmentId}`)
    .then(response => {
        if (!response.ok) {
            throw new Error("Forbidden or failed to load.");
        }
        return response.json();
    })
    .then(data => {
        if (data.error) {
            appointmentCard.innerHTML = `<p>${data.error}</p>`;
            return;
        }

        const isDependent = data.is_dependent === true;

        let personalContactHtml = "";
        if (!isDependent) {
            personalContactHtml = `
                <p><strong>Email:</strong><span>${data.email}</span></p>
                <p><strong>Contact Number:</strong><span>${data.contact_number}</span></p>
                <p><strong>Address:</strong><span>${data.address}</span></p>
            `;
        }

        let guardianHtml = "";
        if (isDependent && data.guardian_info) {
            const g = data.guardian_info;
            const guardianAge = calculateAge(g.dob);

            guardianHtml = `
                <hr style="margin:15px 0;">
                <h4 style="margin-bottom:10px;">Guardian Information</h4>

                <p><strong>Name:</strong> <span>${g.full_name}</span></p>
                <p><strong>Gender:</strong> <span>${g.gender}</span></p>
                <p><strong>Date of Birth:</strong> <span>${g.dob}</span></p>
                <p><strong>Age:</strong> <span>${guardianAge}</span></p>
                <p><strong>Email:</strong> <span>${g.email}</span></p>
                <p><strong>Contact Number:</strong> <span>${g.contact_number}</span></p>
                <p><strong>Address:</strong> <span>${g.address}</span></p>
            `;
        }

        const age = calculateAge(data.date_of_birth);

        appointmentCard.innerHTML = `
            <h3>${data.full_name}</h3>

            <p><strong>Gender:</strong><span>${data.gender}</span></p>
            <p><strong>Date of Birth:</strong><span>${data.date_of_birth}</span></p>
            <p><strong>Age:</strong><span>${age}</span></p>

            ${personalContactHtml}

            <p><strong>Registered:</strong><span>${data.joined}</span></p>
            <p><strong>Last Updated:</strong><span>${data.date_updated}</span></p>

            ${guardianHtml}

            <hr>
            <h3>Appointment Details</h3>
            <p><strong>Appointment ID:</strong><span>${data.appointment_transaction_id}</span></p>
            <p><strong>Branch:</strong><span>${data.branch}</span></p>
            <p><strong>Service:</strong><span>${data.services}</span></p>
            <p><strong>Dentist:</strong><span>${data.dentist}</span></p>
            <p><strong>Date:</strong><span>${data.appointment_date}</span></p>
            <p><strong>Time:</strong><span>${data.appointment_time}</span></p>
            <p><strong>Status:</strong><span>${data.status}</span></p>
            <p><strong>Notes:</strong><span>${data.notes || '-'}</span></p>
            <p><strong>Date Booked:</strong><span>${data.date_created}</span></p>

            <div class="button-group button-group-profile">
                <button class="confirm-btn" id="markDone">Complete Transaction</button>
                <button class="confirm-btn" id="reSched">Resched Appointment</button>
                <button class="cancel-btn-appointment" id="markCancel">Cancel Appointment</button>
            </div>
        `;

        const hasTx = Number(data.has_transaction || 0);
        const hasVitals = Number(data.has_vitals || 0);
        const hasPres = Number(data.has_prescriptions || 0);

        const reSchedBtn = document.getElementById("reSched");
        const cancelBtn = document.getElementById("markCancel");
        const completeBtn = document.getElementById("markDone");

        if (hasTx > 0 || hasVitals > 0 || hasPres > 0) {
            if (reSchedBtn) reSchedBtn.style.display = "none";
            if (cancelBtn) cancelBtn.style.display = "none";
        }

        if (hasTx === 0 && hasVitals === 0 && hasPres === 0) {
            if (completeBtn) completeBtn.style.display = "none";
        }
        
        if (reSchedBtn) {
            reSchedBtn.addEventListener("click", () => {
                openReschedModal(data);
            });
        }

        if (completeBtn) {
            completeBtn.addEventListener("click", () => {
                openStatusModal({
                    action: "complete",
                    formAction: `${BASE_URL}/Admin/processes/manage_appointment/complete_dental_transaction.php`,
                    message: "Are you sure you want to <strong>complete</strong> this patient's transaction?"
                });
            });
        }

        if (cancelBtn) {
            cancelBtn.addEventListener("click", () => {
                openStatusModal({
                    action: "cancel",
                    formAction: `${BASE_URL}/Admin/processes/manage_appointment/cancel_appointment.php`,
                    message: "Are you sure you want to <strong>cancel</strong> this patient's transaction?"
                });
            });
        }

        function openStatusModal({ action, formAction, message }) {
            const modal = document.getElementById("setStatusModal");
            const form = document.getElementById("statusForm");
            const messageBox = document.getElementById("statusMessage");
            const statusInput = document.getElementById("statusValue");
            const userInput = document.getElementById("statusUserId");
            const appointmentInput = document.getElementById("statusAppointmentId");

            form.action = formAction;
            messageBox.innerHTML = message;
            statusInput.value = action;
            userInput.value = data.user_id;
            appointmentInput.value = appointmentId;
            modal.style.display = "block";
        }

        window.closeStatusModal = function () {
            document.getElementById("setStatusModal").style.display = "none";
        };

        window.addEventListener("click", (e) => {
            const modal = document.getElementById("setStatusModal");
            if (e.target === modal) modal.style.display = "none";
        });
    })
    .catch(error => {
        appointmentCard.innerHTML = "<p>Error loading profile.</p>";
        console.error("Fetch error:", error);
    });
});

function calculateAge(dobString) {
    if (!dobString) return "-";
    const dob = new Date(dobString);
    if (isNaN(dob)) return "-";

    const today = new Date();
    let age = today.getFullYear() - dob.getFullYear();
    const monthDiff = today.getMonth() - dob.getMonth();

    if (monthDiff < 0 || (monthDiff === 0 && today.getDate() < dob.getDate())) {
        age--;
    }
    return age;
}

function openReschedModal(data) {
    const bookingModal = document.getElementById("manageAppointmentModal");
    const bookingBody = document.getElementById("appointmentModalBody");

    renderReschedForm(data);
    bookingModal.style.display = "block";

    const branchSelect = bookingBody.querySelector("#appointmentBranch");
    const dateSelect = bookingBody.querySelector("#appointmentDate");
    const timeSelect = bookingBody.querySelector("#appointmentTime");
    const servicesContainer = bookingBody.querySelector("#servicesContainer");
    const dentistSelect = bookingBody.querySelector("#appointmentDentist");

    branchSelect.addEventListener("change", () => {
        resetDate(dateSelect)
        resetTime(timeSelect);
        resetServices(servicesContainer);
        resetDentist(dentistSelect);
        estimatedEnd.textContent = "";
    });

    dateSelect.addEventListener("change", () => {
        resetTime(timeSelect);
        resetDentist(dentistSelect);
        estimatedEnd.textContent = "";
        loadAvailableTimes(branchSelect, dateSelect, servicesContainer, timeSelect, null);
    });

    timeSelect.addEventListener("change", () => {
        calculateEstimatedEnd(
            timeSelect.value,
            servicesContainer,
            bookingBody.querySelector("#estimatedEnd")
        );

        attemptLoadDentists(
            branchSelect,
            dateSelect,
            timeSelect,
            servicesContainer,
            dentistSelect
        );
    });

    window.preselectedServiceIds = data.services_ids;

    loadBranches(branchSelect, () => {
        branchSelect.value = data.branch_id;

        getClosedDates(branchSelect.value).then(dates => {
            window.closedDates = dates;
        });

        loadServices(branchSelect.value, servicesContainer, timeSelect, dentistSelect, () => {
            preselectServices(window.preselectedServiceIds, servicesContainer);

            dateSelect.value = data.raw_appointment_date;

            loadAvailableTimes(branchSelect, dateSelect, servicesContainer, timeSelect, null, () => {
                const wantedTime = normalizeTimeToHHMM(data.raw_appointment_time);

                waitForOption(timeSelect, wantedTime, () => {
                    timeSelect.value = wantedTime;
                    timeSelect.dispatchEvent(new Event("change"));

                    setTimeout(() => {
                        servicesContainer.querySelectorAll('input[name="appointmentServices[]"]:checked')
                            .forEach(cb => cb.dispatchEvent(new Event("change")));
                    }, 200);
                });
            });
        });
    });
}

function waitForOption(select, value, callback, attempts = 0) {
    const want = normalizeTimeToHHMM(value);

    if (attempts > 60) return;

    const options = Array.from(select.querySelectorAll("option"));
    const found = options.find(opt => normalizeTimeToHHMM(opt.value) === want);

    if (found) {
        select.value = found.value;
        select.dispatchEvent(new Event("change"));
        if (callback) callback();
    } else {
        setTimeout(() => {
            waitForOption(select, value, callback, attempts + 1);
        }, 100);
    }
}

function renderReschedForm(data) {
    const bookingBody = document.getElementById("appointmentModalBody");

    bookingBody.innerHTML = `
        <h2>Reschedule Appointment</h2>
        <form id="manageAppointmentForm" 
            action="${BASE_URL}/Admin/processes/manage_appointment/reschedule_appointment.php"
            method="POST" autocomplete="off">

            <input type="hidden" name="appointment_transaction_id" value="${data.appointment_transaction_id}">

            <div class="form-group">
                <select id="appointmentBranch" name="appointmentBranch" class="form-control" required>
                    <option value="" disabled hidden></option>
                </select>
                <label class="form-label">Branch <span class="required">*</span></label>
            </div>

            <div class="form-group">
                <input type="date" id="appointmentDate" name="appointmentDate" class="form-control" required />
                <label class="form-label">Date <span class="required">*</span></label>
                <span id="dateError" class="error-msg-calendar error"></span>
            </div>

            <div class="form-group">
                <select id="appointmentTime" name="appointmentTime" class="form-control" required></select>
                <label class="form-label">Time <span class="required">*</span></label>
                <div id="estimatedEnd"></div>
                <span id="timeError" class="error-msg-calendar error" style="display:none"></span>
            </div>

            <div class="form-group">
                <div id="servicesContainer" class="checkbox-group">
                    <p>Loading services...</p>
                </div>
            </div>

            <div class="form-group">
                <select id="appointmentDentist" name="appointmentDentist" class="form-control" required>
                    <option value="" disabled hidden></option>
                </select>
                <label class="form-label">Dentist <span class="required">*</span></label>
            </div>

            <div class="button-group">
                <button type="submit" class="form-button confirm-btn">Save Changes</button>
                <button type="button" class="form-button cancel-btn" onclick="closePatientBookingModal()">Cancel</button>
            </div>
        </form>
    `;

    const dateInput = bookingBody.querySelector("#appointmentDate");
    const errorMsg = bookingBody.querySelector("#dateError");

    if (dateInput && errorMsg) {
        const now = new Date();
        const lastStart = new Date();
        lastStart.setHours(16, 0, 0, 0);

        function toLocalDateStringPH(date) {
            const year = date.getFullYear();
            const month = String(date.getMonth() + 1).padStart(2, "0");
            const day = String(date.getDate()).padStart(2, "0");
            return `${year}-${month}-${day}`;
        }

        const minDate = new Date();

        if (now >= lastStart) {
            minDate.setDate(minDate.getDate() + 1);
        }

        dateInput.min = toLocalDateStringPH(minDate);

        let closedDates = [];
        const branchSelect = bookingBody.querySelector("#appointmentBranch");
        if (branchSelect) {
            branchSelect.addEventListener("change", async () => {
                const branchId = branchSelect.value;
                closedDates = branchId ? await getClosedDates(branchId) : [];
            });
        }

        dateInput.addEventListener("input", function () {
            if (!this.value) return;
            const selectedDate = new Date(this.value);
            const day = selectedDate.getDay();
            const formatted = this.value;

            if (day === 0) {
                this.value = "";
                this.classList.add("is-invalid");
                errorMsg.textContent = "Sundays are not available for appointments.";
                errorMsg.style.display = "block";
                return;
            }

            if (window.closedDates && window.closedDates.includes(formatted)) {
                this.value = "";
                this.classList.add("is-invalid");
                errorMsg.textContent = "This date is unavailable due to a branch closure.";
                errorMsg.style.display = "block";
                return;
            }

            this.classList.remove("is-invalid");
            errorMsg.style.display = "none";
        });
    }
}

function preselectServices(selectedIds = [], container) {
    container.querySelectorAll('input[name="appointmentServices[]"]').forEach(cb => {
        if (selectedIds.includes(Number(cb.value))) {
            cb.checked = true;
        }
    });
}

function loadServices(branchId, container, timeSelect, dentistSelect, callback) {
    container.innerHTML = `<p>Loading services...</p>`;

    const bookingBody = document.getElementById("appointmentModalBody");

    $.ajax({
        type: "POST",
        url: `${BASE_URL}/processes/load_services.php`,
        data: { appointmentBranch: branchId },
        success: function (response) {
            container.innerHTML = response;

            preselectServices(window.preselectedServiceIds, container);

            container.querySelectorAll('input[name="appointmentServices[]"]').forEach(cb => {

                cb.addEventListener("change", () => {
                    if (!timeSelect.value) {
                        dentistSelect.innerHTML = '<option disabled>Select time first</option>';
                        dentistSelect.disabled = true;
                        return;
                    }

                    attemptLoadDentists(
                        bookingBody.querySelector("#appointmentBranch"),
                        bookingBody.querySelector("#appointmentDate"),
                        bookingBody.querySelector("#appointmentTime"),
                        container,
                        bookingBody.querySelector("#appointmentDentist")
                    );

                    calculateEstimatedEnd(
                        bookingBody.querySelector("#appointmentTime").value,
                        container,
                        bookingBody.querySelector("#estimatedEnd")
                    );
                });
            });

            if (callback) callback();
        },
        error: function () {
            container.innerHTML = `<p class="error-msg">Failed to load services.</p>`;
        }
    });
}

function loadDentists(branchId, date, time, services, dentistSelect) {
    $.ajax({
        type: "POST",
        url: `${BASE_URL}/processes/load_dentists.php`,
        data: {
            appointmentBranch: branchId,
            appointmentDate: date,
            appointmentTime: time,
            appointmentServices: services
        },
        success: res => {
            dentistSelect.innerHTML = res;
        },
        error: () => {
            dentistSelect.innerHTML = '<option disabled>Error loading dentists</option>';
        }
    });
}

function attemptLoadDentists(branchSelect, dateSelect, timeSelect, servicesContainer, dentistSelect, force = false) {
    const branchId = branchSelect.value;
    const date = dateSelect.value;
    const time = timeSelect.value;

    let services = [...servicesContainer.querySelectorAll("input[type='checkbox']:checked")]
        .map(cb => cb.value);

    if (force && services.length === 0) {
        services = [...servicesContainer.querySelectorAll("input[type='checkbox']")]
            .map(cb => cb.value);
    }

    if (!branchId || !date || !time || services.length === 0) {
        resetDentist(dentistSelect);
        return;
    }

    dentistSelect.innerHTML = '<option disabled>Loading dentists...</option>';

    loadDentists(branchId, date, time, services, dentistSelect);
}

function loadAvailableTimes(branchSelect, dateSelect, servicesContainer, timeSelect, hiddenUserIdInput, callback) {
    const branchId = branchSelect.value;
    const date = dateSelect.value;
    if (!branchId || !date) {
        resetTime(timeSelect);
        return;
    }

    const now = new Date();
    const selectedDateObj = new Date(dateSelect.value);
    const isToday = selectedDateObj.toDateString() === now.toDateString();

    const fd = new FormData();
    fd.append("branch_id", branchId);
    fd.append("appointment_date", date);

    if (hiddenUserIdInput && hiddenUserIdInput.value) {
        fd.append("user_id", hiddenUserIdInput.value);
    }

    fetch(`${BASE_URL}/processes/load_available_times.php`, {
        method: "POST",
        body: fd
    })
    .then(res => res.json())
    .then(data => {
        timeSelect.innerHTML = '<option value="" disabled selected hidden></option>';
        const allSlots = generateSlots("09:00", "16:30", 30);
        const availableTimes = data.times || [];
        const blockedSet = new Set(data.blocked || []);

        allSlots.forEach(time => {
            const formatted = formatTimeAMPM(time);

            let isAvailable = availableTimes.includes(time);

            if (isToday) {
                const [hh, mm] = time.split(":");
                const slotTime = new Date();
                slotTime.setHours(hh, mm, 0, 0);

                if (slotTime <= now) {
                    isAvailable = false;
                }
            }

            if (blockedSet.has(time)) isAvailable = false;

            timeSelect.innerHTML += `
                <option value="${time}" ${isAvailable ? "" : "disabled"}>
                    ${formatted}
                </option>
            `;
        });

        if (callback) callback();
    })
    .catch(err => {
        console.error("loadAvailableTimes error:", err);
        resetTime(timeSelect);
    });
}

function generateSlots(start, end, mins) {
    const out = [];
    let t = new Date(`2000-01-01T${start}`);
    const e = new Date(`2000-01-01T${end}`);

    while (t < e) {
        out.push(t.toTimeString().slice(0, 5));
        t.setMinutes(t.getMinutes() + mins);
    }
    return out;
}

function formatTimeAMPM(time) {
    const [h, m] = time.split(":");
    return new Date(0, 0, 0, h, m).toLocaleTimeString([], { hour: "2-digit", minute: "2-digit" });
}

function calculateEstimatedEnd(startTime, servicesContainer, outputDiv) {
    if (!startTime) {
        outputDiv.textContent = "";
        hideTimeError();
        return;
    }

    let totalDuration = 0;

    servicesContainer.querySelectorAll("input[type='checkbox']:checked")
        .forEach(cb => {
            const duration = parseInt(cb.dataset.duration || "0");
            totalDuration += duration;
        });

    if (totalDuration === 0) {
        outputDiv.textContent = "";
        hideTimeError();
        return;
    }

    const [h, m] = startTime.split(":").map(Number);

    const end = new Date();
    end.setHours(h, m, 0, 0);
    end.setMinutes(end.getMinutes() + totalDuration);

    const closing = new Date();
    closing.setHours(16, 30, 0, 0);

    if (end > closing) {
        showTimeError("Selected services exceed clinic closing time.");

        outputDiv.textContent = "";

        servicesContainer.querySelectorAll('input[type="checkbox"]:checked')
            .forEach(cb => cb.checked = false);

        const dentistSelect = document.getElementById("appointmentDentist");
        if (dentistSelect) {
            dentistSelect.innerHTML = '<option value="" disabled selected hidden></option>';
            dentistSelect.disabled = true;
        }

        return;
    }

    hideTimeError();

    const dentistSelect = document.getElementById("appointmentDentist");
    if (dentistSelect) {
        dentistSelect.disabled = false;
    }

    const formattedEnd = end.toLocaleTimeString([], {
        hour: "2-digit",
        minute: "2-digit"
    });

    outputDiv.textContent =
        `Estimated End Time: ${formattedEnd} (${totalDuration} mins)`;
}

function resetTime(timeSelect, endDiv) {
    timeSelect.value = "";
    timeSelect.innerHTML = "";
    if (endDiv) endDiv.textContent = "";
}

function resetDentist(dentistSelect) {
    dentistSelect.innerHTML = '<option value="" disabled selected hidden></option>';
}

function resetDate(dateSelect) {
    dateSelect.value = "";
}

function resetServices(container) {
    container.querySelectorAll('input[type="checkbox"]').forEach(cb => cb.checked = false);
}

async function getClosedDates(branchId) {
    return fetch(`${BASE_URL}/processes/get_closed_dates.php?branch_id=${branchId}`)
        .then(res => res.json())
        .then(data => data.closedDates || [])
        .catch(() => []);
}

function loadBranches(branchSelect, callback) {
    $.ajax({
        type: "GET",
        url: `${BASE_URL}/Admin/processes/index/load_branches.php`,
        success: res => {
            branchSelect.innerHTML = res;
            if (callback) callback();
        },
        error: () => {
            branchSelect.innerHTML = '<option disabled>Error loading branches</option>';
            if (callback) callback();
        }
    });
}

function normalizeTimeToHHMM(t) {
    if (!t) return "";
    const m = ("" + t).match(/^(\d{1,2}):(\d{2})/);
    if (!m) return "";
    return `${m[1].padStart(2, "0")}:${m[2]}`;
}
