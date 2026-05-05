document.addEventListener("DOMContentLoaded", function () {
    const bookingModal = document.getElementById("manageAppointmentModal");
    const bookingBody = document.getElementById("appointmentModalBody");

    document.body.addEventListener("click", function (e) {
        if (e.target.id === "insertAppointmentBtn") {
            renderAppointmentForm();
            bookingModal.style.display = "block";

            const branchSelect = bookingBody.querySelector("#appointmentBranch");
            const dateSelect = bookingBody.querySelector("#appointmentDate");
            const timeSelect = bookingBody.querySelector("#appointmentTime");
            const servicesContainer = bookingBody.querySelector("#servicesContainer");
            const dentistSelect = bookingBody.querySelector("#appointmentDentist");
            const estimatedEndDiv = bookingBody.querySelector("#estimatedEnd");
            const hiddenUserIdInput = bookingBody.querySelector('input[name="user_id"]');

            dateSelect.disabled = true;
            timeSelect.disabled = true;
            dentistSelect.disabled = true;

            loadBranches(branchSelect);

            branchSelect.addEventListener("change", () => {
                resetAll(dateSelect, timeSelect, dentistSelect, estimatedEndDiv);
                loadServices(branchSelect.value, servicesContainer, timeSelect, dentistSelect);
                dateSelect.disabled = false;
            });

            dateSelect.addEventListener("change", () => {
                resetTime(timeSelect, estimatedEndDiv);
                resetDentist(dentistSelect);
                timeSelect.disabled = false;
                loadAvailableTimes(branchSelect, dateSelect, servicesContainer, timeSelect, hiddenUserIdInput);
            });

            timeSelect.addEventListener("change", () => {
                resetDentist(dentistSelect);
                calculateEstimatedEnd(timeSelect.value, servicesContainer, estimatedEndDiv);
                attemptLoadDentists(branchSelect, dateSelect, timeSelect, servicesContainer, dentistSelect);
                hideTimeError();
            });
        }
    });

    function renderAppointmentForm() {
        bookingBody.innerHTML = `
        
            <h2>Book Appointment</h2>
            <form id="manageAppointmentForm" 
                action="${BASE_URL}/Admin/processes/manage_patient/insert_appointment.php" 
                method="POST" autocomplete="off">

                <input type="hidden" name="user_id" value="${userId}">

                <div class="booking-type-selector">
                    <label class="selection-label">Booking For:</label>

                    <div class="radio-row">
                        <label class="radio-option">
                            <input type="radio" name="bookingType" id="bookForSelf" value="self" checked>
                            Patient
                        </label>

                        <label class="radio-option">
                            <input type="radio" name="bookingType" id="bookForChild" value="child">
                            New Dependent (Child, Person with Disability, or Senior Citizen)
                        </label>
                    </div>
                </div>

                <div id="childInfo" style="display:none; margin-top:20px;">
                    <h3 class="section-title">Dependent Information</h3>

                    <div class="form-group">
                        <input type="text" id="childLastName" name="childLastName" class="form-control" placeholder=" ">
                        <label for="childLastName" class="form-label">Dependent Last Name <span class="required">*</span></label>
                    </div>

                    <div class="form-group">
                        <input type="text" id="childFirstName" name="childFirstName" class="form-control" placeholder=" ">
                        <label for="childFirstName" class="form-label">Dependent First Name <span class="required">*</span></label>
                    </div>

                    <div class="form-group">
                        <select id="relationship" name="relationship" class="form-control">
                            <option value="" disabled selected hidden></option>
                            <option value="Parent">Parent</option>
                            <option value="Sibling">Sibling</option>
                            <option value="Child">Child</option>
                        </select>
                        <label for="relationship" class="form-label">
                            Relationship to Guardian <span class="required">*</span>
                        </label>
                    </div>

                    <div class="form-group">
                        <select id="childGender" name="childGender" class="form-control">
                            <option value="" disabled selected hidden></option>
                            <option value="male">Male</option>
                            <option value="female">Female</option>
                        </select>
                        <label for="childGender" class="form-label">Dependent Gender <span class="required">*</span></label>
                    </div>

                    <div class="form-group">
                        <input type="date" id="childDob" name="childDob" class="form-control">
                        <label for="childDob" class="form-label">Dependent Date of Birth <span class="required">*</span></label>
                    </div>
                </div>

                <div class="form-group">
                    <select id="appointmentBranch" name="appointmentBranch" class="form-control" required>
                        <option value="" disabled selected hidden></option>
                    </select>
                    <label for="appointmentBranch" class="form-label">Branch <span class="required">*</span></label>
                </div>

                <div class="form-group">
                    <input type="date" id="appointmentDate" name="appointmentDate" class="form-control" required />
                    <label for="appointmentDate" class="form-label">Date <span class="required">*</span></label>
                    <span id="dateError" class="error-msg-calendar error">Sundays are not available.</span>
                </div>

                <div class="form-group">
                    <select id="appointmentTime" name="appointmentTime" class="form-control" required></select>
                    <label for="appointmentTime" class="form-label">Time <span class="required">*</span></label>
                    <div id="estimatedEnd"></div>
                    <span id="timeError" class="error-msg-calendar error" style="display:none"></span>
                </div>

                <div class="form-group">
                    <div id="servicesContainer" class="checkbox-group">
                        <p class="loading-text">Select a branch to load services</p>
                    </div>
                </div>

                <div class="form-group">
                    <select id="appointmentDentist" name="appointmentDentist" class="form-control" required>
                        <option value="" disabled selected hidden></option>
                    </select>
                    <label for="appointmentDentist" class="form-label">Dentist <span class="required">*</span></label>
                </div>

                <div class="form-group">
                    <textarea id="notes" name="notes" class="form-control" rows="3"></textarea>
                    <label for="notes" class="form-label">Add a note</label>
                </div>

                <div class="button-group">
                    <button type="submit" class="form-button confirm-btn">Confirm</button>
                    <button type="button" class="form-button cancel-btn" onclick="closePatientBookingModal()">Cancel</button>
                </div>
            </form>
        `;

        const bookingTypeSelector = bookingBody.querySelector(".booking-type-selector");
        const childInfo = bookingBody.querySelector("#childInfo");
        const selfRadio = document.getElementById("bookForSelf");
        const childRadio = document.getElementById("bookForChild");

        if (window.IS_DEPENDENT_ACCOUNT) {
            bookingTypeSelector.style.display = "none";

            selfRadio.checked = true;

            selfRadio.disabled = true;
            childRadio.disabled = true;

            childInfo.style.display = "none";

        } else {
            bookingTypeSelector.style.display = "block";
        }

        const childFirst = document.getElementById("childFirstName");
        const childLast = document.getElementById("childLastName");
        const childDob = document.getElementById("childDob");
        const childGender = document.getElementById("childGender");
        const relationship = document.getElementById("relationship");

        if (childDob) {
            const today = new Date();
            const yyyy = today.getFullYear();
            const mm = String(today.getMonth() + 1).padStart(2, "0");
            const dd = String(today.getDate()).padStart(2, "0");
            childDob.max = `${yyyy}-${mm}-${dd}`;
        }

        function resetChildRequirements() {
            childFirst.required = false;
            childLast.required = false;
            childDob.required = false;
            childGender.required = false;
            relationship.required = false;
            relationship.value = "";
        }

        function hideAllBookingForms() {
            childInfo.style.display = "none";

            resetChildRequirements();
        }

        selfRadio.addEventListener("change", () => {
            hideAllBookingForms();
            loadAvailableTimes();
        });

        childRadio.addEventListener("change", () => {
            hideAllBookingForms();
            childInfo.style.display = "block";

            childFirst.required = true;
            childLast.required = true;
            childDob.required = true;
            childGender.required = true;
            relationship.required = true;

            loadAvailableTimes();
        });
        
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

                if (closedDates.includes(formatted)) {
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

    async function getClosedDates(branchId) {
        return fetch(`${BASE_URL}/processes/get_closed_dates.php?branch_id=${branchId}`)
            .then(res => res.json())
            .then(data => data.closedDates || [])
            .catch(() => []);
    }

    function loadBranches(branchSelect) {
        $.ajax({
            type: "GET",
            url: `${BASE_URL}/Admin/processes/index/load_branches.php`,
            success: res => branchSelect.innerHTML = res,
            error: () => branchSelect.innerHTML = '<option disabled>Error loading branches</option>'
        });
    }

    function loadServices(branchId, container, timeSelect, dentistSelect) {
        container.innerHTML = `<p>Loading services...</p>`;

        $.ajax({
            type: "POST",
            url: `${BASE_URL}/processes/load_services.php`,
            data: { appointmentBranch: branchId },
            success: function (response) {
                container.innerHTML = response;

                const estimatedEndDiv = document.getElementById("estimatedEnd");
                const branchSelect = document.getElementById("appointmentBranch");
                const dateSelect = document.getElementById("appointmentDate");

                container.querySelectorAll('input[name="appointmentServices[]"]').forEach(cb => {
                    cb.addEventListener("change", () => {
                        calculateEstimatedEnd(
                            timeSelect.value,
                            container,
                            estimatedEndDiv
                        );

                        if (!timeSelect.value) {
                            dentistSelect.innerHTML = '<option disabled>Select time first</option>';
                            dentistSelect.disabled = true;
                            return;
                        }

                        attemptLoadDentists(
                            branchSelect,
                            dateSelect,
                            timeSelect,
                            container,
                            dentistSelect
                        );
                    });
                });
            },
            error: function () {
                container.innerHTML = `<p class="error-msg">Failed to load services.</p>`;
            }
        });
    }

    function loadAvailableTimes(branchSelect, dateSelect, servicesContainer, timeSelect, hiddenUserIdInput) {
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

                timeSelect.disabled = false;
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

    function attemptLoadDentists(branchSelect, dateSelect, timeSelect, servicesContainer, dentistSelect) {
        const branchId = branchSelect.value;
        const date = dateSelect.value;
        const time = timeSelect.value;

        const services = [...servicesContainer.querySelectorAll("input[type='checkbox']:checked")].map(cb => cb.value);

        if (!branchId || !date || !time || services.length === 0) {
            resetDentist(dentistSelect);
            return;
        }

        dentistSelect.innerHTML = '<option disabled>Loading dentists...</option>';
        dentistSelect.disabled = true;

        loadDentists(branchId, date, time, services, dentistSelect);
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
                dentistSelect.disabled = false;
            },
            error: () => {
                dentistSelect.innerHTML = '<option disabled>Error loading dentists</option>';
                dentistSelect.disabled = true;
            }
        });
    }

    function calculateEstimatedEnd(startTime, servicesContainer, outputDiv) {
        if (!startTime) {
            outputDiv.textContent = "";
            return;
        }

        let totalDuration = 0;

        servicesContainer.querySelectorAll('input[name="appointmentServices[]"]:checked')
            .forEach(cb => {
                const duration = parseInt(cb.dataset.duration || "0");

                totalDuration += duration;
            });

        if (totalDuration === 0) {
            outputDiv.textContent = "";
            return;
        }

        const [h, m] = startTime.split(":").map(Number);
        const end = new Date();
        end.setHours(h, m, 0, 0);
        end.setMinutes(end.getMinutes() + totalDuration);

        const formattedEnd = end.toLocaleTimeString([], {
            hour: "2-digit",
            minute: "2-digit",
        });

        const limit = new Date();
        limit.setHours(16, 30, 0, 0);

        if (end > limit) {
            outputDiv.textContent = "";
            showTimeError("Selected services exceed clinic closing time.");

            servicesContainer.querySelectorAll('input[name="appointmentServices[]"]:checked')
                .forEach(cb => {
                    cb.checked = false;
                });

            const dentistSelect = document.getElementById("appointmentDentist");
            if (dentistSelect) {
                dentistSelect.innerHTML = '<option value="" disabled selected hidden></option>';
                dentistSelect.disabled = true;
            }

            return;
        }

        hideTimeError();
        outputDiv.textContent =
            `Estimated End Time: ${formattedEnd} (${totalDuration} mins)`;
    }

    function resetAll(date, time, dentist, end) {
        resetDate(date);
        resetTime(time, end);
        resetDentist(dentist);
    }

    function resetDate(dateSelect) {
        dateSelect.value = "";
    }

    function resetTime(timeSelect, endDiv) {
        timeSelect.value = "";
        timeSelect.innerHTML = "";
        if (endDiv) endDiv.textContent = "";
    }

    function resetDentist(dentistSelect) {
        dentistSelect.innerHTML = '<option value="" disabled selected hidden></option>';
        dentistSelect.disabled = true;
    }
});

function closePatientBookingModal() {
    document.getElementById("manageAppointmentModal").style.display = "none";
}

function showTimeError(msg) {
    const err = document.getElementById("timeError");
    if (!err) return;

    err.textContent = msg;
    err.style.display = "block";
}

function hideTimeError() {
    const err = document.getElementById("timeError");
    if (!err) return;

    err.style.display = "none";
}